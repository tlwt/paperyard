# Welcome to paperyard's documentation!

the source code can be found at: https://github.com/tlwt/paperyard
The code documentation (doygen) can be found at: https://tlwt.github.io/paperyard/index.html

## Back-end

the backend of paperyard processes the documents. It has three modules

* scanner
* namer
* sorter

### scanner

The scanner kicks of OcrMyPDF on documents placed into the corresponding directory.

### namer

based on the rules specified the namer kicks off and tries to detect date, company name, subject etc.

### sorter


once you confirmed that the document has been detected correctly the sorter will put it into specified directories or trigger pre-defined actions (e.g. mail it so somebody)


## Front-end


needs some description as well


## Indices and tables


* :ref:`genindex`
* :ref:`modindex`
* :ref:`search`
