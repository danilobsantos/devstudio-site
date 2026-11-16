# DevStudio — Especificação Técnica e Plano de Implementação

**Documento:** Especificação técnica e funcional (PRD + Architecture)  
**Versão:** 1.0  
**Data:** 12/11/2026  
**Status:** Base aprovada para início da implementação, condicionada à auditoria final da hospedagem Plesk.

---

## 1. Visão do projeto

Construir o novo site institucional da **DevStudio – Inovação Digital** como uma plataforma comercial e institucional, com CMS próprio para gerenciamento de conteúdo, apresentação independente dos produtos SaaS e captação centralizada de oportunidades comerciais.

O site deve posicionar a DevStudio como uma empresa de tecnologia que desenvolve software, sistemas empresariais, sites, e-commerce, aplicativos, inteligência artificial e automações. A comunicação deve vender resultados e soluções para negócios, não apenas tecnologias ou horas de desenvolvimento.

### 1.1 Objetivos

1. Fortalecer a marca e a autoridade da DevStudio.
2. Gerar leads para projetos de tecnologia.
3. Apresentar os produtos próprios da empresa em páginas comerciais individuais.
4. Publicar projetos, cases e conteúdos para apoiar vendas e SEO.
5. Permitir que a equipe gerencie o conteúdo sem alterar código.
6. Preparar a estrutura para adicionar outros idiomas futuramente.

### 1.2 Públicos

- Pequenas e médias empresas que precisam digitalizar ou automatizar operações.
- Empresas maiores com demandas de software, integração e modernização.
- Empreendedores que pretendem desenvolver produtos digitais.
- Empresas que procuram soluções SaaS prontas da DevStudio.

### 1.3 Posicionamento e mensagem central

**Posicionamento:** empresa de tecnologia e desenvolvimento de soluções digitais.

**Assinatura recomendada:** “Tecnologia que transforma negócios.”

**Mensagem institucional:** “Desenvolvemos softwares, sistemas empresariais e soluções digitais inteligentes para empresas que desejam evoluir, automatizar processos e crescer por meio da tecnologia.”

### 1.4 Indicadores de sucesso

- Leads recebidos pelo formulário e WhatsApp.
- Leads por serviço, página de origem e campanha UTM.
- Cliques nos CTAs dos produtos.
- Visualizações das páginas de serviços, produtos e cases.
- Artigos publicados e tráfego orgânico.
- Conversões de páginas de serviço e produto.

Não inventar números de clientes, anos de mercado, resultados, depoimentos ou métricas de projetos. Publicar somente informações confirmadas e autorizadas.

---

## 2. Decisões consolidadas

| Tema | Decisão |
|---|---|
| Marca | DevStudio – Inovação Digital |
| Posicionamento | Empresa de tecnologia |
| Público | Pequenas, médias e grandes empresas; empreendedores |
| Objetivos | Autoridade, leads e divulgação de produtos próprios |
| Identidade | Manter a identidade atual e modernizar sua aplicação digital |
| Produtos | Páginas comerciais individuais dentro do site institucional; aplicações SaaS independentes |
| CMS | Filament |
| Idiomas | Português inicialmente; arquitetura preparada para expansão |
| Conversão | WhatsApp e formulário comercial |
| Hospedagem | Plesk |
| PHP disponível | 8.5, sujeito à validação do PHP CLI e extensões |
| Banco | MySQL ou MariaDB disponível na hospedagem |
| Deploy | Aplicação PHP tradicional; Docker não é requisito de produção |
| Redis | Fora do escopo inicial |

---

## 3. Arquitetura tecnológica

### 3.1 Stack aprovada

| Camada | Tecnologia |
|---|---|
| Runtime | PHP 8.5 |
| Framework | Laravel 13 |
| Painel/CMS | Filament 5 |
| Frontend público | Blade |
| Interatividade | Livewire 4 e Alpine.js quando necessário |
| CSS | Tailwind CSS 4.1+ |
| Build de assets | Vite |
| Persistência | MySQL ou MariaDB |
| ORM | Eloquent |
| Validação | Form Requests e validação do Laravel |
| Autorização | Policies, Gates e papéis administrativos |
| Testes | Pest ou PHPUnit |
| Controle de versão | Git |
| Hospedagem | Plesk com PHP-FPM, quando disponível |

As versões exatas dos pacotes devem ser resolvidas pelo Composer no momento da criação do projeto e fixadas em `composer.lock`. Não usar dependências `dev` em produção.

### 3.2 Justificativa

A aplicação será um monólito Laravel modular. O site público, painel administrativo, regras de negócio, formulários e integrações serão mantidos no mesmo repositório e ciclo de deploy.

- Blade renderiza páginas públicas no servidor.
- Livewire fornece interatividade pontual, como filtros e formulários dinâmicos.
- Filament fornece o CMS administrativo.
- Eloquent centraliza persistência e relacionamentos.
- Vite compila CSS e JavaScript para arquivos estáticos.

Não criar frontend Next.js/React separado, API interna independente, microsserviços ou containers em produção para esta primeira versão.

### 3.3 Banco de dados

Preferência por MySQL/MariaDB já oferecido pela hospedagem Plesk, para evitar infraestrutura adicional. Confirmar a versão exata antes de fechar migrations e ambiente de produção.

Configuração recomendada:

