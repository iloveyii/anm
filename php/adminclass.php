<?php
	 // session_start();
	 require_once 'mainclass.php';
	 /**
	  * Interface for Admin
	  * @author Hazrat Ali <ali.sweden19@yahoo.com>
	  * Date: May 2013, VT13 BTH
	  */
	 interface IAdmin {
	 	public function showTable($return = FALSE);
	 	public function setAdmin($id, $yesno);
	 	public function setBlocked($id, $yesno);
	 }
	 
	/**
	 * Database operations
	 * 
	 * Reguires database credentials from conf.php to connect to a DB
	 * Displays/returns MySQL table/sql in html table format
	 * 
	 * @author Hazrat Ali <ali.sweden19@yahoo.com>
	 */
	 // 
	class Admin extends mainClass implements IAdmin {
	
		/** 
	     * Performs initializations upon class instantiation 
	     * 
	     * @param string $dbHost Database host name 
	     * @param string $dbName Database name 
	     * @param string $dbUser Database user name 
	     * @param string $dbPass Database password
	     * @param string $debug set to true if you want to display messages, better for debug
	     * @return void 
	     */  
		public function __construct($dbHost, $dbName, $dbUser, $dbPass, $debug=FALSE){
			parent::__construct($dbHost, $dbName, $dbUser, $dbPass, 0);
 			// set sql specific to this class
			$this->sql = "
				SELECT id, email, admin, blocked
				FROM users "; 
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
			if(! $this->isAdmin()) { return FALSE;}
			$strHtml = '';
			$stmt = self::$dBh->prepare($this->sql); 
			$stmt->execute();
			$strHtml = '<table  width="100%" style="border:none; margin:0;">';
			$strHtml.= $this->showHeader();
			$counter = 0;
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$strHtml.= '<tr>';
				$id = $row['id'];
				$email = $row['email'];
				$admin = $row['admin'];
				$blocked = $row['blocked'];
				
				$strHtml.= "<td>".$row['email']."</td>";
				$strHtml.= "<td><input type='checkbox' class='admin' id='$id'";
					$strHtml.= $admin == 'yes' ? 'checked':'unchecked';
					// if ($admin == 'yes') { $strHtml.='checked="yes"';} 
					$strHtml.= '/></td>';
				$strHtml.= "<td><input type='checkbox' class='blockeduser' id='$id'";
					$strHtml.= $blocked == 'yes' ? 'checked':'unchecked';
					// if ($blocked == 'yes') { $strHtml.='checked="yes"';} else {$strHtml.='checked="no"';}
					$strHtml.= '/></td>';

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

		
		public function setAdmin($id, $yesno) {
			$sql = "UPDATE users SET admin =:yesno where id=:id";
			$this->doAdminStuff($sql, $id, $yesno);
		}
		public function setBlocked($id, $yesno) {
			$sql = "UPDATE users SET blocked =:yesno where id=:id";
			$this->doAdminStuff($sql, $id, $yesno);
		}
		private function doAdminStuff($sql, $id, $yesno ) {
			if(! $this->isAdmin()) { echo "You are not admin"; return FALSE;}
			$stmt = self::$dBh->prepare($sql); 
			if($stmt->execute(array(':yesno'=>"$yesno", ':id'=>"$id"))) {
				// echo "ID =$id set to $yesno successfully";
				echo "Success !!!";
			} else {
				echo "Oops something went wrong";
			}
		}
	
		
		/* show header of table */
		private function showHeader() {
			$header = '<thead>';
				$header .= '<tr>';
					$header .= '<th>' . 'Email' . '</th>';
					$header .= '<th>' . 'Admin' . '</th>';
					$header .= '<th>' . 'Blocked' . '</th>';
				$header .= '</tr>';
			$header .= '</thead>';
			return $header;
		}
	} // end class Database
	
	?>