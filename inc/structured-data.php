<?php
/**
 * 構造化データ（JSON-LD / schema.org）自動出力
 *
 * inc/meta-description.php と同じ思想で、各ページ表示時に wp_head へ JSON-LD を
 * 自動出力する。投稿に紐づくのではなくフック（ルール）なので、新規投稿でも
 * 該当フィールドを入力すれば表示時に自動反映される。
 *
 * 【出力方式】
 * 1ページ分を 1つの <script type="application/ld+json"> に @graph でまとめる。
 *   - 共通：Organization（全ページ）
 *   - artist single：Person ＋ BreadcrumbList
 *   - art single：VisualArtwork ＋ BreadcrumbList
 *   - journal / news single：Article ＋ BreadcrumbList
 *   - collector single：BreadcrumbList（個別 schema は付けない）
 *   - 対象CPTの archive：BreadcrumbList（2階層）
 *
 * 【共通ルール】
 *   - 文字列はタグ／ショートコード除去・エンティティ復号・改行/連続空白除去でプレーン化
 *   - 値が無いプロパティはキーごと省略（null/空文字を出さない）
 *   - 画像・URL は絶対URL、日付は ISO 8601（get_the_date('c')）
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 構造化データを出力するCPT（single / archive の対象）。
 *
 * @var string[]
 */
const BANKOFART_SD_POST_TYPES = array( 'artist', 'art', 'collector', 'journal', 'news' );

/**
 * 組織情報（Organization）の定数。
 */
const BANKOFART_SD_ORG_NAME     = 'バンクオブアート';
const BANKOFART_SD_ORG_ALT_NAME = 'BANK of ART';
const BANKOFART_SD_ORG_DESC     = '節税対策×画家支援。アート作品を資産として活用する法人・事業者向けサービス。';

/**
 * SNS（sameAs）。template-parts/footer-main.php のSNSリンクと同一。
 *
 * @var string[]
 */
const BANKOFART_SD_SAME_AS = array(
	'https://www.facebook.com/bankofart2022/',
	'https://www.youtube.com/@bankofart2022',
	'https://www.instagram.com/bankof_art2022/',
	'https://x.com/bankof_art',
);

/**
 * wp_head で JSON-LD（@graph）を出力する。
 *
 * @return void
 */
function bankofart_output_structured_data() {
	// フィード等では出力しない。
	if ( is_feed() || is_404() ) {
		return;
	}

	$graph = array();

	// Organization は全ページ共通。
	$graph[] = bankofart_sd_organization();

	if ( is_singular( BANKOFART_SD_POST_TYPES ) ) {
		$post_id = get_queried_object_id();
		$ptype   = get_post_type( $post_id );

		switch ( $ptype ) {
			case 'artist':
				$node = bankofart_sd_person( $post_id );
				break;
			case 'art':
				$node = bankofart_sd_visual_artwork( $post_id );
				break;
			case 'journal':
			case 'news':
				$node = bankofart_sd_article( $post_id, $ptype );
				break;
			default:
				$node = null; // collector は個別 schema なし。
		}
		if ( $node ) {
			$graph[] = $node;
		}

		$graph[] = bankofart_sd_breadcrumb_single( $post_id, $ptype );
	} elseif ( is_post_type_archive( BANKOFART_SD_POST_TYPES ) ) {
		$graph[] = bankofart_sd_breadcrumb_archive( get_query_var( 'post_type' ) );
	}

	$graph = array_filter( $graph );
	if ( empty( $graph ) ) {
		return;
	}

	$data = array(
		'@context' => 'https://schema.org',
		'@graph'   => array_values( $graph ),
	);

	$flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_PRETTY_PRINT;
	echo "\n<script type=\"application/ld+json\">\n" . wp_json_encode( $data, $flags ) . "\n</script>\n";
}
add_action( 'wp_head', 'bankofart_output_structured_data', 20 );

/* =========================================================
 * 各 schema ノードビルダー
 * ======================================================= */

/**
 * Organization ノード。
 *
 * @return array
 */
function bankofart_sd_organization() {
	return array(
		'@type'         => 'Organization',
		'@id'           => home_url( '/#organization' ),
		'name'          => BANKOFART_SD_ORG_NAME,
		'alternateName' => BANKOFART_SD_ORG_ALT_NAME,
		'url'           => home_url( '/' ),
		'logo'          => bankofart_sd_logo_url(),
		'description'   => BANKOFART_SD_ORG_DESC,
		'sameAs'        => array_values( BANKOFART_SD_SAME_AS ),
	);
}

/**
 * Person ノード（artist single）。
 *
 * @param int $post_id 投稿ID。
 * @return array
 */
