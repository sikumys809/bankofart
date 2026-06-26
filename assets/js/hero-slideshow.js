/*
 * hero-slideshow.js
 * トップ最上部ヒーローの画像スライドショー（クロスフェード）。
 *   - .hero-bg--slideshow 内の .hero-slide を data-hero-interval ごとに .is-active 付け替え。
 *   - フェードはCSS（opacity transition）。JSはクラス付け替えのみ。
 *   - 画像2枚以上のときだけ起動（1枚は静止）。
 *   - prefers-reduced-motion: reduce の場合は自動切替しない（先頭固定）。
 *   - 依存なし・バニラJS。
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var box = document.querySelector( '.hero-bg--slideshow' );
		if ( ! box ) { return; }

		var slides = box.querySelectorAll( '.hero-slide' );
		if ( slides.length < 2 ) { return; } // 1枚は静止表示。

		// アクセシビリティ：モーション低減設定なら自動切替しない。
		if ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			return;
		}

		var interval = parseInt( box.getAttribute( 'data-hero-interval' ), 10 );
		if ( ! interval || interval < 1000 ) { interval = 6000; }

		var current = 0;
		setInterval( function () {
			slides[ current ].classList.remove( 'is-active' );
			current = ( current + 1 ) % slides.length;
			slides[ current ].classList.add( 'is-active' );
		}, interval );
	} );
} )();
