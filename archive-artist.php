<?php
/**
 * ARTIST アーカイブ（archive-artist）
 *
 * mockups/artist.html を正として移植。Status × Genre の2軸ANDフィルタ（JS）。
 * 全件取得（posts_per_page = -1）してクライアント側で絞り込む。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Status / Genre のターム（フィルターボタン用）。
$status_terms = get_terms(
	array(
		'taxonomy'   => 'artist_status',
		'hide_empty' => false,
		'orderby'    => 'term_id',
	)
);
$status_terms = is_wp_error( $status_terms ) ? array() : $status_terms;

$genre_terms = get_terms(
	array(
		'taxonomy'   => 'artist_genre',
		'hide_empty' => false,
		'orderby'    => 'term_id',
	)
);
$genre_terms = is_wp_error( $genre_terms ) ? array() : $genre_terms;

// アーティスト全件（連番採番のため index を使う。専用の番号フィールドは無い）。
$artists_q = new WP_Query(
	array(
		'post_type'      => 'artist',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
		'no_found_rows'  => true,
	)
);

// マッチング診断ページ（Phase 2-E 実装済み・page-matching-purpose.php / スラッグ matching-purpose）。
$matching_url = home_url( '/matching-purpose/' );
?>

<main id="main" class="archive-artist">

	<section class="page-hero">
		<h1 class="page-hero-title rv">ARTIST</h1>
		<p class="page-hero-ja rv d1">所属画家</p>
		<div class="page-statement rv d2">
			<p><?php echo esc_html__( '「画家は絶滅危惧種」とも言われる時代。それでも、描くことをやめない人たちがいる。', 'bankofart' ); ?></p>
			<p class="mid"><?php echo esc_html__( 'なぜ彼らは絵を描くのか。なぜ“画家として生きる”ことを選ぶのか。', 'bankofart' ); ?></p>
			<p><?php echo esc_html__( 'それぞれが歩んできた人生と、描く理由がある。作品の奥にある、彼らだけの物語。', 'bankofart' ); ?></p>
		</div>
	</section>

	<!-- Artist Matching バナー（診断ページは Phase 2-E。リンク先は仮） -->
	<section class="match-banner-sec">
		<div class="match-banner rv">
			<div class="match-banner-left">
				<span class="match-banner-label">Artist Matching</span>
				<h2 class="match-banner-title">企業理念 × アーティスト</h2>
				<p class="match-banner-sub">価値観で繋がる、アートとの出会い。</p>
				<p class="match-banner-body"><span class="boa-num">5</span>つの質問にお答えいただくだけで、御社のパーパスに最も共鳴するアーティストをご提案します。</p>
			</div>
			<div class="match-banner-right">
				<a href="<?php echo esc_url( $matching_url ); ?>" class="match-banner-btn">診断スタート</a>
				<span class="match-banner-meta"><span class="boa-num">5</span>QUESTIONS &nbsp;/&nbsp; <span class="boa-num">3</span>MIN</span>
			</div>
		</div>
	</section>

	<section class="filter-section">
		<div class="filter-inner">
			<div class="filter-row">
				<span class="filter-label">Status</span>
				<div class="filter-tags">
					<button type="button" class="filter-tag is-active" data-axis="status" data-filter="all">すべて</button>
					<?php foreach ( $status_terms as $term ) : ?>
						<button type="button" class="filter-tag" data-axis="status" data-filter="<?php echo esc_attr( $term->term_id ); ?>"><?php echo esc_html( $term->name ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="filter-row">
				<span class="filter-label">Genre</span>
				<div class="filter-tags">
					<button type="button" class="filter-tag is-active" data-axis="genre" data-filter="all">すべて</button>
					<?php foreach ( $genre_terms as $term ) : ?>
						<button type="button" class="filter-tag" data-axis="genre" data-filter="<?php echo esc_attr( $term->term_id ); ?>"><?php echo esc_html( $term->name ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="filter-row filter-row--bottom">
				<span class="filter-count"><span class="boa-num"><?php echo (int) $artists_q->post_count; ?></span>ARTISTS</span>
			</div>
		</div>
	</section>

	<section class="artist-section">
		<?php if ( $artists_q->have_posts() ) : ?>
			<div class="artist-grid" id="artistGrid">
				<?php
				$i = 0;
				while ( $artists_q->have_posts() ) :
					$artists_q->the_post();
					++$i;
					get_template_part(
						'template-parts/cards/card-artist',
						null,
						array(
							'artist_id' => get_the_ID(),
							'context'   => 'archive',
							'number'    => sprintf( '%02d', $i ),
						)
					);
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<div class="news-empty">
				<p><?php echo esc_html__( 'アーティストがまだ登録されていません。', 'bankofart' ); ?></p>
			</div>
		<?php endif; ?>
	</section>

	<!-- 若手画家募集（FOR ARTISTS）バナー。応募導線は recruit 経由（募集要項確認→recruit内の応募ボタンで /artist-entry/ へ） -->
	<?php get_template_part( 'template-parts/sections/section-for-artists', null, array( 'apply_url' => home_url( '/recruit/' ) ) ); ?>

	<!-- CONTACT -->
	<?php get_template_part( 'template-parts/sections/section-cta' ); ?>

</main>

<?php
get_footer();
