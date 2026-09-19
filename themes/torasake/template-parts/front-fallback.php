<?php
/**
 * トップ ─ 開催予定が無い期間のフォールバック。
 *
 * README「開催予定が無い期間は『次回準備中』フォールバックを出す」に対応。
 * 直近の開催回の振り返りと News への導線だけを残す。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;
?>
<section id="hero" data-screen-label="01 Hero">
	<div class="hero-bar">
		<div class="hb-item">
			<span class="hb-k">NEXT</span>
			<span class="hb-v">次回開催 準備中</span>
		</div>
		<a href="<?php echo esc_url( home_url( '/news/' ) ); ?>" class="hb-cta">お知らせを見る</a>
	</div>
</section>

<section id="lead" data-screen-label="02 コンセプト">
	<div class="inner">
		<div class="s-head rv">
			<span class="s-label">About</span>
			<h2 class="s-title">次回開催に向けて、準備を進めています。</h2>
			<div class="divider"></div>
			<p class="s-sub">開催が決まり次第、こちらとお知らせページでご案内します。</p>
		</div>
		<div class="center rv rv-d1">
			<a href="<?php echo esc_url( home_url( '/breweries/' ) ); ?>" class="btn btn-outline">これまでの参加酒蔵を見る</a>
		</div>
	</div>
</section>

<?php get_template_part( 'template-parts/front', 'archive' ); ?>
<?php get_template_part( 'template-parts/front', 'news' ); ?>
