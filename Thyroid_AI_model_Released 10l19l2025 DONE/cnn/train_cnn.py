import os
import tensorflow as tf
from tensorflow.keras.models import Sequential
from tensorflow.keras.layers import Conv2D, MaxPooling2D, Flatten, Dense, Dropout
from tensorflow.keras.preprocessing.image import ImageDataGenerator
import pickle
from sklearn.preprocessing import LabelEncoder

# -----------------------------
# Paths
# -----------------------------
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DATASET_DIR = os.path.join(BASE_DIR, '..', 'dataset')
MODEL_DIR = BASE_DIR
MODEL_PATH = os.path.join(MODEL_DIR, 'thyroid_cnn_model.h5')
HISTORY_PATH = os.path.join(MODEL_DIR, 'history.pkl')
CLASS_INDICES_PATH = os.path.join(MODEL_DIR, 'class_indices.pkl')
ENCODER_PATH = os.path.join(MODEL_DIR, 'cnn_label_encoder.pkl')

# -----------------------------
# Image parameters
# -----------------------------
IMG_HEIGHT, IMG_WIDTH = 128, 128
BATCH_SIZE = 32
EPOCHS = 15

# -----------------------------
# Data generators
# -----------------------------
datagen = ImageDataGenerator(
    rescale=1./255,
    validation_split=0.2,
    rotation_range=20,
    zoom_range=0.15,
    horizontal_flip=True
)

train_generator = datagen.flow_from_directory(
    DATASET_DIR,
    target_size=(IMG_HEIGHT, IMG_WIDTH),
    batch_size=BATCH_SIZE,
    class_mode='categorical',
    subset='training'
)

validation_generator = datagen.flow_from_directory(
    DATASET_DIR,
    target_size=(IMG_HEIGHT, IMG_WIDTH),
    batch_size=BATCH_SIZE,
    class_mode='categorical',
    subset='validation'
)

# -----------------------------
# Save class indices mapping
# -----------------------------
with open(CLASS_INDICES_PATH, 'wb') as f:
    pickle.dump(train_generator.class_indices, f)
print(f" Saved class mapping: {train_generator.class_indices}")

# -----------------------------
# Lightweight CNN model
# -----------------------------
model = Sequential([
    Conv2D(32, (3,3), activation='relu', input_shape=(IMG_HEIGHT, IMG_WIDTH, 3)),
    MaxPooling2D(2,2),
    Conv2D(64, (3,3), activation='relu'),
    MaxPooling2D(2,2),
    Conv2D(128, (3,3), activation='relu'),
    MaxPooling2D(2,2),
    Flatten(),
    Dense(128, activation='relu'),
    Dropout(0.5),
    Dense(len(train_generator.class_indices), activation='softmax')
])

model.compile(optimizer='adam', loss='categorical_crossentropy', metrics=['accuracy'])
model.summary()

# -----------------------------
# Train model
# -----------------------------
history = model.fit(
    train_generator,
    validation_data=validation_generator,
    epochs=EPOCHS
)

# -----------------------------
# Save model and history
# -----------------------------
model.save(MODEL_PATH)
with open(HISTORY_PATH, 'wb') as f:
    pickle.dump(history.history, f)

print(f" Model saved: {MODEL_PATH}")
print(f" Training history saved: {HISTORY_PATH}")

# -----------------------------
# Create and save CNN Label Encoder for RF
# -----------------------------
# Load class names sorted by index
class_names = [None] * len(train_generator.class_indices)
for name, idx in train_generator.class_indices.items():
    class_names[idx] = name

# Fit LabelEncoder
cnn_encoder = LabelEncoder()
cnn_encoder.fit(class_names)

# Save encoder
with open(ENCODER_PATH, 'wb') as f:
    pickle.dump(cnn_encoder, f)

print(f" CNN Label Encoder saved: {ENCODER_PATH}")
