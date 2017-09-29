<html>
<body>
	<pre>
<?php
	require_once('dbHandler.php');
	require_once('helper.php');


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
    echo "Program call from CLI detected.\n";
		if ($db->getConfigValue('enableCron')==0) {
				echo "please enable cron in config\n";
				die();
			}
		}else{
    echo "Program call from Webserver detected.\n";
}
/** @endcond */


// creating folder structure in case it does not exist
exec('mkdir -p /data/scan');
exec('mkdir -p /data/scan/error');
exec('mkdir -p /data/scan/archive');
exec('mkdir -p /data/inbox');
exec('mkdir -p /data/outbox');
exec('mkdir -p /data/sort');


// switching to working directory
echo "calling the renamer ... \n";
chdir("/data/inbox");

//loop all pdfs
$pdfs = glob("*.pdf");
foreach($pdfs as $pdf){
    $pdf=new pdfNamer($pdf, $db);
	$pdf->run();
}
echo "\n";

echo "\n Thanks for watching....\n\n";

$db->close();

?>
	</pre>
</body>
</html>
