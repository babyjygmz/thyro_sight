@echo off
title Thyroid AI — Full Server Launcher
echo =========================================================
echo         Starting All Thyroid AI Model APIs
echo =========================================================
echo.

:: Step 1 — Move to this script's folder
cd /d "%~dp0"

:: Step 2 — Activate the virtual environment
echo [1/5] Activating virtual environment...
call venv\Scripts\activate
echo Virtual environment activated.
echo.

:: Step 3 — Start each Flask API in its own window

echo [2/5] Launching app.py (Main Flask API)...
start "Main App" cmd /k "call venv\Scripts\activate && python app.py"

echo [3/5] Launching app_svm.py (SVM API)...
start "SVM API" cmd /k "call venv\Scripts\activate && python app_svm.py"

echo [4/5] Launching app_gb.py (Gradient Boosting API)...
start "GB API" cmd /k "call venv\Scripts\activate && python app_gb.py"

echo [5/5] Launching CNN API (cnn_api.py)...
cd cnn
start "CNN API" cmd /k "call ..\venv\Scripts\activate && python cnn_api.py"
cd ..

echo.
echo =========================================================
echo  All servers launched successfully!
echo  You can now open each console window to monitor logs.
echo =========================================================

pause
