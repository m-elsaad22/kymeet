# KYMEET — Cursor Task: Convert the existing theme to the new mobile-app-style design

## 0. Context
- Site: kymeet-perfumes.com — WooCommerce perfume store, brand **KYMEET (Egypt I.E.A)**.
- Existing theme: `kayan-theme` (stylesheet `kymeet.css`, widgets named `kymeet_*`).
- Language: **Arabic RTL first**, English LTR supported. Currency: EGP.
- Attached references:
  1. Mockup A (dark navy + gold) and Mockup B (light blue) — use them for **layout, structure, spacing and component shapes only**.
  2. Logo files (gold lotus emblem + "KYMEET / Egypt I.E.A").
  3. Interactive prototype of the new design (open and inspect it): https://claude.ai/artifact/LMBYDu4HgD3r1f4iQJJSGy
- **Colors in the mockups are NOT final.** Use the palette in section 2. **No black or dark page backgrounds anywhere.**

## 1. Ground rules (read first)
1. Work in a **git branch** (`feature/kymeet-app-ui`) or a child theme. Do not touch the database, plugins, or WooCommerce core.
2. First **inspect** the theme: list templates, `functions.php`, `kymeet.css`, all `kymeet_*` widgets, Woo overrides (`woocommerce/`), and how header/footer/menus are built. Write a short report of what you found before editing.
3. **Keep all functionality** (cart, checkout, accounts, search, filters, widgets, Elementor/Customizer options if present). Restyle and restructure markup only where needed.
4. Use WooCommerce hooks and template overrides — never replace Woo logic.
5. No hard-coded colors, fonts or sizes in templates: everything through CSS variables in one tokens file.
6. Do not use real third-party brand logos (Dior, Chanel, Versace, etc.) seen in the mockups unless the site already has them with authorization. Use the store's real brands/categories from WooCommerce data.
7. Do not invent reviews, awards, discounts, shipping prices or contact info. Use existing store data or clearly marked placeholders.
8. Every change must pass: no horizontal scroll, RTL correct, visible focus states, touch targets ≥ 44px, no console errors.

## 2. Design tokens — Royal Metallic Blue on White
Create `assets/css/tokens.css` (enqueue first) with:

```css
:root{
  /* Backgrounds — always light */
  --bg:#FFFFFF;
  --bg-soft:#F4F8FF;          /* section tint */
  --surface:#FFFFFF;
  --border:#DCE6F7;

  /* Royal Metallic Blue (primary) */
  --royal-900:#061A5C;
  --royal-700:#0B2D8F;
  --royal-500:#1B4FD1;
  --royal-300:#5E86E6;
  --royal-metal:linear-gradient(135deg,#061A5C 0%,#0B2D8F 38%,#2F5FD9 52%,#0B2D8F 66%,#061A5C 100%);

  /* Pepsi Royal Metallic Blue (secondary / highlights) */
  --pepsi-700:#0056C7;
  --pepsi-500:#0A7BFF;
  --pepsi-300:#5CB0FF;
  --pepsi-metal:linear-gradient(135deg,#0049B0 0%,#0A7BFF 40%,#7CC2FF 52%,#0A7BFF 64%,#0049B0 100%);

  /* Gold accent (logo only + thin details) */
  --gold:#B79A62; --gold-light:#D4BD8A;
  --gold-metal:linear-gradient(135deg,#9A7F4B,#D4BD8A 50%,#9A7F4B);

  /* Text */
  --text:#0F1B3D; --muted:#5F6F8C;

  /* States */
  --success:#1E8A5A; --error:#C4423F; --warning:#C98A12;

  /* Shape & depth */
  --radius-sm:10px; --radius:16px; --radius-lg:24px; --radius-pill:999px;
  --shadow-1:0 4px 14px rgba(11,45,143,.08);
  --shadow-2:0 12px 32px rgba(11,45,143,.14);
  --shadow-btn:0 8px 20px rgba(10,123,255,.28);
}
```

Usage rules:
- **Primary buttons / key bars / active states:** `--royal-metal` (white text), with a subtle top highlight.
- **Secondary highlights (badges, links, progress, selected chips, cart count):** `--pepsi-metal` / `--pepsi-500`.
- **Gold:** logo, thin separators, small icons, "new" tags. Never large gold surfaces.
- Metallic gradients only on buttons, hero strips, headers of promo cards, bottom-nav active indicator. Body/cards stay flat white with soft shadows.
- Page background is **white** (`--bg`); alternate sections with `--bg-soft`. Dark backgrounds are not allowed; use deep royal blue only for the footer and promo banners.
- Check contrast (WCAG AA) for all text.

## 3. Typography
Load with `font-display:swap`, **self-host** (woff2) if possible, preload the main weights.
- **Arabic UI/body:** IBM Plex Sans Arabic (400/500/600)
- **Arabic headings:** Cairo (700/800)
- **Latin headings & brand wordmark:** Cormorant Garamond (600/700) — matches the serif logo
- **Latin UI/numbers:** Inter (400/500/600)
- Type scale (mobile → desktop): h1 28→44, h2 22→32, h3 18→22, body 15–16, small 13. Line-height 1.7 Arabic body / 1.25 headings. Avoid oversized Arabic headings.
- Use tabular numbers for prices.

