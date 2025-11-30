from sklearn.preprocessing import StandardScaler, MinMaxScaler
import pandas as pd

# Sample dataset
data = pd.DataFrame({
    'TSH': [0.5, 1.2, 5.8, 400],
    'T3': [0.3, 2.5, 3.8, 8.0],
    'TT4': [50, 120, 200, 300]
})

# StandardScaler
scaler_std = StandardScaler()
data_std = scaler_std.fit_transform(data)

# MinMaxScaler
scaler_mm = MinMaxScaler()
data_mm = scaler_mm.fit_transform(data)

print("Standard Scaled:\n", data_std)
print("\nMinMax Scaled:\n", data_mm)
