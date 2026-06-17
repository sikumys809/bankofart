<?php
/**
 * セクションコンポーネント：FOR ARTISTS（若手画家募集バナー）
 *
 * mockups/artist.html の .match-banner.for-artists を移植。Artist Matching バナーと
 * 同構造だが、白背景・インク枠で差別化。アーティスト一覧等で再利用する。
 *
 * 引数（$args 経由）:
 *   - guidelines_url string 募集要項リンク（既定：/recruit/ ＝ RECRUIT ページ）
 *   - apply_url      string 応募リンク（既定：bankofart_apply_url() ＝ 応募フォーム。recruit の応募ボタンと共通）
 *
 * 「募集要項を見る」は RECRUIT ページ（/recruit/）へ、「応募する」は応募フォーム
 * （bankofart_apply_url()）へ接続。確定フォームURLは bankofart_apply_url() 1箇所の差し替えで全反映。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$guidelines_url = isset( $args['guidelines_url'] ) ? $args['guidelines_url'] : home_url( '/recruit/' );
$apply_url      = isset( $args['apply_url'] ) ? $args['apply_url'] : bankofart_apply_url();
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
