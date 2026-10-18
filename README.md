# KYMEET Perfumes — `kymeet`

متجر [kymeet-perfumes.com](https://kymeet-perfumes.com) — واجهة تطبيق WooCommerce داخل قالب **KAYAN** (`kayan-theme/`).

## مصدر الحقيقة (بعد إعادة التأسيس)

| المصدر | المحتوى |
|--------|---------|
| [`cursor-prompt-kymeet-app-theme.md`](cursor-prompt-kymeet-app-theme.md) | تعليمات التطوير الكاملة (9 مراحل) |
| [`docs/design/`](docs/design/) | Mockup داكن/فاتح + شعار KYMEET |
| **`kayan-theme/`** | **قالب كيميت الذي ترفعه أنت** — يجب أن يطابق هيكل البرومبت (`components/styles/kymeet.css`, وidgets `kymeet_*`, …) |

> **تنبيه:** فرع `cursor/kymeet-app-ui-9531` وحزمة `kymeet-store` السابقة (استيلاء v3 + بقايا خدمات) **لم تعد baseline**. لا نبني عليها.

## فرع العمل

```text
feature/kymeet-app-ui   ← من main (البرومبت + التصاميم)
```

## الخطوة التالية (مطلوبة منك)

1. ارفع مجلد **`kayan-theme/`** (أو `kayan-theme.zip`) إلى هذا المستودع على فرع `feature/kymeet-app-ui` — نسخة **كيميت** وليس قالب خدمات KAYAN فقط.
2. بعد ظهور القالب في GitHub نبدأ **Phase 1** من البرومبت: `kymeet-app.css` + `kymeet-app.js` + `body.kymeet-app`.
3. النشر على الاستضافة: رفع ZIP القالب من `dist/kayan-theme.zip` (يُبنى لاحقاً) + Purge LiteSpeed.

## مراجع التصميم

- داكن: `docs/design/mockup-dark-12screens.jpg`
- فاتح: `docs/design/mockup-light-12screens.jpg`
- الشعار: `docs/design/logo-kymeet-on-navy.jpg`, `logo-kymeet.png`
