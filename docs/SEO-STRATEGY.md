# SEO, medição e privacidade

Fonte: [especificação §§ 5–6, 10–11, 15 e 19](../ESPECIFICACAO_TECNICA_DEVSTUDIO_WEBSITE.md); requisitos: [PRD](PRD.md); execução: [TASKS](TASKS.md). Estratégia documental, sem serviço conectado ou resultado de auditoria.

## Indexação e conteúdo

- Somente páginas públicas publicadas e elegíveis na janela entram no sitemap; rascunhos, previews, admin e conteúdo fora da janela não entram. Rascunhos não são acessíveis por URL pública; admin é protegido. `noindex` é sinal adicional, não substitui controle de acesso.
- Decisão do fundador, transmitida por Nexus: `published` com `published_at` nulo é imediato; data futura agenda, data atual ou passada é elegível; sem data final na v1. Aplicar a mesma regra às consultas, rotas públicas e sitemap; rascunhos e agendados retornam 404. Implementação local T3.11 nas rotas públicas existentes e sitemap; implantação não realizada.
- Uma intenção principal por página, H1 único, H2/H3 hierárquicos, links úteis entre soluções, produtos, projetos e insights, imagens com alt adequado. Conteúdo e alegações somente aprovados; não fabricar avaliações nem resultados.
- Título e descrição próprios, canonical, Open Graph e Twitter/X cards; valores globais configuráveis como alternativa. URLs curtas e descritivas; breadcrumbs. Definição do domínio e canonicals finais pendente.
- Schema.org `Organization`, `Service`, `Article` e `BreadcrumbList` quando aplicáveis e coerentes com conteúdo visível. Não gerar avaliações ou números inexistentes. Robots.txt e sitemap XML dinâmico; não tratar bloqueio de robots como proteção de dados.

**T6.01 (implementação local; QA pendente):** somente Pages, Services, Products, Projects e Posts publicados/elegíveis em `pt_BR`. `<title>` usa `meta_title` ou título/nome, sem sufixo do site; description usa `meta_description` ou resumo/excerpt/tagline/descrição editorial existente, omitindo-a quando indisponível. CMS limita title a 70 e description a 200 caracteres, rejeita markup/controles e não trunca; HTML é escapado na saída. Metadado legado inválido é omitido. Listagens usam o heading como title e não inventam description. Canonical, robots, sitemap, Open Graph, schema, domínio e `APP_URL` ficam fora desta fatia.

**T6.02 — rastreamento bloqueado antes do lançamento:** `/robots.txt` responde `User-agent: *` e `Disallow: /` em `text/plain`; não inclui URL absoluta de sitemap porque o domínio final/`APP_URL` ainda não foi definido. O arquivo é também servido estaticamente pelo web server. `robots.txt` é protocolo voluntário para crawlers cooperativos, não controle de acesso: ACL, 404 de conteúdo inelegível e autenticação continuam valendo independentemente dele. É uma política temporária de pré-lançamento; antes de lançar, Produto/SEO deve decidir quais crawlers e caminhos serão liberados, revisar com o domínio/`APP_URL` final e remover ou substituir o bloqueio total. Não publicar nem alterar domínio/`APP_URL` nesta tarefa.

**Estado T3.11 (13/11/2026):** sitemap local usa índice e arquivos streamed por tipo, em lotes de no máximo 10.000 URLs. T6.02 não altera XML, URLs `pt_BR` ou elegibilidade; nenhum host absoluto é presumido.

## Medição e consentimento

Preparar GA4 e Search Console; contas, acesso, configuração final e política de consentimento dependem de autorização. Pixels publicitários dependem de estratégia e consentimento aplicáveis. Não presumir que aceite da política do formulário autoriza analytics ou publicidade; validar bases e escolhas com responsável jurídico antes da ativação.

Eventos mínimos: `generate_lead` apenas após persistência confirmada; `click_whatsapp`, `view_product`, `click_product_cta`, `view_service`, `view_project` nos respectivos eventos. Proposta: testar eventos com identificadores de conteúdo não pessoais e sem nome, e-mail, telefone, mensagem ou outros dados pessoais; não enviar URLs, UTMs ou parâmetros que contenham dados pessoais. Definir parâmetros finais e regras de consentimento antes da ativação.

## Validações de aceite

Em ambiente de teste, conferir HTML e metadados/canonical por tipo de página; sitemap e rotas versus registros com data nula, passada, futura, rascunho e despublicação; proteção do admin; `noindex` onde aplicável; links, robots e dados estruturados. Simular sucesso e falha de lead e verificar que apenas sucesso gera conversão, sem PII nos eventos. Conferir consentimento e ausência de pixels não autorizados. Medir acessibilidade, imagens responsivas e desempenho; Core Web Vitals após publicação autorizada. Nenhuma validação foi executada nesta preparação.
