# ===============================================
# train_model_proper_lab_v2.py — Realistic Hybrid Thyroid AI Model Trainer (Fixed Alignment)
# Dataset: thyroid_lab_reference_600.csv (Symptoms + Lab Values)
# Stratified 70/20/10 Split + Multi-Model Training + Cross-Validation
#  Excludes Label column from training features
#  Ensures scaler aligns perfectly with app.py
# ===============================================

import os
import json
import warnings
import numpy as np
import pandas as pd
from sklearn.preprocessing import StandardScaler
from sklearn.ensemble import RandomForestClassifier, GradientBoostingClassifier
from sklearn.svm import SVC
from sklearn.metrics import (
    accuracy_score, precision_score, recall_score, f1_score,
    confusion_matrix, classification_report
)
from sklearn.model_selection import train_test_split, cross_val_score
import joblib
import matplotlib.pyplot as plt
import seaborn as sns

warnings.filterwarnings("ignore")

# Optional SHAP
try:
    import shap
    SHAP_AVAILABLE = True
except Exception:
    SHAP_AVAILABLE = False

# -----------------------------
# Config / Paths

current_dir = os.path.dirname(os.path.abspath(__file__))
DATASET_PATH = os.path.join(current_dir, "thyroid_lab_reference_600.csv")
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

print(f" Loaded dataset with {len(df)} rows and {len(df.columns)} columns.")

# Ensure Diagnosis
df["Diagnosis"] = df["Diagnosis"].astype(str).str.strip().str.title()
expected_labels = ["Normal", "Hypothyroid", "Hyperthyroid"]
print(" Class counts:", df["Diagnosis"].value_counts().to_dict())

# Encode Sex (if present)
if "Sex" in df.columns:
    df["Sex"] = df["Sex"].replace({"Male": 1, "M": 1, "Female": 0, "F": 0}).fillna(0)

# -----------------------------
# Define Features & Labels
# -----------------------------
label_map = {"Normal": 0, "Hypothyroid": 1, "Hyperthyroid": 2}
df["Label"] = df["Diagnosis"].map(label_map)

#  EXCLUDE the label column from features
feature_columns = [c for c in df.columns if c not in ["Diagnosis", "Label"]]
print(f" Using {len(feature_columns)} lab+symptom features for model training.")


train_idx, test_idx, val_idx = [], [], []
for label in expected_labels:
    label_df = df[df["Diagnosis"] == label]
    indices = label_df.index.tolist()
    train_part, temp_part = train_test_split(indices, test_size=0.3, random_state=RANDOM_STATE)
    test_part, val_part = train_test_split(temp_part, test_size=0.5, random_state=RANDOM_STATE)
    train_idx += train_part
    test_idx += test_part
    val_idx += val_part

train_df = df.loc[train_idx]
test_df = df.loc[test_idx]
val_df = df.loc[val_idx]

X_train, y_train = train_df[feature_columns], train_df["Label"]
X_test, y_test = test_df[feature_columns], test_df["Label"]
X_val, y_val = val_df[feature_columns], val_df["Label"]

print(f" Train={len(X_train)} | Test={len(X_test)} | Val={len(X_val)}")

# -----------------------------
# Scale Data
# -----------------------------
scaler = StandardScaler()
X_train_scaled = scaler.fit_transform(X_train)
X_test_scaled = scaler.transform(X_test)
X_val_scaled = scaler.transform(X_val)
joblib.dump(scaler, os.path.join(OUTPUT_DIR, "thyroid_lab_scaler.pkl"))
print(" Saved thyroid_lab_scaler.pkl")

# -----------------------------
# Train Models (RF, SVM, GB)
# -----------------------------
print("\n Training Models...")

rf = RandomForestClassifier(
    n_estimators=300,
    max_depth=7,
    min_samples_leaf=4,
    class_weight="balanced",
    random_state=RANDOM_STATE
)
svm = SVC(
    probability=True,
    kernel="rbf",
    C=0.9,
    gamma="scale",
    class_weight="balanced",
    random_state=RANDOM_STATE
)
gb = GradientBoostingClassifier(
    n_estimators=180,
    learning_rate=0.1,
    max_depth=3,
    subsample=0.85,
    random_state=RANDOM_STATE
)

