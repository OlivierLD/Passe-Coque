<?php
// Original code from https://www.geeksforgeeks.org/php-send-attachment-email/

$username = "passecc128";
$password = "zcDmf7e53eTs";
$database = "passecc128";
$dbhost = "passecc128.mysql.db";


if (isset($_POST['button']) && isset($_FILES['attachment'])) {
	$from_email		 = 'contact@passeurdecoute.fr'; // 'sender@abc.com';    // from mail, sender email address
	// Recipient, from DB
	// $recipient_email = 'olivier.lediouris@gmail.com'; // 'recipient@xyz.com'; // recipient email address
	
	// Load POST data from HTML form
	$sender_name    = $_POST["sender_name"];  // sender name
	$reply_to_email = $_POST["sender_email"]; // sender email, it will be used in "reply-to" header
	$subject	    = $_POST["subject"];      // subject for the email
	$message	    = $_POST["message"];      // body of the email

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
	  
		$sql = 'SELECT ID, NAME, EMAIL, SUBSCRIPTION_DATE, ACTIVE ' . 
		       'FROM NL_SUBSCRIBERS ' . 
			   'WHERE EMAIL LIKE \'%\' ' .   // Possible restriction here...
			   '      AND (NAME LIKE \'%Le Diouris%\' ' .
			   '        OR NAME LIKE \'%Guy%Gab%\' ' . 
			   '        OR NAME LIKE \'%Allais%\' ' . 
			   '        OR NAME LIKE \'%Pierre-Jean%\')' .
			   '      AND ACTIVE = TRUE;'; 
		
		// echo('Performing query <code>'.$sql.'</code><br/>');
	  
		// $result = mysql_query($sql, $link);
		$result = mysqli_query($link, $sql);
		echo ("Will send " . $result->num_rows . " email(s)<br/>");
	  
		while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
		  // echo "table contains ". count($table) . " entry(ies).<br/>";
		  $active = ($table[4]/* === true*/) ? "Yes" : "No";
		  $nl_id = $table[0];
		  $subscriber_email = $table[2];

		  $footer = "<br/><hr/><p>"; 
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
	  

		  $sentMailResult = mail($subscriber_email, $subject, $body, $headers);

		  if ($sentMailResult) {
			  echo "Email to $subscriber_email was sent successfully.<br/>" . PHP_EOL;
			  // unlink($name); // delete the file after attachment sent.
		  } else {
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

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../../passe-coque.css">
	<title>Send NL-Email With Attachment</title>
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
    </style>
</head>
<body>
	Choose the pdf containing the news letter, enter a message, and click the button!
	<div style="display: flex; justify-content: center; margin-top: 10px;">
		<form enctype="multipart/form-data" method="POST" action="" style="width: 500px;">
			<div class="form-group">
				<input class="form-control" type="text" name="sender_name" placeholder="Sender Name" required value="Passe-Coque Contact"/>
			</div>
			<div class="form-group">
				<input class="form-control" type="email" name="sender_email" placeholder="Recipient's Email Address" required value="contact@passeurdecoutes.fr"/>
			</div>
			<div class="form-group">
				<input class="form-control" type="text" name="subject" placeholder="Subject" value="News Letter XXXX"/>
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
	<hr/>
	<address>&copy; Passe-Coque, 2024</address>
</body>
</html>
