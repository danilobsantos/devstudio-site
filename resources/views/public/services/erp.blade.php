@extends('layouts.public')

@section('content')
@php
    $badge = 'Gestão Integrada & Modular';
    $headline = 'Sistemas de Gestão Empresarial (ERP) Sob Medida';
    $valueProp = 'Unifique vendas, compras, estoque, finanças e emissão fiscal em uma plataforma web moderna, segura e desenhada especificamente para os fluxos operacionais da sua empresa, sem a lentidão ou complexidade de sistemas engessados.';

    $problems = [
        [
            'title' => 'Informações Espalhadas em Dezenas de Planilhas',
            'text' => 'Setores trabalhando com dados isolados e divergentes, causando retrabalho, perda de informações críticas e lentidão na tomada de decisões.',
        ],
        [
            'title' => 'Erros Fiscais & Risco de Autuações',
            'text' => 'Dificuldade para parametrizar regras tributárias complexas por estado e atrasos diários na emissão e envio de notas fiscais eletrônicas.',
        ],
        [
            'title' => 'Falta de Visibilidade em Tempo Real',
            'text' => 'Diretoria sem saber o fluxo de caixa projetado, a margem de lucro real por produto ou a posição consolidada de múltiplos estoques.',
        ],
    ];

    $deliverables = [
        [
            'icon' => 'database',
            'title' => 'Módulo Financeiro & DRE Gerencial',
            'text' => 'Contas a pagar e a receber, conciliação bancária automática com arquivos OFX, fluxo de caixa diário e DRE em tempo real.',
            'highlights' => ['Demonstrativo de Resultado do Exercício (DRE)', 'Centro de custos e planos de contas customizados', 'Projeção financeira e controle de inadimplência'],
        ],
        [
            'icon' => 'fiscal',
            'title' => 'Emissão Fiscal Completa (NF-e, NFS-e, NFC-e)',
            'text' => 'Integração direta com a SEFAZ com cálculo automático de impostos (ICMS, PIS, COFINS, IPI, ISS) e guarda segura de XMLs.',
            'highlights' => ['Suporte a certificado digital A1 seguro', 'Emissão em lote de notas fiscais e boletos', 'Geração de relatórios fiscais para contabilidade'],
        ],
        [
            'icon' => 'box',
            'title' => 'Gestão Inteligente de Estoque & Lotes',
            'text' => 'Rastreabilidade total de mercadorias, controle de múltiplos armazéns, ponto de reposição automatizado e histórico de movimentações.',
            'highlights' => ['Curva ABC de produtos e giro de estoque', 'Controle de número de série e data de validade', 'Inventários rotativos com aplicativo de conferência'],
        ],
        [
            'icon' => 'users',
            'title' => 'Gestão Comercial & Vendas Integradas',
            'text' => 'Do orçamento à entrega: controle de pedidos, comissões de vendedores, regras de tabela de preços e histórico completo do cliente.',
            'highlights' => ['Múltiplas tabelas de preços e políticas comerciais', 'Geração instantânea de pedidos e contratos PDF', 'Integração transparente com e-commerce e CRMs'],
        ],
    ];

    $useCases = [
        [
            'title' => 'Distribuidoras & Atacadistas',
            'text' => 'Orquestração de pedidos de representantes externos, separação por lote, conferência por código de barras e expedição rápida.',
        ],
        [
            'title' => 'Indústrias & Manufaturas',
            'text' => 'Ordens de produção (OP), baixa automática de insumos e matérias-primas, cálculo de custo médio e controle de perdas.',
        ],
        [
            'title' => 'Empresas de Serviços Recorrentes',
            'text' => 'Contratos mensais com reajuste automático, faturamento em lote, emissão de NFS-e e conciliação bancária unificada.',
        ],
    ];

    $process = [
        [
            'title' => 'Diagnóstico Operacional & Fiscal',
            'text' => 'Mapeamos suas regras contábeis, regimes tributários por estado e gargalos operacionais entre setores.',
        ],
        [
            'title' => 'Modelagem & Migração de Dados',
            'text' => 'Higienizamos e importamos seus cadastros históricos (clientes, fornecedores, produtos e saldos) para a nova base.',
        ],
        [
            'title' => 'Implantação Faseada por Módulo',
            'text' => 'Liberamos módulos em etapas testadas (Financeiro, Comercial, Fiscal) sem gerar atrito ou paralisação do negócio.',
        ],
        [
            'title' => 'Treinamento & Virada com Suporte',
            'text' => 'Capacitação prática da sua equipe e acompanhamento dedicado durante o período de transição operacional.',
        ],
    ];

    $stack = ['Laravel 13', 'MySQL / PostgreSQL', 'Filament 5', 'Certificados A1 SEFAZ', 'Redis', 'Docker', 'APIs Bancárias'];

    $faqs = [
        [
            'question' => 'É possível migrar dados do nosso sistema ou planilhas antigas?',
            'answer' => 'Sim! Realizamos a extração, higienização e validação de consistência dos dados de clientes, produtos, estoques e histórico financeiro para que a transição ocorra sem perda de histórico.',
        ],
        [
            'question' => 'O ERP atende empresas com filiais ou múltiplos CNPJs?',
            'answer' => 'Sim. A arquitetura é totalmente multi-empresa: você pode gerenciar múltiplas filiais e empresas do mesmo grupo com visão consolidada da diretoria ou acessos segmentados por unidade.',
        ],
        [
            'question' => 'A emissão de notas fiscais funciona de forma automatizada?',
            'answer' => 'Sim. O sistema se comunica diretamente com os webservices da SEFAZ e prefeituras, calculando impostos conforme as regras tributárias cadastradas e emitindo NF-e, NFS-e e boletos em poucos segundos.',
        ],
        [
            'question' => 'O sistema é 100% online ou precisa de servidor local?',
            'answer' => 'O ERP é uma aplicação web moderna hospedada em nuvem segura, acessível de qualquer computador, tablet ou celular com internet, sem necessidade de servidores caros no seu escritório.',
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
    cta-title="Pronto para modernizar a gestão da sua empresa com um ERP sob medida?"
    cta-text="Converse com nossos especialistas e veja como eliminar planilhas e unificar sua operação com eficiência máxima."
/>
@endsection
