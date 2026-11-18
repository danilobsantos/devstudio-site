@extends('layouts.public')

@section('content')
<main id="main-content">
    @if ($page->slug !== 'home')
        <div class="{{ $page->slug === 'sobre' ? 'page-about-breadcrumbs' : '' }}">
            <x-breadcrumbs :items="[['label' => 'Início', 'url' => url('/')], ['label' => $page->slug === 'sobre' ? 'Sobre' : $page->title, 'current' => true]]" />
        </div>
    @endif

    <article class="page-article {{ $page->slug === 'home' ? 'page-home' : ($page->slug === 'sobre' ? 'page-about' : 'page-standard') }}">
        <h1>{{ $page->title }}</h1>
        @if ($page->slug !== 'home' && $page->slug !== 'sobre' && $page->excerpt)<p class="page-lead">{{ $page->excerpt }}</p>@endif

        @foreach ($sections as $section)
            <section class="section-block section-block--{{ str_replace('_', '-', $section['type']) }}" @if ($section['type'] === 'features_list') id="principios" @endif>
                @if ($section['type'] !== 'cta')
                    @if ($section['type'] === 'hero')
                        <div class="hero-copy">
                    @endif
                    <div class="section-header">
                        @switch($section['type'])
                            @case('hero')
                                <div class="hero-badge">
                                    <span>{{ $page->slug === 'sobre' ? 'Sobre a DevStudio' : 'Engenharia de Software & Inovação Digital' }}</span>
                                </div>
                                @break
                            @case('services_grid')
                                <span class="section-kicker">Capacidades &amp; Soluções</span>
                                @break
                            @case('products_showcase')
                                <span class="section-kicker">Ecossistema DevStudio</span>
                                @break
                            @case('features_list')
                                <span class="section-kicker">{{ $page->slug === 'sobre' ? 'Nossos Princípios & Valores' : 'Diferenciais Competitivos' }}</span>
                                @break
                            @case('faq')
                                <span class="section-kicker">Dúvidas Frequentes</span>
                                @break
                            @case('text_image')
                                @if ($page->slug === 'sobre')
                                    <span class="section-kicker">Institucional &amp; Filosofia</span>
                                @endif
                                @break
                        @endswitch
                        <h2>{{ $section['data']['title'] }}</h2>
                    </div>
                @endif

                @switch($section['type'])
                    @case('hero')
                        <div class="hero-body">
                            @if (isset($section['data']['text']))
                                <p class="hero-text">{{ $section['data']['text'] }}</p>
                            @endif

                            @if (!empty($section['data']['ctas']))
                                <div class="hero-actions">
                                    @foreach ($section['data']['ctas'] as $index => $cta)
                                        <a href="{{ $cta['url'] }}" class="ui-button {{ $index === 0 ? 'ui-button--primary hero-btn-primary' : 'ui-button--secondary hero-btn-secondary' }}">
                                            <span>{{ $cta['label'] }}</span>
                                            @if ($index === 0)
                                                <svg class="hero-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                                    <polyline points="12 5 19 12 12 19"></polyline>
                                                </svg>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                        </div>
                        @if ($section['type'] === 'hero')
                            </div>
                            <div class="hero-art">
                                @php
                                    $heroImage = ($page->slug === 'sobre' && file_exists(public_path('images/devstudio-about-hero.jpg')))
                                        ? 'images/devstudio-about-hero.jpg'
                                        : 'images/devstudio-hero.jpg';
                                @endphp
                                <img src="{{ asset($heroImage) }}" alt="DevStudio - Tecnologia que transforma negócios" width="1536" height="1024" fetchpriority="high">
                            </div>
                        @endif
                        @break

                    @case('text_image')
                        @if ($page->slug === 'sobre' && str_contains(mb_strtolower($section['data']['title']), 'visão') && file_exists(public_path('images/devstudio-about-vision.jpg')))
                            <div class="about-vision-grid">
                                <div class="section-text-block">
                                    @foreach (explode("\n\n", $section['data']['text']) as $paragraph)
                                        @if (trim($paragraph))
                                            <p>{{ trim($paragraph) }}</p>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="about-vision-art">
                                    <img src="{{ asset('images/devstudio-about-vision.jpg') }}" alt="DevStudio - Visão estratégica sobre tecnologia" width="1200" height="800" loading="lazy">
                                </div>
                            </div>
                        @elseif (isset($section['data']['text']))
                            <div class="section-text-block">
                                @foreach (explode("\n\n", $section['data']['text']) as $paragraph)
                                    @if (trim($paragraph))
                                        <p>{{ trim($paragraph) }}</p>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                        @break

                    @case('cta')
                        <x-cta-banner
                            :title="$section['data']['title']"
                            :text="$section['data']['text'] ?? null"
                            :action-url="$section['data']['url']"
                            :action-label="$section['data']['label']"
                        />
                        @break

                    @case('services_grid')
                    @case('products_showcase')
                    @case('projects_showcase')
                        <ul class="cards-grid {{ $section['type'] === 'services_grid' ? 'services-grid' : ($section['type'] === 'products_showcase' ? 'products-grid' : 'projects-grid') }}">
                            @foreach ($section['items'] as $item)
                                <li class="card-item">
                                    @if ($section['type'] === 'services_grid')
                                        <div class="card-icon-container">
                                            @php $slug = $item->slug; @endphp
                                            @if (str_contains($slug, 'software'))
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                            @elseif (str_contains($slug, 'erp'))
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                                            @elseif (str_contains($slug, 'ecommerce') || str_contains($slug, 'site'))
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                                            @elseif (str_contains($slug, 'inteligencia') || str_contains($slug, 'ia'))
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line><line x1="20" y1="9" x2="23" y2="9"></line><line x1="20" y1="14" x2="23" y2="14"></line><line x1="1" y1="9" x2="4" y2="9"></line><line x1="1" y1="14" x2="4" y2="14"></line></svg>
                                            @elseif (str_contains($slug, 'automacao'))
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                            @elseif (str_contains($slug, 'aplicativ') || str_contains($slug, 'app'))
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                                            @else
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                                            @endif
                                        </div>
                                        <h3 class="card-item-title">
                                            <a href="{{ route('services.show', ['slug' => $item->slug]) }}">{{ $item->title }}</a>
                                        </h3>
                                        @if ($item->excerpt)
                                            <p class="card-item-desc">{{ $item->excerpt }}</p>
                                        @endif
                                        <div class="card-item-footer">
                                            <span class="card-item-action" aria-hidden="true">
                                                <span>Conhecer solução</span>
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                            </span>
                                        </div>
                                    @elseif ($section['type'] === 'products_showcase')
                                        <div class="product-badge-wrap">
                                            <span class="ui-badge product-badge">Plataforma Própria</span>
                                        </div>
                                        <h3 class="card-item-title">
                                            <a href="{{ route('products.show', ['slug' => $item->slug]) }}">{{ $item->name }}</a>
                                        </h3>
                                        @if ($item->tagline)
                                            <p class="card-item-desc">{{ $item->tagline }}</p>
                                        @endif
                                        <div class="card-item-footer">
                                            <span class="card-item-action" aria-hidden="true">
                                                <span>Ver detalhes do produto</span>
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                            </span>
                                        </div>
                                    @else
                                        <h3 class="card-item-title">
                                            <a href="{{ route('projects.show', ['slug' => $item->slug]) }}">{{ $item->title }}</a>
                                        </h3>
                                        @if ($item->summary)
                                            <p class="card-item-desc">{{ $item->summary }}</p>
                                        @endif
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        @break

                    @case('features_list')
                        <ul class="features-list">
                            @foreach ($section['data']['items'] as $index => $item)
                                <li class="feature-card">
                                    <div class="feature-num">0{{ $index + 1 }}</div>
                                    <div class="feature-content">
                                        <strong class="feature-title">{{ $item['title'] }}</strong>
                                        @if (isset($item['text']))
                                            <p class="feature-text">{{ $item['text'] }}</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        @break

                    @case('faq')
                        <dl class="faq-list">
                            @foreach ($section['data']['items'] as $item)
                                <div class="faq-item">
                                    <dt class="faq-question">{{ $item['question'] }}</dt>
                                    <dd class="faq-answer">{{ $item['answer'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                        @break
                @endswitch
            </section>
        @endforeach
    </article>
</main>
@endsection
