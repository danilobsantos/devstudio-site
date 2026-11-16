# Orientações de desenvolvimento

> **Adendo 12/11/2026:** o fundador declarou concluído o checklist Plesk e autorizou desenvolvimento local, segundo Nexus; relatório e ambiente não foram verificados por Engenharia. Esta autorização prevalece sobre o bloqueio histórico abaixo, sem autorizar produção. Ver [Deploy](DEPLOYMENT.md).

**Estado:** preparação documental, não autorização para implementar. Fonte: [especificação](../ESPECIFICACAO_TECNICA_DEVSTUDIO_WEBSITE.md), §§ 3, 4, 12–17. Consulte [arquitetura](ARCHITECTURE.md) e [CMS](CMS-MODEL.md).

## Fluxo de trabalho

Antes de implementar, concluir e revisar a auditoria Plesk (§ 4 e fase 0); a auditoria não foi feita nesta preparação. Trabalhar em uma tarefa identificada do backlog por vez, com escopo e critérios de aceite claros. Definir responsável pelos arquivos antes de escrever; não editar simultaneamente os mesmos arquivos. Preservar conteúdo existente e não inventar dados ou métricas. Alterações de stack, serviços externos, produção e publicação exigem aprovação apropriada. Registrar decisões e atualizar o backlog; não criar nem editar arquivos de instruções de agentes sem pedido explícito.

## Convenções futuras

Seguir Laravel e Filament: controllers finos; validação em Form Requests; regras compartilhadas em Actions/Services; relações, casts e scopes simples em Models; Policies/Gates no servidor; componentes Blade pequenos; Livewire somente para interação real. Resources Filament não concentram regras de negócio. Migrations versionadas registram mudanças de banco; não alterar o banco manualmente sem migration. Reutilizar tokens e componentes; não adicionar camadas ou dependências sem necessidade. Criar testes para regras, permissões e fluxos relevantes.

Validar entradas no servidor, sanitizar conteúdo rico, impedir código arbitrário, limitar login e formulário e validar extensão, MIME e tamanho de uploads. Manter CSRF, HTTPS, segredos e dados pessoais fora do Git e dos logs. `.env.example` deve conter apenas exemplos sem segredos. Em produção, `APP_DEBUG=false`, backups privados e somente `public/` exposto.

## Comandos de referência, não executados

Somente depois da auditoria aprovada, criação autorizada da aplicação e instalação aprovada de dependências, executar na raiz da aplicação conforme scripts e ferramentas efetivamente disponíveis:

```bash
php artisan test
npm run build
```

Executar também o formatador e a análise estática efetivamente configurados; não presumir ferramentas instaladas. Comandos de produção, migrations, `storage:link` e otimização exigem ambiente correto, backup e autorização; processo de referência na fonte § 14, não uma instrução para execução agora. Nunca executar migrations destrutivas ou em banco não confirmado.

## Verificação e pronto

Testar acesso a publicados e 404 de rascunhos, slugs por idioma, relações, validação/persistência de leads inclusive falha de e-mail, rate limit, acesso ao admin, papéis, metadados e sitemap. Homologar responsividade, teclado e contraste, uploads, publicação, WhatsApp, e-mail, links e logs (§ 15). Uma tarefa só fica pronta com comportamento e critérios verificados, autorização e validação aplicadas, testes relevantes passando, interface responsiva quando aplicável, sem erros críticos e documentação/backlog atualizados (§ 17). Revisão independente e preparação de entrega não equivalem a aprovação de produção.
