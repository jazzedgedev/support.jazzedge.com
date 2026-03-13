# REST API Performance Audit: aph Namespace Endpoints
## JazzEdge Academy – Dashboard Slow Page Load Investigation

**Date:** March 13, 2025  
**Scope:** Custom REST endpoints in `aph/v1` namespace, with focus on dashboard-related endpoints reported as bottleneck.

---

## A. Endpoint Summary Table

| Route | Method | Callback | File | Used By | Likely Purpose | Risk Level |
|-------|--------|----------|------|---------|----------------|------------|
| `/notifications/popup` | GET | `rest_get_popup_notification` | class-rest-api.php | class-frontend.php (dashboard) | Popup notification on page load | **High** |
| `/practice-sessions` | GET | `rest_get_practice_sessions` | class-rest-api.php | class-frontend.php (dashboard, chart, history) | Practice history + chart data | **High** |
| `/user-stats` | GET | `rest_get_user_stats` | class-rest-api.php | class-frontend.php (stats, leaderboard) | Streak, level, gems | Medium |
| `/analytics` | GET | `rest_get_analytics` | class-rest-api.php | class-frontend.php (analytics tab, chart) | User analytics (7/30/90/365 days) | **High** |
| `/lesson-favorites` | GET | `rest_get_lesson_favorites` | class-rest-api.php | class-frontend.php, lesson shortcodes | Favorites dropdown/list | Medium |
| `/plan` | GET | `rest_get_plan` | class-rest-api.php | class-frontend.php (plan section) | 90-day plan, focus | Medium |
| `/user/dashboard-preferences` | GET | `rest_get_dashboard_preferences` | class-rest-api.php | class-frontend.php (init, settings) | Layout visibility, theme | Low |
| `/user/dashboard-preferences` | POST | `rest_update_dashboard_preferences` | class-rest-api.php | class-frontend.php | Save preferences | Low |
| `/badges` | GET | `rest_get_user_badges` | class-rest-api.php | class-frontend.php (badges tab) | Badge list + earned status | Medium |
| `/repertoire` | GET | `rest_get_repertoire` | class-rest-api.php | class-frontend.php (repertoire section) | User repertoire list | Medium |
| `/leaderboard` | GET | `rest_get_leaderboard` | class-rest-api.php | class-frontend.php | Leaderboard data | Medium |
| `/leaderboard/position` | GET | `rest_get_user_position` | class-frontend.php | Leaderboard widget | User position | Low |
| `/leaderboard/stats` | GET | `rest_get_leaderboard_stats` | class-frontend.php | Leaderboard widget | Leaderboard stats | Medium |
| `/export-practice-history` | GET | `rest_export_practice_history` | class-rest-api.php | class-frontend.php | CSV export | Low (on-demand) |
| `/ai-analysis` | GET | `rest_get_ai_analysis` | class-rest-api.php | class-frontend.php | AI-generated analysis | **High** (generates on every request) |

*Additional admin, JPC, debug, and other endpoints omitted from table; full list in Endpoint Inventory below.*

---

## B. Detailed Findings Per Endpoint

### 1. `/notifications/popup` (GET)

| Field | Value |
|-------|-------|
| **File path** | `plugins/academy-practice-hub-dash/includes/class-rest-api.php` (lines 6962–7019) |
| **Callback** | `rest_get_popup_notification` |
| **Permission callback** | `check_user_permission` |
| **Frontend call locations** | `class-frontend.php:19265` – `initNotificationPopup()` on DOM ready |
| **Main query/data sources** | • `ALM_Notifications_Manager::get_popup_notification()` → `get_notifications(limit=50)` – `SELECT * FROM notifications`<br>• `get_user_meta($user_id, 'alm_notification_popups_shown', true)`<br>• PHP filtering of 50 rows for `show_popup=1`, date check, and “not shown” |
| **Performance risks** | • Always fetches up to 50 notifications even when few are popups<br>• No indexing assumed on `show_popup`, `publish_at`<br>• Loaded on every dashboard visit regardless of popup need |
| **Duplicate call risk** | Low – called once on page load |
| **Cacheability** | Yes – per user; popup eligibility changes infrequently |
| **Quick optimization ideas** | • Add `WHERE show_popup = 1` and `publish_at` conditions in SQL<br>• Reduce limit to ~10<br>• Cache per user for ~5–15 minutes or until shown |

---

### 2. `/practice-sessions` (GET)

