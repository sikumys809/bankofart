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
 *   - filter_data  bool   一覧フィルター用 data 属性（7軸 + ソートキー）を出力するか（既定 false）
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
$filter_data = isset( $args['filter_data'] ) ? (bool) $args['filter_data'] : false;

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

// 制作アーティスト（artist_to_art リレーションの to 側＝この作品に紐づくアーティスト）。
$artists     = array();
$artist_name = '';
if ( $show_artist || $filter_data ) {
	$artists = bankofart_get_connected( 'artist_to_art', 'to', $art_id );
	if ( ! empty( $artists ) ) {
		$artist_name = get_the_title( $artists[0]->ID );
	}
}

// 一覧フィルター用の data 属性（7軸：status / artist / form / genre / technique / size / color）
// ＋ ソートキー（date / artist / size）。axis 名 = data 属性のサフィックスで JS と対応。
$filter_attr = '';
if ( $filter_data ) {
	$tax_slugs = static function ( $tax ) use ( $art_id ) {
		$terms = get_the_terms( $art_id, $tax );
		return ( ! is_wp_error( $terms ) && $terms ) ? implode( ' ', wp_list_pluck( $terms, 'slug' ) ) : '';
	};
	$artist_slugs = ! empty( $artists ) ? implode( ' ', wp_list_pluck( $artists, 'post_name' ) ) : '';

	// ソート用：号数（size_label から数値抽出）/ 公開日 / アーティスト名。
	$sort_size = 0;
	if ( ! empty( $size_label ) && preg_match( '/\d+/', $size_label, $mm ) ) {
		$sort_size = (int) $mm[0];
	}

	$data = array(
		'data-status'      => $tax_slugs( 'art_status' ),
		'data-artist'      => $artist_slugs,
		'data-form'        => $tax_slugs( 'art_form' ),
		'data-genre'       => $tax_slugs( 'art_genre' ),
		'data-technique'   => $tax_slugs( 'art_technique' ),
		'data-size'        => $tax_slugs( 'art_size' ),
		'data-sort-date'   => (string) get_the_date( 'U', $art_id ),
		'data-sort-artist' => $artist_name,
		'data-sort-size'   => (string) $sort_size,
	);
	foreach ( $data as $k => $v ) {
		$filter_attr .= ' ' . $k . '="' . esc_attr( $v ) . '"';
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
<a class="art-card art-card--<?php echo esc_attr( $context ); ?>" href="<?php echo esc_url( $permalink ); ?>" data-color="<?php echo esc_attr( $color_slug ); ?>"<?php echo $filter_attr; // phpcs:ignore WordPress.Security.EscapeOutput -- 各値は上で esc_attr 済み ?>>
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
