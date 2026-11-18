<?php

namespace Tests\Feature;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PublicContentRoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null, 'app.url' => 'http://devstudio.test']);
        DB::purge('sqlite');
        URL::forceRootUrl('http://devstudio.test');
        URL::forceScheme('http');
        Artisan::call('migrate', ['--database' => 'sqlite', '--force' => true]);
        DB::table('users')->insert(['id' => 1, 'name' => 'Synthetic', 'email' => 'public-test@example.invalid', 'password' => 'synthetic']);
    }

    public function test_listings_and_details_only_expose_published_pt_br_content(): void
    {
        $this->seedContent();
        DB::table('products')->where('slug', 'produto-agendado')->update(['meta_title' => 'Meta agendada secreta', 'meta_description' => 'Descrição agendada secreta']);

        foreach (['/produtos' => ['Produto visível', 'Produto agendado'], '/insights' => ['Artigo visível', 'Artigo agendado'], '/solucoes' => ['Serviço visível', 'Serviço agendado']] as $path => $visible) {
            $response = $this->get($path)->assertOk();
            $response->assertSee($visible[0])->assertDontSee($visible[1])->assertDontSee('Outro idioma')->assertDontSee('Meta agendada secreta')->assertDontSee('Descrição agendada secreta');
        }

        $this->get('/produtos/produto-visivel')->assertOk()->assertSee('Detalhe seguro');
        $this->get('/projetos')->assertOk()->assertSee('Nenhum conteúdo disponível no momento.')->assertDontSee('Projeto visível');
        $this->get('/projetos/projeto-visivel')->assertNotFound();
        $this->get('/insights/artigo-visivel')->assertOk()->assertSee('Corpo seguro');
        $this->get('/solucoes/servico-visivel')->assertOk()->assertSee('Descrição do serviço');
        $this->get('/sobre')->assertOk()->assertSee('Conteúdo visível')->assertDontSee('referência privada');
        $this->get('/paginas/sobre')->assertStatus(301)->assertRedirect('/sobre');

        foreach (['/produtos/produto-agendado', '/projetos/projeto-agendado', '/insights/artigo-agendado', '/solucoes/servico-agendado', '/produtos/ausente', '/projetos/ausente', '/insights/ausente', '/solucoes/ausente', '/paginas/ausente'] as $path) {
            $this->get($path)->assertNotFound();
        }
    }

    public function test_listing_renders_existing_public_cover_images_lazily(): void
    {
        $this->seedContent();
        Storage::fake('public');
        Storage::disk('public')->put('products/demo-cover.webp', 'image');
        DB::table('products')->where('slug', 'produto-visivel')->update(['cover_path' => 'products/demo-cover.webp']);

        $html = $this->get('/produtos')->assertOk()->getContent();

        $this->assertStringContainsString(Storage::disk('public')->url('products/demo-cover.webp'), $html);
        $this->assertStringContainsString('loading="lazy" decoding="async"', $html);
    }

    public function test_public_metadata_uses_explicit_values_then_editorial_fallbacks_without_site_suffix(): void
    {
        $this->seedContent();
        foreach ([
            ['pages', 'sobre', 'title', 'excerpt', '/sobre'],
            ['services', 'servico-visivel', 'title', 'excerpt', '/solucoes/servico-visivel'],
            ['products', 'produto-visivel', 'name', 'tagline', '/produtos/produto-visivel'],
            ['posts', 'artigo-visivel', 'title', 'excerpt', '/insights/artigo-visivel'],
        ] as [$table, $slug, $titleField, $summaryField, $path]) {
            DB::table($table)->where('slug', $slug)->update([$summaryField => 'Resumo editorial com acentuação.']);
            $html = $this->get($path)->assertOk()->getContent();
            $this->assertStringContainsString('<title>', $html);
            $this->assertStringContainsString('<meta name="description" content="Resumo editorial com acentuação.">', $html);
            $this->assertStringNotContainsString('| DevStudio</title>', $html);
        }

        DB::table('products')->where('slug', 'produto-visivel')->update([
            'meta_title' => 'Título explícito & especial',
            'meta_description' => 'Descrição explícita com ação & cuidado.',
        ]);
        $html = $this->get('/produtos/produto-visivel')->assertOk()->getContent();
        $this->assertStringContainsString('<title>Título explícito &amp; especial</title>', $html);
        $this->assertStringContainsString('content="Descrição explícita com ação &amp; cuidado."', $html);
        $this->assertStringNotContainsString('name="description" content="Resumo editorial', $html);

        DB::table('products')->where('slug', 'produto-visivel')->update(['meta_title' => '   ', 'meta_description' => " \t ", 'tagline' => 'Resumo útil']);
        $html = $this->get('/produtos/produto-visivel')->assertOk()->getContent();
        $this->assertStringContainsString('<title>Produto visível</title>', $html);
        $this->assertStringContainsString('name="description" content="Resumo útil"', $html);

        DB::table('products')->where('slug', 'produto-visivel')->update(['meta_title' => null, 'meta_description' => null, 'tagline' => null]);
        $html = $this->get('/produtos/produto-visivel')->assertOk()->getContent();
        $this->assertStringContainsString('<title>Produto visível</title>', $html);
        $this->assertStringNotContainsString('name="description"', $html);

        DB::table('products')->where('slug', 'produto-visivel')->update([
            'meta_title' => str_repeat('á', 71),
            'meta_description' => str_repeat('á', 201),
        ]);
        $html = $this->get('/produtos/produto-visivel')->assertOk()->getContent();
        $this->assertStringNotContainsString('<title>', $html);
        $this->assertStringNotContainsString('name="description"', $html);
    }

    public function test_public_content_uses_shared_system_theme_layout(): void
    {
        $this->seedContent();

        foreach ([
            '/solucoes' => ['Soluções', true], '/solucoes/servico-visivel' => ['Soluções', false],
            '/produtos' => ['Produtos', true], '/produtos/produto-visivel' => ['Produtos', false],
            '/projetos' => ['Projetos', true],
            '/insights' => ['Insights', true], '/insights/artigo-visivel' => ['Insights', false], '/sobre' => ['Sobre', true],
            '/contato' => ['Contato', true],
        ] as $path => [$currentLabel, $isCurrentPage]) {
            $html = $this->get($path)->assertOk()->getContent();
            $this->assertStringContainsString('<html lang="pt-BR" data-theme="system">', $html);
            $this->assertStringContainsString('<meta name="color-scheme" content="light dark">', $html);
            $this->assertStringContainsString('data-theme-toggle hidden aria-label="Ativar tema escuro"', $html);
            $this->assertStringContainsString('/build/assets/app-', $html);
            $this->assertStringContainsString('<link rel="stylesheet"', $html);
            $this->assertStringContainsString('<script type="module"', $html);
            $this->assertStringContainsString('aria-label="Navegação principal"', $html);
            $this->assertStringContainsString('aria-label="Navegação do rodapé"', $html);
            $this->assertStringContainsString('alt="DevStudio - início"', $html);
            $this->assertStringContainsString(asset('images/logo_devstudio_dark_300x60.png'), $html);
            $this->assertStringContainsString(asset('images/logo_devstudio_light_300x60.png'), $html);
            $this->assertStringContainsString('href="'.route('services.index').'"', $html);
            $this->assertStringContainsString('href="'.route('products.index').'"', $html);
            $this->assertStringContainsString('href="'.route('projects.index').'"', $html);
            $this->assertStringContainsString('href="'.route('pages.about').'"', $html);
            $this->assertStringContainsString('href="'.route('posts.index').'"', $html);
            $this->assertStringContainsString('href="'.route('contact.show').'"', $html);
            $this->assertStringContainsString('>DevStudio</span>', $html);
            $this->assertStringContainsString('href="#main-content"', $html);
            $this->assertStringContainsString('<main id="main-content">', $html);
            if ($isCurrentPage) {
                $this->assertMatchesRegularExpression('/aria-current="page"\s*>'.preg_quote($currentLabel, '/').'<\/a>/', $html);
            } else {
                $this->assertDoesNotMatchRegularExpression('/aria-current="page"\s*>'.preg_quote($currentLabel, '/').'<\/a>/', $html);
            }
        }

        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString(":root[data-theme='light']", $css);
        $this->assertStringContainsString(":root[data-theme='dark']", $css);
        $this->assertStringContainsString('@media (prefers-color-scheme: dark)', $css);
        $this->assertStringContainsString('html[data-theme=\'system\'] .site-logo--dark', $css);
        $this->assertStringContainsString('@media (max-width: 40rem)', $css);
        $this->assertStringContainsString('html[data-theme] .skip-link:focus', $css);
        $this->assertStringContainsString('aria-current="page"', file_get_contents(resource_path('views/components/public-navigation.blade.php')));
    }

    public function test_home_route_uses_only_an_eligible_cms_page(): void
    {
        $this->travelTo('2026-11-12 15:00:00');
        $this->get('/')->assertNotFound();
        $this->get('/paginas/home')->assertNotFound();

        $id = DB::table('pages')->insertGetId([
            'title' => 'Página Home CMS', 'slug' => 'home', 'template' => 'standard', 'sections' => '[]',
            'status' => 'draft', 'locale' => 'pt_BR', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->get('/')->assertNotFound();
        $this->get('/paginas/home')->assertNotFound();

        DB::table('pages')->where('id', $id)->update(['status' => 'published', 'published_at' => '2026-11-12 15:01:00']);
        $this->get('/')->assertNotFound();
        $this->get('/paginas/home')->assertNotFound();

        DB::table('pages')->where('id', $id)->update(['published_at' => null, 'locale' => 'en']);
        $this->get('/')->assertNotFound();
        $this->get('/paginas/home')->assertNotFound();

        DB::table('pages')->where('id', $id)->update(['locale' => 'pt_BR']);
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('<html lang="pt-BR" data-theme="system">', $html);
        $this->assertStringContainsString('<h1>Página Home CMS</h1>', $html);
        $this->assertStringNotContainsString('Trilha de navegação', $html);
        $this->assertStringNotContainsString('Nenhum conteúdo disponível', $html);
        $response = $this->get('/paginas/home?utm_source=home&utm_medium%5B%5D=array&utm_campaign=launch&utm_term=term&utm_content='.str_repeat('x', 257).'&gclid=a%2Bb&fbclid=click&locale=en&next=https%3A%2F%2Fevil.example');
        $response->assertMovedPermanently();
        $this->assertSame('http://devstudio.test/?utm_source=home&utm_campaign=launch&utm_term=term&gclid=a%2Bb&fbclid=click', $response->headers->get('Location'));

        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('html[data-theme] body', $css);
        $this->assertStringContainsString('html[data-theme] a', $css);
        $this->assertStringContainsString('html[data-theme] a:hover', $css);
        $this->assertStringContainsString('html[data-theme] :focus-visible', $css);
    }

    public function test_home_hides_project_showcase_and_renders_other_allowed_escaped_eligible_sections(): void
    {
        $this->seedContent();
        $home = [
            ['type' => 'hero', 'data' => ['title' => 'Hero permitido', 'text' => 'Texto hero', 'ctas' => [['label' => 'Sobre', 'url' => '/sobre']]]],
            ['type' => 'services_grid', 'data' => ['title' => 'Soluções permitidas', 'service_ids' => [DB::table('services')->where('slug', 'servico-visivel')->value('id'), DB::table('services')->where('slug', 'servico-agendado')->value('id')]]],
            ['type' => 'products_showcase', 'data' => ['title' => 'Produtos permitidos', 'product_ids' => [DB::table('products')->where('slug', 'produto-visivel')->value('id'), DB::table('products')->where('slug', 'produto-agendado')->value('id'), DB::table('products')->where('slug', 'outro-idioma')->value('id')]]],
            ['type' => 'projects_showcase', 'data' => ['title' => 'Projetos permitidos', 'project_ids' => [DB::table('projects')->where('slug', 'projeto-visivel')->value('id'), DB::table('projects')->where('slug', 'projeto-agendado')->value('id')]]],
            ['type' => 'text_image', 'data' => ['title' => 'Texto permitido', 'text' => 'Conteúdo texto']],
            ['type' => 'features_list', 'data' => ['title' => 'Recursos permitidos', 'items' => [['title' => 'Recurso']]]],
            ['type' => 'faq', 'data' => ['title' => 'FAQ permitido', 'items' => [['question' => 'Pergunta?', 'answer' => 'Resposta.']]]],
            ['type' => 'cta', 'data' => ['title' => 'CTA permitido', 'text' => 'Texto CTA', 'label' => 'Sobre', 'url' => '/sobre']],
            ['type' => 'testimonials', 'data' => ['title' => 'Tipo legado proibido']],
            ['type' => 'hero', 'data' => ['title' => '<script>alert(1)</script>']],
            ['legacy' => 'Seção antiga inválida'],
        ];
        DB::table('pages')->insert([
            'title' => '<script>alert(2)</script>', 'slug' => 'home', 'template' => 'standard', 'sections' => json_encode($home),
            'status' => 'published', 'locale' => 'pt_BR', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<h1>&lt;script&gt;alert(2)&lt;/script&gt;</h1>', $html);
        $this->assertStringContainsString('class="page-article page-home"', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        foreach (['Hero permitido', 'Soluções permitidas', 'Produtos permitidos', 'Texto permitido', 'Recursos permitidos', 'FAQ permitido', 'CTA permitido', 'Serviço visível', 'Produto visível', 'http://devstudio.test/sobre'] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }
        foreach (['Projetos permitidos', 'Projeto visível', 'Serviço agendado', 'Produto agendado', 'Outro idioma', 'Projeto agendado', 'Tipo legado proibido', 'Seção antiga inválida'] as $unexpected) {
            $this->assertStringNotContainsString($unexpected, $html);
        }
        $this->assertStringNotContainsString('Trilha de navegação', $html);
        $this->assertStringNotContainsString('Nenhum conteúdo disponível', $html);
        $this->get('/sobre')->assertOk()->assertSee('Conteúdo visível');
        $this->get('/solucoes')->assertOk()->assertSee('Serviço visível');
    }

    public function test_sitemap_contains_only_public_listing_and_eligible_content_urls(): void
    {
        $this->seedContent();
        $static = $this->streamed($this->get('/sitemap/static.xml'));
        $this->assertStringNotContainsString('<loc>http://devstudio.test/</loc>', $static);

        DB::table('pages')->insert([
            'title' => 'Home sitemap', 'slug' => 'home', 'template' => 'standard', 'sections' => '[]',
            'status' => 'published', 'locale' => 'pt_BR', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $index = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $this->assertStringContainsString('/sitemap/static.xml', $this->streamed($index));
        $this->assertStringContainsString('/sitemap/products/1.xml', $this->streamed($this->get('/sitemap.xml')));
        $this->assertStringContainsString('/sitemap/services/1.xml', $this->streamed($this->get('/sitemap.xml')));
        $this->assertStringNotContainsString('/sitemap/projects/', $this->streamed($this->get('/sitemap.xml')));
        $this->get('/sitemap/projects/1.xml')->assertNotFound();
        $static = $this->streamed($this->get('/sitemap/static.xml'));
        $this->assertStringContainsString('http://devstudio.test/', $static);
        $this->assertStringContainsString('http://devstudio.test/solucoes', $static);
        $this->assertStringContainsString('http://devstudio.test/contato', $static);
        $this->assertStringNotContainsString('http://devstudio.test/projetos', $static);
        $this->assertStringNotContainsString('/sitemap/pages/1.xml', $this->streamed($this->get('/sitemap.xml')));
        $products = $this->streamed($this->get('/sitemap/products/1.xml'));
        $this->assertStringContainsString('http://devstudio.test/produtos/produto-visivel', $products);
        $this->assertStringNotContainsString('agendado', $products);
        $this->assertStringNotContainsString('outro-idioma', $products);
        $this->assertStringContainsString('http://devstudio.test/solucoes/servico-visivel', $this->streamed($this->get('/sitemap/services/1.xml')));
    }

    public function test_robots_temporarily_disallows_crawling_without_changing_route_access(): void
    {
        $this->assertSame('/robots.txt', route('robots', [], false));

        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSeeText('User-agent: *')
            ->assertSeeText('Disallow: /');

        $this->get('/produtos')->assertOk();
        $this->get('/produtos/nao-existe')->assertNotFound();
        $this->get('/admin')->assertRedirect();
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function test_empty_listing_is_neutral_and_slug_is_404(): void
    {
        foreach (['/produtos' => 'Produtos', '/projetos' => 'Projetos', '/insights' => 'Insights', '/solucoes' => 'Soluções'] as $path => $heading) {
            $this->get($path)->assertOk()->assertSee('<main id="main-content">', false)->assertSee($heading)->assertSee('Nenhum conteúdo disponível no momento.');
        }

        $this->get('/')->assertNotFound();
        $this->get('/contato')->assertNotFound();
    }

    public function test_public_listings_use_twelve_item_pages(): void
    {
        $this->travelTo('2026-11-12 15:00:00');
        foreach (range(1, 13) as $number) {
            DB::table('products')->insert(['name' => "Produto $number", 'slug' => "produto-$number", 'status' => 'published', 'locale' => 'pt_BR', 'created_at' => now(), 'updated_at' => now()]);
        }

        $this->get('/produtos')->assertOk()->assertSee('Produto 1')->assertDontSee('Produto 13')->assertSee('2');
        $this->get('/produtos?page=2')->assertOk()->assertSee('Produto 13');
    }

    public function test_page_templates_escape_links_filter_references_and_preserve_saved_sections(): void
    {
        $this->seedContent();
        $product = DB::table('products')->where('slug', 'produto-visivel')->value('id');
        $hiddenProduct = DB::table('products')->where('slug', 'outro-idioma')->value('id');
        $scheduledProduct = DB::table('products')->where('slug', 'produto-agendado')->value('id');
        $project = DB::table('projects')->where('slug', 'projeto-visivel')->value('id');
        $service = DB::table('services')->where('slug', 'servico-visivel')->value('id');
        DB::table('pages')->insert(['title' => '<script>alert(1)</script>', 'slug' => 'pagina-publica', 'template' => 'standard', 'status' => 'published', 'locale' => 'pt_BR', 'created_at' => now(), 'updated_at' => now()]);
        $sections = [
            ['type' => 'hero', 'data' => ['title' => 'Hero seguro', 'text' => 'Introdução', 'ctas' => [['label' => 'Contato', 'url' => '/contato']]]],
            ['type' => 'cta', 'data' => ['title' => 'CTA seguro', 'text' => 'Converse', 'label' => 'Fale conosco', 'url' => 'https://example.com/contato?a=1&b=2']],
            ['type' => 'cta', 'data' => ['title' => 'CTA inválido', 'label' => 'Inseguro', 'url' => 'javascript:alert(1)']],
            ['type' => 'services_grid', 'data' => ['title' => 'Serviços', 'service_ids' => [$service, DB::table('services')->where('slug', 'servico-agendado')->value('id')]]],
            ['type' => 'products_showcase', 'data' => ['title' => 'Produtos', 'product_ids' => [$product, $scheduledProduct, $hiddenProduct]]],
            ['type' => 'projects_showcase', 'data' => ['title' => 'Projetos', 'project_ids' => [$project]]],
        ];
        DB::table('pages')->where('slug', 'pagina-publica')->update(['sections' => json_encode($sections)]);
        $saved = DB::table('pages')->where('slug', 'pagina-publica')->value('sections');

        $html = $this->get('/paginas/pagina-publica')->assertOk()->getContent();
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('Hero seguro', $html);
        $this->assertStringContainsString('href="/contato"', $html);
        $this->assertStringContainsString('href="https://example.com/contato?a=1&amp;b=2"', $html);
        $this->assertSame(1, substr_count($html, '<h2>CTA seguro</h2>'));
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('Serviço visível', $html);
        $this->assertStringContainsString('Produto visível', $html);
        $this->assertStringNotContainsString('Projeto visível', $html);
        $this->assertStringNotContainsString('Serviço agendado', $html);
        $this->assertStringNotContainsString('Produto agendado', $html);
        $this->assertStringNotContainsString('Outro idioma', $html);
        $this->assertSame($saved, DB::table('pages')->where('slug', 'pagina-publica')->value('sections'));
        $this->assertStringContainsString('http://devstudio.test/paginas/pagina-publica', $this->streamed($this->get('/sitemap/pages/1.xml')));
    }

    public function test_legacy_page_with_malformed_section_is_skipped_without_server_error(): void
    {
        $this->travelTo('2026-11-12 15:00:00');
        DB::table('pages')->insert([
            'title' => 'Página legado', 'slug' => 'legado', 'template' => 'standard', 'sections' => json_encode([['type' => ['invalid'], 'data' => []]]),
            'status' => 'published', 'locale' => 'pt_BR', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->get('/paginas/legado')->assertOk()->assertSee('Página legado')->assertDontSee('invalid');
    }

    public function test_legacy_page_sections_are_capped_to_the_editorial_limit(): void
    {
        $this->seedContent();
        $serviceId = DB::table('services')->where('slug', 'servico-visivel')->value('id');
        $sections = [];
        foreach (range(1, 21) as $number) {
            $sections[] = ['type' => 'services_grid', 'data' => ['title' => "Grade $number", 'service_ids' => [$serviceId]]];
        }
        DB::table('pages')->insert([
            'title' => 'Página com legado excedente', 'slug' => 'legado-excedente', 'template' => 'standard', 'sections' => json_encode($sections),
            'status' => 'published', 'locale' => 'pt_BR', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $serviceQueries = 0;
        DB::listen(function (QueryExecuted $query) use (&$serviceQueries) {
            if (str_contains($query->sql, 'services')) {
                $serviceQueries++;
            }
        });

        $html = $this->get('/paginas/legado-excedente')->assertOk()->getContent();

        $this->assertStringContainsString('Grade 20', $html);
        $this->assertStringNotContainsString('Grade 21', $html);
        $this->assertSame(20, substr_count($html, '/solucoes/servico-visivel'));
        $this->assertLessThanOrEqual(60, $serviceQueries);
        $this->assertCount(21, json_decode(DB::table('pages')->where('slug', 'legado-excedente')->value('sections'), true));
    }

    public function test_about_canonical_redirect_preserves_only_safe_tracking_parameters(): void
    {
        $response = $this->get('/paginas/sobre?utm_source=initial&utm_source=qa%20campaign&gclid=a%2Bb&fbclid=click&locale=en&next=https%3A%2F%2Fevil.example&utm_medium%5B%5D=invalid');

        $response->assertStatus(301);
        $this->assertSame('http://devstudio.test/sobre?utm_source=qa%20campaign&gclid=a%2Bb&fbclid=click', $response->headers->get('Location'));
        $this->assertStringNotContainsString('locale=', $response->headers->get('Location'));
        $this->assertStringNotContainsString('evil.example', $response->headers->get('Location'));
    }

    private function seedContent(): void
    {
        $this->travelTo('2026-11-12 15:00:00');
        foreach ([
            ['products', 'name', 'Produto visível', 'produto-visivel', 'published', null],
            ['products', 'name', 'Produto agendado', 'produto-agendado', 'published', '2026-11-12 15:01:00'],
            ['products', 'name', 'Outro idioma', 'outro-idioma', 'published', null, 'en'],
            ['projects', 'title', 'Projeto visível', 'projeto-visivel', 'published', null],
            ['projects', 'title', 'Projeto agendado', 'projeto-agendado', 'published', '2026-11-12 15:01:00'],
            ['posts', 'title', 'Artigo visível', 'artigo-visivel', 'published', null],
            ['posts', 'title', 'Artigo agendado', 'artigo-agendado', 'published', '2026-11-12 15:01:00'],
            ['services', 'title', 'Serviço visível', 'servico-visivel', 'published', null],
            ['services', 'title', 'Serviço agendado', 'servico-agendado', 'published', '2026-11-12 15:01:00'],
            ['pages', 'title', 'Página visível', 'sobre', 'published', null],
        ] as $case) {
            [$table, $titleField, $title, $slug, $status, $publishedAt] = $case;
            $locale = $case[6] ?? 'pt_BR';
            $data = [$titleField => $title, 'slug' => $slug, 'status' => $status, 'published_at' => $publishedAt, 'locale' => $locale, 'created_at' => now(), 'updated_at' => now()];
            if ($table === 'posts') {
                $data['author_id'] = 1;
            }
            if ($table === 'products' && $slug === 'produto-visivel') {
                $data['description'] = 'Detalhe seguro';
            }
            if ($table === 'posts' && $slug === 'artigo-visivel') {
                $data['body'] = 'Corpo seguro';
            }
            if ($table === 'services' && $slug === 'servico-visivel') {
                $data['body'] = 'Descrição do serviço';
            }
            if ($table === 'pages' && $slug === 'sobre') {
                $data['template'] = 'standard';
                $data['sections'] = json_encode([['type' => 'text_image', 'data' => ['title' => 'Seção', 'text' => 'Conteúdo visível']]]);
            }
            DB::table($table)->insert($data);
        }
    }

    private function streamed($response): string
    {
        ob_start();
        $response->baseResponse->sendContent();

        return ob_get_clean();
    }
}