rf.fit(X_train_scaled, y_train)
svm.fit(X_train_scaled, y_train)
gb.fit(X_train_scaled, y_train)

joblib.dump(rf, "thyroid_rf_lab.pkl")
joblib.dump(svm, "thyroid_svm_lab.pkl")
joblib.dump(gb, "thyroid_gb_lab.pkl")
print(" Saved models: RF / SVM / GB")

# -----------------------------
# Evaluation Function
# -----------------------------
def evaluate_model(model, X, y_true, name, split_name):
    y_pred = model.predict(X)
    acc = accuracy_score(y_true, y_pred)
    prec = precision_score(y_true, y_pred, average="weighted")
    rec = recall_score(y_true, y_pred, average="weighted")
    f1 = f1_score(y_true, y_pred, average="weighted")
    cm = confusion_matrix(y_true, y_pred)

    print(f"\n {name} ({split_name}) Results:")
    print(f"Accuracy={acc:.4f} | Precision={prec:.4f} | Recall={rec:.4f} | F1={f1:.4f}")

    sns.heatmap(cm, annot=True, fmt="d", cmap="Blues",
                xticklabels=expected_labels, yticklabels=expected_labels)
    plt.title(f"{name} ({split_name}) Confusion Matrix")
    plt.tight_layout()
    plt.savefig(f"confusion_{name.lower()}_{split_name.lower()}_lab.png")
    plt.close()

    report = classification_report(
        y_true, y_pred, target_names=expected_labels, output_dict=True, zero_division=0
    )

    return {
        "accuracy": float(acc),
        "precision": float(prec),
        "recall": float(rec),
        "f1_score": float(f1),
        "confusion_matrix": cm.tolist(),
        "per_class": report
    }

# -----------------------------
# Evaluate Models
# -----------------------------
metrics = {"Train": {}, "Test": {}, "Validation": {}, "CrossVal": {}}

for name, model in [("RandomForest", rf), ("SVM", svm), ("GradientBoosting", gb)]:
    metrics["Train"][name] = evaluate_model(model, X_train_scaled, y_train, name, "Train")
    metrics["Test"][name] = evaluate_model(model, X_test_scaled, y_test, name, "Test")
    metrics["Validation"][name] = evaluate_model(model, X_val_scaled, y_val, name, "Validation")

# -----------------------------
# Cross-Validation (5-fold)
# -----------------------------
print("\n 5-Fold Cross-Validation:")
for name, model in [("RandomForest", rf), ("SVM", svm), ("GradientBoosting", gb)]:
    scores = cross_val_score(model, X_train_scaled, y_train, cv=5, scoring="accuracy")
    metrics["CrossVal"][name] = {"mean_accuracy": float(scores.mean()), "std_dev": float(scores.std())}
    print(f"{name} CV Accuracy: {scores.mean():.4f} ± {scores.std():.4f}")

# -----------------------------
# Save Metrics
# -----------------------------
with open("lab_model_metrics.json", "w", encoding="utf-8") as f:
    json.dump(metrics, f, indent=4)
print(" Saved lab_model_metrics.json")

# -----------------------------
# Optional SHAP Export
# -----------------------------
if SHAP_AVAILABLE:
    try:
        print("\n Generating SHAP explainer (RF)...")
        explainer = shap.TreeExplainer(rf)
        joblib.dump(explainer, os.path.join(OUTPUT_DIR, "thyroid_lab_shap_explainer.pkl"))
        print(" Saved thyroid_lab_shap_explainer.pkl")
    except Exception as e:
        print(" SHAP generation skipped:", e)

# -----------------------------
# Save Training Summary
# -----------------------------
summary = {
    "train_rows": len(X_train),
    "test_rows": len(X_test),
    "val_rows": len(X_val),
    "features": feature_columns,
    "classes": expected_labels,
    "class_counts": df["Diagnosis"].value_counts().to_dict()
}
with open("training_summary_lab.json", "w", encoding="utf-8") as f:
    json.dump(summary, f, indent=4)

print("\n Hybrid Lab-Based Model Training Complete (v2 Fixed Alignment) ")
print(json.dumps(summary, indent=2))
