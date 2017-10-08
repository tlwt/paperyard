--
-- Update config_regexTemplates table to meet requirements of github issue #30
--

-- begin transaction
BEGIN TRANSACTION;

-- step one create tmp table with new format
CREATE TABLE "tmp_rule_senders" (
	`id`	INTEGER PRIMARY KEY AUTOINCREMENT,
	`foundWords`	TEXT,
	`fileCompany`	TEXT,
	`companyScore`	INTEGER NOT NULL DEFAULT (0),
	`tags`	TEXT,
	`isActive` INTEGER NOT NULL DEFAULT (1),
	updated_at TEXT,
	created_at TEXT );


-- move all data to tmp table
INSERT INTO tmp_rule_senders SELECT id, foundWords, fileCompany, companyScore, tags, isActive, NULL, NULL FROM rule_senders;

-- drop original table
DROP TABLE rule_senders;

-- recreate original table
CREATE TABLE "rule_senders" (
	`id`	INTEGER PRIMARY KEY AUTOINCREMENT,
	`foundWords`	TEXT,
	`fileCompany`	TEXT,
	`companyScore`	INTEGER NOT NULL DEFAULT (0),
	`tags`	TEXT,
	`isActive` INTEGER NOT NULL DEFAULT (1),
	updated_at TEXT,
	created_at TEXT );

--moving data
INSERT INTO rule_senders SELECT * FROM tmp_rule_senders;

-- drop tmp table
DROP TABLE tmp_rule_senders;

-- Update the version number accordingly
-- IMPORTANT - needs to match file name (file name has leading zeros though)
UPDATE config SET configValue = 8 WHERE configVariable = 'databaseVersion';

-- ending transaction
END TRANSACTION;
