## Overview

**Pledge, Proceed, and Pay Registration** is an new concept by Russell and Paula for instituting a human-friendly GoBrik course registration system that embodies the GEA principle of non-capital accessibility.  Our idea is a threshold-based course enrollment system in which participants first choose and confirm the amount they are willing to contribute-- including free access if they choose.  The participant's pledged amount goes toward attaining the minimum funding and participation required for the course to run. Once the threshold is reached, the course is confirmed and participants are invited to complete their payments through the appropriate gateway.

This model lets GoBrik enable free access, declare course value and receive payment through a collaborative funding path.  This way, trainings only proceed once there is enough participant commitment. Instead of collecting money immediately at first registration, the system first records a **pledge**, tracks progress publicly on the course page, and only later asks registrants to complete their payment if the course reaches the required threshold.

The innovative model can then be used by other resonant organizations (like New Forest Aquaponics or Asihdiri!) to launch their workshops and trainings.

At the outset course price will be in Indonesian Rupiahs, however a side menu will allow the user to view the cost in their own currency.

---

## Core Concept

The concept separates the registration journey into **three distinct phases**:

1. **Pledge** — the user selects a contribution amount and confirms their intent to participate.
2. **Proceed** — the course reaches its required participant count and/or funding threshold and is officially confirmed to run.
3. **Process** — the system sends payment invitations and collects actual payments through Stripe (for non-indo users) or Midtrans (for indo users).

This is important because a pledge is **not yet a payment**. It is a recorded commitment attached to a user and a specific training. The training only moves into active payment collection after the system determines that the course can go ahead.

---

## User Flow

## 1. Course discovery

The user visits our list of available pending/scheduled course.  They click through to the page that features the training.

The course listing page will clearly indicate whether the course is:

- free,
- fixed-price paid,
- or threshold-funded using the Pledge, Proceed, and Process model.

For threshold-funded courses, the page will feature a nifty bar graph that shows the course status:

- the minimum participant count,
- the minimum funding threshold,
- the amount pledged so far,
- the number of pledgers so far,
- and a progress visualization.

## 2. Registration button behavior

When the user clicks **Register**:

- If the course is free, they go through the normal free registration flow.
- If the course is paid but not threshold-funded, they can go directly into payment.
- If the course uses the threshold model, they go first to the **pledge selection screen**.

## 3. Pledge selection screen

On the pledge screen, the user sees:

