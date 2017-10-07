<?php

	require_once('ppyrd.base.php');


	/**
		* @file
		* \author Till Witt
		* \brief database handler for ppyrd backend
		* \bug certainly still a lot
		* \details
		* It collects files, and processes the ruleset
		*
	 	*/
	class dbHandler extends ppyrd {
		/*!
			* \class dbHandler
			* \brief handling database connection and queries
			* \author Till Witt
			* \bug no error handling implemented yet
			*
		 	* \details database handler
		 	*/
		var $db;

		/**
		 * \brief takes care of basic db handling
		 */
		public function __construct() {
		// connects or creates sqlite db file
		$this->open();


		// creating tables in case they do not exist
		// rules to detect senders of a document
		$this->exec("CREATE TABLE IF NOT EXISTS rule_senders(
		   id INTEGER PRIMARY KEY AUTOINCREMENT,
		   foundWords TEXT,
		   fileCompany TEXT,
		   companyScore INTEGER NOT NULL DEFAULT (0),
		   tags	TEXT,
		   isActive INTEGER NOT NULL DEFAULT (1))");

		 $this->exec("CREATE TABLE IF NOT EXISTS rule_personalInfo(
 		   id INTEGER PRIMARY KEY AUTOINCREMENT,
 		   variableName TEXT,
 		   replaceWith TEXT,
 		   comments TEXT,
 		   isActive INTEGER NOT NULL DEFAULT (1))");

		// rules to detect subject of a document
		$this->exec("CREATE TABLE IF NOT EXISTS rule_subjects(
		   id INTEGER PRIMARY KEY AUTOINCREMENT,
		   foundWords TEXT,
		   foundCompany TEXT,
		   fileSubject TEXT,
		   subjectScore INTEGER NOT NULL DEFAULT (0),
		   tags	TEXT,
		   isActive INTEGER NOT NULL DEFAULT (1))");

		// config
		$this->exec("CREATE TABLE IF NOT EXISTS config(
		   id INTEGER PRIMARY KEY AUTOINCREMENT,
		   configVariable TEXT,
		   configValue TEXT)");

			 // config_regex
	 		$this->exec("CREATE TABLE IF NOT EXISTS config_regexTemples(
	 		   id INTEGER PRIMARY KEY AUTOINCREMENT,
	 		   regextype TEXT,
	 		   regex TEXT,
			   regexComment TEXT)");

		// logs
		$this->exec("CREATE TABLE IF NOT EXISTS logs(
		   id INTEGER PRIMARY KEY AUTOINCREMENT,
			 execDate TEXT DEFAULT (datetime('NOW')),
			 oldFileName TEXT,
		   newFileName TEXT,
		   fileContent TEXT,
		   log TEXT)");

		// recipients
		$this->exec("CREATE TABLE IF NOT EXISTS rule_recipients (
		   id INTEGER PRIMARY KEY AUTOINCREMENT,
		   recipientName TEXT,
		   shortNameForFile TEXT,
		   isActive INTEGER DEFAULT (1) )");

		// Setting up config values in case they dont exist
		$this->exec("INSERT INTO config(configVariable,configValue)
											SELECT 'companyMatchRating', '20'
											WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'companyMatchRating')");
		$this->exec("INSERT INTO config(configVariable,configValue)
											SELECT 'subjectMatchRating', '20'
											WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'subjectMatchRating')");
		$this->exec("INSERT INTO config(configVariable,configValue)
											SELECT 'dateRegEx', '/(\d{2}\.\d{2}\.\d{4})|([0-9]{1,2})\.\s?(januar|februar|märz|april|mai|juni|juli|august|september|oktober|november|dezember)\s?(\d{2,4})/'
											WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'dateRegEx')");
		$this->exec("INSERT INTO config(configVariable,configValue)
											SELECT 'stripCharactersFromContent', '/[^0-9a-zA-ZÄäÖöÜüß\.\,\-]+/'
											WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'stripCharactersFromContent')");
		$this->exec("INSERT INTO config(configVariable,configValue)
											SELECT 'matchPriceRegex', '/(\s?((\d{1,3}(\.\d{3})+)|(\d{1,3})),\d\ds?(euro?|€)?)/'
											WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'matchPriceRegex')");
		$this->exec("INSERT INTO config(configVariable,configValue)
											SELECT 'enableCron', '1'
											WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'enableCron')");
		$this->exec("INSERT INTO config(configVariable,configValue)
											SELECT 'newFilenameStructure', 'ddatum - ffirma - bbetreff (wwer) (bbetrag) [nt] -- '
											WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'newFilenameStructure')");
		$this->exec("INSERT INTO config(configVariable,configValue)
											SELECT 'appendOldFilename', '1'
											WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'appendOldFilename')");
		$this->exec("INSERT INTO config(configVariable,configValue)
											SELECT 'tesseractCommand', 'ocrmypdf -l deu --tesseract-timeout 600  --deskew --rotate-pages --tesseract-timeout 600 --oversample 600 --force-ocr '
											WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'tesseractCommand')");
		$this->exec("INSERT INTO config(configVariable,configValue)
											SELECT 'databaseVersion', '1'
											WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'databaseVersion')");


		// updating table model
		$this->update();

		//$this->alterTableAddColumns("testtable", "createdDate", " TEXT");
		//$this->alterTableDropColumns("testtable", "createdDate,modifiedDate,publishedDate");

		} // End constructor

		/**
		 * \brief function to add columns
		 * @param string $table containing table name
		 * @param string $addColumns containing the names and types of columns to addColumns
		 * @param string $defaultValues containing the default values of the corresponding addColumns
		 */
		function alterTableAddColumns($table, $addColumns, $defaultValues)
		{
			$tableQuery = $this->query("SELECT sql FROM sqlite_master WHERE tbl_name = '$table' AND type = 'table';");
			$oldTable = $tableQuery->fetchArray()['sql'];
			$newTable = str_replace($table, "tmp_$table", substr($oldTable,0,strrpos($oldTable,")")) . $addColumns . ");\n");

			// making sure there is nothing locking the following
			$this->close();
			$this->open();

			var_dump($newTable);

			$query = "";
			$query .= "BEGIN TRANSACTION;";
			$query .= $newTable;
			$query .= "INSERT INTO tmp_$table SELECT *, $defaultValues FROM $table;";
			$query .= "DROP TABLE $table;";
			$query .= "CREATE TABLE $table AS SELECT * FROM tmp_$table;";
			$query .= "DROP TABLE tmp_$table;";
			$query .= "END TRANSACTION;";

			$this->exec($query);
		}


		/**
		 * \brief function to drop columns
		 * @param string $table containing table name
		 * @param string $dropColumns containing the names to drop
		 */
		function alterTableDropColumns($table, $dropColumns)
		{
			$tableQuery = $this->query("SELECT sql FROM sqlite_master WHERE tbl_name = '$table' AND type = 'table';");
			$oldTable = $tableQuery->fetchArray()['sql'];

			$columns = explode(",", $dropColumns);
			$newTable = $oldTable;

			// looping thru columns to be removed and preg_replace them
			foreach ($columns as $column)
			{
				// remove unwanted spaces
				$column = trim($column);
				$this->output("looking for $column");

				// the magical regex
				$newTable = preg_replace("/($column \w*,)|(,\s*$column \w*)/", "", $newTable);
			}
			$newTable = str_replace("$table","tmp_$table", $newTable);

			// creating new table
			$this->exec($newTable);

			// getting new columnNames without types
			$results = $this->query("PRAGMA table_info('tmp_$table')");
			while ($column = $results->fetchArray())
			{
				$query[] = $column['name'];
			}

			// building fields for select statement
			$query = join(',',$query);
			$insertNewTable = "INSERT INTO tmp_$table ($query) SELECT $query FROM $table;";

			// making sure there is nothing locking the following
			$this->close();
			$this->open();

			$query = "";
			$query .= "BEGIN TRANSACTION;";
			$query .= $insertNewTable;
			$query .= "DROP TABLE $table;";
			$query .= "CREATE TABLE $table AS SELECT * FROM tmp_$table;";
			$query .= "DROP TABLE tmp_$table;";
			$query .= "END TRANSACTION;";

			$this->db->exec($query);
		}


		/**
		 * \brief function queries sqlite
		 * @param string $query to execute
		 * @return pointer to db results
		 */
		function query($query)
		{
			$result = $this->db->query($query);
			if (!$result) {
					// the query failed
					$this->output("There was an error in query: $query");
					$this->output($this->db->lastErrorMsg());
			}
			return $result;
		}

		/**
		 * \brief function executes sqlite
		 * @param string $query to execute
		 * @return pointer to db results
		 */
		function exec($query)
		{
				$result = $this->db->exec($query);
				if (!$result) {
						// the query failed
						$this->output("There was an error in query: $query");
						$this->output(
							$this->db->lastErrorMsg());
				}
				return $result;
		}

		/**
		 * gets active ruleset
		 */
		function getActiveArchiveRules ()
		{
			return $this->query("SELECT * FROM rule_archive WHERE isActive = 1");
		}

		/**
		 * gets active senders
		 */
		function getActiveSenders ()
		{
			return $this->query("SELECT * FROM rule_senders WHERE isActive = 1");
		}

		/**
		 * gets config values
		 * @param string $varname to query
		 * @return string containing variable value
		 */
		function getConfigValue ($varname)
		{
			$results = $this->query("SELECT * FROM config WHERE configVariable = '$varname'");
			$row = $results->fetchArray();
			return $row['configValue'];
		}


		/**
		 * gets all personal variables
		 * @param none
		 * @return link to query
		 */
		function getPersonalVariables ()
		{
			return $this->query("SELECT * FROM rule_personalInfo WHERE isActive = 1");
		}


		/**
		 * gets active subjects
		 */
		function getActiveSubjects ()
		{
			return $this->query("SELECT * FROM rule_subjects WHERE isActive = 1");
		}

		/**
		 * gets active recipients
		 */
		function getActiveRecipients ()
		{
			return $this->query("SELECT * FROM rule_recipients WHERE isActive = 1");
		}

		/**
		 * writes to logs
		 * @param string $oldName name of the old file
		 * @param string $newName of the file
		 * @param string $content of the file
		 * @param string $log message
		 */
		function writeLog($oldName, $newName, $content, $log)
		{
			$safe = SQLite3::escapeString($content);
			$this->exec("INSERT INTO logs (oldFileName, newFileName, fileContent, log) VALUES ('$oldName', '$newName', '$safe', '$log');");
		}


		/**
		 * \brief opens database connection or creates file is not found
		 **/
		function open()
		{
			$this->db = new SQLite3("/data/database/paperyard.sqlite");
			$this->db->busyTimeout(15000);
		// WAL mode has better control over concurrency.
		// Source: https://www.sqlite.org/wal.html
			$this->db->exec('PRAGMA journal_mode = wal;');
		}

		/**
		 * \brief checks if the database schema needs to be updated
		 **/
		function update()
		{
			$this->output("looking for updates");
			$updates = glob("update_version_*.sqlite");
			foreach($updates as $update){
				$this->output("found update script");
			}
		}

		/**
		 * \brief closes database connection
		 **/
		function close()
		{

			$res = $this->db->close();
			unset($this->db);
		}

	}

?>
