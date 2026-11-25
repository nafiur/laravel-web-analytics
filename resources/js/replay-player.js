/*
 * Minimal replay player — dependency-free and deferred. It renders stored
 * sanitized snapshots inside a script-disabled sandbox and lists bounded
 * markers. It deliberately does not attempt full-fidelity DOM patching.
 */
(function () {
    'use strict';

    var root = document.getElementById('lwaReplayPlayer');
    if (!root) {
        return;
    }

    var frame = document.getElementById('lwaReplayFrame');
    var playButton = document.getElementById('lwaReplayPlay');
    var prevButton = document.getElementById('lwaReplayPrev');
    var nextButton = document.getElementById('lwaReplayNext');
    var seek = document.getElementById('lwaReplaySeek');
    var timeLabel = document.getElementById('lwaReplayTime');
    var chunkLabel = document.getElementById('lwaReplayChunk');
    var markerList = document.getElementById('lwaReplayMarkers');

    var manifestUrl = root.dataset.manifestUrl || '';
    var chunkTemplate = root.dataset.chunkTemplate || '';
    var chunks = [];
    var current = 0;
    var timer = null;
    var cache = {};

    function seconds(ms) {
        return (Number(ms || 0) / 1000).toFixed(1) + 's';
    }

    function setStatus(message) {
        chunkLabel.textContent = message;
    }

    function renderMarkers(markers, elapsed) {
        markerList.textContent = '';
        if (!markers.length) {
            markerList.textContent = 'No markers recorded.';
            return;
        }
        markers.forEach(function (marker) {
            if (Number(marker.at_ms || 0) > elapsed) {
                return;
            }
            var row = document.createElement('div');
            row.className = 'flex justify-between gap-2 py-1 border-b lwa-border';
            var left = document.createElement('span');
            left.textContent = seconds(marker.at_ms) + ' · ' + marker.type;
            var right = document.createElement('span');
            var target = marker.payload && marker.payload.target;
            right.textContent = target && target.tag ? String(target.tag) : '';
            row.appendChild(left);
            row.appendChild(right);
            markerList.appendChild(row);
        });
        if (!markerList.children.length) {
            markerList.textContent = 'No markers before this point.';
        }
    }

    function readChunk(chunk) {
        var url = chunkTemplate.replace('__SEQ__', encodeURIComponent(chunk.sequence));
        if (cache[chunk.sequence]) {
            return Promise.resolve(cache[chunk.sequence]);
        }

        return fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json, application/gzip' } })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Chunk ' + chunk.sequence + ' could not be loaded');
                }
                if (chunk.encoding !== 'gzip') {
                    return response.text();
                }
                if (typeof DecompressionStream !== 'function') {
                    throw new Error('This browser cannot decompress gzipped replay chunks');
                }
                return response.arrayBuffer().then(function (buffer) {
                    var stream = new Blob([buffer]).stream().pipeThrough(new DecompressionStream('gzip'));
                    return new Response(stream).text();
                });
            })
            .then(function (text) {
                var payload = JSON.parse(text);
                cache[chunk.sequence] = payload;
                return payload;
            });
    }

    function snapshotFrom(payload) {
        var events = payload && Array.isArray(payload.events) ? payload.events : [];
        for (var i = 0; i < events.length; i++) {
            if (events[i].type === 'snapshot' && typeof events[i].html === 'string') {
                return events[i].html;
            }
        }
        return null;
    }

    function renderPayload(payload, chunk, markers) {
        var html = snapshotFrom(payload);
        if (!html) {
            html = '<!doctype html><html><body style="font:14px system-ui;padding:24px">'
                + '<h3>No snapshot in chunk ' + chunk.sequence + '</h3>'
                + '<p>' + (payload.events || []).length + ' structured events stored.</p></body></html>';
        }
        frame.srcdoc = html;
        renderMarkers(markers, chunk.to_ms);
        timeLabel.textContent = seconds(chunk.to_ms);
        setStatus('Chunk ' + (current + 1) + ' / ' + chunks.length + ' · ' + seconds(chunk.from_ms) + ' → ' + seconds(chunk.to_ms));
    }

    function select(index) {
        if (!chunks.length) {
            return Promise.resolve();
        }
        current = Math.max(0, Math.min(chunks.length - 1, index));
        seek.value = String(current);
        var chunk = chunks[current];
        setStatus('Loading chunk ' + chunk.sequence + '…');
        return readChunk(chunk)
            .then(function (payload) {
                renderPayload(payload, chunk, (root.__markers || []));
            })
            .catch(function (error) {
                setStatus(error.message || 'Replay chunk failed to load');
            });
    }

    function stop() {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
        playButton.textContent = 'Play';
    }

    function play() {
        if (!chunks.length) {
            return;
        }
        if (timer) {
            stop();
            return;
        }
        playButton.textContent = 'Pause';
        if (current >= chunks.length - 1) {
            select(0);
        }
        timer = setInterval(function () {
            if (current >= chunks.length - 1) {
                stop();
                return;
            }
            select(current + 1);
        }, 1200);
    }

    fetch(manifestUrl, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Replay manifest could not be loaded');
            }
            return response.json();
        })
        .then(function (manifest) {
            chunks = manifest.chunks || [];
            root.__markers = manifest.markers || [];
            seek.max = String(Math.max(0, chunks.length - 1));
            if (!chunks.length) {
                setStatus('No chunks recorded');
                playButton.disabled = true;
                prevButton.disabled = true;
                nextButton.disabled = true;
                markerList.textContent = 'No markers recorded.';
                return;
            }
            return select(0);
        })
        .catch(function (error) {
            setStatus(error.message || 'Replay manifest failed to load');
            markerList.textContent = 'Unavailable';
        });

    playButton.addEventListener('click', play);
    prevButton.addEventListener('click', function () { stop(); select(current - 1); });
    nextButton.addEventListener('click', function () { stop(); select(current + 1); });
    seek.addEventListener('input', function () { stop(); select(parseInt(seek.value, 10) || 0); });

    window.LWA_REPLAY_PLAYER_READY = true;
})();
