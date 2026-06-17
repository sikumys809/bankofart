<?php
/**
 * 資料請求フォーム：テーブル作成・定数定義（Notion「資料請求フォーム 実装指示書」§3）
 *
 * dbDelta 安全化のための実装上の読み替え：
 *   - status ENUM → VARCHAR(20)（dbDelta は ENUM 再実行で不整合を起こしやすい）
 *   - created_at/updated_at TIMESTAMP ON UPDATE → DATETIME（値はコードで current_time 管理）
 * 機能は仕様と同一。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** DBスキーマのバージョン。 */
define( 'BANKOFART_DOC_REQUEST_DB_VERSION', '1.0.0' );

/**
 * 管理者通知メールの宛先（共通の連絡先メールを参照）。
 * 宛先の変更は inc/helpers.php の BANKOFART_CONTACT_EMAIL で一元管理。
 */
if ( ! defined( 'BANKOFART_DOC_REQUEST_ADMIN_EMAILS' ) ) {
	define(
		'BANKOFART_DOC_REQUEST_ADMIN_EMAILS',
		array(
			defined( 'BANKOFART_CONTACT_EMAIL' ) ? BANKOFART_CONTACT_EMAIL : 'info@bankof-art.com',
		)
	);
}

/*
 * reCAPTCHA v3 キー（Step9）。
 * キー取得後に wp-config.php 等で下記を define すれば自動的に検証が有効化される。
 * 未定義/空の間はサーバー側検証をスキップする（form-handler.php 参照）。
 *   define( 'BANKOFART_RECAPTCHA_SITE_KEY',   '...' );
 *   define( 'BANKOFART_RECAPTCHA_SECRET_KEY', '...' );
 */

/**
 * 資料請求テーブル名。
 *
 * @return string
 */
function bankofart_doc_request_table() {
	global $wpdb;
	return $wpdb->prefix . 'boa_document_requests';
}

/**
 * テーブルを作成/更新する（dbDelta）。
 *
 * @return void
 */
function bankofart_doc_request_create_table() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table   = bankofart_doc_request_table();
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table} (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_name VARCHAR(200) NOT NULL DEFAULT '',
  contact_name VARCHAR(100) NOT NULL DEFAULT '',
  contact_name_kana VARCHAR(100) NULL,
  email VARCHAR(255) NOT NULL DEFAULT '',
  phone VARCHAR(20) NULL,
  industry VARCHAR(100) NULL,
  position VARCHAR(100) NULL,
  interest_level VARCHAR(50) NULL,
  referral_source VARCHAR(100) NULL,
  message TEXT NULL,
  pdf_download_token VARCHAR(64) NOT NULL DEFAULT '',
  pdf_downloaded_at DATETIME NULL,
  pdf_download_count INT NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'new',
  admin_notes TEXT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  updated_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  UNIQUE KEY uk_token (pdf_download_token),
  KEY idx_email (email),
  KEY idx_status (status),
  KEY idx_created (created_at)
) {$charset};";

	dbDelta( $sql );
	update_option( 'boa_doc_request_db_version', BANKOFART_DOC_REQUEST_DB_VERSION );
}
add_action( 'after_switch_theme', 'bankofart_doc_request_create_table' );

/**
 * バージョン差分時にテーブルを作成/更新（管理画面アクセス時）。
 *
 * @return void
 */
function bankofart_doc_request_maybe_upgrade() {
	if ( get_option( 'boa_doc_request_db_version' ) !== BANKOFART_DOC_REQUEST_DB_VERSION ) {
		bankofart_doc_request_create_table();
	}
}
add_action( 'admin_init', 'bankofart_doc_request_maybe_upgrade' );
