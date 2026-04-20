(function () {
    'use strict';

    const TAB_KEY = 'je_ai_emails_active_tab';

    let isRestoringForm = false;
    let saveFormStateTimer = null;

    const state = {
        currentEmailId: null,
        subjectApproved: false,
        bodyApproved: false,
        subjectThread: [],
        bodyThread: [],
        loading: false,
        lastDebugData: null,
    };

    const icons = {
        clipboard: '<svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" /></svg>',
        document: '<svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>',
        eye: '<svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>',
        trash: '<svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.261 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>',
        duplicate: '<svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" /></svg>',
    };

    function apiUrl(path) {
        return (typeof jeAiEmails !== 'undefined' && jeAiEmails.restUrl ? jeAiEmails.restUrl : '') + path.replace(/^\//, '');
    }

    function nonceHeaders() {
        const headers = { 'Content-Type': 'application/json' };
        if (typeof jeAiEmails !== 'undefined' && jeAiEmails.nonce) {
            headers['X-WP-Nonce'] = jeAiEmails.nonce;
        }
        return headers;
    }

    function restErrorMessage(data, res) {
        if (data && data.message) return data.message;
        if (data && data.code) return String(data.code);
        return res.statusText || 'Request failed';
    }

    async function apiPost(route, body) {
        const res = await fetch(apiUrl(route), {
            method: 'POST',
            credentials: 'same-origin',
            headers: nonceHeaders(),
            body: JSON.stringify(body),
        });
        const data = await res.json().catch(function () {
            return {};
        });
        if (!res.ok) {
            throw new Error(restErrorMessage(data, res));
        }
        return data;
    }

    async function apiGet(route) {
        const res = await fetch(apiUrl(route), {
            method: 'GET',
            credentials: 'same-origin',
            headers: nonceHeaders(),
        });
        const data = await res.json().catch(function () {
            return {};
        });
        if (!res.ok) {
            throw new Error(restErrorMessage(data, res));
        }
        return data;
    }

    async function apiDelete(route) {
        const res = await fetch(apiUrl(route), {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: nonceHeaders(),
        });
        const data = await res.json().catch(function () {
            return {};
        });
        if (!res.ok) {
            throw new Error(restErrorMessage(data, res));
        }
        return data;
    }

    function stripWpBlockComments(markup) {
        if (!markup) return '';
        return String(markup).replace(/<!--\s*\/?wp:[\s\S]*?-->/g, '\n').trim();
    }

    function renderDebug(label, messagesArray) {
        state.lastDebugData = { label: label, messages: messagesArray };
        const empty = document.getElementById('je-debug-empty');
        const msgsEl = document.getElementById('je-debug-messages');
        const actionsEl = document.getElementById('je-debug-actions');
        if (!msgsEl) return;

        msgsEl.innerHTML = '';
        const heading = document.createElement('p');
        heading.className = 'je-debug-label';
        heading.textContent = 'Last call: ' + label;
        msgsEl.appendChild(heading);

        (messagesArray || []).forEach(function (msg) {
            const block = document.createElement('div');
            block.className = 'je-debug-msg je-debug-msg--' + (msg.role || 'unknown');
            const roleEl = document.createElement('span');
            roleEl.className = 'je-debug-role';
            roleEl.textContent = (msg.role || 'unknown').toUpperCase();
            const contentEl = document.createElement('pre');
            contentEl.className = 'je-debug-content';
            contentEl.textContent = msg.content || '';
            block.appendChild(roleEl);
            block.appendChild(contentEl);
            msgsEl.appendChild(block);
        });

        if (empty) empty.hidden = true;
        msgsEl.hidden = false;
        if (actionsEl) actionsEl.hidden = false;

        const body = document.getElementById('je-debug-body');
        const toggle = document.getElementById('je-debug-toggle');
        if (body) body.hidden = false;
        if (toggle) {
            toggle.setAttribute('aria-expanded', 'true');
            toggle.classList.add('is-open');
        }
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('#je-points-list .je-point-row');
        rows.forEach(function (row) {
            const btn = row.querySelector('.je-remove-point');
            if (btn) btn.hidden = rows.length <= 1;
        });
    }

    function addPointRow() {
        const list = document.getElementById('je-points-list');
        if (!list) return;
        const row = document.createElement('div');
        row.className = 'je-point-row';
        row.innerHTML =
            '<input type="text" class="je-input je-point-input" placeholder="Add another important point…" />' +
            '<button type="button" class="je-btn je-btn-ghost je-btn-icon je-remove-point" title="Remove" tabindex="-1">' +
            '<svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>' +
            '</button>';
        row.querySelector('.je-remove-point').addEventListener('click', function () {
            row.remove();
            updateRemoveButtons();
            saveFormState();
        });
        list.appendChild(row);
        updateRemoveButtons();
        row.querySelector('input').focus();
        if (!isRestoringForm) {
            saveFormState();
        }
    }

    function saveFormState() {
        clearTimeout(saveFormStateTimer);
        saveFormStateTimer = setTimeout(function () {
            const points = Array.from(document.querySelectorAll('#je-points-list .je-point-input')).map(function (i) {
                return i.value;
            });

            const baseEmail = document.getElementById('je-base-email');
            const designNotes = document.getElementById('je-design-notes');
            const emailAboutField = document.getElementById('je-email-about');

            apiPost('draft-state', {
                points: points,
                baseEmail: baseEmail ? baseEmail.value : '',
                designNotes: designNotes ? designNotes.value : '',
                emailAbout: emailAboutField ? emailAboutField.value : '',
                urls: [
                    {
                        url: document.getElementById('je-url-1') ? document.getElementById('je-url-1').value : '',
                        text: document.getElementById('je-url-text-1') ? document.getElementById('je-url-text-1').value : '',
                    },
                    {
                        url: document.getElementById('je-url-2') ? document.getElementById('je-url-2').value : '',
                        text: document.getElementById('je-url-text-2') ? document.getElementById('je-url-text-2').value : '',
                    },
                ],
            }).catch(function () {
                /* silent fail — non-critical */
            });
        }, 800);
    }

    async function restoreFormState() {
        try {
            const data = await apiGet('draft-state');
            if (!data) return;

            isRestoringForm = true;
            try {
                if (Array.isArray(data.points) && data.points.length) {
                    const list = document.getElementById('je-points-list');
                    if (list) {
                        const existingInputs = list.querySelectorAll('.je-point-input');
                        data.points.forEach(function (val, i) {
                            if (existingInputs[i]) {
                                existingInputs[i].value = val;
                            } else {
                                addPointRow();
                                const inputs = list.querySelectorAll('.je-point-input');
                                inputs[inputs.length - 1].value = val;
                            }
                        });
                        updateRemoveButtons();
                    }
                }

                const baseEmail = document.getElementById('je-base-email');
                if (baseEmail && data.baseEmail) {
                    baseEmail.value = data.baseEmail;
                }

                const designNotes = document.getElementById('je-design-notes');
                if (designNotes && data.designNotes) {
                    designNotes.value = data.designNotes;
                }

                const emailAbout = document.getElementById('je-email-about');
                if (emailAbout && data.emailAbout) {
                    emailAbout.value = data.emailAbout;
                }

                if (Array.isArray(data.urls)) {
                    data.urls.forEach(function (u, i) {
                        const n = i + 1;
                        const urlEl = document.getElementById('je-url-' + n);
                        const textEl = document.getElementById('je-url-text-' + n);
                        if (urlEl && u && typeof u.url === 'string') {
                            urlEl.value = u.url;
                        }
                        if (textEl && u && typeof u.text === 'string') {
                            textEl.value = u.text;
                        }
                    });
                }
            } finally {
                isRestoringForm = false;
            }
        } catch (e) {
            /* silent fail */
        }
    }

    function clearFormState() {
        clearTimeout(saveFormStateTimer);
        saveFormStateTimer = null;
        apiPost('draft-state', {
            points: ['', '', ''],
            baseEmail: '',
            designNotes: '',
            emailAbout: '',
            urls: [
                { url: '', text: '' },
                { url: '', text: '' },
            ],
        }).catch(function () {});
    }

    function setInlineSpinner(id, show) {
        var el = document.getElementById(id);
        if (!el) {
            return;
        }
        el.classList.toggle('is-visible', !!show);
        el.setAttribute('aria-hidden', show ? 'false' : 'true');
    }

    function revisionCount(thread) {
        if (!Array.isArray(thread)) return 0;
        const n = thread.filter(function (m) {
            return m.role === 'assistant';
        }).length;
        return Math.max(0, n - 1);
    }

    function setLoading(on) {
        state.loading = !!on;
        document.body.classList.toggle('je-is-loading', state.loading);
        refreshActionButtons();
    }

    function setReviseLoading(type, isLoading) {
        const btn = document.getElementById('je-btn-revise-' + type);
        const textarea = document.getElementById(type === 'subject' ? 'je-subject-feedback' : 'je-body-feedback');
        if (!btn) {
            return;
        }

        if (isLoading) {
            btn.disabled = true;
            btn.dataset.originalText = btn.innerHTML;
            btn.innerHTML =
                '<svg class="je-spinner-inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="je-spinner-track" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/><path class="je-spinner-head" fill="none" d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>' +
                '<span>Revising…</span>';
            if (textarea) {
                textarea.disabled = true;
            }
        } else {
            if (btn.dataset.originalText) {
                btn.innerHTML = btn.dataset.originalText;
                delete btn.dataset.originalText;
            }
            if (textarea) {
                textarea.disabled = type === 'subject' ? state.subjectApproved : state.bodyApproved;
            }
            refreshActionButtons();
        }
    }

    function refreshActionButtons() {
        const on = state.loading;
        const g = document.getElementById('je-btn-generate');
        if (g) g.disabled = on;
        const rs = document.getElementById('je-btn-revise-subject');
        if (rs) rs.disabled = on || state.subjectApproved;
        const rb = document.getElementById('je-btn-revise-body');
        if (rb) rb.disabled = on || state.bodyApproved;
        const as = document.getElementById('je-btn-approve-subject');
        if (as) as.disabled = on || state.subjectApproved;
        const ab = document.getElementById('je-btn-approve-body');
        if (ab) ab.disabled = on || state.bodyApproved;
        const sh = document.getElementById('je-btn-save-history');
        if (sh) sh.disabled = on || !(state.subjectApproved && state.bodyApproved);
    }

    function updateRevisionBadges() {
        const sEl = document.getElementById('je-subject-revisions');
        const bEl = document.getElementById('je-body-revisions');
        const sN = revisionCount(state.subjectThread);
        const bN = revisionCount(state.bodyThread);
        if (sEl) sEl.textContent = sN + ' revisions';
        if (bEl) bEl.textContent = bN + ' revisions';
    }

    function setSubjectApproved(on) {
        state.subjectApproved = !!on;
        const panel = document.getElementById('je-subject-panel');
        const lock = document.getElementById('je-subject-lock');
        const input = document.getElementById('je-subject-input');
        const fb = document.getElementById('je-subject-feedback');
        const btnA = document.getElementById('je-btn-approve-subject');
        if (panel) panel.classList.toggle('is-approved', on);
        if (lock) lock.hidden = !on;
        if (input) input.readOnly = on;
        if (fb) {
            fb.disabled = on;
        }
        if (btnA) btnA.classList.toggle('is-done', on);
        refreshActionButtons();
        updateSaveBar();
    }

    function setBodyApproved(on) {
        state.bodyApproved = !!on;
        const panel = document.getElementById('je-body-panel');
        const lock = document.getElementById('je-body-lock');
        const fb = document.getElementById('je-body-feedback');
        const btnA = document.getElementById('je-btn-approve-body');
        if (panel) panel.classList.toggle('is-approved', on);
        if (lock) lock.hidden = !on;
        if (fb) fb.disabled = on;
        if (btnA) btnA.classList.toggle('is-done', on);
        refreshActionButtons();
        updateSaveBar();
    }

    function updateSaveBar() {
        const bar = document.getElementById('je-save-bar');
        if (!bar) return;
        bar.hidden = !(state.subjectApproved && state.bodyApproved);
    }

    function resetCreateForm() {
        clearFormState();
        state.currentEmailId = null;
        state.subjectThread = [];
        state.bodyThread = [];
        setSubjectApproved(false);
        setBodyApproved(false);
        const list = document.getElementById('je-points-list');
        if (list) {
            list.innerHTML = '';
            [
                'e.g. Webinar is Thursday March 26th at 1pm Eastern',
                'e.g. Sale price is $147, ends March 30th',
                'e.g. Purchase link: https://jazzedge.academy/item/...',
            ].forEach(function (placeholder) {
                const row = document.createElement('div');
                row.className = 'je-point-row';
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'je-input je-point-input';
                input.placeholder = placeholder;
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'je-btn je-btn-ghost je-btn-icon je-remove-point';
                btn.title = 'Remove';
                btn.setAttribute('tabindex', '-1');
                btn.innerHTML =
                    '<svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>';
                btn.addEventListener('click', function () {
                    row.remove();
                    updateRemoveButtons();
                    saveFormState();
                });
                row.appendChild(input);
                row.appendChild(btn);
                list.appendChild(row);
            });
            updateRemoveButtons();
        }
        const baseEmail = document.getElementById('je-base-email');
        if (baseEmail) baseEmail.value = '';
        const designNotes = document.getElementById('je-design-notes');
        if (designNotes) designNotes.value = '';
        const emailAboutEl = document.getElementById('je-email-about');
        if (emailAboutEl) emailAboutEl.value = '';
        for (let i = 1; i <= 2; i++) {
            const urlEl = document.getElementById('je-url-' + i);
            const textEl = document.getElementById('je-url-text-' + i);
            if (urlEl) urlEl.value = '';
            if (textEl) textEl.value = '';
        }
        document.getElementById('je-subject-input').value = '';
        document.getElementById('je-body-raw').value = '';
        document.getElementById('je-body-preview').innerHTML = '';
        document.getElementById('je-subject-feedback').value = '';
        document.getElementById('je-body-feedback').value = '';
        document.getElementById('je-editor-panels').hidden = true;
        updateRevisionBadges();
        setRawPreview('raw');
    }

    function duplicateEmail(row) {
        resetCreateForm();

        const list = document.getElementById('je-points-list');
        if (list) {
            list.innerHTML = '';
            let savedPoints = [];
            try {
                if (row.prompt_points) {
                    const parsed = JSON.parse(row.prompt_points);
                    if (Array.isArray(parsed)) {
                        savedPoints = parsed;
                    }
                }
            } catch (e) {}

            if (!savedPoints.length) {
                savedPoints = ['Based on previous email with subject: ' + (row.subject || ''), '', ''];
            }

            while (savedPoints.length < 3) {
                savedPoints.push('');
            }

            savedPoints.forEach(function (val) {
                const rowEl = document.createElement('div');
                rowEl.className = 'je-point-row';
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'je-input je-point-input';
                input.value = val;
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'je-btn je-btn-ghost je-btn-icon je-remove-point';
                btn.title = 'Remove';
                btn.setAttribute('tabindex', '-1');
                btn.innerHTML =
                    '<svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>';
                btn.addEventListener('click', function () {
                    rowEl.remove();
                    updateRemoveButtons();
                    saveFormState();
                });
                rowEl.appendChild(input);
                rowEl.appendChild(btn);
                list.appendChild(rowEl);
            });
            updateRemoveButtons();
        }

        const emailAboutEl = document.getElementById('je-email-about');
        if (emailAboutEl) {
            emailAboutEl.value = row.prompt_about || '';
        }

        const baseEmailEl = document.getElementById('je-base-email');
        if (baseEmailEl) {
            baseEmailEl.value = row.prompt_base_email || row.body || '';
        }

        const designNotesEl = document.getElementById('je-design-notes');
        if (designNotesEl) {
            designNotesEl.value = row.prompt_design || '';
        }

        let savedUrls = [];
        try {
            if (row.prompt_urls) {
                const pu = JSON.parse(row.prompt_urls);
                if (Array.isArray(pu)) {
                    savedUrls = pu;
                }
            }
        } catch (e) {}
        for (let i = 1; i <= 2; i++) {
            const u = savedUrls[i - 1] || {};
            const urlEl = document.getElementById('je-url-' + i);
            const textEl = document.getElementById('je-url-text-' + i);
            if (urlEl) urlEl.value = u.url || '';
            if (textEl) textEl.value = u.text || '';
        }

        saveFormState();

        switchTab('create');
        window.scrollTo({ top: 0, behavior: 'smooth' });

        const baseSection = baseEmailEl ? baseEmailEl.closest('.je-prompt-section') : null;
        if (baseSection) {
            baseSection.classList.add('je-section-highlight');
            setTimeout(function () {
                baseSection.classList.remove('je-section-highlight');
            }, 1800);
        }
    }

    function setRawPreview(mode) {
        const raw = document.getElementById('je-body-raw');
        const prev = document.getElementById('je-body-preview');
        const toggles = document.querySelectorAll('.je-toggle');
        toggles.forEach(function (t) {
            t.classList.toggle('is-active', t.getAttribute('data-view') === mode);
        });
        if (mode === 'preview' && raw && prev) {
            raw.hidden = true;
            prev.hidden = false;
            prev.innerHTML = stripWpBlockComments(raw.value);
        } else if (raw && prev) {
            raw.hidden = false;
            prev.hidden = true;
        }
    }

    function showPanelsAfterGenerate(data) {
        state.currentEmailId = data.id;
        state.subjectThread = data.subject_thread || [];
        state.bodyThread = data.body_thread || [];
        document.getElementById('je-subject-input').value = data.subject || '';
        document.getElementById('je-body-raw').value = data.body || '';
        document.getElementById('je-editor-panels').hidden = false;
        setSubjectApproved(false);
        setBodyApproved(false);
        updateRevisionBadges();
        setRawPreview('raw');
    }

    async function onGenerate() {
        const pointInputs = document.querySelectorAll('#je-points-list .je-point-input');
        const rawPoints = Array.from(pointInputs).map(function (i) {
            return i.value;
        });
        const points = rawPoints
            .map(function (v) {
                return v.trim();
            })
            .filter(function (v) {
                return v !== '';
            });

        const baseEmailEl = document.getElementById('je-base-email');
        const designNotesEl = document.getElementById('je-design-notes');
        const emailAboutElGen = document.getElementById('je-email-about');
        const baseEmail = baseEmailEl ? baseEmailEl.value.trim() : '';
        const designNotes = designNotesEl ? designNotesEl.value.trim() : '';

        if (!points.length) {
            window.alert('Please enter at least one important point to guide the email.');
            return;
        }

        let prompt = '';

        prompt += 'TASK: Write a BRAND NEW marketing email using ONLY the important points below as your content source.\n\n';

        prompt += 'IMPORTANT POINTS (this is your content — build the email around these):\n';
        points.forEach(function (p, i) {
            prompt += (i + 1) + '. ' + p + '\n';
        });

        const emailAboutText = emailAboutElGen ? emailAboutElGen.value.trim() : '';

        if (emailAboutText) {
            prompt += '\nEMAIL TOPIC & CONTEXT (use this to shape the email along with the important points above):\n';
            prompt += emailAboutText + '\n';
        }

        if (baseEmail) {
            prompt += '\n---\n';
            prompt += 'STYLE/TONE REFERENCE ONLY — DO NOT USE THIS AS CONTENT:\n';
            prompt += 'The content below is provided so you can match the writing voice, sentence rhythm, and conversational style ONLY.\n';
            prompt += 'Do NOT copy any sentences, phrases, subject matter, or structure from it.\n';
            prompt += 'Do NOT reformat or rewrite it. Treat it purely as a writing style sample.\n';
            prompt += '---\n';
            prompt += baseEmail + '\n';
            prompt += '---\n';
        }

        if (designNotes) {
            prompt += '\nDESIGN REQUIREMENTS (apply these to the new email):\n' + designNotes + '\n';
        }

        const urlsForPrompt = [];
        for (let i = 1; i <= 2; i++) {
            const urlInput = document.getElementById('je-url-' + i);
            const textInput = document.getElementById('je-url-text-' + i);
            const url = urlInput ? urlInput.value.trim() : '';
            const text = textInput ? textInput.value.trim() : '';
            if (url) {
                urlsForPrompt.push({ url: url, text: text || url });
            }
        }

        if (urlsForPrompt.length) {
            prompt +=
                '\nIMPORTANT LINKS — these must appear in the email as hyperlinks. Do NOT show the raw URL — always wrap in an anchor tag with the provided link text:\n';
            urlsForPrompt.forEach(function (u, i) {
                prompt += i + 1 + '. Link text: "' + u.text + '" → URL: ' + u.url + '\n';
            });
            prompt +=
                'Use exactly: <a href="' +
                (urlsForPrompt[0] ? urlsForPrompt[0].url : '') +
                '">link text</a> inside a paragraph block.\n';
        }

        prompt +=
            '\nRemember: The email you write must be based entirely on the IMPORTANT POINTS above. The reference content is voice guidance only.';

        const urlsPayload = [];
        for (let j = 1; j <= 2; j++) {
            const uIn = document.getElementById('je-url-' + j);
            const tIn = document.getElementById('je-url-text-' + j);
            urlsPayload.push({
                url: uIn ? uIn.value : '',
                text: tIn ? tIn.value : '',
            });
        }

        setLoading(true);
        setInlineSpinner('je-spinner-generate', true);
        try {
            const data = await apiPost('generate', {
                prompt: prompt,
                points: rawPoints,
                about: emailAboutElGen ? emailAboutElGen.value : '',
                baseEmail: baseEmailEl ? baseEmailEl.value : '',
                design: designNotesEl ? designNotesEl.value : '',
                urls: urlsPayload,
            });
            showPanelsAfterGenerate(data);
            if (data.debug) {
                renderDebug('Generate — Subject messages', data.debug.subject_messages);
            }
        } catch (e) {
            window.alert(e.message);
        } finally {
            setLoading(false);
            setInlineSpinner('je-spinner-generate', false);
        }
    }

    async function onReviseSubject() {
        if (!state.currentEmailId) return;
        const feedback = document.getElementById('je-subject-feedback').value.trim();
        if (!feedback) return;
        setReviseLoading('subject', true);
        setLoading(true);
        try {
            const data = await apiPost('revise-subject', { id: state.currentEmailId, feedback: feedback });
            document.getElementById('je-subject-input').value = data.subject;
            state.subjectThread = data.subject_thread || [];
            document.getElementById('je-subject-feedback').value = '';
            setSubjectApproved(false);
            updateRevisionBadges();
            if (data.debug) renderDebug('Revise Subject', data.debug.messages_sent);
        } catch (e) {
            window.alert(e.message);
        } finally {
            setLoading(false);
            setReviseLoading('subject', false);
        }
    }

    async function onReviseBody() {
        if (!state.currentEmailId) return;
        const feedback = document.getElementById('je-body-feedback').value.trim();
        if (!feedback) return;
        setReviseLoading('body', true);
        setLoading(true);
        try {
            const data = await apiPost('revise-body', { id: state.currentEmailId, feedback: feedback });
            document.getElementById('je-body-raw').value = data.body;
            state.bodyThread = data.body_thread || [];
            document.getElementById('je-body-feedback').value = '';
            setBodyApproved(false);
            updateRevisionBadges();
            const activeToggle = document.querySelector('.je-body-view-toggle .je-toggle.is-active');
            setRawPreview(activeToggle ? activeToggle.getAttribute('data-view') : 'raw');
            if (data.debug) renderDebug('Revise Body', data.debug.messages_sent);
        } catch (e) {
            window.alert(e.message);
        } finally {
            setLoading(false);
            setReviseLoading('body', false);
        }
    }

    async function onApprove(field) {
        if (!state.currentEmailId) return;
        const payload = { id: state.currentEmailId, field: field };
        if (field === 'subject') {
            payload.value = document.getElementById('je-subject-input').value;
        }
        if (field === 'body') {
            payload.value = document.getElementById('je-body-raw').value;
        }
        setLoading(true);
        try {
            await apiPost('approve', payload);
            if (field === 'subject') setSubjectApproved(true);
            if (field === 'body') setBodyApproved(true);
        } catch (e) {
            window.alert(e.message);
        } finally {
            setLoading(false);
        }
    }

    async function onSaveHistory() {
        if (!state.currentEmailId) return;
        setLoading(true);
        try {
            await apiPost('save', {
                id: state.currentEmailId,
                subject: document.getElementById('je-subject-input').value,
                body: document.getElementById('je-body-raw').value,
            });
            resetCreateForm();
        } catch (e) {
            window.alert(e.message);
        } finally {
            setLoading(false);
        }
    }

    function flashCopied(btn) {
        const orig = btn.innerHTML;
        btn.classList.add('je-copied');
        btn.innerHTML = '<span class="je-copied-label">Copied!</span>';
        setTimeout(function () {
            btn.classList.remove('je-copied');
            btn.innerHTML = orig;
        }, 1600);
    }

    async function copyText(text, btn) {
        try {
            await navigator.clipboard.writeText(text);
            flashCopied(btn);
        } catch (e) {
            window.alert('Could not copy.');
        }
    }

    function truncate(str, max) {
        if (!str) return '';
        str = String(str);
        return str.length <= max ? str : str.slice(0, max) + '…';
    }

    function formatDate(iso) {
        if (!iso) return '';
        try {
            const d = new Date(iso.replace(' ', 'T'));
            const now = new Date();
            const diffMs = now - d;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return diffMins + 'm ago';
            if (diffHours < 24) return diffHours + 'h ago';
            if (diffDays === 1) return 'Yesterday';
            if (diffDays < 7) return diffDays + 'd ago';

            const opts = { month: 'short', day: 'numeric' };
            if (d.getFullYear() !== now.getFullYear()) {
                opts.year = 'numeric';
            }
            return d.toLocaleDateString(undefined, opts);
        } catch (e) {
            return iso;
        }
    }

    async function loadHistory() {
        const tbody = document.getElementById('je-history-tbody');
        const empty = document.getElementById('je-history-empty');
        const table = document.getElementById('je-history-table');
        const loadEl = document.getElementById('je-history-loading');
        loadEl.hidden = false;
        try {
            const rows = await apiGet('emails');
            tbody.innerHTML = '';
            if (!rows.length) {
                empty.hidden = false;
                table.style.display = 'none';
            } else {
                empty.hidden = true;
                table.style.display = '';
                rows.forEach(function (row) {
                    const tr = document.createElement('tr');
                    tr.dataset.emailId = String(row.id);
                    tr.innerHTML =
                        '<td class="je-td-date">' +
                        escapeHtml(formatDate(row.created_at)) +
                        '</td>' +
                        '<td class="je-td-subject">' +
                        escapeHtml(truncate(row.subject, 48)) +
                        '</td>' +
                        '<td class="je-td-actions">' +
                        '<div class="je-row-actions">' +
                        '<button type="button" class="je-btn je-btn-ghost je-copy-subj" title="Copy subject">' +
                        icons.clipboard +
                        '</button>' +
                        '<button type="button" class="je-btn je-btn-ghost je-copy-body" title="Copy body">' +
                        icons.document +
                        '</button>' +
                        '<button type="button" class="je-btn je-btn-ghost je-view" title="View">' +
                        icons.eye +
                        '</button>' +
                        '<button type="button" class="je-btn je-btn-ghost je-duplicate" title="Duplicate">' +
                        icons.duplicate +
                        '</button>' +
                        '<button type="button" class="je-btn je-btn-ghost je-btn-danger je-delete" title="Delete">' +
                        icons.trash +
                        '</button>' +
                        '</div></td>';
                    tr.querySelector('.je-copy-subj').addEventListener('click', function () {
                        copyText(row.subject || '', this);
                    });
                    tr.querySelector('.je-copy-body').addEventListener('click', function () {
                        copyText(row.body || '', this);
                    });
                    tr.querySelector('.je-view').addEventListener('click', function () {
                        toggleExpandRow(tr, row);
                    });
                    tr.querySelector('.je-duplicate').addEventListener('click', function () {
                        duplicateEmail(row);
                    });
                    tr.querySelector('.je-delete').addEventListener('click', function () {
                        if (!window.confirm('Delete this email from history?')) return;
                        apiDelete('email/' + row.id)
                            .then(function () {
                                loadHistory();
                            })
                            .catch(function (e) {
                                window.alert(e.message);
                            });
                    });
                    tbody.appendChild(tr);
                });
            }
        } catch (e) {
            window.alert(e.message);
        } finally {
            loadEl.hidden = true;
        }
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function toggleExpandRow(tr, row) {
        const next = tr.nextElementSibling;
        if (next && next.classList.contains('je-expanded-row')) {
            next.remove();
            return;
        }
        const exp = document.createElement('tr');
        exp.className = 'je-expanded-row';
        const td = document.createElement('td');
        td.colSpan = 3;
        const wrap = document.createElement('div');
        wrap.className = 'je-expanded-inner';
        const cols = document.createElement('div');
        cols.className = 'je-expanded-cols';

        const colS = document.createElement('div');
        const lbS = document.createElement('label');
        lbS.className = 'je-label';
        lbS.textContent = 'Subject';
        const taS = document.createElement('textarea');
        taS.className = 'je-textarea';
        taS.readOnly = true;
        taS.rows = 3;
        taS.value = row.subject || '';
        const btnS = document.createElement('button');
        btnS.type = 'button';
        btnS.className = 'je-btn je-btn-secondary';
        btnS.textContent = 'Copy subject';
        btnS.addEventListener('click', function () {
            copyText(row.subject || '', btnS);
        });
        colS.appendChild(lbS);
        colS.appendChild(taS);
        colS.appendChild(btnS);

        const colB = document.createElement('div');
        const lbB = document.createElement('label');
        lbB.className = 'je-label';
        lbB.textContent = 'Body';
        const taB = document.createElement('textarea');
        taB.className = 'je-textarea je-textarea-code';
        taB.readOnly = true;
        taB.rows = 12;
        taB.value = row.body || '';
        const btnB = document.createElement('button');
        btnB.type = 'button';
        btnB.className = 'je-btn je-btn-secondary';
        btnB.textContent = 'Copy body';
        btnB.addEventListener('click', function () {
            copyText(row.body || '', btnB);
        });
        colB.appendChild(lbB);
        colB.appendChild(taB);
        colB.appendChild(btnB);

        cols.appendChild(colS);
        cols.appendChild(colB);
        wrap.appendChild(cols);
        td.appendChild(wrap);
        exp.appendChild(td);
        tr.parentNode.insertBefore(exp, tr.nextSibling);
    }

    function switchTab(name) {
        document.querySelectorAll('.je-tab').forEach(function (t) {
            const on = t.getAttribute('data-tab') === name;
            t.classList.toggle('is-active', on);
            t.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        document.querySelectorAll('.je-panel').forEach(function (p) {
            const on = p.getAttribute('data-panel') === name;
            p.classList.toggle('is-active', on);
            p.hidden = !on;
        });
        try {
            sessionStorage.setItem(TAB_KEY, name);
        } catch (e) {}
        if (name === 'history') {
            loadHistory();
        }
        if (name === 'settings') {
            loadSettings();
        }
    }

    async function loadSettings() {
        try {
            const s = await apiGet('settings');
            document.getElementById('je-custom-prompt').value = s.custom_system_prompt || '';
        } catch (e) {
            /* ignore */
        }
    }

    async function saveSettings() {
        const msg = document.getElementById('je-settings-msg');
        setInlineSpinner('je-spinner-settings', true);
        try {
            await apiPost('settings', {
                custom_system_prompt: document.getElementById('je-custom-prompt').value,
            });
            msg.textContent = 'Settings saved.';
            msg.className = 'je-help je-help-success';
            msg.hidden = false;
        } catch (e) {
            msg.textContent = e.message;
            msg.className = 'je-help je-help-error';
            msg.hidden = false;
        } finally {
            setInlineSpinner('je-spinner-settings', false);
        }
    }

    async function testAi() {
        const el = document.getElementById('je-test-result');
        setInlineSpinner('je-spinner-test', true);
        el.hidden = true;
        try {
            const r = await apiGet('test');
            el.hidden = false;
            if (r.success) {
                el.textContent = r.message || 'Connection successful.';
                el.className = 'je-test-result je-test-ok';
            } else {
                el.textContent = r.error || 'Connection failed.';
                el.className = 'je-test-result je-test-bad';
            }
        } catch (e) {
            el.hidden = false;
            el.textContent = e.message;
            el.className = 'je-test-result je-test-bad';
        } finally {
            setInlineSpinner('je-spinner-test', false);
        }
    }

    function init() {
        let initial = 'create';
        try {
            const s = sessionStorage.getItem(TAB_KEY);
            if (s === 'history' || s === 'settings' || s === 'create') initial = s;
        } catch (e) {}
        switchTab(initial);

        document.querySelectorAll('.je-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                switchTab(tab.getAttribute('data-tab'));
            });
        });

        document.querySelectorAll('.je-remove-point').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const row = btn.closest('.je-point-row');
                if (row) row.remove();
                updateRemoveButtons();
                saveFormState();
            });
        });
        const addPointBtn = document.getElementById('je-add-point');
        if (addPointBtn) addPointBtn.addEventListener('click', addPointRow);
        updateRemoveButtons();

        const newEmailBtn = document.getElementById('je-btn-new-email');
        if (newEmailBtn) {
            newEmailBtn.addEventListener('click', function () {
                const hasPointText = Array.from(document.querySelectorAll('#je-points-list .je-point-input')).some(function (el) {
                    return el.value.trim() !== '';
                });
                if (state.currentEmailId || hasPointText) {
                    if (!window.confirm('Clear everything and start a fresh email?')) return;
                }
                resetCreateForm();
            });
        }

        const pointsListEl = document.getElementById('je-points-list');
        if (pointsListEl) pointsListEl.addEventListener('input', saveFormState);

        const baseEmailEl = document.getElementById('je-base-email');
        if (baseEmailEl) baseEmailEl.addEventListener('input', saveFormState);

        const designNotesEl = document.getElementById('je-design-notes');
        if (designNotesEl) designNotesEl.addEventListener('input', saveFormState);

        const emailAboutEl = document.getElementById('je-email-about');
        if (emailAboutEl) emailAboutEl.addEventListener('input', saveFormState);

        document.querySelectorAll('.je-url-input, .je-url-text-input').forEach(function (el) {
            el.addEventListener('input', saveFormState);
        });

        restoreFormState();

        document.getElementById('je-btn-generate').addEventListener('click', onGenerate);
        document.getElementById('je-btn-revise-subject').addEventListener('click', onReviseSubject);
        document.getElementById('je-btn-revise-body').addEventListener('click', onReviseBody);
        document.getElementById('je-btn-approve-subject').addEventListener('click', function () {
            onApprove('subject');
        });
        document.getElementById('je-btn-approve-body').addEventListener('click', function () {
            onApprove('body');
        });
        document.getElementById('je-btn-save-history').addEventListener('click', onSaveHistory);

        document.querySelectorAll('.je-toggle').forEach(function (t) {
            t.addEventListener('click', function () {
                setRawPreview(t.getAttribute('data-view'));
            });
        });

        document.getElementById('je-btn-save-settings').addEventListener('click', saveSettings);
        document.getElementById('je-btn-test-ai').addEventListener('click', testAi);

        const debugToggle = document.getElementById('je-debug-toggle');
        if (debugToggle) {
            debugToggle.addEventListener('click', function () {
                const body = document.getElementById('je-debug-body');
                if (!body) return;
                body.hidden = !body.hidden;
                const expanded = !body.hidden;
                debugToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                debugToggle.classList.toggle('is-open', expanded);
            });
        }
        const debugCopy = document.getElementById('je-debug-copy');
        if (debugCopy) {
            debugCopy.addEventListener('click', function () {
                if (!state.lastDebugData || !state.lastDebugData.messages) return;
                copyText(JSON.stringify(state.lastDebugData.messages, null, 2), debugCopy);
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
