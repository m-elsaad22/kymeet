# نشر واجهة KYMEET App UI (مراجعة ما قبل التفعيل)

## ملخص الإضافة

| البند | التفاصيل |
|--------|-----------|
| الاسم | `kymeet-perfumes-ui` (v1.0.1) |
| المسار في المستودع | `plugin/kymeet-perfumes-ui/` |
| الفرع | `cursor/kymeet-app-ui-9531` — [PR #1](https://github.com/m-elsaad22/kymeet/pull/1) |
| Staging | **غير متاح** حاليًا — التفعيل الأول على الإنتاج يتطلب خطة تراجع أدناه |

**تحديث (2026-10):** التصميم الكامل للمتجر يُنفَّذ الآن داخل **قالب KAYAN** (`components/packs/kymeet-store/`). راجع `docs/KYMEET-THEME-DEPLOY.md` وملف `dist/kayan-theme.zip`. الإضافة اختيارية بعد التحقق.

الإضافة **لا تكتب** إلى قاعدة البيانات (لا `wpdb`، لا تعديل منتجات/أسعار/مخزون). **لا تضيف** نقاط AJAX أو REST جديدة؛ السلة تستخدم نفس `/?wc-ajax=kymeet_cart` من قالب `kymeet-store`.

---

## التثبيت والتفعيل

1. **بناء الأرشيف محليًا** (اختياري إن لم تُرفَع نسخة جاهزة):

   ```bash
   bash scripts/build-kymeet-ui-plugin.sh
   # الناتج: dist/kymeet-perfumes-ui.zip
   ```

2. **رفع وتفعيل** في WordPress:
   - لوحة التحكم → **إضافات → أضف جديد → رفع إضافة** → `kymeet-perfumes-ui.zip` → **تفعيل**
   - أو استخدم نسخة مرفوعة سابقًا على الميديا (إن وُجدت):  
     `https://kymeet-perfumes.com/wp-content/uploads/2026/10/kymeet-perfumes-ui.zip`

3. **LiteSpeed Cache** (بعد التفعيل أو أي تحديث للإضافة):
   - **Purge All**
   - يُفضّل أيضًا مسح: **CSS/JS** و **Object Cache** إن كانت مفعّلة
   - الملفات التي يتغيّر كاشها عند التفعيل:
     - `wp-content/plugins/kymeet-perfumes-ui/assets/kymeet-app.css`
     - `wp-content/plugins/kymeet-perfumes-ui/assets/kymeet-core.js`
     - `wp-content/plugins/kymeet-perfumes-ui/assets/kymeet-app.js`
     - أي صفحة HTML تُخدَم عبر LSCache (الرئيسية، `/shop/`، صفحات المنتج)

4. **تحقق سريع**: افتح الموقع في نافذة خاصة وتأكد أن `body` يحمل الص class `kymeet-app-ui` وأن `kymeet.js` **لا** يُحمَّل من القالب (يُستبدل بـ `kymeet-core.js` من الإضافة).

---

## نتائج المراجعة الفنية (12 بندًا)

### 1. تعارض القالب / WooCommerce / Rank Math

| المكوّن | الحالة |
|---------|--------|
| `kymeet-core.js` | بديل لـ `kymeet.js`؛ يُزال تسجيل سكربت القالب بالاسم/المسار في `class-assets.php` |
| WooCommerce | لا تغيير على checkout/cart PHP؛ sticky bar على صفحة المنتج فقط |
| Rank Math | إن وُجد `RANK_MATH_VERSION` **لا** تُستبدل عناوين Rank Math؛ H1 مخفي (sr-only) على الرئيسية فقط عند غياب H1 ظاهر في القالب |
| CSS | طبقة `kymeet-app.css` فوق أنماط `kymeet-store` — قد تحتاج ضبط لاحق إذا تغيّر القالب 1.6.x |

### 2. السلة (parity مع `kymeet.js`)

منطق `wcCall` / `add` / `set` / `remove` / `renderWC` / `checkout` **مطابق** للملف الحي في القالب، مع تحسينات المفضلة و«مختارات كيميت».

**اختُبر على الإنتاج (بدون تفعيل الإضافة، عبر نفس AJAX):**

- إضافة منتج (`pid=89`) → نجاح
- `get` → عدد وعناصر صحيحة
- `set` (زيادة الكمية) → الإجمالي يتحدث
- `remove` → سلة فارغة

**يجب إعادة الاختبار يدويًا بعد التفعيل** من الواجهة: إضافة، +/−، حذف، فتح الدرج، زر Checkout.

**إصلاح v1.0.1:** القائمة السفلية أعادت **زر العربة** + `#anBadge` (كان مفقودًا في v1.0.0).

### 3. jQuery 3.4.1 المكرر

- **v1.0.0:** كان يُستدعى `wp_dequeue_script( 'yourcolor-script-js' )` — **handle خاطئ** (لا يُوقف شيئًا).
- **v1.0.1:** إزالة `yourcolor-script` بعد تحويل تبعيات `owl.carousel` / `setup.js` إلى `jquery` (WordPress core). `setup.js` يعتمد على jQuery — **لا تُعطّل** jQuery دون هذا التحويل.

### 4. قاعدة البيانات والمنتجات

لا تعديلات — مؤكد من مراجعة PHP/JS.

### 5. H1 و `document.title`

- H1 واحد sr-only على **الرئيسية فقط** (`kymeet-sr-h1`).
- `document_title_parts`: يُطبَّق فقط إذا **لم** يكن Rank Math نشطًا؛ و`pre_get_document_title` يملأ العنوان **فقط** إذا كان فارغًا (الموقع الحي غالبًا بلا `<title>` — يُفيد قبل ضبط Rank Math).

### 6. القائمة السفلية (360 / 390 / 430px)

- `safe-area-inset-bottom` + `padding-bottom` على `body`.
- **6 عناصر** (رئيسية، متجر، براندات، **عربة**، مفضلة، حساب) — خط أصغر و`ellipsis` للنصوط الطويلة.
- **لم يُختبر بصريًا** على المتصفح بعد التفعيل (الإضافة غير مفعّلة على الإنتاج وقت المراجعة).

على **≥1024px** القائمة السفلية مخفية؛ التنقل عبر هيدر القالب + روابط WC.

### 7. السلايدرات

المنطق من `kymeet-core.js` (سحب لمس، أسهم، `kslider`) دون تغيير جوهرى. CSS يحدّ `overflow-x` و`object-fit` للصور.

**لم يُختبر بصريًا/باللمس** بعد التفعيل.

### 8. المفضلة

- `localStorage` (`kymeet_wish_v1`)؛ إزالة تكرار المعرفات؛ ربط القلب بـ `openP('id')` على `.prod-img`.
- قسم `#kymeet-wish` يُحقَن على **الرئيسية** فقط؛ من صفحات أخرى الرابط → `/#kymeet-wish`.
- قائمة فارغة: رسالة «لا توجد عطور…» — **يُختبر بعد التفعيل**.

### 9. LiteSpeed

انظر «التثبيت» — Purge All + CSS/JS بعد كل تفعيل/تحديث.

### 10. الأمان

- لا REST/AJAX جديدة من الإضافة.
- Sticky bar: `esc_url` / `wp_kses_post` على HTML المنتج.
- المفضلة: جهاز المحفظ فقط (لا PII على السيرفر).

### 11. Staging وخطة تراجع

**Staging:** غير متاح.

**تراجع (≤ دقيقة):**

1. **إضافات → KYMEET Perfumes UI → إيقاف**
2. LiteSpeed **Purge All**
3. تحقق: عودة `kymeet.js` من القالب، اختفاء `kymeet-app-ui` من `body`

لا حاجة لحذف ملفات أو استعادة DB.

### 12. ما لم يُختبر فعليًا

- واجهة الموبايل 360/390/430 بعد تفعيل الإضافة
- سحب السلايدرات والهيرو «السينمائي» على جهاز حقيقي
- تفعيل الإضافة على السيرفر (يتطلب wp-admin)

---

## قائمة اختبار مطلوبة (بعد التفعيل)

- [ ] الرئيسية: هيرو، سلايدرات، أقسام المنتجات، `#brands`
- [ ] متجر WC + صفحة منتج: بطاقات، sticky buy (موبايل)، إضافة للسلة
- [ ] سلة: إضافة، كمية، حذف، checkout URL
- [ ] قائمة سفلية: 360 / 390 / 430 — لا تغطية FAB أو أزرار checkout
- [ ] مفضلة: قلب، إعادة تحميل، قائمة فارغة
- [ ] Desktop ≥1024: لا شريط سفلي؛ هيدر + سلة header
- [ ] Rank Math: عناوان الصفحات وSchema دون H1 مكرر
- [ ] قوائم/owl من القالب (إن وُجدت خارج kymeet-store) بعد إزالة jQuery المكرر

---

## تعطيل الإضافة والتراجع

1. إيقاف الإضافة من لوحة التحكم.
2. Purge LiteSpeed.
3. (اختياري) حذف مجلد `wp-content/plugins/kymeet-perfumes-ui/`.

لا migrations ولا options تُترك في DB.

---

## وظائف / تصميم **غير مكتمل**

- **تخطيط HTML جديد 100%** (مثل مرجع التطبيق): يتطلب تعديل ح pack `kymeet-store` أو Child Theme — الإضافة طبقة CSS/JS فقط.
- **قوالب WooCommerce** في الإضافة: hook `locate_template` موجود **بدون** ملفات `templates/woocommerce/` بعد — صفحات WC تعتمد على القالب.
- **توحيد بصري كامل** (هيرو سينمائي، صور العطور، WC archive vs الرئيسية): تحسينات v1.0.1 جزئية؛ يحتاج جولة QA بصرية بعد التفعيل.
- **Staging** و CI بصري: غير متوفر.
- **شحن/SEO/robots** من تدقيق الموقع: خارج نطاق هذه الإضافة.

---

## بناء محلي

```bash
bash scripts/build-kymeet-ui-plugin.sh
```

---

## مراجع

- JS مرجعي حي: `.../kymeet-store/assets/kymeet.js`
- PR: https://github.com/m-elsaad22/kymeet/pull/1
