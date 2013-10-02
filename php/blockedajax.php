<?php
	session_start();
	require_once 'adminclass.php';
	require_once '../conf.php';
	$setBlock = new Admin($dbHost, $dbName, $dbUser, $dbPass);
	
	$id= $_POST['id'];
	$yesno = $blocked= $_POST['blocked'];
	$setBlock->setBlocked($id, $yesno);
	
?>