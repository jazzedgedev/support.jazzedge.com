# JazzEdge Academy: WP Engine → SiteGround Migration Notes

**Migration date:** March 2025  
**WordPress:** 6.9.1  
**Site:** jazzedge.academy

## Completed Fixes

### 1. Server Path Migration (WP Engine → SiteGround)
All hardcoded `/nas/content/live/jazzacademy/` paths were replaced with WordPress constants:
- `ABSPATH` — WordPress root
- `WP_CONTENT_DIR` — wp-content directory

**Files updated:**
- `plugins/oxygen-functions/oxygen-functions.php`
- `plugins/academy-lesson-manager-shortcodes/academy-lesson-manager-shortcodes.php`
- `plugins/keap-reports/includes/class-api.php`
- `plugins/wmyette-shortcodes/wmyette-shortcodes.php`
- `plugins/academy-lesson-manager/includes/class-vimeo-api.php`

### 2. Constant Redefinition (JAZZEDGE_MAIN_SITE_URL, JAZZEDGE_API_KEY)
Wrapped constant definitions with `defined()` checks to prevent "Constant already defined" warnings when multiple plugins define them.

**Files updated:**
- `plugins/oxygen-functions/oxygen-functions.php` (lines ~833, ~6493)
- `plugins/fluent-support-ai-integration/fluent-support-ai-integration.php`

### 3. wp-auth-check / Heartbeat Dependency (WP 6.9.1)
A workaround was added in `oxygen-functions.php` to re-register the heartbeat script when it has been deregistered by performance plugins (e.g. Asset CleanUp Pro, SiteGround Optimizer). This resolves:

> Notice: script "wp-auth-check" enqueued with unregistered dependency: heartbeat

**Alternative fix:** Disable "Disable Heartbeat" in:
- SiteGround Optimizer → Frontend → Disable Heartbeat
- Asset CleanUp Pro → Unload Heartbeat (exclude admin if possible)

---

## Action Required: Third-Party Plugin Updates

### FV Player Pro (fv-wordpress-flowplayer)
**Issue:** `_load_textdomain_just_in_time called incorrectly for fv-wordpress-flowplayer`

**Action:** Update FV Player Pro to the latest version. Do not modify vendor/third-party plugin code. Check the plugin’s updates page or vendor for a WP 6.9-compatible release.

---

## Files Not Modified (Intentional)

- **streak_data/old_streak_data.sql** — Historical DB data; paths in SQL are for reference only. Fix during import if needed.
- **oxygen-functions backup copies** (`*-original-working.php`, `*-b4-credit-log.php`, etc.) — Kept as backups; update only if restored as primary.
- **wmyette-shortcodes copy.php / copy 2.php** — Backup files.

---

## Server Path Reference

| Old (WP Engine)       | New (SiteGround)                                      |
|----------------------|--------------------------------------------------------|
| `/nas/content/live/jazzacademy/` | `/home/customer/www/jazzedge.academy/public_html/` |

Use `ABSPATH` instead of hardcoded paths for portability.

---

## Post-Migration: Database Mismatch (Records Not Showing)

**Symptom:** Records added manually in phpMyAdmin don't appear in the app (e.g. Grade JPC → To Be Graded).

**Cause:** After a server move, WordPress often connects to a **different database** than before. phpMyAdmin may show multiple databases:
- `dbt2yxek4je7xk` (old/staging database)
- `dbywtz4fzmjxwu` (jazzedge.academy — **active site DB**)

If you insert into the wrong database, the site won't see the data.

**Fix:**
1. Check `wp-config.php` for `DB_NAME` — that's the database WordPress uses.
2. In phpMyAdmin, switch to that database before inserting/editing.
3. Or run: `SELECT DATABASE();` in MySQL to confirm the current DB, then use the same DB in phpMyAdmin.

**Note:** Table prefix (`wp_`) is usually unchanged; the database name is what changed.

---

## Post-Migration: Lesson Sync Fails (wp_insert_post / insert_id returns 0)

**Symptom:** "Lesson saved to database but WordPress post sync failed." Log shows `post_id=0` or `fallback insert_id=0`.

**Cause:** The `wp_posts` table may have a corrupted or missing primary key / AUTO_INCREMENT on the `ID` column. This can occur during DB migration or restore. Without AUTO_INCREMENT, new rows get `ID = 0` and `$wpdb->insert_id` returns 0.

**Fix:** Run in phpMyAdmin (select the correct DB from wp-config.php first):

```sql
SHOW INDEX FROM wp_posts;
```

If `ID` does not show `PRIMARY` and `AUTO_INCREMENT`, run:

```sql
ALTER TABLE wp_posts MODIFY ID bigint(20) unsigned NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (ID);
```

(Replace `wp_posts` with your table prefix if different, e.g. `wp_xxx_posts`.)
