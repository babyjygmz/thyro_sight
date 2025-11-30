import os
import subprocess
import time
import signal
import psutil

# ===============================================
#  Thyroid AI Flask Multi-App Launcher
# ===============================================

#  Define your Flask apps (script + port)
APPS = [
    {"name": "RandomForest", "script": "app.py", "port": 5000},
    {"name": "SVM", "script": "app_svm.py", "port": 5001},
    {"name": "GradientBoosting", "script": "app_gb.py", "port": 5002},
    {"name": "CNN", "script": os.path.join("cnn", "cnn_api.py"), "port": 5003},
]

# ===============================================
# 1 Optional: Kill any old Flask/python processes
# ===============================================
def cleanup_old_processes():
    print("🧹 Cleaning up old Flask processes...")
    for proc in psutil.process_iter(["pid", "name", "cmdline"]):
        try:
            if proc.info["name"] == "python.exe" or "python" in proc.info["name"].lower():
                cmdline = " ".join(proc.info["cmdline"]).lower()
                if any(app["script"].lower() in cmdline for app in APPS):
                    print(f"  Killing process {proc.pid} ({cmdline})")
                    proc.terminate()
        except (psutil.NoSuchProcess, psutil.AccessDenied):
            continue
    time.sleep(1)
    print(" Old processes cleaned up.\n")

# ===============================================
# 2 Launch all Flask servers
# ===============================================
def launch_flask_apps():
    processes = []
    base_dir = os.path.dirname(os.path.abspath(__file__))
    os.chdir(base_dir)

    print(" Starting Thyroid AI Flask Services...\n")
    for app in APPS:
        script_path = os.path.join(base_dir, app["script"])
        if not os.path.exists(script_path):
            print(f" Missing script: {script_path}")
            continue

        print(f" Launching {app['name']} API on port {app['port']}...")
        process = subprocess.Popen(
            ["python", script_path],
            creationflags=subprocess.CREATE_NEW_CONSOLE if os.name == "nt" else 0,
        )
        processes.append((app, process))
        time.sleep(2)  # give each app time to start

    print("\n All Flask services launched successfully!")
    print("------------------------------------------------")
    for app in APPS:
        print(f"🔹 {app['name']} API: http://127.0.0.1:{app['port']}")
    print("------------------------------------------------\n")

    try:
        print(" Press CTRL+C to stop all Flask servers.")
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        print("\n Stopping all Flask servers...")
        for app, process in processes:
            process.send_signal(signal.SIGTERM)
        print(" All servers stopped cleanly.")

# ===============================================
#  MAIN
# ===============================================
if __name__ == "__main__":
    cleanup_old_processes()
    launch_flask_apps()
