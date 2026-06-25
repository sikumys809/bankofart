/*
 * archive-collector-filter.js
 * COLLECTOR アーカイブの 2軸 AND 絞り込み ＋ ページ番号式表示。
 * UI は ART アーカイブと同じ「詳細から探す」パネル方式（Issue・業種とも .filter-tag チップ）。
 *
 * 絞り込み（PC・モバイル共通）:
 *   - Issue（課題）   … .filter-tag[data-axis="issue"]
 *   - 業種（industry）… .filter-tag[data-axis="industry"]
 *   - .collector-card[data-issue][data-industry]（スペース区切りのタームID）を AND 判定
 *   - .filter-count（COLLECTORS数）は絞り込み後の総数（別ページの分も含む）
 *
 * 表示：フィルタ後の matched にページ番号式（PC12/モバイル6）を適用。
 *       表示制御は共通モジュール window.BankofartArchive.setupPagination に委譲。
 * トグル：#filterToggle で #collectorFilterInner を開閉（モバイル）。
 * 素の JS（jQuery不使用）。
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var cards   = Array.prototype.slice.call( document.querySelectorAll( '.collector-grid .collector-card' ) );
		var tags    = document.querySelectorAll( '.filter-tag[data-axis]' );
		var countEl = document.querySelector( '.filter-count .boa-num' );
		var toggle  = document.getElementById( 'filterToggle' );
		var inner   = document.getElementById( 'collectorFilterInner' );

		// トグルは絞り込み対象が無くても動かす。
		if ( toggle && inner ) {
			toggle.addEventListener( 'click', function () {
				var open = inner.classList.toggle( 'is-open' );
				toggle.classList.toggle( 'is-open', open );
				toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			} );
		}

		if ( ! cards.length || ! window.BankofartArchive ) {
			return;
		}

		var state = { issue: 'all', industry: 'all' };

		var idList = function ( el, attr ) {
			return ( el.getAttribute( attr ) || '' ).split( /\s+/ ).filter( Boolean );
		};
		var matchAxis = function ( card, axis, attr ) {
			if ( 'all' === state[ axis ] ) { return true; }
			return idList( card, attr ).indexOf( state[ axis ] ) !== -1;
		};

		var pager = window.BankofartArchive.setupPagination( {
			pagerEl:        document.getElementById( 'collectorPager' ),
			perPageDesktop: 12,
			perPageMobile:  6,
			scrollTarget:   document.querySelector( '.collector-section' ),
			scrollOffset:   100,
		} );

		var apply = function () {
			var matched = [];
			cards.forEach( function ( card ) {
				var show = matchAxis( card, 'issue', 'data-issue' ) && matchAxis( card, 'industry', 'data-industry' );
				if ( show ) {
					matched.push( card );
				} else {
					card.style.display = 'none';
				}
			} );
			if ( countEl ) {
				countEl.textContent = String( matched.length );
			}
			pager.reset( matched );
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
