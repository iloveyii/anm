<?php

	require_once 'mainclass.php';
	/**
	 * Database operations
	 * 
	 * Reguires database credentials to connect to a DB
	 * Displays/returns MySQL table/sql in html table format
	 * 
	 * @author Hazrat Ali <ali.sweden19@yahoo.com>
	 */
	 // 
	class AddRemoveDevice extends mainClass {
	
		/** 
	     * Performs initializations upon class instantiation 
	     * 
	     * @param string $dbHost Database host name 
	     * @param string $dbName Database name 
	     * @param string $dbUser Database user name 
	     * @param string $dbPass Database password
	     * @return void 
	     */  
		public function __construct($dbHost, $dbName, $dbUser, $dbPass){
			parent::__construct($dbHost, $dbName, $dbUser, $dbPass, 0);
			// SQL 
			$this->sql = "
				SELECT * from DEVICES"; 
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
			if(! $this->isAuthenticated()) { return FALSE;}
			$strHtml = '';
			$stmt = self::$dBh->prepare($this->sql); 
			$stmt->execute();
			$strHtml = '<table style="border:2px solid gray; margin-top:0;">';
			$strHtml.= $this->showHeader();
			$counter = 0;
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$strHtml.= '<tr>';
				$id = $row['id'];
				$ip = $row['IP'];
				$port = $row['PORT'];
				$community = $row['COMMUNITY'];
				$interfaces = $row['INTERFACES'];
				
				$strHtml.= "<td>".$ip."</td>";
				$strHtml.= "<td>".$port."</td>";
				$strHtml.= "<td>".$community."</td>";
				$strHtml.= "<td>".$interfaces."</td>";
				$strHtml.= "<td><a href='#' class='delete' id='$id'><img src='images/delete.png'/></a></td>";

				$strHtml.= '</tr>';
				$counter++;
			}
			// if no record found
			if ($counter == 0) {
				$strHtml.= '<tr>';
					$strHtml.= "<td colspan=5> No Record found in Database</td>";
				$strHtml.= '</tr>';
			}
			$add_str='';
			// add fields for adding devices
			$add_str .= '</tr>';
			$add_str = '<td><input id="ip" type="text" value="" style="width:200px;" size="20" placeholder="IP" name="ip" id="user_email" class="form_inputs"></td>';
			$add_str .= '<td><input id="port" type="text" value="" style="width:60px;" size="10" placeholder="Port" name="ip" id="user_email" class="form_inputs"></td>';
			$add_str .= '<td><input id="community" type="text" value="" style="width:160px;" size="20" placeholder="Community" name="ip" id="user_email" class="form_inputs"></td>';
			$add_str .= '<td><input id="interfaces" type="text" value="" style="width:260px;" size="30" placeholder="Interfaces" name="ip" id="user_email" class="form_inputs"></td>';
			$add_str .= "<td><a href='#' class='add' id='add'><img src='images/add.png'/></a></td>";
			$add_str .= '</tr>';
			
			
			$strHtml.= $add_str;
			$strHtml.= '</table>';
			if (! $return) {
				echo $strHtml;
			} else {
				return $strHtml;
			}
		}
		public function removeDevice($id) {
			if(! $this->isAuthenticated()) { return FALSE;}
			$stmt = self::$dBh->prepare("DELETE FROM DEVICES where id= :id");
			if ($stmt->execute(array(':id'=>"$id"))) {
				echo "Device removed successfully";
				return TRUE;
			}
			return FALSE;
		}
		
		public function addDevice($ip, $port, $community, $interfaces) {
			if (empty($ip)) {echo "IP cannot be empty"; return TRUE;}
			if(! $this->isAuthenticated()) { return FALSE;}
			$stmt = self::$dBh->prepare("INSERT into DEVICES (id, IP, PORT, COMMUNITY, INTERFACES) 
				VALUES (NULL,:IP,:PORT,:COMMUNITY,:INTERFACES)
				ON DUPLICATE KEY UPDATE
				IP=:IP,
				PORT=:PORT,
				COMMUNITY=:COMMUNITY,
				INTERFACES=:INTERFACES
				");
			if ($stmt->execute(array(':IP'=>"$ip", ':PORT'=>"$port", ':COMMUNITY'=>"$community",':INTERFACES'=>"$interfaces"))) {
				echo "Device IP: $ip added successfully";
				return TRUE;
			}
			return FALSE;
		}

		/* show header of table */
		private function showHeader() {
			$header = '<thead>';
				$header .= '<tr>';
					$header .= '<th>' . 'IP' . '</th>';
					$header .= '<th>' . 'Port' . '</th>';
					$header .= '<th>' . 'Community' . '</th>';
					$header .= '<th>' . 'Interfaces' . '</th>';
					$header .= '<th>' . 'Action' . '</th>';
				$header .= '</tr>';
			$header .= '</thead>';
			return $header;
		}
	} // end class Database
	
	?>
  