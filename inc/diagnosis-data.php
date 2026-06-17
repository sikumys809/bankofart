<?php
/**
 * マッチング診断データ（PHP配列）
 *
 * パーパス診断（アーティスト診断）と課題逆引き診断の質問・効用タイプ・
 * スコアリング用マッピングを定義する。CPT 化せず固定データとして管理する。
 *
 * 整合性ルール:
 *   - get_effect_types() の target_issues は collector_issue タクソノミーの
 *     統一後の値（離職・モチベーション 等）と一致させること。
 *   - get_effect_to_artist_tag_map() / 各設問の tags は artist_diagnosis_tag
 *     タクソノミー（inc/taxonomies.php でシード）に存在する値のみ使うこと。
 *
 * ※質問文の最終コピーは Phase 2-B で Notion 仕様（パーパス診断／課題逆引き診断
 *   各仕様書）と突き合わせて微調整する前提。データ構造は確定。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * パーパス診断（アーティスト診断）の質問データ。
 *
 * 各 option の tags は artist_diagnosis_tag。回答結果のタグ集計で
 * アーティストの diagnosis_tag と突合し、共鳴度の高い順に表示する。
 *
 * @return array
 */
/**
 * パーパス診断（アーティスト診断）の質問データ。
 *
 * Notion「アーティストマッチング機能 構成指示書」2章を正として定義。
 * 各 option の tags は artist_diagnosis_tag（回答集計でアーティストの診断タグと突合）。
 * 配点：Q1=30 / Q2=20 / Q3=20 / Q4=15 / Q5=10（満点95）。
 *
 * @return array
 */
function bankofart_get_purpose_questions() {
	return array(
		array(
			'id'       => 'q1',
			'question' => '御社が事業を通じて、最も大切にしているのは？',
			'weight'   => 30,
			'options'  => array(
				array( 'id' => 'q1_a', 'label' => '人と人とのつながりを生むこと', 'tags' => array( 'つながり', 'コミュニティ', '地域' ) ),
				array( 'id' => 'q1_b', 'label' => '何かに挑み、新しい価値を生むこと', 'tags' => array( '挑戦', '突破', '唯一無二' ) ),
				array( 'id' => 'q1_c', 'label' => '困っている人や社会課題に向き合うこと', 'tags' => array( '社会貢献', '貧困', 'SDGs' ) ),
				array( 'id' => 'q1_d', 'label' => '伝統や文化を受け継ぎ、発展させること', 'tags' => array( '伝統', '継承', '工芸' ) ),
				array( 'id' => 'q1_e', 'label' => '美しさや心の豊かさを届けること', 'tags' => array( '癒し', '心の豊かさ', '救い' ) ),
			),
		),
		array(
			'id'       => 'q2',
			'question' => '御社が事業を続けてきた中で、最も誇りに思う瞬間に近いのは？',
			'weight'   => 20,
			'options'  => array(
				array( 'id' => 'q2_a', 'label' => '困難を乗り越えて、再び立ち上がれた時', 'tags' => array( '再生', 'リカバリー', '第二のキャリア' ) ),
				array( 'id' => 'q2_b', 'label' => '大切にしてきた価値を、次の世代に渡せた時', 'tags' => array( '家族', '継承', '伝統' ) ),
				array( 'id' => 'q2_c', 'label' => '国境や文化を超えて、誰かと繋がれた時', 'tags' => array( '国際', '越境', '多文化' ) ),
				array( 'id' => 'q2_d', 'label' => '思いがけない出会いが、新しい扉を開いた時', 'tags' => array( '偶然', '転機', '実験' ) ),
				array( 'id' => 'q2_e', 'label' => '言うべきことを、ちゃんと言えた時', 'tags' => array( '社会批評', '不条理', 'メッセージ' ) ),
			),
		),
		array(
			'id'       => 'q3',
			'question' => '御社が将来、どんな未来を作りたいか？',
			'weight'   => 20,
			'options'  => array(
				array( 'id' => 'q3_a', 'label' => '子供たちが希望を持てる社会', 'tags' => array( '子供', '希望', '未来' ) ),
				array( 'id' => 'q3_b', 'label' => '多様な価値観が共存する社会', 'tags' => array( '多様性', '越境', 'POP' ) ),
				array( 'id' => 'q3_c', 'label' => '自然と調和した持続可能な社会', 'tags' => array( '自然', 'サステナビリティ', '植物' ) ),
				array( 'id' => 'q3_d', 'label' => '一人ひとりが自分らしく輝く社会', 'tags' => array( '自立', 'エンパワー', '唯一無二' ) ),
				array( 'id' => 'q3_e', 'label' => '過去と未来が繋がる文化的な社会', 'tags' => array( '伝統', '文化', '普遍性' ) ),
			),
		),
		array(
			'id'       => 'q4',
			'question' => '御社の社風・カルチャーに近いのは？',
			'weight'   => 15,
			'options'  => array(
				array( 'id' => 'q4_a', 'label' => '熱量高く、勢いで突き進む', 'tags' => array( '力強さ', '格闘', '生命エネルギー' ) ),
				array( 'id' => 'q4_b', 'label' => 'じっくり、丁寧に積み上げる', 'tags' => array( '静謐', '工芸', '継承' ) ),
				array( 'id' => 'q4_c', 'label' => '自由でDIYな精神', 'tags' => array( 'DIY', 'ストリート', 'バンドカルチャー' ) ),
				array( 'id' => 'q4_d', 'label' => 'グローバル・国際志向', 'tags' => array( '国際', '越境' ) ),
				array( 'id' => 'q4_e', 'label' => 'アカデミック・知的探究', 'tags' => array( '知性', '構造', '探究' ) ),
			),
		),
		array(
			'id'       => 'q5',
			'question' => 'オフィスや店舗に飾る作品で重視するのは？',
			'weight'   => 10,
			'options'  => array(
				array( 'id' => 'q5_a', 'label' => '来訪者の話題になるインパクト', 'tags' => array( '力強さ', '唯一無二', '挑戦' ) ),
				array( 'id' => 'q5_b', 'label' => '落ち着きと安心感を与える存在感', 'tags' => array( '静謐', '癒し', '心の豊かさ' ) ),
				array( 'id' => 'q5_c', 'label' => '社員が日々元気をもらえるエネルギー', 'tags' => array( 'ポジティブ', '生命エネルギー', 'POP' ) ),
				array( 'id' => 'q5_d', 'label' => '哲学・思想を感じられる深さ', 'tags' => array( '探究', '普遍性', '物語' ) ),
			),
		),
	);
}

