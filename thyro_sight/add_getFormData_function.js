// Helper function to get form data
function getFormData() {
    const formData = {};

    // Get radio button values
    const radioFields = [
        // Medical History
        'thyroxine', 'advised-thyroxine', 'antithyroid', 'illness', 'pregnant', 
        'surgery', 'radioactive', 'hypo-suspected', 'hyper-suspected', 'lithium', 
        'goitre', 'tumor', 'hypopituitarism', 'psychiatric', 'tsh', 't3', 't4', 
        't4-uptake', 'fti',
        // Other Medical Conditions
        'Diabetes', 'HighBloodPressure', 'HighCholesterol', 'Anemia', 
        'DepressionAnxiety', 'HeartDisease', 'MenstrualIrregularities', 'AutoimmuneDiseases',
        // Family History
        'FH_Hypothyroidism', 'FH_Hyperthyroidism', 'FH_Goiter', 'FH_ThyroidCancer',
        // Current Symptoms
        'Sym_Fatigue', 'Sym_WeightChange', 'Sym_DrySkin', 'Sym_HairLoss', 'Sym_HeartRate',
        'Sym_Digestion', 'Sym_IrregularPeriods', 'Sym_NeckSwelling'
    ];

    radioFields.forEach(field => {
        const selected = document.querySelector(`input[name="${field}"]:checked`);
        formData[field] = selected ? selected.value : 'no'; // default to 'no'
    });

    // Get input values for lab tests
    const conditionalFields = ['tsh', 't3', 't4', 't4-uptake', 'fti'];
    conditionalFields.forEach(field => {
        const inputContainer = document.getElementById(field + '-input');
        if (inputContainer) {
            const input = inputContainer.querySelector('input');
            if (input) {
                formData[field + 'Value'] = input.value.trim();
            }
        }
    });

    // Get age and gender
    const ageField = document.getElementById('calculated-age');
    if (ageField && ageField.value) {
        const ageMatch = ageField.value.match(/\d+/);
        formData.age = ageMatch ? parseInt(ageMatch[0]) : 0;
    } else {
        formData.age = 0;
    }

    const genderField = document.getElementById('gender-display');
    if (genderField && genderField.value) {
        formData.gender = genderField.value.toLowerCase();
    } else {
        formData.gender = 'unknown';
    }

    return formData;
}
