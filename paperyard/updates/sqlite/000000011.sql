--
-- Creates logging table for output
--

-- begin transaction
BEGIN TRANSACTION;

-- step one create tmp table with new format
CREATE TABLE IF NOT EXISTS tmp_logShell(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	severity INTEGER DEFAULT (9),
  logProgram TEXT,
  logContent TEXT,
	updated_at TEXT,
	created_at TEXT DEFAULT (datetime('NOW')));

-- move all data to tmp table
INSERT INTO tmp_logShell SELECT id, NULL, logProgram, logContent, updated_at, created_at FROM logShell;

-- drop original table
DROP TABLE logShell;

-- step one create tmp table with new format
CREATE TABLE IF NOT EXISTS logShell(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	severity INTEGER DEFAULT (9),
  logProgram TEXT,
  logContent TEXT,
	updated_at TEXT,
	created_at TEXT DEFAULT (datetime('NOW')));

	--moving data
	INSERT INTO logShell SELECT * FROM tmp_logShell;

	-- drop tmp table
	DROP TABLE tmp_logShell;


-- Update the version number accordingly
-- IMPORTANT - needs to match file name (file name has leading zeros though)
UPDATE config SET configValue = 11 WHERE configVariable = 'databaseVersion';

-- ending transaction
END TRANSACTION;
