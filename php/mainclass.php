<?php

	 /**
	  * Interface for Database
	  * 
	  * The showTable displays a DB table in html table format
	  * 
	  * The setSQL is used to set sql query to any valid 
	  * user provided sql and then it will be displayed by showTable method
	  * 
	  * The setProbedSince sets the time in seconds for the devices  
	  * 
	  * @author Hazrat Ali <ali.sweden19@yahoo.com>
	  * Date: May 2013, VT13 BTH
	  */
	 interface IMainClass {
	 }
	 
	/**
	 * Database operations
	 * 
	 * Reguires database credentials to connect to a DB
	 * Displays/returns MySQL table/sql in html table format
	 * 
	 * @author Hazrat Ali <ali.sweden19@yahoo.com>
	 */
	 // 
	abstract class mainClass implements IMainClass {
		protected static $dBh;
		protected $debug;
		protected $sql;
		protected $probedSince;
		private $dbHost; private $dbName; private $dbUser; private $dbPass;
	
		/** 
	     * Performs initializations upon class instantiation 
	     * 
	     * @param string $dbHost Database host name 
	     * @param string $dbName Database name 
	     * @param string $dbUser Database user name 
	     * @param string $dbPass Database password
	     * @param string $probedSince is the probing time in Seconds
	     * @param string $debug set to true if you want to display messages, better for debug
	     * @return void 
	     */  
		public function __construct($dbHost, $dbName, $dbUser, $dbPass, $probedSince, $debug=FALSE){
			$this->dbHost = $dbHost; $this->dbName = $dbName; $this->dbUser = $dbUser; $this->dbPass = $dbPass;
			self::$dBh =$this->connectDB();
			$this->debug=$debug;
			$this->probedSince = $probedSince;
		}
		
		/* Connects to database */
		private function connectDB(){
			return new PDO(
				 "mysql:host=".$this->dbHost.";dbname=".$this->dbName.";charset=utf8",$this->dbUser,$this->dbPass
			);
		}
		
		protected function isAdmin () {
			if (isset($_SESSION['admin'])) {
				return TRUE;
			}
			echo "You are not admin !!!";
			return FALSE;
		}
		
		protected function isAuthenticated () {
			if (isset($_SESSION['email'])) {
				return TRUE;
			}
			echo "You are not logged In/authenticated !!!";
			return FALSE;
		}
	}
