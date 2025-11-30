# ThyroSight Database ERD

## Entity Relationship Diagram

```mermaid
erDiagram
    USER ||--o{ healthA : "creates"
    USER ||--o{ medhis : "has"
    USER ||--o{ famhis : "has"
    USER ||--o{ cursym : "reports"
    USER ||--o{ labres : "submits"
    USER ||--o{ Result : "receives"
    USER ||--o{ shap_history : "has"
    
    healthA ||--|| medhis : "contains"
    healthA ||--|| famhis : "contains"
    healthA ||--|| cursym : "contains"
    healthA ||--|| labres : "contains"
    healthA ||--|| Result : "generates"
    healthA ||--|| shap_history : "explains"

    USER {
        int user_id PK
        varchar first_name
        varchar last_name
        varchar email UK
        varchar password
        varchar phone
        date date_of_birth
        enum gender
        varchar otp
        datetime otp_expiry
        tinyint is_verified
        timestamp created_at
        timestamp updated_at
    }

    healthA {
        int form_id PK
        int user_id FK
        int age
        enum gender
        timestamp assessment_date
        varchar mode
        enum status
        timestamp created_at
        timestamp updated_at
    }

    medhis {
        int medhis_id PK
        int form_id FK
        int user_id FK
        tinyint diabetes
        tinyint high_blood_pressure
        tinyint high_cholesterol
        tinyint anemia
        tinyint depression_anxiety
        tinyint heart_disease
        tinyint menstrual_irregularities
        tinyint autoimmune_diseases
        timestamp created_at
        timestamp updated_at
    }

    famhis {
        int famhis_id PK
        int form_id FK
        int user_id FK
        tinyint fh_hypothyroidism
        tinyint fh_hyperthyroidism
        tinyint fh_goiter
        tinyint fh_thyroid_cancer
        timestamp created_at
        timestamp updated_at
    }

    cursym {
        int cursym_id PK
        int form_id FK
        int user_id FK
        tinyint sym_fatigue
        tinyint sym_weight_change
        tinyint sym_dry_skin
        tinyint sym_hair_loss
        tinyint sym_heart_rate
        tinyint sym_digestion
        tinyint sym_irregular_periods
        tinyint sym_neck_swelling
        timestamp created_at
        timestamp updated_at
    }

    labres {
        int labres_id PK
        int form_id FK
        int user_id FK
        tinyint tsh
        tinyint t3
        tinyint t4
        tinyint t4_uptake
        tinyint fti
        float tsh_level
        float t3_level
        float t4_level
        float t4_uptake_result
        float fti_result
        timestamp created_at
        timestamp updated_at
    }

    Result {
        int result_id PK
        int form_id FK
        int user_id FK
        enum prediction
        decimal c_score
        varchar mode
        timestamp created_at
    }

    shap_history {
        int shap_id PK
        int user_id FK
        int form_id FK
        varchar prediction_label
        decimal confidence
        json shap_factors
        varchar mode
        timestamp created_at
    }
```

## Table Descriptions

### USER
Central table storing user account information, authentication details, and profile data.

### healthA (Health Assessment)
Main assessment record that links all other assessment-related tables. Each form submission creates one record here.

### medhis (Medical History)
Stores user's medical conditions (8 fields): diabetes, blood pressure, cholesterol, anemia, depression/anxiety, heart disease, menstrual issues, autoimmune diseases.

### famhis (Family History)
Stores family history of thyroid conditions (4 fields): hypothyroidism, hyperthyroidism, goiter, thyroid cancer.

### cursym (Current Symptoms)
Stores current symptoms reported by user (8 fields): fatigue, weight change, dry skin, hair loss, heart rate changes, digestion issues, irregular periods, neck swelling.

### labres (Lab Results)
Stores laboratory test results with both flags (yes/no) and actual numeric values for TSH, T3, T4, T4 Uptake, and FTI tests.

### Result
Stores the prediction outcome (normal/hypo/hyper) with confidence score for each assessment.

### shap_history
Stores SHAP (SHapley Additive exPlanations) factors as JSON for explainable AI predictions.

## Relationships

- **One USER** can have **many assessments** (healthA)
- **One healthA** record has **one** of each: medhis, famhis, cursym, labres, Result, shap_history
- All child tables reference both `user_id` and `form_id` for data integrity
- CASCADE DELETE ensures when a user or assessment is deleted, all related data is removed

## Key Features

1. **Normalized Design**: Separate tables for different data categories
2. **Foreign Key Constraints**: Maintain referential integrity
3. **Cascade Operations**: Automatic cleanup of related records
4. **Timestamps**: Track creation and updates
5. **Flexible Lab Results**: Store both test availability flags and actual values
6. **JSON Storage**: SHAP factors stored as JSON for flexibility
