<html>
<body>
	<pre>
<?php
	/**
		* @file
		* \author Till Witt
		* \brief 	this file contains the backend functionality for paperyard.
		* \bug certainly still a lot
		* \details
		* It collects files, and processes the ruleset
		*
	 	*/


	class dbHandler {
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
		$this->db = new SQLite3("/data/database/paperyard.sqlite");

		// creating tables in case they do not exist
		// rules to detect senders of a document
		$this->db->exec("CREATE TABLE IF NOT EXISTS rule_senders(
		   id INTEGER PRIMARY KEY AUTOINCREMENT,
		   foundWords TEXT,
		   fileCompany TEXT,
		   companyScore INTEGER NOT NULL DEFAULT (0),
		   tags	TEXT,
		   isActive INTEGER NOT NULL DEFAULT (1))");

		// rules to detect subject of a document
		$this->db->exec("CREATE TABLE IF NOT EXISTS rule_subjects(
		   id INTEGER PRIMARY KEY AUTOINCREMENT,
		   foundWords TEXT,
		   foundCompany TEXT,
		   fileSubject TEXT,
		   subjectScore INTEGER NOT NULL DEFAULT (0),
		   tags	TEXT,
		   isActive INTEGER NOT NULL DEFAULT (1))");

		// config
		$this->db->exec("CREATE TABLE IF NOT EXISTS config(
		   id INTEGER PRIMARY KEY AUTOINCREMENT,
		   configVariable TEXT,
		   configValue TEXT)");

		// logs
		$this->db->exec("CREATE TABLE IF NOT EXISTS logs(
		   id INTEGER PRIMARY KEY AUTOINCREMENT,
			 execDate TEXT DEFAULT (datetime('NOW')),
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

		// Setting up config values in case they dont exist
		$this->db->exec("INSERT INTO config(configVariable,configValue)
											SELECT 'companyMatchRating', '20'
											WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'companyMatchRating')");

		$this->db->exec("INSERT INTO config(configVariable,configValue)
											SELECT 'subjectMatchRating', '20'
											WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'subjectMatchRating')");
		$this->db->exec("INSERT INTO config(configVariable,configValue)
											SELECT 'dateRegEx', '/(\d{2}\.\d{2}\.\d{4})|([0-9]{1,2})\.\s?(januar|februar|märz|april|mai|juni|juli|august|september|oktober|november|dezember)\s?(\d{2,4})/'
											WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'dateRegEx')");
		$this->db->exec("INSERT INTO config(configVariable,configValue)
											SELECT 'stripCharactersFromContent', '/[^0-9a-zA-ZÄäÖöÜüß\.\,\-]+/'
											WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'stripCharactersFromContent')");
		$this->db->exec("INSERT INTO config(configVariable,configValue)
											SELECT 'matchPriceRegex', '/(\s?((\d{1,3}(\.\d{3})+)|(\d{1,3})),\d\ds?(euro?|€)?)/'
											WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'matchPriceRegex')");
		$this->db->exec("INSERT INTO config(configVariable,configValue)
											SELECT 'enableCron', '1'
											WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'enableCron')");
		$this->db->exec("INSERT INTO config(configVariable,configValue)
											SELECT 'newFilenameStructure', 'ddatum - ffirma - bbetreff (wwer) (bbetrag) [nt] -- '
											WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'newFilenameStructure')");
		$this->db->exec("INSERT INTO config(configVariable,configValue)
											SELECT 'appendOldFilename', '1'
											WHERE NOT EXISTS(SELECT 1 FROM config WHERE configVariable = 'appendOldFilename')");
		} // End constructor



		/**
		 * gets active ruleset
		 */
		function getActiveArchiveRules ()
		{
			return $this->db->query("SELECT * FROM rule_archive WHERE isActive = 1");
		}

		/**
		 * gets active senders
		 */
		function getActiveSenders ()
		{
			return $this->db->query("SELECT * FROM rule_senders WHERE isActive = 1");
		}

		/**
		 * gets config values
		 * @param string $varname to query
		 * @return string containing variable value
		 */
		function getConfigValue ($varname)
		{
			$results = $this->db->query("SELECT * FROM config WHERE configVariable = '$varname'");
			$row = $results->fetchArray();
			return $row['configValue'];
		}


		/**
		 * gets active subjects
		 */
		function getActiveSubjects ()
		{
			return $this->db->query("SELECT * FROM rule_subjects WHERE isActive = 1");
		}

		/**
		 * gets active recipients
		 */
		function getActiveRecipients ()
		{
			return $this->db->query("SELECT * FROM rule_recipients WHERE isActive = 1");
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
			$this->db->exec("INSERT INTO logs (oldFileName, newFileName, fileContent, log) VALUES ('$oldName', '$newName', '$safe', '$log');");
		}


	}


	/*!
		* \class pdfNamer
		* \brief takes care of the correct naming of files
		* \author Till Witt
		*/
	class pdfNamer {

		/**
		 * \brief constructor for the class
		 * @param string $pdf with file name to process
		 * @param string $db with database connection
		 * @return none
		 */
		public function __construct($pdf, $db)
		{
			// cleaning the log
			$this->log = "";

			// dont output debug information
			$this->debug = false;

			// creating db handler to talk to DB
			$this->db=$db;

			// old name equals new name in the beginning
			$this->oldName=$pdf;

			// set new name only if it has not been applied already (e.g. a document is not fully recognized and rematched with updated DB entries)
			/** \bug this still checks for a date at the start - but this may change according to configured value.*/
			if (!preg_match('(ddatum|ffirma|bbetreff|wwer|bbetrag)',$pdf) && !preg_match('(^\d{8}\s\-)',$pdf)) {
					// getting new file name structure from database
					$this->newName=$this->db->getConfigValue('newFilenameStructure');
					// appending old file name - otherwise just add .pdf to the end
					if ($db->getConfigValue('appendOldFilename')==1) {
						$this->newName .=  $pdf;
					} else {
						$this->newName .= ".pdf";
					}


					}
				else {
					$this->newName=$pdf;
				}

			$this->companyName = "";
			$this->subjectName = "";

			// standard tag if no tags are found
			$this->tags = "[nt]";



			// what mimimum score is required until we accept the company as correct
			$this->companyMatchRating = $this->db->getConfigValue("companyMatchRating");

			// what mimimum score is required until we accept the company as correct
			$this->subjectMatchRating = $this->db->getConfigValue("subjectMatchRating");

			$this->dateRegEx = $this->db->getConfigValue("dateRegEx");

			// reads the pdf
			$this->getTextFromPdf($pdf);
		}

		/**
		 * \brief outputs string
		 * \bug no debug handling implemented yet. https://github.com/tlwt/paperyard/issues/10
		 * @param string $string to output
		 * @param int $debug set to 1 to debug
		 */
		function output($string, $debug=0)
		{
					echo "$string\n";
		}


		/**
		 * \brief function executes pdftotext to extract text from file
		 * @param string $pdf name of file
		 */
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
		 * \brief converts a text string to a date. used for array walk in \link matchDates \endlink
		 *
		 * @param pointer $item pointer to array item
		 * @param string $key array key name
		 * @return none
		 */
		function toDate(&$item, $key)	{
			self::addtolog("Date found $item");
			if (strpos($item, "im") !== false) {
				$item =str_replace("im ","",$item);
				$item = date("Ymt", strtotime($item));
			} else {
				$item = date("Ymd", strtotime($item));
			}
		}

		/**
		 * \brief takes an array of dates and returns the closest one before today (since
		 * Paper documents have dates in the past, not in the future)
		 *
		 * @param array $dates containing all dates in YYYYMMDD format
		 * @return string YYYYMMDD if match or ddatum if failed to match a date
		 */
		function closestDateToToday ($dates)
		{
			arsort($dates);
			foreach ($dates as $date) {
				if ($date<=date('Ymd'))
					return $date;
			}
			return "ddatum";
		}


		/**
		 * \brief takes the PDF content and cleans it up
		 * @param none
		 * @return none
		 */
		function cleanContent()
		{
			// get everything into one long string
			$this->content = implode(" ", $this->content);

			// convert everything to lowercase to avoid case sensitive mismatches
			$this->content = strtolower($this->content);

			// todo: remove everything but digits and letters
			$this->content = preg_replace($this->db->getConfigValue('stripCharactersFromContent'), " ", $this->content);

			// remove spaces if there is more than one (double space, tripple space etc.);
			$this->content = preg_replace("/\s\s+/", " ", $this->content);

			//var_dump($this->content);
		}

		//
		/**
		 * \brief looks regular expression dates in the content of the file
		 * @param none
		 * @return none
		 */
		function matchDates()
		{
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

			// most likely date found
			$this->newDate = $this->closestDateToToday($dates);

			// changing date in fileName
			$this->newName = str_replace("ddatum",$this->newDate, $this->newName);

		}

		/**
		 * \brief reads rulesets from database and executes accordingly
		 *
		 * @param none
		 * @return none
		 */
		function matchSenders ()
		{
			// looking for active rules from database to check document against
			$results = $this->db->getActiveSenders();
			$company = array();
			$tmpMatchedCompanyTags= array();

			// start  matching search terms vs content
			while ($row = $results->fetchArray()) {

				// checking if there are multiple search terms separated by a comma

				// start - just one searchterm
				if (strpos($row['foundWords'], ",")=== false) {

					// checking if we found it at least once
					if (substr_count($this->content, strtolower($row['foundWords']))>0) {
						@$company[$row['fileCompany']] += $row['companyScore'];
						// keeping a list of match hits for later tagging
						$tmpMatchedCompanyTags[$row['fileCompany']][]=$row['tags'];

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
						// keeping a list of match hits for later tagging
						$tmpMatchedCompanyTags[$row['fileCompany']][]=$row['tags'];

						// writing log
						$this->addToLog('"' . $row['foundWords'] . '" ' . " found - " . $row['companyScore'] . " points for company " . $row['fileCompany']);
					}

					// not all found - thus no results to write
					else {
					}
				}

			} // end - matching search terms vs content


			// sorting so highest match is on top
			arsort($company);

			if (isset($company[key($company)])) {
				$companyMatchRating = $company[key($company)];
				$this->companyName = key($company);
				$this->matchedCompanyTags = $tmpMatchedCompanyTags[$this->companyName];

				// checking match ranking
				$this->output("company: " . $this->companyName . " scored " . $companyMatchRating);

				if ($companyMatchRating >= $this->companyMatchRating) {
					$this->newName = str_replace("ffirma",$this->companyName, $this->newName);
				}
			}



		}

		/**
		 * \brief checks if there is a price in the text
		 */
		function matchPrice()
		{
			// matching all potential price mentions
			preg_match_all($this->db->getConfigValue('matchPriceRegex'), $this->content, $results);

			// getting values of full match only
			$prices = array_values($results[0]);

			// removing all non numeric characters except comma and period
			$prices = preg_replace("/[^0-9,.]/", "", $prices);
			$maxprice = 0;
			foreach ($prices as $price) {
				$price = floatval(str_replace(',','.',str_replace('.','', $price)));
				if ($price > $maxprice) $maxprice = $price;
			}

			// setting max price
			$this->price=number_format($maxprice,2,",",".");

			$this->newName = str_replace("bbetrag","EUR".$this->price, $this->newName);

			$this->output("amount:  EUR" . $this->price);

			}


			/**
			 * \brief matching subject
			 */
		function matchSubjects() {
			// looking for active rules from database to check document against
			$results = $this->db->getActiveSubjects();
			$subject = array();

			$tmpMatchedSubjectTags = array();

			// start  matching search terms vs content
			while ($row = $results->fetchArray()) {

				// checking if the found company matches the company specified in subject rule
				// also checking that it is not empty
				@$tmpFoundCompany = trim($row['foundCompany']);
				if ($tmpFoundCompany== $this->companyName || empty($tmpFoundCompany)) {


					// checking if there are multiple search terms separated by a comma

					// start - just one searchterm
					if (strpos($row['foundWords'], ",")=== false) {

						// checking if we found it at least once
						if (substr_count($this->content, strtolower($row['foundWords']))>0) {
							@$subject[$row['fileSubject']] += $row['subjectScore'];

							// keeping a list of match hits for later tagging
							$tmpMatchedSubjectTags[$row['fileSubject']][]=$row['tags'];
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
							// keeping a list of match hits for later tagging
							$tmpMatchedSubjectTags[$row['fileSubject']][]=$row['tags'];
							// writing log
							$this->addToLog('"' . $row['foundWords'] . '" ' . " found - " . $row['subjectScore'] . " points for subject " . $row['fileSubject']);
						}

						// not all found - thus no results to write
						else {
							}
						}
					} // end check if company name matches
			} // end - matching search terms vs content


			// sorting so highest match is on top
			arsort($subject);

			@$subjectMatchRating = $subject[key($subject)];
			$this->subjectName = key($subject);
			@$this->matchedSubjectTags = $tmpMatchedSubjectTags[$this->subjectName];

			// checking match ranking
			$this->output("subject: " . $this->subjectName . " scored " . $subjectMatchRating);

			if ($subjectMatchRating >= $this->subjectMatchRating) {
				$this->newName = str_replace("bbetreff",$this->subjectName, $this->newName);
			}




		}


		/**
		 * \brief reads recipient list from database and tries to match in text
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
				$this->output("look for " . $row['recipientName'] . " found $cfound", 1);
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
			if (!empty($recipients))
				$this->newName = str_replace("wwer",$recipients, $this->newName);
		}

		/**
		 * \brief function adds tags once company and subject are correctly matched
		 */
		function addTags() {
			// tossing all tags into one array
			@$alltags = array_merge($this->matchedCompanyTags, $this->matchedSubjectTags);

			// splitting up comma separated values and putting them back into the array
			@$tags = explode(',',join(",", $alltags));

			// cleaning the tags
			$cleantags = "";
			foreach ($tags as $tag) {
				$tag = trim($tag);
				if (!empty($tag))
					$cleantags[] = "[$tag]";
			}

			// removing duplicates
			if (is_array($cleantags)) {
				$cleantags = array_unique($cleantags);

				// sorting tags
				asort($cleantags);

				// joining them into one string
				$this->tags=implode($cleantags);

			$this->output("tags:    " . $this->tags);
			}
				else {
					$this->output("tags:    no tags to assign");
				}

			// changing date in fileName only if tags are to assign
			if (!empty($this->tags))
				$this->newName = str_replace("[nt]",$this->tags, $this->newName);

		}


		/**
		 * \brief main function calling relevant process steps to identify document
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

			// matching tags once company and subject are matched
			$this->addTags();



			// match recipients from database
			$this->matchRecipients();

			//
			$this->matchPrice();

			// renaming the file in case everything matched
			if (!preg_match('(ddatum|ffirma|bbetreff|wwer|bbetrag)',$this->newName)) {
					exec('mv --backup=numbered "' . $this->oldName . '" "../outbox/' . $this->newName . '"');
					}
				else {
					// dont move in case something is still unmatched
					if ($this->oldName != $this->newName) {
						exec('mv --backup=numbered "' . $this->oldName . '" "' . $this->newName . '"');
					}
				}

			$this->output("new name: " . $this->newName);


			// logging everything to database
			$this->db->writeLog($this->oldName, $this->newName, $this->content, $this->log);
		}

	}


	/**
	 * \brief Sorts thru PDF documents and puts them into corresponding folders etc.
	 * @param none
	 * @return none
	 */
	class pdfSorter {

		/**
		 * \brief constructor
		 * @param string $pdf file name to be processed
		 * @param string $db database handler
		 * @return none
		 */
		public function __construct($pdf, $db)
		{
				$this->pdf = $pdf;

				// creating db handler to talk to DB
				$this->db=$db;
		}

		/**
		 * \brief function gets from file name the information what the date, company and subject is.
		 */
		function splitUpFilename()
		{
			$this->output("working on: " . $this->pdf);

			// getting the file name template - needed to see what part (date, company, subject) is at which part of the string
			$newFilenameStructure=$this->db->getConfigValue('newFilenameStructure');

			// getting things via regex
			// removing closing parts of the brackets as they are not needed in this case
			$unwanted = array(')',']');
			$separators = "/ - | \(| \[| \-\- /";
			$templateName = str_replace($unwanted, '', $newFilenameStructure);

			// splitting up template name into parts
			$templateParts = array_flip(preg_split($separators, $templateName));

			// now doing the same stuff with the actual file
			$tmpName = str_replace($unwanted, '', $this->pdf);
			$filenameParts = preg_split($separators, $tmpName);



			// separating the file name into its parts
			$parts = explode(" - ", $this->pdf);

			// date is 1st
			$this->date = $filenameParts[$templateParts['ddatum']];
			$this->year = date('Y',strtotime($this->date));
			$this->month = date('m',strtotime($this->date));
			$this->day = date('d',strtotime($this->date));

			// company etc.
			$this->company = $filenameParts[$templateParts['ffirma']];
			$this->recipient = $filenameParts[$templateParts['wwer']];
			$this->subject = $filenameParts[$templateParts['bbetreff']];
			$this->amount = $filenameParts[$templateParts['bbetrag']];



			// getting all tags @todo - needs to be least greedy ...
			preg_match_all('/\[(\d|\w)*\]/',$this->pdf,$tags);
			$this->tags = implode($tags[0]);

			//
			$this->output( "date: " . $this->date);
			$this->output( "year: " . $this->year);
			$this->output( "month: " . $this->month);
			$this->output( "day: " . $this->day);

			$this->output( "company: " . $this->company);
			$this->output( "recipient: " . $this->recipient);
			$this->output( "subject: " . $this->subject);
			$this->output( "amount: " . $this->amount);
			$this->output( "tags: " . $this->tags);

		}

		/**
		 * \brief Output formatter
		 * @param string $string what to output
		 * @debug int $debug to specify if debug or not
		 */
		function output($string, $debug=0)
		{
					echo "$string\n";
		}

		/**
		 * \brief checks rules
		 */
		function checkRules()
		{
			$rules = $this->db->getActiveArchiveRules();
			while ($row = $rules->fetchArray()) {
				// we have  a rule match if the company found matches the specified string
				// * is the wild card like in file names
				$match = fnmatch($row['company'], $this->company)
								&& fnmatch($row['subject'], $this->subject)
								&& fnmatch($row['recipient'], $this->recipient)
								&& fnmatch($row['tags'], $this->tags);

				// if everything matched - go ahead
				if ($match) {
						// processing the folder to which document shall be moved
						$toFolder = $row['toFolder'];

						// in case the [year] variable has been used etc.
						$toFolder = str_replace('[year]', $this->year, $toFolder);
						$toFolder = str_replace('[month]', $this->month, $toFolder);
						$toFolder = str_replace('[day]', $this->day, $toFolder);
						$toFolder = str_replace('[recipient]', $this->recipient, $toFolder);

						// adding a trailing slash in case none existed
						$toFolder = rtrim($toFolder, '/') . '/';

						// create folders in case required
						exec("mkdir -p $toFolder");

						// move the file to destination folder
						exec('mv --backup=numbered "' . $this->pdf . '" "' . $toFolder . $this->pdf . '"');

						$this->db->writeLog($this->pdf, $this->pdf, "", "Moved file to: " . $toFolder);
				}
			}
		}

		/**
		 * \brief runs the main process
		 */
		function run()
		{

			// process the file name first
			$this->splitUpFilename();

			// then see if there is any rule to process
			$this->checkRules();
		}
	}

	class pdfScanner
	{
		function __construct($pdf,$db)
		{
				$this->pdf = $pdf;
				$this->db = $db;
		}

		function run()
		{
			// ensuring that we only have one OcrMyPDF running process running even though
			$fp = fopen('/tmp/ppyrdOcrMyPdf.txt', 'w+');
			var_dump($fp);

			/* Aktiviere die LOCK_NB-Option bei einer LOCK_EX-Operation */
			if(flock($fp, LOCK_EX))
			{
					exec("ocrmypdf -l deu --tesseract-timeout 600  --deskew --rotate-pages --tesseract-timeout 600 --oversample 600 --force-ocr '" . $this->pdf . "' '/data/inbox/" . $this->pdf . "'");
					if (file_exists("/data/inbox/" . $this->pdf))
					{
							echo "found ok OCR - moving input to archive";
							exec("mv --backup=numbered '" . $this->pdf . "' '/data/scan/archive/" . $this->pdf . "'");
					} else
					{
						echo "did not find ok OCR - moving input to error";

							exec("mv --backup=numbered '" . $this->pdf . "' '/data/scan/error/" . $this->pdf . "'");
					}

			} else
			{
				echo "OcrMyPdf still running - cannot interfere with it ... if this persists too long check /tmp/ppyrOcrMyPdf.txt and delete";
			}

			/* ... */

			fclose($fp);

		}
	}

// main program
// looping main directory and calling the pdf parser
echo "starting paperyard\n";

/**
 * creating db handler to talk to DB
 */
$db=new dbHandler();


/**
 * checking if called via command line or webserver
 * @cond */
if ("cli" == php_sapi_name())
	{
    echo "CLI\n";
		if ($db->getConfigValue('enableCron')==0) {
				echo "please enable cron in config\n";
				die();
			}
		}else{
    echo "WebServer\n";
}
/** @endcond */


// creating folder structure in case it does not exist
exec('mkdir -p /data/scan');
exec('mkdir -p /data/scan/error');
exec('mkdir -p /data/scan/archive');

exec('mkdir -p /data/inbox');
exec('mkdir -p /data/outbox');
exec('mkdir -p /data/sort');

// checking if any new PDFs need to be OCRed
echo "calling OcrMyPDF ... \n";
chdir("/data/scan");

//loop all pdfs
$pdfs = glob("*.pdf");
foreach($pdfs as $pdf){
    $pdf=new pdfScanner($pdf, $db);
		$pdf->run();
}

// switching to working directory
echo "calling the renamer ... \n";
chdir("/data/inbox");

//loop all pdfs
$pdfs = glob("*.pdf");
foreach($pdfs as $pdf){
    $pdf=new pdfNamer($pdf, $db);
	$pdf->run();
}

/*******************************************************************************/

echo "\n";
echo "calling the sorter ... \n";
chdir("/data/sort");

/**
 * reading all pdf files from current directory
 */
$pdfs = glob("*.pdf");

/**
 * loops thru all found pdfs and puts the individual pdf name into pdf variable
 */
foreach ($pdfs as $pdf){
    $pdf=new pdfSorter($pdf, $db);
		$pdf->run();
}

echo "\n Thanks for watching....\n\n";

?>
	</pre>
</body>
</html>
