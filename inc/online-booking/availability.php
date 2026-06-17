<?php
/**
 * オンライン説明会予約：空きスロット算出（admin-ajax）
 *
 * フェーズ1：DBの既存予約（status=confirmed）と過去時刻のみ除外。
 * フェーズ2で Google Freebusy のbusyをマージ（bankofart_booking_busy_slots フィルタ）。
 *
 * エンドポイント：
 *   - action=boa_booking_availability&date=YYYY-MM-DD … 単日（互換用）
 *   - action=boa_booking_week&start=YYYY-MM-DD       … 週表示用（7日分まとめて）
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 指定日の利用可能スロット（"HH:MM"配列）を返す。範囲外は空配列。
 *
 * @param string $date YYYY-MM-DD。
 * @return string[]
 */
function bankofart_booking_available_for_date( $date ) {
	$today    = current_time( 'Y-m-d' );
	$max_date = gmdate( 'Y-m-d', strtotime( $today . ' +' . BANKOFART_BOOKING_DAYS_AHEAD . ' days' ) );
	if ( $date < $today || $date > $max_date ) {
		return array();
	}

	global $wpdb;
	$table  = bankofart_booking_table();
	$rows   = $wpdb->get_col( $wpdb->prepare( "SELECT DATE_FORMAT(booked_at, '%%H:%%i') FROM {$table} WHERE DATE(booked_at) = %s AND status = 'confirmed'", $date ) ); // phpcs:ignore WordPress.DB
	$booked = is_array( $rows ) ? $rows : array();

	/**
	 * ▼▼▼ フェーズ2 Google連携 差し込み口 ▼▼▼
	 * Google Calendar Freebusy のbusy時間帯を $booked にマージする。
	 *   例： add_filter( 'bankofart_booking_busy_slots', 'bankofart_booking_gcal_busy_slots', 10, 2 );
	 * inc/online-booking/gcal.php（フェーズ2）に bankofart_booking_gcal_busy_slots( $booked, $date ) を実装予定。
	 * ▲▲▲ フェーズ2 Google連携 差し込み口 ▲▲▲
	 */
	$booked = apply_filters( 'bankofart_booking_busy_slots', $booked, $date );

	// 当日は過去時刻を除外。
	$now_min = -1;
	if ( $date === $today ) {
		$now_min = (int) current_time( 'G' ) * 60 + (int) current_time( 'i' );
	}

	$available = array();
	foreach ( bankofart_booking_all_slots() as $slot ) {
		if ( in_array( $slot, $booked, true ) ) {
			continue;
		}
		if ( $now_min >= 0 ) {
			list( $h, $m ) = array_map( 'intval', explode( ':', $slot ) );
			if ( $h * 60 + $m <= $now_min ) {
				continue;
			}
		}
		$available[] = $slot;
	}
	return $available;
}

/**
 * 単日の利用可能スロットを返す（互換用）。
 *
 * @return void
 */
function bankofart_booking_availability() {
	check_ajax_referer( 'boa_booking', 'nonce' );
	$date = isset( $_GET['date'] ) ? sanitize_text_field( wp_unslash( $_GET['date'] ) ) : '';
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
		wp_send_json_error( array( 'message' => '日付が不正です。' ), 400 );
	}
	wp_send_json_success(
		array(
			'slots'     => bankofart_booking_all_slots(),
			'available' => bankofart_booking_available_for_date( $date ),
		)
	);
}
add_action( 'wp_ajax_boa_booking_availability', 'bankofart_booking_availability' );
add_action( 'wp_ajax_nopriv_boa_booking_availability', 'bankofart_booking_availability' );

/**
 * 週表示用：start から7日分の空き状況をまとめて返す。
 *
 * レスポンス：{ slots:[...全枠], days:[{date, day, wd, isToday, isPast, available:[...]}] }
 *
 * @return void
 */
function bankofart_booking_week() {
	check_ajax_referer( 'boa_booking', 'nonce' );
	$start = isset( $_GET['start'] ) ? sanitize_text_field( wp_unslash( $_GET['start'] ) ) : '';
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start ) ) {
		wp_send_json_error( array( 'message' => '日付が不正です。' ), 400 );
	}

	$today    = current_time( 'Y-m-d' );
	$max_date = gmdate( 'Y-m-d', strtotime( $today . ' +' . BANKOFART_BOOKING_DAYS_AHEAD . ' days' ) );
	$wd_names = array( '日', '月', '火', '水', '木', '金', '土' );

	$days = array();
	for ( $i = 0; $i < 7; $i++ ) {
		$ts   = strtotime( $start . ' +' . $i . ' days' );
		$date = gmdate( 'Y-m-d', $ts );
		$in_range = ( $date >= $today && $date <= $max_date );
		$days[] = array(
			'date'      => $date,
			'day'       => (int) gmdate( 'j', $ts ),
			'month'     => (int) gmdate( 'n', $ts ),
			'wd'        => $wd_names[ (int) gmdate( 'w', $ts ) ],
			'isToday'   => ( $date === $today ),
			'isPast'    => ( $date < $today ),
			'inRange'   => $in_range,
			'available' => $in_range ? bankofart_booking_available_for_date( $date ) : array(),
		);
	}

	wp_send_json_success(
		array(
			'slots'   => bankofart_booking_all_slots(),
			'days'    => $days,
			'today'   => $today,
			'maxDate' => $max_date,
		)
	);
}
add_action( 'wp_ajax_boa_booking_week', 'bankofart_booking_week' );
add_action( 'wp_ajax_nopriv_boa_booking_week', 'bankofart_booking_week' );
