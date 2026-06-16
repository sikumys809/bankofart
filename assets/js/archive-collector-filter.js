/*
 * archive-collector-filter.js
 * COLLECTOR アーカイブの 1軸（Issue＝課題）絞り込み。
 * archive-artist-filter.js（2軸AND）を1軸に簡易化したもの。
 *   - .filter-tag[data-filter] のクリックで選択中の課題を保持
 *   - .collector-card[data-issue]（スペース区切りのタームID）を表示/非表示
 *   - .filter-count（COLLECTORS数）を可視件数に動的更新
 *   - 素の JS（jQuery不使用）
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var cards   = document.querySelectorAll( '.collector-grid .collector-card' );
		var tags    = document.querySelectorAll( '.filter-tag[data-filter]' );
		var countEl = document.querySelector( '.filter-count .boa-num' );

		if ( ! cards.length || ! tags.length ) {
			return;
		}

		var setCount = function ( n ) {
			if ( countEl ) {
				countEl.textContent = String( n );
			}
		};

		var applyFilter = function ( filter ) {
			var visible = 0;
			cards.forEach( function ( card ) {
				var ids  = ( card.getAttribute( 'data-issue' ) || '' ).split( /\s+/ ).filter( Boolean );
				var show = ( 'all' === filter || ids.indexOf( filter ) !== -1 );
				card.style.display = show ? '' : 'none';
				if ( show ) {
					visible++;
				}
			} );
			setCount( visible );
		};

		tags.forEach( function ( tag ) {
			tag.addEventListener( 'click', function () {
				tags.forEach( function ( x ) {
					x.classList.remove( 'is-active' );
				} );
				tag.classList.add( 'is-active' );
				applyFilter( tag.getAttribute( 'data-filter' ) || 'all' );
			} );
		} );

		// 初期表示は全件。
		setCount( cards.length );
	} );
} )();
