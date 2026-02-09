CREATE TABLE IF NOT EXISTS albums (
    id BIGSERIAL PRIMARY KEY,
    title TEXT NOT NULL,
    vocalist_model_id TEXT,
    style_bible_json JSONB NOT NULL DEFAULT '{}',
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS songs (
    id BIGSERIAL PRIMARY KEY,
    album_id BIGINT NOT NULL REFERENCES albums(id) ON DELETE CASCADE,
    title TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'plan_pending_approval',
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS song_versions (
    id BIGSERIAL PRIMARY KEY,
    song_id BIGINT NOT NULL REFERENCES songs(id) ON DELETE CASCADE,
    version_number INTEGER NOT NULL,
    stage TEXT NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS assets (
    id BIGSERIAL PRIMARY KEY,
    song_id BIGINT NOT NULL REFERENCES songs(id) ON DELETE CASCADE,
    version_id BIGINT NOT NULL REFERENCES song_versions(id) ON DELETE CASCADE,
    stage TEXT NOT NULL,
    asset_type TEXT NOT NULL,
    uri TEXT NOT NULL,
    preview_url TEXT,
    metadata JSONB NOT NULL DEFAULT '{}',
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS approvals (
    id BIGSERIAL PRIMARY KEY,
    song_id BIGINT NOT NULL REFERENCES songs(id) ON DELETE CASCADE,
    version_id BIGINT NOT NULL REFERENCES song_versions(id) ON DELETE CASCADE,
    stage TEXT NOT NULL,
    status TEXT NOT NULL,
    notes TEXT,
    payload JSONB NOT NULL DEFAULT '{}',
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_songs_album_id ON songs(album_id);
CREATE INDEX IF NOT EXISTS idx_song_versions_song_id ON song_versions(song_id);
CREATE INDEX IF NOT EXISTS idx_song_versions_stage ON song_versions(stage);
CREATE INDEX IF NOT EXISTS idx_assets_song_id ON assets(song_id);
CREATE INDEX IF NOT EXISTS idx_assets_version_id ON assets(version_id);
CREATE INDEX IF NOT EXISTS idx_assets_stage ON assets(stage);
CREATE INDEX IF NOT EXISTS idx_approvals_song_id ON approvals(song_id);
CREATE INDEX IF NOT EXISTS idx_approvals_version_id ON approvals(version_id);
CREATE INDEX IF NOT EXISTS idx_approvals_stage ON approvals(stage);