## 4. Mobile-app experience (critical)
Target 360×800, 390×844, 430×932, then 768, 1024, 1440.
- **Top bar:** white, compact (56px): logo emblem + wordmark, search icon, cart icon with Pepsi-blue count badge. Sticky.
- **Bottom navigation (mobile only, fixed, white, safe-area aware):** Home · Categories · Search · Favorites · Account. Active item uses royal blue + small metallic indicator. Hide on ≥1024px.
- Bottom sheets (slide-up) for: filters, sort, quick view, mini-cart. Focus trap, `Esc` to close, scrim.
- Product cards: 2 columns on mobile, soft shadow, 4:5 image ratio, favorite heart, price in EGP, add-to-cart button (44px min).
- Sticky add-to-cart bar on single product (above bottom nav).
- Horizontal scroll chips for categories (no overflow of the page itself).
- Respect `prefers-reduced-motion`. Transitions 150–300ms.

## 5. Screens → WooCommerce mapping
| Screen | Target |
|---|---|
| Splash/Home | front-page template: hero (metallic royal strip + product image), latest perfumes, brands row, categories, gift sets, story, trust items, newsletter |
| All perfumes | `archive-product.php` / shop: chips, filter sheet, sort, grid, load more or pagination |
| Category | `taxonomy-product_cat`: banner, subcategories, grid, related categories |
| Brands | brand taxonomy (use the existing one or `product_brand` if present) |
| Search | dedicated search template + live suggestions (reuse existing search/AJAX if any) |
| Single perfume | `single-product.php`: swipe gallery, price, size variations, qty, add to cart, buy now, notes accordion, related, recently viewed |
| Cart | `cart/cart.php`: item cards, qty, remove, coupon, totals |
| Checkout | `form-checkout.php`: 3-step look (contact → shipping → payment), validation styling, order summary |
| Order received | `thankyou.php`: success graphic + order number + summary |
| Account | `myaccount/*`: dashboard, orders, order details (timeline), addresses, edit account, login/register/lost password |
| Wishlist | existing wishlist plugin if present; otherwise propose options before adding anything |
| Pages | About, Contact, FAQ, policies: shared "content page" template |
| 404 | branded 404 |

## 6. Components to restyle (single source of truth in `components.css`)
Buttons (primary royal-metal, secondary pepsi outline, text, disabled, loading, destructive) · inputs/selects/checkbox/radio with error & success states · product card · badges · chips · accordion · tabs · toast notifications (Woo notices restyled) · bottom sheet · quantity stepper · price block · breadcrumb · pagination · empty states · skeleton loaders.

## 7. Logo & imagery
- Use the supplied logo files; header uses the emblem + wordmark (small), footer and splash use the full logo. Provide SVG/WebP versions; do not stretch.
- Product images: consistent 4:5 aspect, `object-fit:contain` on light tinted background (`--bg-soft`), lazy-loaded, `srcset`. Provide a graceful fallback if an image fails.
- Egyptian motifs: subtle geometric patterns only (thin lines, low opacity), never heavy ornaments.

## 8. Performance & quality
- Enqueue properly (`wp_enqueue_style/script`), minimal JS (vanilla), no new heavy libraries.
- Hero image ≤ 150KB on mobile, WebP/AVIF, `fetchpriority="high"` for LCP image only.
- Accessibility: landmarks, labels, focus visible, aria for sheets/accordions, RTL arrow icons mirrored.
- Test: RTL + LTR, logged in/out, empty cart, variable product, out-of-stock, long Arabic names, slow network.

## 9. Work plan (do in this order, commit after each)
1. Audit report of the current theme (files, hooks, widgets, risks).
2. Tokens + fonts + reset/typography.
3. Header, bottom nav, footer, announcement bar.
4. Home (all sections) + `kymeet_*` widgets restyle.
5. Shop/category/search + filter sheet.
6. Single product (+ quick view).
7. Cart + checkout + order received.
8. Account pages + auth forms.
9. Content pages + 404.
10. QA pass (checklist below) and a short CHANGELOG.

## 10. Acceptance checklist
- [ ] White/light backgrounds everywhere; no black sections
- [ ] Royal metallic blue + Pepsi metallic blue used per section 2; gold only as accent
- [ ] All colors/fonts from variables, none hard-coded
- [ ] Arabic RTL perfect; English LTR works
- [ ] No horizontal scroll at 360px
- [ ] Bottom nav (5 items) on mobile; cart accessible in header
- [ ] Touch targets ≥ 44px; focus states visible
- [ ] Cart, checkout, login, search, filters all still work
- [ ] No fake claims, discounts or third-party logos
- [ ] Lighthouse mobile: Performance ≥ 80, Accessibility ≥ 90

**Start with step 1 (audit) and wait for my confirmation before editing files.**
