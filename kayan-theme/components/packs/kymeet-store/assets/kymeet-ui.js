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
      '<div class="wrap"><div class="shead rv"><span class="tag en">CATEGORIES</span><h2>تصنيفات <em>العطور</em></h2><p>نسائي، رجالي، ووصل حديثًا.</p></div>' +
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

  function wishHomeHref() {
    const base = HOME.replace(/\/?$/, '/');
    return base + '#kymeet-wish';
  }

  function renderWishGrid() {
    const grid = document.getElementById('wishGrid');
    if (!grid || !window.KYMEET_DATA) return;
    const key = CFG.wishKey || 'kymeet_wish_v1';
    let ids = [];
    try {
      ids = JSON.parse(localStorage.getItem(key) || '[]');
    } catch (e) {}
    ids = [...new Set(ids.filter(Boolean))];
    const list = (window.KYMEET_DATA.products || []).filter((p) => ids.includes(p.id));
    if (!list.length) {
      grid.innerHTML = '<p class="empty">لا توجد عطور في المفضلة بعد.</p>';
      return;
    }
    if (typeof productHTML === 'function') {
      grid.innerHTML = list.map((p) => productHTML(p, true)).join('');
      if (typeof wishSync === 'function') setTimeout(wishSync, 50);
    }
  }

  function dismissSplash() {
    const splash = document.getElementById('kymeet-splash');
    if (!splash || splash.classList.contains('is-hidden')) return;
    splash.classList.add('is-hidden');
    try {
      sessionStorage.setItem('kymeet_splash_seen', '1');
    } catch (e) {}
  }

  function killKayanLoader() {
    const ld = document.getElementById('loader');
    if (ld) {
      ld.classList.add('out');
      ld.remove();
    }
    document.body.classList.remove('before-start');
  }

  function initSplash() {
    const splash = document.getElementById('kymeet-splash');
    if (!splash) return;
    try {
      if (sessionStorage.getItem('kymeet_splash_seen') === '1') {
        splash.classList.add('is-hidden');
        return;
      }
    } catch (e) {}
    splash.querySelector('#kymeet-splash-go')?.addEventListener('click', dismissSplash);
    setTimeout(dismissSplash, 2500);
  }

  function injectCheckoutSteps() {
    if (document.querySelector('.kymeet-checkout-steps')) return;
    const isCart = document.body.classList.contains('woocommerce-cart');
    const isCheckout = document.body.classList.contains('woocommerce-checkout');
    if (!isCart && !isCheckout) return;
    const bar = document.createElement('div');
    bar.className = 'kymeet-checkout-steps';
    bar.innerHTML =
      '<span class="' +
      (isCart ? 'on' : '') +
      '">1</span><span class="' +
      (isCheckout ? 'on' : '') +
      '">2</span><span>3</span>';
    document.querySelector('.kymeet-chrome-root')?.prepend(bar) || document.body.prepend(bar);
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
      '<a class="an-i" data-nav="wish" href="' +
      wishHomeHref() +
      '"><i class="fas fa-heart"></i><span>المفضلة</span></a>' +
      '<button type="button" class="an-i" data-nav="cart" onclick="openCart()"><i class="fas fa-bag-shopping"></i><span>العربة</span><b class="an-badge" id="anBadge">0</b></button>' +
      '<a class="an-i" data-nav="account" href="' +
      ACCOUNT +
      '"><i class="fas fa-user"></i><span>حسابي</span></a>';
    nav.style.gridTemplateColumns = 'repeat(5, 1fr)';
    const path = location.pathname.replace(/\/$/, '');
    nav.querySelectorAll('.an-i').forEach((a) => a.classList.remove('on'));
    if (path === '' || path === '/') nav.querySelector('[data-nav="home"]')?.classList.add('on');
    else if (path.includes('shop')) nav.querySelector('[data-nav="shop"]')?.classList.add('on');
    else if (path.includes('my-account')) nav.querySelector('[data-nav="account"]')?.classList.add('on');
    else if (location.hash === '#kymeet-wish') nav.querySelector('[data-nav="wish"]')?.classList.add('on');
    else nav.querySelector('[data-nav="home"]')?.classList.add('on');
  }

  function markReducedMotion() {
    if (matchMedia('(prefers-reduced-motion: reduce)').matches) {
      document.documentElement.classList.add('kymeet-reduce-motion');
    }
  }

  function init() {
    markReducedMotion();
    killKayanLoader();
    initSplash();
    injectCategoryRail();
    injectWishlistSection();
    rebuildBottomNav();
    injectCheckoutSteps();
    setTimeout(renderWishGrid, 400);
    document.addEventListener('click', (e) => {
      if (e.target.closest('.prod-wish')) setTimeout(renderWishGrid, 200);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
  window.addEventListener('load', killKayanLoader);
  setTimeout(killKayanLoader, 1500);
})();
