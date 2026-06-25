/*
 * archive-paging.js
 * アーカイブ共通の表示制御モジュール（DRY）。各 archive-*-filter.js から呼ぶ。
 *   - setupLoadMore : artist 用「もっと見る」式（初期N件＋ボタンで増分）
 *   - setupPagination : art / collector 用「ページ番号式」（‹ 1 2 3 … N ›）
 *
 * いずれも「フィルタ（artはソート）後の matched 配列」を reset() に渡して使う。
 * PC/モバイルは matchMedia(760px) で件数を出し分け、リサイズ時に再計算する。
 * 素の JS（jQuery不使用）。
 *
 * @namespace window.BankofartArchive
 */
( function () {
	'use strict';

	var MQ = '(max-width: 760px)';

	function onMqChange( mq, fn ) {
		if ( mq.addEventListener ) {
			mq.addEventListener( 'change', fn );
		} else if ( mq.addListener ) {
			mq.addListener( fn ); // 旧Safari互換.
		}
	}

	/* ===== もっと見る（Load More）===== */
	function setupLoadMore( opts ) {
		var mq    = window.matchMedia( MQ );
		var btn   = opts.button || null;
		var wrap  = opts.wrap || ( btn ? btn.parentNode : null );
		var items = [];
		var shown = 0;

		function initial() { return mq.matches ? opts.initialMobile : opts.initialDesktop; }
		function increment() { return mq.matches ? opts.incrementMobile : opts.incrementDesktop; }

		function render() {
			items.forEach( function ( el, i ) {
				el.style.display = i < shown ? '' : 'none';
			} );
			var hasMore = items.length > shown;
			if ( wrap ) {
				wrap.classList.toggle( 'is-active', hasMore );
			} else if ( btn ) {
				btn.style.display = hasMore ? '' : 'none';
			}
		}

		// フィルタ後に呼ぶ：表示件数を初期値に戻して再描画。
		function reset( newItems ) {
			if ( newItems ) { items = newItems; }
			shown = initial();
			render();
		}

		if ( btn ) {
			btn.addEventListener( 'click', function () {
				shown += increment();
				render();
			} );
		}
		onMqChange( mq, function () { reset(); } );

		return { reset: reset };
	}

	/* ===== ページ番号式（Pagination）===== */
	// 総ページ7超は「先頭・末尾・現在±1」＋…省略。
	function pageList( cur, total ) {
		var out = [], i;
		if ( total <= 7 ) {
			for ( i = 1; i <= total; i++ ) { out.push( i ); }
			return out;
		}
		out.push( 1 );
		var left  = Math.max( 2, cur - 1 );
		var right = Math.min( total - 1, cur + 1 );
		if ( left > 2 ) { out.push( '…' ); }
		for ( i = left; i <= right; i++ ) { out.push( i ); }
		if ( right < total - 1 ) { out.push( '…' ); }
		out.push( total );
		return out;
	}

	function setupPagination( opts ) {
		var mq    = window.matchMedia( MQ );
		var pager = opts.pagerEl || null;
		var items = [];
		var page  = 1;

		function perPage() { return mq.matches ? opts.perPageMobile : opts.perPageDesktop; }
		function totalPages() { return Math.max( 1, Math.ceil( items.length / perPage() ) ); }

		function showSlice() {
			var pp    = perPage();
			var start = ( page - 1 ) * pp;
			var end   = start + pp;
			items.forEach( function ( el, i ) {
				el.style.display = ( i >= start && i < end ) ? '' : 'none';
			} );
		}

		function go( p, doScroll ) {
			page = Math.min( Math.max( 1, p ), totalPages() );
			showSlice();
			buildPager();
			if ( doScroll && opts.scrollTarget ) {
				var off = opts.scrollOffset || 0;
				var y   = opts.scrollTarget.getBoundingClientRect().top + window.pageYOffset - off;
				window.scrollTo( { top: y, behavior: 'smooth' } );
			}
		}

		function makeArrow( dir, disabled ) {
			var b = document.createElement( 'button' );
			b.type = 'button';
			b.className = 'pager-arrow' + ( disabled ? ' is-disabled' : '' );
			b.setAttribute( 'aria-label', dir === 'prev' ? '前のページ' : '次のページ' );
			b.textContent = dir === 'prev' ? '‹' : '›';
			if ( disabled ) {
				b.disabled = true;
			} else {
				b.addEventListener( 'click', function () { go( dir === 'prev' ? page - 1 : page + 1, true ); } );
			}
			return b;
		}

		function buildPager() {
			if ( ! pager ) { return; }
			pager.innerHTML = '';
			var tp = totalPages();
			// 0件 or 1ページのみ → ページャー非表示。
			if ( items.length === 0 || tp <= 1 ) {
				pager.style.display = 'none';
				return;
			}
			pager.style.display = '';
			pager.appendChild( makeArrow( 'prev', page <= 1 ) );
			pageList( page, tp ).forEach( function ( p ) {
				if ( p === '…' ) {
					var s = document.createElement( 'span' );
					s.className = 'pager-ellipsis';
					s.textContent = '…';
					pager.appendChild( s );
					return;
				}
				var b = document.createElement( 'button' );
				b.type = 'button';
				b.className = 'pager-num' + ( p === page ? ' is-current' : '' );
				b.textContent = String( p );
				if ( p === page ) {
					b.setAttribute( 'aria-current', 'page' );
				} else {
					b.addEventListener( 'click', function () { go( p, true ); } );
				}
				pager.appendChild( b );
			} );
			pager.appendChild( makeArrow( 'next', page >= tp ) );
		}

		// フィルタ/ソート後に呼ぶ：1ページ目に戻して再描画。
		function reset( newItems ) {
			if ( newItems ) { items = newItems; }
			page = 1;
			showSlice();
			buildPager();
		}

		// リサイズで PC/モバイルをまたいだら perPage が変わるのでクランプして再計算。
		onMqChange( mq, function () {
			page = Math.min( page, totalPages() );
			showSlice();
			buildPager();
		} );

		return { reset: reset };
	}

	window.BankofartArchive = {
		setupLoadMore: setupLoadMore,
		setupPagination: setupPagination,
	};
} )();
