/**
 * Fluent Ticket Custom Code — ticket sidebar behaviors.
 */
(function ($) {
	'use strict';

	function fallbackCopyToClipboard(text, $button, originalHtml) {
		var textArea = document.createElement('textarea');
		textArea.value = text;
		textArea.style.position = 'fixed';
		textArea.style.left = '-999999px';
		textArea.style.top = '-999999px';
		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();

		try {
			document.execCommand('copy');
			showCopySuccess($button, originalHtml);
		} catch (err) {
			window.alert('Failed to copy. Copy manually:\n\n' + text);
		}

		document.body.removeChild(textArea);
	}

	function showCopySuccess($button, originalHtml) {
		$button.addClass('ftcc-copy--success').text('Copied');
		window.setTimeout(function () {
			$button.removeClass('ftcc-copy--success').html(originalHtml);
		}, 2000);
	}

	$(document).on('click', '.copy-autologin-btn', function (e) {
		e.preventDefault();
		var url = $(this).data('url');
		var $button = $(this);
		var originalHtml = $button.html();

		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(url).then(function () {
				showCopySuccess($button, originalHtml);
			}).catch(function () {
				fallbackCopyToClipboard(url, $button, originalHtml);
			});
		} else {
			fallbackCopyToClipboard(url, $button, originalHtml);
		}
	});
})(jQuery);
