<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Models\Page;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class PageResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--database' => 'sqlite', '--force' => true]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $user = new User;
        $user->role = UserRole::Admin;
        $this->actingAs($user);
    }

    public function test_create_and_edit_page(): void
    {
        Livewire::test(CreatePage::class)
            ->fillForm(['title' => 'Página', 'slug' => 'pagina', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'draft', 'sections' => [['type' => 'cta', 'data' => ['title' => ' Teste ', 'label' => 'Abrir', 'url' => '/contato']]]])
            ->call('create')->assertHasNoFormErrors();
        $page = Page::query()->where('slug', 'pagina')->firstOrFail();
        $this->assertSame('Teste', $page->sections[0]['data']['title']);
        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->fillForm(['title' => 'Atualizada', 'slug' => 'pagina', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'draft', 'sections' => []])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame('Atualizada', $page->fresh()->title);
    }

    public function test_metadata_limits_and_plain_text_are_validated(): void
    {
        Livewire::test(CreatePage::class)->fillForm(['title' => 'Página', 'slug' => 'pagina-meta', 'template' => 'standard', 'sections' => [], 'meta_title' => str_repeat('a', 71)])->call('create')->assertHasFormErrors(['meta_title']);
        Livewire::test(CreatePage::class)->fillForm(['title' => 'Página', 'slug' => 'pagina-meta', 'template' => 'standard', 'sections' => [], 'meta_description' => str_repeat('a', 201)])->call('create')->assertHasFormErrors(['meta_description']);
        Livewire::test(CreatePage::class)->fillForm(['title' => 'Página', 'slug' => 'pagina-meta', 'template' => 'standard', 'sections' => [], 'meta_title' => '<b>HTML</b>'])->call('create')->assertHasFormErrors(['meta_title']);
    }

    public function test_invalid_section_and_duplicate_slug_are_rejected(): void
    {
        $page = new Page;
        $page->title = 'Existing';
        $page->slug = 'existing';
        $page->locale = 'pt_BR';
        $page->template = 'standard';
        $page->save();
        Livewire::test(CreatePage::class)
            ->fillForm(['title' => 'Duplicate', 'slug' => 'existing', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'draft', 'sections' => []])
            ->call('create')->assertHasFormErrors(['slug']);
        $this->assertSame(1, Page::query()->count());

        $other = new Page;
        $other->title = 'Other';
        $other->slug = 'other';
        $other->locale = 'pt_BR';
        $other->template = 'standard';
        $other->save();
        Livewire::test(EditPage::class, ['record' => $other->getRouteKey()])
            ->fillForm(['title' => 'Changed', 'slug' => 'existing', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'draft', 'sections' => []])
            ->call('save')->assertHasFormErrors(['slug']);
        $this->assertSame('other', $other->fresh()->slug);

        $this->expectException(ValidationException::class);
        PageResource::validatedData(['title' => 'Test', 'slug' => 'test', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'draft', 'sections' => [['type' => 'testimonials', 'data' => ['title' => 'Bad']]]]);
    }

    public function test_showcase_only_accepts_published_service(): void
    {
        $service = new Service;
        $service->title = 'Service';
        $service->slug = 'service';
        $service->status = 'published';
        $service->save();
        $data = ['title' => 'Test', 'slug' => 'test', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'draft', 'sections' => [['type' => 'services_grid', 'data' => ['title' => 'Serviços', 'service_ids' => [(string) $service->id]]]]];
        $this->assertSame([$service->id], PageResource::validatedData($data)['sections'][0]['data']['service_ids']);
        $formData = $data;
        $formData['sections'][0]['data']['service_ids'] = [['id' => (string) $service->id]];
        Livewire::test(CreatePage::class)->fillForm($formData)->call('create')->assertHasNoFormErrors();
        $this->assertSame([$service->id], Page::query()->where('slug', 'test')->firstOrFail()->sections[0]['data']['service_ids']);
        $service->published_at = now()->addDay();
        $service->save();
        $data['slug'] = 'future';
        $this->expectException(ValidationException::class);
        PageResource::validatedData($data);
    }

    public function test_minimal_hero_and_raw_extra_fields_on_create_and_edit(): void
    {
        $base = ['title' => 'Hero', 'slug' => 'hero', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'draft'];
        Livewire::test(CreatePage::class)
            ->fillForm($base + ['sections' => [['type' => 'hero', 'data' => ['title' => 'Hero', 'text' => null, 'ctas' => []]]]])
            ->call('create')->assertHasNoFormErrors();
        $page = Page::query()->where('slug', 'hero')->firstOrFail();
        Livewire::test(EditPage::class, ['record' => $page->id])
            ->fillForm($base + ['sections' => [['type' => 'hero', 'data' => ['title' => 'Edit', 'text' => null, 'ctas' => null]]]])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame('Edit', $page->fresh()->sections[0]['data']['title']);

        $bad = ['block' => ['type' => 'hero', 'data' => ['title' => 'Bad', 'image' => 'unsafe']]];
        Livewire::test(CreatePage::class)->fillForm($base + ['slug' => 'bad'])
            ->set('data.sections', $bad)->call('create')->assertHasFormErrors(['sections']);
        Livewire::test(EditPage::class, ['record' => $page->id])->fillForm($base)
            ->set('data.sections', $bad)->call('save')->assertHasFormErrors(['sections']);
        $this->assertSame('Edit', $page->fresh()->sections[0]['data']['title']);
        $invalidUrl = [['type' => 'cta', 'data' => ['title' => 'CTA', 'label' => 'Abrir', 'url' => '/%2fexample.invalid']]];
        Livewire::test(CreatePage::class)->fillForm(array_replace($base, ['slug' => 'new', 'sections' => $invalidUrl]))
            ->call('create')->assertHasFormErrors(['sections']);
        Livewire::test(EditPage::class, ['record' => $page->id])->fillForm(array_replace($base, ['sections' => $invalidUrl]))
            ->call('save')->assertHasFormErrors(['sections']);
        $this->assertSame('Edit', $page->fresh()->sections[0]['data']['title']);
    }

    public function test_same_locale_references_are_accepted_and_persisted(): void
    {
        $service = $this->reference(Service::class, 'pt_BR', 'servico');
        $product = $this->reference(Product::class, 'pt_BR', 'produto');
        $project = $this->reference(Project::class, 'pt_BR', 'projeto');
        Livewire::test(CreatePage::class)
            ->fillForm(['title' => 'Refs', 'slug' => 'refs', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'draft', 'sections' => $this->referenceSections($service, $product, $project)])
            ->call('create')->assertHasNoFormErrors();
        $page = Page::query()->where('slug', 'refs')->firstOrFail();
        $this->assertSame([$service->id], $page->sections[0]['data']['service_ids']);
        $this->assertSame([$product->id], $page->sections[1]['data']['product_ids']);
        $this->assertSame([$project->id], $page->sections[2]['data']['project_ids']);
    }

    public function test_cross_locale_references_are_rejected_on_create(): void
    {
        foreach ([Service::class, Product::class, Project::class] as $model) {
            $foreign = $this->reference($model, 'en', 'foreign-'.class_basename($model));
            Livewire::test(CreatePage::class)
                ->fillForm([
                    'title' => 'Divergente', 'slug' => 'divergente-'.class_basename($model), 'locale' => 'pt_BR',
                    'template' => 'standard', 'status' => 'draft',
                    'sections' => [['type' => $this->referenceType($model), 'data' => ['title' => 'Lista', $this->referenceField($model) => [['id' => (string) $foreign->id]]]]],
                ])
                ->call('create')->assertHasFormErrors(['sections']);
        }
        $this->assertSame(0, Page::query()->count());

        try {
            PageResource::validatedData([
                'title' => 'Divergente', 'slug' => 'divergente', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'draft',
                'sections' => [['type' => 'services_grid', 'data' => ['title' => 'Lista', 'service_ids' => [$this->reference(Service::class, 'en', 'outro')->id]]]],
            ]);
            $this->fail('Referência de outro idioma deveria ser rejeitada.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('mesmo idioma', $exception->errors()['data.sections'][0]);
        }
    }

    public function test_locale_change_with_divergent_references_is_blocked_until_corrected(): void
    {
        $service = $this->reference(Service::class, 'pt_BR', 'servico');
        $page = new Page;
        $page->fill(['title' => 'Idioma', 'slug' => 'idioma', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'draft']);
        $page->sections = [['type' => 'services_grid', 'data' => ['title' => 'Lista', 'service_ids' => [$service->id]]]];
        $page->save();
        $base = ['title' => 'Idioma', 'slug' => 'idioma', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'draft'];
        $form = [['type' => 'services_grid', 'data' => ['title' => 'Lista', 'service_ids' => [['id' => (string) $service->id]]]]];

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->fillForm(array_replace($base, ['locale' => 'fr', 'sections' => $form]))
            ->call('save')->assertHasFormErrors(['sections']);
        $page->refresh();
        $this->assertSame('pt_BR', $page->locale);
        $this->assertSame([$service->id], $page->sections[0]['data']['service_ids']);

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->fillForm($base + ['sections' => $form])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame('pt_BR', $page->fresh()->locale);

        $translated = $this->reference(Service::class, 'fr', 'service-fr');
        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->fillForm(array_replace($base, ['locale' => 'fr', 'sections' => [['type' => 'services_grid', 'data' => ['title' => 'Liste', 'service_ids' => [['id' => (string) $translated->id]]]]]]))
            ->call('save')->assertHasNoFormErrors();
        $page = $page->fresh();
        $this->assertSame('fr', $page->locale);
        $this->assertSame([$translated->id], $page->sections[0]['data']['service_ids']);
    }

    public function test_existing_divergent_references_are_preserved_and_block_save(): void
    {
        $service = $this->reference(Service::class, 'en', 'legacy-service');
        $page = Page::withoutEvents(function () use ($service) {
            $page = new Page;
            $page->fill(['title' => 'Legado', 'slug' => 'legado', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'draft']);
            $page->sections = [['type' => 'services_grid', 'data' => ['title' => 'Lista', 'service_ids' => [$service->id]]]];
            $page->save();

            return $page;
        });

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->fillForm(['title' => 'Legado editado', 'slug' => 'legado', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'draft', 'sections' => [['type' => 'services_grid', 'data' => ['title' => 'Lista', 'service_ids' => [['id' => (string) $service->id]]]]]])
            ->call('save')->assertHasFormErrors(['sections']);
        $page->refresh();
        $this->assertSame('Legado', $page->title);
        $this->assertSame([$service->id], $page->sections[0]['data']['service_ids']);
        $this->assertSame(1, Service::query()->count());
    }

    public function test_unpublished_reference_in_same_locale_is_rejected(): void
    {
        $service = $this->reference(Service::class, 'pt_BR', 'rascunho');
        $service->published_at = now()->addDay();
        $service->save();
        Livewire::test(CreatePage::class)
            ->fillForm(['title' => 'Agendado', 'slug' => 'agendado', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'draft', 'sections' => [['type' => 'services_grid', 'data' => ['title' => 'Lista', 'service_ids' => [['id' => (string) $service->id]]]]]])
            ->call('create')->assertHasFormErrors(['sections']);
        $this->assertSame(0, Page::query()->count());
    }

    public function test_cross_locale_reference_shows_a_single_form_error_on_create(): void
    {
        $foreign = $this->reference(Service::class, 'en', 'unico-create');
        $component = Livewire::test(CreatePage::class)->fillForm([
            'title' => 'Único', 'slug' => 'unico-create', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'draft',
            'sections' => [['type' => 'services_grid', 'data' => ['title' => 'Lista', 'service_ids' => [['id' => (string) $foreign->id]]]]],
        ])->call('create');
        $errors = $component->errors();
        $this->assertCount(1, $errors->get('data.sections'));
        $this->assertSame([], $errors->get('sections'));
        $this->assertStringContainsString('mesmo idioma', $errors->get('data.sections')[0]);
        $this->assertSame(0, Page::query()->count());
    }

    public function test_locale_change_shows_a_single_form_error_on_edit(): void
    {
        $service = $this->reference(Service::class, 'pt_BR', 'unico-edit');
        $page = Page::create([
            'title' => 'Único', 'slug' => 'unico-edit', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'draft',
            'sections' => [['type' => 'services_grid', 'data' => ['title' => 'Lista', 'service_ids' => [$service->id]]]],
        ]);
        $component = Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])->fillForm([
            'title' => 'Único', 'slug' => 'unico-edit', 'locale' => 'en', 'template' => 'standard', 'status' => 'draft',
            'sections' => [['type' => 'services_grid', 'data' => ['title' => 'Lista', 'service_ids' => [['id' => (string) $service->id]]]]],
        ])->call('save');
        $errors = $component->errors();
        $this->assertCount(1, $errors->get('data.sections'));
        $this->assertSame([], $errors->get('sections'));
        $this->assertSame('pt_BR', $page->fresh()->locale);
    }

    public function test_model_guard_failure_maps_to_one_form_error(): void
    {
        $service = $this->reference(Service::class, 'pt_BR', 'corrida');
        $page = Page::create([
            'title' => 'Corrida', 'slug' => 'corrida', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'draft',
            'sections' => [['type' => 'services_grid', 'data' => ['title' => 'Lista', 'service_ids' => [$service->id]]]],
        ]);
        $service->status = 'draft';
        $service->save();

        try {
            $page->title = 'Corrida editada';
            $page->save();
            $this->fail('Referência despublicada deveria ser recusada na gravação.');
        } catch (ValidationException $exception) {
            $mapped = PageResource::asFormValidation($exception);
            $this->assertSame(['data.sections'], array_keys($mapped->errors()));
            $this->assertCount(1, $mapped->errors()['data.sections']);
            $this->assertStringContainsString('mesmo idioma', $mapped->errors()['data.sections'][0]);
        }
        $this->assertSame('Corrida', $page->fresh()->title);
    }

    private function reference(string $model, string $locale, string $slug): Service|Product|Project
    {
        $record = new $model;
        $record->fill($model === Product::class ? ['name' => $slug] : ['title' => $slug]);
        $record->slug = $slug;
        $record->locale = $locale;
        $record->status = 'published';
        $record->save();

        return $record;
    }

    private function referenceSections(Service $service, Product $product, Project $project): array
    {
        return [
            ['type' => 'services_grid', 'data' => ['title' => 'Serviços', 'service_ids' => [['id' => (string) $service->id]]]],
            ['type' => 'products_showcase', 'data' => ['title' => 'Produtos', 'product_ids' => [['id' => (string) $product->id]]]],
            ['type' => 'projects_showcase', 'data' => ['title' => 'Projetos', 'project_ids' => [['id' => (string) $project->id]]]],
        ];
    }

    private function referenceType(string $model): string
    {
        return match ($model) {
            Service::class => 'services_grid',
            Product::class => 'products_showcase',
            Project::class => 'projects_showcase',
        };
    }

    private function referenceField(string $model): string
    {
        return match ($model) {
            Service::class => 'service_ids',
            Product::class => 'product_ids',
            Project::class => 'project_ids',
        };
    }
}
