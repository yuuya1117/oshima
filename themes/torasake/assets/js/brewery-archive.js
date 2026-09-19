/**
 * 酒蔵一覧の絞り込み（参照: reference/brewery-archive.html の inline script）。
 *
 * 参照HTMLは JS 内の配列を描画していたが、カードは PHP が全件出力済み。
 * ここでは data-* 属性を見て表示/非表示を切り替えるだけにしている
 * （初期表示がHTMLに入るのでSEO・JS無効環境にも耐える）。
 *
 * ★ 価格はカードに存在しない（CLAUDE.md）。検索対象にもしない。
 */
(function () {
  'use strict';

  var qInput = document.getElementById('q');
  var prefSelect = document.getElementById('prefSelect');
  var eventSelect = document.getElementById('eventSelect');
  var grid = document.getElementById('grid');
  var countNum = document.getElementById('countNum');
  var emptyState = document.getElementById('emptyState');
  var activeTags = document.getElementById('activeTags');
  var clearBtn = document.getElementById('clearBtn');

  if (!grid || !qInput || !prefSelect || !eventSelect) {
    return;
  }

  var cards = Array.prototype.slice.call(grid.querySelectorAll('.brew-card'));
  var defaultEvent = eventSelect.dataset.default || '';

  function labelFor(select, value) {
    var option = select.querySelector('option[value="' + CSS.escape(value) + '"]');
    return option ? option.textContent.trim() : value;
  }

  function render() {
    var q = qInput.value.trim().toLowerCase();
    var pref = prefSelect.value;
    var ev = eventSelect.value;
    var visible = 0;

    cards.forEach(function (card) {
      var events = (card.dataset.events || '').split(',');
      var matchQ = !q || (card.dataset.search || '').indexOf(q) !== -1;
      var matchPref = !pref || card.dataset.pref === pref;
      var matchEv = !ev || events.indexOf(ev) !== -1;
      var show = matchQ && matchPref && matchEv;

      card.hidden = !show;
      if (show) {
        visible++;
      }
    });

    if (countNum) {
      countNum.textContent = String(visible);
    }
    if (emptyState) {
      emptyState.classList.toggle('show', visible === 0);
    }
    if (clearBtn) {
      clearBtn.classList.toggle('show', !!(q || pref || ev !== defaultEvent));
    }

    if (!activeTags) {
      return;
    }

    var tags = [];
    if (q) {
      tags.push({
        label: '"' + q + '"',
        clear: function () {
          qInput.value = '';
        }
      });
    }
    if (pref) {
      tags.push({
        label: pref,
        clear: function () {
          prefSelect.value = '';
        }
      });
    }
    if (ev) {
      tags.push({
        label: labelFor(eventSelect, ev),
        clear: function () {
          eventSelect.value = '';
        }
      });
    }

    activeTags.textContent = '';
    tags.forEach(function (tag) {
      var span = document.createElement('span');
      span.className = 'tag';
      span.appendChild(document.createTextNode(tag.label));

      var button = document.createElement('button');
      button.type = 'button';
      button.setAttribute('aria-label', '解除');
      button.textContent = '×';
      button.addEventListener('click', function () {
        tag.clear();
        render();
      });

      span.appendChild(button);
      activeTags.appendChild(span);
    });
  }

  qInput.addEventListener('input', render);
  prefSelect.addEventListener('change', render);
  eventSelect.addEventListener('change', render);

  if (clearBtn) {
    clearBtn.addEventListener('click', function () {
      qInput.value = '';
      prefSelect.value = '';
      eventSelect.value = '';
      render();
    });
  }

  // URL パラメータでのプリフィル（?pref= / ?q= / ?event=）。
  var params = new URLSearchParams(location.search);
  if (params.get('pref')) {
    prefSelect.value = params.get('pref');
  }
  if (params.get('q')) {
    qInput.value = params.get('q');
  }
  if (params.get('event')) {
    eventSelect.value = params.get('event');
  }

  render();
})();
