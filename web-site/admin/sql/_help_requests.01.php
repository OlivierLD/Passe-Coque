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

    <link rel="stylesheet" href="../../fonts/font.01.css">
	<link rel="stylesheet" href="../../fonts/font.02.css">
	<!--link rel="stylesheet" href="./passe-coque.menu.css"-->
	<link rel="stylesheet" href="../../passe-coque.css" type="text/css"/>
    
    <style type="text/css">
      /* * {
        font-family: 'Courier New'
      } */

      tr > td {
        border: 1px solid silver;
      }

      ul > li {
        display: grid;
        grid-template-columns: 80% 20%;
        border: 1px solid silver;
        border-radius: 5px;
        padding: 5px 10px;
      }

      body {
        background: transparent;
      }
    </style>

  </head>

  <script type="text/javascript">
    const validateForm = () => { // Used below
        document.getElementById("submit-message").innerHTML = ''; // Clear

        let dateFrom = new Date(document.getElementById('from-date').value);
        let dateTo = new Date(document.getElementById('to-date').value);
        
        console.log(`Validation requested for ${dateFrom} and ${dateTo}`);
        if (dateTo < dateFrom) {
            let message = `Mauvaise chronologie, ${dateTo} est anterieur a ${dateFom}`;
            document.getElementById("submit-message").innerHTML = message;
            // console.log(message);
            // alert(message);
            return false;
        }
        // return (dateFrom <= dateTo);
    };
  </script>    

  <body>
    <!--h1>PHP / MySQL. Help Request Planning</h1-->

    <?php
// phpinfo();

require __DIR__ . "/../../php/db.cred.php";
require __DIR__ . "/_db.utils.php";
require __DIR__ . "/_emails.utils.php";

$VERBOSE = false; // Change at will

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
// echo("Admin privileges: " . ($adminPriv ? "yes" : "no") . "<br/>");
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

$operation = 'list';  // Default operation
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

// echo("operation:" . $operation . ", option:" . $option . "<br/>" . PHP_EOL);

