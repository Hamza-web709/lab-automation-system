-- ============================================================
-- lab_automation_db.sql
-- Combined full database file for Lab Automation System
-- Includes:
-- 1) Original Lab Automation database schema
-- 2) Role-based panel update: admin, lab_manager, tester
-- 3) Username login update: login by email OR username
--
-- Import this single file in phpMyAdmin or MySQL CLI.
-- Database name: lab_automation_db
-- ============================================================

CREATE DATABASE IF NOT EXISTS lab_automation_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE lab_automation_db;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS test_persons;
DROP TABLE IF EXISTS testing_records;
DROP TABLE IF EXISTS testing_types;
DROP TABLE IF EXISTS testing_departments;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS product_types;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- Secure application users. Passwords are verified with password_verify().
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  username VARCHAR(60) NOT NULL UNIQUE,
  email VARCHAR(160) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','lab_manager','tester') NOT NULL DEFAULT 'tester',
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_role_status (role, status)
) ENGINE=InnoDB;

-- Product families such as switch gear, fuse, capacitor, and resistor.
CREATE TABLE product_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  code VARCHAR(10) NOT NULL UNIQUE,
  description TEXT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_product_types_status (status)
) ENGINE=InnoDB;

-- Manufactured products entering the lab workflow.
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id VARCHAR(10) NOT NULL UNIQUE,
  product_type_id INT NOT NULL,
  product_name VARCHAR(160) NOT NULL,
  product_code VARCHAR(10) NOT NULL,
  revise_no VARCHAR(20) NOT NULL,
  manufacturing_no VARCHAR(40) NOT NULL,
  batch_no VARCHAR(80) NULL,
  manufacturing_date DATE NULL,
  description TEXT NULL,
  current_status ENUM('manufactured','under_testing','passed_internal','failed_internal','sent_to_cpri','approved','sent_for_remaking') NOT NULL DEFAULT 'manufactured',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_type FOREIGN KEY (product_type_id)
    REFERENCES product_types(id) ON UPDATE CASCADE,
  INDEX idx_products_status (current_status),
  INDEX idx_products_type_status (product_type_id, current_status),
  INDEX idx_products_search (product_id, product_name, product_code)
) ENGINE=InnoDB;

-- Lab departments responsible for specific testing stages.
CREATE TABLE testing_departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  code VARCHAR(10) NOT NULL UNIQUE,
  description TEXT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_testing_departments_status (status)
) ENGINE=InnoDB;

-- Test definitions with reusable criteria and expected outputs.
CREATE TABLE testing_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  department_id INT NOT NULL,
  product_type_id INT NOT NULL,
  name VARCHAR(160) NOT NULL,
  testing_code VARCHAR(10) NOT NULL,
  description TEXT NULL,
  expected_output TEXT NULL,
  criteria TEXT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_testing_types_department FOREIGN KEY (department_id)
    REFERENCES testing_departments(id) ON UPDATE CASCADE,
  CONSTRAINT fk_testing_types_product_type FOREIGN KEY (product_type_id)
    REFERENCES product_types(id) ON UPDATE CASCADE,
  UNIQUE KEY uq_testing_code_department (department_id, testing_code),
  INDEX idx_testing_types_status (status),
  INDEX idx_testing_types_lookup (department_id, product_type_id)
) ENGINE=InnoDB;

-- Individual test executions for a manufactured product.
CREATE TABLE testing_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  test_id VARCHAR(12) NOT NULL UNIQUE,
  product_id_ref INT NOT NULL,
  testing_type_id INT NOT NULL,
  department_id INT NOT NULL,
  test_roll_no VARCHAR(20) NOT NULL,
  test_date DATE NOT NULL,
  criteria TEXT NULL,
  expected_output TEXT NULL,
  observed_output TEXT NULL,
  detailed_remarks TEXT NULL,
  result ENUM('pass','fail','pending') NOT NULL DEFAULT 'pending',
  status ENUM('pending','in_progress','completed','sent_to_next_department','sent_to_cpri','sent_for_remaking') NOT NULL DEFAULT 'pending',
  next_action ENUM('none','next_test','send_to_cpri','send_for_remaking') NOT NULL DEFAULT 'none',
  created_by INT NULL,
  assigned_tester_id INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_testing_records_product FOREIGN KEY (product_id_ref)
    REFERENCES products(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_testing_records_type FOREIGN KEY (testing_type_id)
    REFERENCES testing_types(id) ON UPDATE CASCADE,
  CONSTRAINT fk_testing_records_department FOREIGN KEY (department_id)
    REFERENCES testing_departments(id) ON UPDATE CASCADE,
  CONSTRAINT fk_testing_records_user FOREIGN KEY (created_by)
    REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_testing_records_assigned_tester FOREIGN KEY (assigned_tester_id)
    REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_testing_records_result_status (result, status),
  INDEX idx_testing_records_date (test_date),
  INDEX idx_testing_records_product (product_id_ref),
  INDEX idx_testing_records_assigned_tester (assigned_tester_id)
) ENGINE=InnoDB;

