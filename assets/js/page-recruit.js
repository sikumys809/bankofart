/*
 * page-recruit.js
 * RECRUIT（JOIN US）固定ページのインタラクション。
 * mockups/recruit__1_.html の <script> から recruit 固有の挙動のみ移植。
 * 共通chrome（preloader / cursor / header / drawer / contact）は header.js が担うため除外。
 * 注意事項アコーディオンは <details>/<summary> のネイティブ機能のため JS 不要。
 *   - reveal（.rv）。JS有効時のみ body.reveal-ready を付与（無効時は常に表示）
 * 素の JS（jQuery不使用）。
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		if ( ! ( 'IntersectionObserver' in window ) ) {
			return;
		}
		document.body.classList.add( 'reveal-ready' );
		var obs = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( e ) {
					e.target.classList.toggle( 'on', e.isIntersecting );
				} );
			},
			{ threshold: 0.1, rootMargin: '0px 0px -50px 0px' }
		);
		document.querySelectorAll( '.rv' ).forEach( function ( el ) {
			obs.observe( el );
		} );
	} );
} )();
