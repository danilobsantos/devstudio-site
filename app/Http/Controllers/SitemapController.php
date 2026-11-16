<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Service;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SitemapController extends Controller
{
    private const CHUNK_SIZE = 10000;

    private const CONTENT = [
        'pages' => [Page::class, 'pages.show'],
        'services' => [Service::class, 'services.show'],
        'products' => [Product::class, 'products.show'],
        'posts' => [Post::class, 'posts.show'],
    ];

    public function __invoke(): StreamedResponse
    {
        $entries = [route('sitemap.static')];
        foreach (self::CONTENT as $key => [$model]) {
            $query = $model::query()->publishedInLocale('pt_BR');
            if ($key === 'pages') {
                $query->whereNotIn('slug', ['sobre', 'home']);
            }
            $count = $query->count();
            for ($page = 1; $page <= (int) ceil($count / self::CHUNK_SIZE); $page++) {
                $entries[] = route('sitemap.content', ['type' => $key, 'page' => $page]);
            }
        }

        return response()->stream(function () use ($entries) {
            echo '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
            foreach ($entries as $entry) {
                echo '<sitemap><loc>'.e($entry).'</loc></sitemap>';
            }
            echo '</sitemapindex>';
        }, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function static(): StreamedResponse
    {
        $urls = [route('services.index'), route('products.index'), route('posts.index'), route('contact.show')];
        if (Page::query()->publishedInLocale('pt_BR')->where('slug', 'home')->exists()) {
            array_unshift($urls, route('home'));
        }
        if (Page::query()->publishedInLocale('pt_BR')->where('slug', 'sobre')->exists()) {
            $urls[] = route('pages.about');
        }

        return $this->urlset($urls);
    }

    public function content(string $type, int $page): StreamedResponse
    {
        abort_unless(isset(self::CONTENT[$type]) && $page > 0, 404);
        [$model, $route] = self::CONTENT[$type];
        $query = $model::query()->publishedInLocale('pt_BR');
        if ($type === 'pages') {
            $query->whereNotIn('slug', ['sobre', 'home']);
        }
        $urls = $query->orderBy('id')->offset(($page - 1) * self::CHUNK_SIZE)
            ->limit(self::CHUNK_SIZE)->get(['id', 'slug'])->map(fn ($item) => route($route, ['slug' => $item->slug]));

        return $this->urlset($urls);
    }

    private function urlset(iterable $urls): StreamedResponse
    {
        return response()->stream(function () use ($urls) {
            echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
            foreach ($urls as $url) {
                echo '<url><loc>'.e($url).'</loc></url>';
            }
            echo '</urlset>';
        }, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