- Charset: `utf8mb4`.
- Collation: compatível com o banco instalado, preferencialmente `utf8mb4_unicode_ci` ou equivalente suportado.
- Usuário exclusivo para a aplicação.
- Privilégios limitados ao banco do site.
- Backup diário do banco.

Não usar o usuário `root` da instância na aplicação.

### 3.4 Cache, sessão e filas

Para o primeiro lançamento:

- Cache: driver `file` ou `database`, conforme a hospedagem.
- Sessões: `file`.
- Fila: `sync` inicialmente.
- Rate limiting: cache persistente disponível no Laravel; validar comportamento no ambiente Plesk.
- Redis: não instalar ou configurar nesta fase.

A persistência de leads deve ocorrer antes do envio de e-mail. Uma falha de e-mail não pode apagar ou impedir o registro do lead.

Se houver necessidade posterior de processamento assíncrono, avaliar fila `database` e execução periódica por tarefa agendada no Plesk. Não assumir disponibilidade de processo worker permanente em hospedagem compartilhada.

### 3.5 Armazenamento de arquivos

Usar o filesystem do Laravel, inicialmente no disco local `public` para imagens destinadas ao site.

- Uploads pelo Filament devem validar extensão, MIME type e tamanho.
- Arquivos públicos devem ser servidos a partir do disco público.
- Arquivos privados, caso existam, não devem ser colocados no diretório público.
- Executar `php artisan storage:link` se o ambiente permitir symlinks.
- Fazer backup do storage junto com o banco.

Não instalar MinIO ou storage S3 nesta primeira versão. A abstração de filesystem do Laravel permitirá essa troca futuramente.

---

## 4. Hospedagem Plesk: requisitos e auditoria obrigatória

A indicação de PHP 8.5 no painel precisa ser conferida com o PHP usado pelo terminal/Composer. O PHP-FPM do domínio e o PHP CLI podem apontar para binários diferentes.

### 4.1 Checklist técnico inicial

- [ ] Confirmar PHP 8.5 nas configurações do domínio.
- [ ] Confirmar PHP CLI 8.5 por SSH ou terminal do Plesk.
- [ ] Confirmar Composer 2 disponível.
- [ ] Confirmar acesso SSH ou alternativa para executar comandos Artisan.
- [ ] Confirmar extensão `pdo_mysql`.
- [ ] Confirmar extensões exigidas pelo Laravel: Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, Session, Tokenizer e XML.
- [ ] Confirmar `intl`, necessário pelo Filament.
- [ ] Confirmar `mbstring`, `openssl`, `fileinfo`, `ctype`, `curl`, `dom`, `xml`, `tokenizer`, `pdo` e `pdo_mysql`.
- [ ] Confirmar suporte a Composer e instalação de dependências sem extensões ausentes.
- [ ] Confirmar versão MySQL/MariaDB.
- [ ] Confirmar possibilidade de configurar o Document Root para a pasta `public` da aplicação.
- [ ] Confirmar permissões de escrita em `storage` e `bootstrap/cache`.
- [ ] Confirmar cron/tarefas agendadas no Plesk.
- [ ] Confirmar limites de upload e memória PHP.
- [ ] Confirmar SSL/HTTPS.

### 4.2 Handler PHP

Preferir PHP-FPM/FPM Application se estiver disponível e for compatível com a configuração Apache/Nginx do domínio. Manter OPcache habilitado quando a hospedagem oferecer essa opção.

Não alterar limites de PHP sem necessidade. Como ponto inicial para avaliação, considerar `memory_limit` de 256 MB e limites de upload coerentes com as imagens do CMS; ajustar conforme o plano de hospedagem e testes reais.

### 4.3 Document Root e segurança

A raiz pública do domínio deve apontar exclusivamente para a pasta `public` do Laravel.

Estrutura preferencial:

```text
<raiz-da-assinatura>/
├── devstudio-app/          # código privado da aplicação
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── storage/
│   ├── vendor/
│   └── public/             # único diretório exposto
└── outros-arquivos/
```

No Plesk, configurar o Document Root do domínio para `devstudio-app/public`, se a hospedagem permitir esse caminho. Caso o provedor não permita Document Root personalizado, solicitar ajuste ao suporte ou definir uma estrutura equivalente em que todo o código Laravel fique fora do diretório público e apenas o conteúdo de `public` seja exposto.

**Proibido:** apontar o domínio para a raiz completa do Laravel, copiar `.env`, `vendor`, `storage`, `app` ou `bootstrap` para uma pasta publicamente acessível, ou mover `index.php` para a raiz do projeto.

### 4.4 Comandos de auditoria

Executar por SSH/terminal, adaptando o caminho do binário PHP ao Plesk:

```bash
php -v
composer --version
php -m
php --ini
which php
```

Em instalações Plesk Linux, o binário da versão pode estar em um caminho como `/opt/plesk/php/8.5/bin/php`, mas o caminho real deve ser confirmado no servidor.

Validar também:

```bash
PHP_BIN=/caminho/confirmado/do/php
"$PHP_BIN" -v
"$PHP_BIN" -m
composer --version
```

Não executar migrations ou comandos destrutivos antes de confirmar o ambiente e o banco correto.

---

## 5. Arquitetura de informação e rotas públicas

### 5.1 Sitemap funcional

