# SHAP Backend Integration Guide

## Problem
Key factors are showing the same results for every prediction because the backend ML models aren't returning actual SHAP values. The frontend is falling back to client-side generation based on form inputs.

## Solution Overview
Modify your Python Flask APIs (ports 5000, 5002, 5003) to calculate and return real SHAP values from your trained models.

---

## Step 1: Install Required Packages

```bash
pip install shap
```

If you don't have other packages:
```bash
pip install flask flask-cors numpy pandas scikit-learn shap
```

---

## Step 2: Locate Your Flask API Files

Your Flask APIs should be running on:
- **Port 5000** - Random Forest model
- **Port 5002** - SVM model  
- **Port 5003** - Gradient Boosting model

Find these Python files (they might be named like):
- `rf_app.py` or `random_forest_server.py`
- `svm_app.py` or `svm_server.py`
- `gb_app.py` or `gradient_boosting_server.py`

---

## Step 3: Add SHAP Calculation Function

Add this function to each of your Flask API files:

```python
import shap
import numpy as np

def calculate_shap_values(model, input_data, feature_names, prediction):
    """
    Calculate SHAP values for the prediction
    Returns formatted SHAP factors for frontend display
    """
    try:
        # Create SHAP explainer based on model type
        
        # For Random Forest or Gradient Boosting:
        explainer = shap.TreeExplainer(model)
        
        # For SVM or Logistic Regression:
        # explainer = shap.LinearExplainer(model, input_data)
        
        # Calculate SHAP values
        shap_values = explainer.shap_values(input_data)
        
        # Handle multi-class output
        if isinstance(shap_values, list):
            # Get SHAP values for the predicted class
            class_map = {'normal': 0, 'hypothyroid': 1, 'hyperthyroid': 2}
            class_idx = class_map.get(prediction.lower(), 0)
            shap_values_array = shap_values[class_idx][0]
        else:
            shap_values_array = shap_values[0]
        
        # Format for frontend
        shap_factors = []
        
        for i, feature_name in enumerate(feature_names):
            shap_value = float(shap_values_array[i])
            
            # Only include significant features
            if abs(shap_value) > 0.01:
                shap_factors.append({
                    'name': format_feature_name(feature_name),
                    'feature': feature_name,
                    'impact': round(shap_value * 100, 2),
                    'type': 'positive' if shap_value > 0 else 'negative',
                    'description': get_feature_description(feature_name, input_data[0][i], shap_value),
                    'value': float(input_data[0][i])
                })
        
        # Sort by absolute impact
        shap_factors.sort(key=lambda x: abs(x['impact']), reverse=True)
        
        return shap_factors
        
    except Exception as e:
        print(f"Error calculating SHAP: {e}")
        return []

def format_feature_name(feature_name):
    """Convert technical feature names to user-friendly names"""
    name_map = {
        'tsh': 'TSH Levels',
        't3': 'T3 Levels',
        't4': 'T4 Levels',
        'fti': 'Free Thyroxine Index',
        't4_uptake': 'T4 Uptake',
        'age': 'Age',
        'gender': 'Gender',
        'diabetes': 'Diabetes History',
        'high_blood_pressure': 'High Blood Pressure',
        'high_cholesterol': 'High Cholesterol',
        'anemia': 'Anemia',
        'autoimmune_diseases': 'Autoimmune Disease History',
        'sym_fatigue': 'Fatigue/Weakness',
        'sym_weight_change': 'Weight Changes',
        'sym_heart_rate': 'Heart Rate Changes',
        'fh_hypothyroidism': 'Family History of Hypothyroidism'
    }
    return name_map.get(feature_name, feature_name.replace('_', ' ').title())

def get_feature_description(feature_name, value, shap_value):
    """Generate contextual description for each feature"""
    descriptions = {
        'tsh': f'Your TSH level is {value:.2f} mIU/L. {"High" if value > 4 else "Low" if value < 0.4 else "Normal"} TSH {"increases" if shap_value > 0 else "decreases"} the likelihood of this diagnosis.',
        't3': f'Your T3 level is {value:.2f} ng/dL. This {"supports" if shap_value > 0 else "contradicts"} the predicted condition.',
        't4': f'Your T4 level is {value:.2f} ng/dL. This {"contributes to" if shap_value > 0 else "works against"} the diagnosis.',
        'age': f'At age {int(value)}, this {"increases" if shap_value > 0 else "decreases"} your risk for thyroid disorders.',
        'autoimmune_diseases': 'Having autoimmune diseases increases risk for thyroid conditions, which often cluster together.',
        'sym_fatigue': 'Fatigue is a hallmark symptom of hypothyroidism due to slowed metabolism.',
        'sym_weight_change': 'Unexplained weight changes are classic thyroid symptoms.',
        'fh_hypothyroidism': 'Family history indicates genetic predisposition, increasing risk 3-5 times.'
    }
    
    default = f'This feature {"increases" if shap_value > 0 else "decreases"} the likelihood of the predicted condition.'
    return descriptions.get(feature_name, default)
```

---

## Step 4: Modify Your Prediction Endpoint

In your `/predict` endpoint, add SHAP calculation:

