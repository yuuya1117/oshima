<?php
/**
 * WordPress を起動せずにテンプレートを実行するための最小スタブ。
 * 本物のWPが無い環境（wordpress.org がネットワークポリシーで遮断）での
 * 静的検証専用。挙動の正しさではなく「致命的エラーが出ないこと」を見る。
 */
define('ABSPATH', __DIR__ . '/');

// ── 状態 ───────────────────────────────────────────────
$GLOBALS['T'] = [
    'context'   => 'front',
    'posts'     => [],
    'current'   => null,
    'loop'      => [],
    'fields'    => [],
    'actions'   => [],
    'filters'   => [],
    'output_hooks' => [],
];

class WP_Post {
    public $ID; public $post_title=''; public $post_name=''; public $post_type='post'; public $post_status='publish';
    public function __construct($a=[]) { foreach($a as $k=>$v) $this->$k=$v; }
}
class WP_Term {
    public $term_id; public $name=''; public $slug=''; public $taxonomy='';
    public function __construct($a=[]) { foreach($a as $k=>$v) $this->$k=$v; }
}
class WP_Error {}
class WP_Query {
    public $posts=[]; public $post_count=0; private $i=0;
    public function __construct($args=[]) {
        $pt = (array)($args['post_type'] ?? 'post');
        $this->posts = array_values(array_filter($GLOBALS['T']['posts'], fn($p)=>in_array($p->post_type,$pt,true)));
        $n = $args['posts_per_page'] ?? -1;
        if ($n > 0) $this->posts = array_slice($this->posts, 0, $n);
        $this->post_count = count($this->posts);
    }
    public function is_main_query() { return true; }
    public function is_post_type_archive($t=''){ return false; }
    public function is_home(){ return false; }
    public function set($k,$v){ $this->vars[$k]=$v; }
    public $vars = [];
    public function have_posts() { return $this->i < count($this->posts); }
    public function the_post() { $GLOBALS['T']['current'] = $this->posts[$this->i++]; }
}

// ── フック ─────────────────────────────────────────────
function add_action($h,$c,$p=10,$a=1){ $GLOBALS['T']['actions'][$h][]=$c; }
function add_filter($h,$c,$p=10,$a=1){ $GLOBALS['T']['filters'][$h][]=$c; }
function do_action($h,...$args){ foreach($GLOBALS['T']['actions'][$h]??[] as $c) $c(...$args); }
function apply_filters($h,$v,...$a){ foreach($GLOBALS['T']['filters'][$h]??[] as $c) $v=$c($v,...$a); return $v; }

// ── テーマ基本 ─────────────────────────────────────────
function get_template_directory(){ return dirname(__DIR__) . '/themes/torasake'; }
function get_template_directory_uri(){ return 'https://example.test/wp-content/themes/torasake'; }
function get_stylesheet_uri(){ return get_template_directory_uri().'/style.css'; }
function load_theme_textdomain($d,$p){ return true; }
function add_theme_support(...$a){}
function add_image_size(...$a){}
function register_nav_menus($a){}
function register_post_type($t,$a=[]){ $GLOBALS['T']['pt'][$t] = (object)($a + ['has_archive'=>false]); }
function register_taxonomy($t,$o,$a=[]){}
function add_rewrite_rule($r,$q,$p=''){ $GLOBALS['T']['rules'][$r]=$q; }
function flush_rewrite_rules($h=true){}
function __($s,$d=''){ return $s; }
function _e($s,$d=''){ echo $s; }

