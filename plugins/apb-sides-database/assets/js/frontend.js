/* global apbSidesFrontend, jQuery */
(function ($) {
	'use strict';

	var page = 1;
	var timer = null;

	function debounce(fn, ms) {
		return function () {
			var ctx = this, args = arguments;
			clearTimeout(timer);
			timer = setTimeout(function () { fn.apply(ctx, args); }, ms);
		};
	}

	function fieldVal(id) {
		var $e = $('#' + id);
		return $e.length ? ($e.val() || '') : '';
	}

	function getFilters() {
		return {
			keyword:      fieldVal('apb-filter-keyword'),
			casting_type: fieldVal('apb-filter-casting_type'),
			show:         fieldVal('apb-filter-show'),
			genre:        fieldVal('apb-filter-genre'),
			medium:       fieldVal('apb-filter-medium'),
			gender:       fieldVal('apb-filter-gender'),
		};
	}

	function hasActiveFilters(f) {
		return Object.keys(f).some(function (k) { return f[k] !== '' && f[k] !== '0' && f[k] !== 0; });
	}

	function runSearch() {
		var $results = $('#apb-sides-results');
		var $label   = $('#apb-sides-page-label');
		if (!$results.length) { return; }
		$results.html('<p>Loading…</p>');
		var filters = getFilters();
		$.post(apbSidesFrontend.ajaxUrl, $.extend({ action: 'apb_sides_search', nonce: apbSidesFrontend.nonce, page: page }, filters))
			.done(function (res) {
				if (!res.success) { $results.html('<p>Search failed.</p>'); return; }
				var d = res.data;
				var html = '';
				(d.results || []).forEach(function (r) {
					var sideBase   = (apbSidesFrontend.sideDetailUrl || '').replace(/\/?$/, '');
					var detailUrl  = sideBase + '?side_id=' + encodeURIComponent(r.id);
					var scriptUrl  = (apbSidesFrontend.scriptDetailUrl || '').replace(/\/?$/, '') + '?script_id=' + encodeURIComponent(r.script_id || '');
					html += '<article class="apb-result-card" data-url="' + esc(detailUrl) + '">';
					html += '<h3><a href="' + esc(scriptUrl) + '">' + esc(r.script_title || '') + '</a></h3>';
					html += '<div class="apb-result-subtitle"><a href="' + esc(detailUrl) + '">' + esc(r.title || '') + '</a></div>';
					html += '<div class="apb-result-meta">' + esc(r.casting_type || '') + '</div>';
					html += '<p><a href="' + esc(detailUrl) + '" class="button">View Side</a></p>';
					html += '</article>';
				});
				if (!html) { html = '<p>No results found.</p>'; }
				$results.html(html);
				$label.text('Page ' + d.current_page + ' / ' + d.pages + ' (' + d.total + ' total)');
				// show/hide clear button
				$('#apb-clear-filters').toggle(hasActiveFilters(filters));
			})
			.fail(function () { $results.html('<p>Search failed.</p>'); });
	}

	function esc(str) {
		return $('<div/>').text(String(str)).html();
	}

	var debounced = debounce(function () { page = 1; runSearch(); }, 400);

	$(document).on('click', '.apb-result-card', function (e) {
		if (!$(e.target).is('a, button, .button')) {
			window.location.href = $(this).data('url');
		}
	});

	$(function () {
		if ($('#apb-sides-search-root').length) {
			// Text inputs — debounce
			$(document).on('keyup', '.apb-filter[type=search], .apb-filter[type=text]', debounced);

			// Selects — immediate
			$(document).on('change', 'select.apb-filter', function () { page = 1; runSearch(); });

			$('#apb-sides-prev').on('click', function () { if (page > 1) { page--; runSearch(); } });
			$('#apb-sides-next').on('click', function () { page++; runSearch(); });

			$('#apb-clear-filters').on('click', function () {
				$('.apb-filter').val('');
				page = 1;
				runSearch();
			});

			// Hide clear button initially
			$('#apb-clear-filters').hide();

			runSearch();

			$(document).on('click', '.apb-save-side', function (e) { e.preventDefault(); });
		}

		// PDF viewer toggle
		$(document).on('click', '.apb-toggle-pdf', function () {
			var $btn    = $(this);
			var $viewer = $('#apb-pdf-viewer');
			var $frame  = $('#apb-pdf-frame');
			var pdfUrl  = $btn.data('pdf');
			if ($viewer.is(':visible')) {
				$viewer.slideUp(200);
				$frame.attr('src', '');
				$btn.text('View PDF');
			} else {
				$frame.attr('src', pdfUrl);
				$viewer.slideDown(200);
				$btn.text('Hide PDF');
				$('html, body').animate({ scrollTop: $viewer.offset().top - 20 }, 300);
			}
		});
	});
})(jQuery);
