/*
 * Laravel Web Analytics replay recorder — dependency-free, build-free.
 *
 * Privacy rules are enforced here before any bytes leave the browser:
 * every input value is removed, masked selectors are dropped, framework
 * state attributes are stripped, and only structural events (tag/id/class,
 * no text) are recorded. The server stores the chunk verbatim and never
 * parses it.
 */
(function () {
    'use strict';

    var config = window.LWA_REPLAY_CONFIG;
    var defaults = window.LWA_REPLAY_DEFAULTS || {};

    if (!config || !config.token || config.consent !== true) {
        return;
    }

    var settings = config.settings || {};
    var sessionKey = String(settings.session_key || '');
    if (!/^[A-Za-z0-9_-]{8,64}$/.test(sessionKey)) {
        return;
    }

    var path = String(settings.path || '');
    var excluded = settings.excluded_paths || [];
    for (var i = 0; i < excluded.length; i++) {
        if (matchesGlob(path, excluded[i])) {
            return;
        }
    }

    var sampleRate = Math.max(0, Math.min(1, Number(settings.sample_rate) || 0));
    var bucket = Number(settings.bucket || 0) % 10000;
    if (sampleRate <= 0 || bucket >= Math.floor(sampleRate * 10000)) {
        return;
    }

    var endpoint = String(settings.endpoint || defaults.endpoint || '/web-analytics/api/v1/replay/chunks');
    var flushMs = Math.max(1000, Number(defaults.flush_ms) || 5000);
    var maxBytes = Math.max(1024, Number(defaults.max_chunk_bytes) || 1000000);
    var maxMarkers = Math.max(1, Number(defaults.max_markers) || 25);
    var sequenceKey = 'lwa_replay_seq_' + sessionKey;
    var sequence = parseInt(sessionStorage.getItem(sequenceKey) || '0', 10) || 0;
    var startedAt = Date.now();
    var events = [];
    var stopped = false;

    function matchesGlob(value, pattern) {
        var escaped = String(pattern).replace(/[.+^${}()|[\]\\]/g, '\\$&').replace(/\*/g, '.*');
        try {
            return new RegExp('^' + escaped + '$').test(value);
        } catch (e) {
            return false;
        }
    }

    function short(value) {
        return String(value == null ? '' : value).slice(0, 100);
    }

    function describe(target) {
        if (!target || target.nodeType !== 1) {
            return { tag: 'unknown' };
        }
        var info = {
            tag: String(target.tagName || '').toLowerCase(),
            id: short(target.id || ''),
            cls: short(typeof target.className === 'string' ? target.className : '')
        };
        if (info.tag === 'input') {
            info.type = short(target.getAttribute('type') || 'text');
        }
        return info;
    }

    function sanitizedSnapshot() {
        var clone = document.documentElement.cloneNode(true);
        var hide = clone.querySelectorAll('[data-lwa-replay-ignore]');
        for (var h = 0; h < hide.length; h++) {
            if (hide[h].parentNode) {
                hide[h].parentNode.removeChild(hide[h]);
            }
        }

        var selectors = settings.masked_selectors || [];
        for (var s = 0; s < selectors.length; s++) {
            try {
                var masked = clone.querySelectorAll(selectors[s]);
                for (var m = 0; m < masked.length; m++) {
                    masked[m].textContent = '';
                    masked[m].removeAttribute('value');
                }
            } catch (e) {
                // Invalid selectors are ignored; masking inputs still applies.
            }
        }

        var fields = clone.querySelectorAll('input, textarea, select');
        for (var f = 0; f < fields.length; f++) {
            var field = fields[f];
            field.removeAttribute('value');
            field.removeAttribute('checked');
            field.removeAttribute('selected');
            if (field.tagName === 'TEXTAREA') {
                field.textContent = '';
            }
            if (field.tagName === 'SELECT') {
                var options = field.querySelectorAll('option');
                for (var o = 0; o < options.length; o++) {
                    options[o].removeAttribute('selected');
                }
            }
        }

        var all = clone.querySelectorAll('*');
        for (var n = 0; n < all.length; n++) {
            var node = all[n];
            var attrs = node.attributes;
            for (var a = attrs.length - 1; a >= 0; a--) {
                var name = attrs[a].name;
                if (name.indexOf('wire:') === 0 || name === 'wire:snapshot' ||
                    name === 'data-lwa-token' || name === 'data-lwa-public' ||
                    name === 'data-lwa-replay-ignore') {
                    node.removeAttribute(name);
                }
            }
        }

        return '<!doctype html>' + clone.outerHTML;
    }

    function record(type, target, extra) {
        if (stopped || events.length >= 200) {
            return;
        }
        var event = { at: Date.now() - startedAt, type: type, target: describe(target) };
        if (extra && typeof extra === 'object') {
            event.data = extra;
        }
        var json = JSON.stringify(event);
        if (json.length <= 2048) {
            events.push(event);
        }
    }

    events.push({ at: 0, type: 'snapshot', html: sanitizedSnapshot().slice(0, 180000) });

    var seenFormFields = Object.create(null);

    function formInfo(form) {
        return {
            id: short(form.id || ''),
            cls: short(typeof form.className === 'string' ? form.className : ''),
            method: short(form.getAttribute('method') || 'get')
        };
    }

    function fieldInfo(field) {
        return {
            tag: short(field.tagName || '').toLowerCase(),
            type: short(field.getAttribute('type') || ''),
            name: short(field.name || ''),
            id: short(field.id || ''),
            cls: short(typeof field.className === 'string' ? field.className : '')
        };
    }

    function formKey(form) {
        var info = formInfo(form);
        return info.id || info.cls || 'form';
    }

    function fieldKey(field) {
        var info = fieldInfo(field);
        return info.name || info.id || info.type || info.tag;
    }

    document.addEventListener('focusin', function (e) {
        var field = e.target;
        if (!field || field.nodeType !== 1 || !/^(INPUT|TEXTAREA|SELECT)$/.test(field.tagName)) {
            return;
        }
        var form = field.closest ? field.closest('form') : null;
        if (!form) {
            return;
        }
        var key = formKey(form) + '|' + fieldKey(field);
        if (seenFormFields[key]) {
            return;
        }
        seenFormFields[key] = true;
        record('field_focus', form, { form: formInfo(form), field: fieldInfo(field) });
    }, true);

    document.addEventListener('change', function (e) {
        var field = e.target;
        if (!field || field.nodeType !== 1 || !/^(INPUT|TEXTAREA|SELECT)$/.test(field.tagName)) {
            return;
        }
        var form = field.closest ? field.closest('form') : null;
        if (!form) {
            return;
        }
        var key = formKey(form) + '|' + fieldKey(field) + '|change';
        if (seenFormFields[key]) {
            return;
        }
        seenFormFields[key] = true;
        record('field_change', form, { form: formInfo(form), field: fieldInfo(field) });
    }, true);

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.tagName !== 'FORM') {
            return;
        }
        var valid = form.checkValidity ? form.checkValidity() : true;
        var invalid = 0;
        try {
            invalid = form.querySelectorAll(':invalid').length;
        } catch (err) {
            invalid = 0;
        }
        record('form_submit', form, {
            form: formInfo(form),
            valid: !!valid,
            invalid_fields: invalid
        });
    }, true);

    document.addEventListener('invalid', function (e) {
        var field = e.target;
        var form = field && field.closest ? field.closest('form') : null;
        if (!form) {
            return;
        }
        var validity = field.validity || {};
        record('form_error', form, {
            form: formInfo(form),
            field: fieldInfo(field),
            validity: {
                valueMissing: !!validity.valueMissing,
                typeMismatch: !!validity.typeMismatch,
                patternMismatch: !!validity.patternMismatch
            }
        });
    }, true);

    document.addEventListener('click', function (e) {
        record('click', e.target, {
            x: e.clientX || 0,
            y: e.clientY || 0,
            vw: window.innerWidth || 0,
            vh: window.innerHeight || 0
        });
    }, true);
    document.addEventListener('scroll', function () {
        record('scroll', document.body, {
            y: window.scrollY || 0,
            vh: window.innerHeight || 0,
            dh: document.documentElement.scrollHeight || 0
        });
    }, true);
    window.addEventListener('error', function (e) {
        record('error', e.target || null, { message: short(e.message || '') });
    });
    window.addEventListener('unhandledrejection', function () {
        record('error', null, { message: 'unhandledrejection' });
    });
    if ('PerformanceObserver' in window) {
        try {
            var observer = new PerformanceObserver(function (list) {
                var entries = list.getEntries();
                for (var i = 0; i < entries.length && i < 5; i++) {
                    record('web_vital', null, { name: short(entries[i].name), value: Number(entries[i].value || 0) });
                }
            });
            observer.observe({ type: 'largest-contentful-paint', buffered: true });
        } catch (e) {
            // Web vitals are optional; never block replay for them.
        }
    }

    function base64(bytes) {
        var binary = '';
        var chunk = 0x8000;
        for (var i = 0; i < bytes.length; i += chunk) {
            binary += String.fromCharCode.apply(null, bytes.subarray(i, i + chunk));
        }
        return btoa(binary);
    }

    function encode(payload) {
        var text = JSON.stringify(payload);
        if (typeof CompressionStream === 'function') {
            var stream = new Blob([text]).stream().pipeThrough(new CompressionStream('gzip'));
            return new Response(stream).arrayBuffer().then(function (buffer) {
                return { body: base64(new Uint8Array(buffer)), encoding: 'gzip' };
            });
        }
        return Promise.resolve({
            body: btoa(unescape(encodeURIComponent(text))),
            encoding: 'identity'
        });
    }

    function flush(final) {
        if (stopped || events.length === 0) {
            return Promise.resolve();
        }
        var batch = events.splice(0, events.length);
        var to = Date.now() - startedAt;
        var from = batch[0].at || 0;
        var markerTypes = [
            'click', 'scroll', 'web_vital', 'error',
            'field_focus', 'field_change', 'form_submit', 'form_error'
        ];
        var markers = batch.filter(function (event) {
            return event.type !== 'snapshot';
        }).slice(0, maxMarkers).map(function (event) {
            return {
                type: markerTypes.indexOf(event.type) >= 0 ? event.type : 'custom',
                at_ms: event.at,
                payload: { target: event.target, data: event.data || null }
            };
        });

        return encode({
            v: 1,
            session_key: sessionKey,
            from_ms: from,
            to_ms: to,
            events: batch
        }).then(function (encoded) {
            if (encoded.body.length > maxBytes) {
                return;
            }
            sequence += 1;
            sessionStorage.setItem(sequenceKey, String(sequence));
            var body = {
                token: config.token,
                session_key: sessionKey,
                sequence: sequence,
                encoding: encoded.encoding,
                from_ms: from,
                to_ms: to,
                event_count: batch.length,
                chunk: encoded.body,
                markers: markers
            };
            var init = {
                method: 'POST',
                mode: 'cors',
                credentials: 'omit',
                keepalive: !!final,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            };
            if (final && navigator.sendBeacon) {
                navigator.sendBeacon(endpoint, new Blob([JSON.stringify(body)], { type: 'application/json' }));
                return;
            }
            return fetch(endpoint, init).catch(function () {});
        }).catch(function () {
            stopped = true;
        });
    }

    var timer = setInterval(function () { flush(false); }, flushMs);
    window.addEventListener('pagehide', function () {
        stopped = true;
        clearInterval(timer);
        flush(true);
    });
})();
