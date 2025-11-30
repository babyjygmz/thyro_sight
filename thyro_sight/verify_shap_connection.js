// ========================================
// SHAP CONNECTION VERIFICATION SCRIPT
// Run this in browser console to verify the connection
// ========================================

(function() {
    console.log('\n' + '='.repeat(70));
    console.log('🔬 SHAP CONNECTION VERIFICATION SCRIPT');
    console.log('='.repeat(70) + '\n');

    // Check if enhanced_shap_factors.js is loaded
    if (typeof generateEnhancedSHAPFactors !== 'function') {
        console.error('❌ ERROR: enhanced_shap_factors.js not loaded!');
        console.log('   Please ensure the script is included in your HTML:');
        console.log('   <script src="enhanced_shap_factors.js"></script>');
        return;
    }
    console.log('✅ enhanced_shap_factors.js loaded successfully\n');

    // Test data for each condition
    const testCases = [
        {
            condition: 'hypo',
            name: 'HYPOTHYROIDISM',
            formData: {
                age: 35,
                gender: 'female',
                tsh: 'yes',
                tshValue: 8.5,
                t3: 'yes',
                t3Value: 70,
                t4: 'yes',
                t4Value: 4.0,
                diabetes: 'no',
                high_blood_pressure: 'no',
                high_cholesterol: 'yes',
                anemia: 'no',
                depression_anxiety: 'yes',
                heart_disease: 'no',
                menstrual_irregularities: 'no',
                autoimmune_diseases: 'no',
                fh_hypothyroidism: 'no',
                fh_hyperthyroidism: 'no',
                fh_goiter: 'no',
                fh_thyroid_cancer: 'no',
                sym_fatigue: 'yes',
                sym_weight_change: 'yes',
                sym_dry_skin: 'no',
                sym_hair_loss: 'no',
                sym_heart_rate: 'no',
                sym_digestion: 'no',
                sym_irregular_periods: 'no',
                sym_neck_swelling: 'no'
            }
        },
        {
            condition: 'hyper',
            name: 'HYPERTHYROIDISM',
            formData: {
                age: 35,
                gender: 'female',
                tsh: 'yes',
                tshValue: 0.2,
                t3: 'yes',
                t3Value: 220,
                t4: 'yes',
                t4Value: 14.0,
                diabetes: 'no',
                high_blood_pressure: 'yes',
                high_cholesterol: 'no',
                anemia: 'no',
                depression_anxiety: 'yes',
                heart_disease: 'no',
                menstrual_irregularities: 'no',
                autoimmune_diseases: 'no',
                fh_hypothyroidism: 'no',
                fh_hyperthyroidism: 'no',
                fh_goiter: 'no',
                fh_thyroid_cancer: 'no',
                sym_fatigue: 'no',
                sym_weight_change: 'yes',
                sym_dry_skin: 'no',
                sym_hair_loss: 'no',
                sym_heart_rate: 'yes',
                sym_digestion: 'no',
                sym_irregular_periods: 'no',
                sym_neck_swelling: 'no'
            }
        },
        {
            condition: 'normal',
            name: 'NORMAL THYROID FUNCTION',
            formData: {
                age: 25,
                gender: 'female',
                tsh: 'yes',
                tshValue: 2.0,
                t3: 'yes',
                t3Value: 120,
                t4: 'yes',
                t4Value: 8.0,
                diabetes: 'no',
                high_blood_pressure: 'no',
                high_cholesterol: 'no',
                anemia: 'no',
                depression_anxiety: 'no',
                heart_disease: 'no',
                menstrual_irregularities: 'no',
                autoimmune_diseases: 'no',
                fh_hypothyroidism: 'no',
                fh_hyperthyroidism: 'no',
                fh_goiter: 'no',
                fh_thyroid_cancer: 'no',
                sym_fatigue: 'no',
                sym_weight_change: 'no',
                sym_dry_skin: 'no',
                sym_hair_loss: 'no',
                sym_heart_rate: 'no',
                sym_digestion: 'no',
                sym_irregular_periods: 'no',
                sym_neck_swelling: 'no'
            }
        }
    ];

    // Override getFormData for testing
    const originalGetFormData = window.getFormData;
    
    // Run tests
    let allTestsPassed = true;
    
    testCases.forEach((testCase, index) => {
        console.log(`\n${'─'.repeat(70)}`);
        console.log(`TEST ${index + 1}: ${testCase.name}`);
        console.log('─'.repeat(70));
        
        // Set mock form data
        window.getFormData = function() {
            return testCase.formData;
        };
        
        try {
            // Generate SHAP factors
            const age = testCase.formData.age;
            const shapFactors = generateEnhancedSHAPFactors(testCase.condition, age, []);
            
            // Verify results
            const positives = shapFactors.filter(f => f.type === 'positive');
            const negatives = shapFactors.filter(f => f.type === 'negative');
            
            console.log(`\n📊 Results:`);
            console.log(`   Condition: ${testCase.condition}`);
            console.log(`   Total Factors: ${shapFactors.length}`);
            console.log(`   Contributing Factors: ${positives.length}`);
            console.log(`   Suppressing Factors: ${negatives.length}`);
            
            // Check if factors are relevant to the condition
            let relevantFactors = 0;
            
            if (testCase.condition === 'hypo') {
                // Check for hypothyroid-relevant factors
                const relevantNames = ['TSH', 'T3', 'T4', 'Fatigue', 'Weight', 'Cholesterol'];
                shapFactors.forEach(f => {
                    if (relevantNames.some(name => f.name.includes(name))) {
                        relevantFactors++;
                    }
                });
            } else if (testCase.condition === 'hyper') {
                // Check for hyperthyroid-relevant factors
                const relevantNames = ['TSH', 'T3', 'T4', 'Heart Rate', 'Weight', 'Blood Pressure'];
                shapFactors.forEach(f => {
                    if (relevantNames.some(name => f.name.includes(name))) {
                        relevantFactors++;
                    }
                });
            } else {
                // Check for normal-relevant factors
                const relevantNames = ['TSH', 'T3', 'T4', 'Age', 'No'];
                shapFactors.forEach(f => {
                    if (relevantNames.some(name => f.name.includes(name))) {
                        relevantFactors++;
                    }
                });
            }
            
            console.log(`   Relevant Factors: ${relevantFactors}`);
            
            // Display top factors
            if (positives.length > 0) {
                console.log(`\n   ✅ Top Contributing Factors:`);
                positives.slice(0, 3).forEach((f, i) => {
                    console.log(`      ${i+1}. ${f.name}: +${Math.abs(f.impact)}%`);
                });
            }
            
            if (negatives.length > 0) {
                console.log(`\n   ❌ Top Suppressing Factors:`);
                negatives.slice(0, 3).forEach((f, i) => {
                    console.log(`      ${i+1}. ${f.name}: -${Math.abs(f.impact)}%`);
                });
            }
            
            // Verify connection
            const testPassed = shapFactors.length > 0 && relevantFactors > 0;
            
            if (testPassed) {
                console.log(`\n   ✅ TEST PASSED: Factors correctly generated for ${testCase.name}`);
            } else {
                console.log(`\n   ❌ TEST FAILED: Factors not properly generated`);
                allTestsPassed = false;
            }
            
        } catch (error) {
            console.error(`\n   ❌ TEST FAILED: ${error.message}`);
            console.error(`   Stack: ${error.stack}`);
            allTestsPassed = false;
        }
    });
    
    // Restore original getFormData
    if (originalGetFormData) {
        window.getFormData = originalGetFormData;
    }
    
    // Final summary
    console.log(`\n${'='.repeat(70)}`);
    if (allTestsPassed) {
        console.log('✅ ALL TESTS PASSED - SHAP CONNECTION VERIFIED');
        console.log('   The prediction is properly connected to key factors!');
    } else {
        console.log('❌ SOME TESTS FAILED - PLEASE REVIEW ERRORS ABOVE');
    }
    console.log('='.repeat(70) + '\n');
    
    // Return test results
    return {
        success: allTestsPassed,
        message: allTestsPassed 
            ? 'All tests passed! SHAP factors are correctly connected to predictions.'
            : 'Some tests failed. Please review the console output above.'
    };
})();