// ── エスケープ ─────────────────────────────────────────
function esc_html($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function esc_attr($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function esc_url($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function wp_kses($s,$allowed){ return (string)$s; }
function wp_kses_post($s){ return (string)$s; }
function wp_strip_all_tags($s){ return strip_tags((string)$s); }
function wp_trim_words($s,$n=55,$m=''){ return $s; }
function wp_json_encode($d,$f=0){ return json_encode($d,$f); }
function wp_unslash($v){ return $v; }
function sanitize_title_for_query($t){ return $t; }
function rawurlencode_stub($s){ return rawurlencode($s); }
function nl2br_wp($s){ return nl2br($s); }
function number_format_i18n($n,$d=0){ return number_format((float)$n,$d); }

// ── URL / オプション ───────────────────────────────────
function home_url($p='/'){ return 'https://torasake.test' . $p; }
function add_query_arg($k,$v,$u){ return $u . (str_contains($u,'?')?'&':'?') . $k . '=' . $v; }
function get_option($k,$d=false){ return ['page_for_posts'=>99,'permalink_structure'=>'/%postname%/','show_on_front'=>'page'][$k] ?? $d; }
function update_option($k,$v){ return true; }
function get_bloginfo($k=''){ return ['name'=>'TORASAKE -虎ノ酒-','description'=>'日本酒と出会う、継続開催イベントシリーズ','charset'=>'UTF-8'][$k] ?? ''; }
function bloginfo($k=''){ echo get_bloginfo($k); }
function language_attributes(){ echo 'lang="ja"'; }
function body_class($c=''){ echo 'class="torasake"'; }
function get_theme_mod($k,$d=false){ return $d; }
function get_site_icon_url($s=512){ return ''; }
function wp_timezone(){ return new DateTimeZone('Asia/Tokyo'); }
function wp_timezone_string(){ return 'Asia/Tokyo'; }
function wp_date($f,$ts=null){ return (new DateTimeImmutable('now', wp_timezone()))->format($f); }
function wp_parse_url($u,$c=-1){ return $c===-1 ? parse_url($u) : parse_url($u,$c); }
function wp_safe_redirect($u,$s=302){ return true; }
function is_wp_error($t){ return $t instanceof WP_Error; }

// ── 条件分岐タグ ───────────────────────────────────────
function ctx(){ return $GLOBALS['T']['context']; }
function is_front_page(){ return ctx()==='front'; }
function is_home(){
    if (ctx()==='home') return true;
    // WP コア準拠: singular でも archive でも search でも 404 でもなければ is_home にフォールバックする。
    if (in_array(ctx(),['archive-brewery','archive-blog','archive-event'],true)) return !is_post_type_archive();
    return false;
}
function is_admin(){ return false; }
function is_search(){ return ctx()==='search'; }
function is_404(){ return ctx()==='404'; }
function is_category(){ return false; }
function is_archive(){ return is_post_type_archive() || ctx()==='home'; }
function is_post_type_archive($t=''){
    // WP コア準拠: post_type が指定され、その投稿タイプの has_archive が空でないときだけ真。
    $m = ['archive-brewery'=>'brewery','archive-blog'=>'blog_post','archive-event'=>'event'];
    $cur = $m[ctx()] ?? null;
    if ($cur === null) return false;
    $obj = $GLOBALS['T']['pt'][$cur] ?? null;
    if (empty($obj->has_archive)) return false;   // ← ここが落ちると is_home にフォールバックする
    return $t==='' || in_array($cur,(array)$t,true);
}
function is_singular($t=''){
    $map = ['single-brewery'=>'brewery','single'=>'post','single-event'=>'event',
            'single-past'=>'past_event','single-blog'=>'blog_post'];
    $cur = $map[ctx()] ?? null;
    if ($cur === null) return false;
    return $t==='' || in_array($cur,(array)$t,true);
}

// ── ループ ─────────────────────────────────────────────
function have_posts(){
    if (!isset($GLOBALS['T']['loop_all'])) { $GLOBALS['T']['loop_all'] = $GLOBALS['T']['loop']; }
    return !empty($GLOBALS['T']['loop']);
}
function the_post(){ $GLOBALS['T']['current'] = array_shift($GLOBALS['T']['loop']); }
function wp_reset_postdata(){}
function rewind_posts(){ $GLOBALS['T']['loop'] = $GLOBALS['T']['loop_all'] ?? $GLOBALS['T']['loop']; }
function get_the_ID(){ return $GLOBALS['T']['current']->ID ?? 0; }
function get_post($id=null){ foreach($GLOBALS['T']['posts'] as $p) if($p->ID==$id) return $p; return null; }
function get_post_status($id){ return get_post($id)?->post_status ?? false; }
function get_the_title($p=null){ $id = $p instanceof WP_Post ? $p->ID : ($p ?: get_the_ID()); return get_post($id)?->post_title ?? ''; }
function the_title(){ echo esc_html(get_the_title()); }
function get_permalink($p=null){ $id = $p instanceof WP_Post ? $p->ID : ($p ?: get_the_ID()); $po=get_post($id); return $po ? home_url('/'.$po->post_type.'/'.$po->post_name) : ''; }
function the_permalink(){ echo esc_url(get_permalink()); }
function get_the_excerpt($p=null){ return 'ダミーの抜粋テキストです。'; }
function get_the_date($f='Y.m.d',$p=null){ return date($f, strtotime('2026-07-03')); }
function has_post_thumbnail($p=null){
    $id = $p instanceof WP_Post ? $p->ID : ($p ?: get_the_ID());
    return isset($GLOBALS['T']['img']['post'.$id]) || empty($GLOBALS['T']['img']);
}
function get_the_post_thumbnail($p=null,$s='',$a=[]){
    $id = $p instanceof WP_Post ? $p->ID : ($p ?: get_the_ID());
    return '<img src="'.esc_attr(torasake_stub_img('post'.$id)).'" alt="'.esc_attr($a['alt']??'').'" loading="lazy">';
}
function the_post_thumbnail($s='',$a=[]){ echo get_the_post_thumbnail(null,$s,$a); }
function get_the_post_thumbnail_url($p=null,$s=''){ return 'https://torasake.test/uploads/thumb.jpg'; }
function get_the_post_thumbnail_caption($p=null){ return 'キャプション'; }
function the_content(){ echo '<p>本文</p>'; }
function get_previous_post(){ return $GLOBALS['T']['posts'][0] ?? null; }
function get_next_post(){ return $GLOBALS['T']['posts'][1] ?? null; }
function the_posts_pagination($a=[]){
    // WP は max_num_pages <= 1 のとき何も出力しない。スタブは常に1ページ扱い。
    if (!empty($GLOBALS['T']['paginate'])) echo '<nav class="news-pagination"><div class="nav-links"></div></nav>';
}
function post_type_archive_title($p='',$d=true){
    $labels = ['archive-brewery'=>'参加酒蔵一覧','archive-event'=>'イベント','archive-blog'=>'ブログ'];
    $t = $labels[ctx()] ?? 'アーカイブ';
    if ($d) echo esc_html($t);
    return $t;
}
function the_archive_title(){ echo 'アーカイブ'; }
function get_search_query(){ return 'テスト'; }
function selected($a,$b,$e=true){ $r = ((string)$a===(string)$b)?" selected='selected'":''; if($e) echo $r; return $r; }
function torasake_stub_img($id){
    // プレビュー時は実画像を指す（tests/preview.php が $T['img'] を入れる）。
    return $GLOBALS['T']['img'][$id] ?? ($GLOBALS['T']['img']['*'] ?? 'a.jpg');
}
function wp_get_attachment_image($id,$s='',$icon=false,$attr=[]){
    $extra = '';
    foreach ($attr as $k=>$v) { if (!in_array($k,['alt','loading'],true)) $extra .= ' '.$k.'="'.esc_attr((string)$v).'"'; }
    return '<img src="'.esc_attr(torasake_stub_img($id)).'" alt="'.esc_attr($attr['alt']??'').'" loading="lazy"'.$extra.'>';
}
function wp_get_attachment_image_src($id,$s=''){ return ['https://torasake.test/uploads/a.jpg',100,100,false]; }

// ── タクソノミー ───────────────────────────────────────
function get_terms($a=[]){
    $tax = is_array($a) ? ($a['taxonomy']??'') : $a;
    if ($tax==='prefecture') return [ new WP_Term(['term_id'=>11,'name'=>'山口県','slug'=>'yamaguchi','taxonomy'=>'prefecture']),
                                      new WP_Term(['term_id'=>12,'name'=>'長野県','slug'=>'nagano','taxonomy'=>'prefecture']) ];
    if ($tax==='event_edition') return [ new WP_Term(['term_id'=>21,'name'=>'TORASAKE -虎ノ酒- 2026','slug'=>'torasake-2026','taxonomy'=>'event_edition']),
                                         new WP_Term(['term_id'=>22,'name'=>'TORASAKE mini','slug'=>'torasake-mini-2026','taxonomy'=>'event_edition']) ];
    return [];
}
function get_term_meta($id,$k='',$single=false){ return ['21'=>'20260627','22'=>'20261016'][(string)$id] ?? ''; }
function update_term_meta($id,$k,$v){ return true; }
function get_term_by($f,$v,$tax){
    $cats=['report'=>'開催報告','info'=>'お知らせ','guide'=>'イベントガイド'];
    if($tax==='category' && isset($cats[$v])) return new WP_Term(['term_id'=>crc32($v),'name'=>$cats[$v],'slug'=>$v,'taxonomy'=>'category']);
    return false;
}
function term_exists($t,$tax=''){ return ['term_id'=>1]; }
function wp_insert_term($n,$tax,$a=[]){ return ['term_id'=>1]; }
function get_the_terms($id,$tax){
    if ($tax==='prefecture') {
        $name = $GLOBALS['T']['pref_by_post'][$id] ?? '山口県';
        return [new WP_Term(['term_id'=>crc32($name),'name'=>$name,'slug'=>'pref'])];
    }
    if ($tax==='event_edition') {
        return [new WP_Term(['term_id'=>22,'name'=>'TORASAKE mini','slug'=>'torasake-mini-2026','taxonomy'=>'event_edition'])];
    }
    return [];
}
function wp_get_post_terms($id,$tax,$a=[]){ return ['torasake-2026','torasake-mini-2026']; }
function get_the_category($id=false){ return [ new WP_Term(['term_id'=>1,'name'=>'開催報告','slug'=>'report']) ]; }
function get_page_by_path($p,$o=OBJECT,$t='page'){ return new WP_Post(['ID'=>99,'post_title'=>'お知らせ','post_name'=>$p]); }
function wp_insert_post($a){ return 100; }

// ── enqueue ────────────────────────────────────────────
function wp_enqueue_style(...$a){}
function wp_enqueue_script(...$a){}
function wp_localize_script(...$a){}
function wp_head(){ do_action('wp_head'); }
function wp_footer(){}
function wp_body_open(){}
function wp_get_document_title(){ return 'ページタイトル | TORASAKE'; }
function redirect_canonical($u=''){ return $u; }

// ── テンプレート読み込み ───────────────────────────────
function get_header($n=''){ include get_template_directory().'/header.php'; }
function get_footer($n=''){ include get_template_directory().'/footer.php'; }
function get_template_part($slug,$name=null,$args=[]){
    $f = get_template_directory().'/'.$slug.($name?'-'.$name:'').'.php';
    if (file_exists($f)) include $f;
    else throw new RuntimeException("template part not found: $f");
}
function get_post_type_archive_link($t){ return false; }

// ── ACF ────────────────────────────────────────────────
function get_field($sel,$id=false,$fmt=true){
    // 一覧テンプレートは複数投稿を跨ぐので、投稿ごとの値があればそちらを優先する。
    $pid = $id ?: get_the_ID();
    if (isset($GLOBALS['T']['fields_by_post'][$pid])) {
        return $GLOBALS['T']['fields_by_post'][$pid][$sel] ?? null;
    }
    return $GLOBALS['T']['fields'][$sel] ?? null;
}
function acf_add_options_page($a){}
function wp_count_posts($t='post',$p='readable'){ $o=new stdClass; $o->publish = count(array_filter($GLOBALS['T']['posts'], fn($x)=>$x->post_type===$t)); return $o; }

// ── 追加テンプレート用スタブ ──
function is_page($p=''){ return ctx()==='page-sponsors' && ($p==='' || $p==='sponsors'); }
function is_page_template($t=''){ return ctx()==='page-sponsors' && ($t==='' || $t==='page-sponsors.php'); }
function get_page_template_slug($id=null){ return ''; }
function update_post_meta($id,$k,$v){ return true; }
function wp_get_attachment_image_url($id,$s=''){ return 'https://torasake.test/uploads/full.jpg'; }
