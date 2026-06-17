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
 * 課題逆引き診断の質問データ（Notion「課題逆引き診断 構成指示書」3章）。
 *
 * 各 option の tags は効用タグ。集計で効用タイプを判定（5.1）。
 * 配点：Q1=50 / Q2=30 / Q3=20（満点100）。
 *
 * @return array
 */
function bankofart_get_issue_questions() {
	return array(
		array(
			'id'       => 'q1',
			'question' => '御社がいま、最も解決したい課題に近いのは？',
			'weight'   => 50,
			'options'  => array(
				array( 'id' => 'q1_a', 'label' => '社員の離職・モチベーション低下', 'tags' => array( '活気', '安心', '心の豊かさ' ) ),
				array( 'id' => 'q1_b', 'label' => '採用で他社と差をつけたい', 'tags' => array( '個性', '話題性', '先進性' ) ),
				array( 'id' => 'q1_c', 'label' => '取引先・来訪者への印象を強めたい', 'tags' => array( '風格', '話題性', '信頼感' ) ),
				array( 'id' => 'q1_d', 'label' => '経営理念が社員に浸透していない', 'tags' => array( '物語性', '求心力', '共感' ) ),
				array( 'id' => 'q1_e', 'label' => 'オフィス・店舗の空間が画一的で寂しい', 'tags' => array( '彩り', '安らぎ', '個性' ) ),
			),
		),
		array(
			'id'       => 'q2',
			'question' => 'その課題について、特にどう感じていますか？',
			'weight'   => 30,
			'options'  => array(
				array( 'id' => 'q2_a', 'label' => '社内の空気を前向きに変えたい', 'tags' => array( '活気', '彩り', '心の豊かさ' ) ),
				array( 'id' => 'q2_b', 'label' => '「らしさ」や独自性を打ち出したい', 'tags' => array( '個性', '物語性', '先進性' ) ),
				array( 'id' => 'q2_c', 'label' => '落ち着き・信頼される雰囲気がほしい', 'tags' => array( '風格', '安心', '信頼感' ) ),
				array( 'id' => 'q2_d', 'label' => '人の心に残る・語りたくなるものがほしい', 'tags' => array( '話題性', '物語性', '求心力' ) ),
			),
		),
		array(
			'id'       => 'q3',
			'question' => 'アートを飾る場所として考えているのは？',
			'weight'   => 20,
			'options'  => array(
				array( 'id' => 'q3_a', 'label' => '受付・エントランス', 'tags' => array( '風格', '話題性', '信頼感' ) ),
				array( 'id' => 'q3_b', 'label' => '執務スペース・社員の働く場所', 'tags' => array( '活気', '安らぎ', '心の豊かさ' ) ),
				array( 'id' => 'q3_c', 'label' => '会議室・応接室', 'tags' => array( '風格', '物語性', '信頼感' ) ),
				array( 'id' => 'q3_d', 'label' => '店舗・顧客が訪れる空間', 'tags' => array( '彩り', '話題性', '個性' ) ),
			),
		),
	);
}

/**
 * 効用タイプ（5種）。Notion 仕様 4章＋出典注記は 8.5.2 の割当。
 *
 * target_issues は collector_issue タクソノミーのターム名と一致させること
 * （離職・モチベーション / 採用・差別化 / 取引先への印象 / 理念浸透 / 空間の活性化）。
 * citation の note は 8.5.3 の表示ルール準拠：断定せず「報告/指摘されています」、成果保証は書かない。
 *
 * @return array
 */
function bankofart_get_effect_types() {
	$meti = array(
		'label' => 'アートと経済社会について考える研究会 報告書（経済産業省, 2023）',
		'url'   => 'https://www.meti.go.jp/shingikai/mono_info_service/art_economic/20230704_report.html',
	);
	$who = array(
		'label' => 'What is the evidence on the role of the arts in improving health and well-being?（WHO/Europe, 2019）',
		'url'   => 'https://www.ncbi.nlm.nih.gov/books/NBK553773/',
	);
	return array(
		'type_01' => array(
			'name'          => '空間に活気を生むアート',
			'effect_tags'   => array( '活気', '彩り', '心の豊かさ' ),
			'target_issues' => array( '離職・モチベーション', '空間の活性化' ),
			'description'   => '色彩豊かで生命力のある作品は、働く人の視界に毎日入ることで、空間そのものの空気を前向きに変えていきます。社員が日々アートからエネルギーを受け取る環境は、職場への愛着を静かに育てます。',
			'citation'      => array( 'note' => '経済産業省の報告書では、オフィスへのアート導入により「気分転換につながった」「オフィスが行きたい場所になった」といった声が報告されています。', 'label' => $meti['label'], 'url' => $meti['url'] ),
		),
		'type_02' => array(
			'name'          => '個性で差をつけるアート',
			'effect_tags'   => array( '個性', '話題性', '先進性' ),
			'target_issues' => array( '採用・差別化' ),
			'description'   => '唯一無二の表現を持つ作品は、「この会社は他と違う」という印象を訪れた人の記憶に残します。採用候補者や来訪者に、企業のセンスと先進性を言葉以上に伝えます。',
			'citation'      => array( 'note' => '経済産業省の報告書は、アートが企業のブランドイメージ形成や企業価値の向上につながり得ると指摘しています。', 'label' => $meti['label'], 'url' => $meti['url'] ),
		),
		'type_03' => array(
			'name'          => '風格と信頼を伝えるアート',
			'effect_tags'   => array( '風格', '信頼感', '安心' ),
			'target_issues' => array( '取引先への印象' ),
			'description'   => '落ち着いた佇まいと確かな技術に裏打ちされた作品は、空間に品格をもたらします。受付や応接室に置かれたとき、その企業の信頼性と成熟を静かに物語ります。',
			'citation'      => array( 'note' => '経済産業省の報告書は、アートの「社会的価値」のひとつとして、空間や組織への信頼形成への寄与を挙げています。', 'label' => $meti['label'], 'url' => $meti['url'] ),
		),
		'type_04' => array(
			'name'          => '物語で理念を語るアート',
			'effect_tags'   => array( '物語性', '求心力', '共感' ),
			'target_issues' => array( '理念浸透' ),
			'description'   => '背景に明確な物語を持つ作品は、企業理念を語るきっかけになります。アーティストの生き方や作品の意味を社員と共有することで、言葉だけでは伝わらない理念が、日常の風景の中に根づいていきます。',
			'citation'      => array( 'note' => '経済産業省の報告書では、社員がアートに接することが新たな発想や対話のきっかけになると報告されています。', 'label' => $meti['label'], 'url' => $meti['url'] ),
		),
		'type_05' => array(
			'name'          => '心に安らぎを与えるアート',
			'effect_tags'   => array( '安らぎ', '安心', '心の豊かさ' ),
			'target_issues' => array( '離職・モチベーション', '空間の活性化' ),
			'description'   => '自然や静謐をモチーフにした作品は、働く空間に呼吸の余白を生みます。視界に安らぎがあることは、社員の心理的な負荷をやわらげ、落ち着いて働ける環境づくりを支えます。',
			'citation'      => array( 'note' => 'WHOの報告書では、3000以上の研究の統合により、芸術が心身の健康の促進に役割を果たし得ることが示されています。', 'label' => $who['label'], 'url' => $who['url'] ),
		),
	);
}

