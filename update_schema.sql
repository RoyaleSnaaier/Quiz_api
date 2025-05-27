-- Remove time_limit from quizes table if it exists
ALTER TABLE quizes DROP COLUMN IF EXISTS time_limit;

-- Add new columns to existing tables
ALTER TABLE quizes ADD COLUMN IF NOT EXISTS category VARCHAR(100) DEFAULT NULL;
ALTER TABLE quizes ADD COLUMN IF NOT EXISTS tags TEXT DEFAULT NULL COMMENT 'Comma-separated tags';
ALTER TABLE quizes ADD COLUMN IF NOT EXISTS image_url VARCHAR(500) DEFAULT NULL;

ALTER TABLE quiz_questions ADD COLUMN IF NOT EXISTS time_limit INT DEFAULT NULL COMMENT 'Time limit in seconds for this question, NULL for no limit';
ALTER TABLE quiz_questions ADD COLUMN IF NOT EXISTS image_url VARCHAR(500) DEFAULT NULL;

ALTER TABLE answers ADD COLUMN IF NOT EXISTS image_url VARCHAR(500) DEFAULT NULL;
