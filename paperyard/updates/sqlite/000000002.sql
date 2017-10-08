--
-- Update config table to meet requirements of github issue #30
--

-- begin transaction
BEGIN TRANSACTION;

-- step one create tmp table with new format
CREATE TABLE tmp_config(
   id INTEGER PRIMARY KEY AUTOINCREMENT,
   configVariable TEXT,
   configValue TEXT,
   defaultValue TEXT,
   validateRegex TEXT,
   inputType TEXT,
   onlyInternal TEXT,
   updated_at TEXT,
   created_at TEXT );

-- move all data to tmp table
INSERT INTO tmp_config SELECT id, configVariable, configValue, NULL, NULL, NULL,NULL, NULL, NULL FROM config;

-- drop original table
DROP TABLE config;

-- recreate original table
CREATE TABLE config(
   id INTEGER PRIMARY KEY AUTOINCREMENT,
   configVariable TEXT,
   configValue TEXT,
   defaultValue TEXT,
   validateRegex TEXT,
   inputType TEXT,
   onlyInternal TEXT,
   updated_at TEXT,
   created_at TEXT );

-- moving data
INSERT INTO config SELECT * FROM tmp_config;

-- drop tmp table
DROP TABLE tmp_config;

-- Update the version number accordingly
UPDATE config SET configValue = 2 WHERE configVariable = 'databaseVersion';

-- ending transaction
END TRANSACTION;
