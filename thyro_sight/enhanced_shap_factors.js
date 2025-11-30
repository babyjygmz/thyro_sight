// ========================================
// ENHANCED SHAP FACTORS GENERATION
// Analyzes ALL user inputs and generates comprehensive factors
// ========================================

// Helper function to collect form data
function getFormData() {
    const formData = {};
    
    // Collect all form inputs
    document.querySelectorAll("input, select").forEach(input => {
        const name = input.name.replace(/-/g, "_").toLowerCase();
        if (input.type === "radio" && input.checked) {
            formData[name] = input.value.toLowerCase() === "yes" ? "yes" : "no";
        } else if (["text", "number"].includes(input.type) || input.tagName.toLowerCase() === "select") {
            formData[name] = input.value;
        }
    });
    
    // Handle specific field mappings
    formData.tshValue = formData.tsh_value;
    formData.t3Value = formData.t3_value;
    formData.t4Value = formData.t4_value;
    formData['t4-uptakeValue'] = formData.t4_uptake_value;
    formData.ftiValue = formData.fti_value;
    
    // Normalize field names for consistency
    formData.Diabetes = formData.diabetes;
    formData.HighBloodPressure = formData.highbloodpressure || formData.high_blood_pressure;
    formData.HighCholesterol = formData.highcholesterol || formData.high_cholesterol;
    formData.Anemia = formData.anemia;
    formData.DepressionAnxiety = formData.depressionanxiety || formData.depression_anxiety;
    formData.HeartDisease = formData.heartdisease || formData.heart_disease;
    formData.MenstrualIrregularities = formData.menstrualirregularities || formData.menstrual_irregularities;
    formData.AutoimmuneDiseases = formData.autoimmunediseases || formData.autoimmune_diseases;
    
    formData.FH_Hypothyroidism = formData.fh_hypothyroidism;
    formData.FH_Hyperthyroidism = formData.fh_hyperthyroidism;
    formData.FH_Goiter = formData.fh_goiter;
    formData.FH_ThyroidCancer = formData.fh_thyroid_cancer || formData.fh_thyroidcancer;
    
    formData.Sym_Fatigue = formData.sym_fatigue;
    formData.Sym_WeightChange = formData.sym_weightchange || formData.sym_weight_change;
    formData.Sym_DrySkin = formData.sym_dryskin || formData.sym_dry_skin;
    formData.Sym_HairLoss = formData.sym_hairloss || formData.sym_hair_loss;
    formData.Sym_HeartRate = formData.sym_heartrate || formData.sym_heart_rate;
    formData.Sym_Digestion = formData.sym_digestion;
    formData.Sym_IrregularPeriods = formData.sym_irregularperiods || formData.sym_irregular_periods;
    formData.Sym_NeckSwelling = formData.sym_neckswelling || formData.sym_neck_swelling;
    
    return formData;
}

