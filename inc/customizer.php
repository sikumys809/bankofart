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
}
add_action( 'customize_register', 'bankofart_customize_register' );
