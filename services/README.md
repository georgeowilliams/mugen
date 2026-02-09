# Service Stubs

Each service exposes a JSON HTTP API used by n8n. For the MVP, stubs can return static payloads that match the contract in `docs/n8n-stage-machine.md`.

## Expected Endpoints
- musicgen-api: `POST /generate_candidates`
- melody-api: `POST /generate_melody`
- singer-api: `POST /render_base_vocal`
- vc-api: `POST /train_vocalist`, `POST /convert_vocal`
- audio-tools: `POST /mix_master`
