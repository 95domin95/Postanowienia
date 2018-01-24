<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8" />
	<title>Postanowienia</title>
</head>
<body>
<?php
	session_start();
	
	if ((!isset($_POST['login'])) || (!isset($_POST['password'])))
	{
		header('Location: index.php');
		exit();
	}
	
	require_once "connect.php";
		
	$login = htmlentities($_POST['login'], ENT_QUOTES, "UTF-8");
	$password = $_POST['password'];
	
	try
	{
		mysqli_report(MYSQLI_REPORT_OFF);
		$connection = new mysqli($host, $db_user, $db_password, $db_name);
		if($connection->connect_errno!=0)
		{
			throw new Exception(mysqli_connect_errno());
		}
		
		if ($result = $connection->query(
		sprintf("SELECT * FROM users WHERE login='%s'",
		mysqli_real_escape_string($connection, $login))))
		{
			$users_number = $result->num_rows;
			if($users_number>0)
			{
				$record = $result->fetch_assoc();
				
				if(password_verify($password, $record['password']))
				{
					$_SESSION['logged'] = true;
				
					$_SESSION['ID'] = $record['ID'];
					$_SESSION['login'] = $record['login'];
					$_SESSION['password'] = $record['password'];
			
					$result->free_result();
					header('Location: account.php');
				}
				else 
				{
					echo '<span style="color:red">Nieprawidłowy login lub hasło!</span>';
					echo '</br><a href="index.php" title="Strona główna">[Strona główna]</a>';
					$connection->close();
					exit();		
				}

			} 
			else 
			{
				echo '<span style="color:red">Nieprawidłowy login lub hasło!</span>';
				echo '</br><a href="index.php" title="Strona główna">[Strona główna]</a>';
				$connection->close();
				exit();		
			}
			
		}
		$connection->close();
	}
	catch(Exception $ex)
	{
		echo '<span style="color:red">Błąd połączenia!</span>';
		echo '</br><a href="index.php" title="Strona główna">[Strona główna]</a>';
		exit();
	}
	
?>
</body>
</html>