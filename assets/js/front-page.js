/*
 * front-page.js
 * TOP（front-page）のインタラクション。mockups/index.html の <script> から
 * front-page 固有の挙動を移植（preloader/cursor/header/contact/drawer は除く＝共通chrome）。
 *   1) HERO blur on scroll（--hero-blur）
 *   2) reveal（.rv）＋ text-reveal（[data-tr]）。JS有効時のみ body.reveal-ready を付与
 *   3) GALLERY カルーセル
 *   4) HOW スクロールライン
 * 素の JS（jQuery不使用）。
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {

		/* ---- 1) HERO blur on scroll ---- */
		var heroBlur = document.querySelector( '.hero-layer-logo .hero-blur-target' );
		if ( heroBlur ) {
			var onScroll = function () {
				var blur = Math.min( 16, window.scrollY / 50 );
				heroBlur.style.setProperty( '--hero-blur', blur + 'px' );
			};
			window.addEventListener( 'scroll', onScroll, { passive: true } );
			onScroll();
		}

		/* ---- 2) reveal + text-reveal ---- */
		if ( 'IntersectionObserver' in window ) {
			document.body.classList.add( 'reveal-ready' );
			var revObs = new IntersectionObserver(
				function ( entries ) {
					entries.forEach( function ( e ) {
						e.target.classList.toggle( 'on', e.isIntersecting );
					} );
				},
				{ threshold: 0.1, rootMargin: '0px 0px -50px 0px' }
			);
			document.querySelectorAll( '.rv' ).forEach( function ( el ) {
				revObs.observe( el );
			} );

			var trObs = new IntersectionObserver(
				function ( entries ) {
					entries.forEach( function ( e ) {
						e.target.classList.toggle( 'on', e.isIntersecting );
					} );
				},
				{ threshold: 0.15, rootMargin: '0px 0px -40px 0px' }
			);
			document.querySelectorAll( '[data-tr]' ).forEach( function ( el ) {
				trObs.observe( el );
			} );
		}

		/* ---- 3) GALLERY カルーセル ---- */
		( function () {
			var track     = document.getElementById( 'carTrack' );
			var wrap      = track ? track.parentElement : null;
			var prev      = document.getElementById( 'carPrev' );
			var next      = document.getElementById( 'carNext' );
			var indicator = document.getElementById( 'carIndicator' );
			if ( ! track || ! wrap || ! indicator ) {
				return;
			}
			var items = Array.prototype.slice.call( track.children );
			var N     = items.length;
			var idx   = 0;

			var visibleCount = function () {
				var w = wrap.clientWidth;
				if ( w < 700 ) { return 1; }
				if ( w < 1200 ) { return 2; }
				return 3;
			};
			var maxIdx = function () {
				return Math.max( 0, N - visibleCount() );
			};
			var updateDots = function () {
				Array.prototype.slice.call( indicator.children ).forEach( function ( d, i ) {
					d.classList.toggle( 'active', i === idx );
				} );
			};
			var update = function ( animate ) {
				if ( idx > maxIdx() ) { idx = 0; }
				if ( idx < 0 ) { idx = maxIdx(); }
				track.style.transition = ( false === animate ) ? 'none' : 'transform .6s cubic-bezier(.4,0,.2,1)';
				var style = window.getComputedStyle( track );
				var gap   = parseFloat( style.columnGap || style.gap || 0 ) || 0;
				var step  = items[0].getBoundingClientRect().width + gap;
				track.style.transform = 'translateX(' + ( -idx * step ) + 'px)';
				updateDots();
			};
			var buildDots = function () {
				indicator.innerHTML = '';
				var pages = maxIdx() + 1;
				for ( var i = 0; i < pages; i++ ) {
					var d = document.createElement( 'button' );
					d.className = 'car-dot' + ( i === idx ? ' active' : '' );
					d.setAttribute( 'aria-label', 'Slide ' + ( i + 1 ) );
					( function ( n ) {
						d.addEventListener( 'click', function () {
							idx = n;
							update();
						} );
					} )( i );
					indicator.appendChild( d );
				}
			};

			if ( prev ) {
				prev.addEventListener( 'click', function () {
					idx = ( 0 === idx ) ? maxIdx() : idx - 1;
					update();
				} );
			}
			if ( next ) {
				next.addEventListener( 'click', function () {
					idx = ( idx >= maxIdx() ) ? 0 : idx + 1;
					update();
				} );
			}
			window.addEventListener( 'resize', function () {
				buildDots();
				update( false );
			} );

			var timer = setInterval( function () {
				idx = ( idx >= maxIdx() ) ? 0 : idx + 1;
				update();
			}, 5500 );
			if ( wrap.parentElement ) {
				wrap.parentElement.addEventListener( 'mouseenter', function () {
					clearInterval( timer );
				} );
			}

			buildDots();
			update( false );
		} )();

		/* ---- 5) ART コラージュ：data-order 順に時間差で起き上がる（双方向） ---- */
		( function () {
			var collage = document.getElementById( 'artCollage' );
			if ( ! collage || ! ( 'IntersectionObserver' in window ) ) {
				return;
			}
			var frames = collage.querySelectorAll( '.frame' );
			var timers = new Map();
			var obs    = new IntersectionObserver(
				function ( entries ) {
					entries.forEach( function ( e ) {
						if ( e.isIntersecting ) {
							frames.forEach( function ( f ) {
								var o = parseInt( f.dataset.order || 0, 10 );
								if ( timers.has( f ) ) { clearTimeout( timers.get( f ) ); }
								timers.set( f, setTimeout( function () {
									f.classList.add( 'flipped' );
								}, o * 180 ) );
							} );
						} else {
							frames.forEach( function ( f ) {
								if ( timers.has( f ) ) { clearTimeout( timers.get( f ) ); timers.delete( f ); }
								f.classList.remove( 'flipped' );
							} );
						}
					} );
				},
				{ threshold: 0.15 }
			);
			obs.observe( collage );
		} )();

		/* ---- 4) HOW スクロールライン ---- */
		( function () {
			var stack = document.getElementById( 'howStack' );
			var fill  = document.getElementById( 'howLineFill' );
			if ( ! stack || ! fill ) {
				return;
			}
			var update = function () {
				var rect  = stack.getBoundingClientRect();
				var vh    = window.innerHeight;
				var start = vh * 0.8;
				var end   = vh * 0.3;
				var total = rect.height + ( start - end );
				var passed   = start - rect.top;
				var progress = Math.max( 0, Math.min( 1, passed / total ) );
				fill.style.height = ( progress * 100 ) + '%';
			};
			update();
			window.addEventListener( 'scroll', update, { passive: true } );
			window.addEventListener( 'resize', update );
		} )();
	} );
} )();
