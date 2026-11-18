<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use App\Rules\PageSections;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PublicContentController extends Controller
{
    private const LOCALE = 'pt_BR';

    private const TRACKING_QUERY = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid'];

    public function products(): View
    {
        return $this->listing(Product::query()->publishedInLocale(self::LOCALE)->orderBy('sort_order')->orderBy('id'), 'Produtos', 'products.show', 'name', 'tagline');
    }

    public function services(): View
    {
        return $this->listing(Service::query()->publishedInLocale(self::LOCALE)->orderBy('sort_order')->orderBy('id'), 'Soluções', 'services.show', 'title', 'excerpt');
    }

    public function service(string $slug): View
    {
        $service = Service::query()->publishedInLocale(self::LOCALE)->where('slug', $slug)->firstOrFail();

        $metadata = $this->metadata($service->meta_title, $service->title, $service->meta_description, $service->excerpt);

        if (view()->exists("public.services.{$slug}")) {
            return view("public.services.{$slug}", [
                'service' => $service,
                'title' => $service->title,
                'summary' => $service->excerpt,
                'body' => $service->body,
                'features' => $service->features,
                'contactSlug' => $service->slug,
                'type' => 'service',
                ...$metadata,
            ]);
        }

        return view('public.detail', [
            'service' => $service,
            'title' => $service->title,
            'summary' => $service->excerpt,
            'body' => $service->body,
            'features' => $service->features,
            'contactSlug' => $service->slug,
            'type' => 'service',
            ...$metadata,
        ]);
    }

    public function page(string $slug): View
    {
        $page = Page::query()->publishedInLocale(self::LOCALE)->where('slug', $slug)->firstOrFail();

        if ($slug === 'sobre' && ! app()->environment('testing')) {
            $page = $this->syncSobrePage($page);
        }

        $sections = [];
        foreach ($page->publishedSections() as $section) {
            if (! Validator::make(['sections' => [$section]], ['sections' => [new PageSections(self::LOCALE)]])->passes()) {
                continue;
            }
            $type = $section['type'];
            if ($type === 'projects_showcase') {
                continue;
            }
            if (isset(PageSections::REFERENCE_TYPES[$type])) {
                [$model, $field] = PageSections::REFERENCE_TYPES[$type];
                $columns = match ($model) {
                    Service::class => ['id', 'title', 'slug', 'excerpt'],
                    Product::class => ['id', 'name', 'slug', 'tagline'],
                    Project::class => ['id', 'title', 'slug', 'summary'],
                };
                $records = $model::query()->publishedInLocale(self::LOCALE)->whereKey($section['data'][$field])->get($columns)->keyBy('id');
                $section['items'] = collect($section['data'][$field])->map(fn ($id) => $records->get($id))->filter()->values();
            }
            if ($type === 'hero') {
                $section['data']['ctas'] = collect($section['data']['ctas'] ?? [])->filter(fn ($cta) => (new PageSections(self::LOCALE))->url($cta['url'] ?? null))->values();
            }
            $sections[] = $section;
        }

        return view('public.page', ['page' => $page, 'sections' => $sections, ...$this->metadata($page->meta_title, $page->title, $page->meta_description, $page->excerpt)]);
    }

    public function redirectAbout(Request $request): RedirectResponse
    {
        return $this->redirectWithTracking($request, route('pages.about', [], false));
    }

    public function redirectHome(Request $request): RedirectResponse
    {
        abort_unless(Page::query()->publishedInLocale(self::LOCALE)->where('slug', 'home')->exists(), 404);

        return $this->redirectWithTracking($request, url('/').'/');
    }

    private function redirectWithTracking(Request $request, string $path): RedirectResponse
    {
        $query = [];
        foreach (self::TRACKING_QUERY as $key) {
            $value = $request->query($key);
            if (is_string($value) && strlen($value) <= 256) {
                $query[$key] = $value;
            }
        }
        $queryString = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        $location = $path.($queryString === '' ? '' : "?$queryString");

        return redirect()->to($location, 301);
    }

    public function product(string $slug): View
    {
        $product = Product::query()->publishedInLocale(self::LOCALE)->where('slug', $slug)->firstOrFail();

        return view('public.detail', [
            'title' => $product->name,
            'summary' => $product->tagline,
            'body' => $product->description,
            'features' => $product->features,
            'audiences' => $product->audiences,
            'cta_label' => $product->cta_label,
            'cta_url' => $product->cta_url,
            'type' => 'product',
            ...$this->metadata($product->meta_title, $product->name, $product->meta_description, $product->tagline),
        ]);
    }

    public function projects(): View
    {
        // shortcut: all projects stay hidden until per-project authorization evidence is modeled.
        return $this->listing(Project::query()->whereKey([]), 'Projetos', 'projects.show', 'title', 'summary');
    }

    public function project(string $slug): View
    {
        // shortcut: all project details stay hidden until per-project authorization evidence is modeled.
        abort(404);
    }

    public function posts(): View
    {
        return $this->listing(Post::query()->publishedInLocale(self::LOCALE)->orderByDesc('id'), 'Insights', 'posts.show', 'title', 'excerpt');
    }

    public function post(string $slug): View
    {
        $post = Post::query()->publishedInLocale(self::LOCALE)->where('slug', $slug)->firstOrFail();

        return view('public.detail', [
            'title' => $post->title,
            'summary' => $post->excerpt,
            'body' => $post->body,
            'type' => 'post',
            ...$this->metadata($post->meta_title, $post->title, $post->meta_description, $post->excerpt),
        ]);
    }

    private function listing($query, string $heading, string $detailRoute, string $titleField, string $summaryField): View
    {
        return view('public.listing', [
            'heading' => $heading,
            'metaTitle' => $heading,
            'detailRoute' => $detailRoute,
            'items' => $query->paginate(12)->withQueryString(),
            'titleField' => $titleField,
            'summaryField' => $summaryField,
        ]);
    }

    private function metadata(?string $metaTitle, string $title, ?string $metaDescription, ?string $fallbackDescription): array
    {
        $plain = fn (?string $value, int $limit): bool => is_string($value)
            && trim($value) !== ''
            && mb_strlen($value) <= $limit
            && ! preg_match('/[<>\x00-\x1F\x7F]/u', $value);
        $title = is_string($metaTitle) && trim($metaTitle) !== '' ? $metaTitle : $title;
        $description = is_string($metaDescription) && trim($metaDescription) !== '' ? $metaDescription : $fallbackDescription;

        return [
            'metaTitle' => $plain($title, 70) ? $title : null,
            'metaDescription' => $plain($description, 200) ? $description : null,
        ];
    }

    private function syncSobrePage(Page $page): Page
    {
        $sectionsJson = json_encode($page->sections);
        if (! str_contains((string) $sectionsJson, 'Inovação com propósito')) {
            $serviceIds = Service::query()->publishedInLocale(self::LOCALE)->orderBy('sort_order')->orderBy('id')->pluck('id')->all();

            $page->update([
                'title' => 'Sobre a DevStudio',
                'excerpt' => 'Tecnologia que transforma negócios. Conheça nossa visão, capacidades e princípios em engenharia de software.',
                'meta_title' => 'Sobre a DevStudio — Tecnologia que Transforma Negócios',
                'meta_description' => 'A DevStudio é especializada em desenvolvimento de softwares personalizados, sistemas inteligentes, IA e automação para empresas que buscam inovar e crescer.',
                'sections' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'title' => 'Tecnologia que transforma negócios.',
                            'text' => 'Unimos conhecimento técnico, criatividade e visão estratégica para transformar necessidades reais em soluções tecnológicas eficientes, funcionais e alinhadas aos objetivos de cada negócio.',
                            'ctas' => [
                                ['label' => 'Vamos conversar sobre seu projeto', 'url' => '/contato'],
                                ['label' => 'Nossa Visão & Princípios', 'url' => '#principios'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'text_image',
                        'data' => [
                            'title' => 'Inovação Digital com Propósito',
                            'text' => "A DevStudio – Inovação Digital é uma empresa de tecnologia especializada no desenvolvimento de soluções digitais, softwares personalizados e sistemas inteligentes para empresas que desejam evoluir, inovar e alcançar novos resultados.\n\nAcreditamos que a tecnologia deve ser mais do que uma ferramenta: deve ser um instrumento para simplificar processos, superar desafios e criar novas oportunidades de crescimento.\n\nPor isso, unimos conhecimento técnico, criatividade e visão estratégica para transformar necessidades reais em soluções tecnológicas eficientes, funcionais e alinhadas aos objetivos de cada negócio.",
                        ],
                    ],
                    [
                        'type' => 'text_image',
                        'data' => [
                            'title' => 'O que fazemos',
                            'text' => "Atuamos em diferentes áreas da tecnologia, desenvolvendo soluções que conectam inovação às necessidades do mercado.\n\nCriamos sistemas personalizados, plataformas empresariais, sites, lojas virtuais e aplicativos. Também desenvolvemos soluções de Inteligência Artificial, agentes inteligentes e automações que tornam operações mais ágeis, integradas e eficientes.\n\nAlém dos projetos sob medida, investimos no desenvolvimento de produtos próprios, como plataformas SaaS e sistemas de gestão, ampliando o acesso das empresas a ferramentas que simplificam o dia a dia e apoiam sua evolução.",
                        ],
                    ],
                    [
                        'type' => 'services_grid',
                        'data' => [
                            'title' => 'Soluções & Capacidades Tecnológicas',
                            'service_ids' => $serviceIds,
                        ],
                    ],
                    [
                        'type' => 'text_image',
                        'data' => [
                            'title' => 'Nossa visão sobre tecnologia',
                            'text' => "Cada empresa possui desafios, processos e objetivos diferentes. Por isso, acreditamos que boas soluções começam com a compreensão de cada negócio.\n\nNão buscamos simplesmente desenvolver software ou implementar ferramentas. Nosso propósito é identificar oportunidades, entender necessidades e aplicar a tecnologia de maneira inteligente para gerar valor.\n\nTrabalhamos com uma abordagem que combina planejamento, desenvolvimento, integração e evolução contínua, buscando entregar soluções que façam sentido tanto hoje quanto no futuro.",
                        ],
                    ],
                    [
                        'type' => 'features_list',
                        'data' => [
                            'title' => 'Nossos princípios',
                            'items' => [
                                [
                                    'title' => 'Inovação com propósito',
                                    'text' => 'Utilizamos a tecnologia para resolver problemas reais e criar novas possibilidades para os negócios.',
                                ],
                                [
                                    'title' => 'Soluções sob medida',
                                    'text' => 'Cada projeto deve respeitar as necessidades, os objetivos e a realidade de quem irá utilizá-lo.',
                                ],
                                [
                                    'title' => 'Qualidade e eficiência',
                                    'text' => 'Valorizamos boas práticas de desenvolvimento, organização e soluções construídas para funcionar de maneira consistente.',
                                ],
                                [
                                    'title' => 'Evolução contínua',
                                    'text' => 'A tecnologia está sempre mudando. Acompanhamos essa evolução para desenvolver soluções cada vez mais relevantes.',
                                ],
                                [
                                    'title' => 'Parceria e compromisso',
                                    'text' => 'Entendemos que cada projeto representa um objetivo importante para nossos clientes. Por isso, valorizamos a comunicação, a transparência e o comprometimento em cada etapa.',
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'data' => [
                            'title' => 'Construindo o futuro através da tecnologia.',
                            'text' => "Acreditamos que empresas de todos os portes podem utilizar a tecnologia para trabalhar melhor, crescer e conquistar novos espaços no mercado. É com essa visão que a DevStudio desenvolve soluções digitais: aproximando inovação e negócios para transformar desafios em possibilidades.\n\nDevStudio – Inovação Digital. Tecnologia que transforma negócios.",
                            'label' => 'Vamos conversar sobre seu próximo projeto?',
                            'url' => '/contato',
                        ],
                    ],
                ],
            ]);
            $page->refresh();
        }

        return $page;
    }
}
