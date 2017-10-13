# backend scanner

The scanner kicks of OcrMyPDF on documents placed into the corresponding directory.

### flow description

the program roughly executes as follows.

1. database connection is established.
1. the base class is being instanciated to work provide output and other supporting functionalities
1. the program then goes into ```/data/scan``` (a local folder needs to be mapped via ```docker run``` command) and looks for ```*.pdf``` files

**foreach found pdf** the following flow is executed:

1. a lock is checked (```/tmp/ppyrdOcrMyPdf.txt```) and if not existing established to ensure that we dont have concurrent OcrMyPDF processes working on the same large file.
1. Tesseract is started. The output is written to ```/data/inbox```
1. We check if the output has been written.
   * if not the original (no ocr) is moved to ```/data/scan/error```
   * if found - the original is moved to ```/data/scan/archive```
1. if a lock exists - a simple output is generated stating that we wait ...