| Rota | Página |
|---|---|
| `/` | Home |
| `/solucoes` | Visão geral de soluções |
| `/solucoes/software-personalizado` | Desenvolvimento de software |
| `/solucoes/erp` | Desenvolvimento de sistemas de gestão |
| `/solucoes/sites-e-ecommerce` | Sites e lojas virtuais |
| `/solucoes/inteligencia-artificial` | IA aplicada a negócios |
| `/solucoes/automacao` | Automação e integrações |
| `/solucoes/aplicativos` | Aplicativos mobile |
| `/produtos` | Catálogo de produtos DevStudio |
| `/produtos/{slug}` | Página individual de produto |
| `/projetos` | Portfólio/cases |
| `/projetos/{slug}` | Detalhe de projeto |
| `/sobre` | Sobre a DevStudio |
| `/insights` | Artigos e conteúdos |
| `/insights/{slug}` | Artigo individual |
| `/contato` | Contato e formulário |
| `/admin` | Painel Filament protegido |

Os produtos SaaS continuam sendo aplicações independentes. O site institucional apresenta cada produto e encaminha para o domínio/plataforma correspondente quando a URL estiver configurada.

### 5.2 Navegação principal

- Soluções
- Produtos
- Projetos
- Sobre
- Insights
- Contato

CTA persistente: **Fale com a DevStudio**.

### 5.3 Home

Seções recomendadas, nesta ordem inicial:

1. Hero com proposta de valor e dois CTAs.
2. Serviços/soluções principais.
3. Destaque dos produtos próprios.
4. Diferenciais e metodologia.
5. Projetos/cases selecionados.
6. Segmentos ou problemas atendidos.
7. CTA comercial final.

A Home deve ser composta por seções predefinidas e gerenciáveis pelo CMS, não por um page builder irrestrito.

### 5.4 Páginas de serviço

Cada página deve ter:

- Título e proposta de valor.
- Problema que resolve.
- Soluções e entregáveis.
- Aplicações e exemplos.
- Processo de trabalho.
- Perguntas frequentes quando houver conteúdo aprovado.
- CTA para WhatsApp e formulário.
- SEO individual.

### 5.5 Páginas de produto

Cada produto terá página comercial independente, com:

- Nome e identidade do produto.
- Descrição curta e proposta de valor.
- Público e segmentos atendidos.
- Funcionalidades.
- Benefícios.
- Screenshots ou demonstrações autorizadas.
- CTA específico (solicitar demonstração, falar com equipe ou visitar plataforma).
- URL externa opcional.
- Metadados SEO próprios.

Não presumir que ERP e DeskcommCRM estarão ambos disponíveis no lançamento. O CMS permitirá cadastrar ambos, mas somente produtos aprovados e publicados serão exibidos.

### 5.6 Portfólio e cases

Cada case poderá conter contexto, desafio, solução, entregáveis, tecnologias e resultados comprovados. Campos de métricas devem ser opcionais e só publicados quando houver autorização e evidência.

---

## 6. CMS Filament: módulos administrativos

Painel em `/admin`, com autenticação obrigatória e sem indexação por mecanismos de busca.

### 6.1 Recursos Filament

| Resource | Campos e funcionalidades centrais |
|---|---|
| Pages | Título, slug, resumo, template, seções, status, publicação, SEO |
| Services | Nome, slug, resumo, conteúdo, ícone, imagem, ordem, destaque, status, SEO |
| Products | Nome, slug, tagline, descrição, logo, capa, galeria, funcionalidades, CTA, URL externa, ordem, status, SEO |
| Projects | Título, slug, cliente opcional, categoria, resumo, desafio, solução, resultados, tecnologias, galeria, URL, status, SEO |
| Posts | Título, slug, resumo, corpo, autor, categoria, capa, tags, publicação, SEO |
| Categories | Nome, slug, descrição e tipo de conteúdo |
| Leads | Dados do contato, serviço, origem, UTM, status, observações internas |
| Users | Usuários administrativos e papéis |
| Site Settings | Identidade, contatos, redes sociais, WhatsApp, SEO padrão e configurações globais |

Mídias poderão ser gerenciadas inicialmente pelos campos de upload do Filament e salvas no filesystem Laravel. Um gerenciador avançado de mídia pode ser adicionado se o volume justificar.

### 6.2 Dashboard

Exibir indicadores básicos:

- Total de leads recebidos.
- Leads novos aguardando contato.
- Leads por status.
- Leads recentes.
- Conteúdos publicados recentemente.

Não construir um CRM completo no CMS. O módulo de Leads é uma caixa de entrada comercial simples, com status e dados de origem.

### 6.3 Papéis e permissões

Papéis iniciais:

- **Admin:** acesso total, incluindo usuários e configurações.
- **Editor:** gerencia páginas, serviços, produtos, projetos e artigos; não administra usuários nem configurações sensíveis.

Implementar autorização no servidor usando Policies/Gates e verificar o acesso ao painel Filament. Ocultar um botão no frontend não substitui autorização no backend.

### 6.4 Publicação

Conteúdos devem aceitar status `draft` e `published`, com `published_at` opcional.

Somente conteúdo publicado e dentro da janela de publicação deve aparecer no site público. Conteúdo em rascunho não pode ser exposto por URL pública.

---

## 7. Modelagem de dados

### 7.1 Tabelas principais

#### `pages`

- `id`
- `title`
- `slug` (único por idioma)
- `excerpt` nullable
- `template` (ex.: `standard`, `landing`)
- `sections` JSON
- `status` (`draft`, `published`)
- `published_at` nullable
- `locale` (padrão `pt_BR`)
- `translation_key` nullable
- `meta_title` nullable
- `meta_description` nullable
- `og_image` nullable
- `canonical_url` nullable
- timestamps

