# ThyroSight Database Structure - Restructured

## Visual Database Schema

```
┌─────────────────────────────────────────────────────────────────────┐
│                           USER TABLE                                 │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │ user_id (PK) | first_name | last_name | email | password     │  │
│  │ phone | date_of_birth | gender | otp | is_verified          │  │
│  └──────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  │ (1:N)
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      healthA (Main Assessment)                       │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │ form_id (PK) | user_id (FK) | age | gender                   │  │
│  │ assessment_date | mode | status | created_at | updated_at    │  │
│  └──────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
         │              │              │              │
         │              │              │              │
         ▼              ▼              ▼              ▼
┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│   medhis     │ │   famhis     │ │   cursym     │ │   labres     │
│  (Medical    │ │  (Family     │ │  (Current    │ │  (Lab        │
│   History)   │ │   History)   │ │   Symptoms)  │ │   Results)   │
├──────────────┤ ├──────────────┤ ├──────────────┤ ├──────────────┤
│ medhis_id PK │ │ famhis_id PK │ │ cursym_id PK │ │ labres_id PK │
│ form_id FK   │ │ form_id FK   │ │ form_id FK   │ │ form_id FK   │
│ user_id FK   │ │ user_id FK   │ │ user_id FK   │ │ user_id FK   │
├──────────────┤ ├──────────────┤ ├──────────────┤ ├──────────────┤
│ • diabetes   │ │ • fh_hypo    │ │ • fatigue    │ │ • tsh        │
│ • high_bp    │ │ • fh_hyper   │ │ • weight     │ │ • t3         │
│ • cholesterol│ │ • fh_goiter  │ │ • dry_skin   │ │ • t4         │
│ • anemia     │ │ • fh_cancer  │ │ • hair_loss  │ │ • t4_uptake  │
│ • depression │ │              │ │ • heart_rate │ │ • fti        │
│ • heart_dis  │ │              │ │ • digestion  │ │ • tsh_level  │
│ • menstrual  │ │              │ │ • periods    │ │ • t3_level   │
│ • autoimmune │ │              │ │ • swelling   │ │ • t4_level   │
└──────────────┘ └──────────────┘ └──────────────┘ │ • t4_uptake  │
                                                    │ • fti_result │
                                                    └──────────────┘
         │              │              │              │
         └──────────────┴──────────────┴──────────────┘
                                  │
                                  ▼
                    ┌─────────────────────────────┐
                    │         Result              │
                    ├─────────────────────────────┤
                    │ result_id (PK)              │
                    │ form_id (FK)                │
                    │ user_id (FK)                │
                    │ prediction                  │
                    │ c_score                     │
                    │ mode                        │
                    └─────────────────────────────┘
                                  │
                                  ▼
                    ┌─────────────────────────────┐
                    │      shap_history           │
                    ├─────────────────────────────┤
                    │ shap_id (PK)                │
                    │ form_id (FK)                │
                    │ user_id (FK)                │
                    │ prediction_label            │
                    │ confidence                  │
                    │ shap_factors (JSON)         │
                    └─────────────────────────────┘
```

## Table Relationships

### One-to-Many Relationships
- **USER → healthA**: One user can have many assessments
- **healthA → medhis**: One assessment has one medical history record
- **healthA → famhis**: One assessment has one family history record
- **healthA → cursym**: One assessment has one current symptoms record
- **healthA → labres**: One assessment has one lab results record
- **healthA → Result**: One assessment has one result
- **healthA → shap_history**: One assessment has one SHAP analysis

## Data Flow

### Assessment Submission Flow
```
Frontend Form
     │
     ▼
submit_health_assessment.php
     │
     ├─→ INSERT into healthA (get form_id)
     │
     ├─→ INSERT into medhis (using form_id)
     │
     ├─→ INSERT into famhis (using form_id)
     │
     ├─→ INSERT into cursym (using form_id)
     │
     ├─→ INSERT into labres (using form_id)
     │
     ├─→ INSERT into Result (using form_id)
     │
     └─→ INSERT into shap_history (using form_id)
```

### Assessment Retrieval Flow
```
Frontend Request
     │
     ▼
get_assessment_history.php
     │
     ├─→ SELECT from healthA
     │
     ├─→ JOIN medhis
     │
     ├─→ JOIN famhis
     │
     ├─→ JOIN cursym
     │
     ├─→ JOIN labres
     │
     ├─→ JOIN Result
     │
     └─→ JOIN shap_history
     │
     ▼
Return Combined JSON
```

## Field Mapping

