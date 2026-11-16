<!doctype html>
<html lang="pt-BR" data-theme="system">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if (! empty($metaTitle))<title>{{ $metaTitle }}</title>@endif
    @if (! empty($metaDescription))<meta name="description" content="{{ $metaDescription }}">@endif

    {{-- Typography Preconnect (Sora & Inter) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Canonical & Open Graph --}}
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $metaTitle ?? 'DevStudio – Inovação Digital' }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Tecnologia que transforma negócios. Softwares, sistemas empresariais e soluções digitais.' }}">
    <meta property="og:image" content="{{ asset('images/logo_devstudio_dark_300x60.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle ?? 'DevStudio – Inovação Digital' }}">
    <meta name="twitter:description" content="{{ $metaDescription ?? 'Tecnologia que transforma negócios. Softwares, sistemas empresariais e soluções digitais.' }}">
    <meta name="twitter:image" content="{{ asset('images/logo_devstudio_dark_300x60.png') }}">

    {{-- Schema.org Organization --}}
    <script type="application/ld+json">
    {
        "{{ '@context' }}": "https://schema.org",
        "@type": "Organization",
        "name": "DevStudio – Inovação Digital",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('images/logo_devstudio_dark_300x60.png') }}",
        "description": "Desenvolvemos softwares, sistemas empresariais e soluções digitais inteligentes para empresas que desejam evoluir, automatizar processos e crescer por meio da tecnologia."
    }
    </script>

    <style>
        :root[data-theme='system'],
        :root[data-theme='light'] {
            color-scheme: light;
            --theme-background: #F6F6F7;
            --theme-text: #17171B;
            --theme-link: #D10047;
            --theme-focus: #D10047;
            --color-border: #E5E7EB;
            --card-background: #FFFFFF;
        }

        :root[data-theme='dark'] {
            color-scheme: dark;
            --theme-background: #17191E;
            --theme-text: #F6F6F7;
            --theme-link: #F6F6F7;
            --theme-focus: #D10047;
            --color-border: rgba(255, 255, 255, 0.1);
            --card-background: #1C1F26;
        }

        @media (prefers-color-scheme: dark) {
            :root[data-theme='system'] {
                color-scheme: dark;
                --theme-background: #17191E;
                --theme-text: #F6F6F7;
                --theme-link: #F6F6F7;
                --theme-focus: #D10047;
                --color-border: rgba(255, 255, 255, 0.1);
                --card-background: #1C1F26;
            }
        }

        html[data-theme] body {
            margin: 0;
            background-color: var(--theme-background);
            color: var(--theme-text);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Sora', system-ui, -apple-system, sans-serif;
            letter-spacing: -0.02em;
            font-weight: 700;
            color: var(--theme-text);
        }

        .site-header {
            box-sizing: border-box;
            position: sticky;
            top: 0;
            z-index: 50;
            background: color-mix(in srgb, var(--theme-background) 88%, transparent);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--color-border);
            padding: 0.85rem clamp(1rem, 4vw, 4rem);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
        }

        .site-brand {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
        }

        .site-logo {
            display: block;
            width: 175px;
            height: auto;
        }

        .site-navigation ul {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem 1.75rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .site-navigation a {
            display: inline-block;
            padding-block: 0.35rem;
            font-size: 0.9375rem;
            font-weight: 500;
            text-decoration: none;
            color: var(--theme-text);
            opacity: 0.85;
            transition: opacity 0.2s, color 0.2s;
        }

        .site-navigation a:hover {
            opacity: 1;
            color: #D10047;
        }

        .site-navigation a[aria-current='page'] {
            font-weight: 600;
            opacity: 1;
            color: #D10047;
            text-decoration-thickness: 0.12em;
        }

        .site-header-cta {
            padding: 0.6rem 1.25rem !important;
            font-size: 0.9375rem !important;
            border-radius: 9999px !important;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .page-article {
            width: 100%;
        }

        .page-home > h1 {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            padding: 0 !important;
            margin: -1px !important;
            overflow: hidden !important;
            clip: rect(0, 0, 0, 0) !important;
            white-space: nowrap !important;
            border: 0 !important;
        }

        .section-block {
            margin-block: clamp(3rem, 6vw, 5rem);
        }

        .section-header {
            margin-bottom: 2rem;
            max-width: 48rem;
        }

        .section-kicker {
            display: inline-block;
            font-family: 'Sora', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #D10047;
            margin-bottom: 0.5rem;
        }

        .section-header h2 {
            font-size: clamp(1.75rem, 3.5vw, 2.5rem);
            margin: 0;
            line-height: 1.2;
        }

        .section-block--hero {
            margin-top: clamp(1.5rem, 3vw, 2.5rem);
            margin-bottom: clamp(3.5rem, 7vw, 5.5rem);
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.4rem 0.95rem;
            border-radius: 9999px;
            background: rgba(209, 0, 71, 0.1);
            border: 1px solid rgba(209, 0, 71, 0.3);
            color: #D10047;
            font-size: 0.8125rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            margin-bottom: 1.25rem;
        }

        .hero-badge-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 9999px;
            background: #D10047;
            box-shadow: 0 0 10px #D10047;
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .section-block--hero h2 {
            font-size: clamp(2.25rem, 5vw, 3.75rem);
            font-weight: 800;
            line-height: 1.12;
            letter-spacing: -0.03em;
            margin: 0 0 1.25rem;
            max-width: 52rem;
        }

        .hero-text {
            font-size: clamp(1.125rem, 2vw, 1.35rem);
            line-height: 1.6;
            color: var(--theme-text);
            opacity: 0.85;
            max-width: 44rem;
            margin: 0 0 2.25rem;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            margin-bottom: 3.5rem;
        }

        .hero-actions .ui-button {
            padding: 0.875rem 1.75rem;
            font-size: 1.05rem;
            border-radius: 0.625rem;
        }

        .hero-btn-primary {
            background: #D10047;
            color: #FFFFFF;
            box-shadow: 0 8px 24px -4px rgba(209, 0, 71, 0.4);
        }

        .hero-btn-primary:hover:not(:disabled) {
            background: #C8104A;
            transform: translateY(-2px);
            box-shadow: 0 12px 28px -4px rgba(209, 0, 71, 0.5);
        }

        .hero-btn-secondary {
            border: 1px solid var(--color-border);
            background: var(--theme-background);
            color: var(--theme-text);
        }

        .hero-btn-secondary:hover:not(:disabled) {
            background: color-mix(in srgb, var(--theme-text) 5%, var(--theme-background));
            border-color: var(--theme-text);
            transform: translateY(-2px);
        }

        .hero-trust-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 15rem), 1fr));
            gap: 1.25rem;
            padding-top: 2.5rem;
            border-top: 1px solid var(--color-border);
        }

        .trust-pill {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 1rem 1.25rem;
            background: var(--card-background);
            border: 1px solid var(--color-border);
            border-radius: 0.75rem;
            transition: transform 0.2s, border-color 0.2s;
        }

        .trust-pill:hover {
            border-color: rgba(209, 0, 71, 0.4);
            transform: translateY(-2px);
        }

        .trust-icon-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.5rem;
            background: rgba(209, 0, 71, 0.12);
            border: 1px solid rgba(209, 0, 71, 0.2);
            color: #D10047;
            flex-shrink: 0;
        }

        .trust-text {
            display: flex;
            flex-direction: column;
        }

        .trust-text strong {
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--theme-text);
        }

        .trust-text span {
            font-size: 0.8125rem;
            color: var(--theme-text);
            opacity: 0.7;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 20rem), 1fr));
            gap: 1.5rem;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .card-item {
            display: flex;
            flex-direction: column;
            padding: 1.75rem;
            border: 1px solid var(--color-border);
            border-radius: 1rem;
            background: var(--card-background);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            transition: border-color 0.25s, transform 0.25s, box-shadow 0.25s;
            position: relative;
        }

        .card-item:hover {
            border-color: #D10047;
            transform: translateY(-4px);
            box-shadow: 0 12px 28px -6px rgba(0, 0, 0, 0.12), 0 0 0 1px #D10047;
        }

        .card-icon-container {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3.25rem;
            height: 3.25rem;
            border-radius: 0.75rem;
            background: rgba(209, 0, 71, 0.12);
            border: 1px solid rgba(209, 0, 71, 0.25);
            color: #D10047;
            margin-bottom: 1.25rem;
        }

        .card-item-title {
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1.3;
            margin: 0 0 0.75rem;
        }

        .card-item-title a {
            color: var(--theme-text);
            text-decoration: none;
            transition: color 0.2s;
        }

        .card-item-title a:hover {
            color: #D10047;
        }

        .card-item-desc {
            font-size: 0.95rem;
            line-height: 1.6;
            color: var(--theme-text);
            opacity: 0.8;
            margin: 0 0 1.5rem;
            flex: 1;
        }

        .card-item-footer {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid var(--color-border);
        }

        .card-item-action {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #D10047;
            text-decoration: none;
            transition: gap 0.2s;
        }

        .card-item:hover .card-item-action {
            gap: 0.625rem;
        }

        .product-badge-wrap {
            margin-bottom: 1rem;
        }

        .product-badge {
            background: rgba(209, 0, 71, 0.1);
            color: #D10047;
            border-color: rgba(209, 0, 71, 0.3);
            font-weight: 600;
            border-radius: 0.375rem;
        }

        .features-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 17rem), 1fr));
            gap: 1.5rem;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .feature-card {
            padding: 1.75rem;
            border: 1px solid var(--color-border);
            border-radius: 1rem;
            background: var(--card-background);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .feature-num {
            font-family: 'Sora', sans-serif;
            font-size: 1.75rem;
            font-weight: 800;
            color: #D10047;
            line-height: 1;
            opacity: 0.85;
        }

        .feature-title {
            font-family: 'Sora', sans-serif;
            font-size: 1.15rem;
            font-weight: 700;
            line-height: 1.35;
            color: var(--theme-text);
        }

        .feature-text {
            font-size: 0.95rem;
            line-height: 1.6;
            color: var(--theme-text);
            opacity: 0.8;
            margin: 0.5rem 0 0;
        }

        .faq-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin: 0;
            padding: 0;
        }

        .faq-item {
            border: 1px solid var(--color-border);
            border-radius: 0.75rem;
            background: var(--card-background);
            overflow: hidden;
        }

        .faq-question {
            padding: 1.25rem 1.5rem;
            font-family: 'Sora', sans-serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--theme-text);
            margin: 0;
        }

        .faq-answer {
            padding: 0 1.5rem 1.25rem;
            margin: 0;
            font-size: 0.95rem;
            line-height: 1.65;
            color: var(--theme-text);
            opacity: 0.85;
        }

        .ui-cta-banner {
            box-sizing: border-box;
            padding: clamp(2.5rem, 5vw, 4rem) clamp(1.5rem, 4vw, 3.5rem);
            border: 1px solid rgba(209, 0, 71, 0.4);
            border-radius: 1.25rem;
            background: linear-gradient(135deg, #111315 0%, #17191E 55%, #25121B 100%);
            color: #FFFFFF;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.4), 0 0 30px -10px rgba(209, 0, 71, 0.3);
            position: relative;
            overflow: hidden;
        }

        .ui-cta-banner__kicker {
            display: inline-block;
            font-family: 'Sora', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #D10047;
            margin-bottom: 0.75rem;
        }

        .ui-cta-banner h2 {
            font-size: clamp(1.85rem, 4vw, 2.75rem);
            font-weight: 800;
            line-height: 1.2;
            color: #FFFFFF;
            margin: 0 0 1rem;
            max-width: 44rem;
        }

        .ui-cta-banner__text {
            font-size: clamp(1.05rem, 2vw, 1.2rem);
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.85);
            margin: 0 0 2rem;
            max-width: 42rem;
        }

        .ui-cta-banner__btn {
            background: #D10047;
            color: #FFFFFF;
            padding: 0.875rem 1.75rem;
            font-size: 1.05rem;
            border-radius: 0.625rem;
            border: none;
            box-shadow: 0 6px 20px rgba(209, 0, 71, 0.45);
        }

        .ui-cta-banner__btn:hover:not(:disabled) {
            background: #C8104A;
            box-shadow: 0 8px 25px rgba(209, 0, 71, 0.6);
            transform: translateY(-2px);
        }

        .site-footer {
            box-sizing: border-box;
            background: color-mix(in srgb, var(--theme-text) 2%, var(--theme-background));
            border-top: 1px solid var(--color-border);
            padding: clamp(3rem, 6vw, 4.5rem) clamp(1rem, 4vw, 4rem) 2rem;
            margin-top: 4rem;
        }

        .site-footer-inner {
            width: min(100%, 80rem);
            margin-inline: auto;
        }

        .site-footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.25fr;
            gap: 2.5rem;
            padding-bottom: 3rem;
            border-bottom: 1px solid var(--color-border);
        }

        @media (max-width: 64rem) {
            .site-footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 36rem) {
            .site-footer-grid {
                grid-template-columns: 1fr;
            }
        }

        .site-footer-desc {
            font-size: 0.9375rem;
            line-height: 1.6;
            color: var(--theme-text);
            opacity: 0.75;
            margin: 1rem 0 1.25rem;
            max-width: 24rem;
        }

        .site-footer-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            background: rgba(209, 0, 71, 0.08);
            border: 1px solid rgba(209, 0, 71, 0.2);
            font-size: 0.75rem;
            font-weight: 600;
            color: #D10047;
        }

        .site-footer-dot {
            width: 0.375rem;
            height: 0.375rem;
            border-radius: 9999px;
            background: #D10047;
        }

        .site-footer-heading {
            font-family: 'Sora', sans-serif;
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--theme-text);
            margin: 0 0 1rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .site-footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
        }

        .site-footer-links a {
            font-size: 0.9375rem;
            color: var(--theme-text);
            opacity: 0.75;
            text-decoration: none;
            transition: opacity 0.2s, color 0.2s;
        }

        .site-footer-links a:hover {
            opacity: 1;
            color: #D10047;
        }

        .site-footer-contact-row {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            font-size: 0.9375rem;
            color: var(--theme-text);
            opacity: 0.8;
            margin: 0 0 0.75rem;
        }

        .site-footer-contact-row a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s;
        }

        .site-footer-contact-row a:hover {
            color: #D10047;
        }

        .site-footer-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            padding-top: 1.5rem;
            flex-wrap: wrap;
        }

        .site-footer-brand-wrap {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .site-footer-brand {
            font-weight: 700;
            font-size: 1.125rem;
        }

        .listing-hero {
            margin-bottom: 2.5rem;
            max-width: 48rem;
        }

        .listing-title {
            font-size: clamp(2rem, 4vw, 3rem);
            margin: 0 0 0.75rem;
            line-height: 1.2;
        }

        .listing-lead {
            font-size: 1.15rem;
            line-height: 1.6;
            color: var(--theme-text);
            opacity: 0.85;
            margin: 0;
        }

        .listing-card {
            display: flex;
            flex-direction: column;
            transition: transform 0.25s, border-color 0.25s, box-shadow 0.25s;
        }

        .listing-card:hover {
            border-color: #D10047;
            transform: translateY(-3px);
            box-shadow: 0 10px 24px -4px rgba(0, 0, 0, 0.08);
        }

        .listing-card h2 {
            font-size: 1.35rem;
            margin: 0 0 0.75rem;
            line-height: 1.3;
        }

        .listing-card h2 a {
            color: var(--theme-text);
            text-decoration: none;
            transition: color 0.2s;
        }

        .listing-card h2 a:hover {
            color: #D10047;
        }

        .listing-card-desc {
            font-size: 0.95rem;
            line-height: 1.6;
            color: var(--theme-text);
            opacity: 0.8;
            margin: 0 0 1.25rem;
            flex: 1;
        }

        .listing-card-action {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #D10047;
            margin-top: auto;
        }

        .listing-empty-box {
            padding: 3rem 1.5rem;
            text-align: center;
            border: 1px dashed var(--color-border);
            border-radius: 0.75rem;
            opacity: 0.8;
        }

        .listing-pagination {
            margin-top: 2.5rem;
        }

        .whatsapp-float-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 99;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 9999px;
            background: #25D366;
            color: #FFFFFF;
            box-shadow: 0 8px 24px rgba(37, 211, 102, 0.4);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .whatsapp-float-btn:hover {
            transform: scale(1.08);
            box-shadow: 0 12px 28px rgba(37, 211, 102, 0.55);
        }
    </style>

    @vite('resources/css/app.css')
</head>
<body class="public-layout">
    <a class="skip-link" href="#main-content">Pular para o conteúdo</a>

    <header class="site-header">
        <div class="site-header-container">
            <a class="site-brand" href="{{ route('home') }}">
                <img class="site-logo site-logo--light" src="{{ asset('images/logo_devstudio_dark_300x60.png') }}" alt="DevStudio - início">
                <img class="site-logo site-logo--dark" src="{{ asset('images/logo_devstudio_light_300x60.png') }}" alt="DevStudio - início">
            </a>
            <x-public-navigation label="Navegação principal" />
            <x-button variant="primary" :href="route('contact.show')" class="site-header-cta">Fale com a DevStudio</x-button>
        </div>
    </header>

    @yield('content')

    <footer class="site-footer">
        <div class="site-footer-inner">
            <div class="site-footer-grid">
                <div class="site-footer-col site-footer-col--brand">
                    <a class="site-brand" href="{{ route('home') }}">
                        <img class="site-logo site-logo--light" src="{{ asset('images/logo_devstudio_dark_300x60.png') }}" alt="DevStudio - início">
                        <img class="site-logo site-logo--dark" src="{{ asset('images/logo_devstudio_light_300x60.png') }}" alt="DevStudio - início">
                    </a>
                    <p class="site-footer-desc">Desenvolvemos softwares, sistemas empresariais sob medida e soluções digitais inteligentes para empresas que desejam evoluir, automatizar processos e crescer por meio da tecnologia.</p>
                    <div class="site-footer-pill">
                        <span class="site-footer-dot"></span>
                        <span>Arquitetura Moderna & Alta Disponibilidade</span>
                    </div>
                </div>

                <div class="site-footer-col">
                    <h4 class="site-footer-heading">Soluções</h4>
                    <ul class="site-footer-links">
                        <li><a href="{{ route('services.show', ['slug' => 'software-personalizado']) }}">Software Personalizado</a></li>
                        <li><a href="{{ route('services.show', ['slug' => 'erp']) }}">Sistemas ERP</a></li>
                        <li><a href="{{ route('services.show', ['slug' => 'sites-e-ecommerce']) }}">Sites & E-commerce</a></li>
                        <li><a href="{{ route('services.show', ['slug' => 'inteligencia-artificial']) }}">Inteligência Artificial</a></li>
                        <li><a href="{{ route('services.show', ['slug' => 'automacao']) }}">Automação & Integrações</a></li>
                        <li><a href="{{ route('services.show', ['slug' => 'aplicativos']) }}">Aplicativos Mobile</a></li>
                    </ul>
                </div>

                <div class="site-footer-col">
                    <h4 class="site-footer-heading">Institucional</h4>
                    <ul class="site-footer-links">
                        <li><a href="{{ route('pages.about') }}">Sobre a DevStudio</a></li>
                        <li><a href="{{ route('products.index') }}">Produtos Próprios</a></li>
                        <li><a href="{{ route('projects.index') }}">Projetos & Cases</a></li>
                        <li><a href="{{ route('posts.index') }}">Insights & Artigos</a></li>
                        <li><a href="{{ route('contact.show') }}">Fale Conosco</a></li>
                    </ul>
                </div>

                <div class="site-footer-col site-footer-col--contact">
                    <h4 class="site-footer-heading">Atendimento</h4>
                    <p class="site-footer-contact-row">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        <a href="mailto:contato@devstudio.com.br">contato@devstudio.com.br</a>
                    </p>
                    <p class="site-footer-contact-row">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        <a href="https://wa.me/5511999999999" target="_blank" rel="noopener noreferrer">+55 (11) 99999-9999</a>
                    </p>
                    <div style="margin-top: 1rem;">
                        <x-button variant="primary" :href="route('contact.show')">Fale com a DevStudio</x-button>
                    </div>
                </div>
            </div>

            <div class="site-footer-bottom">
                <div class="site-footer-brand-wrap">
                    <span class="site-footer-brand">DevStudio</span>
                    <small style="opacity: 0.7;">Tecnologia que transforma negócios.</small>
                </div>
                <x-public-navigation label="Navegação do rodapé" />
            </div>
        </div>
    </footer>

    {{-- Botão flutuante acessível do WhatsApp --}}
    @php
        $globalWhatsApp = '5511999999999';
    @endphp
    <a href="https://wa.me/{{ $globalWhatsApp }}?text={{ urlencode('Olá! Gostaria de falar com a equipe da DevStudio.') }}"
       target="_blank" rel="noopener noreferrer"
       class="whatsapp-float-btn"
       aria-label="Conversar pelo WhatsApp da DevStudio">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 0 1 2.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.196 8.196 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24m4.52 11.53c-.25-.12-1.47-.72-1.7-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-2-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.12-.15.17-.25.25-.42.08-.17.04-.31-.02-.43s-.56-1.35-.77-1.85c-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.22.25-.86.84-.86 2.05s.88 2.38 1 2.55c.13.17 1.74 2.66 4.21 3.73.59.25 1.05.41 1.41.52.59.19 1.13.16 1.56.1.47-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.06-.12-.22-.19-.47-.32"/>
        </svg>
    </a>
</body>
</html>
