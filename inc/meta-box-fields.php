<?php
/**
 * Meta Box フィールド定義（タブ式UI 決定版）
 *
 * docs/phase1-finalize.md を反映。全CPTを art と同じタブ式UIに統一する。
 * Meta Box AIO（MB Tabs 拡張を含む）が有効な場合に動作する（rwmb_meta_boxes フィルター）。
 *
 * 運用性の3原則:
 *   1. 管理画面から表示制御可能 … 各CPTに「セクション表示設定」タブ + switch フィールド
 *   2. 未入力項目の自動非表示  … テンプレート側で値の存在チェック（inc/helpers.php）
 *   3. 再利用可能なコンポーネント … template-parts/ で部品化（Phase 2）
 *
 * 非公開フィールド（本名・連絡先・価格等）は別メタボックスに分離し、
 * current_user_can('manage_options') の場合のみ登録する＝管理者のみ閲覧可。
 *
 * セクション表示の switch は全て std => 1（デフォルトON）。
 * フィールドIDは仕様書通り。テンプレートから rwmb_meta() で参照する。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 「表示する」switch フィールドの共通定義を生成する。
 *
 * @param string $id   フィールドID（例：artist_show_works）。
 * @param string $name ラベル。
 * @param string $tab  所属タブキー。
 * @return array
 */
function bankofart_section_switch( $id, $name, $tab = 'section_display' ) {
	return array(
		'id'        => $id,
		'name'      => $name,
		'type'      => 'switch',
		'tab'       => $tab,
		'std'       => 1,
		'style'     => 'rounded',
		'on_label'  => '表示',
		'off_label' => '非表示',
	);
}

/**
 * タクソノミー選択ピッカー（select2）の共通定義を生成する。
 *
 * type は 'taxonomy'（実際のタームひも付けを保存）を使用する。
 * これにより get_the_terms() / tax_query が従来どおり機能する
 * （taxonomy_advanced はメタ値保存のため関連付けが作られず不可）。
 * field_type:
 *   - multiple = true  → 'select_advanced'（select2・複数選択・検索可。診断タグ等）
 *   - multiple = false → 'select'（ネイティブのプルダウン。単一選択を素直に表現）
 *
 * @param string $id       フィールドID（例：'artist_status_picker'）。
 * @param string $name     ラベル。
 * @param string $taxonomy タクソノミー名。
 * @param string $tab      所属タブキー。
 * @param bool   $multiple 複数選択可か。
 * @param string $desc     補足説明。
 * @return array
 */
function bankofart_taxonomy_picker( $id, $name, $taxonomy, $tab, $multiple = false, $desc = '' ) {
	return array(
		'id'          => $id,
		'name'        => $name,
		'type'        => 'taxonomy',
		'taxonomy'    => $taxonomy,
		'field_type'  => $multiple ? 'select_advanced' : 'select',
		'multiple'    => $multiple,
		'add_new'     => false,
		// select_advanced は既定で ajax=true（候補をAJAX取得）。本環境では候補が
		// 出ない（選択済みのみ表示）ため ajax=false にし、全タームをクライアント
		// 側に描画して select2 でローカル検索させる（最大55件で十分軽い）。
		'ajax'        => false,
		'tab'         => $tab,
		'placeholder' => $multiple ? '選択してください（複数可）' : '選択してください',
		'desc'        => $desc,
		// 投稿0件のタームも選択肢に出す（既定の hide_empty=true だと空に見えるため）。
		'query_args'  => array(
			'hide_empty' => false,
		),
	);
}

/**
 * Meta Box のフィールドグループを登録する。
 *
 * @param array $meta_boxes 既存のメタボックス定義。
 * @return array
 */
