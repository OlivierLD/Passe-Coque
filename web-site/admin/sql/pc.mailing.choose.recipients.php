<?php
// Must be on top
$timeout = 300;  // In seconds
$applyTimeout = false; // Change at will

try {
  if (!isset($_SESSION)) {
    if ($applyTimeout) {
      ini_set("session.gc_maxlifetime", $timeout);
      ini_set("session.cookie_lifetime", $timeout);
    }
    session_start();
  }
} catch (Throwable $e) {
  echo "Session settings: Captured Throwable: " . $e->getMessage() . "<br/>" . PHP_EOL;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../../passe-coque.css">
	<title>Send Email- no Attachment</title>
    <style type="text/css">
        h3 {
            margin: 0 10px;
        }
		textarea.form-control {
			height: 200px;
		}
		.form-group {
			margin-bottom: 1rem
		}
		.form-control {
			display: inline-block;
			width: 400px;
			vertical-align: middle
		}
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
		* {
			line-height: 1.0em;
		}
    </style>
</head>
<body>

<div>
	<h3>Envoi d'un mailing aux membres de Passe-Coque, avec (possibles) restrictions</h3>
</div>

<?php
// Original code from https://www.geeksforgeeks.org/php-send-attachment-email/

require __DIR__ . "/../../php/db.cred.php";

// Authentication required !!
if (!isset($_SESSION['USER_NAME'])) {
	echo ("<button onclick='window.open(\"https://passe-coque.com/php/admin.menu.html\");'>Authenticate</button><br/>" . PHP_EOL);
	die ("You are not connected! Please log in first!");
} else {
	if (!isset($_SESSION['ADMIN'])) {
	  ?>
	  <button onclick="window.open('https://passe-coque.com/php/admin.menu.html');">Authenticate</button><br/>
	  <form action="../../php/members.php" method="post"> <!-- members.php -->
		  <input type="hidden" name="operation" value="logout">
		  <table>
			<tr>
			  <td colspan="2" style="text-align: center;"><input type="submit" value="Log out"></td>
			</tr>
		  </table>
		</form>
	  <?php
	  echo("From script " . basename(__FILE__) . "<br/>" . PHP_EOL);
	  die ("No ADMIN property found! Please log in first!");
	} else {
	  if (!$_SESSION['ADMIN']) {
		?>
		<button onclick="window.open('https://passe-coque.com/php/admin.menu.html');">Authenticate</button><br/>
		<form action="../../php/members.php" method="post"> <!-- members.php -->
			<input type="hidden" name="operation" value="logout">
			<table>
			  <tr>
				<td colspan="2" style="text-align: center;"><input type="submit" value="Log out"></td>
			  </tr>
			</table>
		  </form>
		<?php
		echo("From script " . basename(__FILE__) . "<br/>" . PHP_EOL);
		die("Sorry, you're NOT an Admin.");
	  }
	}
}

if (isset($_POST['button'])) {

	// echo ("Top Loop<br/>");
	echo ("<h2>Sending process initiated</h2>" . PHP_EOL);

	$from_email = 'contact@passe-coque.com'; // 'sender@abc.com';    // from mail, sender email address
	// Recipient, from DB
	// $recipient_email = 'olivier.lediouris@gmail.com'; // 'recipient@xyz.com'; // recipient email address

	// Load POST data from HTML form
	$sender_name    = $_POST["sender_name"];  // sender name
	$reply_to_email = $_POST["sender_email"]; // sender email, it will be used in "reply-to" header
	$subject	    = $_POST["subject"];      // subject for the email
	$message	    = $_POST["message"];      // body of the email
	$email_restriction = $_POST['email_restriction']; // Optional, for tests before the real send.
	$do_send = false;
	if (isset($_POST['send_email'])) {
		$send_email_value = $_POST['send_email'];
		$do_send = ($send_email_value == 'DoSend');
	}
	$member_type = $_POST['member-type']; // in ALL, ADMIN, REFERENT, PRJ_LEAD, BC_MEMBER

	/*Always remember to validate the form fields like this
	if (strlen($sender_name) < 1) {
		die('Name is too short or empty!');
	}
	*/

	// header
	$headers = "MIME-Version: 1.0\r\n";              // Defining the MIME version
	$headers .= "From:".$from_email."\r\n";          // Sender Email
	$headers .= "Reply-To: ".$reply_to_email."\r\n"; // Email address to reach back
	$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

	// Loop on recepients
	try {

		$link = new mysqli($dbhost, $username, $password, $database);

		if ($link->connect_errno) {
		  echo("Oops, errno:".$link->connect_errno."...<br/>");
		  die("Connection failed: " . $conn->connect_error);
		} else {
		  // echo("Connected.<br/>");
		}

		/*
			Dates and fees:
			SELECT F.EMAIL, DATEDIFF(NOW(), MAX(F.PERIOD))
			FROM MEMBERS_AND_FEES F
			GROUP BY F.EMAIL
			HAVING DATEDIFF(NOW(), MAX(F.PERIOD)) > 365;
		*/

		$sql = 'SELECT EMAIL, FIRST_NAME, LAST_NAME, NEWS_LETTER_OK ' .
		       'FROM PASSE_COQUE_MEMBERS ' .
			   'WHERE EMAIL LIKE \'%' . $email_restriction . '%\' ' .   // Possible restriction here, and below...
// 		   '      AND (LAST_NAME LIKE \'%Le%Diouris%\') ' .
//			   '      AND (LAST_NAME LIKE \'%Le%Diouris%\' ' .
//			   '        OR LAST_NAME LIKE \'%Allais%\' ' .
//			   '        OR FIRST_NAME LIKE \'%Pierre-Jean%\')' .
               '';
		switch ($member_type) {
			case 'ALL':
				$sql .= '  AND TARIF IS NOT NULL';
				break;
			case 'ADMIN':
				$sql .= '  AND (TARIF IN (\'CA / Administration\') OR ADMIN_PRIVILEGES = TRUE)';
				break;
			case 'REFERENT':
				$sql .= '  AND (TARIF IN (\'Référent\') OR EMAIL IN (SELECT BR.EMAIL FROM BOATS_AND_REFERENTS BR))';
				break;
			case 'PRJ_LEAD':
				$sql .= ' AND EMAIL IN (SELECT PRJ.OWNER_EMAIL FROM PROJECT_OWNERS PRJ)';
				break;
			case 'BC_MEMBER':
				$sql .= ' AND EMAIL IN (SELECT BCM.EMAIL FROM BOAT_CLUB_MEMBERS BCM)';
				break;
			case 'LATE_FEE':
				$sql .= ' AND EMAIL IN (SELECT F.EMAIL
										FROM MEMBERS_AND_FEES F
										GROUP BY F.EMAIL
										HAVING DATEDIFF(NOW(), MAX(F.PERIOD)) > 365)';
				break;
			default:
				break;
		}
		$sql .= ';';

		echo('Performing query <code>'.$sql.'</code><br/>');

		// $result = mysql_query($sql, $link);
		$result = mysqli_query($link, $sql);
		echo ("Will send " . $result->num_rows . " email(s)<br/>" . PHP_EOL);

		set_time_limit($result->num_rows); // 1 second per email

		if (!$do_send) {
			echo ("<hr/>" . PHP_EOL);
		}
		while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
		  // echo "table contains ". count($table) . " entry(ies).<br/>";
		  $active = ($table[3]/* === true*/) ? "Yes" : "No";
		  $nl_id = $table[0];
		  $subscriber_email = $table[0];

		  $footer = "<br/><hr/><p>";
		  $footer .= "<img src='http://www.passe-coque.com/logos/LOGO_PC_rvb.png' width='40'><br/>";  // The full URL of the image.
		  $footer .= "The <a href='http://www.passe-coque.com' target='PC'>Passe-Coque</a> web site<br/>"; // Web site
		  $footer .= "</p>";

		  $fmt_message = str_replace("\n", "\n<br/>", $message);
		  $fmt_message .= $footer;

		  // html content
		  $body = "<html><body>\n";
		  $body .= $fmt_message;
		  $body .= "\n</body></html>";

		  if ($do_send) {
			// TODO Bcc in the headers (see https://stackoverflow.com/questions/9525415/php-email-sending-bcc)
			$sentMailResult = mail($subscriber_email, $subject, $body, $headers);

			if ($sentMailResult) {
				echo "Email to $subscriber_email was sent successfully.<br/>" . PHP_EOL;
				// unlink($name); // delete the file after attachment sent.
			} else {
				echo "There was a problem for $subscriber_email ...<br/>";
				die("Sorry but the email to $subscriber_email could not be sent. Please go back and try again!");
			}
		  } else {
			echo "- Would send email to $subscriber_email ...<br/>" . PHP_EOL;
		  }
		}

		// On ferme !
		$link->close();
		// echo("Closed DB<br/>".PHP_EOL);
	  } catch (Throwable $e) {
		echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
	  }
	  echo "<hr/>" . PHP_EOL;
	  // TODO A Button to get back to the page.
	  echo ("<a href=''>Back !</a><br/>" . PHP_EOL);
} else {
?>
	Enter a message (the content of the email), a subject, and click the Send button!
	<div style="display: flex; justify-content: center; margin-top: 10px;">
		<form enctype="multipart/form-data" method="POST" action="" style="width: 500px;">
			<div class="form-group">
				<input class="form-control" type="text" name="sender_name" placeholder="Sender Name" required value="Passe-Coque Contact"/>
			</div>
			<div class="form-group">
				<input class="form-control" type="email" name="sender_email" placeholder="Sender's Email Address" required value="contact@passe-coque.com"/>
			</div>
			<div class="form-group">
				<span class="form-control"><input type="checkbox" name="send_email" value="DoSend" checked/> Do send the email - or not (will return the list of recepients, only; for test).</span>
			</div>
			<div class="form-group">
				Filter on email address:<br/>
				<input class="form-control" type="text" name="email_restriction" placeholder="Recipient's Email Address filter (optional)" value=""/>
			</div>
			<div class="form-group">
				<div style="display: grid; grid-template-columns: auto auto;">
					<input type="radio" name="member-type" value="ALL" checked><span>Tous les membres de Passe-Coque</span>
					<input type="radio" name="member-type" value="ADMIN"><span>Admin</span>
					<input type="radio" name="member-type" value="REFERENT"><span>R&eacute;f&eacute;rents</span>
					<input type="radio" name="member-type" value="PRJ_LEAD"><span>Chefs de projet</span>
					<input type="radio" name="member-type" value="BC_MEMBER"><span>Membres Boat-Club</span>
					<input type="radio" name="member-type" value="LATE_FEE"><span>Membres en retard de cotisation</span>
				</div>
			</div>
			<div class="form-group">
				<input class="form-control" type="text" name="subject" placeholder="Subject" value="Communication Passe-Coque"/>
			</div>
			<div class="form-group">
				<textarea class="form-control" name="message" placeholder="Message"></textarea>
			</div>
			<div class="form-group">
				<input class="pc-button" type="submit" name="button" value="Send"/>
			</div>
		</form>
	</div>
	<?php
}
	?>
	<hr/>
	<address>&copy; Passe-Coque, 2024</address>
</body>
</html>

