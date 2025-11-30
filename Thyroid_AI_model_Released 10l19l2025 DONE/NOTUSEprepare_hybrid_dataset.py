# ===============================================
# prepare_hybrid_dataset.py — Auto-Fix Hybrid Dataset
# ===============================================
#  Purpose:
# This script upgrades your thyroid dataset to match the hybrid AI model schema
# (used in train_model.py). It auto-fixes missing fields and ensures consistent encoding.

import pandas as pd
import os

# ===============================================
# 1 File Paths
# ===============================================
SOURCE_FILE = r"C:\Users\rxkyfr\thyroid_ai_model\thyroid_dataset_FINAL_.csv"
TARGET_FILE = r"C:\Users\rxkyfr\thyroid_ai_model\thyroid_dataset_FINAL_HYBRID.csv"

if not os.path.exists(SOURCE_FILE):
    raise FileNotFoundError(f" Dataset not found at: {SOURCE_FILE}")

print(" Loading dataset...")
#  use latin1 to handle special characters (µ etc.)
df = pd.read_csv(SOURCE_FILE, encoding="latin1")
print(f" Loaded {len(df)} rows, {len(df.columns)} columns")

# ===============================================
# 2 Normalize Column Names
# ===============================================
rename_map = {
    "HighBP": "HighBloodPressure",
    "Depression/Anxiety": "DepressionAnxiety",
    "Sex ": "Sex",
    "FTI ": "FTI",
    "T4 µg/dl": "T4 µg/dL",   # normalize µ symbol capitalization
    "T4 ug/dl": "T4 µg/dL"
}
df.rename(columns=rename_map, inplace=True)
df.columns = [c.strip() for c in df.columns]

# ===============================================
# 3 Ensure Required Hybrid Columns Exist
# ===============================================
expected_columns = [
    "Age", "Sex", "Weight", "Height", "BMI",
    "Diabetes", "HighBloodPressure", "HighCholesterol", "Anemia",
    "DepressionAnxiety", "HeartDisease", "MenstrualIrregularities", "AutoimmuneDiseases",
    "FH_Hypothyroidism", "FH_Hyperthyroidism", "FH_Goiter", "FH_ThyroidCancer",
    "Sym_Fatigue", "Sym_WeightChange", "Sym_DrySkin", "Sym_HairLoss",
    "Sym_HeartRate", "Sym_Digestion", "Sym_IrregularPeriods", "Sym_NeckSwelling",
    "TSH mIU/L", "T3 ng/dL", "T4 µg/dL", "FTI",
    "Diagnosis", "image_prediction_encoded"
]

# Add missing columns with default 0
for col in expected_columns:
    if col not in df.columns:
        df[col] = 0
        print(f" Added missing column: {col}")

# ===============================================
# 4 Encode Text (Yes/No, Male/Female)
# ===============================================
for col in df.columns:
    if df[col].dtype == "object":
        df[col] = df[col].astype(str).str.strip().str.lower().map({
            "yes": 1, "no": 0, "male": 1, "female": 0
        }).fillna(df[col])

# ===============================================
# 5 Handle Age, Sex, and CNN Encoded Column
# ===============================================
# Age — if missing or zero, fill with average (35)
if "Age" in df.columns:
    df["Age"] = pd.to_numeric(df["Age"], errors="coerce").fillna(35)
    df.loc[df["Age"] == 0, "Age"] = 35

# Sex — ensure numeric (1 = Male, 0 = Female)
if "Sex" in df.columns:
    df["Sex"] = pd.to_numeric(df["Sex"], errors="coerce").fillna(1).astype(int)
    df.loc[~df["Sex"].isin([0, 1]), "Sex"] = 1

# CNN encoded column — always exists and defaults to 0
df["image_prediction_encoded"] = pd.to_numeric(df["image_prediction_encoded"], errors="coerce").fillna(0).astype(int)

# ===============================================
# 6 Replace missing lab values with physiological midrange defaults
# ===============================================
lab_defaults = {
    "TSH mIU/L": 2.5,
    "T3 ng/dL": 120,
    "T4 µg/dL": 8.0,
    "FTI": 6.0
}
for lab, val in lab_defaults.items():
    if lab in df.columns:
        df[lab] = pd.to_numeric(df[lab], errors="coerce").fillna(val)
        df.loc[df[lab] == 0, lab] = val

# ===============================================
# 7 Final Reordering (Consistent Schema)
# ===============================================
df = df[expected_columns]

# ===============================================
# 8 Save Clean Hybrid Dataset
# ===============================================
df.to_csv(TARGET_FILE, index=False, encoding="utf-8-sig")

print("\n Hybrid dataset prepared successfully!")
print(f" Saved as: {TARGET_FILE}")
print(f" Final shape: {df.shape[0]} rows × {df.shape[1]} columns")
print("\nYou can now train using:")
print(" python train_model.py")
