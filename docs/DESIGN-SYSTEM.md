# Design system — DevStudio

> **Adendo 12/11/2026:** o fundador declarou concluído o checklist Plesk e autorizou desenvolvimento local, conforme informado por Nexus; Engenharia não verificou a auditoria. Este adendo prevalece sobre as referências históricas ao bloqueio de implementação, sem autorizar produção. Ver [Deploy](DEPLOYMENT.md).

## Escopo e rastreabilidade

Documento de planejamento, não interface implementada nem sistema aprovado. Base: [especificação técnica](../ESPECIFICACAO_TECNICA_DEVSTUDIO_WEBSITE.md), especialmente §§ 5, 8–10, 11.4, 15 e 16. A base do projeto está aprovada, condicionada à auditoria final do Plesk; **a implementação aguarda essa auditoria**. Itens abaixo distinguem requisitos da fonte, propostas e decisões pendentes. Não houve auditoria de acessibilidade, teste visual ou homologação.

Planejamento relacionado: [deploy e hospedagem](DEPLOYMENT.md).

## Direção e cores — requisitos da fonte (§ 9.1–9.3)

Identidade atual modernizada: premium, minimalista, tecnológica e profissional. Vermelho como assinatura, com parcimônia, sobre fundos neutros claros e escuros. Evitar neon excessivo, futurismo genérico, gradientes generalizados e animações que prejudiquem desempenho.

| Token inicial da fonte | Valor | Uso indicado na fonte |
|---|---|---|
| `brand-500` | `#D10047` | Identidade, links e destaques |
| `brand-600` | `#C8104A` | Botões e interações |
| `brand-700` | `#A80D3D` | Hover e contraste mais forte |
| `burgundy` | `#851337` | Degradês e detalhes escuros |
| `graphite` | `#111315` | Fundos escuros |
| `surface-dark` | `#17191E` | Cards escuros |
| `surface-light` | `#F6F6F7` | Fundos claros |
| `text-primary` | `#17171B` | Texto em fundo claro |
| `text-muted` | `#737782` | Texto secundário |
| `border` | `#E5E7EB` | Bordas claras |

São cores iniciais aprovadas como direção, não combinações de contraste certificadas. Validar cada par real em todos os estados e fundos: texto normal ≥ 4,5:1, texto grande ≥ 3:1, e componentes gráficos e indicadores de interface relevantes ≥ 3:1. Não usar vermelho em texto pequeno sobre fundo escuro sem medição. Ajustar aplicações ou propor cores adicionais para aprovação quando necessário; não depender apenas da cor para transmitir significado.

## Marca — inventário e regras (§ 9.2)

Inventário local por nome e metadados PNG, sem modificar originais:

| Arquivo | Dimensões | Uso indicado na fonte |
|---|---|---|
| `logo/logo_devstudio_dark_300x60.png` | 300 × 60, RGBA | Wordmark escuro em fundo claro |
| `logo/logo_devstudio_light_300x60.png` | 300 × 60, RGBA | Wordmark branco em fundo escuro |
| `logo/Icone2.png` | 529 × 529, RGBA | Símbolo isolado |

`logo/Icone3(2).png`, composição vertical citada na fonte, **não foi encontrado** no inventário; solicitar o original antes de usá-lo. RGBA indica canal alfa, não valida sozinho aparência ou contraste. Preservar proporções, transparência e originais; não redesenhar nem distorcer. Escolher a versão conforme o fundo e verificar legibilidade em tamanho real; não presumir margem de respiro ou tamanho mínimo oficiais. Pedir arquivos vetoriais provenientes da fonte original, se disponíveis; não converter ou redesenhar como substituto sem aprovação. Alt da marca vinculada à home deve identificar o destino; imagem decorativa repetida deve ter alt vazio.

## Tipografia, espaçamento e formas

**Fonte (§ 9.4):** o fundador escolheu Sora para títulos; texto em Inter conforme a especificação. Pesos e escala tipográfica final ainda estão pendentes. Limitar famílias e pesos; preferir arquivos locais otimizados quando licenciados, senão fonte web com política de carregamento adequada. Confirmar licença, origem e permissão de hospedagem antes de usar qualquer arquivo; nenhuma licença ou arquivo de fonte foi verificado.

**Aprovado provisoriamente pelo fundador:** escala de espaçamento em múltiplos de `4px` (4, 8, 16, 24, 32, 48, 64) e raios de `4px`, `8px` e `16px` para controles, cards e superfícies maiores. **Ainda provisórios:** texto base `1rem` com entrelinha `1.5` e escala fluida dos títulos. Validar legibilidade, zoom e densidade em protótipos antes de fixar esses valores. Bordas e superfícies devem separar conteúdo sem depender exclusivamente de sombras.

