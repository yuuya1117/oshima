<?php
require __DIR__ . '/stubs.php';
$T = &$GLOBALS['T'];
$T['posts'] = [
    new WP_Post(['ID'=>4,'post_title'=>'TORASAKE 2026','post_name'=>'torasake-2026','post_type'=>'past_event']),
    new WP_Post(['ID'=>5,'post_title'=>'お知らせ記事','post_name'=>'news-1','post_type'=>'post']),
];
require get_template_directory() . '/functions.php';
do_action('after_setup_theme'); do_action('init');
$T['context']='front'; $T['fields']=[]; $T['loop']=[];
ob_start();
try { include get_template_directory() . '/front-page.php'; $h=ob_get_clean(); }
catch (Throwable $e){ ob_end_clean(); echo "  ✗ ".$e->getMessage()."\n"; exit(1); }
$ok = str_contains($h,'次回開催 準備中') && str_contains($h,'準備を進めています');
printf("  %s front (次回準備中フォールバック)  %d bytes  |  フォールバック文言: %s\n", $ok?'✓':'✗', strlen($h), $ok?'出力あり':'出力なし');
exit($ok?0:1);