| Field | Value |
|-------|-------|
| **File path** | `plugins/academy-practice-hub-dash/includes/class-rest-api.php` (lines 1019–1096) |
| **Callback** | `rest_get_practice_sessions` |
| **Permission callback** | `check_user_permission` |
| **Frontend call locations** | • `class-frontend.php:18853` – practice history tab<br>• `class-frontend.php:18855` – analytics chart (`initializePracticeChart`)<br>• `class-frontend.php:18858` – `generateDailyData` with `limit: 1000`<br>• `class-frontend.php:19888`, `20077` – practice sessions display |
| **Main query/data sources** | • With dates: `$wpdb->prepare` SELECT from `jph_practice_sessions` + LEFT JOIN `jph_practice_items`, ORDER BY `created_at DESC`, LIMIT/OFFSET<br>• Without dates: `$this->database->get_practice_sessions($user_id, $limit, $offset)` |
| **Performance risks** | • **limit=1000** in `generateDailyData` (class-frontend.php:18858) – heavy for chart<br>• **limit=1000** in `rest_delete_practice_session` to verify ownership before delete (lines 1106–1112)<br>• No indexed `created_at` / `user_id` guarantee<br>• Chart and history both fetch sessions; possible duplicate logic |
| **Duplicate call risk** | High – chart and history fetch independently; analytics tab re-triggers chart + practice-sessions |
| **Cacheability** | Partial – sessions change on log; chart data can be cached per user for 5–15 min |
| **Quick optimization ideas** | • Use date range + smaller limit for chart; e.g. 90 days max<br>• For delete, check ownership with `SELECT 1 ... LIMIT 1` instead of `get_practice_sessions(1000)`<br>• Share practice-sessions data between chart and history where possible |

---

### 3. `/user-stats` (GET)

| Field | Value |
|-------|-------|
| **File path** | `plugins/academy-practice-hub-dash/includes/class-rest-api.php` (lines 6637–6655) |
| **Callback** | `rest_get_user_stats` |
| **Permission callback** | `check_user_permission` |
| **Frontend call locations** | `class-frontend.php:21940`, `22062` – stats block and leaderboard |
| **Main query/data sources** | `$this->database->get_user_stats($user_id)` → `SELECT * FROM jph_user_stats WHERE user_id = %d` |
| **Performance risks** | • `SELECT *` – minor; table is small and single row per user<br>• Risk is duplication: stats likely needed in multiple dashboard sections |
| **Duplicate call risk** | Medium – stats and leaderboard may both request it |
| **Cacheability** | Yes – per user; changes on practice/level-up; can cache 1–5 minutes |
| **Quick optimization ideas** | • Transient cache per user (1–5 min)<br>• Combine with dashboard bootstrap payload to avoid extra request |

---

### 4. `/analytics` (GET)

| Field | Value |
|-------|-------|
| **File path** | `plugins/academy-practice-hub-dash/includes/class-rest-api.php` (lines 4541–4734) |
| **Callback** | `rest_get_analytics` |
| **Permission callback** | `check_user_permission` |
| **Frontend call locations** | • `class-frontend.php:18747` – analytics tab<br>• `class-frontend.php:18956` – `initializePracticeChart`<br>• `class-frontend.php:20814` – analytics tab load |
| **Main query/data sources** | **9+ raw SQL queries**, all recomputed each request:<br>1. 7-day: `SELECT COUNT(*), SUM(duration_minutes), AVG(...) FROM jph_practice_sessions WHERE user_id AND created_at >= 7d`<br>2. 30-day: same pattern<br>3. 90-day: same pattern<br>4. 365-day: same pattern<br>5. `SELECT * FROM jph_user_stats WHERE user_id`<br>6. `COUNT(DISTINCT DATE(created_at))` for 30 days<br>7. `GROUP BY DATE(created_at) ORDER BY total_minutes DESC LIMIT 1` (best day)<br>8. `GROUP BY HOUR(created_at) ORDER BY sessions DESC LIMIT 1` (favorite hour)<br>9. `GROUP BY practice_item_id` for most practiced item |
| **Performance risks** | • **No caching** – all work done on every request<br>• Full scans of `jph_practice_sessions` for long windows (90/365 days)<br>• `GROUP BY DATE(created_at)` and `GROUP BY HOUR(created_at)` can be slow on large tables<br>• Multiple heavy queries in sequence |
| **Duplicate call risk** | High – analytics tab and chart both call it; chart also calls practice-sessions |
| **Cacheability** | Yes – per user; can cache 15–60 minutes |
| **Quick optimization ideas** | • Cache result in transients or object cache, e.g. `aph_analytics_{user_id}` for 15–30 min<br>• Add composite index `(user_id, created_at)` on `jph_practice_sessions`<br>• Optionally pre-aggregate daily stats in a separate table |