/**
 * 効用タグ → アーティストタグ 対応表（Notion 仕様 5.2）。
 *
 * 課題診断の効用タグと、artist投稿の診断タグ（artist_diagnosis_tag）は語彙が異なるため、
 * この対応表で変換しておすすめ画家を選出する。右辺はすべて artist_diagnosis_tag に存在するターム。
 *
 * @return array<string, string[]>
 */
function bankofart_get_effect_to_artist_tag_map() {
	return array(
		'活気'       => array( '生命エネルギー', '力強さ', 'POP' ),
		'彩り'       => array( 'POP', 'ポジティブ', '実験' ),
		'心の豊かさ' => array( '癒し', '救い', '心の豊かさ' ),
		'個性'       => array( '唯一無二', '実験', '突破' ),
		'話題性'     => array( 'ストリート', '力強さ', '唯一無二' ),
		'先進性'     => array( '現代性', '都市', '実験' ),
		'風格'       => array( '伝統', '工芸', '普遍性' ),
		'信頼感'     => array( '静謐', '伝統', '普遍性' ),
		'安心'       => array( '静謐', '癒し', '自然' ),
		'物語性'     => array( '物語', '国際', '社会貢献' ),
		'求心力'     => array( '希望', '子供', 'つながり' ),
		'共感'       => array( 'つながり', '家族', '再生' ),
		'安らぎ'     => array( '自然', '植物', '静謐' ),
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

/**
 * 課題診断のコレクター記事候補をWPの collector 投稿から動的取得する（Notion 仕様 5.3）。
 *
 * 課題タグは collector_issue タクソノミー（離職・モチベーション 等の5種）で管理。
 * 課題タグ未設定の投稿は除外（紐付けキーが無く診断連携できないため）。
 * 並びは公開日降順（新しい順）。JS 側で効用タイプの target_issues と突合し2〜3件表示、0件はブロック非表示。
 *
 * @return array JS（wp_localize_script）へ供給するコレクター配列。
 */
function bankofart_get_matching_collectors() {
	$collectors = array();

	$posts = get_posts(
		array(
			'post_type'      => 'collector',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		)
	);

	$has_rwmb = function_exists( 'rwmb_meta' );

	foreach ( $posts as $collector_post ) {
		$terms = get_the_terms( $collector_post->ID, 'collector_issue' );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			continue; // 課題タグ未設定は除外。
		}
		$issues = array_values( wp_list_pluck( $terms, 'name' ) );

		$company = $has_rwmb ? (string) rwmb_meta( 'collector_company_name', array(), $collector_post->ID ) : '';
		if ( '' === trim( $company ) ) {
			$company = get_the_title( $collector_post->ID );
		}
		$summary = $has_rwmb ? (string) rwmb_meta( 'collector_change_summary', array(), $collector_post->ID ) : '';
		$summary = trim( wp_strip_all_tags( $summary ) );

		// サムネ：オフィスメイン写真 → 企業ロゴ の順でフォールバック。
		$photo = bankofart_get_image( 'collector_main_office_image', $collector_post->ID, 'medium' );
		if ( empty( $photo['url'] ) ) {
			$photo = bankofart_get_image( 'collector_logo', $collector_post->ID, 'medium' );
		}

		$collectors[] = array(
			'id'      => $collector_post->post_name,
			'name'    => $company,
			'issues'  => $issues,
			'summary' => $summary,
			'url'     => get_permalink( $collector_post->ID ),
			'photo'   => $photo['url'],
		);
	}

	return $collectors;
}
