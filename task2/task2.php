<?php
$dbHost='localhost'; $dbName='haae09'; $dbUser='haae09'; $dbPass='8005035335'; $tableName='TRAFFIC'; $debug=FALSE;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>ANM P1 Assigment 02</title>
<style type="text/css">
	table {	border-collapse:collapse;margin-bottom:20px;font: 14px/22px "Arial",Arial,Helvetica,sans-serif;	border: medium solid #039;min-width:800px;margin-top:10px; margin-left:auto; margin-right:auto;}
	table thead {border-bottom: thin solid #FFCC33;	border-top: thin solid #FFCC33;	font-weight: bold;	line-height: 32px;}
	table th { border:none; background:#9999FF; color:#fff;	padding:3px 10px; text-align: center; margin-bottom: 15px;}
	table  td {border:1px solid #FCD09E;padding:3px 10px;color: #006699;vertical-align: top;text-align: center;}
	table tbody tr:nth-child(odd) {	background: none repeat scroll 0 0 #ECF4FA;}
	table tbody tr:nth-child(even) {background: none repeat scroll 0 0 #F7F7F7;}
	body, *{font-family: 14px/22px "Arial",Arial,Helvetica,sans-serif;font-size: 14px;line-height: 1.2;margin:0px;}
	html,body {margin:0;padding:0;height:100%;}
	.wrapper {min-height:100%;	position:relative;background-color: #F4EDFE; margin-left: auto; margin-right: auto; width: 1000px;}
	div.content {width: 80%;min-width:800px;margin-right: auto;	margin-left: auto;	padding-bottom:135px; padding-top:50px;}
	.header {background-color:#036;background-repeat: repeat-x;background-position: center top;background-repeat: repeat-x;height: 141px;left: 0;top: 0;width: 100%;z-index: 200;}
	.wrapper .header span{font-size: 24px;color: #FFF; display: block;padding: 50px 0 0 30px;}
	.footer {background-color:#3EB0D2; background-repeat: repeat-x;width:100%; height: 125px;bottom:0px;left:0;	position:absolute;}
	.footer span{font-size: 14px;color: #FFF; display: block; padding: 50px 0 0 30px;}
</style>
</head>

<body>
<div class="wrapper">
<div class="header"><span> ANM : P1 Assignement 02 </br><label> Course Responsible: Patrik Arlos</label></span></div>
  <div class="content">
  	<?php
  	/* ############## THIS IS CLASS #################### */ 
	 error_reporting(E_ALL);
	 ini_set("display_errors", 1); 
	 define('DEBUG', FALSE);

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
	 interface IDatabase {
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
	class Database implements IDatabase {
		private static $dBh;
		private $debug;
		private $sql;
		private $probedSince;
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
			// SQL withoud where
			$this->sql = "
				SELECT ip, interface, inRate, inRateAverage, outRate, outRateAverage
				FROM TRAFFIC
				INNER JOIN DEVICES 
				ON 
				DEVICES.id = TRAFFIC.deviceID
			 	WHERE lastUpdated > (NOW() - $this->probedSince)"; 
		}
		
		/* Connects to database */
		private function connectDB(){
			return new PDO(
				 "mysql:host=".$this->dbHost.";dbname=".$this->dbName.";charset=utf8",$this->dbUser,$this->dbPass
			);
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
			$strHtml = '<table style="border:2px solid gray;">';
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
  
	<?php 
		/* ############## THIS IS TEST DRIVE OF CLASS  #################### */ 
		$task2 = new Database($dbHost, $dbName, $dbUser, $dbPass, 300);
		$task2->showTable();
		
	?>
  </div>
  <div class="footer"><span> By : Hazrat Ali (haae09)</br> BTH VT13</span></div>
</div>

</body>
</html>
