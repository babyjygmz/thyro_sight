# cnn_api.py
import os
from flask import Flask, request, jsonify
from tensorflow.keras.models import load_model
from tensorflow.keras.preprocessing.image import img_to_array, load_img
import numpy as np
from werkzeug.utils import secure_filename
import pickle
from flask_cors import CORS

# -----------------------------
# Paths and Config
# -----------------------------
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH = os.path.join(BASE_DIR, 'thyroid_cnn_model.h5')
CLASS_INDICES_PATH = os.path.join(BASE_DIR, 'class_indices.pkl')
ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg'}
IMG_HEIGHT, IMG_WIDTH = 128, 128
TEMP_DIR = os.path.join(BASE_DIR, 'temp')

os.makedirs(TEMP_DIR, exist_ok=True)

# -----------------------------
# Initialize Flask app
# -----------------------------
app = Flask(__name__)
CORS(app)  # allow cross-origin requests

# -----------------------------
# Load model and class mapping
# -----------------------------
try:
    model = load_model(MODEL_PATH)
    print(" CNN model loaded successfully.")
except Exception as e:
    print(f" Failed to load model: {e}")
    raise

try:
    with open(CLASS_INDICES_PATH, 'rb') as f:
        class_indices = pickle.load(f)
    class_labels = {v: k for k, v in class_indices.items()}  # inverse mapping
    print(f" Class labels: {class_labels}")
except Exception as e:
    print(f" Failed to load class indices: {e}")
    raise

# -----------------------------
# Helper functions
# -----------------------------
def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

def preprocess_image(image_path):
    img = load_img(image_path, target_size=(IMG_HEIGHT, IMG_WIDTH))
    img_array = img_to_array(img) / 255.0
    img_array = np.expand_dims(img_array, axis=0)  # add batch dimension
    return img_array

# -----------------------------
# Routes
# -----------------------------
@app.route('/predict_image', methods=['POST'])
def predict_image():
    if 'image' not in request.files:
        return jsonify({'success': False, 'message': 'No image provided'}), 400

    file = request.files['image']
    if file.filename == '':
        return jsonify({'success': False, 'message': 'No selected file'}), 400

    if not allowed_file(file.filename):
        return jsonify({'success': False, 'message': 'Invalid file type'}), 400

    filename = secure_filename(file.filename)
    temp_path = os.path.join(TEMP_DIR, filename)
    file.save(temp_path)

    try:
        img_array = preprocess_image(temp_path)
        predictions = model.predict(img_array)
        class_index = np.argmax(predictions[0])
        confidence = float(predictions[0][class_index]) * 100
        prediction_label = class_labels[class_index]

        os.remove(temp_path)  # clean up

        return jsonify({
            'success': True,
            'prediction': prediction_label,
            'confidence': round(confidence, 2)
        })
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)}), 500

# -----------------------------
# Run Flask app
# -----------------------------
if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5001)
