<?php
/**
 * セクションコンポーネント：FOR ARTISTS（若手画家募集バナー）
 *
 * mockups/artist.html の .match-banner.for-artists を移植。Artist Matching バナーと
 * 同構造だが、白背景・インク枠で差別化。アーティスト一覧等で再利用する。
 *
 * 引数（$args 経由）:
 *   - guidelines_url string 募集要項リンク（既定：/recruit/。PDF等があれば差し替え）
 *   - apply_url      string 応募リンク（既定：/recruit/）
 *
 * ※ recruit ページ / 募集要項PDF は Phase 2 で実装予定のため URL は暫定。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$guidelines_url = isset( $args['guidelines_url'] ) ? $args['guidelines_url'] : home_url( '/recruit/' );
$apply_url      = isset( $args['apply_url'] ) ? $args['apply_url'] : home_url( '/recruit/' );
?>
<section class="for-artists-sec">
	<div class="match-banner for-artists rv">
		<div class="match-banner-left">
			<span class="match-banner-label">For Artists</span>
			<h2 class="match-banner-title">若手画家募集中</h2>
			<p class="match-banner-sub"><?php echo esc_html__( 'BANK OF ART は、ともに歩む画家を募集しています。', 'bankofart' ); ?></p>
			<p class="match-banner-body"><?php echo esc_html__( '全作品買取制で、制作に専念できる環境を提供します。ご縁のある方には登録画家としてご活動いただき、公認画家としてご契約に至る場合もあります。', 'bankofart' ); ?></p>
		</div>
		<div class="match-banner-right">
			<a href="<?php echo esc_url( $guidelines_url ); ?>" class="match-banner-btn-secondary"><?php echo esc_html__( '募集要項を見る', 'bankofart' ); ?></a>
			<a href="<?php echo esc_url( $apply_url ); ?>" class="match-banner-btn"><?php echo esc_html__( '応募する', 'bankofart' ); ?></a>
		</div>
	</div>
</section>
