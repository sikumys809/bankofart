<?php
/**
 * テンプレート用ヘルパー関数
 *
 * 運用性の3原則のうち「2. 未入力項目の自動非表示」を支える共通関数。
 * Phase 2 のテンプレート移植で全 single ページが本関数を使う前提。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * セクション可視性の二段階チェック。
 *
 * 段階1: 管理画面の switch（例：artist_show_works）が ON か。
 * 段階2: 表示すべきデータが実際に存在するか。
 *
 * 両方を満たした場合のみ true を返す。テンプレート側はこの戻り値で
 * セクションの描画可否を判定する。
 *
 * @param string $switch_field_id Meta Box の switch フィールドID（例：'artist_show_works'）。
 *                                空文字を渡すと段階1チェックを省略しデータ有無のみで判定。
 * @param mixed  $data_to_check   存在チェック対象（array / string / 数値 / オブジェクト等）。
 * @param int    $post_id         投稿ID。省略時は現在の投稿。
 * @return bool 描画すべきなら true。
 */
function bankofart_should_show_section( $switch_field_id, $data_to_check, $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	// 段階1: 管理画面の switch（switch_field_id が指定された場合のみ）。
	if ( ! empty( $switch_field_id ) ) {
		$switch_value = function_exists( 'rwmb_meta' )
			? rwmb_meta( $switch_field_id, '', $post_id )
			: get_post_meta( $post_id, $switch_field_id, true );

		// 明示的に OFF（'0' / 0）の場合のみ非表示にする。
		// 未設定（'' / null / false）は switch の std => 1（デフォルトON）扱い。
		// ※CSVインポートや未保存投稿で switch メタが無くても、データがあれば表示する。
		if ( '0' === (string) $switch_value ) {
			return false;
		}
	}

	// 段階2: データの有無。
	return bankofart_has_value( $data_to_check );
}

/**
 * フィールド値が「実質的に空でない」かを型に応じて判定する。
 *
 * - 配列        : 要素が1つ以上ある
 * - 文字列      : タグ除去・トリム後に文字が残る（wysiwyg の空 <p></p> 対策）
 * - 数値        : null でなければ true（0 も値として扱う）
 * - それ以外    : !empty()
 *
 * @param mixed $value 判定対象。
 * @return bool 値があれば true。
 */
function bankofart_has_value( $value ) {
	if ( is_array( $value ) ) {
		return count( $value ) > 0;
	}

	if ( is_string( $value ) ) {
		return '' !== trim( wp_strip_all_tags( $value ) );
	}

	if ( is_int( $value ) || is_float( $value ) ) {
		// 0 も「入力済み」とみなす（号数・査定率0% 等の要件に対応）。
		return true;
	}

	return ! empty( $value );
}

/**
 * 指定 Relationship で接続された投稿群を取得する薄いラッパー。
 *
 * MB Relationships 未導入時も致命的エラーにならないよう存在チェックする。
 *
 * @param string $relationship_id Relationship ID（例：'artist_to_art'）。
 * @param string $direction       'from' または 'to'。接続元としてのIDなら 'from'。
 * @param int    $post_id         基準投稿ID。省略時は現在の投稿。
 * @return array 接続された WP_Post 配列（無い場合は空配列）。
 */
function bankofart_get_connected( $relationship_id, $direction = 'from', $post_id = null ) {
	if ( ! class_exists( 'MB_Relationships_API' ) ) {
		return array();
	}

	$post_id = $post_id ? $post_id : get_the_ID();

	$connected = MB_Relationships_API::get_connected(
		array(
			'id'        => $relationship_id,
			$direction  => $post_id,
		)
	);

	return is_array( $connected ) ? $connected : array();
}

/**
 * Meta Box の single_image フィールドから url / alt を取得する。
 *
 * カードコンポーネント等での画像出力を共通化する。値が無い場合は
 * url が空文字の配列を返すので、呼び出し側は !empty($img['url']) で判定する。
 *
 * @param string $field_id single_image フィールドID（例：'artist_main_photo'）。
 * @param int    $post_id  投稿ID。省略時は現在の投稿。
 * @param string $size     画像サイズ。既定 'medium'。
 * @return array array( 'url' => string, 'alt' => string, 'id' => int )
 */
function bankofart_get_image( $field_id, $post_id = null, $size = 'medium' ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$result  = array(
		'url' => '',
		'alt' => '',
		'id'  => 0,
	);

	if ( ! function_exists( 'rwmb_meta' ) ) {
		return $result;
	}

	$img = rwmb_meta( $field_id, array( 'size' => $size ), $post_id );

	if ( ! empty( $img['url'] ) ) {
		$result['url'] = $img['url'];
		$result['alt'] = ! empty( $img['alt'] ) ? $img['alt'] : '';
		$result['id']  = ! empty( $img['ID'] ) ? (int) $img['ID'] : 0;
	}

	return $result;
}

/**
 * タクソノミーの最初のターム名を返す（単一値ラベル用）。
 *
 * @param int    $post_id  投稿ID。
 * @param string $taxonomy タクソノミー名。
 * @return string ターム名（無ければ空文字）。
 */
function bankofart_get_first_term_name( $post_id, $taxonomy ) {
	$terms = get_the_terms( $post_id, $taxonomy );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}
	return $terms[0]->name;
}

/**
 * 画家応募フォームの URL を返す（一元管理）。
 *
 * recruit ページの「応募フォームへ」ボタンと、archive 等の FOR ARTISTS バナーの
 * 「応募する」ボタンが共通でこれを参照する。確定フォーム（Tally 等）URL が出たら
 * この1関数の戻り値を差し替えるだけで全箇所に反映される。
 *
 * @return string 応募フォーム URL（未確定のため現状はプレースホルダ '#'）。
 */
function bankofart_apply_url() {
	// 応募フォームURL未確定（Tally 等）。確定後はここを差し替える。
	return '#';
}

/**
 * 募集要項PDF の URL を返す（一元管理）。
 *
 * recruit ページの「詳しい募集要項はこちら」ボタンが参照する。
 * PDF を配置/確定したら、この1関数の戻り値を差し替える。
 *
 * @return string 募集要項PDF URL（未確定のため現状はプレースホルダ '#'）。
 */
function bankofart_recruit_guidelines_pdf_url() {
	// 募集要項PDF未確定。確定後はここを差し替える（例：wp-content/uploads のPDF URL）。
	return '#';
}

/**
 * 資料請求の URL を返す（一元管理）。
 *
 * CONTACT 系ボタン（CTA / ヘッダー / フッター / about MOVIE 等）が共通で参照する。
 * 暫定は現行運用の外部フォーム（form-mailer）。将来 内部の資料請求フォームを実装したら、
 * このフィルター既定値を差し替える（apply_filters は header/footer と共有）。
 *
 * @return string 資料請求フォーム URL。
 */
function bankofart_document_request_url() {
	return apply_filters( 'bankofart_document_request_url', 'https://business.form-mailer.jp/fms/1c6a81d4280183' );
}

/**
 * オンライン説明会予約の URL を返す（一元管理）。
 *
 * 暫定は現行運用の外部予約（receptionist）。将来 内部の説明会予約システムを実装したら、
 * このフィルター既定値を差し替える（apply_filters は header/footer と共有）。
 *
 * @return string オンライン説明会予約 URL。
 */
function bankofart_briefing_url() {
	return apply_filters( 'bankofart_briefing_url', 'https://booking.receptionist.jp/5ade6c6d-ae6c-44d9-9921-000ad24af9f9/30min' );
}
