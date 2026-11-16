<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Providers\Filament\AdminPanelProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Panel;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    public function test_guest_is_redirected_to_login_and_can_view_it(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('/admin/login')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_only_admin_can_access_panel_in_local_environment(): void
    {
        $this->app['env'] = 'local';

        $admin = new User;
        $admin->name = 'Admin sintético';
        $admin->role = UserRole::Admin;
        $this->actingAs($admin)->get('/admin')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $editor = new User;
        $editor->name = 'Editor sintético';
        $editor->role = UserRole::Editor;
        $this->actingAs($editor)->get('/admin')->assertForbidden()->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $unassigned = new User;
        $unassigned->name = 'Usuário sintético';
        $this->actingAs($unassigned)->get('/admin')->assertForbidden()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_admin_access_is_denied_outside_local_environment(): void
    {
        $admin = new User;
        $admin->name = 'Admin sintético';
        $admin->role = UserRole::Admin;

        foreach (['testing', 'preview', 'production'] as $environment) {
            $this->app['env'] = $environment;
            $this->actingAs($admin)->get('/admin')->assertForbidden()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        }
    }

    public function test_panel_keeps_csrf_and_session_authentication_middleware(): void
    {
        $panel = (new AdminPanelProvider(app()))->panel(Panel::make());

        $this->assertContains(VerifyCsrfToken::class, $panel->getMiddleware());
        $this->assertContains(AuthenticateSession::class, $panel->getMiddleware());
        $this->assertContains(Authenticate::class, $panel->getAuthMiddleware());
    }

    public function test_csrf_middleware_rejects_invalid_token_in_a_real_token_check(): void
    {
        $middleware = new class(app(), app('encrypter')) extends VerifyCsrfToken
        {
            // Laravel skips token comparison in tests; force the middleware's real check.
            protected function runningUnitTests(): bool
            {
                return false;
            }
        };
        $session = app('session')->driver('array');
        $session->start();
        $request = Request::create('/admin/action', 'POST', ['_token' => 'invalid-token']);
        $request->setLaravelSession($session);

        $this->expectException(TokenMismatchException::class);
        $middleware->handle($request, fn () => response('unexpected'));
    }
}
