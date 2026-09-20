<?php
require __DIR__ . '/stubs.php';

$T = &$GLOBALS['T'];

// ダミーデータ
$T['posts'] = [
    new WP_Post(['ID'=>1,'post_title'=>'永山本家酒造場','post_name'=>'taka','post_type'=>'brewery']),
    new WP_Post(['ID'=>2,'post_title'=>'大信州酒造','post_name'=>'daishinshu','post_type'=>'brewery']),
    new WP_Post(['ID'=>3,'post_title'=>'TORASAKE mini','post_name'=>'torasake-mini','post_type'=>'event']),
    new WP_Post(['ID'=>4,'post_title'=>'TORASAKE 2026','post_name'=>'torasake-2026','post_type'=>'past_event']),
    new WP_Post(['ID'=>5,'post_title'=>'盛況のうちに終了いたしました','post_name'=>'2026-07-03-event-report','post_type'=>'post']),
];

require get_template_directory() . '/functions.php';
do_action('after_setup_theme');
do_action('init');

$scenarios = [
  'front (イベントあり)' => ['context'=>'front','tpl'=>'front-page.php','fields'=>[
      'event_status'=>'開催予定','event_date'=>'20261016','event_start_time'=>'17:00',
      'event_time_range'=>'17:00-23:00','venue_name'=>'虎ノ門ヒルズ ステーションタワー B2F T-MARKET',
      'ticker_text'=>'🎟 チケット販売中','key_visual'=>['ID'=>7,'url'=>'kv.jpg'],
      'ticket_url'=>'https://peatix.com/event/5186533?utm_source=hp',
      'lead_heading'=>'造り手と話して、<br>気になる一杯に出会う。','lead_copy'=>'全国から<span class="em">10蔵</span>が集結。',
      'lead_body'=>'今回は300〜500名規模。',
      'lead_points'=>[['label_en'=>'SCALE','value'=>'300-500名','body'=>'蔵人とゆっくり話せる。']],
      'outline'=>[['key_en'=>'DATE & TIME','value'=>'2026年10月16日(金)','note'=>'L.O. 22:30']],
      'breweries'=>[1,2],'brewery_section_sub'=>'造り手が会場に立ちます。',
      'food_image'=>['ID'=>8,'alt'=>'会場'],'food_heading'=>'日本酒に合う料理を。','food_body'=>'各飲食店でご用意します。',
      'tickets'=>[['label_en'=>'EARLY BIRD','name'=>'早割チケット','price'=>2500,'entry_time'=>'17:00','limit_label'=>'限定100枚','is_featured'=>1,'note'=>'いちばんお得。']],
      'ticket_includes'=>[['text'=>'専用500円コイン × 3枚','note'=>'［1,500円分］']],
      'ticket_notes'=>"※追加のお酒は別途。\n※入場制限の場合あり。",
      'sticky_cta_text'=>'TORASAKE mini <b>10/16(金)</b> 販売中',
      'report_link'=>null,
  ]],
  'front (次回準備中)' => ['context'=>'front','tpl'=>'front-page.php','fields'=>[], 'no_event'=>true],
  'archive-brewery'    => ['context'=>'archive-brewery','tpl'=>'archive-brewery.php','loop'=>[1,2],'fields'=>[
      'brand_name'=>'貴','lineup_summary'=>'貴 純米大吟醸','logo'=>['ID'=>9],
  ]],
  'single-brewery (銘柄あり)' => ['context'=>'single-brewery','tpl'=>'single-brewery.php','loop'=>[1],'fields'=>[
      'brand_name'=>'貴','brand_name_en'=>'TAKA','brand_alias'=>'Domaine Taka','region_en'=>'Yamaguchi','city'=>'宇部市',
      'founded_year'=>'1888年（明治21年）','tagline'=>'<strong>ドメーヌ型酒造り</strong>を掲げる蔵。','logo'=>['ID'=>9,'url'=>'l.jpg'],
      'official_url'=>'https://www.domainetaka.com/','instagram_url'=>'https://www.instagram.com/taka_domaine/',
      'lineup_section_label'=>'Featured Sake','lineup_section_title'=>'出展する3銘柄',
      'sake_lineup'=>[['number_label'=>'No.01 ・ 微発泡','bottle_image'=>['ID'=>10],'type_label'=>'特別純米','name_ja'=>'貴 スパークリング','name_en'=>'TAKA SPARKLING','description'=>'きめ細やかな泡。','tags'=>[['tag'=>'微発泡'],['tag'=>'爽快']],'accent_hue'=>230]],
      'lineup_badge'=>'☀ 夏の3本を。','story_heading'=>'地の米、地の水で醸す。','story_body'=>'<p>1888年創業。</p>',
      'meta_table'=>[['label'=>'所在地','value'=>'山口県 宇部市'],['label'=>'創業','value'=>'1888年']],
      'event_history'=>[3,4],
  ]],
  'single-brewery (銘柄未定)' => ['context'=>'single-brewery','tpl'=>'single-brewery.php','loop'=>[2],'fields'=>[
      'region_en'=>'Nagano','city'=>'松本市','logo'=>['ID'=>9],
      'flagship_name'=>'大信州','flagship_description'=>'当日の出展銘柄は決まり次第お知らせします。',
      'meta_table'=>[['label'=>'所在地','value'=>'長野県松本市島立']],
  ]],
  'home (news一覧)' => ['context'=>'home','tpl'=>'home.php','loop'=>[5],'fields'=>[]],
  'single (news記事)' => ['context'=>'single','tpl'=>'single.php','loop'=>[5],'fields'=>[]],
  'index (404)' => ['context'=>'404','tpl'=>'index.php','loop'=>[],'fields'=>[]],
];

$fail = 0;
foreach ($scenarios as $label => $s) {
    $T['context'] = $s['context'];
    $T['fields']  = $s['fields'];
    $T['loop']    = array_map(fn($id)=>get_post($id), $s['loop'] ?? []);
    $T['current'] = $T['loop'][0] ?? null;
    if (!empty($s['no_event'])) { $T['fields']['event_status'] = null; }

    // torasake_current_event() の static キャッシュを跨がせない
    foreach (['torasake_current_event','torasake_latest_edition'] as $fn) {}

    ob_start();
    try {
        include get_template_directory() . '/' . $s['tpl'];
        $html = ob_get_clean();
        $len = strlen($html);
        printf("  ✓ %-28s %6d bytes\n", $label, $len);
    } catch (Throwable $e) {
        ob_end_clean();
        printf("  ✗ %-28s %s: %s @ %s:%d\n", $label, get_class($e), $e->getMessage(), basename($e->getFile()), $e->getLine());
        $fail++;
    }
}
exit($fail > 0 ? 1 : 0);
