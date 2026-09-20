<?php
/**
 * ブラウザで開けるプレビューHTMLを全テンプレート分書き出す。
 *
 * WordPress を起動できない環境でデザインを目視確認するためのもの。
 * テンプレートを実行し、CSSをインライン化して1枚のHTMLにまとめる。
 * 画像は docs/design_handoff_wordpress/uploads/ の実ファイルを指す。
 *
 *   php tests/preview.php [出力先ディレクトリ]
 *
 * 既定の出力先は tests/.preview/（.gitignore 済み）。
 * 生成後 tests/.preview/index.html を開くと全ページを行き来できる。
 *
 * 本物の WordPress で見る手順は README.md の「ローカルで動かす」を参照。
 */
require __DIR__ . '/stubs.php';

$out_dir = $argv[1] ?? __DIR__ . '/.preview';
@mkdir($out_dir, 0777, true);

// 参照HTMLの画像をプレビュー用にコピーしておく
$uploads_src = dirname(__DIR__) . '/docs/design_handoff_wordpress/uploads';
$uploads_dst = "$out_dir/uploads";
if (! is_dir($uploads_dst)) {
    @mkdir($uploads_dst, 0777, true);
    foreach (glob("$uploads_src/*") as $f) {
        copy($f, $uploads_dst . '/' . basename($f));
    }
}

$T = &$GLOBALS['T'];

// ── ダミーの投稿 ──────────────────────────────────────
$T['posts'] = [
    // 酒蔵
    new WP_Post(['ID'=>1,'post_title'=>'永山本家酒造場','post_name'=>'taka','post_type'=>'brewery']),
    new WP_Post(['ID'=>2,'post_title'=>'大信州酒造','post_name'=>'daishinshu','post_type'=>'brewery']),
    new WP_Post(['ID'=>3,'post_title'=>'丸石醸造','post_name'=>'nito','post_type'=>'brewery']),
    new WP_Post(['ID'=>4,'post_title'=>'成龍酒造','post_name'=>'iyokagiya','post_type'=>'brewery']),
    new WP_Post(['ID'=>5,'post_title'=>'豊国酒造','post_name'=>'ippoki','post_type'=>'brewery']),
    new WP_Post(['ID'=>6,'post_title'=>'高千代酒造','post_name'=>'takachiyo','post_type'=>'brewery']),
    new WP_Post(['ID'=>7,'post_title'=>'農口尚彦研究所','post_name'=>'noguchi-naohiko','post_type'=>'brewery']),
    new WP_Post(['ID'=>8,'post_title'=>'豊島屋酒造','post_name'=>'okunokami','post_type'=>'brewery']),
    new WP_Post(['ID'=>9,'post_title'=>'富美菊酒造','post_name'=>'haneya','post_type'=>'brewery']),
    new WP_Post(['ID'=>10,'post_title'=>'小野酒造店','post_name'=>'yoakemae','post_type'=>'brewery']),
    // イベント
    new WP_Post(['ID'=>30,'post_title'=>'TORASAKE mini','post_name'=>'torasake-mini','post_type'=>'event']),
    new WP_Post(['ID'=>31,'post_title'=>'TORASAKE -虎ノ酒- 2026','post_name'=>'torasake-2026','post_type'=>'event']),
    new WP_Post(['ID'=>32,'post_title'=>'日本酒虎の巻 Vol.2','post_name'=>'toranomaki-vol2','post_type'=>'event']),
    // 過去開催
    new WP_Post(['ID'=>40,'post_title'=>'日本酒虎の巻 Vol.2','post_name'=>'toranomaki-vol2','post_type'=>'past_event']),
    // お知らせ
    new WP_Post(['ID'=>50,'post_title'=>'盛況のうちに終了いたしました ─ 開催報告とお礼','post_name'=>'2026-07-03-event-report','post_type'=>'post']),
    new WP_Post(['ID'=>51,'post_title'=>'本日いよいよ開幕！当日券販売のお知らせ','post_name'=>'2026-06-27-当日券','post_type'=>'post']),
    new WP_Post(['ID'=>52,'post_title'=>'酒蔵 × 飲食店 マッチング31組 発表！','post_name'=>'2026-05-28-matching','post_type'=>'post']),
    // ブログ
    new WP_Post(['ID'=>60,'post_title'=>'永山本家酒造場「貴」に込めた想い','post_name'=>'taka-iv','post_type'=>'blog_post']),
    new WP_Post(['ID'=>61,'post_title'=>'大信州酒造 ─ 北アルプスの伏流水と、全量契約栽培の米で','post_name'=>'daishinshu-iv','post_type'=>'blog_post']),
    new WP_Post(['ID'=>62,'post_title'=>'T-MARKETの料理人に聞く、日本酒に合わせるということ','post_name'=>'tmarket-iv','post_type'=>'blog_post']),
    new WP_Post(['ID'=>63,'post_title'=>'TORASAKE 2026 をつくった人たち','post_name'=>'staff-iv','post_type'=>'blog_post']),
    // 協賛
    new WP_Post(['ID'=>70,'post_title'=>'虎ノ門ヒルズ','post_name'=>'toranomon-hills','post_type'=>'sponsor']),
    new WP_Post(['ID'=>71,'post_title'=>'T-MARKET','post_name'=>'tmarket','post_type'=>'sponsor']),
    new WP_Post(['ID'=>72,'post_title'=>'SAKETIMES','post_name'=>'saketimes','post_type'=>'sponsor']),
    // 固定ページ
    new WP_Post(['ID'=>80,'post_title'=>'協賛について','post_name'=>'sponsors','post_type'=>'page']),
];

