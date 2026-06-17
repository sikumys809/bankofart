/*
 * page-matching-issue.js
 * 課題逆引き診断（collector向け3問）。Notion「課題逆引き診断 構成指示書」準拠。データは完全動的：
 *   - window.BOA_ISSUE.questions    … 質問3問（diagnosis-data.php / 仕様3章）
 *   - window.BOA_ISSUE.effectTypes  … 効用タイプ5種（仕様4章＋出典8.5.2）
 *   - window.BOA_ISSUE.effectMap    … 効用タグ→アーティストタグ対応表（仕様5.2）
 *   - window.BOA_ISSUE.artists      … artist投稿（診断タグ付き）
 *   - window.BOA_ISSUE.collectors   … collector投稿（課題タグ付き）
 *
 * ロジック（仕様5章）：
 *   5.1 効用タイプ判定＝各回答の効用タグ × タイプの効くタグ一致ごとに配点加算。最高スコアがメイン。
 *       同点はQ1で選ばれたタグを多く含むタイプを優先。
 *   5.2 おすすめ画家＝メインタイプの効くタグを対応表でアーティストタグへ変換→各artistの診断タグと照合→上位3名。
 *   5.3 コレクター記事＝メインタイプの target_issues と課題タグが一致するcollectorを新しい順に2〜3件。0件はブロック非表示。
 * 出典注記は仕様8.5.3：断定せず・成果保証なし・新規タブでリンク。
 * 素の JS（jQuery不使用）。
 */
