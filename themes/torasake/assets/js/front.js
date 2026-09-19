/**
 * トップページ（参照: reference/top-page.html の inline script）。
 *
 * カウントダウンの基準時刻はハードコードせず、PHP から
 * TORASAKE_FRONT.eventDatetime（ISO8601）で受け取る。
 */
(function () {
  'use strict';

  var nav = document.getElementById('nav');
  var sticky = document.getElementById('sticky');

  window.addEventListener(
    'scroll',
    function () {
      if (nav) {
        nav.classList.toggle('scrolled', window.scrollY > 50);
      }
      if (sticky) {
        sticky.classList.toggle('show', window.scrollY > window.innerHeight * 0.8);
      }
    },
    { passive: true }
  );

  // ハンバーガー / モバイルメニュー
  var hb = document.getElementById('hamburger');
  var mm = document.getElementById('mobile-menu');
  if (hb && mm) {
    hb.addEventListener('click', function () {
      var open = mm.classList.toggle('open');
      hb.classList.toggle('open', open);
      hb.setAttribute('aria-expanded', String(open));
    });
    mm.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () {
        mm.classList.remove('open');
        hb.classList.remove('open');
        hb.setAttribute('aria-expanded', 'false');
      });
    });
  }

  // スクロールフェードイン
  var targets = document.querySelectorAll('.rv');
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (e) {
          if (e.isIntersecting) {
            e.target.classList.add('in');
            io.unobserve(e.target);
          }
        });
      },
      { threshold: 0.12, rootMargin: '0px 0px -6% 0px' }
    );
    targets.forEach(function (el) {
      io.observe(el);
    });
  } else {
    targets.forEach(function (el) {
      el.classList.add('in');
    });
  }

  // 開催までのカウントダウン
  var el = document.getElementById('countdown');
  var config = window.TORASAKE_FRONT || {};
  if (!el || !config.eventDatetime) {
    return;
  }

  var target = new Date(config.eventDatetime);
  if (isNaN(target.getTime())) {
    return;
  }

  function tick() {
    var diff = target - new Date();
    if (diff <= 0) {
      el.textContent = config.finished ? '終了しました' : '開催中';
      return;
    }
    var d = Math.floor(diff / 864e5);
    var h = Math.floor(diff / 36e5) % 24;
    var m = Math.floor(diff / 6e4) % 60;
    el.textContent = d + '日 ' + h + '時間 ' + m + '分';
  }

  tick();
  setInterval(tick, 60000);
})();