$U = 'uploads';
$T['img'] = [
    // アイキャッチ（post{ID}）
    'post30' => "$U/OOSHIMA-KV-0903_16-9.jpg",
    'post31' => "$U/torasake2026daiseikyou.jpg",
    'post40' => "$U/torasake2026daiseikyou.jpg",
    'post50' => "$U/torasake2026daiseikyou.jpg",
    'post51' => "$U/flyer-keyvisual.jpg",
    'post52' => "$U/flyer-keyvisual.jpg",
    'post60' => "$U/torasake2026daiseikyou.jpg",
    'post61' => "$U/toranomonhirls.webp",
    'post63' => "$U/flyer-keyvisual.jpg",
    // 添付ID
    100 => "$U/OOSHIMA-KV-0903_16-9.jpg",      // KV
    101 => "$U/toranomonhirls.webp",            // 会場
    102 => "$U/torasake2026daiseikyou.jpg",     // 会場の様子
    103 => "$U/flyer-keyvisual.jpg",
    // 蔵ロゴ
    201 => "$U/taka_logo.jpg",
    202 => "$U/大信州ロゴ-5bec2758.jpg",
    203 => "$U/二兎ロゴ２.webp",
    204 => "$U/賀儀屋ロゴ.png",
    205 => "$U/一歩己.jpg",
    206 => "$U/たかちよ高千代ロゴ.jpg",
    207 => "$U/農口尚彦研究所ロゴ.png",
    208 => "$U/屋守ロゴ.jpeg",
    209 => "$U/haneya_logo.jpg",
    210 => "$U/夜明け前ロゴ-9aa0037b.jpg",
    '*'  => "$U/OOSHIMA-KV-0903_16-9.jpg",
];

require __DIR__ . '/stubs_bootstrap.php';

/** 蔵ごとのACF値（ロゴ・県・銘柄）をまとめて作る。 */
function brewery_fields(): array {
    $rows = [
        1  => ['貴', 201, '山口県', '貴 純米大吟醸',       'Yamaguchi', '宇部市'],
        2  => ['大信州', 202, '長野県', '大信州 純米吟醸',  'Nagano',    '松本市'],
        3  => ['二兎', 203, '愛知県', '二兎 純米大吟醸',    'Aichi',     '岡崎市'],
        4  => ['伊予賀儀屋', 204, '愛媛県', '伊予賀儀屋 純米', 'Ehime',   '西条市'],
        5  => ['一歩己', 205, '福島県', '一歩己 純米',      'Fukushima', '古殿町'],
        6  => ['高千代', 206, '新潟県', '高千代 辛口純米',   'Niigata',   '南魚沼市'],
        7  => ['農口尚彦研究所', 207, '石川県', '山廃 純米',  'Ishikawa',  '小松市'],
        8  => ['屋守', 208, '東京都', '屋守 純米',          'Tokyo',     '東村山市'],
        9  => ['羽根屋', 209, '富山県', '羽根屋 純米吟醸',   'Toyama',    '富山市'],
        10 => ['夜明け前', 210, '長野県', '夜明け前 しずく採り生酒', 'Nagano', '辰野町'],
    ];
    $out = [];
    foreach ($rows as $id => [$brand, $logo, $pref, $lineup, $en, $city]) {
        $out[$id] = [
            'brand_name' => $brand,
            'logo' => ['ID' => $logo],
            'lineup_summary' => $lineup,
            'region_en' => $en,
            'city' => $city,
            '__pref' => $pref,
        ];
    }
    return $out;
}

