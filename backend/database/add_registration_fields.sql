-- Add additional fields for player registration approval workflow
-- Run this migration after the base tournament_management.sql

USE tournament_management;

-- Add new columns to tournament_participants table
ALTER TABLE tournament_participants
ADD COLUMN phone_number VARCHAR(20) NULL AFTER registration_notes,
ADD COLUMN experience_level ENUM('beginner', 'intermediate', 'advanced', 'professional') NULL AFTER phone_number,
ADD COLUMN player_role VARCHAR(50) NULL AFTER experience_level,
ADD COLUMN additional_info TEXT NULL AFTER player_role;

-- Add index for faster filtering by experience level
ALTER TABLE tournament_participants
ADD INDEX idx_experience_level (experience_level);

-- Update existing confirmed participants to have a phone number placeholder if needed
-- (Optional - remove if you don't want to modify existing data)
-- UPDATE tournament_participants SET experience_level = 'intermediate' WHERE experience_level IS NULL;

COMMIT;
