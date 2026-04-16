-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 16, 2026 at 04:28 PM
-- Server version: 10.11.16-MariaDB-cll-lve
-- PHP Version: 8.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecobricks_gobrik_msql_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `continents_tb`
--

CREATE TABLE `continents_tb` (
  `continent_code` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `continent_name` varchar(50) NOT NULL,
  `continent_name_en` varchar(50) NOT NULL,
  `continent_name_es` varchar(50) NOT NULL,
  `continent_name_fr` varchar(50) NOT NULL,
  `continent_name_id` varchar(50) NOT NULL,
  `number_of_countries` int(11) DEFAULT 0,
  `area_sq_km` decimal(15,2) DEFAULT NULL,
  `population` bigint(20) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `countries_tb`
--

CREATE TABLE `countries_tb` (
  `country_id` int(11) NOT NULL,
  `country_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country_population` int(11) NOT NULL,
  `country_plastic_consumption` decimal(15,2) NOT NULL,
  `per_capita_consumption` decimal(15,2) NOT NULL,
  `country_code` varchar(5) NOT NULL,
  `country_language` varchar(255) NOT NULL,
  `country_continent` varchar(100) DEFAULT NULL,
  `iso_alpha_2` char(2) DEFAULT NULL,
  `iso_alpha_3` char(3) DEFAULT NULL,
  `currency_code` char(3) DEFAULT NULL,
  `time_zone` varchar(100) DEFAULT NULL,
  `capital_city` varchar(255) DEFAULT NULL,
  `official_languages` text DEFAULT NULL,
  `internet_domain` varchar(10) DEFAULT NULL,
  `calling_code` varchar(10) DEFAULT NULL,
  `population_density` decimal(15,2) DEFAULT NULL,
  `area_sq_km` decimal(15,2) DEFAULT NULL,
  `gdp` decimal(15,2) DEFAULT NULL,
  `per_capita_data_year` year(4) DEFAULT NULL,
  `continent_code` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dash_notices_tb`
--

CREATE TABLE `dash_notices_tb` (
  `notice_id` bigint(20) UNSIGNED NOT NULL,
  `message_body` text NOT NULL,
  `message_emoji` varchar(10) DEFAULT NULL,
  `featured_url` varchar(255) DEFAULT NULL,
  `featured_text` varchar(100) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive','archived') DEFAULT 'active',
  `background_colour` varchar(55) NOT NULL DEFAULT '#00c916;'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `earthen_mailgun_events_tb`
--

CREATE TABLE `earthen_mailgun_events_tb` (
  `event_id` bigint(20) UNSIGNED NOT NULL,
  `member_id` int(10) UNSIGNED DEFAULT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `campaign_name` varchar(191) DEFAULT NULL,
  `newsletter_slug` varchar(191) DEFAULT NULL,
  `mailgun_message_id` varchar(191) DEFAULT NULL,
  `mailgun_event_id` varchar(191) DEFAULT NULL,
  `event_type` varchar(50) NOT NULL,
  `event_timestamp` datetime NOT NULL,
  `received_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `severity` varchar(50) DEFAULT NULL,
  `reason` varchar(191) DEFAULT NULL,
  `error_code` varchar(50) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `client_ip` varchar(45) DEFAULT NULL,
  `client_country` varchar(100) DEFAULT NULL,
  `client_region` varchar(100) DEFAULT NULL,
  `client_city` varchar(100) DEFAULT NULL,
  `client_device` varchar(191) DEFAULT NULL,
  `clicked_url` text DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `user_variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`user_variables`)),
  `raw_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_payload`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `earthen_member_engagement_tb`
--

CREATE TABLE `earthen_member_engagement_tb` (
  `recipient_email` varchar(255) NOT NULL,
  `member_id` int(10) UNSIGNED DEFAULT NULL,
  `last_event_at` datetime DEFAULT NULL,
  `last_delivered_at` datetime DEFAULT NULL,
  `last_open_at` datetime DEFAULT NULL,
  `last_click_at` datetime DEFAULT NULL,
  `delivered_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `open_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `click_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `hard_bounce_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `soft_bounce_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `complaint_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_suppressed` tinyint(1) NOT NULL DEFAULT 0,
  `suppression_reason` varchar(191) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `earthen_send_batch_tb`
--

CREATE TABLE `earthen_send_batch_tb` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `batch_label` varchar(191) NOT NULL,
  `ghost_member_id` char(24) NOT NULL,
  `ghost_email` varchar(255) NOT NULL,
  `ghost_name` varchar(191) DEFAULT NULL,
  `ghost_newsletter_id` char(24) DEFAULT NULL,
  `ghost_newsletter_slug` varchar(191) DEFAULT NULL,
  `test_sent` tinyint(1) NOT NULL DEFAULT 0,
  `test_sent_date_time` datetime DEFAULT NULL,
  `processing` tinyint(1) DEFAULT NULL,
  `last_error` text DEFAULT NULL,
  `email_open_rate` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `find_duplicate_ecobrickers`
--

CREATE TABLE `find_duplicate_ecobrickers` (
  `buwana_id` mediumint(9) DEFAULT NULL,
  `ecobricker_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `maker_id` char(24) NOT NULL DEFAULT '1',
  `full_name` varchar(255) DEFAULT NULL,
  `email_addr` varchar(100) DEFAULT NULL,
  `c` bigint(21) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `load_brk_transaction`
--

CREATE TABLE `load_brk_transaction` (
  `tran_id` int(11) NOT NULL,
  `individual_amt` varchar(20) DEFAULT NULL,
  `tran_name` varchar(50) DEFAULT NULL,
  `status` varchar(10) NOT NULL,
  `send_ts` varchar(50) DEFAULT NULL,
  `sender_ecobricker` varchar(50) DEFAULT NULL,
  `block_tran_type` varchar(100) DEFAULT NULL,
  `block_amt` varchar(20) DEFAULT NULL,
  `sender` varchar(50) DEFAULT NULL,
  `sender_prebalance` varchar(30) DEFAULT NULL,
  `sender_new_balance_formula` varchar(20) DEFAULT NULL,
  `sender_simple_tran_name_formula` varchar(255) DEFAULT NULL,
  `receiver_or_receivers` varchar(255) DEFAULT NULL,
  `receivers_comma_variable` varchar(100) DEFAULT NULL,
  `receiver_tran_name_formula` varchar(255) DEFAULT NULL,
  `receiver_1` varchar(50) DEFAULT NULL,
  `receiver_2` varchar(50) DEFAULT NULL,
  `receiver_3` varchar(50) DEFAULT NULL,
  `receiver_1_new_balance` varchar(20) DEFAULT NULL,
  `receiver_1_pre_balance` varchar(20) DEFAULT NULL,
  `receiver_2_pre_balance` varchar(20) DEFAULT NULL,
  `receiver_3_pre_balance` varchar(20) DEFAULT NULL,
  `receiver_central_reserve` varchar(50) DEFAULT NULL,
  `sender_central_reserve` varchar(50) DEFAULT NULL,
  `central_reserve_prebalance` varchar(30) DEFAULT NULL,
  `central_reserve_credit` varchar(20) DEFAULT NULL,
  `ecobrick_serial_no` varchar(10) DEFAULT NULL,
  `earth_connection` varchar(50) DEFAULT NULL,
  `add_a_note_yn` varchar(5) DEFAULT NULL,
  `tran_sender_note` mediumtext DEFAULT NULL COMMENT 'paragraph',
  `tran_compensation_on_validation` varchar(20) DEFAULT NULL,
  `sender_is_receiver_chk` varchar(5) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `xxx_personalize` varchar(50) DEFAULT NULL,
  `recurring_tran_yn` varchar(5) DEFAULT NULL,
  `send_dt` varchar(50) DEFAULT NULL,
  `accomp_payment` varchar(20) DEFAULT NULL,
  `authenticator_version` varchar(10) DEFAULT NULL,
  `payment_record_yn` varchar(5) DEFAULT NULL,
  `payment_record_url` varchar(255) DEFAULT NULL,
  `block_or_not_tf` varchar(5) DEFAULT NULL,
  `receiver_2_final_balance` varchar(20) DEFAULT NULL,
  `receiver_3_final_balance` varchar(20) DEFAULT NULL,
  `revenue_type_yn` varchar(10) DEFAULT NULL,
  `expense_type` varchar(50) DEFAULT NULL,
  `shipping_address` varchar(255) DEFAULT NULL,
  `shipping_addr_street1` varchar(50) DEFAULT NULL,
  `shipping_addr_street2` varchar(50) DEFAULT NULL,
  `shipping_addr_city` varchar(50) DEFAULT NULL,
  `shipping_addr_state` varchar(50) DEFAULT NULL,
  `shipping_addr_zip` varchar(20) DEFAULT NULL,
  `shipping_addr_country` varchar(50) DEFAULT NULL,
  `shipping_addr_latitude` varchar(30) DEFAULT NULL,
  `shipping_addr_longitude` varchar(30) DEFAULT NULL,
  `confirmation_email` varchar(100) DEFAULT NULL,
  `save_addr_for_later_yn` varchar(5) DEFAULT NULL,
  `gea_accounting_category` varchar(20) DEFAULT NULL,
  `shipping_sentence` varchar(255) DEFAULT NULL,
  `mail_message_on_payment` varchar(255) DEFAULT NULL,
  `zoom_link_for_course_conf_email` varchar(255) DEFAULT NULL,
  `shipping_cost_brk` varchar(20) DEFAULT NULL,
  `product_cost_brk` varchar(20) DEFAULT NULL,
  `total_cost_incl_shipping` varchar(20) DEFAULT NULL,
  `shipping_with_currency` varchar(20) DEFAULT NULL,
  `order_status` varchar(20) DEFAULT NULL,
  `aes_offset_purch_separator` varchar(10) DEFAULT NULL,
  `aes_plastic_being_purchased` varchar(20) DEFAULT NULL,
  `aes_officially_purchased` varchar(20) DEFAULT NULL,
  `purchase_summary_msg` varchar(255) DEFAULT NULL,
  `terms_agreement_yn` varchar(5) DEFAULT NULL,
  `make_monthly_recurring_purchase_yn` varchar(5) DEFAULT NULL,
  `country_of_buyer` varchar(20) DEFAULT NULL,
  `countries_for_product` varchar(255) DEFAULT NULL,
  `country_error_yn` varchar(5) DEFAULT NULL,
  `phone_for_course_conf_yn` varchar(5) DEFAULT NULL,
  `payment_view` varchar(100) DEFAULT NULL,
  `x_payment_view2` varchar(100) DEFAULT NULL,
  `currency_for_shipping` varchar(20) DEFAULT NULL,
  `certificate_reqd_yn` varchar(5) DEFAULT NULL,
  `cert_issued_for` varchar(20) DEFAULT NULL,
  `aes_cert_yn` varchar(5) DEFAULT NULL,
  `aes_cert_url` varchar(255) DEFAULT NULL,
  `2020_cr_brk` varchar(20) DEFAULT NULL,
  `2019_total_brk` varchar(20) DEFAULT NULL,
  `2019_cr_brk` varchar(20) DEFAULT NULL,
  `update_me_yn` varchar(5) DEFAULT NULL,
  `deleteable_yn` varchar(5) DEFAULT NULL,
  `credit_other_ecobricker_yn` varchar(5) DEFAULT NULL,
  `catalyst_name` varchar(50) DEFAULT NULL,
  `credit_to_catalyst_enterprise_yn` varchar(5) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `load_cash_transaction`
--

CREATE TABLE `load_cash_transaction` (
  `transac_id` int(11) NOT NULL,
  `amount_idr` int(11) DEFAULT NULL,
  `sender_ecobricker` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `datetime_sent_ts` varchar(20) DEFAULT NULL,
  `native_ccy_amount` decimal(20,2) DEFAULT NULL,
  `currency` varchar(3) DEFAULT NULL,
  `final_total_with_ccy` varchar(50) DEFAULT NULL,
  `exchange_ratio` float DEFAULT NULL,
  `amount_usd` varchar(26) DEFAULT NULL,
  `total_product_cost_incl_shipping` decimal(20,2) DEFAULT NULL,
  `x_final_amt_old_usd` varchar(20) DEFAULT NULL,
  `sender_for_display` varchar(50) DEFAULT NULL,
  `product_transaction_name_formula` varchar(250) DEFAULT NULL,
  `transac_name` varchar(250) DEFAULT NULL,
  `transaction_name_pre_text` varchar(50) DEFAULT NULL,
  `transac_type` varchar(50) DEFAULT NULL,
  `product_cost` decimal(15,2) DEFAULT NULL,
  `sender_form_set` varchar(60) DEFAULT NULL,
  `receiver_for_display` varchar(50) DEFAULT NULL,
  `receiver_ecobricker` varchar(30) DEFAULT NULL,
  `receiving_gea_acct` varchar(20) DEFAULT NULL,
  `shipping_label` varchar(1) DEFAULT NULL,
  `shipping_date_dt` varchar(10) DEFAULT NULL,
  `quantity_requested` tinyint(4) DEFAULT NULL,
  `sub_shipping_cost_display_txt` varchar(20) DEFAULT NULL,
  `shipping_email_sentence` varchar(100) DEFAULT NULL,
  `shipping_for_subsequent_items` decimal(10,2) DEFAULT NULL,
  `shipping_cost` decimal(20,2) DEFAULT NULL,
  `shipping_with_ccy` varchar(26) DEFAULT NULL,
  `expense_vendors` varchar(50) DEFAULT NULL,
  `ecobrick_serial` int(11) DEFAULT NULL,
  `add_a_note_yn` varchar(3) DEFAULT NULL,
  `transfer_note` varchar(250) DEFAULT NULL,
  `product` varchar(250) DEFAULT NULL,
  `personalise` varchar(100) DEFAULT NULL,
  `recurring_transaction_period` varchar(20) DEFAULT NULL,
  `you_received_msg` varchar(255) DEFAULT NULL,
  `Sender_simple_tran_name_formula` varchar(255) DEFAULT NULL,
  `earth_connection` varchar(5) DEFAULT NULL,
  `accounting_label` varchar(1) DEFAULT NULL,
  `simple_transaction_date` varchar(10) DEFAULT NULL,
  `payment_note` varchar(100) DEFAULT NULL,
  `expense_type` varchar(50) DEFAULT NULL,
  `revenue_type` varchar(30) DEFAULT NULL,
  `paymt_record_img_url` varchar(255) DEFAULT NULL,
  `paymt_record_url` varchar(255) DEFAULT NULL,
  `update_stored_shipping_address` varchar(3) DEFAULT NULL,
  `shipping_address` varchar(255) DEFAULT NULL,
  `shipping_addr_street1` varchar(150) DEFAULT NULL,
  `shipping_addr_street2` varchar(100) DEFAULT NULL,
  `shipping_addr_city` varchar(30) DEFAULT NULL,
  `shipping_addr_state` varchar(20) DEFAULT NULL,
  `shipping_addr_zip` varchar(20) DEFAULT NULL,
  `shipping_addr_country` varchar(20) DEFAULT NULL,
  `shipping_addr_lattitude` float DEFAULT NULL,
  `shipping_addr_longitude` float DEFAULT NULL,
  `confirmation_email_addr` varchar(30) DEFAULT NULL,
  `payment_processor` varchar(30) DEFAULT NULL,
  `mail_message_on_payment` mediumtext DEFAULT NULL,
  `zoom_link_for_emsg` varchar(255) DEFAULT NULL,
  `order_status` varchar(20) DEFAULT NULL,
  `catalyst_name` varchar(50) DEFAULT NULL,
  `transaction_logger` varchar(30) DEFAULT NULL,
  `brikcoin_buying_label` varchar(1) DEFAULT NULL,
  `current+brk_to_aes_valuation` float DEFAULT NULL,
  `2020_genesis_sale_label` varchar(1) DEFAULT NULL,
  `cash_to_brk_transaction` varchar(100) DEFAULT NULL,
  `terms_agreed` varchar(3) DEFAULT NULL,
  `final_aes_plastic_purchased_or_requested` varchar(20) DEFAULT NULL,
  `final_brk_purchased` varchar(10) DEFAULT NULL,
  `final_usd_paid_for_brk` varchar(26) DEFAULT NULL,
  `cost_of_aes_plastic_req_usd` varchar(20) DEFAULT NULL,
  `cost_of_aes_plastic_req_gbp` varchar(20) DEFAULT NULL,
  `cost_of_aes_converted` float DEFAULT NULL,
  `total_cost_aes_req_w_ccy` varchar(20) DEFAULT NULL,
  `cost_of_aes_brk_display` varchar(50) DEFAULT NULL,
  `offset_credit_for_cash_pymt` varchar(50) DEFAULT NULL,
  `aes_val_brk_from_planet` float DEFAULT NULL,
  `allocate_purch_to_ent_catalyst_acc_bool` varchar(3) DEFAULT NULL,
  `make_monthly_purchase_bool` varchar(3) DEFAULT NULL,
  `confirmation_tel_no` varchar(15) DEFAULT NULL,
  `course_mgr_email` varchar(30) DEFAULT NULL,
  `purchase_with` varchar(40) DEFAULT NULL,
  `countries_accepted` varchar(100) DEFAULT NULL,
  `deletable_bool` varchar(3) DEFAULT NULL,
  `request_purchase_msg` varchar(150) DEFAULT NULL,
  `what_want_to_do_bool` varchar(50) DEFAULT NULL,
  `paymt_view_to_show` varchar(30) DEFAULT NULL,
  `user_country` varchar(20) DEFAULT NULL,
  `country_error_bool` varchar(3) DEFAULT NULL,
  `what_ccy_desired` varchar(3) DEFAULT NULL,
  `pdf_cert_req_bool` varchar(3) DEFAULT NULL,
  `name_for_certificate` varchar(50) DEFAULT NULL,
  `aes_offset_plastic_certificate` mediumtext DEFAULT NULL,
  `aes_cert_url` varchar(255) DEFAULT NULL,
  `xx_record_id` varchar(20) DEFAULT NULL,
  `total_product_cost_+ccy_display` varchar(100) DEFAULT NULL,
  `carbon_offset_value_display` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments_tb`
--

CREATE TABLE `payments_tb` (
  `payment_id` bigint(20) UNSIGNED NOT NULL,
  `buwana_id` bigint(20) UNSIGNED NOT NULL,
  `campaign_type` varchar(24) DEFAULT NULL,
  `campaign_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pledge_id` bigint(20) UNSIGNED DEFAULT NULL,
  `registration_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `app_code` varchar(32) NOT NULL DEFAULT 'gobrik',
  `payment_purpose` varchar(32) NOT NULL DEFAULT 'training_registration',
  `currency` char(3) NOT NULL,
  `amount_total` int(10) UNSIGNED NOT NULL,
  `amount_tax` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `amount_fee` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `amount_net` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'created',
  `gateway` varchar(16) NOT NULL,
  `gateway_method` varchar(32) DEFAULT NULL,
  `gateway_ref` varchar(128) DEFAULT NULL,
  `gateway_status` varchar(64) DEFAULT NULL,
  `client_reference` varchar(64) DEFAULT NULL,
  `idempotency_key` varchar(64) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `payment_requested_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `paid_at` datetime DEFAULT NULL,
  `gateway_payload_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gateway_payload_json`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_events_tb`
--

CREATE TABLE `payment_events_tb` (
  `payment_event_id` bigint(20) UNSIGNED NOT NULL,
  `payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `gateway` varchar(16) NOT NULL,
  `gateway_event_id` varchar(128) NOT NULL,
  `gateway_ref` varchar(128) DEFAULT NULL,
  `event_type` varchar(64) NOT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `payload_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload_json`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `processed_at` datetime DEFAULT NULL,
  `processing_status` varchar(24) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_items_tb`
--

CREATE TABLE `payment_items_tb` (
  `payment_item_id` bigint(20) UNSIGNED NOT NULL,
  `payment_id` bigint(20) UNSIGNED NOT NULL,
  `item_type` varchar(32) NOT NULL,
  `item_ref_id` bigint(20) UNSIGNED NOT NULL,
  `qty` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `currency` char(3) NOT NULL,
  `unit_amount` int(10) UNSIGNED NOT NULL,
  `line_total` int(10) UNSIGNED NOT NULL,
  `meta_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_json`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Photo_swap`
--

CREATE TABLE `Photo_swap` (
  `serial_no` varchar(10) DEFAULT NULL,
  `ecobrick_thumb_photo_url` varchar(255) DEFAULT NULL,
  `ecobrick_full_photo_url` varchar(255) DEFAULT NULL,
  `selfie_thumb_url` varchar(255) DEFAULT NULL,
  `selfie_photo_url` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products_tb`
--

CREATE TABLE `products_tb` (
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `product_code` varchar(64) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_subtitle` varchar(255) DEFAULT NULL,
  `product_description` longtext DEFAULT NULL,
  `product_type` varchar(32) NOT NULL DEFAULT 'physical',
  `sku` varchar(64) DEFAULT NULL,
  `base_currency` char(3) NOT NULL DEFAULT 'IDR',
  `default_price_idr` int(10) UNSIGNED DEFAULT NULL,
  `display_price` varchar(255) DEFAULT NULL,
  `stock_mode` varchar(24) NOT NULL DEFAULT 'campaign',
  `product_status` varchar(24) NOT NULL DEFAULT 'draft',
  `weight_grams` int(10) UNSIGNED DEFAULT NULL,
  `length_mm` int(10) UNSIGNED DEFAULT NULL,
  `width_mm` int(10) UNSIGNED DEFAULT NULL,
  `height_mm` int(10) UNSIGNED DEFAULT NULL,
  `featured_image_main` varchar(255) DEFAULT NULL,
  `featured_image_tmb` varchar(255) DEFAULT NULL,
  `product_url` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `meta_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_json`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_campaigns_tb`
--

CREATE TABLE `product_campaigns_tb` (
  `campaign_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `campaign_title` varchar(255) NOT NULL,
  `campaign_subtitle` varchar(255) DEFAULT NULL,
  `campaign_description` longtext DEFAULT NULL,
  `campaign_mode` varchar(24) NOT NULL DEFAULT 'pledge_threshold',
  `base_currency` char(3) NOT NULL DEFAULT 'IDR',
  `default_price_idr` int(10) UNSIGNED DEFAULT NULL,
  `min_backers_required` int(10) UNSIGNED DEFAULT NULL,
  `funding_goal_idr` int(10) UNSIGNED DEFAULT NULL,
  `min_pledge_idr` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `max_pledge_idr` int(10) UNSIGNED DEFAULT NULL,
  `allow_overpledge` tinyint(1) NOT NULL DEFAULT 1,
  `pledge_deadline` datetime DEFAULT NULL,
  `payment_deadline` datetime DEFAULT NULL,
  `threshold_status` varchar(24) NOT NULL DEFAULT 'open',
  `threshold_reached_at` datetime DEFAULT NULL,
  `campaign_confirmed_at` datetime DEFAULT NULL,
  `campaign_failed_at` datetime DEFAULT NULL,
  `auto_confirm_threshold` tinyint(1) NOT NULL DEFAULT 1,
  `delivery_mode` varchar(24) NOT NULL DEFAULT 'ship',
  `estimated_delivery_date` date DEFAULT NULL,
  `ready_to_show` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `meta_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_json`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_orders_tb`
--

CREATE TABLE `product_orders_tb` (
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `campaign_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `buwana_id` bigint(20) UNSIGNED NOT NULL,
  `pledge_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(24) NOT NULL DEFAULT 'reserved',
  `qty` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `unit_price_idr` int(10) UNSIGNED DEFAULT NULL,
  `total_price_idr` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `invited_to_pay_at` datetime DEFAULT NULL,
  `payment_due_at` datetime DEFAULT NULL,
  `payment_completed_at` datetime DEFAULT NULL,
  `fulfilled_at` datetime DEFAULT NULL,
  `shipped_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(64) DEFAULT NULL,
  `shipping_name` varchar(255) DEFAULT NULL,
  `shipping_address1` varchar(255) DEFAULT NULL,
  `shipping_address2` varchar(255) DEFAULT NULL,
  `shipping_city` varchar(255) DEFAULT NULL,
  `shipping_region` varchar(255) DEFAULT NULL,
  `shipping_postal_code` varchar(32) DEFAULT NULL,
  `shipping_country` char(2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `meta_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_json`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_pledges_tb`
--

CREATE TABLE `product_pledges_tb` (
  `pledge_id` bigint(20) UNSIGNED NOT NULL,
  `campaign_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `buwana_id` bigint(20) UNSIGNED NOT NULL,
  `pledge_currency` char(3) NOT NULL DEFAULT 'IDR',
  `pledged_amount_idr` int(10) UNSIGNED NOT NULL,
  `display_currency` varchar(10) NOT NULL DEFAULT 'IDR',
  `display_amount` decimal(12,2) DEFAULT NULL,
  `suggested_amount_idr` int(10) UNSIGNED DEFAULT NULL,
  `qty` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `pledge_status` varchar(24) NOT NULL DEFAULT 'active',
  `invited_to_pay_at` datetime DEFAULT NULL,
  `payment_due_at` datetime DEFAULT NULL,
  `payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `confirmed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `cancelled_at` datetime DEFAULT NULL,
  `expired_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `shipping_name` varchar(255) DEFAULT NULL,
  `shipping_email` varchar(255) DEFAULT NULL,
  `shipping_phone` varchar(64) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `meta_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_json`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_brk_transaction`
--

CREATE TABLE `tb_brk_transaction` (
  `chain_ledger_id` bigint(20) UNSIGNED NOT NULL,
  `tran_id` int(11) NOT NULL,
  `tran_name` varchar(30) DEFAULT 'via transfer',
  `individual_amt` decimal(20,2) DEFAULT NULL,
  `status` varchar(16) NOT NULL,
  `send_ts` datetime NOT NULL,
  `sender_ecobricker` varchar(50) DEFAULT NULL,
  `block_tran_type` varchar(40) DEFAULT NULL,
  `block_amt` float NOT NULL DEFAULT 0,
  `sender` varchar(50) DEFAULT NULL,
  `receiver_or_receivers` varchar(255) DEFAULT NULL,
  `receiver_1` varchar(50) DEFAULT ' ',
  `receiver_2` varchar(50) DEFAULT ' ',
  `receiver_3` varchar(50) DEFAULT ' ',
  `receiver_central_reserve` varchar(50) DEFAULT NULL,
  `sender_central_reserve` varchar(50) DEFAULT '',
  `ecobrick_serial_no` mediumint(9) DEFAULT NULL,
  `tran_sender_note` mediumtext DEFAULT '' COMMENT 'paragraph',
  `product` text DEFAULT '',
  `send_dt` date DEFAULT NULL,
  `accomp_payment` decimal(20,2) NOT NULL DEFAULT 0.00,
  `authenticator_version` varchar(10) DEFAULT '',
  `expense_type` varchar(50) DEFAULT ' ',
  `gea_accounting_category` varchar(20) DEFAULT '',
  `shipping_cost_brk` decimal(10,2) NOT NULL DEFAULT 0.00,
  `product_cost_brk` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_cost_incl_shipping` decimal(10,2) DEFAULT NULL,
  `shipping_with_currency` decimal(20,2) NOT NULL DEFAULT 0.00,
  `aes_officially_purchased` decimal(10,2) DEFAULT NULL,
  `country_of_buyer` varchar(20) DEFAULT '',
  `currency_for_shipping` varchar(20) DEFAULT '',
  `credit_other_ecobricker_yn` varchar(5) DEFAULT NULL,
  `catalyst_name` varchar(50) DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_cash_transaction`
--

CREATE TABLE `tb_cash_transaction` (
  `knack_record_id` char(24) DEFAULT '000000000000000000000000',
  `cash_tran_id` int(11) NOT NULL,
  `payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sender_ecobricker` varchar(50) DEFAULT NULL,
  `datetime_sent_ts` datetime DEFAULT NULL,
  `type_of_transaction` varchar(40) DEFAULT NULL,
  `tran_name_desc` varchar(255) DEFAULT NULL,
  `native_ccy_amt` decimal(20,2) DEFAULT NULL,
  `currency_code` char(3) DEFAULT NULL,
  `native_ccy_amt_display` varchar(50) DEFAULT NULL,
  `exchange_ratio` float DEFAULT NULL,
  `usd_amount` decimal(20,2) DEFAULT NULL,
  `idr_amount` int(20) DEFAULT NULL,
  `total_product_cost_incl_shipping` decimal(20,2) DEFAULT NULL,
  `product` varchar(250) DEFAULT NULL,
  `product_cost` decimal(10,2) DEFAULT NULL,
  `transaction_date_dt` date DEFAULT NULL,
  `shipping_cost` decimal(10,2) DEFAULT NULL,
  `shipping_cost_+ccy_display` varchar(50) DEFAULT NULL,
  `total_product_cost_+ccy_display` varchar(100) DEFAULT NULL,
  `receiving_gea_acct` varchar(20) DEFAULT NULL,
  `sender_for_display` varchar(50) DEFAULT NULL,
  `receiver_for_display` varchar(50) DEFAULT NULL,
  `receiver_gea_account` varchar(20) DEFAULT NULL,
  `expense_vendor` varchar(50) DEFAULT NULL,
  `purchase_method` varchar(30) DEFAULT NULL,
  `recurring_trans_period` varchar(15) DEFAULT NULL,
  `expense_accounting_type` varchar(50) DEFAULT NULL,
  `revenue_accounting_type` varchar(50) DEFAULT NULL,
  `tran_processor` varchar(50) DEFAULT NULL,
  `gateway_ref` varchar(128) DEFAULT NULL,
  `connected_brk_tran_name` varchar(100) DEFAULT NULL,
  `aes_to_usd_rate` float DEFAULT NULL,
  `aes_plastic_offset_purchase_kg` decimal(10,2) DEFAULT NULL,
  `usd_payment_for_aes` decimal(20,2) DEFAULT NULL,
  `gbp_payment_for_aes` decimal(20,2) DEFAULT NULL,
  `native_conversion_of_aes` decimal(20,2) DEFAULT NULL,
  `brk_cost_of_aes_display` varchar(100) DEFAULT NULL,
  `credited_catalyst` varchar(255) DEFAULT NULL,
  `brikcoins_purchased_display` varchar(20) DEFAULT NULL,
  `usd_paid_for_brk_+ccy_display` varchar(30) DEFAULT NULL,
  `connected_brk_trans` varchar(100) DEFAULT NULL,
  `paymt_record_url` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_ecobrickers`
--

CREATE TABLE `tb_ecobrickers` (
  `open_id` varchar(255) DEFAULT NULL,
  `ecobricker_id` int(11) NOT NULL,
  `maker_idxx` int(11) NOT NULL DEFAULT 1,
  `buwana_id` mediumint(9) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `first_name` varchar(25) DEFAULT NULL,
  `last_name` varchar(25) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `user_roles` varchar(255) DEFAULT 'ecobricker',
  `gea_status` varchar(20) DEFAULT 'gobriker',
  `community` varchar(100) DEFAULT NULL,
  `email_addr` varchar(100) DEFAULT NULL,
  `email_confirm_dt` datetime DEFAULT NULL,
  `earthen_registered` tinyint(1) DEFAULT 0,
  `date_registered` datetime DEFAULT NULL,
  `terms_of_service` tinyint(1) DEFAULT 0,
  `phone_no` varchar(20) DEFAULT NULL,
  `ecobricks_made` smallint(6) DEFAULT NULL,
  `brk_balance` decimal(10,2) DEFAULT NULL,
  `aes_balance` varchar(20) DEFAULT NULL,
  `aes_purchased` varchar(20) DEFAULT NULL,
  `country_txt` varchar(100) DEFAULT NULL,
  `region_txt` varchar(100) DEFAULT NULL,
  `city_txt` varchar(100) DEFAULT NULL,
  `location_full_txt` varchar(100) DEFAULT NULL,
  `household_txt` varchar(100) DEFAULT NULL,
  `gender` varchar(100) DEFAULT NULL,
  `personal_catalyst` varchar(100) DEFAULT NULL,
  `trainer_availability` varchar(100) DEFAULT NULL,
  `pronoun` varchar(10) DEFAULT NULL,
  `ecobrick_weight_avg` decimal(10,2) DEFAULT NULL,
  `ecobrick_density_avg` decimal(10,2) DEFAULT NULL,
  `household_generation` decimal(10,2) DEFAULT NULL,
  `country_per_capita_consumption` decimal(10,2) DEFAULT NULL,
  `my_consumption_estimate` decimal(10,2) DEFAULT NULL,
  `household_members` int(11) DEFAULT NULL,
  `household` tinyint(1) DEFAULT NULL,
  `buwana_activated` tinyint(1) DEFAULT NULL,
  `buwana_activation_dt` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `login_count` int(11) NOT NULL DEFAULT 0,
  `language_pref` varchar(10) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gobrik_migrated` tinyint(1) DEFAULT NULL,
  `account_notes` varchar(255) DEFAULT NULL,
  `profile_pic` varchar(255) NOT NULL DEFAULT 'null',
  `gobrik_migrated_dt` datetime DEFAULT NULL,
  `activation_code` varchar(25) DEFAULT NULL,
  `location_full` varchar(254) DEFAULT NULL,
  `location_watershed` varchar(254) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `location_lat` decimal(10,8) DEFAULT NULL,
  `location_long` decimal(11,8) DEFAULT NULL,
  `community_id` int(11) DEFAULT NULL,
  `earthling_emoji` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `language_id` varchar(11) DEFAULT 'en',
  `legacy_knack_id` char(24) DEFAULT NULL,
  `emailing_status` varchar(50) NOT NULL DEFAULT 'unsent',
  `user_capabilities` varchar(255) DEFAULT NULL,
  `spam_trapped` tinyint(1) DEFAULT NULL,
  `continent_code` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `tb_ecobrickers`
--
DELIMITER $$
CREATE TRIGGER `update_maker_on_delete` BEFORE DELETE ON `tb_ecobrickers` FOR EACH ROW BEGIN
    -- Update the maker_id in tb_ecobricks to 2
    UPDATE tb_ecobricks
    SET maker_id = 2
    WHERE maker_id = OLD.ecobricker_id;
    -- Update the ecobricker_maker field with the full_name of the maker being deleted
    UPDATE tb_ecobricks
    SET ecobricker_maker = OLD.full_name
    WHERE maker_id = 2;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tb_ecobricks`
--

CREATE TABLE `tb_ecobricks` (
  `date_published_ts` datetime DEFAULT NULL,
  `knack_record_id` char(24) DEFAULT '000000000000000000000000',
  `ecobricker_id` int(11) DEFAULT NULL,
  `maker_id` int(11) UNSIGNED DEFAULT NULL,
  `ecobrick_unique_id` int(11) NOT NULL,
  `serial_no` varchar(10) DEFAULT NULL,
  `owner` varchar(50) DEFAULT '',
  `ecobricker_maker` varchar(50) DEFAULT '',
  `status` varchar(255) DEFAULT NULL,
  `ecobrick_thumb_photo_url` varchar(255) DEFAULT NULL,
  `ecobrick_full_photo_url` varchar(255) DEFAULT NULL,
  `selfie_thumb_url` varchar(255) DEFAULT NULL,
  `selfie_photo_url` varchar(255) DEFAULT NULL,
  `photo_version` int(11) DEFAULT 1,
  `volume_ml` int(11) DEFAULT NULL,
  `universal_volume_ml` float DEFAULT NULL,
  `weight_g` int(11) DEFAULT NULL,
  `density` decimal(10,2) DEFAULT NULL,
  `date_logged_ts` datetime DEFAULT NULL,
  `CO2_kg` decimal(10,2) DEFAULT NULL,
  `sequestration_type` varchar(100) DEFAULT 'missing type',
  `last_validation_ts` datetime DEFAULT NULL,
  `validator_1` varchar(50) DEFAULT 'Unknown',
  `validator_2` varchar(50) DEFAULT 'Unknown',
  `validator_3` varchar(50) DEFAULT 'Unknown',
  `validation_score_avg` decimal(10,2) DEFAULT 0.00,
  `final_validation_score` decimal(10,2) DEFAULT NULL,
  `vision` varchar(1024) DEFAULT ' ',
  `last_ownership_change` date DEFAULT NULL,
  `non_registered_maker_name` varchar(30) DEFAULT '',
  `actual_maker_name` varchar(50) DEFAULT '',
  `weight_authenticated_kg` float DEFAULT NULL,
  `location_country` varchar(35) DEFAULT NULL,
  `location_watershed` varchar(100) DEFAULT NULL,
  `location_region` varchar(50) DEFAULT '',
  `location_city` varchar(100) DEFAULT NULL,
  `location_municipality` varchar(60) DEFAULT NULL,
  `location_full` varchar(254) DEFAULT '',
  `community_name` varchar(50) DEFAULT '',
  `brand_name` varchar(30) DEFAULT '',
  `bottom_colour` varchar(20) DEFAULT 'Mixed',
  `plastic_from` varchar(30) DEFAULT 'home',
  `ecobrick_brk_display_value` varchar(20) DEFAULT 'Unknown',
  `ecobrick_dec_brk_val` decimal(10,2) DEFAULT 0.00,
  `ecobrick_brk_amt` float DEFAULT 0,
  `photo_choice` varchar(100) DEFAULT NULL,
  `location_lat` decimal(10,8) DEFAULT NULL,
  `location_long` decimal(11,8) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `training_id` int(11) DEFAULT NULL,
  `catalyst` varchar(50) DEFAULT NULL,
  `brik_notes` varchar(255) DEFAULT NULL,
  `community_id` int(11) DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `feature` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_projects`
--

CREATE TABLE `tb_projects` (
  `project_id` int(11) UNSIGNED NOT NULL,
  `project_name` varchar(50) NOT NULL,
  `description_short` varchar(255) NOT NULL,
  `description_long` longtext NOT NULL,
  `start_dt` date NOT NULL,
  `end_dt` date NOT NULL,
  `logged_ts` datetime NOT NULL,
  `project_end` date NOT NULL,
  `briks_required` smallint(11) NOT NULL,
  `project_phase` varchar(25) NOT NULL DEFAULT 'Completed',
  `project_perc_complete` tinyint(4) NOT NULL DEFAULT 100,
  `community` varchar(50) NOT NULL,
  `project_type` varchar(30) NOT NULL,
  `construction_type` varchar(30) NOT NULL,
  `project_sort` varchar(30) NOT NULL,
  `briks_used` smallint(6) NOT NULL,
  `est_avg_brik_weight` smallint(255) NOT NULL,
  `featured_img` varchar(255) DEFAULT NULL,
  `tmb_featured_img` varchar(255) DEFAULT NULL,
  `location_full` text NOT NULL,
  `project_url` varchar(255) DEFAULT NULL,
  `project_admins` varchar(256) NOT NULL,
  `photo1_main` varchar(255) DEFAULT NULL,
  `photo1_tmb` varchar(255) DEFAULT NULL,
  `photo2_main` varchar(255) DEFAULT NULL,
  `photo2_tmb` varchar(255) DEFAULT NULL,
  `photo3_main` varchar(255) DEFAULT NULL,
  `photo3_tmb` varchar(255) DEFAULT NULL,
  `photo4_main` varchar(255) DEFAULT NULL,
  `photo4_tmb` varchar(255) DEFAULT NULL,
  `photo5_main` varchar(255) DEFAULT NULL,
  `photo5_tmb` varchar(255) DEFAULT NULL,
  `photo6_main` varchar(255) DEFAULT NULL,
  `photo6_tmb` varchar(255) NOT NULL,
  `est_total_weight` smallint(5) UNSIGNED DEFAULT NULL,
  `ready_to_show` tinyint(1) NOT NULL DEFAULT 0,
  `location_lat` decimal(10,8) DEFAULT NULL,
  `location_long` decimal(11,8) DEFAULT NULL,
  `connected_ecobricks` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_static_calc_data`
--

CREATE TABLE `tb_static_calc_data` (
  `row_id` tinyint(4) DEFAULT 1,
  `table_description` varchar(100) DEFAULT NULL,
  `plastic_in_g_to_co2_in_kg_multiplier` float NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_trainings`
--

CREATE TABLE `tb_trainings` (
  `training_id` int(11) UNSIGNED NOT NULL,
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
  `community_id` int(11) UNSIGNED DEFAULT NULL,
  `featured_description` longtext DEFAULT NULL,
  `display_cost` text NOT NULL DEFAULT 'Free / Donation',
  `Cost` int(11) DEFAULT NULL,
  `Currency` varchar(25) DEFAULT NULL,
  `default_price_idr` int(10) UNSIGNED DEFAULT NULL,
  `base_currency` char(3) DEFAULT 'IDR',
  `payment_mode` varchar(24) NOT NULL DEFAULT 'free',
  `min_participants_required` smallint(5) UNSIGNED DEFAULT NULL,
  `funding_goal_idr` int(10) UNSIGNED DEFAULT NULL,
  `pledge_deadline` datetime DEFAULT NULL,
  `payment_deadline` datetime DEFAULT NULL,
  `threshold_status` varchar(24) NOT NULL DEFAULT 'open',
  `threshold_reached_at` datetime DEFAULT NULL,
  `course_confirmed_at` datetime DEFAULT NULL,
  `course_failed_at` datetime DEFAULT NULL,
  `auto_confirm_threshold` tinyint(1) NOT NULL DEFAULT 1,
  `allow_overpledge` tinyint(1) NOT NULL DEFAULT 1,
  `min_pledge_idr` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `max_pledge_idr` int(10) UNSIGNED DEFAULT NULL,
  `show_report` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Showing the final training report on ecobricks.org',
  `show_signup_count` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Show the signup count on the public registration page',
  `earthcal_event_url` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_training_trainees`
--

CREATE TABLE `tb_training_trainees` (
  `id` int(11) UNSIGNED NOT NULL,
  `training_id` int(11) UNSIGNED NOT NULL,
  `ecobricker_id` int(11) NOT NULL,
  `rsvp_status` enum('pending','confirmed','attended') DEFAULT 'pending',
  `date_registered` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_training_trainers`
--

CREATE TABLE `tb_training_trainers` (
  `id` int(11) UNSIGNED NOT NULL,
  `training_id` int(11) UNSIGNED NOT NULL,
  `ecobricker_id` int(11) NOT NULL,
  `trainer_role` enum('lead','assistant') DEFAULT 'lead',
  `date_assigned` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_validations`
--

CREATE TABLE `tb_validations` (
  `Ecobrick` varchar(8) DEFAULT NULL,
  `Date/Time Made` varchar(18) DEFAULT NULL,
  `Authenticator version used` varchar(26) DEFAULT NULL,
  `Recorded Maker of Ecobrick` varchar(38) DEFAULT NULL,
  `Validation Scoring Algorithm` varchar(28) DEFAULT NULL,
  `Recorded Weight` varchar(15) DEFAULT NULL,
  `Recorded Color` varchar(24) DEFAULT NULL,
  `Recorded Serial` varchar(15) DEFAULT NULL,
  `Can one main ecobrick be clearly seen in the photo?` varchar(51) DEFAULT NULL,
  `Does it appear the ecobrick is made with used clean dry plastic?` varchar(66) DEFAULT NULL,
  `Confirmation of Clean Plastic` varchar(29) DEFAULT NULL,
  `Is the serial no clearly & physically inscribed on the ecobrick?` varchar(70) DEFAULT NULL,
  `Confirmation of serial attachment` varchar(33) DEFAULT NULL,
  `How is the serial inscribed on the bottle?` varchar(43) DEFAULT NULL,
  `Confirmation on Inscription Method` varchar(34) DEFAULT NULL,
  `What deliberate color was given to the bottom of the ecobrick?` varchar(67) DEFAULT NULL,
  `Confirmation of Bottom Color` varchar(28) DEFAULT NULL,
  `What is the serial number inscribed on this ecobrick?` varchar(53) DEFAULT NULL,
  `Confirmation on Serial matching` varchar(31) DEFAULT NULL,
  `What is the recorded weight of the ecobrick?` varchar(44) DEFAULT NULL,
  `Confirmation of Weight` varchar(22) DEFAULT NULL,
  `Is the ecobrick sealed with a screw top cap?` varchar(44) DEFAULT NULL,
  `Confirmation on Cap Type` varchar(24) DEFAULT NULL,
  `Has the label been removed from the ecobrick?` varchar(45) DEFAULT NULL,
  `Confirmation on label removed` varchar(29) DEFAULT NULL,
  `=== FINAL PAGE ===` varchar(18) DEFAULT NULL,
  `Is this a reasonable volume for this ecobrick's bottle?` varchar(55) DEFAULT NULL,
  `Conf of Reasonable Volume` varchar(25) DEFAULT NULL,
  `How densely packed is the ecobrick?` varchar(35) DEFAULT NULL,
  `Confirmation on densely packed` varchar(30) DEFAULT NULL,
  `Is the calculated density within 0.33g/ml and 0.8g/ml?` varchar(67) DEFAULT NULL,
  `Confirmation on Density in Range` varchar(32) DEFAULT NULL,
  `Validations Counter Field` varchar(25) DEFAULT NULL,
  `Validator no 1` varchar(18) DEFAULT NULL,
  `Validator No2` varchar(21) DEFAULT NULL,
  `Validator no3` varchar(17) DEFAULT NULL,
  `Transaction` varchar(29) DEFAULT NULL,
  `Authenticated yet?` varchar(18) DEFAULT NULL,
  `All of GoBrik` varchar(13) DEFAULT NULL,
  `25% Valuation of preset ecobrick value` varchar(38) DEFAULT NULL,
  `Central Reserve Balance + Full Brik value` varchar(41) DEFAULT NULL,
  `Valuation for Central Reserve (Hardcoded at 25%)` varchar(48) DEFAULT NULL,
  `! New Central Reserve Balance with valuation added` varchar(50) DEFAULT NULL,
  `Validation Status` varchar(17) DEFAULT NULL,
  `Real Count Formula` varchar(18) DEFAULT NULL,
  `Validation Name` varchar(38) DEFAULT NULL,
  `Preset Ecobrick Value` varchar(21) DEFAULT NULL,
  `Preset Central Balance` varchar(22) DEFAULT NULL,
  `!!! Language of Validator 1` varchar(27) DEFAULT NULL,
  `Star Rating` varchar(11) DEFAULT NULL,
  `Preset 25% Valuation of an Ecobrick` varchar(35) DEFAULT NULL,
  `Validator 1 Preset Language` varchar(27) DEFAULT NULL,
  `Validator 3 Preset Language` varchar(27) DEFAULT NULL,
  `Validator 2 Preset Language` varchar(27) DEFAULT NULL,
  `Validator 1 Send Validation Notification` varchar(40) DEFAULT NULL,
  `Validator 2 Send Validation Notification` varchar(40) DEFAULT NULL,
  `Validator 3 Send Validation Notification` varchar(40) DEFAULT NULL,
  `Deleteable` varchar(10) DEFAULT NULL,
  `??? No Show Validation Count from Ecobrick` varchar(42) DEFAULT NULL,
  `New Plastic for Sale Balance` varchar(28) DEFAULT NULL,
  `Preset Central Plastic for Sale Balance` varchar(39) DEFAULT NULL,
  `What kind of Ecobrick is this?` varchar(30) DEFAULT NULL,
  `No Show Count  Setfrom Ecobrick` varchar(31) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_pledges_tb`
--

CREATE TABLE `training_pledges_tb` (
  `pledge_id` bigint(20) UNSIGNED NOT NULL,
  `training_id` int(11) UNSIGNED NOT NULL,
  `buwana_id` bigint(20) UNSIGNED NOT NULL,
  `pledge_currency` char(3) NOT NULL DEFAULT 'IDR',
  `pledged_amount_idr` int(10) UNSIGNED NOT NULL,
  `display_currency` varchar(10) NOT NULL DEFAULT 'IDR',
  `display_amount` decimal(12,2) DEFAULT NULL,
  `suggested_amount_idr` int(10) UNSIGNED DEFAULT NULL,
  `pledge_status` varchar(24) NOT NULL DEFAULT 'active',
  `invited_to_pay_at` datetime DEFAULT NULL,
  `payment_due_at` datetime DEFAULT NULL,
  `payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `confirmed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `cancelled_at` datetime DEFAULT NULL,
  `expired_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `converted_payment_id` int(11) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `meta_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_json`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_registrations_tb`
--

CREATE TABLE `training_registrations_tb` (
  `registration_id` bigint(20) UNSIGNED NOT NULL,
  `training_id` int(11) UNSIGNED NOT NULL,
  `buwana_id` bigint(20) UNSIGNED NOT NULL,
  `pledge_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'reserved',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `confirmed_at` datetime DEFAULT NULL,
  `invited_to_pay_at` datetime DEFAULT NULL,
  `payment_due_at` datetime DEFAULT NULL,
  `payment_completed_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `attendee_name` varchar(255) DEFAULT NULL,
  `attendee_email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `validations_tb`
--

CREATE TABLE `validations_tb` (
  `validation_id` bigint(20) UNSIGNED NOT NULL,
  `authenticator_version` varchar(10) DEFAULT '2.1' COMMENT 'Version of the validation system used',
  `created` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Datetime when validation record was created',
  `recorded_serial` varchar(10) NOT NULL COMMENT 'Serial number of the ecobrick being validated',
  `ecobricker_id` int(11) NOT NULL COMMENT 'Ecobricker ID of the validator (links to tb_ecobrickers)',
  `recorded_weight` decimal(10,2) DEFAULT NULL COMMENT 'Weight (g) recorded from ecobrick record',
  `preset_brk_value` decimal(10,2) DEFAULT NULL COMMENT 'BRK value calculated from weight',
  `star_rating` tinyint(1) DEFAULT NULL COMMENT 'Validator 1–5 star rating of ecobrick',
  `validations_count` tinyint(1) DEFAULT 0 COMMENT 'How many validations have been made for this ecobrick',
  `validation_status` varchar(50) DEFAULT 'pending' COMMENT 'Status of validation review (pending, complete, approved, etc.)',
  `validator_comments` text DEFAULT NULL COMMENT 'Comments by the validator',
  `validation_note` varchar(255) DEFAULT NULL COMMENT 'System note (e.g. early deployment, single validator)',
  `admin_forced_status` varchar(50) DEFAULT NULL COMMENT 'Admin override of validation status',
  `revision_date` datetime DEFAULT NULL COMMENT 'Datetime if validation is revised',
  `brk_trans_no` int(11) DEFAULT NULL COMMENT 'Reference to tb_brk_transaction.tran_id',
  `last_updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Auto-update timestamp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Records validator reviews of ecobricks';

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_brk_by_year`
-- (See below for the actual view)
--
CREATE TABLE `vw_brk_by_year` (
`year` int(5)
,`from_date` date
,`to_date` date
,`total_brk` varchar(417)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_brk_pool`
-- (See below for the actual view)
--
CREATE TABLE `vw_brk_pool` (
`total_net_brk` double
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_brk_tranid_asc`
-- (See below for the actual view)
--
CREATE TABLE `vw_brk_tranid_asc` (
`chain_ledger_id` bigint(20) unsigned
,`tran_id` int(11)
,`tran_name` varchar(30)
,`individual_amt` decimal(20,2)
,`status` varchar(16)
,`send_ts` datetime
,`sender_ecobricker` varchar(50)
,`block_tran_type` varchar(40)
,`block_amt` float
,`sender` varchar(50)
,`receiver_or_receivers` varchar(255)
,`receiver_1` varchar(50)
,`receiver_2` varchar(50)
,`receiver_3` varchar(50)
,`receiver_central_reserve` varchar(50)
,`sender_central_reserve` varchar(50)
,`ecobrick_serial_no` mediumint(9)
,`tran_sender_note` mediumtext
,`product` text
,`send_dt` date
,`accomp_payment` decimal(20,2)
,`authenticator_version` varchar(10)
,`expense_type` varchar(50)
,`gea_accounting_category` varchar(20)
,`shipping_cost_brk` decimal(10,2)
,`product_cost_brk` decimal(10,2)
,`total_cost_incl_shipping` decimal(10,2)
,`shipping_with_currency` decimal(20,2)
,`aes_officially_purchased` decimal(10,2)
,`country_of_buyer` varchar(20)
,`currency_for_shipping` varchar(20)
,`credit_other_ecobricker_yn` varchar(5)
,`catalyst_name` varchar(50)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_brk_tranid_desc`
-- (See below for the actual view)
--
CREATE TABLE `vw_brk_tranid_desc` (
`chain_ledger_id` bigint(20) unsigned
,`tran_id` int(11)
,`tran_name` varchar(30)
,`individual_amt` decimal(20,2)
,`status` varchar(16)
,`send_ts` datetime
,`sender_ecobricker` varchar(50)
,`block_tran_type` varchar(40)
,`block_amt` float
,`sender` varchar(50)
,`receiver_or_receivers` varchar(255)
,`receiver_1` varchar(50)
,`receiver_2` varchar(50)
,`receiver_3` varchar(50)
,`receiver_central_reserve` varchar(50)
,`sender_central_reserve` varchar(50)
,`ecobrick_serial_no` mediumint(9)
,`tran_sender_note` mediumtext
,`product` text
,`send_dt` date
,`accomp_payment` decimal(20,2)
,`authenticator_version` varchar(10)
,`expense_type` varchar(50)
,`gea_accounting_category` varchar(20)
,`shipping_cost_brk` decimal(10,2)
,`product_cost_brk` decimal(10,2)
,`total_cost_incl_shipping` decimal(10,2)
,`shipping_with_currency` decimal(20,2)
,`aes_officially_purchased` decimal(10,2)
,`country_of_buyer` varchar(20)
,`currency_for_shipping` varchar(20)
,`credit_other_ecobricker_yn` varchar(5)
,`catalyst_name` varchar(50)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_brk_tran_ledgerid_asc`
-- (See below for the actual view)
--
CREATE TABLE `vw_brk_tran_ledgerid_asc` (
`chain_ledger_id` bigint(20) unsigned
,`tran_id` int(11)
,`tran_name` varchar(30)
,`individual_amt` decimal(20,2)
,`status` varchar(16)
,`send_ts` datetime
,`sender_ecobricker` varchar(50)
,`block_tran_type` varchar(40)
,`block_amt` float
,`sender` varchar(50)
,`receiver_or_receivers` varchar(255)
,`receiver_1` varchar(50)
,`receiver_2` varchar(50)
,`receiver_3` varchar(50)
,`receiver_central_reserve` varchar(50)
,`sender_central_reserve` varchar(50)
,`ecobrick_serial_no` mediumint(9)
,`tran_sender_note` mediumtext
,`product` text
,`send_dt` date
,`accomp_payment` decimal(20,2)
,`authenticator_version` varchar(10)
,`expense_type` varchar(50)
,`gea_accounting_category` varchar(20)
,`shipping_cost_brk` decimal(10,2)
,`product_cost_brk` decimal(10,2)
,`total_cost_incl_shipping` decimal(10,2)
,`shipping_with_currency` decimal(20,2)
,`aes_officially_purchased` decimal(10,2)
,`country_of_buyer` varchar(20)
,`currency_for_shipping` varchar(20)
,`credit_other_ecobricker_yn` varchar(5)
,`catalyst_name` varchar(50)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_brk_tran_ledgerid_desc`
-- (See below for the actual view)
--
CREATE TABLE `vw_brk_tran_ledgerid_desc` (
`chain_ledger_id` bigint(20) unsigned
,`tran_id` int(11)
,`tran_name` varchar(30)
,`individual_amt` decimal(20,2)
,`status` varchar(16)
,`send_ts` datetime
,`sender_ecobricker` varchar(50)
,`block_tran_type` varchar(40)
,`block_amt` float
,`sender` varchar(50)
,`receiver_or_receivers` varchar(255)
,`receiver_1` varchar(50)
,`receiver_2` varchar(50)
,`receiver_3` varchar(50)
,`receiver_central_reserve` varchar(50)
,`sender_central_reserve` varchar(50)
,`ecobrick_serial_no` mediumint(9)
,`tran_sender_note` mediumtext
,`product` text
,`send_dt` date
,`accomp_payment` decimal(20,2)
,`authenticator_version` varchar(10)
,`expense_type` varchar(50)
,`gea_accounting_category` varchar(20)
,`shipping_cost_brk` decimal(10,2)
,`product_cost_brk` decimal(10,2)
,`total_cost_incl_shipping` decimal(10,2)
,`shipping_with_currency` decimal(20,2)
,`aes_officially_purchased` decimal(10,2)
,`country_of_buyer` varchar(20)
,`currency_for_shipping` varchar(20)
,`credit_other_ecobricker_yn` varchar(5)
,`catalyst_name` varchar(50)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_calc_weight_by_year`
-- (See below for the actual view)
--
CREATE TABLE `vw_calc_weight_by_year` (
`year` int(5)
,`calculated_weight_total_kg` double
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_cash_tran_asc`
-- (See below for the actual view)
--
CREATE TABLE `vw_cash_tran_asc` (
`knack_record_id` char(24)
,`cash_tran_id` int(11)
,`sender_ecobricker` varchar(50)
,`datetime_sent_ts` datetime
,`type_of_transaction` varchar(40)
,`tran_name_desc` varchar(255)
,`native_ccy_amt` decimal(20,2)
,`currency_code` char(3)
,`native_ccy_amt_display` varchar(50)
,`exchange_ratio` float
,`usd_amount` decimal(20,2)
,`total_product_cost_incl_shipping` decimal(20,2)
,`product` varchar(250)
,`product_cost` decimal(10,2)
,`transaction_date_dt` date
,`shipping_cost` decimal(10,2)
,`shipping_cost_+ccy_display` varchar(50)
,`total_product_cost_+ccy_display` varchar(100)
,`receiving_gea_acct` varchar(20)
,`sender_for_display` varchar(50)
,`receiver_for_display` varchar(50)
,`receiver_gea_account` varchar(20)
,`expense_vendor` varchar(50)
,`purchase_method` varchar(30)
,`recurring_trans_period` varchar(15)
,`expense_accounting_type` varchar(50)
,`revenue_accounting_type` varchar(50)
,`tran_processor` varchar(50)
,`connected_brk_tran_name` varchar(100)
,`aes_to_usd_rate` float
,`aes_plastic_offset_purchase_kg` decimal(10,2)
,`usd_payment_for_aes` decimal(20,2)
,`gbp_payment_for_aes` decimal(20,2)
,`native_conversion_of_aes` decimal(20,2)
,`brk_cost_of_aes_display` varchar(100)
,`credited_catalyst` varchar(255)
,`brikcoins_purchased_display` varchar(20)
,`usd_paid_for_brk_+ccy_display` varchar(30)
,`connected_brk_trans` varchar(100)
,`paymt_record_url` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_cash_tran_desc`
-- (See below for the actual view)
--
CREATE TABLE `vw_cash_tran_desc` (
`knack_record_id` char(24)
,`cash_tran_id` int(11)
,`sender_ecobricker` varchar(50)
,`datetime_sent_ts` datetime
,`type_of_transaction` varchar(40)
,`tran_name_desc` varchar(255)
,`native_ccy_amt` decimal(20,2)
,`currency_code` char(3)
,`native_ccy_amt_display` varchar(50)
,`exchange_ratio` float
,`usd_amount` decimal(20,2)
,`total_product_cost_incl_shipping` decimal(20,2)
,`product` varchar(250)
,`product_cost` decimal(10,2)
,`transaction_date_dt` date
,`shipping_cost` decimal(10,2)
,`shipping_cost_+ccy_display` varchar(50)
,`total_product_cost_+ccy_display` varchar(100)
,`receiving_gea_acct` varchar(20)
,`sender_for_display` varchar(50)
,`receiver_for_display` varchar(50)
,`receiver_gea_account` varchar(20)
,`expense_vendor` varchar(50)
,`purchase_method` varchar(30)
,`recurring_trans_period` varchar(15)
,`expense_accounting_type` varchar(50)
,`revenue_accounting_type` varchar(50)
,`tran_processor` varchar(50)
,`connected_brk_tran_name` varchar(100)
,`aes_to_usd_rate` float
,`aes_plastic_offset_purchase_kg` decimal(10,2)
,`usd_payment_for_aes` decimal(20,2)
,`gbp_payment_for_aes` decimal(20,2)
,`native_conversion_of_aes` decimal(20,2)
,`brk_cost_of_aes_display` varchar(100)
,`credited_catalyst` varchar(255)
,`brikcoins_purchased_display` varchar(20)
,`usd_paid_for_brk_+ccy_display` varchar(30)
,`connected_brk_trans` varchar(100)
,`paymt_record_url` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_detail_sums_by_year`
-- (See below for the actual view)
--
CREATE TABLE `vw_detail_sums_by_year` (
`year` int(5)
,`from_date` date
,`to_date` date
,`total_no_of_exp_transactions` bigint(21)
,`total_exp_usd_amount` varchar(57)
,`total_exp_idr_amount` varchar(55)
,`raw_usd_amt` decimal(42,2)
,`raw_idr_amt` decimal(41,0)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_detail_sums_by_year_idr`
-- (See below for the actual view)
--
CREATE TABLE `vw_detail_sums_by_year_idr` (
`year` int(5)
,`from_date` date
,`to_date` date
,`total_brk` varchar(417)
,`brick_count` bigint(21)
,`weight` decimal(10,1)
,`tot_idr_exp_amt` varchar(55)
,`tot_idr_rev_amt` varchar(55)
,`calculated_weight` varchar(417)
,`final_aes_plastic_cost_idr` varchar(414)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_ecobricks_count_by_year`
-- (See below for the actual view)
--
CREATE TABLE `vw_ecobricks_count_by_year` (
`brick_count` bigint(21)
,`year` int(5)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_ecobricks_desc`
-- (See below for the actual view)
--
CREATE TABLE `vw_ecobricks_desc` (
`knack_record_id` char(24)
,`ecobrick_unique_id` int(11)
,`serial_no` varchar(10)
,`owner` varchar(50)
,`ecobricker_maker` varchar(50)
,`ecobrick_thumb_photo_url` varchar(255)
,`ecobrick_full_photo_url` varchar(255)
,`selfie_photo_url` varchar(255)
,`volume_ml` int(11)
,`universal_volume_ml` float
,`weight_g` int(11)
,`density` decimal(10,2)
,`date_logged_ts` datetime
,`CO2_kg` decimal(10,2)
,`sequestration_type` varchar(100)
,`last_validation_ts` datetime
,`validator_1` varchar(50)
,`validator_2` varchar(50)
,`validator_3` varchar(50)
,`validation_score_avg` decimal(10,2)
,`final_validation_score` decimal(10,2)
,`vision` varchar(1024)
,`last_ownership_change` date
,`non_registered_maker_name` varchar(30)
,`actual_maker_name` varchar(50)
,`weight_authenticated_kg` float
,`location_country` varchar(35)
,`location_region` varchar(50)
,`location_city` varchar(100)
,`location_full` varchar(254)
,`community_name` varchar(50)
,`brand_name` varchar(30)
,`bottom_colour` varchar(20)
,`plastic_from` varchar(30)
,`ecobrick_brk_display_value` varchar(20)
,`ecobrick_dec_brk_val` decimal(10,2)
,`ecobrick_brk_amt` float
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_ecobrick_tran_desc`
-- (See below for the actual view)
--
CREATE TABLE `vw_ecobrick_tran_desc` (
`ecobrick_unique_id` int(11)
,`serial_no` varchar(10)
,`owner` varchar(50)
,`ecobricker_maker` varchar(50)
,`ecobrick_thumb_photo_url` varchar(255)
,`ecobrick_full_photo_url` varchar(255)
,`selfie_photo_url` varchar(255)
,`volume_ml` int(11)
,`universal_volume_ml` float
,`weight_g` int(11)
,`density` decimal(10,2)
,`date_logged_ts` datetime
,`CO2_kg` decimal(10,2)
,`sequestration_type` varchar(100)
,`last_validation_ts` datetime
,`validator_1` varchar(50)
,`validator_2` varchar(50)
,`validator_3` varchar(50)
,`validation_score_avg` decimal(10,2)
,`final_validation_score` decimal(10,2)
,`vision` varchar(1024)
,`last_ownership_change` date
,`non_registered_maker_name` varchar(30)
,`actual_maker_name` varchar(50)
,`weight_authenticated_kg` float
,`location_country` varchar(35)
,`location_region` varchar(50)
,`location_city` varchar(100)
,`location_full` varchar(254)
,`community_name` varchar(50)
,`brand_name` varchar(30)
,`bottom_colour` varchar(20)
,`plastic_from` varchar(30)
,`ecobrick_brk_display_value` varchar(20)
,`ecobrick_dec_brk_val` decimal(10,2)
,`ecobrick_brk_amt` float
,`brk_beneficiaries` varchar(255)
,`individual_brk_allocated` decimal(20,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_exp_by_year_category`
-- (See below for the actual view)
--
CREATE TABLE `vw_exp_by_year_category` (
`year` int(5)
,`expense_category` varchar(50)
,`no_of_transactions` bigint(21)
,`total_usd` decimal(42,2)
,`total_idr` decimal(41,0)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_exp_cash_tran_desc`
-- (See below for the actual view)
--
CREATE TABLE `vw_exp_cash_tran_desc` (
`knack_record_id` char(24)
,`cash_tran_id` int(11)
,`sender_ecobricker` varchar(50)
,`datetime_sent_ts` datetime
,`type_of_transaction` varchar(40)
,`tran_name_desc` varchar(255)
,`native_ccy_amt` decimal(20,2)
,`currency_code` char(3)
,`native_ccy_amt_display` varchar(50)
,`exchange_ratio` float
,`usd_amount` decimal(20,2)
,`total_product_cost_incl_shipping` decimal(20,2)
,`product` varchar(250)
,`product_cost` decimal(10,2)
,`transaction_date_dt` date
,`shipping_cost` decimal(10,2)
,`shipping_cost_+ccy_display` varchar(50)
,`total_product_cost_+ccy_display` varchar(100)
,`receiving_gea_acct` varchar(20)
,`sender_for_display` varchar(50)
,`receiver_for_display` varchar(50)
,`receiver_gea_account` varchar(20)
,`expense_vendor` varchar(50)
,`purchase_method` varchar(30)
,`recurring_trans_period` varchar(15)
,`expense_accounting_type` varchar(50)
,`revenue_accounting_type` varchar(50)
,`tran_processor` varchar(50)
,`connected_brk_tran_name` varchar(100)
,`aes_to_usd_rate` float
,`aes_plastic_offset_purchase_kg` decimal(10,2)
,`usd_payment_for_aes` decimal(20,2)
,`gbp_payment_for_aes` decimal(20,2)
,`native_conversion_of_aes` decimal(20,2)
,`brk_cost_of_aes_display` varchar(100)
,`credited_catalyst` varchar(255)
,`brikcoins_purchased_display` varchar(20)
,`usd_paid_for_brk_+ccy_display` varchar(30)
,`connected_brk_trans` varchar(100)
,`paymt_record_url` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_gallery_feed`
-- (See below for the actual view)
--
CREATE TABLE `vw_gallery_feed` (
`ecobrick_unique_id` int(11)
,`final_validation_score` decimal(10,2)
,`ecobrick_owner` varchar(50)
,`location` varchar(254)
,`ecobrick_brk_amt` float
,`CO2_kg` decimal(10,2)
,`weight_in_g` int(11)
,`density` varchar(14)
,`photo_url` varchar(255)
,`thumb_url` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_rev_by_year_category`
-- (See below for the actual view)
--
CREATE TABLE `vw_rev_by_year_category` (
`year` int(5)
,`revenue_category` varchar(50)
,`no_of_transactions` bigint(21)
,`total_usd` decimal(42,2)
,`total_idr` decimal(41,0)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_rev_cash_tran_desc`
-- (See below for the actual view)
--
CREATE TABLE `vw_rev_cash_tran_desc` (
`knack_record_id` char(24)
,`cash_tran_id` int(11)
,`sender_ecobricker` varchar(50)
,`datetime_sent_ts` datetime
,`type_of_transaction` varchar(40)
,`tran_name_desc` varchar(255)
,`native_ccy_amt` decimal(20,2)
,`currency_code` char(3)
,`native_ccy_amt_display` varchar(50)
,`exchange_ratio` float
,`usd_amount` decimal(20,2)
,`total_product_cost_incl_shipping` decimal(20,2)
,`product` varchar(250)
,`product_cost` decimal(10,2)
,`transaction_date_dt` date
,`shipping_cost` decimal(10,2)
,`shipping_cost_+ccy_display` varchar(50)
,`total_product_cost_+ccy_display` varchar(100)
,`receiving_gea_acct` varchar(20)
,`sender_for_display` varchar(50)
,`receiver_for_display` varchar(50)
,`receiver_gea_account` varchar(20)
,`expense_vendor` varchar(50)
,`purchase_method` varchar(30)
,`recurring_trans_period` varchar(15)
,`expense_accounting_type` varchar(50)
,`revenue_accounting_type` varchar(50)
,`tran_processor` varchar(50)
,`connected_brk_tran_name` varchar(100)
,`aes_to_usd_rate` float
,`aes_plastic_offset_purchase_kg` decimal(10,2)
,`usd_payment_for_aes` decimal(20,2)
,`gbp_payment_for_aes` decimal(20,2)
,`native_conversion_of_aes` decimal(20,2)
,`brk_cost_of_aes_display` varchar(100)
,`credited_catalyst` varchar(255)
,`brikcoins_purchased_display` varchar(20)
,`usd_paid_for_brk_+ccy_display` varchar(30)
,`connected_brk_trans` varchar(100)
,`paymt_record_url` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_sum_aes_brk`
-- (See below for the actual view)
--
CREATE TABLE `vw_sum_aes_brk` (
`total_aes_brk` decimal(42,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_sum_brk_total`
-- (See below for the actual view)
--
CREATE TABLE `vw_sum_brk_total` (
`from_date` date
,`to_date` date
,`total_brk` varchar(417)
,`aes_purchased` varchar(57)
,`net_brk_in_circulation` varchar(417)
,`total_weight_of_plastic` varchar(417)
,`plastic_value_g_per_brk` varchar(417)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_sum_cash_aes`
-- (See below for the actual view)
--
CREATE TABLE `vw_sum_cash_aes` (
`total_cash_aes_brk` decimal(42,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_top_10_last_month`
-- (See below for the actual view)
--
CREATE TABLE `vw_top_10_last_month` (
`month` varchar(9)
,`ecobrick_unique_id` int(11)
,`final_validation_score` decimal(10,2)
,`ecobrick_owner` varchar(50)
,`location` varchar(254)
,`weight_in_g` int(11)
,`density` decimal(10,2)
,`ecobrick_full_photo_url` varchar(255)
,`ecobrick_thumb_photo_url` varchar(255)
,`selfie_photo_url` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_tot_exp_by_year`
-- (See below for the actual view)
--
CREATE TABLE `vw_tot_exp_by_year` (
`year` int(5)
,`from_date` date
,`to_date` date
,`total_no_of_exp_transactions` bigint(21)
,`total_exp_usd_amount` varchar(57)
,`total_exp_idr_amount` varchar(55)
,`raw_usd_amt` decimal(42,2)
,`raw_idr_amt` decimal(41,0)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_tot_rev_by_year`
-- (See below for the actual view)
--
CREATE TABLE `vw_tot_rev_by_year` (
`year` int(5)
,`from_date` date
,`to_date` date
,`total_no_of_rev_transactions` bigint(21)
,`total_rev_usd_amount` varchar(57)
,`total_rev_idr_amount` varchar(55)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_weight_by_year`
-- (See below for the actual view)
--
CREATE TABLE `vw_weight_by_year` (
`year` int(5)
,`weight` decimal(10,1)
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `continents_tb`
--
ALTER TABLE `continents_tb`
  ADD PRIMARY KEY (`continent_code`);

--
-- Indexes for table `countries_tb`
--
ALTER TABLE `countries_tb`
  ADD PRIMARY KEY (`country_id`),
  ADD KEY `country_name_index` (`country_name`),
  ADD KEY `country_code_index` (`country_code`),
  ADD KEY `fk_continent_code` (`continent_code`);

--
-- Indexes for table `dash_notices_tb`
--
ALTER TABLE `dash_notices_tb`
  ADD PRIMARY KEY (`notice_id`);

--
-- Indexes for table `earthen_mailgun_events_tb`
--
ALTER TABLE `earthen_mailgun_events_tb`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `idx_recipient_time` (`recipient_email`,`event_timestamp`),
  ADD KEY `idx_event_type_time` (`event_type`,`event_timestamp`),
  ADD KEY `idx_campaign_time` (`campaign_name`,`event_timestamp`),
  ADD KEY `idx_member_id` (`member_id`),
  ADD KEY `idx_message_id` (`mailgun_message_id`);

--
-- Indexes for table `earthen_member_engagement_tb`
--
ALTER TABLE `earthen_member_engagement_tb`
  ADD PRIMARY KEY (`recipient_email`),
  ADD KEY `idx_member_id` (`member_id`),
  ADD KEY `idx_last_open` (`last_open_at`),
  ADD KEY `idx_suppressed` (`is_suppressed`);

--
-- Indexes for table `earthen_send_batch_tb`
--
ALTER TABLE `earthen_send_batch_tb`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_batch_member` (`batch_label`,`ghost_member_id`),
  ADD KEY `idx_batch_processing` (`batch_label`,`processing`,`test_sent`),
  ADD KEY `idx_email` (`ghost_email`),
  ADD KEY `idx_ghost_member` (`ghost_member_id`);

--
-- Indexes for table `load_brk_transaction`
--
ALTER TABLE `load_brk_transaction`
  ADD PRIMARY KEY (`tran_id`);

--
-- Indexes for table `load_cash_transaction`
--
ALTER TABLE `load_cash_transaction`
  ADD PRIMARY KEY (`transac_id`) USING BTREE;

--
-- Indexes for table `payments_tb`
--
ALTER TABLE `payments_tb`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `uq_idempotency` (`idempotency_key`),
  ADD KEY `idx_payments_buwana` (`buwana_id`),
  ADD KEY `idx_payments_campaign` (`campaign_type`,`campaign_id`),
  ADD KEY `idx_payments_pledge` (`pledge_id`),
  ADD KEY `idx_payments_registration` (`registration_id`),
  ADD KEY `idx_payments_order` (`order_id`),
  ADD KEY `idx_payments_purpose` (`payment_purpose`),
  ADD KEY `idx_payments_status` (`status`),
  ADD KEY `idx_payments_gateway` (`gateway`,`gateway_ref`);

--
-- Indexes for table `payment_events_tb`
--
ALTER TABLE `payment_events_tb`
  ADD PRIMARY KEY (`payment_event_id`),
  ADD UNIQUE KEY `uq_gateway_event` (`gateway`,`gateway_event_id`),
  ADD KEY `idx_events_payment` (`payment_id`),
  ADD KEY `idx_events_gateway_ref` (`gateway`,`gateway_ref`);

--
-- Indexes for table `payment_items_tb`
--
ALTER TABLE `payment_items_tb`
  ADD PRIMARY KEY (`payment_item_id`),
  ADD KEY `idx_items_payment` (`payment_id`),
  ADD KEY `idx_items_ref` (`item_type`,`item_ref_id`);

--
-- Indexes for table `products_tb`
--
ALTER TABLE `products_tb`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `uq_product_code` (`product_code`),
  ADD KEY `idx_product_status` (`product_status`),
  ADD KEY `idx_product_type` (`product_type`);

--
-- Indexes for table `product_campaigns_tb`
--
ALTER TABLE `product_campaigns_tb`
  ADD PRIMARY KEY (`campaign_id`),
  ADD KEY `idx_campaign_product` (`product_id`),
  ADD KEY `idx_campaign_status` (`threshold_status`),
  ADD KEY `idx_campaign_deadline` (`pledge_deadline`);

--
-- Indexes for table `product_orders_tb`
--
ALTER TABLE `product_orders_tb`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_order_campaign` (`campaign_id`),
  ADD KEY `idx_order_product` (`product_id`),
  ADD KEY `idx_order_buwana` (`buwana_id`),
  ADD KEY `idx_order_pledge` (`pledge_id`),
  ADD KEY `idx_order_status` (`status`);

--
-- Indexes for table `product_pledges_tb`
--
ALTER TABLE `product_pledges_tb`
  ADD PRIMARY KEY (`pledge_id`),
  ADD UNIQUE KEY `uq_product_campaign_user_status` (`campaign_id`,`buwana_id`,`pledge_status`),
  ADD KEY `idx_campaign` (`campaign_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_buwana` (`buwana_id`),
  ADD KEY `idx_status` (`pledge_status`),
  ADD KEY `idx_campaign_status` (`campaign_id`,`pledge_status`),
  ADD KEY `idx_payment_id` (`payment_id`);

--
-- Indexes for table `tb_brk_transaction`
--
ALTER TABLE `tb_brk_transaction`
  ADD PRIMARY KEY (`tran_id`),
  ADD UNIQUE KEY `chain_ledger_id` (`chain_ledger_id`),
  ADD KEY `ix_send_dt` (`send_dt`) USING BTREE,
  ADD KEY `ix_block_tran_type` (`block_tran_type`) USING BTREE,
  ADD KEY `ecobrick_serial_no` (`ecobrick_serial_no`),
  ADD KEY `brk_tran_catalyst_ix` (`catalyst_name`);

--
-- Indexes for table `tb_cash_transaction`
--
ALTER TABLE `tb_cash_transaction`
  ADD PRIMARY KEY (`cash_tran_id`),
  ADD KEY `tran_date_idx` (`transaction_date_dt`),
  ADD KEY `idx_cash_payment` (`payment_id`),
  ADD KEY `idx_cash_gateway` (`tran_processor`,`gateway_ref`);

--
-- Indexes for table `tb_ecobrickers`
--
ALTER TABLE `tb_ecobrickers`
  ADD PRIMARY KEY (`ecobricker_id`),
  ADD UNIQUE KEY `open_id` (`open_id`),
  ADD KEY `name` (`name`),
  ADD KEY `email_addr` (`email_addr`),
  ADD KEY `fk_ecobrick` (`buwana_id`),
  ADD KEY `Bricker_name` (`full_name`(250)),
  ADD KEY `fk_community` (`community_id`),
  ADD KEY `maker_id` (`maker_idxx`);

--
-- Indexes for table `tb_ecobricks`
--
ALTER TABLE `tb_ecobricks`
  ADD PRIMARY KEY (`ecobrick_unique_id`) USING BTREE,
  ADD KEY `colour_idx` (`bottom_colour`),
  ADD KEY `val_dt_idx` (`last_validation_ts`),
  ADD KEY `fk_project` (`project_id`),
  ADD KEY `fk_training` (`training_id`),
  ADD KEY `fk_ecobrick_country` (`country_id`),
  ADD KEY `fk_maker_id` (`maker_id`),
  ADD KEY `idx_serial_no` (`serial_no`),
  ADD KEY `fk_ecobricker_id` (`ecobricker_id`);

--
-- Indexes for table `tb_projects`
--
ALTER TABLE `tb_projects`
  ADD PRIMARY KEY (`project_id`);

--
-- Indexes for table `tb_static_calc_data`
--
ALTER TABLE `tb_static_calc_data`
  ADD UNIQUE KEY `row_idx` (`row_id`);

--
-- Indexes for table `tb_trainings`
--
ALTER TABLE `tb_trainings`
  ADD PRIMARY KEY (`training_id`),
  ADD KEY `fk_training_community` (`community_id`),
  ADD KEY `fk_training_country` (`country_id`);

--
-- Indexes for table `tb_training_trainees`
--
ALTER TABLE `tb_training_trainees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tb_training_trainees_ibfk_2` (`ecobricker_id`),
  ADD KEY `tb_training_trainees_ibfk_1` (`training_id`);

--
-- Indexes for table `tb_training_trainers`
--
ALTER TABLE `tb_training_trainers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tb_training_trainers_ibfk_1` (`training_id`),
  ADD KEY `tb_training_trainers_ibfk_2` (`ecobricker_id`);

--
-- Indexes for table `training_pledges_tb`
--
ALTER TABLE `training_pledges_tb`
  ADD PRIMARY KEY (`pledge_id`),
  ADD UNIQUE KEY `uq_training_user_status` (`training_id`,`buwana_id`,`pledge_status`),
  ADD KEY `idx_training` (`training_id`),
  ADD KEY `idx_buwana` (`buwana_id`),
  ADD KEY `idx_status` (`pledge_status`),
  ADD KEY `idx_training_status` (`training_id`,`pledge_status`),
  ADD KEY `idx_payment_id` (`payment_id`);

--
-- Indexes for table `training_registrations_tb`
--
ALTER TABLE `training_registrations_tb`
  ADD PRIMARY KEY (`registration_id`),
  ADD KEY `idx_reg_training` (`training_id`),
  ADD KEY `idx_reg_buwana` (`buwana_id`),
  ADD KEY `idx_reg_pledge` (`pledge_id`),
  ADD KEY `idx_reg_status` (`status`);

--
-- Indexes for table `validations_tb`
--
ALTER TABLE `validations_tb`
  ADD PRIMARY KEY (`validation_id`),
  ADD KEY `idx_recorded_serial` (`recorded_serial`),
  ADD KEY `idx_ecobricker_id` (`ecobricker_id`),
  ADD KEY `idx_brk_trans_no` (`brk_trans_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `countries_tb`
--
ALTER TABLE `countries_tb`
  MODIFY `country_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dash_notices_tb`
--
ALTER TABLE `dash_notices_tb`
  MODIFY `notice_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `earthen_mailgun_events_tb`
--
ALTER TABLE `earthen_mailgun_events_tb`
  MODIFY `event_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `earthen_send_batch_tb`
--
ALTER TABLE `earthen_send_batch_tb`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments_tb`
--
ALTER TABLE `payments_tb`
  MODIFY `payment_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_events_tb`
--
ALTER TABLE `payment_events_tb`
  MODIFY `payment_event_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_items_tb`
--
ALTER TABLE `payment_items_tb`
  MODIFY `payment_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products_tb`
--
ALTER TABLE `products_tb`
  MODIFY `product_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_campaigns_tb`
--
ALTER TABLE `product_campaigns_tb`
  MODIFY `campaign_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_orders_tb`
--
ALTER TABLE `product_orders_tb`
  MODIFY `order_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_pledges_tb`
--
ALTER TABLE `product_pledges_tb`
  MODIFY `pledge_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_brk_transaction`
--
ALTER TABLE `tb_brk_transaction`
  MODIFY `chain_ledger_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_cash_transaction`
--
ALTER TABLE `tb_cash_transaction`
  MODIFY `cash_tran_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_ecobrickers`
--
ALTER TABLE `tb_ecobrickers`
  MODIFY `ecobricker_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_projects`
--
ALTER TABLE `tb_projects`
  MODIFY `project_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_trainings`
--
ALTER TABLE `tb_trainings`
  MODIFY `training_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_training_trainees`
--
ALTER TABLE `tb_training_trainees`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_training_trainers`
--
ALTER TABLE `tb_training_trainers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `training_pledges_tb`
--
ALTER TABLE `training_pledges_tb`
  MODIFY `pledge_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `training_registrations_tb`
--
ALTER TABLE `training_registrations_tb`
  MODIFY `registration_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `validations_tb`
--
ALTER TABLE `validations_tb`
  MODIFY `validation_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Structure for view `vw_brk_by_year`
--
DROP TABLE IF EXISTS `vw_brk_by_year`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_brk_by_year`  AS SELECT year(`tb_brk_transaction`.`send_dt`) AS `year`, min(`tb_brk_transaction`.`send_dt`) AS `from_date`, max(`tb_brk_transaction`.`send_dt`) AS `to_date`, format(sum(`tb_brk_transaction`.`block_amt`),2) AS `total_brk` FROM `tb_brk_transaction` GROUP BY year(`tb_brk_transaction`.`send_dt`) ;

-- --------------------------------------------------------

--
-- Structure for view `vw_brk_pool`
--
DROP TABLE IF EXISTS `vw_brk_pool`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_brk_pool`  AS SELECT (select `vw_sum_brk_total`.`total_brk` from `vw_sum_brk_total`) - (select `vw_sum_aes_brk`.`total_aes_brk` from `vw_sum_aes_brk`) AS `total_net_brk` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_brk_tranid_asc`
--
DROP TABLE IF EXISTS `vw_brk_tranid_asc`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_brk_tranid_asc`  AS SELECT `tb_brk_transaction`.`chain_ledger_id` AS `chain_ledger_id`, `tb_brk_transaction`.`tran_id` AS `tran_id`, `tb_brk_transaction`.`tran_name` AS `tran_name`, `tb_brk_transaction`.`individual_amt` AS `individual_amt`, `tb_brk_transaction`.`status` AS `status`, `tb_brk_transaction`.`send_ts` AS `send_ts`, `tb_brk_transaction`.`sender_ecobricker` AS `sender_ecobricker`, `tb_brk_transaction`.`block_tran_type` AS `block_tran_type`, `tb_brk_transaction`.`block_amt` AS `block_amt`, `tb_brk_transaction`.`sender` AS `sender`, `tb_brk_transaction`.`receiver_or_receivers` AS `receiver_or_receivers`, `tb_brk_transaction`.`receiver_1` AS `receiver_1`, `tb_brk_transaction`.`receiver_2` AS `receiver_2`, `tb_brk_transaction`.`receiver_3` AS `receiver_3`, `tb_brk_transaction`.`receiver_central_reserve` AS `receiver_central_reserve`, `tb_brk_transaction`.`sender_central_reserve` AS `sender_central_reserve`, `tb_brk_transaction`.`ecobrick_serial_no` AS `ecobrick_serial_no`, `tb_brk_transaction`.`tran_sender_note` AS `tran_sender_note`, `tb_brk_transaction`.`product` AS `product`, `tb_brk_transaction`.`send_dt` AS `send_dt`, `tb_brk_transaction`.`accomp_payment` AS `accomp_payment`, `tb_brk_transaction`.`authenticator_version` AS `authenticator_version`, `tb_brk_transaction`.`expense_type` AS `expense_type`, `tb_brk_transaction`.`gea_accounting_category` AS `gea_accounting_category`, `tb_brk_transaction`.`shipping_cost_brk` AS `shipping_cost_brk`, `tb_brk_transaction`.`product_cost_brk` AS `product_cost_brk`, `tb_brk_transaction`.`total_cost_incl_shipping` AS `total_cost_incl_shipping`, `tb_brk_transaction`.`shipping_with_currency` AS `shipping_with_currency`, `tb_brk_transaction`.`aes_officially_purchased` AS `aes_officially_purchased`, `tb_brk_transaction`.`country_of_buyer` AS `country_of_buyer`, `tb_brk_transaction`.`currency_for_shipping` AS `currency_for_shipping`, `tb_brk_transaction`.`credit_other_ecobricker_yn` AS `credit_other_ecobricker_yn`, `tb_brk_transaction`.`catalyst_name` AS `catalyst_name` FROM `tb_brk_transaction` ORDER BY `tb_brk_transaction`.`tran_id` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_brk_tranid_desc`
--
DROP TABLE IF EXISTS `vw_brk_tranid_desc`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_brk_tranid_desc`  AS SELECT `tb_brk_transaction`.`chain_ledger_id` AS `chain_ledger_id`, `tb_brk_transaction`.`tran_id` AS `tran_id`, `tb_brk_transaction`.`tran_name` AS `tran_name`, `tb_brk_transaction`.`individual_amt` AS `individual_amt`, `tb_brk_transaction`.`status` AS `status`, `tb_brk_transaction`.`send_ts` AS `send_ts`, `tb_brk_transaction`.`sender_ecobricker` AS `sender_ecobricker`, `tb_brk_transaction`.`block_tran_type` AS `block_tran_type`, `tb_brk_transaction`.`block_amt` AS `block_amt`, `tb_brk_transaction`.`sender` AS `sender`, `tb_brk_transaction`.`receiver_or_receivers` AS `receiver_or_receivers`, `tb_brk_transaction`.`receiver_1` AS `receiver_1`, `tb_brk_transaction`.`receiver_2` AS `receiver_2`, `tb_brk_transaction`.`receiver_3` AS `receiver_3`, `tb_brk_transaction`.`receiver_central_reserve` AS `receiver_central_reserve`, `tb_brk_transaction`.`sender_central_reserve` AS `sender_central_reserve`, `tb_brk_transaction`.`ecobrick_serial_no` AS `ecobrick_serial_no`, `tb_brk_transaction`.`tran_sender_note` AS `tran_sender_note`, `tb_brk_transaction`.`product` AS `product`, `tb_brk_transaction`.`send_dt` AS `send_dt`, `tb_brk_transaction`.`accomp_payment` AS `accomp_payment`, `tb_brk_transaction`.`authenticator_version` AS `authenticator_version`, `tb_brk_transaction`.`expense_type` AS `expense_type`, `tb_brk_transaction`.`gea_accounting_category` AS `gea_accounting_category`, `tb_brk_transaction`.`shipping_cost_brk` AS `shipping_cost_brk`, `tb_brk_transaction`.`product_cost_brk` AS `product_cost_brk`, `tb_brk_transaction`.`total_cost_incl_shipping` AS `total_cost_incl_shipping`, `tb_brk_transaction`.`shipping_with_currency` AS `shipping_with_currency`, `tb_brk_transaction`.`aes_officially_purchased` AS `aes_officially_purchased`, `tb_brk_transaction`.`country_of_buyer` AS `country_of_buyer`, `tb_brk_transaction`.`currency_for_shipping` AS `currency_for_shipping`, `tb_brk_transaction`.`credit_other_ecobricker_yn` AS `credit_other_ecobricker_yn`, `tb_brk_transaction`.`catalyst_name` AS `catalyst_name` FROM `tb_brk_transaction` ORDER BY `tb_brk_transaction`.`tran_id` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_brk_tran_ledgerid_asc`
--
DROP TABLE IF EXISTS `vw_brk_tran_ledgerid_asc`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_brk_tran_ledgerid_asc`  AS SELECT `tb_brk_transaction`.`chain_ledger_id` AS `chain_ledger_id`, `tb_brk_transaction`.`tran_id` AS `tran_id`, `tb_brk_transaction`.`tran_name` AS `tran_name`, `tb_brk_transaction`.`individual_amt` AS `individual_amt`, `tb_brk_transaction`.`status` AS `status`, `tb_brk_transaction`.`send_ts` AS `send_ts`, `tb_brk_transaction`.`sender_ecobricker` AS `sender_ecobricker`, `tb_brk_transaction`.`block_tran_type` AS `block_tran_type`, `tb_brk_transaction`.`block_amt` AS `block_amt`, `tb_brk_transaction`.`sender` AS `sender`, `tb_brk_transaction`.`receiver_or_receivers` AS `receiver_or_receivers`, `tb_brk_transaction`.`receiver_1` AS `receiver_1`, `tb_brk_transaction`.`receiver_2` AS `receiver_2`, `tb_brk_transaction`.`receiver_3` AS `receiver_3`, `tb_brk_transaction`.`receiver_central_reserve` AS `receiver_central_reserve`, `tb_brk_transaction`.`sender_central_reserve` AS `sender_central_reserve`, `tb_brk_transaction`.`ecobrick_serial_no` AS `ecobrick_serial_no`, `tb_brk_transaction`.`tran_sender_note` AS `tran_sender_note`, `tb_brk_transaction`.`product` AS `product`, `tb_brk_transaction`.`send_dt` AS `send_dt`, `tb_brk_transaction`.`accomp_payment` AS `accomp_payment`, `tb_brk_transaction`.`authenticator_version` AS `authenticator_version`, `tb_brk_transaction`.`expense_type` AS `expense_type`, `tb_brk_transaction`.`gea_accounting_category` AS `gea_accounting_category`, `tb_brk_transaction`.`shipping_cost_brk` AS `shipping_cost_brk`, `tb_brk_transaction`.`product_cost_brk` AS `product_cost_brk`, `tb_brk_transaction`.`total_cost_incl_shipping` AS `total_cost_incl_shipping`, `tb_brk_transaction`.`shipping_with_currency` AS `shipping_with_currency`, `tb_brk_transaction`.`aes_officially_purchased` AS `aes_officially_purchased`, `tb_brk_transaction`.`country_of_buyer` AS `country_of_buyer`, `tb_brk_transaction`.`currency_for_shipping` AS `currency_for_shipping`, `tb_brk_transaction`.`credit_other_ecobricker_yn` AS `credit_other_ecobricker_yn`, `tb_brk_transaction`.`catalyst_name` AS `catalyst_name` FROM `tb_brk_transaction` ORDER BY `tb_brk_transaction`.`chain_ledger_id` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_brk_tran_ledgerid_desc`
--
DROP TABLE IF EXISTS `vw_brk_tran_ledgerid_desc`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_brk_tran_ledgerid_desc`  AS SELECT `tb_brk_transaction`.`chain_ledger_id` AS `chain_ledger_id`, `tb_brk_transaction`.`tran_id` AS `tran_id`, `tb_brk_transaction`.`tran_name` AS `tran_name`, `tb_brk_transaction`.`individual_amt` AS `individual_amt`, `tb_brk_transaction`.`status` AS `status`, `tb_brk_transaction`.`send_ts` AS `send_ts`, `tb_brk_transaction`.`sender_ecobricker` AS `sender_ecobricker`, `tb_brk_transaction`.`block_tran_type` AS `block_tran_type`, `tb_brk_transaction`.`block_amt` AS `block_amt`, `tb_brk_transaction`.`sender` AS `sender`, `tb_brk_transaction`.`receiver_or_receivers` AS `receiver_or_receivers`, `tb_brk_transaction`.`receiver_1` AS `receiver_1`, `tb_brk_transaction`.`receiver_2` AS `receiver_2`, `tb_brk_transaction`.`receiver_3` AS `receiver_3`, `tb_brk_transaction`.`receiver_central_reserve` AS `receiver_central_reserve`, `tb_brk_transaction`.`sender_central_reserve` AS `sender_central_reserve`, `tb_brk_transaction`.`ecobrick_serial_no` AS `ecobrick_serial_no`, `tb_brk_transaction`.`tran_sender_note` AS `tran_sender_note`, `tb_brk_transaction`.`product` AS `product`, `tb_brk_transaction`.`send_dt` AS `send_dt`, `tb_brk_transaction`.`accomp_payment` AS `accomp_payment`, `tb_brk_transaction`.`authenticator_version` AS `authenticator_version`, `tb_brk_transaction`.`expense_type` AS `expense_type`, `tb_brk_transaction`.`gea_accounting_category` AS `gea_accounting_category`, `tb_brk_transaction`.`shipping_cost_brk` AS `shipping_cost_brk`, `tb_brk_transaction`.`product_cost_brk` AS `product_cost_brk`, `tb_brk_transaction`.`total_cost_incl_shipping` AS `total_cost_incl_shipping`, `tb_brk_transaction`.`shipping_with_currency` AS `shipping_with_currency`, `tb_brk_transaction`.`aes_officially_purchased` AS `aes_officially_purchased`, `tb_brk_transaction`.`country_of_buyer` AS `country_of_buyer`, `tb_brk_transaction`.`currency_for_shipping` AS `currency_for_shipping`, `tb_brk_transaction`.`credit_other_ecobricker_yn` AS `credit_other_ecobricker_yn`, `tb_brk_transaction`.`catalyst_name` AS `catalyst_name` FROM `tb_brk_transaction` ORDER BY `tb_brk_transaction`.`chain_ledger_id` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_calc_weight_by_year`
--
DROP TABLE IF EXISTS `vw_calc_weight_by_year`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_calc_weight_by_year`  AS SELECT year(`tb_brk_transaction`.`send_dt`) AS `year`, sum(`tb_brk_transaction`.`block_amt` / 10) AS `calculated_weight_total_kg` FROM `tb_brk_transaction` WHERE `tb_brk_transaction`.`tran_name` like '%for a validation disbursement%' AND `tb_brk_transaction`.`block_tran_type` like '%Coins issued for authentication%' OR `tb_brk_transaction`.`sender` like '%BrikCoin Generator%' AND `tb_brk_transaction`.`block_tran_type` like '%BrikCoins Generation%' OR `tb_brk_transaction`.`tran_name` like '%BrikCoins Created%' AND `tb_brk_transaction`.`sender` like '%BrikCoin Generator%' GROUP BY year(`tb_brk_transaction`.`send_dt`) ORDER BY year(`tb_brk_transaction`.`send_dt`) ASC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_cash_tran_asc`
--
DROP TABLE IF EXISTS `vw_cash_tran_asc`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_cash_tran_asc`  AS SELECT `tb_cash_transaction`.`knack_record_id` AS `knack_record_id`, `tb_cash_transaction`.`cash_tran_id` AS `cash_tran_id`, `tb_cash_transaction`.`sender_ecobricker` AS `sender_ecobricker`, `tb_cash_transaction`.`datetime_sent_ts` AS `datetime_sent_ts`, `tb_cash_transaction`.`type_of_transaction` AS `type_of_transaction`, `tb_cash_transaction`.`tran_name_desc` AS `tran_name_desc`, `tb_cash_transaction`.`native_ccy_amt` AS `native_ccy_amt`, `tb_cash_transaction`.`currency_code` AS `currency_code`, `tb_cash_transaction`.`native_ccy_amt_display` AS `native_ccy_amt_display`, `tb_cash_transaction`.`exchange_ratio` AS `exchange_ratio`, `tb_cash_transaction`.`usd_amount` AS `usd_amount`, `tb_cash_transaction`.`total_product_cost_incl_shipping` AS `total_product_cost_incl_shipping`, `tb_cash_transaction`.`product` AS `product`, `tb_cash_transaction`.`product_cost` AS `product_cost`, `tb_cash_transaction`.`transaction_date_dt` AS `transaction_date_dt`, `tb_cash_transaction`.`shipping_cost` AS `shipping_cost`, `tb_cash_transaction`.`shipping_cost_+ccy_display` AS `shipping_cost_+ccy_display`, `tb_cash_transaction`.`total_product_cost_+ccy_display` AS `total_product_cost_+ccy_display`, `tb_cash_transaction`.`receiving_gea_acct` AS `receiving_gea_acct`, `tb_cash_transaction`.`sender_for_display` AS `sender_for_display`, `tb_cash_transaction`.`receiver_for_display` AS `receiver_for_display`, `tb_cash_transaction`.`receiver_gea_account` AS `receiver_gea_account`, `tb_cash_transaction`.`expense_vendor` AS `expense_vendor`, `tb_cash_transaction`.`purchase_method` AS `purchase_method`, `tb_cash_transaction`.`recurring_trans_period` AS `recurring_trans_period`, `tb_cash_transaction`.`expense_accounting_type` AS `expense_accounting_type`, `tb_cash_transaction`.`revenue_accounting_type` AS `revenue_accounting_type`, `tb_cash_transaction`.`tran_processor` AS `tran_processor`, `tb_cash_transaction`.`connected_brk_tran_name` AS `connected_brk_tran_name`, `tb_cash_transaction`.`aes_to_usd_rate` AS `aes_to_usd_rate`, `tb_cash_transaction`.`aes_plastic_offset_purchase_kg` AS `aes_plastic_offset_purchase_kg`, `tb_cash_transaction`.`usd_payment_for_aes` AS `usd_payment_for_aes`, `tb_cash_transaction`.`gbp_payment_for_aes` AS `gbp_payment_for_aes`, `tb_cash_transaction`.`native_conversion_of_aes` AS `native_conversion_of_aes`, `tb_cash_transaction`.`brk_cost_of_aes_display` AS `brk_cost_of_aes_display`, `tb_cash_transaction`.`credited_catalyst` AS `credited_catalyst`, `tb_cash_transaction`.`brikcoins_purchased_display` AS `brikcoins_purchased_display`, `tb_cash_transaction`.`usd_paid_for_brk_+ccy_display` AS `usd_paid_for_brk_+ccy_display`, `tb_cash_transaction`.`connected_brk_trans` AS `connected_brk_trans`, `tb_cash_transaction`.`paymt_record_url` AS `paymt_record_url` FROM `tb_cash_transaction` WHERE `tb_cash_transaction`.`type_of_transaction` like '%revenue%' ORDER BY `tb_cash_transaction`.`cash_tran_id` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_cash_tran_desc`
--
DROP TABLE IF EXISTS `vw_cash_tran_desc`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_cash_tran_desc`  AS SELECT `tb_cash_transaction`.`knack_record_id` AS `knack_record_id`, `tb_cash_transaction`.`cash_tran_id` AS `cash_tran_id`, `tb_cash_transaction`.`sender_ecobricker` AS `sender_ecobricker`, `tb_cash_transaction`.`datetime_sent_ts` AS `datetime_sent_ts`, `tb_cash_transaction`.`type_of_transaction` AS `type_of_transaction`, `tb_cash_transaction`.`tran_name_desc` AS `tran_name_desc`, `tb_cash_transaction`.`native_ccy_amt` AS `native_ccy_amt`, `tb_cash_transaction`.`currency_code` AS `currency_code`, `tb_cash_transaction`.`native_ccy_amt_display` AS `native_ccy_amt_display`, `tb_cash_transaction`.`exchange_ratio` AS `exchange_ratio`, `tb_cash_transaction`.`usd_amount` AS `usd_amount`, `tb_cash_transaction`.`total_product_cost_incl_shipping` AS `total_product_cost_incl_shipping`, `tb_cash_transaction`.`product` AS `product`, `tb_cash_transaction`.`product_cost` AS `product_cost`, `tb_cash_transaction`.`transaction_date_dt` AS `transaction_date_dt`, `tb_cash_transaction`.`shipping_cost` AS `shipping_cost`, `tb_cash_transaction`.`shipping_cost_+ccy_display` AS `shipping_cost_+ccy_display`, `tb_cash_transaction`.`total_product_cost_+ccy_display` AS `total_product_cost_+ccy_display`, `tb_cash_transaction`.`receiving_gea_acct` AS `receiving_gea_acct`, `tb_cash_transaction`.`sender_for_display` AS `sender_for_display`, `tb_cash_transaction`.`receiver_for_display` AS `receiver_for_display`, `tb_cash_transaction`.`receiver_gea_account` AS `receiver_gea_account`, `tb_cash_transaction`.`expense_vendor` AS `expense_vendor`, `tb_cash_transaction`.`purchase_method` AS `purchase_method`, `tb_cash_transaction`.`recurring_trans_period` AS `recurring_trans_period`, `tb_cash_transaction`.`expense_accounting_type` AS `expense_accounting_type`, `tb_cash_transaction`.`revenue_accounting_type` AS `revenue_accounting_type`, `tb_cash_transaction`.`tran_processor` AS `tran_processor`, `tb_cash_transaction`.`connected_brk_tran_name` AS `connected_brk_tran_name`, `tb_cash_transaction`.`aes_to_usd_rate` AS `aes_to_usd_rate`, `tb_cash_transaction`.`aes_plastic_offset_purchase_kg` AS `aes_plastic_offset_purchase_kg`, `tb_cash_transaction`.`usd_payment_for_aes` AS `usd_payment_for_aes`, `tb_cash_transaction`.`gbp_payment_for_aes` AS `gbp_payment_for_aes`, `tb_cash_transaction`.`native_conversion_of_aes` AS `native_conversion_of_aes`, `tb_cash_transaction`.`brk_cost_of_aes_display` AS `brk_cost_of_aes_display`, `tb_cash_transaction`.`credited_catalyst` AS `credited_catalyst`, `tb_cash_transaction`.`brikcoins_purchased_display` AS `brikcoins_purchased_display`, `tb_cash_transaction`.`usd_paid_for_brk_+ccy_display` AS `usd_paid_for_brk_+ccy_display`, `tb_cash_transaction`.`connected_brk_trans` AS `connected_brk_trans`, `tb_cash_transaction`.`paymt_record_url` AS `paymt_record_url` FROM `tb_cash_transaction` WHERE `tb_cash_transaction`.`type_of_transaction` like '%xpense%' ORDER BY `tb_cash_transaction`.`cash_tran_id` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_detail_sums_by_year`
--
DROP TABLE IF EXISTS `vw_detail_sums_by_year`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_detail_sums_by_year`  AS SELECT year(`tb_cash_transaction`.`transaction_date_dt`) AS `year`, min(`tb_cash_transaction`.`transaction_date_dt`) AS `from_date`, max(`tb_cash_transaction`.`transaction_date_dt`) AS `to_date`, count(0) AS `total_no_of_exp_transactions`, format(sum(`tb_cash_transaction`.`usd_amount`),2) AS `total_exp_usd_amount`, format(sum(`tb_cash_transaction`.`idr_amount`),0) AS `total_exp_idr_amount`, sum(`tb_cash_transaction`.`usd_amount`) AS `raw_usd_amt`, sum(`tb_cash_transaction`.`idr_amount`) AS `raw_idr_amt` FROM `tb_cash_transaction` WHERE `tb_cash_transaction`.`type_of_transaction` like '%xpense%' AND `tb_cash_transaction`.`type_of_transaction` not like '%unpaid%' GROUP BY year(`tb_cash_transaction`.`transaction_date_dt`) ;

-- --------------------------------------------------------

--
-- Structure for view `vw_detail_sums_by_year_idr`
--
DROP TABLE IF EXISTS `vw_detail_sums_by_year_idr`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_detail_sums_by_year_idr`  AS SELECT `b`.`year` AS `year`, `b`.`from_date` AS `from_date`, `b`.`to_date` AS `to_date`, `b`.`total_brk` AS `total_brk`, `c`.`brick_count` AS `brick_count`, `w`.`weight` AS `weight`, `e`.`total_exp_idr_amount` AS `tot_idr_exp_amt`, `r`.`total_rev_idr_amount` AS `tot_idr_rev_amt`, format(`k`.`calculated_weight_total_kg`,2) AS `calculated_weight`, format(`e`.`raw_idr_amt` / `k`.`calculated_weight_total_kg`,0) AS `final_aes_plastic_cost_idr` FROM (`vw_brk_by_year` `b` left join ((((`vw_ecobricks_count_by_year` `c` join `vw_weight_by_year` `w`) join `vw_tot_exp_by_year` `e`) join `vw_tot_rev_by_year` `r`) join `vw_calc_weight_by_year` `k`) on(`b`.`year` = `c`.`year` and `b`.`year` = `w`.`year` and `b`.`year` = `e`.`year` and `b`.`year` = `r`.`year` and `b`.`year` = `k`.`year`)) GROUP BY `b`.`year` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_ecobricks_count_by_year`
--
DROP TABLE IF EXISTS `vw_ecobricks_count_by_year`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_ecobricks_count_by_year`  AS SELECT count(`tb_ecobricks`.`serial_no`) AS `brick_count`, year(`tb_ecobricks`.`last_validation_ts`) AS `year` FROM `tb_ecobricks` GROUP BY year(`tb_ecobricks`.`last_validation_ts`) ;

-- --------------------------------------------------------

--
-- Structure for view `vw_ecobricks_desc`
--
DROP TABLE IF EXISTS `vw_ecobricks_desc`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_ecobricks_desc`  AS SELECT `tb_ecobricks`.`knack_record_id` AS `knack_record_id`, `tb_ecobricks`.`ecobrick_unique_id` AS `ecobrick_unique_id`, `tb_ecobricks`.`serial_no` AS `serial_no`, `tb_ecobricks`.`owner` AS `owner`, `tb_ecobricks`.`ecobricker_maker` AS `ecobricker_maker`, `tb_ecobricks`.`ecobrick_thumb_photo_url` AS `ecobrick_thumb_photo_url`, `tb_ecobricks`.`ecobrick_full_photo_url` AS `ecobrick_full_photo_url`, `tb_ecobricks`.`selfie_photo_url` AS `selfie_photo_url`, `tb_ecobricks`.`volume_ml` AS `volume_ml`, `tb_ecobricks`.`universal_volume_ml` AS `universal_volume_ml`, `tb_ecobricks`.`weight_g` AS `weight_g`, `tb_ecobricks`.`density` AS `density`, `tb_ecobricks`.`date_logged_ts` AS `date_logged_ts`, `tb_ecobricks`.`CO2_kg` AS `CO2_kg`, `tb_ecobricks`.`sequestration_type` AS `sequestration_type`, `tb_ecobricks`.`last_validation_ts` AS `last_validation_ts`, `tb_ecobricks`.`validator_1` AS `validator_1`, `tb_ecobricks`.`validator_2` AS `validator_2`, `tb_ecobricks`.`validator_3` AS `validator_3`, `tb_ecobricks`.`validation_score_avg` AS `validation_score_avg`, `tb_ecobricks`.`final_validation_score` AS `final_validation_score`, `tb_ecobricks`.`vision` AS `vision`, `tb_ecobricks`.`last_ownership_change` AS `last_ownership_change`, `tb_ecobricks`.`non_registered_maker_name` AS `non_registered_maker_name`, `tb_ecobricks`.`actual_maker_name` AS `actual_maker_name`, `tb_ecobricks`.`weight_authenticated_kg` AS `weight_authenticated_kg`, `tb_ecobricks`.`location_country` AS `location_country`, `tb_ecobricks`.`location_region` AS `location_region`, `tb_ecobricks`.`location_city` AS `location_city`, `tb_ecobricks`.`location_full` AS `location_full`, `tb_ecobricks`.`community_name` AS `community_name`, `tb_ecobricks`.`brand_name` AS `brand_name`, `tb_ecobricks`.`bottom_colour` AS `bottom_colour`, `tb_ecobricks`.`plastic_from` AS `plastic_from`, `tb_ecobricks`.`ecobrick_brk_display_value` AS `ecobrick_brk_display_value`, `tb_ecobricks`.`ecobrick_dec_brk_val` AS `ecobrick_dec_brk_val`, `tb_ecobricks`.`ecobrick_brk_amt` AS `ecobrick_brk_amt` FROM `tb_ecobricks` ORDER BY `tb_ecobricks`.`ecobrick_unique_id` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_ecobrick_tran_desc`
--
DROP TABLE IF EXISTS `vw_ecobrick_tran_desc`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_ecobrick_tran_desc`  AS SELECT `e`.`ecobrick_unique_id` AS `ecobrick_unique_id`, `e`.`serial_no` AS `serial_no`, `e`.`owner` AS `owner`, `e`.`ecobricker_maker` AS `ecobricker_maker`, `e`.`ecobrick_thumb_photo_url` AS `ecobrick_thumb_photo_url`, `e`.`ecobrick_full_photo_url` AS `ecobrick_full_photo_url`, `e`.`selfie_photo_url` AS `selfie_photo_url`, `e`.`volume_ml` AS `volume_ml`, `e`.`universal_volume_ml` AS `universal_volume_ml`, `e`.`weight_g` AS `weight_g`, `e`.`density` AS `density`, `e`.`date_logged_ts` AS `date_logged_ts`, `e`.`CO2_kg` AS `CO2_kg`, `e`.`sequestration_type` AS `sequestration_type`, `e`.`last_validation_ts` AS `last_validation_ts`, `e`.`validator_1` AS `validator_1`, `e`.`validator_2` AS `validator_2`, `e`.`validator_3` AS `validator_3`, `e`.`validation_score_avg` AS `validation_score_avg`, `e`.`final_validation_score` AS `final_validation_score`, `e`.`vision` AS `vision`, `e`.`last_ownership_change` AS `last_ownership_change`, `e`.`non_registered_maker_name` AS `non_registered_maker_name`, `e`.`actual_maker_name` AS `actual_maker_name`, `e`.`weight_authenticated_kg` AS `weight_authenticated_kg`, `e`.`location_country` AS `location_country`, `e`.`location_region` AS `location_region`, `e`.`location_city` AS `location_city`, `e`.`location_full` AS `location_full`, `e`.`community_name` AS `community_name`, `e`.`brand_name` AS `brand_name`, `e`.`bottom_colour` AS `bottom_colour`, `e`.`plastic_from` AS `plastic_from`, `e`.`ecobrick_brk_display_value` AS `ecobrick_brk_display_value`, `e`.`ecobrick_dec_brk_val` AS `ecobrick_dec_brk_val`, `e`.`ecobrick_brk_amt` AS `ecobrick_brk_amt`, `t`.`receiver_or_receivers` AS `brk_beneficiaries`, `t`.`individual_amt` AS `individual_brk_allocated` FROM (`tb_ecobricks` `e` left join `tb_brk_transaction` `t` on(`e`.`ecobrick_unique_id` = `t`.`ecobrick_serial_no`)) ORDER BY `e`.`ecobrick_unique_id` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_exp_by_year_category`
--
DROP TABLE IF EXISTS `vw_exp_by_year_category`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_exp_by_year_category`  AS SELECT year(`tb_cash_transaction`.`transaction_date_dt`) AS `year`, `tb_cash_transaction`.`expense_accounting_type` AS `expense_category`, count(0) AS `no_of_transactions`, sum(`tb_cash_transaction`.`usd_amount`) AS `total_usd`, sum(`tb_cash_transaction`.`idr_amount`) AS `total_idr` FROM `tb_cash_transaction` WHERE `tb_cash_transaction`.`type_of_transaction` like '%xpense%' GROUP BY year(`tb_cash_transaction`.`transaction_date_dt`), `tb_cash_transaction`.`expense_accounting_type` ORDER BY year(`tb_cash_transaction`.`transaction_date_dt`) ASC, `tb_cash_transaction`.`expense_accounting_type` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_exp_cash_tran_desc`
--
DROP TABLE IF EXISTS `vw_exp_cash_tran_desc`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_exp_cash_tran_desc`  AS SELECT `tb_cash_transaction`.`knack_record_id` AS `knack_record_id`, `tb_cash_transaction`.`cash_tran_id` AS `cash_tran_id`, `tb_cash_transaction`.`sender_ecobricker` AS `sender_ecobricker`, `tb_cash_transaction`.`datetime_sent_ts` AS `datetime_sent_ts`, `tb_cash_transaction`.`type_of_transaction` AS `type_of_transaction`, `tb_cash_transaction`.`tran_name_desc` AS `tran_name_desc`, `tb_cash_transaction`.`native_ccy_amt` AS `native_ccy_amt`, `tb_cash_transaction`.`currency_code` AS `currency_code`, `tb_cash_transaction`.`native_ccy_amt_display` AS `native_ccy_amt_display`, `tb_cash_transaction`.`exchange_ratio` AS `exchange_ratio`, `tb_cash_transaction`.`usd_amount` AS `usd_amount`, `tb_cash_transaction`.`total_product_cost_incl_shipping` AS `total_product_cost_incl_shipping`, `tb_cash_transaction`.`product` AS `product`, `tb_cash_transaction`.`product_cost` AS `product_cost`, `tb_cash_transaction`.`transaction_date_dt` AS `transaction_date_dt`, `tb_cash_transaction`.`shipping_cost` AS `shipping_cost`, `tb_cash_transaction`.`shipping_cost_+ccy_display` AS `shipping_cost_+ccy_display`, `tb_cash_transaction`.`total_product_cost_+ccy_display` AS `total_product_cost_+ccy_display`, `tb_cash_transaction`.`receiving_gea_acct` AS `receiving_gea_acct`, `tb_cash_transaction`.`sender_for_display` AS `sender_for_display`, `tb_cash_transaction`.`receiver_for_display` AS `receiver_for_display`, `tb_cash_transaction`.`receiver_gea_account` AS `receiver_gea_account`, `tb_cash_transaction`.`expense_vendor` AS `expense_vendor`, `tb_cash_transaction`.`purchase_method` AS `purchase_method`, `tb_cash_transaction`.`recurring_trans_period` AS `recurring_trans_period`, `tb_cash_transaction`.`expense_accounting_type` AS `expense_accounting_type`, `tb_cash_transaction`.`revenue_accounting_type` AS `revenue_accounting_type`, `tb_cash_transaction`.`tran_processor` AS `tran_processor`, `tb_cash_transaction`.`connected_brk_tran_name` AS `connected_brk_tran_name`, `tb_cash_transaction`.`aes_to_usd_rate` AS `aes_to_usd_rate`, `tb_cash_transaction`.`aes_plastic_offset_purchase_kg` AS `aes_plastic_offset_purchase_kg`, `tb_cash_transaction`.`usd_payment_for_aes` AS `usd_payment_for_aes`, `tb_cash_transaction`.`gbp_payment_for_aes` AS `gbp_payment_for_aes`, `tb_cash_transaction`.`native_conversion_of_aes` AS `native_conversion_of_aes`, `tb_cash_transaction`.`brk_cost_of_aes_display` AS `brk_cost_of_aes_display`, `tb_cash_transaction`.`credited_catalyst` AS `credited_catalyst`, `tb_cash_transaction`.`brikcoins_purchased_display` AS `brikcoins_purchased_display`, `tb_cash_transaction`.`usd_paid_for_brk_+ccy_display` AS `usd_paid_for_brk_+ccy_display`, `tb_cash_transaction`.`connected_brk_trans` AS `connected_brk_trans`, `tb_cash_transaction`.`paymt_record_url` AS `paymt_record_url` FROM `tb_cash_transaction` WHERE `tb_cash_transaction`.`type_of_transaction` like '%xpense%' ORDER BY `tb_cash_transaction`.`cash_tran_id` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_gallery_feed`
--
DROP TABLE IF EXISTS `vw_gallery_feed`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_gallery_feed`  AS SELECT `tb_ecobricks`.`ecobrick_unique_id` AS `ecobrick_unique_id`, `tb_ecobricks`.`final_validation_score` AS `final_validation_score`, `tb_ecobricks`.`owner` AS `ecobrick_owner`, `tb_ecobricks`.`location_full` AS `location`, `tb_ecobricks`.`ecobrick_brk_amt` AS `ecobrick_brk_amt`, `tb_ecobricks`.`CO2_kg` AS `CO2_kg`, `tb_ecobricks`.`weight_g` AS `weight_in_g`, format(`tb_ecobricks`.`density`,2) AS `density`, ifnull(`tb_ecobricks`.`selfie_photo_url`,`tb_ecobricks`.`ecobrick_full_photo_url`) AS `photo_url`, ifnull(`tb_ecobricks`.`selfie_thumb_url`,`tb_ecobricks`.`ecobrick_thumb_photo_url`) AS `thumb_url` FROM (`tb_ecobricks` join `validations_tb`) WHERE `tb_ecobricks`.`ecobrick_unique_id` = `validations_tb`.`recorded_serial` AND `validations_tb`.`star_rating` = 5 AND `tb_ecobricks`.`status` = 'authenticated' ORDER BY `tb_ecobricks`.`ecobrick_unique_id` DESC LIMIT 0, 20 ;

-- --------------------------------------------------------

--
-- Structure for view `vw_rev_by_year_category`
--
DROP TABLE IF EXISTS `vw_rev_by_year_category`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_rev_by_year_category`  AS SELECT year(`tb_cash_transaction`.`transaction_date_dt`) AS `year`, `tb_cash_transaction`.`revenue_accounting_type` AS `revenue_category`, count(0) AS `no_of_transactions`, sum(`tb_cash_transaction`.`usd_amount`) AS `total_usd`, sum(`tb_cash_transaction`.`idr_amount`) AS `total_idr` FROM `tb_cash_transaction` WHERE `tb_cash_transaction`.`type_of_transaction` like '%evenue%' GROUP BY year(`tb_cash_transaction`.`transaction_date_dt`), `tb_cash_transaction`.`revenue_accounting_type` ORDER BY year(`tb_cash_transaction`.`transaction_date_dt`) ASC, `tb_cash_transaction`.`revenue_accounting_type` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_rev_cash_tran_desc`
--
DROP TABLE IF EXISTS `vw_rev_cash_tran_desc`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_rev_cash_tran_desc`  AS SELECT `tb_cash_transaction`.`knack_record_id` AS `knack_record_id`, `tb_cash_transaction`.`cash_tran_id` AS `cash_tran_id`, `tb_cash_transaction`.`sender_ecobricker` AS `sender_ecobricker`, `tb_cash_transaction`.`datetime_sent_ts` AS `datetime_sent_ts`, `tb_cash_transaction`.`type_of_transaction` AS `type_of_transaction`, `tb_cash_transaction`.`tran_name_desc` AS `tran_name_desc`, `tb_cash_transaction`.`native_ccy_amt` AS `native_ccy_amt`, `tb_cash_transaction`.`currency_code` AS `currency_code`, `tb_cash_transaction`.`native_ccy_amt_display` AS `native_ccy_amt_display`, `tb_cash_transaction`.`exchange_ratio` AS `exchange_ratio`, `tb_cash_transaction`.`usd_amount` AS `usd_amount`, `tb_cash_transaction`.`total_product_cost_incl_shipping` AS `total_product_cost_incl_shipping`, `tb_cash_transaction`.`product` AS `product`, `tb_cash_transaction`.`product_cost` AS `product_cost`, `tb_cash_transaction`.`transaction_date_dt` AS `transaction_date_dt`, `tb_cash_transaction`.`shipping_cost` AS `shipping_cost`, `tb_cash_transaction`.`shipping_cost_+ccy_display` AS `shipping_cost_+ccy_display`, `tb_cash_transaction`.`total_product_cost_+ccy_display` AS `total_product_cost_+ccy_display`, `tb_cash_transaction`.`receiving_gea_acct` AS `receiving_gea_acct`, `tb_cash_transaction`.`sender_for_display` AS `sender_for_display`, `tb_cash_transaction`.`receiver_for_display` AS `receiver_for_display`, `tb_cash_transaction`.`receiver_gea_account` AS `receiver_gea_account`, `tb_cash_transaction`.`expense_vendor` AS `expense_vendor`, `tb_cash_transaction`.`purchase_method` AS `purchase_method`, `tb_cash_transaction`.`recurring_trans_period` AS `recurring_trans_period`, `tb_cash_transaction`.`expense_accounting_type` AS `expense_accounting_type`, `tb_cash_transaction`.`revenue_accounting_type` AS `revenue_accounting_type`, `tb_cash_transaction`.`tran_processor` AS `tran_processor`, `tb_cash_transaction`.`connected_brk_tran_name` AS `connected_brk_tran_name`, `tb_cash_transaction`.`aes_to_usd_rate` AS `aes_to_usd_rate`, `tb_cash_transaction`.`aes_plastic_offset_purchase_kg` AS `aes_plastic_offset_purchase_kg`, `tb_cash_transaction`.`usd_payment_for_aes` AS `usd_payment_for_aes`, `tb_cash_transaction`.`gbp_payment_for_aes` AS `gbp_payment_for_aes`, `tb_cash_transaction`.`native_conversion_of_aes` AS `native_conversion_of_aes`, `tb_cash_transaction`.`brk_cost_of_aes_display` AS `brk_cost_of_aes_display`, `tb_cash_transaction`.`credited_catalyst` AS `credited_catalyst`, `tb_cash_transaction`.`brikcoins_purchased_display` AS `brikcoins_purchased_display`, `tb_cash_transaction`.`usd_paid_for_brk_+ccy_display` AS `usd_paid_for_brk_+ccy_display`, `tb_cash_transaction`.`connected_brk_trans` AS `connected_brk_trans`, `tb_cash_transaction`.`paymt_record_url` AS `paymt_record_url` FROM `tb_cash_transaction` WHERE `tb_cash_transaction`.`type_of_transaction` like '%evenue%' ORDER BY `tb_cash_transaction`.`cash_tran_id` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_sum_aes_brk`
--
DROP TABLE IF EXISTS `vw_sum_aes_brk`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_sum_aes_brk`  AS SELECT sum(`tb_brk_transaction`.`individual_amt`) AS `total_aes_brk` FROM `tb_brk_transaction` WHERE `tb_brk_transaction`.`block_tran_type` = 'AES Offset Purchase with BRK' ;

-- --------------------------------------------------------

--
-- Structure for view `vw_sum_brk_total`
--
DROP TABLE IF EXISTS `vw_sum_brk_total`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_sum_brk_total`  AS SELECT min(`t`.`send_dt`) AS `from_date`, max(`t`.`send_dt`) AS `to_date`, format(sum(`t`.`block_amt`),2) AS `total_brk`, format(sum(case when `t`.`receiver_or_receivers` = 'Brikcoins Destroyed' then `t`.`individual_amt` else 0 end),2) AS `aes_purchased`, format(sum(`t`.`block_amt` - case when `t`.`receiver_or_receivers` = 'Brikcoins Destroyed' then `t`.`individual_amt` else 0 end),2) AS `net_brk_in_circulation`, format(sum(case when `t`.`tran_name` like '%for a validation disbursement%' and `t`.`block_tran_type` like '%Coins issued for authentication%' or `t`.`sender` like '%BrikCoin Generator%' and `t`.`block_tran_type` like '%BrikCoins Generation%' or `t`.`tran_name` like '%BrikCoins Created%' and `t`.`sender` like '%BrikCoin Generator%' then `t`.`block_amt` / 10 else 0 end),2) AS `total_weight_of_plastic`, format((select sum(`vw_calc_weight_by_year`.`calculated_weight_total_kg`) from `vw_calc_weight_by_year`) * 1000 / sum(`t`.`block_amt` - case when `t`.`receiver_or_receivers` = 'Brikcoins Destroyed' then `t`.`individual_amt` else 0 end),2) AS `plastic_value_g_per_brk` FROM `tb_brk_transaction` AS `t` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_sum_cash_aes`
--
DROP TABLE IF EXISTS `vw_sum_cash_aes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_sum_cash_aes`  AS SELECT sum(`tb_brk_transaction`.`individual_amt`) AS `total_cash_aes_brk` FROM `tb_brk_transaction` WHERE `tb_brk_transaction`.`block_tran_type` = 'AES Offset Purchase with USD' ;

-- --------------------------------------------------------

--
-- Structure for view `vw_top_10_last_month`
--
DROP TABLE IF EXISTS `vw_top_10_last_month`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_top_10_last_month`  AS SELECT monthname(curdate() + interval -1 month) AS `month`, `tb_ecobricks`.`ecobrick_unique_id` AS `ecobrick_unique_id`, `tb_ecobricks`.`final_validation_score` AS `final_validation_score`, `tb_ecobricks`.`owner` AS `ecobrick_owner`, `tb_ecobricks`.`location_full` AS `location`, `tb_ecobricks`.`weight_g` AS `weight_in_g`, `tb_ecobricks`.`density` AS `density`, `tb_ecobricks`.`ecobrick_full_photo_url` AS `ecobrick_full_photo_url`, `tb_ecobricks`.`ecobrick_thumb_photo_url` AS `ecobrick_thumb_photo_url`, `tb_ecobricks`.`selfie_photo_url` AS `selfie_photo_url` FROM `tb_ecobricks` WHERE month(`tb_ecobricks`.`last_validation_ts`) = month(curdate() + interval -1 month) ORDER BY `tb_ecobricks`.`final_validation_score` DESC LIMIT 0, 10 ;

-- --------------------------------------------------------

--
-- Structure for view `vw_tot_exp_by_year`
--
DROP TABLE IF EXISTS `vw_tot_exp_by_year`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_tot_exp_by_year`  AS SELECT year(`tb_cash_transaction`.`transaction_date_dt`) AS `year`, min(`tb_cash_transaction`.`transaction_date_dt`) AS `from_date`, max(`tb_cash_transaction`.`transaction_date_dt`) AS `to_date`, count(0) AS `total_no_of_exp_transactions`, format(sum(`tb_cash_transaction`.`usd_amount`),2) AS `total_exp_usd_amount`, format(sum(`tb_cash_transaction`.`idr_amount`),0) AS `total_exp_idr_amount`, sum(`tb_cash_transaction`.`usd_amount`) AS `raw_usd_amt`, sum(`tb_cash_transaction`.`idr_amount`) AS `raw_idr_amt` FROM `tb_cash_transaction` WHERE `tb_cash_transaction`.`type_of_transaction` like '%xpense%' AND `tb_cash_transaction`.`type_of_transaction` not like '%unpaid%' GROUP BY year(`tb_cash_transaction`.`transaction_date_dt`) ;

-- --------------------------------------------------------

--
-- Structure for view `vw_tot_rev_by_year`
--
DROP TABLE IF EXISTS `vw_tot_rev_by_year`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_tot_rev_by_year`  AS SELECT year(`tb_cash_transaction`.`transaction_date_dt`) AS `year`, min(`tb_cash_transaction`.`transaction_date_dt`) AS `from_date`, max(`tb_cash_transaction`.`transaction_date_dt`) AS `to_date`, count(0) AS `total_no_of_rev_transactions`, format(sum(`tb_cash_transaction`.`usd_amount`),2) AS `total_rev_usd_amount`, format(sum(`tb_cash_transaction`.`idr_amount`),0) AS `total_rev_idr_amount` FROM `tb_cash_transaction` WHERE `tb_cash_transaction`.`type_of_transaction` like '%evenue%' GROUP BY year(`tb_cash_transaction`.`transaction_date_dt`) ;

-- --------------------------------------------------------

--
-- Structure for view `vw_weight_by_year`
--
DROP TABLE IF EXISTS `vw_weight_by_year`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ecobricks`@`localhost` SQL SECURITY DEFINER VIEW `vw_weight_by_year`  AS SELECT year(`tb_ecobricks`.`last_validation_ts`) AS `year`, cast(sum(`tb_ecobricks`.`weight_authenticated_kg`) as decimal(10,1)) AS `weight` FROM `tb_ecobricks` GROUP BY year(`tb_ecobricks`.`last_validation_ts`) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `countries_tb`
--
ALTER TABLE `countries_tb`
  ADD CONSTRAINT `fk_continent_code` FOREIGN KEY (`continent_code`) REFERENCES `continents_tb` (`continent_code`);

--
-- Constraints for table `payment_items_tb`
--
ALTER TABLE `payment_items_tb`
  ADD CONSTRAINT `fk_items_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments_tb` (`payment_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_campaigns_tb`
--
ALTER TABLE `product_campaigns_tb`
  ADD CONSTRAINT `fk_campaign_product` FOREIGN KEY (`product_id`) REFERENCES `products_tb` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_orders_tb`
--
ALTER TABLE `product_orders_tb`
  ADD CONSTRAINT `fk_product_order_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `product_campaigns_tb` (`campaign_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_product_order_pledge` FOREIGN KEY (`pledge_id`) REFERENCES `product_pledges_tb` (`pledge_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_product_order_product` FOREIGN KEY (`product_id`) REFERENCES `products_tb` (`product_id`);

--
-- Constraints for table `product_pledges_tb`
--
ALTER TABLE `product_pledges_tb`
  ADD CONSTRAINT `fk_product_pledge_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `product_campaigns_tb` (`campaign_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_product_pledge_product` FOREIGN KEY (`product_id`) REFERENCES `products_tb` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `tb_trainings`
--
ALTER TABLE `tb_trainings`
  ADD CONSTRAINT `fk_training_community` FOREIGN KEY (`community_id`) REFERENCES `communities_tbX` (`community_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_training_country` FOREIGN KEY (`country_id`) REFERENCES `countries_tb` (`country_id`);

--
-- Constraints for table `tb_training_trainees`
--
ALTER TABLE `tb_training_trainees`
  ADD CONSTRAINT `tb_training_trainees_ibfk_1` FOREIGN KEY (`training_id`) REFERENCES `tb_trainings` (`training_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tb_training_trainees_ibfk_2` FOREIGN KEY (`ecobricker_id`) REFERENCES `tb_ecobrickers` (`ecobricker_id`) ON DELETE CASCADE;

--
-- Constraints for table `tb_training_trainers`
--
ALTER TABLE `tb_training_trainers`
  ADD CONSTRAINT `tb_training_trainers_ibfk_1` FOREIGN KEY (`training_id`) REFERENCES `tb_trainings` (`training_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tb_training_trainers_ibfk_2` FOREIGN KEY (`ecobricker_id`) REFERENCES `tb_ecobrickers` (`ecobricker_id`) ON DELETE CASCADE;

--
-- Constraints for table `training_pledges_tb`
--
ALTER TABLE `training_pledges_tb`
  ADD CONSTRAINT `fk_training_pledge_training` FOREIGN KEY (`training_id`) REFERENCES `tb_trainings` (`training_id`) ON DELETE CASCADE;

--
-- Constraints for table `training_registrations_tb`
--
ALTER TABLE `training_registrations_tb`
  ADD CONSTRAINT `fk_reg_pledge` FOREIGN KEY (`pledge_id`) REFERENCES `training_pledges_tb` (`pledge_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_reg_training` FOREIGN KEY (`training_id`) REFERENCES `tb_trainings` (`training_id`);

--
-- Constraints for table `validations_tb`
--
ALTER TABLE `validations_tb`
  ADD CONSTRAINT `fk_validations_ecobricker` FOREIGN KEY (`ecobricker_id`) REFERENCES `tb_ecobrickers` (`ecobricker_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_validations_ecobricks` FOREIGN KEY (`recorded_serial`) REFERENCES `tb_ecobricks` (`serial_no`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