- the default course price,
- a long vertical slider that begins at the standard course price,
- the option to slide lower or higher (but we'll start it at the suggested price)
- a display of the currently selected amount,
- a currency selector,
- and explanatory text about how pledging works.
- The max option will be for the user to pay the entire course base amount (this way a user can pay for a full community workshop-- like the lady in Washington who is poised to do so!)

The desired behavior discussed so far is:

- the course price is stored in Indonesian rupiah,
- the slider position begins about two-thirds across the available range,
- the slider can be moved right to increase contributions,
- and it can also be moved left, even to zero.

That means the system supports:

- full pledge,
- partial pledge,
- generous pledge,
- or symbolic/free participation where allowed by course rules.

## 4. Pledge confirmation

After the user selects the amount, they do **not** go immediately to Stripe or Midtrans.

Instead, they are shown a confirmation screen that explains:

- this is a pledge, not yet a charge,
- the course will proceed only if thresholds are met,
- the user will receive an email if the course is confirmed,
- and payment will only be requested at that later stage.

When the user confirms, the system creates a registration record and a pledge record.

## 5. Public course progress

Once saved, the course page should reflect updated totals such as:

- number of registrants,
- number of pledgers,
- total pledged amount,
- percentage of funding threshold achieved,
- and percentage of participant threshold achieved.

This is the motivational and transparency layer of the system. It turns the course into a collaborative campaign rather than a simple one-step checkout.

## 6. Threshold reached

When the course reaches the required thresholds:

- the course status changes to confirmed,
- all eligible pledgers receive an email,
- each recipient is given a payment link,
- and the payment collection window opens.

At this point the system moves from **pledge mode** into **payment mode**.

## 7. Payment processing

When a user follows the payment request:

- if the selected currency is IDR, the backend routes to Midtrans,
- if the selected currency is non-IDR, the backend routes to Stripe,
- the final amount to be collected is the amount previously pledged,
- and the gateway collects the payment on its hosted checkout or equivalent secure payment page.

## 8. Course success or failure notification

Two communication paths are needed:

### If the thresholds are reached

Users receive a message stating that the course will proceed and that they should now complete payment.

### If the thresholds are not reached by the deadline

Users receive a message stating that the course will not go ahead and that no payment will be collected.

---

## Database Integration

The current payment schema is already a strong foundation for actual payment collection because it includes:

- `payments_tb` for a payment header,
- `payment_items_tb` for line items,
- `payment_events_tb` for gateway webhook events,
- `training_registrations_tb` for training signups,
- and `tb_cash_transaction` for accounting linkage.

That means the current design is already suitable for the **Process** part of the model.

However, it does **not yet fully model the Pledge phase**. A pledge is not the same thing as a created payment. If you create payment rows too early, you blur the line between intent and actual charge. The cleanest design is to keep **pledges** separate from **payments**, and only create a real payment record once the course is confirmed and the user is being asked to actually pay.

---

## A. Upgrading the tb_trainings

The training or course table needs fields that define how the threshold model behaves.

Suggested additions to `tb_training` (or whatever the active training master table is):

```sql
ALTER TABLE tb_training
ADD COLUMN pricing_mode VARCHAR(32) NOT NULL DEFAULT 'free',
ADD COLUMN base_price_idr INT UNSIGNED NOT NULL DEFAULT 0,
ADD COLUMN threshold_enabled TINYINT(1) NOT NULL DEFAULT 0,
ADD COLUMN min_registrants INT UNSIGNED NOT NULL DEFAULT 0,
ADD COLUMN funding_goal_idr INT UNSIGNED NOT NULL DEFAULT 0,
ADD COLUMN pledge_deadline DATETIME DEFAULT NULL,
ADD COLUMN payment_deadline DATETIME DEFAULT NULL,
ADD COLUMN threshold_status VARCHAR(20) NOT NULL DEFAULT 'open',
ADD COLUMN confirmed_at DATETIME DEFAULT NULL,
ADD COLUMN failed_at DATETIME DEFAULT NULL;
```

### Purpose of these fields

- `pricing_mode` distinguishes `free`, `fixed_paid`, and `threshold_pledge`.
- `base_price_idr` stores the standard recommended price shown on the slider.
- `threshold_enabled` makes the behavior explicit and easy to test in code.
- `min_registrants` stores the participant threshold.
- `funding_goal_idr` stores the pledge total required to proceed.
- `pledge_deadline` defines when the campaign closes if the target is not reached.
- `payment_deadline` defines how long users have to complete payment after confirmation.
- `threshold_status` can hold values like `open`, `reached`, `confirmed`, `failed`, `closed`.
- `confirmed_at` and `failed_at` provide audit history.

## B. Expand `training_registrations_tb`

The current `training_registrations_tb` is close, but it needs more registration-state detail for a pledge-first workflow.

Suggested additions:

```sql
ALTER TABLE training_registrations_tb
ADD COLUMN registration_type VARCHAR(32) NOT NULL DEFAULT 'standard',
ADD COLUMN pledge_status VARCHAR(20) NOT NULL DEFAULT 'none',
ADD COLUMN payment_requested_at DATETIME DEFAULT NULL,
ADD COLUMN payment_due_at DATETIME DEFAULT NULL,
ADD COLUMN payment_completed_at DATETIME DEFAULT NULL,
ADD COLUMN currency CHAR(3) DEFAULT NULL,
ADD COLUMN pledged_amount INT UNSIGNED NOT NULL DEFAULT 0,
ADD COLUMN pledge_confirmed_at DATETIME DEFAULT NULL,
ADD COLUMN pledge_note VARCHAR(255) DEFAULT NULL;
```

### Purpose of these fields

- `registration_type` distinguishes normal registration from threshold registration.
- `pledge_status` can hold values such as `none`, `pledged`, `invited`, `paid`, `expired`, `cancelled`.
- `payment_requested_at` logs when the invitation to pay was sent.
- `payment_due_at` stores the payment deadline.
- `payment_completed_at` provides quick reporting without always joining payment tables.
- `currency` stores the user-selected display/payment currency at time of pledge.
- `pledged_amount` stores the user’s committed amount.
- `pledge_confirmed_at` stores the moment the pledge was accepted.
- `pledge_note` is optional for future flexibility.

This is the simplest path if you want to keep pledges directly on the registration row.

## C. Preferred option: create a dedicated pledges table

A cleaner architecture is to separate registration from pledging. This is the stronger recommendation.

```sql
CREATE TABLE IF NOT EXISTS training_pledges_tb (
  pledge_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  registration_id BIGINT UNSIGNED NOT NULL,
  training_id INT(11) UNSIGNED NOT NULL,
  buwana_id BIGINT UNSIGNED NOT NULL,

  currency CHAR(3) NOT NULL DEFAULT 'IDR',
  pledged_amount INT UNSIGNED NOT NULL,
  base_price_snapshot INT UNSIGNED NOT NULL DEFAULT 0,

  status VARCHAR(20) NOT NULL DEFAULT 'pledged',
  gateway_preference VARCHAR(16) DEFAULT NULL,

  pledged_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  invited_to_pay_at DATETIME DEFAULT NULL,
  payment_due_at DATETIME DEFAULT NULL,
  paid_at DATETIME DEFAULT NULL,
  expired_at DATETIME DEFAULT NULL,
  cancelled_at DATETIME DEFAULT NULL,

  notes VARCHAR(255) DEFAULT NULL,
  meta_json JSON DEFAULT NULL,

  PRIMARY KEY (pledge_id),
  KEY idx_tp_training (training_id),
  KEY idx_tp_buwana (buwana_id),
  KEY idx_tp_registration (registration_id),
  KEY idx_tp_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Why a separate pledges table is better

It preserves the distinction between:

- a person registering,
- a person pledging,
- and a person actually paying.

It also gives you room later for:

- pledge edits,
- multiple pledge attempts,
- pledge history,
- reporting,
- and reconciliation with eventual payment records.

## D. Add a payment request table or reuse `payments_tb`

Once the threshold is reached, you need a way to create an actual payable transaction from the pledge.

You have two options:

### Option 1: reuse `payments_tb` only when payment is actually requested

This is the recommended option.

When threshold is reached:

- create a row in `payments_tb`,
- create a row in `payment_items_tb`,
- link the item back to the pledge or registration,
- send the user to the appropriate gateway when they click through.

To support this cleanly, add pledge linkage to `payment_items_tb`:

```sql
ALTER TABLE payment_items_tb
ADD COLUMN source_type VARCHAR(32) DEFAULT NULL,
ADD COLUMN source_ref_id BIGINT UNSIGNED DEFAULT NULL;
```

Then use values like:

- `source_type = 'training_pledge'`
- `source_ref_id = pledge_id`

This makes it much easier to reconcile payments back to original pledges.

### Option 2: add a dedicated payment request table

You could add a `payment_requests_tb` table to track the invitation-to-pay phase separately. That would be useful if you expect a long period between pledge confirmation and payment attempt, or if you want multiple payment retries per pledge.

For GoBrik’s current scope, this is probably optional rather than required.

## E. Amend `payments_tb` for pledge-origin payments

`payments_tb` already works well, but these additions would improve traceability:

```sql
ALTER TABLE payments_tb
ADD COLUMN origin_type VARCHAR(32) DEFAULT NULL,
ADD COLUMN origin_ref_id BIGINT UNSIGNED DEFAULT NULL,
ADD COLUMN expires_at DATETIME DEFAULT NULL;
```

### Purpose

- `origin_type` could be `training_pledge`.
- `origin_ref_id` could be the `pledge_id`.
- `expires_at` gives the payment session a clean timeout.

## F. Consider a training threshold snapshot or rollup table

You do not strictly need a separate totals table, because totals can be computed by query. However, if performance or reporting becomes important, a rollup table can help.

Example:

```sql
CREATE TABLE IF NOT EXISTS training_thresholds_tb (
  training_id INT(11) UNSIGNED NOT NULL,
  pledged_count INT UNSIGNED NOT NULL DEFAULT 0,
  pledged_total_idr BIGINT UNSIGNED NOT NULL DEFAULT 0,
  paid_count INT UNSIGNED NOT NULL DEFAULT 0,
  paid_total_idr BIGINT UNSIGNED NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (training_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

This is optional. It is a convenience table, not a core requirement.

---

## Recommended Status Models

## Training-level statuses

Suggested `threshold_status` values:

- `open`
- `reached`
- `confirmed`
- `failed`
- `closed`

## Pledge-level statuses

Suggested pledge status values:

- `pledged`
- `invited`
- `processing`
- `paid`
- `expired`
- `cancelled`

## Payment-level statuses

Suggested use of existing `payments_tb.status` values:

- `created`
- `pending`
- `paid`
- `failed`
- `expired`
- `cancelled`
- `refunded`

---

## UI Work Needed

## 1. Course page additions

The training page should be enhanced to show:

- standard course price - DONE (needs upgrading)
- whether the course is threshold-funded, (needs upgrading)
- minimum participants required, Done
- funding goal, (needs upgrading)
- current pledged amount, (needs upgrading)
- current registrant count, (needs upgrading)
- and a progress visualization. (needs upgrading)

Visual modules:

- **Funding progress bar** — pledged total versus required total.
- **Participation progress bar** — number of registrants versus required participants.
- **Status badge** — open, threshold reached, confirmed, failed.
- **Deadline block** — pledge deadline and later payment deadline.

## 2. Pledge slider page

This is the key new UI component.

Required elements:

- course title,
- explanation text,
- slider control,
- selected amount display,
- base price marker,
- min and max labels,
- currency selector,
- note that payment is not yet being collected,
- and a confirmation button.

Behavior requirements:

- initialize the displayed amount from the standard course price,
- place the slider’s starting thumb at the intended starting point,
- show live updates while sliding,
- update displayed currency formatting,
- and preserve the selected amount through confirmation.

## 3. Pledge confirmation screen

This screen should summarize:

- selected pledge amount,
- selected currency,
- course name,
- course threshold rules,
- and the fact that this is not yet a payment.

Primary action:

- **Confirm Pledge**

Secondary action:

- **Edit Amount**

## 4. Threshold reached email-to-payment UI

Once the training is confirmed, users need a smooth path back into payment.

This includes:

- email template with clear call-to-action,
- payment landing page,
- summary of original pledge,
- gateway routing behind the scenes,
- and success/failure return pages.

## 5. Admin UI needs

The admin or training management UI should support:

- setting training pricing mode,
- enabling threshold mode,
- defining base price,
- setting funding goal,
- setting minimum participant count,
- defining pledge deadline,
- viewing live pledge totals,
- manually confirming or failing a course if needed,
- and triggering payment request emails.

---

## Backend and Processing Work Needed

## 1. Pledge creation endpoint

Create a dedicated endpoint for recording a threshold pledge.

Suggested example:

```text
/api/add_training_pledge.php
```

Responsibilities:

- validate logged-in user,
- validate training is open for pledges,
- validate selected amount,
- create registration if needed,
- create pledge record,
- return updated totals for UI refresh.

## 2. Training totals endpoint

Create or extend a fetch endpoint to provide:

- funding goal,
- pledged total,
- participant threshold,
- current participant count,
- progress percentages,
- threshold status.

Suggested example:

```text
/api/fetch_training_threshold_status.php
```

## 3. Threshold evaluation logic

You need backend logic that checks whether a training has met its thresholds.

This can happen:

- after each new pledge,
- on a scheduled cron,
- or both.

When thresholds are met:

- update the training status,
- mark pledge records as eligible for payment invitation,
- generate payment records,
- queue emails.

## 4. Deadline processing

A scheduled process should also:

- detect expired pledge campaigns,
- mark unsuccessful trainings as failed,
- notify users,
- and prevent further pledging.

## 5. Payment invitation flow

When a course is confirmed, the backend should:

- generate payment requests from valid pledges,
- assign gateway by currency,
- create `payments_tb` and `payment_items_tb` rows,
- generate checkout sessions or payment tokens,
- email users with their payment links.

## 6. GEA Open BOoks linkage

Once payment is completed:

- `payments_tb` is updated,
- payment gateway webhook events are recorded in `payment_events_tb`,
- the registration/pledge status is updated,
- and `tb_cash_transaction` can be linked to the payment using `payment_id` and `gateway_ref`.

This fits well with the cash transaction linkage already proposed in the current payment schema.

---

## Stripe API Integration

Stripe does not need to know about the pledge campaign itself. GoBrik handles the pledge and threshold logic internally. Once the course is confirmed, GoBrik simply passes the final payable amount to Stripe and asks Stripe to collect that amount.  Later our open books is updated.


# Stripe should be used only after:

- the user already has a confirmed pledge,
- the training has reached threshold,
- and GoBrik has created a real payment record.

# At that point, Stripe can:

- display a secure hosted checkout page,
- show the exact amount being charged,
- collect card details securely,
- return the user to GoBrik,
- and notify GoBrik through webhook events.

## GEA Dev Team Recommended Stripe implementation pattern

### 1. GoBrik creates the payment row

Create a row in `payments_tb` with:

- user,
- currency,
- pledged amount,
- gateway = `stripe`,
- status = `created`,
- origin linkage back to the pledge.

### 2. GoBrik creates a Stripe Checkout Session

The backend calls Stripe with:

- amount,
- currency,
- description,
- metadata such as training ID, pledge ID, registration ID, and buwana ID,
- success URL,
- cancel URL.

### 3. Stripe hosts the payment page

The user is redirected to Stripe Checkout. Stripe shows:

- the amount,
- card entry form,
- and any other supported payment methods configured on the Stripe account.

This means GoBrik does **not** need to build or host its own card form for the first version.

### 4. Stripe sends webhooks

On success or other state changes, Stripe posts to a GoBrik webhook endpoint.

Suggested example:

```text
/api/stripe_webhook.php
```

The webhook should:

- verify the Stripe signature,
- record the raw event in `payment_events_tb`,
- update `payments_tb.status`,
- update pledge/registration payment status,
- optionally create or update accounting rows.

## Useful Stripe metadata

When creating the Stripe session, include metadata such as:

- `training_id`
- `registration_id`
- `pledge_id`
- `buwana_id`
- `app_code = gobrik`

This makes webhook reconciliation much easier.

## Stripe does not need to manage the threshold

This is the key architectural point: Stripe handles **payment collection**, not **course viability logic**. The threshold campaign belongs entirely in GoBrik’s own database and backend.

---

## Midtrans Role

Midtrans should play the equivalent role for Indonesian rupiah payments.

The routing rule can be:

- `IDR` → Midtrans
- non-`IDR` → Stripe

This decision belongs in GoBrik backend logic, not in the UI alone.

---

## Recommended Implementation Sequence

## Phase 1 — Database foundation

1. Extend the training table for threshold settings.
2. Add pledge support, preferably with `training_pledges_tb`.
3. Add source/origin linkage fields to payment tables.
4. Add any needed indexes for reporting and webhook reconciliation.

## Phase 2 — Pledge UI

1. Add course progress display.
2. Build pledge slider screen.
3. Build pledge confirmation screen.
4. Add success message after pledge creation.

## Phase 3 — Threshold engine

1. Build pledge totals query.
2. Build threshold evaluation logic.
3. Build course confirm/fail logic.
4. Build email notifications.

## Phase 4 — Payment collection

1. Create payment rows from pledges.
2. Implement Stripe Checkout integration.
3. Implement Midtrans integration.
4. Process webhooks and update records.
5. Link cash transactions and reporting.

## Phase 5 — Admin and reporting

1. Add admin threshold controls.
2. Add payment invitation controls.
3. Add pledge and payment reporting.
4. Add failure and refund handling later if needed.

---

---

## Community Registration — Whole-Group Booking

### Overview

Community Registration is an extension of the 3P system that allows an individual to book an entire training for their community by committing to the full course funding amount and minimum participant count. Rather than pledging a personal portion, the community organiser commits to the entire course — making the course viable in a single step.

This model serves a different persona than the standard 3P pledge flow: the community leader or organisation coordinator who wants to bring a training to their specific group, on a date and in a language that suits them.

### New `threshold_status` value: `open_request`

A community training request creates a **new training record** that is a copy of an existing published course, with:
- A proposed new date, language, location, timezone description, and community association set by the requester
- `threshold_status = 'open_request'` — a new status value that flags the record as a community booking request awaiting trainer confirmation
- `ready_to_show = 0` — the course is not yet publicly visible
- `training_logged` set to the current datetime
- All pricing fields (`funding_goal_idr`, `min_participants_required`, `default_price_idr`) inherited from the source training

The original training is not modified.

### User Flow

#### 1. Discovery: Register my Community

On any `pledge_threshold` course registration page (`register.php`), below the primary Register button, a grey **"Register my Community"** button appears. Below it, small text reads: *"Have a whole community that wants to do this training? Register your own event."*

#### 2. Community-3p form (`community-3p.php`)

The requester is taken to a form page showing:
- The source training's title, subtitle, type, and lead trainer (read-only)
- A **Commitment Summary** card displaying:
  - Full course amount (`funding_goal_idr`) with a live currency converter
  - Minimum participant count
- A form to propose:
  - New training date and time
  - Time in key timezones (text)
  - Training language (select from all languages)
  - Training location
  - Community (autocomplete against the Buwana community search API)

Submitting the form POSTs to `processes/community_training_request_process.php`.

#### 3. Processing (`processes/community_training_request_process.php`)

The process file:
1. Validates session and POST data
2. Fetches the source training
3. Inserts a new `tb_trainings` record with `threshold_status = 'open_request'` and all inherited + proposed fields
4. Copies all trainers from `tb_training_trainers` to the new training record
5. Inserts the requester into `training_registrations_tb` with `status = 'reserved'`
6. Sends a notification email to `trainer_contact_email` (CC: requester) with a full breakdown
7. Sends a confirmation email to the requester acknowledging the request
8. Redirects to `community-3p.php?id=SOURCE_ID&requested=1&new_id=NEW_ID`

#### 4. Trainer review: Dashboard "My Trainings v2"

The new training appears in the trainer's **My Trainings v2** panel on their dashboard with an orange **"🏘 Community Request"** status pill. Clicking the pill opens a modal that:
- Displays training details (proposed date, language, location, community)
- Shows who made the request (name + email)
- Shows the full course amount and minimum participants
- Provides a pre-filled reply email text area (polite confirmation template)
- Includes a **slide toggle** to confirm the course is going ahead

**API: `api/fetch_community_request.php`** — fetches training + requester details for the modal.

**API: `api/confirm_community_training.php`** — processes the trainer's response:
- If **toggle is ON (confirmed)**:
  - Updates training: `threshold_status = 'reached'`, `course_confirmed_at = NOW()`, `ready_to_show = 1`
  - Creates a `training_pledges_tb` record for the requester with `pledged_amount_idr = funding_goal_idr` and `pledge_status = 'invited'`
  - Updates the registration to `status = 'awaiting_payment'`
  - Sends a confirmation email to the requester that includes a **"Complete Community Course Payment →"** button linking to `community-pledge-pay.php?id=NEW_TRAINING_ID`
- If **toggle is OFF**: sends a generic update/reply email
- Either way, a reply email is sent to the requester

#### 5. Community payment (`community-pledge-pay.php`)

The requester follows the payment link to `community-pledge-pay.php`, a page based on `pledge-pay.php` that:
- Shows the training details under a "Your Community Commitment" card displaying the full course amount
- Uses the same 3P progress graph
- Routes to `create_midtrans_payment.php` (IDR) or `create_stripe_payment.php` (non-IDR) via the existing payment gateway APIs

### Key Files

| File | Purpose |
|---|---|
| `/en/community-3p.php` | Community booking form page |
| `/includes/community-3p-inc.php` | CSS + header include for community-3p |
| `/meta/community-3p-{lang}.php` | SEO/OG meta tags (en, id, es, fr) |
| `/processes/community_training_request_process.php` | POST handler: clone training, copy trainers, send emails |
| `/api/fetch_community_request.php` | GET: training + requester details for trainer modal |
| `/api/confirm_community_training.php` | POST: trainer confirms or replies to community request |
| `/en/community-pledge-pay.php` | Community payment page (full course amount) |
| `/includes/community-pledge-pay-inc.php` | CSS + header include for community payment page |
| `/meta/community-pledge-pay-{lang}.php` | SEO/OG meta tags for payment page (en, id, es, fr) |

### No New Database Fields Required

The community registration flow uses only existing `tb_trainings` fields:
- `threshold_status = 'open_request'` — new value for an existing VARCHAR field
- All other fields already exist in the schema

The `training_registrations_tb` table tracks the requester via `status = 'reserved'`, and `training_pledges_tb` records the community pledge when the trainer confirms.

---

##  Making it happen!

While this may sound like a big undertaking, in fact, the current GoBrik payment schema is already well-positioned to support the **actual payment processing** side of this feature, especially because it separates payment headers, line items, webhook events, and accounting linkage. The major missing layer is the **pledge-and-threshold logic**, which should be modeled explicitly rather than forcing it into the payment tables too early.

The cleanest and most durable design is to:

- keep registrations,
- keep pledges,
- keep payments,
- and connect them with clear status transitions.

That gives GoBrik a flexible foundation for this training model now that embodies our principles and can serve other groups (like New Forest Aquaponics or Asihdiri!), and also opens the door later to use the same pattern for other campaign-style transactions across the platform.  For example Rere's idea of a kickstarter for new GEA products.