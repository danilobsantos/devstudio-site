@extends('layouts.public')

@section('content')
@php
    $badge = 'Automação & Eficiência Operacional';
    $headline = 'Automação de Processos e Integrações de Sistemas';
    $valueProp = 'Elimine retrabalho manual, conecte sistemas legados via APIs seguras e crie fluxos automáticos de sincronização de dados que funcionam 24/7 com máxima confiabilidade e monitoramento de falhas em tempo real.';

    $problems = [
        [
            'title' => 'Digitação Repetitiva Entre Sistemas Diferentes',
            'text' => 'Colaboradores gastando horas do expediente copiando dados de vendas do e-commerce para o ERP, ou cadastrando leads do WhatsApp manualmente no CRM.',
        ],
        [
            'title' => 'Falhas Silenciosas de Sincronização',
            'text' => 'Trocas de arquivos em Excel que esquecem pedidos, vendem produtos sem estoque disponível e geram conflitos graves de entrega com clientes.',
        ],
        [
            'title' => 'Falta de Visibilidade de Erros Operacionais',
            'text' => 'Descobrir que um pagamento não foi baixado ou que uma nota fiscal não foi emitida somente após a reclamação indignada do cliente.',
        ],
    ];

    $deliverables = [
        [
            'icon' => 'zap',
            'title' => 'Pipelines de Integração via APIs & Webhooks',
            'text' => 'Comunicação automática entre plataformas heterogêneas utilizando filas assíncronas com reprocessamento inteligente de falhas.',
            'highlights' => ['Suporte a APIs RESTful, GraphQL, SOAP e Webhooks', 'Mapeamento e transformação inteligente de estruturas JSON/XML', 'Autenticação segura via tokens e chaves criptografadas'],
        ],
        [
            'icon' => 'sync',
            'title' => 'Sincronização Bidirecional em Tempo Real',
            'text' => 'Atualização imediata de saldos de estoque, status de pedidos, cadastros de clientes e faturas entre todas as suas pontas de venda.',
            'highlights' => ['Resolução inteligente de conflitos de concorrência', 'Registro histórico de cada transação sincronizada', 'Zero duplicidade de registros cadastrais'],
        ],
        [
            'icon' => 'pulse',
            'title' => 'Monitoramento Ativo & Alertas Imediatos',
            'text' => 'Painel de controle visual da saúde das integrações com notificações instantâneas via WhatsApp, Slack ou E-mail quando um serviço terceiro oscilar.',
            'highlights' => ['Detecção precoce de indisponibilidade de parceiros', 'Fila de reprocessamento (Dead Letter Queue)', 'Logs detalhados para rápida atuação técnica'],
        ],
        [
            'icon' => 'routine',
            'title' => 'Automação de Rotinas Financeiras & Administrativas',
            'text' => 'Execução automática de conciliações bancárias noturnas, disparo de réguas de cobrança por WhatsApp e geração periódica de relatórios executivos.',
            'highlights' => ['Emissão automática de contratos digitais', 'Disparo de boletos e cobranças Pix recorrentes', 'Integração contábil com fechamento mensal automático'],
        ],
    ];

    $useCases = [
        [
            'title' => 'Integração E-commerce ↔ ERP ↔ Logística',
            'text' => 'Venda realizada no site é enviada ao ERP, gera nota fiscal e etiqueta de frete na transportadora em segundos sem toque humano.',
        ],
        [
            'title' => 'Automação de Assinatura Eletrônica de Contratos',
            'text' => 'Proposta comercial aceita no CRM gera automaticamente o contrato em PDF e dispara para as assinaturas no Clicksign ou DocuSign.',
        ],
        [
            'title' => 'Conciliação Automática de Maquininhas & Bancos',
            'text' => 'Confronto diário de vendas por cartão, extratos bancários e taxas de adquirentes com conciliação contábil direta no ERP.',
        ],
    ];

    $process = [
        [
            'title' => 'Mapeamento de Fluxos & Dicionário de Dados',
            'text' => 'Identificamos todos os sistemas participantes, endpoints de comunicação e regras de equivalência de dados entre eles.',
        ],
        [
            'title' => 'Arquitetura com Filas & Políticas de Retentativa',
            'text' => 'Projetamos a esteira com Redis e filas assíncronas para garantir que nenhuma mensagem seja perdida caso um serviço caia.',
        ],
        [
            'title' => 'Desenvolvimento com Tratamento Rigoroso de Erros',
            'text' => 'Construímos os conectores e testes automatizados de estresse simulando quedas de internet e respostas lentas de terceiros.',
        ],
        [
            'title' => 'Homologação Segura em Sandbox & Go-Live',
            'text' => 'Executamos testes com cargas reais de dados em ambiente espelho antes de liberar o fluxo para a operação definitiva.',
        ],
    ];

    $stack = ['Laravel Queues', 'Redis', 'Webhooks Seguros', 'Guzzle HTTP Client', 'Supervisor', 'PostgreSQL / MySQL', 'OpenAPI / Swagger'];

    $faqs = [
        [
            'question' => 'O que acontece quando um dos serviços externos (ex: gateway de pagamento) fica fora do ar?',
            'answer' => 'Nossa arquitetura conta com gerenciamento de filas assíncronas e política de retry exponencial (retentativas com intervalo progressivo): os eventos são armazenados com segurança e reprocessados automaticamente assim que o serviço externo restabelece sua conexão, sem perda de dados.',
        ],
        [
            'question' => 'É possível integrar sistemas legados antigos que não possuem documentação de API?',
            'answer' => 'Sim! Possuímos ampla experiência em construir conectores para sistemas legados, seja através de conexões diretas controladas aos bancos de dados, leitura automatizada de arquivos em pastas seguras (SFTP) ou APIs intermediárias.',
        ],
        [
            'question' => 'Como ficamos sabendo se uma integração apresentar instabilidade?',
            'answer' => 'Configuramos monitoramento contínuo em tempo real que envia alertas instantâneos para os canais de comunicação da sua equipe (WhatsApp, Slack ou E-mail) detalhando o motivo do alerta e orientações técnicas de resolução.',
        ],
        [
            'question' => 'A automação reduz custos com equipe?',
            'answer' => 'Sim, substancialmente. Ao eliminar tarefas de digitação mecânica e transferências manuais de arquivos, sua equipe pode ser redirecionada para atividades analíticas e comerciais que realmente geram receita.',
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
    cta-title="Pronto para automatizar os fluxos da sua empresa e eliminar erros manuais?"
    cta-text="Converse com nossos especialistas em integrações para planejar uma arquitetura rápida e resiliente para seus sistemas."
/>
@endsection
