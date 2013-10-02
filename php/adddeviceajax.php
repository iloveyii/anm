<?php
	session_start();
	ob_start();
	require_once 'addremoveclass.php';
	require_once '../conf.php';
	$addDevice = new AddRemoveDevice($dbHost, $dbName, $dbUser, $dbPass);
	
	$ip= $_POST['ip'];
	$port= $_POST['port'];
	$community= $_POST['community'];
	$interfaces= $_POST['interfaces'];
	if($addDevice->addDevice($ip, $port, $community, $interfaces)) {
		echo $addDevice->showTable(TRUE);
	}
	ob_flush();
	
?>