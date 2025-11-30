
import os
import json
import numpy as np
import pandas as pd
import joblib
import matplotlib.pyplot as plt
import seaborn as sns
from sklearn.preprocessing import StandardScaler
import shap
import tensorflow as tf
from tensorflow.keras.models import load_model
from tensorflow.keras.preprocessing.image import img_to_array, load_img
import pickle

# Suppress warnings
import warnings
warnings.filterwarnings("ignore")

# -----------------------------
# Config / Paths
# -----------------------------
current_dir = os.path.dirname(os.path.abspath(__file__))
DATASET_PATH = os.path.join(current_dir, "thyroid_lab_reference_600.csv")
RF_MODEL_PATH = os.path.join(current_dir, "thyroid_rf_lab.pkl")
SCALER_PATH = os.path.join(current_dir, "thyroid_lab_scaler.pkl")
CNN_MODEL_PATH = os.path.join(current_dir, "cnn", "thyroid_cnn_model.h5")
CNN_CLASS_INDICES_PATH = os.path.join(current_dir, "cnn", "class_indices.pkl")
CNN_LABEL_ENCODER_PATH = os.path.join(current_dir, "cnn", "cnn_label_encoder.pkl")
OUTPUT_DIR = current_dir
os.makedirs(OUTPUT_DIR, exist_ok=True)

# -----------------------------
# Load Dataset and Preprocess
# -----------------------------
print(" Loading dataset...")
df = pd.read_csv(DATASET_PATH, encoding="utf-8-sig")
df = df.fillna(0)
df.columns = df.columns.str.strip()

# Encode Diagnosis
label_map = {"Normal": 0, "Hypothyroid": 1, "Hyperthyroid": 2}
df["Label"] = df["Diagnosis"].map(label_map)

# Define features (exclude Diagnosis and Label)
feature_columns = [c for c in df.columns if c not in ["Diagnosis", "Label"]]
print(f" Using {len(feature_columns)} features for SHAP analysis.")

# Load scaler and scale data
scaler = joblib.load(SCALER_PATH)
X_scaled = scaler.transform(df[feature_columns])
y_true = df["Label"].values

# -----------------------------
# Load RandomForest Model
# -----------------------------
print(" Loading RandomForest model...")
rf_model = joblib.load(RF_MODEL_PATH)

# -----------------------------
# Load CNN Model and Components
# -----------------------------
print(" Loading CNN model...")
cnn_model = load_model(CNN_MODEL_PATH)

# Load class indices and label encoder
with open(CNN_CLASS_INDICES_PATH, 'rb') as f:
    class_indices = pickle.load(f)
class_labels = {v: k for k, v in class_indices.items()}

with open(CNN_LABEL_ENCODER_PATH, 'rb') as f:
    cnn_encoder = pickle.load(f)

print(f" CNN class labels: {class_labels}")

# -----------------------------
# Generate SHAP Explainer for RF
# -----------------------------
print(" Generating SHAP explainer for RandomForest...")
explainer_rf = shap.TreeExplainer(rf_model)

# Sample a subset for SHAP (e.g., 100 instances for efficiency)
sample_size = min(100, len(X_scaled))
X_sample = X_scaled[:sample_size]
y_sample = y_true[:sample_size]

# Compute SHAP values
shap_values_rf = explainer_rf.shap_values(X_sample)
print(f" SHAP values computed for {sample_size} samples.")

# -----------------------------
# SHAP Summary Plot for RF
# -----------------------------
print(" Generating SHAP summary plot...")
plt.figure(figsize=(10, 6))
shap.summary_plot(shap_values_rf, X_sample, feature_names=feature_columns, show=False)
plt.title("SHAP Summary Plot - RandomForest (Lab Features)")
plt.tight_layout()
plt.savefig(os.path.join(OUTPUT_DIR, "shap_summary_rf_lab_with_cnn.png"))
plt.close()
print(" Saved shap_summary_rf_lab_with_cnn.png")

# -----------------------------
# SHAP Feature Importance Bar Plot
# -----------------------------
print(" Generating SHAP feature importance...")
if len(shap_values_rf.shape) == 3:  # Multi-class: (n_samples, n_features, n_classes)
    shap_values_mean = np.abs(shap_values_rf).mean(axis=0).mean(axis=1)  # Mean over samples and classes
else:  # Binary or regression
    shap_values_mean = np.abs(shap_values_rf).mean(axis=0)

feature_importance = pd.DataFrame({
    'Feature': feature_columns,
    'Importance': shap_values_mean
}).sort_values('Importance', ascending=False)

