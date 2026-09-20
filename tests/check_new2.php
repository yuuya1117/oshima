<?php
require __DIR__ . '/stubs.php';
$T=&$GLOBALS['T'];
$T['posts']=[ new WP_Post(['ID'=>1,'post_title'=>'永山本家酒造場','post_name'=>'taka','post_type'=>'brewery']),
              new WP_Post(['ID'=>5,'post_title'=>'インタビュー','post_name'=>'iv','post_type'=>'blog_post']),
              new WP_Post(['ID'=>6,'post_title'=>'協賛について','post_name'=>'sponsors','post_type'=>'page']) ];
require get_template_directory() . '/functions.php'; do_action('after_setup_theme'); do_action('init');

$T['context']='single-blog'; $T['loop']=[get_post(5)]; $T['current']=get_post(5);
$T['fields']=['category_label'=>'蔵元インタビュー','youtube_video_id'=>'dQw4w9WgXcQ',
  'video_caption'=>'蔵元インタビュー','related_brewery'=>[1],'pull_quote'=>'「地の米で醸す。」','pull_quote_who'=>'蔵元'];
ob_start(); include get_template_directory() . '/single-blog_post.php'; $b=ob_get_clean();
foreach ([
 'YouTube を nocookie で埋め込む'=>str_contains($b,'youtube-nocookie.com/embed/dQw4w9WgXcQ'),
 'プルクオート'=>str_contains($b,'pull-quote')&&str_contains($b,'class="who"'),
 '関連酒蔵カード'=>str_contains($b,'related-brewery'),
 'tpl-flag を出力しない'=>!str_contains($b,'tpl-flag'),
] as $k=>$v) printf("  %s %s\n",$v?'✓':'✗',$k);

$T['context']='page-sponsors'; $T['loop']=[get_post(6)]; $T['current']=get_post(6);
$T['fields']=['hero_heading'=>'日本酒文化を届ける。','sponsor_tiers'=>[['tier'=>'title','badge'=>'タイトル','name'=>'presents','benefits'=>[['text'=>'冠称']]]],
  'sponsor_flow'=>[['title'=>'お問い合わせ','body'=>'ご相談ください。']],'contact_email'=>'info@torasake.com'];
ob_start(); include get_template_directory() . '/page-sponsors.php'; $s=ob_get_clean();
foreach ([
 'タイトルティアのカード'=>str_contains($s,'tier-card title-tier'),
 '流れの連番'=>str_contains($s,'flow-num'),
 'mailto リンク'=>str_contains($s,'mailto:info@torasake.com'),
] as $k=>$v) printf("  %s %s\n",$v?'✓':'✗',$k);
