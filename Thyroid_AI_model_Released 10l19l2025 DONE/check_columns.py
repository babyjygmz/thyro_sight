import pandas as pd
df = pd.read_csv(r"C:\Users\rxkyfr\thyroid_ai_model\thyroid_dataset_FINAL_.csv", encoding='latin1')
print(" Columns in dataset:")
print(list(df.columns))
print("\n First few rows:")
print(df.head(3))