plt.figure(figsize=(12, 8))
sns.barplot(x='Importance', y='Feature', data=feature_importance.head(20))
plt.title("Top 20 SHAP Feature Importances - RandomForest (Lab Features)")
plt.tight_layout()
plt.savefig(os.path.join(OUTPUT_DIR, "shap_feature_importance_rf_lab_with_cnn.png"))
plt.close()
print(" Saved shap_feature_importance_rf_lab_with_cnn.png")

# -----------------------------
# SHAP Waterfall Plots for Specific Instances
# -----------------------------
print(" Generating SHAP waterfall plots for specific instances...")
expected_labels = ["Normal", "Hypothyroid", "Hyperthyroid"]

for i in range(min(3, sample_size)):  # First 3 instances
    prediction = rf_model.predict(X_sample[i:i+1])[0]
    pred_label = expected_labels[prediction]

    plt.figure(figsize=(10, 6))
    shap.waterfall_plot(
        shap.Explanation(
            values=shap_values_rf[i, :, prediction],  # SHAP values for predicted class: (sample, feature, class)
            base_values=explainer_rf.expected_value[prediction],
            data=X_sample[i],
            feature_names=feature_columns
        ),
        show=False
    )
    plt.title(f"SHAP Waterfall Plot - Instance {i} (Predicted: {pred_label})")
    plt.tight_layout()
    plt.savefig(os.path.join(OUTPUT_DIR, f"shap_waterfall_rf_lab_instance_{i}_{pred_label.lower()}_with_cnn.png"))
    plt.close()
    print(f" Saved shap_waterfall_rf_lab_instance_{i}_{pred_label.lower()}_with_cnn.png")

# -----------------------------
# Hybrid SHAP with CNN Integration
# -----------------------------
print(" Integrating CNN predictions for hybrid SHAP...")

# For hybrid, we need to simulate or assume CNN predictions
# Since CNN is image-based, we'll use a placeholder or synthetic CNN prediction
# In a real scenario, you'd have image data corresponding to each sample

# For demonstration, generate synthetic CNN predictions (0, 1, 2 for classes)
np.random.seed(42)
cnn_predictions = np.random.choice([0, 1, 2], size=len(X_sample))

# Combine RF SHAP with CNN "confidence" (synthetic)
hybrid_shap_values = shap_values_rf.copy()
for i in range(len(X_sample)):
    # Amplify SHAP values if CNN agrees with RF prediction
    rf_pred = rf_model.predict(X_sample[i:i+1])[0]
    if rf_pred == cnn_predictions[i]:
        hybrid_shap_values[i, :, rf_pred] *= 1.2  # Boost agreement: (sample, feature, class)
    else:
        hybrid_shap_values[i, :, rf_pred] *= 0.8  # Dampen disagreement

# Note: shap_values_rf is (n_samples, n_features, n_classes)

# Hybrid summary plot
plt.figure(figsize=(10, 6))
shap.summary_plot(hybrid_shap_values, X_sample, feature_names=feature_columns, show=False)
plt.title("Hybrid SHAP Summary Plot - RF + CNN Agreement Boosted")
plt.tight_layout()
plt.savefig(os.path.join(OUTPUT_DIR, "shap_summary_hybrid_rf_cnn.png"))
plt.close()
print(" Saved shap_summary_hybrid_rf_cnn.png")

# -----------------------------
# Save SHAP Values to JSON
# -----------------------------
print(" Saving SHAP values to JSON...")
shap_data = {
    "sample_size": sample_size,
    "features": feature_columns,
    "shap_values_rf": shap_values_rf.tolist(),
    "expected_values_rf": explainer_rf.expected_value.tolist(),
    "feature_importance": feature_importance.to_dict('records'),
    "cnn_predictions_synthetic": cnn_predictions.tolist(),
    "hybrid_shap_values": hybrid_shap_values.tolist()
}

with open(os.path.join(OUTPUT_DIR, "shap_values_rf_cnn_hybrid.json"), "w") as f:
    json.dump(shap_data, f, indent=4)
print(" Saved shap_values_rf_cnn_hybrid.json")

# -----------------------------
# Print Summary
# -----------------------------
print("\n SHAP Analysis Complete!")
print(f" - Analyzed {sample_size} samples")
print(f" - Generated summary and feature importance plots")
print(f" - Created waterfall plots for individual instances")
print(f" - Integrated synthetic CNN predictions for hybrid explainability")
print(f" - All outputs saved in {OUTPUT_DIR}")
