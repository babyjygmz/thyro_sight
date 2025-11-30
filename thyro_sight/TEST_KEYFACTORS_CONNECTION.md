# Testing Key Factors Connection

## Quick Test Guide

### Test Case 1: Hypothyroidism with High TSH
**Input:**
- TSH: 6.5 mIU/L (high)
- Age: 55
- Fatigue: Yes
- Weight gain: Yes
- High cholesterol: Yes

**Expected Key Factors:**
- ✅ **TSH Levels (High)** - "Your TSH level is 6.5 mIU/L (above normal >4.0)..." (+25%)
- ✅ **Fatigue/Weakness** - "You experience fatigue..." (+13%)
- ✅ **Unexplained Weight Gain** - "Weight gain is a classic symptom..." (+14%)
- ✅ **High Cholesterol** - "Common symptom of hypothyroidism..." (+12%)
- ✅ **Age Factor (50+)** - "At age 55, your risk is elevated..." (+12%)

### Test Case 2: Hyperthyroidism with Low TSH
**Input:**
- TSH: 0.2 mIU/L (low)
- Age: 35
- Rapid heart rate: Yes
- Weight loss: Yes
- Anxiety: Yes

**Expected Key Factors:**
- ✅ **TSH Levels (Low)** - "Your TSH level is 0.2 mIU/L (below normal <0.4)..." (+25%)
- ✅ **Rapid Heart Rate** - "Tachycardia is a cardinal sign..." (+16%)
- ✅ **Unexplained Weight Loss** - "Weight loss despite normal eating..." (+14%)
- ✅ **Anxiety** - "Hallmark symptom of hyperthyroidism..." (+11%)
- ❌ **Young Age** - "At age 35, thyroid disorders are less common..." (-6%)

### Test Case 3: Normal Thyroid
**Input:**
- TSH: 2.0 mIU/L (normal)
- Age: 28
- No symptoms
- No medical history
- No family history

**Expected Key Factors:**
- ✅ **TSH Levels (Normal)** - "Your TSH level is 2.0 mIU/L, within normal range..." (+18%)
- ✅ **Age Factor (<30)** - "At age 28, thyroid disorders are less common..." (+8%)
- ✅ **No Risk Factors Present** - "No significant medical conditions..." (+15%)

## How to Test

### 1. Open Browser Console
- Press F12 or right-click → Inspect
- Go to Console tab

### 2. Fill Out Form
- Enter test data from one of the cases above
- Upload a thyroid image

### 3. Submit Assessment
- Click "Submit Assessment"
- Watch console for logs

### 4. Check Console Logs
Look for these key messages:
```
🔍 Generating COMPREHENSIVE SHAP factors for condition: hypo
📋 Form Data: {tsh: "yes", tshValue: "6.5", ...}
🔄 Mapping prediction 'hypothyroid' to SHAP condition 'hypo'
✅ Generated SHAP factors from form data: 8 factors
📊 Final SHAP values to display: [...]
✅ Positive factors: 5
❌ Negative factors: 0
```

### 5. Verify Results Display
- Check "Key Factors Affecting Result" section
- Verify factors match your inputs
- Confirm percentages are shown
- Check descriptions are relevant

## Debugging

### If No Factors Show:
1. Check console for errors
2. Verify `enhanced_shap_factors.js` is loaded:
   ```javascript
   typeof generateEnhancedSHAPFactors
   // Should return: "function"
   ```
3. Verify `getFormData` exists:
   ```javascript
   typeof getFormData
   // Should return: "function"
   ```

### If Factors Don't Match Prediction:
1. Check condition mapping in console:
   ```
   🔄 Mapping prediction 'hypothyroid' to SHAP condition 'hypo'
   ```
2. Verify the condition parameter is correct
3. Check form data is being collected properly

### If Factors Are Generic:
1. Verify form fields have correct `name` attributes
2. Check field name normalization in `getFormData()`
3. Ensure radio buttons are checked (not just filled)

## Success Criteria
✅ Key Factors section displays factors
✅ Factors are relevant to the prediction
✅ Factors reference actual form inputs (TSH values, symptoms, etc.)
✅ Positive factors support the diagnosis
✅ Negative factors (if any) contradict the diagnosis
✅ Impact percentages are shown
✅ Descriptions are detailed and personalized

## Common Issues

### Issue: "getFormData is not defined"
**Solution:** Ensure `enhanced_shap_factors.js` is included in HTML before the closing `</body>` tag

### Issue: Factors show but don't match inputs
**Solution:** Check field name mapping in `getFormData()` function - may need to add more aliases

### Issue: All factors show as "Unknown Factor"
**Solution:** Check `mapFactorName()` function in health-assessment.html

### Issue: No factors generated
**Solution:** 
1. Check if prediction is being mapped correctly
2. Verify form data is not empty
3. Check console for JavaScript errors
