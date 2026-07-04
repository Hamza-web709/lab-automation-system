-- ============================================================
-- Lab Automation System - Clean MySQL Database
-- Compatible with the current PHP project in lab-automation.zip
-- Database: lab_automation_db
--
-- WARNING:
-- Importing this file drops and recreates the application's tables.
-- Existing data in these tables will be deleted.
-- ============================================================

SET NAMES utf8mb4;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `lab_automation_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `lab_automation_db`;

-- Drop child tables before parent tables.
DROP TABLE IF EXISTS `test_persons`;
DROP TABLE IF EXISTS `testing_records`;
DROP TABLE IF EXISTS `testing_types`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `testing_departments`;
DROP TABLE IF EXISTS `product_types`;
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `users`;

-- ------------------------------------------------------------
-- Users and role-based authentication
-- ------------------------------------------------------------
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `username` VARCHAR(30) NOT NULL,
  `email` VARCHAR(160) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'lab_manager', 'tester') NOT NULL DEFAULT 'tester',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_role_status` (`role`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Product master data
-- ------------------------------------------------------------
CREATE TABLE `product_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `code` VARCHAR(10) NOT NULL,
  `description` TEXT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_product_types_code` (`code`),
  KEY `idx_product_types_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `testing_departments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `code` VARCHAR(10) NOT NULL,
  `description` TEXT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_testing_departments_code` (`code`),
  KEY `idx_testing_departments_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` VARCHAR(10) NOT NULL,
  `product_type_id` INT UNSIGNED NOT NULL,
  `product_name` VARCHAR(160) NOT NULL,
  `product_code` VARCHAR(10) NOT NULL,
  `revise_no` VARCHAR(20) NOT NULL,
  `manufacturing_no` VARCHAR(40) NOT NULL,
  `batch_no` VARCHAR(80) NULL,
  `manufacturing_date` DATE NULL,
  `description` TEXT NULL,
  `current_status` ENUM(
    'manufactured',
    'under_testing',
    'passed_internal',
    'failed_internal',
    'sent_to_cpri',
    'approved',
    'sent_for_remaking'
  ) NOT NULL DEFAULT 'manufactured',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_products_product_id` (`product_id`),
  KEY `idx_products_status` (`current_status`),
  KEY `idx_products_type_status` (`product_type_id`, `current_status`),
  KEY `idx_products_search` (`product_id`, `product_name`, `product_code`),
  CONSTRAINT `fk_products_product_type`
    FOREIGN KEY (`product_type_id`) REFERENCES `product_types` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Testing definitions
-- ------------------------------------------------------------
CREATE TABLE `testing_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_id` INT UNSIGNED NOT NULL,
  `product_type_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(160) NOT NULL,
  `testing_code` VARCHAR(10) NOT NULL,
  `description` TEXT NULL,
  `expected_output` TEXT NULL,
  `criteria` TEXT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_testing_code_department` (`department_id`, `testing_code`),
  KEY `idx_testing_types_status` (`status`),
  KEY `idx_testing_types_lookup` (`department_id`, `product_type_id`),
  KEY `idx_testing_types_product_type` (`product_type_id`),
  CONSTRAINT `fk_testing_types_department`
    FOREIGN KEY (`department_id`) REFERENCES `testing_departments` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_testing_types_product_type`
    FOREIGN KEY (`product_type_id`) REFERENCES `product_types` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Product test executions
-- ------------------------------------------------------------
CREATE TABLE `testing_records` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `test_id` VARCHAR(12) NOT NULL,
  `product_id_ref` INT UNSIGNED NOT NULL,
  `testing_type_id` INT UNSIGNED NOT NULL,
  `department_id` INT UNSIGNED NOT NULL,
  `test_roll_no` VARCHAR(20) NOT NULL,
  `test_date` DATE NOT NULL,
  `criteria` TEXT NULL,
  `expected_output` TEXT NULL,
  `observed_output` TEXT NULL,
  `detailed_remarks` TEXT NULL,
  `result` ENUM('pass', 'fail', 'pending') NOT NULL DEFAULT 'pending',
  `status` ENUM(
    'pending',
    'in_progress',
    'completed',
    'sent_to_next_department',
    'sent_to_cpri',
    'sent_for_remaking'
  ) NOT NULL DEFAULT 'pending',
  `next_action` ENUM(
    'none',
    'next_test',
    'send_to_cpri',
    'send_for_remaking'
  ) NOT NULL DEFAULT 'none',
  `created_by` INT UNSIGNED NULL,
  `assigned_tester_id` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_testing_records_test_id` (`test_id`),
  KEY `idx_testing_records_result_status` (`result`, `status`),
  KEY `idx_testing_records_date` (`test_date`),
  KEY `idx_testing_records_product` (`product_id_ref`),
  KEY `idx_testing_records_testing_type` (`testing_type_id`),
  KEY `idx_testing_records_department` (`department_id`),
  KEY `idx_testing_records_created_by` (`created_by`),
  KEY `idx_testing_records_assigned_tester` (`assigned_tester_id`),
  CONSTRAINT `fk_testing_records_product`
    FOREIGN KEY (`product_id_ref`) REFERENCES `products` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_testing_records_testing_type`
    FOREIGN KEY (`testing_type_id`) REFERENCES `testing_types` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_testing_records_department`
    FOREIGN KEY (`department_id`) REFERENCES `testing_departments` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_testing_records_created_by`
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_testing_records_assigned_tester`
    FOREIGN KEY (`assigned_tester_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `test_persons` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `testing_record_id` INT UNSIGNED NOT NULL,
  `person_name` VARCHAR(120) NOT NULL,
  `designation` VARCHAR(120) NULL,
  `remarks` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_test_persons_record` (`testing_record_id`),
  KEY `idx_test_persons_name` (`person_name`),
  CONSTRAINT `fk_test_persons_testing_record`
    FOREIGN KEY (`testing_record_id`) REFERENCES `testing_records` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Activity/audit log
-- ------------------------------------------------------------
CREATE TABLE `activity_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `action` VARCHAR(180) NOT NULL,
  `description` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_activity_logs_user` (`user_id`),
  KEY `idx_activity_logs_created` (`created_at`),
  KEY `idx_activity_logs_action` (`action`),
  CONSTRAINT `fk_activity_logs_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Default users
-- Passwords are bcrypt hashes and work with PHP password_verify().
-- ------------------------------------------------------------
INSERT INTO `users`
  (`name`, `username`, `email`, `password`, `role`, `status`)
VALUES
  ('System Admin', 'admin', 'admin@labautomation.test',
   '$2y$10$pVj9hwzwpa9wdLESr0n.qupVernLCcuN3wtLdXWlCaSHQDFHNbVoa',
   'admin', 'active'),
  ('Lab Manager', 'manager', 'manager@labautomation.test',
   '$2y$10$VFyhz8lDTCELkTteb1Dsh.dHUQjVZCOqvZhYxVNwgG.K338fn4FiW',
   'lab_manager', 'active'),
  ('Lab Tester', 'tester', 'tester@labautomation.test',
   '$2y$10$vBB26mXpryi0jm1PW9zl5.CnUKRX6LTIR/MxaNyQP/0xodl8FQk5u',
   'tester', 'active');

-- Default credentials:
-- Admin:       admin / admin123
-- Lab Manager: manager / manager123
-- Tester:      tester / tester123

-- ------------------------------------------------------------
-- Initial master data
-- ------------------------------------------------------------
INSERT INTO `product_types`
  (`name`, `code`, `description`, `status`)
VALUES
  ('Switch Gear', 'SWG', 'Electrical switching and protection devices.', 'active'),
  ('Fuse', 'FUS', 'Protective fuse products for current interruption.', 'active'),
  ('Capacitor', 'CAP', 'Capacitive electrical components for appliance assemblies.', 'active'),
  ('Resistor', 'RES', 'Resistance components used in electrical circuits.', 'active');

INSERT INTO `testing_departments`
  (`name`, `code`, `description`, `status`)
VALUES
  ('Electrical Testing', 'ELEC', 'Electrical performance and endurance tests.', 'active'),
  ('Safety Testing', 'SAFE', 'Safety and compliance validation.', 'active'),
  ('Load Testing', 'LOAD', 'Load capacity and stability tests.', 'active'),
  ('Quality Assurance', 'QA', 'Final quality assurance checks and approvals.', 'active');

INSERT INTO `testing_types`
  (`department_id`, `product_type_id`, `name`, `testing_code`, `description`, `expected_output`, `criteria`, `status`)
VALUES
  (1, 1, 'Voltage Endurance Test', 'VOLT',
   'Measures product endurance against rated and surge voltage.',
   'Product sustains rated voltage without breakdown or abnormal heating.',
   'Apply rated voltage as per internal lab standard and observe stability.',
   'active'),
  (1, 2, 'Current Flow Test', 'CURR',
   'Verifies steady current flow and interruption behavior.',
   'Measured current remains within accepted tolerance.',
   'Run current flow for configured duration and record deviations.',
   'active'),
  (1, 4, 'Resistance Accuracy Test', 'OHM',
   'Validates resistance accuracy against design specification.',
   'Resistance output stays within defined tolerance band.',
   'Measure resistance at controlled temperature and compare with expected range.',
   'active'),
  (3, 3, 'Heat Stability Test', 'HEAT',
   'Checks thermal response under sustained load.',
   'No swelling, leakage, burn mark, or unsafe temperature rise.',
   'Operate under rated load and inspect temperature curve.',
   'active'),
  (2, 1, 'Safety Compliance Test', 'SAFE',
   'Confirms electrical insulation and safety compliance.',
   'Product satisfies safety checklist with no critical non-conformity.',
   'Perform insulation, enclosure, and safety checklist validations.',
   'active');

INSERT INTO `activity_logs` (`user_id`, `action`, `description`)
VALUES
  (1, 'database_seeded', 'Clean Lab Automation database and initial master data were installed.');

SET FOREIGN_KEY_CHECKS = 1;
