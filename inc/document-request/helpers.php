<?php
/**
 * 資料請求フォーム：共通関数（トークン / レートリミット / reCAPTCHA / バリデーション / 挿入）
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 業種の選択肢（仕様§4）。
 *
 * @return string[]
 */
function bankofart_doc_request_industries() {
	return array(
		'製造業', 'IT・通信', '金融・保険', '不動産', '建設', '卸売・小売',
		'飲食・宿泊', '医療・福祉', '教育', '専門サービス（士業・コンサル等）', '広告・マスコミ', 'その他',
	);
}

/**
 * 興味度の選択肢（仕様§4・必須radio）。
 *
 * @return string[]
 */
function bankofart_doc_request_interest_levels() {
	return array( '具体的に検討したい（説明会希望含む）', '情報収集の段階', '興味本位で' );
}

/**
 * 知ったきっかけの選択肢（仕様§4・任意select）。
 *
 * @return string[]
 */
function bankofart_doc_request_referral_sources() {
	return array( 'インターネット検索', 'SNS（Instagram / X / Facebook）', '知人からの紹介', 'メディア（テレビ・新聞・雑誌）', 'セミナー・イベント', 'その他' );
}

/**
 * ステータスのラベル。
 *
 * @return array<string,string>
 */
function bankofart_doc_request_statuses() {
	return array(
		'new'       => '新規',
		'followed'  => 'フォロー済み',
		'converted' => '商談・契約',
		'archived'  => 'アーカイブ',
	);
}

/**
 * DLトークンを生成する（64文字hex・ユニーク）。
 *
 * @return string
 */
function bankofart_doc_request_generate_token() {
	return wp_generate_password( 64, false, false );
}

/**
 * 申込者IPを取得する。
 *
 * @return string
 */
function bankofart_doc_request_get_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return substr( $ip, 0, 45 );
}

/**
 * IPレートリミット（同一IP・10分・3回まで）。仕様§10-3。
 *
 * @param string $ip IP。
 * @return bool ブロックすべきなら true。
 */
function bankofart_doc_request_is_rate_limited( $ip ) {
	$key   = 'boa_dr_rate_' . md5( $ip );
	$count = (int) get_transient( $key );
	if ( $count >= 3 ) {
		return true;
	}
	set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );
	return false;
}

/**
 * reCAPTCHA v3 検証。キー未定義/空ならスキップ（true）。仕様§10-2・Step9。
 *
 * ※ BANKOFART_RECAPTCHA_SITE_KEY / SECRET_KEY を define すれば有効化される。
 *
 * @param string $token g-recaptcha-response。
 * @return bool 合格なら true。
 */
function bankofart_doc_request_verify_recaptcha( $token ) {
	if ( ! defined( 'BANKOFART_RECAPTCHA_SECRET_KEY' ) || '' === constant( 'BANKOFART_RECAPTCHA_SECRET_KEY' ) ) {
		return true; // キー未取得：検証スキップ（後から有効化）。
	}
	if ( '' === $token ) {
		return false;
	}
	$res = wp_remote_post(
		'https://www.google.com/recaptcha/api/siteverify',
		array(
			'timeout' => 10,
			'body'    => array(
				'secret'   => constant( 'BANKOFART_RECAPTCHA_SECRET_KEY' ),
				'response' => $token,
				'remoteip' => bankofart_doc_request_get_ip(),
			),
		)
	);
	if ( is_wp_error( $res ) ) {
		return true; // 通信失敗時は通す（ユーザー機会損失を避ける。スパムはハニーポット/レートで抑止）。
	}
	$body = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( empty( $body['success'] ) ) {
		return false;
	}
	$score = isset( $body['score'] ) ? (float) $body['score'] : 1.0;
	return $score > 0.5; // しきい値0.5以下はブロック。
}

/**
 * 入力バリデーション（サーバー側）。
 *
 * @param array $post $_POST。
 * @return array エラーメッセージ配列（空なら合格）。
 */
function bankofart_doc_request_validate( $post ) {
	$errors = array();
	$company = isset( $post['company_name'] ) ? trim( (string) $post['company_name'] ) : '';
	$name    = isset( $post['contact_name'] ) ? trim( (string) $post['contact_name'] ) : '';
	$email   = isset( $post['email'] ) ? trim( (string) $post['email'] ) : '';
	$industry = isset( $post['industry'] ) ? trim( (string) $post['industry'] ) : '';
	$interest = isset( $post['interest_level'] ) ? trim( (string) $post['interest_level'] ) : '';
	$phone   = isset( $post['phone'] ) ? trim( (string) $post['phone'] ) : '';

	if ( '' === $company ) {
		$errors['company_name'] = '会社名をご入力ください。';
	}
	if ( '' === $name ) {
		$errors['contact_name'] = '担当者氏名をご入力ください。';
	}
	if ( '' === $email || ! is_email( $email ) ) {
		$errors['email'] = '有効なメールアドレスをご入力ください。';
	}
	if ( '' === $industry || ! in_array( $industry, bankofart_doc_request_industries(), true ) ) {
		$errors['industry'] = '業種をご選択ください。';
	}
	if ( '' === $interest || ! in_array( $interest, bankofart_doc_request_interest_levels(), true ) ) {
		$errors['interest_level'] = '興味度をご選択ください。';
	}
	if ( '' !== $phone && preg_match( '/[^0-9\-]/', $phone ) ) {
		$errors['phone'] = '電話番号は数字とハイフンでご入力ください。';
	}
	if ( empty( $post['privacy'] ) ) {
		$errors['privacy'] = '個人情報の取り扱いへの同意が必要です。';
	}
	return $errors;
}

/**
 * 1件INSERT。成功時は insert id、失敗時 0。
 *
 * @param array $data サニタイズ済みデータ（pdf_download_token 含む）。
 * @return int
 */
function bankofart_doc_request_insert( $data ) {
	global $wpdb;
	$now = current_time( 'mysql' );
	$ok  = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		bankofart_doc_request_table(),
		array(
			'company_name'       => $data['company_name'],
			'contact_name'       => $data['contact_name'],
			'contact_name_kana'  => $data['contact_name_kana'],
			'email'              => $data['email'],
			'phone'              => $data['phone'],
			'industry'           => $data['industry'],
			'position'           => $data['position'],
			'interest_level'     => $data['interest_level'],
			'referral_source'    => $data['referral_source'],
			'message'            => $data['message'],
			'pdf_download_token' => $data['pdf_download_token'],
			'pdf_download_count' => 0,
			'status'             => 'new',
			'ip_address'         => $data['ip_address'],
			'user_agent'         => $data['user_agent'],
			'created_at'         => $now,
			'updated_at'         => $now,
		),
		array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s' )
	);
	return $ok ? (int) $wpdb->insert_id : 0;
}
