<?php
/**
 * 共通フッター本体
 *
 * 4列構成（ブランド / NAVIGATION / CONTACT / For Artists）＋ ボトム行
 * （Privacy Policy ・ © BANK OF ART, INC.）。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bankofart_logo_foot = get_theme_file_uri( 'assets/img/logo/boa-07.png' );

$bankofart_url_document = apply_filters( 'bankofart_document_request_url', 'https://business.form-mailer.jp/fms/1c6a81d4280183' );
$bankofart_url_briefing = apply_filters( 'bankofart_briefing_url', 'https://booking.receptionist.jp/5ade6c6d-ae6c-44d9-9921-000ad24af9f9/30min' );
$bankofart_url_apply    = apply_filters( 'bankofart_artist_apply_url', 'https://tally.so/r/MeMEV0' );

// SNS リンク。
$bankofart_socials = array(
	array(
		'label' => 'Facebook',
		'short' => 'F',
		'url'   => 'https://www.facebook.com/bankofart2022/',
	),
	array(
		'label' => 'YouTube',
		'short' => 'Y',
		'url'   => 'https://www.youtube.com/@bankofart2022',
	),
	array(
		'label' => 'Instagram',
		'short' => 'I',
		'url'   => 'https://www.instagram.com/bankof_art2022/',
	),
	array(
		'label' => 'X',
		'short' => 'X',
		'url'   => 'https://x.com/bankof_art',
	),
);

// NAVIGATION 列。
$bankofart_foot_nav = array(
	array(
		'label' => 'バンクオブアートとは',
		'url'   => home_url( '/about/' ),
	),
	array(
		'label' => 'アーティスト一覧',
		'url'   => home_url( '/artist/' ),
	),
	array(
		'label' => '作品',
		'url'   => home_url( '/art/' ),
	),
	array(
		'label' => '画家応援企業',
		'url'   => home_url( '/collector/' ),
	),
	array(
		'label' => '最新記事',
		'url'   => home_url( '/news/' ),
	),
	array(
		'label' => '読み物',
		'url'   => home_url( '/journal/' ),
	),
);
?>
<footer class="site-footer">
	<div class="footer-top">

		<div class="foot-brand">
			<div class="foot-logo">
				<img src="<?php echo esc_url( $bankofart_logo_foot ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="160" height="32">
			</div>
			<p class="foot-tagline">絵描きの明日を創出する。<br>減価償却 × 画家応援</p>
			<div class="foot-social">
				<?php foreach ( $bankofart_socials as $bankofart_social ) : ?>
					<a href="<?php echo esc_url( $bankofart_social['url'] ); ?>" class="soc-a" target="_blank" rel="noopener" title="<?php echo esc_attr( $bankofart_social['label'] ); ?>" aria-label="<?php echo esc_attr( $bankofart_social['label'] ); ?>"><?php echo esc_html( $bankofart_social['short'] ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="foot-col">
			<div class="foot-col-t">NAVIGATION</div>
			<ul class="foot-links">
				<?php foreach ( $bankofart_foot_nav as $bankofart_link ) : ?>
					<li><a href="<?php echo esc_url( $bankofart_link['url'] ); ?>"><?php echo esc_html( $bankofart_link['label'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="foot-col">
			<div class="foot-col-t">CONTACT</div>
			<ul class="foot-links">
				<li><a href="<?php echo esc_url( $bankofart_url_document ); ?>" target="_blank" rel="noopener">資料ダウンロード</a></li>
				<li><a href="<?php echo esc_url( $bankofart_url_briefing ); ?>" target="_blank" rel="noopener">オンライン説明会</a></li>
				<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">お問い合わせ</a></li>
			</ul>
		</div>

		<div class="foot-col">
			<div class="foot-col-t">For Artists</div>
			<ul class="foot-links">
				<li><a href="<?php echo esc_url( home_url( '/recruit/' ) ); ?>">画家募集</a></li>
				<li><a href="<?php echo esc_url( $bankofart_url_apply ); ?>" target="_blank" rel="noopener">応募フォーム</a></li>
			</ul>
		</div>

	</div>

	<div class="footer-bottom">
		<a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>" class="foot-privacy">Privacy Policy</a>
		<span class="foot-copy">&copy; BANK OF ART, INC.</span>
	</div>
</footer>
