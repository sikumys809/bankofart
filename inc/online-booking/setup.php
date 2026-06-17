<?php
/**
 * オンライン説明会予約：テーブル作成・定数・スロット定義（Notion「オンライン説明会予約システム 実装指示書」§2）
 *
 * 実装方針：プラグインではなくテーマ内実装（資料請求・リセールと同作法）。
 * フェーズ1：Google Calendar 連携なし。空きスロットは DB の既存予約のみで算出。
 * Google連携（Freebusy/イベント作成/Meet発行）はフェーズ2で inc/online-booking/ に追加する。
 *
 * dbDelta 安全化：status ENUM → VARCHAR(20)、created_at TIMESTAMP → DATETIME（コード管理）。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** DBスキーマのバージョン。 */
define( 'BANKOFART_BOOKING_DB_VERSION', '1.0.0' );

/**
 * 管理者通知メールの宛先（複数可・仮。運営確定後に差し替え）。
 */
if ( ! defined( 'BANKOFART_BOOKING_ADMIN_EMAILS' ) ) {
	define(
		'BANKOFART_BOOKING_ADMIN_EMAILS',
		array(
			'info@bankof-art.com', // 仮（後で確定）.
			'eiichi@sikumys.com',  // 仮（後で確定）.
		)
	);
}

/*
 * reCAPTCHA v3：BANKOFART_RECAPTCHA_SECRET_KEY が未定義/空なら検証スキップ
 * （資料請求と共通。キー取得後に define すれば有効化）。
 */

/** 予約枠：開始/終了/間隔（分）。仕様§1：09:00〜22:30・30分間隔。 */
define( 'BANKOFART_BOOKING_START_MIN', 9 * 60 );      // 09:00.
define( 'BANKOFART_BOOKING_END_MIN', 22 * 60 + 30 );  // 22:30.
define( 'BANKOFART_BOOKING_INTERVAL', 30 );           // 30分.
define( 'BANKOFART_BOOKING_DAYS_AHEAD', 30 );         // 当日〜30日先.

/**
 * 予約テーブル名。
 *
 * @return string
 */
function bankofart_booking_table() {
	global $wpdb;
	return $wpdb->prefix . 'boa_bookings';
}

/**
 * 全スロット（"HH:MM" 配列）を返す。
 *
 * @return string[]
 */
function bankofart_booking_all_slots() {
	$slots = array();
	for ( $m = BANKOFART_BOOKING_START_MIN; $m <= BANKOFART_BOOKING_END_MIN; $m += BANKOFART_BOOKING_INTERVAL ) {
		$slots[] = sprintf( '%02d:%02d', intdiv( $m, 60 ), $m % 60 );
	}
	return $slots;
}

/**
 * 目的の選択肢（仕様§4・固定4択）。
 *
 * @return string[]
 */
function bankofart_booking_purposes() {
	return array(
		'即時償却に興味がある',
		'画家支援に興味がある',
		'アート投資に興味がある',
		'オフィスや事務所に飾るアートを探している',
	);
}

/**
 * テーブル作成/更新（dbDelta）。
 *   - UNIQUE KEY uk_booked_at_active (booked_at, status) でダブルブッキング防止。
 *
 * @return void
 */
function bankofart_booking_create_table() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table   = bankofart_booking_table();
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table} (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  booked_at DATETIME NOT NULL,
  name VARCHAR(100) NOT NULL DEFAULT '',
  company VARCHAR(200) NOT NULL DEFAULT '',
  email VARCHAR(255) NOT NULL DEFAULT '',
  phone VARCHAR(50) NOT NULL DEFAULT '',
  purpose VARCHAR(100) NOT NULL DEFAULT '',
  gcal_event_id VARCHAR(255) DEFAULT NULL,
  meet_link VARCHAR(255) DEFAULT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'confirmed',
  ip_address VARCHAR(45) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  KEY idx_booked_at (booked_at),
  UNIQUE KEY uk_booked_at_active (booked_at, status)
) {$charset};";

	dbDelta( $sql );
	update_option( 'boa_booking_db_version', BANKOFART_BOOKING_DB_VERSION );
}
add_action( 'after_switch_theme', 'bankofart_booking_create_table' );

/**
 * バージョン差分時に作成/更新（管理画面アクセス時）。
 *
 * @return void
 */
function bankofart_booking_maybe_upgrade() {
	if ( get_option( 'boa_booking_db_version' ) !== BANKOFART_BOOKING_DB_VERSION ) {
		bankofart_booking_create_table();
	}
}
add_action( 'admin_init', 'bankofart_booking_maybe_upgrade' );