#### `services`

- `id`, `title`, `slug`, `excerpt`, `body`
- `icon`, `cover_image`
- `features` JSON
- `sort_order`, `is_featured`
- `status`, `published_at`
- `locale`, `translation_key`
- campos SEO e timestamps

#### `products`

- `id`, `name`, `slug`, `tagline`, `description`
- `logo_path`, `cover_path`
- `gallery` JSON
- `features` JSON
- `audiences` JSON
- `cta_label`, `cta_url`, `external_url`
- `sort_order`, `is_featured`
- `status`, `published_at`
- `locale`, `translation_key`
- campos SEO e timestamps

#### `projects`

- `id`, `title`, `slug`, `client_name` nullable
- `category_id` nullable
- `summary`, `challenge`, `solution`, `results` nullable
- `technologies` JSON
- `cover_image`, `gallery` JSON
- `external_url` nullable
- `is_featured`, `status`, `published_at`
- `locale`, `translation_key`
- campos SEO e timestamps

#### `posts`

- `id`, `title`, `slug`, `excerpt`, `body`
- `author_id`
- `category_id` nullable
- `cover_image`
- `tags` JSON ou relação própria, conforme necessidade
- `status`, `published_at`
- `locale`, `translation_key`
- `meta_title`, `meta_description`, `og_image`
- timestamps

#### `categories`

- `id`, `name`, `slug`, `description` nullable, `type`, timestamps

#### `leads`

- `id`
- `name`
- `company` nullable
- `email`
- `phone` nullable
- `service_id` nullable
- `message`
- `source_url` nullable
- `utm_source`, `utm_medium`, `utm_campaign` nullable
- `status` (`new`, `in_progress`, `qualified`, `converted`, `discarded`)
- `internal_notes` nullable
- `privacy_accepted_at`
- timestamps

#### `site_settings`

Configuração global do site: razão/nome de exibição, logotipo para fundo claro, logotipo para fundo escuro, ícone, e-mail, telefone, WhatsApp, redes sociais, endereço se aplicável, SEO padrão e links institucionais.

### 7.2 Internacionalização futura

Não implementar tradução no lançamento. Preparar registros de conteúdo com:

- `locale`, inicialmente `pt_BR`.
- `translation_key` para agrupar versões do mesmo conteúdo.
- Slug único por idioma.

A futura expansão poderá introduzir prefixos `/en` e `/es`, seletor de idioma, `hreflang` e tradução de conteúdo no CMS. Não traduzir automaticamente conteúdo publicado sem revisão.

### 7.3 Integridade

- Criar índices para `slug`, `status`, `published_at`, `locale` e campos de busca.
- Definir chaves estrangeiras e regras de exclusão coerentes.
- Usar transações ao salvar operações relacionadas.
- Garantir unicidade de slug por idioma.
- Usar casts Eloquent para JSON e enums quando apropriado.

---

## 8. Estratégia de conteúdo configurável

### 8.1 Blocos permitidos para páginas

O campo `sections` da Home e das páginas configuráveis deve aceitar somente blocos conhecidos:

1. `hero`
2. `services_grid`
3. `products_showcase`
4. `projects_showcase`
5. `text_image`
6. `features_list`
7. `testimonials` (somente com depoimentos autorizados)
8. `faq`
9. `cta`

Cada bloco tem schema definido em código e campos editáveis no Filament. O frontend renderiza cada tipo por um componente Blade próprio.

Não permitir que o editor insira HTML, JavaScript ou CSS arbitrário. Conteúdo rico deve ser sanitizado e renderizado com componentes seguros.

### 8.2 Conteúdo mínimo para lançamento

- Home completa.
- Sobre a DevStudio.
- Página geral de soluções.
- Páginas de serviços prioritários.
- Página de produtos.
- Páginas dos produtos efetivamente prontos para divulgação.
- Contato e política de privacidade.
- Pelo menos alguns projetos autorizados ou, na ausência deles, não exibir seção de cases vazia.

---

## 9. Identidade visual e design system

### 9.1 Direção

Premium, minimalista, tecnológica e profissional. Evitar excesso de neon, elementos futuristas genéricos, gradientes em toda a interface e animações que prejudiquem desempenho.

### 9.2 Arquivos da marca recebidos

- `logo_devstudio_dark_300x60.png`: versão com wordmark escuro, indicada para fundos claros.
- `logo_devstudio_light_300x60.png`: versão com wordmark branco, indicada para fundos escuros.
- `Icone2.png`: símbolo isolado.
- `Icone3(2).png`: composição vertical com símbolo e wordmark.

Os arquivos possuem transparência. As duas logos horizontais devem ser usadas conforme o contraste do fundo; não são arquivos incompletos. Preservar os arquivos originais no projeto.

Recomendação de organização:

```text
public/images/brand/
├── logo-horizontal-dark.png
├── logo-horizontal-light.png
├── logo-icon.png
└── logo-vertical.png
```

Se possível, solicitar/gerar também os equivalentes vetoriais em SVG a partir do arquivo-fonte original. Não redesenhar nem distorcer o símbolo durante a implementação.

### 9.3 Paleta inicial

A cor vermelha da marca deve ser preservada como assinatura, mas usada com parcimônia. A interface deve apoiar-se em fundos neutros claros e escuros.

