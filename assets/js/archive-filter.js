/*
 * archive-filter.js
 * NEWS / JOURNAL アーカイブのカテゴリ絞り込み（クライアント側DOMフィルタ）。
 *   - .filter-tag[data-filter] のクリックで .news-item[data-category] を表示/非表示
 *   - .filter-count（ARTICLES数）を表示中の件数に動的更新
 *   - 素の JS（jQuery不使用）。現在ページ内のアイテムを対象にフィルタする。
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var tags    = document.querySelectorAll( '.filter-tag' );
		var items   = document.querySelectorAll( '.news-list .news-item' );
		var countEl = document.querySelector( '.filter-count .boa-num' );

		if ( ! tags.length || ! items.length ) {
			return;
		}

		var setCount = function ( n ) {
			if ( countEl ) {
				countEl.textContent = String( n );
			}
		};

		var applyFilter = function ( filter ) {
			var visible = 0;
			items.forEach( function ( item ) {
				var cat  = item.getAttribute( 'data-category' ) || '';
				var show = ( 'all' === filter || cat === filter );
				item.style.display = show ? '' : 'none';
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
		setCount( items.length );
	} );
} )();
