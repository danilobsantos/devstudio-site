# DevStudio — Inovação Digital (Website Institucional & CMS)

Plataforma institucional e comercial desenvolvida em **Laravel 13**, **Filament 5**, **Tailwind CSS 4** e **PHP 8.5**, com CMS integrado para gestão de conteúdos e motor resiliente de captação de leads comerciais.

Documento mestre: [ESPECIFICACAO_TECNICA_DEVSTUDIO_WEBSITE.md](ESPECIFICACAO_TECNICA_DEVSTUDIO_WEBSITE.md)  
Backlog técnico: [docs/TASKS.md](docs/TASKS.md) | Sistema visual: [DESIGN.md](DESIGN.md)

---

## 🛠️ Stack Tecnológica

- **Runtime:** PHP 8.5
- **Framework:** Laravel 13
- **Painel & CMS:** Filament 5
- **Frontend:** Blade, Livewire 4, Alpine.js
- **Estilização & Build:** Tailwind CSS v4, Vite 8
- **Banco de Dados:** MySQL 8.4 (desenvolvimento via Docker) / MariaDB (produção)
- **Testes:** PHPUnit / Pest (>135 testes automatizados em SQLite `:memory:`)

---

## 🧭 Estrutura de Rotas Públicas

| Rota | Descrição |
|---|---|
| `/` | Home institucional com seções modulares configuráveis pelo CMS |
| `/solucoes` | Catálogo das 6 soluções e serviços de tecnologia |
| `/solucoes/{slug}` | Detalhes da solução com entregáveis e CTA comercial |
| `/produtos` | Vitrine comercial de produtos SaaS da DevStudio |
| `/produtos/{slug}` | Apresentação comercial do produto |
| `/projetos` | Portfólio de cases (gate fail-closed ativo) |
| `/sobre` | Apresentação institucional da DevStudio |
| `/insights` | Artigos e conteúdos técnicos sobre tecnologia e negócios |
| `/insights/{slug}` | Leitura do artigo |
| `/contato` | Formulário comercial com honeypot, rate limit e WhatsApp direto |
| `/admin` | Painel administrativo Filament protegido |
| `/sitemap.xml` | Sitemap index XML dinâmico e estático |

---

## 🚀 Como Executar Localmente

### 1. Dependências e Ambiente
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### 2. Banco de Dados Local (Docker)
```bash
docker compose up -d --wait
php artisan migrate
php artisan db:seed
```
*Os seeders criam o usuário administrador inicial (`admin@devstudio.com.br`), configurações globais de contato e as páginas e serviços prioritários da especificação.*

### 3. Compilação de Assets
```bash
npm run build
# ou para desenvolvimento com hot reload:
npm run dev
```

### 4. Executar Servidor Local
```bash
php artisan serve
```
Acesse a aplicação em `http://localhost:8000` e o painel em `http://localhost:8000/admin`.

---

## 🧪 Suíte de Testes Automatizados

A suíte cobre 100% dos modelos editoriais, validações de seções, conversão de leads, proteção contra spam e regras de fuso horário:

```bash
APP_KEY="base64:$(openssl rand -base64 32)" SESSION_DRIVER=file CACHE_STORE=file QUEUE_CONNECTION=sync php artisan test
```
