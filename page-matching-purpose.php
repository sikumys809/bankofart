<?php
/**
 * Template Name: MATCHING（企業理念マッチング診断）
 *
 * 企業理念 × アーティストのマッチング診断（artist向け5問）。
 * mockups/matching-purpose.html を正として移植。
 *
 * 方針 B改：診断ロジック・質問(QUESTIONS)・アーティストデータ(ARTISTS 20名)・スコアリング・
 * 画面遷移はすべて assets/js/page-matching-purpose.js（モックJSをそのまま移植）で完結する。
 * 本テンプレートは「画面の器」と、結果リンク動的化用の名前→URL辞書供給（wp_localize_script）のみを担う。
 *   - 結果に出たアーティストは nameJa で実 artist 投稿を検索し、あれば single-artist 実URLへ。
 *     無ければ「プロフィール準備中」フォールバック（JS側）。
 *   - 辞書供給は inc/enqueue.php（is_page('matching-purpose')）で実施。
 *
 * テンプレート適用：スラッグ "matching-purpose" の固定ページ、または「MATCHING」テンプレート選択ページ。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$briefing_url = bankofart_briefing_url(); // 結果画面「問い合わせる」。
?>

<main id="main" class="matching-purpose-page">

	<!-- ━━━━━━ Screen 1: スタート ━━━━━━ -->
	<section class="match-screen is-active" id="screen-start">
		<div class="match-hero">
			<div class="match-hero-eyebrow">Matching</div>
			<h1 class="match-hero-title">MATCHING</h1>
			<p class="match-hero-ja">企業理念×アーティスト</p>
			<p class="match-hero-lead">
				5つの質問に答えるだけで、<br>
				あなたの企業に最も響くアーティスト1名と、<br>
				相性の良いその他アーティスト3名をご提案します。
			</p>
			<button class="match-start-btn" id="start-btn">診断スタート</button>
		</div>
	</section>

	<!-- ━━━━━━ Screen 2: 質問 ━━━━━━ -->
	<section class="match-screen" id="screen-question">
		<div class="match-q-section">
			<div class="match-progress" id="q-progress">QUESTION 1 / 5</div>
			<div class="match-progress-bar"><div class="match-progress-fill" id="q-progress-fill" style="width:20%;"></div></div>
			<h2 class="match-q-title" id="q-title">質問テキスト</h2>
			<div class="match-options" id="q-options"></div>
		</div>
	</section>

	<!-- ━━━━━━ Screen 3: ローディング ━━━━━━ -->
	<section class="match-screen" id="screen-loading">
		<div class="match-loading">
			<div class="match-loading-spinner"></div>
			<p class="match-loading-text">あなたの価値観を読み解いています...</p>
		</div>
	</section>

	<!-- ━━━━━━ Screen 4: 結果 ━━━━━━ -->
	<section class="match-screen" id="screen-result">
		<div class="match-result">
			<div class="result-eyebrow">Your Match</div>
			<h2 class="result-title">あなたに最も響くアーティスト</h2>

			<div class="result-main" id="main-artist"></div>

			<div class="result-sub-h">その他のおすすめアーティスト</div>
			<div class="result-sub-list" id="sub-artists"></div>

			<div class="result-actions">
				<button class="is-secondary" onclick="restart()">もう一度診断する</button>
				<a href="<?php echo esc_url( $briefing_url ); ?>" target="_blank" rel="noopener" class="is-primary">問い合わせる</a>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
