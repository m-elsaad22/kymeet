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

**`kayan-theme/` غير موجود على `main` حتى الآن** — يلزم رفع القالب من جهتك لنبدأ التعديل.

## النشر على kymeet-perfumes.com

- لا نلمس WooCommerce core/plugins.
- Checkout / Rank Math / `kayan-track` تبقى كما هي؛ التخصيص عبر overrides في `kayan-theme/woocommerce/` + CSS/JS الجديدة فقط.
