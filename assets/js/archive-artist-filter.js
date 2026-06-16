/*
 * archive-artist-filter.js
 * ARTIST アーカイブの 2軸（Status × Genre）AND 絞り込み。
 *   - .filter-tag[data-axis][data-filter] のクリックで軸ごとの選択状態を保持
 *   - .artist-card[data-status][data-genre]（スペース区切りのタームID）を AND 判定で表示/非表示
 *   - .filter-count（ARTISTS数）を可視件数に動的更新
 *   - 素の JS（jQuery不使用）
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var cards   = document.querySelectorAll( '.artist-grid .artist-card' );
		var tags    = document.querySelectorAll( '.filter-tag[data-axis]' );
		var countEl = document.querySelector( '.filter-count .boa-num' );

		if ( ! cards.length || ! tags.length ) {
			return;
		}

		// 軸ごとの選択状態（初期は all）。
		var state = { status: 'all', genre: 'all' };

		var idList = function ( el, attr ) {
			var v = el.getAttribute( attr ) || '';
			return v.split( /\s+/ ).filter( Boolean );
		};

		var matchAxis = function ( card, axis, attr ) {
			if ( 'all' === state[ axis ] ) {
				return true;
			}
			return idList( card, attr ).indexOf( state[ axis ] ) !== -1;
		};

		var apply = function () {
			var visible = 0;
			cards.forEach( function ( card ) {
				var show = matchAxis( card, 'status', 'data-status' ) && matchAxis( card, 'genre', 'data-genre' );
				card.style.display = show ? '' : 'none';
				if ( show ) {
					visible++;
				}
			} );
			if ( countEl ) {
				countEl.textContent = String( visible );
			}
		};

		tags.forEach( function ( tag ) {
			tag.addEventListener( 'click', function () {
				var axis = tag.getAttribute( 'data-axis' );
				// 同じ軸のボタンだけ is-active を付け替え。
				document.querySelectorAll( '.filter-tag[data-axis="' + axis + '"]' ).forEach( function ( x ) {
					x.classList.remove( 'is-active' );
				} );
				tag.classList.add( 'is-active' );
				state[ axis ] = tag.getAttribute( 'data-filter' ) || 'all';
				apply();
			} );
		} );

		// 初期カウント。
		if ( countEl ) {
			countEl.textContent = String( cards.length );
		}
	} );
} )();
