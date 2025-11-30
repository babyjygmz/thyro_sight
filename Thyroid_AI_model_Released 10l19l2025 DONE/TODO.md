# TODO: Integrate SHAP Explanations into GB API Response

- [x] Load SHAP explainer in app_gb.py at startup
- [x] Compute SHAP values in /predict endpoint after scaling input
- [x] Extract top contributing factors (positive SHAP values)
- [x] Extract top suppressing factors (negative SHAP values)
- [x] Add SHAP details to JSON response
- [x] Test the updated API for SHAP inclusion
