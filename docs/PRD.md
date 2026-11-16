# PRD — DevStudio

> **Adendo 12/11/2026:** o fundador declarou concluído o checklist Plesk e autorizou desenvolvimento local, segundo Nexus; Engenharia não verificou evidências. Este adendo prevalece sobre o bloqueio histórico, sem autorizar produção. Ver [Deploy](DEPLOYMENT.md).

Fonte: [especificação aprovada, §§ 1–2, 5–6, 8, 10, 15 e 18–19](../ESPECIFICACAO_TECNICA_DEVSTUDIO_WEBSITE.md). A aprovação da base está condicionada à auditoria final do Plesk; este documento não comprova execução ou aceite. Backlog: [TASKS](TASKS.md); busca e medição: [SEO](SEO-STRATEGY.md).

## Objetivo e público

Requisito aprovado: apresentar soluções e produtos SaaS independentes, fortalecer autoridade, captar oportunidades e permitir gestão de conteúdo sem código. Público: pequenas, médias e grandes empresas e empreendedores com necessidades de software, modernização, automação ou SaaS. Indicadores: leads por origem, serviço e campanha; cliques em CTAs; visualizações e conversões de serviços, produtos e projetos; tráfego orgânico e artigos publicados. Não há metas numéricas aprovadas.

## Escopo funcional

- Home com hero, soluções, produtos, diferenciais, projetos, segmentos e CTA; seções predefinidas editáveis, não construtor livre.
- `/solucoes` e páginas de software personalizado, ERP, sites e e-commerce, inteligência artificial, automação e aplicativos: proposta, problema, entregáveis, aplicações, processo, FAQ quando aprovada, CTAs e SEO.
- `/produtos` e `/produtos/{slug}`: catálogo e páginas comerciais dos produtos aprovados; plataforma SaaS continua independente e URL externa é opcional.
- `/projetos` e `/projetos/{slug}`: cases autorizados; resultados somente comprovados. Sem cases autorizados, omitir seção vazia.
- `/sobre`, `/insights`, `/insights/{slug}`, `/contato` e política de privacidade; rota desta última pendente de definição.
- `/admin` autenticado: páginas, serviços, produtos, projetos, artigos, categorias, leads, usuários e configurações; Admin e Editor com permissões distintas. Caixa de entrada de leads não é CRM completo.
- Português inicial; estrutura preparada para outros idiomas, sem tradução no lançamento.

## Jornadas e aceite verificável

1. Visitante encontra uma solução ou produto, consulta informações aprovadas e aciona formulário ou WhatsApp contextual; links e mensagem configurados são verificáveis em homologação.
2. Formulário exige nome, e-mail, tipo de solução, necessidade e aceite de privacidade; empresa e telefone são opcionais. Decisão do fundador, transmitida por Nexus: permitir opção **Outro**, sem referência a serviço, com campo específico para descrevê-lo. Servidor valida, protege contra abuso, salva lead e origem/UTM disponíveis antes de tentar notificação. Falha de e-mail não perde lead; interface apresenta estado apropriado.
3. Editor autorizado gerencia conteúdo, categorias e mídias, sem acesso a leads, widgets comerciais, usuários ou configurações globais. Decisão do fundador, transmitida por Nexus: `published` com `published_at` nulo publica imediatamente; data futura agenda, e não há data final na v1. Rotas, listagens e sitemap aplicam a mesma regra; rascunhos e conteúdo agendado retornam 404 por acesso direto. Nada disso indica implementação concluída.
4. Produto só aparece quando aprovado e publicado; conteúdo, imagens, resultados e depoimentos só entram com autorização e evidência aplicáveis.
5. Lançamento requer páginas P0 revisadas, testes automatizados e manuais, responsividade, SEO básico, HTTPS, Document Root seguro, backup com restauração testada, deploy e rollback documentados e aprovação do fundador para produção. Ver também fonte § 15.

## Limites e decisões pendentes

Sem frontend separado, API independente, microsserviços, Docker de produção, Redis, tradução, integração efetiva com CRM ou construtor de páginas irrestrito. Stack aprovada na fonte § 3: PHP 8.5, Laravel 13, Filament 5, Blade, Livewire/Alpine quando necessário, Tailwind, Vite e MySQL ou MariaDB; versões efetivamente resolvidas ainda não verificadas.

Pendências: domínio, contatos/WhatsApp, SMTP, banco de produção e versão, serviços e produtos prioritários, textos e direitos de uso, política jurídica e retenção/exclusão LGPD. Se um serviço for removido, preservar intenção e histórico do lead; exclusão destrutiva depende de aprovação. Contrato técnico proposto em [CMS](CMS-MODEL.md). Conforme adendo, desenvolvimento local foi autorizado; compatibilidade Plesk continua sem evidência técnica verificada e produção não foi autorizada.
