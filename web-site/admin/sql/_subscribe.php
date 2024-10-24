<?php 
/*
    Boat Club Subscription request backend.

    May return several errors:
    SUBSCRIBE-001 - Chart not read.
    SUBSCRIBE-002 - Already Boat-Club member.
    SUBSCRIBE-003 - Not a Passe-Coque member.
    SUBSCRIBE-004 - Unknown status returned.

    See the code for details. Also see the errNo returned by the checkMemberShip function.

    Can be invoke directly from a GET request with QS parameters (for tests):
    https://passe-coque.com/admin/sql/_subscribe.php?lang=FR&email=olivier@lediouris.net&motivations=Duh&read-the-chart=on

    POST is used in prod.
*/

require __DIR__ . "/../../php/db.cred.php";
require __DIR__ . "/_db.utils.php";
require __DIR__ . "/_emails.utils.php";

$VERBOSE = false;

// POST is preferred, GET is here for tests...

if (!isset($_POST['email']) && !isset($_GET['email'])) {
    die ("ERROR: Email is required");
}
if (!isset($_POST['read-the-chart'])) {
    // die ("read-the-chart is required");
    $check = 'off';
} else {
    $check = $_POST['read-the-chart'];  // on|off
}
if (isset($_GET['read-the-chart'])) {
    $check = $_GET['read-the-chart'];
}
if (!isset($_POST['motivations']) && !isset($_GET['motivations'])) {
    die ("ERROR: Motivations is (are) required");
}

$lang = 'FR';
if (isset($_POST['lang'])) {
    $lang = $_POST['lang'];
}
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
}
if (isset($_POST['email'])) {
    $email = $_POST['email'];
}
if (isset($_GET['email'])) {
    $email = $_GET['email'];
}
if (isset($_POST['motivations'])) {
    $motivations = $_POST['motivations'];
}
if (isset($_GET['motivations'])) {
    $motivations = $_GET['motivations'];
}
// File attachment ?
$name = '';
if (isset($_FILES['attachment'])) {
    // Get uploaded file data using $_FILES array
	$tmp_name = $_FILES['attachment']['tmp_name']; // get the temporary file name of the file on the server
	$name	  = $_FILES['attachment']['name'];     // get the name of the file
	$size	  = $_FILES['attachment']['size'];     // get size of the file for size validation
	$type	  = $_FILES['attachment']['type'];     // get type of the file
	$error	  = $_FILES['attachment']['error'];    // get the error (if any)

	//validate form field for attaching the file
	if ($error > 0) {
		die('Upload error or No files uploaded');
    }
// } else {
//    echo("No _FILES");
}

if ($VERBOSE) {
    echo ("Subscribe Data: email [$email], read the chart [$check], motivations [$motivations] <br/>" . PHP_EOL);
}

// Read the chart ?
if ($check != 'on') {
    echo ("PROCESS-ERROR: SUBSCRIBE-001 - Chart not read." . PHP_EOL);
} else {
    $ok = true;
    $errMess = "";
    try {
        // Member of the asso ? Already member of the club ?
        $status = checkMemberShip($dbhost, $username, $password, $database, $email, $VERBOSE);
        if ($status->status) { // Passe-Coque AND Boat-Club
            // All good, but already member
            if ($VERBOSE) {
                echo("Membership, Already Boat-Club Member." . PHP_EOL);
            }
            $ok = false;
            $message = "SUBSCRIBE-002 - Already Boat-Club member.";
        } else {
            if ($VERBOSE) {
                echo("Membership: <br/>" . PHP_EOL);
                var_dump($status);
                echo ("<br/>");
            }
            if ($status->errNo == 1) { // Not even Passe-Coque
                $ok = false;
                $message = "SUBSCRIBE-003 - Not a Passe-Coque member.";
            } else if ($status->errNo == 2) { // Not Boat-Club member
                $ok = true;
            } else {
                // WHAT ?
                $ok = false;
                $message = "SUBSCRIBE-004 - Unknown status returned.";
            }
        }
        if ($VERBOSE) {
            echo ("OK ? " . $ok . "<br/>");
            echo ("Message: " . $message . "<br/>");
        }
        if (!$ok) {
            echo ("PROCESS-ERROR: " . $message);
        }
        // If all good, send email
        if ($ok) {
            // TODO Translate that one.
            $message = "$email a soumis une demande d'inscription au BoatClub, avec ces motivations :\n<hr/>$motivations\n<hr/>\n&Agrave; valider.";
            if (strlen($name) > 0) { // WiP... Attach that one to the email to the club
                echo("File to attach:" . $name);
            } else {
                echo("No file was attached");
            }
            sendEmail("pcc@passe-coque.com", "Boat Club Subscription", $message, $lang, false, true);
            if ($lang == 'FR') {
                $message = 'Votre demande d\'adh&eacute;sion a &eacute;t&eacute; adress&eacute;e &agrave; pcc@passe-coque.com. Vous recevrez bient&ograve;t des nouvelles.';
            } else {
                $message = 'Your subscription request has been sent to pcc@passe-coque.com. You\'ll be updated soon.';
            }
            echo("\n");
            sendEmail($email, "Boat Club Subscription", $message, $lang, false, true);
        }
    } catch (Throwable $e) {
        echo "ERROR: Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }
}
// Content-type ?
header('Content-Type: text/plain; charset=utf-8');
?>
