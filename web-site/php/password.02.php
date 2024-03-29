<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../passe-coque.css">
	<title>Send Password request</title>
    <style type="text/css">
		a:link, a:visited {
			background-color: #f44336;
			color: white;
			padding: 14px 25px;
			text-align: center;
			text-decoration: none;
			display: inline-block;
			border-radius: 10px;
		}
		a:hover, a:active {
			background-color: red;
		}
    </style>
</head>
<body>

<?php

require __DIR__ . "/db.cred.php";

try {
	if (isset($_GET['subscriber-id'])) { // operation = send-reset

		$random = substr(md5(mt_rand()), 0, 7);
		// echo "Your password will be [" . $random . "]";

		$pc_id = $_GET['subscriber-id'];
		$lang = $_GET['lang'];

		$link = new mysqli($dbhost, $username, $password, $database);

		// Make sure the member exists.
		$sql = "SELECT CONCAT(FIRST_NAME, ' ', LAST_NAME) FROM PASSE_COQUE_MEMBERS WHERE EMAIL = '$pc_id';"; 
      
		// $result = mysql_query($sql, $link);
		$result = mysqli_query($link, $sql);
		// echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
		if ($result->num_rows != 1) {
			if ($lang == "FR") {
				echo "On ne vous ($pc_id) a pas trouv&eacute; dans la base de donn&eacute;es...";
			} else {
				echo "You ($pc_id) were not found in the member database...";
			}
		} else {
			while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
				$display_name = urldecode($table[0]);
			}
			// Update password
			try {
				$sql = 'UPDATE PASSE_COQUE_MEMBERS ' .
				       'SET PASSWORD = \'' . sha1($random) . '\'' .
				       'WHERE EMAIL = \'' . $pc_id . '\'';
				$all_good = true;
				if (true) { // Do perform ?
					if ($link->query($sql) === TRUE) {
						if ($lang == "FR") {
							echo "OK. Mise &agrave; jour effectu&eacute;e.<br/>" . PHP_EOL;
						} else {
							echo "OK. Operation performed successfully<br/>" . PHP_EOL;
						}
					} else {
						$all_good = false;
						if ($lang == "FR") {
							echo "ERREUR d'ex&eacute;cution : " . $sql . "<br/>" . $link->error . "<br/>";
						} else {
							echo "ERROR executing: " . $sql . "<br/>" . $link->error . "<br/>";
						}
					}
				} else {
					echo "Stby<br/>" . PHP_EOL;
				}
				if ($all_good) {
					if ($lang == "FR") {
						echo "OK $display_name.<br/>Votre mot de passe est [$random]. Connectez-vous sur votre Espace Membre, et changer le pour quelque chose que vous seul connaissez." . "<br/>" . PHP_EOL;
					} else {
						echo "OK $display_name.<br/>Your password is now [$random]. Do use your Member Space to log in, and change it to something YOU only know." . "<br/>" . PHP_EOL;
					}
				}
			} catch (Throwable $e2) {
				echo "[Captured Throwable for password.02.php : " . $e2 . "] " . PHP_EOL;

			}
		}
		// On ferme !
        $link->close();
        // echo("Closed DB<br/>".PHP_EOL);
	} else {
		echo "Where do you think you're going?";
	}

} catch (Throwable $e) {
    echo "[Captured Throwable for password.02.php : " . $e . "] " . PHP_EOL;
}
	?>
</body>
</html>

