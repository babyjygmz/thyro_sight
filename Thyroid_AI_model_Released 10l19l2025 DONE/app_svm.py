# ===============================================
# app_svm.py — Thyroid AI API with SVM (Unified v3.1)
# ===============================================

from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib, numpy as np, pandas as pd, json, os, traceback, logging, warnings
from smart_case_utils import normalize_input, detect_has_lab, cosine_match, confirm_case

warnings.simplefilter(action="ignore", category=FutureWarning)
pd.set_option("future.no_silent_downcasting", True)

app = Flask(__name__)
CORS(app)

# -----------------------------
# Paths
# -----------------------------
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
SVM_MODEL_PATH = os.path.join(BASE_DIR, "thyroid_svm_model.pkl")
SCALER_PATH = os.path.join(BASE_DIR, "thyroid_scaler.pkl")
DATASET_PATH = os.path.join(BASE_DIR, "thyroid_combined_reference_sample.csv")
LOG_FILE = os.path.join(BASE_DIR, "api_requests_svm.log")

# -----------------------------
# Logging Setup
# -----------------------------
logging.basicConfig(filename=LOG_FILE, level=logging.INFO,
                    format="%(asctime)s - %(levelname)s - %(message)s")

# -----------------------------
# Utility Loader
# -----------------------------
def safe_load(path):
    try:
        return joblib.load(path)
    except Exception as e:
        print(f" Failed to load {path}: {e}")
        return None

# -----------------------------
# Preload Model + Scaler + Data
# -----------------------------
svm_model = safe_load(SVM_MODEL_PATH)
scaler = safe_load(SCALER_PATH)

def load_dataset(path):
    if not os.path.exists(path):
        return pd.DataFrame()
    df = pd.read_csv(path, encoding="utf-8-sig")
    df.columns = df.columns.str.strip()
    return df

df = load_dataset(DATASET_PATH)

# -----------------------------
# Build Class Splits
# -----------------------------
def build_class_splits(df, per_class=200):
    if df.empty or "Diagnosis" not in df.columns:
        return {"Normal": pd.DataFrame(), "Hypothyroid": pd.DataFrame(), "Hyperthyroid": pd.DataFrame()}
    return {
        "Normal": df[df["Diagnosis"] == "Normal"].head(per_class),
        "Hypothyroid": df[df["Diagnosis"] == "Hypothyroid"].head(per_class),
        "Hyperthyroid": df[df["Diagnosis"] == "Hyperthyroid"].head(per_class),
    }

splits = build_class_splits(df)

# -----------------------------
# Normalize Columns (Safe)
# -----------------------------
def normalize_columns(df, model_features):
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
    return df[model_features]

# -----------------------------
# /predict Endpoint
# -----------------------------
@app.route("/predict", methods=["POST"])
def predict():
    try:
        data = request.get_json()
        if not data:
            return jsonify({"success": False, "message": "No JSON data received"}), 400
        logging.info(f"SVM received: {json.dumps(data)}")

        # Normalize + detect mode
        normalized_data = normalize_input(data)
        has_lab = detect_has_lab(normalized_data)

        # Case-based reasoning
        best_class, sim_score, scores = cosine_match(normalized_data, splits, has_lab)
        validation_conf = confirm_case(best_class, normalized_data, splits, has_lab)

        # Features for this dataset
        normal_split = splits.get("Normal", pd.DataFrame())
        if not normal_split.empty:
            model_features = [c for c in normal_split.columns if c not in ["Diagnosis", "Label"]]
        else:
            # fallback if empty
            model_features = list(normalized_data.keys())

        df_input = normalize_columns(pd.DataFrame([normalized_data]), model_features)

        #  Scaler-safe feature alignment (fix for "feature names must match" errors)
        if scaler is not None and hasattr(scaler, "feature_names_in_"):
            expected_order = list(map(str, getattr(scaler, "feature_names_in_").tolist()))
            expected_order = [c for c in expected_order if c.lower() != "label"]
            present = [c for c in expected_order if c in df_input.columns]
            missing = [c for c in expected_order if c not in df_input.columns]
            if missing:
                logging.info(f"⚙️ Adding {len(missing)} missing features: {missing}")
            for c in missing:
                df_input[c] = 0.0
            df_input = df_input[expected_order]

        # Scale data
        df_scaled = scaler.transform(df_input) if scaler is not None else df_input.values

        # Predict
        svm_pred = svm_model.predict(df_scaled)[0]
        svm_prob = float(np.max(svm_model.predict_proba(df_scaled)[0]) * 100)
        svm_label = ["Normal", "Hypothyroid", "Hyperthyroid"][int(svm_pred)]

        # Decision fusion with case-based
        if best_class == svm_label:
            final_label = best_class
            final_conf = (validation_conf * 0.6) + (svm_prob * 0.4)
        else:
            final_label = svm_label if has_lab else best_class or "Uncertain"
            final_conf = (validation_conf * 0.5) + (svm_prob * 0.5)

        # Confidence message
        if sim_score < 60 and svm_prob < 70:
            message = " Low similarity & confidence — recommend further lab evaluation."
        else:
            message = f" SVM {'Hybrid (Lab-Assisted)' if has_lab else 'Symptom-Only'} prediction successful."

        return jsonify({
            "success": True,
            "prediction": final_label,
            "confidence": round(final_conf, 2),
            "svm_prediction": svm_label,
            "svm_confidence": round(svm_prob, 2),
            "case_similarity": sim_score,
            "validation_confidence": validation_conf,
            "class_scores": scores,
            "mode": "Hybrid (Lab-Assisted)" if has_lab else "Symptom-Only",
            "message": message
        })

    except Exception as e:
        logging.error(traceback.format_exc())
        return jsonify({"success": False, "message": str(e)}), 500

# -----------------------------
# Root Endpoint
# -----------------------------
@app.route("/")
def home():
    return jsonify({"message": "🧠 SVM Thyroid API active", "endpoints": ["/predict"]})

# -----------------------------
# Run Server
# -----------------------------
if __name__ == "__main__":
    app.run(port=5002, debug=True)
