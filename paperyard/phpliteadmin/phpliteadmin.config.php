<?php
//
// This is the paperyard configuration file
//

//password to gain access
$password = 'paperyard';

//directory relative to this file to search for databases (if false, manually list databases in the $databases variable)
$directory = '/data/database/';

//whether or not to scan the subdirectories of the above directory infinitely deep
$subdirectories = false;

//if the above $directory variable is set to false, you must specify the databases manually in an array as the next variable
//if any of the databases do not exist as they are referenced by their path, they will be created automatically
$databases = array(
	array(
		'path'=> 'paperyard.sqlite',
		'name'=> 'Paperyard'
	)
);


/* ---- Interface settings ---- */

// Theme! If you want to change theme, save the CSS file in same folder of phpliteadmin or in folder "themes"
$theme = 'phpliteadmin.css';

// the default language! If you want to change it, save the language file in same folder of phpliteadmin or in folder "languages"
// More about localizations (downloads, how to translate etc.): https://bitbucket.org/phpliteadmin/public/wiki/Localization
$language = 'en';

// set default number of rows. You need to relog after changing the number
$rowsNum = 30;

// reduce string characters by a number bigger than 10
$charsNum = 300;

// maximum number of SQL queries to save in the history
$maxSavedQueries = 10;

/* ---- Custom functions ---- */

//a list of custom functions that can be applied to columns in the databases
//make sure to define every function below if it is not a core PHP function
$custom_functions = array(
	'md5', 'sha1', 'time', 'strtotime',
	// add the names of your custom functions to this array
	/* 'leet_text', */
);


/* ---- Advanced options ---- */

//changing the following variable allows multiple phpLiteAdmin installs to work under the same domain.
$cookie_name = 'ppyrd1234';

//whether or not to put the app in debug mode where errors are outputted
$debug = false;

// the user is allowed to create databases with only these extensions
$allowed_extensions = array('db','db3','sqlite','sqlite3');
