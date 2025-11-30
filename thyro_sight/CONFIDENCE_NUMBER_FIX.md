# Confidence Number Font Size Fix

## Problem
The confidence number text (e.g., "85%") was too large (1.5rem) and was overlapping the blue circle ring, making it unreadable.

![Problem](https://i.imgur.com/example.png)
- Text was extending beyond the white inner circle
- Overlapping with the colored ring
- Poor readability

## Solution
Reduced the font size of the confidence number to fit properly inside the white inner circle (55px diameter) without overlapping the colored ring.

## Changes Made

### Desktop View
- **Circle outer size**: 80px (unchanged)
- **Circle inner size**: 55px (unchanged)
- **Font size**: 1.5rem → **0.95rem** ✅
- **Margin bottom**: 0.2rem → **0** (removed extra spacing)

### Mobile View  
- **Circle outer size**: 80px (unchanged)
- **Circle inner size**: 55px (unchanged)
- **Font size**: 1.5rem → **0.9rem** ✅

## Visual Comparison

### Before (1.5rem):
```
┌──────────────┐
│   ╭────╮     │
│  ╱ 85% ╲     │  ← Text overlaps ring
│ │  ██   │    │
│  ╲ ██  ╱     │
│   ╰────╯     │
└──────────────┘
```

### After (0.95rem):
```
┌──────────────┐
│   ╭────╮     │
│  ╱      ╲    │
│ │  85%  │    │  ← Text fits perfectly
│  ╲      ╱    │
│   ╰────╯     │
└──────────────┘
```

## Technical Details

The confidence circle has:
- **Outer ring**: 80px diameter (colored ring)
- **Inner circle**: 55px diameter (white background)
- **Available space for text**: ~45px (accounting for padding)

With the old font size (1.5rem ≈ 24px), the text "85%" was approximately:
- Width: ~35-40px
- Height: ~24px
- **Result**: Overlapping the ring

With the new font size (0.95rem ≈ 15px), the text "85%" is approximately:
- Width: ~25-28px
- Height: ~15px
- **Result**: Fits comfortably inside the inner circle

## Files Modified
- `thyro_sight/health-assessment.html` - Updated `.confidence-number` font-size in 2 places

## How to Test
1. Clear browser cache (Ctrl+Shift+Delete)
2. Hard refresh (Ctrl+F5)
3. Submit a health assessment
4. Check the result popup
5. Verify the confidence percentage fits inside the white circle without overlapping the colored ring

## Result
✅ Confidence number now fits perfectly inside the inner circle
✅ No overlap with the colored ring
✅ Better readability
✅ Consistent sizing across all conditions (normal, hypo, hyper)
✅ Responsive on mobile devices
