<?php
require __DIR__ . '/stubs.php';
$T = &$GLOBALS['T'];
$T['posts'] = [
  new WP_Post(['ID'=>1,'post_title'=>'永山本家酒造場','post_name'=>'taka','post_type'=>'brewery']),
  new WP_Post(['ID'=>5,'post_title'=>'開催報告','post_name'=>'2026-07-03-event-report','post_type'=>'post']),
  // WordPress は非ASCIIスラッグを post_name にパーセントエンコードで保持する
  new WP_Post(['ID'=>6,'post_title'=>'本日開幕','post_name'=>'2026-06-27-'.rawurlencode('当日券'),'post_type'=>'post']),
  new WP_Post(['ID'=>7,'post_title'=>'開催決定','post_name'=>'2026-05-21-'.rawurlencode('開催決定'),'post_type'=>'post']),
  new WP_Post(['ID'=>8,'post_title'=>'下書き','post_name'=>'draft','post_type'=>'post','post_status'=>'draft']),
  new WP_Post(['ID'=>9,'post_title'=>'過去開催','post_name'=>'torasake-2026','post_type'=>'past_event']),
  new WP_Post(['ID'=>10,'post_title'=>'TORASAKE mini','post_name'=>'torasake-mini','post_type'=>'event']),
  new WP_Post(['ID'=>11,'post_title'=>'インタビュー','post_name'=>'taka-iv','post_type'=>'blog_post']),
];
require get_template_directory() . '/functions.php';
do_action('after_setup_theme'); do_action('init');

$fail = 0;
function check(string $label, $got, $want) {
  global $fail;
  $ok = ($got === $want);
  if (!$ok) $fail++;
  printf("  %s %-46s %s\n", $ok?'✓':'✗', $label, $ok ? $got : "got: $got  /  want: $want");
}

echo "--- リライトルール（方式A）---\n";
$rules = $GLOBALS['T']['rules'] ?? [];
foreach ([
  '^news/([^/]+)\.html$'      => 'index.php?post_type=post&name=$matches[1]',
  '^breweries/?$'             => 'index.php?post_type=brewery',
  '^breweries/([^/]+)\.html$' => 'index.php?post_type=brewery&brewery=$matches[1]&name=$matches[1]',
  '^events/?$'                => 'index.php?post_type=event',
  '^events/([^/]+)\.html$'    => 'index.php?post_type=event&event=$matches[1]&name=$matches[1]',
  '^blog/?$'                  => 'index.php?post_type=blog_post',
  '^blog/([^/]+)/?$'          => 'index.php?post_type=blog_post&blog_post=$matches[1]&name=$matches[1]',
  '^past/([^/]+)/?$'          => 'index.php?post_type=past_event&past_event=$matches[1]&name=$matches[1]',
] as $re => $want) {
  check("rule: $re", $rules[$re] ?? '(未登録)', $want);
}

echo "\n--- パーマリンク出力（.html 付きが正規URL）---\n";
check('post → /news/{slug}.html',
  apply_filters('post_link','/fallback/',get_post(5)),
  'https://torasake.test/news/2026-07-03-event-report.html');
check('brewery → /breweries/{slug}.html',
  apply_filters('post_type_link','/fallback/',get_post(1)),
  'https://torasake.test/breweries/taka.html');
check('past_event → /past/{slug}/',
  apply_filters('post_type_link','/fallback/',get_post(9)),
  'https://torasake.test/past/torasake-2026/');
check('event → /events/{slug}.html',
  apply_filters('post_type_link','/fallback/',get_post(10)),
  'https://torasake.test/events/torasake-mini.html');
check('blog_post → /blog/{slug}/',
  apply_filters('post_type_link','/fallback/',get_post(11)),
  'https://torasake.test/blog/taka-iv/');

echo "\n--- 日本語スラッグ（二重エンコードしないこと）---\n";
$u1 = apply_filters('post_link','/f/',get_post(6));
$u2 = apply_filters('post_link','/f/',get_post(7));
check('当日券', $u1, 'https://torasake.test/news/2026-06-27-%E5%BD%93%E6%97%A5%E5%88%B8.html');
check('開催決定', $u2, 'https://torasake.test/news/2026-05-21-%E9%96%8B%E5%82%AC%E6%B1%BA%E5%AE%9A.html');
check('デコードすると元の日本語に戻る', urldecode($u1), 'https://torasake.test/news/2026-06-27-当日券.html');
printf("  %s %-46s %s\n", str_contains($u1,'%25')?'✗':'✓', '二重エンコード（%25）が無い', str_contains($u1,'%25')?'混入あり':'なし');

echo "\n--- 下書きは既定のパーマリンクのまま ---\n";
check('draft はフィルタを通さない', apply_filters('post_link','/fallback/',get_post(8)), '/fallback/');

echo "\n--- redirect_canonical の無効化（.html が飛ばされないこと）---\n";
foreach (['single'=>'post','single-brewery'=>'brewery','single-event'=>'event'] as $ctx=>$pt) {
  $T['context']=$ctx;
  check("$pt 単体で無効化", var_export(apply_filters('redirect_canonical','https://x/redirected','https://x/orig'),true), 'false');
}
$T['context']='home';
check('それ以外では有効のまま', apply_filters('redirect_canonical','https://x/redirected','https://x/orig'), 'https://x/redirected');

echo "\n--- 旧 index.html の 301 マップ ---\n";
$src = file_get_contents(get_template_directory() . '/inc/rewrite.php');
foreach (['/index.html'=>'/', '/news/index.html'=>'/news/', '/past/index.html'=>'/past/',
          '/breweries/index.html'=>'/breweries/', '/blog/index.html'=>'/blog/',
          '/events/index.html'=>'/events/', '/sponsors/index.html'=>'/sponsors/'] as $from=>$to) {
  $has = str_contains($src, "'$from'") && str_contains($src, "'$to'");
  printf("  %s %-46s\n", $has?'✓':'✗', "$from → $to");
  if(!$has) $fail++;
}

echo "\n--- アーカイブリンク ---\n";
check('brewery archive', apply_filters('post_type_archive_link',false,'brewery'), 'https://torasake.test/breweries/');
check('event archive',   apply_filters('post_type_archive_link',false,'event'),   'https://torasake.test/events/');
check('blog archive',    apply_filters('post_type_archive_link',false,'blog_post'), 'https://torasake.test/blog/');

echo "\n", $fail ? "  ✗ 失敗 $fail 件\n" : "  ✓ 全項目パス\n";
exit($fail?1:0);
