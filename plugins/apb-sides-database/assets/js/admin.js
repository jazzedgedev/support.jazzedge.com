/* global apbSidesAdmin, ajaxurl, jQuery */
(function ($) {
	'use strict';

	/**
	 * Admin AJAX URL: prefer wp_localize_script (apbSidesAdmin), then WordPress global ajaxurl (admin).
	 */
	function apbAdminAjaxUrl() {
		if (typeof apbSidesAdmin !== 'undefined' && apbSidesAdmin.ajaxUrl) {
			return apbSidesAdmin.ajaxUrl;
		}
		if (typeof ajaxurl !== 'undefined' && ajaxurl) {
			return ajaxurl;
		}
		return '';
	}

	function apbAdminNonce() {
		return typeof apbSidesAdmin !== 'undefined' && apbSidesAdmin.nonce ? apbSidesAdmin.nonce : '';
	}

	function notice($el, msg, ok) {
		if (!$el || !$el.length) {
			return;
		}
		$el.text(msg || '');
		$el.css('color', ok ? '#008a20' : '#d63638');
	}

	function post(action, data, $notice) {
		data = data || {};
		data.action = action;
		data.nonce = apbAdminNonce();
		return $.post(apbAdminAjaxUrl(), data)
			.done(function (res) {
				if (res.success) {
					notice($notice, res.data && res.data.message ? res.data.message : 'OK', true);
				} else {
					notice($notice, (res.data && res.data.message) || 'Error', false);
				}
			})
			.fail(function () {
				notice($notice, 'Request failed', false);
			});
	}

	function fallbackCopy(text) {
		var $ta = $('<textarea>')
			.val(text)
			.css({ position: 'fixed', opacity: 0 })
			.appendTo('body');
		$ta[0].select();
		try {
			document.execCommand('copy');
			$('#apb-copy-feedback').text('✓ Copied.').css('color', '#46b450');
		} catch (e) {
			$('#apb-copy-feedback')
				.text('✗ Copy failed. Select manually.')
				.css('color', '#dc3232');
		}
		$ta.remove();
	}

	$(function () {
		$(document).on('click', '#apb-reinstall-tables', function () {
			var $btn = $(this);
			var $result = $('#apb-reinstall-result');
			var originalText = $btn.text();
			var ajaxUrl =
				typeof apbSidesAdmin !== 'undefined' && apbSidesAdmin.ajaxUrl
					? apbSidesAdmin.ajaxUrl
					: apbAdminAjaxUrl();
			var nonce =
				typeof apbSidesAdmin !== 'undefined' && apbSidesAdmin.nonce
					? apbSidesAdmin.nonce
					: apbAdminNonce();
			$btn.prop('disabled', true).text('Reinstalling...');
			$result.text('').css('color', '');
			$.post(
				ajaxUrl,
				{
					action: 'apb_sides_reinstall_tables',
					nonce: nonce,
				},
				function (response) {
					if (response.success) {
						var msg =
							response.data && response.data.message ? response.data.message : '';
						$result.text('✓ ' + msg).css('color', '#46b450');
						setTimeout(function () {
							window.location.reload();
						}, 1200);
					} else {
						var err =
							response.data && response.data.message ? response.data.message : '';
						$result.text('✗ ' + err).css('color', '#dc3232');
						$btn.prop('disabled', false).text(originalText);
					}
				},
				'json'
			).fail(function () {
				$result.text('✗ Request failed.').css('color', '#dc3232');
				$btn.prop('disabled', false).text(originalText);
			});
		});

		$('#apb-migrate-schema').on('click', function () {
			if (!window.confirm('This will remove all unused columns and old tables. Your current data is preserved. Continue?')) { return; }
			var $btn = $(this);
			$btn.prop('disabled', true).text('Migrating…');
			post('apb_sides_migrate_schema', {}, $btn)
				.done(function (res) {
					$('#apb-migrate-result').text(
						res.success ? (res.data.message || 'Done!') : ((res.data && res.data.message) || 'Failed.')
					).css('color', res.success ? 'green' : '#dc3232');
				})
				.always(function () { $btn.prop('disabled', false).text('Migrate Schema (Remove Old Columns)'); });
		});

		$(document).on('click', '#apb-clear-log', function () {
			var $btn = $(this);
			var ajaxUrl =
				typeof apbSidesAdmin !== 'undefined' && apbSidesAdmin.ajaxUrl
					? apbSidesAdmin.ajaxUrl
					: apbAdminAjaxUrl();
			var nonce =
				typeof apbSidesAdmin !== 'undefined' && apbSidesAdmin.nonce
					? apbSidesAdmin.nonce
					: apbAdminNonce();
			$btn.prop('disabled', true).text('Clearing...');
			$.post(
				ajaxUrl,
				{
					action: 'apb_sides_clear_log',
					nonce: nonce,
				},
				function (response) {
					if (response.success) {
						$('#apb-log-content').val('');
						$('#apb-log-feedback')
							.text('Log cleared.')
							.css('color', '#46b450');
					} else {
						var err =
							response.data && response.data.message ? response.data.message : '';
						$('#apb-log-feedback').text('✗ ' + err).css('color', '#dc3232');
					}
					$btn.prop('disabled', false).text('Clear Log');
				},
				'json'
			).fail(function () {
				$('#apb-log-feedback').text('✗ Request failed.').css('color', '#dc3232');
				$btn.prop('disabled', false).text('Clear Log');
			});
		});

		$(document).on('click', '#apb-refresh-log', function () {
			var $btn = $(this);
			var ajaxUrl =
				typeof apbSidesAdmin !== 'undefined' && apbSidesAdmin.ajaxUrl
					? apbSidesAdmin.ajaxUrl
					: apbAdminAjaxUrl();
			var nonce =
				typeof apbSidesAdmin !== 'undefined' && apbSidesAdmin.nonce
					? apbSidesAdmin.nonce
					: apbAdminNonce();
			$btn.prop('disabled', true).text('Refreshing...');
			$.post(
				ajaxUrl,
				{
					action: 'apb_sides_get_log',
					nonce: nonce,
				},
				function (response) {
					if (response.success) {
						var c =
							response.data && typeof response.data.content !== 'undefined'
								? response.data.content
								: '';
						$('#apb-log-content').val(c);
						if (response.data && response.data.log_path) {
							$('#apb-log-path-display').text(response.data.log_path);
						}
						$('#apb-log-feedback').text('Refreshed.').css('color', '#646970');
					}
					$btn.prop('disabled', false).text('Refresh Log');
				},
				'json'
			)
				.fail(function () {
				$('#apb-log-feedback').text('✗ Request failed.').css('color', '#dc3232');
				$btn.prop('disabled', false).text('Refresh Log');
			});
		});

		$(document).on('click', '#apb-copy-log', function () {
			var content = $('#apb-log-content').val();
			if (!content) {
				$('#apb-copy-feedback').text('Log is empty.').css('color', '#646970');
				return;
			}
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(content).then(
					function () {
						$('#apb-copy-feedback').text('✓ Copied.').css('color', '#46b450');
					},
					function () {
						fallbackCopy(content);
					}
				);
			} else {
				fallbackCopy(content);
			}
		});

		$(document).on('click', '#apb-test-logging', function () {
			var $btn = $(this);
			var $out = $('#apb-test-logging-result');
			var ajaxUrl =
				typeof apbSidesAdmin !== 'undefined' && apbSidesAdmin.ajaxUrl
					? apbSidesAdmin.ajaxUrl
					: apbAdminAjaxUrl();
			var nonce =
				typeof apbSidesAdmin !== 'undefined' && apbSidesAdmin.nonce
					? apbSidesAdmin.nonce
					: apbAdminNonce();
			$btn.prop('disabled', true);
			$out.text('Testing…').css('color', '#646970');
			$.post(
				ajaxUrl,
				{
					action: 'apb_sides_test_logging',
					nonce: nonce,
				},
				function (response) {
					if (response.success && response.data) {
						var d = response.data;
						var msg =
							'Path: ' +
							(d.log_path || '') +
							' | written: ' +
							(d.logged ? 'yes' : 'no') +
							' | dir writable: ' +
							(d.writable ? 'yes' : 'no') +
							' | wp-content: ' +
							(d.wp_content_writable ? 'ok' : 'no') +
							' | plugin: ' +
							(d.plugin_dir_writable ? 'ok' : 'no');
						$out.text(msg).css('color', d.logged ? '#46b450' : '#dc3232');
						if (d.log_path) {
							$('#apb-log-path-display').text(d.log_path);
						}
						$.post(
							ajaxUrl,
							{
								action: 'apb_sides_get_log',
								nonce: nonce,
							},
							function (r2) {
								if (r2.success && r2.data && typeof r2.data.content !== 'undefined') {
									$('#apb-log-content').val(r2.data.content);
								}
								if (r2.success && r2.data && r2.data.log_path) {
									$('#apb-log-path-display').text(r2.data.log_path);
								}
							},
							'json'
						);
					} else {
						$out.text('✗ Failed').css('color', '#dc3232');
					}
					$btn.prop('disabled', false);
				},
				'json'
			).fail(function () {
				$out.text('✗ Request failed').css('color', '#dc3232');
				$btn.prop('disabled', false);
			});
		});

		var $reviewRoot = $('#apb-review-root');
		var reviewUid = $reviewRoot.length ? $reviewRoot.data('upload-id') : null;
		if (reviewUid) {
			$(document).on('click', '#apb-save-text-override', function () {
				var $btn = $(this);
				var $note = $('#apb-override-notice');
				var text = $('#apb-override-text').val();
				$btn.prop('disabled', true);
				$note.text('').css('color', '');
				$.post(apbAdminAjaxUrl(), {
					action: 'apb_sides_save_text_override',
					nonce: apbAdminNonce(),
					upload_id: reviewUid,
					override_text: text,
				})
					.done(function (res) {
						if (res.success) {
							$note
								.text(
									res.data && res.data.message ? res.data.message : 'Saved.'
								)
								.css('color', '#46b450');
							window.location.reload();
						} else {
							$note
								.text((res.data && res.data.message) || 'Error')
								.css('color', '#d63638');
						}
					})
					.fail(function () {
						$note.text('Request failed').css('color', '#d63638');
					})
					.always(function () {
						$btn.prop('disabled', false);
					});
			});

		}

		function apbCopyPrompt(textareaId) {
			var text = $('#' + textareaId)
				.val()
				.replace(/[\u201C\u201D]/g, '"')
				.replace(/[\u2018\u2019]/g, "'");
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(text).then(function () {
					$('#apb-copy-gpt-msg').show().delay(2000).fadeOut();
				});
			} else {
				var $t = $('#' + textareaId).show().select();
				document.execCommand('copy');
				$t.hide();
				$('#apb-copy-gpt-msg').show().delay(2000).fadeOut();
			}
		}
		$('#apb-copy-scene-prompt').on('click', function () {
			apbCopyPrompt('apb-gpt-scene-prompt');
		});
		$('#apb-copy-script-prompt').on('click', function () {
			apbCopyPrompt('apb-gpt-script-prompt');
		});

		$('#apb-json-import-form').on('submit', function (e) {
			e.preventDefault();
			var json = $('#apb-json-paste').val().trim();
			var $msg = $('#apb-json-import-msg');
			if (!json) {
				$msg.text('Please paste JSON.').css('color', '#dc3232');
				return;
			}
			json = json
				.replace(/[\u201C\u201D]/g, '"')
				.replace(/[\u2018\u2019]/g, "'");
			$('#apb-json-paste').val(json);
			try {
				JSON.parse(json);
			} catch (err) {
				$msg.text('Invalid JSON — check for syntax errors.').css('color', '#dc3232');
				return;
			}
			var fd2 = new FormData();
			fd2.append('action', 'apb_sides_import_json');
			fd2.append('nonce', apbAdminNonce());
			fd2.append('json_data', json);
			var pdfFile = document.getElementById('apb-json-pdf-file');
			if (pdfFile && pdfFile.files.length) {
				fd2.append('script_pdf', pdfFile.files[0]);
			}
			$msg.text('Importing…').css('color', '');
			$('#apb-json-import-btn').prop('disabled', true);
			$.ajax({
				url: apbAdminAjaxUrl(),
				type: 'POST',
				data: fd2,
				processData: false,
				contentType: false,
			})
				.done(function (res) {
					if (res.success) {
						var msg = res.data.grouped
							? 'Added ' + res.data.scene_count + ' scene(s) to existing show. Redirecting…'
							: 'Imported! ' + res.data.scene_count + ' scene(s) created. Redirecting…';
						$msg.text(msg).css('color', 'green');
						setTimeout(function () {
							window.location.href =
								'admin.php?page=apb-sides-scripts&id=' +
								encodeURIComponent(res.data.script_id);
						}, 1400);
					} else {
						$msg
							.text((res.data && res.data.message) || 'Import failed.')
							.css('color', '#dc3232');
						$('#apb-json-import-btn').prop('disabled', false);
					}
				})
				.fail(function () {
					$msg.text('Request failed.').css('color', '#dc3232');
					$('#apb-json-import-btn').prop('disabled', false);
				});
		});

		// Inline filename edit
		$('.apb-uploads-table').on('click', '.apb-filename-edit', function () {
			var $cell = $(this).closest('.apb-filename-cell');
			$cell.find('.apb-filename-display, .apb-filename-edit').hide();
			$cell.find('.apb-filename-input, .apb-filename-save, .apb-filename-cancel').show();
			$cell.find('.apb-filename-input').focus().select();
		});

		$('.apb-uploads-table').on('click', '.apb-filename-cancel', function () {
			var $cell = $(this).closest('.apb-filename-cell');
			var orig = $cell.find('.apb-filename-display').text();
			$cell.find('.apb-filename-input').val(orig);
			$cell.find('.apb-filename-input, .apb-filename-save, .apb-filename-cancel').hide();
			$cell.find('.apb-filename-display, .apb-filename-edit').show();
		});

		$('.apb-uploads-table').on('click', '.apb-filename-save', function () {
			var $btn = $(this);
			var $cell = $btn.closest('.apb-filename-cell');
			var $tr = $btn.closest('tr');
			var uid = $tr.data('upload-id');
			var name = $cell.find('.apb-filename-input').val().trim();
			if (!name) {
				return;
			}
			$btn.prop('disabled', true).text('Saving…');
			post('apb_sides_rename_upload', { upload_id: uid, filename: name }, null)
				.done(function (res) {
					if (res.success) {
						$cell.find('.apb-filename-display').text(name);
						$cell.find('.apb-filename-input, .apb-filename-save, .apb-filename-cancel').hide();
						$cell.find('.apb-filename-display, .apb-filename-edit').show();
					} else {
						alert((res.data && res.data.message) || 'Could not rename.');
					}
				})
				.always(function () {
					$btn.prop('disabled', false).text('Save');
				});
		});

		$('.apb-uploads-table').on('click', '.apb-reset-upload', function () {
			var $tr = $(this).closest('tr');
			var id = $tr.data('upload-id');
			var $n = $tr.find('.apb-inline-notice');
			$n.text('Resetting…');
			$.post(apbAdminAjaxUrl(), {
				action: 'apb_sides_repair_parsed_doc',
				nonce: apbAdminNonce(),
				upload_id: id,
			})
				.done(function (res) {
					if (res.success && res.data) {
						if (res.data.badge_html) {
							$tr.find('.apb-upload-status').html(res.data.badge_html);
						}
						$tr.attr('data-upload-status', 'uploaded');
						notice($n, res.data.message || 'Reset complete. Click Parse to re-parse.', true);
						$tr.find('.apb-reset-upload').remove();
					} else {
						notice($n, (res.data && res.data.message) || 'Error', false);
					}
				})
				.fail(function () {
					notice($n, 'Request failed', false);
				});
		});

		$('.apb-uploads-table').on('click', '.apb-publish-upload', function () {
			var $tr = $(this).closest('tr');
			var id = $tr.data('upload-id');
			var $n = $tr.find('.apb-inline-notice');
			post('apb_sides_publish_upload', { upload_id: id }, $n).done(function (res) {
				if (res.success) {
					window.location.reload();
				}
			});
		});

		$('.apb-uploads-table').on('click', '.apb-delete-upload', function () {
			if (!window.confirm('Delete this upload and related drafts?')) {
				return;
			}
			var $tr = $(this).closest('tr');
			var id = $tr.data('upload-id');
			var $n = $tr.find('.apb-inline-notice');
			post('apb_sides_delete_upload', { upload_id: id }, $n).done(function (res) {
				if (res.success) {
					$tr.fadeOut(function () {
						$(this).remove();
					});
				}
			});
		});

		function collectReviewPayload() {
			var payload = { script: null, characters: [], sides: [], queue: [] };
			var scriptId = $('#apb-review-script-id').val();
			if (scriptId) {
				payload.script = { id: parseInt(scriptId, 10) };
				$('.apb-review-script').find('[data-field]').each(function () {
					var k = $(this).data('field');
					payload.script[k] = $(this).val();
				});
			}
			$('.apb-review-character').each(function () {
				var cid = $(this).data('character-id');
				var row = { id: cid };
				$(this)
					.find('[data-field]')
					.each(function () {
						row[$(this).data('field')] = $(this).val();
					});
				payload.characters.push(row);
			});
			$('.apb-review-side').each(function () {
				var sid = $(this).data('side-id');
				var row = { id: sid, character_ids: [] };
				$(this)
					.find('[data-field]')
					.each(function () {
						row[$(this).data('field')] = $(this).val();
					});
				$(this)
					.find('.apb-side-char:checked')
					.each(function () {
						row.character_ids.push(parseInt($(this).val(), 10));
					});
				payload.sides.push(row);
			});
			$('.apb-queue-row').each(function () {
				payload.queue.push({
					id: $(this).data('queue-id'),
					approved_value: $(this).find('.apb-queue-approved').val(),
				});
			});
			return payload;
		}

		$('#apb-approve-publish-all').on('click', function () {
			var $btn = $(this);
			var uid = $('#apb-review-root').data('upload-id');
			if (!uid) {
				return;
			}
			if (!window.confirm('Approve all items and publish to the front end?')) {
				return;
			}
			$btn.prop('disabled', true).text('Publishing…');
			post('apb_sides_approve_publish_all', { upload_id: uid }, null)
				.done(function (res) {
					if (res.success) {
						$('#apb-review-notice').text('Published! Redirecting…').css('color', 'green');
						setTimeout(function () {
							window.location.href = 'admin.php?page=apb-sides-uploads';
						}, 1500);
					} else {
						$('#apb-review-notice').text((res.data && res.data.message) || 'Failed.').css('color', '#dc3232');
						$btn.prop('disabled', false).text('Approve All & Publish');
					}
				})
				.fail(function () {
					$('#apb-review-notice').text('Request failed.').css('color', '#dc3232');
					$btn.prop('disabled', false).text('Approve All & Publish');
				});
		});

		$('#apb-save-review').on('click', function () {
			var $note = $('#apb-review-notice');
			$note.text('Saving…');
			$.post(apbSidesAdmin.ajaxUrl, {
				action: 'apb_sides_save_review',
				nonce: apbSidesAdmin.nonce,
				payload: JSON.stringify(collectReviewPayload()),
			})
				.done(function (res) {
					if (res.success) {
						$note.text('Saved.');
						$note.css('color', '#008a20');
					} else {
						$note.text((res.data && res.data.message) || 'Error');
						$note.css('color', '#d63638');
					}
				})
				.fail(function () {
					$note.text('Request failed');
					$note.css('color', '#d63638');
				});
		});

		$('#apb-bulk-queue-approve, #apb-bulk-queue-reject').on('click', function () {
			var mode = $(this).data('mode');
			var uid = $('#apb-review-root').data('upload-id');
			var $note = $('#apb-review-notice');
			post('apb_sides_bulk_queue', { upload_id: uid, mode: mode }, $note).done(function () {
				window.location.reload();
			});
		});

		$(document).on('click', '.apb-upload-script-pdf', function () {
			var $btn = $(this);
			var scriptId = $btn.data('script-id');
			var $notice = $('#apb-pdf-upload-notice');
			var input = document.getElementById('apb-script-pdf-upload');
			if (!input || !input.files.length) {
				$notice.text('Choose a PDF file first.').css('color', '#dc3232');
				return;
			}
			var fd = new FormData();
			fd.append('action', 'apb_sides_upload_script_pdf');
			fd.append('nonce', apbAdminNonce());
			fd.append('script_id', scriptId);
			fd.append('script_pdf', input.files[0]);
			$btn.prop('disabled', true).text('Uploading…');
			$notice.text('').css('color', '');
			$.ajax({
				url: apbAdminAjaxUrl(),
				type: 'POST',
				data: fd,
				processData: false,
				contentType: false,
			})
				.done(function (res) {
					if (res.success) {
						$notice.text('PDF uploaded successfully!').css('color', 'green');
						var $td = $btn.closest('td');
						$td.find('a.button').remove();
						$btn.before(
							'<p><a href="' +
								(res.data && res.data.pdf_url ? res.data.pdf_url : '') +
								'" target="_blank" class="button" rel="noopener noreferrer">View / Download current PDF</a></p>'
						);
					} else {
						$notice
							.text((res.data && res.data.message) || 'Upload failed.')
							.css('color', '#dc3232');
					}
				})
				.fail(function () {
					$notice.text('Request failed.').css('color', '#dc3232');
				})
				.always(function () {
					$btn.prop('disabled', false).text('Upload PDF');
				});
		});

		$('#apb-review-root').on('click', '.apb-approve-entity, .apb-reject-entity', function () {
			var $b = $(this);
			var type = $b.data('entity-type');
			var eid = $b.data('entity-id');
			var act = $b.hasClass('apb-approve-entity') ? 'apb_sides_approve_draft' : 'apb_sides_reject_draft';
			var $note = $('#apb-review-notice');
			post(act, { entity_type: type, entity_id: eid }, $note).done(function () {
				window.location.reload();
			});
		});

		$('.wrap').on('click', '.apb-publish-script', function () {
			var $tr = $(this).closest('tr');
			var id = $tr.data('script-id');
			var $n = $tr.find('.apb-inline-notice');
			post('apb_sides_publish_script', { script_id: id }, $n);
		});

		$('.wrap').on('click', '.apb-unpublish-script', function () {
			var $tr = $(this).closest('tr');
			var id = $tr.data('script-id');
			var $n = $tr.find('.apb-inline-notice');
			post('apb_sides_unpublish_script', { script_id: id }, $n);
		});

		$('.wrap').on('click', '.apb-delete-script', function () {
			if (!window.confirm('Delete this script and all characters/sides?')) {
				return;
			}
			var $tr = $(this).closest('tr');
			var id = $tr.data('script-id');
			var $n = $tr.find('.apb-inline-notice');
			post('apb_sides_delete_script', { script_id: id }, $n).done(function (res) {
				if (res.success) {
					$tr.remove();
				}
			});
		});

		$('.wrap').on('click', '.apb-delete-character', function () {
			if (!window.confirm('Delete this character?')) {
				return;
			}
			var $tr = $(this).closest('tr');
			var id = $tr.data('character-id');
			var $n = $tr.find('.apb-inline-notice');
			post('apb_sides_delete_character', { character_id: id }, $n).done(function (res) {
				if (res.success) {
					$tr.remove();
				}
			});
		});

		$('.wrap').on('click', '.apb-toggle-featured', function () {
			var $tr = $(this).closest('tr');
			var id = $tr.data('side-id');
			var $n = $tr.find('.apb-inline-notice');
			post('apb_sides_toggle_featured', { side_id: id }, $n);
		});

		$('.wrap').on('click', '.apb-delete-side', function () {
			if (!window.confirm('Delete this side?')) {
				return;
			}
			var $tr = $(this).closest('tr');
			var id = $tr.data('side-id');
			var $n = $tr.find('.apb-inline-notice');
			post('apb_sides_delete_side', { side_id: id }, $n).done(function (res) {
				if (res.success) {
					$tr.remove();
				}
			});
		});

		function collectFields($wrap) {
			var o = {};
			$wrap.find('.apb-field').each(function () {
				o[$(this).attr('name')] = $(this).val();
			});
			return o;
		}

		$('.apb-save-entity').on('click', function () {
			var $wrap = $(this).closest('[data-entity-type]');
			var type = $wrap.data('entity-type');
			var id = $wrap.data('entity-id');
			var fields = collectFields($wrap);
			if (type === 'side') {
				fields.character_ids = [];
				$wrap.find('.apb-side-char-cb:checked').each(function () {
					fields.character_ids.push(parseInt($(this).val(), 10));
				});
			}
			var $n = $wrap.find('.apb-inline-notice');
			post(
				'apb_sides_save_entity',
				{
					entity_type: type,
					entity_id: id,
					fields: JSON.stringify(fields),
				},
				$n
			);
		});

		// Delegated + ajaxurl fallback (same pattern as Katahdin AI Hub admin AJAX).
		$(document).on('click', '#apb-test-api-key', function () {
			var $btn = $(this);
			var $result = $('#apb-test-api-key-result');
			var url = apbAdminAjaxUrl();
			var nonce = apbAdminNonce();
			if (!url || !nonce) {
				$result.text('✗ Admin AJAX not configured. Reload the page.').css('color', '#dc3232');
				return;
			}
			$btn.prop('disabled', true).text('Testing...');
			$result.text('').css('color', '');
			$.post(
				url,
				{
					action: 'apb_sides_test_api_key',
					nonce: nonce,
				},
				function (response) {
					if (response.success) {
						$result
							.text('✓ ' + (response.data && response.data.message ? response.data.message : ''))
							.css('color', '#46b450');
					} else {
						var err =
							response.data && response.data.message
								? response.data.message
								: typeof response.data === 'string'
									? response.data
									: 'Error';
						$result.text('✗ ' + err).css('color', '#dc3232');
					}
				},
				'json'
			)
				.fail(function () {
					$result.text('✗ Request failed. Check server logs.').css('color', '#dc3232');
				})
				.always(function () {
					$btn.prop('disabled', false).text('Test Claude API Key');
				});
		});
	});
})(jQuery);
