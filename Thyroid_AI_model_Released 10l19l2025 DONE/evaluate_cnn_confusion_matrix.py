import os
import pickle
import numpy as np
import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns
from sklearn.metrics import confusion_matrix, classification_report
from tensorflow.keras.models import load_model
from tensorflow.keras.preprocessing.image import ImageDataGenerator

# =====================================================
# Configuration
# =====================================================
BASE_DIR = 'cnn'
MODEL_PATH = os.path.join(BASE_DIR, 'thyroid_cnn_model.h5')
CLASS_INDICES_PATH = os.path.join(BASE_DIR, 'class_indices.pkl')
DATASET_DIR = 'dataset'
IMG_HEIGHT, IMG_WIDTH = 128, 128
BATCH_SIZE = 32

# =====================================================
# Load Model and Class Indices
# =====================================================
if not os.path.exists(MODEL_PATH):
    raise FileNotFoundError(f"Model not found: {MODEL_PATH}")

if not os.path.exists(CLASS_INDICES_PATH):
    raise FileNotFoundError(f"Class indices not found: {CLASS_INDICES_PATH}")

model = load_model(MODEL_PATH)
print("CNN model loaded successfully.")

with open(CLASS_INDICES_PATH, 'rb') as f:
    class_indices = pickle.load(f)

class_labels = {v: k for k, v in class_indices.items()}
labels = list(class_indices.keys())
print(f"Class labels: {class_labels}")

# =====================================================
# Data Generator for Evaluation (using validation split as test)
# =====================================================
datagen = ImageDataGenerator(rescale=1./255)

test_generator = datagen.flow_from_directory(
    DATASET_DIR,
    target_size=(IMG_HEIGHT, IMG_WIDTH),
    batch_size=BATCH_SIZE,
    class_mode='categorical',
    shuffle=False  # important for confusion matrix
)

# =====================================================
# Make Predictions
# =====================================================
predictions = model.predict(test_generator)
y_pred = np.argmax(predictions, axis=1)
y_true = test_generator.classes

# Map to labels
y_pred_labels = [class_labels[i] for i in y_pred]
y_true_labels = [class_labels[i] for i in y_true]

# =====================================================
# Confusion Matrix and Classification Report
# =====================================================
cm = confusion_matrix(y_true_labels, y_pred_labels, labels=labels)

print("\nClassification Report:")
print(classification_report(y_true_labels, y_pred_labels, target_names=labels, zero_division=0))

plt.figure(figsize=(7, 6))
sns.heatmap(cm, annot=True, fmt='d', cmap='Blues',
            xticklabels=labels, yticklabels=labels)
plt.title("CNN Model — Confusion Matrix")
plt.xlabel("Predicted Label")
plt.ylabel("True Label")
plt.tight_layout()
plt.savefig("confusion_matrix_cnn.png")
# plt.show()  # Commented out to avoid blocking in terminal

print("\nConfusion Matrix Summary:")
print(pd.DataFrame(cm,
                   index=[f"Actual_{l}" for l in labels],
                   columns=[f"Pred_{l}" for l in labels]))
