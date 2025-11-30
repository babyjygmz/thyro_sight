# ThyroSight ML API Deployment Guide

## Deploy to Railway

### Option 1: Deploy from Root Directory (Recommended)

1. **Create New Service in Railway:**
   - Go to your Railway project
   - Click "+ New" → "Empty Service"
   - Connect to your GitHub repository
   - Railway will detect this as a Python project

2. **Configure Root Path:**
   - In Railway service settings → "Settings"
   - Set **Root Directory**: `Thyroid_AI_model_Released 10l19l2025 DONE`
   - Railway will automatically use the files in this directory

3. **Environment Variables:**
   - `PORT` - Automatically set by Railway
   - No other variables needed

4. **Deploy:**
   - Railway will automatically:
     - Install dependencies from `requirements.txt`
     - Use `nixpacks.toml` for build configuration
     - Start the app with gunicorn

### Option 2: Deploy as Separate Repository

1. Create a new repository with just this folder's contents
2. Push to GitHub
3. Deploy to Railway from that repository

## Testing the API

Once deployed, test with:

```bash
# Health check
curl https://your-ml-api.railway.app/health

# Prediction
curl -X POST https://your-ml-api.railway.app/predict \
  -H "Content-Type: application/json" \
  -d '{
    "age": 45,
    "gender": "female",
    "TSH": 5.2,
    "T3": 1.8,
    "T4": 8.5,
    "fatigue": 1,
    "weight_gain": 1
  }'
```

## Available Endpoints

- `GET /health` - Health check
- `POST /predict` - Make prediction
- `GET /api/info` - API information

## Models Included

- Random Forest Lab Model (`thyroid_rf_lab.pkl`)
- Random Forest Symptom-Only Model (`thyroid_rf_symptom_only.pkl`)
- Gradient Boosting Model (`thyroid_gb_lab.pkl`)
- SVM Model (`thyroid_svm_lab.pkl`)
- All scalers and SHAP explainers