( function () {
	'use strict';

	var BOA = ( typeof window.BOA_ISSUE === 'object' && window.BOA_ISSUE ) ? window.BOA_ISSUE : {};
	var QUESTIONS = Array.isArray( BOA.questions ) ? BOA.questions : [];
	var ARTISTS   = Array.isArray( BOA.artists ) ? BOA.artists : [];
	var COLLECTORS = Array.isArray( BOA.collectors ) ? BOA.collectors : [];
	var EFFECT_MAP = ( BOA.effectMap && typeof BOA.effectMap === 'object' ) ? BOA.effectMap : {};
	var DEFAULT_GRAD = 'linear-gradient(135deg,#2a2a2a 0%,#01ae84 100%)';

	// 効用タイプ：連想配列(type_01..)を配列化（登録順タイブレーク用に index 保持）。
	var TYPES = [];
	if ( BOA.effectTypes && typeof BOA.effectTypes === 'object' ) {
		Object.keys( BOA.effectTypes ).forEach( function ( k, i ) {
			var t = BOA.effectTypes[ k ];
			t._order = i;
			TYPES.push( t );
		} );
	}
	ARTISTS.forEach( function ( a, i ) { a._order = i; } );

	function esc( s ) {
		var d = document.createElement( 'div' );
		d.textContent = ( s === null || s === undefined ) ? '' : String( s );
		return d.innerHTML;
	}
	function initialOf( a ) {
		var src = String( a.nameEn || a.name || '?' ).trim();
		var letters = src.replace( /[^A-Za-z]/g, '' ).slice( 0, 2 ).toUpperCase();
		return letters || src.slice( 0, 1 );
	}
	function photoStyleOf( o ) {
		return o.photo ? ( 'background-image:url(' + o.photo + ');' ) : ( 'background:' + DEFAULT_GRAD + ';' );
	}

	var answers = [];

	function showScreen( id ) {
		document.querySelectorAll( '.match-screen' ).forEach( function ( s ) {
			s.classList.toggle( 'is-active', s.id === id );
		} );
		window.scrollTo( { top: 0, behavior: 'smooth' } );
	}

	var startBtn = document.getElementById( 'start-btn' );
	if ( startBtn ) {
		startBtn.addEventListener( 'click', function () {
			if ( ! QUESTIONS.length ) { return; }
			answers = [];
			showQuestion( 0 );
			showScreen( 'screen-question' );
		} );
	}

	function showQuestion( idx ) {
		var q = QUESTIONS[ idx ];
		document.getElementById( 'q-progress' ).textContent = 'QUESTION ' + ( idx + 1 ) + ' / ' + QUESTIONS.length;
		document.getElementById( 'q-progress-fill' ).style.width = ( ( idx + 1 ) / QUESTIONS.length * 100 ) + '%';
		document.getElementById( 'q-title' ).textContent = q.question;
		var optsEl = document.getElementById( 'q-options' );
		optsEl.innerHTML = '';
		q.options.forEach( function ( opt, oi ) {
			var btn = document.createElement( 'button' );
			btn.className = 'match-option';
			btn.textContent = opt.label;
			btn.addEventListener( 'click', function () { selectOption( idx, oi ); } );
			optsEl.appendChild( btn );
		} );
	}

	function selectOption( qIdx, optIdx ) {
		var q = QUESTIONS[ qIdx ];
		answers.push( { qIdx: qIdx, tags: q.options[ optIdx ].tags || [], weight: q.weight } );
		if ( qIdx + 1 < QUESTIONS.length ) {
			showQuestion( qIdx + 1 );
		} else {
			showScreen( 'screen-loading' );
			setTimeout( function () { showResult(); }, 1400 );
		}
	}

	function showResult() {
		if ( ! TYPES.length ) {
			document.getElementById( 'result-type' ).innerHTML = '<div class="result-type-desc">診断データを準備中です。</div>';
			showScreen( 'screen-result' );
			return;
		}

		// 5.1 効用タイプ判定
		var q1tags = answers[ 0 ] ? answers[ 0 ].tags : [];
		var scoredTypes = TYPES.map( function ( t ) {
			var score = 0;
			answers.forEach( function ( ans ) {
				ans.tags.forEach( function ( tag ) {
					if ( t.effect_tags.indexOf( tag ) !== -1 ) { score += ans.weight; }
				} );
			} );
			var q1overlap = 0;
			q1tags.forEach( function ( tag ) { if ( t.effect_tags.indexOf( tag ) !== -1 ) { q1overlap++; } } );
			return { type: t, score: score, q1overlap: q1overlap, order: t._order };
		} );
		scoredTypes.sort( function ( x, y ) {
			if ( y.score !== x.score ) { return y.score - x.score; }
			if ( y.q1overlap !== x.q1overlap ) { return y.q1overlap - x.q1overlap; }
			return x.order - y.order;
		} );
		var mainType = scoredTypes[ 0 ].type;

		renderType( mainType );
		renderArtists( recommendArtists( mainType ) );
		renderCollectors( relatedCollectors( mainType ) );
		showScreen( 'screen-result' );
	}

	// 5.2 おすすめ画家3名
	function recommendArtists( type ) {
		// 効用タグ→アーティストタグ（union）
		var target = {};
		( type.effect_tags || [] ).forEach( function ( et ) {
			( EFFECT_MAP[ et ] || [] ).forEach( function ( at ) { target[ at ] = true; } );
		} );
		var scored = ARTISTS.map( function ( a ) {
			var score = 0;
			( a.tags || [] ).forEach( function ( t ) { if ( target[ t ] ) { score++; } } );
			return { artist: a, score: score, order: a._order };
		} );
		scored.sort( function ( x, y ) {
			if ( y.score !== x.score ) { return y.score - x.score; }
			return x.order - y.order;
		} );
		return scored.slice( 0, 3 ).filter( function ( s ) { return s.score > 0 || ARTISTS.length <= 3; } ).map( function ( s ) { return s.artist; } );
	}

	// 5.3 同じ課題のコレクター記事（2〜3件、新しい順。0件はブロック非表示）
	function relatedCollectors( type ) {
		var issues = type.target_issues || [];
		return COLLECTORS.filter( function ( c ) {
			return ( c.issues || [] ).some( function ( i ) { return issues.indexOf( i ) !== -1; } );
		} ).slice( 0, 3 );
	}

	function renderType( t ) {
		var c = t.citation || {};
		var source = '';
		if ( c.note ) {
			source = '<div class="result-type-source">※ ' + esc( c.note );
			if ( c.url && c.label ) {
				source += '<br>出典：<a href="' + c.url + '" target="_blank" rel="noopener">' + esc( c.label ) + '</a>';
			}
			source += '</div>';
		}
		document.getElementById( 'result-type' ).innerHTML =
			'<div class="result-type-name">' + esc( t.name ) + '</div>' +
			'<div class="result-type-desc">' + esc( t.description ) + '</div>' +
			source;
	}

	function renderArtists( list ) {
		var subH = document.querySelector( '.result-sub-h' );
		if ( subH ) { subH.style.display = list.length ? '' : 'none'; }
		document.getElementById( 'recommended-artists' ).innerHTML = list.map( function ( a ) {
			var photoInner = a.photo ? '' : ( '<span class="result-sub-photo-initial">' + esc( initialOf( a ) ) + '</span>' );
			var openTag = a.url ? ( '<a href="' + a.url + '" class="result-sub-card">' ) : '<div class="result-sub-card">';
			var closeTag = a.url ? '</a>' : '</div>';
			return openTag +
				'<div class="result-sub-photo" style="' + photoStyleOf( a ) + '">' + photoInner + '</div>' +
				'<div class="result-sub-body">' +
					'<div class="result-sub-name-en">' + esc( a.nameEn || a.name ) + '</div>' +
					'<div class="result-sub-name-ja">' + esc( a.name ) + '</div>' +
					( a.theme ? ( '<div class="result-sub-theme">' + esc( a.theme ) + '</div>' ) : '' ) +
				'</div>' +
				closeTag;
		} ).join( '' );
	}

	function renderCollectors( list ) {
		// 仕様5.3＋オープン課題10：該当0件はブロックごと非表示。
		var block = document.getElementById( 'collector-block' );
		if ( ! list.length ) {
			if ( block ) { block.style.display = 'none'; }
			document.getElementById( 'related-collectors' ).innerHTML = '';
			return;
		}
		if ( block ) { block.style.display = ''; }
		document.getElementById( 'related-collectors' ).innerHTML = list.map( function ( c ) {
			var photoInner = c.photo ? '' : ( '<span class="result-collector-photo-initial">' + esc( initialOf( c ) ) + '</span>' );
			var tag = ( c.issues && c.issues.length ) ? ( '<span class="result-collector-tag">' + esc( c.issues[ 0 ] ) + '</span>' ) : '';
			var openTag = c.url ? ( '<a href="' + c.url + '" class="result-collector-card">' ) : '<div class="result-collector-card">';
			var closeTag = c.url ? '</a>' : '</div>';
			return openTag +
				'<div class="result-collector-photo" style="' + photoStyleOf( c ) + '">' + photoInner + '</div>' +
				'<div class="result-collector-body">' +
					tag +
					'<div class="result-collector-name">' + esc( c.name ) + '</div>' +
					( c.summary ? ( '<div class="result-collector-summary">' + esc( c.summary ) + '</div>' ) : '' ) +
				'</div>' +
				closeTag;
		} ).join( '' );
	}

	window.restart = function () {
		answers = [];
		var block = document.getElementById( 'collector-block' );
		if ( block ) { block.style.display = ''; }
		var subH = document.querySelector( '.result-sub-h' );
		if ( subH ) { subH.style.display = ''; }
		showScreen( 'screen-start' );
	};

} )();
