<?php
/**
 * 資料請求フォーム：CSVエクスポート（UTF-8 BOM付き・仕様§8）
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CSV出力（管理者のみ・nonce保護）。
 *
 * @return void
 */
function bankofart_export_doc_requests_csv() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( '権限がありません。', 'bankofart' ) );
	}
	check_admin_referer( 'boa_dr_export', 'boa_dr_export_nonce' );

	global $wpdb;
	$table = bankofart_doc_request_table();
	$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC", ARRAY_A ); // phpcs:ignore WordPress.DB

	$filename = 'document-requests_' . gmdate( 'Ymd_His' ) . '.csv';
	nocache_headers();
	header( 'Content-Type: text/csv; charset=UTF-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

	$out = fopen( 'php://output', 'w' );
	echo "\xEF\xBB\xBF"; // UTF-8 BOM（Excel 文字化け防止）。

	fputcsv(
		$out,
		array( '申込ID', '申込日時', '会社名', '担当者氏名', 'フリガナ', 'メール', '電話', '業種', '役職', '興味度', 'きっかけ', 'ご質問', 'ステータス', '管理者メモ', 'DL日時', 'DL回数' )
	);

	if ( $rows ) {
		foreach ( $rows as $r ) {
			// fputcsv が "" エスケープと改行の引用を自動処理する。
			fputcsv(
				$out,
				array(
					$r['id'], $r['created_at'], $r['company_name'], $r['contact_name'], $r['contact_name_kana'],
					$r['email'], $r['phone'], $r['industry'], $r['position'], $r['interest_level'],
					$r['referral_source'], $r['message'], $r['status'], $r['admin_notes'],
					$r['pdf_downloaded_at'], $r['pdf_download_count'],
				)
			);
		}
	}
	fclose( $out );
	exit;
}
add_action( 'admin_post_boa_doc_request_export', 'bankofart_export_doc_requests_csv' );
