# Implantação e operação no Plesk

> **Adendo 12/11/2026 — prevalece sobre os registros anteriores de bloqueio:** o fundador declarou concluído o checklist técnico Plesk e autorizou desenvolvimento local; informação transmitida por Nexus, sem relatório ou evidências verificadas por Engenharia. Não se presume versão do banco, domínio, SMTP ou compatibilidade comprovada. A declaração não autoriza acesso à hospedagem, deploy ou produção; obter evidências antes de qualquer liberação operacional.

**Estado: preparação documental; hospedagem não auditada, aplicação não implementada e nenhuma publicação autorizada.** Todos os itens abaixo estão não verificados. A auditoria da hospedagem e seu relatório de compatibilidade são pré-requisitos para iniciar a implementação, conforme [especificação, §§ 4, 14, 15 e 16](../ESPECIFICACAO_TECNICA_DEVSTUDIO_WEBSITE.md#4-hospedagem-plesk-requisitos-e-auditoria-obrigatória). Preparação não autoriza alterações em produção: cada publicação e alteração em produção exige aprovação explícita do fundador.

## Decisões e pendências

### T3.08 — singleton MySQL; ensaio descartável local

**Ensaio local limitado em 13/11/2026:** com autorização do fundador transmitida pelo Nexus, foi criado um container temporário `mysql:8.4` (imagem observada MySQL 8.4.13, digest `sha256:a81d043930e51745522bd5c430961cf8286f0a23f0a9a2a6e0108f7ea71b0902`) com schema sintético dedicado, porta dinâmica ligada somente a `127.0.0.1` e sem mounts/volumes persistentes. A primeira execução usou a bridge padrão; a rede sem outros endpoints foi confirmada apenas na reprodução posterior com rede própria abaixo. Antes do primeiro DDL em cada execução, `docker inspect` confirmou `Mounts=[]` e consulta a `information_schema` confirmou zero tabelas no schema alvo. Migrations-base criaram users/CMS vazios; foi inserida uma linha sintética em `site_settings` e feito dump de backup antes da migration T3.08. Credencial root aleatória ficou em arquivo temporário com modo 0600 apenas durante o ensaio, nunca no `.env` ou em mensagens, e foi removida ao final.

Resultados: `2026_11_12_000001_enforce_single_site_setting` aplicou a coluna virtual `singleton_guard` (`EXTRA=VIRTUAL GENERATED`, expressão `1`) e índice único (`NON_UNIQUE=0`); segunda inserção foi recusada. `migrate:rollback --step=1` removeu índice/coluna e preservou a linha sintética. Para o precheck, duas linhas sintéticas fizeram a migration falhar com “Mais de uma configuração existe”; contagem permaneceu 2 e coluna não foi criada. Removida somente a segunda fixture sintética, reexecução da migration e rollback passaram. Dump foi importado em schema restore no mesmo container e recuperou uma linha. Container temporário removido e ausência conferida (`docker ps -aq --filter name=...` vazio); container persistente `devstudio-site-db-1` permaneceu ativo e inalterado. Artefatos sem credenciais retidos no diretório temporário indicado: backup sintético, log do precheck e erro 1062 esperado.

Sequência executada (nomes, porta e credencial efêmeros omitidos): `docker run -d --publish 127.0.0.1::3306 --tmpfs /var/lib/mysql:rw,noexec,nosuid,size=1g ... mysql:8.4`; validar `docker inspect`/`docker port` e zero tabelas no schema; aplicar migrations base de `users` e `2026_11_12_000000_create_cms_tables.php` com `php artisan migrate --path=... --force`; inserir fixture sintética e `mysqldump --single-transaction`; aplicar `2026_11_12_000001_enforce_single_site_setting.php`; testar segunda inserção e `php artisan migrate:rollback --step=1 --force`; semear duas fixtures para falha do precheck, conferir preservação, retirar somente a fixture extra, reaplicar e reverter; importar o backup em schema separado e conferir uma linha; `docker rm -f <container-temporário>` e confirmar ausência.

**Reprodução com trilha completa:** executada em novo container temporário, sem volume, em 13/11/2026. Log sanitizado de 57 linhas: `/private/var/folders/zd/3395mwk95p52mmggfd7txcf80000gn/T/opencode/devstudio-t308-evidence.log`; backup correspondente de 19.482 bytes: `devstudio-t308-evidence.sql`; logs de precheck e erro UNIQUE: `devstudio-t308-precheck-evidence.log` e `devstudio-t308-unique-error.log`. Os quatro arquivos estão em modo 0600, sem credenciais; o log confirma porta aleatória loopback, tmpfs, `Mounts=[]`, schema inicial vazio, execução/rollback, linha recuperada e container temporário ausente. QA fez uma reprodução independente separada; parecer limitado registrado abaixo. O container `devstudio-site-db-1` não foi usado.

**Trilha reforçada após revisão QA:** container e rede bridge próprios; `/private/var/folders/zd/3395mwk95p52mmggfd7txcf80000gn/T/opencode/devstudio-t308-proof.log` registra a porta dinâmica, `DB_HOST=127.0.0.1`, `DB_PORT` e schema usados nas chamadas Artisan; a identidade obtida pela conexão Laravel (hostname, schema, UUID, versão) coincide com a conexão do container. `docker network inspect` mostrou um endpoint (somente o container do ensaio). Container e rede foram removidos e verificados ausentes. Backup `devstudio-t308-proof.sql` (19.476 bytes), log e saídas de erro esperadas `devstudio-t308-proof-precheck.log` e `devstudio-t308-proof-unique.log` estão em modo 0600 e não contêm credenciais. A trilha não testa concorrência nem falhas entre etapas DDL.

**Pareceres QA independentes limitados (13/11/2026):** QA repetiu o ensaio em MySQL 8.4.13 descartável, confirmou migration/índice UNIQUE bloqueando duplicata, rollback preservando a linha, precheck abortando com duplicatas sem DDL/perda e retry aprovado; considerou T3.08 apta somente para esse escopo. Depois, QA revisou a trilha reforçada e considerou que ela supre a lacuna de identidade/isolamento; parecer apto para o ensaio delimitado. QA não verificou diretamente remoção do container/rede nem preservação do container persistente: isso foi conferido por Engenharia no log e em consulta local após limpeza. Log e dump independentes de QA permanecem no caminho indicado pela mensagem da QA e não devem ser removidos; Engenharia não os inspecionou nesta sessão. Não estender o aceite a concorrência, falha parcial DDL, ambiente de destino ou operação.

**Limites:** não testados concorrência, falhas entre etapas DDL, restauração fora desse container, nem compatibilidade com o MySQL/MariaDB do Plesk. Isto não autoriza migration em banco persistente nem produção. Sem escrita ou leitura do `.env` real; sem contato com o container persistente, volumes persistentes, dados reais, preview ou produção.

1. Em MySQL descartável explicitamente autorizado, isolado e da mesma versão/configuração do destino, testar migration e rollback com zero e uma linha, tentativa de segunda inserção, recusa de duplicatas sem perda, concorrência e falhas entre as operações DDL; inspecionar coluna, expressão, índice único e registro na tabela `migrations`. Repetir recuperação e conferir dados e esquema. Registrar evidências sem segredos.
2. Antes de aplicação autorizada, identificar destino e responsáveis, obter backup consistente protegido e testar restauração em ambiente isolado; definir release anterior, decisão de parada e plano de recuperação. Sem restauração verificada, não prosseguir.
3. Abrir janela sem escritas: interromper todos os escritores, inclusive jobs e integrações, e confirmar ausência de transações ativas antes da inspeção. Contar registros de `site_settings` no destino autorizado; se houver mais de um, parar e obter decisão explícita de conciliação, sem apagar ou escolher registros automaticamente.
4. Inspecionar previamente coluna `singleton_guard`, sua expressão e índice, além do registro em `migrations`. Divergência ou vestígio de execução anterior exige parada e diagnóstico; não repetir cegamente. Sob a janela sem escritas, aplicar somente após aprovação, verificar esquema, índice, registro da migration e preservação dos dados antes de liberar escritores.
5. DDL MySQL pode efetivar etapas separadamente: em falha, manter bloqueio de escrita, capturar erro e inspecionar novamente coluna, índice e `migrations`. Não presumir rollback transacional nem executar `down` ou nova migration às cegas. Um responsável autorizado deve escolher e testar a correção específica ou restauração do backup; preservar alterações posteriores e obter aprovação antes de qualquer perda ou sobrescrita. Validar consistência e só então reabrir escrita.

### T3.09 — staging e publicação de imagem; código local não ativado

Decisão do fundador: originais devem permanecer privados e somente derivados sanitizados podem ser públicos; storage privado local no Plesk. Nexus esclareceu que há um único site, sem ambiente separado de homologação; “staging” aqui significa pasta temporária privada no mesmo servidor, não outro ambiente. `config/livewire.php` fixa uploads temporários em `local` (`storage/app/private`), `file|max:5120`, sem preview temporário e com limpeza de arquivos expirados habilitada; path/permissões efetivos ainda precisam ser verificados no host. A regra de 5 MiB é validada antes de Livewire copiar o arquivo para seu storage, mas o corpo já chegou ao PHP e pode ocupar o diretório temporário PHP durante a requisição; `upload_max_filesize=1024M` reportado deixa exposição potencial a uso de espaço até esse teto. Não alteramos configuração do host; dimensionamento/mitigação desse limite é gate operacional. `UpdateSiteSettingImage` não persiste o original: valida e lê orientação EXIF JPEG, decodifica e reencoda para PNG em `local:site-settings/.staging` (diretório 0700); para orientação 2–8 ajusta pixels (orientações 5–8 trocam eixos) antes do derivado. A extensão `exif` é necessária para aceitar JPEG; sem ela o serviço falha fechado. A publicação cria o destino com `fopen('x+b')` para reserva exclusiva e `umask(0777)` para modo inicial 000. Copia o staged para inode distinto, confere tamanho e SHA-256 com o PNG sanitizado em memória, fecha o arquivo e só então altera o destino para 0644; mutação do staging entre validação e cópia falha antes de expor o destino. Se a criação exclusiva, modo 000, cópia, hash, close ou chmod falhar, tenta remover apenas o arquivo que criou, conferindo inode; colisão não é sobrescrita. A garantia contra leitura parcial depende de o webserver não poder ignorar modo 000 (por exemplo, privilégio root); validar usuário/permissões reais do Plesk antes de integrar. Staging criado pela chamada é removido em falhas anteriores à publicação, inclusive início/consulta da transação; se a limpeza falhar, registra aviso. Após publicação, em falha de save/commit preserva o derivado potencialmente referenciado. Plesk continua sem validação de identidade efetiva, ACL/owner, paths ou symlinks; por isso o código não tem rota/caller e não está aprovado para ativação. GD remove dados anexados não rasterizados no reencode. Não há campo/caller editorial conectado; não habilitar o endpoint temporário nem painel operacional. Falha de persistência pode deixar derivado público órfão para reconciliação manual; nenhum coletor geral ou limpeza automática existe. Testes usam SQLite e discos locais fake; não verificam symlink/ACL/owner, troca concorrente de diretório, permissões efetivas do webserver ou entrega via webserver no Plesk real. `storage:link` e exposição de `/storage/...` não foram executados/verificados. Limites provisórios: Livewire até 5 MiB por upload temporário; serviço até 5 MiB por original, 2048 px por eixo e 4 MP no total; escolhidos conservadoramente abaixo do ensaio local anterior de 4000×4000 (~247 MiB RSS), mas sem benchmark de concorrência e sem aceite do host. Preview temporário está desabilitado até definir UX que não exponha o original.

Nexus/fundador reportaram (não verificado por Engenharia): PHP 8.5.12, `upload_max_filesize=1024M`, `post_max_size=1024M`, `memory_limit=1G`, `max_execution_time=300s` e 50 GB de espaço disponível; um site sem ambiente de homologação e pasta temporária privada no mesmo host. Esses máximos do PHP não são recomendação de tamanho de imagem nem aprovação operacional; limite de 5 MiB de Livewire só valida após o corpo chegar ao PHP e não reduz sozinho uso de temporário PHP. Ainda confirmar no Plesk (FPM/CLI quando diferirem), sem segredos: (1) os dados reportados e extensões GD, EXIF, WebP (`imagewebp`) e `fileinfo`; (2) espaço/quota efetivamente disponíveis; (3) paths absolutos/canônicos da pasta temporária privada (`storage/app/private`) e destino público, URL/mapeamento de entrega e confirmação de que pasta privada está fora do document root; (4) usuário/grupo do PHP-FPM e owner/group, modos e ACL; (5) symlinks em cada componente, destino real e política webserver; (6) comprovar que `fopen('xb')` cria modo 000, chmod final publica somente arquivo completo e o webserver não lê arquivo mode 000; (7) confirmar efetivamente no host disk, limite 5 MiB, preview desabilitado e limpeza de temporários em `config/livewire.php`, além de garantir que original não seja público; (8) decidir como limitar o consumo do diretório temporário PHP antes da validação da aplicação. Valores devem vir de inspeção autorizada, sem `.env` ou credenciais.

**QA independente (13/11/2026):** confirmou no snapshot local a detecção de mutação do staging por SHA-256 e a normalização EXIF 1–8; os probes e testes passaram. Isso não valida o webserver em modo 000, configuração efetiva do Plesk, caminhos/permissões, nem autoriza ativação. Upload segue sem rota/caller.

### Banco local de desenvolvimento

Foi provisionado somente localmente MySQL 8.4 LTS via `compose.yaml` (versão observada 8.4.13), escolhido pela linha estável; a versão do banco no Plesk é desconhecida e não se presume equivalência. O serviço usa `127.0.0.1:3307`, volume exclusivo `devstudio_mysql_data` e credenciais locais no `.env` ignorado. Em verificação local, `docker compose ps` informou `healthy` e conexão PDO do usuário da aplicação retornou `SELECT 1`. Não foram executadas migrations. Veja comandos e cuidados no [README](../README.md#banco-local-com-docker). Isso não constitui auditoria de produção nem teste de backup ou restauração.

- **Requisito aprovado:** PHP 8.5, Laravel 13, Filament 5, Plesk, MySQL ou MariaDB; `public/` como única raiz web; dependências sem pacotes de desenvolvimento em produção. PHP-FPM é preferencial quando disponível. Versões exatas de pacotes ainda serão resolvidas e fixadas no `composer.lock`.
- **Proposta operacional:** preparar e testar uma release identificável fora de produção, manter a anterior e registrar evidências de cada etapa; o procedimento concreto depende da auditoria e dos recursos do provedor.
- **Decisões pendentes:** domínio e destino, versão e escolha exata do banco, SMTP, retenção de backups e de dados pessoais, conteúdo e autorizações de publicação, acesso e janela de implantação. Não presumir que backups, monitoramento ou infraestrutura existam.
- Redis está fora do escopo inicial; não presumir Docker em produção nem worker permanente. Cache `file` ou `database`, sessão `file` e fila `sync` são o ponto de partida aprovado (§ 3.4).

## Auditoria obrigatória — Plesk, não realizada

Registrar para cada item resultado, data, evidência sem segredos e responsável; falhas ou restrições impedem avançar até decisão documentada (§§ 4 e 16, tarefas T0.01–T0.08).

- [ ] Identificar domínio, assinatura e ambiente corretos; confirmar autorização e acesso somente para a auditoria.
- [ ] Verificar PHP 8.5 e handler PHP-FPM do domínio, configuração efetiva e OPcache; comparar versão, configuração e extensões do FPM com as do PHP CLI. Confirmar caminho absoluto do binário CLI e seu `php.ini`.
- [ ] Verificar Composer 2, SSH/terminal ou alternativa segura e que o Composer roda efetivamente com o PHP CLI 8.5 confirmado, não outro PHP do `PATH`.
- [ ] Verificar em FPM e CLI: Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, `pdo_mysql`, Session, Tokenizer, XML e `intl`; conferir também requisitos efetivos das dependências resolvidas.
- [ ] Confirmar MySQL ou MariaDB e versão, conectividade, charset `utf8mb4`, collation suportada, conta exclusiva com privilégios limitados e capacidade de backup/restauração. Não usar root na aplicação.
- [ ] Confirmar Document Root exclusivamente no `public/` da aplicação; código, `.env`, dependências, backups e arquivos privados fora da web. Caso não seja possível, solicitar ajuste ou definir alternativa segura antes de prosseguir; nunca expor a raiz Laravel nem mover `index.php` para ela.
- [ ] Confirmar permissões mínimas de escrita em `storage` e `bootstrap/cache`, usuário do processo, limites de memória e upload, espaço e suporte a symlinks para o disco público. Não expor arquivos privados pelo link.
- [ ] Verificar SSL/HTTPS e certificado válido, acesso SMTP e parâmetros ainda pendentes, logs sem segredos ou dados pessoais desnecessários, e disponibilidade de tarefas agendadas no Plesk.

### Referências futuras de inspeção

Não executar agora. Somente após autorização, identificação do servidor e confirmação dos caminhos reais. Os marcadores abaixo não são caminhos reais; substituir todos antes do uso. Não imprimir credenciais nem `.env`.

```bash
<CAMINHO_ABSOLUTO_PHP_85> -v
<CAMINHO_ABSOLUTO_PHP_85> --ini
<CAMINHO_ABSOLUTO_PHP_85> -m
<CAMINHO_ABSOLUTO_PHP_85> <CAMINHO_ABSOLUTO_COMPOSER_PHAR> --version
```

O último comando pressupõe um Composer em formato PHAR acessível: se houver apenas outro formato, verificar como vinculá-lo ao binário correto, sem supor que `composer` no PATH use o mesmo PHP. Estas inspeções cobrem CLI; conferir FPM separadamente pelo painel/configuração efetiva sem disponibilizar diagnósticos sensíveis publicamente.

## Preparação de release e publicação futura

1. Concluir auditoria e registrar decisão de compatibilidade; definir destino, responsável, janela, acesso, método de transferência e plano de retorno. Obter aprovação explícita do fundador para produção e publicação.
2. Fora de produção, revisar a versão aprovada, executar testes e build Vite; verificar artefato e assets compilados. A resolução de pacotes e os lockfiles ocorrerão na implementação, não nesta preparação. Não presumir Node como processo permanente em produção.
3. Antes de qualquer mudança, verificar espaço e backup protegido de banco, storage e configuração necessária, inclusive segredos em local seguro, fora da raiz web. Registrar identificação, horário e escopo do backup. Planejar migrations e compatibilidade entre versões; bloquear migrations destrutivas sem plano de dados aprovado.
4. **Antes do lançamento**, testar restauração de banco, arquivos e configuração em ambiente isolado: conferir integridade, acesso à aplicação e correspondência entre banco e mídia; registrar evidências sem dados pessoais. Um backup criado sem teste não é restauração verificada. Configurar backup diário do banco e backup de storage conforme §§ 3.3, 3.5 e 14.4; frequência adicional e retenção permanecem pendentes.
5. Com aprovação, disponibilizar release e assets no destino privado, instalar dependências PHP de produção usando o binário confirmado e configurar ambiente com `APP_DEBUG=false`; proteger `.env`. Verificar destino do banco antes de migrations, após backup e aprovação explícita. Aplicar link de storage apenas se suportado e seguro; ajustar permissões mínimas e otimização Laravel conforme versão implementada.
6. Validar HTTPS, páginas públicas, `/admin` protegido, permissões, uploads, formulário e persistência de lead mesmo com falha de e-mail, notificação SMTP, WhatsApp e logs sem erros críticos. Usar dados de teste autorizados e plano de limpeza aprovado; não tratar recebimento de e-mail como prova isolada da persistência. Verificar conteúdo e direitos antes de publicar.
7. Registrar versão, horário, resultados, evidências e decisão de manter ou reverter. Critérios de lançamento completos constam na [especificação, § 15](../ESPECIFICACAO_TECNICA_DEVSTUDIO_WEBSITE.md#15-testes-e-critérios-de-aceite).

### Referências futuras de comandos

Não executar agora. Pressupõem aplicação implementada, lockfiles existentes, release e diretório confirmados, ferramentas compatíveis e autorização apropriada. Build ocorre fora de produção. A instalação no servidor abaixo pressupõe Composer PHAR; ajustar somente após auditoria.

```bash
# Em ambiente de desenvolvimento/CI, na raiz da release confirmada:
npm ci
npm run build

# Na raiz privada da release, após aprovação para produção:
<CAMINHO_ABSOLUTO_PHP_85> <CAMINHO_ABSOLUTO_COMPOSER_PHAR> install --no-dev --optimize-autoloader
# Somente após verificar banco e backup, e aprovar a migration:
<CAMINHO_ABSOLUTO_PHP_85> artisan migrate --force
# Somente se symlinks forem permitidos e o destino seguro:
<CAMINHO_ABSOLUTO_PHP_85> artisan storage:link
<CAMINHO_ABSOLUTO_PHP_85> artisan optimize
```

## Reversão de código e dados

- Definir previamente quem decide e executa a reversão, gatilhos de falha, janela e comunicação. Guardar release anterior identificada, assets e configuração compatível. Não assumir que trocar apenas o código reverte migrations.
- Diante de falha crítica, preservar evidências e, com autorização aplicável, interromper mudanças e recuperar a release anterior pelo método validado para o Plesk; conferir configuração, cache e acesso. Verificar páginas, admin, formulário e logs.
- Se o esquema ou dados forem incompatíveis, avaliar reversão de migration ou restauração coordenada do banco e storage em ponto consistente. Restaurar pode apagar leads ou alterações recebidas depois do backup: identificar e preservar essas alterações e obter aprovação explícita antes de qualquer perda ou sobrescrita. Não executar rollback destrutivo automaticamente.
- Testar primeiro o procedimento em ambiente isolado; registrar resultado e limitações. Sem backup recuperável e plano compatível, bloquear publicação (§§ 14.5 e 18).

## Scheduler da aplicação

Somente se funcionalidades futuras precisarem de tarefas Laravel, avaliar uma única tarefa Plesk por minuto com PHP 8.5 e caminho absoluto da aplicação, após autorização e validação. É scheduler da aplicação, não rotina dos agentes; não criar tarefa agora. Não pressupor fila assíncrona ou worker permanente (§§ 3.4 e 14.3).
