<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>BC Subscription</title>
    <style type="text/css">
      * {
        font-family: 'Courier New'
      }

      tr > td {
        border: 1px solid silver;
      }
    </style>
  </head>
  <body>
<?php 

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
            sendEmail("pcc@passe-coque.com", "Boat Club Subscription", $message, $lang, true);
            if ($lang == 'FR') {
                $message = 'Votre demande d\'adh&eacute;sion a &eacute;t&eacute; adress&eacute;e &agrave; pcc@passe-coque.com. Vous recevrez bient&ograve;t des nouvelles.';
            } else {
                $message = 'Your subscription request has been sent to pcc@passe-coque.com. You\'ll be updated soon.';
            }
            echo("\n");
            sendEmail($email, "Boat Club Subscription", $message, $lang, true);
        }
    } catch (Throwable $e) {
        echo "ERROR: Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }
}
?>
  </body>        
</html>