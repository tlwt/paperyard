--
-- Update log table to meet requirements of github issue #30
--

-- begin transaction
BEGIN TRANSACTION;

-- step one create tmp table with new format
CREATE TABLE tmp_logs(
		   id INTEGER PRIMARY KEY AUTOINCREMENT,
			 `execDate` TEXT DEFAULT (datetime('NOW')),
			 oldFileName TEXT,
		   newFileName TEXT,
		   fileContent TEXT,
		   log TEXT,
       fileHash TEXT,
       hashFunction TEXT,
       updated_at TEXT,
       created_at TEXT );

-- move all data to tmp table
INSERT INTO tmp_logs SELECT id, execDate, oldFileName, newFileName, fileContent, log,NULL, NULL, NULL,NULL FROM logs;

-- drop original table
DROP TABLE logs;

-- recreate original table
CREATE TABLE logs AS SELECT * FROM tmp_logs;

-- drop tmp table
DROP TABLE tmp_logs;

-- Update the version number accordingly
-- IMPORTANT - needs to match file name (file name has leading zeros though)
UPDATE config SET configValue = 3 WHERE configVariable = 'databaseVersion';

-- ending transaction
END TRANSACTION;
