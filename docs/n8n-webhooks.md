# n8n Webhook Contracts (Album Factory MVP)

All webhooks respond immediately with `202` or `200` and a JSON body. The UI polls `song-status` every 3 seconds.

## POST /webhook/song-create
Creates a song, version v1, and sets stage to `plan_pending_approval`.

**Request**
```json
{
  "albumId": 12,
  "title": "Neon Skyline",
  "refs": [
    {"type": "text", "label": "creative_brief", "value": "Upbeat synth-pop"},
    {"type": "audio", "label": "inspo_1", "url": "s3://album-factory/refs/inspo.wav"}
  ]
}
```

**Response**
```json
{
  "songId": 45,
  "versionId": 1,
  "stage": "plan_pending_approval",
  "status": "queued"
}
```

## POST /webhook/song-approve
Inserts an approval row and advances the stage machine.

**Request**
```json
{
  "songId": 45,
  "stage": "lyrics",
  "candidateId": "lyrics_v1_option_2",
  "notes": "Option 2 feels right"
}
```

**Response**
```json
{
  "songId": 45,
  "stage": "melody_pending_approval",
  "status": "queued"
}
```

## POST /webhook/song-revise
Inserts a revision row and regenerates outputs for the current stage.

**Request**
```json
{
  "songId": 45,
  "stage": "melody",
  "revisionNotes": "Add a stronger hook"
}
```

**Response**
```json
{
  "songId": 45,
  "stage": "melody_pending_approval",
  "status": "queued"
}
```

## GET /webhook/song-status?songId=45
Returns the latest stage and assets for UI polling.

**Response**
```json
{
  "songId": 45,
  "stage": "music_pending_approval",
  "updatedAt": "2025-02-09T03:12:00Z",
  "assets": [
    {
      "assetId": 203,
      "stage": "music",
      "assetType": "audio",
      "label": "candidate_1",
      "previewUrl": "https://minio.local/preview/candidate_1.mp3",
      "candidateId": "music_candidate_1"
    }
  ]
}
```
