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
	<title>Send Email With Attachment</title>
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
	<h3>Envoi d'un mailing aux responsables, contributeurs et donateurs d'un projet</h3>
</div>

<?php
// Original code from https://www.geeksforgeeks.org/php-send-attachment-email/

require __DIR__ . "/../../php/db.cred.php";

function getProjects(string $dbhost, string $username, string $password, string $database, bool $verbose = false): array {
	try {
		$link = new mysqli($dbhost, $username, $password, $database);
		
		if ($link->connect_errno) {
			echo("[Oops, errno:".$link->connect_errno."...] ");
			// die("Connection failed: " . $conn->connect_error);
			throw $conn->connect_error;
		} else {
			if ($verbose) {
				echo("[Connected.] ");
			}
		}
		$sql = "SELECT PROJECT_ID, PROJECT_NAME FROM PROJECTS ORDER BY 2;";
		if ($verbose) {
			echo('[Performing instruction ['.$sql.']] ');
		}
		
		$result = mysqli_query($link, $sql);
		if ($verbose) {
			echo ("Returned " . $result->num_rows . " row(s)<br/>");
		}

		$projects = array();
		$projectIndex = 0;
		while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
			$projects[$projectIndex] = array("id" => $table[0], "name" => $table[1]);
			$projectIndex++;
		}
		// On ferme !
		$link->close();
		if ($verbose) {
			echo("[Closed DB] ".PHP_EOL);
		}
		return $projects;

	} catch (Throwable $e) {
		echo "[ Captured Throwable for connection : " . $e->getMessage() . "] " . PHP_EOL;
		throw $e;
	}                
	return null;
}
  
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
	

