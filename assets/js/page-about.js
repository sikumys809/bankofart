/*
 * page-about.js
 * ABOUT 固定ページのインタラクション。mockups/about__17_.html の <script>（2420-2551 行）から
 * about 固有の挙動のみ移植。共通chrome（preloader / cursor / header / drawer / contact）は
 * 既存の header.js が担うため除外。
 *   1) reveal（.rv）。JS有効時のみ body.reveal-ready を付与（無効時は常に表示）
 *   2) 数字カウントアップ（2秒 easeOutCubic, カンマ区切り）
 *   3) RESALE SERVICE の斜め破線（高比率バー右上 ⇄ 標準比率バー左上）
 * 素の JS（jQuery不使用）。計算式・数値はモックから一切改変なし。
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {

		/* ---- 1) reveal（.rv） ---- */
		if ( 'IntersectionObserver' in window ) {
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
		}

		/* ---- 2) 数字カウントアップ（2秒 easeOutCubic, カンマ区切り） ---- */
		( function () {
			var easeOutCubic = function ( t ) { return 1 - Math.pow( 1 - t, 3 ); };
			var formatNum = function ( n ) { return Math.floor( n ).toLocaleString( 'en-US' ); };
			var animate = function ( el ) {
				if ( '1' === el.dataset.done ) { return; }
				el.dataset.done = '1';
				var target = parseInt( el.dataset.target, 10 ) || 0;
				var duration = 2000;
				var start = performance.now();
				var tick = function ( now ) {
					var p = Math.min( ( now - start ) / duration, 1 );
					var v = target * easeOutCubic( p );
					el.textContent = formatNum( v );
					if ( p < 1 ) {
						requestAnimationFrame( tick );
					} else {
						el.textContent = formatNum( target );
					}
				};
				requestAnimationFrame( tick );
			};
			if ( ! ( 'IntersectionObserver' in window ) ) {
				document.querySelectorAll( '.count-up' ).forEach( animate );
				return;
			}
			var countObs = new IntersectionObserver(
				function ( entries ) {
					entries.forEach( function ( e ) {
						if ( e.isIntersecting ) { animate( e.target ); }
					} );
				},
				{ threshold: 0.4 }
			);
			document.querySelectorAll( '.count-up' ).forEach( function ( el ) {
				countObs.observe( el );
			} );
		} )();

		/* ---- 3) RESALE SERVICE 破線：高比率バーの右上角 ⇄ 標準比率バーの左上角 を結ぶ ---- */
		( function () {
			function drawResaleDiff() {
				var bars = document.querySelector( '.resale-bars' );
				var high = document.querySelector( '.resale-bar.bar-high' );
				var low  = document.querySelector( '.resale-bar.bar-low' );
				var line = document.getElementById( 'resaleDiffLine' );
				if ( ! bars || ! high || ! low || ! line ) { return; }
				var b = bars.getBoundingClientRect();
				var h = high.getBoundingClientRect();
				var l = low.getBoundingClientRect();
				// 高比率バーの右上角
				var x1 = h.right - b.left;
				var y1 = h.top - b.top;
				// 標準比率バーの左上角
				var x2 = l.left - b.left;
				var y2 = l.top - b.top;
				line.setAttribute( 'x1', x1 );
				line.setAttribute( 'y1', y1 );
				line.setAttribute( 'x2', x2 );
				line.setAttribute( 'y2', y2 );
			}
			window.addEventListener( 'load', drawResaleDiff );
			window.addEventListener( 'resize', drawResaleDiff );
			setTimeout( drawResaleDiff, 300 );
		} )();
	} );
} )();