| Token | Cor | Aplicação |
|---|---|---|
| `brand-500` | `#D10047` | Vermelho de identidade, links e destaques |
| `brand-600` | `#C8104A` | Botões e estados interativos |
| `brand-700` | `#A80D3D` | Hover e contraste mais forte |
| `burgundy` | `#851337` | Degradês e detalhes escuros |
| `graphite` | `#111315` | Fundos escuros |
| `surface-dark` | `#17191E` | Cards escuros |
| `surface-light` | `#F6F6F7` | Fundos claros |
| `text-primary` | `#17171B` | Texto em fundo claro |
| `text-muted` | `#737782` | Texto secundário |
| `border` | `#E5E7EB` | Bordas claras |

Validar contraste de todos os estados. O vermelho não deve ser usado como cor de texto pequeno em fundo escuro sem verificação de contraste.

### 9.4 Tipografia

- Títulos: Sora ou Space Grotesk.
- Texto corrido: Inter.
- Limitar número de famílias e pesos.
- Preferir fontes locais/otimizadas quando houver arquivos licenciados; caso contrário, usar fonte web com política de carregamento adequada.

### 9.5 Componentes

- Header com variante clara/escura conforme a seção.
- Footer institucional.
- Botão primário, secundário e link.
- Cards de serviços, produtos e projetos.
- Breadcrumbs.
- CTA banners.
- Formulários acessíveis.
- Seções de conteúdo e FAQ.
- Estados de foco, hover, loading e erro.

### 9.6 Responsividade e acessibilidade

- Mobile-first.
- Navegação por teclado.
- Foco visível.
- Hierarquia semântica de títulos.
- Labels explícitos nos formulários.
- Texto alternativo para imagens informativas.
- Não comunicar informações somente por cor.
- Respeitar `prefers-reduced-motion`.
- Buscar conformidade WCAG 2.2 AA nos componentes principais.

---

## 10. Formulários e captação de leads

### 10.1 Campos

- Nome (obrigatório).
- Empresa (opcional).
- E-mail (obrigatório).
- WhatsApp/telefone (opcional, mas recomendado).
- Tipo de solução (obrigatório).
- Descrição da necessidade (obrigatório).
- Aceite da política de privacidade (obrigatório).
- Campos ocultos para URL de origem e UTM.

### 10.2 Fluxo

1. Validar e normalizar dados no servidor.
2. Aplicar proteção antispam e rate limiting.
3. Salvar lead no banco.
4. Registrar origem e campanha quando disponíveis.
5. Tentar enviar notificação por e-mail.
6. Exibir confirmação ao usuário.
7. Registrar falha de e-mail sem perder o lead.

### 10.3 WhatsApp

O CTA abre o link `wa.me` com número e mensagem contextual configurados no CMS. Não requer integração com a API oficial do WhatsApp na primeira versão.

### 10.4 Integração futura com DeskcommCRM

Preparar uma classe/serviço de integração desacoplado. A primeira versão não deve depender do CRM para salvar leads. Uma futura integração por webhook deve usar timeout, autenticação por segredo, logs e política de retentativa.

### 10.5 Privacidade

Incluir política de privacidade, finalidade da coleta, consentimento explícito e regras internas de retenção/exclusão dos leads. Coletar somente os dados necessários. Revisar o texto jurídico antes da publicação.

---

## 11. SEO, analytics e desempenho

### 11.1 SEO técnico

- Meta title e description por página.
- URLs curtas e descritivas.
- Canonical URL.
- Open Graph e Twitter/X cards.
- Sitemap XML dinâmico.
- Robots.txt.
- Breadcrumbs.
- Dados estruturados Schema.org: `Organization`, `Service`, `Article` e `BreadcrumbList` quando aplicáveis.
- Não publicar schema de avaliações ou resultados inexistentes.
- Páginas de admin, rascunho e preview devem ser `noindex`.

### 11.2 Conteúdo

- Uma intenção de busca principal por página.
- H1 único e hierarquia H2/H3 coerente.
- Links internos entre serviços, produtos, projetos e artigos.
- Imagens com nomes, dimensões e alt text adequados.
- Páginas de serviço com conteúdo útil e específico, sem repetição automática de palavras-chave.

### 11.3 Analytics

Preparar configuração para GA4 e Search Console. Eventos mínimos:

- `generate_lead` após envio confirmado.
- `click_whatsapp`.
- `view_product`.
- `click_product_cta`.
- `view_service`.
- `view_project`.

A instalação de pixels de publicidade deve depender da estratégia de mídia e da política de consentimento aplicável.

### 11.4 Desempenho

- Imagens responsivas e comprimidas.
- Preferir WebP/AVIF quando suportado.
- Lazy loading abaixo da dobra.
- Evitar bibliotecas JavaScript pesadas.
- Compilar assets de produção com Vite.
- Cache de configuração, rotas e views no deploy.
- Medir Core Web Vitals após publicação.

---

## 12. Segurança

- `APP_DEBUG=false` em produção.
- `.env` fora do diretório público e fora do Git.
- HTTPS obrigatório.
- Senha forte e acesso administrativo restrito.
- Rate limit no login e nos formulários.
- Policies/Gates para operações administrativas.
- Validação de uploads por MIME, extensão e tamanho.
- Sanitização de conteúdo rico.
- CSRF habilitado nos formulários web.
- Dependências auditadas e atualizadas com controle.
- Logs sem senhas, tokens ou dados pessoais desnecessários.
- Backups protegidos e fora do diretório público.
- Remover usuários administrativos de teste antes da produção.

