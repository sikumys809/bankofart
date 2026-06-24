/*
 * archive-collector-filter.js
 * COLLECTOR アーカイブの 2軸 AND 絞り込み ＋ モバイル限定ロードモア。
 *
 * 絞り込み（PC・モバイル共通）:
 *   - Issue（課題）   … .filter-tag[data-axis="issue"] のチップ（既存UI）
 *   - 業種（industry）… #industryFilter の <select>（プルダウン）
 *   - .collector-card[data-issue][data-industry]（スペース区切りのタームID）を AND 判定
 *   - .filter-count（COLLECTORS数）を「絞り込み後の総数」に更新（ロードモアで隠れている分も含む）
 *
 * ロードモア（@media max-width:760px 相当・JSで判定）:
 *   - 絞り込み結果のうち最初の 5 件のみ表示し、「もっと見る」で 5 件ずつ追加表示
 *   - PC（>760px）ではロードモアUIを隠して全件表示
 *   - 軸を変えるとロードモアのカウントは 5 件にリセット
 *
 * 素の JS（jQuery不使用）。
 */
( function () {
	'use strict';

	var MOBILE_CHUNK = 5;

	document.addEventListener( 'DOMContentLoaded', function () {
		var cards    = Array.prototype.slice.call( document.querySelectorAll( '.collector-grid .collector-card' ) );
		var tags     = document.querySelectorAll( '.filter-tag[data-axis="issue"]' );
		var select   = document.getElementById( 'industryFilter' );
		var countEl  = document.querySelector( '.filter-count .boa-num' );
		var moreWrap = document.getElementById( 'collectorLoadMore' );
		var moreBtn  = document.getElementById( 'collectorLoadMoreBtn' );
		var mq       = window.matchMedia( '(max-width: 760px)' );

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

			// いったん全件隠す。
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

			// 件数は絞り込み後の総数（隠れている分も含む）。
			if ( countEl ) {
				countEl.textContent = String( matched.length );
			}
		};

		// 軸変更時はロードモアを 5 件にリセットして再描画。
		var changeFilter = function () {
			shown = MOBILE_CHUNK;
			render();
		};

		// Issue チップ。
		tags.forEach( function ( tag ) {
			tag.addEventListener( 'click', function () {
				document.querySelectorAll( '.filter-tag[data-axis="issue"]' ).forEach( function ( x ) {
					x.classList.remove( 'is-active' );
				} );
				tag.classList.add( 'is-active' );
				state.issue = tag.getAttribute( 'data-filter' ) || 'all';
				changeFilter();
			} );
		} );

		// 業種プルダウン。
		if ( select ) {
			select.addEventListener( 'change', function () {
				state.industry = select.value || 'all';
				changeFilter();
			} );
		}

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