function bankofart_sd_person( $post_id ) {
	$node = array(
		'@type'    => 'Person',
		'name'     => bankofart_sd_text( get_the_title( $post_id ) ),
		'jobTitle' => '画家',
	);

	$en = bankofart_sd_text( get_post_meta( $post_id, 'artist_name_en', true ) );
	if ( '' !== $en ) {
		$node['alternateName'] = $en;
	}

	$desc = bankofart_sd_text( get_post_meta( $post_id, 'artist_theme_long', true ) );
	if ( '' !== $desc ) {
		$node['description'] = $desc;
	}

	$image = bankofart_sd_image_url( 'artist_main_photo', $post_id );
	if ( '' !== $image ) {
		$node['image'] = $image;
	}

	$birthplace = bankofart_sd_text( get_post_meta( $post_id, 'artist_birthplace', true ) );
	if ( '' !== $birthplace ) {
		$node['birthPlace'] = $birthplace;
	}

	$sns_keys = array(
		'artist_sns_instagram',
		'artist_sns_x',
		'artist_sns_facebook',
		'artist_sns_youtube',
		'artist_sns_other',
	);
	$same_as = array();
	foreach ( $sns_keys as $key ) {
		$url = trim( (string) get_post_meta( $post_id, $key, true ) );
		if ( '' !== $url ) {
			$same_as[] = esc_url_raw( $url );
		}
	}
	$same_as = array_values( array_filter( array_unique( $same_as ) ) );
	if ( ! empty( $same_as ) ) {
		$node['sameAs'] = $same_as;
	}

	$node['memberOf'] = array(
		'@type' => 'Organization',
		'name'  => BANKOFART_SD_ORG_ALT_NAME,
	);

	return $node;
}

/**
 * VisualArtwork ノード（art single）。
 *
 * @param int $post_id 投稿ID。
 * @return array
 */
function bankofart_sd_visual_artwork( $post_id ) {
	$node = array(
		'@type' => 'VisualArtwork',
		'name'  => bankofart_sd_text( get_the_title( $post_id ) ),
	);

	$image = bankofart_sd_image_url( 'art_main_image', $post_id );
	if ( '' !== $image ) {
		$node['image'] = $image;
	}

	// 制作者（artist_to_art の逆引き：先頭1名）。取れなければ creator 省略。
	if ( function_exists( 'bankofart_get_connected' ) ) {
		$artists = bankofart_get_connected( 'artist_to_art', 'to', $post_id );
		if ( ! empty( $artists ) ) {
			$creator_name = bankofart_sd_text( get_the_title( $artists[0]->ID ) );
			if ( '' !== $creator_name ) {
				$node['creator'] = array(
					'@type' => 'Person',
					'name'  => $creator_name,
				);
			}
		}
	}

	// 技法（art_technique）→ artMedium / artform。
	$technique = bankofart_sd_terms( $post_id, 'art_technique' );
	if ( '' !== $technique ) {
		$node['artMedium'] = $technique;
		$node['artform']   = $technique;
	}

	// ジャンル（art_genre）→ genre。
	$genre = bankofart_sd_terms( $post_id, 'art_genre' );
	if ( '' !== $genre ) {
		$node['genre'] = $genre;
	}

	return $node;
}

/**
 * Article ノード（journal / news single）。
 *
 * @param int    $post_id 投稿ID。
 * @param string $ptype   投稿タイプ。
 * @return array
 */
function bankofart_sd_article( $post_id, $ptype ) {
	$node = array(
		'@type'    => 'Article',
		'headline' => bankofart_sd_text( get_the_title( $post_id ) ),
	);

	// description：要約フィールド優先、無ければ本文セクション冒頭（meta-description と同ソース）。
	$summary = trim( (string) get_post_meta( $post_id, $ptype . '_summary', true ) );
	if ( '' === $summary && function_exists( 'bankofart_first_section_body' ) ) {
		$summary = bankofart_first_section_body( $post_id, $ptype . '_sections' );
	}
	$summary = bankofart_sd_text( $summary );
	if ( '' !== $summary ) {
		$node['description'] = $summary;
	}

	// image：アイキャッチ優先、無ければ {ptype}_main_image。
	$image = get_the_post_thumbnail_url( $post_id, 'large' );
	if ( ! $image ) {
		$image = bankofart_sd_image_url( $ptype . '_main_image', $post_id );
	}
	if ( $image ) {
		$node['image'] = $image;
	}

	$node['datePublished'] = get_the_date( 'c', $post_id );
	$node['dateModified']  = get_the_modified_date( 'c', $post_id );

	// author：journal は著者名フィールドがあれば Person、無ければ Organization。
	$author_name = '';
	if ( 'journal' === $ptype ) {
		$author_name = bankofart_sd_text( get_post_meta( $post_id, 'journal_author', true ) );
	}
	$node['author'] = '' !== $author_name
		? array(
			'@type' => 'Person',
			'name'  => $author_name,
		)
		: array(
			'@type' => 'Organization',
			'name'  => BANKOFART_SD_ORG_ALT_NAME,
		);

	// publisher。
	$node['publisher'] = array(
		'@type' => 'Organization',
		'name'  => BANKOFART_SD_ORG_NAME,
		'logo'  => array(
			'@type' => 'ImageObject',
			'url'   => bankofart_sd_logo_url(),
		),
	);

	return $node;
}

