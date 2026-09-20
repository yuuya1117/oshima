<?php
/**
 * ブラウザで開けるプレビューHTMLを書き出す。
 *
 * WordPress を起動できない環境でデザインを目視確認するためのもの。
 * テンプレートを実行し、CSSをインライン化して1枚のHTMLにまとめる。
 * 画像は docs/design_handoff_wordpress/uploads/ の実ファイルを指す。
 *
 *   php tests/preview.php [出力先ディレクトリ]
 *
 * 既定の出力先は tests/.preview/（.gitignore 済み）。
 */
require __DIR__ . '/stubs.php';

$out_dir = $argv[1] ?? __DIR__ . '/.preview';
@mkdir($out_dir, 0777, true);

$repo    = dirname(__DIR__);
$uploads = 'uploads';  // 出力先からの相対パス

$T = &$GLOBALS['T'];
$T['posts'] = [
    new WP_Post(['ID'=>1,'post_title'=>'永山本家酒造場','post_name'=>'taka','post_type'=>'brewery']),
    new WP_Post(['ID'=>2,'post_title'=>'大信州酒造','post_name'=>'daishinshu','post_type'=>'brewery']),
    new WP_Post(['ID'=>3,'post_title'=>'TORASAKE mini','post_name'=>'torasake-mini','post_type'=>'event']),
    new WP_Post(['ID'=>4,'post_title'=>'TORASAKE -虎ノ酒- 2026','post_name'=>'torasake-2026','post_type'=>'event']),
    new WP_Post(['ID'=>5,'post_title'=>'日本酒虎の巻 Vol.2','post_name'=>'toranomaki-vol2','post_type'=>'event']),
    new WP_Post(['ID'=>11,'post_title'=>'永山本家酒造場「貴」に込めた想い','post_name'=>'taka-iv','post_type'=>'blog_post']),
    new WP_Post(['ID'=>12,'post_title'=>'大信州酒造 ─ 北アルプスの伏流水と、全量契約栽培の米で','post_name'=>'daishinshu-iv','post_type'=>'blog_post']),
    new WP_Post(['ID'=>13,'post_title'=>'T-MARKETの料理人に聞く、日本酒に合わせるということ','post_name'=>'tmarket-iv','post_type'=>'blog_post']),
    new WP_Post(['ID'=>14,'post_title'=>'TORASAKE 2026 をつくった人たち','post_name'=>'staff-iv','post_type'=>'blog_post']),
];

// 実画像の割り当て
$T['img'] = [
    'post3'  => "$uploads/OOSHIMA-KV-0903_16-9.jpg",
    'post11' => "$uploads/torasake2026daiseikyou.jpg",
    'post12' => "$uploads/toranomonhirls.webp",
    'post14' => "$uploads/flyer-keyvisual.jpg",
    '*'      => "$uploads/OOSHIMA-KV-0903_16-9.jpg",
];

require __DIR__ . '/stubs_bootstrap.php';

/**
 * テンプレートを実行してプレビューHTMLを書き出す。
 *
 * @param string            $file    出力ファイル名。
 * @param string            $tpl     テンプレート。
 * @param string            $ctx     スタブの文脈。
 * @param array<int,int>    $loop    ループに流す投稿ID。
 * @param array<int,mixed>  $byPost  投稿ごとのACF値。
 * @param array<int,string> $css     読み込むCSS（assets/css/ 配下・拡張子なし）。
 * @param string            $note    プレビュー上部に出す注記。
 */
function preview( string $file, string $tpl, string $ctx, array $loop, array $byPost, array $css, string $note ): void {
    global $out_dir;
    $T = &$GLOBALS['T'];

    $T['context'] = $ctx;
    $T['fields'] = [];
    $T['fields_by_post'] = $byPost;
    unset($T['loop_all']);
    $T['loop'] = array_map(fn($i) => get_post($i), $loop);
    $T['current'] = $T['loop'][0] ?? null;

    ob_start();
    include get_template_directory() . '/' . $tpl;
    $html = ob_get_clean();

    // <head> にCSSをインライン化し、フォントは本番と同じものを読む
    $styles = '';
    foreach ($css as $name) {
        $styles .= "\n/* ===== assets/css/$name.css ===== */\n"
                 . file_get_contents(get_template_directory() . "/assets/css/$name.css");
    }

    $head = '<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">'
          . '<title>[プレビュー] ' . htmlspecialchars($tpl) . '</title>'
          . '<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@400;500;600;700;900'
          . '&family=Noto+Sans+JP:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">'
          . '<style>' . $styles . "\n"
          . '.__note{position:fixed;left:12px;bottom:12px;z-index:999;background:#0d1929;color:#f0d9a8;'
          . 'font-family:"Noto Sans JP",sans-serif;font-size:.7rem;letter-spacing:.06em;line-height:1.7;'
          . 'padding:.7rem 1rem;border-radius:6px;box-shadow:0 8px 24px rgba(0,0,0,.25);max-width:330px}'
          . '</style>';

    // スタブが出した <head>/<body> を差し替える
    $html = preg_replace('#<head>.*?</head>#s', "<head>$head</head>", $html, 1);
    $html = str_replace('</body>', '<div class="__note">' . $note . '</div></body>', $html);

    // ロゴ等の絶対URLをローカルの uploads/ に向ける（プレビューで画像を出すため）
    $html = str_replace('https://torasake.test/uploads/', 'uploads/', $html);

    file_put_contents("$out_dir/$file", $html);
    printf("  ✓ %-28s %s\n", $file, $tpl);
}

preview(
    'events.html', 'archive-event.php', 'archive-event',
    [3, 4, 5],
    [
        3 => ['event_status'=>'開催予定','event_date'=>'20261016',
              'venue_name'=>'虎ノ門ヒルズ ステーションタワー B2F T-MARKET',
              'breweries'=>array_fill(0, 10, 1)],
        4 => ['event_status'=>'終了','event_date'=>'20260627',
              'venue_name'=>'虎ノ門ヒルズ ステーションタワー'],
        5 => ['event_status'=>'終了','event_date'=>'20251008',
              'venue_name'=>'虎ノ門ヒルズ ステーションタワー'],
    ],
    ['news-archive', 'event-archive'],
    'プレビュー / archive-event.php<br>開催予定は紺のフィーチャーカード（top-page の .arch）、<br>終了した回は行（brewery-style の .eh-row）。'
);

preview(
    'blog.html', 'archive-blog_post.php', 'archive-blog',
    [11, 12, 13, 14],
    [
        11 => ['category_label'=>'蔵元インタビュー','youtube_video_id'=>'dQw4w9WgXcQ','related_brewery'=>[1]],
        12 => ['category_label'=>'蔵元インタビュー','related_brewery'=>[2]],
        13 => ['category_label'=>'会場レポート'],
        14 => ['category_label'=>'運営の裏側','youtube_video_id'=>'dQw4w9WgXcQ'],
    ],
    ['news-archive', 'blog-archive'],
    'プレビュー / archive-blog_post.php<br>カード構造は top-page の .news-card、<br>金バッジは blog-single の .article-cat。<br>動画つきの記事だけ再生マークが出る。'
);

echo "\n  出力先: $out_dir\n";
