@extends('layouts.public')

@section('content')
@php
    $badge = 'IA & Automação Cognitiva';
    $headline = 'Inteligência Artificial Aplicada a Negócios';
    $valueProp = 'Integre agentes inteligentes autônomos, sistemas RAG treinados na documentação da sua empresa e automações preditivas aos seus processos diários, multiplicando a produtividade operacional com isolamento estrito de dados e conformidade com a LGPD.';

    $problems = [
        [
            'title' => 'Equipes Especializadas Presas em Tarefas Repetitivas',
            'text' => 'Profissionais seniores gastando horas preciosas triando e-mails, respondendo dúvidas comuns de clientes e preenchendo formulários manuais.',
        ],
        [
            'title' => 'Dados Corporativos Subutilizados',
            'text' => 'Montanhas de PDFs, históricos de atendimento e contratos arquivados que ninguém consegue consultar ou analisar em tempo hábil.',
        ],
        [
            'title' => 'Riscos Graves de Vazamento com IAs Públicas',
            'text' => 'Colaboradores colando dados confidenciais de clientes e códigos em ferramentas públicas de IA sem controle corporativo.',
        ],
    ];

    $deliverables = [
        [
            'icon' => 'ai',
            'title' => 'Agentes de Atendimento 24/7 Especializados',
            'text' => 'Chatbots cognitivos integrados ao WhatsApp e sites, treinados especificamente com o catálogo de produtos e políticas da sua empresa.',
            'highlights' => ['Triagem inteligente de leads com qualificação de interesse', 'Respostas contextuais sem alucinações', 'Transbordo suave para atendentes humanos quando necessário'],
        ],
        [
            'icon' => 'rag',
            'title' => 'Sistemas RAG sobre Base de Conhecimento Privada',
            'text' => 'Busca semântica avançada em manuais técnicos, procedimentos internos e contratos, fornecendo respostas precisas com citação de fontes.',
            'highlights' => ['Indexação vetorial em tempo real', 'Isolamento estrito dos dados da sua empresa', 'Zero compartilhamento com modelos públicos'],
        ],
        [
            'icon' => 'ocr',
            'title' => 'Extração Automatizada de Documentos (OCR Inteligente)',
            'text' => 'Algoritmos para ler e converter automaticamente notas fiscais, laudos, certidões e planilhas digitalizadas em dados estruturados no ERP.',
            'highlights' => ['Eliminação de digitação manual de notas e contratos', 'Validação de campos obrigatórios e inconsistências', 'Processamento de lotes de centenas de arquivos por minuto'],
        ],
        [
            'icon' => 'chart',
            'title' => 'Análise Preditiva & Tomada de Decisão',
            'text' => 'Modelos matemáticos treinados no seu histórico para prever padrões de compras, riscos de inadimplência e sazonalidade de estoque.',
            'highlights' => ['Previsão de demanda de vendas', 'Detecção precoce de risco de cancelamento (Churn)', 'Alertas preditivos de reposição de insumos'],
        ],
    ];

    $useCases = [
        [
            'title' => 'Assistente Virtual Corporativo de RH e TI',
            'text' => 'Agente interno que responde dúvidas de colaboradores sobre políticas de benefícios, reembolso e orientações de procedimentos operacionais.',
        ],
        [
            'title' => 'Qualificação Comercial no WhatsApp',
            'text' => 'Agente cognitivo que atende contatos 24/7, responde dúvidas sobre preços e prazos e agenda reuniões diretamente na agenda dos consultores.',
        ],
        [
            'title' => 'Auditoria & Análise Contratual Rápida',
            'text' => 'Ferramenta que analisa minutas contratuais de terceiros e aponta cláusulas com penalidades abusivas ou divergências de escopo.',
        ],
    ];

    $process = [
        [
            'title' => 'Mapeamento de Dados & Viabilidade',
            'text' => 'Avaliamos a qualidade dos dados da sua empresa e desenhamos a arquitetura ideal com métricas claras de sucesso.',
        ],
        [
            'title' => 'Construção da Prova de Conceito (PoC)',
            'text' => 'Desenvolvemos uma versão preliminar funcional em 2 semanas para testar a acurácia dos modelos com dados reais.',
        ],
        [
            'title' => 'Engenharia de Prompt & Guardrails de Segurança',
            'text' => 'Refinamos as instruções e adicionamos barreiras rígidas para garantir respostas 100% corretas e alinhadas ao tom da empresa.',
        ],
        [
            'title' => 'Integração em Produção & Monitoramento',
            'text' => 'Conectamos a IA aos seus canais de atendimento e sistemas ERP/CRM, acompanhando métricas de retenção e assertividade.',
        ],
    ];

    $stack = ['Google Gemini API', 'OpenAI / Claude Enterprise', 'LangChain', 'Bancos Vetoriais (pgvector)', 'Python / FastAPI', 'Laravel 13', 'Criptografia AES-256'];

    $faqs = [
        [
            'question' => 'Os dados da minha empresa serão usados para treinar modelos públicos de IA?',
            'answer' => 'Não! Utilizamos exclusivamente APIs corporativas com acordos de serviço que garantem retenção zero e isolamento estrito: nenhum dado seu ou de seus clientes é armazenado ou utilizado para retreinar modelos públicos de inteligência artificial.',
        ],
        [
            'question' => 'A IA pode "alucinar" ou inventar informações incorretas?',
            'answer' => 'Para evitar esse risco, adotamos arquitetura RAG (Retrieval-Augmented Generation) com guardrails rígidos: o modelo é instruído a responder exclusivamente com base na documentação homologada da sua empresa, informando honestamente quando não dispuser de uma informação em vez de inventar.',
        ],
        [
            'question' => 'Quanto tempo leva para colocar um agente de IA no ar?',
            'answer' => 'Uma Prova de Conceito (PoC) funcional com base na sua documentação costuma ser validada entre 2 e 3 semanas. A integração completa com WhatsApp e CRM corporativo é realizada logo em seguida em sprints ágeis.',
        ],
        [
            'question' => 'A solução funciona integrada ao WhatsApp oficial da empresa?',
            'answer' => 'Sim! Integramos com a API Oficial do WhatsApp Business (Cloud API), garantindo estabilidade, sem risco de banimento de número e com suporte a envio de mensagens ativas e transbordo humano.',
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
    cta-title="Pronto para aplicar Inteligência Artificial de forma prática e segura no seu negócio?"
    cta-text="Agende um diagnóstico com nossos engenheiros de IA e descubra os maiores pontos de retorno sobre investimento."
/>
@endsection
