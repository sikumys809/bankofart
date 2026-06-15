<?php
/**
 * テーマヘッダー
 *
 * ドキュメントの <head> を出力し、<body> を開いて共通ヘッダーパーツを読み込む。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link visually-hidden" href="#main"><?php esc_html_e( 'コンテンツへスキップ', 'bankofart' ); ?></a>

<?php get_template_part( 'template-parts/header', 'main' ); ?>
