# ===============================================
# thyroid_symptom_reference_sample_with_history.py
# -----------------------------------------------
# Generates a demonstration dataset for the Thyroid
# AI Diagnostic System (Symptom-Only + Medical History)
# -----------------------------------------------
# Output File: thyroid_symptom_reference_sample.csv
# Contains: 10 Normal, 10 Hypothyroid, 10 Hyperthyroid samples
# -----------------------------------------------
# Author: DevRoxx Web/Software Services
# Version: 2.0 (October 2025)
# ===============================================

import pandas as pd

# -----------------------------------------------
# Define Columns (includes medical history)
# -----------------------------------------------
columns = [
    "Age",
    "Gender",
    "Diabetes",
    "HighBloodPressure",
    "HighCholesterol",
    "Anemia",
    "DepressionAnxiety",
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
    "Diagnosis"
]

# -----------------------------------------------
# Define Data — 10 per class
# -----------------------------------------------

# Normal: healthy, no symptoms, no chronic history
normal_data = [
    [27,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,"Normal"],
    [25,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,"Normal"],
    [31,1,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,"Normal"],
    [45,1,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,"Normal"],
    [38,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,"Normal"],
    [34,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,"Normal"],
    [40,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,"Normal"],
    [29,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,"Normal"],
    [50,1,0,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,"Normal"],
    [36,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,"Normal"],
]

# Hypothyroid: fatigue, weight gain, dry skin, depression
hypo_data = [
    [42,0,0,0,1,0,1,0,1,0,0,0,1,1,1,1,0,1,1,0,"Hypothyroid"],
    [47,1,0,1,1,0,1,0,1,0,0,0,1,1,1,1,0,1,0,0,"Hypothyroid"],
    [39,0,0,0,0,1,1,0,1,0,0,0,1,1,1,0,0,1,1,0,"Hypothyroid"],
    [55,0,0,1,0,0,1,0,1,0,0,0,1,1,1,1,0,1,1,0,"Hypothyroid"],
    [36,1,0,0,1,0,1,0,1,0,0,0,1,1,1,1,0,1,0,0,"Hypothyroid"],
    [48,0,1,0,1,0,1,0,1,0,0,0,1,1,1,1,0,1,1,0,"Hypothyroid"],
    [51,0,0,1,0,1,1,0,1,0,0,0,1,1,1,0,0,1,1,0,"Hypothyroid"],
    [43,1,0,0,1,0,1,0,1,0,0,0,1,1,1,1,0,1,0,0,"Hypothyroid"],
    [57,0,0,1,1,0,1,0,1,0,0,0,1,1,1,1,0,1,1,0,"Hypothyroid"],
    [40,0,0,0,0,0,1,0,1,0,0,0,1,1,1,0,0,1,1,0,"Hypothyroid"],
]

# Hyperthyroid: weight loss, anxiety, rapid heart rate
hyper_data = [
    [33,1,0,0,0,0,1,1,0,1,0,0,0,1,0,1,1,1,0,1,"Hyperthyroid"],
    [29,1,0,0,0,0,1,0,0,1,0,0,0,1,0,1,1,1,0,0,"Hyperthyroid"],
    [38,0,0,0,0,0,1,1,0,1,0,0,0,1,0,1,1,1,1,1,"Hyperthyroid"],
    [41,1,0,0,0,0,1,1,0,1,0,0,0,1,0,1,1,1,0,1,"Hyperthyroid"],
    [35,0,0,0,0,0,1,1,0,1,0,0,0,1,0,1,1,1,1,0,"Hyperthyroid"],
    [46,1,0,0,0,0,1,1,0,1,0,0,0,1,0,1,1,1,0,1,"Hyperthyroid"],
    [37,1,0,0,0,0,1,1,0,1,0,0,0,1,0,1,1,1,0,1,"Hyperthyroid"],
    [32,0,0,0,0,0,1,1,0,1,0,0,0,1,0,1,1,1,1,0,"Hyperthyroid"],
    [44,1,0,0,0,0,1,1,0,1,0,0,0,1,0,1,1,1,0,1,"Hyperthyroid"],
    [39,0,0,0,0,0,1,1,0,1,0,0,0,1,0,1,1,1,1,1,"Hyperthyroid"],
]

# Combine data
data = normal_data + hypo_data + hyper_data

# Create DataFrame
df = pd.DataFrame(data, columns=columns)

# Save as CSV
output_file = "thyroid_symptom_reference_sample.csv"
df.to_csv(output_file, index=False, encoding="utf-8-sig")

# Print summary
print("Dataset created:", output_file)
print(df['Diagnosis'].value_counts())
