@echo off
echo ========================================
echo Starting ThyroSight ML API
echo ========================================
echo.

cd thyro_sight

echo Installing dependencies...
pip install -r requirements.txt

echo.
echo Starting Flask server...
echo ML API will be available at: http://localhost:5000
echo Press Ctrl+C to stop
echo.

python flask_api_with_shap_example.py

pause