```python
@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.get_json()
        
        # Prepare input (your existing code)
        input_array = prepare_input(data)  # Your existing function
        
        # Make prediction (your existing code)
        prediction = model.predict(input_array)[0]
        confidence = get_confidence(model, input_array)  # Your existing function
        
        # ⭐ NEW: Calculate SHAP values
        shap_factors = calculate_shap_values(
            model, 
            input_array, 
            feature_names,  # Your model's feature names
            prediction
        )
        
        # Return response WITH SHAP values
        return jsonify({
            'success': True,
            'prediction': prediction,
            'confidence': confidence,
            'shap_values': shap_factors,      # ⭐ Add this
            'shap_explanation': shap_factors,  # ⭐ Add this (alternative name)
            'feature_importance': shap_factors, # ⭐ Add this (alternative name)
            'message': 'Prediction successful'
        })
        
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)}), 500
```

---

## Step 5: Test Your Changes

### Test 1: Check if SHAP is working

```python
# Add this test endpoint to your Flask app
@app.route('/test-shap', methods=['GET'])
def test_shap():
    try:
        # Create dummy input
        dummy_input = np.zeros((1, len(feature_names)))
        dummy_input[0][0] = 30  # age
        dummy_input[0][1] = 1   # gender
        
        # Test SHAP calculation
        explainer = shap.TreeExplainer(model)
        shap_values = explainer.shap_values(dummy_input)
        
        return jsonify({
            'status': 'SHAP working!',
            'shap_shape': str(np.array(shap_values).shape),
            'sample_values': str(shap_values[0][:5] if isinstance(shap_values, list) else shap_values[:5])
        })
    except Exception as e:
        return jsonify({'error': str(e)}), 500
```

Visit: `http://127.0.0.1:5000/test-shap`

### Test 2: Check prediction response

Use Postman or curl:

```bash
curl -X POST http://127.0.0.1:5000/predict \
  -H "Content-Type: application/json" \
  -d '{"age": 45, "gender": 0, "tsh": 5.2, "t3": 120, "t4": 8.5}'
```

Expected response should include:
```json
{
  "success": true,
  "prediction": "hypothyroid",
  "confidence": 87.5,
  "shap_values": [
    {
      "name": "TSH Levels",
      "impact": 25.3,
      "type": "positive",
      "description": "Your TSH level is 5.20 mIU/L..."
    },
    ...
  ]
}
```

---

## Step 6: Verify Frontend Integration

1. Open your health assessment page
2. Fill out the form with test data
3. Submit the assessment
4. Open browser console (F12)
5. Look for these log messages:
   - `✅ Found SHAP values in rfResult.shap_values`
   - `📊 Final SHAP values to display: [...]`

If you see:
- `⚠️ No SHAP values found in RF result` → Backend isn't returning SHAP values
- `⚠️ No SHAP values in results, checking alternative sources` → Check API response format

---

## Troubleshooting

### Issue 1: "No module named 'shap'"
```bash
pip install shap
```

### Issue 2: SHAP calculation is slow
```python
# Use a background sample for faster computation
background = shap.sample(training_data, 100)
explainer = shap.TreeExplainer(model, background)
```

### Issue 3: Different factors for same input
This is CORRECT behavior! Real SHAP values should vary based on:
- Actual feature values
- Model's learned patterns
- Feature interactions

### Issue 4: Frontend still showing same factors
Check:
1. Backend is returning `shap_values` in response
2. Frontend console shows "Found SHAP values"
3. Clear browser cache
4. Restart Flask servers

---

## Expected Results

### Before Fix:
- ❌ Same factors for all users with similar symptoms
- ❌ Generic descriptions
- ❌ No connection to actual model reasoning

### After Fix:
- ✅ Unique factors based on individual input values
- ✅ Specific descriptions with actual values
- ✅ Reflects true model decision-making process
- ✅ Different users get different factors even with similar symptoms

---

## Example Output

**User 1:** TSH=5.5, Age=45, Fatigue=Yes
```
Contributing Factors:
1. TSH Levels (High) - +28% - Your TSH is 5.50 mIU/L (elevated)
2. Age Factor - +15% - At age 45, risk increases
3. Fatigue/Weakness - +12% - Classic hypothyroid symptom
```

**User 2:** TSH=0.2, Age=32, Heart Rate=Yes
```
Contributing Factors:
1. TSH Levels (Low) - +32% - Your TSH is 0.20 mIU/L (suppressed)
2. Heart Rate Changes - +18% - Rapid heartbeat indicates hyperthyroid
3. Young Age - -8% - Lower risk at age 32
```

Notice how the factors are DIFFERENT and SPECIFIC to each user's data!

---

## Need Help?

If you encounter issues:
1. Check Flask server logs for errors
2. Verify model is loaded correctly
3. Test with `/test-shap` endpoint
4. Check browser console for frontend errors
5. Ensure all three Flask servers (5000, 5002, 5003) are updated

---

## Files to Modify

1. **Random Forest API** (port 5000) - Add SHAP calculation
2. **SVM API** (port 5002) - Add SHAP calculation  
3. **Gradient Boosting API** (port 5003) - Add SHAP calculation

Each should return `shap_values` in the response!
