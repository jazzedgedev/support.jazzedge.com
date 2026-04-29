(function () {
	var STORE_KEY = 'apb_resources_view';

	function applyView(wrap, view) {
		var grid = wrap.querySelector('.apb-resources-grid');
		if (!grid) return;
		if (view === 'list') {
			grid.classList.add('apb-view--list');
		} else {
			grid.classList.remove('apb-view--list');
		}
		wrap.querySelectorAll('.apb-view-btn').forEach(function (btn) {
			btn.classList.toggle('apb-view-btn--active', btn.dataset.view === view);
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		var saved = localStorage.getItem(STORE_KEY) || 'cards';

		document.querySelectorAll('.apb-resources-wrap').forEach(function (wrap) {
			applyView(wrap, saved);
		});

		document.querySelectorAll('.apb-view-btn').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var view = this.dataset.view;
				var wrap = document.getElementById(this.dataset.target);
				if (!wrap) return;
				localStorage.setItem(STORE_KEY, view);
				applyView(wrap, view);
			});
		});
	});
})();