---

### 5. `/lesson-favorites` (GET)

| Field | Value |
|-------|-------|
| **File path** | `plugins/academy-practice-hub-dash/includes/class-rest-api.php` (lines 1861–1875) |
| **Callback** | `rest_get_lesson_favorites` |
| **Permission callback** | `check_user_permission` |
| **Frontend call locations** | • `class-frontend.php:20700`, `21393` – favorites dropdown<br>• `academy-lesson-manager-shortcodes.php` – lesson cards<br>• `academy-lesson-manager/includes/class-frontend-search.php` |
| **Main query/data sources** | `$database->get_lesson_favorites($user_id)` → `SELECT * FROM jph_lesson_favorites WHERE user_id ORDER BY created_at DESC`<br>Then N+1-style loop: for each favorite with `/lesson/{id}` URL, `get_lesson_permalink_from_id()` runs `SELECT post_id FROM alm_lessons` + `get_permalink()` |
| **Performance risks** | • **N+1**: `get_lesson_permalink_from_id` per favorite with legacy URL format<br>• `get_permalink()` can trigger more queries<br>• No LIMIT on favorites |
| **Duplicate call risk** | Medium – dashboard and lesson/shortcode contexts may both load |
| **Cacheability** | Yes – per user; changes when favorites are modified |
| **Quick optimization ideas** | • Batch permalink resolution (collect IDs, one query, map in PHP)<br>• Cache permalinks by lesson_id<br>• Add LIMIT if large lists are possible |

---

### 6. `/plan` (GET)

| Field | Value |
|-------|-------|
| **File path** | `plugins/academy-practice-hub-dash/includes/class-rest-api.php` (lines 9769–9804) |
| **Callback** | `rest_get_plan` |
| **Permission callback** | `check_user_permission` |
| **Frontend call locations** | `class-frontend.php:18356` – plan section |
| **Main query/data sources** | • `$this->database->get_user_plan($user_id)` – `SELECT * FROM jph_user_plans WHERE user_id`<br>• `$this->database->get_weekly_session_count($user_id)` – internally calls `get_user_plan` again and `maybe_reset_week`, which can call `get_user_plan` a third time |
| **Performance risks** | • **Redundant reads** – plan fetched 2–3 times per request via `get_weekly_session_count` and `maybe_reset_week`<br>• `get_user_plan` runs multiple times for same user in one request |
| **Duplicate call risk** | Low – single section |
| **Cacheability** | Yes – per user; changes when plan or sessions change |
| **Quick optimization ideas** | • Refactor `get_weekly_session_count` to reuse already-fetched plan<br>• Avoid repeated `get_user_plan` in `maybe_reset_week` |

---

### 7. `/user/dashboard-preferences` (GET)

| Field | Value |
|-------|-------|
| **File path** | `plugins/academy-practice-hub-dash/includes/class-rest-api.php` (lines 6729–6813) |
| **Callback** | `rest_get_dashboard_preferences` |
| **Permission callback** | `check_user_permission` |
| **Frontend call locations** | • `class-frontend.php:19113`, `19160`, `19198`, `19230`, `19492`, `19536`, `19565` – `loadAndApplyPreferences`, `loadDashboardPreferences`, `updateRoadmapVisibilityPreference` |
| **Main query/data sources** | `get_user_meta($user_id, 'aph_dashboard_preferences', true)` |
| **Performance risks** | Low – single user meta read |
| **Duplicate call risk** | **High** – `loadAndApplyPreferences` and `loadDashboardPreferences` both call GET; `updateRoadmapVisibilityPreference` calls GET again if `dashboardPreferences` is null |
| **Cacheability** | Yes – per user; change frequency is low |
| **Quick optimization ideas** | • Call once on init; pass shared `dashboardPreferences` to other functions<br>• Lazy-load only when settings modal opens |

---

### 8. `/badges` (GET)

