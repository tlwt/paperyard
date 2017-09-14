<?php

	class pdfNamer {
	
	
	}

	$log = "";
	function addtolog($str) {
		global $log;
		$log .= $str . "\n"; 
	}

	function toDate(&$item, $key)	{
		addtolog("Date found $item");
	    $item = date("Ymd", strtotime($item));
	}

	// returns date closest to today
	function closestDateToToday ($array) {
		foreach ($array as $value) {
			if ($value<=date('Ymd'))
				return $value;
		}
	return "ddatum";
	}
	
	// get first parameter with which the script is called
	$pdf = $argv[1];
	$newName = $pdf;
	echo "\n";
	echo "======================\n";
	echo "Processing: " . $newName . "\n";
	
	// reads content into $output 
	exec('pdftotext "' . $pdf . '" -', $output);
	exec('pdftotext "' . $pdf . '"');

	
	// cleaning output removing duplicate spaces
	$output = preg_replace("/\s\s+/", " ", implode(" ", $output));

	// checking for dates in dd.mm.yyyy or dd.mm.yy format

	
	// Datumsformate
	preg_match_all ('/(\d{2}\.\d{2}\.\d{4})|([0-9]{1,2})\.\s?(Januar|Februar|März|April|Mai|Juni|Juli|August|September|Oktober|November|Dezember)\s?(\d{2,4})/', $output, $dates);

	// only consider full matches and remove duplicates
	$dates = array_unique($dates[0]);

addToLog('');
addToLog('===');
addToLog('FOUND DATES');	

	// getting into YYYYmmdd format
	array_walk($dates, 'toDate');
	$dates = array_unique($dates);
	arsort($dates);
	

	
	$newName = str_replace("ddatum",closestDateToToday($dates), $newName);


	$db = new SQLite3("/data/ruleSet.sqlite");
$db-> exec("CREATE TABLE IF NOT EXISTS ruleSet(
   id INTEGER PRIMARY KEY AUTOINCREMENT, 
   foundWords TEXT,   
   fileCompany TEXT,
   fileSubject TEXT,
   companyScore INTEGER NOT NULL DEFAULT (0),
   subjectScore INTEGER NOT NULL DEFAULT (0),
   isActive INTEGER NOT NULL DEFAULT (1))");
$db-> exec("CREATE TABLE IF NOT EXISTS config(
   id INTEGER PRIMARY KEY AUTOINCREMENT, 
   configVariable TEXT,   
   configValue TEXT)");
$db-> exec("CREATE TABLE IF NOT EXISTS logs(
   id INTEGER PRIMARY KEY AUTOINCREMENT, 
   oldFileName TEXT,   
   newFileName TEXT,   
   fileContent TEXT,   
   log TEXT)");		

$results = $db->query("SELECT * FROM ruleSet WHERE isActive = 1");

// going thru ruleset
$company = array();
$subject = array();
addToLog('');
addToLog('===');
addToLog('COMPANY AND SUBJECT SCORE');

while ($row = $results->fetchArray()) {
    $cfound = substr_count($output, $row['foundWords']);
	@$company[$row['fileCompany']] += $row['companyScore'] *  $cfound;
	addToLog('"' . $row['foundWords'] . '" ' . "$cfound found - " . $cfound*$row['companyScore'] . " points for company " . $row['fileCompany']);
	
	@$subject[$row['fileSubject']] += $row['subjectScore'] *  $cfound;
	addToLog('"' . $row['foundWords'] . '" ' . "$cfound found - " . $cfound*$row['subjectScore'] . " points for subject " . $row['fileSubject']);

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
	$newName = str_replace("ffirma",$companyName, $newName);
}

if ($subjectMatchRating > 20) {
	$newName = str_replace("bbetreff",$subjectName, $newName);
}


	echo "new name: " . $newName . "\n";
	
	//exec('mv "' . $pdf . '" "' . $newName . '"');	

$safe = SQLite3::escapeString($output);
$db->exec("INSERT INTO logs (oldFileName, newFileName, fileContent, log) VALUES ('$pdf', '$newName', '$safe', '$log');");		
   
?>