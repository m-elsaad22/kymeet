# إعادة تأسيس مشروع KYMEET App UI

**تاريخ:** 2026-10-18

## ما تم تجاهله

- فرع `cursor/kymeet-app-ui-9531` و PR السابق (حزمة `components/packs/kymeet-store/` مع `template_redirect`، `kymeet-egypt.css`، تعطيل KAYAN v3، إلخ).
- أي توثيق نشر مرتبط بالإصدارات 1.7.x على تلك الحزمة.

## Baseline الجديد

1. **`origin/main`:** `cursor-prompt-kymeet-app-theme.md` + 4 ملفات تصميم (رفع GitHub).
2. **القالب:** `kayan-theme/` كما يرفعه صاحب المتجر — يجب أن يحتوي على KYMEET (انظر §2 في البرومبت)، وليس build «خدمات منزلية» فقط.
3. **التنفيذ:** حسب §9 في البرومبت، مرحلة مرحلة، مع `components/styles/kymeet-app.css` و `components/packs/#footer/js/kymeet-app.js`.

## حالة المستودع على GitHub (main)

```
cursor-prompt-kymeet-app-theme.md
1792238022698.jpg          → mockup dark
1792319007104.jpg          → mockup light
IMG_…١٣٣١٠٧.jpg            → logo
IMG_…١٣٣١٤٦.png            → logo PNG
README.md
```

**`kayan-theme.zip` على `main` (commit `d0c1d4b`)** — تم فكّه محلياً إلى `kayan-theme/` على فرع `feature/kymeet-app-ui`.

### تحقق سريع من القabat المرفوع

| البند | الحالة |
|--------|--------|
| `components/styles/kymeet.css` (~85KB) | ✅ |
| Widgets `kymeet_*` في `YourColorWidgets/.../Standard/` | ✅ |
| `kymeet-store` (manifest، templates، `kymeet.js`) | ✅ — **بدون** استيلاء `template_redirect` / `kymeet-egypt` |
| `woocommerce/content-product.php` | ✅ |
| مصدر المنتجات | **WooCommerce `product`** إن WC مفعّل؛ وإلا CPT **`perfumes`** (`kayan-cpt`) |
| إصدار `style.css` | **1.6.0** (سجل `KYMEET-CHANGELOG.md` حتى 1.6.4) |

**Phase 1 (البرومبت):** ✅ `kymeet-app.css` + `kymeet-app.js` + `body.kymeet-app` (v1.6.5). Phase 2: شريط علوي/سفلي + «المزيد».

## النشر على kymeet-perfumes.com

- لا نلمس WooCommerce core/plugins.
- Checkout / Rank Math / `kayan-track` تبقى كما هي؛ التخصيص عبر overrides في `kayan-theme/woocommerce/` + CSS/JS الجديدة فقط.
