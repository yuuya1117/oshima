<?php
/**
 * TORASAKE テーマ ブートストラップ。
 *
 * ここでは読み込みだけを行い、実処理は inc/ 配下に置く。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

define( 'TORASAKE_VERSION', '1.0.0' );
define( 'TORASAKE_DIR', get_template_directory() );
define( 'TORASAKE_URI', get_template_directory_uri() );

require_once TORASAKE_DIR . '/inc/setup.php';
require_once TORASAKE_DIR . '/inc/post-types.php';
require_once TORASAKE_DIR . '/inc/taxonomies.php';
require_once TORASAKE_DIR . '/inc/rewrite.php';
require_once TORASAKE_DIR . '/inc/acf.php';
require_once TORASAKE_DIR . '/inc/template-tags.php';
require_once TORASAKE_DIR . '/inc/enqueue.php';
require_once TORASAKE_DIR . '/inc/seo.php';
