(currently being developed by Till)

the backend of paperyard processes the documents. It has three modules

* scanner
* namer
* sorter

## backend scanner

The scanner kicks of OcrMyPDF on documents placed into the corresponding directory.

## namer

based on the rules specified the namer kicks off and tries to detect date, company name, subject etc.

## sorter

once you confirmed that the document has been detected correctly the sorter will put it into specified directories or trigger pre-defined actions (e.g. mail it so somebody)
