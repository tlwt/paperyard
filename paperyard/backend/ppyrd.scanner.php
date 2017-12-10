<?php
  require_once('dbHandler.php');
  require_once('ppyrd.base.php');


  /**
   * \brief takes PDFs and runs OCRmyPDF on them
   */

  class pdfScanner extends ppyrd
  {
    /**
     * \brief constructor taking care of setup
     */
    function __construct($pdf,$db)
    {
        $this->pdf = $pdf;
        $this->db = $db;
        $this->tesseractCommand = $this->db->getConfigValue('tesseractCommand');
    }

    /**
     * \brief function executing main logic
     */
    function run()
    {
      $this->output("executing on " . $this->pdf);
      // ensuring that we only have one OcrMyPDF running process running
      // due to cron usage and large PDFs this could be an issue
      $fp = fopen('/tmp/ppyrdOcrMyPdf.txt', 'w+');

      // checking if lock has been set properly
      if(flock($fp, LOCK_EX))
      {
          // running OCRmyPDF
          exec($this->tesseractCommand . " '" . $this->pdf . "' '/data/inbox/" . $this->pdf . "'");
          if (file_exists("/data/inbox/" . $this->pdf))
          {
              // fixing user permissions since OCR is run as www-data
              $this->output("fixing permissions");
              exec ("user_id=$(stat -c '%u:%g' " . $this->pdf . '); chown $user_id /data/inbox/' . $this->pdf);
              $this->output("found ok OCR - moving input to archive");
              exec("mv --backup=numbered '" . $this->pdf . "' '/data/scan/archive/" . $this->pdf . "'");
          } else
          {
            $this->output("did not find ok OCR - moving input to error");
            exec("mv --backup=numbered '" . $this->pdf . "' '/data/scan/error/" . $this->pdf . "'");
          }

      } else
      {
        // this will be echoed in case OCRmyPDF is still running
        $this->output("OcrMyPdf still running - cannot interfere with it ... if this persists too long check /tmp/ppyrOcrMyPdf.txt and delete");
      }

      // closing lock again - so other instances can be started.
      fclose($fp);

    }
  }

// main program

/**
 * creating db handler to talk to DB
 */
$db=new dbHandler();
$ppyrd = new ppyrd($db);


// looping main directory and calling the pdf parser

/**
 * checking if called via command line or webserver
 */
 $ppyrd->checkCliVsWebserver();


// checking if any new PDFs need to be OCRed
chdir("/data/scan");

//loop all pdfs
$pdfs = glob("*.pdf");
foreach($pdfs as $pdf){
    $pdf=new pdfScanner($pdf, $db);
    $pdf->run();
}

$db->close();

?>
