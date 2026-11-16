# Segurança — DevStudio

> **Adendo 12/11/2026:** o fundador declarou concluído o checklist Plesk e autorizou desenvolvimento local, segundo Nexus; Engenharia não verificou evidências. Esta autorização prevalece sobre o bloqueio histórico de desenvolvimento, não sobre controles ou aprovação necessários para produção. Ver [Deploy](DEPLOYMENT.md).

**Data:** 12/11/2026. **Estado:** planejamento; nenhum controle implementado ou auditado por este documento.

## 1. Fonte, escopo e classificação

Fonte normativa: [Especificação técnica v1.0](../ESPECIFICACAO_TECNICA_DEVSTUDIO_WEBSITE.md), especialmente §§3, 4, 6–8, 10–12, 14–18 e 21. As referências abaixo usam esses números de seção.

- **RA — requisito aprovado:** consta da especificação; ainda exige implementação e evidência.
- **PT — proposta técnica:** detalhamento recomendado, não decisão já aprovada; validar com Engenharia e Nexus antes de incorporar.
- **DP — decisão pendente:** exige confirmação técnica, jurídica ou do fundador.

Este plano cobre site institucional, CMS `/admin`, leads, mídias e operação. Não cobre a segurança interna das aplicações SaaS independentes. Não autoriza pentest, acesso à produção, auditoria de hospedagem, instalação ou publicação. Conforme adendo, o desenvolvimento local foi autorizado; agentes não verificaram evidências da auditoria Plesk. A liberação local não substitui aprovação do fundador para produção.

**RA (§§3, 21):** PHP 8.5, Laravel 13, Filament 5, Blade, Livewire 4/Alpine quando necessários, Tailwind 4.1+, Vite e MySQL/MariaDB. Versões resolvidas e compatibilidade ainda não verificadas. Não adicionar Redis, S3/MinIO, Docker em produção, API independente, frontend separado ou microsserviços.

## 2. Ativos, fronteiras e ameaças

| Ativo/fronteira | Ameaça principal | Tratamento planejado | Base |
|---|---|---|---|
| Internet → login/admin | Força bruta, sessão roubada, elevação de papel | Autenticação, rate limit, sessão segura, Policies/Gates | RA §§6.3, 12; PT §3 abaixo |
| Editor → operações administrativas | Acesso direto a usuários, configurações ou leads | Negação no servidor, inclusive ações Livewire e em lote | RA §6.3; PT §3 |
| Visitante → formulário/banco | Spam, entradas maliciosas, exposição de contatos | Validação no servidor, CSRF, antispam e limitação persistente | RA §§3.4, 10, 12 |
| CMS → páginas públicas | XSS persistente, rascunho divulgado, URL perigosa | Sanitização, escape contextual, publicação filtrada, validação de URL | RA §§6.4, 8, 12; PT §§4–5 |
| CMS → filesystem | Upload executável, sobrescrita ou travessia de caminho | Extensão/MIME/tamanho, separação público/privado, nomes controlados | RA §§3.5, 12; PT §4 |
| Hospedagem → código e dados | Exposição de `.env`, backups ou banco | Document Root exclusivo em `public`, privilégios mínimos, HTTPS | RA §§3.3, 4.3, 12 |
| Aplicação → SMTP/links externos | Vazamento de dados, destino fraudulento, perda do lead | Persistir antes de notificar; URLs restritas e notificação mínima | RA §§10, 12; PT §§5–6 |
| Dependências/deploy → ambiente | Pacote vulnerável, artefato adulterado, regressão | Lockfiles, auditoria, testes e rollback | RA §§3.1, 12, 14–15; PT §8 |
| Backups/logs → operadores | Vazamento ou restauração que reintroduz dados excluídos | Acesso restrito, minimização e restauração controlada | RA §§12, 14.4; PT §§6–7 |

## 3. Identidade, sessão e autorização

**RA (§§6.1–6.4, 12):** autenticação obrigatória; Admin com acesso total; Editor gerencia conteúdo, sem administrar usuários ou configurações sensíveis. Policies/Gates no servidor e verificação de acesso ao painel. `noindex` não é controle de acesso.

### Matriz operacional proposta

