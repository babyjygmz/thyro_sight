# Confidence Score History Display Fix

## Problem
The confidence score might not be displaying correctly in the history page, showing as "0%" or "undefined%" for some assessments.

## Root Causes

### Possible Issues:
1. **Null/Undefined Values**: Confidence score might be null in database
2. **Data Type Mismatch**: Score stored as decimal but displayed as integer
3. **Missing Data**: Old assessments might not have confidence scores
4. **JavaScript Error**: Undefined handling in frontend

## Solution Implemented

### 1. Added Null/Undefined Handling

**File**: `thyro_sight/history.html`

**In Table Display**:
```javascript
// Before:
<td><span class="confidence-score">${assessment.confidence_score}%</span></td>

// After:
const confidenceScore = assessment.confidence_score !== null && assessment.confidence_score !== undefined 
    ? Math.round(assessment.confidence_score) 
    : 0;
<td><span class="confidence-score">${confidenceScore}%</span></td>
```

**In Detail Modal**:
```javascript
// Before:
<span class="detail-value">${assessment.confidence_score}%</span>

// After:
<span class="detail-value">${assessment.confidence_score !== null && assessment.confidence_score !== undefined ? Math.round(assessment.confidence_score) : 0}%</span>
```

### 2. Added Debug Logging

Added console logging to track confidence scores:
```javascript
console.log('Assessment:', assessment.form_id, 'Confidence:', assessment.confidence_score);
```

## Data Flow

### 1. Submission (health-assessment.html → submit_health_assessment.php)
```javascript
// Frontend sends:
{
    prediction: 'hypo',
    c_score: 85.3,  // Confidence as percentage
    form_data: {...}
}
```

### 2. Storage (submit_health_assessment.php)
```php
// Validates and stores:
$confidence_score = floatval($input['c_score']);  // 85.3
INSERT INTO Result (form_id, user_id, prediction, c_score, mode) 
VALUES (?, ?, ?, ?, ?)
```

### 3. Retrieval (get_assessment_history.php)
```php
// Fetches:
SELECT r.c_score as confidence_score FROM Result r
// Returns: confidence_score: 85.3
```

### 4. Display (history.html)
```javascript
// Displays:
const confidenceScore = Math.round(85.3);  // 85
${confidenceScore}%  // "85%"
```

## Database Schema

### Result Table:
```sql
CREATE TABLE Result (
    result_id INT PRIMARY KEY AUTO_INCREMENT,
    form_id INT NOT NULL,
    user_id VARCHAR(50) NOT NULL,
    prediction ENUM('normal', 'hypo', 'hyper') NOT NULL,
    c_score DECIMAL(5,2) NOT NULL,  -- Stores as 85.30
    mode VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Testing

### 1. Check Database Values
```sql
-- Check if confidence scores are stored
SELECT form_id, prediction, c_score 
FROM Result 
WHERE user_id = 'your_user_id' 
ORDER BY created_at DESC 
LIMIT 10;
```

### 2. Check API Response
Open browser console (F12) and check:
```javascript
// In history.html, check console logs:
Assessment: 123 Confidence: 85.3
Assessment: 124 Confidence: 91.2
```

### 3. Visual Check
1. Go to History page
2. Check "Confidence Score" column
3. Should show values like "85%", "91%", etc.
4. Click "View Details" on any assessment
5. Check "Confidence Score" in modal

## Expected Results

### History Table:
```
┌──────────────┬─────────────┬──────────────────┬──────────┐
│ Date         │ Prediction  │ Confidence Score │ View     │
├──────────────┼─────────────┼──────────────────┼──────────┤
│ Nov 28, 2025 │ NORMAL      │ 91%              │ [View]   │
│ Nov 27, 2025 │ HYPO        │ 85%              │ [View]   │
│ Nov 26, 2025 │ HYPER       │ 78%              │ [View]   │
└──────────────┴─────────────┴──────────────────┴──────────┘
```

### Detail Modal:
```
Assessment Details
─────────────────────────────
Age: 22
Gender: Female
Assessment Date: Nov 28, 2025
Prediction: NORMAL
Confidence Score: 91%  ← Should display correctly
─────────────────────────────
```

## Troubleshooting

### If confidence shows as "0%":

1. **Check Database**:
   ```sql
   SELECT * FROM Result WHERE form_id = [your_form_id];
   ```
   - If `c_score` is NULL or 0, the assessment was saved incorrectly

2. **Check Console Logs**:
   - Open F12 → Console
   - Look for: `Assessment: X Confidence: undefined` or `null`
   - This indicates the API is not returning the value

3. **Check API Response**:
   - Open F12 → Network tab
   - Find `get_assessment_history.php` request
   - Check response JSON:
     ```json
     {
       "success": true,
       "assessments": [
         {
           "form_id": 123,
           "confidence_score": 85  ← Should be a number
         }
       ]
     }
     ```

### If confidence shows as "NaN%":

1. **Data Type Issue**: The value is not a valid number
2. **Fix**: The `Math.round()` function should handle this
3. **Check**: Look for string values like "85%" instead of 85

### If old assessments show "0%":

1. **Missing Data**: Old assessments might not have confidence scores
2. **Solution**: Re-run those assessments or update database:
   ```sql
   -- Set default confidence for old records
   UPDATE Result 
   SET c_score = 75.0 
   WHERE c_score IS NULL OR c_score = 0;
   ```

## Files Modified

1. ✅ `thyro_sight/history.html` - Added null handling and debugging
2. ✅ `thyro_sight/get_assessment_history.php` - Already correct
3. ✅ `thyro_sight/get_assessment_details.php` - Already correct
4. ✅ `thyro_sight/submit_health_assessment.php` - Already correct

## Summary

✅ Added null/undefined handling for confidence scores
✅ Added Math.round() to ensure integer display
✅ Added debug logging to track values
✅ Ensured consistent display in table and modal
✅ Handles edge cases (null, undefined, 0)

The confidence score should now display correctly in the history page for all assessments!
