<?php
require __DIR__ . '/stubs.php';
$T=&$GLOBALS['T'];
$T['posts']=[ new WP_Post(['ID'=>1,'post_title'=>'永山本家酒造場','post_name'=>'taka','post_type'=>'brewery']) ];
require get_template_directory() . '/functions.php'; do_action('after_setup_theme'); do_action('init');
$T['context']='single-brewery'; $T['current']=get_post(1); $T['loop']=[get_post(1)];
$T['fields']=['brand_name'=>'貴','brand_name_en'=>'TAKA','founded_year'=>'1888年（明治21年）','city'=>'宇部市',
  'official_url'=>'https://www.domainetaka.com/','instagram_url'=>'https://www.instagram.com/taka_domaine/',
  'logo'=>['ID'=>9,'url'=>'https://torasake.test/uploads/taka_logo.jpg'],'tagline'=>'ドメーヌ型酒造り。',
  'sake_lineup'=>[['name_ja'=>'特別純米 貴 スパークリング','type_label'=>'日本酒・スパークリング','price'=>2500]]];
ob_start(); torasake_brewery_jsonld(); $out=ob_get_clean();
preg_match('/<script[^>]*>(.*)<\/script>/s',$out,$m);
$json=json_decode($m[1],true);
echo json_encode($json['@graph'][0]['makesOffer']??[], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),"\n";
echo (stripos($m[1],'price')===false ? "  ✓ JSON-LD に price キーなし\n" : "  ✗ price が混入\n");
