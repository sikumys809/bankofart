/*
 * archive-art-filter.js
 * ART アーカイブの 7軸 AND 絞り込み ＋ ソート。
 *   - 軸：status / artist / form / genre / technique / size / color
 *   - .filter-tag / .color-swatch（data-axis + data-filter）で軸ごとの選択状態を保持
 *   - .art-card[data-{axis}]（スペース区切りの slug）を全軸 AND 判定で表示/非表示
 *   - .sort-tabs（data-sort: newest / artist / size）で DOM を並べ替え
 *   - 表示件数（.filter-visible）を可視件数に動的更新
 *   - 素の JS（jQuery不使用）
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var grid = document.getElementById( 'artGrid' );
		if ( ! grid ) {
			return;
		}
		var cards   = Array.prototype.slice.call( grid.querySelectorAll( '.art-card' ) );
		var buttons = document.querySelectorAll( '[data-axis]' );
		var visEl   = document.querySelector( '.filter-visible' );

		var AXES = [ 'status', 'artist', 'form', 'genre', 'technique', 'size', 'color' ];
		var state = {};
		AXES.forEach( function ( a ) {
			state[ a ] = 'all';
		} );

		// 元の並び順（NEWEST = サーバの投稿日降順）を保持。
		cards.forEach( function ( c, i ) {
			c.dataset.origIndex = String( i );
		} );

		var idList = function ( card, axis ) {
			var v = card.getAttribute( 'data-' + axis ) || '';
			return v.split( /\s+/ ).filter( Boolean );
		};

		var applyFilter = function () {
			var visible = 0;
			cards.forEach( function ( card ) {
				var show = AXES.every( function ( axis ) {
					return 'all' === state[ axis ] || idList( card, axis ).indexOf( state[ axis ] ) !== -1;
				} );
				card.style.display = show ? '' : 'none';
				if ( show ) {
					visible++;
				}
			} );
			if ( visEl ) {
				visEl.textContent = String( visible );
			}
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
				applyFilter();
			} );
		} );

		// ---- ソート ----
		var sortTabs = document.querySelectorAll( '.sort-tabs [data-sort]' );
		var num = function ( card, key ) {
			return parseFloat( card.getAttribute( key ) ) || 0;
		};
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
				// newest：元の並び（投稿日降順）に戻す。
				sorted.sort( function ( a, b ) {
					return num( a, 'data-orig-index' ) - num( b, 'data-orig-index' ) || ( parseInt( a.dataset.origIndex, 10 ) - parseInt( b.dataset.origIndex, 10 ) );
				} );
			}
			sorted.forEach( function ( card ) {
				grid.appendChild( card );
			} );
		};

		sortTabs.forEach( function ( tab ) {
			tab.addEventListener( 'click', function () {
				sortTabs.forEach( function ( x ) {
					x.classList.remove( 'is-active' );
				} );
				tab.classList.add( 'is-active' );
				sortBy( tab.getAttribute( 'data-sort' ) );
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

					// 「すべて」または効果データが無い色 → パネル非表示。
					if ( 'all' === filter || '' === title ) {
						panel.classList.remove( 'is-open' );
						return;
					}
					if ( cpChip ) {
						cpChip.style.background = sw.getAttribute( 'data-hex' ) || 'var(--warm-gray)';
					}
					if ( cpET ) {
						cpET.textContent = title;
					}
					if ( cpEX ) {
						cpEX.textContent = sw.getAttribute( 'data-effect-text' ) || '';
					}
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

		// 初期表示件数。
		if ( visEl ) {
			visEl.textContent = String( cards.length );
		}
	} );
} )();
