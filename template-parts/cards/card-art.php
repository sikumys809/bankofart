<?php
/**
 * カードコンポーネント：作品（ART）
 *
 * mockups/art.html の .art-card を正としてDOM・クラスを一致させる。
 * 各要素は !empty() チェックで自動非表示。
 *
 * 引数（$args 経由）:
 *   - art_id       int    投稿ID（省略時 get_the_ID()）
 *   - context      string 'archive' | 'related' | 'top' | 'matching-result'（既定 'archive'）
 *   - show_artist  bool   制作アーティスト名を表示するか（既定 true）
 *   - show_specs   bool   号数・技法・制作年を表示するか（既定 true）
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$art_id      = isset( $args['art_id'] ) ? (int) $args['art_id'] : get_the_ID();
$context     = isset( $args['context'] ) ? $args['context'] : 'archive';
$show_artist = isset( $args['show_artist'] ) ? (bool) $args['show_artist'] : true;
$show_specs  = isset( $args['show_specs'] ) ? (bool) $args['show_specs'] : true;

if ( ! $art_id ) {
	return;
}

$permalink  = get_permalink( $art_id );
$title      = get_the_title( $art_id );
$number     = rwmb_meta( 'art_number', '', $art_id );
$year       = rwmb_meta( 'art_year', '', $art_id );
$medium     = rwmb_meta( 'art_medium', '', $art_id );
$size_label = rwmb_meta( 'art_size_label', '', $art_id );
$image      = bankofart_get_image( 'art_main_image', $art_id, 'large' );

// ステータス（AVAILABLE / OWNED）。
$status    = bankofart_get_first_term_name( $art_id, 'art_status' );
$status_uc = strtoupper( (string) $status );
$is_owned  = ( 'OWNED' === $status_uc );

// メインカラー（フィルター用 data-color）。
$color_terms = get_the_terms( $art_id, 'art_main_color' );
$color_slug  = ( ! is_wp_error( $color_terms ) && ! empty( $color_terms ) ) ? $color_terms[0]->slug : '';

// 制作アーティスト名（artist_to_art リレーションの from 側）。
$artist_name = '';
if ( $show_artist ) {
	$artists = bankofart_get_connected( 'artist_to_art', 'to', $art_id );
	if ( ! empty( $artists ) ) {
		$artist_name = get_the_title( $artists[0]->ID );
	}
}

// 仕様（号数・技法・年）。mockup art-meta は span を並べ "/" 区切りはCSSで付与。
$specs = array();
if ( $show_specs ) {
	if ( ! empty( $size_label ) ) {
		$specs[] = array( 'text' => $size_label, 'num' => false );
	}
	if ( ! empty( $medium ) ) {
		$specs[] = array( 'text' => $medium, 'num' => false );
	}
	if ( ! empty( $year ) ) {
		$specs[] = array( 'text' => $year, 'num' => true );
	}
}
?>
<a class="art-card art-card--<?php echo esc_attr( $context ); ?>" href="<?php echo esc_url( $permalink ); ?>"<?php if ( '' !== $color_slug ) : ?> data-color="<?php echo esc_attr( $color_slug ); ?>"<?php endif; ?>>
	<div class="art-image">
		<?php if ( ! empty( $status ) ) : ?>
			<span class="art-status <?php echo $is_owned ? 'is-owned' : 'is-available'; ?>"><?php echo esc_html( $status_uc ); ?></span>
		<?php endif; ?>

		<?php if ( ! empty( $number ) ) : ?>
			<span class="art-number"><?php echo esc_html( $number ); ?></span>
		<?php endif; ?>

		<span class="art-image-inner"<?php if ( ! empty( $image['url'] ) ) : ?> style="background-image:url('<?php echo esc_url( $image['url'] ); ?>');" role="img" aria-label="<?php echo esc_attr( $image['alt'] ? $image['alt'] : $title ); ?>"<?php endif; ?>></span>
	</div>

	<div class="art-info">
		<?php if ( ! empty( $artist_name ) ) : ?>
			<div class="art-artist"><?php echo esc_html( $artist_name ); ?></div>
		<?php endif; ?>

		<?php if ( ! empty( $title ) ) : ?>
			<h3 class="art-title"><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<?php if ( ! empty( $specs ) ) : ?>
			<div class="art-meta">
				<?php foreach ( $specs as $spec ) : ?>
					<span<?php echo $spec['num'] ? ' class="boa-num"' : ''; ?>><?php echo esc_html( $spec['text'] ); ?></span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</a>
