<?php
/**
 * MB Relationships 定義
 *
 * theme-structure.md「6. MB Relationships」の定義に準拠。
 * Meta Box AIO の MB Relationships 拡張が有効な場合にのみ動作する
 * （'mb_relationships_init' は拡張が有効なときだけ発火する）。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 関連付けを登録する。
 *
 * @return void
 */
function bankofart_register_relationships() {
	if ( ! class_exists( 'MB_Relationships_API' ) ) {
		return;
	}

	// 関係1: アーティスト ⇔ 作品（1対多）.
	MB_Relationships_API::register(
		array(
			'id'   => 'artist_to_art',
			'from' => array(
				'object_type' => 'post',
				'post_type'   => 'artist',
				'meta_box'    => array( 'title' => 'このアーティストの作品' ),
			),
			'to'   => array(
				'object_type' => 'post',
				'post_type'   => 'art',
				'meta_box'    => array( 'title' => 'このアートを制作したアーティスト' ),
			),
		)
	);

	// 関係2: 作品 ⇔ 所有企業（OWNED時、現在の所有者）.
	MB_Relationships_API::register(
		array(
			'id'   => 'art_to_owner',
			'from' => array(
				'object_type' => 'post',
				'post_type'   => 'art',
				'meta_box'    => array( 'title' => '所有企業' ),
			),
			'to'   => array(
				'object_type' => 'post',
				'post_type'   => 'collector',
				'meta_box'    => array( 'title' => '所有作品' ),
			),
		)
	);

	// 関係3: NEWS ⇔ 関連アーティスト（artist_to_news の逆方向を兼ねる）.
	MB_Relationships_API::register(
		array(
			'id'   => 'news_to_artist',
			'from' => array(
				'object_type' => 'post',
				'post_type'   => 'news',
				'meta_box'    => array( 'title' => '関連アーティスト' ),
			),
			'to'   => array(
				'object_type' => 'post',
				'post_type'   => 'artist',
				'meta_box'    => array( 'title' => '関連NEWS' ),
			),
		)
	);

	// 関係4: NEWS ⇔ 関連作品.
	MB_Relationships_API::register(
		array(
			'id'   => 'news_to_art',
			'from' => array(
				'object_type' => 'post',
				'post_type'   => 'news',
				'meta_box'    => array( 'title' => '関連作品' ),
			),
			'to'   => array(
				'object_type' => 'post',
				'post_type'   => 'art',
				'meta_box'    => array( 'title' => '関連NEWS' ),
			),
		)
	);

	// 関係5: JOURNAL ⇔ 関連アーティスト（artist_to_journal の逆方向を兼ねる）.
	MB_Relationships_API::register(
		array(
			'id'   => 'journal_to_artist',
			'from' => array(
				'object_type' => 'post',
				'post_type'   => 'journal',
				'meta_box'    => array( 'title' => '関連アーティスト' ),
			),
			'to'   => array(
				'object_type' => 'post',
				'post_type'   => 'artist',
				'meta_box'    => array( 'title' => '関連JOURNAL' ),
			),
		)
	);

	// 関係6: JOURNAL ⇔ 関連作品.
	MB_Relationships_API::register(
		array(
			'id'   => 'journal_to_art',
			'from' => array(
				'object_type' => 'post',
				'post_type'   => 'journal',
				'meta_box'    => array( 'title' => '関連作品' ),
			),
			'to'   => array(
				'object_type' => 'post',
				'post_type'   => 'art',
				'meta_box'    => array( 'title' => '関連JOURNAL' ),
			),
		)
	);
}
add_action( 'mb_relationships_init', 'bankofart_register_relationships' );
