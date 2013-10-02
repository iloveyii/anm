<?php
	 // session_start();
	 require_once 'mainclass.php';
	 /**
	  * Interface for Registration
	  * @author Hazrat Ali <ali.sweden19@yahoo.com>
	  * Date: May 2013, VT13 BTH
	  */
	 interface IRegister {
	 	public function doRegistration($email, $password);
	 	public function doLogin($email, $password);
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
	class Registration extends mainClass implements IRegister {
	
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
			parent::__construct($dbHost, $dbName, $dbUser, $dbPass, $probedSince);
			$this->debug=$debug;
			$this->createTableIfNotExist();
		}
		
		/**
		 * Do registration
		 * @param string email 
		 * @param string password
		 * @return TRUE if success FALSE otherwise
		 */
		public function doRegistration($email, $password) {
			// check parameters
			if (empty($email) OR empty($password)) {
				echo "Invalid User Name or Password";
				return FALSE;
			}
			// check if user already exist
			if ($this->userExist($email)) {
				return FALSE;
			}
			// Add user
			return $this->addUsr($email, $password);
			
		}

		
		private function userExist($email) {
			$stmt = self::$dBh->prepare("SELECT count(*) from users where email =:email"); 
			$stmt->execute(array(':email'=>"$email"));
			$rows = $stmt->fetch(PDO::FETCH_NUM);
			if ($rows[0] > 0) { // email exist
				echo "Email already registered !";
				return TRUE;
			} else {
				return FALSE;
			}
		}
		
		private function addUsr($email, $password) {
			$stmt = self::$dBh->prepare("INSERT into users (email, password) VALUES (:email, :password)");
			$password = md5($password); 
			if ($stmt->execute(array(':email'=>"$email", ':password'=>"$password"))) {
				echo "User added successfully";
				return TRUE;
			}
			return FALSE;
		}
		private function checkAdmin($email) {
			$stmt = self::$dBh->prepare("SELECT count(*) from users where email =:email and admin='yes'"); 
			$stmt->execute(array(':email'=>"$email"));
			$rows = $stmt->fetch(PDO::FETCH_NUM);
			if ($rows[0] > 0) { // admin == yes
				$_SESSION['admin'] = 'admin';
				echo " Hi Admin !!!";
			}
		}
		public function doLogin($email, $password) {
			$email = trim($email);
			// by pass
			// $_SESSION['email'] = $email; $_SESSION['admin'] = 'admin'; return  TRUE;
			
			// first check if user passes authentication
			$sql="SELECT count(*) from users where email =:email and password=:password";
			if($this->login($sql, $email, $password)) {
				// now check if user is unblocked
				$sql="SELECT count(*) from users where email =:email and password=:password and blocked ='no'"; 
				if($this->login($sql, $email, $password)) {
					echo "Login Success !";
					$_SESSION['email'] = $email; // use is not blocked, so create session
					$this->checkAdmin($email);
					return TRUE;
				} else {
					echo "You are blocked by admin";
					return FALSE;
				}
			} else {
				echo "Invalid email or password !";
				return FALSE;
			}
		}
		private function login($sql,$email, $password) {
			unset($_SESSION['admin']); unset($_SESSION['email']);
			$stmt = self::$dBh->prepare($sql);
			$password = md5($password);  
			$stmt->execute(array(':email'=>"$email", ':password'=>"$password"));
			$rows = $stmt->fetch(PDO::FETCH_NUM);
			if ($rows[0] > 0) { // email and pass exist
				return TRUE;
			} else {
				return FALSE;
			}
		}
		
		private function createTableIfNotExist () {
			$table_sql = "
			  CREATE TABLE IF NOT EXISTS `users` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `email` varchar(45) DEFAULT NULL,
			  `password` varchar(32) DEFAULT NULL,
			  `admin` varchar(3) DEFAULT 'no',
			  `blocked` varchar(3) DEFAULT 'no',
			  PRIMARY KEY (`id`)
			  ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;
		    ";
			$stmt = self::$dBh->prepare($table_sql); 
			$stmt->execute();
		}
	} // end class Registration
	
	?>