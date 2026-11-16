<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\SiteSettings\Pages\CreateSiteSetting;
use App\Filament\Resources\SiteSettings\Pages\EditSiteSetting;
use App\Filament\Resources\SiteSettings\Pages\ListSiteSettings;
use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class SiteSettingResourceTest extends TestCase
{
    public function test_consecutive_edits_refresh_timestamp_and_still_reject_tampering(): void
    {
        $setting = SiteSetting::create(['display_name' => 'Original']);
        $setting->logo_light_background = 'existing.png';
        $setting->save();
        $edit = Livewire::test(EditSiteSetting::class, ['record' => $setting->id]);
        $this->travel(5)->seconds();
        $edit->set('data.display_name', 'First')->call('save')->assertHasNoFormErrors();
        $this->assertSame($setting->fresh()->attributesToArray()['updated_at'], $edit->get('data.updated_at'));
        $this->travel(5)->seconds();
        $edit->set('data.display_name', 'Second')->call('save')->assertHasNoFormErrors();
        $this->assertSame('Second', $setting->fresh()->display_name);
        $edit->set('data.logo_light_background', 'unsafe.png')->call('save')->assertHasFormErrors(['display_name']);
        $this->assertSame('existing.png', $setting->fresh()->logo_light_background);
        $edit->set('data.logo_light_background', 'existing.png')->set('data.updated_at', 'forged')->call('save')->assertHasFormErrors(['display_name']);
        $this->assertSame('Second', $setting->fresh()->display_name);
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--database' => 'sqlite', '--force' => true]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $user = new User;
        $user->name = 'Synthetic';
        $user->email = 'setting@example.invalid';
        $user->password = 'synthetic';
        $user->role = UserRole::Admin;
        $user->save();
        $this->actingAs($user);
    }

    public function test_admin_create_edit_list_and_preserve_unedited_data(): void
    {
        Livewire::test(CreateSiteSetting::class)->fillForm(['display_name' => ' Test ', 'email' => 'test@example.invalid'])->call('create')->assertHasNoFormErrors();
        $setting = SiteSetting::query()->firstOrFail();
        $this->assertSame('Test', $setting->display_name);
        $setting->logo_light_background = 'existing.png';
        $setting->social_links = ['existing' => 'https://example.invalid'];
        $setting->save();
        Livewire::test(EditSiteSetting::class, ['record' => $setting->id])->fillForm(['display_name' => 'Changed', 'whatsapp' => '5511999999999'])->call('save')->assertHasNoFormErrors();
        $this->assertSame('existing.png', $setting->fresh()->logo_light_background);
        $this->assertSame(['existing' => 'https://example.invalid'], $setting->fresh()->social_links);
        Livewire::test(ListSiteSettings::class)->assertCanSeeTableRecords([$setting]);
        $this->assertFalse(Gate::forUser(auth()->user())->allows('delete', $setting));
        Livewire::test(CreateSiteSetting::class)->assertForbidden();
    }

    public function test_invalid_values_and_access_are_denied(): void
    {
        Livewire::test(CreateSiteSetting::class)->fillForm(['display_name' => '<script>'])->call('create')->assertHasFormErrors(['display_name']);
        Livewire::test(CreateSiteSetting::class)->fillForm(['canonical_url' => '/%2fexample.invalid'])->call('create')->assertHasFormErrors(['canonical_url']);
        Livewire::test(CreateSiteSetting::class)->set('data.logo_light_background', 'unsafe')->call('create')->assertHasFormErrors(['display_name']);
        $this->assertSame(0, SiteSetting::query()->count());
        $component = Livewire::test(CreateSiteSetting::class)->fillForm(['display_name' => 'Denied']);
        auth()->user()->role = UserRole::Editor;
        auth()->user()->save();
        $component->call('create')->assertForbidden();
        Livewire::test(ListSiteSettings::class)->assertForbidden();
        auth()->logout();
        Livewire::test(ListSiteSettings::class)->assertForbidden();
    }

    public function test_links_and_singleton_database_guard(): void
    {
        Livewire::test(CreateSiteSetting::class)->fillForm(['display_name' => 'Test', 'social_links' => ['Rede' => 'https://example.invalid'], 'institutional_links' => ['Contato' => '/contato']])->call('create')->assertHasNoFormErrors();
        $setting = SiteSetting::query()->firstOrFail();
        $this->assertSame(['Rede' => 'https://example.invalid'], $setting->social_links);
        Livewire::test(EditSiteSetting::class, ['record' => $setting->id])->set('data.social_links', ['Rede' => '/%2fexample.invalid'])->call('save')->assertHasFormErrors(['social_links']);
        $this->assertSame(['Rede' => 'https://example.invalid'], $setting->fresh()->social_links);
        try {
            DB::table('site_settings')->insert(['display_name' => 'Duplicate']);
            $this->fail('Singleton must reject duplicate inserts.');
        } catch (QueryException) {
            $this->assertSame(1, SiteSetting::query()->count());
        }
    }

    public function test_duplicate_precheck_preserves_existing_rows(): void
    {
        config(['database.connections.guard_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true]]);
        Schema::connection('guard_test')->create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('display_name');
        });
        DB::connection('guard_test')->table('site_settings')->insert([['display_name' => 'One'], ['display_name' => 'Two']]);
        DB::setDefaultConnection('guard_test');
        try {
            $migration = require base_path('database/migrations/2026_11_12_000001_enforce_single_site_setting.php');
            $migration->up();
            $this->fail('Duplicate rows must prevent the migration.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Mais de uma', $exception->getMessage());
            $this->assertSame(2, DB::table('site_settings')->count());
            $this->assertFalse(Schema::hasColumn('site_settings', 'singleton_guard'));
        } finally {
            DB::setDefaultConnection('sqlite');
        }
    }

    public function test_invalid_link_state_cannot_be_saved_without_correction(): void
    {
        $create = Livewire::test(CreateSiteSetting::class)->fillForm(['display_name' => 'Test']);
        $create->set('data.social_links', 'invalid')->assertHasFormErrors(['social_links']);
        $create->call('create')->assertHasFormErrors(['social_links']);
        $this->assertSame(0, SiteSetting::query()->count());
        $create->set('data.social_links', ['Rede' => 'https://example.invalid'])->call('create')->assertHasNoFormErrors();
        $setting = SiteSetting::query()->firstOrFail();
        $edit = Livewire::test(EditSiteSetting::class, ['record' => $setting->id])->set('data.display_name', 'Changed');
        $edit->set('data.institutional_links', 'invalid')->call('save')->assertHasFormErrors(['institutional_links']);
        $this->assertSame('Test', $setting->fresh()->display_name);
        $edit->set('data.institutional_links', ['Contato' => '/contato'])->call('save')->assertHasNoFormErrors();
        $this->assertSame(['Contato' => '/contato'], $setting->fresh()->institutional_links);
    }

    public function test_links_reject_unsafe_values_and_keys(): void
    {
        foreach ([['Bad' => 'javascript:alert(1)'], ['Bad' => 'https://user@example.invalid/'], ['Bad' => '/%2fexample.invalid'], ['<script>' => '/contato'], ['Bad' => ['url' => '/contato']]] as $links) {
            $this->assertFalse(SiteSettingResource::validLinks($links));
        }
        $this->assertTrue(SiteSettingResource::validLinks(['Contato' => '/contato', 'Rede' => 'https://example.invalid/']));
    }

    public function test_invalid_link_marker_is_locked(): void
    {
        $component = Livewire::test(CreateSiteSetting::class)->set('data.social_links', 'invalid');
        $this->expectException(CannotUpdateLockedPropertyException::class);
        $component->set('rejectedLinks', []);
    }
}
