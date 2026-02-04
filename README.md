# Trello Analytics Dashboard — Laravel Edition

Sistema profissional de análise de licitações Trello com banco de dados MySQL.

## Stack

- **Backend**: Laravel 12+ / PHP 8.2+
- **Database**: MySQL 8.0+
- **Frontend**: Blade + Chart.js + Glassmorphism CSS

## Deploy na Hostinger

### 1. Configurar Git Repository

```bash
git init
git add .
git commit -m "Initial commit - Laravel Trello Analytics"
git remote add origin <seu-repositorio>
git push -u origin main
```

### 2. Hostinger Git Deployment

1. No painel Hostinger, vá em **Git** → **Create New Repository**
2. Cole a URL do repositório
3. Configure o **Branch**: `main`
4. O deploy será automático via webhook

### 3. Após o Deploy

O Hostinger executa automaticamente:
- `composer install`
- `php artisan config:cache`
- `php artisan route:cache`
- `php artisan view:cache`
- `php artisan migrate --force`

### 4. Configurar .env na Hostinger

Edite o arquivo `.env` no painel de arquivos da Hostinger:

```env
APP_KEY=base64:SUA_CHAVE_AQUI
APP_URL=https://seu-dominio.com
```

## Estrutura

```
├── app/
│   ├── Http/Controllers/     # Controllers
│   ├── Models/               # Eloquent Models
│   └── Services/             # TagNormalizer, DateExtractor, TrelloImporter
├── database/migrations/      # Schema do banco
├── resources/views/          # Blade templates
├── public/                   # Assets públicos
└── routes/web.php            # Rotas
```

## Features

- ✅ **Dashboard** com KPIs e gráficos Chart.js
- ✅ **Cards** com edição inline (status, motivo derrota)
- ✅ **Tags customizadas** (adicionar/remover)
- ✅ **Import JSON** com drag-and-drop
- ✅ **Time-series** com datas reais ("2025-11-24 a 2025-11-30")
- ✅ **TagNormalizer** (MÉDIA-ALTA → Média, 🟡 → Média)

## Desenvolvimento Local

```bash
cd trello-analytics
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

Acesse: http://localhost:8000
