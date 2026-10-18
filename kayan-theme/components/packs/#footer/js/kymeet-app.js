/**
 * KYMEET App UI — Phase 1: theme mode + theme-color meta
 */
(function () {
  'use strict';

  var STORAGE_KEY = 'kymeet_theme';
  var DEFAULT = 'dark';

  function isBot() {
    if (typeof navigator === 'undefined' || !navigator.userAgent) {
      return false;
    }
    return /bot|crawl|spider|slurp|facebookexternalhit|preview/i.test(navigator.userAgent);
  }

  function readStored() {
    try {
      var v = localStorage.getItem(STORAGE_KEY);
      return v === 'light' || v === 'dark' ? v : DEFAULT;
    } catch (e) {
      return DEFAULT;
    }
  }

  function themeColorFor(mode) {
    if (mode === 'light') {
      return '#eef4ff';
    }
    return '#050e24';
  }

  function applyTheme(mode, persist) {
    var m = mode === 'light' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', m);
    if (persist) {
      try {
        localStorage.setItem(STORAGE_KEY, m);
      } catch (e) {}
    }
    var meta = document.querySelector('meta[name="theme-color"]');
    if (!meta) {
      meta = document.createElement('meta');
      meta.setAttribute('name', 'theme-color');
      document.head.appendChild(meta);
    }
    meta.setAttribute('content', themeColorFor(m));
    document.dispatchEvent(new CustomEvent('kymeet-theme-change', { detail: { theme: m } }));
  }

  function toggleTheme() {
    var next = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
    applyTheme(next, true);
    return next;
  }

  function bindToggles() {
    document.querySelectorAll('[data-kymeet-theme-toggle]').forEach(function (btn) {
      if (btn.dataset.kymeetBound) {
        return;
      }
      btn.dataset.kymeetBound = '1';
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        toggleTheme();
      });
    });
  }

  function init() {
    if (isBot()) {
      applyTheme(DEFAULT, false);
      return;
    }
    applyTheme(readStored(), false);
    bindToggles();
  }

  window.kymeetApp = {
    applyTheme: applyTheme,
    toggleTheme: toggleTheme,
    readTheme: readStored,
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
