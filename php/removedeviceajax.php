<?php
	session_start();
	ob_start();
	require_once 'addremoveclass.php';
	require_once '../conf.php';
	$removeDevice = new AddRemoveDevice($dbHost, $dbName, $dbUser, $dbPass);
	
	$id= $_POST['id'];
	if($removeDevice->removeDevice($id)) {
		echo $removeDevice->showTable(TRUE);
	}
	ob_flush();
?>