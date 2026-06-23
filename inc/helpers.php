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

/* =========================================================
 * 連絡先メール（全フォーム共通・一元管理）
 * 資料請求／オンライン説明会予約／リセール待機リストの
 * 管理者通知宛先・送信元(From)・Reply-To はすべてこの定数を参照する。
 * 変更は BANKOFART_CONTACT_EMAIL の1箇所のみでよい。
 * ======================================================= */
if ( ! defined( 'BANKOFART_CONTACT_EMAIL' ) ) {
	define( 'BANKOFART_CONTACT_EMAIL', 'info@bankof-art.com' );
}
if ( ! defined( 'BANKOFART_CONTACT_FROM_NAME' ) ) {
	define( 'BANKOFART_CONTACT_FROM_NAME', 'BANK of ART' );
}

/**
 * フォーム送信メール共通ヘッダー（Content-Type / From / Reply-To）。
 *
 * @return string[] wp_mail() 用ヘッダー配列。
 */
function bankofart_mail_headers() {
	return array(
		'Content-Type: text/plain; charset=UTF-8',
		'From: ' . BANKOFART_CONTACT_FROM_NAME . ' <' . BANKOFART_CONTACT_EMAIL . '>',
		'Reply-To: ' . BANKOFART_CONTACT_EMAIL,
	);
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
 * 「応募する」ボタンが共通でこれを参照する。差し替えはこの1関数のみ。
 * 旧：外部 Tally（https://tally.so/r/MeMEV0）→ 自前の画家応募フォーム /artist-entry/ に置き換え。
 *
 * @return string 応募フォーム URL（自前ページ /artist-entry/）。
 */
function bankofart_apply_url() {
	return home_url( '/artist-entry/' );
}

/**
 * 募集要項PDF の URL を返す（一元管理）。
 *
 * recruit ページの「詳しい募集要項はこちら」と、FOR ARTISTS バナーの
 * 「募集要項を見る」ボタンが共通でこれを参照する。差し替えはこの1関数のみ。
 *
 * @return string 募集要項PDF URL。
 */
function bankofart_recruit_guidelines_pdf_url() {
	return get_theme_file_uri( 'assets/docs/boa-artist-application-guidelines.pdf' );
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
	// 自前の資料請求フォームページ（/document-request/）へ。確定後の差し替えはフィルターで。
	return apply_filters( 'bankofart_document_request_url', home_url( '/document-request/' ) );
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
	// 自前のオンライン説明会予約ページ（/online-briefing/）へ。差し替えはフィルターで。
	return apply_filters( 'bankofart_briefing_url', home_url( '/online-briefing/' ) );
}

/**
 * 本文（wysiwyg）に挿入された画像を「大きく・鮮明に」表示できるよう整える。
 *
 * クラシックエディタで「中サイズ」等を選んで挿入すると、src が縮小版（例 -300x216）に
 * 固定され width/height 属性も付くため小さく表示される。本関数は：
 *   - class の wp-image-{ID} から添付IDを取得し、src を large（無ければ full）に差し替え
 *   - 古い srcset / sizes / width / height 属性を除去（表示幅は CSS 側に委ねる）
 * これにより本文のメイン画像が本文幅いっぱいに大きく、かつ縮小版でない鮮明な画像で出る。
 * 添付IDが取れない場合は src のサイズ接尾辞（-WxH）を除去して原寸に寄せる。
 *
 * @param string $html 本文HTML。
 * @return string 変換後HTML。
 */
function bankofart_enlarge_content_images( $html ) {
	if ( '' === (string) $html || false === strpos( $html, '<img' ) ) {
		return $html;
	}
	return preg_replace_callback(
		'~<img\b[^>]*>~i',
		static function ( $m ) {
			$tag     = $m[0];
			$new_src = '';
			if ( preg_match( '~wp-image-(\d+)~', $tag, $idm ) ) {
				$id      = (int) $idm[1];
				$new_src = wp_get_attachment_image_url( $id, 'large' );
				if ( ! $new_src ) {
					$new_src = wp_get_attachment_image_url( $id, 'full' );
				}
			}
			if ( $new_src ) {
				$tag = preg_replace( '~\ssrc=("|\')[^"\']*\1~i', ' src="' . esc_url( $new_src ) . '"', $tag );
			} else {
				// 添付ID不明：サイズ接尾辞を除去して原寸URLに寄せる（-scaled 等はそのまま）。
				$tag = preg_replace( '~(src=("|\')[^"\']*?)-\d+x\d+(\.(?:jpe?g|png|webp|gif)\2)~i', '$1$3', $tag );
			}
			// 縮小前提の属性を除去（表示サイズは CSS に委ねる）。
			$tag = preg_replace( '~\ssrcset=("|\')[^"\']*\1~i', '', $tag );
			$tag = preg_replace( '~\ssizes=("|\')[^"\']*\1~i', '', $tag );
			$tag = preg_replace( '~\swidth=("|\')\d+\1~i', '', $tag );
			$tag = preg_replace( '~\sheight=("|\')\d+\1~i', '', $tag );
			return $tag;
		},
		$html
	);
}
