-- basic setup of database version 1
-- sender tables
CREATE TABLE IF NOT EXISTS rule_senders(
   id INTEGER PRIMARY KEY AUTOINCREMENT,
   foundWords TEXT,
   fileCompany TEXT,
   companyScore INTEGER NOT NULL DEFAULT (0),
   tags	TEXT,
   isActive INTEGER NOT NULL DEFAULT (1));

 CREATE TABLE IF NOT EXISTS rule_personalInfo(
   id INTEGER PRIMARY KEY AUTOINCREMENT,
   variableName TEXT,
   replaceWith TEXT,
   comments TEXT,
   isActive INTEGER NOT NULL DEFAULT (1));

-- rules to detect subject of a document
CREATE TABLE IF NOT EXISTS rule_subjects(
   id INTEGER PRIMARY KEY AUTOINCREMENT,
   foundWords TEXT,
   foundCompany TEXT,
   fileSubject TEXT,
   subjectScore INTEGER NOT NULL DEFAULT (0),
   tags	TEXT,
   isActive INTEGER NOT NULL DEFAULT (1));

CREATE TABLE "rule_archive"(
    "id" Integer PRIMARY KEY AUTOINCREMENT,
    "toFolder" Text,
    "isActive" Integer NOT NULL DEFAULT 1,
    "company" Text,
    "subject" Text,
    "recipient" Text,
    "tags" Text);

-- config
CREATE TABLE IF NOT EXISTS config(
   id INTEGER PRIMARY KEY AUTOINCREMENT,
   configVariable TEXT,
   configValue TEXT);

   -- config_regex
  CREATE TABLE IF NOT EXISTS config_regexTemples(
     id INTEGER PRIMARY KEY AUTOINCREMENT,
     regextype TEXT,
     regex TEXT,
     regexComment TEXT);

-- logs
CREATE TABLE IF NOT EXISTS logs(
   id INTEGER PRIMARY KEY AUTOINCREMENT,
   execDate TEXT DEFAULT (datetime('NOW')),
   oldFileName TEXT,
   newFileName TEXT,
   fileContent TEXT,
   log TEXT);

-- recipients
CREATE TABLE IF NOT EXISTS rule_recipients (
   id INTEGER PRIMARY KEY AUTOINCREMENT,
   recipientName TEXT,
   shortNameForFile TEXT,
   isActive INTEGER DEFAULT (1) );

-- Setting up config values in case they dont exist
INSERT INTO config(configVariable,configValue)
                  SELECT 'companyMatchRating', '20'
                  WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'companyMatchRating');
INSERT INTO config(configVariable,configValue)
                  SELECT 'subjectMatchRating', '20'
                  WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'subjectMatchRating');
INSERT INTO config(configVariable,configValue)
                  SELECT 'dateRegEx', '/(\d{2}\.\d{2}\.\d{4})|([0-9]{1,2})\.\s?(januar|februar|märz|april|mai|juni|juli|august|september|oktober|november|dezember)\s?(\d{2,4})/'
                  WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'dateRegEx');
INSERT INTO config(configVariable,configValue)
                  SELECT 'stripCharactersFromContent', '/[^0-9a-zA-ZÄäÖöÜüß\.\,\-]+/'
                  WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'stripCharactersFromContent');
INSERT INTO config(configVariable,configValue)
                  SELECT 'matchPriceRegex', '/(\s?((\d{1,3}(\.\d{3})+)|(\d{1,3})),\d\ds?(euro?|€)?)/'
                  WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'matchPriceRegex');
INSERT INTO config(configVariable,configValue)
                  SELECT 'enableCron', '1'
                  WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'enableCron');
INSERT INTO config(configVariable,configValue)
                  SELECT 'newFilenameStructure', 'ddatum - ffirma - bbetreff (wwer) (bbetrag) [nt] -- '
                  WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'newFilenameStructure');
INSERT INTO config(configVariable,configValue)
                  SELECT 'appendOldFilename', '1'
                  WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'appendOldFilename');
INSERT INTO config(configVariable,configValue)
                  SELECT 'tesseractCommand', 'ocrmypdf -l deu --tesseract-timeout 600  --deskew --rotate-pages --tesseract-timeout 600 --oversample 600 --force-ocr '
                  WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'tesseractCommand');
INSERT INTO config(configVariable,configValue)
                  SELECT 'databaseVersion', '1'
                  WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'databaseVersion');
