(function () {
	'use strict';

	const cfg = window.fcHelper || {};
	const i18n = cfg.i18n || {};
	const root = document.documentElement;
	const defaultSampleSplashUrl = cfg.defaultSampleSplashUrl || '';
	let savedProductsCatalog = Array.isArray(cfg.savedProducts)
		? cfg.savedProducts.slice()
		: [];
	let loadedSavedProductId = '';
	let successNoticeHideTimer = null;

	/** @type {Array<{id:number,title:string,primary:boolean,collection_id:number,collection_title:string,edit_url:string,view_url:string,payload:object|null}>} */
	let selectedLessons = [];
	const MAX_LESSONS = 10;
	let userEditedProductName = false;

	let themeList = Array.isArray(cfg.themes) ? cfg.themes.slice() : [];
	const builtinThemeSlugs = Array.isArray(cfg.builtinThemeSlugs) ? cfg.builtinThemeSlugs : [];
	const THEME_COLOR_KEYS = [
		'primary',
		'bg_mid',
		'bg_gradient_end',
		'accent',
		'gold',
		'gold_dark',
		'text_light',
		'text_muted',
		'bg_cream',
		'bg_warm',
		'border_warm',
		'text_on_light',
		'bg_dark',
	];
	let themeModalIsNew = false;
	let themeModalSelectedSlug = '';

	function $(sel) {
		return document.querySelector(sel);
	}

	function $$(sel) {
		return document.querySelectorAll(sel);
	}

	function getFormData() {
		const form = $('#fc-helper-form');
		if (!form) {
			return new FormData();
		}
		return new FormData(form);
	}

	function bundleOrderedLessons() {
		const primary = [];
		const rest = [];
		for (let i = 0; i < selectedLessons.length; i++) {
			if (selectedLessons[i].primary) {
				primary.push(selectedLessons[i]);
			} else {
				rest.push(selectedLessons[i]);
			}
		}
		return primary.concat(rest);
	}

	function maybeAutofillProductName() {
		const el = $('#fc_product_title');
		if (!el || userEditedProductName) {
			return;
		}
		const ordered = bundleOrderedLessons();
		if (ordered.length === 0) {
			return;
		}
		if (ordered.length === 1 && ordered[0].title) {
			el.value = ordered[0].title;
			return;
		}
		const prim = ordered[0];
		if (prim && prim.title) {
			el.value = String(prim.title).trim() + ' Bundle';
		}
	}

	function syncLessonHiddenFields() {
		const idInp = $('#fc-lesson-id');
		const dataTa = $('#fc-lesson-data');
		const idsJson = $('#fc-lesson-ids-json');
		const dataListTa = $('#fc-lesson-data-list');
		const ordered = bundleOrderedLessons();
		let primary = null;
		for (let i = 0; i < ordered.length; i++) {
			if (ordered[i].primary) {
				primary = ordered[i];
				break;
			}
		}
		if (!primary && ordered.length) {
			primary = ordered[0];
		}
		if (!primary || !primary.payload) {
			if (idInp) idInp.value = '';
			if (dataTa) dataTa.value = '';
			if (idsJson) idsJson.value = '';
			if (dataListTa) dataListTa.value = '';
			return;
		}
		const ids = ordered.map(function (e) {
			return { id: e.id, primary: !!e.primary };
		});
		const payloads = ordered
			.map(function (e) {
				return e.payload;
			})
			.filter(Boolean);
		if (idInp) idInp.value = String(primary.id);
		if (dataTa) {
			try {
				dataTa.value = JSON.stringify(primary.payload);
			} catch (err) {
				dataTa.value = '';
			}
		}
		if (idsJson) {
			try {
				idsJson.value = JSON.stringify(ids);
			} catch (err2) {
				idsJson.value = '';
			}
		}
		if (dataListTa) {
			try {
				dataListTa.value = JSON.stringify(payloads);
			} catch (err3) {
				dataListTa.value = '';
			}
		}
	}

	function getThemeInput() {
		return $('#fc_theme_input');
	}

	function getTheme() {
		const inp = getThemeInput();
		return inp && inp.value ? inp.value : 'dark_gold';
	}

	function hexToRgbCsv(hex) {
		let h = String(hex || '').replace(/^#/, '');
		if (h.length === 3) {
			h = h[0] + h[0] + h[1] + h[1] + h[2] + h[2];
		}
		if (h.length !== 6 || /[^0-9a-f]/i.test(h)) {
			return '201,168,70';
		}
		const r = parseInt(h.slice(0, 2), 16);
		const g = parseInt(h.slice(2, 4), 16);
		const b = parseInt(h.slice(4, 6), 16);
		return r + ',' + g + ',' + b;
	}

	function contrastLabelForBg(bgHex) {
		let hex = String(bgHex || '').replace(/^#/, '');
		if (hex.length === 3) {
			hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
		}
		if (hex.length !== 6 || /[^0-9a-f]/i.test(hex)) {
			return '#ffffff';
		}
		const r = parseInt(hex.slice(0, 2), 16);
		const g = parseInt(hex.slice(2, 4), 16);
		const b = parseInt(hex.slice(4, 6), 16);
		const l = (0.2126 * r + 0.7152 * g + 0.0722 * b) / 255;
		return l > 0.55 ? '#1c1917' : '#ffffff';
	}

	function normalizeHexForInput(hex) {
		let h = String(hex || '').trim();
		if (!h) {
			return '#000000';
		}
		if (h[0] !== '#') {
			h = '#' + h;
		}
		if (h.length === 4 && /^#[0-9a-f]{3}$/i.test(h)) {
			return ('#' + h[1] + h[1] + h[2] + h[2] + h[3] + h[3]).toLowerCase();
		}
		if (/^#[0-9a-f]{6}$/i.test(h)) {
			return h.toLowerCase();
		}
		return '#000000';
	}

	function slugFromThemeName(name) {
		return String(name || '')
			.toLowerCase()
			.trim()
			.replace(/\s+/g, '_')
			.replace(/[^a-z0-9_]/g, '');
	}

	function getThemeRowBySlug(slug) {
		let found = null;
		themeList.forEach(function (t) {
			if (t.slug === slug) {
				found = t;
			}
		});
		return found;
	}

	function reRenderMainSwatches() {
		const wrap = $('.fc-helper-swatches');
		if (!wrap) {
			return;
		}
		const prev = getTheme();
		const exists = themeList.some(function (t) {
			return t.slug === prev;
		});
		const activeSlug = exists ? prev : themeList[0] ? themeList[0].slug : '';
		let html = '';
		themeList.forEach(function (t) {
			const isSel = t.slug === activeSlug;
			const genLab = contrastLabelForBg(t.accent);
			const onPrimary = contrastLabelForBg(t.primary);
			html +=
				'<button type="button" class="fc-helper-swatch' +
				(isSel ? ' is-selected' : '') +
				'" role="listitem" style="--swatch-ring: ' +
				escapeHtml(t.primary) +
				';" data-theme="' +
				escapeHtml(t.slug) +
				'" data-primary="' +
				escapeHtml(t.primary) +
				'" data-accent="' +
				escapeHtml(t.accent) +
				'" data-btn-on-accent="' +
				escapeHtml(genLab) +
				'" data-on-primary="' +
				escapeHtml(onPrimary) +
				'" aria-pressed="' +
				(isSel ? 'true' : 'false') +
				'" aria-label="' +
				escapeHtml(t.label) +
				'"><span class="fc-helper-swatch__colors" aria-hidden="true"><span class="fc-helper-swatch__chip" style="background-color: ' +
				escapeHtml(t.primary) +
				'"></span><span class="fc-helper-swatch__chip" style="background-color: ' +
				escapeHtml(t.accent) +
				'"></span></span><span class="fc-helper-swatch__label">' +
				escapeHtml(t.label) +
				'</span></button>';
		});
		wrap.innerHTML = html;
		const hidden = getThemeInput();
		if (hidden && activeSlug) {
			hidden.value = activeSlug;
		}
		bindSwatches();
	}

	function mergeThemesFromServer(list) {
		themeList = Array.isArray(list) ? list.slice() : [];
		cfg.themes = themeList;
		reRenderMainSwatches();
	}

	function applyThemeToRoot(btn) {
		if (!btn) return;
		const primary = btn.getAttribute('data-primary');
		const accent = btn.getAttribute('data-accent');
		const genLab = btn.getAttribute('data-btn-on-accent');
		const onPrimary = btn.getAttribute('data-on-primary');
		if (primary) root.style.setProperty('--fc-primary', primary);
		if (accent) {
			root.style.setProperty('--fc-accent', accent);
			root.style.setProperty('--fc-accent-rgb', hexToRgbCsv(accent));
		}
		if (genLab) root.style.setProperty('--fc-generate-label', genLab);
		if (onPrimary) root.style.setProperty('--fc-on-primary', onPrimary);
	}

	function bindSwatches() {
		const swatches = $$('.fc-helper-swatch');
		const hidden = getThemeInput();
		swatches.forEach(function (btn) {
			btn.addEventListener('click', function () {
				swatches.forEach(function (b) {
					b.classList.remove('is-selected');
					b.setAttribute('aria-pressed', 'false');
				});
				btn.classList.add('is-selected');
				btn.setAttribute('aria-pressed', 'true');
				const slug = btn.getAttribute('data-theme');
				if (hidden && slug) {
					hidden.value = slug;
				}
				applyThemeToRoot(btn);
			});
		});
		const sel = $('.fc-helper-swatch.is-selected');
		if (sel) {
			applyThemeToRoot(sel);
		}
	}

	function formatTimestamp() {
		const d = new Date();
		const p = function (n) {
			return String(n).padStart(2, '0');
		};
		return (
			d.getFullYear() +
			'-' +
			p(d.getMonth() + 1) +
			'-' +
			p(d.getDate()) +
			' ' +
			p(d.getHours()) +
			':' +
			p(d.getMinutes()) +
			':' +
			p(d.getSeconds())
		);
	}

	function formatDebugBlock(action, payload, errorMessage) {
		const u = payload.usage || {};
		const pt = u.prompt_tokens != null ? u.prompt_tokens : '—';
		const ct = u.completion_tokens != null ? u.completion_tokens : '—';
		const tt = u.total_tokens != null ? u.total_tokens : '—';
		const st = payload.http_status != null ? payload.http_status : '—';
		const rt =
			payload.response_time != null ? String(payload.response_time) + 'ms' : '—';
		const model = payload.model || 'gpt-4o';
		const err =
			errorMessage && String(errorMessage).trim() !== ''
				? errorMessage
				: 'none';
		return [
			'[' + formatTimestamp() + '] Action: ' + action,
			'Model: ' + model,
			'Status: ' + st,
			'Prompt tokens: ' + pt,
			'Completion tokens: ' + ct,
			'Total tokens: ' + tt,
			'Response time: ' + rt,
			'Error: ' + err,
			'',
		].join('\n');
	}

	function fillPromptSent(promptSent) {
		const ta = $('#fc_helper_prompt_json');
		if (!ta) return;
		if (promptSent == null) {
			ta.value = '';
			return;
		}
		try {
			ta.value = JSON.stringify(promptSent, null, 2);
		} catch (e) {
			ta.value = String(promptSent);
		}
	}

	function writeDebug(action, payload, errorMessage, append) {
		const ta = $('#fc_helper_debug');
		if (!ta) return;
		const block = formatDebugBlock(action, payload, errorMessage);
		if (append && ta.value) {
			ta.value = ta.value.trimEnd() + '\n\n' + block;
		} else {
			ta.value = block;
		}
	}

	function revealWorkspace() {
		const main = $('#fc-helper-output-main');
		const tabs = $('#fc-helper-tabs');
		const panels = $('#fc-helper-tab-panels');
		if (main) main.classList.add('has-output');
		if (tabs) tabs.hidden = false;
		if (panels) panels.hidden = false;
	}

	function selectTab(name) {
		$$('.fc-helper-tab').forEach(function (btn) {
			const on = btn.getAttribute('data-tab') === name;
			btn.classList.toggle('is-active', on);
			btn.setAttribute('aria-selected', on ? 'true' : 'false');
		});
		$$('.fc-helper-tab-panel').forEach(function (panel) {
			const on = panel.getAttribute('data-panel') === name;
			panel.classList.toggle('is-active', on);
		});
	}

	function bindTabs() {
		$$('.fc-helper-tab').forEach(function (btn) {
			btn.addEventListener('click', function () {
				const name = btn.getAttribute('data-tab');
				if (name) selectTab(name);
			});
		});
	}

	function revealRevision() {
		const rev = $('#fc-helper-revision');
		if (rev) {
			rev.removeAttribute('hidden');
		}
	}

	function setLoading(on) {
		const overlay = $('#fc-helper-spinner');
		const gen = $('#fc-helper-generate');
		const rev = $('#fc-helper-revise');
		if (overlay) overlay.hidden = !on;
		if (gen) {
			gen.disabled = on;
			gen.classList.toggle('is-loading', !!on);
			const icon = gen.querySelector('.fc-helper-generate__icon');
			const spin = gen.querySelector('.fc-helper-generate__spin');
			if (icon) icon.toggleAttribute('hidden', !!on);
			if (spin) spin.toggleAttribute('hidden', !on);
		}
		if (rev) rev.disabled = on;
	}

	function setOutput(html) {
		const ta = $('#fc_helper_output');
		if (ta) ta.value = html || '';
		revealWorkspace();
		revealRevision();
		updateStats();
	}

	function updateStats() {
		const ta = $('#fc_helper_output');
		const countEl = $('#fc-helper-count');
		const tabCount = $('#fc-tab-count');
		const text = ta && ta.value ? ta.value : '';
		const chars = text.length;
		const word = i18n.characters || 'characters';
		const label = chars.toLocaleString() + ' ' + word;
		if (countEl) countEl.textContent = label;
		if (tabCount) {
			tabCount.textContent = chars ? ' · ' + label : '';
		}
	}

	function showNotice(message, isError) {
		let n = $('.fc-helper-inline-notice');
		if (!n) {
			n = document.createElement('div');
			n.className = 'notice fc-helper-inline-notice';
			const app = $('.fc-helper-app');
			const wrap = $('.fc-helper-wrap');
			if (app && app.parentNode) {
				app.parentNode.insertBefore(n, app);
			} else if (wrap) {
				wrap.appendChild(n);
			} else {
				return;
			}
		}
		if (successNoticeHideTimer) {
			clearTimeout(successNoticeHideTimer);
			successNoticeHideTimer = null;
		}
		n.removeAttribute('hidden');
		n.style.display = 'block';
		n.style.transition = '';
		n.style.opacity = '';
		n.classList.remove('notice-error', 'notice-success');
		n.classList.add(isError ? 'notice-error' : 'notice-success');
		n.innerHTML = '<p>' + escapeHtml(message) + '</p>';
		if (!isError) {
			successNoticeHideTimer = setTimeout(function () {
				successNoticeHideTimer = null;
				var notice = document.querySelector('.fc-helper-inline-notice.notice-success');
				if (!notice) return;
				notice.style.transition = 'opacity 0.4s ease';
				notice.style.opacity = '0';
				setTimeout(function () {
					notice.hidden = true;
					notice.style.display = 'none';
					notice.style.opacity = '';
					notice.style.transition = '';
				}, 400);
			}, 4000);
		}
	}

	function escapeHtml(s) {
		const d = document.createElement('div');
		d.textContent = s;
		return d.innerHTML;
	}

	function ajax(action, body) {
		const fd = body instanceof FormData ? body : new FormData();
		fd.set('action', action);
		fd.set('nonce', cfg.nonce || '');
		return fetch(cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: fd,
		}).then(function (res) {
			return res.json().catch(function () {
				return { success: false, data: { message: i18n.error } };
			});
		});
	}

	function handleGenerateResponse(raw) {
		const d = raw && raw.data ? raw.data : {};
		const ok = !!(raw && raw.success);
		const msg = d.message || i18n.error;

		if (d.prompt_sent) {
			fillPromptSent(d.prompt_sent);
		}
		revealWorkspace();

		if (ok) {
			const generatedHtml = d.html || '';
			setOutput(generatedHtml);
			writeDebug(
				'generate',
				{
					model: d.model,
					usage: d.usage,
					response_time: d.response_time,
					http_status: d.http_status,
				},
				null,
				false
			);
			selectTab('output');
			const inline = $('.fc-helper-inline-notice');
			if (inline) inline.style.display = 'none';
		} else {
			showNotice(msg, true);
			writeDebug(
				'generate',
				{
					model: d.model,
					usage: d.usage,
					response_time: d.response_time,
					http_status: d.http_status,
				},
				msg,
				false
			);
		}
	}

	function handleReviseResponse(raw) {
		const d = raw && raw.data ? raw.data : {};
		const ok = !!(raw && raw.success);
		const msg = d.message || i18n.error;

		if (d.prompt_sent) {
			fillPromptSent(d.prompt_sent);
		}
		revealWorkspace();

		if (ok) {
			setOutput(d.html || '');
			writeDebug(
				'revise',
				{
					model: d.model,
					usage: d.usage,
					response_time: d.response_time,
					http_status: d.http_status,
				},
				null,
				true
			);
			selectTab('output');
			const inline = $('.fc-helper-inline-notice');
			if (inline) inline.style.display = 'none';
		} else {
			showNotice(msg, true);
			writeDebug(
				'revise',
				{
					model: d.model,
					usage: d.usage,
					response_time: d.response_time,
					http_status: d.http_status,
				},
				msg,
				true
			);
		}
	}

	async function onGenerate() {
		setLoading(true);
		try {
			syncLessonHiddenFields();
			const productNameEl = $('#fc_product_title');
			if (!productNameEl || !String(productNameEl.value || '').trim()) {
				showNotice(
					i18n.productNameRequired || 'Please enter a product name.',
					true
				);
				return;
			}
			const fd = getFormData();
			const data = await ajax('fc_helper_generate', fd);
			handleGenerateResponse(data);
		} catch (e) {
			showNotice(i18n.error, true);
		} finally {
			setLoading(false);
		}
	}

	async function onRevise() {
		const ta = $('#fc_helper_output');
		const rev = $('#fc_revision_request');
		if (!ta || !rev) return;
		const fd = new FormData();
		fd.set('html', ta.value);
		fd.set('revision_request', rev.value);
		fd.set('theme', getTheme());
		setLoading(true);
		try {
			const data = await ajax('fc_helper_revise', fd);
			handleReviseResponse(data);
		} catch (e) {
			showNotice(i18n.error, true);
		} finally {
			setLoading(false);
		}
	}

	function flashCopyTop(btn) {
		if (!btn) return;
		const label = i18n.copied || 'Copied!';
		if (!btn.dataset.fcOrigHtml) {
			btn.dataset.fcOrigHtml = btn.innerHTML;
		}
		const orig = btn.dataset.fcOrigHtml;
		btn.innerHTML =
			'<span class="fc-helper-btn__text">' + escapeHtml(label) + '</span>';
		setTimeout(function () {
			btn.innerHTML = orig;
		}, 1800);
	}

	function flashCopyInline(btn) {
		if (!btn) return;
		const copyIcon = btn.querySelector('.fc-helper-btn__icon--copy');
		const checkIcon = btn.querySelector('.fc-helper-btn__icon--check');
		const textEl = btn.querySelector('.fc-helper-btn__text');
		const label = i18n.copied || 'Copied!';
		if (!textEl) return;
		if (!btn.dataset.fcOrigText) {
			btn.dataset.fcOrigText = textEl.textContent;
		}
		const origText = btn.dataset.fcOrigText;
		if (copyIcon) copyIcon.hidden = true;
		if (checkIcon) checkIcon.hidden = false;
		textEl.textContent = label;
		setTimeout(function () {
			if (copyIcon) copyIcon.hidden = false;
			if (checkIcon) checkIcon.hidden = true;
			textEl.textContent = origText;
		}, 1800);
	}

	function copyOutput(triggerBtn) {
		const ta = $('#fc_helper_output');
		if (!ta) return;
		const text = ta.value;
		if (!text) return;
		const isInline = triggerBtn && triggerBtn.id === 'fc-helper-copy-inline';

		function done() {
			if (isInline) {
				flashCopyInline(triggerBtn);
			} else if (triggerBtn) {
				flashCopyTop(triggerBtn);
			}
		}

		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(text).then(done);
			return;
		}
		ta.removeAttribute('readonly');
		ta.select();
		ta.setSelectionRange(0, text.length);
		try {
			document.execCommand('copy');
			done();
		} finally {
			ta.removeAttribute('readonly');
		}
	}

	function syncSplashUi() {
		const selectBtn = $('#fc-helper-splash-select');
		const hidden = $('#fc_sample_video_splash_url');
		const preview = $('#fc-helper-splash-preview');
		const img = $('#fc-helper-splash-preview-img');
		if (!selectBtn || !hidden || !preview || !img) {
			return;
		}
		const url = (hidden.value || '').trim();
		if (url) {
			img.src = url;
			img.alt = '';
			preview.hidden = false;
			selectBtn.hidden = true;
		} else {
			img.removeAttribute('src');
			img.alt = '';
			preview.hidden = true;
			selectBtn.hidden = false;
		}
	}

	function bindSplashMediaPicker() {
		const selectBtn = $('#fc-helper-splash-select');
		const removeBtn = $('#fc-helper-splash-remove');
		const hidden = $('#fc_sample_video_splash_url');
		if (!selectBtn || !hidden) {
			return;
		}
		if (!(hidden.value || '').trim() && defaultSampleSplashUrl) {
			hidden.value = defaultSampleSplashUrl;
		}
		if (typeof wp === 'undefined' || !wp.media) {
			syncSplashUi();
			return;
		}
		let frame = null;

		selectBtn.addEventListener('click', function (e) {
			e.preventDefault();
			if (!frame) {
				frame = wp.media({
					title: i18n.splashFrameTit || 'Select splash image',
					button: { text: i18n.splashFrameBtn || 'Use this image' },
					multiple: false,
					library: { type: 'image' },
				});
				frame.on('select', function () {
					const att = frame.state().get('selection').first().toJSON();
					hidden.value = att.url || '';
					syncSplashUi();
				});
			}
			frame.open();
		});

		if (removeBtn) {
			removeBtn.addEventListener('click', function (e) {
				e.preventDefault();
				hidden.value = '';
				syncSplashUi();
			});
		}

		syncSplashUi();
	}

	function mergeSavedProductsResponse(raw) {
		const d = raw && raw.data ? raw.data : {};
		if (Array.isArray(d.products)) {
			savedProductsCatalog = d.products;
			rebuildSavedProductsSelect();
		}
	}

	function setSavedProductLoadedUi(on) {
		const toolbar = $('.fc-helper-saved-toolbar');
		const updateBtn = $('#fc-helper-update-product');
		if (toolbar) {
			toolbar.classList.toggle('has-loaded-product', !!on);
		}
		if (updateBtn) {
			updateBtn.hidden = !on;
		}
	}

	function rebuildSavedProductsSelect() {
		const sel = $('#fc-helper-saved-select');
		const delBtn = $('#fc-helper-saved-delete');
		if (!sel) return;
		const placeholder = i18n.selectSaved || '— Select —';
		const prev = sel.value;
		sel.innerHTML = '';
		const opt0 = document.createElement('option');
		opt0.value = '';
		opt0.textContent = placeholder;
		sel.appendChild(opt0);
		const sorted = savedProductsCatalog.slice().sort(function (a, b) {
			return (a.name || '').localeCompare(b.name || '', undefined, {
				sensitivity: 'base',
			});
		});
		sorted.forEach(function (p) {
			const o = document.createElement('option');
			o.value = p.id;
			o.textContent = p.name;
			sel.appendChild(o);
		});
		if (prev && sorted.some(function (p) { return p.id === prev; })) {
			sel.value = prev;
		} else {
			sel.value = '';
		}
		if (delBtn) delBtn.disabled = !sel.value;
	}

	function applyThemeSlug(slug) {
		const hidden = getThemeInput();
		const swatches = $$('.fc-helper-swatch');
		const key = slug && String(slug).trim() ? String(slug).trim() : 'dark_gold';
		if (hidden) hidden.value = key;
		let matched = false;
		swatches.forEach(function (btn) {
			const on = btn.getAttribute('data-theme') === key;
			btn.classList.toggle('is-selected', on);
			btn.setAttribute('aria-pressed', on ? 'true' : 'false');
			if (on) {
				matched = true;
				applyThemeToRoot(btn);
			}
		});
		if (!matched && swatches.length) {
			const first = swatches[0];
			const fallback = first.getAttribute('data-theme') || 'dark_gold';
			if (hidden) hidden.value = fallback;
			swatches.forEach(function (btn) {
				const on = btn === first;
				btn.classList.toggle('is-selected', on);
				btn.setAttribute('aria-pressed', on ? 'true' : 'false');
				if (on) applyThemeToRoot(btn);
			});
		}
	}

	function startNewSession() {
		if (
			!window.confirm(
				'Start a new session? Any unsaved changes will be lost.'
			)
		) {
			return;
		}
		const sel = $('#fc-helper-saved-select');
		const delBtn = $('#fc-helper-saved-delete');
		if (sel) sel.value = '';
		if (delBtn) delBtn.disabled = true;
		loadedSavedProductId = '';
		setSavedProductLoadedUi(false);
		userEditedProductName = false;

		const search = $('#fc-lesson-search');
		if (search) search.value = '';
		clearLessonSelection();

		const titleInp = $('#fc_product_title');
		const descTa = $('#fc_description');
		const vidInp = $('#fc_sample_video_url');
		const notesTa = $('#fc_additional_notes');
		const revTa = $('#fc_revision_request');
		if (titleInp) titleInp.value = '';
		if (descTa) descTa.value = '';
		if (vidInp) vidInp.value = '';
		if (notesTa) notesTa.value = '';
		if (revTa) revTa.value = '';

		const splashHidden = $('#fc_sample_video_splash_url');
		if (splashHidden && defaultSampleSplashUrl) {
			splashHidden.value = defaultSampleSplashUrl;
		}
		syncSplashUi();

		const out = $('#fc_helper_output');
		if (out) out.value = '';
		fillPromptSent(null);
		const dbg = $('#fc_helper_debug');
		if (dbg) dbg.value = '';

		const revPanel = $('#fc-helper-revision');
		if (revPanel) revPanel.setAttribute('hidden', '');

		const main = $('#fc-helper-output-main');
		const tabs = $('#fc-helper-tabs');
		const panels = $('#fc-helper-tab-panels');
		if (main) main.classList.remove('has-output');
		if (tabs) tabs.hidden = true;
		if (panels) panels.hidden = true;
		selectTab('output');
		updateStats();

		applyThemeSlug('dark_gold');

		window.scrollTo({ top: 0, behavior: 'smooth' });
	}

	async function fetchLessonPayload(lessonId) {
		const fd = new FormData();
		fd.set('lesson_id', String(lessonId));
		const raw = await ajax('fc_helper_get_lesson', fd);
		const d = raw && raw.data ? raw.data : {};
		if (raw && raw.success && d.lesson && d.chapters) {
			return { lesson: d.lesson, chapters: d.chapters };
		}
		return null;
	}

	function renderPrimaryChapters() {
		let primary = null;
		for (let i = 0; i < selectedLessons.length; i++) {
			if (selectedLessons[i].primary) {
				primary = selectedLessons[i];
				break;
			}
		}
		if (!primary && selectedLessons.length) {
			primary = selectedLessons[0];
		}
		const chEl = $('#fc-lesson-selected-chapters');
		if (!chEl) return;
		if (!primary || !primary.payload) {
			chEl.innerHTML = '';
			return;
		}
		const pl = primary.payload;
		const chapters = pl.chapters || [];
		const totalSeconds = chapters.reduce(function (sum, ch) {
			return sum + (parseInt(ch.duration_seconds, 10) || 0);
		}, 0);
		const totalFormatted = formatChapterDurationHuman(totalSeconds);
		const rows = chapters.map(function (ch) {
			const st = ch.start_time || '00:00:00';
			const tit = ch.title || '';
			const dur = formatChapterDurationHuman(ch.duration_seconds);
			const line =
				'[' + st + '] ' + tit + ' (duration: ' + dur + ')';
			return (
				'<div class="fc-helper-chapter-row">' +
				escapeHtml(line) +
				'</div>'
			);
		});
		const totalHtml =
			'<div class="fc-helper-chapter-total">Total Course Time: <strong>' +
			escapeHtml(totalFormatted) +
			'</strong></div>';
		chEl.innerHTML = rows.join('') + totalHtml;
	}

	function renderSelectedLessons() {
		const panel = $('#fc-lesson-selected');
		const emptyEl = $('#fc-lesson-selected-empty');
		const ul = $('#fc-selected-lessons');
		const addBtn = $('#fc-add-lesson-btn');
		const countEl = $('#fc-lesson-count');
		const maxMsg = $('#fc-lesson-max-msg');
		const n = selectedLessons.length;
		if (panel) {
			panel.removeAttribute('hidden');
		}
		if (emptyEl) {
			emptyEl.hidden = n > 0;
		}
		if (countEl) {
			countEl.textContent =
				n +
				' / ' +
				MAX_LESSONS +
				' ' +
				(i18n.lessonsSelected || 'selected');
		}
		if (addBtn) {
			addBtn.hidden = n >= MAX_LESSONS;
		}
		if (maxMsg) {
			maxMsg.hidden = n < MAX_LESSONS;
		}
		if (!ul) return;
		ul.innerHTML = '';
		const baseEdit = 'admin.php?page=academy-manager-lessons&action=edit&id=';
		bundleOrderedLessons().forEach(function (entry) {
			const li = document.createElement('li');
			li.className =
				'fc-selected-lessons__item' + (entry.primary ? ' is-primary' : '');
			li.setAttribute('data-lesson-id', String(entry.id));
			const editUrl =
				entry.edit_url || baseEdit + encodeURIComponent(String(entry.id));
			const viewUrl = entry.view_url || '';
			const titleEsc = escapeHtml(entry.title || '');
			const coll =
				entry.collection_title && String(entry.collection_title).trim()
					? escapeHtml(String(entry.collection_title).trim())
					: '';
			const collLine = coll
				? '<span class="fc-selected-lessons__collection">' +
				  coll +
				  '</span>'
				: '';
			const starBtn =
				'<button type="button" class="fc-selected-lessons__star' +
				(entry.primary ? ' is-primary' : '') +
				'" data-action="primary" data-lesson-id="' +
				entry.id +
				'" aria-label="' +
				escapeHtml(
					entry.primary
						? i18n.primaryLesson || 'Primary lesson'
						: i18n.makePrimary || 'Make primary'
				) +
				'">' +
				(entry.primary
					? '<span class="fc-selected-lessons__star-icon" aria-hidden="true">★</span>'
					: '<span class="fc-selected-lessons__star-icon fc-selected-lessons__star-icon--outline" aria-hidden="true">☆</span>') +
				'</button>';
			const primaryLabel = entry.primary
				? '<span class="fc-selected-lessons__badge">' +
				  escapeHtml(i18n.primaryLabel || 'Primary') +
				  '</span>'
				: '';
			const viewBtn = viewUrl
				? '<a href="' +
				  escapeHtml(viewUrl) +
				  '" target="_blank" rel="noopener" class="fc-helper-btn fc-helper-btn--secondary fc-helper-btn--sm">' +
				  escapeHtml(i18n.viewLesson || 'View') +
				  '</a>'
				: '';
			const editBtn =
				'<a href="' +
				escapeHtml(editUrl) +
				'" target="_blank" rel="noopener" class="fc-helper-btn fc-helper-btn--secondary fc-helper-btn--sm">' +
				escapeHtml(i18n.editLesson || 'Edit') +
				'</a>';
			const makePrimaryBtn = !entry.primary
				? '<button type="button" class="fc-helper-btn fc-helper-btn--outline fc-helper-btn--sm" data-action="make-primary" data-lesson-id="' +
				  entry.id +
				  '">' +
				  escapeHtml(i18n.makePrimaryBtn || 'Make Primary') +
				  '</button>'
				: '';
			const removeBtn =
				'<button type="button" class="fc-selected-lessons__remove fc-helper-btn fc-helper-btn--secondary fc-helper-btn--sm" data-action="remove" data-lesson-id="' +
				entry.id +
				'" aria-label="' +
				escapeHtml(i18n.removeLesson || 'Remove lesson') +
				'">×</button>';
			li.innerHTML =
				'<div class="fc-selected-lessons__row">' +
				starBtn +
				'<div class="fc-selected-lessons__main">' +
				'<div class="fc-selected-lessons__title-line">' +
				primaryLabel +
				'<strong class="fc-selected-lessons__title">' +
				titleEsc +
				'</strong>' +
				'</div>' +
				collLine +
				'</div>' +
				'<div class="fc-selected-lessons__actions">' +
				viewBtn +
				editBtn +
				makePrimaryBtn +
				removeBtn +
				'</div>' +
				'</div>';
			ul.appendChild(li);
		});
		if (!ul._fcBound) {
			ul._fcBound = true;
			ul.addEventListener('click', function (e) {
				const btn = e.target && e.target.closest ? e.target.closest('[data-action]') : null;
				if (!btn) return;
				const action = btn.getAttribute('data-action');
				const lid = btn.getAttribute('data-lesson-id');
				const idNum = lid ? parseInt(lid, 10) : 0;
				if (!idNum) return;
				if (action === 'remove') {
					removeLessonFromSelection(idNum);
				} else if (action === 'primary' || action === 'make-primary') {
					makeLessonPrimary(idNum);
				}
			});
		}
		renderPrimaryChapters();
		syncLessonHiddenFields();
		let primId = '';
		for (let j = 0; j < selectedLessons.length; j++) {
			if (selectedLessons[j].primary) {
				primId = String(selectedLessons[j].id);
				break;
			}
		}
		updateEditProductLink(primId);
		maybeAutofillProductName();
	}

	function makeLessonPrimary(lessonId) {
		let entry = null;
		selectedLessons.forEach(function (e) {
			e.primary = e.id === lessonId;
			if (e.id === lessonId) {
				entry = e;
			}
		});
		if (entry && entry.payload && entry.payload.lesson) {
			populateProductFieldsFromAlmLesson(entry.payload.lesson);
		}
		renderSelectedLessons();
	}

	function removeLessonFromSelection(lessonId) {
		const idx = selectedLessons.findIndex(function (e) {
			return e.id === lessonId;
		});
		if (idx < 0) return;
		const wasPrimary = selectedLessons[idx].primary;
		selectedLessons.splice(idx, 1);
		if (wasPrimary && selectedLessons.length) {
			selectedLessons[0].primary = true;
			if (selectedLessons[0].payload && selectedLessons[0].payload.lesson) {
				populateProductFieldsFromAlmLesson(selectedLessons[0].payload.lesson);
			}
		}
		renderSelectedLessons();
	}

	async function addLessonFromSearch(lessonId) {
		const nid = parseInt(lessonId, 10);
		if (!nid) return;
		if (selectedLessons.some(function (e) {
			return e.id === nid;
		})) {
			return;
		}
		if (selectedLessons.length >= MAX_LESSONS) {
			showNotice(i18n.maxLessons || 'Maximum 10 lessons.', true);
			return;
		}
		const wasEmpty = selectedLessons.length === 0;
		const fd = new FormData();
		fd.set('lesson_id', String(nid));
		try {
			const raw = await ajax('fc_helper_get_lesson', fd);
			const d = raw && raw.data ? raw.data : {};
			if (!(raw && raw.success && d.lesson && d.chapters)) {
				const msg =
					d && d.message ? d.message : i18n.error || 'Error';
				showNotice(msg, true);
				return;
			}
			const lesson = d.lesson;
			const payload = { lesson: lesson, chapters: d.chapters };
			const entry = {
				id: nid,
				title: lesson.title || '',
				primary: wasEmpty,
				collection_id: lesson.collection_id || 0,
				collection_title: lesson.collection_title || '',
				edit_url: lesson.edit_url || '',
				view_url: lesson.view_url || '',
				payload: payload,
			};
			if (!entry.edit_url) {
				entry.edit_url =
					'admin.php?page=academy-manager-lessons&action=edit&id=' + nid;
			}
			selectedLessons.push(entry);
			if (wasEmpty) {
				populateProductFieldsFromAlmLesson(lesson);
			}
			hideLessonResults();
			renderSelectedLessons();
		} catch (err) {
			showNotice(i18n.error, true);
		}
	}

	async function applySavedProductData(data) {
		const d = data || {};
		const title = $('#fc_product_title');
		const desc = $('#fc_description');
		const vid = $('#fc_sample_video_url');
		const splash = $('#fc_sample_video_splash_url');
		const notes = $('#fc_additional_notes');
		if (title) title.value = d.product_title || '';
		userEditedProductName = String(d.product_title || '').trim() !== '';
		if (desc) desc.value = d.description || '';
		if (vid) vid.value = d.sample_video_url || '';
		if (splash) splash.value = d.sample_video_splash_url || '';
		if (notes) notes.value = d.additional_notes || '';
		syncSplashUi();
		applyThemeSlug(d.theme);

		let rows = Array.isArray(d.lesson_ids_display)
			? d.lesson_ids_display.slice()
			: [];
		if (!rows.length && Array.isArray(d.lesson_ids)) {
			rows = d.lesson_ids.map(function (r) {
				return {
					id: r.id,
					title: '',
					collection_id: 0,
					collection_title: '',
					edit_url:
						'admin.php?page=academy-manager-lessons&action=edit&id=' +
						encodeURIComponent(String(r.id)),
					view_url: '',
					primary: !!r.primary,
				};
			});
		}
		let lessonDataList = null;
		if (d.lesson_data_list) {
			if (typeof d.lesson_data_list === 'string' && d.lesson_data_list.trim()) {
				try {
					lessonDataList = JSON.parse(d.lesson_data_list);
				} catch (err) {
					lessonDataList = null;
				}
			} else if (Array.isArray(d.lesson_data_list)) {
				lessonDataList = d.lesson_data_list;
			}
		}
		let lessonPayload = null;
		if (d.lesson_data) {
			if (typeof d.lesson_data === 'string' && d.lesson_data.trim()) {
				try {
					lessonPayload = JSON.parse(d.lesson_data);
				} catch (err2) {
					lessonPayload = null;
				}
			} else if (typeof d.lesson_data === 'object') {
				lessonPayload = d.lesson_data;
			}
		}
		if (rows.length) {
			selectedLessons = [];
			for (let i = 0; i < rows.length; i++) {
				const row = rows[i];
				const lid = row.id;
				let payload = null;
				if (Array.isArray(lessonDataList)) {
					for (let j = 0; j < lessonDataList.length; j++) {
						if (
							lessonDataList[j].lesson &&
							lessonDataList[j].lesson.id === lid
						) {
							payload = lessonDataList[j];
							break;
						}
					}
				}
				if (!payload && lessonPayload && lessonPayload.lesson && lessonPayload.lesson.id === lid) {
					payload = lessonPayload;
				}
				if (!payload) {
					payload = await fetchLessonPayload(lid);
				}
				selectedLessons.push({
					id: lid,
					title:
						row.title ||
						(payload && payload.lesson && payload.lesson.title) ||
						'',
					primary: !!row.primary,
					collection_id: row.collection_id || 0,
					collection_title: row.collection_title || '',
					edit_url: row.edit_url || '',
					view_url: row.view_url || '',
					payload: payload,
				});
			}
			renderSelectedLessons();
			return;
		}
		if (lessonPayload && lessonPayload.lesson) {
			selectedLessons = [];
			const lid = lessonPayload.lesson.id;
			selectedLessons.push({
				id: lid,
				title: lessonPayload.lesson.title || '',
				primary: true,
				collection_id: lessonPayload.lesson.collection_id || 0,
				collection_title: lessonPayload.lesson.collection_title || '',
				edit_url: '',
				view_url: '',
				payload: lessonPayload,
			});
			renderSelectedLessons();
			return;
		}
		clearLessonSelection();
	}

	async function onSavedSelectChange() {
		const sel = $('#fc-helper-saved-select');
		const delBtn = $('#fc-helper-saved-delete');
		if (!sel) return;
		const id = sel.value;
		if (delBtn) delBtn.disabled = !id;
		if (!id) {
			loadedSavedProductId = '';
			setSavedProductLoadedUi(false);
			return;
		}
		let product = null;
		for (let i = 0; i < savedProductsCatalog.length; i++) {
			if (savedProductsCatalog[i].id === id) {
				product = savedProductsCatalog[i];
				break;
			}
		}
		if (!product) return;
		await applySavedProductData(product.data);
		loadedSavedProductId = product.id;
		setSavedProductLoadedUi(true);

		const savedHtml =
			product.data && product.data.html_output ? String(product.data.html_output) : '';
		const outputTa = $('#fc_helper_output');
		const outputMain = $('#fc-helper-output-main');
		const countEl = $('#fc-tab-count');
		if (outputTa) {
			outputTa.value = savedHtml;
			if (outputMain) {
				if (savedHtml) {
					outputMain.classList.add('has-output');
				} else {
					outputMain.classList.remove('has-output');
				}
			}
			if (countEl) {
				countEl.textContent = savedHtml
					? savedHtml.length.toLocaleString() +
					  ' ' +
					  (i18n.characters || 'characters')
					: '';
			}
		}
		updateStats();
		if (savedHtml) {
			revealWorkspace();
			revealRevision();
			selectTab('output');
		}
	}

	async function onUpdateProduct() {
		const btn = $('#fc-helper-update-product');
		if (!loadedSavedProductId) return;
		if (btn) btn.disabled = true;
		try {
			syncLessonHiddenFields();
			const titleEl = $('#fc_product_title');
			if (!titleEl || !String(titleEl.value || '').trim()) {
				showNotice(
					i18n.productNameRequired || 'Please enter a product name.',
					true
				);
				return;
			}
			const fd = getFormData();
			const outputTa = $('#fc_helper_output');
			fd.set('html_output', outputTa ? (outputTa.value || '') : '');
			fd.set('product_id', loadedSavedProductId);
			const raw = await ajax('fc_helper_save_product', fd);
			if (raw && raw.success) {
				showNotice(i18n.productUpdatedHtml || 'HTML saved to product.', false);
				mergeSavedProductsResponse(raw);
			} else {
				const msg =
					raw && raw.data && raw.data.message ? raw.data.message : i18n.error;
				showNotice(msg, true);
			}
		} catch (e) {
			showNotice(i18n.error, true);
		} finally {
			if (btn) btn.disabled = false;
		}
	}

	async function onSaveProduct() {
		const saveBtn = $('#fc-helper-save-product');
		if (saveBtn) saveBtn.disabled = true;
		try {
			syncLessonHiddenFields();
			const fd = getFormData();
			const outputTa = $('#fc_helper_output');
			fd.set('html_output', outputTa ? (outputTa.value || '') : '');

			const titleEl = $('#fc_product_title');
			if (!titleEl || !String(titleEl.value || '').trim()) {
				showNotice(
					i18n.productNameRequired || 'Please enter a product name.',
					true
				);
				return;
			}
			const defName = titleEl && titleEl.value ? titleEl.value : '';
			const name = window.prompt(
				i18n.saveProductPrompt || 'Name this saved product:',
				defName
			);
			if (name === null) return;
			const trimmed = String(name).trim();
			if (!trimmed) {
				showNotice(i18n.nameRequired || 'Please enter a name.', true);
				return;
			}
			fd.set('product_name', trimmed);
			const raw = await ajax('fc_helper_save_product', fd);
			if (raw && raw.success) {
				showNotice(i18n.productSaved || 'Saved.', false);
				mergeSavedProductsResponse(raw);
			} else {
				const msg =
					raw && raw.data && raw.data.message ? raw.data.message : i18n.error;
				showNotice(msg, true);
			}
		} catch (e) {
			showNotice(i18n.error, true);
		} finally {
			if (saveBtn) saveBtn.disabled = false;
		}
	}

	async function onDeleteSavedProduct() {
		const sel = $('#fc-helper-saved-select');
		const delBtn = $('#fc-helper-saved-delete');
		if (!sel || !sel.value) return;
		const fd = new FormData();
		fd.set('product_id', sel.value);
		if (delBtn) delBtn.disabled = true;
		try {
			const raw = await ajax('fc_helper_delete_product', fd);
			if (raw && raw.success) {
				showNotice(i18n.productDeleted || 'Removed.', false);
				sel.value = '';
				loadedSavedProductId = '';
				setSavedProductLoadedUi(false);
				mergeSavedProductsResponse(raw);
			} else {
				const msg =
					raw && raw.data && raw.data.message ? raw.data.message : i18n.error;
				showNotice(msg, true);
			}
		} catch (e) {
			showNotice(i18n.error, true);
		} finally {
			if (delBtn) delBtn.disabled = !sel.value;
		}
	}

	function formatChapterDurationHuman(sec) {
		const n = Math.max(0, parseInt(sec, 10) || 0);
		const h = Math.floor(n / 3600);
		const m = Math.floor((n % 3600) / 60);
		const s = n % 60;
		if (h > 0) {
			return h + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
		}
		return m + ':' + String(s).padStart(2, '0');
	}

	function hideLessonResults() {
		const results = $('#fc-lesson-results');
		if (results) {
			results.innerHTML = '';
			results.hidden = true;
		}
	}

	function updateEditProductLink(lessonId) {
		var editProductLink = document.getElementById('fc-edit-product-link');
		if (!editProductLink) return;
		if (lessonId) {
			editProductLink.href =
				'admin.php?page=academy-manager-lessons&action=edit&id=' +
				encodeURIComponent(lessonId);
			editProductLink.hidden = false;
		} else {
			editProductLink.hidden = true;
			editProductLink.href = '#';
		}
	}

	function flashAutofilledField(el) {
		if (!el) return;
		el.classList.add('fc-field-autofilled');
		setTimeout(function () {
			el.classList.remove('fc-field-autofilled');
		}, 1500);
	}

	function populateProductFieldsFromAlmLesson(lesson) {
		if (!lesson) return;
		const descTa = $('#fc_description');
		const vidInp = $('#fc_sample_video_url');
		if (descTa) {
			descTa.value = lesson.description != null ? String(lesson.description) : '';
			flashAutofilledField(descTa);
		}
		if (vidInp) {
			const u = lesson.resolved_video_url
				? String(lesson.resolved_video_url).trim()
				: '';
			if (u) {
				vidInp.value = u;
				flashAutofilledField(vidInp);
			}
		}
	}

	function clearLessonSelection() {
		selectedLessons = [];
		const search = $('#fc-lesson-search');
		if (search) search.value = '';
		hideLessonResults();
		renderSelectedLessons();
		syncLessonHiddenFields();
		updateEditProductLink('');
	}

	async function runLessonSearch(query) {
		const q = (query || '').trim();
		const results = $('#fc-lesson-results');
		if (!results) return;
		if (q.length < 2) {
			hideLessonResults();
			return;
		}
		const fd = new FormData();
		fd.set('s', q);
		try {
			const raw = await ajax('fc_helper_search_lessons', fd);
			const d = raw && raw.data ? raw.data : {};
			const list = d.lessons || [];
			if (!raw || !raw.success) {
				hideLessonResults();
				return;
			}
			results.innerHTML = '';
			list.forEach(function (row) {
				const li = document.createElement('li');
				li.setAttribute('role', 'option');
				li.setAttribute('data-lesson-id', String(row.id));
				const already = selectedLessons.some(function (e) {
					return e.id === row.id;
				});
				if (already) {
					li.classList.add('fc-helper-lesson-results__item--added');
				}
				const titleSpan = document.createElement('span');
				titleSpan.className = 'fc-helper-lesson-results__title';
				titleSpan.textContent = row.title || '';
				const collSpan = document.createElement('span');
				collSpan.className = 'fc-helper-lesson-results__collection';
				const ct =
					row.collection_title && String(row.collection_title).trim();
				collSpan.textContent = ct
					? 'Collection: ' + ct
					: 'Collection: —';
				li.appendChild(titleSpan);
				li.appendChild(collSpan);
				if (already) {
					const tag = document.createElement('span');
					tag.className = 'fc-helper-lesson-results__added-tag';
					tag.textContent = i18n.alreadyAdded || 'Already added';
					li.appendChild(tag);
				}
				results.appendChild(li);
			});
			results.hidden = list.length === 0;
		} catch (e) {
			hideLessonResults();
		}
	}

	function bindLessonSearch() {
		const search = $('#fc-lesson-search');
		const results = $('#fc-lesson-results');
		const field = $('.fc-helper-field--lesson-search');
		const addBtn = $('#fc-add-lesson-btn');
		let lessonSearchTimer = null;
		if (!search) return;
		function scheduleSearch() {
			const q = search.value;
			if (lessonSearchTimer) clearTimeout(lessonSearchTimer);
			lessonSearchTimer = setTimeout(function () {
				lessonSearchTimer = null;
				runLessonSearch(q);
			}, 400);
		}
		search.addEventListener('keyup', scheduleSearch);
		search.addEventListener('input', scheduleSearch);
		if (results) {
			results.addEventListener('click', function (e) {
				const li = e.target && e.target.closest ? e.target.closest('li[data-lesson-id]') : null;
				if (!results.hidden && li) {
					if (li.classList.contains('fc-helper-lesson-results__item--added')) {
						return;
					}
					const id = li.getAttribute('data-lesson-id');
					if (id) addLessonFromSearch(id);
				}
			});
		}
		document.addEventListener(
			'mousedown',
			function (e) {
				if (results && results.hidden) return;
				if (
					field &&
					!field.contains(e.target) &&
					(!results || !results.contains(e.target))
				) {
					hideLessonResults();
				}
			},
			true
		);
		if (addBtn) {
			addBtn.addEventListener('click', function () {
				if (search) {
					search.focus();
				}
			});
		}
	}

	function bindThemeManager() {
		const overlay = $('#fc-theme-manager-modal');
		const openBtn = $('#fc-theme-manager-btn');
		const closeBtn = $('#fc-theme-manager-close');
		const doneBtn = $('#fc-theme-done-btn');
		const resetBtn = $('#fc-theme-reset-btn');
		const newBtn = $('#fc-theme-new-btn');
		const saveBtn = $('#fc-theme-save-btn');
		const delBtn = $('#fc-theme-delete-btn');
		const listEl = $('#fc-theme-list');
		const editor = $('#fc-theme-editor');
		const nameInp = $('#fc-theme-name');
		const slugInp = $('#fc-theme-slug');
		const preview = $('#fc-theme-preview');
		const masterColorInp = $('#fc-theme-master-color');
		const masterHexSpan = $('.fc-theme-master-hex');
		const generateColorsBtn = $('#fc-theme-generate-btn');
		const generateErrorEl = $('#fc-theme-generate-error');
		let generateColorsDefaultText = generateColorsBtn ? generateColorsBtn.textContent.trim() : '';

		function openThemeModal() {
			if (!overlay) {
				return;
			}
			overlay.removeAttribute('hidden');
			overlay.setAttribute('aria-hidden', 'false');
			overlay.classList.add('is-open');
			document.body.style.overflow = 'hidden';
		}

		function closeThemeModal() {
			if (!overlay) {
				return;
			}
			overlay.setAttribute('hidden', '');
			overlay.setAttribute('aria-hidden', 'true');
			overlay.classList.remove('is-open');
			document.body.style.overflow = '';
		}

		function getColorFieldValue(key) {
			const inp = $('#fc-theme-c-' + key);
			return inp && inp.value ? normalizeHexForInput(inp.value) : '#000000';
		}

		function setColorFieldValue(key, hex) {
			const inp = $('#fc-theme-c-' + key);
			const row = inp && inp.closest ? inp.closest('.fc-theme-color-row') : null;
			const hexRo = row ? row.querySelector('.fc-theme-color-hex') : null;
			const v = normalizeHexForInput(hex);
			if (inp) {
				inp.value = v;
			}
			if (hexRo) {
				hexRo.value = v;
			}
		}

		function syncMasterHexDisplay() {
			if (masterColorInp && masterHexSpan) {
				masterHexSpan.textContent = normalizeHexForInput(masterColorInp.value);
			}
		}

		function syncMasterFromEditorPrimary() {
			if (masterColorInp) {
				masterColorInp.value = getColorFieldValue('primary');
				syncMasterHexDisplay();
			}
		}

		function updateThemePreviewStrip() {
			if (!preview) {
				return;
			}
			const primary = getColorFieldValue('primary');
			const mid = getColorFieldValue('bg_mid');
			const accent = getColorFieldValue('accent');
			preview.style.background = 'linear-gradient(90deg, ' + primary + ', ' + mid + ')';
			const badge = preview.querySelector('.fc-theme-preview__badge');
			if (badge) {
				badge.style.backgroundColor = accent;
				badge.style.color = contrastLabelForBg(accent);
			}
			const h1 = preview.querySelector('.fc-theme-preview__title');
			if (h1) {
				h1.style.color = '#ffffff';
			}
		}

		function populateEditorFromRow(row) {
			if (!nameInp || !slugInp || !editor) {
				return;
			}
			nameInp.value = row.label || '';
			slugInp.value = row.slug || '';
			THEME_COLOR_KEYS.forEach(function (k) {
				setColorFieldValue(k, row[k] || '#000000');
			});
			editor.removeAttribute('hidden');
			updateThemePreviewStrip();
			syncMasterFromEditorPrimary();
			if (delBtn) {
				const isBuiltIn = builtinThemeSlugs.indexOf(row.slug) >= 0;
				delBtn.hidden = isBuiltIn;
			}
		}

		function clearEditorForNew(baseRow) {
			if (!nameInp || !slugInp || !editor) {
				return;
			}
			themeModalIsNew = true;
			themeModalSelectedSlug = '';
			nameInp.value = 'New Theme';
			slugInp.value = '';
			slugInp.readOnly = false;
			const base = baseRow || getThemeRowBySlug(getTheme()) || (themeList[0] || {});
			THEME_COLOR_KEYS.forEach(function (k) {
				setColorFieldValue(k, base[k] || '#000000');
			});
			editor.removeAttribute('hidden');
			updateThemePreviewStrip();
			syncMasterFromEditorPrimary();
			if (delBtn) {
				delBtn.hidden = true;
			}
			renderModalThemeList('');
		}

		function selectModalTheme(slug) {
			themeModalIsNew = false;
			themeModalSelectedSlug = slug;
			const row = getThemeRowBySlug(slug);
			if (!row) {
				return;
			}
			if (slugInp) {
				slugInp.readOnly = true;
			}
			populateEditorFromRow(row);
			renderModalThemeList(slug);
		}

		function renderModalThemeList(activeSlug) {
			if (!listEl) {
				return;
			}
			listEl.innerHTML = '';
			themeList.forEach(function (t) {
				const li = document.createElement('li');
				li.className = 'fc-theme-list__item';
				li.setAttribute('role', 'listitem');
				if (t.slug === activeSlug) {
					li.classList.add('is-active');
				}
				li.setAttribute('data-slug', t.slug);
				li.innerHTML =
					'<span class="fc-theme-list__swatch" aria-hidden="true"><span class="fc-theme-list__dot" style="background:' +
					escapeHtml(t.primary) +
					'"></span><span class="fc-theme-list__dot" style="background:' +
					escapeHtml(t.accent) +
					'"></span></span><span class="fc-theme-list__label">' +
					escapeHtml(t.label) +
					'</span>';
				li.addEventListener('click', function () {
					selectModalTheme(t.slug);
				});
				listEl.appendChild(li);
			});
		}

		function collectThemePayload() {
			const o = {
				label: nameInp ? nameInp.value.trim() : '',
			};
			THEME_COLOR_KEYS.forEach(function (k) {
				o[k] = getColorFieldValue(k);
			});
			return o;
		}

		function wireColorInputs() {
			THEME_COLOR_KEYS.forEach(function (k) {
				const inp = $('#fc-theme-c-' + k);
				if (!inp) {
					return;
				}
				inp.addEventListener('input', function () {
					const row = inp.closest('.fc-theme-color-row');
					const hexRo = row ? row.querySelector('.fc-theme-color-hex') : null;
					if (hexRo) {
						hexRo.value = normalizeHexForInput(inp.value);
					}
					updateThemePreviewStrip();
				});
			});
		}

		wireColorInputs();

		if (masterColorInp) {
			masterColorInp.addEventListener('input', syncMasterHexDisplay);
		}

		if (generateColorsBtn) {
			generateColorsBtn.addEventListener('click', function () {
				if (!masterColorInp) {
					return;
				}
				if (generateErrorEl) {
					generateErrorEl.hidden = true;
					generateErrorEl.textContent = '';
				}
				const restoreLabel = generateColorsDefaultText || generateColorsBtn.textContent.trim();
				generateColorsBtn.disabled = true;
				generateColorsBtn.textContent = i18n.generating || 'Generating…';
				const fd = new FormData();
				fd.set('master_color', normalizeHexForInput(masterColorInp.value));
				fd.set('theme_name', nameInp ? nameInp.value.trim() : '');
				ajax('fc_helper_suggest_theme_colors', fd)
					.then(function (raw) {
						generateColorsBtn.disabled = false;
						generateColorsBtn.textContent = restoreLabel;
						const d = raw && raw.data ? raw.data : {};
						const ok = !!(raw && raw.success);
						if (ok && d.colors && typeof d.colors === 'object') {
							THEME_COLOR_KEYS.forEach(function (k) {
								if (d.colors[k]) {
									setColorFieldValue(k, d.colors[k]);
								}
							});
							updateThemePreviewStrip();
						} else {
							const msg =
								(d && d.message) || i18n.error || 'Something went wrong.';
							if (generateErrorEl) {
								generateErrorEl.textContent = msg;
								generateErrorEl.hidden = false;
							}
						}
					})
					.catch(function () {
						generateColorsBtn.disabled = false;
						generateColorsBtn.textContent = restoreLabel;
						if (generateErrorEl) {
							generateErrorEl.textContent = i18n.error || 'Something went wrong.';
							generateErrorEl.hidden = false;
						}
					});
			});
		}

		if (nameInp) {
			nameInp.addEventListener('input', function () {
				if (themeModalIsNew && slugInp && !slugInp.readOnly) {
					slugInp.value = slugFromThemeName(nameInp.value);
				}
			});
		}

		if (openBtn) {
			openBtn.addEventListener('click', function () {
				themeModalIsNew = false;
				themeModalSelectedSlug = getTheme();
				openThemeModal();
				const row = getThemeRowBySlug(themeModalSelectedSlug);
				if (row) {
					selectModalTheme(themeModalSelectedSlug);
				} else if (themeList[0]) {
					selectModalTheme(themeList[0].slug);
				} else {
					clearEditorForNew({});
				}
			});
		}
		if (closeBtn) {
			closeBtn.addEventListener('click', closeThemeModal);
		}
		if (doneBtn) {
			doneBtn.addEventListener('click', closeThemeModal);
		}
		if (overlay) {
			overlay.addEventListener('click', function (e) {
				if (e.target === overlay) {
					closeThemeModal();
				}
			});
		}

		if (newBtn) {
			newBtn.addEventListener('click', function () {
				clearEditorForNew(getThemeRowBySlug(getTheme()));
			});
		}

		if (saveBtn) {
			saveBtn.addEventListener('click', function () {
				const payload = collectThemePayload();
				let slug = slugInp ? slugInp.value.trim() : '';
				if (themeModalIsNew) {
					if (!slug) {
						slug = slugFromThemeName(payload.label);
					}
					if (!slug) {
						slug = 'new_theme';
					}
				} else {
					slug = themeModalSelectedSlug;
				}
				slug = String(slug)
					.toLowerCase()
					.replace(/\s+/g, '_')
					.replace(/[^a-z0-9_]/g, '');
				if (!slug) {
					showNotice(i18n.themeSlugRequired || 'Theme slug is required.', true);
					return;
				}
				const fd = new FormData();
				fd.set('theme_slug', slug);
				fd.set('theme_data', JSON.stringify(payload));
				ajax('fc_helper_save_theme', fd).then(function (raw) {
					const d = raw && raw.data ? raw.data : {};
					if (raw && raw.success && d.themes) {
						mergeThemesFromServer(d.themes);
						themeModalIsNew = false;
						themeModalSelectedSlug = slug;
						if (slugInp) {
							slugInp.readOnly = true;
							slugInp.value = slug;
						}
						renderModalThemeList(slug);
						if (delBtn) {
							delBtn.hidden = builtinThemeSlugs.indexOf(slug) >= 0;
						}
						showNotice(i18n.themeSaved || 'Theme saved.', false);
					} else {
						showNotice((d && d.message) || i18n.error, true);
					}
				});
			});
		}

		if (delBtn) {
			delBtn.addEventListener('click', function () {
				const slug = themeModalSelectedSlug;
				if (!slug || builtinThemeSlugs.indexOf(slug) >= 0) {
					return;
				}
				const msg = i18n.themeDeleteConfirm || 'Delete this theme?';
				if (!window.confirm(msg)) {
					return;
				}
				const fd = new FormData();
				fd.set('theme_slug', slug);
				ajax('fc_helper_delete_theme', fd).then(function (raw) {
					const d = raw && raw.data ? raw.data : {};
					if (raw && raw.success && d.themes) {
						mergeThemesFromServer(d.themes);
						themeModalIsNew = false;
						themeModalSelectedSlug = '';
						if (themeList.length) {
							selectModalTheme(themeList[0].slug);
						} else {
							if (editor) {
								editor.setAttribute('hidden', '');
							}
							renderModalThemeList('');
							if (nameInp) {
								nameInp.value = '';
							}
							if (slugInp) {
								slugInp.value = '';
							}
						}
					} else {
						showNotice((d && d.message) || i18n.error, true);
					}
				});
			});
		}

		if (resetBtn) {
			resetBtn.addEventListener('click', function () {
				const msg =
					i18n.themeResetConfirm ||
					'Restore all themes to the original defaults? Custom themes will be removed.';
				if (!window.confirm(msg)) {
					return;
				}
				const fd = new FormData();
				ajax('fc_helper_reset_themes', fd).then(function (raw) {
					const d = raw && raw.data ? raw.data : {};
					if (raw && raw.success && d.themes) {
						mergeThemesFromServer(d.themes);
						themeModalIsNew = false;
						themeModalSelectedSlug = 'dark_gold';
						const row = getThemeRowBySlug('dark_gold');
						if (row) {
							selectModalTheme('dark_gold');
						} else if (themeList[0]) {
							selectModalTheme(themeList[0].slug);
						}
					} else {
						showNotice((d && d.message) || i18n.error, true);
					}
				});
			});
		}
	}

	function bind() {
		bindSwatches();
		bindThemeManager();
		bindTabs();
		bindSplashMediaPicker();
		bindLessonSearch();
		rebuildSavedProductsSelect();
		clearLessonSelection();

		const productNameInp = $('#fc_product_title');
		if (productNameInp) {
			productNameInp.addEventListener('input', function () {
				userEditedProductName = true;
			});
		}

		const savedSel = $('#fc-helper-saved-select');
		if (savedSel) savedSel.addEventListener('change', onSavedSelectChange);
		const newSessionBtn = $('#fc-new-session');
		if (newSessionBtn) {
			newSessionBtn.addEventListener('click', startNewSession);
		}
		const saveProd = $('#fc-helper-save-product');
		if (saveProd) saveProd.addEventListener('click', onSaveProduct);
		const updateProductBtn = $('#fc-helper-update-product');
		if (updateProductBtn) updateProductBtn.addEventListener('click', onUpdateProduct);
		const delSaved = $('#fc-helper-saved-delete');
		if (delSaved) delSaved.addEventListener('click', onDeleteSavedProduct);

		const gen = $('#fc-helper-generate');
		const rev = $('#fc-helper-revise');
		const copyTop = $('#fc-helper-copy-top');
		const copyIn = $('#fc-helper-copy-inline');
		const out = $('#fc_helper_output');
		const clearDbg = $('#fc-helper-clear-debug');

		if (gen) gen.addEventListener('click', onGenerate);
		if (rev) rev.addEventListener('click', onRevise);
		if (copyTop) {
			copyTop.addEventListener('click', function () {
				copyOutput(copyTop);
			});
		}
		if (copyIn) {
			copyIn.addEventListener('click', function () {
				copyOutput(copyIn);
			});
		}
		if (clearDbg) {
			clearDbg.addEventListener('click', function () {
				const t = $('#fc_helper_debug');
				if (t) t.value = '';
			});
		}
		if (out) {
			out.addEventListener('input', updateStats);
			out.addEventListener('keyup', updateStats);
			out.addEventListener('change', updateStats);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', bind);
	} else {
		bind();
	}
})();
