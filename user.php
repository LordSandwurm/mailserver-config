<?php
	include_once 'config.php';
	session_start();
	if (@$_SESSION["login"] == 0)
	{
		session_destroy();
		header ("Location: ".$_host_url);
		exit;
	}
	$info = $_SESSION["info"];
	if (empty($info)) $info=" ";
	include 'templates/user.tpl.html';
	exit;
?>