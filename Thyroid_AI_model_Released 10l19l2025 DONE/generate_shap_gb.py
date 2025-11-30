import pandas as pd
import numpy as np
import joblib
import shap
import os

# Paths
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DATASET_PATH = os.path.join(BASE_DIR, 'thyroid_combined_reference_sample.csv')
GB_MODEL_PATH = os.path.join(BASE_DIR, 'thyroid_gb_model.pkl')
SCALER_PATH = os.path.join(BASE_DIR, 'thyroid_scaler.pkl')
SHAP_EXPLAINER_GB_PATH = os.path.join(BASE_DIR, 'thyroid_gb_shap_explainer.pkl')

# Load dataset
df = pd.read_csv(DATASET_PATH, encoding='utf-8-sig')
df.columns = df.columns.str.strip()
df = df.fillna(0)

# Load model and scaler first
gb_model = joblib.load(GB_MODEL_PATH)
scaler = joblib.load(SCALER_PATH)

# Prepare features
features = [c for c in df.columns if c not in ['Diagnosis', 'Mode']]
X = df[features]

# Ensure feature names match scaler
scaler_features = list(scaler.feature_names_in_)
X = X.reindex(columns=scaler_features, fill_value=0)

y = df['Diagnosis'].map({'Normal': 0, 'Hypothyroid': 1, 'Hyperthyroid': 2})

# Scale data
X_scaled = scaler.transform(X)

# Create SHAP explainer (use KernelExplainer for multi-class)
background = X_scaled[:100]  # Use a subset as background
explainer_gb = shap.KernelExplainer(gb_model.predict_proba, background)
joblib.dump(explainer_gb, SHAP_EXPLAINER_GB_PATH)
print('GB SHAP explainer saved.')
