<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--database' => 'sqlite', '--force' => true]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $user = new User;
        $user->name = 'Synthetic';
        $user->email = 'project@example.invalid';
        $user->password = 'synthetic-test-password';
        $user->role = UserRole::Admin;
        $user->save();
        $this->actingAs($user);
    }

    public function test_create_edit_list_delete_and_category_validation(): void
    {
        $projectCategory = new Category;
        $projectCategory->name = 'Project';
        $projectCategory->slug = 'project';
        $projectCategory->type = 'project';
        $projectCategory->save();
        $postCategory = new Category;
        $postCategory->name = 'Post';
        $postCategory->slug = 'post';
        $postCategory->type = 'post';
        $postCategory->save();
        Livewire::test(CreateProject::class)->fillForm(['title' => 'Test', 'slug' => 'test'])->call('create')->assertHasNoFormErrors();
        $project = Project::query()->where('slug', 'test')->firstOrFail();
        $this->assertNull($project->category_id);
        $project->cover_image = 'existing.png';
        $project->save();
        Livewire::test(EditProject::class, ['record' => $project->id])->fillForm(['title' => 'Changed', 'slug' => 'test', 'category_id' => $projectCategory->id])->call('save')->assertHasNoFormErrors();
        $this->assertSame('existing.png', $project->fresh()->cover_image);
        $this->assertSame($projectCategory->id, $project->fresh()->category_id);
        Livewire::test(EditProject::class, ['record' => $project->id])->fillForm(['title' => 'Bad', 'slug' => 'test', 'category_id' => $postCategory->id])->call('save')->assertHasFormErrors(['category_id']);
        $this->assertSame('Changed', $project->fresh()->title);
        Livewire::test(CreateProject::class)->fillForm(['title' => 'Bad', 'slug' => 'bad', 'category_id' => $postCategory->id])->call('create')->assertHasFormErrors(['category_id']);
        Livewire::test(CreateProject::class)->fillForm(['title' => 'Bad', 'slug' => 'bad', 'category_id' => 99999])->call('create')->assertHasFormErrors(['category_id']);
        Livewire::test(EditProject::class, ['record' => $project->id])->fillForm(['title' => 'Changed', 'slug' => 'test', 'category_id' => null])->call('save')->assertHasNoFormErrors();
        $this->assertNull($project->fresh()->category_id);
        Livewire::test(ListProjects::class)->assertCanSeeTableRecords([$project]);
        Livewire::test(EditProject::class, ['record' => $project->id])->callAction(DeleteAction::class)->assertHasNoActionErrors();
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_bad_fields_and_slug_are_rejected(): void
    {
        Livewire::test(CreateProject::class)->fillForm(['title' => 'First', 'slug' => 'first'])->call('create')->assertHasNoFormErrors();
        Livewire::test(CreateProject::class)->fillForm(['title' => 'Second', 'slug' => 'first'])->call('create')->assertHasFormErrors(['slug']);
        Livewire::test(CreateProject::class)->fillForm(['title' => 'Second', 'slug' => 'first', 'locale' => 'en'])->call('create')->assertHasNoFormErrors();
        $second = Project::query()->where('locale', 'en')->firstOrFail();
        Livewire::test(EditProject::class, ['record' => $second->id])->fillForm(['title' => 'Second', 'slug' => 'first', 'locale' => 'pt_BR'])->call('save')->assertHasFormErrors(['slug']);
        $this->assertSame('en', $second->fresh()->locale);
        Livewire::test(CreateProject::class)->fillForm(['title' => '<script>', 'slug' => 'bad'])->call('create')->assertHasFormErrors(['title']);
        Livewire::test(CreateProject::class)->fillForm(['title' => 'Bad', 'slug' => 'bad', 'external_url' => '/%2fexample.invalid'])->call('create')->assertHasFormErrors(['external_url']);
        Livewire::test(CreateProject::class)->fillForm(['title' => 'Bad', 'slug' => 'bad'])->set('data.technologies', ['a' => ['title' => 'Test', 'icon' => 'bad']])->call('create')->assertHasFormErrors(['technologies']);
    }

    public function test_metadata_limits_and_plain_text_are_validated(): void
    {
        Livewire::test(CreateProject::class)->fillForm(['title' => 'Projeto', 'slug' => 'projeto-meta', 'meta_title' => str_repeat('a', 71)])->call('create')->assertHasFormErrors(['meta_title']);
        Livewire::test(CreateProject::class)->fillForm(['title' => 'Projeto', 'slug' => 'projeto-meta', 'meta_description' => str_repeat('a', 201)])->call('create')->assertHasFormErrors(['meta_description']);
        Livewire::test(CreateProject::class)->fillForm(['title' => 'Projeto', 'slug' => 'projeto-meta', 'meta_description' => '<b>HTML</b>'])->call('create')->assertHasFormErrors(['meta_description']);
    }

    public function test_repeater_and_role_revocation(): void
    {
        $create = Livewire::test(CreateProject::class)->fillForm(['title' => 'Test', 'slug' => 'test']);
        $create->set('data.technologies', 'bad')->call('create')->assertHasFormErrors(['technologies']);
        $create->callFormComponentAction('technologies', 'add')->call('create')->assertHasFormErrors(['technologies']);
        $id = array_key_first($create->get('data.technologies'));
        $create->set("data.technologies.$id.title", 'Valid')->call('create')->assertHasNoFormErrors();
        $project = Project::query()->where('slug', 'test')->firstOrFail();
        $this->assertSame('Valid', $project->technologies[0]['title']);
        $recover = Livewire::test(EditProject::class, ['record' => $project->id]);
        $old = array_key_first($recover->get('data.technologies'));
        $recover->set('data.technologies', 'bad')->callFormComponentAction('technologies', 'add')->call('save')->assertHasFormErrors(['technologies']);
        $ids = array_keys($recover->get('data.technologies'));
        $new = end($ids);
        $recover->set("data.technologies.$new.title", 'New')->callFormComponentAction('technologies', 'delete', arguments: ['item' => $old])->call('save')->assertHasNoFormErrors();
        $this->assertSame('New', $project->fresh()->technologies[0]['title']);
        $edit = Livewire::test(EditProject::class, ['record' => $project->id])->set('data.title', 'Denied');
        auth()->user()->role = null;
        auth()->user()->save();
        $edit->call('save')->assertForbidden();
        $this->assertSame('Test', $project->fresh()->title);
        Livewire::test(CreateProject::class)->assertForbidden();
        auth()->logout();
        Livewire::test(CreateProject::class)->assertForbidden();
    }

    public function test_rejection_marker_is_locked(): void
    {
        $component = Livewire::test(CreateProject::class)->set('data.technologies', 'bad');
        $this->expectException(CannotUpdateLockedPropertyException::class);
        $component->set('technologiesRejected', false);
    }
}
