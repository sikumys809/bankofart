/*
 * online-briefing.js
 * オンライン説明会予約の Calendly 風ウィザード（素のJS・jQuery不使用）。
 *   Step1 日付 → Step2 時間（admin-ajax で空き取得）→ Step3 情報 → Step4 確認 → 確定（admin-post）
 * 状態はJS上のみ（localStorage不使用）。戻るボタンで前ステップへ。
 * window.BOA_BOOKING = { ajaxUrl, nonce, today:'YYYY-MM-DD', maxDate:'YYYY-MM-DD', recaptchaSiteKey }
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var form = document.getElementById( 'ob-form' );
		if ( ! form ) { return; }

		var cfg = ( typeof window.BOA_BOOKING === 'object' && window.BOA_BOOKING ) ? window.BOA_BOOKING : {};
		var state = { date: '', time: '' };

		var WD = [ '日', '月', '火', '水', '木', '金', '土' ];
		function pad( n ) { return ( n < 10 ? '0' : '' ) + n; }
		function ymd( y, m, d ) { return y + '-' + pad( m ) + '-' + pad( d ); }
		function parseYmd( s ) { var p = s.split( '-' ); return { y: +p[0], m: +p[1], d: +p[2] }; }

		var today = cfg.today || '';
		var maxD  = cfg.maxDate || '';
		var tod   = parseYmd( today );
		var view  = { y: tod.y, m: tod.m }; // 表示中の月.

		// ───── ステップ表示 ─────
		function gotoStep( n ) {
			form.querySelectorAll( '.ob-step' ).forEach( function ( el ) {
				el.classList.toggle( 'is-active', el.getAttribute( 'data-panel' ) === String( n ) );
			} );
			document.querySelectorAll( '.ob-steps li' ).forEach( function ( li ) {
				li.classList.toggle( 'is-current', li.getAttribute( 'data-step' ) === String( n ) );
				li.classList.toggle( 'is-done', parseInt( li.getAttribute( 'data-step' ), 10 ) < n );
			} );
			window.scrollTo( { top: form.getBoundingClientRect().top + window.scrollY - 110, behavior: 'smooth' } );
		}

		// ───── カレンダー描画 ─────
		function renderCalendar() {
			var monthEl = document.getElementById( 'ob-cal-month' );
			var grid    = document.getElementById( 'ob-cal-grid' );
			monthEl.textContent = view.y + '年 ' + view.m + '月';
			grid.innerHTML = '';
			WD.forEach( function ( w ) {
				var h = document.createElement( 'div' );
				h.className = 'ob-cal-wd';
				h.textContent = w;
				grid.appendChild( h );
			} );
			var first = new Date( view.y, view.m - 1, 1 );
			var startWd = first.getDay();
			var days = new Date( view.y, view.m, 0 ).getDate();
			for ( var i = 0; i < startWd; i++ ) {
				grid.appendChild( document.createElement( 'div' ) );
			}
			for ( var d = 1; d <= days; d++ ) {
				var cell = document.createElement( 'button' );
				cell.type = 'button';
				cell.className = 'ob-cal-day';
				cell.textContent = d;
				var ds = ymd( view.y, view.m, d );
				if ( ds < today || ds > maxD ) {
					cell.disabled = true;
					cell.classList.add( 'is-disabled' );
				} else {
					if ( ds === state.date ) { cell.classList.add( 'is-selected' ); }
					( function ( dstr ) {
						cell.addEventListener( 'click', function () { selectDate( dstr ); } );
					} )( ds );
				}
				grid.appendChild( cell );
			}
			// ナビ可否（範囲外の月へは行かせない）。
			var prevDisabled = ymd( view.y, view.m, 1 ) <= today; // 当月より前は不可（当月まで）。
			var lastOfView   = ymd( view.y, view.m, days );
			var nextDisabled = lastOfView >= maxD;
			document.getElementById( 'ob-cal-prev' ).disabled = prevDisabled;
			document.getElementById( 'ob-cal-next' ).disabled = nextDisabled;
		}
		document.getElementById( 'ob-cal-prev' ).addEventListener( 'click', function () {
			view.m--; if ( view.m < 1 ) { view.m = 12; view.y--; } renderCalendar();
		} );
		document.getElementById( 'ob-cal-next' ).addEventListener( 'click', function () {
			view.m++; if ( view.m > 12 ) { view.m = 1; view.y++; } renderCalendar();
		} );

		// ───── 日付選択 → 時間取得 ─────
		function selectDate( ds ) {
			state.date = ds;
			state.time = '';
			var p = parseYmd( ds );
			document.getElementById( 'ob-sel-date' ).textContent = p.y + '年' + p.m + '月' + p.d + '日';
			gotoStep( 2 );
			loadSlots( ds );
		}

		function loadSlots( ds ) {
			var wrap = document.getElementById( 'ob-slots' );
			wrap.innerHTML = '<p class="ob-slots-loading">読み込み中…</p>';
			var url = cfg.ajaxUrl + '?action=boa_booking_availability&nonce=' + encodeURIComponent( cfg.nonce ) + '&date=' + encodeURIComponent( ds );
			fetch( url, { credentials: 'same-origin' } )
				.then( function ( r ) { return r.json(); } )
				.then( function ( res ) {
					if ( ! res || ! res.success || ! res.data ) { wrap.innerHTML = '<p class="ob-slots-empty">空き状況を取得できませんでした。</p>'; return; }
					var avail = res.data.available || [];
					if ( ! avail.length ) { wrap.innerHTML = '<p class="ob-slots-empty">この日に空いている時間はありません。別の日をお選びください。</p>'; return; }
					wrap.innerHTML = '';
					avail.forEach( function ( t ) {
						var b = document.createElement( 'button' );
						b.type = 'button';
						b.className = 'ob-slot';
						b.textContent = t;
						b.addEventListener( 'click', function () { selectTime( t ); } );
						wrap.appendChild( b );
					} );
				} )
				.catch( function () { wrap.innerHTML = '<p class="ob-slots-empty">通信エラーが発生しました。</p>'; } );
		}

		// ───── 時間選択 → フォーム ─────
		function selectTime( t ) {
			state.time = t;
			var p = parseYmd( state.date );
			var label = p.y + '年' + p.m + '月' + p.d + '日（' + WD[ new Date( p.y, p.m - 1, p.d ).getDay() ] + '） ' + t + '〜';
			document.getElementById( 'ob-booked-at' ).value = state.date + ' ' + t;
			document.getElementById( 'ob-dt-label' ).textContent = label;
			gotoStep( 3 );
		}

		// ───── 確認へ（Step3バリデーション）─────
		document.getElementById( 'ob-to-confirm' ).addEventListener( 'click', function () {
			var msg = document.getElementById( 'ob-form-msg' );
			msg.textContent = '';
			var name = document.getElementById( 'ob-name' ).value.trim();
			var company = document.getElementById( 'ob-company' ).value.trim();
			var email = document.getElementById( 'ob-email' ).value.trim();
			var phone = document.getElementById( 'ob-phone' ).value.trim();
			var purpose = document.getElementById( 'ob-purpose' ).value;
			var errs = [];
			if ( ! name ) { errs.push( 'お名前' ); }
			if ( ! company ) { errs.push( '会社名' ); }
			if ( ! /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test( email ) ) { errs.push( 'メールアドレス' ); }
			if ( ( phone.replace( /[^0-9]/g, '' ).length < 10 ) ) { errs.push( '電話番号（10桁以上）' ); }
			if ( ! purpose ) { errs.push( 'ご目的' ); }
			if ( errs.length ) { msg.textContent = '次の項目をご確認ください：' + errs.join( '、' ); return; }

			var rows = [
				[ 'ご予約日時', document.getElementById( 'ob-dt-label' ).textContent ],
				[ 'お名前', name ], [ '会社名', company ], [ 'メール', email ], [ '電話番号', phone ], [ 'ご目的', purpose ],
			];
			document.getElementById( 'ob-confirm' ).innerHTML = rows.map( function ( r ) {
				return '<dt>' + r[0] + '</dt><dd>' + r[1].replace( /</g, '&lt;' ) + '</dd>';
			} ).join( '' );
			gotoStep( 4 );
		} );

		// ───── 戻る ─────
		form.querySelectorAll( '.ob-back' ).forEach( function ( b ) {
			b.addEventListener( 'click', function () { gotoStep( parseInt( b.getAttribute( 'data-back' ), 10 ) ); } );
		} );

		// ───── 確定（reCAPTCHA があれば実行してから submit）─────
		form.addEventListener( 'submit', function ( e ) {
			var key = cfg.recaptchaSiteKey || '';
			var btn = document.getElementById( 'ob-submit' );
			if ( key && window.grecaptcha && ! form.dataset.rcDone ) {
				e.preventDefault();
				window.grecaptcha.ready( function () {
					window.grecaptcha.execute( key, { action: 'booking' } ).then( function ( tok ) {
						var inp = document.getElementById( 'ob-recaptcha-response' );
						if ( inp ) { inp.value = tok; }
						form.dataset.rcDone = '1';
						if ( btn ) { btn.disabled = true; btn.textContent = '送信中…'; }
						form.submit();
					} );
				} );
				return;
			}
			if ( btn ) { btn.disabled = true; btn.textContent = '送信中…'; }
		} );

		renderCalendar();
	} );
} )();