if (isset($_POST['button']) && isset($_FILES['attachment'])) {

	// echo ("Top Loop<br/>");
	echo ("<h2>Sending process initiated</h2>" . PHP_EOL);

	$from_email		 = 'contact@passe-coque.com'; // 'sender@abc.com';    // from mail, sender email address
	// Recipient, from DB
	// $recipient_email = 'olivier.lediouris@gmail.com'; // 'recipient@xyz.com'; // recipient email address
	
	// Load POST data from HTML form
	$sender_name    = $_POST["sender_name"];  // sender name
	$reply_to_email = $_POST["sender_email"]; // sender email, it will be used in "reply-to" header
	$subject	    = $_POST["subject"];      // subject for the email
	$message	    = $_POST["message"];      // body of the email
	$email_restriction = $_POST['email_restriction']; // Optional, for tests before the real send.
	$project_id     = $_POST["project-id"];

	/*Always remember to validate the form fields like this
	if (strlen($sender_name) < 1) {
		die('Name is too short or empty!');
	}
	*/
	//Get uploaded file data using $_FILES array
	$tmp_name = $_FILES['attachment']['tmp_name']; // get the temporary file name of the file on the server
	$name	  = $_FILES['attachment']['name'];     // get the name of the file
	$size	  = $_FILES['attachment']['size'];     // get size of the file for size validation
	$type	  = $_FILES['attachment']['type'];     // get type of the file
	$error	  = $_FILES['attachment']['error'];    // get the error (if any)

	//validate form field for attaching the file
	if ($error > 0) {
		die('Upload error or No files uploaded');
	}

	//read from the uploaded file & base64_encode content
	$handle = fopen($tmp_name, "r");  // set the file handle only for reading the file
	$content = fread($handle, $size); // reading the file
	fclose($handle);				  // close upon completion

	$encoded_content = chunk_split(base64_encode($content));
	$boundary = md5("random"); // define boundary with a md5 hashed value

	// header
	$headers = "MIME-Version: 1.0\r\n";              // Defining the MIME version
	$headers .= "From:".$from_email."\r\n";          // Sender Email
	$headers .= "Reply-To: ".$reply_to_email."\r\n"; // Email address to reach back
	$headers .= "Content-Type: multipart/mixed;";    // Defining Content-Type
	$headers .= "boundary = $boundary\r\n";          // Defining the Boundary
			
	// Loop on recepients
	try {

		$link = new mysqli($dbhost, $username, $password, $database);
    
		if ($link->connect_errno) {
		  echo("Oops, errno:".$link->connect_errno."...<br/>");
		  die("Connection failed: " . $conn->connect_error);
		} else {
		  // echo("Connected.<br/>");
		}
	  
		$sql = 'SELECT EMAIL, FIRST_NAME, LAST_NAME, NEWS_LETTER_OK ' . 
		       'FROM PASSE_COQUE_MEMBERS ' . 
			   'WHERE EMAIL LIKE \'%' . $email_restriction . '%\' ' .   // Possible restriction here, and below...
// 		   '      AND (LAST_NAME LIKE \'%Le%Diouris%\') ' .
//			   '      AND (LAST_NAME LIKE \'%Le%Diouris%\' ' .
//			   '        OR LAST_NAME LIKE \'%Allais%\' ' . 
//			   '        OR FIRST_NAME LIKE \'%Pierre-Jean%\')' .
			   '  AND TARIF IN (\'Passeur d\\\'Écoute\', \'Référent\', \'CA / Administration\');'; 

		$sql = "SELECT PO.PROJECT_ID, " .                              // 0: prj id
					"PO.OWNER_EMAIL,  " .                              // 1: destination email
					"CONCAT(PC.FIRST_NAME, ' ', PC.LAST_NAME),  " .    // 2: Full name 
					"PRJ.DESCRIPTION  " .                              // 3: Prj descriptipn
				"FROM PROJECT_OWNERS PO,  " . 
					"PASSE_COQUE_MEMBERS PC,  " . 
					"PROJECTS PRJ " . 
				"WHERE PO.PROJECT_ID = '" . $project_id . "' AND  " . 
					"PO.OWNER_EMAIL = PC.EMAIL AND  " . 
					"PC.EMAIL LIKE '%" . $email_restriction . "%' AND " .
					"PO.PROJECT_ID = PRJ.PROJECT_ID " . 
				"UNION " . 
				"SELECT CTR.PROJECT_ID,  " . 
					"CTR.DONOR_EMAIL,  " . 
					"CONCAT(PC.FIRST_NAME, ' ', PC.LAST_NAME),  " . 
					"PRJ.DESCRIPTION   " . 
				"FROM PROJECT_CONTRIBUTORS CTR,  " . 
					"PASSE_COQUE_MEMBERS PC,  " . 
					"PROJECTS PRJ  " . 
				"WHERE CTR.PROJECT_ID = '" . $project_id . "' AND  " . 
					"CTR.DONOR_EMAIL = PC.EMAIL AND  " . 
					"PC.EMAIL LIKE '%" . $email_restriction . "%' AND " .
					"CTR.PROJECT_ID = PRJ.PROJECT_ID;";
		   
		
		echo('Performing query <code>'.$sql.'</code><br/>');
	  
		// $result = mysql_query($sql, $link);
		$result = mysqli_query($link, $sql);
		echo ("Will send " . $result->num_rows . " email(s)<br/>");

		set_time_limit($result->num_rows); // 1 second per email
	  
		while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
		  // echo "table contains ". count($table) . " entry(ies).<br/>";
		  $nl_id = $table[1];
		  $subscriber_email = $table[1];

		  $footer = "<br/><hr/><p>"; 
		  $footer .= "<img src='http://www.passe-coque.com/logos/LOGO_PC_rvb.png' width='40'><br/>";  // The full URL of the image.
		  $footer .= "The <a href='http://www.passe-coque.com' target='PC'>Passe-Coque</a> web site<br/>"; // Web site
		  $footer .= "<a href='http://www.passe-coque.com/php/unsubscribe.php?subscriber-id=$nl_id'>Se d&eacute;sabonner / Unsubscribe</a><br/>"; // Use the real ID
		  $footer .= "</p>";
		  $fmt_message = str_replace("\n", "\n<br/>", $message);
		  $fmt_message .= $footer;
	  
		  // plain text, or html
		  $body = "--$boundary\r\n";
		  // $body .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
		  $body .= "Content-Type: text/html; charset=UTF-8\r\n"; // To allow HTML artifacts, like links and Co.
		  $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
		  $body .= chunk_split(base64_encode($fmt_message));
			  
		  // attachment
		  $body .= "--$boundary\r\n";
		  $body .="Content-Type: $type; name=".$name."\r\n";
		  $body .="Content-Disposition: attachment; filename=".$name."\r\n";
		  $body .="Content-Transfer-Encoding: base64\r\n";
		  $body .="X-Attachment-Id: ".rand(1000, 99999)."\r\n\r\n";
		  $body .= $encoded_content; // Attaching the encoded file with email
	  
		  // TODO Bcc in the headers (see https://stackoverflow.com/questions/9525415/php-email-sending-bcc)
		  $sentMailResult = mail($subscriber_email, $subject, $body, $headers);

		  if ($sentMailResult) {
			  echo "Email to $subscriber_email was sent successfully.<br/>" . PHP_EOL;
			  // unlink($name); // delete the file after attachment sent.
		  } else {
			  echo "There was a problem for $subscriber_email ...<br/>";
			  die("Sorry but the email to $subscriber_email could not be sent. Please go back and try again!");
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
	Choose the project, the file to attach (like a pdf), enter a message (the content of the email), and click the Send button!
	<div style="display: flex; justify-content: center; margin-top: 10px;">
		<form enctype="multipart/form-data" method="POST" action="" id="formEmail" style="width: 500px;">

			<div class=form-group>
				<!-- Project list -->
				For project <select name="project-id" form="formEmail">
<?php
        try {
			$projectList = getProjects($dbhost, $username, $password, $database); // For the drop-down list
			foreach($projectList as $project) {
			  echo('<option value="' . $project["id"] . '">' . $project["name"] . '</option>');
			}	
		} catch (Throwable $e) {
			echo "Captured Throwable for connection (getProjects) : " . $e->getMessage() . "<br/>" . PHP_EOL;
		}
?>					
				</select>
			</div>
			<div class="form-group">
				<input class="form-control" type="text" name="sender_name" placeholder="Sender Name" required value="Passe-Coque Contact"/>
			</div>
			<div class="form-group">
				<input class="form-control" type="email" name="sender_email" placeholder="Sender's Email Address" required value="contact@passe-coque.com"/>
			</div>
			<div class="form-group">
				<input class="form-control" type="text" name="email_restriction" placeholder="Recipient's Email Address filter" value=""/>
			</div>
			<div class="form-group">
				<input class="form-control" type="text" name="subject" placeholder="Subject" required value="Communication Passe-Coque"/>
			</div>
			<div class="form-group">
				<textarea class="form-control" name="message" placeholder="Message"></textarea>
			</div>
			<div class="form-group">
				<input class="form-control" type="file" name="attachment" placeholder="Attachment" required/>
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

