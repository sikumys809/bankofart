<?php
/**
 * テーマ基本設定（テーマサポート・メニュー・画像サイズ等）
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * add_theme_support 等の基本設定。
 *
 * @return void
 */
function bankofart_theme_setup() {
	// 翻訳ファイルの読み込み（languages/bankofart-ja.po 等）。
	load_theme_textdomain( 'bankofart', get_theme_file_path( 'languages' ) );

	// <title> タグを WordPress に管理させる。
	add_theme_support( 'title-tag' );

	// アイキャッチ画像（サムネイル）を有効化。
	add_theme_support( 'post-thumbnails' );

	// HTML5 マークアップ出力。
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// 自動フィードリンク。
	add_theme_support( 'automatic-feed-links' );

	// カスタムロゴ（必要に応じてカスタマイザーで設定）。
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 60,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	// レスポンシブ埋め込み（YouTube 等）。
	add_theme_support( 'responsive-embeds' );

	// ナビゲーションメニューの登録。
	register_nav_menus(
		array(
			'primary' => __( 'グローバルナビゲーション', 'bankofart' ),
			'footer'  => __( 'フッターナビゲーション', 'bankofart' ),
		)
	);
}
add_action( 'after_setup_theme', 'bankofart_theme_setup' );

/**
 * コンテンツ幅の設定。
 *
 * @return void
 */
function bankofart_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'bankofart_content_width', 1280 );
}
add_action( 'after_setup_theme', 'bankofart_content_width', 0 );
