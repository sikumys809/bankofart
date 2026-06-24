<?php
/**
 * NEWS アーカイブ（archive-news）
 *
 * mockups/news.html を正として移植。記事は news-item グリッドで一覧表示。
 * NEWS は外部記事リンク方式（external_url があれば別タブ、無ければ内部）。
 * 並びは公開日降順（メインクエリの既定 = 投稿日 DESC。専用 publish_date は無し）。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$cat_terms = get_terms(
	array(
		'taxonomy'   => 'news_category',
		'hide_empty' => false,
		'orderby'    => 'term_id',
	)
);
$cat_terms = is_wp_error( $cat_terms ) ? array() : $cat_terms;
?>

<main id="main" class="archive-news">

	<section class="page-hero">
		<h1 class="page-hero-title rv">NEWS</h1>
		<p class="page-hero-ja rv d1">最新記事</p>
		<div class="page-statement rv d2">
			<p><?php echo esc_html__( 'バンク・オブ・アートの', 'bankofart' ); ?><br class="br-sp"><?php echo esc_html__( '最新情報をお届けします。', 'bankofart' ); ?></p>
		</div>
	</section>

	<section class="filter-section">
		<div class="filter-inner">
			<span class="filter-label">Filter</span>
			<div class="filter-tags">
				<button type="button" class="filter-tag en is-active" data-filter="all">ALL</button>
				<?php foreach ( $cat_terms as $term ) : ?>
					<button type="button" class="filter-tag" data-filter="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></button>
				<?php endforeach; ?>
			</div>
			<span class="filter-count"><span class="boa-num"><?php echo (int) $GLOBALS['wp_query']->post_count; ?></span>ARTICLES</span>
		</div>
	</section>

	<section class="news-section">
		<?php if ( have_posts() ) : ?>
			<div class="news-list" id="newsList">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part(
						'template-parts/cards/card-news',
						null,
						array( 'news_id' => get_the_ID() )
					);
				endwhile;
				?>
			</div>

			<?php
			the_posts_pagination(
				array(
					'mid_size'           => 2,
					'prev_text'          => 'PREV',
					'next_text'          => 'NEXT',
					'screen_reader_text' => ' ',
					'aria_label'         => 'ページ送り',
				)
			);
			?>
		<?php else : ?>
			<div class="news-empty">
				<p><?php echo esc_html__( '記事がまだありません。', 'bankofart' ); ?></p>
			</div>
		<?php endif; ?>
	</section>

</main>

<?php
get_footer();