/**
 * 課題逆引き診断の質問データ。
 *
 * 各 option の tags は効用タグ。集計結果から効用タイプを決定し、
 * 効用タグ→アーティストタグ変換でアーティストを推薦する。
 *
 * @return array
 */
function bankofart_get_issue_questions() {
	return array(
		array(
			'id'       => 'q1',
			'question' => '今、社内で最も解決したい課題は？',
			'weight'   => 50,
			'options'  => array(
				array(
					'id'     => 'q1_a',
					'label'  => '社員の離職を防ぎ、モチベーションを高めたい',
					'issue'  => '離職・モチベーション',
					'tags'   => array( '活気', '心の豊かさ' ),
				),
				array(
					'id'     => 'q1_b',
					'label'  => '採用や他社との差別化を強めたい',
					'issue'  => '採用・差別化',
					'tags'   => array( '個性', '唯一無二' ),
				),
				array(
					'id'     => 'q1_c',
					'label'  => '取引先・来客への印象を良くしたい',
					'issue'  => '取引先への印象',
					'tags'   => array( '風格', '信頼' ),
				),
				array(
					'id'     => 'q1_d',
					'label'  => '企業理念を社内外へ浸透させたい',
					'issue'  => '理念浸透',
					'tags'   => array( '物語', '理念', 'メッセージ' ),
				),
				array(
					'id'     => 'q1_e',
					'label'  => 'オフィス空間を活性化したい',
					'issue'  => '空間の活性化',
					'tags'   => array( '彩り', '活気', '安らぎ' ),
				),
			),
		),
		array(
			'id'       => 'q2',
			'question' => 'アートを置く主な場所は？',
			'weight'   => 25,
			'options'  => array(
				array(
					'id'    => 'q2_a',
					'label' => 'エントランス・受付',
					'tags'  => array( '風格', '個性' ),
				),
				array(
					'id'    => 'q2_b',
					'label' => '執務スペース',
					'tags'  => array( '活気', '彩り' ),
				),
				array(
					'id'    => 'q2_c',
					'label' => '会議室・応接室',
					'tags'  => array( '信頼', '物語' ),
				),
				array(
					'id'    => 'q2_d',
					'label' => '共用・リフレッシュスペース',
					'tags'  => array( '安らぎ', '癒し' ),
				),
				array(
					'id'    => 'q2_e',
					'label' => '店舗・接客スペース',
					'tags'  => array( '彩り', '個性' ),
				),
			),
		),
		array(
			'id'       => 'q3',
			'question' => 'アートで生み出したい空気感は？',
			'weight'   => 25,
			'options'  => array(
				array(
					'id'    => 'q3_a',
					'label' => '明るく、活気のある空気',
					'tags'  => array( '活気', '彩り' ),
				),
				array(
					'id'    => 'q3_b',
					'label' => '個性的で記憶に残る空気',
					'tags'  => array( '個性', '唯一無二', '革新' ),
				),
				array(
					'id'    => 'q3_c',
					'label' => '信頼感・風格のある空気',
					'tags'  => array( '風格', '信頼', '伝統' ),
				),
				array(
					'id'    => 'q3_d',
					'label' => '理念や想いが伝わる空気',
					'tags'  => array( '物語', '理念', 'メッセージ' ),
				),
				array(
					'id'    => 'q3_e',
					'label' => '落ち着き・癒しのある空気',
					'tags'  => array( '安らぎ', '癒し', '自然' ),
				),
			),
		),
	);
}

