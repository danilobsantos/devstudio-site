@extends('layouts.public')

@section('content')
<main id="main-content">
    <x-breadcrumbs :items="[['label' => 'Início', 'url' => url('/')], ['label' => $title, 'current' => true]]" />

    <article class="public-detail" style="max-width: 54rem; margin: 0 auto; padding-bottom: 3rem;">
        <header style="margin-bottom: 2rem;">
            <h1 style="font-size: clamp(2rem, 3.5vw, 2.75rem); line-height: 1.2; margin-bottom: 1rem;">{{ $title }}</h1>
            @if ($summary)
                <p style="font-size: 1.25rem; line-height: 1.6; color: var(--theme-text); opacity: 0.9; margin: 0;">{{ $summary }}</p>
            @endif
        </header>

        @if ($body)
            <div style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 2.5rem; white-space: pre-line;">{{ $body }}</div>
        @endif

        @if (! empty($features) && is_array($features))
            <section style="margin-top: 3rem; margin-bottom: 3rem;">
                <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem;">O que entregamos nesta solução</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 16rem), 1fr)); gap: 1.25rem;">
                    @foreach ($features as $feature)
                        <div class="ui-card">
                            <h3 style="font-size: 1.1rem; margin-top: 0; margin-bottom: 0.5rem; color: var(--color-brand-500);">
                                {{ $feature['title'] ?? '' }}
                            </h3>
                            @if (! empty($feature['text']))
                                <p style="font-size: 0.95rem; margin: 0; opacity: 0.85;">{{ $feature['text'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if (isset($type) && $type === 'service')
            <div style="margin-top: 3rem;">
                <x-cta-banner
                    title="Pronto para aplicar esta solução na sua empresa?"
                    text="Converse diretamente com nossos arquitetos de software e receba uma análise de viabilidade e escopo para sua operação."
                    :action-url="route('contact.show', ['servico' => $contactSlug ?? null])"
                    action-label="Solicitar diagnóstico técnico"
                />
            </div>

            <script type="application/ld+json">
            {
                "{{ '@context' }}": "https://schema.org",
                "@type": "Service",
                "name": "{{ $title }}",
                "description": "{{ $summary }}",
                "provider": {
                    "@type": "Organization",
                    "name": "DevStudio – Inovação Digital",
                    "url": "{{ url('/') }}"
                }
            }
            </script>
        @elseif (isset($type) && $type === 'product')
            <div style="margin-top: 3rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                @if (! empty($cta_url) && ! empty($cta_label))
                    <x-button variant="primary" :href="$cta_url">{{ $cta_label }}</x-button>
                @endif
                <x-button variant="secondary" :href="route('contact.show')">Falar com especialista</x-button>
            </div>
        @elseif (isset($type) && $type === 'post')
            <script type="application/ld+json">
            {
                "{{ '@context' }}": "https://schema.org",
                "@type": "Article",
                "headline": "{{ $title }}",
                "description": "{{ $summary }}",
                "author": {
                    "@type": "Organization",
                    "name": "DevStudio"
                },
                "publisher": {
                    "@type": "Organization",
                    "name": "DevStudio – Inovação Digital",
                    "logo": "{{ asset('images/logo_devstudio_dark_300x60.png') }}"
                }
            }
            </script>
        @endif
    </article>
</main>
@endsection
