# ===============================================
# app.py — Hybrid Thyroid AI API (v2.1 realistic) — FIXED
# Dual-Model System:
#    Hybrid Lab-Assisted Model  → thyroid_rf_lab.pkl
#    Symptom-Only Model         → thyroid_rf_symptom_only.pkl
# Integrated:
#   - Contextual Case Matching
#   - SHAP Explainability (lab + symptom explainers)
#   - Auto-switching logic
#   - Safe feature alignment (handles old scaler "Label")
# ===============================================

from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import numpy as np
import pandas as pd
import json
import os
import traceback
import logging
import warnings
from smart_case_utils import normalize_input, detect_has_lab, cosine_match, confirm_case

# -----------------------------
# Suppress harmless warnings
# -----------------------------
warnings.simplefilter(action="ignore", category=FutureWarning)
pd.set_option("future.no_silent_downcasting", True)

# -----------------------------
# Flask Init
# -----------------------------
app = Flask(__name__)
CORS(app)

# -----------------------------
# Paths
# -----------------------------
BASE_DIR = os.path.dirname(os.path.abspath(__file__))

# Models & scalers
RF_MODEL_LAB_PATH = os.path.join(BASE_DIR, "thyroid_rf_lab.pkl")
RF_MODEL_SYMPTOM_PATH = os.path.join(BASE_DIR, "thyroid_rf_symptom_only.pkl")

SCALER_LAB_PATH = os.path.join(BASE_DIR, "thyroid_lab_scaler.pkl")
SCALER_SYMPTOM_PATH = os.path.join(BASE_DIR, "thyroid_symptom_scaler.pkl")

# SHAP explainers (lab + symptom)
SHAP_EXPLAINER_LAB_PATH = os.path.join(BASE_DIR, "thyroid_lab_shap_explainer.pkl")
SHAP_EXPLAINER_SYMPTOM_PATH = os.path.join(BASE_DIR, "thyroid_symptom_shap_explainer.pkl")

# Datasets for CBR
DATASET_LAB_PATH = os.path.join(BASE_DIR, "thyroid_lab_reference_600.csv")
DATASET_SYMPTOM_PATH = os.path.join(BASE_DIR, "thyroid_symptom_reference_600.csv")

LOG_FILE = os.path.join(BASE_DIR, "api_requests.log")

# -----------------------------
# Logging Setup
# -----------------------------
logging.basicConfig(
    filename=LOG_FILE,
    level=logging.INFO,
    format="%(asctime)s - %(levelname)s - %(message)s"
)

# -----------------------------
# Utility: Safe Loader
# -----------------------------
def safe_load(path):
    try:
        return joblib.load(path)
    except Exception as e:
        logging.warning(f" Failed to load {path}: {e}")
        return None

# -----------------------------
# Preload Models + Scalers + SHAP explainers
# -----------------------------
rf_lab_model = safe_load(RF_MODEL_LAB_PATH)
rf_symptom_model = safe_load(RF_MODEL_SYMPTOM_PATH)
scaler_lab = safe_load(SCALER_LAB_PATH)
scaler_symptom = safe_load(SCALER_SYMPTOM_PATH)
explainer_lab = safe_load(SHAP_EXPLAINER_LAB_PATH)
explainer_symptom = safe_load(SHAP_EXPLAINER_SYMPTOM_PATH)

# -----------------------------
# Load Datasets (for Case Matching)
# -----------------------------
def load_dataset(path):
    if not os.path.exists(path):
        return pd.DataFrame()
    df = pd.read_csv(path, encoding="utf-8-sig")
    df.columns = df.columns.str.strip()
    return df

df_lab = load_dataset(DATASET_LAB_PATH)
df_symptom = load_dataset(DATASET_SYMPTOM_PATH)

# -----------------------------
# Build simplified class splits
# -----------------------------
def build_class_splits(df, per_class=200):
    if df.empty or "Diagnosis" not in df.columns:
        return {"Normal": pd.DataFrame(), "Hypothyroid": pd.DataFrame(), "Hyperthyroid": pd.DataFrame()}
    return {
        "Normal": df[df["Diagnosis"] == "Normal"].head(per_class),
        "Hypothyroid": df[df["Diagnosis"] == "Hypothyroid"].head(per_class),
        "Hyperthyroid": df[df["Diagnosis"] == "Hyperthyroid"].head(per_class),
    }

splits_lab = build_class_splits(df_lab)
splits_symptom = build_class_splits(df_symptom)

# -----------------------------
# Normalize Columns (ensure features exist)
# -----------------------------
def normalize_columns(df, model_features):
    """
    Clean column names and ensure all model_features exist (missing → 0).
    Returns df[model_features] in the same order supplied.
    """
    df = df.copy()
    df.columns = (
        df.columns.str.strip()
        .str.replace(r"\s+", " ", regex=True)
        .str.replace("ug/dl", "ng/dL", regex=False)
        .str.replace("µg/dL", "ng/dL", regex=False)
    )
    for col in model_features:
        if col not in df.columns:
            df[col] = 0
    # keep order
    return df[model_features]

# -----------------------------
# SHAP Value Extraction helper
# -----------------------------
def extract_shap_values(explainer_obj, df_scaled, df_input, pred_index=0):
    try:
        if explainer_obj is None:
            return []
        shap_values = explainer_obj.shap_values(df_scaled)
        if isinstance(shap_values, list):
            shap_array = np.array(shap_values[int(pred_index)])
        else:
            shap_array = np.array(shap_values)
        shap_row = shap_array[0] if shap_array.ndim == 2 else shap_array.flatten()
        abs_sum = np.sum(np.abs(shap_row)) or 1
        scaled_shap = (shap_row / abs_sum) * 100
        key_features = {"TSH mIU/L", "T4 (ng/dL)", "FTI", "Age", "Sym_Fatigue", "T3 ng/dL"}
        out = []
        cols = list(df_input.columns)
        for i, f in enumerate(cols):
            val = float(scaled_shap[i]) if i < len(scaled_shap) else 0.0
            out.append({"name": f, "impact": round(val, 2), "highlight": f in key_features})
        return out
    except Exception as e:
        logging.warning(f" SHAP extraction failed: {e}")
        return []

