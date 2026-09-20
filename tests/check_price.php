<?php
require __DIR__ . '/stubs.php';
$T = &$GLOBALS['T'];
$T['posts'] = [ new WP_Post(['ID'=>1,'post_title'=>'永山本家酒造場','post_name'=>'taka','post_type'=>'brewery']),
                new WP_Post(['ID'=>2,'post_title'=>'大信州酒造','post_name'=>'daishinshu','post_type'=>'brewery']) ];
require get_template_directory() . '/functions.php';
do_action('after_setup_theme'); do_action('init');

// 価格らしき値を全フィールドに突っ込んでも酒蔵ページに出ないことを確認する
$poison = ['price'=>3000,'ticket_price'=>3000,'cost'=>'2,500円'];
$base = [
  'brand_name'=>'貴','region_en'=>'Yamaguchi','city'=>'宇部市','logo'=>['ID'=>9],
  'lineup_summary'=>'貴 純米大吟醸','tagline'=>'ドメーヌ型酒造り。',
  'sake_lineup'=>[['number_label'=>'No.01','bottle_image'=>['ID'=>10],'type_label'=>'特別純米',
                   'name_ja'=>'貴 スパークリング','name_en'=>'TAKA SPARKLING','description'=>'きめ細やかな泡。',
                   'tags'=>[['tag'=>'微発泡']],'accent_hue'=>230,'price'=>2500]],
  'meta_table'=>[['label'=>'所在地','value'=>'山口県 宇部市']],
  'official_url'=>'https://www.domainetaka.com/','instagram_url'=>'https://www.instagram.com/taka_domaine/',
];

foreach ([['single-brewery.php','single-brewery',[1]], ['archive-brewery.php','archive-brewery',[1,2]]] as [$tpl,$ctx,$ids]) {
  $T['context']=$ctx; $T['fields']=array_merge($base,$poison);
  $T['loop']=array_map(fn($i)=>get_post($i), $ids); $T['current']=$T['loop'][0];
  ob_start(); include get_template_directory() . '/'.$tpl; $html=ob_get_clean();
  $hits=[];
  if (preg_match_all('/[0-9][0-9,]*\s*円/u',$html,$m)) $hits=array_merge($hits,$m[0]);
  if (stripos($html,'"price"')!==false || str_contains($html,'class="price"')) $hits[]='class=price';
  printf("  %s %-18s  価格らしき出力: %s\n", $hits?'✗':'✓', $ctx, $hits ? implode(' / ',array_unique($hits)) : 'なし');
}
