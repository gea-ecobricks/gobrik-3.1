-- ------------------------------------------------------------
-- payments_tb
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payments_tb (
payment_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
buwana_id BIGINT UNSIGNED NOT NULL,

app_code VARCHAR(32) NOT NULL DEFAULT 'gobrik',

currency CHAR(3) NOT NULL,
amount_total INT UNSIGNED NOT NULL,      -- minor units recommended
amount_tax   INT UNSIGNED NOT NULL DEFAULT 0,
amount_fee   INT UNSIGNED NOT NULL DEFAULT 0,
amount_net   INT UNSIGNED NOT NULL DEFAULT 0,

status VARCHAR(20) NOT NULL DEFAULT 'created',

gateway VARCHAR(16) NOT NULL,            -- 'stripe' | 'midtrans'
gateway_method VARCHAR(32) DEFAULT NULL,
gateway_ref VARCHAR(128) DEFAULT NULL,   -- set once you have it
gateway_status VARCHAR(64) DEFAULT NULL,

client_reference VARCHAR(64) DEFAULT NULL,
idempotency_key VARCHAR(64) DEFAULT NULL,

created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
paid_at DATETIME DEFAULT NULL,

gateway_payload_json JSON DEFAULT NULL,

PRIMARY KEY (payment_id),
KEY idx_payments_buwana (buwana_id),
KEY idx_payments_status (status),
KEY idx_payments_gateway (gateway, gateway_ref),

UNIQUE KEY uq_idempotency (idempotency_key)

-- NOTE:
-- I am intentionally NOT adding UNIQUE(gateway, gateway_ref) here,
-- because Day-1 flows often create payment rows before gateway_ref exists.
-- If you ALWAYS set gateway_ref immediately, you can add it later.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ------------------------------------------------------------
-- payment_items_tb
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payment_items_tb (
payment_item_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
payment_id BIGINT UNSIGNED NOT NULL,

item_type VARCHAR(32) NOT NULL,        -- 'training_registration' day 1
item_ref_id BIGINT UNSIGNED NOT NULL,  -- registration_id etc.

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ------------------------------------------------------------
-- payment_events_tb
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payment_events_tb (
payment_event_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
payment_id BIGINT UNSIGNED DEFAULT NULL,

gateway VARCHAR(16) NOT NULL,
gateway_event_id VARCHAR(128) NOT NULL,
gateway_ref VARCHAR(128) DEFAULT NULL,     -- add this so you can attach events before you know payment_id
event_type VARCHAR(64) NOT NULL,
verified TINYINT(1) NOT NULL DEFAULT 0,

payload_json JSON NOT NULL,
created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

PRIMARY KEY (payment_event_id),
UNIQUE KEY uq_gateway_event (gateway, gateway_event_id),
KEY idx_events_payment (payment_id),
KEY idx_events_gateway_ref (gateway, gateway_ref)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ------------------------------------------------------------
-- training_registrations_tb
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS training_registrations_tb (
registration_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
training_id INT(11) UNSIGNED NOT NULL,
buwana_id BIGINT UNSIGNED NOT NULL,

status VARCHAR(20) NOT NULL DEFAULT 'reserved',

created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
confirmed_at DATETIME DEFAULT NULL,
cancelled_at DATETIME DEFAULT NULL,

attendee_name VARCHAR(255) DEFAULT NULL,
attendee_email VARCHAR(255) DEFAULT NULL,

PRIMARY KEY (registration_id),
KEY idx_reg_training (training_id),
KEY idx_reg_buwana (buwana_id),
KEY idx_reg_status (status)

-- Optional FK if trainings_tb is InnoDB and types match:
-- ,CONSTRAINT fk_reg_training
--   FOREIGN KEY (training_id) REFERENCES trainings_tb(training_id)
--   ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ------------------------------------------------------------
-- Alter tb_cash_transaction (Day-1 accounting linkage)
-- ------------------------------------------------------------
ALTER TABLE tb_cash_transaction
ADD COLUMN payment_id BIGINT UNSIGNED NULL AFTER cash_tran_id,
ADD COLUMN gateway_ref VARCHAR(128) NULL AFTER tran_processor,
ADD KEY idx_cash_payment (payment_id),
ADD KEY idx_cash_gateway (tran_processor, gateway_ref);