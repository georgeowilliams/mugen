# Album Factory (MVP)

Human-in-the-loop pipeline for creating songs one-by-one with approvals between stages.

## Quick start (containers)
```bash
cd /path/to/repo
docker compose up -d
```

This starts Postgres, MinIO, and n8n. The `ui` service is a thin Laravel skeleton mount and **requires a real Laravel app** to serve the UI.

## Run the Laravel UI
Recommended: create a Laravel app and copy in the provided routes/controller/view.

```bash
cd /path/to/repo/ui
composer create-project laravel/laravel laravel
```

Copy/merge these files from this repo into the new Laravel app:
- `routes/web.php`
- `app/Http/Controllers/SongStageController.php`
- `resources/views/song/stage.blade.php`
- `config/services.php` (merge the `n8n` config)

Then configure and run:
```bash
cd /path/to/repo/ui/laravel
cp .env.example .env
php artisan key:generate
```

Set `N8N_BASE_URL=http://localhost:5678` in `.env`, then:
```bash
php artisan serve
```

## What to build next (docs)
- `docs/n8n-webhooks.md` — Webhook contracts + example payloads.
- `docs/n8n-stage-machine.md` — Stage transitions + service API shapes.
- `services/README.md` — Expected stub endpoints for local dev.
- `db/init.sql` — Postgres schema for albums, songs, assets, approvals.

## Local URLs
- n8n: `http://localhost:5678`
- MinIO: `http://localhost:9001`
- Postgres: `localhost:5432`

## MVP Requirements (summary)
- Stage machine: `plan -> lyrics -> melody -> music -> vocals -> mix -> export_done`.
- UI polls `GET /webhook/song-status?songId=X` and submits approvals/revisions.
- Service contracts: musicgen, melody, singer, vc, audio-tools APIs.
