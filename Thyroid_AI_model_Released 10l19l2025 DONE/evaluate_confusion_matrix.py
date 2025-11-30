import json
import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns
from sklearn.metrics import confusion_matrix, classification_report
import joblib
from sklearn.model_selection import train_test_split
import warnings
import numpy as np
import os

warnings.filterwarnings("ignore", category=UserWarning)

# =====================================================
# 1 Configuration
# =====================================================
CONFIG = {
    "lab": {
        "dataset": "thyroid_lab_reference_600.csv",
        "model": "thyroid_rf_lab.pkl",
        "scaler": "thyroid_lab_scaler.pkl",
        "metrics": "lab_model_metrics.json",
        "title": "Hybrid Lab-Based Random Forest"
    },
    "symptom": {
        "dataset": "thyroid_symptom_reference_600.csv",
        "model": "thyroid_rf_symptom_only.pkl",
        "scaler": "thyroid_symptom_scaler.pkl",
        "metrics": "symptom_only_metrics.json",
        "title": "Symptom-Only Random Forest"
    }
}

# =====================================================
# 2 Select Which Model to Evaluate
# =====================================================
mode = "lab"
cfg = CONFIG[mode]

print(f"\n Evaluating {cfg['title']} using {cfg['dataset']}")

# =====================================================
# 3 Load Dataset
# =====================================================
if not os.path.exists(cfg["dataset"]):
    raise FileNotFoundError(f" Dataset not found: {cfg['dataset']}")

df = pd.read_csv(cfg["dataset"], encoding="utf-8-sig")
df = df.fillna(0)
df["Diagnosis"] = df["Diagnosis"].astype(str).str.title()

expected_labels = ["Normal", "Hypothyroid", "Hyperthyroid"]
label_map = {"Normal": 0, "Hypothyroid": 1, "Hyperthyroid": 2}
inv_label_map = {v: k for k, v in label_map.items()}

X = df.drop(columns=["Diagnosis"])
y = df["Diagnosis"].map(label_map)

# =====================================================
# 4 Load Model + Scaler
# =====================================================
if not os.path.exists(cfg["model"]):
    raise FileNotFoundError(f" Model not found: {cfg['model']}")
if not os.path.exists(cfg["scaler"]):
    raise FileNotFoundError(f" Scaler not found: {cfg['scaler']}")

model = joblib.load(cfg["model"])
scaler = joblib.load(cfg["scaler"])

X_scaled = scaler.transform(X)

# =====================================================
# 5 Train/Test Split
# =====================================================
X_train, X_test, y_train, y_test = train_test_split(
    X_scaled, y, test_size=0.2, stratify=y, random_state=42
)
y_pred = model.predict(X_test)

y_pred_labels = pd.Series(y_pred).map(inv_label_map)
y_test_labels = pd.Series(y_test).map(inv_label_map)

# =====================================================
# 6 Confusion Matrix + Classification Report
# =====================================================
labels = expected_labels
cm = confusion_matrix(y_test_labels, y_pred_labels, labels=labels)

print("\n Classification Report:")
print(classification_report(y_test_labels, y_pred_labels, target_names=labels, zero_division=0))

plt.figure(figsize=(7, 6))
sns.heatmap(cm, annot=True, fmt='d', cmap='Blues',
            xticklabels=labels, yticklabels=labels)
plt.title(f" {cfg['title']} — Confusion Matrix")
plt.xlabel("Predicted Label")
plt.ylabel("True Label")
plt.tight_layout()
plt.savefig(f"confusion_matrix_{mode}.png")
plt.show()

print("\n Confusion Matrix Summary:")
print(pd.DataFrame(cm,
                   index=[f"Actual_{l}" for l in labels],
                   columns=[f"Pred_{l}" for l in labels]))

# =====================================================
# 7 Metrics Summary
# =====================================================
if os.path.exists(cfg["metrics"]):
    with open(cfg["metrics"], "r", encoding="utf-8") as f:
        metrics = json.load(f)
    print("\n Saved Metrics from Training:")
    print(json.dumps(metrics, indent=4))
else:
    print("\n No metrics file found for this model.")
