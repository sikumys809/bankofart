<?php
/**
 * 各CPTのメイン画像 → アイキャッチ（_thumbnail_id）自動設定
 *
 * 新規投稿・更新の保存時に、CPTごとの「メイン画像」フィールド（Meta Box single_image、
 * 値は添付ファイルID）をアイキャッチに自動コピーする。これにより SEO SIMPLE PACK の
 * OGP画像が各投稿で個別画像になる。
 *
 * ルール（既存データの一括設定スクリプトと同一）:
 *   - 既にアイキャッチがある投稿は上書きしない
 *   - メイン画像が未設定／参照先が実在する添付ファイルでない場合はスキップ
 *
 * フック:
 *   - rwmb_after_save_post … Meta Box が全フィールドを保存した直後（本命）
 *   - save_post（優先度 99）… インポート等 Meta Box 経由でない保存への保険
 * 関数は冪等なため、両方で発火しても二重設定にはならない。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPTスラッグ → メイン画像フィールドID の対応表を返す。
 *
 * @return array<string,string>
 */
function bankofart_main_image_field_map() {
	return array(
		'artist'    => 'artist_main_photo',
		'art'       => 'art_main_image',
		'collector' => 'collector_main_office_image',
		'news'      => 'news_main_image',
		'journal'   => 'journal_main_image',
	);
}

/**
 * 保存された投稿のメイン画像をアイキャッチに自動設定する。
 *
 * @param int $post_id 投稿ID。
 * @return void
 */
function bankofart_auto_set_featured_image( $post_id ) {
	// リビジョン・自動保存はスキップ。
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	$map       = bankofart_main_image_field_map();
	$post_type = get_post_type( $post_id );

	// 対象CPT以外はスキップ。
	if ( ! isset( $map[ $post_type ] ) ) {
		return;
	}

	// 既にアイキャッチがある投稿は上書きしない。
	if ( has_post_thumbnail( $post_id ) ) {
		return;
	}

	// メイン画像（添付ファイルID）を取得。
	$att_id = (int) get_post_meta( $post_id, $map[ $post_type ], true );
	if ( $att_id <= 0 ) {
		return;
	}

	// 参照先が実在する添付ファイルでない場合はスキップ。
	if ( 'attachment' !== get_post_type( $att_id ) ) {
		return;
	}

	set_post_thumbnail( $post_id, $att_id );
}
add_action( 'rwmb_after_save_post', 'bankofart_auto_set_featured_image', 20 );
add_action( 'save_post', 'bankofart_auto_set_featured_image', 99 );
