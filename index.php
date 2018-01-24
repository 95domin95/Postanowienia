<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8" />
	<title>Postanowienia</title>
</head>
<body>
<?php
	session_start();
	if ((isset($_SESSION['login'])) && (isset($_SESSION['password'])))
	{
		header('Location: account.php');
		exit();
	}
?>
	<b>Witamy w programie "Postanowienia (ALPHA)"!</b></br></br>
	<form action="login.php" method="post">
		Login:</br>
		<input type="text" name="login"/></br>
		Hasło:</br>
		<input type="password" name="password"/></br>
		<input type="submit" value="Zaloguj się"/></br>
	</form>
	</br>Nie masz jeszcze konta? <a href="registration.php" title="Rejestracja">[Rejestracja]</a>
</body>
</html>