/**
 * テンプレートを実行してプレビューHTMLを書き出す。
 *
 * @param string $file   出力ファイル名。
 * @param string $label  index に出す見出し。
 * @param string $url    本番でのURL。
 * @param string $tpl    テンプレート。
 * @param string $ctx    スタブの文脈。
 * @param array  $loop   ループに流す投稿ID。
 * @param array  $byPost 投稿ごとのACF値。
 * @param array  $css    読み込むCSS（assets/css/ 配下・拡張子なし）。
 */
function preview(string $file, string $label, string $url, string $tpl, string $ctx,
                 array $loop, array $byPost, array $css): void {
    global $out_dir, $INDEX;
    $T = &$GLOBALS['T'];

    $T['context'] = $ctx;
    $T['fields'] = [];
    $T['fields_by_post'] = $byPost;
    unset($T['loop_all']);
    $T['loop'] = array_map(fn($i) => get_post($i), $loop);
    $T['current'] = $T['loop'][0] ?? null;

    ob_start();
    try {
        include get_template_directory() . '/' . $tpl;
        $html = ob_get_clean();
    } catch (Throwable $e) {
        ob_end_clean();
        printf("  ✗ %-22s %s: %s\n", $file, get_class($e), $e->getMessage());
        return;
    }

    $styles = '';
    foreach ($css as $name) {
        $styles .= "\n/* ===== assets/css/$name.css ===== */\n"
                 . file_get_contents(get_template_directory() . "/assets/css/$name.css");
    }

    $head = '<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">'
          . '<title>[プレビュー] ' . htmlspecialchars($label) . '</title>'
          . '<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@400;500;600;700;900'
          . '&family=Noto+Sans+JP:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">'
          . '<style>' . $styles . "\n"
          . '.__bar{position:fixed;left:0;right:0;bottom:0;z-index:9999;background:#0d1929;color:#f0d9a8;'
          . 'font-family:"Noto Sans JP",sans-serif;font-size:.72rem;letter-spacing:.06em;'
          . 'padding:.6rem 1rem;display:flex;gap:1rem;align-items:center;justify-content:space-between;flex-wrap:wrap}'
          . '.__bar a{color:#f0d9a8;text-decoration:underline}'
          . '.__bar code{color:#c8a060;font-size:.72rem}'
          . 'body{padding-bottom:52px}'
          . '</style>';

    $bar = '<div class="__bar"><span>プレビュー ─ <code>' . htmlspecialchars($tpl) . '</code>'
         . '　本番URL <code>' . htmlspecialchars($url) . '</code></span>'
         . '<a href="index.html">← 一覧へ</a></div>';

    $html = preg_replace('#<head>.*?</head>#s', "<head>$head</head>", $html, 1);
    $html = str_replace('</body>', $bar . '</body>', $html);
    $html = str_replace('https://torasake.test/uploads/', 'uploads/', $html);

    file_put_contents("$out_dir/$file", $html);
    $INDEX[] = [$file, $label, $url, $tpl];
    printf("  ✓ %-22s %s\n", $file, $tpl);
}

$INDEX = [];
$BF = brewery_fields();

// 蔵の都道府県はタクソノミーから来るのでスタブ側に渡す
$T['pref_by_post'] = array_map(fn($f) => $f['__pref'], $BF);

