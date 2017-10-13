.. paperyard documentation master file, created by
   sphinx-quickstart on Fri Oct 13 09:13:38 2017.
   You can adapt this file completely to your liking, but it should at least
   contain the root `toctree` directive.

Welcome to paperyard's documentation!
=====================================

.. toctree::
   :maxdepth: 2
   :caption: Contents:

Back-end
========
the backend of paperyard processes the documents. It has three modules

* scanner
* namer
* sorter

scanner
^^^^^^^
The scanner kicks of OcrMyPDF on documents placed into the corresponding directory.

namer
^^^^^
based on the rules specified the namer kicks off and tries to detect date, company name, subject etc.

sorter
^^^^^^
once you confirmed that the document has been detected correctly the sorter will put it into specified directories or trigger pre-defined actions (e.g. mail it so somebody)


front-end
=========

needs some description as well


Indices and tables
==================

* :ref:`genindex`
* :ref:`modindex`
* :ref:`search`
