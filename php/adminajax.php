<?php
	session_start();
	require_once 'adminclass.php';
	require_once '../conf.php';
	$setAdmin = new Admin($dbHost, $dbName, $dbUser, $dbPass);
	
	$id= $_POST['id'];
	$yesno = $admin= $_POST['admin'];
	$setAdmin->setAdmin($id, $yesno);
	
?>