<?php
/**
 * セクションコンポーネント：CTA（CONTACT）
 *
 * mockups/index.html の .cta-strip を正としてDOM・クラスを一致させる。
 * リンク先は指示通りテーマ内ページ（資料請求 / オンライン説明会）に差し替え。
 * テキスト・デザインの変更はこの1ファイルの編集で全ページに反映される。
 *
 * 引数（$args 経由）:
 *   - variant string 'default' | 'compact'（既定 'default'）
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$variant = isset( $args['variant'] ) ? $args['variant'] : 'default';

$document_url = home_url( '/document-request/' );
$briefing_url = home_url( '/online-briefing/' );
?>
<div class="cta-strip cta-strip--<?php echo esc_attr( $variant ); ?>" id="contact">
	<div class="cta-inner">
		<h2 class="cta-title">CONTACT</h2>
		<div class="cta-btns">
			<a class="btn-cta-w" href="<?php echo esc_url( $document_url ); ?>"><?php echo esc_html__( '資料請求', 'bankofart' ); ?></a>
			<a class="btn-cta-o" href="<?php echo esc_url( $briefing_url ); ?>"><?php echo esc_html__( '説明会を予約', 'bankofart' ); ?></a>
		</div>
	</div>
</div>
