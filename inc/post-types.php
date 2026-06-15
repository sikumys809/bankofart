<?php
/**
 * カスタム投稿タイプ（CPT）登録
 *
 * theme-structure.md「3. カスタム投稿タイプ（CPT）」の定義に準拠。
 * WordPress標準の register_post_type で登録する（Meta Box プラグインに依存しない）。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT を一括登録する。
 *
 * @return void
 */
function bankofart_register_post_types() {

	$post_types = array(
		'artist' => array(
			'name'          => 'アーティスト',
			'menu_position' => 5,
			'menu_icon'     => 'dashicons-art',
			// 本文・抜粋は使わず、全情報を Meta Box フィールドで管理（art と同じ構成）。
			// これによりタブ式 Meta Box がタイトル直下にメイン表示される。
			'supports'      => array( 'title', 'thumbnail' ),
		),
		'art' => array(
			'name'          => '作品',
			'menu_position' => 6,
			'menu_icon'     => 'dashicons-format-image',
			'supports'      => array( 'title', 'thumbnail' ),
		),
		'collector' => array(
			'name'          => '画家応援企業',
			'menu_position' => 7,
			'menu_icon'     => 'dashicons-building',
			// 本文はインタビューQ&A等の Meta Box フィールドで管理。
			'supports'      => array( 'title', 'thumbnail' ),
		),
		'news' => array(
			'name'          => 'NEWS',
			'menu_position' => 8,
			'menu_icon'     => 'dashicons-megaphone',
			// 本文は「本文セクション」リピーターで管理。
			'supports'      => array( 'title', 'thumbnail' ),
		),
		'journal' => array(
			'name'          => 'JOURNAL',
			'menu_position' => 9,
			'menu_icon'     => 'dashicons-book-alt',
			// 本文は「本文セクション」リピーターで管理。
			'supports'      => array( 'title', 'thumbnail' ),
		),
	);

	foreach ( $post_types as $slug => $config ) {
		$name = $config['name'];

		$labels = array(
			'name'               => $name,
			'singular_name'      => $name,
			'menu_name'          => $name,
			'all_items'          => $name . '一覧',
			'add_new'            => '新規追加',
			'add_new_item'       => $name . 'を追加',
			'edit_item'          => $name . 'を編集',
			'new_item'           => '新しい' . $name,
			'view_item'          => $name . 'を表示',
			'view_items'         => $name . '一覧を表示',
			'search_items'       => $name . 'を検索',
			'not_found'          => $name . 'が見つかりませんでした',
			'not_found_in_trash' => 'ゴミ箱に' . $name . 'はありません',
		);

		register_post_type(
			$slug,
			array(
				'labels'        => $labels,
				'public'        => true,
				'has_archive'   => true,
				'hierarchical'  => false,
				'menu_position' => $config['menu_position'],
				'menu_icon'     => $config['menu_icon'],
				'supports'      => $config['supports'],
				'show_in_rest'  => true,
				'rewrite'       => array(
					'slug'       => $slug,
					'with_front' => false,
				),
			)
		);
	}
}
add_action( 'init', 'bankofart_register_post_types', 0 );

/**
 * 本テーマの CPT はブロックエディター（Gutenberg）ではなくクラシックエディターで開く。
 *
 * Meta Box のタブ式UI（MB Tabs）は、クラシックエディターでこそ
 * メインカラムにタブとして表示される。ブロックエディターだと
 * カスタム Meta Box が画面下部の「Meta Boxes」ドロワーに押し込まれ、
 * art だけ整って見える状態になっていたため、全 CPT を揃える。
 *
 * @param bool   $use_block_editor 既定の判定値。
 * @param string $post_type        対象の投稿タイプ。
 * @return bool ブロックエディターを使うなら true。
 */
function bankofart_disable_block_editor( $use_block_editor, $post_type ) {
	$classic_post_types = array( 'artist', 'art', 'collector', 'news', 'journal' );

	if ( in_array( $post_type, $classic_post_types, true ) ) {
		return false;
	}

	return $use_block_editor;
}
add_filter( 'use_block_editor_for_post_type', 'bankofart_disable_block_editor', 10, 2 );

/**
 * 本テーマの Meta Box を「画面オプション」で常に表示状態にする。
 *
 * ブロックエディター運用時などに、ユーザーごとの画面オプション
 * （user_meta: metaboxhidden_{screen}）へ本テーマの Meta Box が
 * 非表示として保存され、フィールド定義は正しいのに編集画面に
 * 出ない事象が発生していた。get_hidden_meta_boxes() に適用される
 * このフィルターで、保存済みの非表示状態より優先して必ず表示させる。
 *
 * @param array $hidden 非表示にする meta box ID の配列。
 * @return array
 */
function bankofart_force_show_meta_boxes( $hidden ) {
	$our_boxes = array(
		'bankofart_artist_public',
		'bankofart_artist_private',
		'bankofart_art_public',
		'bankofart_art_private',
		'bankofart_collector',
		'bankofart_news',
		'bankofart_journal',
	);

	return array_values( array_diff( (array) $hidden, $our_boxes ) );
}
add_filter( 'hidden_meta_boxes', 'bankofart_force_show_meta_boxes', 20 );