As células abaixo são regras descritivas, não permissões já implementadas. O fundador decidiu, conforme Nexus: Editor apenas conteúdo, categorias e mídias, sem leads, widgets comerciais, usuários ou configurações globais. Detalhes operacionais não especificados continuam propostas.

| Operação | Admin | Editor | Classificação/base |
|---|---|---|---|
| Entrar no painel autenticado e autorizado | Permitido | Permitido | RA §6.3 |
| Criar/ler/editar/publicar/despublicar páginas, serviços, produtos, projetos e artigos | Permitido | Permitido | RA §§6.3–6.4 |
| Excluir conteúdo editorial e mídias vinculadas | Permitido com confirmação e integridade | Permitido somente no escopo editorial autorizado e com confirmação | PT; RA §7.3 exige integridade |
| Gerenciar categorias e uploads editoriais | Permitido | Permitido no escopo editorial; sem arquivos privados | Decisão do fundador; restrição a arquivos privados é PT |
| Criar/alterar usuários, papéis, configurações globais ou segredos | Permitido | Negado | Decisão do fundador; segredos por menor privilégio |
| Listar/ler/editar status e observações de leads | Permitido | Negado | Decisão do fundador |
| Ver totais, leads recentes e widgets com informações comerciais/pessoais | Permitido | Negado | Decisão do fundador; dashboard editorial separado é PT |
| Exportar/excluir leads ou executar ações em lote sobre dados pessoais | Somente fluxo aprovado, sem exportação genérica inicial | Negado | PT; RA Admin tem acesso total, mas descarte depende de política aprovada |

**PT:** negar por padrão operações não mapeadas; reaplicar Policies em endpoints, registros, relacionamentos, downloads e ações em lote. Não confiar em IDs, campos ocultos, botões invisíveis ou propriedades Livewire como autorização. Impedir atribuição de papel/status administrativo por mass assignment. Não criar cadastro público de administradores.

**RA:** senha forte, restrição administrativa e remoção dos usuários de teste (§12). **PT:** usar hashing e autenticação nativos, regenerar sessão após login, invalidar sessão ao sair e revogar acessos de contas desativadas. Cookies `Secure`, `HttpOnly` e `SameSite` compatível com o fluxo; validar HTTPS/proxy para evitar interpretação incorreta da origem. Tempo de sessão e recuperação de acesso: **DP** antes do lançamento.

**RA como recomendação (§12):** dois fatores é melhoria recomendada, não obrigação já aprovada. **DP:** decidir adoção; se habilitado, testar recuperação e revogação antes do lançamento, sem guardar códigos/segredos em logs ou Git.

## 4. Entradas, conteúdo e arquivos

- **RA (§§3.1, 10.2, 13.1):** Form Requests/validação Laravel no servidor; normalizar e limitar entradas. Decisão do fundador: permitir Outro sem serviço, com campo específico. **PT:** `solution_type` `service|other`; serviço exige `service_id` publicado e elegível; Outro exige ID nulo e descrição específica. Preservar intenção/histórico ao remover serviço; não decidir exclusão destrutiva sem aprovação. Limites por campo e status inicial definidos no servidor; visitante não envia papel, status comercial nem notas internas. Origem/UTM são entrada não confiável.
- **RA (§§8.1, 12):** schemas de blocos conhecidos, sem HTML/JS/CSS arbitrário; sanitizar conteúdo rico. **PT:** allowlist de elementos/atributos e protocolos no salvamento e renderização segura; escape padrão Blade para texto, atributos e metadados, serialização segura para JSON-LD. Não inserir conteúdo do CMS diretamente em scripts ou renderização não escapada.
- **RA (§6.4), decisão do fundador via Nexus:** `published` com data nula é imediato, data futura agenda, data atual ou passada é elegível; sem data final na v1. Consultas, sitemap e rotas aplicam a mesma regra; rascunho, futuro ou inexistente responde 404. **PT:** impedir cache público de previews/admin e vazamento em relacionamentos ou pesquisas. Se houver preview, exigir autorização; `noindex` sozinho não basta.
- **RA (§§3.5, 12):** validar MIME, extensão e tamanho no upload; público apenas para imagens do site; privado fora do disco público. **PT:** começar com allowlist de formatos raster realmente suportados; rejeitar SVG enviado pelo CMS, HTML, scripts, dupla extensão executável e MIME divergente. SVG institucional futuro requer avaliação separada; a recomendação de arte vetorial não autoriza upload irrestrito.
- **PT:** gerar nomes de arquivo no servidor, não aceitar caminhos do cliente, impedir travessia/sobrescrita e execução de scripts no storage público; limitar dimensões e volume conforme capacidade real. Aplicar autorização em upload, remoção e download; validar temporários do Livewire e sua limpeza. Limites numéricos dependem da auditoria Plesk (**DP**), não foram medidos.
- **PT:** usar Eloquent/consultas parametrizadas; não interpolar entrada em SQL, ordenação, caminho de arquivo ou nome de template. Exclusão de mídia deve preservar referências válidas e não apagar arquivo compartilhado por outro conteúdo.

