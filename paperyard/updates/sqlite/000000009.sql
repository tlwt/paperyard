--
-- Update config_regexTemplates table to meet requirements of github issue #30
--

-- begin transaction
BEGIN TRANSACTION;

-- step one create tmp table with new format
CREATE TABLE "tmp_rule_subjects" (
	`id`	INTEGER PRIMARY KEY AUTOINCREMENT,
	`foundWords`	TEXT,
	`foundCompany`	TEXT,
	`fileSubject`	TEXT,
	`subjectScore`	INTEGER NOT NULL DEFAULT (0),
	`tags` TEXT,
	`isActive`	INTEGER NOT NULL DEFAULT (1),
	updated_at TEXT,
	created_at TEXT );


-- move all data to tmp table
INSERT INTO tmp_rule_subjects SELECT id, foundWords, foundCompany, fileSubject, subjectScore, tags, isActive, NULL, NULL FROM rule_subjects;

-- drop original table
DROP TABLE rule_subjects;

-- recreate original table
CREATE TABLE "rule_subjects" (
	`id`	INTEGER PRIMARY KEY AUTOINCREMENT,
	`foundWords`	TEXT,
	`foundCompany`	TEXT,
	`fileSubject`	TEXT,
	`subjectScore`	INTEGER NOT NULL DEFAULT (0),
	`tags` TEXT,
	`isActive`	INTEGER NOT NULL DEFAULT (1),
	updated_at TEXT,
	created_at TEXT );

--moving data
INSERT INTO rule_subjects SELECT * FROM tmp_rule_subjects;

-- drop tmp table
DROP TABLE tmp_rule_subjects;

-- Update the version number accordingly
-- IMPORTANT - needs to match file name (file name has leading zeros though)
UPDATE config SET configValue = 9 WHERE configVariable = 'databaseVersion';

-- ending transaction
END TRANSACTION;
