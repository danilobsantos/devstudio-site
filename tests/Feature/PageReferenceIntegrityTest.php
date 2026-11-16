<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PageReferenceIntegrityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--database' => 'sqlite', '--force' => true]);
    }

    public function test_direct_eloquent_saves_require_same_locale_references_for_all_types(): void
    {
        foreach ([[Service::class, 'services_grid', 'service_ids'], [Product::class, 'products_showcase', 'product_ids'], [Project::class, 'projects_showcase', 'project_ids']] as [$model, $type, $field]) {
            $local = $this->reference($model, 'pt_BR', 'local-'.class_basename($model));
            $page = $this->page('pt_BR', 'local-'.class_basename($model), [['type' => $type, 'data' => ['title' => 'Lista', $field => [$local->id]]]]);
            $page->save();
            $this->assertTrue($page->exists);

            $foreign = $this->reference($model, 'en', 'foreign-'.class_basename($model));
            $invalid = $this->page('pt_BR', 'foreign-'.class_basename($model), [['type' => $type, 'data' => ['title' => 'Lista', $field => [$foreign->id]]]]);
            try {
                $invalid->save();
                $this->fail('Referência de outro idioma deveria ser recusada na gravação direta.');
            } catch (ValidationException $exception) {
                $this->assertStringContainsString('mesmo idioma', $exception->validator->errors()->first('sections'));
                $this->assertFalse($invalid->exists);
            }
        }
        $this->assertSame(3, Page::query()->count());
    }

    public function test_direct_save_blocks_locale_change_with_existing_references(): void
    {
        $service = $this->reference(Service::class, 'pt_BR', 'servico');
        $page = $this->page('pt_BR', 'idioma', [['type' => 'services_grid', 'data' => ['title' => 'Lista', 'service_ids' => [$service->id]]]]);
        $page->save();

        $page->locale = 'en';
        try {
            $page->save();
            $this->fail('Troca de idioma com referência divergente deveria ser recusada.');
        } catch (ValidationException) {
            $this->assertSame('pt_BR', $page->fresh()->locale);
            $this->assertSame([$service->id], $page->fresh()->sections[0]['data']['service_ids']);
        }

        $translated = $this->reference(Service::class, 'en', 'service-en');
        $page->sections = [['type' => 'services_grid', 'data' => ['title' => 'List', 'service_ids' => [$translated->id]]]];
        $page->save();
        $page->refresh();
        $this->assertSame('en', $page->locale);
        $this->assertSame([$translated->id], $page->sections[0]['data']['service_ids']);
    }

    public function test_legacy_divergent_page_blocks_save_until_references_are_corrected(): void
    {
        $foreign = $this->reference(Service::class, 'en', 'legado');
        $page = Page::withoutEvents(fn () => Page::create([
            'title' => 'Legado', 'slug' => 'legado', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'draft',
            'sections' => [['type' => 'services_grid', 'data' => ['title' => 'Lista', 'service_ids' => [$foreign->id]]]],
        ]));
        $this->assertTrue($page->exists);

        $page->title = 'Editado';
        try {
            $page->save();
            $this->fail('Página com referência divergente deveria ser recusada mesmo sem tocar nas seções.');
        } catch (ValidationException) {
            $page->refresh();
            $this->assertSame('Legado', $page->title);
            $this->assertSame([$foreign->id], $page->sections[0]['data']['service_ids']);
        }

        $local = $this->reference(Service::class, 'pt_BR', 'corrigido');
        $page->title = 'Editado';
        $page->sections = [['type' => 'services_grid', 'data' => ['title' => 'Lista', 'service_ids' => [$local->id]]]];
        $page->save();
        $page->refresh();
        $this->assertSame('Editado', $page->title);
        $this->assertSame([$local->id], $page->sections[0]['data']['service_ids']);
    }

    public function test_direct_save_still_allows_sections_without_references(): void
    {
        $page = $this->page('pt_BR', 'sem-referencia', [['type' => 'hero']]);
        $page->save();
        $this->assertTrue($page->exists);
        $this->assertSame([['type' => 'hero']], $page->fresh()->sections);
    }

    public function test_direct_save_rejects_scheduled_reference_in_same_locale(): void
    {
        $service = $this->reference(Service::class, 'pt_BR', 'agendado');
        $service->published_at = now()->addDay();
        $service->save();
        $page = $this->page('pt_BR', 'agendado', [['type' => 'services_grid', 'data' => ['title' => 'Lista', 'service_ids' => [$service->id]]]]);
        $this->expectException(ValidationException::class);
        $page->save();
    }

    public function test_direct_save_requires_explicit_locale_for_references(): void
    {
        $service = $this->reference(Service::class, 'pt_BR', 'sem-locale');
        $page = new Page;
        $page->fill(['title' => 'Sem locale', 'slug' => 'sem-locale', 'template' => 'standard', 'status' => 'draft']);
        $page->sections = [['type' => 'services_grid', 'data' => ['title' => 'Lista', 'service_ids' => [$service->id]]]];
        $this->expectException(ValidationException::class);
        $page->save();
    }

    public function test_published_sections_keep_only_same_locale_eligible_references(): void
    {
        $service = $this->reference(Service::class, 'pt_BR', 'visivel');
        $draft = $this->reference(Service::class, 'pt_BR', 'rascunho-visivel');
        $draft->status = 'draft';
        $draft->save();
        $scheduled = $this->reference(Service::class, 'pt_BR', 'agendado-visivel');
        $scheduled->published_at = now()->addDay();
        $scheduled->save();
        $foreignService = $this->reference(Service::class, 'en', 'estrangeiro');
        $foreignProduct = $this->reference(Product::class, 'fr', 'produto-estrangeiro');
        $project = $this->reference(Project::class, 'pt_BR', 'projeto-visivel');

        $page = new Page;
        $page->locale = 'pt_BR';
        $page->sections = [
            ['type' => 'hero', 'data' => ['title' => 'Hero']],
            ['type' => 'services_grid', 'data' => ['title' => 'Serviços', 'service_ids' => [$draft->id, $foreignService->id, $service->id, $scheduled->id]]],
            ['type' => 'products_showcase', 'data' => ['title' => 'Produtos', 'product_ids' => [$foreignProduct->id]]],
            ['type' => 'projects_showcase', 'data' => ['title' => 'Projetos', 'project_ids' => [$project->id]]],
        ];
        $before = $page->sections;

        $visible = $page->publishedSections();
        $this->assertSame('hero', $visible[0]['type']);
        $this->assertSame([$service->id], $visible[1]['data']['service_ids']);
        $this->assertSame([$project->id], $visible[2]['data']['project_ids']);
        $this->assertCount(3, $visible);
        $this->assertSame($before, $page->sections);
    }

    public function test_published_sections_drop_references_without_valid_locale(): void
    {
        $service = $this->reference(Service::class, 'pt_BR', 'sem-locale-visivel');
        $page = new Page;
        $page->sections = [
            ['type' => 'hero', 'data' => ['title' => 'Hero']],
            ['type' => 'services_grid', 'data' => ['title' => 'Serviços', 'service_ids' => [$service->id]]],
        ];
        $this->assertSame([['type' => 'hero', 'data' => ['title' => 'Hero']]], $page->publishedSections());
    }

    public function test_published_sections_are_empty_without_sections(): void
    {
        $this->assertSame([], (new Page)->publishedSections());
    }

    private function page(string $locale, string $slug, array $sections): Page
    {
        $page = new Page;
        $page->fill(['title' => 'Página', 'slug' => $slug, 'locale' => $locale, 'template' => 'standard', 'status' => 'draft']);
        $page->sections = $sections;

        return $page;
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
}
