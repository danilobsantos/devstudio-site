<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublishedScopeTest extends TestCase
{
    public function test_publication_scope_respects_status_time_locale_and_unpublishing(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--database' => 'sqlite', '--force' => true]);
        $at = now()->startOfSecond();
        $this->travelTo($at);
        DB::table('users')->insert(['id' => 1, 'name' => 'Synthetic', 'email' => 'test@example.invalid', 'password' => 'synthetic']);

        foreach ([Page::class, Service::class, Product::class, Project::class, Post::class] as $model) {
            $table = (new $model)->getTable();
            $base = ['slug' => 'synthetic', 'locale' => 'pt_BR'];
            $base[$model === Product::class ? 'name' : 'title'] = 'Synthetic';
            if ($model === Page::class) {
                $base['template'] = 'default';
            }
            if ($model === Post::class) {
                $base['author_id'] = 1;
            }
            $cases = [
                ['immediate', 'published', null, 'pt_BR'],
                ['past', 'published', now()->subSecond()->toDateTimeString(), 'pt_BR'],
                ['current', 'published', now()->toDateTimeString(), 'pt_BR'],
                ['future', 'published', now()->addSecond()->toDateTimeString(), 'pt_BR'],
                ['draft', 'draft', null, 'pt_BR'],
                ['other-locale', 'published', now()->subSecond()->toDateTimeString(), 'en'],
                ['draft', 'published', null, 'en'],
                ['immediate', 'published', null, 'en'],
            ];
            foreach ($cases as [$slug, $status, $date, $locale]) {
                DB::table($table)->insert(array_merge($base, ['slug' => $slug, 'status' => $status, 'published_at' => $date, 'locale' => $locale]));
            }
            $this->assertSame(['current', 'immediate', 'past'], $model::query()->where('locale', 'pt_BR')->published()->orderBy('slug')->pluck('slug')->all(), $model);
            $this->assertSame(['current', 'immediate', 'past'], $model::query()->publishedInLocale('pt_BR')->orderBy('slug')->pluck('slug')->all(), $model);
            $this->assertSame('en', $model::query()->publishedInLocale('en')->where('slug', 'draft')->firstOrFail()->locale);
            $this->assertSame('pt_BR', $model::query()->publishedInLocale('pt_BR')->where('slug', 'immediate')->firstOrFail()->locale);
            foreach (['draft', 'future', 'other-locale', 'missing'] as $slug) {
                try {
                    $model::query()->publishedInLocale('pt_BR')->where('slug', $slug)->firstOrFail();
                    $this->fail('Conteúdo não elegível foi encontrado: '.$slug);
                } catch (ModelNotFoundException) {
                    $this->assertTrue(true);
                }
            }
            try {
                $model::query()->publishedInLocale('invalid');
                $this->fail('Locale inválido foi aceito.');
            } catch (\InvalidArgumentException) {
                $this->assertTrue(true);
            }
            $this->assertNull($model::query()->published()->where('slug', 'future')->first());
            DB::table($table)->where('slug', 'past')->update(['status' => 'draft']);
            $this->assertNull($model::query()->published()->where('slug', 'past')->first());
            $this->travel(2)->seconds();
            $this->assertNotNull($model::query()->published()->where('slug', 'future')->first());
            $this->travelTo($at);
        }
    }
}