function bankofart_register_meta_boxes( $meta_boxes ) {

	$can_manage = current_user_can( 'manage_options' );

	/* =========================================================
	 * ARTIST（公開フィールド）— 7タブ
	 * 基本 / テーマ / 展示 / 画像 / SNS / 診断 / セクション表示
	 * ======================================================= */
	$meta_boxes[] = array(
		'title'      => 'アーティスト情報',
		'id'         => 'bankofart_artist_public',
		'post_types' => array( 'artist' ),
		'context'    => 'normal',
		'priority'   => 'high',
		'tab_style'  => 'left',
		'tabs'       => array(
			'basic'           => array(
				'label' => '基本情報',
				'icon'  => 'dashicons-id',
			),
			'theme'           => array(
				'label' => 'テーマ・物語',
				'icon'  => 'dashicons-book',
			),
			'exhibition'      => array(
				'label' => '展示・経歴',
				'icon'  => 'dashicons-awards',
			),
			'media'           => array(
				'label' => '画像・動画',
				'icon'  => 'dashicons-format-image',
			),
			'sns'             => array(
				'label' => 'SNS',
				'icon'  => 'dashicons-share',
			),
			'diagnosis'       => array(
				'label' => '診断タグ・共鳴',
				'icon'  => 'dashicons-search',
			),
			'section_display' => array(
				'label' => 'セクション表示設定',
				'icon'  => 'dashicons-visibility',
			),
		),
		'fields'     => array(
			// --- 基本情報 ---
			array(
				'id'   => 'artist_name_en',
				'name' => '活動名（英字大文字）',
				'type' => 'text',
				'tab'  => 'basic',
				'desc' => '例：AZUMA JUSEI',
			),
			array(
				'id'   => 'artist_catch_phrase',
				'name' => 'キャッチフレーズ',
				'type' => 'text',
				'tab'  => 'basic',
				'desc' => '例：ADRENALINE ARTIST',
			),
			bankofart_taxonomy_picker( 'artist_status_picker', 'アーティストステータス', 'artist_status', 'basic', false, '公認画家 / 登録画家' ),
			bankofart_taxonomy_picker( 'artist_genre_picker', 'ジャンル', 'artist_genre', 'basic', true, '複数選択可・検索可' ),
			array(
				'id'         => 'artist_birthday',
				'name'       => '生年月日',
				'type'       => 'date',
				'tab'        => 'basic',
				'desc'       => '表示しない・管理用',
				'js_options' => array( 'dateFormat' => 'yy-mm-dd' ),
			),
			array(
				'id'   => 'artist_birthplace',
				'name' => '出身地',
				'type' => 'text',
				'tab'  => 'basic',
				'desc' => '例：兵庫県神戸市',
			),
			// --- テーマ・物語 ---
			array(
				'id'         => 'artist_theme_short',
				'name'       => '制作テーマ（13字以内）',
				'type'       => 'text',
				'tab'        => 'theme',
				'desc'       => 'カード表示用・文字数注意',
				'attributes' => array( 'maxlength' => 13 ),
			),
			array(
				'id'   => 'artist_theme_long',
				'name' => '制作テーマ詳細',
				'type' => 'textarea',
				'tab'  => 'theme',
				'desc' => 'プロフィールページ用',
			),
			array(
				'id'   => 'artist_theme_keywords',
				'name' => 'テーマキーワード',
				'type' => 'text',
				'tab'  => 'theme',
				'desc' => 'カンマ区切り（例：生命エネルギー,挑戦,格闘）',
			),
			array(
				'id'   => 'artist_reason',
				'name' => 'なぜ描くか（Philosophy）',
				'type' => 'wysiwyg',
				'tab'  => 'theme',
			),
			array(
				'id'   => 'artist_origin_story',
				'name' => '起源の物語（History）',
				'type' => 'wysiwyg',
				'tab'  => 'theme',
				'desc' => '長文・段落構成可',
			),
			array(
				'id'   => 'artist_goal',
				'name' => '目標（Goal）',
				'type' => 'textarea',
				'tab'  => 'theme',
			),
			// --- 展示・経歴 ---
			array(
				'id'   => 'artist_solo_exhibitions',
				'name' => '個展',
				'type' => 'textarea',
				'tab'  => 'exhibition',
				'desc' => '改行区切りで複数',
			),
			array(
				'id'   => 'artist_group_exhibitions',
				'name' => 'グループ展',
				'type' => 'textarea',
				'tab'  => 'exhibition',
				'desc' => '改行区切り',
			),
			array(
				'id'   => 'artist_media_awards',
				'name' => 'メディア・受賞',
				'type' => 'textarea',
				'tab'  => 'exhibition',
				'desc' => '改行区切り',
			),
			array(
				'id'   => 'artist_other_activities',
				'name' => 'その他活動',
				'type' => 'textarea',
				'tab'  => 'exhibition',
				'desc' => '改行区切り',
			),
			// --- 画像・動画 ---
			array(
				'id'   => 'artist_main_photo',
				'name' => 'アーティストメイン写真',
				'type' => 'single_image',
				'tab'  => 'media',
				'desc' => '一覧カード用',
			),
			array(
				'id'               => 'artist_gallery_photos',
				'name'             => 'アーティスト写真ギャラリー',
				'type'             => 'image_advanced',
				'tab'              => 'media',
				'desc'             => '1〜4枚（詳細ページ）',
				'max_file_uploads' => 4,
			),
			array(
				'id'   => 'artist_symbol_image',
				'name' => '自己を象徴する写真',
				'type' => 'single_image',
				'tab'  => 'media',
				'desc' => 'Philosophyセクション用',
			),
			array(
				'id'               => 'artist_working_photos',
				'name'             => '制作風景写真',
				'type'             => 'image_advanced',
				'tab'              => 'media',
				'desc'             => '複数可',
				'max_file_uploads' => 20,
			),
			array(
				'id'   => 'artist_video_url',
				'name' => 'プロフィール動画URL',
				'type' => 'url',
				'tab'  => 'media',
				'desc' => 'YouTube埋込用',
			),
			// --- SNS ---
			array(
				'id'   => 'artist_sns_instagram',
				'name' => 'Instagram URL',
				'type' => 'url',
				'tab'  => 'sns',
			),
			array(
				'id'   => 'artist_sns_x',
				'name' => 'X (Twitter) URL',
				'type' => 'url',
				'tab'  => 'sns',
			),
			array(
				'id'   => 'artist_sns_facebook',
				'name' => 'Facebook URL',
				'type' => 'url',
				'tab'  => 'sns',
			),
			array(
				'id'   => 'artist_sns_youtube',
				'name' => 'YouTube URL',
				'type' => 'url',
				'tab'  => 'sns',
			),
			array(
				'id'   => 'artist_sns_other',
				'name' => 'その他URL（公式サイト等）',
				'type' => 'url',
				'tab'  => 'sns',
			),
			// --- 診断タグ・共鳴 ---
			bankofart_taxonomy_picker( 'artist_diagnosis_tag_picker', '診断タグ', 'artist_diagnosis_tag', 'diagnosis', true, '推奨：3〜6個。検索しながら選択できます（全55タグ）' ),
			array(
				'id'   => 'artist_resonance_message',
				'name' => '共鳴ポイント文章',
				'type' => 'wysiwyg',
				'tab'  => 'diagnosis',
				'desc' => 'パーパス診断結果で表示。200字程度。診断タグ自体は右サイドバーの「診断タグ」タクソノミーで管理。',
			),
			// --- セクション表示設定（8 switch）---
			bankofart_section_switch( 'artist_show_theme', '制作テーマ（Theme）セクション' ),
			bankofart_section_switch( 'artist_show_philosophy', 'なぜ描くのか（Philosophy）セクション' ),
			bankofart_section_switch( 'artist_show_origin_story', '起源の物語（History）セクション' ),
			bankofart_section_switch( 'artist_show_goal', 'Goal セクション' ),
			bankofart_section_switch( 'artist_show_works', 'WORKS（このアーティストの作品）セクション' ),
			bankofart_section_switch( 'artist_show_articles', 'ARTICLE（このアーティストの記事）セクション' ),
			bankofart_section_switch( 'artist_show_matching', 'Artist Matching バナー' ),
			bankofart_section_switch( 'artist_show_cta', 'CTA（資料請求・説明会）セクション' ),
		),
	);

	/* =========================================================
	 * ARTIST（非公開・管理者のみ）
	 * ======================================================= */
	if ( $can_manage ) {
		$meta_boxes[] = array(
			'title'      => 'アーティスト管理情報（非公開）',
			'id'         => 'bankofart_artist_private',
			'post_types' => array( 'artist' ),
			'context'    => 'normal',
			'priority'   => 'low',
			'fields'     => array(
				array(
					'id'   => 'artist_education',
					'name' => '学歴・経歴',
					'type' => 'wysiwyg',
					'desc' => '非公開・管理用',
				),
				array(
					'id'   => 'artist_legal_name',
					'name' => '本名',
					'type' => 'text',
				),
				array(
					'id'   => 'artist_legal_name_kana',
					'name' => '本名フリガナ',
					'type' => 'text',
				),
				array(
					'id'   => 'artist_phone',
					'name' => '電話番号',
					'type' => 'text',
				),
				array(
					'id'   => 'artist_email',
					'name' => '連絡先メール',
					'type' => 'email',
				),
				array(
					'id'   => 'artist_address',
					'name' => '住所',
					'type' => 'text',
				),
				array(
					'id'     => 'artist_bank_info',
					'name'   => '振込先（銀行・支店・口座）',
					'type'   => 'group',
					'fields' => array(
						array(
							'id'   => 'bank_name',
							'name' => '銀行名',
							'type' => 'text',
						),
						array(
							'id'   => 'bank_branch',
							'name' => '支店名',
							'type' => 'text',
						),
						array(
							'id'      => 'bank_account_type',
							'name'    => '口座種別',
							'type'    => 'select',
							'options' => array(
								'ordinary' => '普通',
								'checking' => '当座',
							),
						),
						array(
							'id'   => 'bank_account_number',
							'name' => '口座番号',
							'type' => 'text',
						),
						array(
							'id'   => 'bank_account_holder',
							'name' => '口座名義',
							'type' => 'text',
						),
					),
				),
			),
		);
	}

	/* =========================================================
	 * ART（作品情報）— 5タブ
	 * 基本 / 説明・コンセプト / 画像 / 所有歴 / セクション表示
	 * ======================================================= */
	$meta_boxes[] = array(
		'title'       => '作品情報',
		'id'          => 'bankofart_art_public',
		'post_types'  => array( 'art' ),
		'context'     => 'normal',
		'priority'    => 'high',
		'tab_style'   => 'left',
		'tabs'        => array(
			'basic'           => array(
				'label' => '基本情報',
				'icon'  => 'dashicons-id',
			),
			'concept'         => array(
				'label' => '説明・コンセプト',
				'icon'  => 'dashicons-editor-paragraph',
			),
			'images'          => array(
				'label' => '画像',
				'icon'  => 'dashicons-format-image',
			),
			'ownership'       => array(
				'label' => '所有歴',
				'icon'  => 'dashicons-groups',
			),
			'section_display' => array(
				'label' => 'セクション表示設定',
				'icon'  => 'dashicons-visibility',
			),
		),
		'fields'      => array(
			// --- 基本情報 ---
			array(
				'id'   => 'art_title_en',
				'name' => '作品英題',
				'type' => 'text',
				'tab'  => 'basic',
				'desc' => '例：ADRENALINE ART',
			),
			array(
				'id'   => 'art_number',
				'name' => '作品NO.',
				'type' => 'text',
				'tab'  => 'basic',
				'desc' => '例：No.01、VOL.5',
			),
			array(
				'id'   => 'art_year',
				'name' => '制作年',
				'type' => 'number',
				'tab'  => 'basic',
				'desc' => '西暦4桁',
			),
			array(
				'id'   => 'art_medium',
				'name' => '素材・支持体',
				'type' => 'text',
				'tab'  => 'basic',
				'desc' => '例：アクリル / キャンバス',
			),
			array(
				'id'   => 'art_size_detail',
				'name' => 'サイズ詳細',
				'type' => 'text',
				'tab'  => 'basic',
				'desc' => '例：F10（530×455mm）',
			),
			array(
				'id'   => 'art_size_label',
				'name' => '号数表記',
				'type' => 'text',
				'tab'  => 'basic',
				'desc' => '例：F10、P20',
			),
			// --- タクソノミー（select2 ピッカー）---
			bankofart_taxonomy_picker( 'art_status_picker', 'ステータス', 'art_status', 'basic', false, 'AVAILABLE / OWNED' ),
			bankofart_taxonomy_picker( 'art_form_picker', '形態', 'art_form', 'basic', false, '平面 / 立体 / 半立体' ),
			bankofart_taxonomy_picker( 'art_genre_picker', 'ジャンル', 'art_genre', 'basic', true, '複数選択可' ),
			bankofart_taxonomy_picker( 'art_technique_picker', '技法', 'art_technique', 'basic', true, '複数選択可' ),
			bankofart_taxonomy_picker( 'art_size_picker', 'サイズ（号数区分）', 'art_size', 'basic', false ),
			bankofart_taxonomy_picker( 'art_main_color_picker', 'メインカラー', 'art_main_color', 'basic', false, '登録済みの12色から選択' ),
			// --- 説明・コンセプト ---
			array(
				'id'   => 'art_description',
				'name' => '作品説明',
				'type' => 'wysiwyg',
				'tab'  => 'concept',
				'desc' => '「この作品について」',
			),
			array(
				'id'   => 'art_concept',
				'name' => '作品コンセプト',
				'type' => 'wysiwyg',
				'tab'  => 'concept',
				'desc' => '補足説明',
			),
			array(
				'id'   => 'art_series_name',
				'name' => 'シリーズ名',
				'type' => 'text',
				'tab'  => 'concept',
				'desc' => '例：ADRENALINE ART（将来CPT化候補）',
			),
			// --- 画像 ---
			array(
				'id'   => 'art_main_image',
				'name' => 'メイン作品画像',
				'type' => 'single_image',
				'tab'  => 'images',
				'desc' => 'カード・一覧用',
			),
			array(
				'id'               => 'art_gallery',
				'name'             => '作品ギャラリー画像',
				'type'             => 'image_advanced',
				'tab'              => 'images',
				'desc'             => '3〜4枚（詳細ページ）',
				'max_file_uploads' => 20,
			),
			// --- 所有歴（Repeater）---
			array(
				'id'          => 'ownership_history',
				'name'        => '所有歴',
				'type'        => 'group',
				'tab'         => 'ownership',
				'clone'       => true,
				'sort_clone'  => true,
				'collapsible' => true,
				'add_button'  => '所有歴を追加',
				'group_title' => array( 'field' => 'collector_ref' ),
				'fields'      => array(
					array(
						'id'         => 'collector_ref',
						'name'       => '所有企業',
						'type'       => 'post',
						'post_type'  => 'collector',
						'field_type' => 'select_advanced',
						'ajax'       => false, // 候補をクライアント側に描画（select_advanced の ajax 既定対策）.
					),
					array(
						'id'         => 'from_date',
						'name'       => 'コレクト開始日',
						'type'       => 'date',
						'js_options' => array( 'dateFormat' => 'yy-mm-dd' ),
					),
					array(
						'id'         => 'to_date',
						'name'       => 'リセール日',
						'type'       => 'date',
						'js_options' => array( 'dateFormat' => 'yy-mm-dd' ),
					),
					array(
						'id'   => 'is_current',
						'name' => '現所有',
						'type' => 'switch',
					),
					array(
						'id'   => 'is_first',
						'name' => '初代所有',
						'type' => 'switch',
					),
					array(
						'id'   => 'resale_rate',
						'name' => 'リセール査定率（%）',
						'type' => 'number',
						'min'  => 0,
						'max'  => 100,
					),
					array(
						'id'   => 'comment',
						'name' => 'コメント',
						'type' => 'text',
					),
				),
			),
			// --- セクション表示設定（6 switch）---
			bankofart_section_switch( 'art_show_about', 'この作品について セクション' ),
			bankofart_section_switch( 'art_show_artist', 'ARTIST（描いたアーティスト）セクション' ),
			bankofart_section_switch( 'art_show_more_works', 'MORE WORKS（他の作品）セクション' ),
			bankofart_section_switch( 'art_show_collected_by', 'Collected by（所有企業）セクション ※OWNED時のみ' ),
			bankofart_section_switch( 'art_show_ownership_history', 'Ownership History（所有歴）セクション' ),
			bankofart_section_switch( 'art_show_cta', 'CTA（資料請求・説明会）セクション' ),
		),
	);

	/* =========================================================
	 * ART（価格・非公開／管理者のみ）
	 * ======================================================= */
	if ( $can_manage ) {
		$meta_boxes[] = array(
			'title'      => '作品 管理情報（非公開）',
			'id'         => 'bankofart_art_private',
			'post_types' => array( 'art' ),
			'context'    => 'side',
			'priority'   => 'low',
			'fields'     => array(
				array(
					'id'   => 'art_price',
					'name' => '価格（非公開）',
					'type' => 'number',
					'desc' => '管理用',
				),
			),
		);
	}

	/* =========================================================
	 * COLLECTOR（画家応援企業）— 4タブ
	 * 基本 / 画像 / インタビュー / セクション表示
	 * ======================================================= */
	$meta_boxes[] = array(
		'title'       => '画家応援企業情報',
		'id'          => 'bankofart_collector',
		'post_types'  => array( 'collector' ),
		'context'     => 'normal',
		'priority'    => 'high',
		'tab_style'   => 'left',
		'tabs'        => array(
			'basic'           => array(
				'label' => '基本情報',
				'icon'  => 'dashicons-building',
			),
			'media'           => array(
				'label' => '画像',
				'icon'  => 'dashicons-format-image',
			),
			'interview'       => array(
				'label' => 'インタビュー',
				'icon'  => 'dashicons-format-chat',
			),
			'section_display' => array(
				'label' => 'セクション表示設定',
				'icon'  => 'dashicons-visibility',
			),
		),
		'fields'      => array(
			// --- 基本情報 ---
			array(
				'id'   => 'collector_company_name',
				'name' => '企業正式名称',
				'type' => 'text',
				'tab'  => 'basic',
				'desc' => 'post_titleと使い分け',
			),
			array(
				'id'   => 'collector_url',
				'name' => '企業URL',
				'type' => 'url',
				'tab'  => 'basic',
				'desc' => '公式サイト',
			),
			array(
				'id'   => 'collector_industry_text',
				'name' => '業界（表示用テキスト）',
				'type' => 'text',
				'tab'  => 'basic',
				'desc' => 'タクソノミーより細かく',
			),
			array(
				'id'   => 'collector_office_location_detail',
				'name' => '設置場所（自由記述）',
				'type' => 'text',
				'tab'  => 'basic',
				'desc' => 'タクソノミーの補足',
			),
			array(
				'id'         => 'collector_implementation_date',
				'name'       => '導入時期',
				'type'       => 'date',
				'tab'        => 'basic',
				'desc'       => '「2024年4月」表示',
				'js_options' => array( 'dateFormat' => 'yy-mm-dd' ),
			),
			array(
				'id'   => 'collector_change_summary',
				'name' => 'アートを置いた変化（一文）',
				'type' => 'textarea',
				'tab'  => 'basic',
				'desc' => 'カード表示用',
			),
			bankofart_taxonomy_picker( 'collector_industry_picker', '業種', 'collector_industry', 'basic', false, '13業種から選択' ),
			bankofart_taxonomy_picker( 'collector_issue_picker', '課題', 'collector_issue', 'basic', true, '複数選択可・診断と連動' ),
			bankofart_taxonomy_picker( 'collector_placement_picker', '設置場所', 'collector_placement', 'basic', true, '複数選択可' ),
			// --- 画像 ---
			array(
				'id'   => 'collector_logo',
				'name' => '企業ロゴ',
				'type' => 'single_image',
				'tab'  => 'media',
				'desc' => 'TOP・CLIENT COMPANIES用',
			),
			array(
				'id'   => 'collector_main_office_image',
				'name' => 'オフィスメイン写真',
				'type' => 'single_image',
				'tab'  => 'media',
				'desc' => 'カード一覧用',
			),
			array(
				'id'               => 'collector_office_images',
				'name'             => 'オフィス追加写真',
				'type'             => 'image_advanced',
				'tab'              => 'media',
				'desc'             => '3〜5枚（詳細ページ）',
				'max_file_uploads' => 10,
			),
			array(
				'id'   => 'collector_entrance_image',
				'name' => 'エントランス写真',
				'type' => 'single_image',
				'tab'  => 'media',
				'desc' => 'インタビューQ1付近で使用',
			),
			array(
				'id'   => 'collector_interview_image_1',
				'name' => 'インタビュー写真1',
				'type' => 'single_image',
				'tab'  => 'media',
				'desc' => 'Q2付近',
			),
			array(
				'id'   => 'collector_interview_image_2',
				'name' => 'インタビュー写真2',
				'type' => 'single_image',
				'tab'  => 'media',
				'desc' => 'Q4付近',
			),
			array(
				'id'   => 'collector_interview_image_3',
				'name' => 'インタビュー写真3',
				'type' => 'single_image',
				'tab'  => 'media',
				'desc' => 'Q5付近',
			),
			// --- インタビュー（Q&A 5問）---
			array(
				'id'   => 'collector_q1_values',
				'name' => 'Q1：理念や価値観について',
				'type' => 'wysiwyg',
				'tab'  => 'interview',
				'desc' => 'エントランス写真と並列表示',
			),
			array(
				'id'   => 'collector_q2_motivation',
				'name' => 'Q2：アート導入のきっかけ',
				'type' => 'wysiwyg',
				'tab'  => 'interview',
				'desc' => 'インタビュー写真1と並列表示',
			),
			array(
				'id'   => 'collector_q3_choice',
				'name' => 'Q3：作品・作家を選んだ決め手',
				'type' => 'wysiwyg',
				'tab'  => 'interview',
				'desc' => '該当アートの写真と並列表示',
			),
			array(
				'id'   => 'collector_q4_changes',
				'name' => 'Q4：飾ってからの変化',
				'type' => 'wysiwyg',
				'tab'  => 'interview',
				'desc' => 'インタビュー写真2と並列表示',
			),
			array(
				'id'   => 'collector_q5_message',
				'name' => 'Q5：検討中企業へのメッセージ',
				'type' => 'wysiwyg',
				'tab'  => 'interview',
				'desc' => 'インタビュー写真3と並列表示',
			),
			// --- セクション表示設定（5 switch）---
			bankofart_section_switch( 'collector_show_interview', 'INTERVIEW（導入企業の声）セクション' ),
			bankofart_section_switch( 'collector_show_introduced_work', 'INTRODUCED WORK（迎えた作品）セクション' ),
			bankofart_section_switch( 'collector_show_same_issue', 'SAME ISSUE（同じ課題に取り組む企業）セクション' ),
			bankofart_section_switch( 'collector_show_matching', 'Issue Matching バナー' ),
			bankofart_section_switch( 'collector_show_cta', 'CTA（資料請求・説明会）セクション' ),
		),
	);

	/* =========================================================
	 * NEWS — 3タブ
	 * 基本 / 本文セクション / セクション表示
	 * 関連アーティスト/作品は MB Relationships で管理。
	 * ======================================================= */
	$meta_boxes[] = array(
		'title'       => 'NEWS情報',
		'id'          => 'bankofart_news',
		'post_types'  => array( 'news' ),
		'context'     => 'normal',
		'priority'    => 'high',
		'tab_style'   => 'left',
		'tabs'        => array(
			'basic'           => array(
				'label' => '基本情報',
				'icon'  => 'dashicons-megaphone',
			),
			'body'            => array(
				'label' => '本文セクション',
				'icon'  => 'dashicons-edit',
			),
			'section_display' => array(
				'label' => 'セクション表示設定',
				'icon'  => 'dashicons-visibility',
			),
		),
		'fields'      => array(
			// --- 基本情報 ---
			array(
				'id'   => 'news_summary',
				'name' => '要約（カード表示用）',
				'type' => 'textarea',
				'tab'  => 'basic',
				'desc' => '1〜2文',
			),
			array(
				'id'   => 'news_main_image',
				'name' => 'メイン写真',
				'type' => 'single_image',
				'tab'  => 'basic',
				'desc' => 'カード・詳細冒頭',
			),
			array(
				'id'   => 'news_external_url',
				'name' => '外部リンクURL',
				'type' => 'url',
				'tab'  => 'basic',
				'desc' => 'メディア掲載時の元記事',
			),
			array(
				'id'   => 'news_external_label',
				'name' => '外部リンクラベル',
				'type' => 'text',
				'tab'  => 'basic',
				'desc' => '例：PR TIMESで読む',
			),
			bankofart_taxonomy_picker( 'news_category_picker', 'カテゴリー', 'news_category', 'basic', false, '受賞 / 展示 / メディア掲載 / お知らせ' ),
			// --- 本文セクション（Repeater）---
			array(
				'id'          => 'news_sections',
				'name'        => '本文セクション',
				'type'        => 'group',
				'tab'         => 'body',
				'clone'       => true,
				'sort_clone'  => true,
				'collapsible' => true,
				'add_button'  => 'セクションを追加',
				'group_title' => array( 'field' => 'section_heading' ),
				'fields'      => array(
					array(
						'id'   => 'section_heading',
						'name' => '見出し',
						'type' => 'text',
					),
					array(
						'id'   => 'section_body',
						'name' => '本文',
						'type' => 'wysiwyg',
					),
					array(
						'id'               => 'section_images',
						'name'             => 'サブ写真',
						'type'             => 'image_advanced',
						'max_file_uploads' => 10,
					),
				),
			),
			// --- セクション表示設定（4 switch）---
			bankofart_section_switch( 'news_show_related_artist', 'Related Artist（関連アーティスト）セクション' ),
			bankofart_section_switch( 'news_show_related_art', 'Related Art（関連作品）セクション' ),
			bankofart_section_switch( 'news_show_more_news', 'MORE NEWS セクション' ),
			bankofart_section_switch( 'news_show_cta', 'CTA（資料請求・説明会）セクション' ),
		),
	);

	/* =========================================================
	 * JOURNAL — 3タブ
	 * 基本 / 本文セクション / セクション表示
	 * ======================================================= */
	$meta_boxes[] = array(
		'title'       => 'JOURNAL情報',
		'id'          => 'bankofart_journal',
		'post_types'  => array( 'journal' ),
		'context'     => 'normal',
		'priority'    => 'high',
		'tab_style'   => 'left',
		'tabs'        => array(
			'basic'           => array(
				'label' => '基本情報',
				'icon'  => 'dashicons-book-alt',
			),
			'body'            => array(
				'label' => '本文セクション',
				'icon'  => 'dashicons-edit',
			),
			'section_display' => array(
				'label' => 'セクション表示設定',
				'icon'  => 'dashicons-visibility',
			),
		),
		'fields'      => array(
			// --- 基本情報 ---
			array(
				'id'   => 'journal_summary',
				'name' => '要約（カード表示用）',
				'type' => 'textarea',
				'tab'  => 'basic',
				'desc' => '1〜2文',
			),
			array(
				'id'   => 'journal_author',
				'name' => '著者名',
				'type' => 'text',
				'tab'  => 'basic',
				'desc' => '例：水野永吉',
			),
			array(
				'id'   => 'journal_reading_time',
				'name' => '読了時間（分）',
				'type' => 'number',
				'tab'  => 'basic',
				'desc' => '例：5',
			),
			array(
				'id'   => 'journal_main_image',
				'name' => 'メイン写真',
				'type' => 'single_image',
				'tab'  => 'basic',
				'desc' => 'カード・詳細冒頭',
			),
			bankofart_taxonomy_picker( 'journal_category_picker', 'カテゴリー', 'journal_category', 'basic', false, 'コラム / インタビュー' ),
			// --- 本文セクション（Repeater）---
			array(
				'id'          => 'journal_sections',
				'name'        => '本文セクション',
				'type'        => 'group',
				'tab'         => 'body',
				'clone'       => true,
				'sort_clone'  => true,
				'collapsible' => true,
				'add_button'  => 'セクションを追加',
				'group_title' => array( 'field' => 'section_heading' ),
				'fields'      => array(
					array(
						'id'   => 'section_heading',
						'name' => '見出し',
						'type' => 'text',
					),
					array(
						'id'   => 'section_body',
						'name' => '本文',
						'type' => 'wysiwyg',
					),
					array(
						'id'               => 'section_images',
						'name'             => 'サブ写真',
						'type'             => 'image_advanced',
						'max_file_uploads' => 10,
					),
				),
			),
			// --- セクション表示設定（4 switch）---
			bankofart_section_switch( 'journal_show_related_artist', 'Related Artist（関連アーティスト）セクション' ),
			bankofart_section_switch( 'journal_show_related_art', 'Related Art（関連作品）セクション' ),
			bankofart_section_switch( 'journal_show_more_journal', 'MORE JOURNAL セクション' ),
			bankofart_section_switch( 'journal_show_cta', 'CTA（資料請求・説明会）セクション' ),
		),
	);

	return $meta_boxes;
}
add_filter( 'rwmb_meta_boxes', 'bankofart_register_meta_boxes' );
