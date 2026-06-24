/*
 * archive-collector-filter.js
 * COLLECTOR アーカイブの 2軸 AND 絞り込み ＋ モバイル限定ロードモア。
 * UI は ART アーカイブと同じ「詳細から探す」パネル方式（Issue・業種とも .filter-tag チップ）。
 *
 * 絞り込み（PC・モバイル共通）:
 *   - Issue（課題）   … .filter-tag[data-axis="issue"]
 *   - 業種（industry）… .filter-tag[data-axis="industry"]
 *   - .collector-card[data-issue][data-industry]（スペース区切りのタームID）を AND 判定
 *   - .filter-count（COLLECTORS数）を「絞り込み後の総数」に更新（ロードモアで隠れている分も含む）
 *   - どちらの軸も「すべて」でその軸は無効化
 *
 * トグル（@media max-width:760px 相当・CSS）:
 *   - #filterToggle で #collectorFilterInner を開閉（.is-open）
 *
 * ロードモア（モバイル・JSで判定）:
 *   - 絞り込み結果の先頭 5 件のみ表示 →「もっと見る」で 5 件ずつ追加
 *   - PC（>760px）は全件表示＋ロードモアUI非表示。軸変更で 5 件にリセット
 *
 * 素の JS（jQuery不使用）。
 */
( function () {
	'use strict';

	var MOBILE_CHUNK = 5;

	document.addEventListener( 'DOMContentLoaded', function () {
		var cards    = Array.prototype.slice.call( document.querySelectorAll( '.collector-grid .collector-card' ) );
		var tags     = document.querySelectorAll( '.filter-tag[data-axis]' );
		var countEl  = document.querySelector( '.filter-count .boa-num' );
		var moreWrap = document.getElementById( 'collectorLoadMore' );
		var moreBtn  = document.getElementById( 'collectorLoadMoreBtn' );
		var toggle   = document.getElementById( 'filterToggle' );
		var inner    = document.getElementById( 'collectorFilterInner' );
		var mq       = window.matchMedia( '(max-width: 760px)' );

		// トグルは絞り込み対象が無くても動かす。
		if ( toggle && inner ) {
			toggle.addEventListener( 'click', function () {
				var open = inner.classList.toggle( 'is-open' );
				toggle.classList.toggle( 'is-open', open );
				toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			} );
		}

		if ( ! cards.length ) {
			return;
		}

		var state = { issue: 'all', industry: 'all' };
		var shown = MOBILE_CHUNK; // モバイルで表示中の件数。

		var idList = function ( el, attr ) {
			return ( el.getAttribute( attr ) || '' ).split( /\s+/ ).filter( Boolean );
		};

		var matchAxis = function ( card, axis, attr ) {
			if ( 'all' === state[ axis ] ) {
				return true;
			}
			return idList( card, attr ).indexOf( state[ axis ] ) !== -1;
		};

		var getMatched = function () {
			return cards.filter( function ( card ) {
				return matchAxis( card, 'issue', 'data-issue' ) && matchAxis( card, 'industry', 'data-industry' );
			} );
		};

		var render = function () {
			var matched  = getMatched();
			var isMobile = mq.matches;

			cards.forEach( function ( card ) {
				card.style.display = 'none';
			} );

			if ( isMobile ) {
				matched.forEach( function ( card, i ) {
					if ( i < shown ) {
						card.style.display = '';
					}
				} );
				if ( moreWrap ) {
					moreWrap.classList.toggle( 'is-active', matched.length > shown );
				}
			} else {
				matched.forEach( function ( card ) {
					card.style.display = '';
				} );
				if ( moreWrap ) {
					moreWrap.classList.remove( 'is-active' );
				}
			}

			if ( countEl ) {
				countEl.textContent = String( matched.length );
			}
		};

		// 軸変更時はロードモアを 5 件にリセットして再描画。
		var changeFilter = function () {
			shown = MOBILE_CHUNK;
			render();
		};

		// Issue / 業種 チップ（軸ごとに is-active を付け替え）。
		tags.forEach( function ( tag ) {
			tag.addEventListener( 'click', function () {
				var axis = tag.getAttribute( 'data-axis' );
				document.querySelectorAll( '.filter-tag[data-axis="' + axis + '"]' ).forEach( function ( x ) {
					x.classList.remove( 'is-active' );
				} );
				tag.classList.add( 'is-active' );
				state[ axis ] = tag.getAttribute( 'data-filter' ) || 'all';
				changeFilter();
			} );
		} );

		// もっと見る（モバイル）。
		if ( moreBtn ) {
			moreBtn.addEventListener( 'click', function () {
				shown += MOBILE_CHUNK;
				render();
			} );
		}

		// 画面幅の変化（PC⇔モバイル）でロードモア状態をリセットして再描画。
		var onMqChange = function () {
			shown = MOBILE_CHUNK;
			render();
		};
		if ( mq.addEventListener ) {
			mq.addEventListener( 'change', onMqChange );
		} else if ( mq.addListener ) {
			mq.addListener( onMqChange ); // 旧Safari互換.
		}

		render();
	} );
} )();
