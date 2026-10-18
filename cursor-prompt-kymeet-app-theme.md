# Cursor Prompt — KYMEET App-Style WooCommerce Theme

> Attach with this prompt: the theme folder (`kayan-theme`), the dark mockup, the light mockup, and the logo.
> Use Cursor **Agent mode**. Work on a git branch or staging copy, never production.

---

## 1. Role and goal

You are a senior WordPress / WooCommerce front-end engineer. Redesign the theme `kayan-theme` for the live store **https://kymeet-perfumes.com** (KYMEET Perfumes, Egypt I.E.A) so it looks and behaves like a **native mobile shopping app**, exactly in the spirit of the attached mockups (dark navy + gold, and light royal-blue variant). Arabic RTL first. Currency EGP ("ج.م"). The store runs on **WooCommerce**.

This is a production e-commerce site. Do not break checkout, payments, SEO, tracking, or i18n.

## 2. Existing theme structure (read before editing)

- CSS: `components/styles/kymeet.css` (brand, ~85KB), `main.css`, `main2.css`, `responsive.css`, `rukn-v3.css`, `singular/single.css`
- Widgets: `components/packs/YourColorWidgets/model-widgets/Standard/kymeet_{hero,new,brands,catalog,bestsellers,discover,curated,story,cta,faq}.php`
- Templates: `components/packs/#header/part.php`, `#footer/part.php`, `@single/perfumes.php`, `@single/product.php`, `@index/shape.php`, `@page/shape.php`, `@search/shape.php`
- JS: `components/packs/#footer/js/setup.js` (jQuery, owl.carousel, wow)
- Enqueues: `components/packs/Enqueues/setup.php`
- Other packs to leave alone: `kayan-i18n`, `kayan-track`, `kayan-security`, `schema`, `theme-seo`, `export-import`, `RuknContact`, `currency_edits`, `Numberformat`

**Step 0 (mandatory, before any edit):** audit and report:
1. Is WooCommerce already integrated in the theme (`add_theme_support('woocommerce')`, `woocommerce/` override folder, WC hooks)? 
2. Are products WooCommerce `product` posts, or the custom `perfumes` CPT (`@single/perfumes.php`)? If both exist, tell me which one is the source of truth and how they relate. Do not create a second product/cart system.
3. Which checkout is active: classic shortcode `[woocommerce_checkout]` or the block checkout? Which payment gateways and shipping methods are enabled (read from code/config only)?
4. Is there any wishlist, quick-view or side-cart plugin or code?
Then output a short plan listing every file you will create or modify. Wait for no confirmation; continue after the plan.

## 3. Hard rules

