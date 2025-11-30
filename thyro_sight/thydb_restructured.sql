-- ===================================================
-- ThyroSight Database - Restructured Version
-- Separates health assessment into 4 distinct tables
-- ===================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Drop existing database and recreate it for clean import
DROP DATABASE IF EXISTS `thydb`;

-- Create the database
CREATE DATABASE `thydb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Use the database
USE `thydb`;

-- --------------------------------------------------------
-- Table structure for table `USER`
-- --------------------------------------------------------

CREATE TABLE `USER` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique user identifier',
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `otp` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `healthA` (Main Assessment Record)
-- --------------------------------------------------------

CREATE TABLE `healthA` (
  `form_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `age` INT(11) DEFAULT NULL COMMENT 'User age',
  `gender` enum('male','female','other') DEFAULT NULL COMMENT 'User gender',
  `assessment_date` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT 'Date when assessment was completed',
  `mode` VARCHAR(20) DEFAULT 'Hybrid' COMMENT 'Assessment mode: Hybrid or Symptom-only',
  `status` enum('completed','pending','incomplete') DEFAULT 'pending' COMMENT 'Assessment status',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`form_id`),
  KEY `user_id` (`user_id`),
  KEY `assessment_date` (`assessment_date`),
  KEY `status` (`status`),
  CONSTRAINT `healthA_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `USER` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `medhis` (Medical History)
-- --------------------------------------------------------

CREATE TABLE `medhis` (
  `medhis_id` INT(11) NOT NULL AUTO_INCREMENT,
  `form_id` INT(11) NOT NULL,
  `user_id` INT NOT NULL,
  
  -- Medical History Fields (1=Yes, 0=No)
  `diabetes` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `high_blood_pressure` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `high_cholesterol` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `anemia` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `depression_anxiety` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `heart_disease` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `menstrual_irregularities` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `autoimmune_diseases` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`medhis_id`),
  KEY `form_id` (`form_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `medhis_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `healthA` (`form_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `medhis_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `USER` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `famhis` (Family History)
-- --------------------------------------------------------

CREATE TABLE `famhis` (
  `famhis_id` INT(11) NOT NULL AUTO_INCREMENT,
  `form_id` INT(11) NOT NULL,
  `user_id` INT NOT NULL,
  
  -- Family History Fields (1=Yes, 0=No)
  `fh_hypothyroidism` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `fh_hyperthyroidism` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `fh_goiter` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `fh_thyroid_cancer` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`famhis_id`),
  KEY `form_id` (`form_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `famhis_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `healthA` (`form_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `famhis_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `USER` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `cursym` (Current Symptoms)
-- --------------------------------------------------------

CREATE TABLE `cursym` (
  `cursym_id` INT(11) NOT NULL AUTO_INCREMENT,
  `form_id` INT(11) NOT NULL,
  `user_id` INT NOT NULL,
  
  -- Current Symptoms Fields (1=Yes, 0=No)
  `sym_fatigue` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `sym_weight_change` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `sym_dry_skin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `sym_hair_loss` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `sym_heart_rate` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `sym_digestion` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `sym_irregular_periods` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `sym_neck_swelling` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`cursym_id`),
  KEY `form_id` (`form_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `cursym_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `healthA` (`form_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cursym_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `USER` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `labres` (Lab Results)
-- --------------------------------------------------------

CREATE TABLE `labres` (
  `labres_id` INT(11) NOT NULL AUTO_INCREMENT,
  `form_id` INT(11) NOT NULL,
  `user_id` INT NOT NULL,
  
  -- Lab Results Flags (1=Yes, 0=No)
  `tsh` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `t3` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `t4` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `t4_uptake` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  `fti` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Yes, 0=No',
  
  -- Lab Results Values
  `tsh_level` FLOAT DEFAULT NULL COMMENT 'TSH level value',
  `t3_level` FLOAT DEFAULT NULL COMMENT 'T3 level value',
  `t4_level` FLOAT DEFAULT NULL COMMENT 'T4 level value',
  `t4_uptake_result` FLOAT DEFAULT NULL COMMENT 'T4 Uptake result',
  `fti_result` FLOAT DEFAULT NULL COMMENT 'FTI result value',
  
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`labres_id`),
  KEY `form_id` (`form_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `labres_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `healthA` (`form_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `labres_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `USER` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `Result`
-- --------------------------------------------------------

CREATE TABLE `Result` (
    `result_id` INT AUTO_INCREMENT PRIMARY KEY,
    `form_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `prediction` ENUM('normal', 'hypo', 'hyper') NOT NULL,
    `c_score` DECIMAL(5,2) NOT NULL,
    `mode` VARCHAR(20) DEFAULT 'Hybrid' COMMENT 'Assessment mode: Hybrid or Symptom-only',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`form_id`) REFERENCES `healthA`(`form_id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `USER`(`user_id`) ON DELETE CASCADE,
    INDEX `idx_user_results` (`user_id`),
    INDEX `idx_form_results` (`form_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `shap_history` (if needed)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `shap_history` (
    `shap_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `form_id` INT NOT NULL,
    `prediction_label` VARCHAR(20) NOT NULL,
    `confidence` DECIMAL(5,2) NOT NULL,
    `shap_factors` JSON DEFAULT NULL,
    `mode` VARCHAR(20) DEFAULT 'Hybrid',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `USER`(`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`form_id`) REFERENCES `healthA`(`form_id`) ON DELETE CASCADE,
    INDEX `idx_user_shap` (`user_id`),
    INDEX `idx_form_shap` (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
