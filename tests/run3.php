<?php
/**
 * イベント一覧（archive-event.php）の描画チェック。
 *
 * 開催予定は紺のフィーチャーカード、終了した回は行で並ぶ。
 */
require __DIR__ . '/stubs.php';

$T = &$GLOBALS['T'];
$T['posts'] = [
  new WP_Post(['ID'=>3,'post_title'=>'TORASAKE mini','post_name'=>'torasake-mini','post_type'=>'event']),
  new WP_Post(['ID'=>4,'post_title'=>'TORASAKE -虎ノ酒- 2026','post_name'=>'torasake-2026','post_type'=>'event']),
  new WP_Post(['ID'=>1,'post_title'=>'蔵','post_name'=>'taka','post_type'=>'brewery']),
];
require __DIR__ . '/stubs_bootstrap.php';

// 開催予定1件 + 終了1件。event_status は投稿ごとに変える必要があるので
// get_field をIDで出し分けられるよう per-post のフィールドを使う。
$T['context'] = 'archive-event';
$T['fields_by_post'] = [
  3 => ['event_status'=>'開催予定','event_date'=>'20261016','venue_name'=>'虎ノ門ヒルズ ステーションタワー B2F T-MARKET','breweries'=>[1,1,1,1,1,1,1,1,1,1]],
  4 => ['event_status'=>'終了','event_date'=>'20260627','venue_name'=>'虎ノ門ヒルズ ステーションタワー'],
];
unset($T['loop_all']);
$T['loop'] = [get_post(3), get_post(4)];
$T['current'] = $T['loop'][0];

ob_start();
try {
    include get_template_directory() . '/archive-event.php';
    $h = ob_get_clean();
} catch (Throwable $e) {
    ob_end_clean();
    echo '  ✗ ' . get_class($e) . ': ' . $e->getMessage()
       . ' @ ' . basename($e->getFile()) . ':' . $e->getLine() . "\n";
    exit(1);
}

printf("  ✓ archive-event         %6d bytes\n", strlen($h));

$checks = [
  'ページヒーロー'                 => str_contains($h, 'page-hero') && str_contains($h, 'Events'),
  '開催予定は紺フィーチャーカード' => str_contains($h, 'class="ev-feature"'),
  '  └ ステータスチップ'           => str_contains($h, '<span class="status">開催予定</span>'),
  '  └ 日付行 (Y.m.d DAY)'         => str_contains($h, '2026.10.16 FRI'),
  '  └ 会場＋蔵数（算出値）'       => str_contains($h, '全国10蔵'),
  '終了した回は行で並ぶ'           => str_contains($h, 'class="ev-row"'),
  '  └ 終了チップ'                 => str_contains($h, '<span class="status">終了</span>'),
  '  └ Archive ラベル'             => str_contains($h, 'class="ev-label"'),
  '価格を出さない'                 => !preg_match('/[0-9][0-9,]*\s*円/u', $h) && !str_contains($h, '¥'),
];
foreach ($checks as $k => $v) {
    printf("  %s %s\n", $v ? '✓' : '✗', $k);
}
exit(in_array(false, $checks, true) ? 1 : 0);