// ── イベント（トップ・イベント詳細で使い回す）──
$event_common = [
    'event_status'=>'開催予定','event_date'=>'20261016','event_start_time'=>'17:00',
    'event_time_range'=>'17:00-23:00',
    'venue_name'=>'虎ノ門ヒルズ ステーションタワー B2F T-MARKET',
    'venue_address'=>'東京都港区虎ノ門2-6-2','venue_access'=>'東京メトロ日比谷線 虎ノ門ヒルズ駅 直結',
    'venue_floor'=>'B2F','venue_image'=>['ID'=>101],
    'key_visual'=>['ID'=>100],
    'ticker_text'=>'🎟 チケット販売中 ─ 早割[限定100枚]・30分先行入場[限定50枚]は予定枚数に達し次第終了',
    'ticket_url'=>'https://peatix.com/event/5186533?utm_source=hp',
    'edition_pill'=>'第3回 ミニイベント',
    'lead_heading'=>'造り手と話して、<br>気になる一杯に出会う。',
    'lead_copy'=>'全国から選りすぐりの<span class="em">10蔵</span>が虎ノ門ヒルズに集結。<br>おいしい料理と一緒に、日本酒を楽しむ<span class="em">秋の夜</span>を。',
    'lead_body'=>'今回は300〜500名規模のミニイベント。前回よりも酒蔵との距離が近く、一つひとつのお酒をじっくり楽しめる一夜を目指します。',
    'lead_points'=>[
        ['label_en'=>'SCALE','value'=>'300-500名','body'=>'前回より小さな規模だから、<br>蔵人とゆっくり話せる。'],
        ['label_en'=>'BREWERIES','value'=>'10蔵','body'=>'数を絞ったからこそ、<br>一蔵ずつじっくり味わえる。'],
        ['label_en'=>'PAIRING','value'=>'T-MARKET','body'=>'各飲食店が日本酒に合う<br>料理をご用意します。'],
    ],
    'outline'=>[
        ['key_en'=>'DATE &amp; TIME','value'=>'2026年10月16日(金)<br>17:00 - 23:00','note'=>'L.O. 22:30 ／ 先行入場チケットをお持ちの方は16:30から入場可能'],
        ['key_en'=>'VENUE','value'=>'虎ノ門ヒルズ ステーションタワー B2F<br>T-MARKET','note'=>'東京メトロ日比谷線 虎ノ門ヒルズ駅 直結'],
        ['key_en'=>'STYLE','value'=>'日本酒飲み歩きイベント','note'=>'300〜500名規模／全国10蔵が出展。専用コインでお酒と料理をその都度ご購入いただけます。'],
        ['key_en'=>'TICKET','value'=>'前売 3,000円 - 当日 3,500円','note'=>'早割2,500円[限定100枚]・30分先行入場3,000円[限定50枚]を販売中。'],
    ],
    'breweries'=>[4,2,5,6,3,7,8,9,10,1],
    'brewery_section_sub'=>'造り手が会場に立ちます。気になる一杯について、直接聞いてみてください。',
    'food_image'=>['ID'=>101],'food_image_position'=>'50% 26%',
    'food_heading'=>'日本酒に合う料理を、<br>T-MARKETの各飲食店で。',
    'food_body'=>'会場となるT-MARKETの各飲食店では、この日のために日本酒に合う料理をご用意します。気になるお酒と料理を、その都度購入して自由にお楽しみください。',
    'ticket_section_sub'=>'すべてのチケットに専用コイン1,500円分とオリジナルグラスが付いています。',
    'tickets'=>[
        ['label_en'=>'EARLY BIRD','name'=>'早割チケット','price'=>2500,'entry_time'=>'17:00','limit_label'=>'限定100枚','is_featured'=>1,'note'=>'いちばんお得な早割。予定枚数に達し次第、販売終了します。'],
        ['label_en'=>'EARLY ENTRY','name'=>'30分先行入場','price'=>3000,'entry_time'=>'16:30','limit_label'=>'限定50枚','note'=>'ひと足先に、気になる酒蔵を巡りたい方に。混み合う前にゆっくりと。'],
        ['label_en'=>'ADVANCE','name'=>'前売りチケット','price'=>3000,'entry_time'=>'17:00','note'=>'当日券は3,500円。前売りでのご購入がおすすめです。'],
    ],
    'ticket_includes'=>[
        ['text'=>'イベント専用500円コイン × 3枚','note'=>'［1,500円分］'],
        ['text'=>'TORASAKEオリジナルグラス'],
    ],
    'ticket_notes'=>"※追加のお酒・料理は別途ご購入いただけます。／※混雑状況により入場制限を行う場合があります。\n※早割・先行入場チケットは、予定枚数に達し次第販売終了。ぜひお早めにお求めください。",
    'sticky_cta_text'=>'TORASAKE mini <b>10/16(金)</b> 虎ノ門ヒルズ T-MARKET ─ チケット販売中',
    'theme_body'=>'<p>今回は「造り手との距離」をテーマに、規模を絞った一夜をご用意しました。蔵人と直接ことばを交わしながら、一杯ずつじっくり味わっていただけます。</p>',
    'howto_steps'=>[
        ['title'=>'受付・チケット確認','body'=>'B2F T-MARKET 入口にて、Peatixのチケット画面をご提示ください。'],
        ['title'=>'専用コインを受け取る','body'=>'500円コイン3枚とオリジナルグラスをお渡しします。'],
        ['title'=>'蔵を巡る','body'=>'気になるお酒をコインと引き換えに。造り手に直接聞いてみてください。'],
    ],
    'sponsors'=>[70,71,72],
    'sponsor_section_sub'=>'皆さまのご協賛により開催しています。',
    'ticket_price'=>3000,
    'ticket_note'=>"専用コイン1,500円分とオリジナルグラス付き。\n当日券は3,500円です。",
];

