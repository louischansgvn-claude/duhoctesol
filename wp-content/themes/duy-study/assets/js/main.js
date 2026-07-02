(() => {
	const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	const menuToggle = document.getElementById('menuToggle');
	const navLinks = document.getElementById('navLinks');

	if (menuToggle && navLinks) {
		const mobileNav = window.matchMedia('(max-width: 1439px)');

		const closeMenu = () => {
			navLinks.classList.remove('open');
			menuToggle.setAttribute('aria-expanded', 'false');
		};

		const collapseDrops = () => {
			navLinks.querySelectorAll('.has-drop.open').forEach((li) => {
				li.classList.remove('open');
				li.querySelector(':scope > a')?.setAttribute('aria-expanded', 'false');
			});
		};

		menuToggle.addEventListener('click', () => {
			const open = navLinks.classList.toggle('open');
			menuToggle.setAttribute('aria-expanded', String(open));
			if (!open) {
				collapseDrops();
			}
		});

		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape') {
				closeMenu();
				collapseDrops();
			}
		});

		// Mobile accordion: first tap opens the group's submenu; a second tap on an
		// already-open parent follows its link. Desktop keeps the hover behaviour.
		navLinks.querySelectorAll('.has-drop > a').forEach((trigger) => {
			const li = trigger.parentElement;
			const setExpanded = (expanded) => trigger.setAttribute('aria-expanded', String(expanded));
			trigger.setAttribute('aria-expanded', 'false');
			li?.addEventListener('mouseenter', () => {
				if (!mobileNav.matches) {
					setExpanded(true);
				}
			});
			li?.addEventListener('mouseleave', () => {
				if (!mobileNav.matches) {
					setExpanded(false);
				}
			});
			li?.addEventListener('focusin', () => {
				if (!mobileNav.matches) {
					setExpanded(true);
				}
			});
			li?.addEventListener('focusout', (event) => {
				if (!mobileNav.matches && !li.contains(event.relatedTarget)) {
					setExpanded(false);
				}
			});
			trigger.addEventListener('click', (event) => {
				if (!mobileNav.matches) {
					return;
				}
				if (!li.classList.contains('open')) {
					event.preventDefault();
					collapseDrops();
					li.classList.add('open');
					setExpanded(true);
				}
			});
		});

		// Close the whole menu when a leaf link (not a group trigger) is tapped.
		navLinks.querySelectorAll('a').forEach((link) => {
			const li = link.parentElement;
			const isTrigger = li?.classList.contains('has-drop') && li.querySelector(':scope > a') === link;
			if (isTrigger) {
				return;
			}
			link.addEventListener('click', closeMenu);
		});
	}

	document.querySelectorAll('.has-drop > a').forEach((trigger) => {
		trigger.addEventListener('keydown', (event) => {
			const drop = trigger.parentElement?.querySelector('.drop');
			if (!drop) {
				return;
			}
			if (event.key === 'ArrowDown') {
				event.preventDefault();
				drop.querySelector('a')?.focus();
			}
		});
	});

	document.querySelectorAll('.drop').forEach((drop) => {
		drop.addEventListener('keydown', (event) => {
			if (event.key !== 'Escape') {
				return;
			}
			event.preventDefault();
			const trigger = drop.closest('.has-drop')?.querySelector(':scope > a');
			trigger?.setAttribute('aria-expanded', 'false');
			trigger?.focus();
		});
	});

	document.querySelectorAll('[data-search-tabs]').forEach((tabs) => {
		const form = tabs.closest('[data-hero-search]');
		tabs.querySelectorAll('[data-search-tab]').forEach((button) => {
			button.addEventListener('click', () => {
				tabs.querySelectorAll('[data-search-tab]').forEach((tab) => {
					tab.classList.toggle('active', tab === button);
					tab.setAttribute('aria-selected', tab === button ? 'true' : 'false');
				});
				if (form) {
					form.dataset.searchMode = button.dataset.searchTab || 'schools';
				}
			});
		});
	});

	document.querySelectorAll('[data-hero-search]').forEach((form) => {
		form.addEventListener('submit', (event) => {
			event.preventDefault();
			const mode = form.dataset.searchMode || 'schools';
			const country = form.querySelector('[name="country"]')?.value || '';
			const keyword = form.querySelector('[name="keyword"]')?.value || '';
			const params = new URLSearchParams();
			if (country) {
				params.set('country', country);
			}
			if (keyword) {
				params.set('q', keyword);
			}

			let path = '/truong/';
			if (mode === 'scholarships') {
				path = '/hoc-bong/';
			}
			if (mode === 'countries') {
				path = country ? `/quoc-gia/${country}/` : '/quoc-gia/';
			}

			window.location.href = `${path}${params.toString() && mode !== 'countries' ? `?${params}` : ''}`;
		});
	});

	document.querySelectorAll('.role-toggle').forEach((toggle) => {
		const form = toggle.closest('form');
		const input = form?.querySelector('input[name="role"]');
		toggle.querySelectorAll('button').forEach((button) => {
			button.addEventListener('click', () => {
				toggle.querySelectorAll('button').forEach((item) => {
					const active = item === button;
					item.classList.toggle('active', active);
					item.setAttribute('aria-pressed', String(active));
				});
				if (input) {
					input.value = button.dataset.role || 'student';
				}
			});
		});
	});

	document.querySelectorAll('[data-duy-consultation-form]').forEach((form) => {
		const message = form.querySelector('[data-form-message]');

		const setMessage = (text, isError = false) => {
			if (!message) {
				return;
			}
			message.textContent = text;
			message.classList.toggle('is-error', isError);
		};

		form.addEventListener('submit', async (event) => {
			event.preventDefault();
			setMessage('');

			const invalid = Array.from(form.querySelectorAll('[required]')).find((field) => !field.value.trim());
			if (invalid) {
				invalid.focus();
				setMessage('Vui lòng điền các thông tin bắt buộc.', true);
				return;
			}

			const email = form.querySelector('[type="email"]');
			if (email && !email.checkValidity()) {
				email.focus();
				setMessage('Email chưa đúng định dạng.', true);
				return;
			}

			const data = new FormData(form);
			data.set('action', 'duy_consultation');
			data.set('nonce', window.DUY_AJAX?.nonce || '');

			try {
				const response = await fetch(window.DUY_AJAX?.ajax_url || '/wp-admin/admin-ajax.php', {
					method: 'POST',
					body: data,
					credentials: 'same-origin',
				});
				const result = await response.json();
				if (!result.success) {
					setMessage(result.data?.message || 'Vui lòng kiểm tra lại thông tin.', true);
					return;
				}
				form.reset();
				setMessage(result.data?.message || 'Cảm ơn bạn. Du học TESOL sẽ liên hệ sớm.');
			} catch (error) {
				setMessage('Đã ghi nhận thông tin demo. Kết nối gửi form thật sẽ hoàn thiện ở M9.');
			}
		});
	});

	const skeletonMarkup = () => '<div class="skeleton-card glass" aria-hidden="true"></div><div class="skeleton-card glass" aria-hidden="true"></div><div class="skeleton-card glass" aria-hidden="true"></div>';

	document.querySelectorAll('[data-finder]').forEach((finder) => {
		const type = finder.dataset.finder;
		const grid = finder.querySelector(`[data-finder-grid="${type}"]`);
		const empty = finder.querySelector(`[data-finder-empty="${type}"]`);
		const count = finder.querySelector(`[data-finder-count="${type}"]`);
		const loadMore = finder.querySelector(`[data-load-more="${type}"]`);
		const sort = finder.querySelector('[data-finder-sort]');
		const cards = grid ? Array.from(grid.querySelectorAll('[data-finder-card]')) : [];
		const state = { page: 1, pageSize: Number(finder.dataset.pageSize || 6) };

		if (!grid) {
			return;
		}

		const filterValue = (selector) => finder.querySelector(`${selector}:checked`)?.value || '';

		const cardMatches = (card) => {
			const keyword = finder.querySelector('[data-filter-keyword]')?.value.trim().toLowerCase() || '';
			const country = filterValue('[data-filter-country]');
			const level = filterValue('[data-filter-level]');
			const major = filterValue('[data-filter-major]');
			const fee = filterValue('[data-filter-fee]');
			const eventType = filterValue('[data-filter-event]');
			const newsCat = filterValue('[data-filter-news]');
			const text = card.textContent.toLowerCase();

			if (keyword && !text.includes(keyword)) {
				return false;
			}
			if (country && card.dataset.country !== country) {
				return false;
			}
			if (level && card.dataset.level !== level) {
				return false;
			}
			if (major && card.dataset.major !== major) {
				return false;
			}
			if (fee && card.dataset.feeBand !== fee) {
				return false;
			}
			if (eventType && card.dataset.eventType !== eventType) {
				return false;
			}
			if (newsCat && card.dataset.newsCat !== newsCat) {
				return false;
			}
			return true;
		};

		const render = (withLoading = false) => {
			if (withLoading) {
				grid.setAttribute('aria-busy', 'true');
				grid.innerHTML = skeletonMarkup();
				window.setTimeout(() => render(false), prefersReducedMotion ? 0 : 180);
				return;
			}

			const matches = cards.filter(cardMatches);
			const sortMode = sort?.value || 'featured';
			matches.sort((a, b) => {
				if (sortMode === 'title') {
					return (a.dataset.title || a.textContent).localeCompare(b.dataset.title || b.textContent, 'vi');
				}
				if (sortMode === 'deadline') {
					return (a.dataset.deadline || '').localeCompare(b.dataset.deadline || '');
				}
				return Number(a.dataset.order || 0) - Number(b.dataset.order || 0);
			});
			const limit = state.page * state.pageSize;
			grid.innerHTML = '';
			matches.slice(0, limit).forEach((card) => grid.appendChild(card));
			grid.removeAttribute('aria-busy');

			if (empty) {
				empty.hidden = matches.length > 0;
			}
			if (count) {
				count.textContent = `${matches.length} kết quả`;
			}
			if (loadMore) {
				loadMore.hidden = limit >= matches.length;
			}
		};

		finder.querySelectorAll('[data-filter-keyword]').forEach((input) => {
			input.addEventListener('input', () => {
				state.page = 1;
				render(true);
			});
		});

		finder.querySelectorAll('[data-filter-country], [data-filter-level], [data-filter-major], [data-filter-fee], [data-filter-event], [data-filter-news]').forEach((input) => {
			input.addEventListener('change', () => {
				state.page = 1;
				render(true);
			});
		});

		finder.querySelectorAll('[data-filter-reset]').forEach((button) => {
			button.addEventListener('click', () => {
				finder.querySelectorAll('input[type="radio"]').forEach((input) => {
					input.checked = input.value === '';
				});
				finder.querySelectorAll('[data-filter-keyword]').forEach((input) => {
					input.value = '';
				});
				state.page = 1;
				render(true);
			});
		});

		loadMore?.addEventListener('click', () => {
			state.page += 1;
			render(false);
		});

		sort?.addEventListener('change', () => {
			state.page = 1;
			render(true);
		});

		finder.querySelectorAll('[data-view-toggle]').forEach((button) => {
			button.addEventListener('click', () => {
				const view = button.dataset.viewToggle || 'grid';
				finder.querySelectorAll('[data-view-toggle]').forEach((item) => {
					item.classList.toggle('active', item === button);
					item.setAttribute('aria-pressed', item === button ? 'true' : 'false');
				});
				grid.classList.toggle('is-list', view === 'list');
			});
		});

		render(false);
	});

	document.querySelectorAll('[data-youtube-id]').forEach((facade) => {
		facade.addEventListener('click', () => {
			const id = facade.dataset.youtubeId || '';
			if (!id || facade.classList.contains('is-playing')) {
				return;
			}

			const iframe = document.createElement('iframe');
			iframe.src = `https://www.youtube-nocookie.com/embed/${encodeURIComponent(id)}?autoplay=1&rel=0`;
			iframe.title = facade.getAttribute('aria-label') || 'YouTube video';
				iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
			iframe.allowFullscreen = true;
			iframe.loading = 'lazy';

			facade.classList.add('is-playing');
			facade.replaceChildren(iframe);
		});
	});

	const galleryItems = document.querySelectorAll('[data-gallery-full]');
	if (galleryItems.length) {
		const lightbox = document.createElement('div');
		lightbox.className = 'event-lightbox';
		lightbox.setAttribute('role', 'dialog');
		lightbox.setAttribute('aria-modal', 'true');
		lightbox.setAttribute('aria-label', 'Ảnh sự kiện');
		lightbox.innerHTML = '<div class="event-lightbox-inner"><button class="event-lightbox-close" type="button" aria-label="Đóng ảnh"><span aria-hidden="true">×</span></button><img alt=""></div>';
		document.body.appendChild(lightbox);

		const image = lightbox.querySelector('img');
		const close = lightbox.querySelector('.event-lightbox-close');
		let lastFocus = null;

		const closeLightbox = () => {
			lightbox.classList.remove('open');
			image.removeAttribute('src');
			lastFocus?.focus();
		};

		galleryItems.forEach((item) => {
			item.addEventListener('click', () => {
				lastFocus = item;
				image.src = item.dataset.galleryFull || '';
				image.alt = item.dataset.galleryAlt || '';
				lightbox.classList.add('open');
				close?.focus();
			});
		});

		close?.addEventListener('click', closeLightbox);
		lightbox.addEventListener('click', (event) => {
			if (event.target === lightbox) {
				closeLightbox();
			}
		});
		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && lightbox.classList.contains('open')) {
				closeLightbox();
			}
		});
	}

	document.querySelectorAll('[data-service-stepper]').forEach((stepper) => {
		const steps = Array.from(stepper.querySelectorAll('[data-service-step]'));
		const panels = Array.from(stepper.querySelectorAll('[data-service-panel]'));
		const track = stepper.querySelector('.service-steps');

		if (!steps.length || !panels.length || !track) {
			return;
		}

		const clampIndex = (index) => Math.max(0, Math.min(index, steps.length - 1));
		const activeIndex = () => {
			const index = steps.findIndex((step) => step.classList.contains('is-active'));
			return index >= 0 ? index : 0;
		};

		const setActive = (index, focusStep = false) => {
			const next = clampIndex(index);
			const progress = steps.length > 1 ? next / (steps.length - 1) : 0;
			track.style.setProperty('--service-progress', String(progress));

			steps.forEach((step, stepIndex) => {
				const active = stepIndex === next;
				step.classList.toggle('is-active', active);
				step.setAttribute('aria-selected', active ? 'true' : 'false');
				step.tabIndex = active ? 0 : -1;
			});

			panels.forEach((panel, panelIndex) => {
				const active = panelIndex === next;
				panel.hidden = !active;
				panel.classList.toggle('is-active', active);
			});

			steps[next]?.scrollIntoView({ inline: 'center', block: 'nearest', behavior: prefersReducedMotion ? 'auto' : 'smooth' });

			if (focusStep) {
				steps[next]?.focus();
			}
		};

		steps.forEach((step, index) => {
			step.addEventListener('click', () => setActive(index));

			step.addEventListener('keydown', (event) => {
				if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
					return;
				}

				event.preventDefault();

				if (event.key === 'Home') {
					setActive(0, true);
					return;
				}

				if (event.key === 'End') {
					setActive(steps.length - 1, true);
					return;
				}

				setActive(activeIndex() + (event.key === 'ArrowRight' ? 1 : -1), true);
			});
		});

		let startX = 0;
		let startY = 0;

		track.addEventListener('pointerdown', (event) => {
			startX = event.clientX;
			startY = event.clientY;
		});

		track.addEventListener('pointerup', (event) => {
			const dx = event.clientX - startX;
			const dy = event.clientY - startY;

			if (Math.abs(dx) < 42 || Math.abs(dx) < Math.abs(dy)) {
				return;
			}

			setActive(activeIndex() + (dx < 0 ? 1 : -1), true);
		});

		setActive(activeIndex());
	});

	document.querySelectorAll('.vp-carousel').forEach((carousel) => {
		const track = carousel.querySelector('.vp-track');
		const prev = carousel.querySelector('[data-vp-prev]');
		const next = carousel.querySelector('[data-vp-next]');

		if (!track) {
			return;
		}

		const step = () => {
			const card = track.querySelector('.video-pair');
			return card ? card.getBoundingClientRect().width + 18 : 320;
		};

		const scrollByCard = (direction) => {
			track.scrollBy({ left: direction * step(), behavior: prefersReducedMotion ? 'auto' : 'smooth' });
		};

		prev?.addEventListener('click', () => scrollByCard(-1));
		next?.addEventListener('click', () => scrollByCard(1));

		carousel.addEventListener('keydown', (event) => {
			if (event.key === 'ArrowLeft') {
				scrollByCard(-1);
			}
			if (event.key === 'ArrowRight') {
				scrollByCard(1);
			}
		});

		let startX = 0;
		let startScroll = 0;
		let dragging = false;

		track.addEventListener('pointerdown', (event) => {
			dragging = true;
			startX = event.clientX;
			startScroll = track.scrollLeft;
			track.setPointerCapture(event.pointerId);
		});

		track.addEventListener('pointermove', (event) => {
			if (!dragging) {
				return;
			}
			track.scrollLeft = startScroll - (event.clientX - startX);
		});

		track.addEventListener('pointerup', () => {
			dragging = false;
		});
	});
})();
