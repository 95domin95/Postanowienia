<?php
	session_start();
	if (isset($_SESSION['registration_success']))
	{
		header('Location: index.php');
		exit();
	}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8" />
	<title>Postanowienia</title>
</head>
<body>
	<b>Dziękujemy za rejestracje w programie "Postanowienia (ALPHA)"!</b></br></br>
	</br><a href="index.php" title="Rejestracja">[Zaloguj się na swoje konto!]</a>
</body>
</html>
