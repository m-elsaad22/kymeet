# KYMEET — Step 1: Theme audit (baseline v1.6.5)

Per **KYMEET-CURSOR-PROMPT-v2.md** (Royal Metallic Blue on **white** — no dark page backgrounds).

## 1. Bootstrap & packs

| Area | Finding |
|------|---------|
| Entry | `functions.php` → glob-loads `components/packs/*/setup.php` |
| KYMEET pack | `components/packs/kymeet-store/` — manifest HTML templates, `products.php`, `woo.php`, `admin.php`, `assets/kymeet.js` |
| Toggle | `get_option('kymeet_store_mode') !== 'off'` → `kymeet_store_active()` |
| Header/footer (store) | `#header/part.php` → `kymeet_store_tpl('header')` + early return; `#footer/part.php` → footer template + `kymeet_store_appnav()` + scripts |
| Enqueues | `components/packs/Enqueues/setup.php` — aggressive `disable_all_scripts` (999999); **Phase 1** added protected `kymeet-app` handles |

## 2. Styles & scripts

| File | Role |
|------|------|
| `components/styles/kymeet.css` | ~85KB brand UI (inlined in `<style>` on most visits, or link when `open_css` IP) |
| `components/styles/kymeet-app.css` | **Phase 1 (old brief):** dark/light `data-theme` — **conflicts with v2 prompt** (white-only) |
| `#footer/js/kymeet-app.js` | Theme toggle + `theme-color` — to be **replaced** by v2 tokens (no dark mode) |
| `#footer/js/setup.js` + jQuery 3.4.1 | Legacy KAYAN carousel/lazy (dequeued on front in places) |
| `kymeet-store/assets/kymeet.js` | Catalog grid, cart AJAX (`wc-ajax=kymeet_cart`), wishlist hearts (`wish()`), drawer |

## 3. Widgets (`kymeet_*`)

Registered under `YourColorWidgets/.../Standard/` — each calls `kymeet_store_tpl()` + fields from `manifest.json`:

`kymeet_hero`, `kymeet_new`, `kymeet_brands`, `kymeet_catalog`, `kymeet_bestsellers`, `kymeet_discover`, `kymeet_curated`, `kymeet_story`, `kymeet_faq`, `kymeet_cta`.

Home layout = `widgets_home__meta` in theme options (FieldsMachine), not a single `front-page.php`.

## 4. WooCommerce

| Item | Status |
|------|--------|
| `add_theme_support('woocommerce')` | In `kymeet-store/woo.php` (+ gallery features) |
| Overrides | `woocommerce/content-product.php`, `woocommerce/myaccount/form-login.php` only |
| Missing vs v2 plan | `archive-product.php`, `single-product.php`, cart/checkout/thankyou templates |
| Products | **`product`** when WC active; fallback CPT **`perfumes`** + taxonomies via `kayan-cpt` |
| Cart | Custom AJAX `kymeet_cart` in `woo.php` (not WC fragments-only) |
| Checkout | Classic Woo form (no theme checkout override yet) |

## 5. Header / footer / nav (current)

- **Top:** `kymeet-store/templates/header.html` (manifest-driven), search as icon, cart in header.
- **Bottom nav (mobile):** 4 links + cart button — **Home, Brands, Store, Account** + cart — **not** v2 spec (Home · Categories · Search · Favorites · Account; cart in header only).
- **Wishlist:** client-side in `kymeet.js` (no plugin); no dedicated favorites route/sheet yet.
- **i18n:** `kayan-i18n` — RTL/LTR via `kayan_i18n_get_html_attrs()`.

## 6. Risks for redesign

1. **`kymeet.css` inlined** — large critical CSS; new `tokens.css` / `components.css` must enqueue without breaking LSCache / `open_css` path.
2. **`disable_all_scripts`** — any new handles must stay on protected list (`kymeet-app`, etc.).
3. **Hard-coded URLs** in templates (e.g. `kymeet-perfumes.com/shop/`) — replace with `wc_get_page_permalink` when touching files.
4. **Phase 1 dark theme** — remove/replace before v2 visual work to avoid contradicting “no dark backgrounds”.
5. **Brand logos in manifest/seed** — audit live data; v2 forbids unauthorized third-party marks in markup.
6. **No Elementor** in tree; customization = theme options + widgets list.

## 7. Mapping to v2 work plan

| Step | Action |
|------|--------|
| 2 | Add `assets/css/tokens.css` (+ fonts per v2), reset typography; **retire** dark `data-theme` layer |
| 3 | Rebuild header (56px white), **5-item bottom nav**, footer (deep royal allowed) |
| 4–9 | Home widgets → shop → single → cart/checkout → account → pages/404 |
| 10 | QA checklist in v2 prompt |

## 8. Prototype reference

Interactive: https://claude.ai/artifact/LMBYDu4HgD3r1f4iQJJSGy (layout/spacing only; colors = section 2 tokens).

---

**Awaiting your confirmation** before file edits (per v2 prompt §9).
