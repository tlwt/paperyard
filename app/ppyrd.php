<?php
	class dbHandler {
		var $db;
	
		// constructor
		// takes care of basic db handling
		function dbHandler() {
		// connects or creates sqlite db file
		$this->db = new SQLite3("/data/paperyard.sqlite");
		
		// creating tables in case they do not exist
		// rules to detect senders of a document
		$this->db->exec("CREATE TABLE IF NOT EXISTS rule_senders(
		   id INTEGER PRIMARY KEY AUTOINCREMENT, 
		   foundWords TEXT,   
		   fileCompany TEXT,
		   companyScore INTEGER NOT NULL DEFAULT (0),
		   isActive INTEGER NOT NULL DEFAULT (1))");

		// rules to detect subject of a document
		$this->db->exec("CREATE TABLE IF NOT EXISTS rule_subjects(
		   id INTEGER PRIMARY KEY AUTOINCREMENT, 
		   foundWords TEXT,   
		   foundCompany TEXT,
		   fileSubject TEXT,
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

		// recipients
		$this->db->exec("CREATE TABLE IF NOT EXISTS rule_recipients (
		   id INTEGER PRIMARY KEY AUTOINCREMENT, 
		   recipientName TEXT,   
		   shortNameForFile TEXT,
		   isActive INTEGER DEFAULT (1) )");				
		} // End constructor
		

				
		
		// gets active ruleset
		function getActiveRules () {
			return $this->db->query("SELECT * FROM ruleSet WHERE isActive = 1");
		}

		// gets active ruleset
		function getActiveSenders () {
			return $this->db->query("SELECT * FROM rule_senders WHERE isActive = 1");
		}
		
		function getConfigValue ($variable) {
			$results = $this->db->query("SELECT * FROM config WHERE configVariable = '$variable'");
			$row = $results->fetchArray();
			return $row['configValue'];
			
		
		}
		
		// gets active ruleset
		function getActiveSubjects () {
			return $this->db->query("SELECT * FROM rule_subjects WHERE isActive = 1");
		}		

		// gets active ruleset
		function getActiveRecipients () {
			return $this->db->query("SELECT * FROM rule_recipients WHERE isActive = 1");
		}
		
		// adds something to the log
		function writeLog($oldName, $newName, $content, $log) {
			$safe = SQLite3::escapeString($content);
			$this->db->exec("INSERT INTO logs (oldFileName, newFileName, fileContent, log) VALUES ('$oldName', '$newName', '$safe', '$log');");			
		}
		
		
	}



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
			
			// what mimimum score is required until we accept the company as correct
			$this->companyMatchRating = $this->db->getConfigValue("companyMatchRating");

			// what mimimum score is required until we accept the company as correct
			$this->subjectMatchRating = $this->db->getConfigValue("subjectMatchRating");

			$this->dateRegEx = $this->db->getConfigValue("dateRegEx");
			
			
			// reads the pdf
			$this->getTextFromPdf($pdf);
		}
		
		
		// extracts text from PDF and writes it into variable
		function getTextFromPdf($pdf) {
			// reads content into $this->content 
			exec('pdftotext -layout "' . $pdf . '" -', $this->content);
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
			// get everything into one long string
			$this->content = implode(" ", $this->content);
			
			// convert everything to lowercase to avoid case sensitive mismatches
			$this->content = strtolower($this->content);
			
			// remove spaces if there is more than one (double space, tripple space etc.);
			$this->content = preg_replace("/\s\s+/", " ", $this->content);								
			
			// todo: remove everything but digits and letters
			$this->content = preg_replace("/[^0-9a-zA-ZÄäÖöÜüß\.\,\-]+/", " ", $this->content);					
					
			//var_dump($this->content);
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
			preg_match_all ($this->dateRegEx, $this->content, $dates);


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
		function matchSenders () {
			// looking for active rules from database to check document against
			$results = $this->db->getActiveSenders();
			$company = array();	
			
			// start  matching search terms vs content
			while ($row = $results->fetchArray()) {

				// checking if there are multiple search terms separated by a comma
				
				// start - just one searchterm
				if (strpos($row['foundWords'], ",")=== false) {
					
					// checking if we found it at least once
					if (substr_count($this->content, strtolower($row['foundWords']))>0) {
						@$company[$row['fileCompany']] += $row['companyScore'];
						}
				} // end - just one search
				
				
				// start - multiple search terms
				 else {

					// separating search terms and removing white spaces				
					$split = explode(',',  strtolower($row['foundWords']));

					// break variable to stop in case one word was not found
					$foundAll = true;
					foreach ($split as $value) {
						if($foundAll) {
						
							// removing any whitespace
							$value = trim($value);
							
							// counting occurances
							$cfound = substr_count($this->content, $value);
							
							
							// setting stop variable since nothing was found	
							if ($cfound==0) {
								$foundAll= false;
							}
						}
					}
					
					// found all - lets write the result
					if ($foundAll) 	{
						@$company[$row['fileCompany']] += $row['companyScore'];
					} 
					
					// not all found - thus no results to write
					else {
					}					
				}
			} // end - matching search terms vs content
			

			// sorting so highest match is on top
			arsort($company);

			$companyMatchRating = $company[key($company)];
			$this->companyName = key($company);

			// checking match ranking
			echo "company: " . $this->companyName . " scored " . $companyMatchRating . "\n";

			if ($companyMatchRating > $this->companyMatchRating) {
				$this->newName = str_replace("ffirma",$this->companyName, $this->newName);
			}			
			

			// writing log
			$this->addToLog('"' . $row['foundWords'] . '" ' . " found - " . $row['companyScore'] . " points for company " . $row['fileCompany']);			
		
		}
		
		
		
		function matchSubjects() {
			// looking for active rules from database to check document against
			$results = $this->db->getActiveSubjects();
			$subject = array();	
			
			// start  matching search terms vs content
			while ($row = $results->fetchArray()) {

				// checking if the found company matches the company specified in subject rule
				// also checking that it is not empty
				if ($row['foundCompany']== $this->companyName || empty(trim($row['foundCompany']))) {


					// checking if there are multiple search terms separated by a comma
				
					// start - just one searchterm
					if (strpos($row['foundWords'], ",")=== false) {
					
						// checking if we found it at least once
						if (substr_count($this->content, strtolower($row['foundWords']))>0) {
							@$subject[$row['fileSubject']] += $row['subjectScore'];
							}
					} // end - just one search
				
				
					// start - multiple search terms
					 else {

						// separating search terms and removing white spaces				
						$split = explode(',',  strtolower($row['foundWords']));

						// break variable to stop in case one word was not found
						$foundAll = true;
						foreach ($split as $value) {
							if($foundAll) {
						
								// removing any whitespace
								$value = trim($value);
							
								// counting occurances
								$cfound = substr_count($this->content, $value);
							
							
								// setting stop variable since nothing was found	
								if ($cfound==0) {
									$foundAll= false;
								}
							}
						}
					
						// found all - lets write the result
						if ($foundAll) 	{
							@$subject[$row['fileSubject']] += $row['subjectScore'];
						} 
					
						// not all found - thus no results to write
						else {
							}					
						}
					} // end check if company name matches
			} // end - matching search terms vs content
			

			// sorting so highest match is on top
			arsort($subject);

			$subjectMatchRating = $subject[key($subject)];
			$this->subjectName = key($subject);

			// checking match ranking
			echo "subject: " . $this->subjectName . " scored " . $subjectMatchRating . "\n";

			if ($subjectMatchRating > $this->subjectMatchRating) {
				$this->newName = str_replace("bbetreff",$this->subjectName, $this->newName);
			}			
			

			// writing log
			$this->addToLog('"' . $row['foundWords'] . '" ' . " found - " . $row['subjectScore'] . " points for subject " . $row['fileSubject']);			
		
		
		}
		

		/**
		 * reads recipient list from database and tries to match in text
		 *
		 * @param none
		 * @return none
		 */		
		function matchRecipients() {
			// looking for active rules from database to check document against
			$results = $this->db->getActiveRecipients();
			$recipients = array();
		
			// for each rule check if the name occures in the text.
			while ($row = $results->fetchArray()) {
				$cfound = substr_count($this->content, strtolower($row['recipientName']));
				@$recipients[$row['shortNameForFile']] += $cfound;	
			}
		
			// sort the results alphabetically
			asort($recipients);
			
			// kill all entries which have not been matched
			foreach ($recipients as $name => $score) {
				if ($score == 0) unset($recipients[$name]);
			}
			
			// switch key & values => as we want to have the name and not the # of hits
			// join all hits with a comma
			$recipients = implode(',',array_flip($recipients));
			
			// write the new name
			$this->newName = str_replace("wwer",$recipients, $this->newName);
			

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
			//$this->matchRules();
			
			// match recipients from database
			$this->matchSenders();
			// match recipients from database
			$this->matchSubjects();


			
			// match recipients from database
			$this->matchRecipients();



			// renaming the file
			//exec('mv "' . $this->oldName . '" "' . $this->newName . '"');	
			
			echo "new name: " . $this->newName . "\n";

			
			// logging everything to database
			$this->db->writeLog($this->oldName, $this->newName, $this->content, $this->log);
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
   
?>