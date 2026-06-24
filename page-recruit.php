<?php
/**
 * Template Name: RECRUIT（JOIN US / 画家募集）
 *
 * RECRUIT 固定ページ。mockups/recruit__1_.html を正として移植（静的中心・1ステップ）。
 *   セクション順：HERO → あなたの未来 → 2つの活動形態 → 選考フロー → 応募資格 → 大型CTA → 諸注意（折りたたみ）
 *
 * 仕様変更：応募資格「年齢」上限を 39歳 → 49歳 に変更（モックは39歳）。
 * 注意事項は <details>/<summary> のネイティブアコーディオン（JS不要）。
 * 応募フォーム / 募集要項PDF の URL は未確定のため bankofart_apply_url() /
 * bankofart_recruit_guidelines_pdf_url()（共に '#' プレースホルダ）で一元管理し、確定後は1箇所差し替え。
 *
 * テンプレート適用：スラッグ "recruit" の固定ページ、または「RECRUIT」テンプレート選択ページ。
 * アイコン画像はテーマ同梱（assets/img/icons）の実ファイルを参照。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$icon           = function ( $file ) {
	return esc_url( get_theme_file_uri( 'assets/img/icons/' . $file ) );
};
$apply_url      = bankofart_apply_url();                      // 応募フォーム（未確定・プレースホルダ '#'）。
$guidelines_pdf = bankofart_recruit_guidelines_pdf_url();     // 募集要項PDF（未確定・プレースホルダ '#'）。
$artist_archive = get_post_type_archive_link( 'artist' );     // 「先輩アーティストを見る」。
?>

<main id="main" class="recruit-page">

	<!-- ════════ 1. HERO ════════ -->
	<section class="page-hero">
		<h1 class="page-hero-title rv">JOIN US</h1>
		<p class="page-hero-ja rv d1">画家募集</p>
		<div class="recruit-hero-msg rv d2">
			<strong>描いて、生きていける世界へ。</strong>
			その世界は、私たちだけではつくれません。<br>
			描き続けるあなたの挑戦が必要です。<br>
			Bank of Artは、創作に情熱を注ぐ画家を<br class="br-sp">募集しています。
		</div>
	</section>

	<!-- ════════ 2. あなたの未来がどう変わる？ ════════ -->
	<section class="recruit-section">
		<div class="recruit-section-eyebrow rv">Your Future</div>
		<h2 class="recruit-section-title rv d1">この場所で、<br>あなたの「描く」はこう変わる。</h2>
		<div class="future-grid">
			<div class="future-card rv d2">
				<img class="future-card-icon" src="<?php echo $icon( 'BOAicon_join-06.png' ); ?>" alt="">
				<div class="future-card-num">01</div>
				<div class="future-card-h">描くことに、<br>もっと夢中になれる。</div>
				<div class="future-card-body">委託販売モデルでは作品が売れるまで収入が入りません。BANK OF ARTは全作品買取制。次の制作にすぐ取りかかれる環境を、私たちが整えます。</div>
			</div>
			<div class="future-card rv d3">
				<img class="future-card-icon" src="<?php echo $icon( 'BOAicon-16.png' ); ?>" alt="">
				<div class="future-card-num">02</div>
				<div class="future-card-h">作品が、<br>社会を巡る。</div>
				<div class="future-card-body">買い取った作品は応援企業のオフィスへ。リセールでまた別の企業へ。あなたの絵は、何年も何十年も、見られ続け、価値を蓄えていきます。</div>
			</div>
			<div class="future-card rv d4">
				<img class="future-card-icon" src="<?php echo $icon( 'BOAicon_join-04.png' ); ?>" alt="">
				<div class="future-card-num">03</div>
				<div class="future-card-h">登録画家から、<br>公認画家へ。</div>
				<div class="future-card-body">まずは登録画家として活動を開始。継続的な制作と発信のなかで、BANK OF ARTの代表作家として迎え入れる「公認画家」契約への道も開かれています。</div>
			</div>
		</div>
		<div style="text-align:center;margin-top:60px;">
			<a href="<?php echo esc_url( $apply_url ); ?>" class="future-apply-btn rv d5">応募フォームへ</a>
		</div>
	</section>

	<!-- ════════ 3. 登録画家 → 公認画家 ════════ -->
	<section class="recruit-section" style="background:var(--paper);max-width:none;">
		<div style="max-width:1080px;margin:0 auto;">
			<div class="recruit-section-eyebrow rv">Two Tiers</div>
			<h2 class="recruit-section-title rv d1">2つの活動形態</h2>
			<div class="tier-grid">
				<div class="tier-card rv d2">
					<div class="tier-label">Tier 01</div>
					<div class="tier-name">BOA 登録画家</div>
					<ul class="tier-list">
						<li>契約形態：作品ごとの買取契約</li>
						<li>並列のクリエイターとして関わる</li>
						<li>公式サイト・作品流通網への掲載</li>
						<li>本募集の応募窓口</li>
					</ul>
				</div>
				<div class="tier-card is-pro rv d3">
					<div class="tier-label">Tier 02</div>
					<div class="tier-name">BOA 公認画家</div>
					<ul class="tier-list">
						<li>契約形態：BANK OF ARTとの専属契約</li>
						<li>BANK OF ARTの代表作家として継続的に関わる</li>
						<li>新作制作やキャリア形成を長期的に支援</li>
						<li>BOAからのお声がけにより契約</li>
					</ul>
				</div>
			</div>
		</div>
	</section>

	<!-- ════════ 4. 選考フロー ════════ -->
	<section class="recruit-section">
		<div class="recruit-section-eyebrow rv">Process</div>
		<h2 class="recruit-section-title rv d1">選考フロー</h2>
		<div class="flow">
			<div class="flow-step rv d2">
				<div class="flow-step-num">STEP 01</div>
				<div class="flow-step-h">ご応募</div>
				<div class="flow-step-body">オンライン応募フォームよりポートフォリオ（PDF/10MB以下）を提出。</div>
			</div>
			<div class="flow-step rv d3">
				<div class="flow-step-num">STEP 02</div>
				<div class="flow-step-h">書類審査</div>
				<div class="flow-step-body">BANK OF ARTによる審査。応募から2〜4週間程度で結果をメールで通知。</div>
			</div>
			<div class="flow-step rv d4">
				<div class="flow-step-num">STEP 03</div>
				<div class="flow-step-h">面接</div>
				<div class="flow-step-body">書類審査通過者のみ。オンラインまたは対面で30〜60分程度。</div>
			</div>
			<div class="flow-step rv d5">
				<div class="flow-step-num">STEP 04</div>
				<div class="flow-step-h">登録画家として開始</div>
				<div class="flow-step-body">契約手続きを経てBOA登録画家として活動開始。専属契約の道も。</div>
			</div>
		</div>
		<p style="text-align:center;margin-top:36px;font-family:'Shippori Mincho B1',serif;font-size:13px;letter-spacing:1px;color:var(--mid);">
			通年で募集しています。書類審査の結果は合否を問わず必ずご連絡いたします。
		</p>
	</section>

	<!-- ════════ 5. 応募資格 ════════ -->
	<section class="recruit-section">
		<div class="recruit-section-eyebrow rv">Requirements</div>
		<h2 class="recruit-section-title rv d1">応募資格</h2>
		<table class="spec-table rv d2">
			<tr><th>年齢</th><td>応募時点で18歳以上49歳以下の方</td></tr>
			<tr><th>所在</th><td>日本国内にて手続き、および作品の搬入搬出・発送受領が可能な方</td></tr>
			<tr><th>制作意欲</th><td>継続して制作・発表を行う意志のある方</td></tr>
			<tr><th>作品</th><td>1点ものの作品を主軸として制作される方</td></tr>
			<tr><th>理念</th><td>BANK OF ARTの理念・運用方針にご賛同いただける方</td></tr>
		</table>
		<div style="text-align:center;margin-top:40px;">
			<a href="<?php echo esc_url( $guidelines_pdf ); ?>" target="_blank" rel="noopener" class="recruit-guidelines-btn rv d3">詳しい募集要項はこちら</a>
		</div>
	</section>

	<!-- ════════ 6. 大型CTA ════════ -->
	<section class="recruit-cta-big" id="apply">
		<h2 class="recruit-cta-big-h rv">あなたの作品を、<br>未来へつなぐ。</h2>
		<p class="recruit-cta-big-sub rv d1">あなたの創作への情熱を見せてください。</p>
		<div class="recruit-cta-btns rv d2">
			<a href="<?php echo esc_url( $apply_url ); ?>" target="_blank" rel="noopener" class="recruit-cta-btn is-primary">応募フォームへ</a>
			<a href="<?php echo esc_url( $artist_archive ); ?>" class="recruit-cta-btn is-secondary">先輩アーティストを見る</a>
		</div>
	</section>

	<!-- ════════ 7. 諸注意（折りたたみ＝<details> ネイティブアコーディオン） ════════ -->
	<section class="recruit-section">
		<div class="recruit-section-eyebrow rv">Notes</div>
		<h2 class="recruit-section-title rv d1">応募にあたっての注意事項</h2>
		<div class="notes-block">
			<details class="notes-item">
				<summary>著作権・肖像権・作品の取り扱いについて</summary>
				<div class="notes-item-body">
					<p>応募作品および買取後の作品の著作権は、原則として作者（応募者）に帰属します。</p>
					<p>買取後の作品について、BANK OF ARTは次の目的での使用権を有します：公式サイト・SNS・図録等での画像掲載、広報・宣伝活動における画像使用、法人・個人事業主への販売・貸出・リセール、展示会・各種イベントでの展示。</p>
					<p>審査通過・登録・専属契約後の広報活動において、応募者の氏名・顔写真・経歴・インタビュー等を、サイト・SNS・印刷物・動画等で使用させていただく場合があります。詳細は契約時に書面で取り交わします。</p>
				</div>
			</details>
			<details class="notes-item">
				<summary>個人情報の取り扱いについて</summary>
				<div class="notes-item-body">
					<p>ご応募の過程で取得した個人情報は、審査・選考・面接調整・結果通知、登録画家・公認画家としての契約手続き・連絡、BANK OF ARTからの関連情報のお知らせにのみ使用します。</p>
					<p>法令に基づく場合を除き、ご本人の同意なく第三者に提供することはありません。</p>
				</div>
			</details>
			<details class="notes-item">
				<summary>その他の諸注意</summary>
				<div class="notes-item-body">
					<p>提出いただいた応募書類・データは返却いたしません。</p>
					<p>提出いただいた情報に虚偽が判明した場合、審査通過・登録・契約を取り消すことがあります。</p>
					<p>不適切な内容・公序良俗に反する作品は審査の対象外とします。</p>
					<p>応募に伴う通信費・データ作成費等は応募者のご負担となります。</p>
					<p>本要項は予告なく内容を変更する場合があります。最新版はBANK OF ART公式サイトをご確認ください。</p>
					<p>やむを得ない事情により、募集を中止または延期する場合があります。</p>
				</div>
			</details>
			<details class="notes-item">
				<summary>買取条件について</summary>
				<div class="notes-item-body">
					<p>買取条件（価格・点数等）については、面接時に直接ご説明いたします。本要項上には記載しておりません。</p>
				</div>
			</details>
		</div>
	</section>

</main>

<?php
get_footer();
