<?php
/**
 * 最終フォールバックテンプレート。
 *
 * WordPress がクラシックテーマを有効化するために必須のファイル。
 * 各専用テンプレート（front-page.php / archive-*.php / single-*.php 等）は
 * 後続フェーズで作成する。それまでの暫定表示として最小限のループを置く。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main" class="site-main">
	<div class="container section">
		<?php
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				?>
				<article <?php post_class(); ?>>
					<h1><?php the_title(); ?></h1>
					<div class="entry-content">
						<?php the_content(); ?>
					</div>
				</article>
				<?php
			}
		} else {
			?>
			<p><?php esc_html_e( 'コンテンツが見つかりませんでした。', 'bankofart' ); ?></p>
			<?php
		}
		?>
	</div>
</main>

<?php
get_footer();
