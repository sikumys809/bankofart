<?php
/**
 * Template Name: 公認画家申請フォーム
 *
 * 公認画家申請フォーム（form-mailer 置き換え）。送信は admin-post（inc/artist-application/）。
 * 申請データは Google スプレッドシート＋Drive（GAS）で受け取り、info@ へバックアップ通知も送る。
 * 見た目は共通基盤 .boa-form 系（components.css）＋ pages/artist-application.css を流用。
 *
 * 運用：公開メニューには載せず、推測しにくいスラッグ（例：artist-application-entry-2026）の
 *   固定ページに本テンプレートを割り当て、採用候補者へ個別にURLを送付する。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// 完了画面（?app_thanks=KEY）。
$thanks = false;
if ( isset( $_GET['app_thanks'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$tk    = sanitize_text_field( wp_unslash( $_GET['app_thanks'] ) );
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
if ( ! $thanks && isset( $_GET['app_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$ek  = sanitize_text_field( wp_unslash( $_GET['app_error'] ) );
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

$privacy_url   = home_url( '/privacy-policy/' );
$recaptcha_key = defined( 'BANKOFART_RECAPTCHA_SITE_KEY' ) ? constant( 'BANKOFART_RECAPTCHA_SITE_KEY' ) : '';
$form_action   = admin_url( 'admin-post.php' );
$self_url      = get_permalink();
?>

<main id="main" class="site-main boa-form-page artist-application-page">

<?php if ( $thanks ) : ?>

	<section class="boa-form-section">
		<div class="boa-complete">
			<div class="boa-complete-check" aria-hidden="true">✓</div>
			<div class="boa-form-eyebrow">Thank you</div>
			<h1 class="boa-form-title">ご申請ありがとうございました</h1>
			<p class="boa-complete-lead">
				申請内容を確認のうえ、担当者よりご連絡いたします。<br class="br-pc">
				今しばらくお待ちくださいませ。
			</p>
			<div class="boa-form-actions">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="boa-btn-outline">トップへ</a>
			</div>
		</div>
	</section>

<?php else : ?>

	<section class="aa-hero">
		<div class="aa-hero-inner">
			<div class="boa-form-eyebrow">ARTIST APPLICATION</div>
			<h1 class="aa-hero-title">公認画家申請フォーム</h1>
			<p class="aa-hero-lead">
				BANK OF ART とともに歩む画家を募集しています。<br class="br-pc">
				下記フォームにご記入のうえ、ご申請ください。担当者より追ってご連絡いたします。
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

		<form class="boa-form aa-form" method="post" action="<?php echo esc_url( $form_action ); ?>" enctype="multipart/form-data" id="aa-form">
			<input type="hidden" name="action" value="boa_artist_application">
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( $self_url ); ?>">
			<?php wp_nonce_field( 'boa_artist_app', 'boa_artist_app_nonce' ); ?>
			<div class="boa-honeypot" aria-hidden="true"><label>Website<input type="text" name="website_hp" tabindex="-1" autocomplete="off"></label></div>
			<?php if ( $recaptcha_key ) : ?><input type="hidden" name="g-recaptcha-response" id="aa-recaptcha-response" value=""><?php endif; ?>

			<!-- ══════ 基本情報 ══════ -->
			<div class="aa-section">
				<div class="aa-section-head">
					<span class="aa-section-en">Basic</span>
					<h2 class="aa-section-title">基本情報</h2>
				</div>

				<div class="boa-field-row">
					<div class="boa-field">
						<label class="boa-label" for="name_sei">本名（姓） <span class="boa-required">必須</span></label>
						<input type="text" id="name_sei" name="name_sei" class="boa-input" maxlength="50" value="<?php echo esc_attr( $val( 'name_sei' ) ); ?>" required>
					</div>
					<div class="boa-field">
						<label class="boa-label" for="name_mei">本名（名） <span class="boa-required">必須</span></label>
						<input type="text" id="name_mei" name="name_mei" class="boa-input" maxlength="50" value="<?php echo esc_attr( $val( 'name_mei' ) ); ?>" required>
					</div>
				</div>
				<div class="boa-field">
					<label class="boa-label" for="name_kana">本名フリガナ <span class="boa-required">必須</span></label>
					<input type="text" id="name_kana" name="name_kana" class="boa-input" maxlength="100" placeholder="例：ヤマダ タロウ" value="<?php echo esc_attr( $val( 'name_kana' ) ); ?>" required>
				</div>
				<div class="boa-field">
					<label class="boa-label" for="artist_name">活動名（アーティスト名） <span class="boa-required">必須</span></label>
					<input type="text" id="artist_name" name="artist_name" class="boa-input" maxlength="100" value="<?php echo esc_attr( $val( 'artist_name' ) ); ?>" required>
					<p class="boa-help">公式サイトに掲載される名前です。</p>
				</div>
				<div class="boa-field-row">
					<div class="boa-field">
						<label class="boa-label" for="email">メールアドレス <span class="boa-required">必須</span></label>
						<input type="email" id="email" name="email" class="boa-input" maxlength="255" value="<?php echo esc_attr( $val( 'email' ) ); ?>" required>
					</div>
					<div class="boa-field">
						<label class="boa-label" for="email_confirm">メールアドレス（確認用） <span class="boa-required">必須</span></label>
						<input type="email" id="email_confirm" name="email_confirm" class="boa-input" maxlength="255" value="<?php echo esc_attr( $val( 'email_confirm' ) ); ?>" required>
					</div>
				</div>
				<div class="boa-field">
					<label class="boa-label" for="phone">電話番号 <span class="boa-required">必須</span></label>
					<input type="tel" id="phone" name="phone" class="boa-input" maxlength="20" placeholder="ハイフンなし可" value="<?php echo esc_attr( $val( 'phone' ) ); ?>" pattern="[0-9\-]*" required>
				</div>

				<div class="boa-field">
					<span class="boa-label">住所</span>
					<div class="aa-address">
						<input type="text" name="postal_code" class="boa-input aa-addr-postal" maxlength="8" placeholder="郵便番号（例：150-0001）" value="<?php echo esc_attr( $val( 'postal_code' ) ); ?>">
						<select name="pref" class="boa-select aa-addr-pref">
							<option value="">都道府県</option>
							<?php foreach ( bankofart_artist_app_prefectures() as $opt ) : ?>
								<option value="<?php echo esc_attr( $opt ); ?>"<?php echo $sel( 'pref', $opt ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $opt ); ?></option>
							<?php endforeach; ?>
						</select>
						<input type="text" name="city" class="boa-input aa-addr-city" maxlength="100" placeholder="市区町村" value="<?php echo esc_attr( $val( 'city' ) ); ?>">
						<input type="text" name="address1" class="boa-input aa-addr-line" maxlength="150" placeholder="番地" value="<?php echo esc_attr( $val( 'address1' ) ); ?>">
						<input type="text" name="building" class="boa-input aa-addr-line" maxlength="150" placeholder="建物名・部屋番号" value="<?php echo esc_attr( $val( 'building' ) ); ?>">
					</div>
				</div>

				<div class="boa-field-row">
					<div class="boa-field">
						<label class="boa-label" for="gender">性別</label>
						<select id="gender" name="gender" class="boa-select">
							<option value="">選択してください</option>
							<?php foreach ( bankofart_artist_app_genders() as $opt ) : ?>
								<option value="<?php echo esc_attr( $opt ); ?>"<?php echo $sel( 'gender', $opt ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $opt ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="boa-field">
						<label class="boa-label" for="birthday">生年月日</label>
						<input type="date" id="birthday" name="birthday" class="boa-input" value="<?php echo esc_attr( $val( 'birthday' ) ); ?>">
					</div>
				</div>
				<div class="boa-field">
					<label class="boa-label" for="career">経歴 <span class="boa-required">必須</span></label>
					<textarea id="career" name="career" class="boa-textarea" rows="5" maxlength="2000" required><?php echo esc_textarea( $val( 'career' ) ); ?></textarea>
				</div>
			</div>

			<!-- ══════ 公開プロフィール情報 ══════ -->
			<div class="aa-section aa-section-public">
				<div class="aa-section-head">
					<span class="aa-section-en">Public Profile</span>
					<h2 class="aa-section-title">公開プロフィール情報</h2>
					<span class="aa-visibility aa-visibility-public">公式サイトに掲載されます</span>
				</div>

				<div class="boa-field">
					<label class="boa-label" for="theme_short">制作テーマ（13字以内） <span class="boa-required">必須</span></label>
					<input type="text" id="theme_short" name="theme_short" class="boa-input" maxlength="13" value="<?php echo esc_attr( $val( 'theme_short' ) ); ?>" required>
				</div>
				<div class="boa-field">
					<label class="boa-label" for="theme_long">制作テーマ詳細 <span class="boa-required">必須</span></label>
					<textarea id="theme_long" name="theme_long" class="boa-textarea" rows="4" maxlength="2000" required><?php echo esc_textarea( $val( 'theme_long' ) ); ?></textarea>
				</div>
				<div class="boa-field">
					<label class="boa-label" for="reason">なぜ描くか <span class="boa-required">必須</span></label>
					<textarea id="reason" name="reason" class="boa-textarea" rows="4" maxlength="2000" required><?php echo esc_textarea( $val( 'reason' ) ); ?></textarea>
				</div>
				<div class="boa-field">
					<label class="boa-label" for="origin">起源の物語 <span class="boa-required">必須</span></label>
					<textarea id="origin" name="origin" class="boa-textarea" rows="6" maxlength="4000" required><?php echo esc_textarea( $val( 'origin' ) ); ?></textarea>
					<p class="boa-help">あなたが描き始めた原点の物語です。最低200字程度を推奨します。</p>
				</div>
				<div class="boa-field">
					<label class="boa-label" for="goal">画家としての目標・ゴール</label>
					<textarea id="goal" name="goal" class="boa-textarea" rows="4" maxlength="2000"><?php echo esc_textarea( $val( 'goal' ) ); ?></textarea>
				</div>
				<div class="boa-field">
					<label class="boa-label" for="solo_exh">個展歴</label>
					<textarea id="solo_exh" name="solo_exh" class="boa-textarea" rows="3" maxlength="2000" placeholder="改行区切りで複数ご記入いただけます"><?php echo esc_textarea( $val( 'solo_exh' ) ); ?></textarea>
				</div>
				<div class="boa-field">
					<label class="boa-label" for="group_exh">グループ展歴</label>
					<textarea id="group_exh" name="group_exh" class="boa-textarea" rows="3" maxlength="2000" placeholder="改行区切り"><?php echo esc_textarea( $val( 'group_exh' ) ); ?></textarea>
				</div>
				<div class="boa-field">
					<label class="boa-label" for="awards">受賞・メディア歴</label>
					<textarea id="awards" name="awards" class="boa-textarea" rows="3" maxlength="2000" placeholder="改行区切り"><?php echo esc_textarea( $val( 'awards' ) ); ?></textarea>
				</div>

				<div class="boa-field">
					<span class="boa-label">SNS / リンク <span class="boa-optional">任意</span></span>
					<div class="aa-sns">
						<input type="url" name="sns_instagram" class="boa-input" placeholder="Instagram URL" value="<?php echo esc_attr( $val( 'sns_instagram' ) ); ?>">
						<input type="url" name="sns_x" class="boa-input" placeholder="X（旧Twitter）URL" value="<?php echo esc_attr( $val( 'sns_x' ) ); ?>">
						<input type="url" name="sns_facebook" class="boa-input" placeholder="Facebook URL" value="<?php echo esc_attr( $val( 'sns_facebook' ) ); ?>">
						<input type="url" name="sns_youtube" class="boa-input" placeholder="YouTube URL" value="<?php echo esc_attr( $val( 'sns_youtube' ) ); ?>">
						<input type="url" name="sns_other" class="boa-input" placeholder="その他 URL" value="<?php echo esc_attr( $val( 'sns_other' ) ); ?>">
					</div>
				</div>
			</div>

			<!-- ══════ 契約情報（非公開）══════ -->
			<div class="aa-section aa-section-private">
				<div class="aa-section-head">
					<span class="aa-section-en">Contract</span>
					<h2 class="aa-section-title">契約情報</h2>
					<span class="aa-visibility aa-visibility-private">非公開・振込用</span>
				</div>
				<p class="boa-help aa-private-note">こちらの情報はサイトには掲載されません。買取・お振込みのためにのみ使用します。</p>

				<div class="boa-field-row">
					<div class="boa-field">
						<label class="boa-label" for="bank_name">銀行名</label>
						<input type="text" id="bank_name" name="bank_name" class="boa-input" maxlength="100" value="<?php echo esc_attr( $val( 'bank_name' ) ); ?>">
					</div>
					<div class="boa-field">
						<label class="boa-label" for="bank_branch">支店名</label>
						<input type="text" id="bank_branch" name="bank_branch" class="boa-input" maxlength="100" value="<?php echo esc_attr( $val( 'bank_branch' ) ); ?>">
					</div>
				</div>
				<div class="boa-field-row">
					<div class="boa-field">
						<label class="boa-label" for="bank_account_type">口座種別</label>
						<select id="bank_account_type" name="bank_account_type" class="boa-select">
							<option value="">選択してください</option>
							<?php foreach ( bankofart_artist_app_account_types() as $opt ) : ?>
								<option value="<?php echo esc_attr( $opt ); ?>"<?php echo $sel( 'bank_account_type', $opt ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $opt ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="boa-field">
						<label class="boa-label" for="bank_account_number">口座番号</label>
						<input type="text" id="bank_account_number" name="bank_account_number" class="boa-input" maxlength="20" inputmode="numeric" pattern="[0-9]*" value="<?php echo esc_attr( $val( 'bank_account_number' ) ); ?>">
					</div>
				</div>
				<div class="boa-field">
					<label class="boa-label" for="bank_account_holder">口座名義（カナ）</label>
					<input type="text" id="bank_account_holder" name="bank_account_holder" class="boa-input" maxlength="100" placeholder="例：ヤマダ タロウ" value="<?php echo esc_attr( $val( 'bank_account_holder' ) ); ?>">
				</div>
			</div>

			<!-- ══════ 画像アップロード ══════ -->
			<div class="aa-section aa-section-public">
				<div class="aa-section-head">
					<span class="aa-section-en">Images</span>
					<h2 class="aa-section-title">画像アップロード</h2>
					<span class="aa-visibility aa-visibility-public">作品・プロフィールに使用</span>
				</div>
				<p class="boa-help">JPG / PNG・1枚あたり <?php echo (int) ( BANKOFART_ARTIST_APP_MAX_IMAGE_BYTES / 1024 / 1024 ); ?>MB 以内。合計 <?php echo (int) ( BANKOFART_ARTIST_APP_MAX_TOTAL_BYTES / 1024 / 1024 ); ?>MB まで。</p>

				<div class="boa-field">
					<label class="boa-label" for="main_image">メイン画像（1枚）</label>
					<input type="file" id="main_image" name="main_image" class="aa-file" accept="image/jpeg,image/png">
				</div>
				<div class="boa-field">
					<label class="boa-label" for="symbol_image">自己を象徴する画像（1枚）</label>
					<input type="file" id="symbol_image" name="symbol_image" class="aa-file" accept="image/jpeg,image/png">
				</div>
				<div class="boa-field">
					<label class="boa-label" for="work_images">制作風景・画材・制作環境の写真（複数可）</label>
					<input type="file" id="work_images" name="work_images[]" class="aa-file" accept="image/jpeg,image/png" multiple>
					<p class="boa-help">最大 <?php echo (int) BANKOFART_ARTIST_APP_MAX_WORK_IMAGES; ?>枚までアップロードできます。たくさんお寄せいただけると嬉しいです。</p>
				</div>
			</div>

			<!-- ══════ 同意 ══════ -->
			<div class="aa-section">
				<div class="boa-field boa-field-check">
					<label class="boa-check">
						<input type="checkbox" name="agreed" value="1">
						<span><a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener">個人情報の取り扱い・規約</a>に同意する <span class="boa-required">必須</span></span>
					</label>
				</div>
			</div>

			<p class="boa-note">
				ご記入いただいた情報は、公認画家としての選考・ご連絡・お振込みの目的にのみ使用します。<br class="br-pc">
				公開プロフィール情報・画像は、選考通過後に BANK OF ART 公式サイトへの掲載に使用させていただく場合があります。
			</p>

			<div class="boa-form-actions">
				<button type="submit" class="boa-submit" id="aa-submit">この内容で申請する</button>
			</div>
		</form>
	</section>

<?php endif; ?>

</main>

<?php
get_footer();
