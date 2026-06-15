<?php
/**
 * MB Term Meta フィールド定義
 *
 * docs/phase1-finalize.md §3「Main Color Filter」を反映。
 * art_main_color タクソノミーの各ターム（赤・橙…）に「カラー効果」と
 * 「推奨設置場所」のメタ情報を持たせ、ARTフィルターで色クリック時に表示する。
 *
 * Meta Box AIO の MB Term Meta 拡張が有効な場合に動作する。
 * テンプレートからは get_term_meta( $term_id, $key, true ) で参照する。
 *
 * 初期値は inc/taxonomies.php の seed 関数で投入する（管理画面で編集可）。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * art_main_color のターム編集画面にカラー情報フィールドを追加する。
 *
 * @param array $meta_boxes 既存のメタボックス定義。
 * @return array
 */
function bankofart_register_term_meta_boxes( $meta_boxes ) {
	$meta_boxes[] = array(
		'title'      => 'カラー情報（効果・推奨設置場所）',
		'id'         => 'art_main_color_meta',
		'taxonomies' => array( 'art_main_color' ),
		'fields'     => array(
			array(
				'id'   => 'color_hex',
				'name' => '色（カラーコード）',
				'type' => 'color',
				'desc' => 'この色の実際の表示色。一覧・フィルターのスウォッチに使用（例：#D32F2F）',
			),
			array(
				'id'   => 'color_effect_title',
				'name' => 'カラー効果（タイトル）',
				'type' => 'text',
				'desc' => '例：情熱・行動力・活気',
			),
			array(
				'id'   => 'color_effect_description',
				'name' => 'カラー効果（説明）',
				'type' => 'textarea',
				'rows' => 3,
			),
			array(
				'id'   => 'recommended_place_title',
				'name' => '推奨設置場所（タイトル）',
				'type' => 'text',
				'desc' => '例：会議室・ブレインストーミングスペース',
			),
			array(
				'id'   => 'recommended_place_description',
				'name' => '推奨設置場所（説明）',
				'type' => 'textarea',
				'rows' => 3,
			),
		),
	);

	return $meta_boxes;
}
add_filter( 'rwmb_meta_boxes', 'bankofart_register_term_meta_boxes' );
