<?php
/**
 * ART アーカイブ（archive-art）
 *
 * mockups/art.html を正として移植。7軸ANDフィルタ（Status / Artist / Form /
 * Genre / Technique / Size / Main Color）＋ソート（NEWEST / ARTIST / SIZE）。
 * 全件取得（posts_per_page = -1）してクライアント側で絞り込む。
 *
 * フィルターボタン・カラースウォッチは get_terms() / get_posts() で動的生成。
 * 表示名＝name（日本語）、照合キー＝slug（Main Color は英語slug）。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// 各軸のターム（フィルターボタン用）。
$get_terms_for = static function ( $tax ) {
	$terms = get_terms(
		array(
			'taxonomy'   => $tax,
			'hide_empty' => false,
			'orderby'    => 'term_id',
		)
	);
	return is_wp_error( $terms ) ? array() : $terms;
};
$status_terms    = $get_terms_for( 'art_status' );
$form_terms      = $get_terms_for( 'art_form' );
$genre_terms     = $get_terms_for( 'art_genre' );
$technique_terms = $get_terms_for( 'art_technique' );
$size_terms      = $get_terms_for( 'art_size' );
$color_terms     = $get_terms_for( 'art_main_color' );

// Artist 軸：公開アーティスト（post_name を照合キーに）。
$artist_posts = get_posts(
	array(
		'post_type'      => 'artist',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
	)
);

// 作品を全件取得（NEWEST = 投稿日降順を既定に）。
$arts_q = new WP_Query(
	array(
		'post_type'      => 'art',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	)
);
$total = (int) $arts_q->post_count;

/**
 * 通常タクソノミーのフィルター行を出力するヘルパー。
 *
 * @param string $axis   軸名（data 属性サフィックス）。
 * @param string $label  ラベル。
 * @param array  $terms  ターム配列。
 * @param bool   $en     英字ボタン（.en）にするか。
 */
$render_filter_row = static function ( $axis, $label, $terms, $en = false ) {
	$cls = $en ? 'filter-tag en' : 'filter-tag';
	?>
	<div class="filter-row">
		<span class="filter-label"><?php echo esc_html( $label ); ?></span>
		<div class="filter-tags">
			<button type="button" class="<?php echo esc_attr( $cls ); ?> is-active" data-axis="<?php echo esc_attr( $axis ); ?>" data-filter="all"><?php echo $en ? 'ALL' : 'すべて'; ?></button>
			<?php foreach ( $terms as $term ) : ?>
				<button type="button" class="<?php echo esc_attr( $cls ); ?>" data-axis="<?php echo esc_attr( $axis ); ?>" data-filter="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></button>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
};
?>

