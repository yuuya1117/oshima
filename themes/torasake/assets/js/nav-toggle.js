/**
 * 記事ページのハンバーガー（参照: reference/news-single-example.html の inline script）。
 */
(function () {
  'use strict';

  var hamburger = document.getElementById('hamburger');
  var navLinks = document.getElementById('nav-links');

  if (!hamburger || !navLinks) {
    return;
  }

  hamburger.addEventListener('click', function () {
    var isOpen = navLinks.classList.toggle('open');
    hamburger.classList.toggle('open', isOpen);
    hamburger.setAttribute('aria-expanded', String(isOpen));
  });
})();