/**
 * BreadcrumbList（single：トップ > CPTアーカイブ > 現在の投稿）。
 *
 * @param int    $post_id 投稿ID。
 * @param string $ptype   投稿タイプ。
 * @return array
 */
function bankofart_sd_breadcrumb_single( $post_id, $ptype ) {
	$items = array(
		bankofart_sd_crumb( 1, 'ホーム', home_url( '/' ) ),
	);

	$archive_link = get_post_type_archive_link( $ptype );
	if ( $archive_link ) {
		$items[] = bankofart_sd_crumb( count( $items ) + 1, bankofart_sd_post_type_label( $ptype ), $archive_link );
	}

	$items[] = bankofart_sd_crumb( count( $items ) + 1, bankofart_sd_text( get_the_title( $post_id ) ), get_permalink( $post_id ) );

	return array(
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	);
}

/**
 * BreadcrumbList（archive：トップ > CPTアーカイブ）。
 *
 * @param string|string[] $ptype 投稿タイプ。
 * @return array|null
 */
function bankofart_sd_breadcrumb_archive( $ptype ) {
	$ptype = is_array( $ptype ) ? reset( $ptype ) : $ptype;
	if ( ! $ptype ) {
		return null;
	}

	$items = array(
		bankofart_sd_crumb( 1, 'ホーム', home_url( '/' ) ),
	);

	$archive_link = get_post_type_archive_link( $ptype );
	if ( $archive_link ) {
		$items[] = bankofart_sd_crumb( 2, bankofart_sd_post_type_label( $ptype ), $archive_link );
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	);
}

/* =========================================================
 * ヘルパー
 * ======================================================= */

/**
 * BreadcrumbList の 1要素を生成する。
 *
 * @param int    $position 位置（1始まり）。
 * @param string $name     表示名。
 * @param string $item     URL。
 * @return array
 */
function bankofart_sd_crumb( $position, $name, $item ) {
	return array(
		'@type'    => 'ListItem',
		'position' => (int) $position,
		'name'     => $name,
		'item'     => $item,
	);
}

/**
 * CPTの表示名（パンくず用）を返す。
 *
 * @param string $ptype 投稿タイプ。
 * @return string
 */
function bankofart_sd_post_type_label( $ptype ) {
	$obj = get_post_type_object( $ptype );
	if ( $obj && isset( $obj->labels->name ) && '' !== $obj->labels->name ) {
		return $obj->labels->name;
	}
	return $ptype;
}

/**
 * テーマ同梱ロゴ（boa-17）の絶対URL。
 *
 * @return string
 */
function bankofart_sd_logo_url() {
	return get_theme_file_uri( 'assets/img/logo/boa-17.png' );
}

/**
 * single_image フィールドの絶対URL（large）を返す。無ければ空文字。
 *
 * @param string $field_id フィールドID。
 * @param int    $post_id  投稿ID。
 * @return string
 */
function bankofart_sd_image_url( $field_id, $post_id ) {
	if ( function_exists( 'bankofart_get_image' ) ) {
		$img = bankofart_get_image( $field_id, $post_id, 'large' );
		if ( ! empty( $img['url'] ) ) {
			return $img['url'];
		}
		return '';
	}

	// フォールバック：添付IDから直接URLを引く。
	$att_id = (int) get_post_meta( $post_id, $field_id, true );
	if ( $att_id > 0 ) {
		$url = wp_get_attachment_image_url( $att_id, 'large' );
		return $url ? $url : '';
	}
	return '';
}

/**
 * タクソノミーのターム名を「, 」連結で返す（schema 値用）。無ければ空文字。
 *
 * @param int    $post_id  投稿ID。
 * @param string $taxonomy タクソノミー。
 * @return string
 */
function bankofart_sd_terms( $post_id, $taxonomy ) {
	$terms = get_the_terms( $post_id, $taxonomy );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return '';
	}
	$names = array();
	foreach ( $terms as $term ) {
		$names[] = $term->name;
	}
	$names = array_values( array_filter( array_unique( $names ), 'strlen' ) );
	return implode( ', ', $names );
}

/**
 * 文字列をプレーンテキスト化する（タグ・ショートコード除去、エンティティ復号、空白畳み）。
 * 長さの切り詰めは行わない（JSON-LD のため）。
 *
 * @param mixed $text 元テキスト。
 * @return string
 */
function bankofart_sd_text( $text ) {
	$text = (string) $text;
	if ( '' === $text ) {
		return '';
	}
	$text = strip_shortcodes( $text );
	$text = wp_strip_all_tags( $text );
	$text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
	$text = preg_replace( '/\s+/u', ' ', $text );
	return trim( $text );
}
