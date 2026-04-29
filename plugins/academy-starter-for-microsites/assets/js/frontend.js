(function () {
	'use strict';

	var config = window.AcademyStarter || {};
	var focusableSelector = [
		'a[href]',
		'button:not([disabled])',
		'textarea:not([disabled])',
		'input:not([disabled])',
		'select:not([disabled])',
		'[tabindex]:not([tabindex="-1"])'
	].join(',');

	function logClick(type) {
		if (!config.ajaxUrl || !config.nonce || !type) {
			return;
		}

		var body = new window.FormData();
		body.append('action', config.action || 'academy_starter_log_click');
		body.append('nonce', config.nonce);
		body.append('style', config.style || '');
		body.append('type', type);

		if (typeof window.gtag === 'function' && config.ga4Id) {
			window.gtag('event', 'cta_click', {
				event_category: 'Academy Starter',
				event_label: type === 'paid' ? 'Paid CTA ($7)' : 'Free CTA',
				cta_type: type,
				piano_style: config.style
			});
		}

		window.fetch(config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: body
		}).catch(function () {
			// Analytics should never block the visitor's next action.
		});
	}

	function initLanding(landing) {
		var modalOverlay = landing.querySelector('[data-academy-starter-modal]');
		var closeButton = modalOverlay ? modalOverlay.querySelector('.academy-starter-modal-close') : null;
		var lastFocusedElement = null;

		landing.querySelectorAll('.academy-starter-video-element[data-hls-src]').forEach(function (video) {
			var videoUrl = video.getAttribute('data-hls-src');

			if (!videoUrl) {
				return;
			}

			if (window.Hls && window.Hls.isSupported()) {
				var hls = new window.Hls({
					enableWorker: true,
					lowLatencyMode: false
				});
				hls.loadSource(videoUrl);
				hls.attachMedia(video);
				video.hls = hls;
			} else if (video.canPlayType('application/vnd.apple.mpegurl')) {
				video.src = videoUrl;
			}
		});

		function getFocusableElements() {
			if (!modalOverlay) {
				return [];
			}

			return Array.prototype.slice.call(modalOverlay.querySelectorAll(focusableSelector)).filter(function (element) {
				return element.offsetWidth > 0 || element.offsetHeight > 0 || element === document.activeElement;
			});
		}

		function openModal() {
			if (!modalOverlay) {
				return;
			}

			lastFocusedElement = document.activeElement;
			modalOverlay.classList.add('is-open');
			modalOverlay.setAttribute('aria-hidden', 'false');
			document.body.classList.add('academy-starter-modal-open');

			var focusable = getFocusableElements();
			if (focusable.length) {
				focusable[0].focus();
			}
		}

		function closeModal() {
			if (!modalOverlay) {
				return;
			}

			modalOverlay.classList.remove('is-open');
			modalOverlay.setAttribute('aria-hidden', 'true');
			document.body.classList.remove('academy-starter-modal-open');

			if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
				lastFocusedElement.focus();
			}
		}

		function trapFocus(event) {
			if (!modalOverlay || !modalOverlay.classList.contains('is-open') || event.key !== 'Tab') {
				return;
			}

			var focusable = getFocusableElements();
			if (!focusable.length) {
				event.preventDefault();
				return;
			}

			var firstElement = focusable[0];
			var lastElement = focusable[focusable.length - 1];

			if (event.shiftKey && document.activeElement === firstElement) {
				event.preventDefault();
				lastElement.focus();
			} else if (!event.shiftKey && document.activeElement === lastElement) {
				event.preventDefault();
				firstElement.focus();
			}
		}

		landing.querySelectorAll('.academy-starter-free-cta').forEach(function (button) {
			button.addEventListener('click', function (event) {
				event.preventDefault();
				logClick('free');
				openModal();
			});
		});

		landing.querySelectorAll('.academy-starter-paid-cta').forEach(function (link) {
			link.addEventListener('click', function () {
				logClick('paid');
			});
		});

		if (closeButton) {
			closeButton.addEventListener('click', closeModal);
		}

		if (modalOverlay) {
			modalOverlay.addEventListener('click', function (event) {
				if (event.target === modalOverlay) {
					closeModal();
				}
			});
		}

		var stickyBar = landing.querySelector('#academy-starter-sticky-bar');
		var dismissBtn = stickyBar ? stickyBar.querySelector('.academy-starter-sticky-dismiss') : null;

		if (stickyBar) {
			setTimeout(function () {
				stickyBar.classList.add('is-visible');
				stickyBar.setAttribute('aria-hidden', 'false');
			}, 3000);

			if (dismissBtn) {
				dismissBtn.addEventListener('click', function () {
					stickyBar.classList.remove('is-visible');
					stickyBar.setAttribute('aria-hidden', 'true');
				});
			}
		}

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape') {
				closeModal();
			}

			trapFocus(event);
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.academy-starter').forEach(initLanding);
	});
}());