Autenticação de dois fatores para administradores é recomendada como melhoria de segurança, desde que habilitada e testada antes do lançamento.

---

## 13. Organização do repositório

Estrutura proposta, respeitando as convenções do Laravel:

```text
devstudio-website/
├── app/
│   ├── Actions/
│   ├── Enums/
│   ├── Filament/
│   │   ├── Resources/
│   │   ├── Pages/
│   │   └── Widgets/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Requests/
│   │   └── Middleware/
│   ├── Models/
│   ├── Policies/
│   └── Services/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── docs/
├── public/
│   ├── images/brand/
│   └── build/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       ├── layouts/
│       ├── components/
│       ├── pages/
│       ├── sections/
│       ├── services/
│       ├── products/
│       ├── projects/
│       └── insights/
├── routes/
├── storage/
├── tests/
├── .env.example
├── AGENTS.md
├── composer.json
├── composer.lock
├── package.json
└── vite.config.js
```

### 13.1 Regras de organização

- Controllers finos: receber requisição, chamar Action/Service e devolver resposta.
- Regras reutilizáveis em Services/Actions.
- Regras de autorização em Policies/Gates.
- Validação de entrada em Form Requests.
- Models responsáveis por relações, casts e scopes simples.
- Componentes Blade pequenos e reutilizáveis.
- Componentes Livewire somente quando houver estado/interação real.
- Recursos Filament não devem concentrar regras de negócio.
- Não duplicar a mesma regra no formulário público e no CMS.

---

## 14. Deploy e operação no Plesk

### 14.1 Processo de publicação

1. Fazer merge da versão aprovada na branch de produção.
2. Executar testes e build de assets no ambiente de desenvolvimento/CI.
3. Enviar código e assets compilados para o servidor.
4. Instalar dependências PHP de produção com Composer.
5. Configurar variáveis de ambiente no servidor.
6. Executar migrations com `--force`, após backup.
7. Executar `storage:link` quando aplicável.
8. Ajustar permissões de `storage` e `bootstrap/cache`.
9. Executar `php artisan optimize`.
10. Validar páginas públicas, `/admin`, formulário e logs.

### 14.2 Comandos de referência

Executar na raiz privada da aplicação, usando o binário PHP 8.5 correto:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

O build Vite deve ser realizado antes da publicação:

```bash
npm ci
npm run build
```

Não é necessário manter Node.js como processo ativo em produção; apenas os arquivos compilados são servidos pelo web server.

### 14.3 Scheduler

Se a aplicação passar a usar tarefas agendadas, cadastrar uma única tarefa no Plesk para executar `php artisan schedule:run` a cada minuto, utilizando o caminho explícito do PHP 8.5 e da aplicação. Não cadastrar tarefas sem necessidade.

### 14.4 Backup

- Banco: backup diário automatizado.
- Arquivos: backup do storage e dos arquivos de configuração necessários, sem expor `.env`.
- Retenção: definir política de retenção de acordo com espaço e criticidade.
- Restauração: testar em ambiente separado antes do lançamento e periodicamente.

### 14.5 Rollback

- Manter release anterior ou backup do código.
- Fazer backup do banco antes de migrations de produção.
- Evitar migrations destrutivas sem plano de reversão.
- Documentar rollback de código e dados.

---

## 15. Testes e critérios de aceite

### 15.1 Testes automatizados

- Acesso público às páginas publicadas.
- Resposta 404 para slugs inexistentes e conteúdo não publicado.
- Unicidade de slugs por idioma.
- Relacionamentos de produtos, serviços, projetos e artigos.
- Validação e persistência de leads.
- Lead continua salvo quando notificação de e-mail falha.
- Proteção contra envio excessivo.
- Acesso restrito a `/admin`.
- Permissões de Admin e Editor.
- Metadata e canonical por conteúdo.
- Sitemap contém somente páginas públicas elegíveis.

### 15.2 Homologação manual

- Desktop, tablet e mobile.
- Navegação e menu mobile.
- Formulários e CTA WhatsApp.
- Uploads de imagens.
- Publicação e despublicação de conteúdo.
- Páginas de produtos e projetos.
- E-mails de notificação.
- Contraste, foco e navegação por teclado.
- Verificação de links quebrados.
- Logs sem erros críticos.

### 15.3 Critérios de aceite do lançamento

- [ ] Todas as páginas P0 estão publicadas e revisadas.
- [ ] Admin Filament funciona com autenticação.
- [ ] Admin e Editor possuem permissões diferentes.
- [ ] Conteúdos podem ser criados, editados, publicados e despublicados.
- [ ] Produtos possuem páginas individuais dinâmicas.
- [ ] Formulário grava leads no banco.
- [ ] Falha de e-mail não remove lead.
- [ ] WhatsApp funciona com mensagem contextual.
- [ ] SEO técnico básico está implementado.
- [ ] Site funciona em dispositivos móveis.
- [ ] Document Root aponta somente para `public`.
- [ ] `APP_DEBUG=false` e HTTPS ativo.
- [ ] Backups foram configurados e restauração testada.
- [ ] Deploy e rollback estão documentados.

---

## 16. Plano de implementação priorizado

### Fase 0 — Auditoria da hospedagem (P0)

