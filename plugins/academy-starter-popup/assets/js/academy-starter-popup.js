(() => {
	const config = window.AcademyStarterPopup || {};
	const dismissedKey = config.dismissedKey || 'academy_starter_popup_dismissed';

	const overlay = document.getElementById('academy-starter-popup-overlay');
	const ribbon = document.getElementById('academy-starter-popup-ribbon');

	if (!overlay || !ribbon) {
		return;
	}

	const closeButton = overlay.querySelector('.academy-starter-popup-close');

	const showModal = () => {
		overlay.classList.add('is-visible');
		overlay.setAttribute('aria-hidden', 'false');
	};

	const hideModal = () => {
		overlay.classList.remove('is-visible');
		overlay.setAttribute('aria-hidden', 'true');
	};

	const showRibbon = () => {
		ribbon.classList.add('is-visible');
		ribbon.setAttribute('aria-hidden', 'false');
	};

	const markDismissed = () => {
		try {
			localStorage.setItem(dismissedKey, '1');
		} catch (error) {
			// Ignore storage issues and still hide the modal.
		}
	};

	const handleDismiss = () => {
		markDismissed();
		hideModal();
		showRibbon();
	};

	const isDismissed = () => {
		try {
			return localStorage.getItem(dismissedKey) === '1';
		} catch (error) {
			return false;
		}
	};

	if (isDismissed()) {
		showRibbon();
	} else {
		showModal();
	}

	if (closeButton) {
		closeButton.addEventListener('click', handleDismiss);
	}

	overlay.addEventListener('click', (event) => {
		if (event.target === overlay) {
			handleDismiss();
		}
	});

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && overlay.classList.contains('is-visible')) {
			handleDismiss();
		}
	});
})();
