@props([
    'service',
    'badge',
    'headline',
    'valueProp',
    'problems' => [],
    'deliverables' => [],
    'useCases' => [],
    'process' => [],
    'stack' => [],
    'faqs' => [],
    'ctaTitle' => 'Pronto para iniciar seu projeto com a DevStudio?',
    'ctaText' => 'Fale diretamente com nossos arquitetos de software e receba uma análise de viabilidade técnica e escopo detalhado.',
])

<main id="main-content">
    <x-breadcrumbs :items="[
        ['label' => 'Início', 'url' => url('/')],
        ['label' => 'Soluções', 'url' => route('services.index')],
        ['label' => $service->title, 'current' => true]
    ]" />

    <article class="service-detail-page">
        {{-- Hero da Solução --}}
        <header class="section-block section-block--hero service-hero" style="margin-top: 1rem; margin-bottom: 4rem;">
            <div class="hero-badge">
                <span class="hero-badge-dot"></span>
                <span>{{ $badge }}</span>
            </div>

            <h1 style="font-size: clamp(2.25rem, 4.5vw, 3.5rem); font-weight: 800; line-height: 1.15; margin: 0 0 1.25rem; max-width: 52rem;">
                {{ $headline }}
            </h1>

            <p class="hero-text" style="font-size: clamp(1.1rem, 2vw, 1.25rem); line-height: 1.6; max-width: 46rem; opacity: 0.9; margin-bottom: 2rem;">
                {{ $valueProp }}
            </p>

            <div class="hero-actions" style="margin-bottom: 2.5rem;">
                <x-button variant="primary" :href="route('contact.show', ['servico' => $service->slug])" class="hero-btn-primary">
                    <span>Solicitar Proposta Técnica</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </x-button>

                <a href="https://wa.me/5511999999999?text={{ urlencode('Olá! Gostaria de falar sobre a solução: '.$service->title) }}"
                   target="_blank" rel="noopener noreferrer" class="ui-button ui-button--secondary hero-btn-secondary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="color: #25D366;">
                        <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 0 1 2.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.196 8.196 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24m4.52 11.53c-.25-.12-1.47-.72-1.7-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-2-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.12-.15.17-.25.25-.42.08-.17.04-.31-.02-.43s-.56-1.35-.77-1.85c-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.22.25-.86.84-.86 2.05s.88 2.38 1 2.55c.13.17 1.74 2.66 4.21 3.73.59.25 1.05.41 1.41.52.59.19 1.13.16 1.56.1.47-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.06-.12-.22-.19-.47-.32"/>
                    </svg>
                    <span>Conversar no WhatsApp</span>
                </a>
            </div>

            {{-- 3 Destaques Técnicos Rápidos --}}
            <div class="hero-trust-bar">
                <div class="trust-pill">
                    <div class="trust-icon-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                    </div>
                    <div class="trust-text">
                        <strong>Código 100% Proprietário</strong>
                        <span>Direitos e repositório exclusivos da sua empresa</span>
                    </div>
                </div>
                <div class="trust-pill">
                    <div class="trust-icon-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    </div>
                    <div class="trust-text">
                        <strong>Segurança &amp; LGPD</strong>
                        <span>Controle de acessos, logs e criptografia</span>
                    </div>
                </div>
                <div class="trust-pill">
                    <div class="trust-icon-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    </div>
                    <div class="trust-text">
                        <strong>SLA &amp; Sustentação Ativa</strong>
                        <span>Monitoramento e suporte contínuo pós-deploy</span>
                    </div>
                </div>
            </div>
        </header>

        {{-- Seção: O Problema que Resolvemos --}}
        @if (!empty($problems))
            <section class="section-block">
                <div class="section-header">
                    <span class="section-kicker">Desafios do Cenário Atual</span>
                    <h2>O que resolvemos na sua operação</h2>
                </div>

                <div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(min(100%, 19rem), 1fr));">
                    @foreach ($problems as $problem)
                        <div class="card-item" style="border-left: 3px solid #D10047;">
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                                <div style="display: inline-flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border-radius: 9999px; background: rgba(209, 0, 71, 0.12); color: #D10047;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                </div>
                                <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0; color: var(--theme-text);">
                                    {{ $problem['title'] }}
                                </h3>
                            </div>
                            <p style="font-size: 0.95rem; line-height: 1.6; margin: 0; color: var(--theme-text); opacity: 0.85;">
                                {{ $problem['text'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Seção: Soluções e Entregáveis Técnicos --}}
        @if (!empty($deliverables))
            <section class="section-block">
                <div class="section-header">
                    <span class="section-kicker">Capacidades &amp; Entregáveis</span>
                    <h2>O que entregamos nesta solução</h2>
                </div>

                <div class="cards-grid">
                    @foreach ($deliverables as $deliverable)
                        <div class="card-item">
                            <div class="card-icon-container">
                                @switch($deliverable['icon'] ?? 'code')
                                    @case('code')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                        @break
                                    @case('api')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 11a9 9 0 0 1 9 9"></path><path d="M4 4a16 16 0 0 1 16 16"></path><circle cx="5" cy="19" r="1"></circle></svg>
                                        @break
                                    @case('shield')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                        @break
                                    @case('dashboard')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                                        @break
                                    @case('database')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                                        @break
                                    @case('fiscal')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                        @break
                                    @case('box')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                                        @break
                                    @case('users')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                        @break
                                    @case('globe')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1 4-10z"></path></svg>
                                        @break
                                    @case('cart')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                                        @break
                                    @case('edit')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        @break
                                    @case('star')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                        @break
                                    @case('ai')
                                    @case('cpu')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line><line x1="20" y1="9" x2="23" y2="9"></line><line x1="20" y1="14" x2="23" y2="14"></line><line x1="1" y1="9" x2="4" y2="9"></line><line x1="1" y1="14" x2="4" y2="14"></line></svg>
                                        @break
                                    @case('book')
                                    @case('rag')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                                        @break
                                    @case('file-text')
                                    @case('ocr')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                        @break
                                    @case('chart')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                                        @break
                                    @case('zap')
                                    @case('automation')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                        @break
                                    @case('refresh')
                                    @case('sync')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path></svg>
                                        @break
                                    @case('pulse')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                                        @break
                                    @case('routine')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                                        @break
                                    @case('smartphone')
                                    @case('mobile')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                                        @break
                                    @case('cloud')
                                    @case('offline')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                        @break
                                    @case('bell')
                                    @case('push')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                                        @break
                                    @case('camera')
                                    @case('hardware')
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                                        @break
                                    @default
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                                @endswitch
                            </div>
                            <h3 class="card-item-title">{{ $deliverable['title'] }}</h3>
                            <p class="card-item-desc">{{ $deliverable['text'] }}</p>
                            @if (!empty($deliverable['highlights']))
                                <ul style="list-style: none; padding: 0; margin: 0.5rem 0 0; display: flex; flex-direction: column; gap: 0.35rem;">
                                    @foreach ($deliverable['highlights'] as $highlight)
                                        <li style="font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; opacity: 0.8;">
                                            <span style="color: #D10047; font-weight: bold;">✓</span>
                                            <span>{{ $highlight }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Seção: Aplicações & Casos de Uso --}}
        @if (!empty($useCases))
            <section class="section-block">
                <div class="section-header">
                    <span class="section-kicker">Cenários Reais</span>
                    <h2>Onde esta solução se aplica</h2>
                </div>

                <div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(min(100%, 19rem), 1fr));">
                    @foreach ($useCases as $useCase)
                        <div class="card-item" style="background: color-mix(in srgb, var(--color-brand-500) 4%, var(--card-background)); border-color: rgba(209, 0, 71, 0.2);">
                            <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.75rem;">
                                <span style="display: inline-block; width: 0.5rem; height: 0.5rem; border-radius: 9999px; background: #D10047;"></span>
                                <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0; color: var(--theme-text);">
                                    {{ $useCase['title'] }}
                                </h3>
                            </div>
                            <p style="font-size: 0.95rem; line-height: 1.6; margin: 0; color: var(--theme-text); opacity: 0.85;">
                                {{ $useCase['text'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Seção: Metodologia de Engenharia DevStudio --}}
        @if (!empty($process))
            <section class="section-block">
                <div class="section-header">
                    <span class="section-kicker">Metodologia Ágil</span>
                    <h2>Como construímos seu projeto</h2>
                </div>

                <ul class="features-list">
                    @foreach ($process as $idx => $step)
                        <li class="feature-card">
                            <div class="feature-num">0{{ $idx + 1 }}</div>
                            <div class="feature-content">
                                <strong class="feature-title">{{ $step['title'] }}</strong>
                                <p class="feature-text">{{ $step['text'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Seção: Stack Tecnológica Recomendada --}}
        @if (!empty($stack))
            <section class="section-block" style="padding: 2.5rem; border: 1px solid var(--color-border); border-radius: 1rem; background: var(--card-background);">
                <div style="margin-bottom: 1.5rem;">
                    <span class="section-kicker">Padrões Técnicos</span>
                    <h3 style="font-size: 1.5rem; margin: 0 0 0.5rem;">Tecnologias &amp; Infraestrutura Homologada</h3>
                    <p style="margin: 0; font-size: 0.95rem; opacity: 0.8; max-width: 44rem;">
                        Trabalhamos com tecnologias consolidadas no mercado corporativo global, garantindo alta manutenibilidade, segurança estrita e facilidade de evolução contínua.
                    </p>
                </div>

                <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;">
                    @foreach ($stack as $tech)
                        <span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.45rem 1rem; border-radius: 9999px; background: rgba(209, 0, 71, 0.08); border: 1px solid rgba(209, 0, 71, 0.25); color: var(--theme-text); font-size: 0.875rem; font-weight: 600;">
                            <span style="width: 0.375rem; height: 0.375rem; border-radius: 9999px; background: #D10047;"></span>
                            {{ $tech }}
                        </span>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Seção: FAQ Específico --}}
        @if (!empty($faqs))
            <section class="section-block">
                <div class="section-header">
                    <span class="section-kicker">Tira-Dúvidas</span>
                    <h2>Perguntas Frequentes sobre esta Solução</h2>
                </div>

                <dl class="faq-list">
                    @foreach ($faqs as $faq)
                        <div class="faq-item">
                            <dt class="faq-question">{{ $faq['question'] }}</dt>
                            <dd class="faq-answer">{{ $faq['answer'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endif

        {{-- CTA Banner Final --}}
        <div style="margin-top: 4rem; margin-bottom: 2rem;">
            <x-cta-banner
                :title="$ctaTitle"
                :text="$ctaText"
                :action-url="route('contact.show', ['servico' => $service->slug])"
                action-label="Falar com Arquiteto de Software"
            />
        </div>

        {{-- Schema.org Service --}}
        <script type="application/ld+json">
        {
            "{{ '@context' }}": "https://schema.org",
            "@type": "Service",
            "name": "{{ $service->title }}",
            "description": "{{ $service->excerpt }}",
            "provider": {
                "@type": "Organization",
                "name": "DevStudio – Inovação Digital",
                "url": "{{ url('/') }}",
                "logo": "{{ asset('images/logo_devstudio_dark_300x60.png') }}"
            }
        }
        </script>
    </article>
</main>
