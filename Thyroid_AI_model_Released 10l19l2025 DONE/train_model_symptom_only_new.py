# ===============================================
# train_model_symptom_only_v2.py — Symptom-Only Model Trainer (Clean + SHAP)
# Dataset: thyroid_symptom_reference_600.csv
#  Removes Label column from features
#  Perfect alignment with Flask API
#  Includes SHAP explainer for explainability
# ===============================================

import os
import json
import warnings
import numpy as np
import pandas as pd
from sklearn.preprocessing import StandardScaler
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import (
    accuracy_score, precision_score, recall_score, f1_score,
    confusion_matrix, classification_report
)
from sklearn.model_selection import train_test_split, cross_val_score
import joblib
import matplotlib.pyplot as plt
import seaborn as sns

warnings.filterwarnings("ignore")

# -----------------------------
# Optional SHAP Support
# -----------------------------
try:
    import shap
    SHAP_AVAILABLE = True
except Exception:
    SHAP_AVAILABLE = False

# -----------------------------
# Config / Paths (Fixed for portability)
# -----------------------------
current_dir = os.path.dirname(os.path.abspath(__file__))
DATASET_PATH = os.path.join(current_dir, "thyroid_symptom_reference_600.csv")
OUTPUT_DIR = current_dir
RANDOM_STATE = 42
os.makedirs(OUTPUT_DIR, exist_ok=True)


# -----------------------------
# Load Dataset
# -----------------------------
print(" Loading dataset...")
df = pd.read_csv(DATASET_PATH, encoding="utf-8-sig")
df = df.fillna(0)
df.columns = df.columns.str.strip()
print(f" Loaded {len(df)} rows, {len(df.columns)} columns.")

df["Diagnosis"] = df["Diagnosis"].astype(str).str.title()
label_map = {"Normal": 0, "Hypothyroid": 1, "Hyperthyroid": 2}
df["Label"] = df["Diagnosis"].map(label_map)

# -----------------------------
# Define Features
# -----------------------------
symptom_features = [c for c in df.columns if c not in ["Diagnosis", "Label"]]
print(f" Using {len(symptom_features)} symptom-only features for training.")

# -----------------------------
# Split Data
# -----------------------------
X_train, X_test, y_train, y_test = train_test_split(
    df[symptom_features],
    df["Label"],
    test_size=0.2,
    stratify=df["Label"],
    random_state=RANDOM_STATE
)
print(f" Split: TRAIN={len(X_train)}, TEST={len(X_test)}")

# -----------------------------
# Scale Data
# -----------------------------
scaler = StandardScaler()
X_train_scaled = scaler.fit_transform(X_train)
X_test_scaled = scaler.transform(X_test)
joblib.dump(scaler, "thyroid_symptom_scaler.pkl")
print(" Saved thyroid_symptom_scaler.pkl")

# -----------------------------
# Train Model
# -----------------------------
print("\n Training Symptom-Only Random Forest Model...")
rf = RandomForestClassifier(
    n_estimators=250,
    max_depth=8,
    min_samples_leaf=4,
    class_weight="balanced",
    random_state=RANDOM_STATE
)
rf.fit(X_train_scaled, y_train)
joblib.dump(rf, "thyroid_rf_symptom_only.pkl")
print(" Saved thyroid_rf_symptom_only.pkl")

# -----------------------------
# Evaluate Model
# -----------------------------
y_pred = rf.predict(X_test_scaled)
acc = accuracy_score(y_test, y_pred)
prec = precision_score(y_test, y_pred, average="weighted")
rec = recall_score(y_test, y_pred, average="weighted")
f1 = f1_score(y_test, y_pred, average="weighted")
cm = confusion_matrix(y_test, y_pred)

print("\n Test Results:")
print(f"Accuracy: {acc:.4f}")
print(f"Precision: {prec:.4f}")
print(f"Recall: {rec:.4f}")
print(f"F1 Score: {f1:.4f}")

expected_labels = ["Normal", "Hypothyroid", "Hyperthyroid"]
report = classification_report(
    y_test, y_pred, target_names=expected_labels, output_dict=True, zero_division=0
)

# -----------------------------
# Confusion Matrix Plot
# -----------------------------
plt.figure(figsize=(6, 5))
sns.heatmap(cm, annot=True, fmt="d", cmap="Blues",
            xticklabels=expected_labels, yticklabels=expected_labels)
plt.title(" Symptom-Only Random Forest Confusion Matrix")
plt.xlabel("Predicted")
plt.ylabel("Actual")
plt.tight_layout()
plt.savefig("confusion_symptom_only_rf.png")
plt.close()
print(" Saved confusion_symptom_only_rf.png")

# -----------------------------
# Cross-Validation
# -----------------------------
cv_scores = cross_val_score(rf, X_train_scaled, y_train, cv=5, scoring="accuracy")
print(f" Cross-Validation Accuracy: {cv_scores.mean():.4f} ± {cv_scores.std():.4f}")

# -----------------------------
# Save Metrics
# -----------------------------
metrics = {
    "RandomForest_SymptomOnly": {
        "accuracy": float(acc),
        "precision": float(prec),
        "recall": float(rec),
        "f1_score": float(f1),
        "confusion_matrix": cm.tolist(),
        "per_class": report,
        "cv_mean": float(cv_scores.mean()),
        "cv_std": float(cv_scores.std())
    }
}
with open("symptom_only_metrics.json", "w", encoding="utf-8") as f:
    json.dump(metrics, f, indent=4)
print(" Saved symptom_only_metrics.json")

# -----------------------------
# SHAP Explainer (NEW)
# -----------------------------
if SHAP_AVAILABLE:
    try:
        print("\n Generating SHAP explainer for Symptom-Only model...")
        explainer = shap.TreeExplainer(rf)
        joblib.dump(explainer, "thyroid_symptom_shap_explainer.pkl")
        print(" Saved thyroid_symptom_shap_explainer.pkl")
    except Exception as e:
        print(f" SHAP generation failed: {e}")
else:
    print(" SHAP not available — run 'pip install shap' to enable explainability.")

# -----------------------------
# Training Summary
# -----------------------------
summary = {
    "train_rows": len(X_train),
    "test_rows": len(X_test),
    "features": symptom_features,
    "classes": expected_labels,
    "class_counts": df["Diagnosis"].value_counts().to_dict()
}
with open("training_summary_symptom.json", "w", encoding="utf-8") as f:
    json.dump(summary, f, indent=4)

print("\n Symptom-Only Model Training Complete (v2.1 + SHAP) ")
print(json.dumps(summary, indent=2))
