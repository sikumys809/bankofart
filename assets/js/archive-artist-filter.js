/*
 * archive-artist-filter.js
 * ARTIST アーカイブの 2軸（Status × Genre）AND 絞り込み ＋ もっと見る式表示。
 *   - .filter-tag[data-axis][data-filter] で軸ごとの選択状態を保持
 *   - .artist-card[data-status][data-genre]（スペース区切りのタームID）を AND 判定
 *   - フィルタ後の matched に対して「もっと見る」（PC12/モバイル6・同増分）を適用
 *   - .filter-count（ARTISTS数）は絞り込み後の総数（隠れている分も含む）
 *   - 表示制御は共通モジュール window.BankofartArchive.setupLoadMore に委譲
 *   - 素の JS（jQuery不使用）
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var grid    = document.getElementById( 'artistGrid' );
		var cards   = grid ? Array.prototype.slice.call( grid.querySelectorAll( '.artist-card' ) ) : [];
		var tags    = document.querySelectorAll( '.filter-tag[data-axis]' );
		var countEl = document.querySelector( '.filter-count .boa-num' );

		if ( ! cards.length || ! window.BankofartArchive ) {
			return;
		}

		var state = { status: 'all', genre: 'all' };

		var idList = function ( el, attr ) {
			return ( el.getAttribute( attr ) || '' ).split( /\s+/ ).filter( Boolean );
		};
		var matchAxis = function ( card, axis, attr ) {
			if ( 'all' === state[ axis ] ) { return true; }
			return idList( card, attr ).indexOf( state[ axis ] ) !== -1;
		};

		var loadMore = window.BankofartArchive.setupLoadMore( {
			wrap:             document.getElementById( 'artistLoadMore' ),
			button:           document.getElementById( 'artistLoadMoreBtn' ),
			initialDesktop:   12,
			initialMobile:    6,
			incrementDesktop: 12,
			incrementMobile:  6,
		} );

		var apply = function () {
			var matched = [];
			cards.forEach( function ( card ) {
				var show = matchAxis( card, 'status', 'data-status' ) && matchAxis( card, 'genre', 'data-genre' );
				if ( show ) {
					matched.push( card );
				} else {
					card.style.display = 'none';
				}
			} );
			if ( countEl ) {
				countEl.textContent = String( matched.length );
			}
			// matched の表示（先頭N件）と「もっと見る」可否は共通モジュールが管理。
			loadMore.reset( matched );
		};

		tags.forEach( function ( tag ) {
			tag.addEventListener( 'click', function () {
				var axis = tag.getAttribute( 'data-axis' );
				document.querySelectorAll( '.filter-tag[data-axis="' + axis + '"]' ).forEach( function ( x ) {
					x.classList.remove( 'is-active' );
				} );
				tag.classList.add( 'is-active' );
				state[ axis ] = tag.getAttribute( 'data-filter' ) || 'all';
				apply();
			} );
		} );

		apply();
	} );
} )();
