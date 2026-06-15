<?php
/**
 * テーマフッター
 *
 * 共通フッターパーツを読み込み、wp_footer() を出力してドキュメントを閉じる。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php get_template_part( 'template-parts/footer', 'main' ); ?>

<?php wp_footer(); ?>
</body>
</html>
