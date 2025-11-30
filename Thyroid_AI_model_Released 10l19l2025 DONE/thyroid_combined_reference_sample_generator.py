# ===============================================
# thyroid_combined_reference_sample_generator.py
# -----------------------------------------------
# Generates a Unified Thyroid AI Dataset combining:
#   - Symptom-Only Cases (no lab results)
#   - Lab-Assisted Cases (with TSH, T3, T4, FTI)
#
# Output File: thyroid_combined_reference_sample.csv
# Contains:
#   10 Normal (Symptom-Only, with mild background history)
#   10 Hypothyroid (Lab-Assisted)
#   10 Hyperthyroid (Lab-Assisted)
# -----------------------------------------------
# Author: DevRoxx Web/Software Services
# Version: 1.1 (October 2025)
# ===============================================

import pandas as pd
import numpy as np

# -----------------------------------------------
# Define Columns
# -----------------------------------------------
columns = [
    "Age",
    "Sex",
    "Diabetes",
    "HighBloodPressure",
    "HighCholesterol",
    "Anemia",
    "DepressionAnxiety",
    "AutoimmuneDiseases",
    "HeartDisease",
    "FH_Hypothyroidism",
    "FH_Hyperthyroidism",
    "FH_Goiter",
    "FH_ThyroidCancer",
    "Sym_Fatigue",
    "Sym_WeightChange",
    "Sym_DrySkin",
    "Sym_HairLoss",
    "Sym_HeartRate",
    "Sym_Digestion",
    "Sym_IrregularPeriods",
    "Sym_NeckSwelling",
    "TSH mIU/L",
    "T3 ng/dL",
    "T4 ng/dL",
    "FTI",
    "Mode",         # “Symptom-Only” or “Lab-Assisted”
    "Diagnosis"     # Ground truth label
]

# -----------------------------------------------
# Helper: Random Lab Value Generator
# -----------------------------------------------
def random_normal_range(mean, low, high):
    val = np.random.normal(mean, (high - low) / 10)
    return float(np.clip(val, low, high))

# -----------------------------------------------
# 1. Symptom-Only NORMAL (10 samples, realistic)
# -----------------------------------------------
normal_symptom = []
for _ in range(10):
    age = np.random.randint(25, 60)
    sex = np.random.choice([0, 1])

    # Mild unrelated medical history
    diabetes = np.random.choice([0, 1], p=[0.85, 0.15])
    high_bp = np.random.choice([0, 1], p=[0.8, 0.2])
    high_chol = np.random.choice([0, 1], p=[0.8, 0.2])
    anemia = np.random.choice([0, 1], p=[0.9, 0.1])
    depression = np.random.choice([0, 1], p=[0.9, 0.1])
    autoimmune = np.random.choice([0, 1], p=[0.95, 0.05])
    heart_disease = np.random.choice([0, 1], p=[0.95, 0.05])

    # Mild or no family history
    fh_hypo = np.random.choice([0, 1], p=[0.8, 0.2])
    fh_hyper = np.random.choice([0, 1], p=[0.9, 0.1])
    fh_goiter = 0
    fh_cancer = 0

    # Mostly no thyroid-related symptoms, small chance of mild fatigue or hair loss
    sym_fatigue = np.random.choice([0, 1], p=[0.8, 0.2])
    sym_weightchange = 0
    sym_dryskin = 0
    sym_hairloss = np.random.choice([0, 1], p=[0.85, 0.15])
    sym_heartrate = 0
    sym_digestion = np.random.choice([0, 1], p=[0.9, 0.1])
    sym_irregularperiods = 0
    sym_neckswelling = 0

    # No lab values (symptom-only mode)
    record = [
        age, sex,
        diabetes, high_bp, high_chol, anemia, depression, autoimmune, heart_disease,
        fh_hypo, fh_hyper, fh_goiter, fh_cancer,
        sym_fatigue, sym_weightchange, sym_dryskin, sym_hairloss,
        sym_heartrate, sym_digestion, sym_irregularperiods, sym_neckswelling,
        "", "", "", "",
        "Symptom-Only",
        "Normal"
    ]
    normal_symptom.append(record)

# -----------------------------------------------
# 2. LAB-ASSISTED HYPOTHYROID (10 samples)
# -----------------------------------------------
hypo_lab = []
for _ in range(10):
    age = np.random.randint(35, 65)
    sex = np.random.choice([0, 1])
    record = [
        age, sex,
        np.random.choice([0, 1], p=[0.7, 0.3]),
        np.random.choice([0, 1], p=[0.7, 0.3]),
        np.random.choice([0, 1], p=[0.8, 0.2]),
        np.random.choice([0, 1], p=[0.8, 0.2]),
        np.random.choice([0, 1], p=[0.6, 0.4]),
        np.random.choice([0, 1], p=[0.9, 0.1]),
        np.random.choice([0, 1], p=[0.9, 0.1]),
        1, 0, 0, 0,
        1, 1, 1, 1, 0, 1, 1, 0,
        random_normal_range(15.0, 8.0, 30.0),
        random_normal_range(60, 30, 90),
        random_normal_range(4.0, 2.0, 6.0),
        random_normal_range(3.0, 1.5, 5.0),
        "Lab-Assisted",
        "Hypothyroid"
    ]
    hypo_lab.append(record)

# -----------------------------------------------
# 3. LAB-ASSISTED HYPERTHYROID (10 samples)
# -----------------------------------------------
hyper_lab = []
for _ in range(10):
    age = np.random.randint(25, 55)
    sex = np.random.choice([0, 1])
    record = [
        age, sex,
        np.random.choice([0, 1], p=[0.9, 0.1]),
        np.random.choice([0, 1], p=[0.85, 0.15]),
        np.random.choice([0, 1], p=[0.9, 0.1]),
        0,
        np.random.choice([0, 1], p=[0.7, 0.3]),
        0,
        np.random.choice([0, 1], p=[0.95, 0.05]),
        0, 1, 0, 0,
        0, 1, 0, 1, 1, 1, 0, 1,
        random_normal_range(0.2, 0.05, 0.4),
        random_normal_range(250, 200, 350),
        random_normal_range(14.0, 12.0, 18.0),
        random_normal_range(14.0, 12.0, 18.0),
        "Lab-Assisted",
        "Hyperthyroid"
    ]
    hyper_lab.append(record)

# -----------------------------------------------
# Combine All
# -----------------------------------------------
data = normal_symptom + hypo_lab + hyper_lab
df = pd.DataFrame(data, columns=columns)

# Save
output_file = "thyroid_combined_reference_sample.csv"
df.to_csv(output_file, index=False, encoding="utf-8-sig")

# -----------------------------------------------
# Summary
# -----------------------------------------------
print("✅ Unified Thyroid AI Dataset Created Successfully")
print("File saved as:", output_file)
print(df["Diagnosis"].value_counts())
print("\nColumns:")
for col in df.columns:
    print(f" - {col}")
