<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InternationalizationPreparationTest extends TestCase
{
    private const TRANSLATION_KEY = 'c3ab8ff1-7b75-4f85-a7f8-9d7d1b2a4d61';

    private const OTHER_TRANSLATION_KEY = 'a4f08ff1-7b75-4f85-a7f8-9d7d1b2a4d61';

    private const CASE_TEST_KEY = 'd4f08ff1-7b75-4f85-a7f8-9d7d1b2a4d61';

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--database' => 'sqlite', '--force' => true]);
        DB::table('users')->insert(['id' => 1, 'name' => 'Synthetic', 'email' => 'i18n-test@example.invalid', 'password' => 'synthetic']);
    }

    public function test_all_editorial_resources_validate_locale_translation_key_and_per_locale_slug_uniqueness(): void
    {
        foreach ($this->resources() as $table => [$resource, $base]) {
            $this->insertRecord($table, $base, 'pt_BR');

            $canonicalized = $resource::validatedData(array_replace($base, ['slug' => "uppercase-key-{$table}", 'translation_key' => strtoupper(self::OTHER_TRANSLATION_KEY)]));
            $this->assertSame(self::OTHER_TRANSLATION_KEY, $canonicalized['translation_key'], $table);

            try {
                $resource::validatedData(array_replace($base, ['slug' => "same-key-{$table}", 'translation_key' => strtoupper(self::TRANSLATION_KEY)]));
                $this->fail("Uppercase translation key bypassed same-locale validation in {$table}.");
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('data.translation_key', $exception->errors(), $table);
            }

            $translated = array_replace($base, ['locale' => 'en', 'translation_key' => self::TRANSLATION_KEY]);
            $this->assertSame('en', $resource::validatedData($translated)['locale'], $table);
            $this->insertRecord($table, $translated, 'en');
            $this->assertSame(2, DB::table($table)->where('translation_key', self::TRANSLATION_KEY)->count(), $table);

            try {
                $resource::validatedData(array_replace($base, ['translation_key' => self::TRANSLATION_KEY]));
                $this->fail("Duplicate slug within pt_BR was accepted for {$table}.");
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('data.slug', $exception->errors(), $table);
            }

            try {
                $resource::validatedData(array_replace($base, ['slug' => "different-slug-{$table}", 'translation_key' => self::TRANSLATION_KEY]));
                $this->fail("Duplicate translation key within pt_BR was accepted for {$table}.");
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('data.translation_key', $exception->errors(), $table);
            }

            foreach ([
                ['locale' => 'pt-br', 'slug' => "invalid-locale-{$table}", 'error' => 'data.locale'],
                ['locale' => 'pt_BR', 'slug' => "invalid-key-{$table}", 'translation_key' => 'not-a-uuid', 'error' => 'data.translation_key'],
            ] as $invalid) {
                try {
                    $resource::validatedData(array_replace($base, $invalid));
                    $this->fail("Invalid i18n value was accepted for {$table}.");
                } catch (ValidationException $exception) {
                    $this->assertArrayHasKey($invalid['error'], $exception->errors(), $table);
                }
            }
        }
    }

    public function test_models_normalize_uppercase_uuid_before_persist_and_unique_constraint(): void
    {
        foreach ($this->modelClasses() as $table => [$modelClass, $titleField]) {
            $model = new $modelClass;
            $attributes = [
                $titleField => "Synthetic {$table}",
                'slug' => "uppercase-model-{$table}",
                'locale' => 'pt_BR',
                'translation_key' => strtoupper(self::CASE_TEST_KEY),
                'status' => 'draft',
            ];
            if ($table === 'pages') {
                $attributes += ['template' => 'standard', 'sections' => []];
            }
            if ($table === 'posts') {
                $attributes['author_id'] = 1;
            }
            $model->forceFill($attributes)->save();
            $this->assertSame(self::CASE_TEST_KEY, $model->fresh()->translation_key, $table);

            $this->insertRecord($table, array_replace($this->resources()[$table][1], ['slug' => "lower-key-{$table}"]), 'pt_BR');
            $duplicate = new $modelClass;
            $attributes['slug'] = "case-duplicate-{$table}";
            $attributes['translation_key'] = strtoupper(self::TRANSLATION_KEY);
            $duplicate->forceFill($attributes);

            try {
                $duplicate->save();
                $this->fail("Uppercase model key bypassed database uniqueness in {$table}.");
            } catch (QueryException) {
                $this->assertSame(self::TRANSLATION_KEY, $duplicate->translation_key, $table);
            }
        }
    }

    public function test_database_constraint_allows_nulls_other_locales_and_other_types_but_rejects_duplicate_group(): void
    {
        foreach ($this->resources() as $table => [, $base]) {
            $this->insertRecord($table, $base, 'pt_BR');
            $this->insertRecord($table, array_replace($base, ['slug' => "translated-{$table}"]), 'en');
            $this->insertRecord($table, array_replace($base, ['slug' => "null-one-{$table}"]), 'pt_BR', null);
            $this->insertRecord($table, array_replace($base, ['slug' => "null-two-{$table}"]), 'pt_BR', null);
            $this->assertSame(2, DB::table($table)->whereNull('translation_key')->count(), $table);

            try {
                $this->insertRecord($table, array_replace($base, ['slug' => "duplicate-key-{$table}"]), 'pt_BR');
                $this->fail("Database accepted a duplicate translation key in {$table}/pt_BR.");
            } catch (QueryException) {
                $this->assertSame(1, DB::table($table)->where('translation_key', self::TRANSLATION_KEY)->where('locale', 'pt_BR')->count(), $table);
            }
        }
        $this->assertSame(5, DB::table('pages')->where('translation_key', self::TRANSLATION_KEY)->where('locale', 'pt_BR')->count()
            + DB::table('services')->where('translation_key', self::TRANSLATION_KEY)->where('locale', 'pt_BR')->count()
            + DB::table('products')->where('translation_key', self::TRANSLATION_KEY)->where('locale', 'pt_BR')->count()
            + DB::table('projects')->where('translation_key', self::TRANSLATION_KEY)->where('locale', 'pt_BR')->count()
            + DB::table('posts')->where('translation_key', self::TRANSLATION_KEY)->where('locale', 'pt_BR')->count());
    }

    public function test_migration_precheck_aborts_before_index_changes_and_preserves_duplicate_rows(): void
    {
        foreach ($this->resources() as $table => [, $base]) {
            Schema::table($table, fn ($blueprint) => $blueprint->dropUnique("{$table}_translation_key_locale_unique"));
        }
        $post = $this->resources()['posts'][1];
        $this->insertRecord('posts', $post, 'pt_BR');
        $this->insertRecord('posts', array_replace($post, ['slug' => 'same-key-second-row']), 'pt_BR');
        $before = DB::table('posts')->where('translation_key', self::TRANSLATION_KEY)->orderBy('id')->get()->toArray();
        $migration = require database_path('migrations/2026_11_13_000000_enforce_translation_key_locale_uniqueness.php');

        try {
            $migration->up();
            $this->fail('Migration must abort on existing duplicate translation groups.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('posts', $exception->getMessage());
        }

        $this->assertEquals($before, DB::table('posts')->where('translation_key', self::TRANSLATION_KEY)->orderBy('id')->get()->toArray());
        foreach ($this->resources() as $table => $resource) {
            $this->assertFalse(Schema::hasIndex($table, "{$table}_translation_key_locale_unique"), $table);
        }
    }

    public function test_migration_index_can_be_rolled_back_and_reapplied_on_sqlite_memory(): void
    {
        $migration = require database_path('migrations/2026_11_13_000000_enforce_translation_key_locale_uniqueness.php');
        $migration->down();
        foreach ($this->resources() as $table => $resource) {
            $this->assertFalse(Schema::hasIndex($table, "{$table}_translation_key_locale_unique"), $table);
        }

        $migration->up();
        foreach ($this->resources() as $table => $resource) {
            $this->assertTrue(Schema::hasIndex($table, "{$table}_translation_key_locale_unique"), $table);
        }
    }

    public function test_migration_aborts_before_ddl_when_expected_index_name_has_wrong_definition(): void
    {
        $migration = require database_path('migrations/2026_11_13_000000_enforce_translation_key_locale_uniqueness.php');
        $migration->down();
        Schema::table('posts', fn (Blueprint $table) => $table->index('locale', 'posts_translation_key_locale_unique'));

        try {
            $migration->up();
            $this->fail('Migration must reject a non-unique index using its expected name.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('definição inesperada', $exception->getMessage());
        }

        $this->assertFalse(Schema::hasIndex('pages', 'pages_translation_key_locale_unique'));
        $this->assertFalse(Schema::hasIndex('services', 'services_translation_key_locale_unique'));
        $this->assertFalse(Schema::hasIndex('products', 'products_translation_key_locale_unique'));
        $this->assertFalse(Schema::hasIndex('projects', 'projects_translation_key_locale_unique'));
        $wrongIndex = collect(Schema::getIndexes('posts'))->firstWhere('name', 'posts_translation_key_locale_unique');
        $this->assertSame(['locale'], $wrongIndex['columns']);
        $this->assertFalse($wrongIndex['unique']);

        try {
            $migration->down();
            $this->fail('Rollback must not drop an unrelated index with the expected name.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('definição inesperada', $exception->getMessage());
        }
        $this->assertNotNull(collect(Schema::getIndexes('posts'))->firstWhere('name', 'posts_translation_key_locale_unique'));
    }

    private function resources(): array
    {
        return [
            'pages' => [PageResource::class, ['title' => 'Synthetic page', 'slug' => 'shared-slug', 'locale' => 'pt_BR', 'template' => 'standard', 'status' => 'draft', 'sections' => []]],
            'services' => [ServiceResource::class, ['title' => 'Synthetic service', 'slug' => 'shared-slug', 'locale' => 'pt_BR', 'status' => 'draft', 'sort_order' => 0, 'is_featured' => false, 'features' => []]],
            'products' => [ProductResource::class, ['name' => 'Synthetic product', 'slug' => 'shared-slug', 'locale' => 'pt_BR', 'status' => 'draft', 'sort_order' => 0, 'is_featured' => false, 'features' => [], 'audiences' => []]],
            'projects' => [ProjectResource::class, ['title' => 'Synthetic project', 'slug' => 'shared-slug', 'locale' => 'pt_BR', 'status' => 'draft', 'is_featured' => false, 'technologies' => []]],
            'posts' => [PostResource::class, ['title' => 'Synthetic post', 'slug' => 'shared-slug', 'locale' => 'pt_BR', 'status' => 'draft']],
        ];
    }

    private function modelClasses(): array
    {
        return [
            'pages' => [Page::class, 'title'],
            'services' => [Service::class, 'title'],
            'products' => [Product::class, 'name'],
            'projects' => [Project::class, 'title'],
            'posts' => [Post::class, 'title'],
        ];
    }

    private function insertRecord(string $table, array $data, string $locale, ?string $translationKey = self::TRANSLATION_KEY): void
    {
        $titleField = $table === 'products' ? 'name' : 'title';
        DB::table($table)->insert([
            $titleField => $data[$titleField],
            'slug' => $data['slug'],
            'locale' => $locale,
            'translation_key' => $translationKey,
            'status' => 'draft',
            ...($table === 'pages' ? ['template' => 'standard', 'sections' => '[]'] : []),
            ...($table === 'posts' ? ['author_id' => 1] : []),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
