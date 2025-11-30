# ===============================================
# generate_lab_reference_dataset_v3.py
# -----------------------------------------------
# Generates a realistic LAB-ASSISTED dataset for Thyroid AI
# Includes:
#   - Medical History (Diabetes, BP, Cholesterol, etc.)
#   - Family History (Thyroid-related)
#   - Symptoms (Fatigue, Hair Loss, etc.)
#   - Lab Values (TSH, T3, T4, FTI, Uptake)
#
# Output: thyroid_lab_reference_600.csv
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
# Probability map per class (index: Normal, Hypo, Hyper)
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
    "FH_Goiter": [0.10, 0.25, 0.40],
    "FH_ThyroidCancer": [0.10, 0.10, 0.15],

    # ------------------ Symptoms ------------------
    "Sym_Fatigue": [0.15, 0.80, 0.65],
    "Sym_WeightChange": [0.15, 0.75, 0.85],
    "Sym_DrySkin": [0.10, 0.65, 0.15],
    "Sym_HairLoss": [0.15, 0.60, 0.30],
    "Sym_HeartRate": [0.15, 0.55, 0.80],
    "Sym_Digestion": [0.15, 0.60, 0.75],
    "Sym_IrregularPeriods": [0.10, 0.65, 0.65],
    "Sym_NeckSwelling": [0.10, 0.30, 0.55],
}

# -----------------------------------------------
# LAB VALUE RANGES (slightly overlapping)
# -----------------------------------------------
lab_ranges = {
    "TSH mIU/L": [(0.3, 4.5), (3.5, 12.0), (0.05, 1.2)],
    "T3 ng/dL": [(80, 200), (50, 120), (100, 250)],
    "T4 (ng/dL)": [(5, 12), (3, 8), (8, 16)],
    "FTI": [(5, 8), (3, 6), (6, 10)],
    "T4 Uptake": [(25, 35), (20, 30), (30, 45)],
}

# -----------------------------------------------
# Generate records per condition
# -----------------------------------------------
for i, condition in enumerate(conditions):
    for _ in range(samples_per_class):
        # Demographics
        if condition == "Normal":
            age = int(np.random.normal(40, 10))
            sex = np.random.choice([0, 1], p=[0.5, 0.5])
        elif condition == "Hypothyroid":
            age = int(np.random.normal(52, 10))
            sex = np.random.choice([0, 1], p=[0.7, 0.3])
        else:
            age = int(np.random.normal(35, 8))
            sex = np.random.choice([0, 1], p=[0.65, 0.35])

        age = int(np.clip(age, 18, 80))

        record = {"Diagnosis": condition, "Age": age, "Sex": sex}

        # Add medical, family, and symptom features
        for feat, probs in feature_probs.items():
            record[feat] = np.random.choice([0, 1], p=[1 - probs[i], probs[i]])

        # Add lab values (with ±15% random noise)
        for lab, ranges in lab_ranges.items():
            low, high = ranges[i]
            val = np.random.uniform(low, high)
            val *= np.random.uniform(0.85, 1.15)
            # 10% missing labs
            if np.random.rand() < 0.1:
                record[lab] = np.nan
            else:
                record[lab] = round(val, 2)

        records.append(record)

# -----------------------------------------------
# Convert to DataFrame
# -----------------------------------------------
df = pd.DataFrame(records)

# Add 5% label noise (for realism)
num_noise = int(0.05 * len(df))
noise_idx = np.random.choice(df.index, num_noise, replace=False)
df.loc[noise_idx, "Diagnosis"] = np.random.choice(conditions, num_noise)

# -----------------------------------------------
# Reorder columns for consistency
# -----------------------------------------------
ordered_cols = [
    "Diagnosis", "Age", "Sex",
    "Diabetes", "HighBloodPressure", "HighCholesterol", "Anemia",
    "DepressionAnxiety", "HeartDisease", "AutoimmuneDiseases",
    "FH_Hypothyroidism", "FH_Hyperthyroidism", "FH_Goiter", "FH_ThyroidCancer",
    "Sym_Fatigue", "Sym_WeightChange", "Sym_DrySkin", "Sym_HairLoss",
    "Sym_HeartRate", "Sym_Digestion", "Sym_IrregularPeriods", "Sym_NeckSwelling",
    "TSH mIU/L", "T3 ng/dL", "T4 (ng/dL)", "FTI", "T4 Uptake"
]
df = df[ordered_cols]

# -----------------------------------------------
# Save dataset
# -----------------------------------------------
output_file = "thyroid_lab_reference_600.csv"
df.to_csv(output_file, index=False, encoding="utf-8-sig")

# -----------------------------------------------
# Summary
# -----------------------------------------------
print(" Generated 'thyroid_lab_reference_600.csv' (with Medical History, v3)")
print(df["Diagnosis"].value_counts())
print("\nColumns Included:")
for c in df.columns:
    print(" -", c)
print("\nSample preview:")
print(df.sample(5))