| Field | Value |
|-------|-------|
| **File path** | `plugins/academy-practice-hub-dash/includes/class-rest-api.php` (lines 1820–1855) |
| **Callback** | `rest_get_user_badges` |
| **Permission callback** | `check_user_permission` |
| **Frontend call locations** | `class-frontend.php:20638` – badges tab |
| **Main query/data sources** | • `$database->get_badges(true)` – `SELECT * FROM jph_badges WHERE is_active = 1`<br>• `$database->get_user_badges($user_id)` – `SELECT ub.*, b.name, ... FROM jph_user_badges ub LEFT JOIN jph_badges b` |
| **Performance risks** | • Two queries per request<br>• `get_badges` fetches all active badges; could be heavy if many badge types<br>• PHP merge of badges + user badges |
| **Duplicate call risk** | Low – badges tab |
| **Cacheability** | Yes – badges list changes rarely; user badges change on award |
| **Quick optimization ideas** | • Cache global badge list (e.g. 1 hour)<br>• Single JOIN query: badges + user badges in one SELECT |

---

### 9. `/repertoire` (GET)

| Field | Value |
|-------|-------|
| **File path** | `plugins/academy-practice-hub-dash/includes/class-rest-api.php` (lines 1134–1146) |
| **Callback** | `rest_get_repertoire` |
| **Permission callback** | `check_user_permission` |
| **Frontend call locations** | `class-frontend.php:22689` – repertoire section |
| **Main query/data sources** | `$this->database->get_user_repertoire($user_id, $order_by, $order)` – `SELECT * FROM academy_user_repertoire WHERE user_id AND deleted_at IS NULL ORDER BY {order_by}` |
| **Performance risks** | • No LIMIT – can return large lists<br>• `ORDER BY last_practiced` may not be indexed |
| **Duplicate call risk** | Low |
| **Cacheability** | Yes – per user |
| **Quick optimization ideas** | • Add reasonable LIMIT (e.g. 50–100) with pagination if needed<br>• Index `(user_id, last_practiced)` |

---

### 10. `/ai-analysis` (GET)

| Field | Value |
|-------|-------|
| **File path** | `plugins/academy-practice-hub-dash/includes/class-rest-api.php` (lines 4739–4766) |
| **Callback** | `rest_get_ai_analysis` |
| **Permission callback** | `check_user_permission` |
| **Frontend call locations** | `class-frontend.php:20959` – AI analysis tab |
| **Main query/data sources** | `$this->generate_ai_analysis($user_id)` – **explicitly uncached**; comment: "Generate new analysis (no caching)" |
| **Performance risks** | • **Very expensive** – full analysis generated every time<br>• Likely iterates over practice data and possibly external APIs |
| **Duplicate call risk** | Low – only when tab is opened |
| **Cacheability** | Yes – per user; can cache 24h or until new practice |
| **Quick optimization ideas** | • Add transient cache (e.g. 24h)<br>• Or compute asynchronously and return last cached result immediately |

---

## C. Cross-Cutting Problems

### 1. Duplicate Frontend Calls on Dashboard Load

On page load, multiple endpoints are requested in parallel with little coordination:

- `loadAndApplyPreferences()` → `GET /user/dashboard-preferences`
- `initNotificationPopup()` → `GET /notifications/popup`
- `loadBadges()` → `GET /badges`
- `loadLessonFavorites()` → `GET /lesson-favorites`
- `loadAnalytics()` → `GET /analytics`
- `loadPlanData()` → `GET /plan` (when plan visible)
- `loadRepertoireItems()` → `GET /repertoire` (when repertoire visible)
- Stats block → `GET /user-stats`

Each runs as a separate HTTP request, increasing latency and connection overhead.

### 2. Multiple Calls to Same Endpoint

- **dashboard-preferences**: 2–3 GETs from different init paths; `updateRoadmapVisibilityPreference` can trigger another if prefs not yet loaded.
- **analytics**: Main analytics tab and chart both call `/analytics`; chart also calls `/practice-sessions` with `limit=1000`.

### 3. Analytics Computed on Every Request

`/analytics` performs 9+ queries and aggregations with no caching, making it a primary hotspot for 10–17+ second responses.

### 4. Large Payloads and Aggressive Limits

- `limit=1000` for practice-sessions in chart and delete-verification.
- `get_notifications(limit=50)` for popup, though only one is needed.
- No pagination or limits on repertoire and lesson-favorites.

### 5. N+1 and Repeated Lookups

- **lesson-favorites**: `get_lesson_permalink_from_id` per favorite with legacy URL.
- **plan**: `get_user_plan` called 2–3 times per `/plan` request.

