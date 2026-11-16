<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Lead;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use App\Providers\Filament\AdminPanelProvider;
use Filament\Panel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class EditorialPolicyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--database' => 'sqlite', '--force' => true]);
    }

    public function test_admin_and_editor_can_manage_all_editorial_content_and_categories(): void
    {
        foreach ([Page::class, Service::class, Product::class, Project::class, Post::class, Category::class] as $model) {
            foreach ([UserRole::Admin, UserRole::Editor] as $role) {
                $gate = Gate::forUser($this->user($role));
                $this->assertTrue($gate->allows('viewAny', $model));
                $this->assertTrue($gate->allows('create', $model));
                foreach (['view', 'update', 'delete'] as $ability) {
                    $this->assertTrue($gate->allows($ability, new $model));
                }
                $this->assertFalse($gate->allows('restore', $model));
            }
        }
        $guest = Gate::forUser($this->user(null));
        foreach ([Page::class, Service::class, Product::class, Project::class, Post::class, Category::class] as $model) {
            $this->assertFalse($guest->allows('viewAny', $model));
            $this->assertFalse($guest->allows('create', $model));
            $this->assertFalse($guest->allows('view', new $model));
            $this->assertFalse($guest->allows('update', new $model));
            $this->assertFalse($guest->allows('delete', new $model));
        }
        $this->assertFalse(Gate::allows('viewAny', Page::class));
    }

    public function test_leads_and_site_settings_are_admin_only_and_restricted_actions_stay_denied(): void
    {
        foreach ([UserRole::Admin, UserRole::Editor, null] as $role) {
            $gate = Gate::forUser($this->user($role));
            $admin = $role === UserRole::Admin;
            $this->assertSame($admin, $gate->allows('viewAny', Lead::class));
            $this->assertSame($admin, $gate->allows('view', new Lead));
            $this->assertSame($admin, $gate->allows('update', new Lead));
            $this->assertFalse($gate->allows('create', Lead::class));
            $this->assertFalse($gate->allows('delete', new Lead));
            $this->assertSame($admin, $gate->allows('viewAny', SiteSetting::class));
            $this->assertSame($admin, $gate->allows('create', SiteSetting::class));
            $this->assertFalse($gate->allows('delete', new SiteSetting));
            $this->assertFalse($gate->allows('viewAny', User::class));
        }
        $setting = SiteSetting::create(['display_name' => 'Synthetic']);
        $admin = Gate::forUser($this->user(UserRole::Admin));
        $editor = Gate::forUser($this->user(UserRole::Editor));
        $this->assertFalse($admin->allows('create', SiteSetting::class));
        $this->assertTrue($admin->allows('view', $setting));
        $this->assertTrue($admin->allows('update', $setting));
        $this->assertFalse($editor->allows('view', $setting));
        $this->assertFalse($editor->allows('update', $setting));
    }

    public function test_role_revocation_takes_effect_after_user_refresh(): void
    {
        $user = $this->user(UserRole::Editor);
        $user->fill(['name' => 'Synthetic', 'email' => 'editor@example.invalid', 'password' => 'synthetic']);
        $user->save();
        $this->assertTrue(Gate::forUser($user)->allows('update', new Page));

        DB::table('users')->where('id', $user->id)->update(['role' => null]);
        $user->refresh();

        $this->assertNull($user->role);
        $this->assertFalse(Gate::forUser($user)->allows('update', new Page));
    }

    public function test_unrecognized_persisted_role_fails_closed_without_enum_cast_errors(): void
    {
        $user = $this->user(UserRole::Admin);
        $user->fill(['name' => 'Synthetic', 'email' => 'invalid-role@example.invalid', 'password' => 'synthetic']);
        $user->save();
        DB::table('users')->where('id', $user->id)->update(['role' => 'owner']);
        $user->refresh();
        $gate = Gate::forUser($user);

        $this->assertFalse($gate->allows('viewAny', Page::class));
        $this->assertFalse($gate->allows('viewAny', Lead::class));
        $this->assertFalse($gate->allows('viewAny', SiteSetting::class));
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_panel_has_no_user_resource_or_commercial_widgets(): void
    {
        $panel = (new AdminPanelProvider(app()))->panel(Panel::make());

        foreach ($panel->getResources() as $resource) {
            $this->assertNotSame(User::class, $resource::getModel());
        }
        $this->assertSame([], $panel->getWidgets());
    }

    private function user(?UserRole $role): User
    {
        $user = new User;
        $user->role = $role;

        return $user;
    }
}
