/*
 * header.js
 * 共通ヘッダーの挙動：
 *   - スクロールで影を付ける（.is-scrolled）
 *   - ハンバーガーメニュー（ドロワー）の開閉
 *   - CONTACT ドロップダウンの開閉
 *
 * Vanilla JS のみ（jQuery禁止）。IIFE で名前空間を汚さない。
 */
(function () {
	'use strict';

	function onReady(fn) {
		if (document.readyState !== 'loading') {
			fn();
		} else {
			document.addEventListener('DOMContentLoaded', fn);
		}
	}

	onReady(function () {
		var header = document.getElementById('site-header');
		var menuBtn = document.getElementById('menuToggle');
		var drawer = document.getElementById('drawer');
		var contactDropdown = document.getElementById('contactDropdown');
		var contactToggle = document.getElementById('contactToggle');

		// ===== スクロールで影を付ける =====
		if (header) {
			var updateScrolled = function () {
				header.classList.toggle('is-scrolled', window.scrollY > 30);
			};
			window.addEventListener('scroll', updateScrolled, { passive: true });
			updateScrolled();
		}

		// ===== ドロワー開閉 =====
		if (menuBtn && drawer) {
			var closeDrawer = function () {
				drawer.classList.remove('is-open');
				menuBtn.setAttribute('aria-expanded', 'false');
			};

			menuBtn.addEventListener('click', function (e) {
				e.stopPropagation();
				var isOpen = drawer.classList.toggle('is-open');
				menuBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});

			// ドロワー内リンクをクリックしたら閉じる
			drawer.querySelectorAll('a').forEach(function (link) {
				link.addEventListener('click', closeDrawer);
			});

			// 外側クリックで閉じる
			document.addEventListener('click', function (e) {
				if (!drawer.contains(e.target) && !menuBtn.contains(e.target)) {
					closeDrawer();
				}
			});

			// Esc で閉じる
			document.addEventListener('keydown', function (e) {
				if (e.key === 'Escape') {
					closeDrawer();
				}
			});
		}

		// ===== CONTACT ドロップダウン開閉 =====
		if (contactDropdown && contactToggle) {
			contactToggle.addEventListener('click', function (e) {
				e.stopPropagation();
				var isOpen = contactDropdown.classList.toggle('is-open');
				contactToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});

			// 子リンククリックで閉じる
			contactDropdown.querySelectorAll('.contact-menu-item').forEach(function (link) {
				link.addEventListener('click', function () {
					contactDropdown.classList.remove('is-open');
					contactToggle.setAttribute('aria-expanded', 'false');
				});
			});

			// 外側クリックで閉じる
			document.addEventListener('click', function (e) {
				if (!contactDropdown.contains(e.target)) {
					contactDropdown.classList.remove('is-open');
					contactToggle.setAttribute('aria-expanded', 'false');
				}
			});
		}
	});
})();