- Never edit WooCommerce core or plugin files. Customize only through the theme: `add_theme_support('woocommerce')`, a `woocommerce/` template-override folder in the theme, hooks/filters, and CSS/JS.
- Keep each overridden template compatible with the installed WooCommerce version (copy the current version's template, keep its `@version` header, change markup minimally, keep all `do_action`/`apply_filters` hooks so payment gateways and plugins keep working).
- Keep all existing PHP logic, CPTs, shortcodes, i18n, tracking (`kayan-track`), schema and SEO output untouched. Do not change URLs, slugs, permalinks.
- Escape everything: `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`; verify nonces on any new AJAX endpoint; sanitize all input.
- No new heavy dependencies. Prefer vanilla JS. jQuery only where WooCommerce already requires it.
- New styles go in a **new file** `components/styles/kymeet-app.css`, enqueued after `kymeet.css` in `Enqueues/setup.php`. New JS in `components/packs/#footer/js/kymeet-app.js`. Do not rewrite `kymeet.css`; override with scoped selectors under a `body.kymeet-app` class added via `body_class`.
- Use CSS logical properties (`margin-inline`, `padding-inline`, `inset-inline`) so RTL/LTR both work (the theme has an i18n switcher).
- Respect `prefers-reduced-motion`. Keep AA contrast in both themes.

## 4. Design system (put tokens in `:root` of `kymeet-app.css`)

**Dark (default)**
- Background gradient `#050E24 → #0A1A3D`
- Card `#0F2350` with `1px solid rgba(201,162,39,.25)`, inner glow
- Accent gold `#C9A227`, light gold `#E5C76B`, gold glow `rgba(201,162,39,.35)`
- Text `#FFFFFF`, secondary `#B8C2DC`

**Light**
- Background `#EEF4FF → #FFFFFF`, cards white with soft blue shadow
- Primary gradient `#0B3A85 → #1A6BD8`, text `#0A1230`, muted `#6B7794`

**Shared**
- Radius: cards 20px, buttons pill 999px, inputs 14px, bottom sheet 28px top
- Theme toggle via `html[data-theme="dark|light"]`, saved in `localStorage`, default dark, plus `<meta name="theme-color">` updated on toggle
- Typography: keep the theme's Arabic font (Alexandria / Montserrat Arabic, already bundled locally). Use a serif for the KYMEET wordmark and Latin product names (local font file or safe fallback `Cinzel, "Playfair Display", Georgia, serif`; do not load from external CDNs).
- Spacing scale 4/8/12/16/24/32. Touch targets at least 44px.
- Icons: use the bundled FontAwesome or inline SVG; no new icon packs.

## 5. App shell (mobile ≤ 768px)

1. **Splash screen**: full-screen, logo centered, pyramids + Bastet background, gold pill CTA "تسوق الآن". Shown once per session via `sessionStorage`, injected by JS (not blocking first paint, not hiding content from crawlers), auto-dismiss after ~2.5s or on tap, skippable. Skip it entirely for bots and for returning to inner pages.
2. **Top app bar** (sticky, blurred background): centered logo, search icon (opens full-screen search overlay using the existing search + WooCommerce product search), cart icon with live count badge.
3. **Bottom tab bar** (fixed, 4 tabs): الرئيسية، المتجر، العلامات التجارية، المزيد (and an account/cart entry as appropriate). Active tab highlighted with indicator; `padding-bottom: env(safe-area-inset-bottom)`; add `body` bottom padding so content is never covered; hide while keyboard is open on checkout. "المزيد" opens a bottom sheet with account, orders, language switcher, theme toggle, contact, policies.
4. Hide the desktop mega-menu and heavy footer on mobile; keep them on desktop (>768px), where the design becomes a refined, wider version of the same visual language (grid layouts, no bottom bar).
5. Add `viewport-fit=cover` to the viewport meta, `theme-color`, and `apple-touch-icon` from the KYMEET logo.

## 6. Screens to build (map each to WooCommerce)

**Home** (existing `kymeet_*` widgets, restyled):
- `kymeet_hero`: rounded banner slider with bottle image, title, gold CTA, pagination dots (CSS scroll-snap)
- `kymeet_new`: "أحدث العطور" horizontal snap row + "عرض الكل" link to shop
- `kymeet_brands`: square brand cards (Dior, Chanel, Versace…) as a snap row, links to the brand/attribute archive
- `kymeet_catalog`: "تصنيفات العطور" image cards (رجالي، نسائي، للجنسين) linking to `product_cat` archives
- `kymeet_bestsellers`, `kymeet_discover`, `kymeet_curated`, `kymeet_story`, `kymeet_cta`, `kymeet_faq`: restyle as app sections (consistent section header + "عرض الكل")

**Product card** (override `woocommerce/content-product.php`): image area with dark gradient, wishlist heart on the corner, name, subtitle (brand/size), price in "ج.م" (sale price with strikethrough), tap area covers the whole card, optional quick add-to-cart button. Keep `woocommerce_before/after_shop_loop_item*` hooks.

**Shop / archive** (`archive-product.php`, `taxonomy-product_cat`, brand archives): back arrow + title, rounded search field, horizontally scrollable filter chips (الكل، الرجال، النساء، المميزة + categories), 2-column grid, sort and filter as a **bottom sheet** (price, brand, size, gender attributes) using WooCommerce's own query vars so it stays server-side and indexable. Infinite scroll or "عرض المزيد" via AJAX with graceful fallback to normal pagination.

**Single product** (`single-product.php` + `content-single-product.php`, and `@single/perfumes.php` if the CPT is used): large bottle image with back and heart buttons, swipeable gallery with thumbnail strip, name, price, short description, 3-icon info row (type / size / notes) from product attributes or ACF fields that already exist, variation selectors styled as pills (size ml), qty stepper, **sticky bottom action bar** with "أضف إلى السلة" and "تسوق الآن" (add to cart then redirect to checkout). Keep Product schema, Open Graph, breadcrumbs.

**Cart** (`cart/cart.php`, `cart-totals.php`, `mini-cart.php`): line items with image, name, price, qty stepper (− 1 +) with AJAX update, trash icon, coupon field collapsed, sticky total bar with checkout button. Also a **side/mini-cart bottom sheet** opened from the cart icon and after add-to-cart (use WooCommerce fragments `woocommerce_add_to_cart_fragments`, no custom cart storage).

**Checkout** (`checkout/form-checkout.php`, `form-billing.php`, `form-shipping.php`, `review-order.php`, `payment.php`): present the standard WooCommerce form as a **3-step flow** with a progress indicator (العنوان → الشحن → الدفع). It must remain one real `<form class="checkout">` posting to WooCommerce so every gateway, validation and the `update_checkout` AJAX keep working; steps are only show/hide of field groups with per-step client validation. Rounded inputs, floating labels, Egyptian governorate select, phone field with Egypt default, shipping methods and payment methods as selectable radio cards (e.g. delivery to home / pickup, cash on delivery, card). Sticky order summary bar. Keep terms checkbox and `woocommerce_checkout_*` hooks.

**Thank-you page** (`checkout/thankyou.php`): logo, "تم استلام طلبك بنجاح", order-number pill (`#order_number`), "متابعة الطلب" button to the order view, gift box image. Keep the `woocommerce_thankyou` action (tracking / pixels depend on it).

**My account** (`myaccount/*`): app-style list menu (orders, addresses, details, logout) as stacked cards; orders as cards with status chips.

**Wishlist**: if a wishlist plugin exists, restyle it. If none, implement a lightweight one: heart toggle storing product IDs in `localStorage` for guests and in user meta for logged-in users via a nonce-protected AJAX endpoint, plus a wishlist page/sheet. Do not add a plugin unless I ask.

**Brands + categories pages**: styled as the mockups (brand cards grid, category cards with image and chevron).

**Empty/error states**: empty cart, no results, 404, out-of-stock badge, offline message. All styled to match.

## 7. Interaction and performance

- Page transitions (fade/slide ≤ 200ms), press feedback `scale(.97)`, ripple on primary buttons, skeleton loaders while images load, `loading="lazy"` plus width/height to avoid layout shift, WebP images where available.
- Bottom sheets with focus trap, ESC/backdrop to close, swipe-down to dismiss.
- Haptic-like micro animation on add-to-cart (cart badge bounce).
- Lighthouse mobile: Performance ≥ 85, Accessibility ≥ 90, CLS < 0.1. Dequeue unused CSS/JS on the new pages where safe; do not break other plugins.
- Make everything keyboard accessible, with proper ARIA roles for tab bar, dialogs and steps.

## 8. SEO and tracking safeguards

- Keep all `<title>`, meta, canonical, hreflang, schema (Product, Offer, Breadcrumb, LocalBusiness) output unchanged or improved.
- Do not hide primary content behind JS; product grids, prices and descriptions must be in server-rendered HTML.
- Keep `kayan-track` events and any Meta/Google pixels firing on add-to-cart, checkout and thank-you.

## 9. Process

1. Run the Step 0 audit and output the plan and file list.
2. Implement in this order and **stop after each phase with a summary and a test checklist**:
   1. Design tokens, `body.kymeet-app`, theme toggle, `kymeet-app.css/js` enqueue
   2. App bar, bottom tab bar, "المزيد" sheet, search overlay
   3. Home widgets
   4. Shop archive, product card, filters
   5. Single product + sticky action bar
   6. Cart + mini-cart sheet
   7. Checkout 3-step + thank-you
   8. My account, wishlist, empty/error states
   9. Splash screen, transitions, performance pass
3. At the end, produce a QA checklist: test at 360, 390, 768, 1280px; RTL and LTR; dark and light; guest and logged-in; add to cart, update qty, coupon, each payment method, COD order, failed payment, out-of-stock, variable product.

## 10. Acceptance criteria

- Visually matches the mockups on mobile in both themes.
- A full order can be placed end-to-end with every enabled payment method.
- No PHP warnings/notices with `WP_DEBUG` on; no console errors.
- WooCommerce Status page shows no outdated-template warnings for overridden files.
- No change in URLs, schema, or tracking events.
