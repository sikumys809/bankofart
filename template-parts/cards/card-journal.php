<?php
/**
 * カードコンポーネント：JOURNAL
 *
 * mockups/journal.html の .news-item（journal は news-* クラスを流用）を正とする。
 * 読了時間・著者は mockup に無いため、存在時のみ news-meta に控えめに付与する。
 * 各要素は !empty() チェックで自動非表示。
 *
 * 引数（$args 経由）:
 *   - journal_id int    投稿ID（省略時 get_the_ID()）
 *   - context    string 'archive' | 'related' | 'top' | 'more'（既定 'archive'）
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$journal_id = isset( $args['journal_id'] ) ? (int) $args['journal_id'] : get_the_ID();
$context    = isset( $args['context'] ) ? $args['context'] : 'archive';

if ( ! $journal_id ) {
	return;
}

$permalink    = get_permalink( $journal_id );
$title        = get_the_title( $journal_id );
$summary      = rwmb_meta( 'journal_summary', '', $journal_id );
$author       = rwmb_meta( 'journal_author', '', $journal_id );
$reading_time = rwmb_meta( 'journal_reading_time', '', $journal_id );
$category     = bankofart_get_first_term_name( $journal_id, 'journal_category' );
$image        = bankofart_get_image( 'journal_main_image', $journal_id, 'medium' );
$date         = get_the_date( 'Y.m.d', $journal_id );

if ( 'top' === $context ) :
	/*
	 * TOP用：縦カード（ダーク背景）。news-card（index.html の NEWS縦カード）を流用。
	 * ダーク背景セクション内での使用前提。要約・著者・読了時間は表示しない（簡潔表示）。
	 */
	?>
	<a class="news-card news-card--journal" href="<?php echo esc_url( $permalink ); ?>">
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
<a class="news-item news-item--journal news-item--<?php echo esc_attr( $context ); ?>" href="<?php echo esc_url( $permalink ); ?>">
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
				<span class="news-cat"><?php echo esc_html( $category ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $reading_time ) ) : ?>
				<span class="journal-reading"><?php echo esc_html( sprintf( '%s分で読めます', $reading_time ) ); ?></span>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $title ) ) : ?>
			<h2 class="news-title"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>

		<?php if ( ! empty( $summary ) ) : ?>
			<p class="news-source"><?php echo esc_html( $summary ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $author ) ) : ?>
			<p class="journal-author"><?php echo esc_html( $author ); ?></p>
		<?php endif; ?>
	</div>

	<span class="news-arrow" aria-hidden="true"></span>
</a>
