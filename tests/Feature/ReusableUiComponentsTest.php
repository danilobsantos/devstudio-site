<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\View\ViewException;
use Tests\TestCase;

class ReusableUiComponentsTest extends TestCase
{
    public function test_button_variants_keep_action_and_navigation_semantics_and_disabled_state(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-button>Salvar</x-button>
            <x-button variant="secondary" disabled>Indisponível</x-button>
            <x-button variant="link" href="/destino">Abrir</x-button>
            BLADE);

        $this->assertMatchesRegularExpression('/<button class="ui-button ui-button--primary" type="button"\s*>Salvar<\/button>/', $html);
        $this->assertStringContainsString('ui-button--secondary" type="button" disabled', $html);
        $this->assertStringContainsString('<a class="ui-button ui-button--link" href="/destino">Abrir</a>', $html);

        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('.ui-button--primary:hover', $css);
        $this->assertStringContainsString('.ui-button:active', $css);
        $this->assertStringContainsString('.ui-button:disabled', $css);
        $this->assertStringContainsString('html[data-theme] :focus-visible', $css);
        $this->assertStringContainsString('padding: 0.75rem 1rem;', $css);
        $this->assertStringContainsString('border-radius: 0.25rem;', $css);
    }

    public function test_button_rejects_unknown_variant(): void
    {
        $this->expectException(ViewException::class);

        Blade::render('<x-button variant="unknown">Ação</x-button>');
    }

    public function test_card_badge_breadcrumb_and_cta_render_provided_content_and_urls(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-card><h2>Conteúdo fornecido</h2></x-card>
            <x-badge>Rótulo fornecido</x-badge>
            <x-breadcrumbs :items="[['label' => 'Anterior', 'url' => '/anterior'], ['label' => 'Atual', 'current' => true]]" />
            <x-cta-banner title="Título fornecido" text="Texto fornecido" action-url="/destino-existente" action-label="Ação fornecida" />
            BLADE);

        $this->assertStringContainsString('<article class="ui-card"><h2>Conteúdo fornecido</h2></article>', $html);
        $this->assertStringContainsString('<span class="ui-badge">Rótulo fornecido</span>', $html);
        $this->assertStringContainsString('<nav class="ui-breadcrumbs" aria-label="Trilha de navegação">', $html);
        $this->assertMatchesRegularExpression('/<span\s+aria-current="page"\s*>Atual<\/span>/', $html);
        $this->assertStringContainsString('href="/destino-existente"', $html);
        $this->assertStringContainsString('Ação fornecida', $html);
        $this->assertStringNotContainsString('/contato', $html);
    }

    public function test_public_layout_has_responsive_listing_and_reduced_motion_rules(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $listing = file_get_contents(resource_path('views/public/listing.blade.php'));

        $this->assertStringContainsString('@media (min-width: 48rem)', $css);
        $this->assertStringContainsString('@media (min-width: 64rem)', $css);
        $this->assertStringContainsString('grid-template-columns: repeat(2, minmax(0, 1fr));', $css);
        $this->assertStringContainsString('grid-template-columns: repeat(3, minmax(0, 1fr));', $css);
        $this->assertMatchesRegularExpression('/\.public-layout > main\s*\{[^}]*overflow-wrap: anywhere;/s', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $this->assertStringContainsString('transform: none;', $css);
        $this->assertStringContainsString('class="public-listing-grid"', $listing);
    }
}
