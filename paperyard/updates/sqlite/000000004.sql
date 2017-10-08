--
-- Update config_regexTemplates table to meet requirements of github issue #30
--

-- begin transaction
BEGIN TRANSACTION;

-- step one create tmp table with new format
CREATE TABLE tmp_config_regexTemplates (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	regextype TEXT,
	regex TEXT,
	regexComment TEXT,
	updated_at TEXT,
 	created_at TEXT);


-- move all data to tmp table
INSERT INTO tmp_config_regexTemplates SELECT id, regextype, regex, regexComment,NULL, NULL FROM config_regexTemples;

-- drop original table
DROP TABLE config_regexTemples;

-- recreate original table
CREATE TABLE config_regexTemplates (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	regextype TEXT,
	regex TEXT,
	regexComment TEXT,
	updated_at TEXT,
 	created_at TEXT);

-- moving data
INSERT INTO config_regexTemplates SELECT * FROM tmp_config_regexTemplates;

-- drop tmp table
DROP TABLE tmp_config_regexTemplates;

-- Update the version number accordingly
-- IMPORTANT - needs to match file name (file name has leading zeros though)
UPDATE config SET configValue = 4 WHERE configVariable = 'databaseVersion';

-- ending transaction
END TRANSACTION;
