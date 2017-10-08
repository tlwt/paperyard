--
-- Update config_regexTemplates table to meet requirements of github issue #30
--

-- begin transaction
BEGIN TRANSACTION;

-- step one create tmp table with new format
CREATE TABLE "tmp_rule_archive"(
	"id" Integer PRIMARY KEY AUTOINCREMENT,
	"toFolder" Text,
	"isActive" Integer NOT NULL DEFAULT 1,
	"company" Text,
	"subject" Text,
	"recipient" Text,
	"tags" Text,
	updated_at TEXT,
	created_at TEXT );



-- move all data to tmp table
INSERT INTO tmp_rule_archive SELECT id, toFolder, isActive, company,subject,recipient, tags, NULL, NULL FROM rule_archive;

-- drop original table
DROP TABLE rule_archive;

-- recreate original table
CREATE TABLE "rule_archive"(
	"id" Integer PRIMARY KEY AUTOINCREMENT,
	"toFolder" Text,
	"isActive" Integer NOT NULL DEFAULT 1,
	"company" Text,
	"subject" Text,
	"recipient" Text,
	"tags" Text,
	updated_at TEXT,
	created_at TEXT );

-- moving data
INSERT INTO rule_archive SELECT * FROM tmp_rule_archive;

-- drop tmp table
DROP TABLE tmp_rule_archive;

-- Update the version number accordingly
-- IMPORTANT - needs to match file name (file name has leading zeros though)
UPDATE config SET configValue = 5 WHERE configVariable = 'databaseVersion';

-- ending transaction
END TRANSACTION;