## 5. Requisições, abuso e links externos

**RA (§§10.2, 12, T5.02):** CSRF nos formulários web e alterações de estado; login e formulário comercial com rate limit; honeypot no formulário. Não isentar endpoints Livewire sem justificativa de segurança.

**PT:** mudanças de estado não usam GET. Limites distintos para login e leads, respostas genéricas de autenticação, erro 429 acessível sem revelar existência de conta/lead. Usar cache persistente `file` ou `database` conforme auditoria (§3.4), verificar concorrência e não confiar em cabeçalhos de IP de proxy não autorizado. **DP:** valores, janelas, proxy confiável e critérios de ajuste; testar limiar e recuperação sem testes de carga em produção. Honeypot não pode bloquear navegação assistiva legítima; CAPTCHA/serviço externo não está aprovado.

**RA (§§5.5, 10.3):** URLs externas opcionais de produtos e `wa.me` configurados no CMS; sem API oficial WhatsApp na v1. **PT:** aceitar destinos web externos HTTPS válidos e rotas locais previstas nos CTAs; rejeitar `javascript:`, `data:`, URLs relativas a protocolo e esquemas inesperados. Validar também links de conteúdo rico e redes sociais; `mailto:`/`tel:` somente nos campos específicos e normalizados. Nova aba usa `rel="noopener noreferrer"`. Não fazer fetch automático de URL informada pelo CMS nem criar redirecionador aberto. Não anexar dados pessoais/segredos à mensagem WhatsApp, URL, UTM ou analytics.

**PT:** avaliar CSP compatível com Filament/Livewire/Alpine, proteção de enquadramento, `X-Content-Type-Options` e `Referrer-Policy` em homologação. Não prescrever CSP incompatível nem ativar HSTS com subdomínios antes de validar domínio/HTTPS. Cabeçalhos são defesa adicional, não substituem sanitização.

## 6. Dados pessoais, segredos e registros

**RA (§§7.1, 10.5, 12, 17):** minimizar coleta, explicar finalidade, exigir aceite explícito e registrar `privacy_accepted_at`; política jurídica revisada antes de publicar. Não declarar conformidade LGPD só por haver checkbox. Campos comerciais obrigatórios e opcionais seguem §10.1; não solicitar dados sensíveis ou anexos no formulário inicial.

**DP jurídico/fundador:** responsável/controlador e canal de atendimento; fundamento legal e texto de consentimento; destinatários/operadores (hospedagem, SMTP, analytics, WhatsApp); transferências; prazo de retenção por finalidade, descarte e atendimento a direitos. Nenhum prazo está aprovado. Aplicar a decisão a banco, e-mails, logs e backups; exclusão não pode ser simulada apenas ocultando registro. Não automatizar descarte até política aprovada.

**PT:** manter origem/UTM limitadas e sem query string contendo dados pessoais; não enviar nome, e-mail, telefone, mensagem ou notas internas ao GA4. Notificação SMTP deve conter o mínimo necessário, preferencialmente referência ao lead acessível após login. Destinatários comerciais e SMTP são **DP**, não contatos inventados. Persistência do lead precede envio; falha SMTP não apaga nem impede registro (§§3.4, 10.2). Evitar duplicidade de envio/registro sem expor se um contato já existe.