### 6. No Dashboard Bootstrap Endpoint

There is no single endpoint that returns dashboard-preferences, user-stats, plan summary, badges summary, and notifications in one request. Creating one could replace 5–7 calls on initial load.

### 7. WP Engine–Specific Legacy Code

| File | Location | Note |
|------|----------|------|
| `class-rest-api.php` | Lines 808–816 | `check_user_permission` – “For WP Engine, we need to handle authentication differently” (nonce-first check) |
| `class-rest-api.php` | Lines 8022–8034 | `rest_jpc_test` – `wp_engine_note` in response |
| `academy-lesson-manager/includes/class-frontend-search.php` | 3005, 3177 | “Get REST API nonce for authentication (WPEngine strips cookies)” |
| `academy-lesson-manager/includes/class-rest.php` | 1876, 1891 | WPEngine cookie stripping note |
| `academy-lesson-manager/includes/class-admin-settings.php` | 2991 | “Increment cache version to bust WP Engine cache” |
| `academy-lesson-manager-shortcodes.php` | 17618 | “Add cache version for WP Engine compatibility” |
| `keap-reports/includes/class-admin.php` | 2312 | “WP Engine Cron” message |

Recommendation: Keep nonce-based auth if needed for your hosting; remove or generalize WP Engine–specific comments and behavior if no longer on WP Engine.

---

## D. Top 5 Worst Endpoints To Fix First

### 1. `/analytics` (GET)

- 9+ heavy SQL queries, no cache.
- Multiple full scans of `jph_practice_sessions` for 7/30/90/365 days.
- Called on analytics tab and chart.
- Strongest candidate for 10–17+ second responses.

**Fix**: Add per-user transient cache (15–30 min) and ensure `(user_id, created_at)` index on `jph_practice_sessions`.

### 2. `/practice-sessions` (GET)

- `limit=1000` used for chart and delete verification.
- Overlapping use with `/analytics` for similar data.
- No caching despite relatively static history.

**Fix**: Reduce limit for chart (e.g. date range + 90–180 rows), optimize delete-verification, add short-term cache for chart data.

### 3. `/notifications/popup` (GET)

- Loaded on every dashboard visit.
- Fetches 50 notifications when only one popup is needed.
- No SQL filtering for `show_popup` / dates.

**Fix**: Filter in SQL, lower limit, add short per-user cache.

### 4. `/user/dashboard-preferences` (GET)

- Light per-request cost, but called 2–3 times from different code paths.
- Adds unnecessary requests and latency.

**Fix**: Consolidate to a single load, share `dashboardPreferences` across init functions.

### 5. `/lesson-favorites` (GET)

- N+1 via `get_lesson_permalink_from_id` for each favorite.
- Extra DB and permalink work per favorite.

**Fix**: Batch lesson IDs, resolve permalinks in one or few queries, cache permalinks by lesson ID.

---

## E. Endpoint Inventory (Complete List)

All aph endpoints are registered in `plugins/academy-practice-hub-dash/includes/class-rest-api.php`:

