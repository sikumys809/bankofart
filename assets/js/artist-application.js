/*
 * artist-application.js
 * 公認画家申請フォームのクライアント補助。
 *   - 二重送信防止（送信時にボタン無効化）
 *   - 画像サイズの事前チェック（送信前に気づけるように。最終判定はサーバー側）
 *   - reCAPTCHA v3（キー設定時のみ execute してから submit）
 * 素のJS・jQuery不使用。secret 等の機密はフロントに無い（GAS連携はサーバー側PHPが実施）。
 * window.BOA_AA = { recaptchaSiteKey, maxImageMB, maxTotalMB, maxWorkImages }
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var form = document.getElementById( 'aa-form' );
		if ( ! form ) { return; }

		var cfg      = ( typeof window.BOA_AA === 'object' && window.BOA_AA ) ? window.BOA_AA : {};
		var maxImage = ( cfg.maxImageMB || 5 ) * 1024 * 1024;
		var maxTotal = ( cfg.maxTotalMB || 20 ) * 1024 * 1024;
		var maxWork  = cfg.maxWorkImages || 10;

		// メール確認の一致チェック（送信前にやさしく通知）。
		var email  = document.getElementById( 'email' );
		var emailC = document.getElementById( 'email_confirm' );

		// 画像サイズの事前チェック。問題があれば文字列メッセージ、無ければ ''。
		function imageWarning() {
			var total = 0;
			var fileInputs = form.querySelectorAll( 'input[type="file"]' );
			for ( var i = 0; i < fileInputs.length; i++ ) {
				var input = fileInputs[ i ];
				var files = input.files || [];
				if ( input.name === 'work_images[]' && files.length > maxWork ) {
					return '制作風景の画像は最大' + maxWork + '枚までです。';
				}
				for ( var j = 0; j < files.length; j++ ) {
					var f = files[ j ];
					if ( f.size > maxImage ) {
						return '「' + f.name + '」が1枚あたりの上限（' + ( cfg.maxImageMB || 5 ) + 'MB）を超えています。';
					}
					total += f.size;
				}
			}
			if ( total > maxTotal ) {
				return '画像の合計サイズが上限（' + ( cfg.maxTotalMB || 20 ) + 'MB）を超えています。枚数やサイズを調整してください。';
			}
			return '';
		}

		form.addEventListener( 'submit', function ( e ) {
			// メール一致。
			if ( email && emailC && email.value && email.value !== emailC.value ) {
				e.preventDefault();
				emailC.focus();
				window.alert( '確認用メールアドレスが一致しません。' );
				return;
			}
			// 画像事前チェック。
			var warn = imageWarning();
			if ( warn ) {
				e.preventDefault();
				window.alert( warn );
				return;
			}

			var btn = document.getElementById( 'aa-submit' );
			var key = cfg.recaptchaSiteKey || '';
			if ( key && window.grecaptcha && ! form.dataset.rcDone ) {
				e.preventDefault();
				window.grecaptcha.ready( function () {
					window.grecaptcha.execute( key, { action: 'artist_application' } ).then( function ( tok ) {
						var inp = document.getElementById( 'aa-recaptcha-response' );
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
