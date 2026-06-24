<?php
/**
 * カードコンポーネント：画家応援企業（COLLECTOR）
 *
 * mockups/collector.html の .collector-card を正としてDOM・クラスを一致させる。
 * 各要素は !empty() チェックで自動非表示。
 *
 * 引数（$args 経由）:
 *   - collector_id int    投稿ID（省略時 get_the_ID()）
 *   - context      string 'archive' | 'related' | 'top' | 'same-issue'（既定 'archive'）
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$collector_id = isset( $args['collector_id'] ) ? (int) $args['collector_id'] : get_the_ID();
$context      = isset( $args['context'] ) ? $args['context'] : 'archive';

if ( ! $collector_id ) {
	return;
}

$permalink = get_permalink( $collector_id );
$title     = get_the_title( $collector_id );
$effect    = rwmb_meta( 'collector_change_summary', '', $collector_id );
$image     = bankofart_get_image( 'collector_main_office_image', $collector_id, 'large' );
$industry  = bankofart_get_first_term_name( $collector_id, 'collector_industry' );
$issue     = bankofart_get_first_term_name( $collector_id, 'collector_issue' );
$placement = bankofart_get_first_term_name( $collector_id, 'collector_placement' );

// 導入時期（保存値は Y-m-d。年月で表示）。
$impl_raw   = rwmb_meta( 'collector_implementation_date', '', $collector_id );
$impl_label = '';
if ( ! empty( $impl_raw ) ) {
	$ts = strtotime( $impl_raw );
	if ( $ts ) {
		$impl_label = date_i18n( 'Y年n月', $ts );
	}
}

// メタ行（業界 / 課題 / 設置場所 / 導入時期）の空要素を除外。
$meta = array_filter( array( $industry, $issue, $placement, $impl_label ) );

// 一覧フィルター用：課題（collector_issue）・業種（collector_industry）のタームIDを
// data 属性に（複数前提・スペース区切り。業種は単一選択だが同形式で持たせる）。
$issue_terms    = get_the_terms( $collector_id, 'collector_issue' );
$industry_terms = get_the_terms( $collector_id, 'collector_industry' );
$data_issue     = ( ! is_wp_error( $issue_terms ) && $issue_terms ) ? implode( ' ', wp_list_pluck( $issue_terms, 'term_id' ) ) : '';
$data_industry  = ( ! is_wp_error( $industry_terms ) && $industry_terms ) ? implode( ' ', wp_list_pluck( $industry_terms, 'term_id' ) ) : '';
?>
<a class="collector-card collector-card--<?php echo esc_attr( $context ); ?>" href="<?php echo esc_url( $permalink ); ?>" data-issue="<?php echo esc_attr( $data_issue ); ?>" data-industry="<?php echo esc_attr( $data_industry ); ?>">
	<div class="collector-image">
		<?php if ( ! empty( $issue ) ) : ?>
			<span class="collector-issue-tag"><?php echo esc_html( $issue ); ?></span>
		<?php endif; ?>
		<span class="collector-image-inner"<?php if ( ! empty( $image['url'] ) ) : ?> style="background-image:url('<?php echo esc_url( $image['url'] ); ?>');" role="img" aria-label="<?php echo esc_attr( $image['alt'] ? $image['alt'] : $title ); ?>"<?php endif; ?>></span>
	</div>

	<?php if ( ! empty( $title ) ) : ?>
		<h3 class="collector-name"><?php echo esc_html( $title ); ?></h3>
	<?php endif; ?>

	<?php if ( ! empty( $effect ) ) : ?>
		<p class="collector-effect"><?php echo esc_html( $effect ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $meta ) ) : ?>
		<div class="collector-meta">
			<?php foreach ( $meta as $m ) : ?>
				<span><?php echo esc_html( $m ); ?></span>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<span class="collector-readmore font-deco">Read Story</span>
</a>
