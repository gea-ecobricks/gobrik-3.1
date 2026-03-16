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
├── earthenAuth_helper.php     ← Auth helper functions (⚠️ sets display_errors = 1 globally)
│
├── /en/  /id/  /es/  /fr/    ← Language-specific user-facing pages
│   ├── index.php              ← Language landing page
│   ├── dashboard.php          ← Main user interface
│   ├── brik.php               ← Individual ecobrick detail view
│   ├── log.php                ← Ecobrick logging
│   ├── project.php            ← Project management
│   ├── training.php           ← Training module
│   ├── courses.php            ← Course management
│   ├── register.php           ← Course registration (3P system — semi-public, no session_start)
│   ├── registration_confirmation.php ← Pledge/registration DB writes after register.php
│   ├── offset.php             ← Carbon offset tracking
│   ├── accounting.php         ← Financial accounting
│   └── /side-modules/         ← Sidebar partials
│
├── /api/                      ← ~40 backend API endpoints
│   ├── fetch_*.php            ← Data retrieval (returns JSON)
│   ├── add_*.php              ← Data creation
│   ├── update_*.php           ← Data updates
│   ├── delete_*.php           ← Data deletion
│   └── unregister_training.php ← Cancels a 3P registration or pledge
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
├── /scripts/                  ← JS (core-2025.js, language-switcher.js, guided-tour.js, etc.)
├── /translations/             ← JS translation files per page per language
├── /meta/                     ← SEO & OpenGraph metadata per page per language
├── /emailing/                 ← Mailgun integration & newsletter system
├── /messenger/                ← User-to-user messaging
├── /earthcal/                 ← Earth calendar integration
├── /docs/                     ← Schema files (e.g. 3p_payments_schema.sql)
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

### Page Output Structure

Every page follows this HTML output order:

1. `register.php` (or any page controller): `echo '<!DOCTYPE html><html><head>...'` then `require_once '../includes/{page}-inc.php'`
2. `{page}-inc.php`: outputs meta tags (`require_once '../meta/{page}-{lang}.php'`), a `<link>` for Font Awesome, and a large `<STYLE>` block of page-specific CSS — then at the very end calls `require_once '../header-2026.php'`
3. `header-2026.php`: outputs remaining `<head>` content (canonical links, stylesheets, script tags for `language-switcher.js`, `core-2025.js`, `mode-toggle.mjs.js` [module], `guided-tour.js` [defer], `site-search.js` [defer]), inline styles, `</HEAD>`, `<BODY>`, modal HTML, nav overlays, `<div id="page-content">`
4. Back in the page controller: page-specific HTML content, then a `<script>` block with PHP-rendered JS constants and all page JS functions
5. `footer-2026.php`: footer HTML, then a `<script>` block that declares `const lang`, `const page`, `const version` and calls `loadTranslationScripts(lang, page)`

### Four-File Page Anatomy

Each feature page typically involves four files working together:

| File | Role |
|---|---|
| `/en/{page}.php` | Page controller — auth check, DB queries, data prep |
| `/includes/{page}-inc.php` | CSS styles block + `require_once '../header-2026.php'` at end |
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
| `/auth/verify_jwt.php` | JWT signature verification |
| `/auth/callback.php` | Handles OAuth2 callback and token exchange |

**`earthenAuth_helper.php` critical note**: This file sets `error_reporting(E_ALL)` and `ini_set('display_errors', 1)` at the top level. It is included by every page. This means PHP warnings and notices are rendered directly into HTML output — including inside `<script>` blocks. See the PHP-in-JavaScript safety section below.

---

## Database Architecture

GoBrik queries **two separate MySQL databases**:

- **GoBrik DB** (`gobrikconn_env.php`) — ecobricks, projects, transactions, training, communities
- **Buwana DB** (`buwanaconn_env.php`) — user credentials and profile data

### Key Tables

| Table | Purpose |
|---|---|
| `tb_ecobricks` | Individual ecobrick records |
| `tb_ecobrickers` | User profiles (links `buwana_id` → `ecobricker_id`) |
| `tb_projects` | Project records |
| `tb_cash_transaction` | Financial transactions |
| `tb_trainings` | Training/course records (includes all 3P fields) |
| `tb_communities` | Community records |
| `training_registrations_tb` | Per-learner registration status for 3P courses |
| `training_pledges_tb` | Per-learner pledge amounts and status for 3P courses |

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
- Buwana term strings: `/translations/buwana-terms-{lang}.js`
- SEO metadata: `/meta/{page}-{lang}.php`
- Language detection at root `index.php` redirects to the appropriate language directory
- Translation scripts are loaded **dynamically** by `loadTranslationScripts(lang, page)` in `language-switcher.js`, called from the footer's inline `<script>` block

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

