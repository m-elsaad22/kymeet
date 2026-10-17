(function () {
  'use strict';
  const CFG = window.KYMEET_UI || {};
  const HOME = CFG.home || '/';
  const SHOP = CFG.shop || '/shop/';
  const ACCOUNT = CFG.account || '/my-account/';

  function injectCategoryRail() {
    if (!document.querySelector('#discover') || document.getElementById('kymeet-cats')) return;
    const kd = window.KYMEET_DATA || {};
    const brands = (kd.BR || []).slice(0, 6);
    const brandCards = brands
      .map(
        (b) =>
          '<a class="kcat rv" href="' +
          HOME +
          '#catalog" data-brand="' +
          b +
          '"><span class="kcat-ico"><i class="fas fa-gem"></i></span><b>' +
          b +
          '</b><small>علامة</small></a>'
      )
      .join('');
    const sec = document.createElement('section');
    sec.className = 'sec kymeet-cats-sec';
    sec.id = 'kymeet-cats';
    sec.innerHTML =
      '<div class="wrap"><div class="shead rv"><span class="tag en">COLLECTIONS</span><h2>تصنيفات <em>العطور</em></h2><p>تصفح حسب العلامة أو اكتشف أحدث الإصدارات.</p></div>' +
      '<div class="kslider kymeet-cat-slider" data-auto="0"><div class="kslider-track kymeet-cat-track">' +
      '<a class="kcat rv" href="' +
      SHOP +
      '"><span class="kcat-ico"><i class="fas fa-border-all"></i></span><b>كل العطور</b><small>المتجر</small></a>' +
      brandCards +
      '<a class="kcat rv" href="#new"><span class="kcat-ico"><i class="fas fa-sparkles"></i></span><b>وصل حديثًا</b><small>GISSAH</small></a>' +
      '</div></div></div>';
    sec.querySelectorAll('[data-brand]').forEach((a) => {
      a.addEventListener('click', (e) => {
        e.preventDefault();
        const b = a.getAttribute('data-brand');
        if (typeof pickBrand === 'function') pickBrand(b);
        else location.href = SHOP;
      });
    });
    const discover = document.getElementById('discover');
    discover.closest('.-YourColor-SingleWidget-Section')?.after(sec) || discover.after(sec);
  }

  function injectWishlistSection() {
    if (!document.getElementById('catalog') || document.getElementById('kymeet-wish')) return;
    const sec = document.createElement('section');
    sec.className = 'sec';
    sec.id = 'kymeet-wish';
    sec.innerHTML =
      '<div class="wrap"><div class="shead rv"><span class="tag en">WISHLIST</span><h2>المفضلة</h2><p>العطور التي حفظتها على هذا الجهاز.</p></div><div class="p-grid" id="wishGrid"></div></div>';
    document.getElementById('catalog').closest('.sec')?.before(sec);
  }

  function renderWishGrid() {
    const grid = document.getElementById('wishGrid');
    if (!grid || !window.KYMEET_DATA) return;
    const key = CFG.wishKey || 'kymeet_wish_v1';
    let ids = [];
    try {
      ids = JSON.parse(localStorage.getItem(key) || '[]');
    } catch (e) {}
    const list = (window.KYMEET_DATA.products || []).filter((p) => ids.includes(p.id));
    if (!list.length) {
      grid.innerHTML = '<p class="empty">لا توجد عطور في المفضلة بعد.</p>';
      return;
    }
    if (typeof productHTML === 'function') {
      grid.innerHTML = list.map((p) => productHTML(p, true)).join('');
    }
  }

  function rebuildBottomNav() {
    const nav = document.querySelector('nav.appnav');
    if (!nav || nav.dataset.kymeetV2) return;
    nav.dataset.kymeetV2 = '1';
    nav.innerHTML =
      '<a class="an-i" data-nav="home" href="' +
      HOME +
      '"><i class="fas fa-house"></i><span>الرئيسية</span></a>' +
      '<a class="an-i" data-nav="shop" href="' +
      SHOP +
      '"><i class="fas fa-store"></i><span>المتجر</span></a>' +
      '<a class="an-i" data-nav="brands" href="#brands"><i class="fas fa-gem"></i><span>البراندات</span></a>' +
      '<a class="an-i" data-nav="wish" href="#kymeet-wish"><i class="far fa-heart"></i><span>المفضلة</span></a>' +
      '<a class="an-i" data-nav="account" href="' +
      ACCOUNT +
      '"><i class="far fa-user"></i><span>حسابي</span></a>';
    const path = location.pathname.replace(/\/$/, '');
    nav.querySelectorAll('.an-i').forEach((a) => a.classList.remove('on'));
    if (path === '' || path === '/') nav.querySelector('[data-nav="home"]')?.classList.add('on');
    else if (path.includes('shop')) nav.querySelector('[data-nav="shop"]')?.classList.add('on');
    else if (path.includes('my-account')) nav.querySelector('[data-nav="account"]')?.classList.add('on');
    else nav.querySelector('[data-nav="home"]')?.classList.add('on');
  }

  function markReducedMotion() {
    if (matchMedia('(prefers-reduced-motion: reduce)').matches) {
      document.documentElement.classList.add('kymeet-reduce-motion');
    }
  }

  function init() {
    markReducedMotion();
    injectCategoryRail();
    injectWishlistSection();
    rebuildBottomNav();
    setTimeout(renderWishGrid, 400);
    document.addEventListener('click', (e) => {
      if (e.target.closest('.prod-wish')) setTimeout(renderWishGrid, 200);
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