Antes de construir UI, manter o `DESIGN.md` na raiz como fonte legível por máquina para os tokens e sua rationale. Ele foi criado como especificação normativa inicial, com decisões provisórias e pendências descritas no próprio documento; não significa que a UI esteja aprovada ou implementada.

## Componentes e estados (§§ 5, 8, 9.5, 10)

Padrões abaixo combinam orientação e componentes Blade iniciados em T2.05; aprovação visual, validação completa de contraste dos estados e conteúdo/call sites permanecem pendentes.

| Componente | Comportamento e estados a especificar e validar |
|---|---|
| Header e navegação | Variantes claras/escuras com logo horizontal apropriada; links apenas para Soluções, Produtos, Projetos, Sobre e Insights, todos com rotas existentes. CTA de contato permanece pendente de T4.07. Navegação responsiva e operável por teclado, com foco visível e rota atual identificada. |
| Footer | Identidade textual DevStudio e a mesma navegação aprovada; sem contatos, redes ou links institucionais ainda não aprovados. Logo vertical segue pendente de T2.01. |
| Botões e links | Blade `x-button`: variantes primária/secundária/link; link gera âncora, ação gera botão; repouso, hover, foco global visível, ativo e disabled nativo. Carregamento omitido até haver callsite que o use. |
| Cards de serviço, produto e projeto | Blade `x-card` envolve conteúdo em `article` com borda/superfície; listagens públicas usam o componente. Sem imagens ou copy inventada. |
| Breadcrumbs | Blade `x-breadcrumbs` usa `nav`/`ol` com item atual identificado; aplicado em páginas e detalhes existentes. |
| Banner CTA | Blade `x-cta-banner` recebe título, texto e URL/rótulo do callsite; aplicado somente ao bloco CTA existente, sem presumir rota de contato. |
| Badge | Blade `x-badge` para rótulo fornecido pelo callsite; sem status fictício.
| Formulário comercial | Labels explícitos; nome, e-mail, tipo de solução, necessidade e aceite de privacidade obrigatórios; empresa e telefone opcionais. Associar ajuda e erros aos campos; manter dados após erro; oferecer confirmação clara após registro. A falha de notificação não deve apagar lead salvo. |
| FAQ | Exibir somente conteúdo aprovado; se expansível, usar controles com estado anunciado e operação por teclado. |
| Listagens e conteúdo | Carregamento anunciado quando houver espera; vazio com orientação pertinente, sem inventar dados; erro recuperável com instrução clara. Omitir seção de cases sem projetos autorizados. |
| 404 | Explicar que a página não foi encontrada e oferecer caminho de volta à navegação. |

CTA de WhatsApp depende de número e mensagem contextual configurados no CMS; não presumir contato ou integração de API. A Home usa seções predefinidas gerenciáveis, não um construtor irrestrito. Apenas conteúdo publicado e autorizado aparece publicamente.

## Responsividade e acessibilidade (§§ 9.6, 11.4, 15.2)

- Mobile-first; adaptar navegação, colunas, imagens e formulários ao espaço disponível, sem cortes ou rolagem horizontal desnecessária. Breakpoints são decisão posterior, guiada pelo conteúdo; validar mobile, tablet e desktop, inclusive zoom de 200% e reflow em largura equivalente a 320 CSS px.
- Meta: WCAG 2.2 AA nos componentes principais, conforme fonte. Usar estrutura semântica, ordem de leitura lógica, títulos coerentes, labels, mensagens de erro e alternativas textuais adequadas. Links distinguíveis e nomes acessíveis descritivos.
- Operar tudo por teclado, sem armadilhas; foco visível e não encoberto por elementos fixos. Testar indicadores de foco e alvos de toque conforme critérios aplicáveis da WCAG 2.2 AA; não prometer conformidade sem auditoria.
- Respeitar `prefers-reduced-motion`; transições não devem impedir uso nem ser necessárias para compreender o conteúdo. Otimizar imagens e fontes e evitar JavaScript pesado.
- Homologação posterior: testar teclado, leitor de tela, estados de formulário, contrastes efetivos, zoom/reflow, orientação, toque, movimento reduzido e navegadores/dispositivos representativos; registrar evidências e corrigir falhas antes de aprovação independente.

## Pendências e limites

- Auditoria Plesk antes de integrar/publicar em hospedagem; não realizada. O trabalho local está autorizado e não depende dessa auditoria.
- Confirmar arquivo vertical da marca e licenças das fontes; validar escala tipográfica final, variantes claras/escuras e composições de contraste em protótipo. Espaçamento e raios foram aprovados provisoriamente pelo fundador.
- Confirmar domínio, contatos, imagens, produtos, cases e textos autorizados; política de privacidade e retenção dependem de revisão e decisão. SMTP e banco exato seguem pendentes.
- Nenhuma interface ou fonte foi implementada; o cálculo sRGB isolado não valida cores em componentes/estados reais. Este documento não representa aprovação visual nem conformidade WCAG.
