<?php
/**
 * WP カスタマイザー設定
 *
 * 「外観 > カスタマイズ > サイト数値（実績）」に、ABOUTページ等で使う実績数値を登録する。
 *   - 導入先（ヶ所） / 所属画家（名） / アート取扱い枚数（枚）
 * テンプレートからは bankofart_stat( 'clients' | 'artists' | 'artworks' ) で取得する。
 * 値は theme_mod として保存され、Meta Box 等のプラグインに依存しない。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 実績数値の定義（キー => ラベル・既定値）。
 *
 * 既定値はモック（about__17_.html）のベタ書き値に一致させている。
 *
 * @return array<string, array{label:string, default:int}>
 */
function bankofart_stat_fields() {
	return array(
		'clients'  => array(
			'label'   => __( '導入先（ヶ所）', 'bankofart' ),
			'default' => 172,
		),
		'artists'  => array(
			'label'   => __( '所属画家（名）', 'bankofart' ),
			'default' => 22,
		),
		'artworks' => array(
			'label'   => __( 'アート取扱い枚数（枚）', 'bankofart' ),
			'default' => 1230,
		),
	);
}

/**
 * 実績数値を取得する（カスタマイザー未設定時は既定値）。
 *
 * @param string $key 'clients' | 'artists' | 'artworks'。
 * @return int 0以上の整数。
 */
function bankofart_stat( $key ) {
	$fields = bankofart_stat_fields();
	if ( ! isset( $fields[ $key ] ) ) {
		return 0;
	}
	return absint( get_theme_mod( 'bankofart_stat_' . $key, $fields[ $key ]['default'] ) );
}

/* =========================================================
 * ヒーロー（トップ最上部）：サニタイズ＆取得ヘルパー
 * ======================================================= */

/**
 * 表示タイプのサニタイズ（slideshow / video のみ許可）。
 *
 * @param string $value 入力値。
 * @return string
 */
function bankofart_sanitize_hero_type( $value ) {
	return in_array( $value, array( 'slideshow', 'video' ), true ) ? $value : 'slideshow';
}

/**
 * 切替間隔（秒）のサニタイズ。3〜15秒にクランプ。
 *
 * @param mixed $value 入力値。
 * @return int
 */
function bankofart_sanitize_hero_interval( $value ) {
	$value = absint( $value );
	if ( $value < 3 ) {
		$value = 3;
	}
	if ( $value > 15 ) {
		$value = 15;
	}
	return $value;
}

/**
 * ヒーロー表示タイプ（'slideshow' | 'video'）。
 *
 * @return string
 */
function bankofart_hero_type() {
	return bankofart_sanitize_hero_type( get_theme_mod( 'bankofart_hero_type', 'slideshow' ) );
}

/**
 * スライド画像URL（設定済みのものだけ・最大4枚）。
 *
 * @return string[]
 */
function bankofart_hero_images() {
	$images = array();
	for ( $i = 1; $i <= 4; $i++ ) {
		$url = get_theme_mod( 'bankofart_hero_image_' . $i, '' );
		if ( $url ) {
			$images[] = $url;
		}
	}
	return $images;
}

/**
 * スライド切替間隔（ミリ秒。JSへ渡す用）。
 *
 * @return int
 */
function bankofart_hero_interval() {
	return bankofart_sanitize_hero_interval( get_theme_mod( 'bankofart_hero_interval', 6 ) ) * 1000;
}

/**
 * 背景動画URL（mp4・添付IDから解決）。未設定は空文字。
 *
 * @return string
 */
function bankofart_hero_video() {
	$id = absint( get_theme_mod( 'bankofart_hero_video', 0 ) );
	if ( ! $id ) {
		return '';
	}
	$url = wp_get_attachment_url( $id );
	return $url ? $url : '';
}

/**
 * 動画ポスター画像URL。未設定は空文字。
 *
 * @return string
 */
function bankofart_hero_poster() {
	return get_theme_mod( 'bankofart_hero_poster', '' );
}

/**
 * カスタマイザーにセクション・設定・コントロールを登録する。
 *
 * @param WP_Customize_Manager $wp_customize カスタマイザーマネージャ。
 * @return void
 */
