<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# NexoraFDV

**NexoraFDV** is a Force de Vente (FDV) platform built with **Laravel 13** + **Livewire 4**, designed to support field sales teams with order management, customer tracking, CRM pipelines, visit scheduling, commissions, and offline sync capabilities.

---

## Table of Contents

- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Getting Started](#getting-started)
  - [Local Setup](#local-setup)
  - [Docker](#docker)
- [Project Structure](#project-structure)
- [API Reference](#api-reference)
- [Authentication](#authentication)
- [Modules](#modules)
- [Running Tests](#running-tests)
- [Contributing](#contributing)

---

## Overview

NexoraFDV provides a complete backend API for mobile and web sales-force applications, including:

- Customer and prospect management
- Product catalog with pricing tables
- Order creation, approval and cancellation workflows
- Visit scheduling with check-in / check-out
- CRM opportunity pipeline
- Sales goals and commission tracking
- Financial titles (accounts receivable)
- Route planning (roteiros)
- Offline synchronization support

---

## Tech Stack

| Layer      | Technology                     |
|------------|-------------------------------|
| Language   | PHP 8.3                        |
| Framework  | Laravel 13                     |
| Frontend   | Livewire 4                     |
| Auth       | Laravel Sanctum (token-based)  |
| Database   | SQLite (dev) / MySQL (prod)    |
| Testing    | PestPHP 4                      |
| Build      | Vite                           |
| Container  | Docker                         |

---

## Requirements

- PHP >= 8.3
- Composer >= 2.x
- Node.js >= 20.x & npm
- SQLite (development) or MySQL 8+ (production)

---

## Getting Started

### Local Setup

```bash
# 1. Clone the repository
git clone <repo-url> nexoraFDV
cd nexoraFDV

# 2. Run the automated setup script
composer setup
```

The `composer setup` script will:
1. Install PHP dependencies (`composer install`)
2. Copy `.env.example` to `.env` (if not present)
3. Generate the application key
4. Run all database migrations
5. Install Node dependencies and build frontend assets

### Starting the Development Server

```bash
composer dev
```

This starts all development processes concurrently:
- `php artisan serve` — Laravel HTTP server
- `php artisan queue:listen` — Queue worker
- `php artisan pail` — Log viewer
- `npm run dev` — Vite HMR

### Docker

```bash
docker-compose up --build
```

The application will be available at `http://localhost:8000`.

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/   # REST API controllers
│   ├── Requests/          # Form request validation
│   └── Resources/         # API resource transformers
├── Models/                # Eloquent models
└── Providers/

database/
├── migrations/            # Database schema migrations
├── factories/             # Model factories for testing
└── seeders/

routes/
├── api.php                # API routes (Sanctum protected)
└── web.php                # Web routes (Livewire)

resources/
├── js/                    # Frontend JS (Vite)
├── css/                   # Stylesheets
└── views/                 # Blade / Livewire views

tests/
├── Feature/               # Feature/integration tests
└── Unit/                  # Unit tests
```

---

## API Reference

All API routes are prefixed with `/api`. Protected routes require a Bearer token obtained via login.

### Authentication

| Method | Endpoint         | Description          | Auth |
|--------|-----------------|----------------------|------|
| POST   | `/api/auth/login`  | Obtain access token  | No   |
| POST   | `/api/auth/logout` | Revoke access token  | Yes  |
| GET    | `/api/auth/me`     | Authenticated user   | Yes  |

### Dashboard

| Method | Endpoint                             | Description             |
|--------|--------------------------------------|-------------------------|
| GET    | `/api/dashboard`                     | General dashboard data  |
| GET    | `/api/dashboard/ranking-vendedores`  | Salesperson ranking     |

### Customers (Clientes)

| Method | Endpoint                          | Description              |
|--------|-----------------------------------|--------------------------|
| GET    | `/api/clientes`                   | List customers           |
| POST   | `/api/clientes`                   | Create customer          |
| GET    | `/api/clientes/{id}`              | Show customer            |
| PUT    | `/api/clientes/{id}`              | Update customer          |
| PATCH  | `/api/clientes/{id}/bloquear`     | Block customer           |
| PATCH  | `/api/clientes/{id}/desbloquear`  | Unblock customer         |
| GET    | `/api/clientes/{id}/timeline`     | Customer activity timeline |

### Prospects

| Method | Endpoint                          | Description              |
|--------|-----------------------------------|--------------------------|
| GET    | `/api/prospects`                  | List prospects           |
| POST   | `/api/prospects`                  | Create prospect          |
| GET    | `/api/prospects/{id}`             | Show prospect            |
| PUT    | `/api/prospects/{id}`             | Update prospect          |
| POST   | `/api/prospects/{id}/converter`   | Convert to customer      |

### Products (Produtos)

| Method | Endpoint                        | Description           |
|--------|---------------------------------|-----------------------|
| GET    | `/api/produtos`                 | List products         |
| GET    | `/api/produtos/{id}`            | Show product          |
| GET    | `/api/produtos/{id}/precos`     | Product pricing       |

### Orders (Pedidos)

| Method | Endpoint                        | Description           |
|--------|---------------------------------|-----------------------|
| GET    | `/api/pedidos`                  | List orders           |
| POST   | `/api/pedidos`                  | Create order          |
| GET    | `/api/pedidos/{id}`             | Show order            |
| PATCH  | `/api/pedidos/{id}/confirmar`   | Confirm order         |
| PATCH  | `/api/pedidos/{id}/cancelar`    | Cancel order          |
| PATCH  | `/api/pedidos/{id}/aprovar`     | Approve order         |

### Visit Schedule (Visitas)

| Method | Endpoint                          | Description         |
|--------|-----------------------------------|---------------------|
| GET    | `/api/visitas`                    | List visits         |
| POST   | `/api/visitas`                    | Schedule visit      |
| GET    | `/api/visitas/{id}`               | Show visit          |
| PUT    | `/api/visitas/{id}`               | Update visit        |
| PATCH  | `/api/visitas/{id}/check-in`      | Register check-in   |
| PATCH  | `/api/visitas/{id}/check-out`     | Register check-out  |

### CRM Opportunities (Oportunidades)

| Method | Endpoint                   | Description             |
|--------|---------------------------|-------------------------|
| GET    | `/api/oportunidades`       | List opportunities      |
| POST   | `/api/oportunidades`       | Create opportunity      |
| GET    | `/api/oportunidades/{id}`  | Show opportunity        |
| PUT    | `/api/oportunidades/{id}`  | Update opportunity      |

### Sales Goals (Metas)

| Method | Endpoint           | Description      |
|--------|--------------------|------------------|
| GET    | `/api/metas`       | List goals       |
| POST   | `/api/metas`       | Create goal      |
| PUT    | `/api/metas/{id}`  | Update goal      |

### Commissions (Comissões)

| Method | Endpoint                  | Description             |
|--------|--------------------------|-------------------------|
| GET    | `/api/comissoes`          | List commissions        |
| GET    | `/api/comissoes/resumo`   | Commission summary      |

### Financial Titles (Títulos Financeiros)

| Method | Endpoint                                           | Description              |
|--------|----------------------------------------------------|--------------------------|
| GET    | `/api/titulos-financeiros`                         | List titles              |
| GET    | `/api/titulos-financeiros/cliente/{clienteId}`     | Titles by customer       |
| PATCH  | `/api/titulos-financeiros/{id}/registrar-pagamento`| Register payment         |

### Routes (Roteiros)

| Method | Endpoint              | Description      |
|--------|-----------------------|------------------|
| GET    | `/api/roteiros`       | List routes      |
| POST   | `/api/roteiros`       | Create route     |
| GET    | `/api/roteiros/{id}`  | Show route       |
| PUT    | `/api/roteiros/{id}`  | Update route     |

### Offline Sync

| Method | Endpoint              | Description                      |
|--------|-----------------------|----------------------------------|
| GET    | `/api/sync/download`  | Download data for offline use    |
| POST   | `/api/sync/upload`    | Upload offline changes to server |

---

## Authentication

NexoraFDV uses **Laravel Sanctum** for API token authentication.

```bash
# Login
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "secret"
}

# Response
{
  "token": "<access_token>",
  "user": { ... }
}
```

Include the token in subsequent requests:

```
Authorization: Bearer <access_token>
```

---

## Modules

| Module              | Description                                              |
|---------------------|----------------------------------------------------------|
| **Clientes**        | Full customer lifecycle management                       |
| **Prospects**       | Lead management with conversion to customers             |
| **Produtos**        | Product catalog with price tables and promotions         |
| **Pedidos**         | Sales order workflow (draft → confirmed → approved)      |
| **Visitas**         | Field visit scheduling with geolocation check-in/out     |
| **Oportunidades**   | CRM pipeline for tracking sales opportunities            |
| **Metas**           | Sales goal definition and tracking per salesperson       |
| **Comissões**       | Commission calculation and reporting                     |
| **Financeiro**      | Accounts receivable titles and payment registration      |
| **Roteiros**        | Daily / weekly route planning for sales reps             |
| **Sync**            | Offline-first synchronization for mobile field use       |

---

## Running Tests

```bash
composer test
```

Or directly:

```bash
php artisan test
```

Tests are written with **PestPHP**. Feature tests cover API endpoints; Unit tests cover business logic.

---

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feat/your-feature`
3. Commit your changes following [Conventional Commits](https://www.conventionalcommits.org/)
4. Push and open a Pull Request

---

## License

This project is licensed under the [MIT License](https://opensource.org/licenses/MIT).
