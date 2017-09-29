<?php
	/**
		* @file
		* \author Till Witt
		* \brief helper for ppyrd
		* \bug certainly still a lot
		*
	 	*/
	class helper {
		/*!
			* \class helper
			* \brief containers helper functions
			* \author Till Witt
			* \bug no error handling implemented yet
			*
		 	* \details database handler
		 	*/
		var $db;

		/**
		 * \brief constructor
		 */
		public function __construct() {
		} // End constructor



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

	}

?>