function bankofart_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'bankofart_stats',
		array(
			'title'       => __( 'サイト数値（実績）', 'bankofart' ),
			'priority'    => 30,
			'description' => __( 'ABOUTページ等に表示する実績数値。半角数字で入力してください。', 'bankofart' ),
		)
	);

	foreach ( bankofart_stat_fields() as $key => $field ) {
		$setting_id = 'bankofart_stat_' . $key;

		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => $field['default'],
				'type'              => 'theme_mod',
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			$setting_id,
			array(
				'label'       => $field['label'],
				'section'     => 'bankofart_stats',
				'type'        => 'number',
				'input_attrs' => array(
					'min'  => 0,
					'step' => 1,
				),
			)
		);
	}

	/*
	 * 募集（RECRUIT）：画家募集要項PDF。
	 * 管理画面からPDFをアップロード／選択して差し替え可能（添付IDを保存）。
	 * recruit ページ「詳しい募集要項はこちら」/ FOR ARTISTS バナーが参照。
	 */
	$wp_customize->add_section(
		'bankofart_recruit',
		array(
			'title'       => __( '募集（RECRUIT）', 'bankofart' ),
			'priority'    => 31,
			'description' => __( '画家募集要項のPDFをアップロードしてください。未設定の場合はテーマ同梱PDFが使われます。', 'bankofart' ),
		)
	);
	$wp_customize->add_setting(
		'bankofart_guidelines_pdf',
		array(
			'default'           => 0,
			'type'              => 'theme_mod',
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'bankofart_guidelines_pdf',
			array(
				'label'       => __( '画家募集要項PDF', 'bankofart' ),
				'description' => __( 'PDFファイルをアップロードまたはメディアから選択。', 'bankofart' ),
				'section'     => 'bankofart_recruit',
				'mime_type'   => 'application/pdf',
				'button_labels' => array(
					'select'       => __( 'PDFを選択', 'bankofart' ),
					'change'       => __( 'PDFを変更', 'bankofart' ),
					'remove'       => __( '削除', 'bankofart' ),
					'placeholder'  => __( 'PDF未選択', 'bankofart' ),
					'frame_title'  => __( '募集要項PDFを選択', 'bankofart' ),
					'frame_button' => __( 'このPDFを使用', 'bankofart' ),
				),
			)
		)
	);

	/*
	 * ヒーロー（トップ最上部）：背景を「画像スライドショー / 動画」から選択。
	 */
	$wp_customize->add_section(
		'bankofart_hero',
		array(
			'title'       => __( 'ヒーロー（トップ最上部）', 'bankofart' ),
			'priority'    => 29,
			'description' => __( 'トップページ最上部の背景。表示タイプ（画像スライドショー / 動画）を選び、画像や動画を設定します。', 'bankofart' ),
		)
	);

	// 表示タイプ。
	$wp_customize->add_setting(
		'bankofart_hero_type',
		array(
			'default'           => 'slideshow',
			'type'              => 'theme_mod',
			'sanitize_callback' => 'bankofart_sanitize_hero_type',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'bankofart_hero_type',
		array(
			'label'   => __( '表示タイプ', 'bankofart' ),
			'section' => 'bankofart_hero',
			'type'    => 'radio',
			'choices' => array(
				'slideshow' => __( '画像スライドショー', 'bankofart' ),
				'video'     => __( '動画', 'bankofart' ),
			),
		)
	);

	// スライド画像 1〜4。
	for ( $bankofart_hi = 1; $bankofart_hi <= 4; $bankofart_hi++ ) {
		$hero_img_id = 'bankofart_hero_image_' . $bankofart_hi;
		$wp_customize->add_setting(
			$hero_img_id,
			array(
				'default'           => '',
				'type'              => 'theme_mod',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				$hero_img_id,
				array(
					/* translators: %d: スライド番号 */
					'label'       => sprintf( __( 'スライド画像 %d', 'bankofart' ), $bankofart_hi ),
					'description' => ( 1 === $bankofart_hi ) ? __( '1枚目（推奨）。2〜4枚目は任意。登録した枚数だけ自動で切り替わります。', 'bankofart' ) : '',
					'section'     => 'bankofart_hero',
				)
			)
		);
	}

	// 切替間隔（秒）。
	$wp_customize->add_setting(
		'bankofart_hero_interval',
		array(
			'default'           => 6,
			'type'              => 'theme_mod',
			'sanitize_callback' => 'bankofart_sanitize_hero_interval',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'bankofart_hero_interval',
		array(
			'label'       => __( '切替間隔（秒）', 'bankofart' ),
			'description' => __( '画像スライドショーの切替間隔。3〜15秒。', 'bankofart' ),
			'section'     => 'bankofart_hero',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 3,
				'max'  => 15,
				'step' => 1,
			),
		)
	);

	// 背景動画（mp4）。
	$wp_customize->add_setting(
		'bankofart_hero_video',
		array(
			'default'           => 0,
			'type'              => 'theme_mod',
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'bankofart_hero_video',
			array(
				'label'       => __( '背景動画（MP4）', 'bankofart' ),
				'description' => __( '表示タイプが「動画」のとき使用。モバイルではポスター画像を表示します。', 'bankofart' ),
				'section'     => 'bankofart_hero',
				'mime_type'   => 'video/mp4',
			)
		)
	);

	// 動画ポスター画像（動画読込前＆モバイルフォールバック）。
	$wp_customize->add_setting(
		'bankofart_hero_poster',
		array(
			'default'           => '',
			'type'              => 'theme_mod',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'bankofart_hero_poster',
			array(
				'label'       => __( '動画ポスター画像', 'bankofart' ),
				'description' => __( '動画の読込前と、モバイル時のフォールバック画像。', 'bankofart' ),
				'section'     => 'bankofart_hero',
			)
		)
	);
}
add_action( 'customize_register', 'bankofart_customize_register' );
