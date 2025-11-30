# Quick Start: Add SHAP to Your Flask API

## The Problem
Your key factors are the same for everyone because your backend isn't returning real SHAP values.

## The Solution (3 Steps)

### Step 1: Install SHAP
```bash
pip install shap
```

### Step 2: Add This Code to Your Flask API

Find your Flask API file (the one running on port 5000, 5002, or 5003) and add:

```python
import shap

# Add this function BEFORE your /predict endpoint
def get_shap_factors(model, input_data, feature_names, prediction):
    """Calculate SHAP values and format for frontend"""
    try:
        # Create explainer (for Random Forest/Gradient Boosting)
        explainer = shap.TreeExplainer(model)
        
        # For SVM, use: explainer = shap.LinearExplainer(model, input_data)
        
        # Calculate SHAP values
        shap_vals = explainer.shap_values(input_data)
        
        # Handle multi-class
        if isinstance(shap_vals, list):
            class_idx = {'normal': 0, 'hypothyroid': 1, 'hyperthyroid': 2}[prediction.lower()]
            shap_vals = shap_vals[class_idx][0]
        else:
            shap_vals = shap_vals[0]
        
        # Format for frontend
        factors = []
        for i, name in enumerate(feature_names):
            val = float(shap_vals[i])
            if abs(val) > 0.01:  # Only significant features
                factors.append({
                    'name': name.replace('_', ' ').title(),
                    'impact': round(val * 100, 2),
                    'type': 'positive' if val > 0 else 'negative',
                    'description': f'Impact: {val:.3f}'
                })
        
        factors.sort(key=lambda x: abs(x['impact']), reverse=True)
        return factors
    except:
        return []
```

### Step 3: Modify Your /predict Endpoint

Find your existing `/predict` endpoint and add SHAP calculation:

```python
@app.route('/predict', methods=['POST'])
def predict():
    # ... your existing code ...
    
    # After you make the prediction:
    prediction = model.predict(input_array)[0]
    confidence = # ... your confidence calculation ...
    
    # ⭐ ADD THIS LINE:
    shap_factors = get_shap_factors(model, input_array, feature_names, prediction)
    
    # ⭐ ADD shap_values TO YOUR RESPONSE:
    return jsonify({
        'success': True,
        'prediction': prediction,
        'confidence': confidence,
        'shap_values': shap_factors  # ⭐ ADD THIS
    })
```

### Step 4: Restart Your Flask Server

```bash
# Stop the server (Ctrl+C)
# Start it again
python your_flask_app.py
```

### Step 5: Test It

Run the test script:
```bash
python test_backend_shap.py
```

You should see:
```
✅ Random Forest API is responding
   ✅ SHAP VALUES FOUND in 'shap_values'!
   Number of factors: 8
```

## That's It!

Now when users submit the health assessment:
- ✅ Each user gets UNIQUE factors based on their specific data
- ✅ Factors reflect the actual model's reasoning
- ✅ No more duplicate factors for everyone

## Before vs After

### Before (Same for Everyone):
```
Contributing Factors:
1. TSH Levels (High) - +25%
2. Autoimmune Disease History - +18%
3. Fatigue/Weakness - +15%
```

### After (Unique per User):
**User A (TSH=8.5, Age=55):**
```
Contributing Factors:
1. TSH Levels - +32% (Your TSH is 8.50)
2. Age Factor - +18% (Age 55 increases risk)
3. High Cholesterol - +14%
```

**User B (TSH=0.3, Age=28):**
```
Contributing Factors:
1. TSH Levels - +35% (Your TSH is 0.30)
2. Heart Rate Changes - +22%
3. Young Age - -12% (Protective factor)
```

Notice how they're DIFFERENT!

## Need More Help?

See the full guide: `SHAP_BACKEND_INTEGRATION_GUIDE.md`
