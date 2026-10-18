/**
 * KYMEET app chrome — Step 3
 */
(function () {
  'use strict';

  var WISH_KEY = 'kymeet_wish_v1';

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

  function renderResults(list, target) {
    if (!target) return;
    if (!list.length) {
      target.innerHTML = '<p class="kapp-muted">لا توجد نتائج.</p>';
      return;
    }
    target.innerHTML = list
      .slice(0, 12)
      .map(function (p) {
        var href = p.url || '#';
        return (
          '<a href="' +
          href +
          '"><span>' +
          (p.name || '') +
          '</span><small>' +
          (p.brand || '') +
          '</small></a>'
        );
      })
      .join('');
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
    initAnnounce();
    syncCartBadge();
    var src = document.getElementById('cartCount');
    if (src && window.MutationObserver) {
      new MutationObserver(syncCartBadge).observe(src, { childList: true, characterData: true, subtree: true });
    }
    document.addEventListener('click', function (e) {
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
      var heart = e.target.closest('.prod-wish');
      if (heart) {
        var card = heart.closest('[data-id], .prod');
        var id = heart.getAttribute('data-id') || (card && card.getAttribute('data-id'));
        if (!id) return;
        var ids = wishIds();
        var i = ids.indexOf(String(id));
        if (i === -1) ids.push(String(id));
        else ids.splice(i, 1);
        saveWish(ids);
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
