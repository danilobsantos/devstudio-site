<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
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
        $user->email = 'category@example.invalid';
        $user->password = 'synthetic-test-password';
        $user->role = UserRole::Admin;
        $user->save();
        $this->actingAs($user);
    }

    public function test_crud_and_raw_validation(): void
    {
        Livewire::test(CreateCategory::class)->fillForm(['name' => ' Category ', 'slug' => 'category', 'type' => 'post'])->call('create')->assertHasNoFormErrors();
        $category = Category::query()->where('slug', 'category')->firstOrFail();
        $this->assertSame('Category', $category->name);
        $this->assertNull($category->description);
        Livewire::test(CreateCategory::class)->fillForm(['name' => 'Duplicate', 'slug' => 'category', 'type' => 'project'])->call('create')->assertHasFormErrors(['slug']);
        Livewire::test(CreateCategory::class)->fillForm(['name' => '<script>', 'slug' => 'bad', 'type' => 'post'])->call('create')->assertHasFormErrors(['name']);
        Livewire::test(CreateCategory::class)->fillForm(['name' => 'Bad', 'slug' => 'bad', 'type' => 'unknown'])->call('create')->assertHasFormErrors(['type']);
        Livewire::test(EditCategory::class, ['record' => $category->id])->fillForm(['name' => 'Changed', 'slug' => 'category'])->set('data.type', 'project')->call('save')->assertHasFormErrors(['type']);
        $this->assertSame('post', $category->fresh()->type);
        Livewire::test(EditCategory::class, ['record' => $category->id])->fillForm(['name' => 'Bad', 'slug' => 'category'])->set('data.unknown', 'x')->call('save')->assertHasFormErrors(['name']);
        Livewire::test(EditCategory::class, ['record' => $category->id])->fillForm(['name' => 'Renamed', 'slug' => 'category'])->call('save')->assertHasNoFormErrors();
        $this->assertSame('Renamed', $category->fresh()->name);
        Livewire::test(ListCategories::class)->assertCanSeeTableRecords([$category]);
        Livewire::test(EditCategory::class, ['record' => $category->id])->callAction(DeleteAction::class)->assertHasNoActionErrors();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_referenced_categories_cannot_be_deleted(): void
    {
        foreach (['post', 'project'] as $type) {
            $category = new Category;
            $category->name = $type;
            $category->slug = $type;
            $category->type = $type;
            $category->save();
            $content = $type === 'post' ? new Post : new Project;
            $content->title = 'Synthetic';
            $content->slug = $type;
            $content->category_id = $category->id;
            if ($content instanceof Post) {
                $content->author_id = auth()->id();
            }
            $content->save();
            Livewire::test(EditCategory::class, ['record' => $category->id])->callAction(DeleteAction::class)->assertNotified('Categoria vinculada a conteúdo não pode ser excluída.');
            $this->assertDatabaseHas('categories', ['id' => $category->id]);
            $this->assertDatabaseHas($type === 'post' ? 'posts' : 'projects', ['id' => $content->id, 'category_id' => $category->id]);
        }
    }

    public function test_permissions_are_enforced_after_mount(): void
    {
        auth()->user()->role = UserRole::Editor;
        auth()->user()->save();
        Livewire::test(CreateCategory::class)->fillForm(['name' => 'Editor', 'slug' => 'editor', 'type' => 'post'])->call('create')->assertHasNoFormErrors();
        $component = Livewire::test(CreateCategory::class)->fillForm(['name' => 'Denied', 'slug' => 'denied', 'type' => 'post']);
        auth()->user()->role = null;
        auth()->user()->save();
        $component->call('create')->assertForbidden();
        Livewire::test(CreateCategory::class)->assertForbidden();
        auth()->logout();
        Livewire::test(CreateCategory::class)->assertForbidden();
        $this->assertSame(1, Category::query()->count());
    }
}
