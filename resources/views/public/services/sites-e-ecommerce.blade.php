@extends('layouts.public')

@section('content')
@php
    $badge = 'Presença Digital & Conversão';
    $headline = 'Sites e Lojas Virtuais de Alta Performance';
    $valueProp = 'Desenvolvemos portais institucionais e plataformas de comércio eletrônico focadas em velocidade extrema (PageSpeed 90+), design corporativo contemporâneo, Core Web Vitals otimizados e experiência impecável para transformar visitantes em clientes.';

    $problems = [
        [
            'title' => 'Sites Lentos com Queda de Posicionamento no Google',
            'text' => 'Páginas pesadas e cheias de plugins desnecessários que demoram mais de 3 segundos para carregar, afastando clientes e sendo penalizadas pelo algoritmo de busca.',
        ],
        [
            'title' => 'Altas Taxas de Abandono no Checkout',
            'text' => 'Processos de compra confusos, falta de opções rápidas como Pix dinâmico e formulários gigantescos que desestimulam o consumidor na etapa final.',
        ],
        [
            'title' => 'Design Amador que Não Passa Credibilidade',
            'text' => 'Templates genéricos que não transmitem o verdadeiro valor da sua marca e não funcionam corretamente em telas de smartphones modernos.',
        ],
    ];

    $deliverables = [
        [
            'icon' => 'globe',
            'title' => 'Velocidade Ultra Rápida & Core Web Vitals',
            'text' => 'Arquitetura front-end enxuta, imagens em formato WebP/AVIF com redimensionamento dinâmico e carregamento prioritário (LCP & INP excepcionais).',
            'highlights' => ['Nota 90+ no Google PageSpeed Insights', 'Código limpo sem dependência de temas lentos', 'Cache inteligente e CDN global'],
        ],
        [
            'icon' => 'cart',
            'title' => 'Checkout Transparente com Pix & Cartões',
            'text' => 'Fluxo de pagamento fluido em uma única etapa, geração instantânea de QR Code Pix, parcelamento inteligente e antifraude integrado.',
            'highlights' => ['Integração com Mercado Pago, Asaas, Stripe ou Pagar.me', 'Recuperação automática de carrinhos abandonados', 'Cálculo de frete em tempo real (Correios, Melhor Envio, transportadoras)'],
        ],
        [
            'icon' => 'edit',
            'title' => 'CMS Amigável para Atualização Rápida',
            'text' => 'Painel administrativo intuitivo que permite à sua equipe cadastrar produtos, alterar preços, publicar artigos e criar banners sem depender de programador.',
            'highlights' => ['Edição visual descomplicada', 'Upload organizado de mídias e fotos', 'Gestão de categorias, cupons e estoque'],
        ],
        [
            'icon' => 'star',
            'title' => 'SEO Técnico & Schema.org Estruturado',
            'text' => 'Estruturação semântica de ponta para mecanismos de busca e redes sociais: tags Open Graph, dados estruturados JSON-LD e sitemaps dinâmicos.',
            'highlights' => ['Rich snippets de produtos e avaliações no Google', 'Meta títulos e descrições customizados por página', 'URLs amigáveis e canônicas automáticas'],
        ],
    ];

    $useCases = [
        [
            'title' => 'Portais Institucionais Corporativos',
            'text' => 'Sites de alta autoridade para empresas B2B, indústrias e prestadores de serviços que precisam conquistar clientes corporativos de alto ticket.',
        ],
        [
            'title' => 'Lojas Virtuais B2C & D2C',
            'text' => 'E-commerces modernos com catálogo dinâmico, filtros rápidos por atributo (tamanho, cor, modelo), avaliações de clientes e promoções programadas.',
        ],
        [
            'title' => 'E-commerce B2B com Regras Comerciais',
            'text' => 'Plataformas de atacado com pedidos mínimos por CNPJ, tabelas de preço diferenciadas por cliente e faturamento via boleto bancário.',
        ],
    ];

    $process = [
        [
            'title' => 'Arquitetura de Informação & UX',
            'text' => 'Estudamos o comportamento de compra do seu público e estruturamos as rotas e funis que mais convertem.',
        ],
        [
            'title' => 'Design Visual Exclusivo (Anti-Template)',
            'text' => 'Criamos uma interface contemporânea e autêntica, alinhada à identidade visual e paleta da sua marca.',
        ],
        [
            'title' => 'Programação Otimizada & Integrações',
            'text' => 'Codificamos com Blade, Tailwind CSS e APIs de pagamento, com testes exaustivos de checkout e responsividade.',
        ],
        [
            'title' => 'Homologação, SEO & Go-Live',
            'text' => 'Auditamos métricas de PageSpeed, cadastramos no Google Search Console e acompanhamos as primeiras compras ao vivo.',
        ],
    ];

    $stack = ['Laravel 13', 'Blade & Livewire', 'Tailwind CSS 4', 'Vite', 'Gateways de Pagamento (Pix / Cartão)', 'Schema.org', 'Cloudflare CDN'];

    $faqs = [
        [
            'question' => 'O site ou e-commerce será otimizado para o Google?',
            'answer' => 'Sim! Todas as nossas páginas são construídas com foco rigoroso em SEO técnico: tags semânticas HTML5, metadados Open Graph para redes sociais, dados estruturados Schema.org, sitemaps XML atualizados automaticamente e tempos de carregamento instantâneos exigidos pelos Core Web Vitals.',
        ],
        [
            'question' => 'Nossa equipe conseguirá atualizar produtos e textos sem suporte técnico?',
            'answer' => 'Com certeza. O site conta com painel administrativo Filament intuitivo em português onde você gerencia produtos, banners, textos, categorias e pedidos de forma simples e rápida.',
        ],
        [
            'question' => 'Quais formas de pagamento podem ser disponibilizadas?',
            'answer' => 'Integramos as principais soluções do mercado brasileiro e global: Pix dinâmico com confirmação instantânea por webhook, cartões de crédito em até 12x com antifraude, boleto bancário e carteiras digitais.',
        ],
        [
            'question' => 'O site funciona perfeitamente em celulares antigos e novos?',
            'answer' => 'Sim. O design é pensado em formato mobile-first, testado em múltiplos dispositivos e tamanhos de tela para garantir fluidez, botões clicáveis com facilidade e navegação rápida.',
        ],
    ];
@endphp

<x-service-landing-page
    :service="$service"
    :badge="$badge"
    :headline="$headline"
    :value-prop="$valueProp"
    :problems="$problems"
    :deliverables="$deliverables"
    :use-cases="$useCases"
    :process="$process"
    :stack="$stack"
    :faqs="$faqs"
    cta-title="Pronto para elevar a presença digital e as vendas da sua empresa?"
    cta-text="Fale com nosso time de design e tecnologia para planejar um site ou e-commerce de alto impacto."
/>
@endsection
