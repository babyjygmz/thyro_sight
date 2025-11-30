"""
Example Flask API with SHAP Values Integration
This shows how to modify your existing Flask APIs to return real SHAP values

SETUP INSTRUCTIONS:
1. Install required packages:
   pip install flask flask-cors shap numpy pandas scikit-learn

2. Replace the prediction logic in your existing Flask apps (ports 5000, 5002, 5003)
   with the SHAP calculation code shown below

3. Make sure your trained model files are loaded correctly
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import numpy as np
import pandas as pd
import shap
import pickle
import traceback

app = Flask(__name__)
CORS(app)

# ========================================
# LOAD YOUR TRAINED MODEL
# ========================================
# Replace these paths with your actual model files
try:
    with open('models/random_forest_model.pkl', 'rb') as f:
        model = pickle.load(f)
    
    with open('models/feature_names.pkl', 'rb') as f:
        feature_names = pickle.load(f)
    
    print("✅ Model loaded successfully")
except Exception as e:
    print(f"⚠️ Warning: Could not load model - {e}")
    model = None
    feature_names = []

# ========================================
# FEATURE NAME MAPPING (User-Friendly Names)
# ========================================
FEATURE_DISPLAY_NAMES = {
    'age': 'Age',
    'gender': 'Gender',
    'tsh': 'TSH Level',
    't3': 'T3 Level',
    't4': 'T4 Level',
    'fti': 'Free Thyroxine Index',
    't4_uptake': 'T4 Uptake',
    'diabetes': 'Diabetes History',
    'high_blood_pressure': 'High Blood Pressure',
    'high_cholesterol': 'High Cholesterol',
    'anemia': 'Anemia',
    'depression_anxiety': 'Depression/Anxiety',
    'heart_disease': 'Heart Disease',
    'menstrual_irregularities': 'Menstrual Irregularities',
    'autoimmune_diseases': 'Autoimmune Disease History',
    'fh_hypothyroidism': 'Family History of Hypothyroidism',
    'fh_hyperthyroidism': 'Family History of Hyperthyroidism',
    'fh_goiter': 'Family History of Goiter',
    'fh_thyroid_cancer': 'Family History of Thyroid Cancer',
    'sym_fatigue': 'Fatigue/Weakness',
    'sym_weight_change': 'Weight Changes',
    'sym_dry_skin': 'Dry Skin',
    'sym_hair_loss': 'Hair Loss',
    'sym_heart_rate': 'Heart Rate Changes',
    'sym_digestion': 'Digestive Issues',
    'sym_irregular_periods': 'Irregular Periods',
    'sym_neck_swelling': 'Neck Swelling/Goiter'
}

# ========================================
# FEATURE DESCRIPTIONS
# ========================================
FEATURE_DESCRIPTIONS = {
    'tsh': 'TSH (Thyroid Stimulating Hormone) regulates thyroid function. High TSH indicates hypothyroidism, low TSH indicates hyperthyroidism.',
    't3': 'T3 (Triiodothyronine) is the active thyroid hormone. Abnormal levels indicate thyroid dysfunction.',
    't4': 'T4 (Thyroxine) is the main thyroid hormone. It converts to T3 in the body.',
    'age': 'Age is a significant risk factor. Thyroid disorders become more common after age 50.',
    'autoimmune_diseases': 'Autoimmune diseases often cluster together. Having one increases risk for thyroid autoimmune conditions.',
    'sym_fatigue': 'Fatigue is a hallmark symptom of hypothyroidism due to slowed metabolism.',
    'sym_weight_change': 'Unexplained weight changes are classic thyroid symptoms - gain in hypo, loss in hyper.',
    'sym_heart_rate': 'Heart rate changes reflect thyroid impact on metabolism - slow in hypo, fast in hyper.',
    'fh_hypothyroidism': 'Family history indicates genetic predisposition, increasing risk 3-5 times.',
    'high_cholesterol': 'High cholesterol is common in hypothyroidism due to reduced cholesterol clearance.'
}

# ========================================
# CALCULATE SHAP VALUES
# ========================================
def calculate_shap_values(model, input_data, feature_names, prediction):
    """
    Calculate SHAP values for the prediction
    Returns formatted SHAP factors for frontend display
    """
    try:
        # Create SHAP explainer
        # For tree-based models (Random Forest, Gradient Boosting)
        explainer = shap.TreeExplainer(model)
        
        # For linear models (SVM, Logistic Regression), use:
        # explainer = shap.LinearExplainer(model, input_data)
        
        # Calculate SHAP values
        shap_values = explainer.shap_values(input_data)
        
        # Handle multi-class output (shap_values might be a list)
        if isinstance(shap_values, list):
            # For multi-class, get SHAP values for the predicted class
            class_idx = ['normal', 'hypothyroid', 'hyperthyroid'].index(prediction.lower())
            shap_values_for_prediction = shap_values[class_idx][0]
        else:
            shap_values_for_prediction = shap_values[0]
        
        # Format SHAP values for frontend
        shap_factors = []
        
        for i, feature_name in enumerate(feature_names):
            shap_value = float(shap_values_for_prediction[i])
            
            # Only include features with significant impact (absolute value > 0.01)
            if abs(shap_value) > 0.01:
                # Get user-friendly name
                display_name = FEATURE_DISPLAY_NAMES.get(feature_name, feature_name.replace('_', ' ').title())
                
                # Get description
                description = FEATURE_DESCRIPTIONS.get(
                    feature_name,
                    f'This feature {"increases" if shap_value > 0 else "decreases"} the likelihood of {prediction}.'
                )
                
                # Add feature value context to description
                feature_value = input_data[0][i]
                if feature_name in ['tsh', 't3', 't4', 'fti', 't4_uptake']:
                    description = f'Your {display_name} is {feature_value:.2f}. {description}'
                elif feature_value == 1:
                    description = f'You have {display_name.lower()}. {description}'
                
                shap_factors.append({
                    'name': display_name,
                    'feature': feature_name,
                    'impact': round(shap_value * 100, 2),  # Convert to percentage
                    'type': 'positive' if shap_value > 0 else 'negative',
                    'description': description,
                    'value': float(feature_value)
                })
        
        # Sort by absolute impact
        shap_factors.sort(key=lambda x: abs(x['impact']), reverse=True)
        
        print(f"✅ Calculated {len(shap_factors)} SHAP factors")
        return shap_factors
        
    except Exception as e:
        print(f"❌ Error calculating SHAP values: {e}")
        traceback.print_exc()
        return []

# ========================================
# PREDICTION ENDPOINT
# ========================================
@app.route('/predict', methods=['POST'])
def predict():
    try:
        # Get input data
        data = request.get_json()
        print(f"📥 Received prediction request with {len(data)} features")
        
        if model is None:
            return jsonify({
                'success': False,
                'message': 'Model not loaded'
            }), 500
        
        # Prepare input data (adjust based on your model's expected format)
        # This is an example - modify based on your actual features
        input_features = []
        for feature in feature_names:
            value = data.get(feature, 0)
            input_features.append(value)
        
        input_array = np.array([input_features])
        
        # Make prediction
        prediction = model.predict(input_array)[0]
        
        # Get prediction probabilities
        if hasattr(model, 'predict_proba'):
            probabilities = model.predict_proba(input_array)[0]
            confidence = float(max(probabilities) * 100)
        else:
            confidence = 85.0  # Default confidence for models without probability
        
        # Map prediction to readable format
        prediction_map = {
            0: 'normal',
            1: 'hypothyroid',
            2: 'hyperthyroid',
            'normal': 'normal',
            'hypothyroid': 'hypothyroid',
            'hyperthyroid': 'hyperthyroid'
        }
        prediction_label = prediction_map.get(prediction, str(prediction))
        
        print(f"🎯 Prediction: {prediction_label} (confidence: {confidence:.1f}%)")
        
        # Calculate SHAP values
        shap_factors = calculate_shap_values(
            model, 
            input_array, 
            feature_names, 
            prediction_label
        )
        
        # Return response with SHAP values
        response = {
            'success': True,
            'prediction': prediction_label,
            'confidence': confidence,
            'shap_values': shap_factors,  # ⭐ This is the key addition!
            'shap_explanation': shap_factors,  # Alternative field name
            'feature_importance': shap_factors,  # Another alternative
            'message': 'Prediction successful with SHAP analysis',
            'model_type': 'Random Forest'  # or 'SVM', 'Gradient Boosting'
        }
        
        print(f"✅ Returning response with {len(shap_factors)} SHAP factors")
        return jsonify(response)
        
    except Exception as e:
        print(f"❌ Prediction error: {e}")
        traceback.print_exc()
        return jsonify({
            'success': False,
            'message': f'Prediction failed: {str(e)}'
        }), 500

# ========================================
# HEALTH CHECK ENDPOINT
# ========================================
@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        'status': 'healthy',
        'model_loaded': model is not None,
        'features': len(feature_names)
    })

# ========================================
# RUN SERVER
# ========================================
if __name__ == '__main__':
    print("🚀 Starting Flask API with SHAP support...")
    print("📊 SHAP values will be calculated for each prediction")
    print("🌐 Server running on http://127.0.0.1:5000")
    
    # For Random Forest model - use port 5000
    # For SVM model - use port 5002
    # For Gradient Boosting model - use port 5003
    app.run(host='127.0.0.1', port=5000, debug=True)
