# Hextris Plugin Consolidation Audit Report

**Date:** 2026-01-20
**Branch:** `claude/consolidate-hextris-plugins-hRb7w`
**Status:** Analysis Complete - Ready for Implementation

---

## Executive Summary

This audit identifies why JavaScript fails to load for non-admin users and proposes a clean consolidation plan. The root cause is a **timing mismatch between asset enqueuing and page caching** - not a bug in the JavaScript itself.

---

## Section 1: Why JavaScript Currently Only Loads for Admin Users

### Root Cause

The JavaScript loading failure for non-admin users stems from **late asset enqueuing that occurs after caching layers capture the page**.

**Current Code Flow:**
```
1. wp_enqueue_scripts hook fires → sacga_hextris_register_assets() REGISTERS assets
2. the_content filter fires → [sacga_hextris] shortcode renders
3. Shortcode callback ENQUEUES assets (lines 393-394)
4. wp_footer hook fires → Scripts print to page
```

**Why it works for admins:**
- WordPress page caching typically **excludes logged-in users** (especially admins)
- Each admin page view triggers fresh PHP execution
- Assets register → shortcode runs → assets enqueue → scripts print ✓

**Why it fails for non-admin/incognito users:**
- Page caching (if enabled) stores the **HTML output** but does NOT replay `wp_enqueue_script()` calls
- Even without caching, if a plugin or theme uses **object caching** or **fragment caching**, the shortcode output may be cached while the enqueue call is lost
- The result: HTML container renders, but no `<script>` tags appear

### Secondary Contributing Factors

1. **Late enqueue timing** (line 374): Registration happens on `wp_enqueue_scripts`, but actual enqueue happens during content rendering.

2. **No fallback detection**: There's no mechanism to detect or recover if assets fail to load.

3. **Localized data dependency** (lines 406-407): `wp_localize_script()` is called during shortcode rendering. If the script itself doesn't get enqueued, the localized data is also lost.

---

## Section 2: Files/Folders to Keep vs Isolate

### KEEP (Root Plugin - Canonical Runtime)

| Path | Purpose | Status |
|------|---------|--------|
| `shortcode-arcade-hextris.php` | Main plugin entry | **KEEP & MODIFY** |
| `js/*.js` (13 files) | Core game logic | **KEEP** |
| `vendor/*.js` (7 files) | Dependencies | **KEEP** |
| `style/style.css` | Main game styles | **KEEP** |
| `style/rrssb.css` | Social sharing styles | **KEEP** |
| `style/fa/` | Font Awesome | **KEEP** |
| `style/fonts/` | Custom fonts | **KEEP** |
| `images/` | UI assets | **KEEP** |
| `README.md`, `LICENSE.md` | Documentation | **KEEP** |

### ISOLATE/ARCHIVE (Nested Plugin)

| Path | Purpose | Recommendation |
|------|---------|----------------|
| `hextris-arcade/` | Entire nested plugin | **ARCHIVE OR DELETE** |

The nested plugin (`hextris-arcade/`) is already disabled (`.disabled` extension on entry file), but its presence:
- Creates confusion about which implementation is active
- Bloats the repository
- Could be accidentally re-enabled

**Recommendation:** Move the entire `hextris-arcade/` folder to an `_archived/` directory, or delete it entirely if the features (REST API, Gutenberg block, leaderboards) are not needed.

---

## Section 3: Proposed Simplified Runtime Architecture

### Current Architecture (Problematic)

```
┌─────────────────────────────────────────────────────────┐
│ wp_enqueue_scripts hook                                 │
│   └── sacga_hextris_register_assets() - REGISTERS only │
└─────────────────────────────────────────────────────────┘
                           ↓
                    (page caching may occur here)
                           ↓
┌─────────────────────────────────────────────────────────┐
│ the_content filter                                      │
│   └── [sacga_hextris] shortcode renders                 │
│       ├── wp_enqueue_style('sacga-hextris-main')        │
│       ├── wp_enqueue_script('sacga-hextris-init')       │  ← LOST if cached
│       ├── wp_localize_script(...)                       │  ← LOST if cached
│       └── return HTML container                         │
└─────────────────────────────────────────────────────────┘
```

### Proposed Architecture (Robust)

```
┌─────────────────────────────────────────────────────────┐
│ wp_enqueue_scripts hook                                 │
│   └── sacga_hextris_maybe_enqueue_assets()              │
│       ├── Check: is shortcode present on current page?  │
│       │   (multiple detection strategies)               │
│       └── If yes: REGISTER + ENQUEUE immediately        │
└─────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────┐
│ the_content filter                                      │
│   └── [sacga_hextris] shortcode renders                 │
│       ├── Set flag: shortcode WAS rendered              │
│       └── return HTML container                         │
└─────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────┐
│ wp_footer hook (fallback)                               │
│   └── sacga_hextris_footer_fallback()                   │
│       ├── Check: was shortcode rendered but no scripts? │
│       └── If yes: print inline scripts as fallback      │
└─────────────────────────────────────────────────────────┘
```