// ── トップ ──
preview('front.html','トップページ','/', 'front-page.php','front',[],
    [30 => $event_common] + $BF, ['front']);

// ── 酒蔵 ──
preview('breweries.html','酒蔵一覧','/breweries/','archive-brewery.php','archive-brewery',
    [1,2,3,4,5,6,7,8,9,10], $BF, ['brewery-archive']);

preview('brewery.html','酒蔵詳細（出展銘柄あり）','/breweries/taka.html','single-brewery.php','single-brewery',[1],
    [1 => $BF[1] + [
        'brand_name_en'=>'TAKA','brand_alias'=>'Domaine Taka',
        'founded_year'=>'1888年（明治21年）',
        'tagline'=>'1888年創業、山口県宇部市の蔵。<strong>「ドメーヌ型酒造り」</strong>を掲げ、自社栽培の山田錦を中心に、地の米・地の水で醸す。代表銘柄「貴」は世界の和食文化に呼応する食中酒として、国内外で高い評価を得る。',
        'official_url'=>'https://www.domainetaka.com/','instagram_url'=>'https://www.instagram.com/taka_domaine/',
        'lineup_section_label'=>'Featured Sake at TORASAKE 2026',
        'lineup_section_title'=>'TORASAKEで出展する3銘柄 ── 夏の貴',
        'sake_lineup'=>[
            ['number_label'=>'No.01 ・ 微発泡','type_label'=>'特別純米 スパークリング','name_ja'=>'特別純米 貴 スパークリング','name_en'=>'TAKA SPARKLING','accent_hue'=>230,
             'description'=>'きめ細やかな泡が口の中で弾ける、まさに夏にぴったりの微発泡スパークリング。爽快なガス感とやさしい甘酸のバランスで、よく冷やして乾杯の一杯に。',
             'tags'=>[['tag'=>'微発泡'],['tag'=>'爽快'],['tag'=>'乾杯酒']]],
            ['number_label'=>'No.02 ・ 新感覚','type_label'=>'特別純米','name_ja'=>'特別純米 貴 ソウメイ','name_en'=>'TAKA SOUMEI','accent_hue'=>140,
             'description'=>'クエン酸の心地よい酸味でゴクゴク飲める新感覚日本酒。低アルコールで軽やか、レモンのような爽やかさが食欲をそそる。',
             'tags'=>[['tag'=>'クエン酸'],['tag'=>'爽やかな酸'],['tag'=>'ゴクゴク']]],
            ['number_label'=>'No.03 ・ 滋味深い','type_label'=>'純米吟醸 雄町','name_ja'=>'純米吟醸 雄町 貴','name_en'=>'TAKA OMACHI','accent_hue'=>300,
             'description'=>'旨味をしっかりと含んだアーシー（滋味深い）な味わい。雄町ならではの厚みと骨格、土を思わせる滋味が広がる。',
             'tags'=>[['tag'=>'雄町'],['tag'=>'旨味'],['tag'=>'アーシー']]],
        ],
        'lineup_badge'=>'☀ 夏にぴったりの3本を、虎ノ門ヒルズで。',
        'story_heading'=>'地の米、地の水で醸す。山口・宇部の「ドメーヌ」。',
        'story_body'=>'<p>永山本家酒造場は、1888年（明治21年）に山口県宇部市の地で創業。ワインの世界に倣い、葡萄畑から醸造までを一貫して手がける「ドメーヌ」の思想を酒造りに持ち込み、<strong>自社栽培の山田錦</strong>を中心に、地元の米と水だけで酒を醸しています。</p><p>テロワール（土地の個性）を映した代表銘柄「貴（たか）」は、和食はもちろん世界各国の料理にも寄り添う食中酒として、国内外で高く評価されています。</p>',
        'meta_table'=>[
            ['label'=>'所在地','value'=>'山口県 宇部市'],
            ['label'=>'創業','value'=>'1888年（明治21年）'],
            ['label'=>'代表銘柄','value'=>'貴（たか）／ Domaine Taka'],
            ['label'=>'酒造りの特徴','value'=>'ドメーヌ型酒造り ／ 自社栽培の山田錦 ／ 地の米・地の水'],
        ],
        'event_history'=>[31],
    ], 31 => ['event_status'=>'終了','event_date'=>'20260627','venue_name'=>'虎ノ門ヒルズ ステーションタワー']],
    ['brewery-single']);