<main id="main" class="archive-art">

	<section class="page-hero">
		<h1 class="page-hero-title rv">ART</h1>
		<p class="page-hero-ja rv d1">作品一覧</p>
		<div class="page-hero-rule rv d2"></div>
		<div class="page-statement rv d3">
			<p><?php echo esc_html__( 'バンク・オブ・アート公認作品の一覧。', 'bankofart' ); ?></p>
		</div>
	</section>

	<section class="filter-section">
		<!-- モバイル用フィルタートグル（PCでは CSS で非表示） -->
		<button type="button" class="filter-toggle" id="filterToggle" aria-expanded="false" aria-controls="artFilterInner">
			<span>詳細から探す</span>
			<span class="filter-toggle-icon" aria-hidden="true"></span>
		</button>
		<div class="filter-inner" id="artFilterInner">
			<?php
			$render_filter_row( 'status', 'Status', $status_terms, true );
			?>

			<div class="filter-row">
				<span class="filter-label">Artist</span>
				<div class="filter-tags">
					<button type="button" class="filter-tag is-active" data-axis="artist" data-filter="all">すべて</button>
					<?php foreach ( $artist_posts as $artist ) : ?>
						<button type="button" class="filter-tag" data-axis="artist" data-filter="<?php echo esc_attr( $artist->post_name ); ?>"><?php echo esc_html( get_the_title( $artist ) ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>

			<?php
			$render_filter_row( 'form', 'Form', $form_terms );
			$render_filter_row( 'genre', 'Genre', $genre_terms );
			$render_filter_row( 'technique', 'Technique', $technique_terms );
			$render_filter_row( 'size', 'Size', $size_terms );
			?>

			<div class="filter-row">
				<span class="filter-label">Main Color</span>
				<div class="color-swatches" id="colorSwatches">
					<button type="button" class="color-swatch is-active" data-axis="color" data-filter="all">
						<span class="color-dot is-allcolor"></span>
						<span class="color-name">すべて</span>
					</button>
					<?php
					foreach ( $color_terms as $term ) :
						$hex          = get_term_meta( $term->term_id, 'color_hex', true );
						$effect_title = get_term_meta( $term->term_id, 'color_effect_title', true );
						$effect_text  = get_term_meta( $term->term_id, 'color_effect_description', true );
						$place_title  = get_term_meta( $term->term_id, 'recommended_place_title', true );
						$place_text   = get_term_meta( $term->term_id, 'recommended_place_description', true );
						?>
						<button
							type="button"
							class="color-swatch"
							data-axis="color"
							data-filter="<?php echo esc_attr( $term->slug ); ?>"
							data-hex="<?php echo esc_attr( $hex ); ?>"
							data-effect-title="<?php echo esc_attr( $effect_title ); ?>"
							data-effect-text="<?php echo esc_attr( $effect_text ); ?>"
							data-place-title="<?php echo esc_attr( $place_title ); ?>"
							data-place-text="<?php echo esc_attr( $place_text ); ?>"
						>
							<span class="color-dot" style="background:<?php echo esc_attr( $hex ? $hex : 'var(--warm-gray)' ); ?>;"></span>
							<span class="color-name"><?php echo esc_html( $term->name ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- カラー効果パネル（スウォッチ選択時に JS で内容を差し込み・表示） -->
			<div class="color-panel" id="colorPanel">
				<div class="color-panel-inner">
					<span class="color-panel-chip" id="cpChip" aria-hidden="true"></span>
					<div class="color-panel-block">
						<div class="color-panel-label">Color Effect</div>
						<div class="color-panel-title" id="cpEffectTitle"></div>
						<p class="color-panel-text" id="cpEffectText"></p>
					</div>
					<div class="color-panel-block">
						<div class="color-panel-label">Recommended Place</div>
						<p class="color-panel-place" id="cpPlace"></p>
					</div>
				</div>
			</div>

			<div class="filter-result">
				<div class="filter-count-text">全 <span class="boa-num"><?php echo esc_html( $total ); ?></span> 点の作品 / 表示 <span class="boa-num filter-visible"><?php echo esc_html( $total ); ?></span> 点</div>
				<div class="sort-tabs">
					<button type="button" class="is-active" data-sort="newest">NEWEST</button>
					<button type="button" data-sort="artist">ARTIST</button>
					<button type="button" data-sort="size">SIZE</button>
				</div>
			</div>
		</div>
	</section>

	<section class="art-section">
		<?php if ( $arts_q->have_posts() ) : ?>
			<div class="art-grid" id="artGrid">
				<?php
				while ( $arts_q->have_posts() ) :
					$arts_q->the_post();
					get_template_part(
						'template-parts/cards/card-art',
						null,
						array(
							'art_id'      => get_the_ID(),
							'context'     => 'archive',
							'filter_data' => true,
						)
					);
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<div class="news-empty">
				<p><?php echo esc_html__( '作品がまだ登録されていません。', 'bankofart' ); ?></p>
			</div>
		<?php endif; ?>
	</section>

	<!-- CONTACT -->
	<?php get_template_part( 'template-parts/sections/section-cta' ); ?>

</main>

<?php
get_footer();
