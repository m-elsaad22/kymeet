# نشر واجهة KYMEET App UI

## ما تم بناؤه

إضافة WordPress **`kymeet-perfumes-ui`** (لا تعدّل قالب `kayan-theme` مباشرة):

- هوية كحلي/ذهبي (`kymeet-app.css`)
- استبدال `kymeet.js` بنسخة محسّنة (`kymeet-core.js`) + طبقة UX (`kymeet-app.js`)
- قائمة سفلية: الرئيسية · المتجر · البراندات · المفضلة · حسابي
- سلايدر تصنيفات/علامات ديناميكي من `KYMEET_DATA.BR`
- المفضلة عبر `localStorage`
- إعادة تسمية قسم «الأكثر طلبًا» إلى **مختارات كيميت** (بدون ادعاء مبيعات)
- H1 وعناوين `document.title` للرئيسية والمتجر
- شريط شراء ثابت على صفحة المنتج (موبايل)

## تفعيل على الموقع الحي

1. حمّل الأرشيف (موجود على السيرفر):
   - https://kymeet-perfumes.com/wp-content/uploads/2026/10/kymeet-perfumes-ui.zip
2. لوحة WordPress → **إضافات → أضف جديد → رفع إضافة** → اختر الملف → **تفعيل**
3. **LiteSpeed Cache → Purge All**
4. اختبر على الموبايل (390px): القائمة السفلية، الهيرو، السلايدرات، إضافة للسلة

## بناء محلي

```bash
bash scripts/build-kymeet-ui-plugin.sh
# الناتج: dist/kymeet-perfumes-ui.zip
```

## ملاحظات

- لا يغيّر slugs المنتجات أو إعدادات الدفع/الشحن.
- يعطّل تحميل jQuery 3.4 المكرر من القالب (`yourcolor-script-js`).
- للوصول الكامل لتخطيط HTML جديد 100% (مثل المرجع)، الخطوة التالية: دمج الحزمة في `kymeet-store` داخل القالب أو Child Theme.
