# Arquitetura do DevStudio

> **Adendo 12/11/2026:** o fundador declarou concluído o checklist Plesk e liberou desenvolvimento local, segundo Nexus; Engenharia não verificou relatório ou ambiente. Esta atualização prevalece sobre o bloqueio anterior, sem liberar produção. Ver [Deploy](DEPLOYMENT.md).

**Estado:** documentação preparatória; implementação bloqueada até a auditoria Plesk. Fonte: [especificação](../ESPECIFICACAO_TECNICA_DEVSTUDIO_WEBSITE.md), seções 2–5, 10–15 e 21.

## Decisões aprovadas

Monólito Laravel 13 em PHP 8.5, Filament 5 no `/admin`, Blade no site público, Livewire 4 e Alpine.js quando necessários, Tailwind CSS 4.1+ e Vite. Eloquent e MySQL ou MariaDB persistem dados; Form Requests validam entradas e Policies/Gates autorizam operações. Pest ou PHPUnit serão escolhidos na implementação. Composer resolverá versões exatas e as fixará em `composer.lock`; nenhuma versão foi resolvida agora. Hospedagem prevista: Plesk, preferencialmente PHP-FPM. SaaS são aplicações independentes, apresentadas por páginas comerciais neste site.

Não introduzir Redis, frontend React/Next separado, API independente, microsserviços, Docker em produção, S3 ou MinIO no lançamento. Cache `file` ou `database` conforme hospedagem; sessão `file`, fila `sync` inicialmente. A arquitetura não presume worker permanente. Scheduler só será configurado se necessário e autorizado.

## Limites e fluxos

Requisição pública → rotas e controllers → validação e regras reutilizáveis → Eloquent → Blade; componentes Livewire apenas onde há interação real. `/admin` exige autenticação e autorização no servidor. [Modelo do CMS](CMS-MODEL.md) define publicação e dados.

Contato → validação, consentimento, antispam e limitação → persistência do lead e origem/UTM → tentativa de e-mail → confirmação; falha de e-mail deve ser registrada sem apagar o lead. A integração futura com DeskcommCRM não pode ser pré-requisito do salvamento; webhook futuro exige autenticação, timeout, logs e retentativas. WhatsApp usa `wa.me` configurado, sem API oficial nesta fase. Fonte: §§ 3.4 e 10.

As páginas públicas compreendem Home, soluções e seis serviços, produtos e detalhes, projetos e detalhes, Sobre, insights e detalhes, contato e privacidade; rotas exatas em § 5.1. Conteúdo em rascunho ou fora da janela nunca é público, inclusive por URL; sitemap inclui somente conteúdo elegível. Preparar `pt_BR`, `translation_key` e slug por idioma, sem implementar traduções agora (§§ 6.4, 7.2, 11).

## Segurança e operação

Somente `public/` pode ser exposto; código, `.env`, backups e arquivos privados ficam fora da raiz pública. HTTPS, `APP_DEBUG=false`, CSRF, autorização, validação de uploads por extensão, MIME e tamanho, sanitização e limitação de login/formulários são obrigatórios. Logs não devem conter segredos ou dados pessoais desnecessários. Uploads públicos usam filesystem local público; symlink somente se permitido. Backups de banco e storage devem ser protegidos e a restauração testada antes do lançamento (§§ 3.5, 4, 12, 14).

## Pendências e riscos

Auditoria Plesk não realizada: confirmar PHP do domínio e CLI, Composer, extensões, acesso, versão de banco, Document Root, permissões, limites, SMTP, cron e SSL antes de implementar. Caso a raiz segura não seja possível, obter solução equivalente aprovada, sem expor a aplicação. Domínio, SMTP, banco exato, retenção e exclusão LGPD e aprovação de conteúdo continuam pendentes. Backups não testados bloqueiam publicação. A implantação e qualquer acesso a produção requerem autorização específica. Ver [orientações de desenvolvimento](DEVELOPMENT-GUIDELINES.md).
