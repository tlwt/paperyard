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

		// updating table model
		$this->update();

		//$this->alterTableAddColumns("testtable", "createdDate", " TEXT");
		//$this->alterTableDropColumns("testtable", "createdDate,modifiedDate,publishedDate");

		} // End constructor


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
			$this->output("looking for DB updates");
			chdir("/www/updates/sqlite/");
			$updates = glob("*.sql");
			foreach($updates as $update){
				$dbversion = $this->getConfigValue("databaseVersion");
				$version = str_replace(".sql", "", $update);
				// really only execute next one - if that fails no further updates shall be attempted
				if ($version == $dbversion+1) {
					$this->output ("applying " . $update);
					$this->output("found update script:" . $version);
					$sql = file_get_contents ($update);
					/** \bug more error handling needed? */
					$this->exec($sql);
				}
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