| Route | Method | Callback | Permission |
|-------|--------|----------|------------|
| `/test` | GET | `rest_test` | `check_admin_permission` |
| `/debug/routes` | GET | `rest_debug_routes` | `check_admin_permission` |
| `/leaderboard` | GET | `rest_get_leaderboard` | `check_rate_limit_permission` |
| `/leaderboard/position` | GET | `rest_get_user_position` | `check_user_permission` |
| `/leaderboard/stats` | GET | `rest_get_leaderboard_stats` | `check_rate_limit_permission` |
| `/leaderboard/display-name` | POST | `rest_update_display_name` | `check_user_permission` |
| `/leaderboard/visibility` | POST | `rest_update_leaderboard_visibility` | `check_user_permission` |
| `/user-stats` | GET | `rest_get_user_stats` | `check_user_permission` |
| `/user/timezone` | GET/POST | `rest_get_user_timezone` / `rest_update_user_timezone` | `check_user_permission` |
| `/user/dashboard-preferences` | GET/POST | `rest_get_dashboard_preferences` / `rest_update_dashboard_preferences` | `check_user_permission` |
| `/roadmap/step-completion` | POST | `rest_update_roadmap_step_completion` | `check_user_permission` |
| `/notifications/popup` | GET | `rest_get_popup_notification` | `check_user_permission` |
| `/notifications/popup/(?P<id>\d+)/mark-shown` | POST | `rest_mark_popup_shown` | `check_user_permission` |
| `/notifications/list` | GET | `rest_get_notifications_list` | `check_user_permission` |
| `/practice-sessions` | GET/POST | `rest_get_practice_sessions` / `rest_log_practice_session` | `check_user_permission` |
| `/practice-sessions/(?P<id>\d+)` | DELETE | `rest_delete_practice_session` | `check_user_permission` |
| `/repertoire` | GET/POST | `rest_get_repertoire` / `rest_add_repertoire` | `check_user_permission` |
| `/repertoire/(?P<id>\d+)` | PUT/DELETE | `rest_update_repertoire` / `rest_delete_repertoire` | `check_user_permission` |
| `/repertoire/(?P<id>\d+)/practice` | POST | `rest_mark_repertoire_practiced` | `check_user_permission` |
| `/repertoire/order` | POST | `rest_update_repertoire_order` | `check_user_permission` |
| `/badges` | GET | `rest_get_user_badges` | `check_user_permission` |
| `/lesson-favorites` | GET/POST | `rest_get_lesson_favorites` / `rest_add_lesson_favorite` | `check_user_permission` |
| `/lesson-favorites/remove` | POST | `rest_remove_lesson_favorite` | `check_user_permission` |
| `/lesson-favorites/check` | POST | `rest_check_lesson_favorite` | `check_user_permission` |
| `/plan` | GET/POST | `rest_get_plan` / `rest_save_plan` | `check_user_permission` |
| `/plan/transformation` | PUT | `rest_update_plan_transformation` | `check_user_permission` |
| `/plan/goal` | PUT | `rest_update_plan_goal` | `check_user_permission` |
| `/plan/focus` | PUT | `rest_update_plan_focus` | `check_user_permission` |
| `/plan/steps` | PUT | `rest_update_plan_steps` | `check_user_permission` |
| `/plan/practiced` | POST | `rest_mark_plan_practiced` | `check_user_permission` |
| `/analytics` | GET | `rest_get_analytics` | `check_user_permission` |
| `/ai-analysis` | GET | `rest_get_ai_analysis` | `check_user_permission` |
| `/beta-disclaimer/shown` | POST | `rest_mark_beta_disclaimer_shown` | `check_user_permission` |
| `/purchase-shield` | POST | `rest_purchase_streak_shield` | `check_user_permission` |
| `/export-practice-history` | GET | `rest_export_practice_history` | `check_user_permission` |

*Class name: `JPH_REST_API` (inferred from callback array format).*

---

## F. Recommended Optimization Order

1. **`/analytics`** – Add transient cache (15–30 min) and index `(user_id, created_at)` on `jph_practice_sessions`.
2. **`/practice-sessions`** – Reduce chart limit, fix delete verification, add light cache for chart use case.
3. **`/notifications/popup`** – Add SQL filters, lower limit, per-user cache.
4. **`/user/dashboard-preferences`** – Single load on init; share `dashboardPreferences` across functions.
5. **`/lesson-favorites`** – Fix N+1 with batched permalink resolution.
6. **`/plan`** – Refactor `get_weekly_session_count` to avoid repeated `get_user_plan` calls.
7. **`/badges`** – Add cache for badge list; consider single JOIN query.
8. **`/user-stats`** – Add short per-user cache; consider including in bootstrap.
9. **`/repertoire`** – Add LIMIT and pagination; index `(user_id, last_practiced)`.
10. **`/ai-analysis`** – Add transient cache (e.g. 24h).
11. **Dashboard bootstrap** – New endpoint aggregating preferences, stats, plan summary, badges summary, and notification flag; use to replace 5–7 initial calls.

---

## G. Suggested Shell Commands for Further Analysis

```bash
# List all aph REST routes
grep -rn "register_rest_route('aph" plugins/academy-practice-hub-dash/

# Find frontend usage of aph endpoints
grep -rn "aph/v1" plugins/ --include="*.php" --include="*.js" | grep -v backup

# Check for limit=1000 or large limits
grep -rn "limit.*1000\|limit: 1000" plugins/academy-practice-hub-dash/

# Find get_user_meta / get_user_plan calls that may be redundant
grep -rn "get_user_plan\|get_user_meta.*aph_dashboard" plugins/academy-practice-hub-dash/

# WP Engine references
grep -rn -i "wp engine\|wpengine" plugins/ --include="*.php" | grep -v backup
```

---

*Report generated from codebase analysis. No code was modified.*
