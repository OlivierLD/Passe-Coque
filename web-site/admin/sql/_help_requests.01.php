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
<html lang="en">
  <!--
   ! WiP.
   ! - Help requests list - 3 month. (see the $span variable)
   ! - Post a request
   ! - Reply to a request
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Help Requests</title>
    <style type="text/css">
      * {
        font-family: 'Courier New'
      }

      tr > td {
        border: 1px solid silver;
      }

      ul > li {
        display: grid;
        grid-template-columns: 90% 10%;
      }
    </style>
  </head>
  <body>
    <!--h1>PHP / MySQL. Help Request Planning</h1-->

    <?php
// phpinfo();

require __DIR__ . "/../../php/db.cred.php";
require __DIR__ . "/_db.utils.php";
require __DIR__ . "/_emails.utils.php";

$VERBOSE = false;

$MONTHS = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");

// Find current date (for the month)
if (false) {
    echo "Today is " . date("Y/m/d") . "<br>";
    echo "Today is " . date("Y.m.d") . "<br>";
    echo "Today is " . date("Y-m-d") . "<br>";
    echo "Today is " . date("l") . "<br>";
}

$adminPriv = false;
if (isset($_SESSION['ADMIN'])) {
    $adminPriv = $_SESSION['ADMIN'];
}
echo("Admin privileges: " . ($adminPriv ? "yes" : "no") . "<br/>");
$userId = '';
if (isset($_SESSION['USER_NAME'])) {
    $userId = $_SESSION['USER_NAME'];
}

$currentYear = date("Y");
// echo("Current year is " . $currentYear . ", next will be " . ($currentYear + 1) . "<br/>");
$currentMonth = date("m");

if (isset($_GET['year'])) {
    $currentYear = $_GET['year'];
}
if (isset($_GET['month'])) {
    $currentMonth = $_GET['month'];
}

$helpType = 'SAILING';
if (isset($_GET['type'])) {
    $helpType = $_GET['type'];
}
$lang = 'FR';
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
}

$operation = 'list';
if (isset($_POST['operation'])) {
    $operation = $_POST['operation'];
}

$option = null;
if (isset($_GET['option'])) {
    $option = $_GET['option'];
}


// echo ("This month (" . $currentYear . " - " . $MONTHS[$currentMonth - 1] . ") we have " . getNbDays($currentYear, $currentMonth) . " days.<br/>");

if (false) {
    $year = 1900;
    $month = 2;
    echo ("In $year, $month, there were " . getNbDays($year, $month) . " days.<br/>");

    $year = 2000;
    $month = 2;
    echo ("In $year, $month, there were " . getNbDays($year, $month) . " days.<br/>");

    $year = 1959;
    $month = 3;
    echo ("In $year, $month, there were " . getNbDays($year, $month) . " days.<br/>");

    $year = 2024;
    $month = 2;
    echo ("In $year, $month, there were " . getNbDays($year, $month) . " days.<br/>");
}