## PHP-in-JavaScript Safety (Critical)

Because `earthenAuth_helper.php` sets `display_errors = 1`, **any PHP warning or notice that fires while rendering inside a `<script>` block will inject raw HTML into the JavaScript**, causing a syntax error that silently breaks the entire script block.

The most common trigger: `json_encode()` called on a string containing invalid UTF-8 bytes (from database data copied from Word, old encodings, etc.) — it returns `false` and emits a PHP warning.

### Rules for embedding PHP data in JavaScript

**Always follow this pattern for every `json_encode` call inside a `<script>` block:**

```php
const MY_VAR = <?php echo json_encode($value, JSON_HEX_TAG) ?: '""'; ?>;
```

- `JSON_HEX_TAG` encodes `<` and `>` as `\u003C`/`\u003E` — prevents `</script>` injection
- ` ?: '""'` — if `json_encode` returns `false` for any reason, outputs a valid empty string instead of nothing (which would be a syntax error)
- For integers: `<?php echo (int)$value; ?>` — no json_encode needed
- For booleans: `<?php echo $value ? 'true' : 'false'; ?>` — no json_encode needed

**Never embed PHP json_encode calls inside function definitions.** If a function body needs PHP data, declare it as a constant at the top of the `<script>` block and reference the constant in the function. This way a bad data value breaks only one constant declaration, not the entire function definition.

**Example — correct pattern:**
```php
// At top of <script> block:
const TRAINING_NAME = <?php echo json_encode($training_name, JSON_HEX_TAG) ?: '""'; ?>;

// In function body — pure JavaScript, no PHP:
function handleClick() {
    openModal(TRAINING_NAME);
}
window.handleClick = handleClick;  // Explicitly assign to window for onclick attribute access
```

