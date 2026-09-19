<?php
/**
 * ヘッダー。
 *
 * 参照HTMLはページ種別ごとにナビの形が違う（トップ＝フルメニュー、
 * 一覧・詳細＝ロゴ＋戻るリンク、記事＝戻るリンク＋ハンバーガー）ので、
 * template-parts/nav-{variant}.php に分けて出し分ける。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="<?php echo esc_url( torasake_favicon_url() ); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php get_template_part( 'template-parts/nav', torasake_nav_variant() ); ?>
