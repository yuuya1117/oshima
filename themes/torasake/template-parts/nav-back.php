<?php
/**
 * ロゴ＋戻るリンクだけのナビ（参照: brewery-archive.html / brewery-style.css / news-archive.html）。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

list( $torasake_back_url, $torasake_back_label ) = torasake_back_link();
?>
<nav>
	<a class="nav-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<img src="<?php echo esc_url( torasake_site_logo_url() ); ?>" alt="<?php bloginfo( 'name' ); ?>">
	</a>
	<a class="nav-back" href="<?php echo esc_url( $torasake_back_url ); ?>">
		<span class="arrow">←</span>
		<span><?php echo esc_html( $torasake_back_label ); ?></span>
	</a>
</nav>
