<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class PublishedAtInputTest extends TestCase
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
        $user->email = 'published-at@example.invalid';
        $user->password = 'synthetic-test-password';
        $user->role = UserRole::Admin;
        $user->save();
        $this->actingAs($user);
    }

    public function test_all_resources_store_local_input_as_utc_and_display_it_back_locally(): void
    {
        foreach ($this->resources() as $name => [$create, $edit, $model, $table, $data]) {
            Livewire::test($create)
                ->fillForm($data('agenda-'.$name) + ['published_at' => '2026-11-12T12:00'])
                ->call('create')->assertHasNoFormErrors();
            $record = $model::query()->where('slug', 'agenda-'.$name)->firstOrFail();
            $this->assertSame('2026-11-12 15:00:00', DB::table($table)->where('id', $record->id)->value('published_at'), $name);
            $this->assertSame('2026-11-12 15:00:00', $record->published_at->utc()->format('Y-m-d H:i:s'), $name);

            Livewire::test($edit, ['record' => $record->getRouteKey()])
                ->assertFormSet(['published_at' => '2026-11-12T12:00'])
                ->fillForm($data('agenda-'.$name) + ['published_at' => '2026-11-12T12:00'])
                ->call('save')->assertHasNoFormErrors();
            $this->assertSame('2026-11-12 15:00:00', DB::table($table)->where('id', $record->id)->value('published_at'), $name);
        }
    }

    public function test_local_boundary_is_compared_in_utc(): void
    {
        Livewire::test(CreatePage::class)
            ->fillForm(['title' => 'Agendada', 'slug' => 'agendada', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'published', 'published_at' => '2026-11-12T12:00', 'sections' => []])
            ->call('create')->assertHasNoFormErrors();

        $this->travelTo('2026-11-12 14:59:59');
        $this->assertSame(0, Page::query()->publishedInLocale('pt_BR')->count());
        $this->travelTo('2026-11-12 15:00:00');
        $this->assertSame(1, Page::query()->publishedInLocale('pt_BR')->count());
    }

    public function test_edit_changes_the_local_value_and_reconverts(): void
    {
        Livewire::test(CreatePage::class)
            ->fillForm(['title' => 'Mudança', 'slug' => 'mudanca', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'published', 'published_at' => '2026-11-12T12:00', 'sections' => []])
            ->call('create')->assertHasNoFormErrors();
        $page = Page::query()->where('slug', 'mudanca')->firstOrFail();

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->fillForm(['title' => 'Mudança', 'slug' => 'mudanca', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'published', 'published_at' => '2026-11-13T09:30', 'sections' => []])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame('2026-11-13 12:30:00', DB::table('pages')->where('id', $page->id)->value('published_at'));
        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->assertFormSet(['published_at' => '2026-11-13T09:30']);
    }

    public function test_null_published_at_stays_null_and_is_immediately_eligible(): void
    {
        Livewire::test(CreatePage::class)
            ->fillForm(['title' => 'Imediata', 'slug' => 'imediata', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'published', 'sections' => []])
            ->call('create')->assertHasNoFormErrors();
        $page = Page::query()->where('slug', 'imediata')->firstOrFail();
        $this->assertNull(DB::table('pages')->where('id', $page->id)->value('published_at'));
        $this->assertSame(1, Page::query()->publishedInLocale('pt_BR')->count());
    }

    public function test_non_local_formats_are_rejected_in_all_resources(): void
    {
        foreach ($this->resources() as $name => [$create, $edit, $model, $table, $data]) {
            foreach (['2026-11-12T12:00:00.000000Z', '2026-11-12 12:00', '12/11/2026 12:00', '2026-02-30T10:00'] as $invalid) {
                Livewire::test($create)
                    ->fillForm($data('invalido-'.$name) + ['published_at' => $invalid])
                    ->call('create')->assertHasFormErrors(['published_at']);
            }
            $this->assertSame(0, $model::query()->count(), $name);
        }
    }

    private function resources(): array
    {
        return [
            'page' => [CreatePage::class, EditPage::class, Page::class, 'pages', fn (string $slug) => ['title' => 'Agenda', 'slug' => $slug, 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'published', 'sections' => []]],
            'service' => [CreateService::class, EditService::class, Service::class, 'services', fn (string $slug) => ['title' => 'Agenda', 'slug' => $slug, 'locale' => 'pt_BR', 'status' => 'published']],
            'product' => [CreateProduct::class, EditProduct::class, Product::class, 'products', fn (string $slug) => ['name' => 'Agenda', 'slug' => $slug, 'locale' => 'pt_BR', 'status' => 'published']],
            'project' => [CreateProject::class, EditProject::class, Project::class, 'projects', fn (string $slug) => ['title' => 'Agenda', 'slug' => $slug, 'locale' => 'pt_BR', 'status' => 'published']],
            'post' => [CreatePost::class, EditPost::class, Post::class, 'posts', fn (string $slug) => ['title' => 'Agenda', 'slug' => $slug, 'locale' => 'pt_BR', 'status' => 'published']],
        ];
    }
}
