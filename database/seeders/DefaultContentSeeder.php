<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Database\Seeder;

class DefaultContentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Categorias Padrão
        Category::firstOrCreate(
            ['slug' => 'sistemas-web', 'type' => 'project'],
            ['name' => 'Sistemas Web e Plataformas', 'description' => 'Projetos e plataformas corporativas desenvolvidas sob medida.']
        );

        Category::firstOrCreate(
            ['slug' => 'tecnologia-e-negocios', 'type' => 'post'],
            ['name' => 'Tecnologia & Negócios', 'description' => 'Artigos sobre inovação, automação e arquitetura de software.']
        );

        // 2. Os 6 Serviços Prioritários (Seção 5.1 e 16 da especificação)
        $servicesData = [
            [
                'title' => 'Desenvolvimento de Software Personalizado',
                'slug' => 'software-personalizado',
                'excerpt' => 'Aplicações web escaláveis e sistemas exclusivos desenhados especificamente para a regra de negócio da sua empresa.',
                'body' => "Criamos softwares corporativos modernos e seguros, focados em eliminar gargalos operacionais e preparar seu negócio para crescer com estabilidade.\n\nNossa metodologia combina arquitetura orientada a serviços, banco de dados otimizado e código limpo com testes automatizados para garantir confiabilidade de longo prazo.",
                'features' => [
                    ['title' => 'Arquitetura Sob Medida', 'text' => 'Software desenhado exatamente para o seu fluxo de trabalho, sem amarras de ferramentas prontas.'],
                    ['title' => 'APIs e Microsserviços', 'text' => 'Estrutura modular para integração facilitada com outros sistemas e alta escalabilidade.'],
                    ['title' => 'Segurança & Criptografia', 'text' => 'Controle de acesso rigoroso, auditoria de logs e conformidade total com a LGPD.'],
                ],
                'sort_order' => 1,
            ],
            [
                'title' => 'Sistemas de Gestão Empresarial (ERP)',
                'slug' => 'erp',
                'excerpt' => 'Centralize fluxos de vendas, estoque, finanças e emissão fiscal em uma plataforma integrada e inteligente.',
                'body' => "Desenvolvemos módulos ERP personalizados para automatizar a gestão de sua empresa de ponta a ponta, com dados em tempo real e relatórios executivos confiáveis.\n\nUnifique departamentos, elimine planilhas manuais e obtenha visibilidade completa de indicadores financeiros e operacionais.",
                'features' => [
                    ['title' => 'Fluxo Financeiro & DRE', 'text' => 'Controle de contas a pagar/receber, conciliação bancária e faturamento em tempo real.'],
                    ['title' => 'Emissão Fiscal Simplificada', 'text' => 'Integração para emissão ágil de NF-e, NFS-e e controle de obrigações fiscais.'],
                    ['title' => 'Estoque & Suprimentos', 'text' => 'Gestão de múltiplos depósitos, rastreamento de lotes e reposição programada.'],
                ],
                'sort_order' => 2,
            ],
            [
                'title' => 'Sites e Lojas Virtuais de Alta Performance',
                'slug' => 'sites-e-ecommerce',
                'excerpt' => 'Presença digital profissional com velocidade otimizada, design moderno e foco total em conversão de clientes.',
                'body' => "Desenvolvemos portais institucionais e plataformas de comércio eletrônico construídas com padrões modernos de UX, Core Web Vitals e conformidade WCAG AA.\n\nSua marca com apresentação impecável em qualquer dispositivo, carregamento ultra rápido e arquitetura otimizada para SEO no Google.",
                'features' => [
                    ['title' => 'Core Web Vitals Otimizados', 'text' => 'Carregamento instantâneo para melhorar sua pontuação no Google e retenção de usuários.'],
                    ['title' => 'Design Responsivo & Acessível', 'text' => 'Interfaces adaptadas para mobile e desktop com total aderência a padrões WCAG AA.'],
                    ['title' => 'Conversão & Lead Capture', 'text' => 'Formulários dinâmicos, integração com WhatsApp e funis focados em geração de oportunidades.'],
                ],
                'sort_order' => 3,
            ],
            [
                'title' => 'Inteligência Artificial Aplicada a Negócios',
                'slug' => 'inteligencia-artificial',
                'excerpt' => 'Integre modelos de IA, automação preditiva e agentes cognitivos em seus processos operacionais.',
                'body' => "Ajudamos empresas a aplicar soluções práticas de Inteligência Artificial para análise de grandes volumes de dados, atendimento automatizado e ganho significativo de produtividade.\n\nTransforme dados históricos em decisões estratégicas e delegue tarefas repetitivas para agentes inteligentes seguros.",
                'features' => [
                    ['title' => 'Agentes e Chatbots Especializados', 'text' => 'Atendimento 24/7 treinado na base de conhecimento e políticas da sua própria empresa.'],
                    ['title' => 'Análise Preditiva de Dados', 'text' => 'Identificação de padrões de compras, riscos operacionais e previsão de demanda.'],
                    ['title' => 'Processamento de Documentos', 'text' => 'Extração inteligente de dados em notas, contratos e relatórios não estruturados.'],
                ],
                'sort_order' => 4,
            ],
            [
                'title' => 'Automação de Processos e Integrações',
                'slug' => 'automacao',
                'excerpt' => 'Conecte seus sistemas existentes, elimine trabalhos manuais repetitivos e reduza custos operacionais.',
                'body' => "Construímos pipelines de integração via APIs seguras, sincronização de bancos de dados legados e automação de rotinas empresariais.\n\nFaça seus softwares conversarem entre si com consistência de dados, zero retrabalho e monitoramento de falhas em tempo real.",
                'features' => [
                    ['title' => 'Integração via Webhooks & APIs', 'text' => 'Conexão estável entre CRMs, ERPs, gateways de pagamento e plataformas externas.'],
                    ['title' => 'Sincronização Bidirecional', 'text' => 'Atualização automática de estoques, clientes e pedidos entre sistemas heterogêneos.'],
                    ['title' => 'Automação de Rotinas Repetitivas', 'text' => 'Disparos de notificações, fechamentos diários e geração de relatórios sem intervenção humana.'],
                ],
                'sort_order' => 5,
            ],
            [
                'title' => 'Aplicativos Mobile iOS e Android',
                'slug' => 'aplicativos',
                'excerpt' => 'Experiências mobile intuitivas e de alto desempenho para seus clientes e colaboradores.',
                'body' => "Desenvolvemos aplicativos móveis nativos e híbridos com design responsivo, alta segurança e integração completa com seus serviços de backend.\n\nLeve as operações críticas do seu negócio ou o autosserviço dos seus clientes diretamente para a palma da mão.",
                'features' => [
                    ['title' => 'Experiência Fluida & Offline', 'text' => 'Interfaces intuitivas com suporte a cache local e funcionamento sem conexão contínua.'],
                    ['title' => 'Notificações Push Segmentadas', 'text' => 'Engajamento direto com usuários com avisos operacionais e ofertas contextuais.'],
                    ['title' => 'Integração com Hardware', 'text' => 'Uso de câmera, GPS, biometria e Bluetooth de forma segura e performática.'],
                ],
                'sort_order' => 6,
            ],
        ];

        $serviceIds = [];
        foreach ($servicesData as $item) {
            $service = Service::updateOrCreate(
                ['slug' => $item['slug'], 'locale' => 'pt_BR'],
                [
                    'title' => $item['title'],
                    'excerpt' => $item['excerpt'],
                    'body' => $item['body'],
                    'features' => $item['features'] ?? [],
                    'sort_order' => $item['sort_order'],
                    'is_featured' => true,
                    'status' => 'published',
                    'published_at' => now(),
                    'meta_title' => $item['title'].' — DevStudio',
                    'meta_description' => $item['excerpt'],
                ]
            );
            $serviceIds[] = $service->id;
        }

        // 3. Produtos Próprios SaaS (Seção 5.5 da especificação)
        $productsData = [
            [
                'name' => 'DeskcommCRM',
                'slug' => 'deskcomm-crm',
                'tagline' => 'Gestão comercial inteligente e automação de atendimento omnichannel para vendas consultivas.',
                'description' => "O DeskcommCRM é uma plataforma SaaS desenvolvida para centralizar leads, acelerar o ciclo de vendas e integrar canais de comunicação como WhatsApp e e-mail em um único pipeline visual.\n\nCom dashboards em tempo real e automações de follow-up, sua equipe comercial foca em fechar negócios enquanto a plataforma gerencia o histórico e os lembretes de cada oportunidade.",
                'features' => [
                    ['title' => 'Pipeline Kanban Visual', 'text' => 'Gestão dinâmica de estágios com arraste fácil e indicadores de tempo por etapa.'],
                    ['title' => 'Integração Omnichannel WhatsApp', 'text' => 'Centralize conversas e histórico de mensagens direto na ficha do lead.'],
                    ['title' => 'Métricas e Previsão de Receita', 'text' => 'Relatórios automáticos de taxa de conversão, ticket médio e projeção de fechamento.'],
                ],
                'audiences' => ['Equipes de vendas B2B', 'Prestadores de serviços', 'Empresas com ciclo comercial consultivo'],
                'cta_label' => 'Solicitar Demonstração',
                'cta_url' => '/contato',
                'sort_order' => 1,
            ],
            [
                'name' => 'DevStudio ERP',
                'slug' => 'devstudio-erp',
                'tagline' => 'Sistema modular de gestão empresarial integrada para médias e grandes operações corporativas.',
                'description' => "O DevStudio ERP foi arquitetado para unificar departamentos fundamentais da empresa em uma única base de dados de alto desempenho: financeiro, fiscal, estoque, compras e vendas.\n\nConstruído com tecnologia web moderna, oferece acesso seguro de qualquer lugar, relatórios em tempo real e estabilidade operacional para suportar o crescimento do seu negócio.",
                'features' => [
                    ['title' => 'Gestão Financeira & DRE', 'text' => 'Fluxo de caixa projetado, contas a pagar/receber e conciliação bancária automatizada.'],
                    ['title' => 'Controle de Estoque & Lotes', 'text' => 'Rastreabilidade completa, inventários periódicos e reposição inteligente.'],
                    ['title' => 'Módulo Fiscal Integrado', 'text' => 'Emissão rápida de NF-e e conformidade rigorosa com normas contábeis.'],
                ],
                'audiences' => ['Indústrias e distribuidoras', 'Comércio em expansão', 'Empresas corporativas multidepartamentais'],
                'cta_label' => 'Conhecer o ERP',
                'cta_url' => '/contato',
                'sort_order' => 2,
            ],
        ];

        $productIds = [];
        foreach ($productsData as $pItem) {
            $product = Product::updateOrCreate(
                ['slug' => $pItem['slug'], 'locale' => 'pt_BR'],
                [
                    'name' => $pItem['name'],
                    'tagline' => $pItem['tagline'],
                    'description' => $pItem['description'],
                    'features' => $pItem['features'],
                    'audiences' => $pItem['audiences'],
                    'cta_label' => $pItem['cta_label'],
                    'cta_url' => $pItem['cta_url'],
                    'sort_order' => $pItem['sort_order'],
                    'is_featured' => true,
                    'status' => 'published',
                    'published_at' => now(),
                    'meta_title' => $pItem['name'].' — DevStudio',
                    'meta_description' => $pItem['tagline'],
                ]
            );
            $productIds[] = $product->id;
        }

        // 4. Página Home Completa e Estruturada (Seção 5.3 da especificação)
        $homeSections = [
            [
                'type' => 'hero',
                'data' => [
                    'title' => 'Tecnologia que transforma negócios e acelera resultados',
                    'text' => 'Desenvolvemos softwares corporativos sob medida, sistemas de gestão integrados e soluções de Inteligência Artificial para empresas que buscam eficiência operacional, estabilidade e liderança de mercado.',
                    'ctas' => [
                        ['label' => 'Fale com a DevStudio', 'url' => '/contato'],
                        ['label' => 'Conhecer Soluções', 'url' => '/solucoes'],
                    ],
                ],
            ],
            [
                'type' => 'services_grid',
                'data' => [
                    'title' => 'Soluções Especializadas',
                    'service_ids' => $serviceIds,
                ],
            ],
            [
                'type' => 'products_showcase',
                'data' => [
                    'title' => 'Produtos & Plataformas Próprias',
                    'product_ids' => $productIds,
                ],
            ],
            [
                'type' => 'features_list',
                'data' => [
                    'title' => 'Por que escolher a DevStudio?',
                    'items' => [
                        [
                            'title' => 'Arquitetura Sob Medida & Alta Disponibilidade',
                            'text' => 'Desenvolvemos sistemas dimensionados para o volume real da sua operação, com bancos de dados otimizados e zero custos com recursos desnecessários.',
                        ],
                        [
                            'title' => 'Rigor Técnico & Segurança Corporativa',
                            'text' => 'Código limpo, arquitetura escalável, testes automatizados e conformidade estrita com padrões internacionais de proteção de dados.',
                        ],
                        [
                            'title' => 'Metodologia Ágil com Entregas Contínuas',
                            'text' => 'Sprints previsíveis e alinhamentos frequentes. Você acompanha cada etapa do desenvolvimento com transparência total e feedback constante.',
                        ],
                        [
                            'title' => 'Parceria Estratégica & Suporte Contínuo',
                            'text' => 'Não encerramos o trabalho no deploy. Oferecemos sustentação ativa, monitoramento 24/7 e planos de evolução tecnológica para o seu negócio.',
                        ],
                    ],
                ],
            ],
            [
                'type' => 'faq',
                'data' => [
                    'title' => 'Dúvidas Frequentes',
                    'items' => [
                        [
                            'question' => 'Como funciona o processo de desenvolvimento de um projeto?',
                            'answer' => 'Iniciamos com uma imersão técnica para mapear as regras de negócio e os objetivos da sua empresa. A partir do diagnóstico, elaboramos a arquitetura do sistema e iniciamos sprints quinzenais de entrega, garantindo validações intermediárias até a implantação final.',
                        ],
                        [
                            'question' => 'Qual é o prazo médio para a entrega de um software sob medida?',
                            'answer' => 'O prazo varia conforme a complexidade. Aplicações modulares e MVPs estruturados costumam ser entregues entre 4 a 8 semanas, enquanto sistemas corporativos completos seguem cronogramas acordados durante a fase de planejamento técnico.',
                        ],
                        [
                            'question' => 'Vocês oferecem suporte, manutenção e evolução contínua?',
                            'answer' => 'Sim. Disponibilizamos contratos de sustentação técnica com SLAs definidos, monitoramento de servidores, rotinas automatizadas de backup e horas dedicadas para evolução contínua de funcionalidades.',
                        ],
                        [
                            'question' => 'Os sistemas desenvolvidos pertencem à minha empresa?',
                            'answer' => 'Sim, 100%. Todo o código-fonte, banco de dados e documentação gerada são de propriedade exclusiva do cliente, garantindo total liberdade e controle patrimonial sobre sua tecnologia.',
                        ],
                    ],
                ],
            ],
            [
                'type' => 'cta',
                'data' => [
                    'title' => 'Pronto para modernizar a tecnologia da sua empresa?',
                    'text' => 'Agende uma conversa técnica com nossos especialistas e receba um diagnóstico exclusivo com a melhor estratégia para o seu projeto.',
                    'label' => 'Falar com a DevStudio',
                    'url' => '/contato',
                ],
            ],
        ];

        Page::updateOrCreate(
            ['slug' => 'home', 'locale' => 'pt_BR'],
            [
                'title' => 'Início',
                'template' => 'standard',
                'excerpt' => 'Tecnologia que transforma negócios.',
                'meta_title' => 'DevStudio – Inovação Digital | Tecnologia que Transforma Negócios',
                'meta_description' => 'Desenvolvemos softwares, sistemas empresariais e soluções digitais inteligentes para empresas que desejam evoluir, automatizar processos e crescer.',
                'status' => 'published',
                'published_at' => now(),
                'sections' => $homeSections,
            ]
        );

        // 5. Página Sobre Institucional (Seção 5.2 e nova especificação institucional)
        Page::updateOrCreate(
            ['slug' => 'sobre', 'locale' => 'pt_BR'],
            [
                'title' => 'Sobre a DevStudio',
                'template' => 'standard',
                'excerpt' => 'Tecnologia que transforma negócios. Conheça nossa visão, capacidades e princípios em engenharia de software.',
                'meta_title' => 'Sobre a DevStudio — Tecnologia que Transforma Negócios',
                'meta_description' => 'A DevStudio é especializada em desenvolvimento de softwares personalizados, sistemas inteligentes, IA e automação para empresas que buscam inovar e crescer.',
                'status' => 'published',
                'published_at' => now(),
                'sections' => [
                    // Bloco 1: Hero
                    [
                        'type' => 'hero',
                        'data' => [
                            'title' => 'Tecnologia que transforma negócios.',
                            'text' => 'Unimos conhecimento técnico, criatividade e visão estratégica para transformar necessidades reais em soluções tecnológicas eficientes, funcionais e alinhadas aos objetivos de cada negócio.',
                            'ctas' => [
                                ['label' => 'Vamos conversar sobre seu projeto', 'url' => '/contato'],
                                ['label' => 'Nossa Visão & Princípios', 'url' => '#principios'],
                            ],
                        ],
                    ],
                    // Bloco 2: Apresentação Institucional e Posicionamento
                    [
                        'type' => 'text_image',
                        'data' => [
                            'title' => 'Inovação Digital com Propósito',
                            'text' => "A DevStudio – Inovação Digital é uma empresa de tecnologia especializada no desenvolvimento de soluções digitais, softwares personalizados e sistemas inteligentes para empresas que desejam evoluir, inovar e alcançar novos resultados.\n\nAcreditamos que a tecnologia deve ser mais do que uma ferramenta: deve ser um instrumento para simplificar processos, superar desafios e criar novas oportunidades de crescimento.\n\nPor isso, unimos conhecimento técnico, criatividade e visão estratégica para transformar necessidades reais em soluções tecnológicas eficientes, funcionais e alinhadas aos objetivos de cada negócio.",
                        ],
                    ],
                    // Bloco 3: O que fazemos (Texto de apresentação)
                    [
                        'type' => 'text_image',
                        'data' => [
                            'title' => 'O que fazemos',
                            'text' => "Atuamos em diferentes áreas da tecnologia, desenvolvendo soluções que conectam inovação às necessidades do mercado.\n\nCriamos sistemas personalizados, plataformas empresariais, sites, lojas virtuais e aplicativos. Também desenvolvemos soluções de Inteligência Artificial, agentes inteligentes e automações que tornam operações mais ágeis, integradas e eficientes.\n\nAlém dos projetos sob medida, investimos no desenvolvimento de produtos próprios, como plataformas SaaS e sistemas de gestão, ampliando o acesso das empresas a ferramentas que simplificam o dia a dia e apoiam sua evolução.",
                        ],
                    ],
                    // Bloco 3: O que fazemos (Cards)
                    [
                        'type' => 'services_grid',
                        'data' => [
                            'title' => 'Soluções & Capacidades Tecnológicas',
                            'service_ids' => $serviceIds,
                        ],
                    ],
                    // Bloco 4: Nossa visão sobre tecnologia
                    [
                        'type' => 'text_image',
                        'data' => [
                            'title' => 'Nossa visão sobre tecnologia',
                            'text' => "Cada empresa possui desafios, processos e objetivos diferentes. Por isso, acreditamos que boas soluções começam com a compreensão de cada negócio.\n\nNão buscamos simplesmente desenvolver software ou implementar ferramentas. Nosso propósito é identificar oportunidades, entender necessidades e aplicar a tecnologia de maneira inteligente para gerar valor.\n\nTrabalhamos com uma abordagem que combina planejamento, desenvolvimento, integração e evolução contínua, buscando entregar soluções que façam sentido tanto hoje quanto no futuro.",
                        ],
                    ],
                    // Bloco 4: Nossos princípios
                    [
                        'type' => 'features_list',
                        'data' => [
                            'title' => 'Nossos princípios',
                            'items' => [
                                [
                                    'title' => 'Inovação com propósito',
                                    'text' => 'Utilizamos a tecnologia para resolver problemas reais e criar novas possibilidades para os negócios.',
                                ],
                                [
                                    'title' => 'Soluções sob medida',
                                    'text' => 'Cada projeto deve respeitar as necessidades, os objetivos e a realidade de quem irá utilizá-lo.',
                                ],
                                [
                                    'title' => 'Qualidade e eficiência',
                                    'text' => 'Valorizamos boas práticas de desenvolvimento, organização e soluções construídas para funcionar de maneira consistente.',
                                ],
                                [
                                    'title' => 'Evolução contínua',
                                    'text' => 'A tecnologia está sempre mudando. Acompanhamos essa evolução para desenvolver soluções cada vez mais relevantes.',
                                ],
                                [
                                    'title' => 'Parceria e compromisso',
                                    'text' => 'Entendemos que cada projeto representa um objetivo importante para nossos clientes. Por isso, valorizamos a comunicação, a transparência e o comprometimento em cada etapa.',
                                ],
                            ],
                        ],
                    ],
                    // Bloco 5: Encerramento com CTA
                    [
                        'type' => 'cta',
                        'data' => [
                            'title' => 'Construindo o futuro através da tecnologia.',
                            'text' => "Acreditamos que empresas de todos os portes podem utilizar a tecnologia para trabalhar melhor, crescer e conquistar novos espaços no mercado. É com essa visão que a DevStudio desenvolve soluções digitais: aproximando inovação e negócios para transformar desafios em possibilidades.\n\nDevStudio – Inovação Digital. Tecnologia que transforma negócios.",
                            'label' => 'Vamos conversar sobre seu próximo projeto?',
                            'url' => '/contato',
                        ],
                    ],
                ],
            ]
        );
    }
}
