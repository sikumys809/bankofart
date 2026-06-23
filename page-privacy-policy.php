<?php
/**
 * プライバシーポリシー固定ページ（slug: privacy-policy）。
 *
 * WordPress の page-{slug}.php 規約により、スラッグ privacy-policy の固定ページに
 * 自動適用される。本文は WP 管理画面（投稿本文）で編集可能。
 * 内容は Notion の正本（株式会社シクミーズ プライバシーポリシー）を移植している。
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>

<main id="main" class="legal-page">

	<nav class="breadcrumb" aria-label="breadcrumb">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">HOME</a>
		<span class="sep">/</span>
		<span>Privacy Policy</span>
	</nav>

	<header class="legal-hero">
		<h1 class="legal-hero-en">PRIVACY POLICY</h1>
		<p class="legal-hero-ja">プライバシーポリシー</p>
		<div class="legal-hero-rule"></div>
	</header>

	<article class="legal-body">
		<?php the_content(); ?>
	</article>

	<div class="legal-back">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">HOMEへ戻る</a>
	</div>

</main>

	<?php
endwhile;

get_footer();
