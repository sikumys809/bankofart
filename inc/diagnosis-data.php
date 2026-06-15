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
function bankofart_get_purpose_questions() {
	return array(
		array(
			'id'       => 'q1',
			'question' => '御社が事業を通じて最も大切にしているのは？',
			'weight'   => 30,
			'options'  => array(
				array(
					'id'    => 'q1_a',
					'label' => '人と人とのつながりを育てること',
					'tags'  => array( 'つながり', 'コミュニティ', '地域' ),
				),
				array(
					'id'    => 'q1_b',
					'label' => '誰もやらないことに挑戦すること',
					'tags'  => array( '挑戦', '突破', '唯一無二' ),
				),
				array(
					'id'    => 'q1_c',
					'label' => '社会や次世代へ貢献すること',
					'tags'  => array( '社会貢献', 'SDGs', '未来' ),
				),
				array(
					'id'    => 'q1_d',
					'label' => '受け継いだ価値を守り伝えること',
					'tags'  => array( '伝統', '継承', '工芸' ),
				),
				array(
					'id'    => 'q1_e',
					'label' => '心の豊かさや人の幸福を大切にすること',
					'tags'  => array( '心の豊かさ', '愛', '祈り' ),
				),
			),
		),
		array(
			'id'       => 'q2',
			'question' => '社内の空気として理想に近いのは？',
			'weight'   => 20,
			'options'  => array(
				array(
					'id'    => 'q2_a',
					'label' => '活気があり、エネルギーに満ちている',
					'tags'  => array( '生命エネルギー', '格闘', 'POP' ),
				),
				array(
					'id'    => 'q2_b',
					'label' => '探究心が強く、学び続けている',
					'tags'  => array( '探究', '勉強会', '実験' ),
				),
				array(
					'id'    => 'q2_c',
					'label' => '落ち着きと品格がある',
					'tags'  => array( '構造', '神性', '普遍性' ),
				),
				array(
					'id'    => 'q2_d',
					'label' => '自由で型にはまらない',
					'tags'  => array( 'オルタナリー', 'DIY', 'ストリート' ),
				),
				array(
					'id'    => 'q2_e',
					'label' => 'あたたかく、人にやさしい',
					'tags'  => array( '心の豊かさ', '自然', '童心' ),
				),
			),
		),
		array(
			'id'       => 'q3',
			'question' => 'アートに重ねたい「物語」に近いのは？',
			'weight'   => 20,
			'options'  => array(
				array(
					'id'    => 'q3_a',
					'label' => '逆境からの再生・再起',
					'tags'  => array( '再生', '転機', '第二のキャリア' ),
				),
				array(
					'id'    => 'q3_b',
					'label' => '国境や枠を越えた広がり',
					'tags'  => array( '国際', '越境', '多文化' ),
				),
				array(
					'id'    => 'q3_c',
					'label' => '社会への問いかけ・メッセージ',
					'tags'  => array( '社会批評', 'メッセージ', '不条理' ),
				),
				array(
					'id'    => 'q3_d',
					'label' => '未来や次世代への希望',
					'tags'  => array( '希望', '子供', '未来' ),
				),
				array(
					'id'    => 'q3_e',
					'label' => '偶然の出会いがもたらす変化',
					'tags'  => array( '偶然', '実験', '挑戦' ),
				),
			),
		),
		array(
			'id'       => 'q4',
			'question' => '惹かれる表現のトーンは？',
			'weight'   => 15,
			'options'  => array(
				array(
					'id'    => 'q4_a',
					'label' => '鮮やかでポジティブ',
					'tags'  => array( 'POP', 'ポジティブ', '実験' ),
				),
				array(
					'id'    => 'q4_b',
					'label' => '力強く、エネルギッシュ',
					'tags'  => array( '生命エネルギー', '格闘', '突破' ),
				),
				array(
					'id'    => 'q4_c',
					'label' => '静かで精神的',
					'tags'  => array( '祈り', '神性', '自然' ),
				),
				array(
					'id'    => 'q4_d',
					'label' => '都会的でクール',
					'tags'  => array( '都市', '現代性', 'ストリート' ),
				),
				array(
					'id'    => 'q4_e',
					'label' => '素朴で温かい',
					'tags'  => array( '童心', '植物', '愛' ),
				),
			),
		),
		array(
			'id'       => 'q5',
			'question' => '応援したいアーティスト像に近いのは？',
			'weight'   => 15,
			'options'  => array(
				array(
					'id'    => 'q5_a',
					'label' => '若く、これから伸びる人',
					'tags'  => array( '若者', '希望', '挑戦' ),
				),
				array(
					'id'    => 'q5_b',
					'label' => '逆境を乗り越えてきた人',
					'tags'  => array( '再生', '第二のキャリア', '転機' ),
				),
				array(
					'id'    => 'q5_c',
					'label' => '社会課題に取り組む人',
					'tags'  => array( '社会貢献', 'SDGs', '貧困' ),
				),
				array(
					'id'    => 'q5_d',
					'label' => '伝統や技を究める人',
					'tags'  => array( '伝統', '工芸', '錬磨' ),
				),
				array(
					'id'    => 'q5_e',
					'label' => '唯一無二の世界観を持つ人',
					'tags'  => array( '唯一無二', 'オルタナリー', '実験' ),
				),
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
