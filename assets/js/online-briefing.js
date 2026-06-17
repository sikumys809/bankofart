/*
 * online-briefing.js
 * オンライン説明会予約：週表示グリッド（横=日付7日 × 縦=時間）→ 情報入力 → 確認 → 確定。
 *   Calendly / receptionist.jp 風。日時選択を1画面（週表示）に統合（旧：日付→時間の2分割）。
 * 空きは週切り替え時に admin-ajax（boa_booking_week）でまとめて取得。状態はJS上のみ（localStorage不使用）。
 * 素のJS・jQuery不使用。window.BOA_BOOKING = { ajaxUrl, nonce, today, maxDate, recaptchaSiteKey }
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var form = document.getElementById( 'ob-form' );
		if ( ! form ) { return; }

		var cfg   = ( typeof window.BOA_BOOKING === 'object' && window.BOA_BOOKING ) ? window.BOA_BOOKING : {};
		var state = { date: '', time: '' };
		var WD    = [ '日', '月', '火', '水', '木', '金', '土' ];

		function pad( n ) { return ( n < 10 ? '0' : '' ) + n; }
		function ymd( dt ) { return dt.getFullYear() + '-' + pad( dt.getMonth() + 1 ) + '-' + pad( dt.getDate() ); }
		function fromYmd( s ) { var p = s.split( '-' ); return new Date( +p[0], +p[1] - 1, +p[2] ); }

		var today    = cfg.today || '';
		var maxDate  = cfg.maxDate || '';
		var weekStart = fromYmd( today ); // 週の起点（初期＝今日）。

		// ───── ステップ表示（3ステップ）─────
		function gotoStep( n ) {
			form.querySelectorAll( '.ob-step' ).forEach( function ( el ) {
				el.classList.toggle( 'is-active', el.getAttribute( 'data-panel' ) === String( n ) );
			} );
			document.querySelectorAll( '.ob-steps li' ).forEach( function ( li ) {
				var s = parseInt( li.getAttribute( 'data-step' ), 10 );
				li.classList.toggle( 'is-current', s === n );
				li.classList.toggle( 'is-done', s < n );
			} );
			window.scrollTo( { top: form.getBoundingClientRect().top + window.scrollY - 110, behavior: 'smooth' } );
		}

		// ───── 週表示の描画 ─────
		function renderWeek() {
			var cols = document.getElementById( 'ob-week-cols' );
			cols.innerHTML = '<p class="ob-slots-loading">読み込み中…</p>';

			// ナビ可否（範囲：今日〜maxDate）。
			var prevYmd = ymd( new Date( weekStart.getFullYear(), weekStart.getMonth(), weekStart.getDate() - 7 ) );
			document.getElementById( 'ob-week-prev' ).disabled = ( ymd( weekStart ) <= today );
			document.getElementById( 'ob-week-next' ).disabled = ( ymd( weekStart ) >= maxDate );

			var url = cfg.ajaxUrl + '?action=boa_booking_week&nonce=' + encodeURIComponent( cfg.nonce ) + '&start=' + encodeURIComponent( ymd( weekStart ) );
			fetch( url, { credentials: 'same-origin' } )
				.then( function ( r ) { return r.json(); } )
				.then( function ( res ) {
					if ( ! res || ! res.success || ! res.data ) { cols.innerHTML = '<p class="ob-slots-empty">空き状況を取得できませんでした。</p>'; return; }
					var data = res.data;
					var d0 = data.days[0], d6 = data.days[6];
					document.getElementById( 'ob-week-range' ).textContent = d0.month + '/' + d0.day + ' 〜 ' + d6.month + '/' + d6.day;

					cols.innerHTML = '';
					data.days.forEach( function ( day ) {
						var col = document.createElement( 'div' );
						col.className = 'ob-day-col' + ( day.isToday ? ' is-today' : '' ) + ( ( day.isPast || ! day.inRange ) ? ' is-out' : '' );

						var head = document.createElement( 'div' );
						head.className = 'ob-day-head';
						head.innerHTML = '<span class="ob-day-num">' + day.day + '</span><span class="ob-day-wd">' + day.wd + '</span>';
						col.appendChild( head );

						var list = document.createElement( 'div' );
						list.className = 'ob-day-slots';
						var avail = day.available || [];
						data.slots.forEach( function ( slot ) {
							var btn = document.createElement( 'button' );
							btn.type = 'button';
							btn.textContent = slot;
							var open = day.inRange && ! day.isPast && avail.indexOf( slot ) !== -1;
							if ( open ) {
								btn.className = 'ob-slot' + ( ( state.date === day.date && state.time === slot ) ? ' is-selected' : '' );
								( function ( dd, tt ) {
									btn.addEventListener( 'click', function () { selectSlot( dd, tt ); } );
								} )( day.date, slot );
							} else {
								btn.className = 'ob-slot is-off';
								btn.disabled = true;
							}
							list.appendChild( btn );
						} );
						col.appendChild( list );
						cols.appendChild( col );
					} );
				} )
				.catch( function () { cols.innerHTML = '<p class="ob-slots-empty">通信エラーが発生しました。</p>'; } );
		}

		document.getElementById( 'ob-week-prev' ).addEventListener( 'click', function () {
			var d = new Date( weekStart.getFullYear(), weekStart.getMonth(), weekStart.getDate() - 7 );
			if ( ymd( d ) < today ) { d = fromYmd( today ); }
			weekStart = d; renderWeek();
		} );
		document.getElementById( 'ob-week-next' ).addEventListener( 'click', function () {
			weekStart = new Date( weekStart.getFullYear(), weekStart.getMonth(), weekStart.getDate() + 7 );
			renderWeek();
		} );

		// ───── スロット選択 → フォームへ ─────
		function selectSlot( date, time ) {
			state.date = date;
			state.time = time;
			var dt = fromYmd( date );
			var label = dt.getFullYear() + '年' + ( dt.getMonth() + 1 ) + '月' + dt.getDate() + '日（' + WD[ dt.getDay() ] + '） ' + time + '〜';
			document.getElementById( 'ob-booked-at' ).value = date + ' ' + time;
			document.getElementById( 'ob-dt-label' ).textContent = label;
			gotoStep( 2 );
		}

		// ───── 確認へ（フォームのバリデーション）─────
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
			if ( phone.replace( /[^0-9]/g, '' ).length < 10 ) { errs.push( '電話番号（10桁以上）' ); }
			if ( ! purpose ) { errs.push( 'ご目的' ); }
			if ( errs.length ) { msg.textContent = '次の項目をご確認ください：' + errs.join( '、' ); return; }

			var rows = [
				[ 'ご予約日時', document.getElementById( 'ob-dt-label' ).textContent ],
				[ 'お名前', name ], [ '会社名', company ], [ 'メール', email ], [ '電話番号', phone ], [ 'ご目的', purpose ],
			];
			document.getElementById( 'ob-confirm' ).innerHTML = rows.map( function ( r ) {
				return '<dt>' + r[0] + '</dt><dd>' + r[1].replace( /</g, '&lt;' ) + '</dd>';
			} ).join( '' );
			gotoStep( 3 );
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

		renderWeek();
	} );
} )();
