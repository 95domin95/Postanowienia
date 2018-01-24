<?php
	session_start();
	if ((isset($_SESSION['login'])) && (isset($_SESSION['password'])))
	{
		header('Location: account.php');
		exit();
	}
	if(isset($_POST['email']))
	{	
		$validator = true;
		$login = $_POST['login'];
		
		if((strlen($login)<3)||(strlen($login)>20))
		{
			$validator=false;
			$_SESSION['loginError']="Login musi posiadać od 3 do 20 znaków!";
		}
		
		if(ctype_alnum($login)==false)
		{
			$validator=false;
			$_SESSION['loginError']="Login nie może zawierać poskich znaków!";
		}
		
		$email = $_POST['email'];
		$emailb = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
		
		if((filter_var($email, FILTER_VALIDATE_EMAIL)==false)||($emailb!=$email))
		{
			$validator = false;
			$_SESSION['emailError']="Podaj poprawny adres e-mail";
		}
		
		$password = $_POST['password'];
		$passwordReplay = $_POST['passwordReplay'];
		
		if((strlen($password)<8)||(strlen($password)>20))
		{
			$validator = false;
			$_SESSION['passwordError']="Hasło musi zawierać od 8 do 20 znaków!";
		}
		if($password!=$passwordReplay)
		{
			$validator = false;
			$_SESSION['password2Error']="Podane hasła nie są identyczne!";
		}
		
		$hash_password = password_hash($password, PASSWORD_DEFAULT);
		
		if (!isset($_POST['regulamin']))
		{
			$validator=false;
			$_SESSION['regulaminError']="Potwierdź akceptację regulaminu!";
		}
		
		require_once "connect.php";
		mysqli_report(MYSQLI_REPORT_STRICT);
		
		try
		{
			$connection = new mysqli($host, $db_user, $db_password, $db_name);
			if($connection->connect_errno!=0)
			{
				throw new Exception(mysqli_connect_errno());
			}
			else
			{
				$result = $connection->query("SELECT ID FROM users WHERE email='$email'");
				if(!$result) throw new Exception($connection->error);
				
				$number_of_same_emails = $result->num_rows;
				if($number_of_same_emails>0)
				{
					$validator = false;
					$_SESSION['emailError']="Taki e-mail już istnieje!";
				}
				
				$result = $connection->query("SELECT ID FROM users WHERE login='$login'");
				if(!$result) throw new Exception($connection->error);
				
				$number_of_same_logins = $result->num_rows;
				if($number_of_same_logins>0)
				{
					$validator = false;
					$_SESSION['loginError']="Taki login już istnieje!";
				}
				
				if($validator)
				{
					$table_name = $login."_user_decisions";
					$query = "CREATE TABLE `postanowienia`.`$table_name` ( `ID` INT NOT NULL AUTO_INCREMENT , `Date` 
					TEXT CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL , `Special` TEXT CHARACTER SET utf8 COLLATE 
					utf8_polish_ci NOT NULL , `Decision` TEXT CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL , 
					PRIMARY KEY (`ID`)) ENGINE = InnoDB CHARSET=utf8 COLLATE utf8_polish_ci";
					
					if(!$connection->query($query)) throw new Exception($connection->error);
					if($connection->query("INSERT INTO users VALUES (NULL, '$login', '$hash_password', '$email')"))
					{
						$_SESSION['registration_success'];
						header('Location: welcome.php');
					}
					else
					{
						throw new Exception($connection->error);
					}
				}
			}
			$connection->close();
		}
		catch(Exception $ex)
		{
			echo '<span style="color:red;">Błąd serwera!';
		}
		
	}
	
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8" />
	<title>Postanowienia</title>
	<style>
		.error
		{
			color:red;
			margin-top: 10px;
			margin-bottom: 10px;
		}
	</style>
</head>
<body>
	Wypełnij poniższe pola by dokonać rejestracji:</br></br>
	<form action="registration.php" method="post">
		Login:</br>
		<input type="text" name="login"/></br>
		<?php
			if(isset($_SESSION['loginError']))
			{
				echo '<div class="error">'.$_SESSION['loginError'].'</div>';
				unset($_SESSION['loginError']);
			}
		?>
		Adres e-mail:</br>
		<input type="text" name="email"/></br>
		<?php
			if(isset($_SESSION['emailError']))
			{
				echo '<div class="error">'.$_SESSION['emailError'].'</div>';
				unset($_SESSION['emailError']);
			}
		?>
		Hasło:</br>
		<input type="password" name="password"/></br>
		<?php
			if(isset($_SESSION['passwordError']))
			{
				echo '<div class="error">'.$_SESSION['passwordError'].'</div>';
				unset($_SESSION['passwordError']);
			}
		?>
		Powtórz hasło:</br>
		<input type="password" name="passwordReplay"/></br>
		<?php
			if(isset($_SESSION['password2Error']))
			{
				echo '<div class="error">'.$_SESSION['password2Error'].'</div>';
				unset($_SESSION['password2Error']);
			}
		?>
		<label>
			<input type="checkbox" name="regulamin"/> Akceptuję <a href="regulamin.php" title="Strona główna">regulamin</a>
		</label>
		
		<?php
			if (isset($_SESSION['regulaminError']))
			{
				echo '<div class="error">'.$_SESSION['regulaminError'].'</div>';
				unset($_SESSION['regulaminError']);
			}
		?>
		</br><input type="submit" value="Rejestracja"/></br>
	</form>
	</br><a href="index.php" title="Strona główna">[Strona główna]</a>
</body>
</html>