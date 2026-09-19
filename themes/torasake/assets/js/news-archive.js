/**
 * お知らせ一覧のカテゴリチップ（参照: reference/news-archive.html の inline script）。
 */
(function () {
  'use strict';

  var chips = document.querySelectorAll('.filter-chip');
  var items = document.querySelectorAll('#newsList > li');
  var emptyState = document.getElementById('emptyState');

  if (!chips.length || !items.length) {
    return;
  }

  chips.forEach(function (chip) {
    chip.addEventListener('click', function () {
      chips.forEach(function (c) {
        c.classList.remove('active');
      });
      chip.classList.add('active');

      var cat = chip.dataset.cat;
      var visible = 0;

      items.forEach(function (li) {
        var show = !cat || li.dataset.cat === cat;
        li.hidden = !show;
        if (show) {
          visible++;
        }
      });

      if (emptyState) {
        emptyState.classList.toggle('show', visible === 0);
      }
    });
  });
})();
