--
-- Creates logging table for output
--

-- begin transaction
BEGIN TRANSACTION;

-- step one create tmp table with new format
CREATE TABLE IF NOT EXISTS logShell(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
  logProgram TEXT,
  logContent TEXT,
	updated_at TEXT,
	created_at TEXT DEFAULT (datetime('NOW')));

INSERT INTO config(configVariable,configValue)
  SELECT 'logShellOutput', '1'
  WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'logShellOutput');


-- Update the version number accordingly
-- IMPORTANT - needs to match file name (file name has leading zeros though)
UPDATE config SET configValue = 10 WHERE configVariable = 'databaseVersion';

-- ending transaction
END TRANSACTION;