/**
 * 効用タイプ（5種）。
 *
 * target_issues は collector_issue（統一後の値）と一致させること。
 *
 * @return array
 */
function bankofart_get_effect_types() {
	return array(
		'type_01' => array(
			'name'          => '空間に活気を生むアート',
			'effect_tags'   => array( '活気', '彩り', '心の豊かさ' ),
			'target_issues' => array( '離職・モチベーション', '空間の活性化' ),
			'description'   => '色彩豊かで生命力のある作品は、働く人の視界に毎日入ることで、空間そのものの空気を前向きに変えていきます。社員が日々アートからエネルギーを受け取る環境は、職場への愛着を静かに育てます。',
			'citation'      => array(
				'url'   => 'https://www.meti.go.jp/shingikai/mono_info_service/art_economic/',
				'label' => 'アートと経済社会について考える研究会 報告書（経済産業省）',
				'note'  => 'オフィスへのアート導入が、社員の気分転換や対話のきっかけになったとの声が報告されています。',
			),
		),
		'type_02' => array(
			'name'          => '個性で差をつけるアート',
			'effect_tags'   => array( '個性', '唯一無二', '革新' ),
			'target_issues' => array( '採用・差別化' ),
			'description'   => '他にはない世界観を持つ作品は、企業の独自性を一目で伝えます。採用候補者や来訪者の記憶に残り、「ここは他と違う」という第一印象を生み出します。',
			'citation'      => array(
				'url'   => 'https://www.meti.go.jp/shingikai/mono_info_service/art_economic/',
				'label' => 'アートと経済社会について考える研究会 報告書（経済産業省）',
				'note'  => 'アートが企業のブランドや個性の発信に寄与することが指摘されています。',
			),
		),
		'type_03' => array(
			'name'          => '風格と信頼を伝えるアート',
			'effect_tags'   => array( '風格', '信頼', '伝統' ),
			'target_issues' => array( '取引先への印象' ),
			'description'   => '落ち着いた品格のある作品は、エントランスや応接室に風格を与えます。取引先や来客に対して、企業の信頼性と文化的な厚みを静かに物語ります。',
			'citation'      => array(
				'url'   => 'https://www.meti.go.jp/shingikai/mono_info_service/art_economic/',
				'label' => 'アートと経済社会について考える研究会 報告書（経済産業省）',
				'note'  => '空間にアートを置くことが来訪者の企業印象に影響を与えうることが論じられています。',
			),
		),
		'type_04' => array(
			'name'          => '物語で理念を語るアート',
			'effect_tags'   => array( '物語', '理念', 'メッセージ' ),
			'target_issues' => array( '理念浸透' ),
			'description'   => '背景に強い物語やメッセージを持つ作品は、企業理念を言葉以上に伝えます。アートを通じて、社員と来訪者の双方に企業の想いを語りかけます。',
			'citation'      => array(
				'url'   => 'https://www.meti.go.jp/shingikai/mono_info_service/art_economic/',
				'label' => 'アートと経済社会について考える研究会 報告書（経済産業省）',
				'note'  => 'アートが価値観やビジョンの共有を促す媒介になりうると報告されています。',
			),
		),
		'type_05' => array(
			'name'          => '心に安らぎを与えるアート',
			'effect_tags'   => array( '安らぎ', '癒し', '自然' ),
			'target_issues' => array( '離職・モチベーション', '空間の活性化' ),
			'description'   => '自然をモチーフにした穏やかな作品は、忙しい職場に余白と落ち着きをもたらします。社員が一息つける環境は、心理的な余裕とウェルビーイングを支えます。',
			'citation'      => array(
				'url'   => 'https://www.meti.go.jp/shingikai/mono_info_service/art_economic/',
				'label' => 'アートと経済社会について考える研究会 報告書（経済産業省）',
				'note'  => '職場環境の質の向上が従業員満足度に関わることが議論されています。',
			),
		),
	);
}

