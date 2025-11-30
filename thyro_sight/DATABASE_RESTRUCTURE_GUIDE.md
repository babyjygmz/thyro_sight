# Database Restructure Guide

## Overview
This guide explains the restructured database schema that separates the health assessment data into 4 distinct tables for better organization and maintainability.

## New Database Structure

### Main Tables

#### 1. **healthA** (Main Assessment Record)
- `form_id` - Primary key
- `user_id` - Foreign key to USER table
- `age` - User age at assessment
- `gender` - User gender
- `assessment_date` - When assessment was completed
- `mode` - Assessment mode (Hybrid/Symptom-only)
- `status` - Assessment status (completed/pending/incomplete)
- `created_at`, `updated_at` - Timestamps

#### 2. **medhis** (Medical History)
Table name: `medhis`
- `medhis_id` - Primary key
- `form_id` - Foreign key to healthA
- `user_id` - Foreign key to USER
- Medical history fields (all TINYINT 1=Yes, 0=No):
  - `diabetes`
  - `high_blood_pressure`
  - `high_cholesterol`
  - `anemia`
  - `depression_anxiety`
  - `heart_disease`
  - `menstrual_irregularities`
  - `autoimmune_diseases`

#### 3. **famhis** (Family History)
Table name: `famhis`
- `famhis_id` - Primary key
- `form_id` - Foreign key to healthA
- `user_id` - Foreign key to USER
- Family history fields (all TINYINT 1=Yes, 0=No):
  - `fh_hypothyroidism`
  - `fh_hyperthyroidism`
  - `fh_goiter`
  - `fh_thyroid_cancer`

#### 4. **cursym** (Current Symptoms)
Table name: `cursym`
- `cursym_id` - Primary key
- `form_id` - Foreign key to healthA
- `user_id` - Foreign key to USER
- Symptom fields (all TINYINT 1=Yes, 0=No):
  - `sym_fatigue`
  - `sym_weight_change`
  - `sym_dry_skin`
  - `sym_hair_loss`
  - `sym_heart_rate`
  - `sym_digestion`
  - `sym_irregular_periods`
  - `sym_neck_swelling`

#### 5. **labres** (Lab Results)
Table name: `labres`
- `labres_id` - Primary key
- `form_id` - Foreign key to healthA
- `user_id` - Foreign key to USER
- Lab test flags (TINYINT 1=Yes, 0=No):
  - `tsh`, `t3`, `t4`, `t4_uptake`, `fti`
- Lab test values (FLOAT):
  - `tsh_level`, `t3_level`, `t4_level`, `t4_uptake_result`, `fti_result`

### Supporting Tables
- **Result** - Stores prediction results
- **shap_history** - Stores SHAP analysis data

## Benefits of New Structure

1. **Better Organization**: Each category has its own table
2. **Easier Maintenance**: Update one category without affecting others
3. **Improved Queries**: Fetch only needed data
4. **Scalability**: Easy to add new fields to specific categories
5. **Data Integrity**: Foreign key constraints ensure consistency

## Migration Steps

### Step 1: Backup Current Database
```bash
mysqldump -u root -p thydb > thydb_backup_$(date +%Y%m%d).sql
```

### Step 2: Run Migration Script
```bash
php migrate_to_restructured_db.php
```

This script will:
- Create new table structure
- Migrate all existing data
- Backup old healthA table as `healthA_backup`
- Activate new structure

### Step 3: Update Backend Endpoint
Replace the old submit endpoint with the new one:

**Option A: Rename files**
```bash
mv submit_health_assessment.php submit_health_assessment_old.php
mv submit_health_assessment_restructured.php submit_health_assessment.php
```

**Option B: Update the existing file**
Copy the content from `submit_health_assessment_restructured.php` to `submit_health_assessment.php`

### Step 4: Update History Retrieval
Replace the old history endpoint:
```bash
mv get_assessment_history.php get_assessment_history_old.php
mv get_assessment_history_restructured.php get_assessment_history.php
```

