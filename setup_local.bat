@echo off
echo ========================================
echo ThyroSight Local Setup Script
echo ========================================
echo.

REM Check if XAMPP is installed
if exist "C:\xampp\htdocs\" (
    echo [OK] XAMPP found
) else (
    echo [ERROR] XAMPP not found!
    echo Please install XAMPP from https://www.apachefriends.org
    pause
    exit /b 1
)

echo.
echo Step 1: Copying files to XAMPP...
xcopy /E /I /Y "thyro_sight" "C:\xampp\htdocs\thyro_sight"
echo [OK] Files copied

echo.
echo Step 2: Starting XAMPP services...
start "" "C:\xampp\xampp-control.exe"
echo [OK] XAMPP Control Panel opened

echo.
echo Step 3: Waiting for MySQL to start...
timeout /t 5 /nobreak > nul

echo.
echo Step 4: Creating database...
"C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS thydb;"
"C:\xampp\mysql\bin\mysql.exe" -u root thydb < "thyro_sight\thydb.sql"
echo [OK] Database created and imported

echo.
echo Step 5: Installing Python dependencies...
pip install -r thyro_sight\requirements.txt
echo [OK] Python packages installed

echo.
echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Your app is now running at:
echo   Frontend: http://localhost/thyro_sight
echo.
echo To start the ML API, run:
echo   cd thyro_sight
echo   python flask_api_with_shap_example.py
echo.
echo To access from other devices on your network:
echo   1. Find your IP: ipconfig
echo   2. Share: http://YOUR_IP/thyro_sight
echo.
pause
