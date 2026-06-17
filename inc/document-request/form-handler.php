<?php
/**
 * 資料請求フォーム：送信処理（admin-post）＋ PDFトークン配信（仕様§6）
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 資料請求の送信を処理する。
 *
 * @return void
 */
function bankofart_handle_doc_request() {
	$form_url     = home_url( '/document-request/' );
	$complete_url = home_url( '/document-request/complete/' );

	// 1) nonce。
	check_admin_referer( 'boa_doc_request', 'boa_doc_request_nonce' );

	// 2) ハニーポット（仕様§10-1）。入力があればスパム扱いで黙って完了風に流す。
	if ( ! empty( $_POST['website_hp'] ) ) {
		wp_safe_redirect( $form_url );
		exit;
	}

	// 3) reCAPTCHA（キー未定義ならスキップ。仕様§10-2）。
	$recaptcha = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';
	if ( ! bankofart_doc_request_verify_recaptcha( $recaptcha ) ) {
		wp_safe_redirect( add_query_arg( 'dr_error', 'recaptcha', $form_url ) );
		exit;
	}

	// 4) レートリミット（仕様§10-3）。
	if ( bankofart_doc_request_is_rate_limited( bankofart_doc_request_get_ip() ) ) {
		wp_safe_redirect( add_query_arg( 'dr_error', 'rate_limit', $form_url ) );
		exit;
	}

	// 5) バリデーション。
	$errors = bankofart_doc_request_validate( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	// 入力値（サニタイズ）。
	$data = array(
		'company_name'      => isset( $_POST['company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['company_name'] ) ) : '',
		'contact_name'      => isset( $_POST['contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_name'] ) ) : '',
		'contact_name_kana' => isset( $_POST['contact_name_kana'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_name_kana'] ) ) : '',
		'email'             => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
		'phone'             => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
		'industry'          => isset( $_POST['industry'] ) ? sanitize_text_field( wp_unslash( $_POST['industry'] ) ) : '',
		'position'          => isset( $_POST['position'] ) ? sanitize_text_field( wp_unslash( $_POST['position'] ) ) : '',
		'interest_level'    => isset( $_POST['interest_level'] ) ? sanitize_text_field( wp_unslash( $_POST['interest_level'] ) ) : '',
		'referral_source'   => isset( $_POST['referral_source'] ) ? sanitize_text_field( wp_unslash( $_POST['referral_source'] ) ) : '',
		'message'           => isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '',
		'privacy'           => ! empty( $_POST['privacy'] ) ? 1 : 0,
	);

	if ( ! empty( $errors ) ) {
		$key = 'boa_dr_' . wp_generate_password( 12, false );
		set_transient(
			$key,
			array(
				'errors' => $errors,
				'values' => $data,
			),
			5 * MINUTE_IN_SECONDS
		);
		wp_safe_redirect( add_query_arg( 'dr_error', $key, $form_url ) );
		exit;
	}

	// 6) DB挿入。
	$token = bankofart_doc_request_generate_token();
	$data['pdf_download_token'] = $token;
	$data['ip_address']         = bankofart_doc_request_get_ip();
	$data['user_agent']         = isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 255 ) : '';
	$insert_id = bankofart_doc_request_insert( $data );

	if ( ! $insert_id ) {
		wp_safe_redirect( add_query_arg( 'dr_error', 'db', $form_url ) );
		exit;
	}

	// 7) メール（申込者＝DLリンク付き / 管理者通知）。
	bankofart_send_doc_request_user_mail( $insert_id, $token );
	bankofart_send_doc_request_admin_mail( $insert_id );

	// 8) 完了画面へ（PRG・トークン付き）。
	wp_safe_redirect( add_query_arg( 'token', $token, $complete_url ) );
	exit;
}
add_action( 'admin_post_nopriv_boa_doc_request', 'bankofart_handle_doc_request' );
add_action( 'admin_post_boa_doc_request', 'bankofart_handle_doc_request' );

/**
 * PDFダウンロード処理（?boa_pdf_download={token}）。DL履歴を更新して配信。
 *
 * @return void
 */
function bankofart_handle_pdf_download() {
	if ( ! isset( $_GET['boa_pdf_download'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- トークン自体が認可キー。
		return;
	}
	$token = sanitize_text_field( wp_unslash( $_GET['boa_pdf_download'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	global $wpdb;
	$table   = bankofart_doc_request_table();
	$request = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE pdf_download_token = %s", $token ) ); // phpcs:ignore WordPress.DB

	if ( ! $request ) {
		wp_die( esc_html__( '無効なダウンロードリンクです。', 'bankofart' ), '', array( 'response' => 404 ) );
	}

	// DL履歴更新。
	$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$table,
		array(
			'pdf_downloaded_at'  => current_time( 'mysql' ),
			'pdf_download_count' => (int) $request->pdf_download_count + 1,
			'updated_at'         => current_time( 'mysql' ),
		),
		array( 'id' => $request->id ),
		array( '%s', '%d', '%s' ),
		array( '%d' )
	);

	// PDF配信。※本番PDF（assets/docs/BOA-service-introduction.pdf）が未配置の場合は案内を表示。
	$pdf_path = get_theme_file_path( 'assets/docs/BOA-service-introduction.pdf' );
	if ( ! file_exists( $pdf_path ) ) {
		wp_die(
			esc_html__( '資料PDFを準備中です。お手数ですが、後ほど再度お試しいただくか、お問い合わせください。（本番PDF未配置）', 'bankofart' ),
			esc_html__( '資料準備中', 'bankofart' ),
			array( 'response' => 503 )
		);
	}

	nocache_headers();
	header( 'Content-Type: application/pdf' );
	header( 'Content-Disposition: attachment; filename="BANK_OF_ART_service_introduction.pdf"' );
	header( 'Content-Length: ' . filesize( $pdf_path ) );
	readfile( $pdf_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
	exit;
}
add_action( 'init', 'bankofart_handle_pdf_download' );
