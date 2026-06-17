<?php
/**
 * COLLECTOR アーカイブ（archive-collector）
 *
 * mockups/collector.html を正として移植。Issue（課題）1軸の絞り込み（JS）。
 * 全件取得（posts_per_page = -1）してクライアント側で絞り込む。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Issue（課題）のターム（フィルターボタン用）。
$issue_terms = get_terms(
	array(
		'taxonomy'   => 'collector_issue',
		'hide_empty' => false,
		'orderby'    => 'term_id',
	)
);
$issue_terms = is_wp_error( $issue_terms ) ? array() : $issue_terms;

// 企業を全件取得。
$collectors_q = new WP_Query(
	array(
		'post_type'      => 'collector',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
		'no_found_rows'  => true,
	)
);

// 課題逆引き診断ページ（Phase 2-E 実装済み・page-matching-issue.php / スラッグ matching-issue）。
$matching_url = home_url( '/matching-issue/' );
?>

<main id="main" class="archive-collector">

	<section class="page-hero">
		<h1 class="page-hero-title rv">COLLECTOR</h1>
		<p class="page-hero-ja rv d1">画家応援企業</p>
		<div class="page-statement rv d2">
			<p><?php echo esc_html__( '「アートを迎える」という選択には、それぞれの想いや物語がある。', 'bankofart' ); ?></p>
			<p class="mid"><?php echo esc_html__( 'なぜ若手画家を応援しようと思ったのか。なぜ“アートを飾る”という選択をしたのか。', 'bankofart' ); ?></p>
			<p><?php echo esc_html__( '作品との出会いは空間だけでなく、人や会話、働く空気までも少しずつ変えていく。', 'bankofart' ); ?></p>
		</div>
	</section>

	<!-- Issue Matching バナー（課題逆引き診断は Phase 2-E。リンク先は仮） -->
	<section class="match-banner-sec">
		<div class="match-banner rv">
			<div class="match-banner-left">
				<span class="match-banner-label">Issue Matching</span>
				<h2 class="match-banner-title">企業課題 <span class="accent">×</span> アート</h2>
				<p class="match-banner-sub">その課題、アートで解決するかも。</p>
				<p class="match-banner-body"><span class="boa-num">3</span>つの質問にお答えいただくだけで、御社の課題に合ったアートをご提案します。</p>
			</div>
			<div class="match-banner-right">
				<a href="<?php echo esc_url( $matching_url ); ?>" class="match-banner-btn">課題から探す</a>
				<span class="match-banner-meta"><span class="boa-num">3</span>QUESTIONS &nbsp;/&nbsp; <span class="boa-num">2</span>MIN</span>
			</div>
		</div>
	</section>

	<section class="collector-section">
		<div class="section-head">
			<div class="section-head-en rv">COLLECTORS</div>
			<div class="section-head-ja rv d1">画家応援企業の声</div>
		</div>

		<div class="filter-inner rv">
			<span class="filter-label">Issue</span>
			<div class="filter-tags">
				<button type="button" class="filter-tag is-active" data-filter="all">すべて</button>
				<?php foreach ( $issue_terms as $term ) : ?>
					<button type="button" class="filter-tag" data-filter="<?php echo esc_attr( $term->term_id ); ?>"><?php echo esc_html( $term->name ); ?></button>
				<?php endforeach; ?>
			</div>
			<a href="<?php echo esc_url( $matching_url ); ?>" class="filter-match-btn">
				<span class="filter-match-eyebrow">Matching</span>
				<span class="filter-match-label">課題から探す →</span>
			</a>
			<span class="filter-count"><span class="boa-num"><?php echo (int) $collectors_q->post_count; ?></span>COLLECTORS</span>
		</div>

		<?php if ( $collectors_q->have_posts() ) : ?>
			<div class="collector-grid" id="collectorGrid">
				<?php
				while ( $collectors_q->have_posts() ) :
					$collectors_q->the_post();
					get_template_part(
						'template-parts/cards/card-collector',
						null,
						array( 'collector_id' => get_the_ID() )
					);
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<div class="news-empty">
				<p><?php echo esc_html__( '画家応援企業がまだ登録されていません。', 'bankofart' ); ?></p>
			</div>
		<?php endif; ?>
	</section>

	<!-- CONTACT -->
	<?php get_template_part( 'template-parts/sections/section-cta' ); ?>

</main>

<?php
get_footer();
