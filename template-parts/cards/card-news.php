<?php
/**
 * カードコンポーネント：NEWS
 *
 * mockups/news.html の .news-item（横並び行）を正としてDOM・クラスを一致させる。
 * 各要素は !empty() チェックで自動非表示。
 *
 * 引数（$args 経由）:
 *   - news_id int    投稿ID（省略時 get_the_ID()）
 *   - context string 'archive' | 'related' | 'top' | 'more'（既定 'archive'）
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$news_id = isset( $args['news_id'] ) ? (int) $args['news_id'] : get_the_ID();
$context = isset( $args['context'] ) ? $args['context'] : 'archive';

if ( ! $news_id ) {
	return;
}

$permalink = get_permalink( $news_id );
$title     = get_the_title( $news_id );
$summary   = rwmb_meta( 'news_summary', '', $news_id );
$category  = bankofart_get_first_term_name( $news_id, 'news_category' );
$image     = bankofart_get_image( 'news_main_image', $news_id, 'medium' );
$date      = get_the_modified_date( 'Y.m.d', $news_id ); // 更新日.

// 「メディア掲載」はダーク系バッジ（mockup: news-cat.media）。
$cat_mod = ( 'メディア掲載' === $category ) ? ' media' : '';

// NEWS は外部記事リンク方式：external_url があればそのURLへ（別タブ）、無ければ内部パーマリンク。
$external    = rwmb_meta( 'news_external_url', '', $news_id );
$href        = ! empty( $external ) ? $external : $permalink;
$target_attr = ! empty( $external ) ? ' target="_blank" rel="noopener"' : '';

// 一覧フィルター用にカテゴリ slug を data 属性に持たせる。
$cat_terms = get_the_terms( $news_id, 'news_category' );
$cat_slug  = ( ! is_wp_error( $cat_terms ) && ! empty( $cat_terms ) ) ? $cat_terms[0]->slug : '';

if ( 'top' === $context ) :
	/*
	 * TOP用：縦カード（ダーク背景）。mockups/index.html の .news-card。
	 * ダーク背景セクション内での使用前提。要約は表示しない（mockup準拠）。
	 */
	?>
	<a class="news-card" href="<?php echo esc_url( $href ); ?>"<?php echo $target_attr; // phpcs:ignore WordPress.Security.EscapeOutput ?> data-category="<?php echo esc_attr( $cat_slug ); ?>">
		<div class="news-thumb">
			<div class="news-thumb-bg"<?php if ( ! empty( $image['url'] ) ) : ?> style="background-image:url('<?php echo esc_url( $image['url'] ); ?>');" role="img" aria-label="<?php echo esc_attr( $image['alt'] ? $image['alt'] : $title ); ?>"<?php endif; ?>></div>
		</div>
		<div class="news-body">
			<?php if ( ! empty( $category ) ) : ?>
				<div class="news-cat"><?php echo esc_html( $category ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $title ) ) : ?>
				<div class="news-title"><?php echo esc_html( $title ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $date ) ) : ?>
				<div class="news-date boa-num"><?php echo esc_html( $date ); ?></div>
			<?php endif; ?>
		</div>
	</a>
	<?php
	return;
endif;
?>
<a class="news-item news-item--<?php echo esc_attr( $context ); ?>" href="<?php echo esc_url( $href ); ?>"<?php echo $target_attr; // phpcs:ignore WordPress.Security.EscapeOutput ?> data-category="<?php echo esc_attr( $cat_slug ); ?>">
	<div class="news-thumb">
		<span class="news-thumb-inner"<?php if ( ! empty( $image['url'] ) ) : ?> style="background-image:url('<?php echo esc_url( $image['url'] ); ?>');" role="img" aria-label="<?php echo esc_attr( $image['alt'] ? $image['alt'] : $title ); ?>"<?php endif; ?>><?php
		if ( empty( $image['url'] ) && ! empty( $category ) ) {
			echo esc_html( $category );
		}
		?></span>
	</div>

	<div class="news-body">
		<div class="news-meta">
			<?php if ( ! empty( $date ) ) : ?>
				<span class="news-date boa-num"><?php echo esc_html( $date ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $category ) ) : ?>
				<span class="news-cat<?php echo esc_attr( $cat_mod ); ?>"><?php echo esc_html( $category ); ?></span>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $title ) ) : ?>
			<h2 class="news-title"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>

		<?php if ( ! empty( $summary ) ) : ?>
			<p class="news-source"><?php echo esc_html( $summary ); ?></p>
		<?php endif; ?>
	</div>

	<span class="news-arrow" aria-hidden="true"></span>
</a>
