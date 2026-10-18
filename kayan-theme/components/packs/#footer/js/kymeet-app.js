/**
 * KYMEET App UI — Phase 2 bootstrap (theme-color only; no dark/light toggle)
 */
(function () {
  'use strict';

  function setThemeColor() {
    var meta = document.querySelector('meta[name="theme-color"]');
    if (!meta) {
      meta = document.createElement('meta');
      meta.setAttribute('name', 'theme-color');
      document.head.appendChild(meta);
    }
    meta.setAttribute('content', '#FFFFFF');
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setThemeColor);
  } else {
    setThemeColor();
  }

  window.kymeetApp = {
    setThemeColor: setThemeColor,
  };
})();
