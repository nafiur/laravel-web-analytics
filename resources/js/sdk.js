/**
 * LwaWebAnalytics — vanilla JavaScript tracker (v3.2 frontend SDK).
 *
 * Loaded from the package route (`/web-analytics/sdk.js`) and exposed as
 * `window.LwaWebAnalytics`. Zero dependencies, zero build step, consent aware.
 *
 *   LwaWebAnalytics.init({ key: 'trk_…' });
 *   LwaWebAnalytics.track('purchase', { plan: 'pro' }, 9);
 *   LwaWebAnalytics.identify('user_123', { plan: 'pro' });
 *   LwaWebAnalytics.pageview('/pricing');
 */
(function (global) {
    'use strict';

    var DEFAULTS = {
        endpoint: '/web-analytics/api/v1/events/batch',
        key: '',
        consent: { analytics: true },
        autocapture: false,
        web_vitals: false,
        max_properties: 20,
        max_batch_bytes: 65536,
        masked_selectors: ['input[type=password]', '[data-lwa-mask]']
    };

    var BANNED_KEYS = [
        'password', 'token', 'secret', 'api_key', 'apikey', 'authorization',
        'email', 'phone', 'card', 'card_number', 'cvv', 'ssn', 'address'
    ];

    var config = null;
    var queue = [];
    var anonymousId = null;
    var sessionId = null;
    var userId = null;
    var bound = false;

    function randomId(prefix) {
        var bytes = 'abcdefghijklmnopqrstuvwxyz0123456789';
        var id = '';
        for (var i = 0; i < 12; i++) {
            id += bytes.charAt(Math.floor(Math.random() * bytes.length));
        }
        return prefix + '_' + id + Date.now().toString(36);
    }

    function granted() {
        return !!config && !(config.consent && config.consent.analytics === false);
    }

    function sanitize(properties) {
        var clean = {};
        var keys = Object.keys(properties || {});
        var count = 0;
        var bytes = 0;

        for (var i = 0; i < keys.length && count < config.max_properties; i++) {
            var key = keys[i];
            var value = properties[key];
            if (BANNED_KEYS.indexOf(key.toLowerCase()) !== -1) { continue; }
            if (typeof value !== 'string' && typeof value !== 'number' && value !== null) { continue; }

            var size = JSON.stringify(key).length + JSON.stringify(value).length + 1;
            if (bytes + size > config.max_batch_bytes) { break; }

            clean[key] = typeof value === 'string' ? value.slice(0, 255) : value;
            bytes += size;
            count++;
        }

        return clean;
    }

    function isMasked(element) {
        var selectors = config.masked_selectors || [];
        for (var i = 0; i < selectors.length; i++) {
            if (element.matches && element.matches(selectors[i])) { return true; }
        }
        return false;
    }

    function build(name, properties, value) {
        if (!anonymousId) { anonymousId = randomId('f'); }
        if (!sessionId) { sessionId = randomId('s'); }

        return {
            uuid: randomId('evt'),
            name: String(name || '').slice(0, 100),
            timestamp: new Date().toISOString(),
            distinct_id: userId,
            anonymous_id: userId ? null : anonymousId,
            session_id: sessionId,
            context: {
                path: global.location ? global.location.pathname : '/',
                method: 'GET',
                user_agent: global.navigator ? (global.navigator.userAgent || '') : '',
                referrer: global.document ? (global.document.referrer || null) : null
            },
            properties: sanitize(properties || {}),
            value: typeof value === 'number' ? value : null,
            created_at: new Date().toISOString()
        };
    }

    function post(events) {
        if (!events.length || !granted()) { return; }

        var body = JSON.stringify({ key: config.key, events: events });
        var url = config.endpoint;

        // sendBeacon keeps the last events on unload, but cannot set headers,
        // so the project key is carried in the body. fetch sets both.
        try {
            if (global.navigator && typeof global.navigator.sendBeacon === 'function') {
                if (global.navigator.sendBeacon(url, new Blob([body], { type: 'application/json' }))) {
                    return;
                }
            }
        } catch (e) { /* fall through to fetch */ }

        try {
            global.fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-LWA-Project-Key': config.key
                },
                body: body,
                keepalive: true
            }).catch(function () { /* network failure is non fatal for the tracker */ });
        } catch (e) { /* non fatal for the tracker */ }
    }

    function flush() {
        if (!queue.length) { return; }

        var batch = [];
        var bytes = 0;
        var max = config.max_batch_bytes;

        while (queue.length) {
            var event = queue[0];
            var size = JSON.stringify(event).length;
            if (batch.length && bytes + size > max) { break; }
            batch.push(queue.shift());
            bytes += size;
        }

        post(batch);
    }

    function track(name, properties, value) {
        if (!granted()) { return; }
        queue.push(build(name, properties, value));
        flush();
    }

    function identify(id, traits) {
        if (!granted() || !id) { return; }
        if (!anonymousId) { anonymousId = randomId('f'); }
        var previousAnonymous = anonymousId;
        userId = 'u_' + String(id).replace(/^u_/, '');
        var event = build('identify', traits || {}, null);
        event.distinct_id = userId;
        event.anonymous_id = previousAnonymous;
        queue.push(event);
        flush();
    }

    function group(type, id) {
        if (!granted()) { return; }
        queue.push(build('group', { group_type: String(type || ''), group_id: String(id || '') }, null));
        flush();
    }

    function pageview(path) {
        if (!granted()) { return; }
        queue.push(build('pageview', { path: String(path || (global.location ? global.location.pathname : '/')) }, null));
        flush();
    }

    function bindAutocapture() {
        if (bound || !config.autocapture || !global.document) { return; }
        bound = true;

        global.document.addEventListener('click', function (event) {
            var target = event.target;
            while (target && target !== global.document) {
                if (target.getAttribute && target.getAttribute('data-lwa-track')) {
                    var name = target.getAttribute('data-lwa-track');
                    var props = { path: global.location ? global.location.pathname : '/' };
                    if (!isMasked(target) && target.value !== undefined) {
                        props.value = String(target.value).slice(0, 255);
                    }
                    track(name, props, null);
                    return;
                }
                target = target.parentNode;
            }
        });

        global.document.addEventListener('submit', function (event) {
            var form = event.target;
            if (form && form.getAttribute && form.getAttribute('data-lwa-track')) {
                track(form.getAttribute('data-lwa-track'), {
                    path: global.location ? global.location.pathname : '/'
                }, null);
            }
        });
    }

    /**
     * Web Vitals are opt-in: they emit numeric values only, never form data.
     * Uses the browser PerformanceObserver API when available.
     */
    function bindWebVitals() {
        if (!config.web_vitals || !global.PerformanceObserver) { return; }

        var report = function (name, value) {
            if (typeof value === 'number' && isFinite(value)) {
                track('web_vital', { metric: name, value: Math.round(value * 1000) / 1000 }, null);
            }
        };

        try {
            var lcp = new global.PerformanceObserver(function (list) {
                var entries = list.getEntries();
                report('lcp', entries[entries.length - 1].startTime);
            });
            lcp.observe({ type: 'largest-contentful-paint', buffered: true });

            var cls = new global.PerformanceObserver(function (list) {
                var total = 0;
                list.getEntries().forEach(function (entry) {
                    if (!entry.hadRecentInput) { total += entry.value; }
                });
                report('cls', total);
            });
            cls.observe({ type: 'layout-shift', buffered: true });
        } catch (e) { /* browser without support — metrics stay disabled */ }
    }

    var api = {
        init: function (options) {
            config = Object.assign({}, DEFAULTS, options || {});
            queue = [];
            bindAutocapture();
            bindWebVitals();
            return api;
        },
        track: track,
        identify: identify,
        group: group,
        pageview: pageview,
        optOut: function () {
            if (config) {
                config.consent = Object.assign({}, config.consent, { analytics: false });
            }
        },
        optIn: function () {
            if (config) {
                config.consent = Object.assign({}, config.consent, { analytics: true });
            }
        },
        flush: flush
    };

    global.LwaWebAnalytics = api;
})(window);