/**
 * 効用タグ → アーティストタグ 対応表。
 *
 * 課題逆引き診断で算出した効用タグを、アーティスト推薦用の
 * artist_diagnosis_tag へ変換する。値はすべて artist_diagnosis_tag に存在すること。
 *
 * @return array
 */
function bankofart_get_effect_to_artist_tag_map() {
	return array(
		'活気'       => array( '生命エネルギー', 'POP', '格闘' ),
		'彩り'       => array( 'POP', 'ポジティブ', '実験' ),
		'心の豊かさ' => array( '心の豊かさ', '祈り', '愛' ),
		'個性'       => array( '唯一無二', 'オルタナリー', '突破' ),
		'唯一無二'   => array( '唯一無二', '挑戦', '実験' ),
		'革新'       => array( '突破', '現代性', '探究' ),
		'風格'       => array( '構造', '神性', '都市' ),
		'信頼'       => array( '継承', '工芸', '普遍性' ),
		'伝統'       => array( '伝統', '継承', '工芸' ),
		'物語'       => array( 'メッセージ', '転機', '再生' ),
		'理念'       => array( '社会貢献', 'SDGs', '祈り' ),
		'メッセージ' => array( 'メッセージ', '社会批評', '不条理' ),
		'安らぎ'     => array( '自然', '植物', '心の豊かさ' ),
		'癒し'       => array( '祈り', '自然', '愛' ),
		'自然'       => array( '自然', '植物', 'サステナビリティ' ),
	);
}

/**
 * 診断対象アーティストをWPの artist 投稿から動的取得する。
 *
 * Notion 仕様 7.2：診断タグ・共鳴文章を入力すればコード変更なしで母集団に自動追加される設計。
 * 診断タグ（artist_diagnosis_tag）が1つも無い投稿は母集団から除外（タグ無しはマッチ不能）。
 * 並び順は公開日昇順（古い順）＝同点処理（仕様 4.2）の登録順タイブレークに使用。
 *
 * @return array JS（wp_localize_script）へ供給するアーティスト配列。
 */
function bankofart_get_matching_artists() {
	$artists = array();

	$posts = get_posts(
		array(
			'post_type'      => 'artist',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);

	$has_rwmb = function_exists( 'rwmb_meta' );

	foreach ( $posts as $artist_post ) {
		$terms = get_the_terms( $artist_post->ID, 'artist_diagnosis_tag' );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			continue; // 診断タグ未入力は母集団から除外。
		}
		$tags = array_values( wp_list_pluck( $terms, 'name' ) );

		$resonance = $has_rwmb ? (string) rwmb_meta( 'artist_resonance_message', array(), $artist_post->ID ) : '';
		$resonance = trim( wp_strip_all_tags( $resonance ) );

		$origin = $has_rwmb ? (string) rwmb_meta( 'artist_origin_story', array(), $artist_post->ID ) : '';
		$origin = trim( wp_strip_all_tags( $origin ) );
		if ( mb_strlen( $origin ) > 120 ) {
			$origin = mb_substr( $origin, 0, 120 ) . '…';
		}

		$photo = bankofart_get_image( 'artist_main_photo', $artist_post->ID, 'large' );

		// 代表作 最大3点（artist_to_art リレーション）。
		$works = array();
		foreach ( bankofart_get_connected( 'artist_to_art', 'from', $artist_post->ID ) as $art_post ) {
			if ( count( $works ) >= 3 ) {
				break;
			}
			$art_img = bankofart_get_image( 'art_main_image', $art_post->ID, 'medium' );
			if ( ! empty( $art_img['url'] ) ) {
				$works[] = $art_img['url'];
			}
		}

		$artists[] = array(
			'id'        => $artist_post->post_name,
			'name'      => get_the_title( $artist_post->ID ),
			'nameEn'    => $has_rwmb ? (string) rwmb_meta( 'artist_name_en', array(), $artist_post->ID ) : '',
			'theme'     => $has_rwmb ? (string) rwmb_meta( 'artist_theme_short', array(), $artist_post->ID ) : '',
			'tags'      => $tags,
			'resonance' => $resonance,
			'origin'    => $origin,
			'url'       => get_permalink( $artist_post->ID ),
			'photo'     => $photo['url'],
			'works'     => $works,
		);
	}

	return $artists;
}