### Step 5: Test the System
1. Submit a new health assessment
2. Check that data is saved in all 4 tables
3. Verify history retrieval works correctly
4. Test result display

### Step 6: Verify Data Integrity
```sql
-- Check record counts match
SELECT COUNT(*) FROM healthA;
SELECT COUNT(*) FROM medhis;
SELECT COUNT(*) FROM famhis;
SELECT COUNT(*) FROM cursym;
SELECT COUNT(*) FROM labres;

-- All counts should be equal

-- Verify foreign key relationships
SELECT h.form_id, m.medhis_id, f.famhis_id, c.cursym_id, l.labres_id
FROM healthA h
LEFT JOIN medhis m ON h.form_id = m.form_id
LEFT JOIN famhis f ON h.form_id = f.form_id
LEFT JOIN cursym c ON h.form_id = c.form_id
LEFT JOIN labres l ON h.form_id = l.form_id
WHERE m.medhis_id IS NULL 
   OR f.famhis_id IS NULL 
   OR c.cursym_id IS NULL 
   OR l.labres_id IS NULL;

-- Should return 0 rows
```

### Step 7: Clean Up (After Verification)
```sql
-- Drop backup table after confirming everything works
DROP TABLE healthA_backup;
```

## Frontend Changes (No Changes Required!)

The frontend form structure remains the same. The field names in the form match the new database structure, so no frontend changes are needed.

## API Response Format

### New Assessment Submission Response
```json
{
  "success": true,
  "message": "Health assessment saved successfully across all tables.",
  "form_id": 123,
  "result_id": 456,
  "mode": "Hybrid"
}
```

### History Retrieval Response
```json
{
  "success": true,
  "count": 5,
  "assessments": [
    {
      "form_id": 123,
      "age": 35,
      "gender": "female",
      "mode": "Hybrid",
      "status": "completed",
      "assessment_date": "2025-11-30 10:30:00",
      "prediction": "normal",
      "c_score": 85.50,
      "medical_history": {
        "medhis_id": 123,
        "diabetes": 0,
        "high_blood_pressure": 1,
        ...
      },
      "family_history": {
        "famhis_id": 123,
        "fh_hypothyroidism": 1,
        ...
      },
      "current_symptoms": {
        "cursym_id": 123,
        "sym_fatigue": 1,
        ...
      },
      "lab_results": {
        "labres_id": 123,
        "tsh": 1,
        "tsh_level": 2.5,
        ...
      }
    }
  ]
}
```

## Rollback Plan

If you need to rollback to the old structure:

```sql
-- Restore from backup
DROP TABLE healthA;
RENAME TABLE healthA_backup TO healthA;

-- Or restore from SQL dump
mysql -u root -p thydb < thydb_backup_YYYYMMDD.sql
```

## Troubleshooting

### Issue: Foreign key constraint fails
**Solution**: Ensure USER table exists and has matching user_ids

### Issue: Migration script fails midway
**Solution**: The script uses transactions and will rollback automatically. Check error message and fix the issue.

### Issue: Data missing after migration
**Solution**: Restore from backup and check migration script logs

## Database Diagram

```
USER (user_id)
  ↓
healthA (form_id, user_id)
  ↓ ↓ ↓ ↓
  ├─→ medhis (form_id, user_id)
  ├─→ famhis (form_id, user_id)
  ├─→ cursym (form_id, user_id)
  ├─→ labres (form_id, user_id)
  ├─→ Result (form_id, user_id)
  └─→ shap_history (form_id, user_id)
```

## Files Created

1. `thydb_restructured.sql` - New database schema
2. `submit_health_assessment_restructured.php` - New submission handler
3. `get_assessment_history_restructured.php` - New history retrieval
4. `migrate_to_restructured_db.php` - Migration script
5. `DATABASE_RESTRUCTURE_GUIDE.md` - This documentation

## Support

If you encounter any issues during migration, check:
1. PHP error logs
2. MySQL error logs
3. Application logs in `/logs` directory
4. Database connection settings in `config/database.php`