function generateEnhancedSHAPFactors(condition, age, riskFactors) {
    const factors = [];
    const formData = getFormData();

    console.log('🔍 Generating COMPREHENSIVE SHAP factors for condition:', condition);
    console.log('📋 Form Data:', formData);
    
    // Get gender from the form
    const genderField = document.getElementById('gender-display');
    const gender = genderField ? genderField.value.toLowerCase() : '';

    // ========================================
    // 1. LAB RESULTS ANALYSIS (TSH, T3, T4, FTI, T4 Uptake)
    // ========================================
    
    // TSH Analysis
    if (formData.tsh === 'yes' && formData.tshValue) {
        const tshValue = parseFloat(formData.tshValue);
        
        if (tshValue < 0.4) {
            // Low TSH indicates hyperthyroidism
            if (condition === 'hyper') {
                factors.push({
                    name: 'TSH Levels (Low)',
                    description: `Your TSH level is ${tshValue} mIU/L (below normal <0.4), which strongly indicates hyperthyroidism. Low TSH means your pituitary gland is producing less TSH because your thyroid is overactive.`,
                    impact: 25,
                    type: 'positive'
                });
            } else {
                factors.push({
                    name: 'TSH Levels (Low)',
                    description: `Your TSH level is ${tshValue} mIU/L, suggesting hyperthyroidism, which contradicts the ${condition} diagnosis.`,
                    impact: -22,
                    type: 'negative'
                });
            }
        } else if (tshValue > 4.0) {
            // High TSH indicates hypothyroidism
            if (condition === 'hypo') {
                factors.push({
                    name: 'TSH Levels (High)',
                    description: `Your TSH level is ${tshValue} mIU/L (above normal >4.0), which strongly indicates hypothyroidism. High TSH means your pituitary gland is working harder to stimulate an underactive thyroid.`,
                    impact: 25,
                    type: 'positive'
                });
            } else {
                factors.push({
                    name: 'TSH Levels (High)',
                    description: `Your TSH level is ${tshValue} mIU/L, suggesting hypothyroidism, which contradicts the ${condition} diagnosis.`,
                    impact: -22,
                    type: 'negative'
                });
            }
        } else {
            // Normal TSH (0.4-4.0)
            if (condition === 'normal') {
                factors.push({
                    name: 'TSH Levels (Normal)',
                    description: `Your TSH level is ${tshValue} mIU/L, which is within the normal range (0.4-4.0), supporting healthy thyroid function.`,
                    impact: 18,
                    type: 'positive'
                });
            } else {
                factors.push({
                    name: 'TSH Levels (Normal)',
                    description: `Your TSH level is ${tshValue} mIU/L (normal range), which contradicts the ${condition} diagnosis.`,
                    impact: -15,
                    type: 'negative'
                });
            }
        }
    }

    // T3 Analysis
    if (formData.t3 === 'yes' && formData.t3Value) {
        const t3Value = parseFloat(formData.t3Value);
        const isNormal = t3Value >= 80 && t3Value <= 200;

        if (isNormal) {
            if (condition === 'normal') {
                factors.push({
                    name: 'T3 Levels (Normal)',
                    description: `Your T3 level is ${t3Value} ng/dL, within normal range (80-200), indicating healthy thyroid hormone production.`,
                    impact: 14,
                    type: 'positive'
                });
            } else {
                factors.push({
                    name: 'T3 Levels (Normal)',
                    description: `Your T3 level is ${t3Value} ng/dL (normal), which doesn't support the ${condition} diagnosis.`,
                    impact: -12,
                    type: 'negative'
                });
            }
        } else if (t3Value > 200) {
            if (condition === 'hyper') {
                factors.push({
                    name: 'T3 Levels (High)',
                    description: `Your T3 level is ${t3Value} ng/dL (above normal >200), indicating excess thyroid hormone production consistent with hyperthyroidism.`,
                    impact: 20,
                    type: 'positive'
                });
            } else {
                factors.push({
                    name: 'T3 Levels (High)',
                    description: `Your elevated T3 level (${t3Value} ng/dL) suggests hyperthyroidism, contradicting the ${condition} diagnosis.`,
                    impact: -18,
                    type: 'negative'
                });
            }
        } else {
            if (condition === 'hypo') {
                factors.push({
                    name: 'T3 Levels (Low)',
                    description: `Your T3 level is ${t3Value} ng/dL (below normal <80), indicating insufficient thyroid hormone production consistent with hypothyroidism.`,
                    impact: 20,
                    type: 'positive'
                });
            } else {
                factors.push({
                    name: 'T3 Levels (Low)',
                    description: `Your low T3 level (${t3Value} ng/dL) suggests hypothyroidism, contradicting the ${condition} diagnosis.`,
                    impact: -18,
                    type: 'negative'
                });
            }
        }
    }

    // T4 Analysis
    if (formData.t4 === 'yes' && formData.t4Value) {
        const t4Value = parseFloat(formData.t4Value);
        const isNormal = t4Value >= 4.5 && t4Value <= 12.5;

        if (isNormal) {
            if (condition === 'normal') {
                factors.push({
                    name: 'T4 Levels (Normal)',
                    description: `Your T4 level is ${t4Value} ng/dL, within normal range (4.5-12.5), supporting healthy thyroid function.`,
                    impact: 14,
                    type: 'positive'
                });
            } else {
                factors.push({
                    name: 'T4 Levels (Normal)',
                    description: `Your T4 level is ${t4Value} ng/dL (normal), which doesn't align with the ${condition} diagnosis.`,
                    impact: -12,
                    type: 'negative'
                });
            }
        } else if (t4Value > 12.5) {
            if (condition === 'hyper') {
                factors.push({
                    name: 'T4 Levels (High)',
                    description: `Your T4 level is ${t4Value} ng/dL (above normal >12.5), indicating excess thyroxine production consistent with hyperthyroidism.`,
                    impact: 18,
                    type: 'positive'
                });
            } else {
                factors.push({
                    name: 'T4 Levels (High)',
                    description: `Your elevated T4 level (${t4Value} ng/dL) suggests hyperthyroidism, contradicting the ${condition} diagnosis.`,
                    impact: -16,
                    type: 'negative'
                });
            }
        } else {
            if (condition === 'hypo') {
                factors.push({
                    name: 'T4 Levels (Low)',
                    description: `Your T4 level is ${t4Value} ng/dL (below normal <4.5), indicating insufficient thyroxine production consistent with hypothyroidism.`,
                    impact: 18,
                    type: 'positive'
                });
            } else {
                factors.push({
                    name: 'T4 Levels (Low)',
                    description: `Your low T4 level (${t4Value} ng/dL) suggests hypothyroidism, contradicting the ${condition} diagnosis.`,
                    impact: -16,
                    type: 'negative'
                });
            }
        }
    }

    // FTI Analysis
    if (formData.fti === 'yes' && formData.ftiValue) {
        const ftiValue = parseFloat(formData.ftiValue);
        const isNormal = ftiValue >= 1.0 && ftiValue <= 4.0;

        if (isNormal) {
            if (condition === 'normal') {
                factors.push({
                    name: 'Free Thyroxine Index (Normal)',
                    description: `Your FTI is ${ftiValue}, within normal range (1.0-4.0), indicating balanced thyroid hormone availability.`,
                    impact: 12,
                    type: 'positive'
                });
            }
        } else if (ftiValue > 4.0) {
            if (condition === 'hyper') {
                factors.push({
                    name: 'Free Thyroxine Index (High)',
                    description: `Your FTI is ${ftiValue} (above normal >4.0), indicating excess free thyroid hormone consistent with hyperthyroidism.`,
                    impact: 15,
                    type: 'positive'
                });
            }
        } else {
            if (condition === 'hypo') {
                factors.push({
                    name: 'Free Thyroxine Index (Low)',
                    description: `Your FTI is ${ftiValue} (below normal <1.0), indicating insufficient free thyroid hormone consistent with hypothyroidism.`,
                    impact: 15,
                    type: 'positive'
                });
            }
        }
    }

    // T4 Uptake Analysis
    if (formData['t4-uptake'] === 'yes' && formData['t4-uptakeValue']) {
        const t4UptakeValue = parseFloat(formData['t4-uptakeValue']);
        const isNormal = t4UptakeValue >= 25 && t4UptakeValue <= 35;

        if (isNormal) {
            if (condition === 'normal') {
                factors.push({
                    name: 'T4 Uptake (Normal)',
                    description: `Your T4 Uptake is ${t4UptakeValue}%, within normal range (25-35%), supporting healthy thyroid binding protein levels.`,
                    impact: 10,
                    type: 'positive'
                });
            }
        } else if (t4UptakeValue > 35) {
            if (condition === 'hyper') {
                factors.push({
                    name: 'T4 Uptake (High)',
                    description: `Your T4 Uptake is ${t4UptakeValue}% (above normal >35%), consistent with hyperthyroidism.`,
                    impact: 12,
                    type: 'positive'
                });
            }
        } else {
            if (condition === 'hypo') {
                factors.push({
                    name: 'T4 Uptake (Low)',
                    description: `Your T4 Uptake is ${t4UptakeValue}% (below normal <25%), consistent with hypothyroidism.`,
                    impact: 12,
                    type: 'positive'
                });
            }
        }
    }

    // ========================================
    // 2. MEDICAL HISTORY ANALYSIS
    // ========================================

    // Diabetes
    if (formData.Diabetes === 'yes' || formData.diabetes === 'yes') {
        if (condition !== 'normal') {
            factors.push({
                name: 'Diabetes History',
                description: 'You have diabetes, which increases the risk of thyroid disorders. Diabetes and thyroid conditions often coexist and can affect each other.',
                impact: 8,
                type: 'positive'
            });
        } else {
            factors.push({
                name: 'Diabetes History',
                description: 'You have diabetes, which increases the risk of thyroid disorders. This contradicts the normal diagnosis.',
                impact: -8,
                type: 'negative'
            });
        }
    } else if (formData.Diabetes === 'no' || formData.diabetes === 'no') {
        if (condition !== 'normal') {
            factors.push({
                name: 'No Diabetes',
                description: 'You do not have diabetes. While diabetes increases thyroid disorder risk, its absence is a protective factor.',
                impact: -3,
                type: 'negative'
            });
        } else {
            factors.push({
                name: 'No Diabetes',
                description: 'You do not have diabetes. This supports normal thyroid function.',
                impact: 3,
                type: 'positive'
            });
        }
    }

    // High Blood Pressure
    if (formData.HighBloodPressure === 'yes' || formData.high_blood_pressure === 'yes') {
        if (condition === 'hyper') {
            factors.push({
                name: 'High Blood Pressure',
                description: 'You have high blood pressure, which is commonly associated with hyperthyroidism due to increased metabolic rate and cardiac output.',
                impact: 10,
                type: 'positive'
            });
        } else if (condition === 'hypo') {
            factors.push({
                name: 'High Blood Pressure',
                description: 'You have high blood pressure. While less common, hypothyroidism can also contribute to hypertension through various mechanisms.',
                impact: 6,
                type: 'positive'
            });
        }
    } else if (formData.HighBloodPressure === 'no' || formData.high_blood_pressure === 'no') {
        if (condition === 'hyper') {
            factors.push({
                name: 'Normal Blood Pressure',
                description: 'Your blood pressure is normal. Hyperthyroidism typically causes elevated blood pressure, so normal readings reduce the likelihood.',
                impact: -8,
                type: 'negative'
            });
        }
    }

    // High Cholesterol
    if (formData.HighCholesterol === 'yes' || formData.high_cholesterol === 'yes') {
        if (condition === 'hypo') {
            factors.push({
                name: 'High Cholesterol',
                description: 'You have high cholesterol, which is a common symptom of hypothyroidism. Low thyroid hormone slows metabolism and reduces cholesterol clearance.',
                impact: 12,
                type: 'positive'
            });
        }
    } else if (formData.HighCholesterol === 'no' || formData.high_cholesterol === 'no') {
        if (condition === 'hypo') {
            factors.push({
                name: 'Normal Cholesterol',
                description: 'Your cholesterol is normal. Hypothyroidism typically causes elevated cholesterol, so normal levels reduce the likelihood.',
                impact: -10,
                type: 'negative'
            });
        }
    }

    // Anemia
    if (formData.Anemia === 'yes' || formData.anemia === 'yes') {
        if (condition === 'hypo') {
            factors.push({
                name: 'Anemia',
                description: 'You have anemia, which is frequently associated with hypothyroidism. Thyroid hormones are essential for red blood cell production.',
                impact: 10,
                type: 'positive'
            });
        } else if (condition === 'normal') {
            factors.push({
                name: 'Anemia',
                description: 'You have anemia. This can be associated with thyroid disorders, particularly hypothyroidism.',
                impact: -8,
                type: 'negative'
            });
        }
    } else if (formData.Anemia === 'no' || formData.anemia === 'no') {
        if (condition === 'hypo') {
            factors.push({
                name: 'No Anemia',
                description: 'You do not have anemia. Hypothyroidism commonly causes anemia, so its absence reduces the likelihood.',
                impact: -8,
                type: 'negative'
            });
        } else if (condition === 'normal') {
            factors.push({
                name: 'No Anemia',
                description: 'You do not have anemia. This supports normal thyroid function.',
                impact: 8,
                type: 'positive'
            });
        }
    }

    // Depression/Anxiety
    if (formData.DepressionAnxiety === 'yes' || formData.depression_anxiety === 'yes') {
        if (condition === 'hypo') {
            factors.push({
                name: 'Depression/Anxiety',
                description: 'You experience depression or anxiety. Hypothyroidism commonly causes mood disorders due to reduced brain metabolism and neurotransmitter function.',
                impact: 11,
                type: 'positive'
            });
        } else if (condition === 'hyper') {
            factors.push({
                name: 'Anxiety',
                description: 'You experience anxiety, which is a hallmark symptom of hyperthyroidism due to excess thyroid hormone stimulating the nervous system.',
                impact: 11,
                type: 'positive'
            });
        } else {
            factors.push({
                name: 'Depression/Anxiety',
                description: 'You experience depression or anxiety. This can be a symptom of thyroid disorders, which contradicts the normal diagnosis.',
                impact: -9,
                type: 'negative'
            });
        }
    } else if (formData.DepressionAnxiety === 'no' || formData.depression_anxiety === 'no') {
        if (condition !== 'normal') {
            factors.push({
                name: 'No Mood Disorders',
                description: 'You do not experience depression or anxiety. Thyroid disorders commonly cause mood changes, so their absence reduces the likelihood.',
                impact: -9,
                type: 'negative'
            });
        } else {
            factors.push({
                name: 'No Mood Disorders',
                description: 'You do not experience depression or anxiety. This supports normal thyroid function.',
                impact: 9,
                type: 'positive'
            });
        }
    }

    // Heart Disease
    if (formData.HeartDisease === 'yes' || formData.heart_disease === 'yes') {
        if (condition === 'hyper') {
            factors.push({
                name: 'Heart Disease',
                description: 'You have heart disease. Hyperthyroidism significantly increases cardiovascular stress and can worsen heart conditions.',
                impact: 13,
                type: 'positive'
            });
        } else if (condition === 'hypo') {
            factors.push({
                name: 'Heart Disease',
                description: 'You have heart disease. Hypothyroidism can contribute to heart problems through increased cholesterol and reduced cardiac function.',
                impact: 9,
                type: 'positive'
            });
        } else {
            factors.push({
                name: 'Heart Disease',
                description: 'You have heart disease. Thyroid disorders can affect cardiovascular health, which contradicts the normal diagnosis.',
                impact: -9,
                type: 'negative'
            });
        }
    } else if (formData.HeartDisease === 'no' || formData.heart_disease === 'no') {
        if (condition !== 'normal') {
            factors.push({
                name: 'No Heart Disease',
                description: 'You do not have heart disease. Thyroid disorders can affect cardiovascular health, so its absence is a protective factor.',
                impact: -5,
                type: 'negative'
            });
        } else {
            factors.push({
                name: 'No Heart Disease',
                description: 'You do not have heart disease. This supports normal thyroid function.',
                impact: 5,
                type: 'positive'
            });
        }
    }

    // Menstrual Irregularities
    if (formData.MenstrualIrregularities === 'yes' || formData.menstrual_irregularities === 'yes') {
        if (condition !== 'normal') {
            factors.push({
                name: 'Menstrual Irregularities',
                description: 'You have menstrual irregularities. Both hypothyroidism and hyperthyroidism can disrupt menstrual cycles through hormonal imbalances.',
                impact: 10,
                type: 'positive'
            });
        } else {
            factors.push({
                name: 'Menstrual Irregularities',
                description: 'You have menstrual irregularities. This can be a sign of thyroid disorders, which contradicts the normal diagnosis.',
                impact: -10,
                type: 'negative'
            });
        }
    } else if (formData.MenstrualIrregularities === 'no' || formData.menstrual_irregularities === 'no') {
        if (condition !== 'normal') {
            factors.push({
                name: 'Regular Menstrual Cycles',
                description: 'Your menstrual cycles are regular. Thyroid disorders commonly cause menstrual irregularities, so regular cycles reduce the likelihood.',
                impact: -8,
                type: 'negative'
            });
        } else {
            factors.push({
                name: 'Regular Menstrual Cycles',
                description: 'Your menstrual cycles are regular. This supports normal thyroid function.',
                impact: 8,
                type: 'positive'
            });
        }
    }

    // Autoimmune Diseases
    if (formData.AutoimmuneDiseases === 'yes' || formData.autoimmune_diseases === 'yes') {
        if (condition !== 'normal') {
            factors.push({
                name: 'Autoimmune Disease History',
                description: 'You have other autoimmune diseases. Thyroid disorders (especially Hashimoto\'s and Graves\' disease) are autoimmune conditions that often cluster with other autoimmune diseases.',
                impact: 14,
                type: 'positive'
            });
        } else {
            // For normal prediction, autoimmune disease is a risk factor (suppressing normal)
            factors.push({
                name: 'Autoimmune Disease History',
                description: 'You have other autoimmune diseases. This increases risk for thyroid disorders, as they often cluster with autoimmune conditions like Hashimoto\'s and Graves\' disease.',
                impact: -14,
                type: 'negative'
            });
        }
    } else if (formData.AutoimmuneDiseases === 'no' || formData.autoimmune_diseases === 'no') {
        if (condition !== 'normal') {
            factors.push({
                name: 'No Autoimmune Diseases',
                description: 'You do not have other autoimmune diseases. Since thyroid disorders are often autoimmune, this absence reduces the likelihood.',
                impact: -12,
                type: 'negative'
            });
        } else {
            // For normal prediction, no autoimmune disease supports it
            factors.push({
                name: 'No Autoimmune Diseases',
                description: 'You do not have other autoimmune diseases. This supports normal thyroid function, as thyroid disorders often occur with autoimmune conditions.',
                impact: 12,
                type: 'positive'
            });
        }
    }

    // ========================================
    // 3. FAMILY HISTORY ANALYSIS
    // ========================================

    if (formData.FH_Hypothyroidism === 'yes' || formData.fh_hypothyroidism === 'yes') {
        if (condition === 'hypo') {
            factors.push({
                name: 'Family History of Hypothyroidism',
                description: 'You have a family history of hypothyroidism. Genetic factors play a significant role, increasing your risk by 3-5 times.',
                impact: 15,
                type: 'positive'
            });
        } else if (condition === 'normal') {
            factors.push({
                name: 'Family History of Hypothyroidism',
                description: 'You have a family history of hypothyroidism. This increases genetic risk for thyroid disorders.',
                impact: -12,
                type: 'negative'
            });
        }
    } else if (formData.FH_Hypothyroidism === 'no' || formData.fh_hypothyroidism === 'no') {
        if (condition === 'hypo') {
            factors.push({
                name: 'No Family History of Hypothyroidism',
                description: 'No family history of hypothyroidism. Genetic factors significantly increase risk, so their absence reduces likelihood.',
                impact: -13,
                type: 'negative'
            });
        } else if (condition === 'normal') {
            factors.push({
                name: 'No Family History of Hypothyroidism',
                description: 'No family history of hypothyroidism. This supports normal thyroid function.',
                impact: 10,
                type: 'positive'
            });
        }
    }

    if (formData.FH_Hyperthyroidism === 'yes' || formData.fh_hyperthyroidism === 'yes') {
        if (condition === 'hyper') {
            factors.push({
                name: 'Family History of Hyperthyroidism',
                description: 'You have a family history of hyperthyroidism. Graves\' disease and other hyperthyroid conditions have strong genetic components.',
                impact: 15,
                type: 'positive'
            });
        } else if (condition === 'normal') {
            factors.push({
                name: 'Family History of Hyperthyroidism',
                description: 'You have a family history of hyperthyroidism. This increases genetic risk for thyroid disorders.',
                impact: -12,
                type: 'negative'
            });
        }
    } else if (formData.FH_Hyperthyroidism === 'no' || formData.fh_hyperthyroidism === 'no') {
        if (condition === 'hyper') {
            factors.push({
                name: 'No Family History of Hyperthyroidism',
                description: 'No family history of hyperthyroidism. Genetic factors significantly increase risk, so their absence reduces likelihood.',
                impact: -13,
                type: 'negative'
            });
        } else if (condition === 'normal') {
            factors.push({
                name: 'No Family History of Hyperthyroidism',
                description: 'No family history of hyperthyroidism. This supports normal thyroid function.',
                impact: 10,
                type: 'positive'
            });
        }
    }

    if (formData.FH_Goiter === 'yes' || formData.fh_goiter === 'yes') {
        if (condition !== 'normal') {
            factors.push({
                name: 'Family History of Goiter',
                description: 'You have a family history of goiter (enlarged thyroid). This indicates genetic susceptibility to thyroid dysfunction.',
                impact: 12,
                type: 'positive'
            });
        } else {
            factors.push({
                name: 'Family History of Goiter',
                description: 'You have a family history of goiter. This indicates genetic susceptibility to thyroid dysfunction, which contradicts the normal diagnosis.',
                impact: -12,
                type: 'negative'
            });
        }
    } else if (formData.FH_Goiter === 'no' || formData.fh_goiter === 'no') {
        if (condition !== 'normal') {
            factors.push({
                name: 'No Family History of Goiter',
                description: 'No family history of goiter. This reduces genetic susceptibility to thyroid dysfunction.',
                impact: -10,
                type: 'negative'
            });
        } else {
            factors.push({
                name: 'No Family History of Goiter',
                description: 'No family history of goiter. This supports normal thyroid function.',
                impact: 10,
                type: 'positive'
            });
        }
    }

    if (formData.FH_ThyroidCancer === 'yes' || formData.fh_thyroid_cancer === 'yes') {
        if (condition !== 'normal') {
            factors.push({
                name: 'Family History of Thyroid Cancer',
                description: 'You have a family history of thyroid cancer. This indicates increased genetic risk for thyroid abnormalities and requires careful monitoring.',
                impact: 13,
                type: 'positive'
            });
        } else {
            // For normal prediction, family history is a risk factor (suppressing normal)
            factors.push({
                name: 'Family History of Thyroid Cancer',
                description: 'You have a family history of thyroid cancer. This indicates increased genetic risk for thyroid abnormalities, which contradicts the normal diagnosis.',
                impact: -13,
                type: 'negative'
            });
        }
    } else if (formData.FH_ThyroidCancer === 'no' || formData.fh_thyroid_cancer === 'no') {
        if (condition !== 'normal') {
            factors.push({
                name: 'No Family History of Thyroid Cancer',
                description: 'No family history of thyroid cancer. This reduces genetic risk for thyroid abnormalities.',
                impact: -11,
                type: 'negative'
            });
        } else {
            // For normal prediction, no family history supports it
            factors.push({
                name: 'No Family History of Thyroid Cancer',
                description: 'No family history of thyroid cancer. This supports normal thyroid function by reducing genetic risk.',
                impact: 11,
                type: 'positive'
            });
        }
    }

    // ========================================
    // 4. CURRENT SYMPTOMS ANALYSIS
    // ========================================

    if (formData.Sym_Fatigue === 'yes' || formData.sym_fatigue === 'yes') {
        if (condition === 'hypo') {
            factors.push({
                name: 'Fatigue/Weakness',
                description: 'You experience fatigue or weakness, a hallmark symptom of hypothyroidism caused by slowed metabolism and reduced energy production.',
                impact: 13,
                type: 'positive'
            });
        } else if (condition === 'hyper') {
            factors.push({
                name: 'Fatigue',
                description: 'You experience fatigue. While less common, hyperthyroidism can cause fatigue due to excessive energy expenditure and sleep disturbances.',
                impact: 8,
                type: 'positive'
            });
        } else {
            factors.push({
                name: 'Fatigue/Weakness',
                description: 'You experience fatigue. This can be a symptom of thyroid disorders, which contradicts the normal diagnosis.',
                impact: -11,
                type: 'negative'
            });
        }
    } else if (formData.Sym_Fatigue === 'no' || formData.sym_fatigue === 'no') {
        if (condition !== 'normal') {
            factors.push({
                name: 'No Fatigue',
                description: 'You do not experience fatigue. Thyroid disorders commonly cause fatigue, so its absence reduces the likelihood.',
                impact: -11,
                type: 'negative'
            });
        } else {
            factors.push({
                name: 'No Fatigue',
                description: 'You do not experience fatigue. This supports normal thyroid function.',
                impact: 11,
                type: 'positive'
            });
        }
    }

    if (formData.Sym_WeightChange === 'yes' || formData.sym_weight_change === 'yes') {
        if (condition === 'hypo') {
            factors.push({
                name: 'Unexplained Weight Gain',
                description: 'You have unexplained weight changes. Weight gain is a classic symptom of hypothyroidism due to decreased metabolic rate.',
                impact: 14,
                type: 'positive'
            });
        } else if (condition === 'hyper') {
            factors.push({
                name: 'Unexplained Weight Loss',
                description: 'You have unexplained weight changes. Weight loss despite normal eating is a key symptom of hyperthyroidism due to increased metabolism.',
                impact: 14,
                type: 'positive'
            });
        } else {
            factors.push({
                name: 'Unexplained Weight Changes',
                description: 'You have unexplained weight changes. This can be a symptom of thyroid disorders, which contradicts the normal diagnosis.',
                impact: -12,
                type: 'negative'
            });
        }
    } else if (formData.Sym_WeightChange === 'no' || formData.sym_weight_change === 'no') {
        if (condition !== 'normal') {
            factors.push({
                name: 'Stable Weight',
                description: 'Your weight is stable. Thyroid disorders typically cause unexplained weight changes, so stable weight reduces the likelihood.',
                impact: -12,
                type: 'negative'
            });
        } else {
            factors.push({
                name: 'Stable Weight',
                description: 'Your weight is stable. This supports normal thyroid function.',
                impact: 12,
                type: 'positive'
            });
        }
    }

    if (formData.Sym_DrySkin === 'yes' || formData.sym_dry_skin === 'yes') {
        if (condition === 'hypo') {
            factors.push({
                name: 'Dry Skin',
                description: 'You have dry skin, a common symptom of hypothyroidism caused by reduced sweat gland activity and decreased skin cell turnover.',
                impact: 10,
                type: 'positive'
            });
        } else if (condition === 'normal') {
            factors.push({
                name: 'Dry Skin',
                description: 'You have dry skin. This can be a symptom of hypothyroidism, which contradicts the normal diagnosis.',
                impact: -8,
                type: 'negative'
            });
        }
    } else if (formData.Sym_DrySkin === 'no' || formData.sym_dry_skin === 'no') {
        if (condition === 'hypo') {
            factors.push({
                name: 'Normal Skin',
                description: 'Your skin is normal. Hypothyroidism commonly causes dry skin, so normal skin reduces the likelihood.',
                impact: -8,
                type: 'negative'
            });
        } else if (condition === 'normal') {
            factors.push({
                name: 'Normal Skin',
                description: 'Your skin is normal. This supports normal thyroid function.',
                impact: 8,
                type: 'positive'
            });
        }
    }

    if (formData.Sym_HairLoss === 'yes' || formData.sym_hair_loss === 'yes') {
        if (condition !== 'normal') {
            factors.push({
                name: 'Hair Thinning/Loss',
                description: 'You experience hair thinning or loss. Both hypothyroidism and hyperthyroidism can cause hair loss through disrupted hair growth cycles.',
                impact: 11,
                type: 'positive'
            });
        } else {
            factors.push({
                name: 'Hair Thinning/Loss',
                description: 'You experience hair thinning or loss. This can be a symptom of thyroid disorders, which contradicts the normal diagnosis.',
                impact: -9,
                type: 'negative'
            });
        }
    } else if (formData.Sym_HairLoss === 'no' || formData.sym_hair_loss === 'no') {
        if (condition !== 'normal') {
            factors.push({
                name: 'Normal Hair Growth',
                description: 'Your hair growth is normal. Thyroid disorders commonly cause hair loss, so normal hair reduces the likelihood.',
                impact: -9,
                type: 'negative'
            });
        } else {
            factors.push({
                name: 'Normal Hair Growth',
                description: 'Your hair growth is normal. This supports normal thyroid function.',
                impact: 9,
                type: 'positive'
            });
        }
    }

    if (formData.Sym_HeartRate === 'yes' || formData.sym_heart_rate === 'yes') {
        if (condition === 'hyper') {
            factors.push({
                name: 'Rapid Heart Rate',
                description: 'You have abnormal heart rate. Rapid heartbeat (tachycardia) is a cardinal sign of hyperthyroidism due to increased cardiac stimulation.',
                impact: 16,
                type: 'positive'
            });
        } else if (condition === 'hypo') {
            factors.push({
                name: 'Slow Heart Rate',
                description: 'You have abnormal heart rate. Slow heartbeat (bradycardia) is common in hypothyroidism due to reduced cardiac metabolism.',
                impact: 12,
                type: 'positive'
            });
        } else {
            factors.push({
                name: 'Abnormal Heart Rate',
                description: 'You have abnormal heart rate. This can be a symptom of thyroid disorders, which contradicts the normal diagnosis.',
                impact: -12,
                type: 'negative'
            });
        }
    } else if (formData.Sym_HeartRate === 'no' || formData.sym_heart_rate === 'no') {
        if (condition !== 'normal') {
            factors.push({
                name: 'Normal Heart Rate',
                description: 'Your heart rate is normal. Thyroid disorders commonly cause heart rate abnormalities, so normal rate reduces the likelihood.',
                impact: -14,
                type: 'negative'
            });
        } else {
            factors.push({
                name: 'Normal Heart Rate',
                description: 'Your heart rate is normal. This supports normal thyroid function.',
                impact: 14,
                type: 'positive'
            });
        }
    }

    if (formData.Sym_Digestion === 'yes' || formData.sym_digestion === 'yes') {
        if (condition === 'hypo') {
            factors.push({
                name: 'Constipation',
                description: 'You experience digestive issues. Constipation is a frequent symptom of hypothyroidism due to slowed intestinal motility.',
                impact: 11,
                type: 'positive'
            });
        } else if (condition === 'hyper') {
            factors.push({
                name: 'Diarrhea',
                description: 'You experience digestive issues. Diarrhea or frequent bowel movements are common in hyperthyroidism due to increased intestinal motility.',
                impact: 11,
                type: 'positive'
            });
        } else {
            // For normal prediction, digestive issues are a symptom (suppressing normal)
            factors.push({
                name: 'Digestive Issues',
                description: 'You experience digestive issues. This can be a symptom of thyroid disorders, which contradicts the normal diagnosis.',
                impact: -9,
                type: 'negative'
            });
        }
    } else if (formData.Sym_Digestion === 'no' || formData.sym_digestion === 'no') {
        if (condition !== 'normal') {
            factors.push({
                name: 'Normal Digestion',
                description: 'Your digestion is normal. Thyroid disorders commonly cause digestive issues, so normal digestion reduces the likelihood.',
                impact: -9,
                type: 'negative'
            });
        } else {
            // For normal prediction, normal digestion supports it
            factors.push({
                name: 'Normal Digestion',
                description: 'Your digestion is normal. This supports normal thyroid function, as thyroid disorders often cause digestive issues.',
                impact: 9,
                type: 'positive'
            });
        }
    }

    if (formData.Sym_IrregularPeriods === 'yes' || formData.sym_irregular_periods === 'yes') {
        if (condition !== 'normal') {
            factors.push({
                name: 'Irregular Menstrual Periods',
                description: 'You have irregular periods. Thyroid hormones directly affect reproductive hormones, causing menstrual irregularities in both hypo and hyperthyroidism.',
                impact: 12,
                type: 'positive'
            });
        } else {
            factors.push({
                name: 'Irregular Menstrual Periods',
                description: 'You have irregular periods. This can be a symptom of thyroid disorders, which contradicts the normal diagnosis.',
                impact: -10,
                type: 'negative'
            });
        }
    } else if (formData.Sym_IrregularPeriods === 'no' || formData.sym_irregular_periods === 'no') {
        if (condition !== 'normal') {
            factors.push({
                name: 'Regular Periods',
                description: 'Your menstrual periods are regular. Thyroid disorders commonly cause irregular periods, so regular cycles reduce the likelihood.',
                impact: -10,
                type: 'negative'
            });
        } else {
            factors.push({
                name: 'Regular Periods',
                description: 'Your menstrual periods are regular. This supports normal thyroid function.',
                impact: 10,
                type: 'positive'
            });
        }
    }

    if (formData.Sym_NeckSwelling === 'yes' || formData.sym_neck_swelling === 'yes') {
        if (condition !== 'normal') {
            factors.push({
                name: 'Neck Swelling/Goiter',
                description: 'You have visible neck swelling or goiter. This indicates thyroid gland enlargement, which occurs in both hypothyroidism and hyperthyroidism.',
                impact: 17,
                type: 'positive'
            });
        } else {
            factors.push({
                name: 'Neck Swelling/Goiter',
                description: 'You have visible neck swelling or goiter. This indicates thyroid gland enlargement, which contradicts the normal diagnosis.',
                impact: -17,
                type: 'negative'
            });
        }
    } else if (formData.Sym_NeckSwelling === 'no' || formData.sym_neck_swelling === 'no') {
        if (condition !== 'normal') {
            factors.push({
                name: 'No Neck Swelling',
                description: 'You have no visible neck swelling. Thyroid disorders commonly cause goiter, so its absence reduces the likelihood.',
                impact: -15,
                type: 'negative'
            });
        } else {
            factors.push({
                name: 'No Neck Swelling',
                description: 'You have no visible neck swelling. This supports normal thyroid function.',
                impact: 15,
                type: 'positive'
            });
        }
    }

    // ========================================
    // 5. AGE FACTOR ANALYSIS
    // ========================================

    if (age > 60) {
        if (condition !== 'normal') {
            factors.push({
                name: 'Age Factor (60+)',
                description: `At age ${age}, you're in a high-risk group. Thyroid disorders become significantly more common after age 60, affecting up to 20% of people.`,
                impact: 14,
                type: 'positive'
            });
        }
    } else if (age > 50) {
        if (condition !== 'normal') {
            factors.push({
                name: 'Age Factor (50+)',
                description: `At age ${age}, your risk for thyroid disorders is elevated. Prevalence increases 3-fold after age 50, especially in women.`,
                impact: 12,
                type: 'positive'
            });
        }
    } else if (age > 40) {
        if (condition !== 'normal') {
            factors.push({
                name: 'Age Factor (40+)',
                description: `At age ${age}, you're entering a higher risk period. Thyroid function naturally declines with age, increasing disorder risk.`,
                impact: 8,
                type: 'positive'
            });
        }
    } else if (age < 30) {
        if (condition === 'normal') {
            factors.push({
                name: 'Age Factor (<30)',
                description: `At age ${age}, thyroid disorders are less common. Your younger age supports the likelihood of normal thyroid function.`,
                impact: 8,
                type: 'positive'
            });
        }
    }

    // ========================================
    // 6. GENDER FACTOR ANALYSIS
    // ========================================

    if (gender.includes('female') || gender.includes('woman')) {
        if (condition !== 'normal') {
            factors.push({
                name: 'Gender Factor (Female)',
                description: 'As a female, you have a significantly higher risk for thyroid disorders. Women are 5-8 times more likely than men to develop thyroid conditions, particularly hypothyroidism and autoimmune thyroid diseases.',
                impact: 12,
                type: 'positive'
            });
        } else {
            factors.push({
                name: 'Gender Factor (Female)',
                description: 'As a female, you have a higher baseline risk for thyroid disorders. Women are 5-8 times more likely than men to develop thyroid conditions.',
                impact: -8,
                type: 'negative'
            });
        }
    } else if (gender.includes('male') || gender.includes('man')) {
        if (condition === 'normal') {
            factors.push({
                name: 'Gender Factor (Male)',
                description: 'As a male, you have a lower baseline risk for thyroid disorders. Men are significantly less likely to develop thyroid conditions compared to women.',
                impact: 8,
                type: 'positive'
            });
        } else {
            factors.push({
                name: 'Gender Factor (Male)',
                description: 'As a male, thyroid disorders are less common. Men have a lower baseline risk compared to women.',
                impact: -8,
                type: 'negative'
            });
        }
    }

    // ========================================
    // 7. PROTECTIVE FACTORS (for normal diagnosis)
    // ========================================

    if (condition === 'normal') {
        let protectiveCount = 0;
        
        // No medical conditions
        if (formData.Diabetes !== 'yes' && formData.HighBloodPressure !== 'yes' && 
            formData.HighCholesterol !== 'yes' && formData.Anemia !== 'yes') {
            protectiveCount++;
        }
        
        // No family history
        if (formData.FH_Hypothyroidism !== 'yes' && formData.FH_Hyperthyroidism !== 'yes' && 
            formData.FH_Goiter !== 'yes' && formData.FH_ThyroidCancer !== 'yes') {
            protectiveCount++;
        }
        
        // No symptoms
        if (formData.Sym_Fatigue !== 'yes' && formData.Sym_WeightChange !== 'yes' && 
            formData.Sym_HeartRate !== 'yes' && formData.Sym_NeckSwelling !== 'yes') {
            protectiveCount++;
        }
        
        if (protectiveCount >= 2) {
            factors.push({
                name: 'No Risk Factors Present',
                description: 'You have no significant medical conditions, family history, or symptoms associated with thyroid disorders, strongly supporting normal thyroid function.',
                impact: 15,
                type: 'positive'
            });
        }
    }

    console.log('📊 Generated', factors.length, 'total factors');
    
    // Log all generated factors for debugging
    console.log('📋 All generated factors:');
    factors.forEach((f, i) => {
        console.log(`   ${i+1}. [${f.type}] ${f.name}: ${f.impact}`);
    });

    // ========================================
    // 8. SORT AND RETURN ALL FACTORS (NO LIMIT)
    // ========================================

    // Separate positive and negative factors
    const posFactors = factors
        .filter(f => f.type === 'positive')
        .sort((a, b) => Math.abs(b.impact) - Math.abs(a.impact));
        // REMOVED LIMIT - Show ALL positive factors

    const negFactors = factors
        .filter(f => f.type === 'negative')
        .sort((a, b) => Math.abs(b.impact) - Math.abs(a.impact));
        // REMOVED LIMIT - Show ALL negative factors

    console.log('✅ Positive (contributing) factors:', posFactors.length);
    console.log('❌ Negative (contradicting) factors:', negFactors.length);
    console.log('📊 TOTAL FACTORS RETURNED:', posFactors.length + negFactors.length);

    return [...posFactors, ...negFactors];
}
