# ThyroSight Prediction & Confidence Flow Analysis

## ✅ CONNECTION STATUS: FULLY CONNECTED

The prediction and confidence score are properly connected to the backend ML models (RF and CNN) and follow a comprehensive ensemble approach.

---

## 🔄 Prediction Flow

### 1. **Backend ML Models Called** (Lines 5864-6000)

Four ML models are called via Flask APIs:

```javascript
const modelEndpoints = {
    rf: "http://127.0.0.1:5000/predict",      // Random Forest (Primary)
    svm: "http://127.0.0.1:5002/predict",     // Support Vector Machine
    gb: "http://127.0.0.1:5003/predict"       // Gradient Boosting
};

// CNN Image Analysis
cnnResponse = await fetch("http://127.0.0.1:5001/predict_image", {
    method: "POST",
    body: imageData
});
```

**Each model returns:**
- `prediction`: The thyroid condition (normal/hypothyroid/hyperthyroid)
- `confidence`: Model's confidence percentage
- `success`: Whether the prediction succeeded

---

### 2. **Ensemble Prediction** (Lines 6000-6060)

**Weighted Voting System:**
```javascript
const weights = {
    rf: 0.35,    // Random Forest - 35% weight (highest)
    svm: 0.25,   // SVM - 25% weight
    gb: 0.20,    // Gradient Boosting - 20% weight
    cnn: 0.20    // CNN Image Analysis - 20% weight
};
```

**Process:**
1. Normalize all predictions to standard format (normal/hypothyroid/hyperthyroid)
2. Calculate weighted votes for each condition
3. Select condition with highest weighted vote as `finalPrediction`

**Example:**
- RF predicts "hypothyroid" (35% vote)
- SVM predicts "hypothyroid" (25% vote)
- GB predicts "normal" (20% vote)
- CNN predicts "hypothyroid" (20% vote)
- **Result:** hypothyroid wins with 80% of votes

---

### 3. **Rule-Based Override** (Lines 6070-6200)

Lab results can override ML predictions:

**TSH-Based Overrides:**
- TSH > 10 mIU/L → Force hypothyroid
- TSH < 0.1 mIU/L → Force hyperthyroid
- TSH 4.0-10 mIU/L → Suggest hypothyroid
- TSH 0.1-0.4 mIU/L → Suggest hyperthyroid

**Clinical Scoring Override:**
- Strong symptom patterns (score > 15) can override ML predictions
- Ensures clinical evidence is not ignored

---

## 📊 Confidence Score Calculation

### 1. **Base Confidence Fusion** (Lines 6340-6380)

Weighted average of all model confidences:

```javascript
finalConfidence = 
    (rfConfidence * 0.40) +      // RF: 40%
    (cnnConfidence * 0.25) +     // CNN: 25%
    (svmConfidence * 0.15) +     // SVM: 15%
    (gbConfidence * 0.10) +      // GB: 10%
    (validationConfidence * 0.05) + // Validation: 5%
    (caseSimilarity * 0.05) +    // Case similarity: 5%
    agreementBonus;              // Up to +15% for model agreement
```

---

### 2. **Agreement Bonus** (Lines 6357-6367)

Models that agree with final prediction boost confidence:

```javascript
agreementCount = 0;
if (rf agrees) agreementCount++;
if (svm agrees) agreementCount++;
if (gb agrees) agreementCount++;
if (cnn agrees) agreementCount++;

agreementBonus = (agreementCount / 4) * 15; // Up to +15%
```

**Example:**
- All 4 models agree → +15% confidence
- 3 models agree → +11.25% confidence
- 2 models agree → +7.5% confidence

---

### 3. **Lab Result Boost** (Lines 6405-6418)

Extreme lab values increase confidence:

```javascript
if (TSH > 10) labBoost = 15%;      // Very high
else if (TSH > 7) labBoost = 12%;  // High
else if (TSH > 4) labBoost = 8%;   // Moderately high
else if (TSH < 0.1) labBoost = 15%; // Very low
else if (TSH < 0.2) labBoost = 12%; // Low
else if (TSH < 0.4) labBoost = 8%;  // Moderately low

displayConfidence = max(displayConfidence, 75 + labBoost);
```

---

### 4. **Data Quality Bonus** (Lines 6429-6445)

Complete data increases confidence:

```javascript
dataQualityBonus = 0;

// Lab results available
if (tsh available) +2%
if (t3 available) +1.5%
if (t4 available) +1.5%

// Symptom completeness: up to +3%
// Family history completeness: up to +2%

displayConfidence += dataQualityBonus;
```

---

### 5. **Model Availability Adjustment** (Lines 6385-6395)

Penalty if some models fail:

```javascript
if (modelsAvailable < 4) {
    finalConfidence *= (0.80 + (modelsAvailable * 0.05));
}
```

**Example:**
- 4 models available: No penalty (100%)
- 3 models available: 95% of confidence
- 2 models available: 90% of confidence
- 1 model available: 85% of confidence

---

## 💾 Data Storage

### Saved to Database (Lines 4970-4980)

```javascript
const resultData = {
    form_data: formData,
    prediction: results.finalCondition || 'normal',  // ✅ Final ensemble prediction
    mode: results.mode || 'Auto',
    c_score: parseFloat((results.confidence ?? results.rf_confidence ?? 0).toFixed(1)), // ✅ Final confidence
    shap_values: results.shap_values || [],
    rf_prediction: results.rf_prediction,  // Individual model results stored
    svm_prediction: results.svm_prediction,
    gb_prediction: results.gb_prediction,
    cnn_prediction: results.cnn_prediction
};
```

**Stored in `Result` table:**
- `prediction`: Final ensemble prediction (normal/hypo/hyper)
- `c_score`: Final confidence score (0-100)
- `mode`: Assessment mode (Hybrid/Symptom-only)

---

## 🎯 Display to User (Lines 4525-4590)

### Prediction Display:
```javascript
const finalCondition = conditionMap[rawCondition] || 'normal';
// Maps: hypothyroid → hypo, hyperthyroid → hyper

conditionBadge.textContent = 
    finalCondition === 'normal' ? 'Normal Thyroid Function' :
    finalCondition === 'hypo' ? 'Hypothyroidism' :
    'Hyperthyroidism';
```

### Confidence Display:
```javascript
const confidence = results.confidence ?? results.rf_confidence ?? 0;
confidenceNumber.textContent = confidence + '%';
confidenceFill.style.width = confidence + '%';
```

---

## 📈 Summary

### ✅ **Fully Connected Components:**

1. **Backend ML Models** → Predictions & Confidences
2. **Ensemble Logic** → Weighted voting for final prediction
3. **Rule-Based Overrides** → Lab results can override ML
4. **Confidence Fusion** → Weighted average of all confidences
5. **Bonuses & Adjustments** → Agreement, lab quality, data completeness
6. **Database Storage** → Final prediction & confidence saved
7. **User Display** → Shows final results with explanations

### 🔢 **Confidence Score Range:**

- **Minimum:** ~50% (single model, no lab data, low agreement)
- **Typical:** 70-85% (multiple models, some lab data, good agreement)
- **Maximum:** 95-100% (all models agree, extreme lab values, complete data)

### 🎯 **Prediction Accuracy:**

The system uses:
- **4 ML models** with weighted voting
- **Lab result validation** to catch ML errors
- **Clinical symptom scoring** for symptom-only mode
- **SHAP explanations** for transparency

This multi-layered approach ensures robust and reliable predictions with appropriate confidence levels.
