 <?php
 	include_once 'config.php';
 	session_start();
 	$info = @$_SESSION["info"];
 	if (empty($info)) $info = "Bitte Logen sie sich ein!";
 	include 'templates/login-formular.tpl.html';
 	session_destroy();
 	exit;
?> 