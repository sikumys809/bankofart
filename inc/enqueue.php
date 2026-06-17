<?php
/**
 * アセット（CSS / JS）の読み込み。
 *
 * すべての CSS / JS はこのファイルで wp_enqueue_* する。
 * テンプレート（PHP）内に <link> / <script> を直書きしない。
 * ページ別アセットは条件分岐で必要なページにだけ読み込む。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * フロント側のアセットを読み込む。
 *
 * Phase 1（土台）では共通の tokens / reset / base と Google Fonts のみを登録する。
 * header.css / footer.css / components.css / pages/*.css / 各種 JS は、
 * 対応するファイルを作成した時点で順次このファイルに追記していく。
 *
 * @return void
 */
function bankofart_enqueue_assets() {
	$theme_uri = get_theme_file_uri();
	$ver       = defined( 'BANKOFART_VERSION' ) ? BANKOFART_VERSION : wp_get_theme()->get( 'Version' );

	/*
	 * Google Fonts
	 * - Cormorant SC : 英大文字ディスプレイ（英字専用）
	 * - Cinzel       : 英字ラベル・小見出し
	 * - Shippori Mincho B1 : 日本語全般・数字
	 * バージョンは null（Google 側で管理されるため）。
	 */
	wp_enqueue_style(
		'bankofart-fonts-preconnect',
		'https://fonts.googleapis.com',
		array(),
		null
	);
	wp_enqueue_style(
		'bankofart-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+SC:wght@400;500;700&family=Cinzel:wght@500;700&family=Shippori+Mincho+B1:wght@400;500;700;800&display=swap',
		array(),
		null
	);

	// 共通CSS（依存関係で読み込み順を保証する）。
	wp_enqueue_style( 'bankofart-tokens', "{$theme_uri}/assets/css/tokens.css", array(), $ver );
	wp_enqueue_style( 'bankofart-reset', "{$theme_uri}/assets/css/reset.css", array(), $ver );
	wp_enqueue_style( 'bankofart-base', "{$theme_uri}/assets/css/base.css", array( 'bankofart-tokens', 'bankofart-reset' ), $ver );
	wp_enqueue_style( 'bankofart-header', "{$theme_uri}/assets/css/header.css", array( 'bankofart-base' ), $ver );
	wp_enqueue_style( 'bankofart-footer', "{$theme_uri}/assets/css/footer.css", array( 'bankofart-base' ), $ver );
	// 再利用コンポーネント（カード・CTA等）。
	wp_enqueue_style( 'bankofart-components', "{$theme_uri}/assets/css/components.css", array( 'bankofart-base' ), $ver );

	// 共通JS（フッターで読み込み）。
	wp_enqueue_script( 'bankofart-header', "{$theme_uri}/assets/js/header.js", array(), $ver, true );

	// 単一ページ共通インタラクション（ヒーロー切替・リビール・ライトボックス）。
	$single_detail_needed = ( is_singular( 'artist' ) || is_singular( 'art' ) || is_singular( 'collector' ) || is_singular( 'news' ) || is_singular( 'journal' )
		|| is_post_type_archive( 'news' ) || is_post_type_archive( 'journal' ) || is_post_type_archive( 'artist' ) || is_post_type_archive( 'collector' ) || is_post_type_archive( 'art' ) ); // アーカイブは .rv リビールに使用.
	if ( $single_detail_needed ) {
		wp_enqueue_script(
			'bankofart-single-detail',
			"{$theme_uri}/assets/js/single-detail.js",
			array(),
			$ver,
			true
		);
	}

	// ページ別アセット：単一アーティスト。
	if ( is_singular( 'artist' ) ) {
		wp_enqueue_style(
			'bankofart-single-artist',
			"{$theme_uri}/assets/css/pages/single-artist.css",
			array( 'bankofart-components' ),
			$ver
		);
	}

	// ページ別アセット：単一作品。
	if ( is_singular( 'art' ) ) {
		wp_enqueue_style(
			'bankofart-single-art',
			"{$theme_uri}/assets/css/pages/single-art.css",
			array( 'bankofart-components' ),
			$ver
		);
	}

	// ページ別アセット：単一 画家応援企業。
	if ( is_singular( 'collector' ) ) {
		wp_enqueue_style(
			'bankofart-single-collector',
			"{$theme_uri}/assets/css/pages/single-collector.css",
			array( 'bankofart-components' ),
			$ver
		);
	}

	// ページ別アセット：単一 NEWS。
	if ( is_singular( 'news' ) ) {
		wp_enqueue_style(
			'bankofart-single-news',
			"{$theme_uri}/assets/css/pages/single-news.css",
			array( 'bankofart-components' ),
			$ver
		);
	}

	// ページ別アセット：単一 JOURNAL。
	if ( is_singular( 'journal' ) ) {
		wp_enqueue_style(
			'bankofart-single-journal',
			"{$theme_uri}/assets/css/pages/single-journal.css",
			array( 'bankofart-components' ),
			$ver
		);
	}

	// ページ別アセット：NEWS / JOURNAL アーカイブ（共通CSS + フィルターJS）。
	if ( is_post_type_archive( 'news' ) || is_post_type_archive( 'journal' ) ) {
		wp_enqueue_style(
			'bankofart-archive-list',
			"{$theme_uri}/assets/css/pages/archive-list.css",
			array( 'bankofart-components' ),
			$ver
		);
		wp_enqueue_script(
			'bankofart-archive-filter',
			"{$theme_uri}/assets/js/archive-filter.js",
			array(),
			$ver,
			true
		);
	}

	// ページ別アセット：ARTIST アーカイブ（CSS + 2軸ANDフィルターJS）。
	if ( is_post_type_archive( 'artist' ) ) {
		wp_enqueue_style(
			'bankofart-archive-artist',
			"{$theme_uri}/assets/css/pages/archive-artist.css",
			array( 'bankofart-components' ),
			$ver
		);
		wp_enqueue_script(
			'bankofart-archive-artist-filter',
			"{$theme_uri}/assets/js/archive-artist-filter.js",
			array(),
			$ver,
			true
		);
	}

	// ページ別アセット：COLLECTOR アーカイブ（CSS + 1軸フィルターJS）。
	if ( is_post_type_archive( 'collector' ) ) {
		wp_enqueue_style(
			'bankofart-archive-collector',
			"{$theme_uri}/assets/css/pages/archive-collector.css",
			array( 'bankofart-components' ),
			$ver
		);
		wp_enqueue_script(
			'bankofart-archive-collector-filter',
			"{$theme_uri}/assets/js/archive-collector-filter.js",
			array(),
			$ver,
			true
		);
	}

	// ページ別アセット：ABOUT 固定ページ（スラッグ about または ABOUT テンプレート）。
	if ( is_page( 'about' ) || is_page_template( 'page-about.php' ) ) {
		wp_enqueue_style(
			'bankofart-page-about',
			"{$theme_uri}/assets/css/pages/page-about.css",
			array( 'bankofart-components' ),
			$ver
		);
		wp_enqueue_script(
			'bankofart-page-about',
			"{$theme_uri}/assets/js/page-about.js",
			array(),
			$ver,
			true
		);
		// コレクトシミュレーター（タブ式：即時償却 / 減価償却）。フッターで読み込み（DOM 要素の後）。
		wp_enqueue_script(
			'bankofart-collect-simulator',
			"{$theme_uri}/assets/js/collect-simulator.js",
			array(),
			$ver,
			true
		);
	}

	// ページ別アセット：MATCHING 企業理念診断（スラッグ matching-purpose または MATCHING テンプレート）。
	if ( is_page( 'matching-purpose' ) || is_page_template( 'page-matching-purpose.php' ) ) {
		wp_enqueue_style(
			'bankofart-page-matching-purpose',
			"{$theme_uri}/assets/css/pages/page-matching-purpose.css",
			array( 'bankofart-components' ),
			$ver
		);
		wp_enqueue_script(
			'bankofart-page-matching-purpose',
			"{$theme_uri}/assets/js/page-matching-purpose.js",
			array(),
			$ver,
			true
		);
		// 診断データ供給（Notion仕様準拠・完全動的）。
		//   - questions：diagnosis-data.php の質問マスター（仕様2章）。
		//   - artists  ：artist 投稿から動的取得（仕様7.2。タグ・共鳴文章を入力すれば自動で母集団に追加）。
		// ハードコードは一切無し。スコアリング/同点処理/タグ被り補正は JS 側で実施。
		wp_localize_script(
			'bankofart-page-matching-purpose',
			'BOA_MATCH',
			array(
				'questions'   => bankofart_get_purpose_questions(),
				'artists'     => bankofart_get_matching_artists(),
				'archiveUrl'  => get_post_type_archive_link( 'artist' ),
				'briefingUrl' => bankofart_briefing_url(),
			)
		);
	}

	// ページ別アセット：MATCHING ISSUE 課題逆引き診断（スラッグ matching-issue または テンプレート）。
	if ( is_page( 'matching-issue' ) || is_page_template( 'page-matching-issue.php' ) ) {
		wp_enqueue_style(
			'bankofart-page-matching-issue',
			"{$theme_uri}/assets/css/pages/page-matching-issue.css",
			array( 'bankofart-components' ),
			$ver
		);
		wp_enqueue_script(
			'bankofart-page-matching-issue',
			"{$theme_uri}/assets/js/page-matching-issue.js",
			array(),
			$ver,
			true
		);
		// 診断データ供給（Notion仕様準拠・完全動的）。質問/効用タイプ/対応表＝diagnosis-data.php、
		// アーティスト・コレクターは投稿から動的取得。判定/推薦/記事抽出は JS。
		wp_localize_script(
			'bankofart-page-matching-issue',
			'BOA_ISSUE',
			array(
				'questions'   => bankofart_get_issue_questions(),
				'effectTypes' => bankofart_get_effect_types(),
				'effectMap'   => bankofart_get_effect_to_artist_tag_map(),
				'artists'     => bankofart_get_matching_artists(),
				'collectors'  => bankofart_get_matching_collectors(),
				'briefingUrl' => bankofart_briefing_url(),
			)
		);
	}

	// ページ別アセット：RECRUIT 固定ページ（スラッグ recruit または RECRUIT テンプレート）。
	if ( is_page( 'recruit' ) || is_page_template( 'page-recruit.php' ) ) {
		wp_enqueue_style(
			'bankofart-page-recruit',
			"{$theme_uri}/assets/css/pages/page-recruit.css",
			array( 'bankofart-components' ),
			$ver
		);
		wp_enqueue_script(
			'bankofart-page-recruit',
			"{$theme_uri}/assets/js/page-recruit.js",
			array(),
			$ver,
			true
		);
	}

	// ページ別アセット：フロントページ（TOP）。
	if ( is_front_page() ) {
		wp_enqueue_style(
			'bankofart-front-page',
			"{$theme_uri}/assets/css/pages/front-page.css",
			array( 'bankofart-components' ),
			$ver
		);
		wp_enqueue_script(
			'bankofart-front-page',
			"{$theme_uri}/assets/js/front-page.js",
			array(),
			$ver,
			true
		);
	}

	// ページ別アセット：ART アーカイブ（CSS + 7軸AND+ソートJS）。
	if ( is_post_type_archive( 'art' ) ) {
		wp_enqueue_style(
			'bankofart-archive-art',
			"{$theme_uri}/assets/css/pages/archive-art.css",
			array( 'bankofart-components' ),
			$ver
		);
		wp_enqueue_script(
			'bankofart-archive-art-filter',
			"{$theme_uri}/assets/js/archive-art-filter.js",
			array(),
			$ver,
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'bankofart_enqueue_assets' );

/**
 * Google Fonts への接続を高速化するための preconnect / crossorigin を付与。
 *
 * wp_enqueue_style では crossorigin 属性を付けられないため、フィルターで補う。
 *
 * @param string $html   生成された link タグ。
 * @param string $handle スタイルのハンドル名。
 * @return string
 */
function bankofart_fonts_preconnect( $html, $handle ) {
	if ( 'bankofart-fonts-preconnect' === $handle ) {
		// preconnect として出力（gstatic 向け crossorigin も併記）。
		$html  = '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
		$html .= '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	}
	return $html;
}
add_filter( 'style_loader_tag', 'bankofart_fonts_preconnect', 10, 2 );