### Medical History (medhis)
| Frontend Field Name        | Database Column           | Type      |
|---------------------------|---------------------------|-----------|
| diabetes                  | diabetes                  | TINYINT   |
| high_blood_pressure       | high_blood_pressure       | TINYINT   |
| high_cholesterol          | high_cholesterol          | TINYINT   |
| anemia                    | anemia                    | TINYINT   |
| depression_anxiety        | depression_anxiety        | TINYINT   |
| heart_disease             | heart_disease             | TINYINT   |
| menstrual_irregularities  | menstrual_irregularities  | TINYINT   |
| autoimmune_diseases       | autoimmune_diseases       | TINYINT   |

### Family History (famhis)
| Frontend Field Name    | Database Column        | Type      |
|-----------------------|------------------------|-----------|
| fh_hypothyroidism     | fh_hypothyroidism      | TINYINT   |
| fh_hyperthyroidism    | fh_hyperthyroidism     | TINYINT   |
| fh_goiter             | fh_goiter              | TINYINT   |
| fh_thyroid_cancer     | fh_thyroid_cancer      | TINYINT   |

### Current Symptoms (cursym)
| Frontend Field Name    | Database Column        | Type      |
|-----------------------|------------------------|-----------|
| sym_fatigue           | sym_fatigue            | TINYINT   |
| sym_weight_change     | sym_weight_change      | TINYINT   |
| sym_dry_skin          | sym_dry_skin           | TINYINT   |
| sym_hair_loss         | sym_hair_loss          | TINYINT   |
| sym_heart_rate        | sym_heart_rate         | TINYINT   |
| sym_digestion         | sym_digestion          | TINYINT   |
| sym_irregular_periods | sym_irregular_periods  | TINYINT   |
| sym_neck_swelling     | sym_neck_swelling      | TINYINT   |

### Lab Results (labres)
| Frontend Field Name | Database Column      | Type      |
|--------------------|----------------------|-----------|
| tsh                | tsh                  | TINYINT   |
| t3                 | t3                   | TINYINT   |
| t4                 | t4                   | TINYINT   |
| t4-uptake          | t4_uptake            | TINYINT   |
| fti                | fti                  | TINYINT   |
| tshValue           | tsh_level            | FLOAT     |
| t3Value            | t3_level             | FLOAT     |
| t4Value            | t4_level             | FLOAT     |
| t4-uptakeValue     | t4_uptake_result     | FLOAT     |
| ftiValue           | fti_result           | FLOAT     |

## Benefits of This Structure

### 1. **Modularity**
Each category is independent and can be modified without affecting others.

### 2. **Query Efficiency**
```sql
-- Get only medical history
SELECT * FROM medhis WHERE user_id = ?;

-- Get only symptoms
SELECT * FROM cursym WHERE user_id = ?;

-- Get complete assessment
SELECT h.*, m.*, f.*, c.*, l.*
FROM healthA h
LEFT JOIN medhis m ON h.form_id = m.form_id
LEFT JOIN famhis f ON h.form_id = f.form_id
LEFT JOIN cursym c ON h.form_id = c.form_id
LEFT JOIN labres l ON h.form_id = l.form_id
WHERE h.user_id = ?;
```

### 3. **Data Integrity**
- Foreign key constraints ensure referential integrity
- CASCADE DELETE removes all related records when assessment is deleted
- Each table has its own primary key for unique identification

### 4. **Scalability**
Easy to add new fields to specific categories:
```sql
-- Add new symptom
ALTER TABLE cursym ADD COLUMN sym_new_symptom TINYINT(1) DEFAULT 0;

-- Add new lab test
ALTER TABLE labres ADD COLUMN new_test TINYINT(1) DEFAULT 0;
ALTER TABLE labres ADD COLUMN new_test_level FLOAT DEFAULT NULL;
```

### 5. **Reporting & Analytics**
```sql
-- Most common symptoms
SELECT 
    SUM(sym_fatigue) as fatigue_count,
    SUM(sym_weight_change) as weight_count,
    SUM(sym_dry_skin) as dry_skin_count
FROM cursym;

-- Family history statistics
SELECT 
    COUNT(*) as total,
    SUM(fh_hypothyroidism) as hypo_family,
    SUM(fh_hyperthyroidism) as hyper_family
FROM famhis;
```

## Migration Impact

### Before (Old Structure)
- 1 table: healthA with 40+ columns
- Difficult to maintain
- Hard to query specific categories

### After (New Structure)
- 5 tables: healthA + 4 category tables
- Easy to maintain each category
- Efficient queries for specific data
- Better organization and scalability

## Cascade Behavior

When a record is deleted:
```
DELETE healthA (form_id = 123)
    ↓
Automatically deletes:
    - medhis (form_id = 123)
    - famhis (form_id = 123)
    - cursym (form_id = 123)
    - labres (form_id = 123)
    - Result (form_id = 123)
    - shap_history (form_id = 123)
```

This ensures no orphaned records remain in the database.
