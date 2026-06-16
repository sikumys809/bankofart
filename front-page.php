<?php
/**
 * フロントページ（front-page）
 *
 * mockups/index.html を正として移植。
 * STEP 1：骨組み + 静的セクション（HERO / GALLERY / HOW / FOUNDER / CTA）。
 * データ連動セクション（ARTIST / ART / COLLECTOR / NEWS）は STEP 2 で実装するため、
 * 見出しのみ出し、カード一覧部分はプレースホルダ（コメント）にしている。
 *
 * セクション順：HERO → ARTIST → ART → COLLECTOR → GALLERY → HOW → FOUNDER → NEWS → CTA
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// ---- HERO 背景画像（仮）。assets/images/hero_test.jpg を置くと反映。無ければCSSのグラデが出る。
// 差し替えはこの1変数 or 画像ファイルの入れ替えのみ。
$hero_bg_file  = get_theme_file_path( 'assets/images/hero_test.jpg' );
$hero_bg_url   = get_theme_file_uri( 'assets/images/hero_test.jpg' );
$hero_bg_style = file_exists( $hero_bg_file ) ? ' style="background-image:url(\'' . esc_url( $hero_bg_url ) . '\');"' : '';

// ロゴ（hero）。boa-12=回転する円マーク / boa-07=白ワードマーク（モックの boa-17 が無いため代替）。
$logo_circle = get_theme_file_uri( 'assets/img/logo/boa-12.png' );
$logo_text   = get_theme_file_uri( 'assets/img/logo/boa-07.png' );

// アーカイブリンク。
$artist_archive    = get_post_type_archive_link( 'artist' );
$art_archive       = get_post_type_archive_link( 'art' );
$collector_archive = get_post_type_archive_link( 'collector' );
$news_archive      = get_post_type_archive_link( 'news' );
?>

<main id="main" class="front-page">

	<!-- ════════ HERO（sticky 2レイヤー）════════ -->
	<div class="hero-stack-wrap">

		<!-- レイヤー1：背景 + ロゴ + キャッチ（スクロールで blur） -->
		<section class="hero-stack-layer hero-layer-logo">
			<div class="hero-blur-target">
				<div class="hero-bg"<?php echo $hero_bg_style; // phpcs:ignore WordPress.Security.EscapeOutput -- URLは上で esc_url 済み ?>></div>
				<div class="hero-content">
					<div class="hero-logo-stack">
						<img class="logo-circle" src="<?php echo esc_url( $logo_circle ); ?>" alt="">
						<img class="logo-text" src="<?php echo esc_url( $logo_text ); ?>" alt="BANK of ART">
					</div>
					<div class="hero-catch">
						<div class="hero-catch-ja">絵描きの明日を創出する。</div>
					</div>
				</div>
			</div>
			<div class="hero-scroll-hint">
				<div class="scroll-v"></div>
				<div class="scroll-v-anim"></div>
				<span class="scroll-word">Scroll</span>
			</div>
		</section>

		<!-- レイヤー2：ARTIST（ヒーロー上に重なる） ※STEP2でカード連動 -->
		<section class="hero-stack-layer hero-layer-artist artist-section" id="artist">
			<div class="artist-stack-inner">
				<div class="dark-section-head">
					<h2 class="dark-section-head-en"><span class="tr" data-tr>Artist</span></h2>
					<p class="dark-section-head-ja rv d1">公認アーティスト一覧</p>
					<div class="head-rule"></div>
				</div>

				<div class="artist-inner">
					<div class="artist-rail">
						<!-- STEP2: 公認アーティストの最新5名をここに（artist_status=公認画家 等で抽出し card） -->
					</div>
					<div class="guest-more rv d2">
						<a href="<?php echo esc_url( $artist_archive ); ?>" class="guest-more-btn">View All Artists →</a>
					</div>
				</div>
			</div>
		</section>
	</div>

	<div class="rule-section"><hr class="rule-h rule-double"></div>

	<!-- ════════ ART ※STEP2でカード連動 ════════ -->
	<section class="art-section" id="art">
		<div class="dark-section-head">
			<h2 class="dark-section-head-en"><span class="tr" data-tr>Art</span></h2>
			<p class="dark-section-head-ja rv d1">作品一覧</p>
			<div class="head-rule"></div>
		</div>
		<!-- STEP2: 作品の最新N件をここに（card-art / art-collage 等） -->
		<div class="art-cta rv">
			<a href="<?php echo esc_url( $art_archive ); ?>" class="art-see-all">View All Works →</a>
		</div>
	</section>

	<!-- ════════ COLLECTOR ※STEP2でカード連動 ════════ -->
	<section class="collector" id="collector">
		<div class="collector-inner">
			<div class="collector-head rv">
				<h2 class="collector-head-en"><span class="tr" data-tr>Collector</span></h2>
				<p class="collector-head-ja">画家応援企業</p>
				<div class="head-rule"></div>
			</div>
			<!-- STEP2: 画家応援企業ロゴ／カードをここに（collector を N件） -->
			<div class="collector-cta rv">
				<a href="<?php echo esc_url( $collector_archive ); ?>" class="collector-see-all">View All Collectors →</a>
			</div>
		</div>
	</section>

	<!-- ════════ GALLERY（静的）════════ -->
	<section class="gallery-section" id="gallery">
		<div class="dark-section-head">
			<h2 class="dark-section-head-en"><span class="tr" data-tr>Gallery</span></h2>
			<p class="dark-section-head-ja rv d1">展示・ギャラリー風景</p>
			<div class="head-rule"></div>
		</div>

		<div class="gallery-stage">
			<div class="carousel-wrap">
				<div class="carousel-track" id="carTrack">
					<?php
					// 仮スライド（実際のギャラリー写真は後日差し替え）。
					$gallery_placeholders = array(
						'linear-gradient(135deg,#2a2a2a 0%,#01ae84 100%)',
						'linear-gradient(135deg,#0a0a0a 0%,#444 100%)',
						'linear-gradient(135deg,#01ae84 0%,#018c6a 100%)',
						'linear-gradient(135deg,#3a3a3a 0%,#7a7a7a 100%)',
					);
					foreach ( $gallery_placeholders as $bg ) :
						?>
						<div class="car-item">
							<div class="car-item-img"><div class="car-item-img-bg" style="background:<?php echo esc_attr( $bg ); ?>;"></div></div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="car-controls">
				<button class="car-btn" id="carPrev" aria-label="Previous">&larr;</button>
				<div class="car-indicator" id="carIndicator"></div>
				<button class="car-btn" id="carNext" aria-label="Next">&rarr;</button>
			</div>

			<div class="gallery-cta rv">
				<a href="<?php echo esc_url( home_url( '/online-briefing/' ) ); ?>" class="gallery-reservation">RESERVATION →</a>
			</div>
		</div>
	</section>

	<!-- ════════ HOW（静的・4 STEPS）════════ -->
	<section class="how" id="how">
		<div class="how-inner">
			<div class="how-head">
				<h2 class="how-head-title"><span class="tr" data-tr>4 STEPS OF BANK OF ART</span></h2>
				<p class="how-head-ja rv d1">ご利用の流れ</p>
				<div class="head-rule"></div>
			</div>

			<div class="how-stack" id="howStack">
				<div class="how-line"><div class="how-line-fill" id="howLineFill"></div></div>

				<div class="how-row right rv">
					<div class="how-num-wrap"><span class="how-num">01</span></div>
					<div class="how-txt-wrap">
						<div class="how-step-label">Deposited</div>
						<div class="how-step-ja">作品を預ける</div>
						<div class="how-step-desc">アーティストから作品を全買取形式で預託。画家のキャッシュフローを改善します。</div>
					</div>
				</div>
				<div class="how-row left rv d1">
					<div class="how-num-wrap"><span class="how-num brand">02</span></div>
					<div class="how-txt-wrap">
						<div class="how-step-label">Purchased</div>
						<div class="how-step-ja">企業が購入</div>
						<div class="how-step-desc">即時償却・少額減価償却特例を活用して購入。節税と芸術支援を同時に実現します。</div>
					</div>
				</div>
				<div class="how-row right rv d2">
					<div class="how-num-wrap"><span class="how-num">03</span></div>
					<div class="how-txt-wrap">
						<div class="how-step-label">Exhibited</div>
						<div class="how-step-ja">オフィスに飾る</div>
						<div class="how-step-desc">作品をオフィスや公共施設に展示。作家の認知向上にも直接つながります。</div>
					</div>
				</div>
				<div class="how-row left rv d3">
					<div class="how-num-wrap"><span class="how-num brand">04</span></div>
					<div class="how-txt-wrap">
						<div class="how-step-label">Recovered</div>
						<div class="how-step-ja">リセールで回収</div>
						<div class="how-step-desc">最大70%のリセールで資産を回収。売却益も期待できます。</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- ════════ FOUNDER（静的・MESSAGE）════════ -->
	<section class="founder" id="founder">
		<div class="founder-inner">
			<h2 class="founder-head-en"><span class="tr" data-tr>MESSAGE</span></h2>
			<div class="head-rule"></div>

			<div class="founder-layout">
				<div class="founder-message rv d1">
					<p class="founder-quote">「画家という生き方を、当たり前に。」</p>
					<p class="founder-quote-sub">誰にも見つかっていない才能に最初のスポンサーを。</p>
				</div>
				<div class="founder-portraits rv d2">
					<div class="founder-card">
						<div class="founder-portrait fp1"></div>
						<div class="founder-info">
							<div class="founder-name-ja">水野 永吉</div>
							<div class="founder-univ">慶應義塾大学 卒</div>
						</div>
					</div>
					<div class="founder-card">
						<div class="founder-portrait fp2"></div>
						<div class="founder-info">
							<div class="founder-name-ja">岡田 美波</div>
							<div class="founder-univ">成蹊大学 卒</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<div class="rule-section"><hr class="rule-h rule-double"></div>

	<!-- ════════ NEWS ※STEP2でカード連動 ════════ -->
	<section class="news" id="news">
		<div class="news-inner">
			<div class="news-head">
				<h2 class="news-head-en"><span class="tr" data-tr>News</span></h2>
				<p class="news-head-ja rv d1">最新記事</p>
				<div class="head-rule"></div>
			</div>
			<!-- STEP2: NEWS の最新4件をここに（card-news context='top'＝縦カード） -->
			<div class="news-all-wrap">
				<a href="<?php echo esc_url( $news_archive ); ?>" class="news-all">All News →</a>
			</div>
		</div>
	</section>

	<!-- ════════ CONTACT ════════ -->
	<?php get_template_part( 'template-parts/sections/section-cta' ); ?>

</main>

<?php
get_footer();
