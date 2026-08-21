-- Migration: Social Login (Google / GitHub) support
USE blog_app;

-- Allow accounts created via OAuth to have no local password
ALTER TABLE user
    MODIFY COLUMN password VARCHAR(255) NULL;

-- Track which provider (if any) an account was created/linked with
ALTER TABLE user
    ADD COLUMN IF NOT EXISTS oauth_provider VARCHAR(20) DEFAULT NULL AFTER role,
    ADD COLUMN IF NOT EXISTS oauth_id VARCHAR(255) DEFAULT NULL AFTER oauth_provider;

CREATE UNIQUE INDEX IF NOT EXISTS idx_oauth ON user (oauth_provider, oauth_id);
