@extends('layouts.public')

@section('content')
@php
    $badge = 'Engenharia Sob Medida';
    $headline = 'Desenvolvimento de Software Personalizado & Corporativo';
    $valueProp = 'Construímos aplicações web escaláveis, sistemas operacionais de missão crítica e plataformas exclusivas desenhadas especificamente para as regras de negócio da sua empresa, garantindo total propriedade do código e independência de fornecedores.';

    $problems = [
        [
            'title' => 'Softwares Engessados de Prateleira',
            'text' => 'Ferramentas genéricas de mercado que forçam sua equipe a adaptar processos eficientes a limitações técnicas do sistema.',
        ],
        [
            'title' => 'Custos Crescentes com Licenças por Usuário',
            'text' => 'Modelos SaaS que penalizam o crescimento da sua empresa cobrando valores abusivos a cada novo colaborador adicionado.',
        ],
        [
            'title' => 'Insegurança & Falta de Integrações',
            'text' => 'Dificuldade crônica em integrar seus sistemas com parceiros e dependência de dados confidenciais sob controle de terceiros.',
        ],
    ];

    $deliverables = [
        [
            'icon' => 'code',
            'title' => 'Arquitetura Sob Medida & Escalável',
            'text' => 'Aplicações web corporativas projetadas para alto volume de dados e concorrência simultânea, com zero lentidão.',
            'highlights' => ['Design orientado a domínio (DDD)', 'Banco de dados otimizado com índices refinados', 'Containers Docker prontos para nuvem'],
        ],
        [
            'icon' => 'api',
            'title' => 'APIs RESTful & Microsserviços',
            'text' => 'Camada de comunicação padronizada para conectar com CRMs, ERPs, gateways de pagamento e serviços de logística.',
            'highlights' => ['Documentação OpenAPI / Swagger', 'Autenticação via OAuth2 / JWT', 'Controle rigoroso de rate limit e cache'],
        ],
        [
            'icon' => 'shield',
            'title' => 'Segurança Corporativa & LGPD',
            'text' => 'Controle de acesso granular baseado em funções (RBAC), criptografia de dados em repouso e trilha de auditoria completa.',
            'highlights' => ['Trilha de logs auditáveis para compliance', 'Proteção contra injeções SQL e vulnerabilidades OWASP', 'Isolamento estrito de permissões por setor'],
        ],
        [
            'icon' => 'dashboard',
            'title' => 'Painéis Administrativos Intuitivos',
            'text' => 'Interfaces operacionais modernas, desenhadas para garantir alta velocidade de navegação e mínima curva de aprendizado.',
            'highlights' => ['Filtros avançados e exportações rápidas', 'Dashboards executivos com indicadores-chave', 'Design responsivo para desktop e tablet'],
        ],
    ];

    $useCases = [
        [
            'title' => 'Portais Corporativos & Autoatendimento B2B',
            'text' => 'Plataformas exclusivas para clientes e parceiros realizarem pedidos, aprovarem orçamentos e acompanharem faturas em tempo real.',
        ],
        [
            'title' => 'Sistemas de Chão de Fábrica & Logística',
            'text' => 'Softwares para monitoramento de etapas de produção, controle de lotes, expedição e conferência rápida de mercadorias.',
        ],
        [
            'title' => 'Plataformas Financeiras & Cobrança Customizada',
            'text' => 'Sistemas com fluxos específicos de comissões, faturamento por medição e conciliação bancária automática com múltiplos bancos.',
        ],
    ];

    $process = [
        [
            'title' => 'Imersão Técnica & Diagnóstico',
            'text' => 'Mapeamos suas regras de negócio, fluxos de trabalho e restrições para definir o escopo exato do projeto sem desperdício.',
        ],
        [
            'title' => 'Arquitetura & Prototipação UI/UX',
            'text' => 'Desenhamos a modelagem de dados e os protótipos visuais de todas as telas principais para validação prévia com sua equipe.',
        ],
        [
            'title' => 'Sprints Ágeis & Entregas Contínuas',
            'text' => 'Ciclos de programação quinzenais com testes automatizados, permitindo que você valide o progresso em ambiente de homologação.',
        ],
        [
            'title' => 'Implantação Segura & Sustentação Ativa',
            'text' => 'Deploy assistido em produção, treinamento das equipes e suporte contínuo para evolução contínua da ferramenta.',
        ],
    ];

    $stack = ['Laravel 13', 'PHP 8.5', 'PostgreSQL / MySQL', 'Livewire 4', 'Tailwind CSS 4', 'Docker', 'Redis', 'AWS Cloud'];

    $faqs = [
        [
            'question' => 'O código-fonte do software será de propriedade exclusiva da minha empresa?',
            'answer' => 'Sim. Todo o código-fonte, banco de dados, documentação técnica e propriedade intelectual pertencem 100% à sua empresa após a quitação das etapas do projeto, sem nenhuma cobrança oculta de licenciamento.',
        ],
        [
            'question' => 'Qual é o prazo médio para o desenvolvimento de um software sob medida?',
            'answer' => 'O prazo depende da complexidade do escopo. Projetos modulares menores costumam ter sua primeira versão funcional entregue entre 4 e 8 semanas, enquanto sistemas corporativos complexos são desenvolvidos em sprints quinzenais contínuas.',
        ],
        [
            'question' => 'Como funciona o suporte técnico após o lançamento em produção?',
            'answer' => 'Oferecemos garantia contratual contra defeitos (bugs) em todas as entregas e planos mensais de sustentação técnica ativa (SLA), cobrindo monitoramento de infraestrutura, atualizações de segurança e evolução de novas funcionalidades.',
        ],
        [
            'question' => 'É possível integrar o novo software com os sistemas legados que já utilizamos?',
            'answer' => 'Com certeza. Desenhamos APIs e conectores para sincronizar dados com sistemas legados, ERPs, CRMs, ferramentas contábeis e gateways de pagamento sem interromper as operações do seu dia a dia.',
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
    cta-title="Pronto para construir o software sob medida da sua empresa?"
    cta-text="Agende uma conversa técnica com nossos engenheiros de software para discutir requisitos, viabilidade e investimento."
/>
@endsection
