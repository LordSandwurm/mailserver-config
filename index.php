<?php
	include_once 'config.php';
	include_once 'encode-dovecot.php';

	session_cache_expire(30);
	SESSION_START();
	
	# Setze Session auf null
	if (!empty($_POST["logout"]))
	{
		$_SESSION["login"] = 0;
		session_destroy();
		header ("Location: ".$_host_url);
	    exit;
	}
	
	
	# Datenbankverbindung herstellen
	$link = mysqli_connect($_db_host, $_db_username, $_db_passwort,$_db_datenbank,3306);
	
	# Hat die Verbindung geklappt ?
	if (mysqli_connect_errno()) 
	{
  		die("Failed to connect to MySQL: " . mysqli_connect_error());
  		#session_destroy();
  		exit;
	}
	
	
	##################################################################
	# Die Datenbank ist ge�ffnet und die richtige Datenbank ausgew�hlt
	# Hier m�ssen wir nun pr�fen, ob der Benutzer eingeloggt ist
	# und ob das Formular angezeigt werden muss
	##################################################################

	# Ist die $_POST Variable submit nicht leer ???
	# dann wurden Logindaten eingegeben, die m�ssen wir �berpr�fen !
	if (!empty($_POST["submit"]))
	{
		
		# Die Werte die im Loginformular eingegeben wurden "escapen",
		# damit keine Hackangriffe �ber den Login erfolgen k�nnen !
		# Mysql_real_escape ist auf jedenfall dem Befehle addslashes()
		# vorzuziehen !!! Ohne sind mysql injections m�glich !!!!
		$_email = @mysqli_real_escape_string($link,$_POST["email"]);
		$_password = @mysqli_real_escape_string($link,$_POST["password"]);
	 
		# Befehl f�r die MySQL Datenbank
		$_sql = "SELECT password FROM virtual_users WHERE email='$_email'";
		 
		$_res = mysqli_query($link, $_sql);
		 
		$line = mysqli_fetch_array($_res, MYSQLI_ASSOC);
		$_dbpassword = $line['password'];
		$hash = encodeDovecot::encode_with_existing_salt($_hash_mode, $_password, $_dbpassword);
		# DEBUG INFO
		#$_SESSION["info"] = $_password."<br>".$_dbpassword."<br>".$hash;
		$_SESSION["info"] = "Login Fehlgeschlagen";
		@mysqli_free_result($_res);
		 
		# Befehl f�r die MySQL Datenbank
		$_sql = "SELECT * FROM virtual_users WHERE
		email='$_email' AND
		password='$hash'
		LIMIT 1";
		
		# Pr�fen, ob der User in der Datenbank existiert !
		$_res = mysqli_query($link, $_sql);
		$_anzahl = @mysqli_num_rows($_res);
	 

		# Die Anzahl der gefundenen Eintr�ge �berpr�fen. Maximal
		# wird 1 Eintrag rausgefiltert (LIMIT 1). Wenn 0 Eintr�ge
		# gefunden wurden, dann gibt es keinen Usereintrag, der
		# g�ltig ist. Keinen wo der Username und das Passwort stimmt
		# und user_geloescht auch gleich 0 ist !
		if ($_anzahl > 0)
		{
			$_SESSION["info"] = "Der Login war erfolgreich.<br>";
		
			# In der Session merken, dass der User eingeloggt ist !
			$_SESSION["login"] = 1;
			
			# Den Eintrag vom User in der Session speichern !
			$username = mysqli_fetch_array($_res, MYSQLI_ASSOC);
			$_SESSION["user"] = $username["email"];
	
			# Das Einlogdatum in der Tabelle setzen !
			#$_sql = "UPDATE login_usernamen SET letzter_login=NOW()
			#WHERE id=".$_SESSION["user"]["id"];
			#mysql_query($_sql);
		}
		
		@mysqli_free_result($_res);
	}

	# Hier wird das ge�nderte Passwort gepr�ft und in die Datenbank gespeichert.
	if (!empty($_POST["changepassword"]))
	{
		$_password1 = @mysqli_real_escape_string($link,$_POST["newpassword1"]);
		$_password2 = @mysqli_real_escape_string($link,$_POST["newpassword2"]);
		
		if (empty($_password1)) 
		{
			$_SESSION["info"] = "Leeres Password nicht erlaubt! <br>";
		}
		else
		{
			if ($_password1 === $_password2)
			{
				
				$user = $_SESSION["user"];
				$hash = encodeDovecot::encode_with_new_salt($_hash_mode, $_password1);
				$_sql = "UPDATE virtual_users SET password='$hash' WHERE email='$user'";
				if (!mysqli_query($link,$_sql))
				{
					die('Error: ' . mysqli_error($link));
					session_destroy();
					exit;
				}
				
				$_SESSION["info"] = "Passwort wurde ge&auml;ndert <br>";
			}
			else
			{
				$_SESSION["info"] = "Passwörter sind nicht gleich! <br>";
			}
		}
 
	}
	
	# passwort eines existierenden users �ndern.
	if (!empty($_POST["changeolduser"]))
	{
		$_password1 = @mysqli_real_escape_string($link,$_POST["changepassword1"]);
		$_password2 = @mysqli_real_escape_string($link,$_POST["changepassword2"]);
		
		if (empty($_password1)) 
		{
			$_SESSION["info"] = "Leeres Password nicht erlaubt! <br>";
		}
		else
		{
			if ($_password1 === $_password2)
			{
			
				$user = @mysqli_real_escape_string($link,$_POST["changemail"]);

				$_sql = "SELECT * FROM virtual_users WHERE email='$user' LIMIT 1";

				//Pr�fen, ob der User in der Datenbank existiert !
				$_res = mysqli_query($link, $_sql);
				$_anzahl = @mysqli_num_rows($_res);


				if ($_anzahl > 0)
				{
					$hash = encodeDovecot::encode_with_new_salt($_hash_mode, $_password1);
					$_sql = "UPDATE virtual_users SET password='$hash' WHERE email='$user'";
					if (!mysqli_query($link,$_sql))
					{
						die('Error: ' . mysqli_error($link));
						session_destroy();
						exit;
					}
			
					$_SESSION["info"] = "Passwort wurde geändert <br>";
				}
				else
				{
					$_SESSION["info"] = "Emailadresse existiert nicht! <br>";
				}
				
			}
			else
			{
				$_SESSION["info"] = "Passwörter sind nicht gleich! <br>";
			}
		}
		
 
	}

	if (!empty($_POST["newuser"]))
	{
		$_newpassword = @mysqli_real_escape_string($link,$_POST["newuserpassword"]);
		$_newpassword2 = @mysqli_real_escape_string($link,$_POST["newuserpassword2"]);
		$_newemail = @mysqli_real_escape_string($link,$_POST["newemail"]);
		
		if (empty($_newpassword))
		{
			$_SESSION["info"] = "Leeres Password nicht erlaubt! <br>";
			header ("Location: ".$_host_url);
			exit;
		}
		if ($_newpassword === $_newpassword2)
		{
			
		}
		$_domain = strstr($_newemail, "@");
		$_domain = substr($_domain, 1);
		
		$_sql = "SELECT id FROM virtual_domains WHERE name='$_domain'";			
		$_res = mysqli_query($link, $_sql);			
		$line = mysqli_fetch_array($_res,MYSQLI_ASSOC);		
		$domain_id = $line['id'];
		
		if (empty($domain_id))
		{
			$_SESSION["info"] = "Keine Gültige Domaine! <br>";
			header ("Location: ".$_host_url);
			exit;
		}
		
		# Befehl f�r die MySQL Datenbank
		$_sql = "SELECT * FROM virtual_users WHERE
		email='$_newemail'
		LIMIT 1";
		
		# Pr�fen, ob der User in der Datenbank existiert !
		$_res = mysqli_query($link, $_sql);
		$_anzahl = @mysqli_num_rows($_res);
		
		if ($_anzahl > 0)
		{
			$_SESSION["info"] = "Email existiert bereits! <br>";
			header ("Location: ".$_host_url);
			exit;
		}
		
		$hash = encodeDovecot::encode_with_new_salt($_hash_mode, $_newpassword);
		
		$_sql = "INSERT INTO virtual_users (password, email, domain_id) values ('$hash','$_newemail','$domain_id')";
		if (!mysqli_query($link,$_sql))
		{
  			die('Error: ' . mysqli_error($link));
  			session_destroy();
  			exit;
		}

		$_sql = "INSERT INTO virtual_aliases (domain_id, source, destination) values ('$domain_id','$_newemail','$_newemail')";
		if (!mysqli_query($link,$_sql))
		{
  			die('Error: ' . mysqli_error($link));
  			session_destroy();
  			exit;
		}
		
		@mysqli_free_result($_res);
		#$_SESSION['info'] = $hash."<br>".$_domain."<br>".$domain_id;
		$_SESSION['info'] = "Neue Emailadresse ".$_newemail." erstellt <br>";
		
	}
	
	# Neue Domäne anlegen
	if (!empty($_POST["newdomain"]))
	{
		$_newdomain = @mysqli_real_escape_string($link,$_POST["domain"]);
		
		if (empty($_newdomain))
		{
			$_SESSION["info"] = "Leeres Eingabefeld! <br>";
			header ("Location: ".$_host_url);
			exit;
		}
		
		$_sql = "SELECT * FROM virtual_domains WHERE name='$_newdomain'";			
		$_res = mysqli_query($link, $_sql);			
		$_anzahl = @mysqli_num_rows($_res);
		
		if ($_anzahl > 0)
		{
			$_SESSION["info"] = "Domäne existiert bereits! <br>";
			header ("Location: ".$_host_url);
			exit;
		}
		
		$_sql = "INSERT INTO virtual_domains (name) values ('$_newdomain')";
		if (!mysqli_query($link,$_sql))
		{
  			die('Error: ' . mysqli_error($link));
  			session_destroy();
  			exit;
		}
		
		@mysqli_free_result($_res);
		#$_SESSION['info'] = $hash."<br>".$_domain."<br>".$domain_id;
		$_SESSION['info'] = "Neue Domäne ".$_newdomain." erstellt <br>";
		
	}
	
	# Ist der User eingeloggt ???
	if (@$_SESSION["login"] == 0)
	{
	
	# ist nicht eingeloggt, also Formular anzeigen, die Datenbank
	# schliessen und das Programm beenden
	header ("Location: ".$_host_url."/login.php");
		@mysqli_close($link);
			exit;
	}
	
	# Hier w�re der User jetzt g�ltig angemeldet ! Hier kann
	# Programmcode stehen, den nur eingeloggte User sehen sollen !!
	if ($_admin_account === $_SESSION["user"])
	{
		header ("Location: ".$_host_url."/admin.php");
	}
	else
	{
		header ("Location: ".$_host_url."/user.php");
	}

	 
	# Datenbank wieder schliessen
	@mysqli_close($link);
	exit;
?>