# -----------------------------
# /predict Route
# -----------------------------
@app.route("/predict", methods=["POST"])
def predict():
    try:
        data = request.get_json()
        if not data:
            return jsonify({"success": False, "message": "No JSON data received."}), 400

        logging.info(f"Received request: {json.dumps(data)}")
        normalized_data = normalize_input(data)
        has_lab = detect_has_lab(normalized_data)

        # Select model + scaler + dataset + explainer
        if has_lab:
            model = rf_lab_model
            scaler = scaler_lab
            explainer = explainer_lab
            model_type = "Hybrid (Lab-Assisted)"
            splits = splits_lab
        else:
            model = rf_symptom_model
            scaler = scaler_symptom
            explainer = explainer_symptom
            model_type = "Symptom-Only"
            splits = splits_symptom

        # sanity checks
        if model is None:
            return jsonify({"success": False, "message": f"Model not loaded for {model_type} mode."}), 500

        logging.info(f" Model Selected: {model_type}")

        # Case Matching Logic
        best_class, sim_score, class_scores = cosine_match(normalized_data, splits, has_lab)
        validation_conf = confirm_case(best_class, normalized_data, splits, has_lab) if best_class else 0.0

        # Prepare input for model
        normal_split = splits.get("Normal", pd.DataFrame())
        if not normal_split.empty:
            model_features = [c for c in normal_split.columns if c not in ["Diagnosis", "Label"]]
        else:
            # fallback to scaler feature names or input keys
            model_features = []
            if scaler is not None and hasattr(scaler, "feature_names_in_"):
                model_features = list(map(str, getattr(scaler, "feature_names_in_").tolist()))
                # remove Label if accidentally included
                model_features = [c for c in model_features if c.lower() != "label"]
            else:
                model_features = list(sorted(normalized_data.keys()))

        # Build df_input with the model_features order
        df_input = normalize_columns(pd.DataFrame([normalized_data]), model_features)

        # Align df_input with scaler.feature_names_in_ (exact order) — safe fallback for old Label-trained scalers
        if scaler is not None and hasattr(scaler, "feature_names_in_"):
            expected_order = list(map(str, getattr(scaler, "feature_names_in_").tolist()))
            # remove Label if present
            expected_order = [c for c in expected_order if c.lower() != "label"]
            # add any missing expected features with zeros and reorder to expected_order
            missing = [c for c in expected_order if c not in df_input.columns]
            for c in missing:
                df_input[c] = 0.0
            # If df_input contains extra columns not in expected_order, drop them
            df_input = df_input[[c for c in expected_order]]

        # Transform using scaler (or raw values)
        if scaler is not None:
            try:
                df_scaled = scaler.transform(df_input)
            except Exception as e:
                # log details and return helpful error
                logging.error(f"Scaler transform failed: {e}\nExpected features: {getattr(scaler, 'feature_names_in_', None)}\nInput columns: {list(df_input.columns)}")
                raise
        else:
            df_scaled = df_input.values

        # Prediction
        rf_pred = model.predict(df_scaled)[0]
        probs = model.predict_proba(df_scaled)[0] if hasattr(model, "predict_proba") else [0.33, 0.33, 0.33]
        rf_prob = float(np.max(probs) * 100)
        rf_label = ["Normal", "Hypothyroid", "Hyperthyroid"][int(rf_pred)]

        # Decision Fusion
        if best_class and rf_label == best_class:
            final_label = rf_label
            final_conf = (validation_conf * 0.4) + (rf_prob * 0.6)
        else:
            final_label = rf_label if has_lab else best_class or "Uncertain"
            final_conf = (validation_conf * 0.5) + (rf_prob * 0.5)

        message = (" Low confidence — recommend adding lab results." if final_conf < 50
                   else f" {model_type} prediction successful.")

        # SHAP values (use the appropriate explainer)
        shap_factors = extract_shap_values(explainer, df_scaled, df_input, int(rf_pred)) if explainer else []

        out = {
            "success": True,
            "prediction": final_label,
            "confidence": round(final_conf, 2),
            "rf_prediction": rf_label,
            "rf_confidence": round(rf_prob, 2),
            "case_similarity": float(sim_score or 0),
            "validation_confidence": round(float(validation_conf or 0), 2),
            "class_scores": class_scores,
            "mode": model_type,
            "shap_values": shap_factors,
            "message": message
        }
        logging.info(f"Prediction result: {json.dumps(out)}")
        return jsonify(out)

    except Exception as e:
        logging.error(traceback.format_exc())
        # don't leak internals — return error message
        return jsonify({"success": False, "message": "Prediction failed: " + str(e)}), 500

# -----------------------------
# Root
# -----------------------------
@app.route("/", methods=["GET"])
def home():
    return jsonify({
        "message": " Thyroid AI API Active — Auto-switches between Lab and Symptom models",
        "endpoints": ["/predict"]
    })

# -----------------------------
# Run Server
# -----------------------------
if __name__ == "__main__":
    # Use PORT from environment (Railway) or default to 5000
    port = int(os.environ.get("PORT", 5000))
    app.run(host="0.0.0.0", port=port, debug=False)
