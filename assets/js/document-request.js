/*
 * document-request.js
 * 資料請求フォームのクライアント挙動（素のJS・jQuery不使用）。
 *   - 送信時の二重送信防止（ボタン disabled ＋ ローディング表記）
 *   - reCAPTCHA v3：サイトキーが localize されていればトークンを取得して hidden へ
 *     （キー未設定なら何もしない＝サーバー側もスキップ。キー取得後に有効化）
 * 必須/形式の基本チェックは HTML5（required / type=email / pattern）に委ねる。
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var form   = document.querySelector( '.document-request-page .boa-form' );
		if ( ! form ) { return; }
		var submit = document.getElementById( 'boa-dr-submit' );

		var cfg = ( typeof window.BOA_DR === 'object' && window.BOA_DR ) ? window.BOA_DR : {};
		var siteKey = cfg.recaptchaSiteKey || '';

		form.addEventListener( 'submit', function ( e ) {
			// reCAPTCHA v3（キーがある場合のみ）。
			if ( siteKey && window.grecaptcha && ! form.dataset.recaptchaDone ) {
				e.preventDefault();
				window.grecaptcha.ready( function () {
					window.grecaptcha.execute( siteKey, { action: 'document_request' } ).then( function ( token ) {
						var input = document.getElementById( 'boa-recaptcha-response' );
						if ( input ) { input.value = token; }
						form.dataset.recaptchaDone = '1';
						lock();
						form.submit();
					} );
				} );
				return;
			}
			lock();
		} );

		function lock() {
			if ( submit ) {
				submit.disabled = true;
				submit.textContent = '送信中…';
			}
		}
	} );
} )();
