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
}
