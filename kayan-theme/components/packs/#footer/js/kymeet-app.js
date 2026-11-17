/**
 * KYMEET app chrome — Step 3 + 4.1
 */
(function () {
  'use strict';

  var WISH_KEY = 'kymeet_wish_v1';
  var LANG = document.documentElement.lang && document.documentElement.lang.indexOf('en') === 0 ? 'en' : 'ar';

  function setThemeColor() {
    var meta = document.querySelector('meta[name="theme-color"]');
    if (!meta) {
      meta = document.createElement('meta');
      meta.setAttribute('name', 'theme-color');
      document.head.appendChild(meta);
    }
    meta.setAttribute('content', '#FFFFFF');
  }

  function products() {
    var data = window.KYMEET_DATA || {};
    return Array.isArray(data.products) ? data.products : [];
  }

  function wishIds() {
    try {
      var raw = JSON.parse(localStorage.getItem(WISH_KEY) || '[]');
      return Array.isArray(raw) ? raw.map(String) : [];
    } catch (e) {
      return [];
    }
  }

  function saveWish(ids) {
    localStorage.setItem(WISH_KEY, JSON.stringify(ids));
  }

  function openSheet(id) {
    var el = document.getElementById(id);
    if (!el) return;
    el.hidden = false;
    var input = el.querySelector('input');
    if (input) input.focus();
  }

  function closeSheets() {
    document.querySelectorAll('.kapp-sheet, .kapp-wish').forEach(function (el) {
      el.hidden = true;
    });
  }

  function esc(t) {
    return String(t || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/"/g, '&quot;');
  }

  function brandInName(name, brand) {
    if (!name || !brand) return false;
    return name.toLowerCase().indexOf(brand.toLowerCase()) !== -1;
  }

  function formatPrice(p) {
    if (!p || p.price == null || p.price === '') return '';
    var n = Number(p.price);
    if (isNaN(n)) return '';
    var cur = LANG === 'en' ? ' EGP' : ' ج.م';
    return n.toLocaleString('en-US') + cur;
  }

  function resultItem(p) {
    var href = p.url || '#';
    var name = p.name || '';
    var brand = p.brand && !brandInName(name, p.brand) ? p.brand : '';
    var img = p.image
      ? '<img class="kapp-hit__img" src="' + esc(p.image) + '" alt="" loading="lazy" width="48" height="48">'
      : '<span class="kapp-hit__ph" aria-hidden="true"></span>';
    var price = formatPrice(p);
    return (
      '<a class="kapp-hit" href="' +
      esc(href) +
      '">' +
      img +
      '<span class="kapp-hit__body"><span class="kapp-hit__name">' +
      esc(name) +
      '</span>' +
      (brand ? '<small class="kapp-hit__brand">' + esc(brand) + '</small>' : '') +
      (price ? '<span class="kapp-hit__price">' + esc(price) + '</span>' : '') +
      '</span></a>'
    );
  }

  function renderResults(list, target) {
    if (!target) return;
    var emptyMsg = LANG === 'en' ? 'No results found.' : 'لا توجد نتائج.';
    if (!list.length) {
      target.innerHTML = '<p class="kapp-muted kapp-empty">' + emptyMsg + '</p>';
      return;
    }
    target.innerHTML = list.map(resultItem).join('');
  }

  function onSearchInput() {
    var q = (document.getElementById('kapp-q') || {}).value || '';
    q = q.trim().toLowerCase();
    var list = products().filter(function (p) {
      var blob = ((p.name || '') + ' ' + (p.brand || '')).toLowerCase();
      return !q || blob.indexOf(q) !== -1;
    });
    renderResults(list.slice(0, 6), document.getElementById('kapp-suggest'));
    renderResults(list, document.getElementById('kapp-results'));
  }

  function renderWish() {
    var ids = wishIds();
    var list = products().filter(function (p) {
      return ids.indexOf(String(p.id || p.pid || '')) !== -1;
    });
    renderResults(list, document.getElementById('kapp-wish-grid'));
  }

  function toggleWishButton(btn, on) {
    if (!btn) return;
    btn.classList.toggle('active', on);
    var i = btn.querySelector('i');
    if (i) {
      i.classList.toggle('far', !on);
      i.classList.toggle('fas', on);
    }
  }

  function syncWishButtons() {
    var ids = wishIds();
    document.querySelectorAll('.prod-wish[data-id], .kapp-card__wish[data-id]').forEach(function (btn) {
      var id = btn.getAttribute('data-id');
      toggleWishButton(btn, ids.indexOf(String(id)) !== -1);
    });
  }

  function toggleWish(id) {
    if (!id) return;
    var ids = wishIds();
    var key = String(id);
    var i = ids.indexOf(key);
    if (i === -1) ids.push(key);
    else ids.splice(i, 1);
    saveWish(ids);
    syncWishButtons();
  }

  var sliderTap = { x: 0, y: 0, t: 0 };

  function syncCartBadge() {
    var src = document.getElementById('cartCount');
    var dst = document.getElementById('kappCartCount');
    if (src && dst) dst.textContent = src.textContent || '0';
  }

  function initAnnounce() {
    var bar = document.getElementById('kapp-announce');
    if (!bar) return;
    try {
      if (sessionStorage.getItem('kapp_announce_off') === '1') bar.classList.add('is-hidden');
    } catch (e) {}
    var btn = bar.querySelector('[data-kapp-announce-close]');
    if (btn) {
      btn.addEventListener('click', function () {
        bar.classList.add('is-hidden');
        try {
          sessionStorage.setItem('kapp_announce_off', '1');
        } catch (e) {}
      });
    }
  }

  function init() {
    setThemeColor();
    document.body.classList.remove('before-start');
    var loader = document.getElementById('loader');
    if (loader) {
      loader.classList.add('out');
      loader.setAttribute('aria-hidden', 'true');
    }
    initAnnounce();
    syncCartBadge();
    syncWishButtons();
    var src = document.getElementById('cartCount');
    if (src && window.MutationObserver) {
      new MutationObserver(syncCartBadge).observe(src, { childList: true, characterData: true, subtree: true });
    }
    document.addEventListener(
      'touchstart',
      function (e) {
        if (!e.target.closest('.kapp-slider')) return;
        sliderTap.x = e.touches[0].clientX;
        sliderTap.y = e.touches[0].clientY;
        sliderTap.t = Date.now();
      },
      { passive: true }
    );

    document.addEventListener(
      'touchend',
      function (e) {
        var link = e.target.closest('.kapp-slider a.kapp-card__link[href]');
        if (!link || !e.changedTouches || !e.changedTouches[0]) return;
        var dx = Math.abs(e.changedTouches[0].clientX - sliderTap.x);
        var dy = Math.abs(e.changedTouches[0].clientY - sliderTap.y);
        if (dx > 14 || dy > 14 || Date.now() - sliderTap.t > 600) return;
        var href = link.getAttribute('href');
        if (!href || href === '#') return;
        e.preventDefault();
        window.location.assign(link.href);
      },
      { passive: false }
    );

    document.addEventListener('click', function (e) {
      var heart = e.target.closest('.prod-wish, .kapp-card__wish');
      if (heart) {
        e.preventDefault();
        e.stopPropagation();
        var card = heart.closest('[data-id], .prod, .kapp-card');
        var id =
          heart.getAttribute('data-id') ||
          (card && (card.getAttribute('data-id') || card.getAttribute('data-pid')));
        toggleWish(id);
        return;
      }
      var open = e.target.closest('[data-kapp-open]');
      if (open) {
        e.preventDefault();
        var which = open.getAttribute('data-kapp-open');
        closeSheets();
        if (which === 'search') {
          openSheet('kapp-search');
          onSearchInput();
        }
        if (which === 'wish') {
          renderWish();
          openSheet('kymeet-wish');
        }
        return;
      }
      if (e.target.closest('[data-kapp-close]')) {
        closeSheets();
      }
    });
    var q = document.getElementById('kapp-q');
    if (q) q.addEventListener('input', onSearchInput);
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeSheets();
    });
    if (location.hash === '#kymeet-wish') {
      renderWish();
      openSheet('kymeet-wish');
    }
  }

  window.kymeetApp = { setThemeColor: setThemeColor, openSearch: function () { openSheet('kapp-search'); } };

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
