/*
 * archive-art-filter.js
 * ART アーカイブの 7軸 AND 絞り込み ＋ ソート ＋ ページ番号式表示。
 *   - 軸：status / artist / form / genre / technique / size / color
 *   - .filter-tag / .color-swatch（data-axis + data-filter）で軸ごとの選択状態を保持
 *   - .art-card[data-{axis}]（スペース区切りの slug）を全軸 AND 判定
 *   - .sort-tabs（data-sort: newest / artist / size）で DOM を並べ替え
 *   - 処理順：①フィルタで matched 算出 ②sort で DOM 並べ替え ③matched をページ表示
 *   - 表示件数（.filter-visible）は絞り込み後の総数
 *   - 表示制御は共通モジュール window.BankofartArchive.setupPagination に委譲
 *   - 素の JS（jQuery不使用）
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var grid = document.getElementById( 'artGrid' );
		if ( ! grid || ! window.BankofartArchive ) {
			return;
		}
		var cards   = Array.prototype.slice.call( grid.querySelectorAll( '.art-card' ) );
		var buttons = document.querySelectorAll( '[data-axis]' );
		var visEl   = document.querySelector( '.filter-visible' );

		var AXES = [ 'status', 'artist', 'form', 'genre', 'technique', 'size', 'color' ];
		var state = {};
		AXES.forEach( function ( a ) { state[ a ] = 'all'; } );

		// 元の並び順（NEWEST = サーバの投稿日降順）を保持。
		cards.forEach( function ( c, i ) { c.dataset.origIndex = String( i ); } );

		var idList = function ( card, axis ) {
			return ( card.getAttribute( 'data-' + axis ) || '' ).split( /\s+/ ).filter( Boolean );
		};
		var isMatch = function ( card ) {
			return AXES.every( function ( axis ) {
				return 'all' === state[ axis ] || idList( card, axis ).indexOf( state[ axis ] ) !== -1;
			} );
		};

		var pager = window.BankofartArchive.setupPagination( {
			pagerEl:        document.getElementById( 'artPager' ),
			perPageDesktop: 12,
			perPageMobile:  6,
			scrollTarget:   document.querySelector( '.art-section' ),
			scrollOffset:   100,
		} );

		// 現在の DOM 並び順（ソート反映後）で matched を取得。
		var currentMatched = function () {
			return Array.prototype.slice.call( grid.children ).filter( function ( el ) {
				return el.classList && el.classList.contains( 'art-card' ) && isMatch( el );
			} );
		};

		// フィルタ／ソート後に呼ぶ：非マッチを隠し、matched をページ表示。
		var applyAll = function () {
			var matched = currentMatched();
			var matchedSet = matched;
			cards.forEach( function ( card ) {
				if ( matchedSet.indexOf( card ) === -1 ) {
					card.style.display = 'none';
				}
			} );
			if ( visEl ) {
				visEl.textContent = String( matched.length );
			}
			pager.reset( matched );
		};

		// ---- フィルターボタン ----
		buttons.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var axis = btn.getAttribute( 'data-axis' );
				document.querySelectorAll( '[data-axis="' + axis + '"]' ).forEach( function ( x ) {
					x.classList.remove( 'is-active' );
				} );
				btn.classList.add( 'is-active' );
				state[ axis ] = btn.getAttribute( 'data-filter' ) || 'all';
				applyAll();
			} );
		} );

		// ---- ソート（DOM 並べ替え後に再ページング）----
		var sortTabs = document.querySelectorAll( '.sort-tabs [data-sort]' );
		var num = function ( card, key ) { return parseFloat( card.getAttribute( key ) ) || 0; };
		var sortBy = function ( mode ) {
			var sorted = cards.slice();
			if ( 'artist' === mode ) {
				sorted.sort( function ( a, b ) {
					return ( a.getAttribute( 'data-sort-artist' ) || '' ).localeCompare( b.getAttribute( 'data-sort-artist' ) || '', 'ja' );
				} );
			} else if ( 'size' === mode ) {
				sorted.sort( function ( a, b ) {
					return num( b, 'data-sort-size' ) - num( a, 'data-sort-size' );
				} );
			} else {
				sorted.sort( function ( a, b ) {
					return num( a, 'data-orig-index' ) - num( b, 'data-orig-index' );
				} );
			}
			sorted.forEach( function ( card ) { grid.appendChild( card ); } );
		};

		sortTabs.forEach( function ( tab ) {
			tab.addEventListener( 'click', function () {
				sortTabs.forEach( function ( x ) { x.classList.remove( 'is-active' ); } );
				tab.classList.add( 'is-active' );
				sortBy( tab.getAttribute( 'data-sort' ) );
				applyAll();
			} );
		} );

		// ---- カラー効果パネル（スウォッチ選択時に内容差し込み・表示）----
		var panel    = document.getElementById( 'colorPanel' );
		var swatches = document.querySelectorAll( '.color-swatch' );
		if ( panel && swatches.length ) {
			var cpChip = document.getElementById( 'cpChip' );
			var cpET   = document.getElementById( 'cpEffectTitle' );
			var cpEX   = document.getElementById( 'cpEffectText' );
			var cpPL   = document.getElementById( 'cpPlace' );

			swatches.forEach( function ( sw ) {
				sw.addEventListener( 'click', function () {
					var filter = sw.getAttribute( 'data-filter' );
					var title  = sw.getAttribute( 'data-effect-title' ) || '';
					if ( 'all' === filter || '' === title ) {
						panel.classList.remove( 'is-open' );
						return;
					}
					if ( cpChip ) { cpChip.style.background = sw.getAttribute( 'data-hex' ) || 'var(--warm-gray)'; }
					if ( cpET ) { cpET.textContent = title; }
					if ( cpEX ) { cpEX.textContent = sw.getAttribute( 'data-effect-text' ) || ''; }
					if ( cpPL ) {
						var pt = sw.getAttribute( 'data-place-title' ) || '';
						var px = sw.getAttribute( 'data-place-text' ) || '';
						cpPL.textContent = pt + ( px ? '　' + px : '' );
					}
					panel.classList.add( 'is-open' );
				} );
			} );
		}

		// ---- モバイル：フィルタートグル開閉 ----
		var toggle = document.getElementById( 'filterToggle' );
		var inner  = document.getElementById( 'artFilterInner' );
		if ( toggle && inner ) {
			toggle.addEventListener( 'click', function () {
				var open = inner.classList.toggle( 'is-open' );
				toggle.classList.toggle( 'is-open', open );
				toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			} );
		}

		applyAll();
	} );
} )();
