# Color Palette Feature - Change Log

This document tracks all changes made to implement the color palette selector feature in the dashboard settings. Use this to revert changes if needed.

## Date: Current Session

## Overview
Added a color palette selector dropdown in the dashboard settings modal that allows users to choose different color schemes for the header background and button colors.

---

## Files Modified

### 1. `plugins/academy-practice-hub-dash/includes/class-frontend.php`

#### Changes Made:

**A. Added Color Palette Selector UI (around line 4746)**
- Added a new settings section with a dropdown selector for color palettes
- Location: After the checkbox settings section, before the modal footer
- Added HTML for:
  - Settings section with border-top separator
  - Label and description text
  - Select dropdown with 7 color palette options (default, green, blue, purple, red, orange, indigo)

**B. Updated `saveDashboardPreferences()` function (around line 13761)**
- Added `color_palette: $('#setting-color-palette').val()` to the preferences object

**C. Updated `loadDashboardPreferences()` function (around line 13598)**
- Added `$('#setting-color-palette').val(prefs.color_palette || 'default');`
- Added call to `applyColorPalette(prefs.color_palette)` if palette exists

**D. Updated `saveDashboardPreferences()` success handler (around line 13785)**
- Added call to `applyColorPalette(preferences.color_palette)` after applying theme

**E. Updated `loadAndApplyPreferences()` function (around line 13634)**
- Added call to `applyColorPalette(prefs.color_palette)` if palette exists

**F. Added `applyColorPalette()` function (around line 13828)**
- New function that applies color palettes to header and buttons
- Defines 7 color palettes with:
  - `headerBg`: gradient for `.jph-header`
  - `buttonPrimary`: color for `.jph-btn-primary`
  - `buttonPrimaryHover`: hover color for primary buttons
  - `buttonSecondary`: color for `.jph-btn-secondary`
  - `buttonSecondaryHover`: hover color for secondary buttons
- Sets CSS variables on dashboard element
- Applies colors directly to header and buttons
- Adds hover event handlers for buttons

**G. Added Color Palette Change Handler (after `applyColorPalette` function)**
- Added `$('#setting-color-palette').on('change', function() { ... })` handler

---

### 2. `plugins/academy-practice-hub-dash/includes/class-rest-api.php`

#### Changes Made:

**A. Updated `rest_get_dashboard_preferences()` function (around line 5850)**
- Added `'color_palette' => 'default'` to `$default_preferences` array
- Added color palette sanitization:
  ```php
  // Sanitize color palette
  if (isset($preferences['color_palette'])) {
      $allowed_palettes = array('default', 'green', 'blue', 'purple', 'red', 'orange', 'indigo');
      $preferences['color_palette'] = in_array($preferences['color_palette'], $allowed_palettes) ? $preferences['color_palette'] : 'default';
  }
  ```

**B. Updated `rest_update_dashboard_preferences()` function (around line 5921)**
- Added `'color_palette' => 'default'` to `$default_preferences` array
- Added color palette validation and sanitization:
  ```php
  // Color palette preference
  $allowed_palettes = array('default', 'green', 'blue', 'purple', 'red', 'orange', 'indigo');
  if (isset($preferences['color_palette']) && in_array($preferences['color_palette'], $allowed_palettes)) {
      $sanitized_preferences['color_palette'] = $preferences['color_palette'];
  } else {
      $sanitized_preferences['color_palette'] = $default_preferences['color_palette'];
  }
  ```

---

## Color Palette Definitions

The following color palettes are defined in the `applyColorPalette()` function:

1. **default**: Teal/Blue theme
   - Header: `linear-gradient(135deg, #004555 0%, #002A34 100%)`
   - Primary Button: `#F04E23`
   - Secondary Button: `#459E90`

2. **green**: Green theme
   - Header: `linear-gradient(135deg, #166534 0%, #0f4a1f 100%)`
   - Primary Button: `#22c55e`
   - Secondary Button: `#15803d`

3. **blue**: Blue theme
   - Header: `linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%)`
   - Primary Button: `#3b82f6`
   - Secondary Button: `#1d4ed8`

4. **purple**: Purple theme
   - Header: `linear-gradient(135deg, #6b21a8 0%, #581c87 100%)`
   - Primary Button: `#a855f7`
   - Secondary Button: `#7e22ce`

5. **red**: Red theme
   - Header: `linear-gradient(135deg, #991b1b 0%, #7f1d1d 100%)`
   - Primary Button: `#ef4444`
   - Secondary Button: `#b91c1c`

6. **orange**: Orange theme
   - Header: `linear-gradient(135deg, #c2410c 0%, #9a3412 100%)`
   - Primary Button: `#f97316`
   - Secondary Button: `#d97706`

7. **indigo**: Indigo theme
   - Header: `linear-gradient(135deg, #4338ca 0%, #3730a3 100%)`
   - Primary Button: `#6366f1`
   - Secondary Button: `#4f46e5`

---

## How to Revert

To undo these changes:

1. **Remove the color palette selector UI** from `class-frontend.php` (the settings section added around line 4746)

2. **Remove `color_palette` from `saveDashboardPreferences()`** function

3. **Remove color palette loading** from `loadDashboardPreferences()` function

4. **Remove color palette application** from `saveDashboardPreferences()` success handler

5. **Remove color palette application** from `loadAndApplyPreferences()` function

6. **Remove the entire `applyColorPalette()` function** and its change handler

7. **Remove `color_palette` from default preferences** in both REST API functions

8. **Remove color palette sanitization/validation** code from both REST API functions

---

## User Meta Data

The color palette preference is stored in user meta as part of the `aph_dashboard_preferences` JSON string. If reverting, existing user preferences will still contain `color_palette` but it will be ignored.

---

## Notes

- All colors are dark enough to maintain white text readability
- Hover states are automatically handled via JavaScript event handlers
- The feature applies colors immediately on selection and saves to user preferences
- Colors are applied on page load if a saved preference exists

