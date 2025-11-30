-- ===================================================
-- Create New Tables Only (Without Dropping Database)
-- Run this if you want to add new tables alongside existing ones
-- ===================================================

USE `thydb`;

-- --------------------------------------------------------
-- Table structure for table `medhis` (Medical History)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `medhis` (
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

CREATE TABLE IF NOT EXISTS `famhis` (
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

CREATE TABLE IF NOT EXISTS `cursym` (
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

CREATE TABLE IF NOT EXISTS `labres` (
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
-- Verification Queries
-- --------------------------------------------------------

-- Check if tables were created successfully
SELECT 'medhis' as table_name, COUNT(*) as count FROM medhis
UNION ALL
SELECT 'famhis', COUNT(*) FROM famhis
UNION ALL
SELECT 'cursym', COUNT(*) FROM cursym
UNION ALL
SELECT 'labres', COUNT(*) FROM labres;