- [ ] T0.01 Confirmar PHP-FPM 8.5 do domínio.
- [ ] T0.02 Confirmar PHP CLI 8.5.
- [ ] T0.03 Confirmar Composer 2 e SSH.
- [ ] T0.04 Verificar extensões PHP necessárias, incluindo `intl` e `pdo_mysql`.
- [ ] T0.05 Confirmar versão MySQL/MariaDB.
- [ ] T0.06 Confirmar Document Root personalizado.
- [ ] T0.07 Confirmar permissões e limites de upload/memória.
- [ ] T0.08 Confirmar cron, SMTP e SSL.

**Saída:** relatório de compatibilidade da hospedagem e decisão final de deploy.

### Fase 1 — Fundação (P0)

- [ ] T1.01 Criar repositório e branch principal.
- [ ] T1.02 Criar aplicação Laravel 13.
- [ ] T1.03 Configurar PHP 8.5 e dependências.
- [ ] T1.04 Configurar MySQL/MariaDB.
- [ ] T1.05 Instalar Filament 5 e criar painel Admin.
- [ ] T1.06 Configurar Livewire, Alpine, Tailwind 4 e Vite.
- [ ] T1.07 Configurar `.env.example`.
- [ ] T1.08 Configurar Pest/PHPUnit.
- [ ] T1.09 Criar `AGENTS.md` e documentação técnica.

**Saída:** aplicação executando localmente, painel protegido e banco conectado.

### Fase 2 — Design system (P0)

- [ ] T2.01 Adicionar as quatro artes originais da marca.
- [ ] T2.02 Criar tokens de cores, fontes, espaçamento e radius.
- [ ] T2.03 Criar layout claro e escuro.
- [ ] T2.04 Implementar header e footer.
- [ ] T2.05 Implementar botões, cards, badges, breadcrumbs e CTAs.
- [ ] T2.06 Criar componentes responsivos.
- [ ] T2.07 Definir padrões de animação e movimento reduzido.

**Saída:** sistema visual reutilizável aprovado.

### Fase 3 — Modelagem e CMS (P0)

- [ ] T3.01 Criar enums de status e papéis.
- [ ] T3.02 Criar migrations e Models.
- [ ] T3.03 Implementar relações e índices.
- [ ] T3.04 Implementar Resources Filament de páginas e serviços.
- [ ] T3.05 Implementar Resources de produtos e projetos.
- [ ] T3.06 Implementar Resources de artigos e categorias.
- [ ] T3.07 Implementar gestão de leads.
- [ ] T3.08 Implementar configurações globais.
- [ ] T3.09 Implementar upload e storage.
- [ ] T3.10 Implementar autorização Admin/Editor.
- [ ] T3.11 Implementar publicação e rascunhos.

**Saída:** CMS funcional para administrar conteúdo e leads.

### Fase 4 — Website (P0)

- [ ] T4.01 Implementar layout público.
- [ ] T4.02 Implementar Home com seções configuráveis.
- [ ] T4.03 Implementar páginas de soluções.
- [ ] T4.04 Implementar catálogo e detalhes de produtos.
- [ ] T4.05 Implementar portfólio e detalhes de projetos.
- [ ] T4.06 Implementar página Sobre.
- [ ] T4.07 Implementar Contato.
- [ ] T4.08 Implementar listagem e detalhe de artigos.
- [ ] T4.09 Implementar 404 e estados vazios.
- [ ] T4.10 Implementar conteúdo dinâmico a partir do CMS.

**Saída:** website institucional completo e integrado ao Filament.

### Fase 5 — Conversão (P0)

- [ ] T5.01 Criar Form Request do formulário comercial.
- [ ] T5.02 Implementar validação, honeypot e rate limiting.
- [ ] T5.03 Persistir lead e metadados de origem.
- [ ] T5.04 Implementar e-mail de notificação.
- [ ] T5.05 Implementar CTAs de WhatsApp por contexto.
- [ ] T5.06 Criar estados de sucesso e erro.
- [ ] T5.07 Registrar eventos de conversão.
- [ ] T5.08 Documentar webhook futuro do DeskcommCRM.

**Saída:** captação comercial operante e auditável.

### Fase 6 — SEO, analytics e otimização (P1)

- [ ] T6.01 Implementar metadados dinâmicos.
- [ ] T6.02 Implementar sitemap e robots.
- [ ] T6.03 Implementar canonical e Open Graph.
- [ ] T6.04 Implementar Schema.org.
- [ ] T6.05 Configurar Analytics e Search Console.
- [ ] T6.06 Otimizar imagens, fontes e assets.
- [ ] T6.07 Validar Core Web Vitals.
- [ ] T6.08 Validar acessibilidade.
- [ ] T6.09 Preparar URLs e dados traduzíveis para i18n futuro.

**Saída:** site indexável, mensurável e otimizado.

### Fase 7 — Homologação e deploy (P0)

- [ ] T7.01 Executar testes automatizados.
- [ ] T7.02 Executar testes manuais responsivos.
- [ ] T7.03 Validar formulários, e-mails e WhatsApp.
- [ ] T7.04 Revisar conteúdo e direitos de publicação.
- [ ] T7.05 Configurar Document Root seguro no Plesk.
- [ ] T7.06 Configurar `.env` de produção.
- [ ] T7.07 Publicar assets e dependências.
- [ ] T7.08 Executar migrations com backup prévio.
- [ ] T7.09 Configurar SSL, logs e backups.
- [ ] T7.10 Homologar produção e documentar rollback.

