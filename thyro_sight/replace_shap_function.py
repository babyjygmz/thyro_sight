import re

# Read the health-assessment.html file
with open('thyro_sight/health-assessment.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Read the enhanced function from the JS file
with open('thyro_sight/enhanced_shap_factors.js', 'r', encoding='utf-8') as f:
    enhanced_function = f.read()

# Find the old function and replace it
# Pattern to match the function from start to end
pattern = r'function generateEnhancedSHAPFactors\(condition, age, riskFactors\) \{[\s\S]*?console\.log\(\'❌ Negative \(contradicting\) factors:\', negFactors\.length\);\s*return \[\.\.\.posFactors, \.\.\.negFactors\];\s*\}'

# Replace the old function with the new one
new_content = re.sub(pattern, enhanced_function.strip(), content, count=1)

# Check if replacement was successful
if new_content != content:
    # Write the updated content back
    with open('thyro_sight/health-assessment.html', 'w', encoding='utf-8') as f:
        f.write(new_content)
    print("✅ Successfully replaced the generateEnhancedSHAPFactors function!")
    print(f"📊 File size: {len(new_content)} characters")
else:
    print("❌ Function not found or replacement failed!")
    print("Searching for function signature...")
    if 'function generateEnhancedSHAPFactors' in content:
        print("✓ Function signature found in file")
        # Find the function location
        match = re.search(r'function generateEnhancedSHAPFactors', content)
        if match:
            start = match.start()
            context = content[max(0, start-100):start+200]
            print(f"Context around function:\n{context}")
    else:
        print("✗ Function signature not found in file")