preview('brewery-mini.html','酒蔵詳細（出展銘柄 未定）','/breweries/daishinshu.html','single-brewery.php','single-brewery',[2],
    [2 => $BF[2] + [
        'founded_year'=>'明治13年（1880年）／ 屋号「原田屋」',
        'tagline'=>'明治13年（1880年）、「原田屋」の屋号で創業。北アルプスの雪解け水が地中をめぐった伏流水と、全量契約栽培の長野県産米で醸す<strong>「天恵の美酒」</strong>。',
        'official_url'=>'https://www.daishinsyu.com/','instagram_url'=>'https://www.instagram.com/daishinsy/',
        'lineup_section_label'=>'Featured Sake at TORASAKE mini',
        'lineup_section_title'=>'TORASAKEで出展する銘柄',
        'flagship_name'=>'大信州',
        'flagship_description'=>'酒米は長野県生まれの酒造好適米「ひとごこち」と「金紋錦」を全量契約栽培。自家精米し、北アルプスの伏流水で仕込む。当日の出展銘柄は決まり次第お知らせします。',
        'meta_table'=>[
            ['label'=>'所在地','value'=>'長野県松本市島立'],
            ['label'=>'創業','value'=>'明治13年（1880年）／ 屋号「原田屋」'],
            ['label'=>'代表銘柄','value'=>'大信州 / 香月'],
            ['label'=>'使用酒米','value'=>'長野県産 ひとごこち・金紋錦（全量契約栽培）'],
            ['label'=>'仕込水','value'=>'北アルプス連峰の雪解けによる伏流水'],
        ],
        'event_history'=>[30],
    ], 30 => $event_common],
    ['brewery-single']);

// ── お知らせ ──
preview('news.html','お知らせ一覧','/news/','home.php','home',[50,51,52],[],['news-archive']);
preview('news-single.html','お知らせ詳細','/news/2026-07-03-event-report.html','single.php','single',[50],[],['news-single']);

// ── イベント ──
preview('events.html','イベント一覧','/events/','archive-event.php','archive-event',[30,31,32],
    [30 => $event_common,
     31 => ['event_status'=>'終了','event_date'=>'20260627','venue_name'=>'虎ノ門ヒルズ ステーションタワー'],
     32 => ['event_status'=>'終了','event_date'=>'20251008','venue_name'=>'虎ノ門ヒルズ ステーションタワー']],
    ['news-archive','event-archive']);

preview('event.html','イベント詳細','/events/torasake-mini.html','single-event.php','single-event',[30],
    [30 => $event_common] + $BF + [
        70 => ['tier'=>'title','logo'=>['ID'=>103],'link_url'=>'https://www.toranomonhills.com/'],
        71 => ['tier'=>'supporter','logo'=>['ID'=>101]],
        72 => ['tier'=>'supporter'],
    ], ['event-single']);