**Saída:** site publicado e operacional.

### Melhorias posteriores (P2/P3)

- Traduções em inglês e espanhol.
- Integração efetiva com DeskcommCRM.
- Automação de conteúdo com IA.
- Newsletter avançada.
- Portal/área do cliente.
- Busca avançada e filtros adicionais no portfólio.
- Integração com storage S3 caso volume ou disponibilidade justifiquem.

---

## 17. Regras para agentes de desenvolvimento

Criar `AGENTS.md` com as seguintes regras:

1. Ler esta especificação antes de iniciar qualquer tarefa.
2. Trabalhar em uma tarefa identificada do backlog por vez.
3. Não trocar stack ou adicionar serviços externos sem justificativa e aprovação.
4. Seguir convenções Laravel e Filament; evitar abstrações desnecessárias.
5. Manter Controllers finos e regras reutilizáveis em Actions/Services.
6. Aplicar Policies/Gates para autorização.
7. Validar toda entrada no servidor.
8. Não renderizar HTML/JavaScript arbitrário fornecido pelo CMS.
9. Criar migrations versionadas e nunca alterar banco manualmente sem registrar migration.
10. Reutilizar componentes Blade e tokens do design system.
11. Não inserir conteúdo, depoimentos ou métricas fictícias.
12. Executar testes, formatador e análise estática aplicável antes de concluir.
13. Atualizar o backlog e documentar decisões relevantes.
14. Não marcar tarefa como concluída sem testar o critério de aceite.
15. Manter `.env`, credenciais e dados pessoais fora do Git e dos logs.

### Definição de pronto por tarefa

Uma tarefa só pode ser marcada como concluída quando:

- O comportamento solicitado foi implementado.
- A autorização e validação estão aplicadas.
- O layout é responsivo quando houver interface.
- Testes relevantes passam.
- Não há erros críticos no log.
- A documentação/backlog foi atualizado.

---

## 18. Dependências e riscos

| Risco/Dependência | Tratamento |
|---|---|
| PHP CLI diferente do PHP-FPM | Auditar binários antes de instalar dependências |
| Hospedagem não permitir Document Root customizado | Solicitar ajuste ou usar estrutura privada equivalente |
| Composer/SSH indisponível | Confirmar com provedor antes do início; definir procedimento alternativo de deploy |
| Extensão `intl` ausente | Solicitar habilitação; necessária ao Filament |
| SMTP não configurado | Validar envio antes de homologar formulário |
| Limite baixo de storage | Otimizar imagens e revisar plano |
| Conteúdo institucional incompleto | Criar lista de conteúdo pendente; não publicar placeholders |
| Cases sem autorização | Omitir ou anonimizar somente com aprovação expressa |
| Produtos não prontos | Manter como rascunho até autorização de lançamento |
| Falta de backup testado | Bloquear publicação até validar restauração |

---

## 19. Materiais necessários da DevStudio

- Confirmação do domínio definitivo.
- E-mail e WhatsApp comerciais.
- Links de redes sociais.
- Breve história da empresa e ano de início, se divulgado.
- Descrição final dos serviços prioritários.
- Quais produtos SaaS estarão publicados no lançamento.
- URLs externas dos produtos, quando existirem.
- Projetos que podem ser exibidos e autorizações correspondentes.
- Depoimentos reais, se disponíveis e autorizados.
- Textos institucionais e política de privacidade.
- Imagens e screenshots dos sistemas.
- Confirmação de versão MySQL/MariaDB, SSH, Composer e Document Root no Plesk.

---

## 20. Referências oficiais

- Laravel 13 — Deployment e requisitos: https://laravel.com/docs/13.x/deployment
- Laravel 13 — Frontend, Blade, Livewire e Vite: https://laravel.com/docs/13.x/frontend
- Laravel 13 — Banco de dados: https://laravel.com/docs/13.x/database
- Laravel 13 — Task Scheduling: https://laravel.com/docs/13.x/scheduling
- Filament 5 — Instalação: https://filamentphp.com/docs/5.x/introduction/installation
- Filament 5 — Visão geral: https://filamentphp.com/docs/5.x/introduction/overview
- Plesk — PHP Settings: https://docs.plesk.com/en-US/obsidian/administrator-guide/70742/
- Plesk — Custom Document Root: https://docs.plesk.com/en-US/obsidian/administrator-guide/web-hosting/website-directory-structure/defining-a-custom-document-root.77500/
- Plesk — Scheduled Tasks: https://docs.plesk.com/en-US/obsidian/customer-guide/scheduling-tasks.65207/

---

## 21. Decisão final de arquitetura

A primeira versão da DevStudio será uma aplicação Laravel 13 + PHP 8.5, com Filament 5 como CMS, Blade/Livewire no frontend e MySQL/MariaDB como banco de dados. Será publicada no Plesk com o Document Root apontando exclusivamente para `public/`.

A solução não utilizará Redis, microsserviços, Docker em produção, frontend separado ou API independente no lançamento. Produtos próprios terão páginas de apresentação no site, mas continuarão operando em suas plataformas SaaS independentes.

**Próximo passo de execução:** concluir a Fase 0 (auditoria da hospedagem) e, confirmada a compatibilidade, iniciar a Fase 1 (fundação Laravel e painel Filament).
