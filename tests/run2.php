<?php
require __DIR__ . '/stubs.php';
$T = &$GLOBALS['T'];
$T['posts'] = [
    new WP_Post(['ID'=>1,'post_title'=>'永山本家酒造場','post_name'=>'taka','post_type'=>'brewery']),
    new WP_Post(['ID'=>2,'post_title'=>'大信州酒造','post_name'=>'daishinshu','post_type'=>'brewery']),
    new WP_Post(['ID'=>3,'post_title'=>'TORASAKE mini','post_name'=>'torasake-mini','post_type'=>'event']),
    new WP_Post(['ID'=>4,'post_title'=>'日本酒虎の巻 Vol.2','post_name'=>'toranomaki-vol2','post_type'=>'past_event']),
    new WP_Post(['ID'=>5,'post_title'=>'蔵元インタビュー','post_name'=>'taka-interview','post_type'=>'blog_post']),
    new WP_Post(['ID'=>6,'post_title'=>'協賛について','post_name'=>'sponsors','post_type'=>'page']),
    new WP_Post(['ID'=>7,'post_title'=>'虎ノ門ヒルズ','post_name'=>'sponsor-a','post_type'=>'sponsor']),
];
require get_template_directory() . '/functions.php';
do_action('after_setup_theme'); do_action('init');

$scenarios = [
 'single-event' => ['ctx'=>'single-event','tpl'=>'single-event.php','loop'=>[3],'f'=>[
    'event_status'=>'開催予定','event_date'=>'20261016','event_time_range'=>'17:00-23:00',
    'edition_pill'=>'第3回 ミニイベント','venue_name'=>'虎ノ門ヒルズ ステーションタワー B2F T-MARKET',
    'venue_address'=>'東京都港区虎ノ門2-6-2','venue_access'=>'虎ノ門ヒルズ駅 直結','venue_floor'=>'B2F',
    'venue_image'=>['ID'=>8],'venue_map_url'=>'https://maps.example/',
    'theme_body'=>'<p>今回のテーマ本文。</p>','breweries'=>[1,2],'brewery_section_sub'=>'厳選した10蔵。',
    'howto_steps'=>[['title'=>'受付','body'=>'会場受付にて。'],['title'=>'試飲','body'=>'蔵を巡る。']],
    'sponsors'=>[7],'sponsor_section_sub'=>'ご協賛により開催しています。','tier'=>'title',
    'ticket_price'=>3000,'ticket_note'=>"専用コイン付き。\n枚数限定。",
    'ticket_url'=>'https://peatix.com/event/5186533?utm_source=hp','logo'=>['ID'=>9],'link_url'=>'https://example.com/',
 ]],
 'single-past_event' => ['ctx'=>'single-past','tpl'=>'single-past_event.php','loop'=>[4],'f'=>[
    'event_date'=>'20251008','venue_name'=>'虎ノ門ヒルズ ステーションタワー',
    'stats'=>[['number'=>'17','unit_suffix'=>'蔵','label'=>'参加酒蔵'],['number'=>'800','unit_suffix'=>'名','label'=>'来場者数']],
    'info_list'=>[['key_en'=>'EVENT','value'=>'日本酒虎の巻 Vol.2'],['key_en'=>'MEDIA','value'=>'SAKETIMES','url'=>'https://jp.sake-times.com/']],
    'report_heading'=>'当日の様子','report_body'=>'<p class="body">17蔵が集結。</p>',
    'gallery'=>[['ID'=>11,'alt'=>'会場'],['ID'=>12,'alt'=>'蔵元']],
    'closing_heading'=>'そして、2026年へ','closing_body'=>'<p class="body">次なるステージへ。</p>',
 ]],
 'single-blog_post' => ['ctx'=>'single-blog','tpl'=>'single-blog_post.php','loop'=>[5],'f'=>[
    'category_label'=>'蔵元インタビュー','youtube_video_id'=>'dQw4w9WgXcQ',
    'video_caption'=>'永山本家酒造場 蔵元インタビュー','related_brewery'=>[1],
    'pull_quote'=>'「地の米、地の水で醸す。」','pull_quote_who'=>'永山本家酒造場 蔵元',
 ]],
 'archive-blog_post' => ['ctx'=>'archive-blog','tpl'=>'archive-blog_post.php','loop'=>[5],'f'=>['related_brewery'=>[1]]],
 'page-sponsors' => ['ctx'=>'page-sponsors','tpl'=>'page-sponsors.php','loop'=>[6],'f'=>[
    'hero_heading'=>'TORASAKEと共に、日本酒文化を届ける。','hero_body'=>'継続開催しています。',
    'sponsor_tiers'=>[['tier'=>'title','badge'=>'タイトルスポンサー','name'=>'◯◯ presents TORASAKE',
                       'benefits'=>[['text'=>'タイトル冠称'],['text'=>'ロゴ掲出']]]],
    'sponsor_flow'=>[['title'=>'お問い合わせ','body'=>'フォームよりご相談ください。']],
    'contact_body'=>'お気軽にどうぞ。','contact_email'=>'info@torasake.com',
    'tier'=>'title','logo'=>['ID'=>9],'link_url'=>'https://example.com/',
 ]],
];

$fail = 0;
foreach ($scenarios as $label => $s) {
    $T['context'] = $s['ctx'];
    $T['fields']  = $s['f'];
    unset($T['loop_all']);
    $T['loop']    = array_map(fn($i)=>get_post($i), $s['loop']);
    $T['current'] = $T['loop'][0] ?? null;
    ob_start();
    try {
        include get_template_directory() . '/' . $s['tpl'];
        $html = ob_get_clean();
        printf("  ✓ %-22s %6d bytes\n", $label, strlen($html));
    } catch (Throwable $e) {
        ob_end_clean();
        printf("  ✗ %-22s %s: %s @ %s:%d\n", $label, get_class($e), $e->getMessage(), basename($e->getFile()), $e->getLine());
        $fail++;
    }
}
exit($fail?1:0);
