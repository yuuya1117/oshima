<?php
require __DIR__ . '/stubs.php';
$T=&$GLOBALS['T'];
$T['posts']=[ new WP_Post(['ID'=>1,'post_title'=>'蔵','post_name'=>'taka','post_type'=>'brewery']) ];
require get_template_directory() . '/functions.php'; do_action('after_setup_theme'); do_action('init');

echo "登録された has_archive:\n";
foreach ($GLOBALS['T']['pt'] as $name=>$obj) {
    printf("  %-12s has_archive = %s\n", $name, var_export($obj->has_archive, true));
}
echo "\nテンプレート階層の判定:\n";
foreach (['archive-brewery'=>'brewery','archive-blog'=>'blog_post','archive-event'=>'event'] as $ctx=>$pt) {
    $T['context']=$ctx;
    $isPta = is_post_type_archive($pt);
    $tpl = $isPta ? "archive-{$pt}.php" : (is_home() ? "home.php ← 誤り" : "index.php");
    printf("  %-16s is_post_type_archive=%-5s is_home=%-5s → %s\n",
        '/'.$ctx, var_export($isPta,true), var_export(is_home(),true), $tpl);
}
