<?php
require __DIR__ . '/stubs.php';
$T = &$GLOBALS['T'];
$T['posts'] = [
  new WP_Post(['ID'=>3,'post_title'=>'TORASAKE mini','post_name'=>'torasake-mini','post_type'=>'event']),
  new WP_Post(['ID'=>4,'post_title'=>'TORASAKE 2026','post_name'=>'torasake-2026','post_type'=>'event']),
];
require get_template_directory() . '/functions.php';
do_action('after_setup_theme'); do_action('init');

$T['context']='archive-event';
$T['fields']=['event_status'=>'開催予定','event_date'=>'20261016','venue_name'=>'虎ノ門ヒルズ T-MARKET'];
$T['loop']=[get_post(3), get_post(4)];
$T['current']=$T['loop'][0];

ob_start();
try { include get_template_directory() . '/archive-event.php'; $h=ob_get_clean(); }
catch (Throwable $e) { ob_end_clean(); echo "  ✗ ".get_class($e).": ".$e->getMessage()." @ ".basename($e->getFile()).":".$e->getLine()."\n"; exit(1); }

printf("  ✓ archive-event         %6d bytes\n", strlen($h));
$checks = [
  'ページヒーロー' => str_contains($h,'page-hero') && str_contains($h,'Events'),
  'ステータスチップ' => str_contains($h,'news-tag cat-info') && str_contains($h,'開催予定'),
  '開催日' => str_contains($h,'2026年10月16日(金)'),
  '会場' => str_contains($h,'虎ノ門ヒルズ T-MARKET'),
  '価格を出さない' => !preg_match('/[0-9][0-9,]*\s*円/u',$h) && !str_contains($h,'¥'),
  'ページネーション' => str_contains($h,'news-pagination'),
];
foreach ($checks as $k=>$v) printf("  %s %s\n", $v?'✓':'✗', $k);
exit(in_array(false,$checks,true)?1:0);
