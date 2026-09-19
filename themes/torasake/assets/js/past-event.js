/**
 * 過去開催レポートのライトボックス（参照: reference/past-event-single.html の inline script）。
 *
 * 参照は item の img.src をそのまま拡大していたが、グリッドには縮小版を出しているので
 * data-full（フルサイズURL）があればそちらを開く。
 */
(function () {
  'use strict';

  var gallery = document.getElementById('gallery');
  var lightbox = document.getElementById('lightbox');
  var lightboxImg = document.getElementById('lightbox-img');

  if (!gallery || !lightbox || !lightboxImg) {
    return;
  }

  function close() {
    lightbox.classList.remove('active');
    lightboxImg.removeAttribute('src');
  }

  gallery.addEventListener('click', function (e) {
    var item = e.target.closest('.gallery-item');
    if (!item) {
      return;
    }
    var img = item.querySelector('img');
    if (!img) {
      return;
    }
    lightboxImg.src = img.dataset.full || img.src;
    lightboxImg.alt = img.alt || '';
    lightbox.classList.add('active');
  });

  lightbox.addEventListener('click', close);

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      close();
    }
  });
})();
