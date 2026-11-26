/*
 * Laravel Web Analytics survey widget — dependency-free and build-free.
 * Only an explicit submit sends a response; identifiers are hashed server-side.
 */
(function () {
    'use strict';

    var config = window.LWA_SURVEY_CONFIG;
    if (!config || !config.key || !config.endpoint) {
        return;
    }

    var answeredKey = 'lwa_survey_answered_' + config.key;
    if (sessionStorage.getItem(answeredKey)) {
        return;
    }

    var panel = document.createElement('div');
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-label', 'Survey');
    panel.style.cssText = 'position:fixed;right:18px;bottom:18px;z-index:9999;width:300px;max-width:calc(100vw - 36px);'
        + 'font:13px system-ui,sans-serif;background:#fff;color:#18191d;border:1px solid #e6e6ea;border-radius:10px;'
        + 'box-shadow:0 16px 40px rgba(20,22,30,.13);padding:14px';

    var title = document.createElement('div');
    title.style.cssText = 'font-weight:600;margin-bottom:8px';
    title.textContent = String(config.question || 'How are we doing?');
    panel.appendChild(title);

    var answer = null;
    if (config.type === 'nps') {
        var scores = document.createElement('div');
        scores.style.cssText = 'display:grid;grid-template-columns:repeat(11,1fr);gap:3px';
        for (var i = 0; i <= 10; i++) {
            (function (value) {
                var button = document.createElement('button');
                button.type = 'button';
                button.textContent = String(value);
                button.setAttribute('aria-label', 'Score ' + value);
                button.style.cssText = 'padding:5px 0;border:1px solid #d5d6db;background:#f7f7f8;border-radius:5px;cursor:pointer';
                button.addEventListener('click', function () {
                    answer = { score: value };
                    send();
                });
                scores.appendChild(button);
            })(i);
        }
        panel.appendChild(scores);
    } else {
        var text = document.createElement('textarea');
        text.rows = 3;
        text.placeholder = 'Your feedback…';
        text.style.cssText = 'width:100%;box-sizing:border-box;border:1px solid #d5d6db;border-radius:5px;padding:8px;font:inherit';
        panel.appendChild(text);

        var submit = document.createElement('button');
        submit.type = 'button';
        submit.textContent = 'Send feedback';
        submit.style.cssText = 'margin-top:8px;padding:6px 10px;border:0;border-radius:5px;background:#5b5bd6;color:#fff;cursor:pointer';
        submit.addEventListener('click', function () {
            var value = text.value.trim();
            if (!value) {
                return;
            }
            answer = { answer: value.slice(0, 2000) };
            send();
        });
        panel.appendChild(submit);
    }

    var close = document.createElement('button');
    close.type = 'button';
    close.textContent = '×';
    close.setAttribute('aria-label', 'Dismiss survey');
    close.style.cssText = 'position:absolute;right:8px;top:6px;border:0;background:none;color:#6c7079;cursor:pointer;font-size:16px';
    close.addEventListener('click', function () { panel.remove(); });
    panel.appendChild(close);

    function send() {
        if (!answer) {
            return;
        }
        var body = {
            key: config.key,
            subject_key: config.subject,
            path: config.path,
            score: answer.score,
            answer: answer.answer
        };
        fetch(config.endpoint, {
            method: 'POST',
            credentials: 'omit',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-LWA-Project-Key': String(config.project_key || '')
            },
            body: JSON.stringify(body)
        }).then(function (response) {
            if (response.ok) {
                sessionStorage.setItem(answeredKey, '1');
            }
            panel.remove();
        }).catch(function () {
            panel.remove();
        });
    }

    document.body.appendChild(panel);
})();
