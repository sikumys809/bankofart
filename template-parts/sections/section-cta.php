<?php
/**
 * セクションコンポーネント：CTA（CONTACT）
 *
 * mockups/index.html の .cta-strip を正としてDOM・クラスを一致させる。
 * リンク先は CONTACT 系の一元管理ヘルパー（bankofart_document_request_url() /
 * bankofart_briefing_url()）を使用。header / footer / about MOVIE と同一URL。
 * 確定（内部フォーム実装）時はヘルパー1箇所の差し替えで全ページに反映される。
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

$document_url = bankofart_document_request_url();
$briefing_url = bankofart_briefing_url();
?>
<div class="cta-strip cta-strip--<?php echo esc_attr( $variant ); ?>" id="contact">
	<div class="cta-inner">
		<h2 class="cta-title">CONTACT</h2>
		<div class="cta-btns">
			<a class="btn-cta-w" href="<?php echo esc_url( $document_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html__( '資料請求', 'bankofart' ); ?></a>
			<a class="btn-cta-o" href="<?php echo esc_url( $briefing_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html__( '説明会を予約', 'bankofart' ); ?></a>
		</div>
	</div>
</div>