// ── 過去開催 ──
preview('past-event.html','過去開催レポート','/past/toranomaki-vol2/','single-past_event.php','single-past',[40],
    [40 => [
        'event_date'=>'20251008','venue_name'=>'虎ノ門ヒルズ ステーションタワー',
        'stats'=>[
            ['number'=>'17','unit_suffix'=>'蔵','label'=>'参加酒蔵'],
            ['number'=>'800','unit_suffix'=>'名','label'=>'来場者数'],
            ['number'=>'1','unit_suffix'=>'日','label'=>'熱い一日'],
        ],
        'info_list'=>[
            ['key_en'=>'EVENT','value'=>'日本酒虎の巻 Vol.2'],
            ['key_en'=>'DATE','value'=>'2025年10月8日（水）'],
            ['key_en'=>'VENUE','value'=>'虎ノ門ヒルズ ステーションタワー'],
            ['key_en'=>'PARTICIPATING BREWERIES','value'=>'全国17蔵'],
            ['key_en'=>'ATTENDANCE','value'=>'800名'],
            ['key_en'=>'MEDIA','value'=>'SAKETIMES（外部リンク）','url'=>'https://jp.sake-times.com/'],
        ],
        'report_heading'=>'当日の様子',
        'report_body'=>'<p class="body">2025年10月、虎ノ門ヒルズ ステーションタワーにて開催した「日本酒虎の巻 Vol.2」には、全国から<strong>17の酒蔵</strong>が集結。当日は<strong>800名</strong>を超える日本酒ファンにお越しいただき、各蔵のブースを巡りながら、蔵人と直接語り合う熱気あふれる一日となりました。</p><p class="body">会場内ではテイスティング、ペアリング、限定酒の販売など、多彩な体験コーナーを展開。蔵人と来場者が酒を介して言葉を交わす光景が、いたるところで見られました。</p>',
        'gallery'=>[['ID'=>102,'alt'=>'会場の様子'],['ID'=>101,'alt'=>'会場'],['ID'=>103,'alt'=>'ポスター'],['ID'=>100,'alt'=>'キービジュアル']],
        'closing_heading'=>'そして、2026年へ',
        'closing_body'=>'<p class="body">Vol.2 で得た学びと、来場者の皆様・蔵人の皆様からの温かいお声を糧に、私たちは次なるステージへ進みます。</p>',
    ], 30 => $event_common],
    ['past-event']);

// ── ブログ ──
preview('blog.html','ブログ一覧','/blog/','archive-blog_post.php','archive-blog',[60,61,62,63],
    [60=>['category_label'=>'蔵元インタビュー','youtube_video_id'=>'dQw4w9WgXcQ','related_brewery'=>[1]],
     61=>['category_label'=>'蔵元インタビュー','related_brewery'=>[2]],
     62=>['category_label'=>'会場レポート'],
     63=>['category_label'=>'運営の裏側','youtube_video_id'=>'dQw4w9WgXcQ']] + $BF,
    ['news-archive','blog-archive']);

preview('blog-single.html','ブログ記事','/blog/taka-iv/','single-blog_post.php','single-blog',[60],
    [60=>['category_label'=>'蔵元インタビュー','youtube_video_id'=>'dQw4w9WgXcQ',
          'video_caption'=>'永山本家酒造場 蔵元インタビュー（TORASAKE Channel）',
          'related_brewery'=>[1],
          'pull_quote'=>'「地の米、地の水で醸す。それがうちのドメーヌです。」',
          'pull_quote_who'=>'永山本家酒造場 5代目蔵元 永山貴博様']] + $BF,
    ['blog-single']);

// ── 協賛 ──
preview('sponsors.html','協賛ページ','/sponsors/','page-sponsors.php','page-sponsors',[80],
    [80=>[
        'hero_heading'=>'TORASAKEと共に、日本酒文化を届ける。',
        'hero_body'=>'虎ノ門ヒルズを起点に、東京駅丸の内など会場を広げながら継続開催しているTORASAKE -虎ノ酒-。毎月のミニイベントと年数回の大型イベントを、企業・団体の皆さまと共につくっていきたいと考えています。',
        'sponsor_tiers'=>[
            ['tier'=>'title','badge'=>'タイトルスポンサー','name'=>'◯◯ presents TORASAKE','benefits'=>[
                ['text'=>'イベント名・告知物へのタイトル冠称'],
                ['text'=>'会場内メインビジュアル・サイネージへのロゴ掲出'],
                ['text'=>'トップページ・イベント告知ページへの大きめのロゴ掲載'],
                ['text'=>'News/SNSでの協賛紹介記事'],
                ['text'=>'会場内ブース・ノベルティ配布などの企画連携（応相談）'],
            ]],
            ['tier'=>'supporter','badge'=>'サポーター','name'=>'Supporter','benefits'=>[
                ['text'=>'会場内サイネージ・掲示物へのロゴ掲出'],
                ['text'=>'トップページ・イベント告知ページへのロゴ掲載'],
                ['text'=>'News/SNSでの協賛紹介（まとめ枠）'],
            ]],
        ],
        'sponsor_flow'=>[
            ['title'=>'お問い合わせ','body'=>'下記フォーム・メールより、ご希望のプランや連携内容をご相談ください。'],
            ['title'=>'内容のすり合わせ','body'=>'次回開催の日程・会場・テーマに合わせて、ロゴ掲出箇所や連携企画をご提案します。'],
            ['title'=>'協賛決定・掲出','body'=>'ご契約後、トップページ・イベントページ・会場内にロゴを順次掲出します。'],
        ],
        'contact_body'=>'プラン内容のご相談、次回イベントのご案内など、お気軽にお問い合わせください。',
        'contact_email'=>'info@torasake.com',
     ],
     70 => ['tier'=>'title','logo'=>['ID'=>103],'link_url'=>'https://www.toranomonhills.com/'],
     71 => ['tier'=>'supporter','logo'=>['ID'=>101]],
     72 => ['tier'=>'supporter'],
    ], ['sponsors']);