-- People who performed or witnessed a test.
CREATE TABLE test_persons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  testing_record_id INT NOT NULL,
  person_name VARCHAR(120) NOT NULL,
  designation VARCHAR(120) NULL,
  remarks TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_test_persons_record FOREIGN KEY (testing_record_id)
    REFERENCES testing_records(id) ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_test_persons_name (person_name)
) ENGINE=InnoDB;

-- Audit trail for important user and workflow actions.
CREATE TABLE activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(180) NOT NULL,
  description TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_activity_logs_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_activity_logs_created (created_at),
  INDEX idx_activity_logs_action (action)
) ENGINE=InnoDB;

-- Default login:
-- email: admin@labautomation.test
-- password: admin123
INSERT INTO users (name, username, email, password, role, status)
VALUES
('System Admin', 'admin', 'admin@labautomation.test', '$2y$10$pVj9hwzwpa9wdLESr0n.qupVernLCcuN3wtLdXWlCaSHQDFHNbVoa', 'admin', 'active'),
('Lab Manager', 'manager', 'manager@labautomation.test', '$2y$10$VFyhz8lDTCELkTteb1Dsh.dHUQjVZCOqvZhYxVNwgG.K338fn4FiW', 'lab_manager', 'active'),
('Lab Tester', 'tester', 'tester@labautomation.test', '$2y$10$vBB26mXpryi0jm1PW9zl5.CnUKRX6LTIR/MxaNyQP/0xodl8FQk5u', 'tester', 'active');

INSERT INTO product_types (name, code, description, status)
VALUES
('Switch Gear', 'SWG', 'Electrical switching and protection devices.', 'active'),
('Fuse', 'FUS', 'Protective fuse products for current interruption.', 'active'),
('Capacitor', 'CAP', 'Capacitive electrical components for appliance assemblies.', 'active'),
('Resistor', 'RES', 'Resistance components used in electrical circuits.', 'active');

INSERT INTO testing_departments (name, code, description, status)
VALUES
('Electrical Testing', 'ELEC', 'Electrical performance and endurance tests.', 'active'),
('Safety Testing', 'SAFE', 'Safety and compliance validation.', 'active'),
('Load Testing', 'LOAD', 'Load capacity and stability tests.', 'active'),
('Quality Assurance', 'QA', 'Final quality assurance checks and approvals.', 'active');

INSERT INTO testing_types
(department_id, product_type_id, name, testing_code, description, expected_output, criteria, status)
VALUES
(1, 1, 'Voltage Endurance Test', 'VOLT', 'Measures product endurance against rated and surge voltage.', 'Product sustains rated voltage without breakdown or abnormal heating.', 'Apply rated voltage as per internal lab standard and observe stability.', 'active'),
(1, 2, 'Current Flow Test', 'CURR', 'Verifies steady current flow and interruption behavior.', 'Measured current remains within accepted tolerance.', 'Run current flow for configured duration and record deviations.', 'active'),
(1, 4, 'Resistance Accuracy Test', 'OHM', 'Validates resistance accuracy against design specification.', 'Resistance output stays within defined tolerance band.', 'Measure resistance at controlled temperature and compare with expected range.', 'active'),
(3, 3, 'Heat Stability Test', 'HEAT', 'Checks thermal response under sustained load.', 'No swelling, leakage, burn mark, or unsafe temperature rise.', 'Operate under rated load and inspect temperature curve.', 'active'),
(2, 1, 'Safety Compliance Test', 'SAFE', 'Confirms electrical insulation and safety compliance.', 'Product satisfies safety checklist with no critical non-conformity.', 'Perform insulation, enclosure, and safety checklist validations.', 'active');

INSERT INTO activity_logs (user_id, action, description)
VALUES (1, 'database_seeded', 'Initial role users and master testing data were inserted.');

-- End of combined Lab Automation database file.
