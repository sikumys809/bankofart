/*
 * page-matching-purpose.js
 * 企業理念マッチング診断（artist向け5問）。
 * Notion「アーティストマッチング機能 構成指示書」準拠。データは完全動的：
 *   - window.BOA_MATCH.questions … 質問マスター（diagnosis-data.php / 仕様2章）
 *   - window.BOA_MATCH.artists   … 診断対象アーティスト（artist投稿から動的取得 / 仕様7.2）
 * ハードコードは一切なし。artist投稿に診断タグ＋共鳴文章を入れれば自動で母集団に追加される。
 *
 * ロジック（仕様4章）：
 *   4.1 スコア＝各回答の選択肢タグ × アーティスト主要タグの一致ごとに選択肢ポイント加算
 *   4.2 同点＝Q1一致タグを持つ者を優先、なお同点なら登録順（古い順 = _order）
 *   4.3 メインとサブで主要タグが被りすぎないよう、サブはメイン軸と被る者を最大1名に制限
 * 素の JS（jQuery不使用）。
 */
( function () {
	'use strict';

	var BOA       = ( typeof window.BOA_MATCH === 'object' && window.BOA_MATCH ) ? window.BOA_MATCH : {};
	var QUESTIONS = Array.isArray( BOA.questions ) ? BOA.questions : [];
	var ARTISTS   = Array.isArray( BOA.artists ) ? BOA.artists : [];
	var DEFAULT_GRAD = 'linear-gradient(135deg,#2a2a2a 0%,#01ae84 100%)';
	var RESONANCE_FALLBACK = 'このアーティストの詳細プロフィールで、制作テーマと作品世界をご覧ください。あなたの企業の価値観と響き合う物語がそこにあります。';

	// 登録順（古い順）を保持＝同点タイブレーク用（仕様4.2）。
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
	function photoStyleOf( a ) {
		return a.photo ? ( 'background-image:url(' + a.photo + ');' ) : ( 'background:' + DEFAULT_GRAD + ';' );
	}

	var currentQ = 0;
	var answers = [];

	function showScreen( id ) {
		document.querySelectorAll( '.match-screen' ).forEach( function ( s ) {
			s.classList.toggle( 'is-active', s.id === id );
		} );
		window.scrollTo( { top: 0, behavior: 'smooth' } );
	}

	// ════════ スタート ════════
	var startBtn = document.getElementById( 'start-btn' );
	if ( startBtn ) {
		startBtn.addEventListener( 'click', function () {
			if ( ! QUESTIONS.length ) { return; }
			currentQ = 0;
			answers = [];
			showQuestion( 0 );
			showScreen( 'screen-question' );
		} );
	}

	// ════════ 質問表示 ════════
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

	// ════════ 選択処理 ════════
	function selectOption( qIdx, optIdx ) {
		var q = QUESTIONS[ qIdx ];
		answers.push( { qIdx: qIdx, optIdx: optIdx, tags: q.options[ optIdx ].tags || [], weight: q.weight } );
		if ( qIdx + 1 < QUESTIONS.length ) {
			showQuestion( qIdx + 1 );
		} else {
			showScreen( 'screen-loading' );
			setTimeout( function () { showResult(); }, 1400 );
		}
	}

	// ════════ スコア計算＆結果表示（仕様4章） ════════
	function showResult() {
		// 候補0名のフォールバック（仕様：診断対象0名）。
		if ( ! ARTISTS.length ) {
			document.getElementById( 'main-artist' ).innerHTML =
				'<div class="result-empty">診断対象のアーティストを準備中です。<br>診断タグを設定したアーティストが登録され次第、結果をご提案できます。</div>';
			document.getElementById( 'sub-artists' ).innerHTML = '';
			var subH = document.querySelector( '.result-sub-h' );
			if ( subH ) { subH.style.display = 'none'; }
			showScreen( 'screen-result' );
			return;
		}

		// 4.1 スコア計算
		var scored = ARTISTS.map( function ( a ) {
			var score = 0;
			var matched = [];
			answers.forEach( function ( ans ) {
				ans.tags.forEach( function ( tag ) {
					if ( a.tags.indexOf( tag ) !== -1 ) {
						score += ans.weight;
						matched.push( tag );
					}
				} );
			} );
			var q1Match = false;
			if ( answers[ 0 ] ) {
				answers[ 0 ].tags.forEach( function ( tag ) {
					if ( a.tags.indexOf( tag ) !== -1 ) { q1Match = true; }
				} );
			}
			return { artist: a, score: score, q1Match: q1Match, matched: matched, order: a._order };
		} );

		// 4.2 ソート：score 降順 → Q1一致優先 → 登録順（古い順）
		scored.sort( function ( x, y ) {
			if ( y.score !== x.score ) { return y.score - x.score; }
			if ( x.q1Match !== y.q1Match ) { return x.q1Match ? -1 : 1; }
			return x.order - y.order;
		} );

		var main = scored[ 0 ];
		var mainTags = main.artist.tags || [];
		var rest = scored.slice( 1 );

		// 4.3 タグ被り補正：サブはメイン軸と被る者を最大1名に制限。足りなければスコア順で補充。
		function sharesMain( s ) {
			return ( s.artist.tags || [] ).some( function ( t ) { return mainTags.indexOf( t ) !== -1; } );
		}
		var subs = [];
		var sharedUsed = 0;
		rest.forEach( function ( s ) {
			if ( subs.length >= 3 ) { return; }
			if ( sharesMain( s ) ) {
				if ( sharedUsed < 1 ) { subs.push( s ); sharedUsed++; }
			} else {
				subs.push( s );
			}
		} );
		if ( subs.length < 3 ) {
			rest.forEach( function ( s ) {
				if ( subs.length >= 3 ) { return; }
				if ( subs.indexOf( s ) === -1 ) { subs.push( s ); }
			} );
		}

		renderMain( main.artist );
		renderSubs( subs );
		showScreen( 'screen-result' );
	}

	// ════════ メイン結果（仕様1.1） ════════
	function renderMain( a ) {
		var photoInner = a.photo ? '' : ( '<span class="result-main-photo-initial">' + esc( initialOf( a ) ) + '</span>' );
		var resonance = a.resonance ? a.resonance : RESONANCE_FALLBACK;

		var originHtml = a.origin ? ( '<div class="result-main-origin">' + esc( a.origin ) + '</div>' ) : '';

		var worksHtml = '';
		if ( a.works && a.works.length ) {
			worksHtml = '<div class="result-main-works">' + a.works.slice( 0, 3 ).map( function ( url ) {
				return '<div class="result-work" style="background-image:url(' + url + ');"></div>';
			} ).join( '' ) + '</div>';
		}

		var ctaHtml = a.url
			? ( '<div class="result-main-cta"><a href="' + a.url + '">このアーティストの詳細</a></div>' )
			: '';

		document.getElementById( 'main-artist' ).innerHTML =
			'<div class="result-main-photo" style="' + photoStyleOf( a ) + '">' + photoInner + '</div>' +
			'<div class="result-main-body">' +
				'<div class="result-main-name-en">' + esc( a.nameEn || a.name ) + '</div>' +
				'<div class="result-main-name-ja">' + esc( a.name ) + '</div>' +
				( a.theme ? ( '<div class="result-main-theme">' + esc( a.theme ) + '</div>' ) : '' ) +
				'<div class="result-main-message">' + esc( resonance ) + '</div>' +
				originHtml +
				worksHtml +
				ctaHtml +
			'</div>';
	}

	// ════════ サブ3名（実 single-artist へリンク） ════════
	function renderSubs( subs ) {
		var subH = document.querySelector( '.result-sub-h' );
		if ( subH ) { subH.style.display = subs.length ? '' : 'none'; }

		document.getElementById( 'sub-artists' ).innerHTML = subs.map( function ( s ) {
			var a = s.artist;
			var photoInner = a.photo ? '' : ( '<span class="result-sub-photo-initial">' + esc( initialOf( a ) ) + '</span>' );
			var openTag = a.url ? ( '<a href="' + a.url + '" class="result-sub-card">' ) : '<div class="result-sub-card is-pending">';
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

	// ════════ もう一度診断 ════════
	window.restart = function () {
		currentQ = 0;
		answers = [];
		var subH = document.querySelector( '.result-sub-h' );
		if ( subH ) { subH.style.display = ''; }
		showScreen( 'screen-start' );
	};

} )();