// ── 目次 ──
$rows = '';
foreach ($INDEX as [$file, $label, $url, $tpl]) {
    $rows .= "<a class=\"row\" href=\"" . htmlspecialchars($file) . "\">"
           . "<span class=\"nm\">" . htmlspecialchars($label) . "</span>"
           . "<code class=\"url\">" . htmlspecialchars($url) . "</code>"
           . "<code class=\"tpl\">" . htmlspecialchars($tpl) . "</code>"
           . "<span class=\"arw\">→</span></a>\n";
}
$index = <<<HTML
<!DOCTYPE html>
<html lang="ja"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TORASAKE テーマ プレビュー</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@400;700;900&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#e8f5fb;--bg3:#fff;--gold:#c8a060;--gold-dim:#a07840;--red:#e63329;--text:#1a1a2e;--text-dim:#5a6a78;--border:rgba(90,140,180,.25);--navy:#0d1929}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Noto Serif JP',serif;background:var(--bg);color:var(--text);line-height:1.8;padding:3rem 1.4rem 5rem}
.wrap{max-width:780px;margin:0 auto}
.eyebrow{font-family:'Noto Sans JP',sans-serif;font-size:.7rem;letter-spacing:.4em;color:var(--gold-dim);font-weight:600;text-transform:uppercase}
h1{font-size:1.9rem;font-weight:900;letter-spacing:.04em;margin:.6rem 0 .8rem}
.lead{font-family:'Noto Sans JP',sans-serif;font-size:.85rem;color:var(--text-dim);line-height:2;margin-bottom:2.4rem}
.note{background:var(--bg3);border:1px solid var(--border);border-left:3px solid var(--gold);border-radius:6px;padding:1.1rem 1.3rem;font-family:'Noto Sans JP',sans-serif;font-size:.8rem;color:var(--text-dim);line-height:1.95;margin-bottom:2.4rem}
.row{display:flex;align-items:center;gap:1rem;background:var(--bg3);border:1px solid var(--border);border-radius:9px;padding:1rem 1.2rem;margin-bottom:.7rem;text-decoration:none;color:inherit;transition:border-color .2s,box-shadow .2s,transform .2s}
.row:hover{border-color:var(--gold);box-shadow:0 10px 26px -14px rgba(200,160,96,.45);transform:translateY(-1px)}
.nm{font-weight:700;font-size:1rem;flex:1;min-width:0}
code{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.72rem}
.url{color:var(--red);white-space:nowrap}
.tpl{color:var(--text-dim);white-space:nowrap;display:none}
.arw{color:var(--gold-dim)}
@media(min-width:700px){.tpl{display:inline}}
</style></head>
<body><div class="wrap">
<div class="eyebrow">Theme Preview</div>
<h1>TORASAKE テーマ プレビュー</h1>
<p class="lead">テンプレートを実行してCSSを埋め込んだ静的HTML。中身はダミーデータです。</p>
<div class="note">
これは <strong>本物のWordPressではありません</strong>。見た目とマークアップの確認用です。<br>
検索・フィルタ・カウントダウンなどのJSは動きません（プレビューではJSを読み込んでいません）。<br>
実際にクリックして回れる状態で見たいときは、README.md の「ローカルで動かす」を参照してください。
</div>
$rows
</div></body></html>
HTML;
file_put_contents("$out_dir/index.html", $index);
printf("  ✓ %-22s (目次)\n", 'index.html');

echo "\n  出力先: $out_dir\n  index.html をブラウザで開いてください。\n";
