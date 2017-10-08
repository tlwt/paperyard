--
-- Update config_regexTemplates table to meet requirements of github issue #30
--

-- begin transaction
BEGIN TRANSACTION;

-- step one create tmp table with new format
CREATE TABLE "tmp_rule_recipients" (
	`id`	INTEGER PRIMARY KEY AUTOINCREMENT,
	`recipientName`	TEXT,
	`shortNameForFile`	TEXT,
	`isActive`	INTEGER DEFAULT (1),
	updated_at TEXT,
	created_at TEXT );


-- move all data to tmp table
INSERT INTO tmp_rule_recipients SELECT id, recipientName, shortNameForFile, isActive, NULL, NULL FROM rule_recipients;

-- drop original table
DROP TABLE rule_recipients;

-- recreate original table
CREATE TABLE "rule_recipients" (
	`id`	INTEGER PRIMARY KEY AUTOINCREMENT,
	`recipientName`	TEXT,
	`shortNameForFile`	TEXT,
	`isActive`	INTEGER DEFAULT (1),
	updated_at TEXT,
	created_at TEXT );

-- moving data
INSERT INTO rule_recipients SELECT * FROM tmp_rule_recipients;

-- drop tmp table
DROP TABLE tmp_rule_recipients;

-- Update the version number accordingly
-- IMPORTANT - needs to match file name (file name has leading zeros though)
UPDATE config SET configValue = 7 WHERE configVariable = 'databaseVersion';

-- ending transaction
END TRANSACTION;
