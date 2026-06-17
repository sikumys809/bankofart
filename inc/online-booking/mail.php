<?php
/**
 * オンライン説明会予約：メール送信（仕様§5）
 *
 * フェーズ1：Google Meet URL 未発行のため、Meet欄はプレースホルダ文言。
 * フェーズ2で meet_link が入れば自動的にURLが文面へ反映される。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 予約日時を和文整形（例：2026年6月10日(火) 14:00〜14:30）。
 *
 * @param string $booked_at "YYYY-MM-DD HH:MM"。
 * @return string
 */
function bankofart_booking_format_datetime( $booked_at ) {
	$ts = strtotime( $booked_at );
	if ( ! $ts ) {
		return $booked_at;
	}
	$w     = array( '日', '月', '火', '水', '木', '金', '土' );
	$start = (int) gmdate( 'H', $ts ) * 60 + (int) gmdate( 'i', $ts );
	$end   = $start + BANKOFART_BOOKING_INTERVAL;
	return sprintf(
		'%d年%d月%d日(%s) %02d:%02d〜%02d:%02d',
		(int) gmdate( 'Y', $ts ),
		(int) gmdate( 'n', $ts ),
		(int) gmdate( 'j', $ts ),
		$w[ (int) gmdate( 'w', $ts ) ],
		intdiv( $start, 60 ),
		$start % 60,
		intdiv( $end, 60 ),
		$end % 60
	);
}

/**
 * Meet URL 表示文言（フェーズ1はプレースホルダ）。
 *
 * @param object $req 予約行。
 * @return string
 */
function bankofart_booking_meet_text( $req ) {
	if ( ! empty( $req->meet_link ) ) {
		return $req->meet_link;
	}
	return 'オンライン会議URLは、追って担当者よりご案内いたします。';
}

/**
 * 予約者への自動返信メール。
 *
 * @param int $booking_id 予約ID。
 * @return bool
 */
function bankofart_send_booking_user_mail( $booking_id ) {
	global $wpdb;
	$table = bankofart_booking_table();
	$req   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $booking_id ) ); // phpcs:ignore WordPress.DB
	if ( ! $req ) {
		return false;
	}

	$dt   = bankofart_booking_format_datetime( $req->booked_at );
	$meet = bankofart_booking_meet_text( $req );

	$subject = '【BANK OF ART】オンライン説明会のご予約を承りました';
	$lines   = array(
		$req->name . ' 様',
		'',
		'この度はオンライン説明会にお申込みいただき、誠にありがとうございます。',
		'以下の内容でご予約を承りました。',
		'',
		'────────────────────',
		'■ ご予約日時',
		$dt,
		'',
		'■ オンライン会議URL',
		$meet,
		'※当日のご案内に従ってご参加ください。',
		'',
		'■ お申込み内容',
		'お名前　：' . $req->name,
		'会社名　：' . $req->company,
		'ご連絡先：' . $req->phone,
		'ご目的　：' . $req->purpose,
		'────────────────────',
		'',
		'ご都合が悪くなった場合は、お手数ですが本メールへご返信ください。',
		'当日お会いできることを楽しみにしております。',
		'',
		'──────────────────────────',
		'BANK of ART　減価償却 × 画家応援',
		home_url( '/' ),
		'──────────────────────────',
	);

	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'From: BANK of ART <no-reply@bankof-art.com>',
		'Reply-To: info@bankof-art.com',
	);

	return wp_mail( $req->email, $subject, implode( "\n", $lines ), $headers );
}

/**
 * 管理者への通知メール。
 *
 * @param int $booking_id 予約ID。
 * @return bool
 */
function bankofart_send_booking_admin_mail( $booking_id ) {
	global $wpdb;
	$table = bankofart_booking_table();
	$req   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $booking_id ) ); // phpcs:ignore WordPress.DB
	if ( ! $req ) {
		return false;
	}

	$to = ( defined( 'BANKOFART_BOOKING_ADMIN_EMAILS' ) && ! empty( BANKOFART_BOOKING_ADMIN_EMAILS ) )
		? BANKOFART_BOOKING_ADMIN_EMAILS
		: get_option( 'admin_email' );

	$dt   = bankofart_booking_format_datetime( $req->booked_at );
	$meet = empty( $req->meet_link ) ? '（フェーズ1：Google連携前のためMeet URL未発行。手動でカレンダー登録・URL発行してください）' : $req->meet_link;

	$subject = sprintf( '【新規予約】%s %s様', $dt, $req->name );
	$lines   = array(
		'新規予約が入りました。',
		'',
		'予約ID：' . $req->id,
		'日時　：' . $dt,
		'氏名　：' . $req->name,
		'会社名：' . $req->company,
		'メール：' . $req->email,
		'電話　：' . $req->phone,
		'目的　：' . $req->purpose,
		'',
		'Google Meet URL：' . $meet,
		'Googleカレンダー：' . ( $req->gcal_event_id ? $req->gcal_event_id : '（未連携）' ),
		'※Googleカレンダー連携はフェーズ2で実装予定。当面は手動登録してください。',
	);

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

	return wp_mail( $to, $subject, implode( "\n", $lines ), $headers );
}
