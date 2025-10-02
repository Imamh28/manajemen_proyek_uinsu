// assets/js/main.js
$(function () {
  const LS_KEY = 'ui_prefs_v1';
  const $html = $('html');
  const $wrapper = $('.wrapper');

  // ---------- storage utils ----------
  function loadPrefs() {
    try {
      return Object.assign(
        { theme: 'light-theme', header: '', sidebar: '', wrapperToggled: $wrapper.hasClass('toggled') },
        JSON.parse(localStorage.getItem(LS_KEY) || '{}')
      );
    } catch (_) {
      return { theme: 'light-theme', header: '', sidebar: '', wrapperToggled: $wrapper.hasClass('toggled') };
    }
  }
  function savePrefs(p) { localStorage.setItem(LS_KEY, JSON.stringify(p)); }

  function stripThemeClasses() {
    const toRemove = ($html.attr('class') || '')
      .split(/\s+/).filter(Boolean)
      .filter(c => /^(light-theme|dark-theme|semi-dark|color-header|headercolor\d+|color-sidebar|sidebarcolor\d+)$/.test(c));
    if (toRemove.length) $html.removeClass(toRemove.join(' '));
  }

  function applyPrefs(p) {
    stripThemeClasses();
    const add = [p.theme, p.header, p.sidebar].filter(Boolean);
    if (add.length) $html.addClass(add.join(' '));
    $wrapper.toggleClass('toggled', !!p.wrapperToggled);

    // sync icon (optional)
    const $icon = $('.mode-icon ion-icon');
    if ($icon.length) $icon.attr('name', p.theme === 'dark-theme' ? 'sunny-outline' : 'moon-outline');
  }

  let PREFS = loadPrefs();
  applyPrefs(PREFS);

  // ---------- re-apply if other script overwrites ----------
  const wantTokens = () => (PREFS.theme + ' ' + PREFS.header + ' ' + PREFS.sidebar)
    .trim().split(/\s+/).filter(Boolean);

  const mo = new MutationObserver(() => {
    const cls = $html.attr('class') || '';
    const wanted = wantTokens();
    // Jika ada token preferensi yang hilang, re-apply
    if (!wanted.every(t => cls.includes(t))) applyPrefs(PREFS);
  });
  mo.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

  // ---------- setters ----------
  function setTheme(theme) { PREFS.theme = theme; savePrefs(PREFS); applyPrefs(PREFS); }
  function setHeader(n) { PREFS.header = n ? `color-header headercolor${n}` : ''; savePrefs(PREFS); applyPrefs(PREFS); }
  function setSidebar(n) { PREFS.sidebar = n ? `color-sidebar sidebarcolor${n}` : ''; savePrefs(PREFS); applyPrefs(PREFS); }
  function setToggled(on) { PREFS.wrapperToggled = !!on; savePrefs(PREFS); applyPrefs(PREFS); }

  // ---------- bind UI ----------
  // toggle dark/light
  $('.dark-mode-icon').on('click', function () {
    setTheme(PREFS.theme === 'dark-theme' ? 'light-theme' : 'dark-theme');
  });

  // theme group
  $('#LightTheme').on('click', () => setTheme('light-theme'));
  $('#DarkTheme').on('click', () => setTheme('dark-theme'));
  $('#SemiDark').on('click', () => setTheme('semi-dark'));

  // header colors
  for (let i = 1; i <= 8; i++) $('#headercolor' + i).on('click', () => setHeader(i));

  // sidebar colors
  for (let i = 1; i <= 8; i++) $('#sidebarcolor' + i).on('click', () => setSidebar(i));

  // sidebar toggled persist
  $('.nav-toggle-icon, .toggle-icon').on('click', () => setToggled(!$wrapper.hasClass('toggled')));
  $('.mobile-menu-button').on('click', () => setToggled(true));

  // ---------- the rest of your page JS (safe-guarded) ----------
  $("#menu").metisMenu();

  if (window.PerfectScrollbar) new PerfectScrollbar(".header-notifications-list");

  $('[data-bs-toggle="tooltip"]').tooltip();
  $(window).on("scroll", function () {
    $(this).scrollTop() > 300 ? $(".back-to-top").fadeIn() : $(".back-to-top").fadeOut()
  });
  $(".back-to-top").on("click", function () { $("html, body").animate({ scrollTop: 0 }, 600); return false; });

  // activate current menu
  (function () {
    var e = window.location.href;
    var $a = $(".metismenu li a").filter(function () { return this.href === e; });
    var o = $a.parent().addClass("mm-active");
    while (o.is("li")) o = o.parent().addClass("mm-show").parent().addClass("mm-active");
  })();
});
