<?php
/**
 * カードコンポーネント：アーティスト
 *
 * mockups/artist.html の .artist-card を正としてDOM・クラスを一致させる。
 * 各要素は !empty() チェックで自動非表示（運用性の3原則・原則2）。
 *
 * 引数（$args 経由）:
 *   - artist_id   int    投稿ID（省略時 get_the_ID()）
 *   - context     string 'archive' | 'related' | 'top' | 'matching-result'（既定 'archive'）
 *   - show_status bool   写真上のタグバッジ（診断タグ）を表示するか（既定 true）
 *                        ※mockupのバッジは artist_diagnosis_tag を表示する
 *   - show_theme  bool   制作テーマを表示するか（既定 true）
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$artist_id   = isset( $args['artist_id'] ) ? (int) $args['artist_id'] : get_the_ID();
$context     = isset( $args['context'] ) ? $args['context'] : 'archive';
$show_status = isset( $args['show_status'] ) ? (bool) $args['show_status'] : true;
$show_theme  = isset( $args['show_theme'] ) ? (bool) $args['show_theme'] : true;

if ( ! $artist_id ) {
	return;
}

$permalink = get_permalink( $artist_id );
$title     = get_the_title( $artist_id ); // post_title が正式名称.
$name_en   = rwmb_meta( 'artist_name_en', '', $artist_id );
$catch     = rwmb_meta( 'artist_catch_phrase', '', $artist_id );
$theme     = rwmb_meta( 'artist_theme_short', '', $artist_id );
$image     = bankofart_get_image( 'artist_main_photo', $artist_id, 'large' );

// 診断タグ（先頭2件を " / " で連結）。mockupの artist-tag-badge に表示。
$tag_terms = get_the_terms( $artist_id, 'artist_diagnosis_tag' );
$tag_label = '';
if ( ! is_wp_error( $tag_terms ) && ! empty( $tag_terms ) ) {
	$names     = wp_list_pluck( array_slice( $tag_terms, 0, 2 ), 'name' );
	$tag_label = implode( ' / ', $names );
}

// 一覧フィルター用：ステータス・ジャンルのタームIDを data 属性に持たせる
// （ジャンルは複数前提でスペース区切り。スラッグは日本語=URLエンコードのためIDを使用）。
$status_terms = get_the_terms( $artist_id, 'artist_status' );
$genre_terms  = get_the_terms( $artist_id, 'artist_genre' );
$data_status  = ( ! is_wp_error( $status_terms ) && $status_terms ) ? implode( ' ', wp_list_pluck( $status_terms, 'term_id' ) ) : '';
$data_genre   = ( ! is_wp_error( $genre_terms ) && $genre_terms ) ? implode( ' ', wp_list_pluck( $genre_terms, 'term_id' ) ) : '';
?>
<a class="artist-card artist-card--<?php echo esc_attr( $context ); ?>" href="<?php echo esc_url( $permalink ); ?>" data-status="<?php echo esc_attr( $data_status ); ?>" data-genre="<?php echo esc_attr( $data_genre ); ?>">
	<div class="artist-photo">
		<span class="artist-photo-inner"<?php if ( ! empty( $image['url'] ) ) : ?> style="background-image:url('<?php echo esc_url( $image['url'] ); ?>');" role="img" aria-label="<?php echo esc_attr( $image['alt'] ? $image['alt'] : $title ); ?>"<?php endif; ?>></span>

		<?php if ( $show_status && ! empty( $tag_label ) ) : ?>
			<span class="artist-tag-badge"><?php echo esc_html( $tag_label ); ?></span>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $title ) ) : ?>
		<h3 class="artist-name"><?php echo esc_html( $title ); ?></h3>
	<?php endif; ?>

	<?php if ( ! empty( $name_en ) ) : ?>
		<p class="artist-name-en"><?php echo esc_html( $name_en ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $catch ) ) : ?>
		<p class="artist-catch"><?php echo esc_html( $catch ); ?></p>
	<?php endif; ?>

	<?php if ( $show_theme && ! empty( $theme ) ) : ?>
		<p class="artist-theme"><?php echo esc_html( $theme ); ?></p>
	<?php endif; ?>
</a>
