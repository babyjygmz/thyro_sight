# Read the files
$htmlContent = Get-Content 'thyro_sight\health-assessment.html' -Raw -Encoding UTF8
$newFunction = Get-Content 'thyro_sight\enhanced_shap_factors.js' -Raw -Encoding UTF8

Write-Host "Reading files..."
Write-Host "HTML file size: $($htmlContent.Length) characters"
Write-Host "New function size: $($newFunction.Length) characters"

# Find the function start
$functionStart = $htmlContent.IndexOf('function generateEnhancedSHAPFactors(condition, age, riskFactors) {')

if ($functionStart -eq -1) {
    Write-Host "ERROR: Function not found!" -ForegroundColor Red
    exit 1
}

Write-Host "Function found at position: $functionStart" -ForegroundColor Green

# Find the function end by counting braces
$braceCount = 0
$inFunction = $false
$functionEnd = -1

for ($i = $functionStart; $i -lt $htmlContent.Length; $i++) {
    $char = $htmlContent[$i]
    
    if ($char -eq '{') {
        $braceCount++
        $inFunction = $true
    }
    elseif ($char -eq '}') {
        $braceCount--
        if ($inFunction -and $braceCount -eq 0) {
            $functionEnd = $i + 1
            break
        }
    }
}

if ($functionEnd -eq -1) {
    Write-Host "ERROR: Could not find function end!" -ForegroundColor Red
    exit 1
}

Write-Host "Function ends at position: $functionEnd" -ForegroundColor Green
$oldFunctionLength = $functionEnd - $functionStart
Write-Host "Old function length: $oldFunctionLength characters"

# Extract parts
$beforeFunction = $htmlContent.Substring(0, $functionStart)
$afterFunction = $htmlContent.Substring($functionEnd)

# Create new content
$newContent = $beforeFunction + $newFunction + $afterFunction

Write-Host "New file size: $($newContent.Length) characters"

# Backup original
$backupPath = 'thyro_sight\health-assessment.html.backup'
Copy-Item 'thyro_sight\health-assessment.html' $backupPath -Force
Write-Host "Backup created: $backupPath" -ForegroundColor Yellow

# Write new content
Set-Content 'thyro_sight\health-assessment.html' -Value $newContent -Encoding UTF8 -NoNewline

Write-Host "`n✅ SUCCESS! Function replaced!" -ForegroundColor Green
Write-Host "Old function: $oldFunctionLength characters"
Write-Host "New function: $($newFunction.Length) characters"
Write-Host "Difference: $($newFunction.Length - $oldFunctionLength) characters"
