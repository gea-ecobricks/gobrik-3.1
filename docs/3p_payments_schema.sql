-- ============================================================
-- GoBrik Shared 3P Schema
-- Pledge, Proceed, and Pay for Trainings and Products
-- ============================================================


-- ------------------------------------------------------------
-- 0. OPTIONAL: drop old pledge table so it can be recreated
-- ------------------------------------------------------------
DROP TABLE IF EXISTS training_pledges_tb;


-- ------------------------------------------------------------
-- 1. payments_tb
-- Shared actual payment table for trainings, products, offsets, etc.
-- This stores REAL payment attempts / gateway transactions only.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payments_tb (
                                           payment_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                           buwana_id BIGINT UNSIGNED NOT NULL,

                                           campaign_type VARCHAR(24) DEFAULT NULL,
    -- Suggested:
    -- 'training', 'product', 'offset'

    campaign_id BIGINT UNSIGNED DEFAULT NULL,
    pledge_id BIGINT UNSIGNED DEFAULT NULL,
    registration_id BIGINT UNSIGNED DEFAULT NULL,
    order_id BIGINT UNSIGNED DEFAULT NULL,

    app_code VARCHAR(32) NOT NULL DEFAULT 'gobrik',
    payment_purpose VARCHAR(32) NOT NULL DEFAULT 'training_registration',
    -- Suggested:
    -- 'training_registration', 'product_order', 'plastic_offset', 'donation'

    currency CHAR(3) NOT NULL,
    amount_total INT UNSIGNED NOT NULL,      -- minor units recommended
    amount_tax   INT UNSIGNED NOT NULL DEFAULT 0,
    amount_fee   INT UNSIGNED NOT NULL DEFAULT 0,
    amount_net   INT UNSIGNED NOT NULL DEFAULT 0,

    status VARCHAR(20) NOT NULL DEFAULT 'created',
    -- Suggested:
    -- 'created', 'pending', 'paid', 'failed', 'expired', 'cancelled', 'refunded'

    gateway VARCHAR(16) NOT NULL,
    -- 'stripe' | 'midtrans'

    gateway_method VARCHAR(32) DEFAULT NULL,
    gateway_ref VARCHAR(128) DEFAULT NULL,
    gateway_status VARCHAR(64) DEFAULT NULL,

    client_reference VARCHAR(64) DEFAULT NULL,
    idempotency_key VARCHAR(64) DEFAULT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    payment_requested_at DATETIME DEFAULT NULL,
    expires_at DATETIME DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    paid_at DATETIME DEFAULT NULL,

    gateway_payload_json JSON DEFAULT NULL,

    PRIMARY KEY (payment_id),

    KEY idx_payments_buwana (buwana_id),
    KEY idx_payments_campaign (campaign_type, campaign_id),
    KEY idx_payments_pledge (pledge_id),
    KEY idx_payments_registration (registration_id),
    KEY idx_payments_order (order_id),
    KEY idx_payments_purpose (payment_purpose),
    KEY idx_payments_status (status),
    KEY idx_payments_gateway (gateway, gateway_ref),

    UNIQUE KEY uq_idempotency (idempotency_key)

    -- Note:
    -- Not adding FK constraints here because pledge_id may point to either
    -- training_pledges_tb or product_pledges_tb depending on campaign_type.
    -- registration_id is for training registrations.
    -- order_id is for product orders.
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ------------------------------------------------------------
-- 2. payment_items_tb
-- Line items for each actual payment
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payment_items_tb (
                                                payment_item_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                                payment_id BIGINT UNSIGNED NOT NULL,

                                                item_type VARCHAR(32) NOT NULL,
    -- Suggested:
    -- 'training_registration', 'product_order', 'plastic_offset'

    item_ref_id BIGINT UNSIGNED NOT NULL,
    -- training_registration => registration_id
    -- product_order         => order_id

    qty INT UNSIGNED NOT NULL DEFAULT 1,

    currency CHAR(3) NOT NULL,
    unit_amount INT UNSIGNED NOT NULL,
    line_total  INT UNSIGNED NOT NULL,

    meta_json JSON DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (payment_item_id),
    KEY idx_items_payment (payment_id),
    KEY idx_items_ref (item_type, item_ref_id),

    CONSTRAINT fk_items_payment
    FOREIGN KEY (payment_id) REFERENCES payments_tb(payment_id)
    ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ------------------------------------------------------------
-- 3. payment_events_tb
-- Gateway webhook / event log
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payment_events_tb (
                                                 payment_event_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                                 payment_id BIGINT UNSIGNED DEFAULT NULL,

                                                 gateway VARCHAR(16) NOT NULL,
    gateway_event_id VARCHAR(128) NOT NULL,
    gateway_ref VARCHAR(128) DEFAULT NULL,
    event_type VARCHAR(64) NOT NULL,
    verified TINYINT(1) NOT NULL DEFAULT 0,

    payload_json JSON NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME DEFAULT NULL,
    processing_status VARCHAR(24) DEFAULT NULL,
    -- Suggested:
    -- 'pending', 'processed', 'ignored', 'failed'

    PRIMARY KEY (payment_event_id),
    UNIQUE KEY uq_gateway_event (gateway, gateway_event_id),
    KEY idx_events_payment (payment_id),
    KEY idx_events_gateway_ref (gateway, gateway_ref)

    -- Optional FK later if wanted:
    -- ,CONSTRAINT fk_events_payment
    --   FOREIGN KEY (payment_id) REFERENCES payments_tb(payment_id)
    --   ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ------------------------------------------------------------
-- 4. training_pledges_tb
-- User pledge commitments for trainings before actual payment
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS training_pledges_tb (
                                                   pledge_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                                   training_id INT(11) UNSIGNED NOT NULL,
    buwana_id BIGINT UNSIGNED NOT NULL,

    pledge_currency CHAR(3) NOT NULL DEFAULT 'IDR',
    pledged_amount_idr INT UNSIGNED NOT NULL,

    display_currency VARCHAR(10) NOT NULL DEFAULT 'IDR',
    display_amount DECIMAL(12,2) DEFAULT NULL,

    suggested_amount_idr INT UNSIGNED DEFAULT NULL,

    pledge_status VARCHAR(24) NOT NULL DEFAULT 'active',
    -- Suggested:
    -- 'active', 'invited', 'paid', 'cancelled', 'expired', 'failed'

    invited_to_pay_at DATETIME DEFAULT NULL,
    payment_due_at DATETIME DEFAULT NULL,
    payment_id BIGINT UNSIGNED DEFAULT NULL,

    confirmed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    cancelled_at DATETIME DEFAULT NULL,
    expired_at DATETIME DEFAULT NULL,
    paid_at DATETIME DEFAULT NULL,

    note TEXT DEFAULT NULL,
    meta_json JSON DEFAULT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (pledge_id),
    KEY idx_training (training_id),
    KEY idx_buwana (buwana_id),
    KEY idx_status (pledge_status),
    KEY idx_training_status (training_id, pledge_status),
    KEY idx_payment_id (payment_id),

    UNIQUE KEY uq_training_user_status (training_id, buwana_id, pledge_status),

    CONSTRAINT fk_training_pledge_training
    FOREIGN KEY (training_id) REFERENCES tb_trainings(training_id)
                                                           ON DELETE CASCADE

    -- payment_id intentionally not FK'd because payments_tb is shared and
    -- can be created in different workflow steps
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ------------------------------------------------------------
-- 5. training_registrations_tb
-- Enrollment state for a user in a training
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS training_registrations_tb (
                                                         registration_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                                         training_id INT(11) UNSIGNED NOT NULL,
    buwana_id BIGINT UNSIGNED NOT NULL,
    pledge_id BIGINT UNSIGNED DEFAULT NULL,

    status VARCHAR(20) NOT NULL DEFAULT 'reserved',
    -- Suggested:
    -- 'reserved', 'pledged', 'awaiting_payment', 'confirmed', 'cancelled', 'expired'

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    confirmed_at DATETIME DEFAULT NULL,
    invited_to_pay_at DATETIME DEFAULT NULL,
    payment_due_at DATETIME DEFAULT NULL,
    payment_completed_at DATETIME DEFAULT NULL,
    cancelled_at DATETIME DEFAULT NULL,

    attendee_name VARCHAR(255) DEFAULT NULL,
    attendee_email VARCHAR(255) DEFAULT NULL,

    PRIMARY KEY (registration_id),
    KEY idx_reg_training (training_id),
    KEY idx_reg_buwana (buwana_id),
    KEY idx_reg_pledge (pledge_id),
    KEY idx_reg_status (status),

    CONSTRAINT fk_reg_training
    FOREIGN KEY (training_id) REFERENCES tb_trainings(training_id)
    ON DELETE RESTRICT,

    CONSTRAINT fk_reg_pledge
    FOREIGN KEY (pledge_id) REFERENCES training_pledges_tb(pledge_id)
    ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ------------------------------------------------------------
-- 6. products_tb
-- Product catalog
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products_tb (
                                           product_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                           product_code VARCHAR(64) DEFAULT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_subtitle VARCHAR(255) DEFAULT NULL,
    product_description LONGTEXT DEFAULT NULL,

    product_type VARCHAR(32) NOT NULL DEFAULT 'physical',
    -- Suggested:
    -- 'physical', 'digital', 'service'

    sku VARCHAR(64) DEFAULT NULL,

    base_currency CHAR(3) NOT NULL DEFAULT 'IDR',
    default_price_idr INT UNSIGNED DEFAULT NULL,
    display_price VARCHAR(255) DEFAULT NULL,

    stock_mode VARCHAR(24) NOT NULL DEFAULT 'campaign',
    -- Suggested:
    -- 'campaign', 'stocked', 'made_to_order'

    product_status VARCHAR(24) NOT NULL DEFAULT 'draft',
    -- Suggested:
    -- 'draft', 'active', 'inactive', 'archived'

    weight_grams INT UNSIGNED DEFAULT NULL,
    length_mm INT UNSIGNED DEFAULT NULL,
    width_mm INT UNSIGNED DEFAULT NULL,
    height_mm INT UNSIGNED DEFAULT NULL,

    featured_image_main VARCHAR(255) DEFAULT NULL,
    featured_image_tmb VARCHAR(255) DEFAULT NULL,

    product_url VARCHAR(255) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    meta_json JSON DEFAULT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (product_id),
    UNIQUE KEY uq_product_code (product_code),
    KEY idx_product_status (product_status),
    KEY idx_product_type (product_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ------------------------------------------------------------
-- 7. product_campaigns_tb
-- Threshold / preorder campaign rules for a product
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_campaigns_tb (
                                                    campaign_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                                    product_id BIGINT UNSIGNED NOT NULL,

                                                    campaign_title VARCHAR(255) NOT NULL,
    campaign_subtitle VARCHAR(255) DEFAULT NULL,
    campaign_description LONGTEXT DEFAULT NULL,

    campaign_mode VARCHAR(24) NOT NULL DEFAULT 'pledge_threshold',
    -- Suggested:
    -- 'pledge_threshold', 'preorder', 'fixed_sale'

    base_currency CHAR(3) NOT NULL DEFAULT 'IDR',
    default_price_idr INT UNSIGNED DEFAULT NULL,

    min_backers_required INT UNSIGNED DEFAULT NULL,
    funding_goal_idr INT UNSIGNED DEFAULT NULL,

    min_pledge_idr INT UNSIGNED NOT NULL DEFAULT 0,
    max_pledge_idr INT UNSIGNED DEFAULT NULL,
    allow_overpledge TINYINT(1) NOT NULL DEFAULT 1,

    pledge_deadline DATETIME DEFAULT NULL,
    payment_deadline DATETIME DEFAULT NULL,

    threshold_status VARCHAR(24) NOT NULL DEFAULT 'open',
    -- Suggested:
    -- 'open', 'threshold_met', 'confirmed', 'failed', 'cancelled', 'completed'

    threshold_reached_at DATETIME DEFAULT NULL,
    campaign_confirmed_at DATETIME DEFAULT NULL,
    campaign_failed_at DATETIME DEFAULT NULL,

    auto_confirm_threshold TINYINT(1) NOT NULL DEFAULT 1,

    delivery_mode VARCHAR(24) NOT NULL DEFAULT 'ship',
    -- Suggested:
    -- 'ship', 'pickup', 'digital'

    estimated_delivery_date DATE DEFAULT NULL,

    ready_to_show TINYINT(1) NOT NULL DEFAULT 0,
    notes TEXT DEFAULT NULL,
    meta_json JSON DEFAULT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (campaign_id),
    KEY idx_campaign_product (product_id),
    KEY idx_campaign_status (threshold_status),
    KEY idx_campaign_deadline (pledge_deadline),

    CONSTRAINT fk_campaign_product
    FOREIGN KEY (product_id) REFERENCES products_tb(product_id)
                                                           ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ------------------------------------------------------------
-- 8. product_pledges_tb
-- User pledge commitments for products before actual payment
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_pledges_tb (
                                                  pledge_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                                  campaign_id BIGINT UNSIGNED NOT NULL,
                                                  product_id BIGINT UNSIGNED NOT NULL,
                                                  buwana_id BIGINT UNSIGNED NOT NULL,

                                                  pledge_currency CHAR(3) NOT NULL DEFAULT 'IDR',
    pledged_amount_idr INT UNSIGNED NOT NULL,

    display_currency VARCHAR(10) NOT NULL DEFAULT 'IDR',
    display_amount DECIMAL(12,2) DEFAULT NULL,

    suggested_amount_idr INT UNSIGNED DEFAULT NULL,
    qty INT UNSIGNED NOT NULL DEFAULT 1,

    pledge_status VARCHAR(24) NOT NULL DEFAULT 'active',
    -- Suggested:
    -- 'active', 'invited', 'paid', 'cancelled', 'expired', 'failed'

    invited_to_pay_at DATETIME DEFAULT NULL,
    payment_due_at DATETIME DEFAULT NULL,
    payment_id BIGINT UNSIGNED DEFAULT NULL,

    confirmed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    cancelled_at DATETIME DEFAULT NULL,
    expired_at DATETIME DEFAULT NULL,
    paid_at DATETIME DEFAULT NULL,

    shipping_name VARCHAR(255) DEFAULT NULL,
    shipping_email VARCHAR(255) DEFAULT NULL,
    shipping_phone VARCHAR(64) DEFAULT NULL,

    note TEXT DEFAULT NULL,
    meta_json JSON DEFAULT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (pledge_id),
    KEY idx_campaign (campaign_id),
    KEY idx_product (product_id),
    KEY idx_buwana (buwana_id),
    KEY idx_status (pledge_status),
    KEY idx_campaign_status (campaign_id, pledge_status),
    KEY idx_payment_id (payment_id),

    UNIQUE KEY uq_product_campaign_user_status (campaign_id, buwana_id, pledge_status),

    CONSTRAINT fk_product_pledge_campaign
    FOREIGN KEY (campaign_id) REFERENCES product_campaigns_tb(campaign_id)
                                                           ON DELETE CASCADE,

    CONSTRAINT fk_product_pledge_product
    FOREIGN KEY (product_id) REFERENCES products_tb(product_id)
                                                           ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ------------------------------------------------------------
-- 9. product_orders_tb
-- Product preorder / order / fulfillment state
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_orders_tb (
                                                 order_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                                 campaign_id BIGINT UNSIGNED DEFAULT NULL,
                                                 product_id BIGINT UNSIGNED NOT NULL,
                                                 buwana_id BIGINT UNSIGNED NOT NULL,
                                                 pledge_id BIGINT UNSIGNED DEFAULT NULL,

                                                 status VARCHAR(24) NOT NULL DEFAULT 'reserved',
    -- Suggested:
    -- 'reserved', 'pledged', 'awaiting_payment', 'paid', 'processing',
    -- 'shipped', 'completed', 'cancelled', 'expired'

    qty INT UNSIGNED NOT NULL DEFAULT 1,

    unit_price_idr INT UNSIGNED DEFAULT NULL,
    total_price_idr INT UNSIGNED DEFAULT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    invited_to_pay_at DATETIME DEFAULT NULL,
    payment_due_at DATETIME DEFAULT NULL,
    payment_completed_at DATETIME DEFAULT NULL,

    fulfilled_at DATETIME DEFAULT NULL,
    shipped_at DATETIME DEFAULT NULL,
    delivered_at DATETIME DEFAULT NULL,
    cancelled_at DATETIME DEFAULT NULL,

    customer_name VARCHAR(255) DEFAULT NULL,
    customer_email VARCHAR(255) DEFAULT NULL,
    customer_phone VARCHAR(64) DEFAULT NULL,

    shipping_name VARCHAR(255) DEFAULT NULL,
    shipping_address1 VARCHAR(255) DEFAULT NULL,
    shipping_address2 VARCHAR(255) DEFAULT NULL,
    shipping_city VARCHAR(255) DEFAULT NULL,
    shipping_region VARCHAR(255) DEFAULT NULL,
    shipping_postal_code VARCHAR(32) DEFAULT NULL,
    shipping_country CHAR(2) DEFAULT NULL,

    notes TEXT DEFAULT NULL,
    meta_json JSON DEFAULT NULL,

    PRIMARY KEY (order_id),
    KEY idx_order_campaign (campaign_id),
    KEY idx_order_product (product_id),
    KEY idx_order_buwana (buwana_id),
    KEY idx_order_pledge (pledge_id),
    KEY idx_order_status (status),

    CONSTRAINT fk_product_order_campaign
    FOREIGN KEY (campaign_id) REFERENCES product_campaigns_tb(campaign_id)
    ON DELETE SET NULL,

    CONSTRAINT fk_product_order_product
    FOREIGN KEY (product_id) REFERENCES products_tb(product_id)
    ON DELETE RESTRICT,

    CONSTRAINT fk_product_order_pledge
    FOREIGN KEY (pledge_id) REFERENCES product_pledges_tb(pledge_id)
    ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ------------------------------------------------------------
-- 10. Alter tb_cash_transaction
-- Accounting linkage for actual money movement only
-- NOTE: tb_cash_transaction is MyISAM, so no FK constraint here.
-- ------------------------------------------------------------
ALTER TABLE tb_cash_transaction
    ADD COLUMN payment_id BIGINT UNSIGNED NULL AFTER cash_tran_id,
  ADD COLUMN gateway_ref VARCHAR(128) NULL AFTER tran_processor,
  ADD KEY idx_cash_payment (payment_id),
  ADD KEY idx_cash_gateway (tran_processor, gateway_ref);


CREATE TABLE `tb_trainings` (
                                `training_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                `training_date` datetime DEFAULT NULL,
                                `training_time_txt` varchar(255) DEFAULT NULL,
                                `training_title` text NOT NULL,
                                `training_subtitle` text NOT NULL,
                                `training_logged` date DEFAULT NULL,
                                `no_participants` smallint(11) NOT NULL,
                                `lead_trainer` text DEFAULT NULL,
                                `training_type` varchar(50) DEFAULT NULL,
                                `training_language` char(2) NOT NULL DEFAULT 'en',
                                `briks_made` smallint(6) DEFAULT NULL,
                                `avg_brik_weight` smallint(6) DEFAULT NULL,
                                `est_plastic_packed` smallint(6) DEFAULT NULL,
                                `training_country` text NOT NULL,
                                `training_location` varchar(255) DEFAULT NULL,
                                `location_geo` point DEFAULT NULL,
                                `location_lat` decimal(10,8) DEFAULT NULL,
                                `location_long` decimal(11,8) DEFAULT NULL,
                                `training_photo0_main` varchar(255) DEFAULT NULL,
                                `training_photo0_tmb` varchar(255) NOT NULL,
                                `training_photo1_main` varchar(255) DEFAULT NULL,
                                `training_photo1_tmb` varchar(255) DEFAULT NULL,
                                `training_photo2_main` varchar(255) DEFAULT NULL,
                                `training_photo2_tmb` varchar(255) DEFAULT NULL,
                                `training_photo3_main` varchar(255) DEFAULT NULL,
                                `training_photo3_tmb` varchar(255) DEFAULT NULL,
                                `training_photo4_main` varchar(255) DEFAULT NULL,
                                `training_photo4_tmb` varchar(255) DEFAULT NULL,
                                `training_photo5_main` varchar(255) DEFAULT NULL,
                                `training_photo5_tmb` varchar(255) DEFAULT NULL,
                                `training_photo6_main` varchar(255) DEFAULT NULL,
                                `training_photo6_tmb` varchar(255) DEFAULT NULL,
                                `training_summary` longtext DEFAULT NULL,
                                `training_agenda` text DEFAULT NULL,
                                `training_agenda_for_report` text NOT NULL,
                                `training_success` text DEFAULT NULL,
                                `training_challenges` text DEFAULT NULL,
                                `training_lessons_learned` text DEFAULT NULL,
                                `training_url` varchar(255) DEFAULT NULL,
                                `youtube_result_video` varchar(255) DEFAULT NULL,
                                `trainer_contact_email` varchar(255) DEFAULT NULL,
                                `connected_ecobricks` text DEFAULT NULL,
                                `ready_to_show` tinyint(1) DEFAULT NULL,
                                `zoom_link` text DEFAULT NULL,
                                `zoom_link_full` text DEFAULT NULL,
                                `feature_photo1_main` varchar(255) DEFAULT NULL,
                                `feature_photo1_tmb` varchar(255) DEFAULT NULL,
                                `feature_photo2_main` varchar(255) DEFAULT NULL,
                                `feature_photo2_tmb` varchar(255) DEFAULT NULL,
                                `feature_photo3_main` varchar(255) DEFAULT NULL,
                                `feature_photo3_tmb` varchar(255) DEFAULT NULL,
                                `registration_scope` varchar(24) DEFAULT 'All',
                                `registration_url` varchar(255) DEFAULT 'https://gobrik.com/en/register.php',
                                `agenda_url` varchar(255) DEFAULT NULL,
                                `moodle_url` varchar(255) DEFAULT NULL,
                                `country_id` int(11) DEFAULT NULL,
                                `community_id` int(11) unsigned DEFAULT NULL,
                                `featured_description` longtext DEFAULT NULL,
                                `display_cost` text NOT NULL DEFAULT 'Free / Donation',
                                `Cost` int(11) DEFAULT NULL,
                                `Currency` varchar(25) DEFAULT NULL,
                                `default_price_idr` int(10) unsigned DEFAULT NULL,
                                `base_currency` char(3) DEFAULT 'IDR',
                                `payment_mode` varchar(24) NOT NULL DEFAULT 'free',
                                `min_participants_required` smallint(5) unsigned DEFAULT NULL,
                                `funding_goal_idr` int(10) unsigned DEFAULT NULL,
                                `pledge_deadline` datetime DEFAULT NULL,
                                `payment_deadline` datetime DEFAULT NULL,
                                `threshold_status` varchar(24) NOT NULL DEFAULT 'open',
                                `threshold_reached_at` datetime DEFAULT NULL,
                                `course_confirmed_at` datetime DEFAULT NULL,
                                `course_failed_at` datetime DEFAULT NULL,
                                `auto_confirm_threshold` tinyint(1) NOT NULL DEFAULT 1,
                                `allow_overpledge` tinyint(1) NOT NULL DEFAULT 1,
                                `min_pledge_idr` int(10) unsigned NOT NULL DEFAULT 0,
                                `max_pledge_idr` int(10) unsigned DEFAULT NULL,
                                `show_report` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Showing the final training report on ecobricks.org',
                                `show_signup_count` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Show the signup count on the public registration page',
                                `earthcal_event_url` text DEFAULT NULL,
                                PRIMARY KEY (`training_id`),
                                KEY `fk_training_community` (`community_id`),
                                KEY `fk_training_country` (`country_id`),
                                CONSTRAINT `fk_training_community` FOREIGN KEY (`community_id`) REFERENCES `communities_tbX` (`community_id`) ON DELETE SET NULL,
                                CONSTRAINT `fk_training_country` FOREIGN KEY (`country_id`) REFERENCES `countries_tb` (`country_id`)
) ENGINE=InnoDB AUTO_INCREMENT=963 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci


-- ------------------------------------------------------------
-- 11. product_campaign_tiers_tb
-- Optional reward / pricing tiers for a product campaign
-- Useful for: ebook, paperback, signed copy, patron tier, bundle, etc.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_campaign_tiers_tb (
                                                         tier_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                                         campaign_id BIGINT UNSIGNED NOT NULL,
                                                         product_id BIGINT UNSIGNED NOT NULL,

                                                         tier_code VARCHAR(64) DEFAULT NULL,
    tier_name VARCHAR(255) NOT NULL,
    tier_subtitle VARCHAR(255) DEFAULT NULL,
    tier_description LONGTEXT DEFAULT NULL,

    tier_currency CHAR(3) NOT NULL DEFAULT 'IDR',
    tier_price_idr INT UNSIGNED NOT NULL,

    qty_per_pledge INT UNSIGNED NOT NULL DEFAULT 1,
    max_backers INT UNSIGNED DEFAULT NULL,

    sort_order INT UNSIGNED NOT NULL DEFAULT 1,

    includes_shipping TINYINT(1) NOT NULL DEFAULT 0,
    shipping_scope VARCHAR(24) NOT NULL DEFAULT 'separate',
    -- Suggested:
    -- 'separate', 'indonesia_included', 'worldwide_included', 'pickup_only', 'digital'

    tier_status VARCHAR(24) NOT NULL DEFAULT 'active',
    -- Suggested:
    -- 'draft', 'active', 'sold_out', 'inactive', 'archived'

    available_from DATETIME DEFAULT NULL,
    available_until DATETIME DEFAULT NULL,

    notes TEXT DEFAULT NULL,
    meta_json JSON DEFAULT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (tier_id),
    UNIQUE KEY uq_campaign_tier_code (campaign_id, tier_code),
    KEY idx_tier_campaign (campaign_id),
    KEY idx_tier_product (product_id),
    KEY idx_tier_status (tier_status),
    KEY idx_tier_sort (campaign_id, sort_order),

    CONSTRAINT fk_tier_campaign
    FOREIGN KEY (campaign_id) REFERENCES product_campaigns_tb(campaign_id)
                                                           ON DELETE CASCADE,

    CONSTRAINT fk_tier_product
    FOREIGN KEY (product_id) REFERENCES products_tb(product_id)
                                                           ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ------------------------------------------------------------
-- 12. product_campaign_updates_tb
-- Public-facing updates for the campaign page
-- Useful for: launch notes, threshold reached, print progress, shipping updates
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_campaign_updates_tb (
                                                           update_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                                           campaign_id BIGINT UNSIGNED NOT NULL,

                                                           update_title VARCHAR(255) NOT NULL,
    update_slug VARCHAR(255) DEFAULT NULL,
    update_summary TEXT DEFAULT NULL,
    update_body LONGTEXT NOT NULL,

    update_type VARCHAR(24) NOT NULL DEFAULT 'general',
    -- Suggested:
    -- 'general', 'milestone', 'threshold', 'production', 'shipping', 'announcement'

    visibility VARCHAR(24) NOT NULL DEFAULT 'public',
    -- Suggested:
    -- 'public', 'supporters_only', 'private'

    is_pinned TINYINT(1) NOT NULL DEFAULT 0,
    ready_to_show TINYINT(1) NOT NULL DEFAULT 1,

    published_at DATETIME DEFAULT NULL,
    created_by_buwana_id BIGINT UNSIGNED DEFAULT NULL,

    notes TEXT DEFAULT NULL,
    meta_json JSON DEFAULT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (update_id),
    UNIQUE KEY uq_campaign_update_slug (campaign_id, update_slug),
    KEY idx_update_campaign (campaign_id),
    KEY idx_update_type (update_type),
    KEY idx_update_visibility (visibility),
    KEY idx_update_published (published_at),
    KEY idx_update_pinned (campaign_id, is_pinned, published_at),

    CONSTRAINT fk_update_campaign
    FOREIGN KEY (campaign_id) REFERENCES product_campaigns_tb(campaign_id)
                                                           ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ------------------------------------------------------------
-- 13. product_shipping_rates_tb
-- Shipping rules for product campaigns / products
-- Supports Indonesia and international pricing
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_shipping_rates_tb (
                                                         shipping_rate_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

                                                         campaign_id BIGINT UNSIGNED DEFAULT NULL,
                                                         product_id BIGINT UNSIGNED DEFAULT NULL,
                                                         tier_id BIGINT UNSIGNED DEFAULT NULL,

                                                         rate_name VARCHAR(255) NOT NULL,
    rate_description VARCHAR(255) DEFAULT NULL,

    destination_scope VARCHAR(24) NOT NULL DEFAULT 'country',
    -- Suggested:
    -- 'pickup', 'country', 'region', 'zone', 'worldwide'

    country_code CHAR(2) DEFAULT NULL,
    region_code VARCHAR(64) DEFAULT NULL,
    zone_code VARCHAR(64) DEFAULT NULL,

    currency CHAR(3) NOT NULL DEFAULT 'IDR',
    shipping_amount INT UNSIGNED NOT NULL,

    per_unit_mode VARCHAR(24) NOT NULL DEFAULT 'per_order',
    -- Suggested:
    -- 'per_order', 'per_item'

    min_qty INT UNSIGNED NOT NULL DEFAULT 1,
    max_qty INT UNSIGNED DEFAULT NULL,

    min_weight_grams INT UNSIGNED DEFAULT NULL,
    max_weight_grams INT UNSIGNED DEFAULT NULL,

    shipping_method VARCHAR(32) DEFAULT NULL,
    -- Suggested:
    -- 'standard', 'express', 'pickup', 'economy'

    estimated_days_min INT UNSIGNED DEFAULT NULL,
    estimated_days_max INT UNSIGNED DEFAULT NULL,

    rate_status VARCHAR(24) NOT NULL DEFAULT 'active',
    -- Suggested:
    -- 'active', 'inactive', 'archived'

    sort_order INT UNSIGNED NOT NULL DEFAULT 1,

    notes TEXT DEFAULT NULL,
    meta_json JSON DEFAULT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (shipping_rate_id),
    KEY idx_ship_campaign (campaign_id),
    KEY idx_ship_product (product_id),
    KEY idx_ship_tier (tier_id),
    KEY idx_ship_country (country_code),
    KEY idx_ship_scope (destination_scope),
    KEY idx_ship_status (rate_status),
    KEY idx_ship_sort (sort_order),

    CONSTRAINT fk_ship_campaign
    FOREIGN KEY (campaign_id) REFERENCES product_campaigns_tb(campaign_id)
                                                           ON DELETE CASCADE,

    CONSTRAINT fk_ship_product
    FOREIGN KEY (product_id) REFERENCES products_tb(product_id)
                                                           ON DELETE CASCADE,

    CONSTRAINT fk_ship_tier
    FOREIGN KEY (tier_id) REFERENCES product_campaign_tiers_tb(tier_id)
                                                           ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;