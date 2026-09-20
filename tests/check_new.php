<?php
require __DIR__ . '/stubs.php';
$T = &$GLOBALS['T'];
$T['posts'] = [
  new WP_Post(['ID'=>1,'post_title'=>'永山本家酒造場','post_name'=>'taka','post_type'=>'brewery']),
  new WP_Post(['ID'=>3,'post_title'=>'TORASAKE mini','post_name'=>'torasake-mini','post_type'=>'event']),
  new WP_Post(['ID'=>7,'post_title'=>'虎ノ門ヒルズ','post_name'=>'sp-a','post_type'=>'sponsor']),
];
require get_template_directory() . '/functions.php'; do_action('after_setup_theme'); do_action('init');
$T['context']='single-event'; $T['loop']=[get_post(3)]; $T['current']=get_post(3);
$T['fields']=['event_status'=>'開催予定','event_date'=>'20261016','event_time_range'=>'17:00-23:00',
  'edition_pill'=>'第3回 ミニイベント','venue_name'=>'T-MARKET','venue_address'=>'港区虎ノ門','venue_floor'=>'B2F',
  'breweries'=>[1],'sponsors'=>[7],'tier'=>'title','logo'=>['ID'=>9],
  'ticket_price'=>3000,'ticket_note'=>'コイン付き','ticket_url'=>'https://peatix.com/event/5186533?utm_source=hp',
  'howto_steps'=>[['title'=>'受付','body'=>'会場にて。']],'theme_body'=>'<p>テーマ。</p>'];
ob_start(); include get_template_directory() . '/single-event.php'; $h=ob_get_clean();

$checks = [
  'tpl-flag（差し替え目印）を出力しない' => !str_contains($h,'tpl-flag'),
  'event 配下なので価格を出す（CLAUDE.md 許可）' => str_contains($h,'¥3,000'),
  'Peatix の utm_source を保持' => str_contains($h,'utm_source=hp'),
  '開催回バッジ pill-mini' => str_contains($h,'pill pill-mini'),
  '会場カード mono-list' => str_contains($h,'mono-list') && str_contains($h,'フロア:B2F'),
  'スポンサーを title ティアに振り分け' => str_contains($h,'sponsor-grid-title') && str_contains($h,'title-slot'),
  '参加方法の連番' => str_contains($h,'howto-num'),
];
foreach ($checks as $k=>$v) { printf("  %s %s\n", $v?'✓':'✗', $k); }
exit(in_array(false,$checks,true)?1:0);