**Always also assign handler functions to `window` explicitly** (`window.myFn = myFn`) when they are referenced by HTML `onclick` attributes. This ensures accessibility regardless of browser extension scope interference (e.g. MetaMask's SES).

---

## Modal System

All pages share a common modal via `header-2026.php`. There are two modal elements:

- `#form-modal-message` — general-purpose modal used by most pages
- `#form-modal-message-v2` — newer modal variant used on dashboard/ecobrick pages

### Showing a modal

Both of these must be called together to override both the CSS class and the ID-level `display: none` rule:

```javascript
modal.classList.remove('modal-hidden');
modal.style.display = 'flex';
document.body.classList.add('modal-open');
```

### Closing a modal

`closeInfoModal()` is defined in **both** `register.php` and `footer-2026.php`. The footer version (which loads last) takes precedence. It sets `modal.style.display = "none"` but does **not** re-add `modal-hidden` — rely on the inline style for close, not the class.

### Modal CSS (from `header-2026.php`)

```css
#form-modal-message { display: none; }    /* ID rule */
.modal-hidden { display: none; }           /* class rule */
.modal-shown  { display: flex; }
```

The modal HTML always starts with `class="modal-hidden"`. Opening it requires both removing the class AND setting inline style.

---

## JavaScript Loading Order

Understanding the load order is critical when debugging JS issues:

1. **`<head>` (synchronous)**: `language-switcher.js`, `core-2025.js`
2. **`<head>` (module/deferred)**: `mode-toggle.mjs.js` (module), `guided-tour.js` (defer), `site-search.js` (defer)
3. **Body inline `<script>` blocks**: page-specific script block in `{page}.php` — runs synchronously when parsed, all page HTML (including buttons) is already in the DOM at this point
4. **Footer inline `<script>`**: declares `const lang`, `const page`, `const version`; calls `loadTranslationScripts()` which dynamically injects translation `<script>` tags into `<head>`
5. **Deferred scripts execute**: `guided-tour.js`, `site-search.js`, `mode-toggle.mjs.js`
6. **DOMContentLoaded fires**
7. **Dynamically loaded translation scripts execute**: `core-texts-{lang}.js`, `{page}-{lang}.js`, `buwana-terms-{lang}.js`

**Implication**: The page `<script>` block (step 3) runs after buttons are in the DOM, so `bindRegisterButtons()` called immediately at the end of the block will always find the buttons.

---

## Training Registration & the 3P System

### `en/register.php` — Course Registration Page

`/en/register.php` is the public-facing course registration page for a specific training. It is accessed via `register.php?id={training_id}` and handles all registration flows — free, and paid via the **3P system**.

The page supports three auth states:
- **Not logged in** — shows training details and a button that opens a login prompt modal
- **Logged in, not yet registered** — opens a registration confirmation modal (free) or the 3P pledge modal (paid)
- **Logged in, already registered/pledged** — button shows current status and opens a cancel/unregister modal

The page does **not** include `auth/session_start.php` (intentional — it is semi-public). Auth state is determined via `isLoggedIn()` from `earthenAuth_helper.php`. The button handler `handleRegistrationClick()` is a pure JavaScript function that reads PHP-rendered JS constants to determine which modal to open.

There are **three register buttons** (desktop, mobile, bottom) that all call the same handler:
- `#rsvp-register-button-desktop` — visible on wide screens
- `#rsvp-register-button-mobile` — visible on narrow screens
- `#rsvp-bottom-button` — at bottom of page

All three have `onclick="handleRegistrationClick(event)"` AND are bound via `addEventListener` in `bindRegisterButtons()`. `bindRegisterButtons()` is called both in `DOMContentLoaded` and immediately (inline) at the end of the script block.

Page styles live in `/includes/register-inc.php` (alongside all modal CSS for this page).

### `register.php` Script Block Architecture

The script block in `register.php` follows this structure:

1. **PHP-rendered constants** — all PHP data injected here at the top, never inside function bodies:
   - `TRAINING_PAYMENT_MODE`, `SUGGESTED_AMOUNT_IDR`, `TRAINING_ID`, `ECOBRICKER_ID`
   - `PLEDGE_DEADLINE_DISPLAY`, `IS_PLEDGED`, `IS_CONFIRMED_REGISTRATION`
   - `TRAINING_NAME`, `TRAINING_TYPE_STR`, `TRAINING_DATE_STR`, `TRAINING_TIME_STR`
   - `TRAINING_LOCATION_STR`, `USER_EMAIL`, `USER_FIRST_NAME`, `DISPLAY_COST_STR`
   - `CURRENCY_RATES`, `CURRENCY_LABELS`
2. **Pure JS utility functions** — `formatCurrencyFromIdr`, `getConvertedAmount`, `escapeHtml`, `mixColors`, `getPledgeColor`, `activateCustomTooltips`
3. **`handleRegistrationClick(e)`** — pure JS, reads constants, calls modal functions; assigned to `window.handleRegistrationClick` immediately after
4. **`bindRegisterButtons()`** — attaches click + hover listeners to all three buttons
5. **Modal-opening functions** — `openInfoModal`, `openConfirmRegistrationModal`, `open3PRegistrationModal`, `openCancelRegistrationModal`, `openUnregisterSuccessModal`
6. **`closeInfoModal()`** — local version (overridden by footer's version at runtime)
7. **`DOMContentLoaded` handler** + **immediate `bindRegisterButtons()` call**

---

### 3P — Pledge, Proceed and Pay

**3P** (Pledge, Proceed and Pay) is GoBrik's collaborative course-funding system. Instead of requiring upfront payment, 3P allows learners to pledge a chosen amount toward a course. The course only proceeds — and payment is collected — if it reaches both a **participant threshold** and a **funding threshold** by a pledge deadline.

**How it works:**
1. Trainer creates a course with `payment_mode = 'pledge_threshold'` in `tb_trainings`
2. Learner visits `register.php?id={training_id}` and clicks Register
3. A slider modal (`open3PRegistrationModal`) lets the learner choose their pledge amount (in IDR, with live currency conversion display)
4. Learner clicks "Confirm Course Pledge" — a link built dynamically with `encodeURIComponent` query params
5. Browser navigates to `registration_confirmation.php?id=...&mode=pledge_threshold&pledged_amount_idr=...&display_currency=...&display_amount=...`
6. `registration_confirmation.php` writes to `training_pledges_tb` and `training_registrations_tb`
7. On success, redirects back to `register.php?id=...&pledged=1&...` which triggers `openPledgeSuccessModal`
8. Progress bars on `register.php` show live participant and funding progress toward thresholds
9. If the course reaches threshold by the pledge deadline, learners are notified and asked to pay
10. If it doesn't reach threshold, pledges are voided and no payment is collected

**Key 3P fields in `tb_trainings`:**

| Field | Purpose |
|---|---|
| `payment_mode` | `'free'` or `'pledge_threshold'` |
| `base_currency` | Base currency for pricing (default: `IDR`) |
| `default_price_idr` | Trainer's suggested pledge amount in IDR |
| `funding_goal_idr` | Total funding threshold required |
| `min_participants_required` | Minimum registrant count required |
| `pledge_deadline` | Deadline for pledges to count |
| `payment_deadline` | Deadline for payment after threshold is reached |
| `threshold_status` | `'open'`, `'reached'`, or `'cancelled'` |
| `ready_to_show` | Must be `1` or page redirects to courses.php |
| `show_signup_count` | Whether to show public registration count |

**Key 3P database tables** — see full schema at [`docs/3p_payments_schema.sql`](docs/3p_payments_schema.sql):

| Table | Purpose |
|---|---|
| `training_registrations_tb` | Tracks each learner's registration and status (`reserved`, `pledged`, `awaiting_payment`, `confirmed`) |
| `training_pledges_tb` | Records each pledge amount, currency, and status (`active`, `invited`, `paid`, `cancelled`) |

**Registration status flow:**
- `reserved` → initial registration (free course or awaiting pledge)
- `pledged` → pledge submitted, awaiting threshold
- `awaiting_payment` → threshold reached, payment requested
- `confirmed` → payment received, fully registered

**Key files for 3P:**

| File | Purpose |
|---|---|
| `/en/register.php` | Public registration page — renders 3P pledge modal, progress bars, all button states |
| `/includes/register-inc.php` | CSS for register page including 3P slider UI and all modal styles |
| `/en/registration_confirmation.php` | Handles pledge/registration confirmation and DB writes after register.php redirect |
| `/api/unregister_training.php` | Cancels a registration or pledge (called via fetch() from `openCancelRegistrationModal`) |
| [`docs/3p_payments_schema.sql`](docs/3p_payments_schema.sql) | Full SQL schema for all 3P tables |

**3P currency system:**
- All pledge amounts stored internally in IDR
- `CURRENCY_RATES` object in JS converts for display only
- Supported display currencies: IDR, USD, EUR, CAD, GBP, MYR
- `formatCurrencyFromIdr(idrAmount, currency)` — formats for display
- `getConvertedAmount(idrAmount, currency)` — returns numeric converted value
- `getPledgeColor(value, min, max, suggested)` — returns a color for the slider (orange → green → dark green)

---

## Key Files Reference

| File | Purpose |
|---|---|
| `index.php` | Root entry point, language detection |
| `header-2026.php` / `footer-2026.php` | Global page wrapper templates |
| `earthenAuth_helper.php` | User data helpers; ⚠️ sets `display_errors = 1` globally |
| `gobrikconn_env.php` | GoBrik MySQL connection (not in repo — server config) |
| `buwanaconn_env.php` | Buwana MySQL connection (not in repo — server config) |
| `/auth/session_start.php` | Session validation on every protected page |
| `/auth/verify_jwt.php` | JWT signature verification |
| `/en/dashboard.php` | Main application interface |
| `/en/register.php` | 3P course registration (semi-public) |
| `/en/registration_confirmation.php` | Post-registration DB writes |
| `/includes/{page}-inc.php` | CSS styles block + header-2026.php include |
| `/styles/main.css` | Primary stylesheet |
| `/styles/mode-light.css`, `mode-dark.css` | Light/dark theme (defines `--show-hide`, `--h1`, `--course-module` CSS vars) |
| `/scripts/core-2025.js` | Core application JavaScript (defines `logoutUser`, `escapeHTML`, etc.) |
| `/scripts/language-switcher.js` | `loadTranslationScripts()` and `switchLanguage()` |
| `/translations/core-texts-{lang}.js` | Core UI translation strings (declares `{lang}_Translations`) |
| `/translations/{page}-{lang}.js` | Page-specific translation strings (declares `{lang}_Page_Translations`) |
| `/translations/buwana-terms-{lang}.js` | Buwana-specific term translations |