if ($option != null) {
    // echo("Option: " . $option . "<br/>" . PHP_EOL);
    if ($option == 'request') {
        // Create a request ?
        // Create a request
        echo("<h2>" . (($lang == 'FR') ? "Cr&eacute;er une requ&ecirc;te" : "Create a request") . "</h2>" . PHP_EOL);
        // echo("Coming..." . PHP_EOL);

        // From user (referent), boat, from-date, to-date, type, comment.
        // $userId = $_SESSION['USER_NAME'];
        // Creating entry for $userId
        if ($lang == 'FR') {
            echo("Creation d'une requ&ecirc;te pour $userId");
        } else {
            echo("Creating entry for $userId");
        }
        $boats = getAllBoatsByReferent($dbhost, $username, $password, $database, $userId, false, $VERBOSE);

        if (false) { // List the boats for this referent
            echo ("<ul>" . PHP_EOL);
            foreach($boats as $boat) {
                echo("<li>$boat[0], $boat[1], $boat[2]</li>" . PHP_EOL);
            }
            echo ("</ul>" . PHP_EOL);
        }

    ?>

<form action="<?php echo basename(__FILE__); ?>"  onsubmit="return validateForm();" method="post">
    <input type="hidden" name="operation" value="create-request">
    <input type="hidden" name="lang" value="<?php echo($lang); ?>">
    <input type="hidden" name="origin-email" value="<?php echo($userId); ?>">
    <table>
        <tr>
            <td><?php echo(($lang != 'FR') ? "The boat" : "Le bateau"); ?></td>
            <td>
                <select name="boat-id">
                    <?php
                    foreach($boats as $boat) {
                        if ($lang != 'FR') {
                            echo ("<option value='" . $boat[0] ."'> \"" . $boat[1] . "\", " . "(" . $boat[2] . ")" . "</option>" . PHP_EOL);
                        } else {
                            echo ("<option value='" . $boat[0] ."'> \"" . $boat[1] . "\", " . "("  . $boat[2] . ")" . "</option>" . PHP_EOL);
                        }
                    }
                    ?>
                </select>    
            </td>
        </tr>
        <tr>
            <td><?php echo(($lang != 'FR') ? "Nature of the request" : "Nature de la requ&ecirc;te"); ?></td>
            <td>
                <select name="req-type">
                    <?php
                        if ($lang != 'FR') {
                            echo ("<option value='SAILING'>Navigation</option>" . PHP_EOL);
                        } else {
                            echo ("<option value='SAILING'>Navigation</option>" . PHP_EOL);
                        }
                        if ($lang != 'FR') {
                            echo ("<option value='SHIPYARD'>Shipyard</option>" . PHP_EOL);
                        } else {
                            echo ("<option value='SHIPYARD'>Chantier</option>" . PHP_EOL);
                        }
                    ?>
                </select>    
            </td>
        </tr>
        <tr><td><?php echo(($lang != 'FR') ? "From" : "Date de d&eacute;but"); ?></td><td><input type="date" id="from-date" name="from-date" required></td></tr>
        <tr><td><?php echo(($lang != 'FR') ? "To" : "Date de fin"); ?></td><td><input type="date" id="to-date" name="to-date" required></td></tr>
        <tr>
            <td style="vertical-align: top;">
                <?php 
                echo(($lang != 'FR') ? 
                    "Comments, questions..." : 
                    "Commentaires, questions..."); 
                 ?>
            </td>
            <td>
                <textarea cols="60" rows="10" name="comment-area" style="line-height: normal;"></textarea>
            </td>
        </tr>
    </table>
    <div id="submit-message"></div>

    <input type="submit" value="<?php echo(($lang != 'FR') ? "Submit request" : "Soumettre votre requ&ecirc;te"); ?>">
</form>    

    <?php
    // List existing requests for current user ($userId), with delete button
    /*
    $requests[$index]->idx = $table[0];
    $requests[$index]->owner = $table[1];
    $requests[$index]->boat = $table[2];
    $requests[$index]->created = $table[3];
    $requests[$index]->from = $table[4];
    $requests[$index]->to = $table[5];
    $requests[$index]->type = $table[6];
    $requests[$index]->comment = $table[7];
    */

    if ($lang == 'FR') {
        echo('Vos requ&ecirc;tes d&apos;aide d&eacute;j&agrave; existantes&nbsp;:<br/>' . PHP_EOL);
    } else {
        echo('Your already existing requests:<br/>' . PHP_EOL);
    }
    $userRequests = getHelpRequestByUserId($dbhost, $username, $password, $database, $userId, $VERBOSE);
    echo("<table>");
    echo("<tr><th>Idx</th><th>Owner</th><th>Boat</th><th>Created</th><th>From</th><th>To</th><th>Type</th><th>Comment</th></tr>" . PHP_EOL);
    foreach($userRequests as $request) {
        echo("<tr><td>" . $request->idx . 
                  "</td><td>" . $request->owner  . 
                  "</td><td>" . $request->boat  . 
                  "</td><td>" . $request->created  . 
                  "</td><td>" . $request->from  . 
                  "</td><td>" . $request->to  . 
                  "</td><td>" . $request->type  . 
                  "</td><td>" . /*utf8_encode*/($request->comment) .
                  "</td><td><form method='post' action='" . basename(__FILE__) . "'>" .
                                "<input type='hidden' name='operation' value='delete'>" .
                                "<input type='hidden' name='lang' value='" . $lang . "'>" .
                                "<input type='hidden' name='from' value='request'>" .
                                "<input type='hidden' name='idx' value='" . $request->idx . "'>" .
                                "<input type='submit' value='Delete'>" .
                        "</form>" .
             "</td></tr>" . PHP_EOL);  // Add delete button
    }
    echo("</table>");
    echo("<hr/>");

    } else if ($option == 'admin') {
        // General requests management. View, Create ?, Delete...
        $allRequests = getAllHelpRequests($dbhost, $username, $password, $database, $VERBOSE);
        echo("<table>");
        echo("<tr><th>Idx</th><th>Owner</th><th>Boat</th><th>Created</th><th>From</th><th>To</th><th>Type</th><th>Comment</th></tr>" . PHP_EOL);
        foreach($allRequests as $request) {
            echo("<tr><td>" . $request->idx . 
                      "</td><td>" . $request->owner  . 
                      "</td><td>" . $request->boat  . 
                      "</td><td>" . $request->created  . 
                      "</td><td>" . $request->from  . 
                      "</td><td>" . $request->to  . 
                      "</td><td>" . $request->type  . 
                      "</td><td>" . /*utf8_encode*/($request->comment) .
                      "</td><td><form method='post' action='" . basename(__FILE__) . "'>" .
                                    "<input type='hidden' name='operation' value='delete'>" .
                                    "<input type='hidden' name='lang' value='" . $lang . "'>" .
                                    "<input type='hidden' name='from' value='admin'>" .
                                    "<input type='hidden' name='idx' value='" . $request->idx . "'>" .
                                    "<input type='submit' value='Delete'>" .
                            "</form>" .
                 "</td></tr>" . PHP_EOL);  // Add delete button
        }
        echo("</table>");
    }

} else if ($operation == 'list') {
    // echo ("<h2>Requests Planning for " . $MONTHS[$currentMonth - 1] . " " . $currentYear . " (3 months)</h2>");

    $requestsArray = getHelpRequests($dbhost, $username, $password, $database, $helpType, $VERBOSE); // TODO Restrict on dates ?

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

    $currentDT = DateTime::createFromFormat("Y-m-d", sprintf("%04d-%02d-%02d", (int)date("Y"), (int)date("m"), (int)date("d")));

    echo("<ul style='padding-left: 1px;'>" . PHP_EOL);
    foreach($requestsArray as $request) {
        if ($VERBOSE) {
            echo("Fetching requests for " . $request->owner . "/" . $request->boat . " in " . $MONTHS[$currentMonth - 1] . " " . $currentYear . "<br/>" . PHP_EOL);
        }
        // TODO : Filter on $request->from, $request->to (nullable) in the span window (3 months)
        $requestFromDT = DateTime::createFromFormat("Y-m-d", $request->from);
        $requestToDT = null;
        if ($request->to != null) {
            $requestToDT = DateTime::createFromFormat("Y-m-d", $request->to);
        }
        $expired = ($currentDT > $requestFromDT ? true : false);
        if ($requestToDT != null) {
            $expired = ($currentDT > $requestToDT ? true : false);
        }

        if (true) { // Dates OK... Refine this.
            echo("<li>" . PHP_EOL);
            $memberArray = getMember($dbhost, $username, $password, $database, $request->owner, $VERBOSE);
            $ownerName = 'Unknown';
            if (count($memberArray) > 0) {
                $ownerName = $memberArray[0]->firstName . ' ' . $memberArray[0]->lastName;
            }
            $reqData = (($lang == 'FR') ? "De " : "By ") . ($ownerName) . 
                    (($lang == 'FR') ? " sur " : " on ") . getBoatName($dbhost, $username, $password, $database, $request->boat, $VERBOSE);
            if ($request->to == null) {
                $reqData .=  (($lang == 'FR') ? ", le " : ", on ") . $request->from;
            } else {
                $reqData .=  (($lang == 'FR') ? ", de " : ", from ") . $request->from . 
                            (($lang == 'FR') ? ", &agrave; " : ", to ") . $request->to;
            }
            if ($expired) {
                $reqData .= (($lang == 'FR') ? " (expir&eacute;...)" : "(expired...)");
            }
            $reqData .= /*", type " . $request->type . */ ", " . /*utf8_encode*/($request->comment) .
                    "<form action='" . basename(__FILE__) . "' method='post'>" .
                    "<input type='hidden' name='lang' value='" . $lang . "'>" .
                    "<input type='hidden' name='operation' value='reply'>" .
                    "<input type='hidden' name='idx' value='" . $request->idx . "'>" .
                    "<input type='hidden' name='type' value='" . $helpType . "'>" .
                    "<input type='submit' value='" . (($lang == 'FR') ? "Je viens !" : "I&apos;m coming!") . "'>" .
                    "</form>";
            echo $reqData;
            echo("</li>" . PHP_EOL);
        }
    }
    echo ("</ul>" . PHP_EOL);

    echo("<hr/>" . PHP_EOL);
} else if ($operation == 'reply') {
    $reqIdx = $_POST['idx'];
    $helpType = $_POST['type'];
    $lang = $_POST['lang'];
    if ($lang == 'FR') {
        if ($VERBOSE) {
            echo("Gestion de la requ&ecirc;te #" . $reqIdx . "<br/>" . PHP_EOL);
        }
        echo("Pour r&eacute;pondre &agrave; cette requ&ecirc;te, identifiez-vous au pr&eacute;alable, avec votre email.<br/>" . PHP_EOL);
    } else {
        if ($VERBOSE) {
            echo("Managing request #" . $reqIdx . "<br/>" . PHP_EOL);
        }
        echo("To answer this request, please identify yourself with your email.<br/>" . PHP_EOL);
    }

    $reqData = "";

    $reqData .= "<form action='" . basename(__FILE__) . "' method='post'>" .
                "<input type='hidden' name='operation' value='valid-email'>" .
                "<input type='hidden' name='lang' value='" . $lang . "'>" .
                "<input type='hidden' name='idx' value='" . $reqIdx . "'>" .
                "<input type='hidden' name='type' value='" . $helpType . "'>" .
                (($lang == 'FR') ? "Votre email :&nbsp;" : "Your email:&nbsp;") .
                "<input type='email' name='user-email' placeholder='email' size='40'>" .
                "<input type='submit' value='OK' style='margin: 5px;'>" .
                "</form>";
    
    echo $reqData;
} else if ($operation == 'valid-email') {
    $userEmail = $_POST['user-email'];
    $lang = $_POST['lang'];

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

    if ($memberStatus->errNo == MemberStatus::$NO_PASSE_COQUE_MEMBER) { // Not Passe-Coque
        if ($lang == 'FR') {
            $txt = "Il semble que vous ne soyiez pas encore un membre de l'association...<br/>" .
                   "Cliquez <a href='/?lang=FR&nav-to=51' target='_blank'>ici</a> pour y adh&eacute;rer !";
        } else {
            $txt = "It seems that you're not yet a member of the association...<br/>" .
                   "Click <a href='/?lang=EN&nav-to=51' target='_blank'>here</a> to join!";
        }
        echo($txt);
    } else {
        $helpType = $_POST['type'];
        // Membership OK, moving on
        $memberArray = getMember($dbhost, $username, $password, $database, $userEmail, $VERBOSE);
        $memberName = 'Unknown';
        if (count($memberArray) > 0) {
            $memberName = $memberArray[0]->firstName . ' ' . $memberArray[0]->lastName;
        }
        // Get request details (referent email, dates, etc)
        $reqIdx = $_POST['idx'];

        $htmlContent = "";
        if ($lang == 'FR') {
            $htmlContent = "Bonjour " . $memberName . ",<br/>" . // " (Request #" . $reqIdx . ")<br/>" .
                           "Votre r&eacute;ponse va &ecirc;tre transime par email au r&eacute;f&eacute;rent du bateau qui vous re-contactera pour confirmation.<br/>" .
                           "Vous pouvez ajouter un commentaire &agrave; cet email :<br/>" .
                           "<form action='" . basename(__FILE__) . "' method='post' id='email-sender'>" .
                           "<input type='hidden' name='lang' value='" . $lang . "'>" .
                           "<input type='hidden' name='idx' value='" . $reqIdx . "'>" .
                           "<input type='hidden' name='type' value='" . $helpType . "'>" .
                           "<input type='hidden' name='operation' value='send-email'>" .
                           "<input type='hidden' name='user-email' value='" . $userEmail . "'>" .
                           "<textarea rows='4' cols='50' name='comment' form='email-sender' placeholder='Vos commentaires...'></textarea><br/>" .
                           "Cliquer OK pour envoyer votre r&eacute;ponse " .
                           "<input type='submit' value='OK' style='margin: 5px;'>" .
                           "</form>";
        } else {
            $htmlContent = "Hi " . $memberName . ",<br/>" . // " (Request #" . $reqIdx . ")<br/>" .
                           "Your reply will be transmitted to the boat's referent by email, who will reach out to you for confirmation.<br/>" .
                           "You can add a comment to this email :<br/>" .
                           "<form action='" . basename(__FILE__) . "' method='post' id='email-sender'>" .
                           "<input type='hidden' name='lang' value='" . $lang . "'>" .
                           "<input type='hidden' name='idx' value='" . $reqIdx . "'>" .
                           "<input type='hidden' name='type' value='" . $helpType . "'>" .
                           "<input type='hidden' name='operation' value='send-email'>" .
                           "<input type='hidden' name='user-email' value='" . $userEmail . "'>" .
                           "<textarea rows='4' cols='50' name='comment' form='email-sender' placeholder='Vos commentaires...'></textarea><br/>" .
                           "Click OK to send your reply " .
                           "<input type='submit' value='OK' style='margin: 5px;'>" .
                           "</form>";
        }
        echo($htmlContent);
    }
    echo("<hr/>" . PHP_EOL);
} else if ($operation == 'send-email') {
    $requestId = $_POST['idx'];
    $userEmail = $_POST['user-email'];
    $userInput = $_POST['comment'];
    $helpType = $_POST['type'];
    $lang = $_POST['lang'];

    if ($lang == 'FR') {
        echo("Envoi d'un message de la part de " . $userEmail . "<br/>" . PHP_EOL);
        echo("Identifiant de la requ&ecirc;te&nbsp;: " . $requestId . "<br/>" . PHP_EOL);
        echo("Avec le message suivant&nbsp;:<br/><i>" . $userInput . "</i><br/>" . PHP_EOL);
        echo("<hr/>". PHP_EOL);
    } else {
        echo("Will send email on behalf of " . $userEmail . "<br/>" . PHP_EOL);
        echo("Request ID " . $requestId . "<br/>" . PHP_EOL);
        echo("With the following message:<br/><i>" . $userInput . "</i><br/>" . PHP_EOL);
        echo("<hr/>". PHP_EOL);
    }

    $reqDetails = getHelpRequestById($dbhost, $username, $password, $database, $requestId, $VERBOSE);
    if (false) {
        echo("<br/>Dumping result\n");
        var_dump($reqDetails);
        echo("\n<br/>");
    }

    if ($lang == 'FR') {
        $content = "Une r&eacute;ponse de " . $userEmail . ", pour la requ&ecirc;te #" . $requestId . " (" . $reqDetails[0]->from . 
        " - " . $reqDetails[0]->to . ", " . $reqDetails[0]->type . "), \n<br/>" . $userInput . "";
    } else {
        $content = "A reply from " . $userEmail . ", for request #" . $requestId . " (" . $reqDetails[0]->from . 
        " - " . $reqDetails[0]->to . ", " . $reqDetails[0]->type . "), \n<br/>" . $userInput . "";
    }

    // echo("Email content:" . $content . "<br/>" . PHP_EOL);
    if ($lang == 'FR') {
        echo("Envoi d'un email &agrave; " . $reqDetails[0]->owner . ", (lang=" . $lang . ")...<br/>" . PHP_EOL);
    } else {
        echo("Sending email to " . $reqDetails[0]->owner . ", (lang=" . $lang . ")...<br/>" . PHP_EOL);
    }
    // Send the email to the referent
    sendEmail($reqDetails[0]->owner, 
              "Help Request #" . $requestId, 
              $content, 
              $lang, 
              false, 
              true,      // cc the board
              $VERBOSE);
    echo("<a href='" . basename(__FILE__) . "?lang=" . $lang . "&type=" . $helpType . "'>" . ($lang == 'FR' ? "Retour" : "Back") . "</a>");

} else if ($operation == 'create-request') {
    $fromEmail = $_POST['origin-email'];
    $boatId = $_POST['boat-id'];
    $reqType = $_POST['req-type'];
    $dateFrom = $_POST['from-date'];
    $dateTo = $_POST['to-date'];
    $lang = $_POST['lang'];
    $comments = $_POST['comment-area'];
    $escapedComment = str_replace("'", "\'", $comments); // Escape !!
    /*
      INSERT INTO HELP_REQUESTS (ORIGIN_EMAIL, BOAT_ID, FROM_DATE, TO_DATE, HELP_TYPE, MISC_COMMENT) VALUES
      ('olivier@lediouris.net', 'don-pedro', STR_TO_DATE('2024-09-08', '%Y-%m-%d'), STR_TO_DATE('2024-09-30', '%Y-%m-%d'), 'SHIPYARD', 'On a besoin de 3 personnes pour bricoler sur le bateau pendant le mois de septembre');
    */
    $sqlStmt = "INSERT INTO HELP_REQUESTS (ORIGIN_EMAIL, BOAT_ID, FROM_DATE, TO_DATE, HELP_TYPE, MISC_COMMENT) VALUES " . 
               "('$fromEmail', '$boatId', STR_TO_DATE('$dateFrom', '%Y-%m-%d'), STR_TO_DATE('$dateTo', '%Y-%m-%d'), '$reqType', '$escapedComment')";

    if ($lang == 'FR') {
        echo("Cr&eacute;ation de la requ&ecirc;te...<br/>" . PHP_EOL);
    } else {
        echo("Request creation...<br/>" . PHP_EOL);
    }
    executeSQL($dbhost, $username, $password, $database, $sqlStmt, $VERBOSE);

    echo("<a href='" . "?lang=" . $lang . "&option=request'>" . ($lang == 'FR' ? 'Retour' : 'Back') . "</a>" . PHP_EOL);

} else if ($operation == 'delete') {
    $idx = $_POST['idx'];
    $lang = $_POST['lang'];
    $sqlStmt = "DELETE FROM HELP_REQUESTS WHERE IDX = " . $idx . ";";
    executeSQL($dbhost, $username, $password, $database, $sqlStmt, $VERBOSE);

    $from = $_POST['from'];
    echo("<a href='" . basename(__FILE__) . "?option=" . $from . "&lang=" . $lang . "'>Back to list</a>" . PHP_EOL);
} else {
    echo ("Unknown operation [" . $operation . "]" . PHP_EOL);
}
?>
  </body>        
</html>