if ($option != null) {
    // Create a request ?
    echo("Option: " . $option . "<br/>" . PHP_EOL);
    // Create a request
    echo("<h2>" . (($lang == 'FR') ? "Cr&eacute;er une requ&ecirc;te" : "Create a request") . "</h2>" . PHP_EOL);
    echo("Coming..." . PHP_EOL);

    // From user (referent), boat, from-date, to-date, type, comment.
    // $userId = $_SESSION['USER_NAME'];
    // Creating entry for $userId
    echo("Creating entry for $userId");

} else if ($operation == 'list') {
    // echo ("<h2>Requests Planning for " . $MONTHS[$currentMonth - 1] . " " . $currentYear . " (3 months)</h2>");

    $requestsArray = getHelpRequests($dbhost, $username, $password, $database, $helpType, $VERBOSE);     

    if ($VERBOSE) {
        echo ("We have " . count($requestsArray) . " requests<br/>");
    }

    $from = $MONTHS[$currentMonth - 1] . " " . $currentYear;

    $firstDayOfMonth = $currentYear . "-" . $currentMonth . "-" . "01";

    $span = 3; // 3 months
    $currentMonth += ($span - 1);
    while ($currentMonth > 12) {
        $currentMonth -= 12;
        $currentYear += 1;
    }

    $to = $MONTHS[$currentMonth - 1] . " " . $currentYear;
    if ($lang == 'FR') {
        echo ("<h2>Planning des demandes d'aide de " . $from . " &agrave; " . $to . " </h2>");
    } else {
        echo ("<h2>Requests Planning from " . $from . " to " . $to . " </h2>");
    }

    $lastDayOfMonth = $currentYear . "-" . $currentMonth . "-" . getNbDays($currentYear, $currentMonth);

    echo("<ul>" . PHP_EOL);
    foreach($requestsArray as $request) {
        if ($VERBOSE) {
            echo("Fetching requests for " . $request->owner . "/" . $request->boat . " in " . $MONTHS[$currentMonth - 1] . " " . $currentYear . "<br/>" . PHP_EOL);
        }
        echo("<li>" . PHP_EOL);
        $memberArray = getMember($dbhost, $username, $password, $database, $request->owner, $VERBOSE);
        $ownerName = 'Unknown';
        if (count($memberArray) > 0) {
            $ownerName = $memberArray[0]->firstName . ' ' . $memberArray[0]->lastName;
        }
        $reqData = (($lang == 'FR') ? "De " : "By ") . $ownerName . 
                (($lang == 'FR') ? " sur " : " on ") . getBoatName($dbhost, $username, $password, $database, $request->boat, $VERBOSE);
        if ($request->to == null) {
            $reqData .=  (($lang == 'FR') ? ", le " : ", on ") . $request->from;
        } else {
            $reqData .=  (($lang == 'FR') ? ", de " : ", from ") . $request->from . 
                        (($lang == 'FR') ? ", &agrave; " : ", to ") . $request->to;
        }
        $reqData .= /*", type " . $request->type . */ ", " . $request->comment .
                "<form action='" . basename(__FILE__) . "' method='post'>" .
                "<input type='hidden' name='operation' value='reply'>" .
                "<input type='hidden' name='idx' value='" . $request->idx . "'>" .
                "<input type='submit' value='" . (($lang == 'FR') ? "Je viens !" : "I'm coming!") . "'>" .
                "</form>";
        echo $reqData;
        echo("</li>" . PHP_EOL);
    }
    echo ("</ul>" . PHP_EOL);

    echo("<hr/>" . PHP_EOL);
} else if ($operation == 'reply') {
    echo ("Managing request #" . $_POST['idx'] . "<br/>" . PHP_EOL);
    if ($lang == 'FR') {
        echo("Pour r&eacute;pondre &agrave; cette requ&ecirc;te, identifiez-vous au pr&eacute;alable, avec votre email");
    } else {
        echo("To answer this request, please identify yourself with your email");
    }

    $reqData = "";

    $reqData .= "<form action='" . basename(__FILE__) . "' method='post'>" .
                "<input type='hidden' name='operation' value='valid-email'>" .
                "<input type='hidden' name='idx' value='" . $request->idx . "'>" .
                (($lang == 'FR') ? "Votre email :" : "Your email:") .
                "<input type='email' name='user-email' placeholder='email'>" .
                "<input type='submit' value='OK'>" .
                "</form>";
    
    echo $reqData;
} else if ($operation == 'valid-email') {
    $userEmail = $_POST['user-email'];
    if ($VERBOSE) {
        echo("Email validation for " . $userEmail . "<br/>" . PHP_EOL);
    }

    $memberStatus = checkMemberShip($dbhost, $username, $password, $database, $userEmail, $VERBOSE);

    if ($VERBOSE) {
        echo("Member status for " . $userEmail . "<br/>");
        echo("Status: " . $memberStatus->status . "<br/>");
        echo("ErrNo: " . $memberStatus->errNo . "<br/>"); // int. O: Passe-Coque & Boat-Club, 1: Not Passe-Coque, 2: Not Boat-Club
        echo("ErrMess: " . $memberStatus->errMess . "<br/>");
    }

    if ($memberStatus->errNo == 1) { // Not Passe-Coque
        if ($lang == 'FR') {
            $txt = "Il semble que vous ne soyiez pas encore un membre de l'association...<br/>" .
                   "Cliquez <a href='/?lang=FR&nav-to=51' target='_blank'>ici</a> pour y adh&eacute;rer !";
        } else {
            $txt = "It seems that you're not yet a member of the association...<br/>" .
                   "Click <a href='/?lang=EN&nav-to=51' target='_blank'>here</a> to join!";
        }
        echo($txt);
    } else {
        // Membership OK, moving on
        $memberArray = getMember($dbhost, $username, $password, $database, $userEmail, $VERBOSE);
        $ownerName = 'Unknown';
        if (count($memberArray) > 0) {
            $ownerName = $memberArray[0]->firstName . ' ' . $memberArray[0]->lastName;
        }
        // Get request details (referent email, dates, etc)

        $htmlContent = "";
        if ($lang == 'FR') {
            $htmlContent = "Bonjour " . $ownerName . "<br/>" .
                           "Votre r&eacute;ponse va &ecirc;tre transime par email au r&eacute;f&eacute;rent du bateau qui vous re-contactera pour confirmation.<br/>" .
                           "Vous pouvez ajouter un commentaire &agrave; cet email :<br/>" .
                           "<form action='" . basename(__FILE__) . "' method='post' id='email-sender'>" .
                           "<input type='hidden' name='idx' value='" . $_POST['idx'] . "'>" .
                           "<input type='hidden' name='operation' value='send-email'>" .
                           "<input type='hidden' name='user-email' value='" . $userEmail . "'>" .
                           "<textarea rows='4' cols='50' name='comment' form='email-sender' placeholder='Vos commentaires...'></textarea><br/>" .
                           "Cliquer OK pour envoyer votre r&eacute;ponse " .
                           "<input type='submit' value='OK'>" .
                           "</form>";
        } else {
            $htmlContent = "Hi " . $ownerName . "<br/>" .
                           "Your reply will be transmitted to the boat's referent by email, who will reach out to you for confirmation.<br/>" .
                           "You can add a comment to this email :<br/>" .
                           "<form action='" . basename(__FILE__) . "' method='post' id='email-sender'>" .
                           "<input type='hidden' name='idx' value='" . $_POST['idx'] . "'>" .
                           "<input type='hidden' name='operation' value='send-email'>" .
                           "<input type='hidden' name='user-email' value='" . $userEmail . "'>" .
                           "<textarea rows='4' cols='50' name='comment' form='email-sender' placeholder='Vos commentaires...'></textarea><br/>" .
                           "Click OK to send your reply " .
                           "<input type='submit' value='OK'>" .
                           "</form>";
        }
        echo($htmlContent);
    }
    echo("<hr/>" . PHP_EOL);
} else if ($operation == 'send-email') {
    $requestId = $_POSRT['idx'];
    $userEmail = $_POSRT['user-email'];
    $userInput = $_POST['comment'];

    echo("Will send email from " . $userEmail . "<br/>" . PHP_EOL);
    echo("Request ID " . $requestId . "<br/>" . PHP_EOL);
    echo("User Message " . $userInput . "<br/>" . PHP_EOL);

    // TODO Send the email to the referent

} else {
    echo ("Unknown operation [" . $operation . "]" . PHP_EOL);
}
?>
  </body>        
</html>