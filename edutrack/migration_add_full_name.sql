-- Migration: Add full_name column to users table
-- Run this query in your database

USE edutrack_db;

-- Add full_name column to users table
ALTER TABLE users 
ADD COLUMN full_name VARCHAR(100) NULL AFTER email;

-- Optional: Update existing users with a default full_name (using username as fallback)
UPDATE users 
SET full_name = username 
WHERE full_name IS NULL OR full_name = '';

