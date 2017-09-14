<?php

	class pdfNamer {
	
		// constructor
		function pdfNamer($pdf) {
			// cleaning the log
			$this->log = "";
			
			// old name equals new name in the beginning
			$this->oldName=$pdf;
			$this->newName=$pdf;
			
			// creating db handler to talk to DB
			$this->db=new dbHandler();
			
			// reads the pdf
			$this->getTextFromPdf($pdf);
		}
		
		
		// extracts text from PDF and writes it into variable
		function getTextFromPdf($pdf) {
			// reads content into $this->content 
			exec('pdftotext "' . $pdf . '" -', $this->content);
			//exec('pdftotext "' . $pdf . '"');	
		}
		
		/**
		 * adds a string to a log message which later can be written to DB out to STDOUT
		 *
		 * @param string $str contains the message which shall be added to log
		 * @return none
		 */
		function addToLog($str) {
			$this->log .= $str . "\n"; 
		}
	
		/**
		 * converts a text string to a date. used for array walk
		 *
		 * @param pointer $item pointer to array item
		 * @param string $key array key name
		 * @return none
		 */	
		function toDate(&$item, $key)	{
			self::addtolog("Date found $item");
			$item = date("Ymd", strtotime($item));
		}

		/**
		 * takes an array of dates and returns the closest one before today.
		 * Paper documents have dates in the past, not in the future
		 *
		 * @param array $array containing all dates in YYYYMMDD format
		 * @return string YYYYMMDD if match or ddatum if failed to match a date
		 */	
		function closestDateToToday ($array) {
			foreach ($array as $value) {
				if ($value<=date('Ymd'))
					return $value;
			}
			return "ddatum";
		}
		

		/**
		 * takes the PDF content and cleans it up
		 *
		 * @param none
		 * @return none
		 */			
		function cleanContent() {
			// todo: remove everything but digits and letters
			
			// ?? remove all whitespaces to ease recognition?
		
			// cleaning output removing duplicate spaces
			$this->content = preg_replace("/\s\s+/", " ", implode(" ", $this->content));		
		}
		
		// 
		/**
		 * looks regular expression dates in the content of the file
		 *
		 * @param none
		 * @return none
		 */			
		function matchDates() {
			$this->addToLog('');
			$this->addToLog('===');
			$this->addToLog('LOOKING FOR DATES');	

			// Datumsformate
			preg_match_all ('/(\d{2}\.\d{2}\.\d{4})|([0-9]{1,2})\.\s?(Januar|Februar|März|April|Mai|Juni|Juli|August|September|Oktober|November|Dezember)\s?(\d{2,4})/', $this->content, $dates);

			// only consider full matches and remove duplicates
			$dates = array_unique($dates[0]);

			// getting into YYYYmmdd format
			array_walk($dates, 'self::toDate');
			$dates = array_unique($dates);
			arsort($dates);
			
			// most likely date found
			$this->newDate = $this->closestDateToToday($dates);
		
			// changing date in fileName
			$this->newName = str_replace("ddatum",$this->newDate, $this->newName);		
		
		}
		
		/**
		 * reads rulesets from database and executes accordingly
		 *
		 * @param none
		 * @return none
		 */
		function matchRules() {
			// looking for active rules from database to check document against
			$results = $this->db->getActiveRules();		
		
			// going thru ruleset
			$company = array();
			$subject = array();
			
			$this->addToLog('');
			$this->addToLog('===');
			$this->addToLog('COMPANY AND SUBJECT SCORE');

			while ($row = $results->fetchArray()) {
				$cfound = substr_count($this->content, $row['foundWords']);
				@$company[$row['fileCompany']] += $row['companyScore'] *  $cfound;
				$this->addToLog('"' . $row['foundWords'] . '" ' . "$cfound found - " . $cfound*$row['companyScore'] . " points for company " . $row['fileCompany']);
	
				@$subject[$row['fileSubject']] += $row['subjectScore'] *  $cfound;
				$this->addToLog('"' . $row['foundWords'] . '" ' . "$cfound found - " . $cfound*$row['subjectScore'] . " points for subject " . $row['fileSubject']);

			}

			// sorting so highest match is on top
			arsort($company);
			arsort($subject);

			$companyMatchRating = $company[key($company)];
			$subjectMatchRating = $subject[key($subject)];
			$companyName = key($company);
			$subjectName = key($subject);

			// checking match ranking
			echo "company: " . $companyName . " has rating " . $companyMatchRating . "\n";
			echo "subject: " . $subjectName . " has rating " . $subjectMatchRating . "\n";

			if ($companyMatchRating > 20) {
				$this->newName = str_replace("ffirma",$companyName, $this->newName);
			}

			if ($subjectMatchRating > 20) {
				$this->newName = str_replace("bbetreff",$subjectName, $this->newName);
			}


			echo "new name: " . $this->newName . "\n";
	
		}
		
		
		/**
		 * main function calling relevant process steps to identify document
		 * 
		 * @param none
		 * @return none
		 */
		
		function run() {	
			// cleaning content of the PDF document
			$this->cleanContent();

			// looking for dates in content
			$this->matchDates();
	
			// matching rule sets from database
			$this->matchRules();

			// renaming the file
			exec('mv "' . $this->oldName . '" "' . $this->newName . '"');	
			
			// logging everything to database
			$this->db->writeLog($this->oldName, $this->newName, $this->content, $this->log);
		}
	
	}


	class dbHandler {
		var $db;
	
		// constructor
		// takes care of basic db handling
		function dbHandler() {
		// connects or creates sqlite db file
		$this->db = new SQLite3("/data/ruleSet.sqlite");
		
		// creating tables in case they do not exist
		// ruleset
		$this->db->exec("CREATE TABLE IF NOT EXISTS ruleSet(
		   id INTEGER PRIMARY KEY AUTOINCREMENT, 
		   foundWords TEXT,   
		   fileCompany TEXT,
		   fileSubject TEXT,
		   companyScore INTEGER NOT NULL DEFAULT (0),
		   subjectScore INTEGER NOT NULL DEFAULT (0),
		   isActive INTEGER NOT NULL DEFAULT (1))");
		
		// config
		$this->db->exec("CREATE TABLE IF NOT EXISTS config(
		   id INTEGER PRIMARY KEY AUTOINCREMENT, 
		   configVariable TEXT,   
		   configValue TEXT)");
		
		// logs
		$this->db->exec("CREATE TABLE IF NOT EXISTS logs(
		   id INTEGER PRIMARY KEY AUTOINCREMENT, 
		   oldFileName TEXT,   
		   newFileName TEXT,   
		   fileContent TEXT,   
		   log TEXT)");				
		}
		
		// gets active ruleset
		function getActiveRules () {
			return $this->db->query("SELECT * FROM ruleSet WHERE isActive = 1");
		}
		
		// adds something to the log
		function writeLog($oldName, $newName, $content, $log) {
			$safe = SQLite3::escapeString($content);
			$this->db->exec("INSERT INTO logs (oldFileName, newFileName, fileContent, log) VALUES ('$oldName', '$newName', '$safe', '$log');");			
		}
		
		
	}


// looping main directory and calling the pdf parser
echo "starting paperyard\n";
$files = glob("/data/ddatum*.pdf");
foreach($files as $pdf){
    $pdf=new pdfNamer($pdf);
	$pdf->run();
}


   
?>