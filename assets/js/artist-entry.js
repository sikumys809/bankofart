/*
 * artist-entry.js
 * 画家応募フォームのクライアント補助。
 *   - 二重送信防止（送信時にボタン無効化）
 *   - ポートフォリオPDFサイズ・拡張子の事前チェック（最終判定はサーバー側）
 *   - reCAPTCHA v3（キー設定時のみ execute してから submit）
 * 素のJS・jQuery不使用。secret 等の機密はフロントに無い（GAS連携はサーバー側PHPが実施）。
 * window.BOA_AE = { recaptchaSiteKey, maxPdfMB }
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var form = document.getElementById( 'ae-form' );
		if ( ! form ) { return; }

		var cfg    = ( typeof window.BOA_AE === 'object' && window.BOA_AE ) ? window.BOA_AE : {};
		var maxPdf = ( cfg.maxPdfMB || 10 ) * 1024 * 1024;

		function pdfWarning() {
			var input = document.getElementById( 'portfolio_file' );
			if ( ! input || ! input.files || ! input.files.length ) { return ''; }
			var f = input.files[0];
			var name = ( f.name || '' ).toLowerCase();
			if ( name.slice( -4 ) !== '.pdf' ) {
				return 'ポートフォリオは PDF 形式でアップロードしてください。';
			}
			if ( f.size > maxPdf ) {
				return 'ポートフォリオPDFが上限（' + ( cfg.maxPdfMB || 10 ) + 'MB）を超えています。';
			}
			return '';
		}

		form.addEventListener( 'submit', function ( e ) {
			var warn = pdfWarning();
			if ( warn ) {
				e.preventDefault();
				window.alert( warn );
				return;
			}

			var btn = document.getElementById( 'ae-submit' );
			var key = cfg.recaptchaSiteKey || '';
			if ( key && window.grecaptcha && ! form.dataset.rcDone ) {
				e.preventDefault();
				window.grecaptcha.ready( function () {
					window.grecaptcha.execute( key, { action: 'artist_entry' } ).then( function ( tok ) {
						var inp = document.getElementById( 'ae-recaptcha-response' );
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
	} );
} )();
