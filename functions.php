<?php
/**
 * Bank of Art テーマ functions.php
 *
 * このファイルは最小構成に留め、機能は inc/ 配下に分割して require する。
 * 各 inc ファイルは bankofart_ プレフィックスの関数とフックで完結させる。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // 直接アクセス禁止
}

/**
 * テーマのバージョン・パス定数。
 */
define( 'BANKOFART_VERSION', wp_get_theme()->get( 'Version' ) );
define( 'BANKOFART_DIR', get_theme_file_path() );
define( 'BANKOFART_URI', get_theme_file_uri() );

/**
 * inc/ 配下の分割ファイルを読み込む。
 *
 * 現時点（Phase 1 土台）で存在するファイルのみを列挙する。
 * 後続フェーズで post-types.php / taxonomies.php / meta-box-fields.php /
 * relationships.php / customizer.php / shortcodes.php / ajax-handlers.php /
 * helpers.php を追加していく。
 *
 * @var string[] $bankofart_includes
 */
$bankofart_includes = array(
	'inc/theme-setup.php',      // add_theme_support 等の基本設定
	'inc/enqueue.php',          // CSS / JS の読み込み
	'inc/post-types.php',       // カスタム投稿タイプ登録
	'inc/taxonomies.php',       // カスタムタクソノミー登録
	'inc/meta-box-fields.php',  // Meta Box フィールド定義（要 Meta Box AIO）
	'inc/term-meta-fields.php', // MB Term Meta（art_main_color のカラー情報）
	'inc/relationships.php',    // MB Relationships 定義（要 Meta Box AIO）
	'inc/diagnosis-data.php',   // マッチング診断データ（PHP配列）
	'inc/helpers.php',          // テンプレート用ヘルパー（セクション可視性判定等）
);

foreach ( $bankofart_includes as $bankofart_file ) {
	$bankofart_path = get_theme_file_path( $bankofart_file );

	if ( is_readable( $bankofart_path ) ) {
		require_once $bankofart_path;
	} else {
		// 開発中の読み込み漏れに気付けるよう、管理者にのみ通知する。
		add_action(
			'admin_notices',
			function () use ( $bankofart_file ) {
				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					esc_html(
						sprintf(
							/* translators: %s: 読み込めなかったファイルのパス */
							__( 'Bank of Art テーマ: 必要なファイルを読み込めませんでした: %s', 'bankofart' ),
							$bankofart_file
						)
					)
				);
			}
		);
	}
}

unset( $bankofart_includes, $bankofart_file, $bankofart_path );
