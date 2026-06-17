<?php
/**
 * オンライン説明会予約：空きスロット算出（admin-ajax）
 *
 * フェーズ1：DBの既存予約（status=confirmed）と過去時刻のみ除外。
 * フェーズ2で Google Freebusy のbusyをマージ（下記フック点参照）。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 指定日の利用可能スロットを返す（JSON）。
 *
 * リクエスト：action=boa_booking_availability, date=YYYY-MM-DD, nonce
 * レスポンス：{ success, slots:[...全枠], available:[...予約可], booked:[...予約済] }
 *
 * @return void
 */
function bankofart_booking_availability() {
	check_ajax_referer( 'boa_booking', 'nonce' );

	$date = isset( $_GET['date'] ) ? sanitize_text_field( wp_unslash( $_GET['date'] ) ) : '';
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
		wp_send_json_error( array( 'message' => '日付が不正です。' ), 400 );
	}

	// 範囲チェック（当日〜30日先）。タイムゾーンは WP 設定（Asia/Tokyo）。
	$today    = current_time( 'Y-m-d' );
	$max_date = gmdate( 'Y-m-d', strtotime( $today . ' +' . BANKOFART_BOOKING_DAYS_AHEAD . ' days' ) );
	if ( $date < $today || $date > $max_date ) {
		wp_send_json_error( array( 'message' => '選択できない日付です。' ), 400 );
	}

	$all_slots = bankofart_booking_all_slots();

	// DB：当日の確定予約スロット。
	global $wpdb;
	$table  = bankofart_booking_table();
	$rows   = $wpdb->get_col( $wpdb->prepare( "SELECT DATE_FORMAT(booked_at, '%%H:%%i') FROM {$table} WHERE DATE(booked_at) = %s AND status = 'confirmed'", $date ) ); // phpcs:ignore WordPress.DB
	$booked = is_array( $rows ) ? $rows : array();

	/**
	 * ▼▼▼ フェーズ2 Google連携 差し込み口 ▼▼▼
	 * ここで Google Calendar Freebusy のbusy時間帯を取得し、$booked にマージする。
	 *   例： $booked = array_merge( $booked, bankofart_booking_gcal_busy_slots( $date ) );
	 * inc/online-booking/gcal.php（フェーズ2で作成）に bankofart_booking_gcal_busy_slots() を実装予定。
	 * ▲▲▲ フェーズ2 Google連携 差し込み口 ▲▲▲
	 */
	$booked = apply_filters( 'bankofart_booking_busy_slots', $booked, $date ); // 拡張フック。

	// 当日は過去時刻を除外。
	$now_min = -1;
	if ( $date === $today ) {
		$now_min = (int) current_time( 'G' ) * 60 + (int) current_time( 'i' );
	}

	$available = array();
	foreach ( $all_slots as $slot ) {
		if ( in_array( $slot, $booked, true ) ) {
			continue;
		}
		if ( $now_min >= 0 ) {
			list( $h, $m ) = array_map( 'intval', explode( ':', $slot ) );
			if ( $h * 60 + $m <= $now_min ) {
				continue; // 過去・直近は不可。
			}
		}
		$available[] = $slot;
	}

	wp_send_json_success(
		array(
			'slots'     => $all_slots,
			'available' => $available,
			'booked'    => array_values( $booked ),
		)
	);
}
add_action( 'wp_ajax_boa_booking_availability', 'bankofart_booking_availability' );
add_action( 'wp_ajax_nopriv_boa_booking_availability', 'bankofart_booking_availability' );
