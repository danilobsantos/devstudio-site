<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class PostResourceTest extends TestCase
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
        $user->email = 'post@example.invalid';
        $user->password = 'synthetic-test-password';
        $user->role = UserRole::Editor;
        $user->save();
        $this->actingAs($user);
    }

    public function test_create_edit_list_delete_and_categories(): void
    {
        $category = new Category;
        $category->name = 'Post';
        $category->slug = 'post';
        $category->type = 'post';
        $category->save();
        $wrong = new Category;
        $wrong->name = 'Project';
        $wrong->slug = 'project';
        $wrong->type = 'project';
        $wrong->save();
        Livewire::test(CreatePost::class)->fillForm(['title' => 'Title', 'slug' => 'title'])->call('create')->assertHasNoFormErrors();
        $post = Post::query()->where('slug', 'title')->firstOrFail();
        $this->assertSame(auth()->id(), $post->author_id);
        $this->assertNull($post->category_id);
        $post->cover_image = 'existing.png';
        $post->save();
        Livewire::test(EditPost::class, ['record' => $post->id])->fillForm(['title' => 'Changed', 'slug' => 'title', 'category_id' => $category->id])->call('save')->assertHasNoFormErrors();
        $this->assertSame('existing.png', $post->fresh()->cover_image);
        $this->assertSame($category->id, $post->fresh()->category_id);
        Livewire::test(EditPost::class, ['record' => $post->id])->fillForm(['title' => 'Bad', 'slug' => 'title', 'category_id' => $wrong->id])->call('save')->assertHasFormErrors(['category_id']);
        $this->assertSame('Changed', $post->fresh()->title);
        Livewire::test(CreatePost::class)->fillForm(['title' => 'Bad', 'slug' => 'bad', 'category_id' => 99999])->call('create')->assertHasFormErrors(['category_id']);
        Livewire::test(ListPosts::class)->assertCanSeeTableRecords([$post]);
        Livewire::test(EditPost::class, ['record' => $post->id])->callAction(DeleteAction::class)->assertHasNoActionErrors();
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_validation_tags_and_permissions(): void
    {
        $component = Livewire::test(CreatePost::class)->fillForm(['title' => 'Post', 'slug' => 'post']);
        $component->set('data.tags', 'bad')->call('create')->assertHasFormErrors(['tags']);
        $component->callFormComponentAction('tags', 'add')->call('create')->assertHasFormErrors(['tags']);
        $id = array_key_first($component->get('data.tags'));
        $component->set("data.tags.$id.title", 'Valid')->call('create')->assertHasNoFormErrors();
        $post = Post::query()->where('slug', 'post')->firstOrFail();
        $this->assertSame('Valid', $post->tags[0]['title']);
        $recover = Livewire::test(EditPost::class, ['record' => $post->id])->set('data.title', 'Updated');
        $recover->set('data.tags', 'bad')->call('save')->assertHasFormErrors(['tags']);
        $this->assertSame('Post', $post->fresh()->title);
        $old = array_key_first($recover->get('data.tags'));
        $recover->callFormComponentAction('tags', 'add')->call('save')->assertHasFormErrors(['tags']);
        $ids = array_keys($recover->get('data.tags'));
        $new = end($ids);
        $recover->set("data.tags.$new.title", 'New')->callFormComponentAction('tags', 'delete', arguments: ['item' => $old])->call('save')->assertHasNoFormErrors();
        $this->assertSame('New', $post->fresh()->tags[0]['title']);
        Livewire::test(CreatePost::class)->fillForm(['title' => 'Duplicate', 'slug' => 'post'])->call('create')->assertHasFormErrors(['slug']);
        Livewire::test(CreatePost::class)->fillForm(['title' => 'Other', 'slug' => 'post', 'locale' => 'en'])->call('create')->assertHasNoFormErrors();
        Livewire::test(CreatePost::class)->fillForm(['title' => '<script>', 'slug' => 'bad'])->call('create')->assertHasFormErrors(['title']);
        Livewire::test(EditPost::class, ['record' => $post->id])->set('data.author_id', 999)->call('save')->assertHasFormErrors(['title']);
        $this->assertSame(auth()->id(), $post->fresh()->author_id);
        $edit = Livewire::test(EditPost::class, ['record' => $post->id])->set('data.title', 'Denied');
        auth()->user()->role = null;
        auth()->user()->save();
        $edit->call('save')->assertForbidden();
        Livewire::test(CreatePost::class)->assertForbidden();
        auth()->logout();
        Livewire::test(CreatePost::class)->assertForbidden();
    }

    public function test_metadata_limits_and_plain_text_are_validated(): void
    {
        Livewire::test(CreatePost::class)->fillForm(['title' => 'Artigo', 'slug' => 'artigo-meta', 'meta_title' => str_repeat('a', 71)])->call('create')->assertHasFormErrors(['meta_title']);
        Livewire::test(CreatePost::class)->fillForm(['title' => 'Artigo', 'slug' => 'artigo-meta', 'meta_description' => str_repeat('a', 201)])->call('create')->assertHasFormErrors(['meta_description']);
        Livewire::test(CreatePost::class)->fillForm(['title' => 'Artigo', 'slug' => 'artigo-meta', 'meta_title' => "linha\nquebrada"])->call('create')->assertHasFormErrors(['meta_title']);
    }

    public function test_rejection_marker_is_locked(): void
    {
        $component = Livewire::test(CreatePost::class)->set('data.tags', 'bad');
        $this->expectException(CannotUpdateLockedPropertyException::class);
        $component->set('tagsRejected', false);
    }
}
