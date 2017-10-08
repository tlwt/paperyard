--
-- Update config_regexTemplates table to meet requirements of github issue #30
--

-- begin transaction
BEGIN TRANSACTION;

-- step one create tmp table with new format
CREATE TABLE tmp_rule_personalInfo(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	variableName TEXT,
	replaceWith TEXT,
	comments TEXT,
	isActive INTEGER NOT NULL DEFAULT (1),
	updated_at TEXT,
	created_at TEXT );


-- move all data to tmp table
INSERT INTO tmp_rule_personalInfo SELECT id, variableName, replaceWith, comments,isActive,NULL, NULL FROM rule_personalInfo;

-- drop original table
DROP TABLE rule_personalInfo;

-- recreate original table
CREATE TABLE rule_personalInfo(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	variableName TEXT,
	replaceWith TEXT,
	comments TEXT,
	isActive INTEGER NOT NULL DEFAULT (1),
	updated_at TEXT,
	created_at TEXT );

-- moving data
INSERT INTO rule_personalInfo SELECT * FROM tmp_rule_personalInfo;

-- drop tmp table
DROP TABLE tmp_rule_personalInfo;

-- Update the version number accordingly
-- IMPORTANT - needs to match file name (file name has leading zeros though)
UPDATE config SET configValue = 6 WHERE configVariable = 'databaseVersion';

-- ending transaction
END TRANSACTION;
