# GoBrik Stack Breakdown

## What is GoBrik?

GoBrik is an **ecological action platform** developed and maintained by the [Global Ecobrick Alliance](https://ecobricks.org). It enables users to log, manage, and track **ecobricks** (plastic bottles packed tightly with non-recyclable plastic), community projects, and plastic transition data. GoBrik integrates with ecobricks.org and uses the **Buwana** system for centralized user authentication across the Earthen ecosystem.

- **Production**: https://gobrik.com
- **Beta**: https://beta.gobrik.com

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP (server-side rendered) |
| Database | MySQL — two databases: GoBrik DB + Buwana user DB |
| Authentication | OAuth2 + JWT (via Buwana/Earthen Auth) |
| Frontend | HTML5, CSS3, jQuery, DataTables |
| Email | Mailgun API |
| Internationalization | JavaScript translation files (4 languages) |

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
├── /api/                      ← ~40 backend endpoints
│   ├── fetch_*.php            ← Data retrieval (returns JSON)
│   ├── add_*.php              ← Data creation
│   ├── update_*.php           ← Data updates
│   └── delete_*.php           ← Data deletion
│
├── /auth/                     ← Authentication system
│   ├── session_start.php      ← JWT validation, 3-hour session enforcement
│   ├── verify_jwt.php         ← JWT verification via Firebase/JWT library
│   ├── callback.php           ← OAuth2 callback from Buwana
│   └── redirect.php           ← OAuth2 redirect handler
│
├── /includes/                 ← Page-specific HTML/CSS/JS fragments
│   └── {page}-inc.php         ← Included by /en/{page}.php
│
├── /styles/                   ← CSS (main, light/dark mode, DataTables)
├── /scripts/                  ← JS (core-2025.js, form handlers, utilities)
├── /translations/             ← 150+ JS translation files (4 languages)
├── /meta/                     ← SEO & OpenGraph metadata per page per language
├── /emailing/                 ← Mailgun integration & newsletter system
├── /messenger/                ← User-to-user messaging
├── /earthcal/                 ← Earth calendar integration
└── /processes/                ← Background processes & cron jobs
```

---

## How Files Connect — Request Flow

GoBrik uses **file-based routing** (no framework). Each language page is a self-contained PHP controller that includes shared helpers, queries the database, and renders an HTML template.

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

### Page Anatomy

Each page typically involves **four files working together**:

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
5. Code is exchanged for a signed **JWT** via cURL to the Buwana token endpoint
6. JWT is stored in `$_SESSION['buwana_user']['jwt']`
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

All database queries use **prepared statements** (MySQLi) for security.

---

## API Endpoints

All API endpoints live in `/api/` and follow a consistent pattern:

- Accept POST or GET parameters
- Require `gobrikconn_env.php` (and sometimes `buwanaconn_env.php`)
- Execute a prepared SQL statement
- Return a JSON response

**Naming conventions:**

| Prefix | Action |
|---|---|
| `fetch_*.php` | Read / SELECT queries |
| `add_*.php` | Insert / CREATE operations |
| `update_*.php` | Update / PATCH operations |
| `delete_*.php` | Delete / REMOVE operations |
| `*_process.php` | Multi-step form processing |

---

## Multi-Language System

GoBrik supports four languages: **English (en), Indonesian (id), Spanish (es), French (fr)**.

- Each language has its own directory: `/en/`, `/id/`, `/es/`, `/fr/`
- Core UI strings: `/translations/core-texts-{lang}.js`
- Page-specific strings: `/translations/{page}-{lang}.js`
- SEO metadata: `/meta/{page}-{lang}.php`
- Language detection happens at the root `index.php` and redirects accordingly

---

## Key Files Reference

| File | Purpose |
|---|---|
| `/index.php` | Root entry point, language detection |
| `/en/dashboard.php` | Main application interface |
| `earthenAuth_helper.php` | User data retrieval helpers |
| `/auth/session_start.php` | Session validation on every protected page |
| `/auth/verify_jwt.php` | JWT signature verification |
| `/includes/{page}-inc.php` | Page view templates |
| `/styles/main.css` | Primary stylesheet |
| `/styles/mode-light.css`, `/styles/mode-dark.css` | Light/dark theme |
| `/scripts/core-2025.js` | Core application JavaScript |
| `/translations/core-texts-{lang}.js` | Core UI translation strings |
