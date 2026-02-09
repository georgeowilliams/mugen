<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Album Factory | Song {{ $songId }} - {{ ucfirst($stage) }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; background: #0f172a; color: #e2e8f0; }
        header { margin-bottom: 1.5rem; }
        h1 { margin: 0 0 0.5rem; }
        .badge { padding: 0.25rem 0.5rem; background: #334155; border-radius: 4px; font-size: 0.85rem; }
        .panel { background: #1e293b; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .assets { display: grid; gap: 1rem; }
        .asset { background: #0f172a; border: 1px solid #334155; padding: 0.75rem; border-radius: 6px; }
        .asset pre { white-space: pre-wrap; background: #111827; padding: 0.75rem; border-radius: 4px; }
        label { display: block; margin: 0.5rem 0 0.25rem; }
        textarea { width: 100%; min-height: 80px; border-radius: 4px; border: 1px solid #334155; padding: 0.5rem; }
        input[type="text"] { width: 100%; border-radius: 4px; border: 1px solid #334155; padding: 0.5rem; }
        button { background: #38bdf8; color: #0f172a; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; }
        button.secondary { background: #fbbf24; }
        .candidate-select { margin-top: 0.5rem; }
    </style>
</head>
<body>
    <header>
        <h1>Song {{ $songId }}</h1>
        <div class="badge">Stage: <span id="current-stage">{{ $stage }}</span></div>
        <p>Album {{ $albumId }}</p>
    </header>

    <section class="panel">
        <h2>Status</h2>
        <pre id="status-json">Loading status...</pre>
    </section>

    <section class="panel">
        <h2>Assets</h2>
        <div class="assets" id="assets-container"></div>
    </section>

    <section class="panel">
        <h2>Approve Stage</h2>
        <form id="approve-form" method="POST" action="/songs/{{ $songId }}/approve">
            @csrf
            <input type="hidden" name="stage" value="{{ $stage }}">
            <div class="candidate-select" id="candidate-select"></div>
            <label for="notes">Notes (optional)</label>
            <textarea id="notes" name="notes" placeholder="Add notes for the pipeline"></textarea>
            <button type="submit">Approve & Advance</button>
        </form>
    </section>

    <section class="panel">
        <h2>Request Revision</h2>
        <form id="revise-form" method="POST" action="/songs/{{ $songId }}/revise">
            @csrf
            <input type="hidden" name="stage" value="{{ $stage }}">
            <label for="revisionNotes">Revision notes</label>
            <textarea id="revisionNotes" name="revisionNotes" placeholder="Describe what to change"></textarea>
            <button type="submit" class="secondary">Submit Revision</button>
        </form>
    </section>

    <script>
        const statusUrl = '{{ rtrim($n8nBaseUrl, '/') }}/webhook/song-status?songId={{ $songId }}';
        const currentStage = '{{ $stage }}';
        const assetsContainer = document.getElementById('assets-container');
        const statusJson = document.getElementById('status-json');
        const candidateSelect = document.getElementById('candidate-select');

        function renderAssets(assets = []) {
            assetsContainer.innerHTML = '';
            candidateSelect.innerHTML = '';

            if (!assets.length) {
                assetsContainer.innerHTML = '<p>No assets yet for this stage.</p>';
                return;
            }

            const candidateIds = new Set();

            assets.forEach((asset) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'asset';

                const title = document.createElement('h3');
                title.textContent = asset.label || asset.assetType || 'Asset';
                wrapper.appendChild(title);

                if (asset.previewUrl) {
                    const audio = document.createElement('audio');
                    audio.controls = true;
                    audio.src = asset.previewUrl;
                    wrapper.appendChild(audio);
                }

                if (asset.text) {
                    const pre = document.createElement('pre');
                    pre.textContent = asset.text;
                    wrapper.appendChild(pre);
                }

                if (asset.uri && !asset.previewUrl) {
                    const link = document.createElement('a');
                    link.href = asset.uri;
                    link.textContent = asset.uri;
                    link.target = '_blank';
                    wrapper.appendChild(link);
                }

                const candidateId = asset.candidateId || (asset.metadata && asset.metadata.candidateId);
                if (candidateId) {
                    candidateIds.add(candidateId);
                }

                assetsContainer.appendChild(wrapper);
            });

            if (candidateIds.size) {
                const label = document.createElement('label');
                label.textContent = 'Select candidate to approve';
                candidateSelect.appendChild(label);

                candidateIds.forEach((candidateId) => {
                    const option = document.createElement('div');
                    option.innerHTML = `
                        <label>
                            <input type="radio" name="candidateId" value="${candidateId}" required>
                            Candidate ${candidateId}
                        </label>
                    `;
                    candidateSelect.appendChild(option);
                });
            }
        }

        async function fetchStatus() {
            const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json' }});
            const payload = await response.json();
            statusJson.textContent = JSON.stringify(payload, null, 2);
            renderAssets(payload.assets || []);

            if (payload.stage && payload.stage !== currentStage) {
                const nextStage = payload.stage.replace('_pending_approval', '');
                window.location.href = `/albums/{{ $albumId }}/songs/{{ $songId }}/${nextStage}`;
            }
        }

        fetchStatus();
        setInterval(fetchStatus, 3000);
    </script>
</body>
</html>
