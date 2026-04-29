(function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var btn     = document.getElementById( 'academy-starter-copy-shortcode' );
		var code    = document.getElementById( 'academy-starter-shortcode' );
		var confirm = document.getElementById( 'academy-starter-copy-confirm' );

		if ( btn && code && confirm ) {
			btn.addEventListener( 'click', function () {
				var text = code.textContent || code.innerText;

				if ( navigator.clipboard && navigator.clipboard.writeText ) {
					navigator.clipboard.writeText( text ).then( showConfirm );
				} else {
					var ta = document.createElement( 'textarea' );
					ta.value = text;
					ta.style.position = 'fixed';
					ta.style.opacity  = '0';
					document.body.appendChild( ta );
					ta.focus();
					ta.select();
					document.execCommand( 'copy' );
					document.body.removeChild( ta );
					showConfirm();
				}
			} );
		}

		var apiKeyEl      = document.getElementById( 'academy-starter-api-key' );
		var copyApiKeyBtn = document.getElementById( 'academy-starter-copy-api-key' );
		var apiKeyCopied  = document.getElementById( 'academy-starter-api-key-copied' );

		if ( apiKeyEl && copyApiKeyBtn && apiKeyCopied ) {
			copyApiKeyBtn.addEventListener( 'click', function () {
				var text = apiKeyEl.textContent || apiKeyEl.innerText;

				if ( navigator.clipboard && navigator.clipboard.writeText ) {
					navigator.clipboard.writeText( text ).then( function () {
						showApiKeyCopied();
					} );
				} else {
					var ta = document.createElement( 'textarea' );
					ta.value = text;
					ta.style.position = 'fixed';
					ta.style.opacity  = '0';
					document.body.appendChild( ta );
					ta.focus();
					ta.select();
					document.execCommand( 'copy' );
					document.body.removeChild( ta );
					showApiKeyCopied();
				}
			} );
		}

		document.querySelectorAll( '.academy-starter-seo-copy' ).forEach( function ( seoBtn ) {
			seoBtn.addEventListener( 'click', function () {
				var targetId = seoBtn.getAttribute( 'data-target' );
				var el = document.getElementById( targetId );
				if ( ! el ) {
					return;
				}

				var text = el.textContent || el.innerText;

				function flashBtn() {
					var orig = seoBtn.textContent;
					seoBtn.textContent = 'Copied!';
					seoBtn.disabled = true;
					setTimeout( function () {
						seoBtn.textContent = orig;
						seoBtn.disabled = false;
					}, 2000 );
				}

				if ( navigator.clipboard && navigator.clipboard.writeText ) {
					navigator.clipboard.writeText( text ).then( flashBtn );
				} else {
					var ta = document.createElement( 'textarea' );
					ta.value = text;
					ta.style.position = 'fixed';
					ta.style.opacity = '0';
					document.body.appendChild( ta );
					ta.focus();
					ta.select();
					document.execCommand( 'copy' );
					document.body.removeChild( ta );
					flashBtn();
				}
			} );
		} );

		var applySeoBtn    = document.getElementById( 'academy-starter-apply-seo' );
		var applySeoResult = document.getElementById( 'academy-starter-apply-seo-result' );

		if ( applySeoBtn && applySeoResult && typeof academyStarterAdmin !== 'undefined' ) {
			applySeoBtn.addEventListener( 'click', function () {
				applySeoBtn.disabled = true;
				applySeoBtn.textContent = 'Applying...';
				applySeoResult.style.display = 'none';
				applySeoResult.className = 'academy-starter-apply-seo-result';

				var formData = new FormData();
				formData.append( 'action', 'academy_starter_apply_seo' );
				formData.append( 'nonce', academyStarterAdmin.seoNonce );

				fetch( academyStarterAdmin.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					body: formData,
				} )
					.then( function ( response ) {
						return response.json();
					} )
					.then( function ( data ) {
						applySeoResult.style.display = 'inline';
						if ( data.success ) {
							applySeoResult.classList.add( 'is-success' );
							applySeoResult.textContent = data.data.message;
						} else {
							applySeoResult.classList.add( 'is-error' );
							applySeoResult.textContent = data.data && data.data.message ? data.data.message : 'Something went wrong.';
						}
					} )
					.catch( function () {
						applySeoResult.style.display = 'inline';
						applySeoResult.classList.add( 'is-error' );
						applySeoResult.textContent = 'Request failed. Please try again.';
					} )
					.finally( function () {
						applySeoBtn.disabled = false;
						applySeoBtn.textContent = '✦ Apply SEO to Homepage';
					} );
			} );
		}

		var resetBtn     = document.getElementById( 'academy-starter-reset-stats' );
		var resetConfirm = document.getElementById( 'academy-starter-reset-confirm' );

		if ( resetBtn ) {
			resetBtn.addEventListener( 'click', function () {
				var config = typeof academyStarterAdmin !== 'undefined' ? academyStarterAdmin : {};
				var ajaxUrl = config.ajaxUrl || resetBtn.getAttribute( 'data-ajax-url' );
				var nonce = config.resetNonce || resetBtn.getAttribute( 'data-nonce' );
				var confirmMessage = config.confirmMessage || resetBtn.getAttribute( 'data-confirm-message' );

				if ( ! ajaxUrl || ! nonce ) {
					window.alert( 'Reset stats is not configured. Please reload the page and try again.' );
					return;
				}

				if ( ! window.confirm( confirmMessage ) ) {
					return;
				}

				resetBtn.disabled = true;

				var formData = new FormData();
				formData.append( 'action', 'academy_starter_reset_stats' );
				formData.append( 'nonce', nonce );

				fetch( ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					body: formData,
				} )
					.then( function ( response ) {
						return response.json();
					} )
					.then( function ( data ) {
						if ( data.success ) {
							if ( resetConfirm ) {
								resetConfirm.style.display = 'inline';
							}
							setTimeout( function () {
								if ( resetConfirm ) {
									resetConfirm.style.display = 'none';
								}
								window.location.reload();
							}, 1500 );
						} else {
							window.alert( data.data && data.data.message ? data.data.message : 'Stats could not be reset.' );
						}
					} )
					.catch( function () {
						window.alert( 'Stats could not be reset. Please reload the page and try again.' );
					} )
					.finally( function () {
						resetBtn.disabled = false;
					} );
			} );
		}

		var dayModalOverlay = document.getElementById( 'academy-starter-day-modal-overlay' );
		var dayModalClose   = document.getElementById( 'academy-starter-day-modal-close' );
		var dayModalDate    = document.getElementById( 'academy-starter-day-modal-date' );
		var dayModalUnique  = document.getElementById( 'academy-starter-day-modal-unique' );
		var dayModalLoading = document.getElementById( 'academy-starter-day-modal-loading' );
		var dayModalTable   = document.getElementById( 'academy-starter-day-modal-table' );
		var dayModalTbody   = document.getElementById( 'academy-starter-day-modal-tbody' );
		var dayModalEmpty   = document.getElementById( 'academy-starter-day-modal-empty' );

		function openDayModal( date ) {
			if ( ! dayModalOverlay || typeof academyStarterAdmin === 'undefined' ) {
				return;
			}

			dayModalDate.textContent = date;
			dayModalLoading.style.display = 'block';
			dayModalTable.style.display = 'none';
			dayModalEmpty.style.display = 'none';
			if ( dayModalUnique ) {
				dayModalUnique.style.display = 'none';
				dayModalUnique.textContent = '';
			}
			dayModalTbody.innerHTML = '';
			dayModalOverlay.style.display = 'flex';
			dayModalOverlay.setAttribute( 'aria-hidden', 'false' );
			document.body.style.overflow = 'hidden';

			var formData = new FormData();
			formData.append( 'action', 'academy_starter_get_day_views' );
			formData.append( 'nonce', academyStarterAdmin.dayViewsNonce );
			formData.append( 'date', date );

			fetch( academyStarterAdmin.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData,
			} )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( data ) {
					dayModalLoading.style.display = 'none';

					if ( ! data.success || ! data.data.views.length ) {
						dayModalEmpty.style.display = 'block';
						return;
					}

					data.data.views.forEach( function ( row, i ) {
						var tr = document.createElement( 'tr' );
						var indexTd = document.createElement( 'td' );
						var timeTd = document.createElement( 'td' );
						var ipTd = document.createElement( 'td' );
						var ipCode = document.createElement( 'code' );

						indexTd.textContent = i + 1;
						timeTd.textContent = row.time;
						ipCode.textContent = row.ip;
						ipTd.appendChild( ipCode );
						tr.appendChild( indexTd );
						tr.appendChild( timeTd );
						tr.appendChild( ipTd );
						dayModalTbody.appendChild( tr );
					} );

					if ( dayModalUnique ) {
						dayModalUnique.textContent = data.data.unique_ips + ' unique IP' + ( data.data.unique_ips !== 1 ? 's' : '' );
						dayModalUnique.style.display = 'inline';
					}

					dayModalTable.style.display = 'table';
				} )
				.catch( function () {
					dayModalLoading.style.display = 'none';
					dayModalEmpty.style.display = 'block';
				} );
		}

		function closeDayModal() {
			if ( ! dayModalOverlay ) {
				return;
			}

			dayModalOverlay.style.display = 'none';
			dayModalOverlay.setAttribute( 'aria-hidden', 'true' );
			document.body.style.overflow = '';
			if ( dayModalUnique ) {
				dayModalUnique.style.display = 'none';
				dayModalUnique.textContent = '';
			}
		}

		if ( dayModalClose ) {
			dayModalClose.addEventListener( 'click', closeDayModal );
		}

		if ( dayModalOverlay ) {
			dayModalOverlay.addEventListener( 'click', function ( event ) {
				if ( event.target === dayModalOverlay ) {
					closeDayModal();
				}
			} );
		}

		document.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Escape' && dayModalOverlay && dayModalOverlay.style.display !== 'none' ) {
				closeDayModal();
			}
		} );

		document.querySelectorAll( '.academy-starter-day-views-btn' ).forEach( function ( dayBtn ) {
			dayBtn.addEventListener( 'click', function () {
				openDayModal( dayBtn.getAttribute( 'data-date' ) );
			} );
		} );

		function showConfirm() {
			if ( confirm ) {
				confirm.style.display = 'inline';
				setTimeout( function () {
					confirm.style.display = 'none';
				}, 2000 );
			}
		}

		function showApiKeyCopied() {
			if ( apiKeyCopied ) {
				apiKeyCopied.style.display = 'inline';
				setTimeout( function () {
					apiKeyCopied.style.display = 'none';
				}, 2000 );
			}
		}
	} );
}());
