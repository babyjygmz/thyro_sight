# Key Factors Affecting Result - Fix Summary

## Issue Fixed
The "Key Factors Affecting Result" section was showing the same factors in both "Contributing" and "Suppressing" columns, which was illogical and confusing.

## Root Cause
The `generateEnhancedSHAPFactors()` function was categorizing factors incorrectly:
- It treated ALL abnormal lab values as "negative" type
- It didn't consider the predicted condition when determining if a factor supports or contradicts the diagnosis

## Example of the Bug
**Scenario:** Prediction = HYPOTHYROIDISM, TSH Level = 5 (high)

**Before Fix:**
- ❌ "TSH Levels (High)" appeared in BOTH Contributing AND Suppressing columns
- This made no sense because high TSH CAUSES hypothyroidism

**After Fix:**
- ✅ "TSH Levels (High)" appears ONLY in Contributing Factors
- Correctly shows that high TSH supports the hypothyroidism diagnosis

## The Solution
Implemented **context-aware factor categorization**:

### For HYPOTHYROIDISM:
- **Contributing Factors (+):** High TSH, Low T4, Taking thyroid medication, Previous surgery
- **Suppressing Factors (-):** Normal TSH, Normal T4, Not taking medication

### For HYPERTHYROIDISM:
- **Contributing Factors (+):** Low TSH, High T4, Doctor suspected hyper
- **Suppressing Factors (-):** Normal TSH, High TSH, Normal T4

### For NORMAL:
- **Contributing Factors (+):** Normal TSH, Normal T4, No medication, Young age
- **Suppressing Factors (-):** Abnormal TSH, Abnormal T4, Taking medication

## Technical Changes

### Modified Function: `generateEnhancedSHAPFactors(condition, age, riskFactors)`

**Key Improvements:**
1. **Context-Aware TSH Logic:**
   ```javascript
   if (tshValue > 4.0) {
       if (condition === 'hypo') {
           // High TSH SUPPORTS hypothyroidism
           type: 'positive', impact: 25
       } else {
           // High TSH CONTRADICTS other diagnoses
           type: 'negative', impact: -22
       }
   }
   ```

2. **Context-Aware T4 Logic:**
   - Normal T4 supports normal diagnosis
   - Abnormal T4 supports dysfunction diagnosis

3. **Context-Aware Medication Logic:**
   - Taking thyroid medication supports hypo diagnosis
   - Not taking medication supports normal diagnosis

4. **Numeric Impact Values:**
   - Changed from string ('+25%') to number (25)
   - Enables proper mathematical sorting and comparison

5. **Better Factor Separation:**
   - Returns top 3 positive + top 3 negative factors
   - Prevents duplicate factors in both columns

## Files Modified
- `thyro_sight/health-assessment.html` - Line 3231-3433 (function replaced)

## Testing Recommendations
1. Test with HIGH TSH (>4.0) - should show in Contributing for Hypo
2. Test with LOW TSH (<0.4) - should show in Contributing for Hyper  
3. Test with NORMAL TSH (0.4-4.0) - should show in Contributing for Normal
4. Verify no factor appears in both columns simultaneously
5. Check console logs for debugging information

## Expected Behavior
- Each factor appears in ONLY ONE column
- Factors logically support the predicted diagnosis
- Impact percentages are calculated relative to total impact
- Empty columns show "No [positive/negative] factors detected"
