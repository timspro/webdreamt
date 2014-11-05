-- Adds foreign keys to the user_groups table since Sentry does not provide them.
ALTER TABLE users_groups ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE users_groups ADD FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE;
