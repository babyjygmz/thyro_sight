# ===============================================
# generate_symptom_reference_dataset_v3.py
# -----------------------------------------------
# Generates a realistic symptom-based dataset for Thyroid AI
# Includes full medical history fields aligned with frontend form:
#   Diabetes, HighBloodPressure, HighCholesterol, Anemia,
#   DepressionAnxiety, HeartDisease, AutoimmuneDiseases
#
# Output: thyroid_symptom_reference_600.csv
# Classes: Normal / Hypothyroid / Hyperthyroid (200 each)
# -----------------------------------------------
# Author: DevRoxx Web/Software Services
# Version: 3.0 (October 2025)
# ===============================================

import pandas as pd
import numpy as np

np.random.seed(42)

records = []
conditions = ["Normal", "Hypothyroid", "Hyperthyroid"]
samples_per_class = 200

# -----------------------------------------------
# Probability map per class (index order: Normal, Hypo, Hyper)
# -----------------------------------------------
feature_probs = {
    # ------------------ Medical History ------------------
    "Diabetes": [0.15, 0.30, 0.20],
    "HighBloodPressure": [0.20, 0.35, 0.25],
    "HighCholesterol": [0.25, 0.40, 0.20],
    "Anemia": [0.10, 0.30, 0.25],
    "DepressionAnxiety": [0.15, 0.45, 0.35],
    "HeartDisease": [0.05, 0.15, 0.10],
    "AutoimmuneDiseases": [0.10, 0.35, 0.30],

    # ------------------ Family History ------------------
    "FH_Hypothyroidism": [0.10, 0.40, 0.15],
    "FH_Hyperthyroidism": [0.10, 0.15, 0.35],
    "FH_Goiter": [0.10, 0.20, 0.30],
    "FH_ThyroidCancer": [0.10, 0.10, 0.15],

    # ------------------ Symptoms ------------------
    "Sym_Fatigue": [0.15, 0.75, 0.60],
    "Sym_WeightChange": [0.15, 0.70, 0.80],
    "Sym_DrySkin": [0.10, 0.65, 0.20],
    "Sym_HairLoss": [0.15, 0.60, 0.25],
    "Sym_HeartRate": [0.10, 0.55, 0.80],
    "Sym_Digestion": [0.10, 0.60, 0.75],
    "Sym_IrregularPeriods": [0.10, 0.65, 0.60],
    "Sym_NeckSwelling": [0.10, 0.25, 0.55],
}

# -----------------------------------------------
# Generate records per condition
# -----------------------------------------------
for i, condition in enumerate(conditions):
    for _ in range(samples_per_class):
        # Demographics
        if condition == "Normal":
            age = int(np.random.normal(40, 10))
            sex = np.random.choice([0, 1], p=[0.5, 0.5])  # 0 = Female, 1 = Male
        elif condition == "Hypothyroid":
            age = int(np.random.normal(50, 10))
            sex = np.random.choice([0, 1], p=[0.7, 0.3])
        else:  # Hyperthyroid
            age = int(np.random.normal(35, 8))
            sex = np.random.choice([0, 1], p=[0.65, 0.35])

        age = int(np.clip(age, 18, 80))

        record = {
            "Diagnosis": condition,
            "Age": age,
            "Sex": sex
        }

        # Add medical, family, and symptom features
        for feat, probs in feature_probs.items():
            record[feat] = np.random.choice([0, 1], p=[1 - probs[i], probs[i]])

        records.append(record)

# -----------------------------------------------
# DataFrame creation and minor noise
# -----------------------------------------------
df = pd.DataFrame(records)

# Introduce 5% label noise (simulate mild mislabeling)
num_noise = int(0.05 * len(df))
noise_idx = np.random.choice(df.index, num_noise, replace=False)
df.loc[noise_idx, "Diagnosis"] = np.random.choice(conditions, num_noise)

# Reorder columns logically
ordered_cols = [
    "Diagnosis", "Age", "Sex",
    "Diabetes", "HighBloodPressure", "HighCholesterol", "Anemia",
    "DepressionAnxiety", "HeartDisease", "AutoimmuneDiseases",
    "FH_Hypothyroidism", "FH_Hyperthyroidism", "FH_Goiter", "FH_ThyroidCancer",
    "Sym_Fatigue", "Sym_WeightChange", "Sym_DrySkin", "Sym_HairLoss",
    "Sym_HeartRate", "Sym_Digestion", "Sym_IrregularPeriods", "Sym_NeckSwelling"
]
df = df[ordered_cols]

# -----------------------------------------------
# Save dataset
# -----------------------------------------------
output_file = "thyroid_symptom_reference_600.csv"
df.to_csv(output_file, index=False, encoding="utf-8-sig")

# -----------------------------------------------
# Summary
# -----------------------------------------------
print(" Generated 'thyroid_symptom_reference_600.csv' (with Medical History, v3)")
print(df["Diagnosis"].value_counts())
print("\nColumns Included:")
for c in df.columns:
    print(" -", c)
print("\nSample preview:")
print(df.sample(5))
