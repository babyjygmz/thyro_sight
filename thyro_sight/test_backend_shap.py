"""
Test Script to Check if Backend APIs Return SHAP Values

This script tests your Flask APIs to see if they're returning SHAP values.
Run this to diagnose the issue.

Usage:
    python test_backend_shap.py
"""

import requests
import json

# Test data
test_data = {
    "age": 45,
    "gender": 0,
    "tsh": 5.5,
    "t3": 110,
    "t4": 7.5,
    "fti": 2.1,
    "t4_uptake": 28,
    "diabetes": 0,
    "high_blood_pressure": 1,
    "high_cholesterol": 1,
    "anemia": 0,
    "depression_anxiety": 1,
    "heart_disease": 0,
    "menstrual_irregularities": 0,
    "autoimmune_diseases": 0,
    "fh_hypothyroidism": 1,
    "fh_hyperthyroidism": 0,
    "fh_goiter": 0,
    "fh_thyroid_cancer": 0,
    "sym_fatigue": 1,
    "sym_weight_change": 1,
    "sym_dry_skin": 1,
    "sym_hair_loss": 0,
    "sym_heart_rate": 0,
    "sym_digestion": 1,
    "sym_irregular_periods": 0,
    "sym_neck_swelling": 0
}

# API endpoints
endpoints = {
    "Random Forest": "http://127.0.0.1:5000/predict",
    "SVM": "http://127.0.0.1:5002/predict",
    "Gradient Boosting": "http://127.0.0.1:5003/predict"
}

print("=" * 60)
print("TESTING BACKEND APIs FOR SHAP VALUES")
print("=" * 60)
print()

for model_name, url in endpoints.items():
    print(f"Testing {model_name} at {url}...")
    print("-" * 60)
    
    try:
        # Send request
        response = requests.post(
            url,
            json=test_data,
            headers={"Content-Type": "application/json"},
            timeout=10
        )
        
        # Check if request was successful
        if response.status_code == 200:
            result = response.json()
            
            print(f"✅ {model_name} API is responding")
            print(f"   Status Code: {response.status_code}")
            print(f"   Prediction: {result.get('prediction', 'N/A')}")
            print(f"   Confidence: {result.get('confidence', 'N/A')}%")
            
            # Check for SHAP values
            has_shap = False
            shap_field = None
            
            if 'shap_values' in result and result['shap_values']:
                has_shap = True
                shap_field = 'shap_values'
            elif 'shap_explanation' in result and result['shap_explanation']:
                has_shap = True
                shap_field = 'shap_explanation'
            elif 'feature_importance' in result and result['feature_importance']:
                has_shap = True
                shap_field = 'feature_importance'
            
            if has_shap:
                shap_data = result[shap_field]
                print(f"   ✅ SHAP VALUES FOUND in '{shap_field}'!")
                print(f"   Number of factors: {len(shap_data)}")
                
                # Show first 3 factors
                print(f"   Top 3 factors:")
                for i, factor in enumerate(shap_data[:3], 1):
                    print(f"      {i}. {factor.get('name', 'Unknown')}: {factor.get('impact', 0)}%")
            else:
                print(f"   ❌ NO SHAP VALUES FOUND")
                print(f"   Available fields: {list(result.keys())}")
                print(f"   ")
                print(f"   ⚠️  ACTION REQUIRED:")
                print(f"   Your {model_name} API needs to be updated to return SHAP values.")
                print(f"   See: SHAP_BACKEND_INTEGRATION_GUIDE.md")
            
        else:
            print(f"❌ {model_name} API returned error")
            print(f"   Status Code: {response.status_code}")
            print(f"   Response: {response.text[:200]}")
            
    except requests.exceptions.ConnectionError:
        print(f"❌ {model_name} API is NOT RUNNING")
        print(f"   Could not connect to {url}")
        print(f"   Make sure the Flask server is running on this port")
        
    except requests.exceptions.Timeout:
        print(f"⏱️  {model_name} API TIMEOUT")
        print(f"   Request took too long (>10 seconds)")
        
    except Exception as e:
        print(f"❌ {model_name} API ERROR")
        print(f"   Error: {str(e)}")
    
    print()

print("=" * 60)
print("SUMMARY")
print("=" * 60)
print()
print("If you see '❌ NO SHAP VALUES FOUND' for any API:")
print("1. Open SHAP_BACKEND_INTEGRATION_GUIDE.md")
print("2. Follow the steps to add SHAP calculation to your Flask APIs")
print("3. Restart the Flask servers")
print("4. Run this test script again")
print()
print("If you see '❌ API is NOT RUNNING':")
print("1. Start your Flask API servers")
print("2. Make sure they're running on the correct ports (5000, 5002, 5003)")
print("3. Run this test script again")
print()
print("Once all APIs return SHAP values, the frontend will show")
print("unique factors for each user based on their specific data!")
print()
