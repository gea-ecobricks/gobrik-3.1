(function () {
    'use strict';

    const ready = (callback) => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
        } else {
            callback();
        }
    };

    const normaliseBoolean = (value) => {
        if (typeof value === 'boolean') return value;
        if (typeof value === 'number') return value !== 0;
        if (typeof value === 'string') {
            const trimmed = value.trim().toLowerCase();
            return trimmed === '1' || trimmed === 'true' || trimmed === 'yes';
        }
        return Boolean(value);
    };

    const resolveBuwanaId = (form) => {
        return form?.dataset?.buwanaId
            || form?.dataset?.userId
            || form?.querySelector('input[name="buwana_id" i]')?.value
            || form?.querySelector('input[name="user_id" i]')?.value
            || window?.EARTHCAL_BUWANA_ID
            || window?.EARTHCAL?.buwanaId
            || window?.buwanaId
            || null;
    };

    const resolveLabel = (input) => {
        if (!input) return '';
        const dataName = input.dataset?.calendarName;
        if (dataName) return dataName;

        if (input.id) {
            const label = document.querySelector(`label[for="${CSS.escape(input.id)}"]`);
            if (label) return label.textContent.trim();
        }

        if (input.closest('label')) {
            return input.closest('label').textContent.trim();
        }

        return input.getAttribute('aria-label') || input.value || '';
    };

    ready(() => {
        const form = document.getElementById('personal-calendar-selection-form');
        if (!form) {
            return;
        }

        const buwanaId = resolveBuwanaId(form);
        if (!buwanaId) {
            console.warn('personal-calendar-selection-form: Unable to determine buwana_id.');
            return;
        }

        const fetchUrl = form.dataset?.fetchUrl
            || form.dataset?.calendarsUrl
            || form.getAttribute('data-fetch')
            || '../earthcal/grab_user_calendars.php';
        const toggleUrl = form.dataset?.toggleUrl
            || form.getAttribute('data-toggle')
            || '../earthcal/cal_active_toggle.php';

        const checkboxSelector = 'input[type="checkbox" i]';
        const toggles = Array.from(form.querySelectorAll(checkboxSelector)).filter((input) => !input.disabled);
        if (!toggles.length) {
            return;
        }

        const stateCache = new Map();

        const applyStates = (calendars) => {
            if (!Array.isArray(calendars)) return;
            calendars.forEach((calendar) => {
                if (!calendar) return;
                const id = String(calendar.calendar_id ?? calendar.id ?? calendar.calendarID ?? '');
                if (!id) return;
                const isActive = normaliseBoolean(calendar.calendar_active ?? calendar.is_active ?? calendar.active);
                stateCache.set(id, isActive);
            });

            toggles.forEach((toggle) => {
                const calendarId = String(toggle.dataset?.calendarId || toggle.value || '');
                if (!calendarId) return;
                if (!stateCache.has(calendarId)) return;
                const active = stateCache.get(calendarId);
                toggle.checked = active;
                toggle.dataset.activeState = active ? '1' : '0';
            });
        };

        const loadCalendarStates = async () => {
            try {
                const response = await fetch(fetchUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ buwana_id: Number(buwanaId) || buwanaId })
                });

                if (!response.ok) {
                    throw new Error(`Request failed with status ${response.status}`);
                }

                const payload = await response.json();
                const calendars = payload.calendars
                    || payload.personal_calendars
                    || payload.data
                    || [];

                applyStates(calendars);
            } catch (error) {
                console.error('Unable to load personal calendar activation states.', error);
            }
        };

        const sendToggle = async (input) => {
            const calendarId = input.dataset?.calendarId || input.value;
            if (!calendarId) {
                console.warn('personal-calendar-selection-form: Missing calendar_id for toggle.');
                return;
            }

            const nextState = input.checked ? '1' : '0';
            const previousState = input.dataset.activeState ?? (input.checked ? '1' : '0');
            const label = resolveLabel(input);

            try {
                const response = await fetch(toggleUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({
                        buwana_id: Number(buwanaId) || buwanaId,
                        calendar_id: Number(calendarId) || calendarId,
                        active: nextState
                    })
                });

                if (!response.ok) {
                    throw new Error(`Request failed with status ${response.status}: ${await response.text()}`);
                }

                const payload = await response.json();
                if (!payload.success) {
                    throw new Error(payload.message || 'Unknown toggle error');
                }

                input.dataset.activeState = nextState;
                stateCache.set(String(calendarId), nextState === '1');

                if (nextState === '0') {
                    console.info(`Calendar "${label}" has been toggled inactive on the server.`, payload);
                } else {
                    console.info(`Calendar "${label}" is now active.`, payload);
                }
            } catch (error) {
                input.checked = previousState === '1';
                console.error('Unable to update calendar active state.', error);
            }
        };

        toggles.forEach((toggle) => {
            toggle.addEventListener('change', () => sendToggle(toggle));
        });

        loadCalendarStates();
    });
})();
