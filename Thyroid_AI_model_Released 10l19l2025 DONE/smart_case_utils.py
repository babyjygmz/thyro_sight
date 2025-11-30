# ===============================================
# smart_case_utils.py — Clean Skip-Missing Version (v2025.10)
# ===============================================

import pandas as pd
import numpy as np
from sklearn.metrics.pairwise import cosine_similarity

# =====================================================
#  Canonical feature sets (updated to match your 600-row dataset)
# =====================================================
BASE_FEATURES = [
    "Age", "Sex",
    "Sym_Fatigue", "Sym_WeightChange", "Sym_DrySkin", "Sym_HairLoss",
    "Sym_HeartRate", "Sym_Digestion", "Sym_IrregularPeriods", "Sym_NeckSwelling",
    "DepressionAnxiety", "AutoimmuneDiseases",
    "FH_Hypothyroidism", "FH_Hyperthyroidism", "FH_Goiter", "FH_ThyroidCancer"
]

LAB_FEATURES = ["TSH mIU/L", "T3 ng/dL", "T4 ng/dL", "FTI"]

# =====================================================
#  Input normalization
# =====================================================
def normalize_input(data_dict):
    rename_map = {
        "age": "Age", "sex": "Sex", "gender": "Sex",
        "tsh": "TSH mIU/L", "tshvalue": "TSH mIU/L",
        "t3": "T3 ng/dL", "t3value": "T3 ng/dL",
        "t4": "T4 ng/dL", "t4value": "T4 ng/dL",
        "fti": "FTI", "ftivalue": "FTI",
        "sym_fatigue": "Sym_Fatigue",
        "sym_weightchange": "Sym_WeightChange",
        "sym_dryskin": "Sym_DrySkin",
        "sym_hairloss": "Sym_HairLoss",
        "sym_heartrate": "Sym_HeartRate",
        "sym_digestion": "Sym_Digestion",
        "sym_irregularperiods": "Sym_IrregularPeriods",
        "sym_neckswelling": "Sym_NeckSwelling",
        "depressionanxiety": "DepressionAnxiety",
        "autoimmunediseases": "AutoimmuneDiseases",
        "fh_hypothyroidism": "FH_Hypothyroidism",
        "fh_hyperthyroidism": "FH_Hyperthyroidism",
        "fh_goiter": "FH_Goiter",
        "fh_thyroidcancer": "FH_ThyroidCancer",
    }

    fixed = {}
    for k, v in data_dict.items():
        key = rename_map.get(str(k).strip().lower(), k)
        fixed[key] = _safe_numeric(v)

    if "Age" in fixed:
        fixed["Age"] = _safe_numeric(fixed["Age"])
    if "Sex" in fixed:
        fixed["Sex"] = int(float(fixed["Sex"])) if str(fixed["Sex"]).strip() not in ["", "nan"] else 0

    return fixed

# =====================================================
#  Lab detection
# =====================================================
def detect_has_lab(data_dict):
    valid_count = sum(1 for lab in LAB_FEATURES if float(data_dict.get(lab, 0) or 0) > 0)
    return valid_count > 0

# =====================================================
#  Cosine similarity (skip-missing)
# =====================================================
def cosine_match(input_dict, splits, has_lab):
    base_cols = BASE_FEATURES + (LAB_FEATURES if has_lab else [])
    input_vector = _make_vector(input_dict, base_cols)
    best_class, best_score = None, -1
    scores = {}

    for cls, part in splits.items():
        if isinstance(part, dict):
            df_part = part.get("train") or next(iter(part.values()), pd.DataFrame())
        else:
            df_part = part

        if df_part is None or df_part.empty:
            continue

        #  Use only shared columns between dataset and base_cols
        shared_cols = [c for c in base_cols if c in df_part.columns]
        if not shared_cols:
            continue

        subset = df_part[shared_cols].fillna(0).astype(float)
        input_sub = _make_vector(input_dict, shared_cols)
        sims = cosine_similarity(input_sub, subset.values)[0]
        mean_sim = float(np.mean(sims))
        scores[cls] = round(mean_sim * 100, 2)

        if mean_sim > best_score:
            best_score = mean_sim
            best_class = cls

    return best_class, round(best_score * 100, 2), scores

# =====================================================
#  Confirmation confidence
# =====================================================
def confirm_case(best_class, input_dict, splits, has_lab):
    if not best_class or best_class not in splits or splits[best_class].empty:
        return 0.0

    base_cols = BASE_FEATURES + (LAB_FEATURES if has_lab else [])
    df = splits[best_class]
    shared_cols = [c for c in base_cols if c in df.columns]
    if not shared_cols:
        return 0.0

    input_vector = _make_vector(input_dict, shared_cols)
    df_subset = df[shared_cols].fillna(0).astype(float)
    sims = cosine_similarity(input_vector, df_subset.values)[0]
    return round(float(np.mean(sims)) * 100, 2)

# =====================================================
#  Helpers
# =====================================================
def _make_vector(data_dict, cols):
    s = pd.Series(data_dict)
    return (
        s.reindex(cols)
        .fillna(0)
        .infer_objects(copy=False)
        .astype(float)
        .values.reshape(1, -1)
    )

def _safe_numeric(value):
    if isinstance(value, (int, float)):
        return float(value)
    try:
        v = str(value).strip()
        if v.lower() in ["yes", "true"]:
            return 1.0
        if v.lower() in ["no", "false"]:
            return 0.0
        return float(v)
    except:
        return 0.0