**RA (§§4.3, 12):** `.env` privado, fora do Git; `APP_DEBUG=false` e HTTPS em produção; logs sem senhas, tokens ou dados pessoais desnecessários. **PT:** `.env.example` só com placeholders; proteger `APP_KEY`, credenciais de banco/SMTP, sessões e backups com acesso mínimo. Não rotacionar `APP_KEY` cegamente: avaliar dados criptografados e sessões. Em vazamento, restringir acesso, revogar/rotacionar a credencial afetada e verificar cópias/uso indevido; apagar o arquivo atual não limpa histórico.

**PT:** registrar resultado e identificador técnico de falhas sem corpo do formulário, cookies, tokens CSRF, headers de autorização ou credenciais SMTP. Auditoria mínima de mudanças de papel, publicação e ações sensíveis, com ator/ação/horário e sem copiar conteúdo pessoal. Acesso e retenção de logs são **DP**. Evidências de teste usam dados sintéticos e redação de informações sensíveis.

## 7. Hospedagem, backup, restauração e incidentes

**RA (§§3.3, 4, 12, 14):** raiz pública somente `public`; nunca expor `.env`, `vendor`, código privado ou backups. Usuário de banco exclusivo, sem `root`, restrito ao banco do site. Confirmar PHP-FPM/CLI, extensões, Composer, banco exato, SSL, permissões e suporte a storage antes da implementação. Escrita em `storage` e `bootstrap/cache` sem permissões globais indiscriminadas (**PT**). Sessões `file`, fila `sync`; sem Redis ou worker permanente presumido.

**RA (§14.4):** backup diário do banco; storage e configurações necessárias protegidos; retenção definida; restauração testada em ambiente separado antes do lançamento e periodicamente. **DP:** retenção, destino, responsáveis, capacidade, frequência do teste e objetivos de perda máxima de dados/tempo de recuperação (RPO/RTO). Nenhum backup ou teste foi verificado.

**PT — procedimento de restauração a detalhar e executar futuramente por responsável autorizado:**

1. Selecionar conjunto consistente de banco/storage e release compatível; conferir integridade e acesso restrito.
2. Restaurar em ambiente isolado, sem SMTP, analytics ou integrações reais e sem credenciais de produção reutilizadas; controlar rigorosamente qualquer dado pessoal real.
3. Verificar registros, mídias, autorização, publicação, configuração e operação com dados de teste; registrar duração e perdas frente aos objetivos aprovados.
4. Reaplicar exclusões/restrições realizadas após a cópia, conforme política jurídica; impedir retorno inadvertido de dados descartados.
5. Documentar resultado, falhas e correções; proteger ou descartar cópias de teste conforme autorização/política. Falha de restauração bloqueia lançamento (§18).

**RA (§14.5):** backup antes de migrations, release anterior preservada, rollback de código e dados documentado; não executar migration destrutiva sem plano. **PT:** rollback não deve sobrescrever leads recentes; conciliar dados antes de restaurar banco. Em incidente, registrar horário/escopo, conter acesso e preservar evidências restritas; Nexus e fundador decidem ações operacionais e comunicação externa com revisão jurídica. Não inventar prazo legal ou enviar notificações sem aprovação.

## 8. Dependências e evidências futuras

**RA (§§3.1, 12, 15):** Composer resolve versões exatas, `composer.lock` versionado; sem dependências `dev` em produção; atualizações controladas. **PT:** versionar lockfile de assets ao existir, usar instalação reprodutível e revisar origem/licença/manutenção de pacotes. Auditar PHP e JavaScript no desenvolvimento/CI, não instalar pacotes para executar esta documentação.

Verificações futuras: `composer validate --strict`, `composer audit`, `npm audit` e testes Pest/PHPUnit conforme ferramentas efetivamente adotadas. São referências, **não comandos já executados**. Registrar release/commit, versões resolvidas, ambiente, data, saída e triagem de alertas; auditoria de dependências não prova ausência de vulnerabilidades. Bloquear vulnerabilidade crítica/alta explorável sem correção/mitigação demonstrada (**PT**); exceções precisam risco, responsável e aprovação, nunca aceite automático.

## 9. Checklist bloqueante e critérios de evidência

Os controles deste checklist estão **não verificados por este documento**. Engenharia implementa/corrige; Qualidade revisa independentemente; Nexus coordena; fundador aprova publicação. Desenvolvimento local autorizado conforme adendo; produção não.

