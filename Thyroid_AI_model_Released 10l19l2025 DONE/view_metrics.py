import json
import pandas as pd
import matplotlib.pyplot as plt

# Load the metrics JSON file
with open("model_comparison_metrics.json", "r", encoding="utf-8") as f:
    metrics_data = json.load(f)

# Flatten to a DataFrame
records = []
for dataset_name, models in metrics_data.items():
    for model_name, m in models.items():
        records.append({
            "Dataset": dataset_name,
            "Model": model_name,
            "Accuracy": m.get("accuracy", None),
            "Precision": m.get("precision", None),
            "Recall": m.get("recall", None),
            "F1 Score": m.get("f1_score", None)
        })

df = pd.DataFrame(records)
print("\n Model Evaluation Summary:")
print(df)

# Plot accuracy comparison
plt.figure(figsize=(8, 5))
for dataset in df["Dataset"].unique():
    subset = df[df["Dataset"] == dataset]
    plt.plot(subset["Model"], subset["Accuracy"], marker="o", label=f"{dataset} Accuracy")

plt.title("Thyroid AI Model Performance Comparison")
plt.xlabel("Model")
plt.ylabel("Accuracy")
plt.legend()
plt.grid(True)
plt.tight_layout()
plt.show()
