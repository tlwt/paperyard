<html>
<body>
	<pre>
<?php
	require_once('dbHandler.php');
	require_once('helper.php');


	/**
	 * \brief takes PDFs and runs OCRmyPDF on them
	 */

	class pdfScanner
	{
		/**
		 * \brief constructor taking care of setup
		 */
		function __construct($pdf,$db)
		{
				$this->pdf = $pdf;
				$this->db = $db;
		}

		/**
		 * \brief function executing main logic
		 */
		function run()
		{
			// ensuring that we only have one OcrMyPDF running process running
			// due to cron usage and large PDFs this could be an issue
			$fp = fopen('/tmp/ppyrdOcrMyPdf.txt', 'w+');

			// checking if lock has been set properly
			if(flock($fp, LOCK_EX))
			{
					// running OCRmyPDF
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
				// this will be echoed in case OCRmyPDF is still running
				echo "OcrMyPdf still running - cannot interfere with it ... if this persists too long check /tmp/ppyrOcrMyPdf.txt and delete";
			}

			// closing lock again - so other instances can be started.
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

// checking if any new PDFs need to be OCRed
echo "calling OcrMyPDF ... \n";
chdir("/data/scan");

//loop all pdfs
$pdfs = glob("*.pdf");
foreach($pdfs as $pdf){
    $pdf=new pdfScanner($pdf, $db);
		$pdf->run();
}
echo "\n";


$db->close();

?>
	</pre>
</body>
</html>
