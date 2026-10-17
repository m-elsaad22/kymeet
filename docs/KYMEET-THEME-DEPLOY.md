# KYMEET — تثبيت قالب KAYAN المعدّل (kymeet-store)

## ما الذي تغيّر؟

- التصميم والتفاعلات أصبحت داخل **حزمة القالب** `components/packs/kymeet-store/` (ملفات PHP + `assets/kymeet.css` + `assets/kymeet-luxury.css` + `kymeet.js` / `kymeet-ui.js`).
- **لا تعتمد** على إضافة `kymeet-perfumes-ui` لتحميل CSS/JS (الإضافة على الإنتاج كانت ناقصة — ملفات `assets` تُرجع 404).

## التثبيت

1. من لوحة WordPress: **المظهر → قوالب → أضف جديد → رفع قالب** واختر `dist/kayan-theme.zip`.
2. فعّل القالب **KAYAN Theme** (الإصدار 1.6.0 في هذا المستودع).
3. **LiteSpeed Cache → Purge All** (أو مسح كاش CDN إن وُجد).
4. تأكد أن WooCommerce مفعّل وأن الصفحة الرئيسية ما زالت تستخدم ويدجتات `kymeet_*` (إعدادات KAYAN Homepage).

## تعطيل إضافة kymeet-perfumes-ui بأمان

بعد التحقق من:

- ظهور `kymeet.css` / `kymeet-luxury.css` في مصدر الصفحة (Network).
- عمل `/?wc-ajax=kymeet_cart` (إضافة/عرض السلة).
- شريط التنقل السفلي (6 عناصر) والمفضلة (localStorage).

يمكن **تعطيل** الإضافة من **الإضافات** دون حذفها. لا حاجة لها لتنفيذ التصميم بعد هذا الإصدار.

## التراجع

1. أعد تثبيت نسخة القالب الاحتياطية (ZIP الأصلي قبل التعديل).
2. فعّل القالب القديم وامسح كاش LiteSpeed.
3. أعد تفعيل الإضافة فقط إذا كنت تعتمد على سلوك قديم غير موجود في القالب (غير متوقع بعد هذا الإصدار).

## الاختبارات الموصى بها (يدوي على الإنتاج)

| المقياس | تحقق |
|---------|------|
| 360 / 390 / 430px | الهيرو، السلايدرات، الشريط السفلي، عدم تمرير أفقي |
| Desktop | إخفاء الشريط السفلي، قائمة الهيدر |
| السلة | إضافة، تعديل كمية، حذف، إجمالي |
| الدفع | الوصول إلى checkout (دون إتمام طلب حقيقي إن لم ترغب) |
| المفضلة | زر القلب + قسم `#kymeet-wish` على الرئيسية |

## ملفات رئيسية

| مسار | دور |
|------|-----|
| `components/packs/kymeet-store/setup.php` | تحميل الأصول، الهيدر/الفوتر، AJAX |
| `components/packs/kymeet-store/woo.php` | `wc-ajax=kymeet_cart` |
| `components/packs/kymeet-store/products.php` | `KYMEET_DATA` من WooCommerce |
| `components/packs/kymeet-store/widgets.php` | ويدجتات `kymeet_*` |
| `assets/kymeet.css` | أنماط المتجر (مستخرجة من التصميم الحي) |
| `assets/kymeet-luxury.css` | طبقة كحلي/ذهبي + 6-tabs + sticky buy |
