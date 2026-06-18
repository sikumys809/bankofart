<?php
/**
 * Template Name: 資料請求フォーム
 *
 * 資料請求フォームページ（Notion「資料請求フォーム 実装指示書」§5）。
 * 送信処理・DB・メール・PDF配信・管理画面は inc/document-request/。
 * フォームの見た目は共通基盤 .boa-form 系（components.css）を流用。
 *
 * テンプレート適用：スラッグ "document-request" の固定ページ、または本テンプレート選択ページ。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// エラー再表示（transientキー or 既定エラーコード）。
$err_values = array();
$err_list   = array();
$err_generic = '';
if ( isset( $_GET['dr_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$ek = sanitize_text_field( wp_unslash( $_GET['dr_error'] ) );
	$map = array(
		'recaptcha'  => '送信を確認できませんでした。お手数ですが再度お試しください。',
		'rate_limit' => '短時間に複数回送信されています。しばらく時間をおいて再度お試しください。',
		'db'         => '送信処理でエラーが発生しました。お手数ですが再度お試しください。',
	);
	if ( isset( $map[ $ek ] ) ) {
		$err_generic = $map[ $ek ];
	} else {
		$stash = get_transient( $ek );
		if ( is_array( $stash ) ) {
			$err_values = isset( $stash['values'] ) ? $stash['values'] : array();
			$err_list   = isset( $stash['errors'] ) ? $stash['errors'] : array();
			delete_transient( $ek );
		}
	}
}
$val = function ( $key ) use ( $err_values ) {
	return isset( $err_values[ $key ] ) ? $err_values[ $key ] : '';
};
$sel = function ( $key, $opt ) use ( $err_values ) {
	return ( isset( $err_values[ $key ] ) && $err_values[ $key ] === $opt ) ? ' selected' : '';
};
$chk = function ( $key, $opt ) use ( $err_values ) {
	return ( isset( $err_values[ $key ] ) && $err_values[ $key ] === $opt ) ? ' checked' : '';
};

$privacy_url     = home_url( '/privacy-policy/' );
$recaptcha_key   = defined( 'BANKOFART_RECAPTCHA_SITE_KEY' ) ? constant( 'BANKOFART_RECAPTCHA_SITE_KEY' ) : '';
// 資料表紙画像。assets/img/doc-cover.{jpg,jpeg,png,webp} を置けば自動表示、無ければプレースホルダ。
// （BOA表紙3.pdf の1ページ目を画像化したものを doc-cover.* として配置する想定）
$cover_url = '';
foreach ( array( 'jpg', 'jpeg', 'png', 'webp' ) as $bankofart_cover_ext ) {
	if ( file_exists( get_theme_file_path( 'assets/img/doc-cover.' . $bankofart_cover_ext ) ) ) {
		$cover_url = get_theme_file_uri( 'assets/img/doc-cover.' . $bankofart_cover_ext );
		break;
	}
}
?>

<main id="main" class="site-main boa-form-page document-request-page">

	<!-- ヒーロー -->
	<section class="dr-hero">
		<div class="dr-hero-inner">
			<div class="dr-hero-text">
				<div class="dr-hero-eyebrow">DOCUMENT REQUEST</div>
				<h1 class="dr-hero-title">資料請求</h1>
				<p class="dr-hero-lead">
					Bank of Art のサービス資料（PDF）を無料でお届けします。<br class="br-pc">
					貴社の課題に合った活用方法を、具体例とともにご紹介します。
				</p>
			</div>
			<div class="dr-hero-cover">
				<?php if ( $cover_url ) : ?>
					<img src="<?php echo esc_url( $cover_url ); ?>" alt="BANK OF ART サービス紹介資料">
				<?php else : ?>
					<!-- 資料表紙画像 未確定：assets/img/doc-cover.jpg を配置すると差し替わる -->
					<div class="dr-cover-placeholder">
						<span class="dr-cover-en">SERVICE<br>INTRODUCTION</span>
						<span class="dr-cover-ja">サービス紹介資料</span>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="boa-form-section">
		<?php if ( '' !== $err_generic ) : ?>
			<div class="boa-form-errors" role="alert"><p><?php echo esc_html( $err_generic ); ?></p></div>
		<?php elseif ( ! empty( $err_list ) ) : ?>
			<div class="boa-form-errors" role="alert">
				<p>入力内容をご確認ください。</p>
				<ul><?php foreach ( $err_list as $m ) : ?><li><?php echo esc_html( $m ); ?></li><?php endforeach; ?></ul>
			</div>
		<?php endif; ?>

		<form class="boa-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"<?php echo $recaptcha_key ? ' id="boa-dr-form"' : ''; ?>>
			<input type="hidden" name="action" value="boa_doc_request">
			<?php wp_nonce_field( 'boa_doc_request', 'boa_doc_request_nonce' ); ?>
			<!-- ハニーポット（CSSで不可視。ボットが埋めたらスパム判定） -->
			<div class="boa-honeypot" aria-hidden="true"><label>Website<input type="text" name="website_hp" tabindex="-1" autocomplete="off"></label></div>
			<?php if ( $recaptcha_key ) : ?>
				<input type="hidden" name="g-recaptcha-response" id="boa-recaptcha-response" value="">
			<?php endif; ?>

			<div class="boa-form-subhead">会社情報</div>
			<div class="boa-field">
				<label class="boa-label" for="company_name">会社名 <span class="boa-required">必須</span></label>
				<input type="text" id="company_name" name="company_name" class="boa-input" maxlength="200" value="<?php echo esc_attr( $val( 'company_name' ) ); ?>" required>
			</div>
			<div class="boa-field">
				<label class="boa-label" for="industry">業種 <span class="boa-required">必須</span></label>
				<select id="industry" name="industry" class="boa-select" required>
					<option value="">選択してください</option>
					<?php foreach ( bankofart_doc_request_industries() as $opt ) : ?>
						<option value="<?php echo esc_attr( $opt ); ?>"<?php echo $sel( 'industry', $opt ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $opt ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<hr class="boa-section-divider">
			<div class="boa-form-subhead">ご担当者情報</div>
			<div class="boa-field">
				<label class="boa-label" for="contact_name">担当者氏名 <span class="boa-required">必須</span></label>
				<input type="text" id="contact_name" name="contact_name" class="boa-input" maxlength="100" value="<?php echo esc_attr( $val( 'contact_name' ) ); ?>" required>
			</div>
			<div class="boa-field">
				<label class="boa-label" for="contact_name_kana">担当者氏名（フリガナ） <span class="boa-optional">任意</span></label>
				<input type="text" id="contact_name_kana" name="contact_name_kana" class="boa-input" maxlength="100" value="<?php echo esc_attr( $val( 'contact_name_kana' ) ); ?>">
			</div>
			<div class="boa-field">
				<label class="boa-label" for="position">役職 <span class="boa-optional">任意</span></label>
				<input type="text" id="position" name="position" class="boa-input" maxlength="100" value="<?php echo esc_attr( $val( 'position' ) ); ?>">
			</div>
			<div class="boa-field">
				<label class="boa-label" for="email">メールアドレス <span class="boa-required">必須</span></label>
				<input type="email" id="email" name="email" class="boa-input" maxlength="255" value="<?php echo esc_attr( $val( 'email' ) ); ?>" required>
			</div>
			<div class="boa-field">
				<label class="boa-label" for="phone">電話番号 <span class="boa-optional">任意</span></label>
				<input type="tel" id="phone" name="phone" class="boa-input" maxlength="20" value="<?php echo esc_attr( $val( 'phone' ) ); ?>" pattern="[0-9\-]*">
			</div>

			<hr class="boa-section-divider">
			<div class="boa-form-subhead">ご関心について</div>
			<div class="boa-field">
				<span class="boa-label">興味度 <span class="boa-required">必須</span></span>
				<div class="boa-radio-group">
					<?php foreach ( bankofart_doc_request_interest_levels() as $opt ) : ?>
						<label class="boa-radio"><input type="radio" name="interest_level" value="<?php echo esc_attr( $opt ); ?>"<?php echo $chk( 'interest_level', $opt ); // phpcs:ignore WordPress.Security.EscapeOutput ?> required><span><?php echo esc_html( $opt ); ?></span></label>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="boa-field">
				<label class="boa-label" for="referral_source">この資料を知ったきっかけ <span class="boa-optional">任意</span></label>
				<select id="referral_source" name="referral_source" class="boa-select">
					<option value="">選択してください</option>
					<?php foreach ( bankofart_doc_request_referral_sources() as $opt ) : ?>
						<option value="<?php echo esc_attr( $opt ); ?>"<?php echo $sel( 'referral_source', $opt ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $opt ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="boa-field">
				<label class="boa-label" for="message">ご質問・ご要望 <span class="boa-optional">任意</span></label>
				<textarea id="message" name="message" class="boa-textarea" rows="5" maxlength="500"><?php echo esc_textarea( $val( 'message' ) ); ?></textarea>
			</div>

			<div class="boa-field boa-field-check">
				<label class="boa-check">
					<input type="checkbox" name="privacy" value="1">
					<span><a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener">個人情報の取り扱い</a>に同意する <span class="boa-required">必須</span></span>
				</label>
			</div>

			<p class="boa-note">
				すぐに自動返信メールにて、資料DLリンクをお送りいたします。<br class="br-pc">
				ご記入いただいた情報は、Bank of Art からの情報提供以外の目的では使用しません。
			</p>

			<div class="boa-form-actions">
				<button type="submit" class="boa-submit" id="boa-dr-submit">資料を請求する</button>
			</div>
		</form>
	</section>

</main>

<?php
get_footer();
