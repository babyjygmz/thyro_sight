# 🚀 ThyroSight Deployment Guide
## Deploy to Render.com (Free - Accessible from Any Device)

This guide will help you deploy ThyroSight with:
- ✅ PHP Frontend & Backend
- ✅ Python ML API (Flask)
- ✅ MySQL Database
- ✅ Public URL accessible from anywhere
- ✅ **100% FREE** (no credit card required)

---

## 📋 Prerequisites

1. **GitHub Account** (free)
2. **Render.com Account** (free - sign up at https://render.com)

---

## 🎯 Step 1: Push Code to GitHub

### Option A: Using Git Command Line
```bash
cd thyro_sight
git init
git add .
git commit -m "Initial commit - ThyroSight deployment"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/thyrosight.git
git push -u origin main
```

### Option B: Using GitHub Desktop
1. Download GitHub Desktop from https://desktop.github.com
2. Open GitHub Desktop
3. Click "Add" → "Add Existing Repository"
4. Select your `thyro_sight` folder
5. Click "Publish repository"
6. Make it public (required for free tier)

---

## 🎯 Step 2: Deploy Database on Render

1. Go to https://render.com and sign in
2. Click **"New +"** → **"PostgreSQL"** (or MySQL if available)
3. Fill in:
   - **Name**: `thyrosight-db`
   - **Database**: `thydb`
   - **User**: `thyrosight_user`
   - **Region**: Choose closest to you
   - **Plan**: **Free**
4. Click **"Create Database"**
5. Wait 2-3 minutes for database to be ready
6. Copy the **Internal Database URL** (you'll need this)

---

## 🎯 Step 3: Import Database Schema

### Method 1: Using Render Dashboard
1. In your database dashboard, click **"Connect"**
2. Copy the **PSQL Command** or connection details
3. Use a database client (like DBeaver or pgAdmin) to connect
4. Run your `thydb.sql` file to create tables

### Method 2: Using Command Line
```bash
# If using PostgreSQL
psql <YOUR_DATABASE_URL> < thyro_sight/thydb.sql

# If using MySQL
mysql -h <HOST> -u <USER> -p<PASSWORD> <DATABASE> < thyro_sight/thydb.sql
```

---

## 🎯 Step 4: Deploy Python ML Backend

1. In Render Dashboard, click **"New +"** → **"Web Service"**
2. Connect your GitHub repository
3. Fill in:
   - **Name**: `thyrosight-ml-api`
   - **Region**: Same as database
   - **Branch**: `main`
   - **Root Directory**: `thyro_sight`
   - **Runtime**: **Python 3**
   - **Build Command**: `pip install -r requirements.txt`
   - **Start Command**: `gunicorn -b 0.0.0.0:$PORT flask_api_with_shap_example:app`
   - **Plan**: **Free**
4. Click **"Create Web Service"**
5. Wait 5-10 minutes for deployment
6. Copy your ML API URL (e.g., `https://thyrosight-ml-api.onrender.com`)

---

## 🎯 Step 5: Deploy PHP Frontend

1. In Render Dashboard, click **"New +"** → **"Web Service"**
2. Connect your GitHub repository
3. Fill in:
   - **Name**: `thyrosight-frontend`
   - **Region**: Same as database
   - **Branch**: `main`
   - **Root Directory**: `thyro_sight`
   - **Runtime**: **Docker**
   - **Plan**: **Free**
4. Add **Environment Variables**:
   - `DB_HOST`: (from database internal URL)
   - `DB_PORT`: `3306` or `5432`
   - `DB_NAME`: `thydb`
   - `DB_USER`: `thyrosight_user`
   - `DB_PASSWORD`: (from database credentials)
   - `ML_API_URL`: (your ML API URL from Step 4)
5. Click **"Create Web Service"**
6. Wait 5-10 minutes for deployment

---

## 🎯 Step 6: Update Frontend to Use ML API

Update your JavaScript files to point to the deployed ML API:

```javascript
// In your prediction JavaScript file
const ML_API_URL = 'https://thyrosight-ml-api.onrender.com';

async function getPrediction(formData) {
    const response = await fetch(`${ML_API_URL}/predict`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    });
    return await response.json();
}
```

---

## 🎯 Step 7: Test Your Deployment

Your app will be live at:
- **Frontend**: `https://thyrosight-frontend.onrender.com`
- **ML API**: `https://thyrosight-ml-api.onrender.com`

Test from any device:
1. Open the frontend URL on your phone/tablet/computer
2. Sign up for an account
3. Complete a health assessment
4. Verify predictions are working

---

## 🔧 Alternative: Deploy to Railway.app

Railway is another excellent free option:

1. Go to https://railway.app
2. Sign in with GitHub
3. Click **"New Project"** → **"Deploy from GitHub repo"**
4. Select your repository
5. Railway will auto-detect and deploy:
   - PHP service
   - Python service
   - MySQL database
6. Add environment variables in Railway dashboard
7. Get your public URLs

---

## 🔧 Alternative: Deploy Locally (LAN Access)

If you want to deploy on your local network:

### Using XAMPP (Windows)
1. Download XAMPP from https://www.apachefriends.org
2. Install and start Apache + MySQL
3. Copy `thyro_sight` to `C:\xampp\htdocs\`
4. Import database:
   - Open http://localhost/phpmyadmin
   - Create database `thydb`
   - Import `thydb.sql`
5. Start Python API:
   ```bash
   cd C:\xampp\htdocs\thyro_sight
   pip install -r requirements.txt
   python flask_api_with_shap_example.py
   ```
6. Access from any device on your network:
   - Find your IP: `ipconfig` (Windows) or `ifconfig` (Mac/Linux)
   - Access: `http://YOUR_IP/thyro_sight`

---

## 📱 Access from Any Device

Once deployed, you can access your app from:
- ✅ Any smartphone (iOS/Android)
- ✅ Any tablet
- ✅ Any computer
- ✅ Anywhere in the world with internet

Just share the URL: `https://thyrosight-frontend.onrender.com`

---

## 🆓 Free Tier Limitations

**Render.com Free Tier:**
- Services sleep after 15 minutes of inactivity
- First request after sleep takes 30-60 seconds to wake up
- 750 hours/month (enough for testing)
- Public repositories only

**Railway.app Free Tier:**
- $5 credit per month
- No sleep time
- Better for production use

---

## 🚀 Upgrade Options

If you need better performance:
- **Render**: $7/month per service (no sleep)
- **Railway**: Pay as you go ($0.000463/GB-hour)
- **DigitalOcean**: $4/month droplet
- **AWS/Azure**: Free tier for 12 months

---

## 🔒 Security Notes

Before going live:
1. Change database passwords
2. Update email credentials
3. Add HTTPS (Render provides this automatically)
4. Enable CORS properly
5. Add rate limiting
6. Sanitize all user inputs

---

## 📞 Need Help?

If you encounter issues:
1. Check Render logs (Dashboard → Service → Logs)
2. Verify environment variables
3. Test database connection
4. Check ML API health endpoint: `/health`

---

## ✅ Deployment Checklist

- [ ] Code pushed to GitHub
- [ ] Database created on Render
- [ ] Database schema imported
- [ ] ML API deployed and running
- [ ] Frontend deployed and running
- [ ] Environment variables configured
- [ ] Database connection working
- [ ] ML predictions working
- [ ] Tested from mobile device
- [ ] Tested from different network

---

**🎉 Congratulations!** Your ThyroSight app is now live and accessible from anywhere!
