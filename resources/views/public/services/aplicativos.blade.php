@extends('layouts.public')

@section('content')
@php
    $badge = 'Mobilidade & Experiência Mobile';
    $headline = 'Aplicativos Mobile Nativos & Híbridos (iOS e Android)';
    $valueProp = 'Desenvolvemos aplicativos móveis de alto desempenho para iOS e Android, com interfaces intuitivas, funcionamento offline inteligente, notificações push contextuais e publicação homologada na Apple App Store e Google Play Store.';

    $problems = [
        [
            'title' => 'Equipes Externas Reféns do Papel e WhatsApp',
            'text' => 'Representantes e técnicos em campo sem acesso instantâneo a estoques, preços e históricos de clientes, gerando lentidão e perda de vendas.',
        ],
        [
            'title' => 'Aplicativos que Travam sem Conexão à Internet',
            'text' => 'Sistemas mal projetados que travam completamente ou perdem dados digitados quando a conexão 4G/5G oscila em trânsito ou no cliente.',
        ],
        [
            'title' => 'Rejeição e Baixa Avaliação nas Lojas de Apps',
            'text' => 'Apps com bugs frequentes, consumo excessivo de bateria e interfaces confusas que recebem avaliações negativas e prejudicam a reputação corporativa.',
        ],
    ];

    $deliverables = [
        [
            'icon' => 'mobile',
            'title' => 'Aplicativos Fluidos a 60 FPS (iOS & Android)',
            'text' => 'Desenvolvimento em Flutter e React Native compartilhando regras de negócio com compilação para código nativo e animações suaves.',
            'highlights' => ['Design System consistente em iPhone e Android', 'Transições de tela instantâneas', 'Baixo consumo de bateria e memória RAM'],
        ],
        [
            'icon' => 'offline',
            'title' => 'Arquitetura Offline-First com Sincronização',
            'text' => 'Armazenamento local seguro no banco SQLite do dispositivo: seus colaboradores continuam trabalhando normalmente sem sinal de internet.',
            'highlights' => ['Sincronização em segundo plano ao reconectar', 'Fila local de pedidos e apontamentos', 'Controle automático de concorrência de dados'],
        ],
        [
            'icon' => 'push',
            'title' => 'Notificações Push Segmentadas & Alertas',
            'text' => 'Canal direto de comunicação com clientes e colaboradores com suporte a mensagens transacionais, avisos de faturamento e ofertas personalizadas.',
            'highlights' => ['Integração com Firebase Cloud Messaging (FCM)', 'Segmentação por perfil de usuário ou filial', 'Links profundos (Deep Linking) para telas específicas'],
        ],
        [
            'icon' => 'hardware',
            'title' => 'Integração com Câmera, GPS & Biometria',
            'text' => 'Aproveitamento total dos sensores dos smartphones para acelerar coletas de dados em campo e garantir máxima segurança de acesso.',
            'highlights' => ['Login por biometria facial (Face ID) ou digital', 'Leitura rápida de código de barras e QR Codes com a câmera', 'Roteirização e geolocalização de equipes por GPS'],
        ],
    ];

    $useCases = [
        [
            'title' => 'Força de Vendas & Emissão de Pedidos em Campo',
            'text' => 'Aplicativo para representantes comerciais consultarem estoque em tempo real, calcularem descontos autorizados e fecharem pedidos no cliente.',
        ],
        [
            'title' => 'Portal de Autoatendimento para Clientes (App do Cliente)',
            'text' => 'Aplicativo exclusivo onde seus clientes consultam faturas, solicitam suporte técnico, acompanham entregas e compram com um toque.',
        ],
        [
            'title' => 'Checklists Operacionais, Vistorias & Auditorias',
            'text' => 'App para engenheiros e técnicos realizarem laudos em campo, com captura de fotos com carimbo de data/hora e assinatura digital do cliente.',
        ],
    ];

    $process = [
        [
            'title' => 'Prototipação UI/UX no Figma',
            'text' => 'Desenhamos telas interativas otimizadas para usabilidade mobile (ergonomia de toque com uma mão) e validamos com seu time.',
        ],
        [
            'title' => 'Arquitetura de APIs & Banco Local',
            'text' => 'Modelamos os endpoints de comunicação com seu backend e estruturamos o banco de dados SQLite offline do aparelho.',
        ],
        [
            'title' => 'Desenvolvimento & Testes em Aparelhos Reais',
            'text' => 'Testamos o aplicativo em dezenas de modelos de smartphones (iOS e Android) com diferentes versões de sistema operacional.',
        ],
        [
            'title' => 'Publicação nas Lojas & Homologação',
            'text' => 'Conduzimos todo o processo de submissão nas lojas da Apple e Google até a aprovação definitiva e disponibilização para download.',
        ],
    ];

    $stack = ['Flutter / React Native', 'iOS (Swift) / Android (Kotlin)', 'Laravel REST APIs', 'Firebase Push', 'SQLite Local Cache', 'Biometria FaceID / TouchID'];

    $faqs = [
        [
            'question' => 'Vocês cuidam de toda a publicação e aprovação na Apple App Store e Google Play Store?',
            'answer' => 'Sim! Cuidamos de todo o processo burocrático e técnico: criação das contas de desenvolvedor, elaboração de screenshots normativos, redação da política de privacidade e resposta a eventuais revisores das lojas até a aprovação pública.',
        ],
        [
            'question' => 'O aplicativo funciona mesmo se o colaborador estiver sem sinal de internet?',
            'answer' => 'Sim! Projetamos o aplicativo com arquitetura Offline-First. Todas as consultas e novos cadastros são armazenados no banco de dados local do smartphone e sincronizados automaticamente com o servidor central assim que uma conexão de internet for detectada.',
        ],
        [
            'question' => 'Um único código atende tanto iPhone (iOS) quanto smartphones Android?',
            'answer' => 'Sim! Com o uso de tecnologias consolidadas como Flutter e React Native, compartilhamos toda a lógica de negócios e regras do sistema, gerando binários compilados nativamente para iOS e Android com desempenho de alto nível e custo reduzido.',
        ],
        [
            'question' => 'Como funcionam as atualizações do aplicativo no futuro?',
            'answer' => 'Oferecemos suporte técnico contínuo para manter o aplicativo sempre atualizado com as exigências anuais de novas versões do iOS e Android, além de publicar novas funcionalidades e melhorias de forma ágil.',
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
    cta-title="Pronto para criar o aplicativo mobile da sua empresa?"
    cta-text="Converse com nossos especialistas em desenvolvimento mobile e transforme sua ideia em um app veloz e de alto impacto."
/>
@endsection
