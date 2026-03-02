# CLAUDE.md — GoBrik 3.1

## Project Overview

GoBrik is an **ecological action platform** built and maintained by the [Global Ecobrick Alliance](https://ecobricks.org). It enables users to log, manage, and track ecobricks, community projects, and plastic transition data. GoBrik integrates with ecobricks.org and uses the **Buwana** system for centralized OAuth2/JWT authentication.

- **Production**: https://gobrik.com
- **Beta**: https://beta.gobrik.com

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP (server-side rendered, no framework) |
| Database | MySQL — two databases: GoBrik DB + Buwana user DB |
| Authentication | OAuth2 + JWT via Buwana/Earthen Auth (external OIDC provider) |
| Frontend | HTML5, CSS3, jQuery, DataTables |
| Email | Mailgun API |
| Internationalization | JavaScript translation files (4 languages: en, id, es, fr) |

There is **no build system, no package manager, no framework**. Files are served directly by the web server.

---

## Directory Structure

```
gobrik-3.1/
├── index.php                  ← Root entry point (language detection & redirect)
├── header-2026.php            ← Global header template
├── footer-2026.php            ← Global footer template
├── earthenAuth_helper.php     ← Auth helper functions
│
├── /en/  /id/  /es/  /fr/    ← Language-specific user-facing pages
│   ├── index.php              ← Language landing page
│   ├── dashboard.php          ← Main user interface
│   ├── brik.php               ← Individual ecobrick detail view
│   ├── log.php                ← Ecobrick logging
│   ├── project.php            ← Project management
│   ├── training.php           ← Training module
│   ├── courses.php            ← Course management
│   ├── offset.php             ← Carbon offset tracking
│   ├── accounting.php         ← Financial accounting
│   └── /side-modules/         ← Sidebar partials
│
├── /api/                      ← ~40 backend API endpoints
│   ├── fetch_*.php            ← Data retrieval (returns JSON)
│   ├── add_*.php              ← Data creation
│   ├── update_*.php           ← Data updates
│   └── delete_*.php           ← Data deletion
│
├── /auth/                     ← Authentication system
│   ├── session_start.php      ← JWT validation, 3-hour session enforcement
│   ├── verify_jwt.php         ← JWT verification via JWKS from Buwana
│   ├── callback.php           ← OAuth2 callback from Buwana
│   └── redirect.php           ← OAuth2 redirect handler
│
├── /includes/                 ← Page-specific HTML/CSS/JS fragments
│   └── {page}-inc.php         ← Included by /en/{page}.php (and other lang dirs)
│
├── /styles/                   ← CSS (main.css, mode-light.css, mode-dark.css, etc.)
├── /scripts/                  ← JS (core-2025.js, form handlers, utilities)
├── /translations/             ← JS translation files per page per language
├── /meta/                     ← SEO & OpenGraph metadata per page per language
├── /emailing/                 ← Mailgun integration & newsletter system
├── /messenger/                ← User-to-user messaging
├── /earthcal/                 ← Earth calendar integration
└── /processes/                ← Background processes & cron jobs
```

---

## Request Flow (File-Based Routing)

GoBrik uses **file-based routing** — no router or framework. Each language page is a self-contained PHP controller that handles auth, DB queries, and renders HTML.

```
User visits: /en/dashboard.php
       │
       ├── require_once '../auth/session_start.php'       ← Validates JWT
       ├── require_once '../earthenAuth_helper.php'        ← User helper functions
       ├── require_once '../gobrikconn_env.php'            ← GoBrik DB connection
       ├── require_once '../buwanaconn_env.php'            ← Buwana user DB connection
       ├── Executes prepared SQL queries → fetches user data
       └── require_once '../includes/dashboard-inc.php'   ← Renders HTML + JS
                   │
                   └── JavaScript (AJAX) → POST /api/fetch_*.php
                               │
                               ├── require_once '../gobrikconn_env.php'
                               ├── Executes prepared SQL statement
                               └── echo json_encode([...])  ← Returns JSON to browser
```

### Four-File Page Anatomy

Each feature page typically involves four files working together:

| File | Role |
|---|---|
| `/en/{page}.php` | Page controller — auth check, DB queries, data prep |
| `/includes/{page}-inc.php` | View template — HTML, CSS, and JavaScript |
| `/meta/{page}-{lang}.php` | SEO metadata — title, description, OpenGraph tags |
| `/translations/{page}-{lang}.js` | UI text strings for the selected language |

---

## Authentication Flow

GoBrik delegates all user authentication to the external **Buwana OIDC/OAuth2 provider** (https://buwana.ecobricks.org).

1. User visits `/en/login.php`
2. Redirected to `/auth/redirect.php` → initiates OAuth2 with Buwana
3. User authenticates on Buwana
4. Buwana redirects back to `/auth/callback.php` with an authorization code
5. Code exchanged for a signed **JWT** via cURL to the Buwana token endpoint
6. JWT stored in `$_SESSION['buwana_user']['jwt']`
7. Every protected page calls `/auth/session_start.php` which re-validates the JWT (3-hour session lifetime enforced)

**Key auth files:**

| File | Purpose |
|---|---|
| `earthenAuth_helper.php` | Helper functions for retrieving user profile data |
| `/auth/session_start.php` | Enforces session lifetime, validates JWT on every request |
| `/auth/verify_jwt.php` | Verifies JWT signature using JWKS fetched from Buwana |
| `/auth/callback.php` | Handles OAuth2 callback and token exchange |

---

## Database Architecture

GoBrik queries **two separate MySQL databases**:

- **GoBrik DB** (`gobrikconn_env.php`) — ecobricks, projects, transactions, training, communities
- **Buwana DB** (`buwanaconn_env.php`) — user credentials and profile data

### Key Tables

| Table | Purpose |
|---|---|
| `tb_ecobricks` | Individual ecobrick records |
| `tb_ecobrickers` | User profiles |
| `tb_projects` | Project records |
| `tb_cash_transaction` | Financial transactions |
| `tb_training` | Training sessions |
| `tb_communities` | Community records |

All database queries **must use MySQLi prepared statements**. Never use string interpolation in SQL.

---

## API Endpoints (`/api/`)

All API endpoints follow a consistent pattern:

- Accept POST or GET parameters
- Include `gobrikconn_env.php` (and sometimes `buwanaconn_env.php`)
- Execute a prepared SQL statement
- Return `echo json_encode([...])` — always JSON

**Naming conventions:**

| Prefix | Action |
|---|---|
| `fetch_*.php` | SELECT / read queries |
| `add_*.php` | INSERT / create operations |
| `update_*.php` | UPDATE / patch operations |
| `delete_*.php` | DELETE / remove operations |
| `*_process.php` | Multi-step form processing |

---

## Multi-Language System

Four languages supported: **English (en), Indonesian (id), Spanish (es), French (fr)**.

- Each language has its own directory: `/en/`, `/id/`, `/es/`, `/fr/`
- Core UI strings: `/translations/core-texts-{lang}.js`
- Page-specific strings: `/translations/{page}-{lang}.js`
- SEO metadata: `/meta/{page}-{lang}.php`
- Language detection at root `index.php` redirects to the appropriate language directory

When adding new UI strings, add translations to **all four language files**.

---

## Coding Conventions

- **PHP**: No framework. Use `require_once` for includes. All DB access via MySQLi prepared statements.
- **SQL**: Always use prepared statements with bound parameters — never interpolate user input.
- **JavaScript**: jQuery-based. AJAX calls POST to `/api/*.php` and handle JSON responses.
- **CSS**: Page styles live in `/styles/`. Light/dark mode via `mode-light.css` / `mode-dark.css`.
- **Headers/footers**: Use `header-2026.php` and `footer-2026.php` (not the 2025 versions).
- **Translations**: Any user-visible string in a page template should come from the translation JS file for that page/language, not be hardcoded in HTML.

---

## Key Files Reference

| File | Purpose |
|---|---|
| `index.php` | Root entry point, language detection |
| `header-2026.php` / `footer-2026.php` | Global page wrapper templates |
| `earthenAuth_helper.php` | User data retrieval helpers |
| `gobrikconn_env.php` | GoBrik MySQL connection (not in repo — server config) |
| `buwanaconn_env.php` | Buwana MySQL connection (not in repo — server config) |
| `/auth/session_start.php` | Session validation on every protected page |
| `/auth/verify_jwt.php` | JWT signature verification |
| `/en/dashboard.php` | Main application interface |
| `/includes/{page}-inc.php` | Page view templates |
| `/styles/main.css` | Primary stylesheet |
| `/styles/mode-light.css`, `mode-dark.css` | Light/dark theme |
| `/scripts/core-2025.js` | Core application JavaScript |
| `/translations/core-texts-{lang}.js` | Core UI translation strings |
