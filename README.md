# KYMEET Perfumes — `kymeet`

متجر [kymeet-perfumes.com](https://kymeet-perfumes.com) — واجهة تطبيق WooCommerce داخل قالب **KAYAN** (`kayan-theme/`).

## مصدر الحقيقة (بعد إعادة التأسيس)

| المصدر | المحتوى |
|--------|---------|
| [`docs/KYMEET-CURSOR-PROMPT-v2.md`](docs/KYMEET-CURSOR-PROMPT-v2.md) | **البرومبت المعتمد** — Royal Blue على أبيض (بدون خلفيات داكنة) |
| [`docs/KYMEET-THEME-AUDIT.md`](docs/KYMEET-THEME-AUDIT.md) | تقرير Step 1 (تدقيق القالب) |
| [`cursor-prompt-kymeet-app-theme.md`](cursor-prompt-kymeet-app-theme.md) | نسخة قديمة (dark/light) — للمرجع فقط |
| [`docs/design/`](docs/design/) | Mockup داكن/فاتح + شعار KYMEET |
| **`kayan-theme/`** + **`kayan-theme.zip`** | قالب كيميت **1.6.x** (مرفوع على GitHub) |

> **تنبيه:** فرع `cursor/kymeet-app-ui-9531` (تعديلات Agent السابقة) **لم يعد baseline**.

## فرع العمل

```text
feature/kymeet-app-ui   ← من main (البرومبت + التصاميم)
```

## الخطوة التالية

1. **Phase 1** (البرومبت): `components/styles/kymeet-app.css` + `#footer/js/kymeet-app.js` + `body.kymeet-app`.
2. النشر: رفع `kayan-theme.zip` (أو ZIP من `dist/` بعد البناء) + Purge LiteSpeed على kymeet-perfumes.com.

## مراجع التصميم

- داكن: `docs/design/mockup-dark-12screens.jpg`
- فاتح: `docs/design/mockup-light-12screens.jpg`
- الشعار: `docs/design/logo-kymeet-on-navy.jpg`, `logo-kymeet.png`
