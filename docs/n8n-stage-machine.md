# Stage Machine (n8n MVP)

## Overview
Stages progress in order with human approvals:
`plan -> lyrics -> melody -> music -> vocals -> mix -> export_done`

Each stage creates assets stored in Postgres + MinIO. All transitions log a structured event.

## Workflow: song-create
1. Insert song and version `v1` with `stage=plan_pending_approval`.
2. Persist any uploaded refs as `assets`.
3. Ensure the album has a `vocalistModelId`:
   - If missing, call `vc-api /train_vocalist`.
   - Save `vocalistModelId` into `albums.style_bible_json`.

## Workflow: song-approve
1. Insert approval row with `status=approved`.
2. Switch by stage:
   - `plan` -> generate lyric options, set `lyrics_pending_approval`.
   - `lyrics` -> call `melody-api /generate_melody`, set `melody_pending_approval`.
   - `melody` -> call `musicgen-api /generate_candidates`, set `music_pending_approval`.
   - `music` -> call `singer-api /render_base_vocal`, then `vc-api /convert_vocal`, set `vocals_pending_approval`.
   - `vocals` -> call `audio-tools /mix_master`, set `mix_pending_approval`.
   - `mix` -> mark exported, set `export_done`.

## Workflow: song-revise
1. Insert approval row with `status=revision`.
2. Regenerate assets for the current stage and keep `stage=_pending_approval`.

## Service API Contracts
All services are JSON over HTTP.

### musicgen-api
`POST /generate_candidates`
```json
{
  "candidates": [
    {"candidateId": "music_candidate_1", "wavUrl": "s3://...", "previewUrl": "https://..."}
  ]
}
```

### melody-api
`POST /generate_melody`
```json
{
  "midiUrl": "s3://.../melody.mid",
  "previewUrl": "https://.../melody.mp3"
}
```

### singer-api
`POST /render_base_vocal`
```json
{
  "baseVocalWavUrl": "s3://.../base.wav",
  "previewUrl": "https://.../base.mp3"
}
```

### vc-api
`POST /train_vocalist`
```json
{
  "vocalistModelId": "vocalist_123",
  "status": "trained"
}
```

`POST /convert_vocal`
```json
{
  "convertedVocalUrl": "s3://.../converted.wav",
  "previewUrl": "https://.../converted.mp3"
}
```

### audio-tools
`POST /mix_master`
```json
{
  "finalMasterMp3Url": "s3://.../master.mp3",
  "instrumentalMp3Url": "s3://.../instrumental.mp3",
  "stemsZipUrl": "s3://.../stems.zip"
}
```