- [ ] Evidências de compatibilidade Plesk verificadas por responsável, incluindo Document Root, versões, SSL e banco (§§4, 16 Fase 0); declaração do fundador não substitui evidência técnica para produção.
- [ ] Domínio, SMTP, destinatários e parâmetros operacionais decididos; permissões do Editor aprovadas devem ser implementadas e testadas; credenciais por canal seguro.
- [ ] Visitante sem acesso a `/admin` ou ações/dados privados; Editor negado ao chamar diretamente usuários, configurações e leads, inclusive IDs alternativos, widgets, uploads e ações em lote (§§6.3, 15).
- [ ] Testes Admin/Editor positivos e negativos; sem contas de teste em produção; política de sessão e eventual 2FA testadas (§12).
- [ ] Conteúdo não publicado retorna 404 e não aparece em listagens/sitemap/cache público; painel/preview sem indexação (§§6.4, 11, 15).
- [ ] Casos sintéticos de XSS em texto, atributos, links e conteúdo rico não executam; uploads proibidos são rejeitados e permitidos não executam código (§§3.5, 8, 12).
- [ ] CSRF inválido é rejeitado; rate limit de login/leads funciona com backend persistente e recuperação da janela; erro acessível e sem perda de lead (§§3.4, 10, 12).
- [ ] Formulário valida entradas/aceite no servidor, grava antes de notificar e preserva lead quando SMTP falha; campos internos não são alteráveis pelo público (§§10, 15).
- [ ] HTTPS/debug/raiz pública e inacessibilidade de arquivos privados confirmados em ambiente autorizado; segredos e dados pessoais ausentes de Git, respostas públicas e logs (§§4.3, 12).
- [ ] URLs externas/protocolos e proteção de nova aba testados; nenhuma informação pessoal enviada por URLs/eventos sem necessidade (PT).
- [ ] Política de privacidade, retenção/descarte e direitos revisados juridicamente, conteúdo e destinatários aprovados; sem pixels não autorizados (§§10.5, 11.3).
- [ ] Dependências resolvidas/auditadas, testes registrados, alertas triados e sem bloqueadores pendentes (§§12, 15).
- [ ] Backups protegidos/configurados; restauração isolada comprovada; rollback sem perda silenciosa de leads documentado (§§14–15, 18).
- [ ] Revisão independente e aprovação explícita do fundador antes da publicação.

Cada evidência deve informar cenário, resultado esperado/obtido, versão/commit e ambiente, sem credenciais nem dados reais desnecessários. Classificar falhas: crítica (controle administrativo/segredo amplamente comprometido), alta (acesso indevido a dados ou execução de script), média (defesa parcial com impacto limitado), baixa (endurecimento sem exploração demonstrada). Gravidade depende do caso confirmado; **nenhum defeito de implementação foi diagnosticado nesta preparação**.

## 10. Integração documental e limitações

Este arquivo é apenas o plano de segurança. Documentos relacionados, cujos nomes foram conferidos no diretório, mas cujo conteúdo ainda depende da revisão integrada:

- [PRD](PRD.md): finalidade, escopo e critérios de aceite.
- [Arquitetura](ARCHITECTURE.md): fronteiras, stack e armazenamento.
- [Modelo do CMS](CMS-MODEL.md): entidades, Policies e dados pessoais.
- [Deploy](DEPLOYMENT.md): auditoria Plesk, configuração, backup e rollback.
- [Design system](DESIGN-SYSTEM.md): estados acessíveis, formulários e mensagens de erro.
- [Diretrizes de desenvolvimento](DEVELOPMENT-GUIDELINES.md): validação, práticas de código e testes.
- [Estratégia SEO](SEO-STRATEGY.md): indexação, analytics e URLs públicas.
- [Tarefas](TASKS.md): bloqueios, responsáveis e evidências de execução.

A [fonte](../ESPECIFICACAO_TECNICA_DEVSTUDIO_WEBSITE.md) mantém os requisitos rastreáveis; esses links não representam aprovação dos demais documentos.

Não houve execução de aplicação, testes de segurança, auditoria Plesk, consulta de dependências resolvidas, acesso a contas/produção ou verificação dos demais documentos. Pendências deste plano não devem ser confundidas com controles entregues.
