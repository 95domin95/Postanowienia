<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8" />
	<title>Postanowienia</title>
	<link rel="stylesheet" href="style.css" type="text/css"/>
	<script>
	    var tableHidden = false;
		
		var recordsContent;

		var tableContent;
			
		document.addEventListener("DOMContentLoaded", function(event) 
		{
			tableContent = document.getElementById("divDecisionsTable").innerHTML;
				
			document.getElementById("divRecords").innerHTML = recordsContent;
			document.getElementById("hide").onclick = hideTable;				
			document.getElementById("show").onclick = showTable;
		});
			
		function refresh()
		{
			window.location.reload();
		}
			
		function showTable()
		{
			if(tableHidden)
			{
				document.getElementById("divDecisionsTable").innerHTML = tableContent;
				tableHidden = false;
			}
		}
			
		function hideTable()
		{
			if(!tableHidden)
			{
				tableContent = document.getElementById("divDecisionsTable").innerHTML;
				document.getElementById("divDecisionsTable").innerHTML = "";
				tableHidden = true;
			}
		}
	</script>
</head>
<body>
<div id="divMainContainer">
	<div id="divTitle">
		<b>Postanowienia alpha</b>
	</div>
	<div id="divHideShow">
		<input type="submit" value="Pokaż tabelę" id="show"/><input type="submit" value="Ukryj tabelę" id="hide"/></br>
	</div>
	<div id="divDecisionsTable">
		<table>
		<?php
		/*TODO
		-ogólnie ficzery z wersji desktopowej przenieść
		 jak się da

		Ustawienia konta:
		-zmiana hasła
		-zmiana adresu email
		-zmiana loginu

		W wersjii produkcjyjnej:
		-opcja zapomniałem hasła w index php
		-zabezpieczenie captcha
		-solenie do hasła

		NO I OCZYWIŚCIE CSS'a kiedyś tam :)
		*/
			
			mysqli_report(MYSQLI_REPORT_OFF);
			
			session_start();

			if(!isset($_SESSION['date'])) $_SESSION['date'] = '';
			if(!isset($_SESSION['decision'])) $_SESSION['decision'] = '';
			if(!isset($_SESSION['newDecision'])) $_SESSION['newDecision'] = '';
			if(!isset($_SESSION['deleteDecision'])) $_SESSION['deleteDecision'] = '';
			if(!isset($_SESSION['pageRefresh'])) $_SESSION['pageRefresh'] = false;
			if(!isset($_SESSION['first'])) $_SESSION['first'] = false;
			if(!isset($_SESSION['deleteDay'])) $_SESSION['deleteDay'] = '';
			if ((!isset($_SESSION['login'])) && (!isset($_SESSION['password'])))
			{
				header('Location: index.php');
				exit();
			}
					
			try
			{
				$TABLE = $_SESSION['login']."_user_decisions";
				require_once "connect.php";
				
				$connection = new mysqli($host, $db_user, $db_password, $db_name);
				if($connection->connect_errno!=0)
				{
					throw new Exception($connection->error);
				}
				else
				{
					if(!($result=$connection->query("SELECT * FROM ".$TABLE." ORDER BY id ASC")))
					{
						throw new Exception($connection->error);
					}
					else
					{
						if($result->num_rows == 0&&$_SESSION['first']==false)
						{
							if(!$connection->query("INSERT INTO ".$TABLE." (ID, Date, Special) VALUES (NULL, '', '')")) throw new Exception($connection->error);
							echo "<script>refresh();</script>";
							$_SESSION['first'] = true;
						}
						if($result->num_rows >= 0)
						{
							$decisionsNames = Array();
							$decisionDaysCount = Array();
							$decisionDaysBuffer = Array();
							/*
							Chodzi tu ile udało się wytrzymać, a nie o 
							rekordy z bazy danych. Taka funkcjonalność dodatkowa.
							*/
							$num_rows = $result->num_rows;		
							for($i=1; $i<=$num_rows; $i++)
							{
								$record = $result->fetch_array();
								$num_columns = mysqli_fetch_lengths($result);
								if($i==1)
								{
									for($k=0; $k<count($num_columns)-3; $k++)
									{
										$decisionDaysCount[] = 0;
										$decisionDaysCountBuffer[] = 0;
									}
									echo '<tr>';
									for($k=1; $k<=count($num_columns); $k++)
									{
										if($k<=count($num_columns))
										{
											$meta = mysqli_fetch_field($result);
											
											if($meta->name!="ID")
											{
												echo '<td><b>'.$meta->name.'</b></td>';
												if($meta->name!="Date"&&$meta->name!="Special")
												{
													$decisionsNames[] = $meta->name;
												}
											}
										}
										if($k==count($num_columns)) echo '</tr>';
									}
								}
								echo '<tr>';
								for($j=1; $j<count($num_columns); $j++)
								{
									if($record[$j]=="nie spełniono") echo '<td><span style="color:red">'.$record[$j].'</span></td>';
									else if($record[$j]=="spełniono") echo '<td><span style="color:green">'.$record[$j].'</span></td>';
									else echo '<td>'.$record[$j].'</td>';
									if($j>2)
									{
										if($record[$j]=="spełniono") $decisionDaysCountBuffer[$j - 3]++;
										else if($record[$j]=="nie spełniono")
										{
											if($decisionDaysCountBuffer[$j - 3]>$decisionDaysCount[$j - 3])
											{
												$decisionDaysCount[$j - 3] = $decisionDaysCountBuffer[$j - 3];
											}
											$decisionDaysCountBuffer[$j - 3] = 0;
										}
										if($decisionDaysCountBuffer[$j - 3]>$decisionDaysCount[$j - 3])
										{
											$decisionDaysCount[$j - 3] = $decisionDaysCountBuffer[$j - 3];
										}
									}
								}
								echo '</tr>';
							}
							$text = "<h3><b>Rekordy</b></h3>";
							for($i=0; $i<count($decisionsNames); $i++)
							{
								$text = $text.$decisionsNames[$i].": ".$decisionDaysCount[$i]."dni</br>";
							}
							echo '<script>recordsContent = "'.$text.'";</script>';
							$result->free_result();
						}
					}
				}
				
				if(isset($_POST['decision'])&&(($_SESSION['date'] != $_POST['date'])||($_SESSION['decision']!=$_POST['decision'])))
				{
		/*TODO
		Tą kwerende z postanowieniami trzeba inaczej rozwiązać
		bo teraz jest wiele postanowień.
		1.Pomysł w pętli doklejać kawałki kolejnych postanowień.
		  Sprawdzić najpierw ile jest kolumn odjąć 3 i mamy ilość.
		  Oczywiście trzeba też sprawdzać poprawność danych wpisanych
		  przez użytkownika
		2.Wypie****ć to wszystko i zmienić wgl strukturę tabel w bazie danych
		3.Zmienić interfej przewijania powiadomień oczywiście w pętli
		  echem wyświetla aktualne postanoiwenie i klika next sprawdzajc uprze-
		  dnio poprawność oczywiście Pierwsze zapytanie to będzie INSERT a potem 
		  UPDATY same bo trza najsamprzód stworzyć rekord, żeby moć go potem
		  edytować
		*/			
					if($connection->connect_errno!=0)
					{
						throw new Exception($connection->error);
					}
					else
					{
						$date = htmlentities($_POST['date'], ENT_QUOTES, "UTF-8");
						$special = htmlentities($_POST['special'], ENT_QUOTES, "UTF-8");
						$decision = htmlentities($_POST['decision'], ENT_QUOTES, "UTF-8");
						
						$query = "SELECT $decision FROM ".$TABLE;
						
						if($result=$connection->query($query))
						{
							$query = "SELECT * FROM ".$TABLE." WHERE Date='$date'";
					
							if(!($result=$connection->query($query)))
							{
								throw new Exception($connection->error);
							}
							
							$conditionsFulfilled = "spełniono";
							if(!isset($_POST['conditionsFulfilled'])) $conditionsFulfilled = "nie spełniono";
							
							if($result->num_rows > 0)
							{
								$query = "UPDATE ".$TABLE." SET $decision='$conditionsFulfilled' WHERE Date='$date'";				
							}
							else
							{
								$query = "INSERT INTO ".$TABLE." (ID, Date, Special, $decision) VALUES (NULL, '$date', '$special', '$conditionsFulfilled')";
							}
							if(!($result=$connection->query($query)))
							{
								throw new Exception($connection->error);
							}
							else
							{
								$_SESSION['pageRefresh']=true;
								if($_SESSION['first']==true)
								{
									if(!$connection->query("DELETE FROM ".$TABLE." WHERE Special='' AND Date=''")) throw new Exception($connection->error);
									$_SESSION['first'] = false;
								}
							}
							$_SESSION['date'] = $_POST['date'];
							$_SESSION['decision'] = $_POST['decision'];
						}			
					}
					if($_SESSION['pageRefresh'])
					{
						$_SESSION['pageRefresh']=false;	
						echo "<script>refresh();</script>";
					}			
				}
				
				if(isset($_POST['newDecision'])&&$_SESSION['newDecision'] != $_POST['newDecision'])
				{	
					$query = "SELECT * FROM ".$TABLE." WHERE $";
					if($connection->connect_errno!=0)
					{
						throw new Exception($connection->error);
					}
					else
					{
						if(ctype_alnum($_POST['newDecision']))
						{
							$newDecision = htmlentities($_POST['newDecision'], ENT_QUOTES, "UTF-8");
						
							if(($result=$connection->query("ALTER TABLE ".$TABLE." ADD `$newDecision` TEXT CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL AFTER `Special`")))
							{
								$_SESSION['pageRefresh']=true;
							}
							$_SESSION['newDecision'] = $_POST['newDecision'];
							if($_SESSION['pageRefresh'])
							{
								$_SESSION['pageRefresh']=false;
								echo "<script>refresh();</script>";
							}				
						}
						else
						{
							echo '<script>alert("Nazwa postanowienia może zawierać tylko znaki alfanumeryczne!")</script>';
						}
					}
				}
				
				if(isset($_POST['deleteDecision'])&&$_SESSION['deleteDecision'] != $_POST['deleteDecision'])
				{
					$validator = true;
					$reservedColumns = Array("ID","Date","Special");
					
					foreach($reservedColumns as $i)
					{
						if($_POST['deleteDecision']==$i) $validator = false;
					}
					
					if($validator)
					{
						$deleteDecision = htmlentities($_POST['deleteDecision'], ENT_QUOTES, "UTF-8");
						
						$query = "ALTER TABLE ".$TABLE." DROP COLUMN ".$_POST['deleteDecision'];
						if($connection->query($query))	$_SESSION['pageRefresh']=true;
						$_SESSION['deleteDecision'] = $_POST['deleteDecision'];
						if($_SESSION['pageRefresh'])
						{
							$_SESSION['pageRefresh']=false;
							echo "<script>refresh();</script>";
						}
					}
				}
				
				if(isset($_POST['deleteDay'])&&$_SESSION['deleteDay'] != $_POST['deleteDay'])
				{
					$deleteDay = htmlentities($_POST['deleteDay'], ENT_QUOTES, "UTF-8");
						
					$query = "DELETE FROM ".$TABLE." WHERE Date='$deleteDay'";
					if(!$connection->query($query)) throw new Exception($connection->error);
					else
					{
						$_SESSION['pageRefresh']=true;
					}
						
					$_SESSION['deleteDay'] = $_POST['deleteDay'];
					if($_SESSION['pageRefresh'])
					{
						$_SESSION['pageRefresh']=false;
						echo "<script>refresh();</script>";
					}
				}
				$connection->close();
			}
			catch(Exception $ex)
			{
				echo '<span style="color:red">Błąd!</span>';
				echo $ex;
				echo '</br><a href="index.php" title="Strona główna">[Strona główna]</a>';
				session_unset();
				exit();
			}
			
		?>
		</table>
	</div>
	<div id="divRecords">
		<h3><b>Records</b></h3></br>
	</div>
	</br>
	<div id="divOptions">
		<div id="divAddToday" class="classBelowTable">
			<div class="Title">
				<h3><b>Dodaj dane z dnia dzisiejszego</b></h3>
			</div>
			<form method="post">
				Data: </br><input type="text" name="date"/></br>
				Sytuacja szczególna:  </br><input type="text" name="special"/></br>
				Postanowienie: </br><input type="text" name="decision"/> <label></br>
				Spełniono: <input type="checkbox" name="conditionsFulfilled"/>
				</label> </br>
				<input type="submit" value="Zatwierdź"/>
			</form>
		</div>
		<div class="classBelowTable">
			<div class="Title">
				<h3><b>Dodaj nowe postanowienie</b></h3></br>
			</div>
			<form method="post">
				Nazwa postanowienia: </br><input type="text" name="newDecision"/></br>
				<input type="submit" value="Zatwierdź"/>
			</form>
		</div>
		<div class="classBelowTable">	
			<div class="Title">
				<h3><b>Usuń postanowienie</b></h3></br>
			</div>
			<form method="post">
				Nazwa postanowienia: </br><input type="text" name="deleteDecision"/></br>
				<input type="submit" value="Zatwierdź"/>
			</form>
		</div>
		<div class="classBelowTable">
			<div class="Title">
				<h3><b>Usuń dzień</b></h3></br>
			</div>
			<form method="post">
				Podaj datę: </br><input type="text" name="deleteDay"/></br>
				<input type="submit" value="Zatwierdź"/>
			</form>
		</div>
	</div>
	<div id="divLogout">
		</br>
		<form action="logout.php">
			<input type="submit" value="Wyloguj się"/>
		</form>
	</div>
</div>	
</body>
</html>