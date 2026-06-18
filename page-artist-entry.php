<?php
/**
 * Template Name: 画家応募フォーム
 *
 * 画家応募フォーム（選考用・公開）。送信は admin-post（inc/artist-entry/）。
 * 応募データは応募用 Google スプレッドシート＋Drive（GAS）で受け取り、info@ へバックアップ通知も送る。
 * 見た目は共通基盤 .boa-form 系（components.css）＋ pages/artist-application.css を共用。
 *
 * 運用：公開ページ（スラッグ artist-entry）。recruit ページ・FOR ARTISTS バナーの「応募する」から誘導。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// 完了画面（?entry_thanks=KEY）。
$thanks = false;
if ( isset( $_GET['entry_thanks'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$tk    = sanitize_text_field( wp_unslash( $_GET['entry_thanks'] ) );
	$stash = get_transient( $tk );
	if ( is_array( $stash ) ) {
		$thanks = true;
		delete_transient( $tk );
	}
}

// エラー再表示（transientキー or 既定コード）。
$err_values  = array();
$err_list    = array();
$err_generic = '';
if ( ! $thanks && isset( $_GET['entry_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$ek  = sanitize_text_field( wp_unslash( $_GET['entry_error'] ) );
	$map = array(
		'recaptcha'  => '送信を確認できませんでした。お手数ですが再度お試しください。',
		'rate_limit' => '短時間に複数回送信されています。しばらく時間をおいて再度お試しください。',
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

$privacy_url    = home_url( '/privacy-policy/' );
$guidelines_url = function_exists( 'bankofart_recruit_guidelines_pdf_url' ) ? bankofart_recruit_guidelines_pdf_url() : home_url( '/recruit/' );
$recaptcha_key  = defined( 'BANKOFART_RECAPTCHA_SITE_KEY' ) ? constant( 'BANKOFART_RECAPTCHA_SITE_KEY' ) : '';
$form_action    = admin_url( 'admin-post.php' );
$self_url       = get_permalink();
?>

<main id="main" class="site-main boa-form-page artist-application-page artist-entry-page">

<?php if ( $thanks ) : ?>

	<section class="boa-form-section">
		<div class="boa-complete">
			<div class="boa-complete-check" aria-hidden="true">✓</div>
			<div class="boa-form-eyebrow">Thank you</div>
			<h1 class="boa-form-title">ご応募ありがとうございました</h1>
			<p class="boa-complete-lead">
				選考のうえ、担当者よりご連絡いたします。<br class="br-pc">
				今しばらくお待ちくださいませ。
			</p>
			<div class="boa-form-actions">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="boa-btn-outline">トップへ</a>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'artist' ) ); ?>" class="boa-btn-outline">アーティスト一覧を見る</a>
			</div>
		</div>
	</section>

<?php else : ?>

	<section class="aa-hero">
		<div class="aa-hero-inner">
			<div class="boa-form-eyebrow">ARTIST ENTRY</div>
			<h1 class="aa-hero-title">画家応募フォーム</h1>
			<p class="aa-hero-lead">
				BANK OF ART は、ともに歩む画家を募集しています。<br class="br-pc">
				下記フォームとポートフォリオ（PDF）をご提出ください。選考のうえご連絡いたします。
			</p>
		</div>
	</section>

	<section class="boa-form-section aa-form-section">

		<?php if ( '' !== $err_generic ) : ?>
			<div class="boa-form-errors" role="alert"><p><?php echo esc_html( $err_generic ); ?></p></div>
		<?php elseif ( ! empty( $err_list ) ) : ?>
			<div class="boa-form-errors" role="alert">
				<p>入力内容をご確認ください。</p>
				<ul><?php foreach ( $err_list as $m ) : ?><li><?php echo esc_html( $m ); ?></li><?php endforeach; ?></ul>
			</div>
		<?php endif; ?>

		<form class="boa-form aa-form" method="post" action="<?php echo esc_url( $form_action ); ?>" enctype="multipart/form-data" id="ae-form">
			<input type="hidden" name="action" value="boa_artist_entry">
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( $self_url ); ?>">
			<?php wp_nonce_field( 'boa_artist_entry', 'boa_artist_entry_nonce' ); ?>
			<div class="boa-honeypot" aria-hidden="true"><label>Website<input type="text" name="website_hp" tabindex="-1" autocomplete="off"></label></div>
			<?php if ( $recaptcha_key ) : ?><input type="hidden" name="g-recaptcha-response" id="ae-recaptcha-response" value=""><?php endif; ?>

			<!-- ══════ 基本情報 ══════ -->
			<div class="aa-section">
				<div class="aa-section-head">
					<span class="aa-section-en">Basic</span>
					<h2 class="aa-section-title">基本情報</h2>
				</div>

				<div class="boa-field-row">
					<div class="boa-field">
						<label class="boa-label" for="name">お名前 <span class="boa-required">必須</span></label>
						<input type="text" id="name" name="name" class="boa-input" maxlength="100" value="<?php echo esc_attr( $val( 'name' ) ); ?>" required>
					</div>
					<div class="boa-field">
						<label class="boa-label" for="name_kana">フリガナ <span class="boa-required">必須</span></label>
						<input type="text" id="name_kana" name="name_kana" class="boa-input" maxlength="100" placeholder="例：ヤマダ タロウ" value="<?php echo esc_attr( $val( 'name_kana' ) ); ?>" required>
					</div>
				</div>
				<div class="boa-field">
					<label class="boa-label" for="artist_name">アーティスト名 <span class="boa-required">必須</span></label>
					<input type="text" id="artist_name" name="artist_name" class="boa-input" maxlength="100" value="<?php echo esc_attr( $val( 'artist_name' ) ); ?>" required>
					<p class="boa-help">公式サイトに掲載される活動名です。</p>
				</div>
				<div class="boa-field-row">
					<div class="boa-field">
						<label class="boa-label" for="age">年齢 <span class="boa-required">必須</span></label>
						<input type="text" id="age" name="age" class="boa-input" maxlength="3" inputmode="numeric" pattern="[0-9]*" value="<?php echo esc_attr( $val( 'age' ) ); ?>" required>
					</div>
					<div class="boa-field">
						<label class="boa-label" for="base">制作拠点 <span class="boa-required">必須</span></label>
						<input type="text" id="base" name="base" class="boa-input" maxlength="100" placeholder="例：東京都 / 兵庫県神戸市" value="<?php echo esc_attr( $val( 'base' ) ); ?>" required>
					</div>
				</div>
				<div class="boa-field-row">
					<div class="boa-field">
						<label class="boa-label" for="email">メールアドレス <span class="boa-required">必須</span></label>
						<input type="email" id="email" name="email" class="boa-input" maxlength="255" value="<?php echo esc_attr( $val( 'email' ) ); ?>" required>
					</div>
					<div class="boa-field">
						<label class="boa-label" for="phone">電話番号 <span class="boa-required">必須</span></label>
						<input type="tel" id="phone" name="phone" class="boa-input" maxlength="20" placeholder="ハイフンなし可" value="<?php echo esc_attr( $val( 'phone' ) ); ?>" pattern="[0-9\-]*" required>
					</div>
				</div>
				<div class="boa-field">
					<label class="boa-label" for="career">経歴 <span class="boa-optional">任意</span></label>
					<textarea id="career" name="career" class="boa-textarea" rows="4" maxlength="2000"><?php echo esc_textarea( $val( 'career' ) ); ?></textarea>
				</div>
				<div class="boa-field">
					<label class="boa-label" for="exhibitions">展示歴 <span class="boa-optional">任意</span></label>
					<textarea id="exhibitions" name="exhibitions" class="boa-textarea" rows="3" maxlength="2000" placeholder="改行区切りで複数ご記入いただけます"><?php echo esc_textarea( $val( 'exhibitions' ) ); ?></textarea>
				</div>
				<div class="boa-field">
					<label class="boa-label" for="awards">受賞歴 <span class="boa-optional">任意</span></label>
					<textarea id="awards" name="awards" class="boa-textarea" rows="3" maxlength="2000" placeholder="改行区切り"><?php echo esc_textarea( $val( 'awards' ) ); ?></textarea>
				</div>
			</div>

			<!-- ══════ あなたについて ══════ -->
			<div class="aa-section aa-section-public">
				<div class="aa-section-head">
					<span class="aa-section-en">About You</span>
					<h2 class="aa-section-title">あなたについて</h2>
				</div>

				<div class="boa-field">
					<label class="boa-label" for="why_paint">なぜ絵を描くか <span class="boa-required">必須</span></label>
					<textarea id="why_paint" name="why_paint" class="boa-textarea" rows="5" maxlength="3000" required><?php echo esc_textarea( $val( 'why_paint' ) ); ?></textarea>
				</div>
				<div class="boa-field">
					<label class="boa-label" for="origin">画家としての起源 <span class="boa-required">必須</span></label>
					<textarea id="origin" name="origin" class="boa-textarea" rows="5" maxlength="3000" required><?php echo esc_textarea( $val( 'origin' ) ); ?></textarea>
				</div>
				<div class="boa-field">
					<label class="boa-label" for="future">どんな画家になりたいか <span class="boa-optional">任意</span></label>
					<textarea id="future" name="future" class="boa-textarea" rows="4" maxlength="2000"><?php echo esc_textarea( $val( 'future' ) ); ?></textarea>
				</div>
				<div class="boa-field">
					<label class="boa-label" for="boa_relation">BANK OF ART とどう関わりたいか <span class="boa-optional">任意</span></label>
					<textarea id="boa_relation" name="boa_relation" class="boa-textarea" rows="4" maxlength="2000"><?php echo esc_textarea( $val( 'boa_relation' ) ); ?></textarea>
				</div>
			</div>

			<!-- ══════ 制作活動 ══════ -->
			<div class="aa-section">
				<div class="aa-section-head">
					<span class="aa-section-en">Practice</span>
					<h2 class="aa-section-title">制作活動</h2>
				</div>

				<div class="boa-field-row">
					<div class="boa-field">
						<label class="boa-label" for="pace">直近1ヶ月の制作ペース <span class="boa-optional">任意</span></label>
						<select id="pace" name="pace" class="boa-select">
							<option value="">選択してください</option>
							<?php foreach ( bankofart_artist_entry_pace_options() as $opt ) : ?>
								<option value="<?php echo esc_attr( $opt ); ?>"<?php echo $sel( 'pace', $opt ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $opt ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="boa-field">
						<label class="boa-label" for="income">現在の主な収入源 <span class="boa-optional">任意</span></label>
						<select id="income" name="income" class="boa-select">
							<option value="">選択してください</option>
							<?php foreach ( bankofart_artist_entry_income_options() as $opt ) : ?>
								<option value="<?php echo esc_attr( $opt ); ?>"<?php echo $sel( 'income', $opt ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $opt ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="boa-field">
					<label class="boa-label" for="sns">SNS・ウェブサイト <span class="boa-optional">任意</span></label>
					<input type="url" id="sns" name="sns" class="boa-input" maxlength="255" placeholder="Instagram / X / ポートフォリオサイト等のURL" value="<?php echo esc_attr( $val( 'sns' ) ); ?>">
				</div>
			</div>

			<!-- ══════ ポートフォリオ ══════ -->
			<div class="aa-section aa-section-public">
				<div class="aa-section-head">
					<span class="aa-section-en">Portfolio</span>
					<h2 class="aa-section-title">ポートフォリオ</h2>
				</div>
				<p class="boa-help">PDF形式・<?php echo (int) ( BANKOFART_ARTIST_ENTRY_MAX_PDF_BYTES / 1024 / 1024 ); ?>MB 以内。作品をまとめた1ファイルをご提出ください。</p>
				<div class="boa-field">
					<label class="boa-label" for="portfolio_file">ポートフォリオPDF</label>
					<input type="file" id="portfolio_file" name="portfolio_file" class="aa-file" accept="application/pdf,.pdf">
				</div>
			</div>

			<!-- ══════ 確認事項 ══════ -->
			<div class="aa-section">
				<div class="aa-section-head">
					<span class="aa-section-en">Confirm</span>
					<h2 class="aa-section-title">確認事項</h2>
				</div>
				<div class="boa-field boa-field-check">
					<label class="boa-check">
						<input type="checkbox" name="agree_guidelines" value="1">
						<span><a href="<?php echo esc_url( $guidelines_url ); ?>" target="_blank" rel="noopener">募集要項</a>の内容を確認し、同意する <span class="boa-required">必須</span></span>
					</label>
				</div>
				<div class="boa-field boa-field-check">
					<label class="boa-check">
						<input type="checkbox" name="agree_privacy" value="1">
						<span><a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener">個人情報の取り扱い</a>に同意する <span class="boa-required">必須</span></span>
					</label>
				</div>
			</div>

			<p class="boa-note">
				ご記入いただいた情報・ポートフォリオは、選考およびご連絡の目的にのみ使用します。
			</p>

			<div class="boa-form-actions">
				<button type="submit" class="boa-submit" id="ae-submit">この内容で応募する</button>
			</div>
		</form>
	</section>

<?php endif; ?>

</main>

<?php
get_footer();