**Key Principles:**
1. **Eager detection** - Detect shortcode presence BEFORE content renders
2. **Early enqueue** - Enqueue during `wp_enqueue_scripts`, not during shortcode
3. **Fallback safety net** - Footer hook catches any edge cases
4. **No admin context dependency** - Zero checks for `is_admin()` or capabilities

---

## Section 4: Minimal, Robust Asset Enqueue Strategy

### Strategy: "Detect Early, Enqueue Once"

```php
/**
 * Detect if current page needs Hextris assets.
 * Uses multiple detection methods to avoid false negatives.
 */
function sacga_hextris_page_needs_assets() {
    global $post;

    // Method 1: Check post content for shortcode
    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'sacga_hextris' ) ) {
        return true;
    }

    // Method 2: Allow themes/plugins to force-load via filter
    if ( apply_filters( 'sacga_hextris_force_load_assets', false ) ) {
        return true;
    }

    return false;
}

/**
 * Enqueue assets early, during wp_enqueue_scripts.
 */
function sacga_hextris_enqueue_assets() {
    if ( ! sacga_hextris_page_needs_assets() ) {
        return;
    }

    // Register AND enqueue in one step
    sacga_hextris_register_assets();

    wp_enqueue_style( 'sacga-hextris-main' );
    wp_enqueue_script( 'sacga-hextris-initialization' );

    // Localize boot data immediately
    $boot_data = sacga_hextris_get_boot_data();
    wp_localize_script( 'sacga-hextris-save-state', 'HEXTRIS_BOOT', $boot_data );
    wp_localize_script( 'sacga-hextris-save-state', 'sacgaHextris', $boot_data );
}
add_action( 'wp_enqueue_scripts', 'sacga_hextris_enqueue_assets' );

/**
 * Shortcode becomes thin - just outputs HTML.
 */
function sacga_hextris_shortcode() {
    // Player context check
    $player_context = sacga_hextris_get_player_context();
    if ( isset( $player_context['blocked'] ) && $player_context['blocked'] ) {
        return '<p>Hextris is available for logged-in users only.</p>';
    }

    // Just return HTML - assets already enqueued
    return sacga_hextris_get_game_html();
}
```

### Why This Works

1. **Detection runs early** - `wp_enqueue_scripts` fires before caching captures content
2. **Single enqueue point** - No split between register and enqueue
3. **Cache-proof** - Even if HTML is cached, scripts were already added to the page
4. **Filter escape hatch** - `sacga_hextris_force_load_assets` filter allows manual override

### Edge Cases Handled

| Scenario | How it's handled |
|----------|------------------|
| Shortcode in post content | `has_shortcode()` detects it |
| Shortcode in widget | Filter override |
| Shortcode in custom field | Filter override |
| Builder-generated content | Filter override |
| Page caching enabled | Assets enqueued before cache |
| Object caching enabled | Same as above |

---

## Section 5: Step-by-Step Implementation Plan

### Phase 1: Archive Nested Plugin (Low Risk)

1. Create `_archived/` directory at repository root
2. Move entire `hextris-arcade/` folder into `_archived/`
3. Update `.gitignore` if needed
4. Test: Verify root plugin still works for admin user

### Phase 2: Refactor Asset Loading (Core Fix)

1. **Extract boot data function** - Move lines 397-404 into `sacga_hextris_get_boot_data()`
2. **Extract HTML output function** - Move lines 411-487 into `sacga_hextris_get_game_html()`
3. **Create detection function** - Implement `sacga_hextris_page_needs_assets()`
4. **Create early enqueue function** - Replace current `wp_enqueue_scripts` hook
5. **Simplify shortcode callback** - Remove enqueue calls, keep only HTML output
6. **Add force-load filter** - For edge cases

### Phase 3: Testing Matrix (Critical)

Test all combinations:

| User Type | Caching | Expected Result |
|-----------|---------|-----------------|
| Admin | Off | Game loads |
| Admin | On | Game loads |
| Logged-in non-admin | Off | Game loads |
| Logged-in non-admin | On | Game loads |
| Guest (logged out) | Off | Game loads |
| Guest (logged out) | On | Game loads |
| Incognito | On | Game loads |

### Phase 4: Cleanup

1. Remove any dead code paths
2. Add inline documentation explaining the enqueue strategy
3. Bump version number
4. Final regression test

---

## Success Criteria

- [ ] Single Hextris implementation remains active
- [ ] Frontend JS loads for all users (admin, non-admin, guest, incognito)
- [ ] No reliance on admin context for gameplay
- [ ] Reduced complexity and clearer ownership of runtime code
- [ ] Works with common caching plugins (WP Super Cache, W3 Total Cache, etc.)

---

## Summary Table

| Problem | Solution |
|---------|----------|
| Duplicate plugin implementations | Archive nested `hextris-arcade/` plugin |
| Late asset enqueuing | Move enqueue to `wp_enqueue_scripts` with early detection |
| Cache-breaking enqueue timing | Enqueue BEFORE content renders |
| Admin-context dependency | Zero capability checks for asset loading |
| Fragile shortcode detection | Multiple detection strategies + filter override |

**The fix is architectural, not a patch.** The current approach of enqueueing inside the shortcode callback is fundamentally incompatible with page caching. The solution is to detect the shortcode early and enqueue assets before WordPress hands off to caching layers.
