--
-- Creates user table for authentication
--

-- begin transaction
BEGIN TRANSACTION;

-- step one create tmp table with new format
CREATE TABLE IF NOT EXISTS users(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	user INTEGER NOT NULL UNIQUE,
    hash TEXT NOT NULL,
    first_name TEXT,
    last_name TEXT,
    street TEXT,
    zip_code TEXT,
    house_number TEXT,
    email TEXT,
    phone TEXT,
    group_id INTEGER,
    api_token TEXT,
    api_key TEXT,
	updated_at TEXT,
	created_at TEXT DEFAULT (datetime('NOW')));

-- Update the version number accordingly
-- IMPORTANT - needs to match file name (file name has leading zeros though)
UPDATE config SET configValue = 12 WHERE configVariable = 'databaseVersion';

-- ending transaction
END TRANSACTION;
