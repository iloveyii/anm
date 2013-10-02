<?php
	require_once 'mainclass.php';
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
	 interface IProbedDevice {
	 	public function showTable();
	 	public function setSQL($sql);
	 	public function setProbedSince($seconds);
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
	class ProbedDevice extends mainClass implements IProbedDevice {
	
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
			parent::__construct($dbHost, $dbName, $dbUser, $dbPass, $probedSince);
			// SQL 
			$this->sql = "
				SELECT ip, interface, inRate, inRateAverage, outRate, outRateAverage
				FROM TRAFFIC
				INNER JOIN DEVICES 
				ON 
				DEVICES.id = TRAFFIC.deviceID
			 	WHERE lastUpdated > (NOW() - $this->probedSince)
			 	order by DEVICES.id, TRAFFIC.interface
			 	"; 
		}

		
		/**
		 * Show DB table in html table format
		 * 
		 * Faciliatated displaying of any table in html format
		 * 
		 * @param boolean $return if true returns the html instead of displaying it
		 * @return string html of the table
		 * @see IDatabase::showTable()
		 */
		public function showTable($return = FALSE) {
			$strHtml = '';
			$stmt = self::$dBh->prepare($this->sql); 
			$stmt->execute();
			$strHtml = '<table style="border:2px solid gray;margin-top:0;">';
			$strHtml.= $this->showHeader();
			$counter = 0;
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$strHtml.= '<tr>';
				foreach ($row as $column_name =>$column_val) {
					$strHtml.= "<td>".$column_val."</td>";
				}
				$strHtml.= '</tr>';
				$counter++;
			}
			// if no record found
			if ($counter == 0) {
				$strHtml.= '<tr>';
					$strHtml.= "<td colspan=6> No Record found in Database</td>";
				$strHtml.= '</tr>';
			}
			$strHtml.= '</table>';
			if (! $return) {
				echo $strHtml;
			} else {
				return $strHtml;
			}
		}
		
		/**
		 * Sets SQL
		 * 
		 * Facilitates displaying of any sql
		 * 
		 * @param $sql a valid mysql query against the given database
		 * @return void
		 * @see IDatabase::setSQL()
		 */
		public function setSQL($sql) {
			$this->sql = $sql;
		}
		
		/**
		 * Sets ProbedSince time of the devices
		 * 
		 * @param $seconds time in seconds for the devices 
		 * @return void
		 * @see IDatabase::setProbedSince()
		 */
		public function setProbedSince($seconds) {
			$this->probedSince = $seconds;
			$this->sql = "
				SELECT ip, interface, inRate, inRateAverage, outRate, outRateAverage
				FROM TRAFFIC
				INNER JOIN DEVICES 
				ON 
				DEVICES.id = TRAFFIC.deviceID
			 	WHERE lastUpdated > (NOW() - $this->probedSince)
			 	order by DEVICES.id, TRAFFIC.interface
			 	"; 
		}
		/* show header of table */
		private function showHeader() {
			$header = '<thead>';
				$header .= '<tr>';
					$header .= '<th>' . 'IP' . '</th>';
					$header .= '<th>' . 'Interface' . '</th>';
					$header .= '<th>' . 'InRate' . '</th>';
					$header .= '<th>' . 'InRateAverage' . '</th>';
					$header .= '<th>' . 'OutRate' . '</th>';
					$header .= '<th>' . 'OutRateAverage' . '</th>';
				$header .= '</tr>';
			$header .= '</thead>';
			return $header;
		}
	} // end class Database
	
	?>
  