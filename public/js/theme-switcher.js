/**
 * Masar Theme Switcher - JS
 * Themes: classic, mint-green, dark, monokai
 * Uses localStorage key: masar_theme
 * Scoped namespace to avoid name collisions.
 */
(function (global) {
  'use strict';

  var STORAGE_KEY = 'masar_theme';
  var BODY_THEME_PREFIX = 'theme-';
  var VALID_THEMES = ['classic', 'mint-green', 'dark', 'monokai'];
  var DEFAULT_THEME = 'classic';

  function getStoredTheme() {
    try {
      var stored = localStorage.getItem(STORAGE_KEY);
      if (stored && VALID_THEMES.indexOf(stored) !== -1) {
        return stored;
      }
    } catch (e) {}
    return DEFAULT_THEME;
  }

  function setStoredTheme(theme) {
    try {
      if (VALID_THEMES.indexOf(theme) !== -1) {
        localStorage.setItem(STORAGE_KEY, theme);
      }
    } catch (e) {}
  }

  function removeThemeClassesFromBody() {
    var body = document.body;
    if (!body) return;
    VALID_THEMES.forEach(function (t) {
      body.classList.remove(BODY_THEME_PREFIX + t);
    });
  }

  function applyThemeToBody(theme) {
    var body = document.body;
    if (!body) return;
    removeThemeClassesFromBody();
    body.classList.add(BODY_THEME_PREFIX + theme);
  }

  function setTheme(theme) {
    if (VALID_THEMES.indexOf(theme) === -1) {
      theme = DEFAULT_THEME;
    }
    setStoredTheme(theme);
    applyThemeToBody(theme);
    return theme;
  }

  function getTheme() {
    return getStoredTheme();
  }

  function init() {
    var theme = getStoredTheme();
    applyThemeToBody(theme);
  }

  function bindDropdown(containerSelector) {
    var container = document.querySelector(containerSelector);
    if (!container) return;
    container.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-masar-theme]');
      if (!btn) return;
      e.preventDefault();
      var theme = btn.getAttribute('data-masar-theme');
      if (theme) {
        setTheme(theme);
      }
    });
  }

  function bindSelect(selectSelectorOrId) {
    var el = typeof selectSelectorOrId === 'string'
      ? (selectSelectorOrId.indexOf('#') === 0
          ? document.getElementById(selectSelectorOrId.slice(1))
          : document.querySelector(selectSelectorOrId))
      : selectSelectorOrId;
    if (!el || el.tagName !== 'SELECT') return;
    el.value = getStoredTheme();
    el.addEventListener('change', function () {
      var theme = this.value;
      if (VALID_THEMES.indexOf(theme) !== -1) {
        setTheme(theme);
      }
    });
  }

  function runEarly() {
    var theme = getStoredTheme();
    var body = document.body;
    if (body) {
      removeThemeClassesFromBody();
      body.classList.add(BODY_THEME_PREFIX + theme);
    }
  }

  var MasarThemeSwitcher = {
    setTheme: setTheme,
    getTheme: getTheme,
    init: init,
    bindDropdown: bindDropdown,
    bindSelect: bindSelect,
    runEarly: runEarly,
    VALID_THEMES: VALID_THEMES,
    DEFAULT_THEME: DEFAULT_THEME
  };

  global.MasarThemeSwitcher = MasarThemeSwitcher;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})(typeof window !== 'undefined' ? window : this);
