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
   ! Make a reservation. Step 2
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Boat Club reservations</title>
	<link rel="icon" type="image/png" href="/logos/LOGO_PC_no_txt.png">
	<!--link rel="stylesheet" href="/css/stylesheet.css" type="text/css"/-->
	<link rel="stylesheet" href="/fonts/font.01.css">
	<link rel="stylesheet" href="/fonts/font.02.css">

	<link rel="stylesheet" href="/passe-coque.menu.css">
	<link rel="stylesheet" href="/passe-coque.css" type="text/css"/>
    <style type="text/css">
      a:link, a:visited {
        background-color: #f44336;
        color: white;
        padding: 10px 20px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        border-radius: 5px;
      }

      a:hover, a:active {
        background-color: red;
      }
    </style>
	<script type="text/javascript" src="/passe-coque.js"></script>

    <script type="text/javascript">
        let enableExtraCrewNumber = (rb) => {
            console.log(`enableExtraCrewNumber: ${rb}`);
            document.getElementById('extra-crew-number').disabled = !rb;
            document.getElementById('extra-crew-message').style.display = (rb ? 'inline-block' : 'none');
        };
    </script>
  </head>

  <script type="text/javascript">
    const validateForm = () => {
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

  <?php
    $lang = 'FR';
    if (isset($_GET['lang'])) {
        $lang = $_GET['lang'];
    }
    $admin = false;
    if (isset($_GET['admin'])) {
        $admin = ($_GET['admin'] == 'true');
    }

    $userId = '';
    if (isset($_SESSION['USER_NAME'])) {
        $userId = $_SESSION['USER_NAME'];
    }

    // echo "-------<br/>";
    // var_dump($_SESSION);
    // echo "-------<br/>";
  ?>

  <body>
    <?php
    if ($lang != 'FR') {
    ?>
    <h2>Make a reservation</h2>
    <?php
    } else {
    ?>
    <h2>Faire une r&eacute;servation</h2>
    <?php
    }
    ?>
    <?php
// phpinfo();

require __DIR__ . "/../../php/db.cred.php";
require __DIR__ . "/_db.utils.php";
require __DIR__ . "/_emails.utils.php";

$VERBOSE = false;

class BoatDetailRef {
    public $referentEmail;
    public $boatId;
    public $boatName;
    public $boatType;
    public $boatBase;
}

function bookTheBoat(string $dbhost,
                     string $username,
                     string $password,
                     string $database,
                     string $userId,
                     string $boatId,
                     string $fromDate,
                     string $toDate,
                     string $comments,
                     bool $verbose,
                     string $lang) : void {
    $sql = "INSERT INTO BC_RESERVATIONS (EMAIL, BOAT_ID, FROM_DATE, TO_DATE, MISC_COMMENT) VALUES
    ('$userId', '$boatId', STR_TO_DATE('$fromDate', '%Y-%m-%d'), STR_TO_DATE('$toDate', '%Y-%m-%d'), '$comments');";

    try {
        if ($verbose) {
            echo("Will connect on ".$database." ...<br/>");
        }
        $link = new mysqli($dbhost, $username, $password, $database);

        if ($link->connect_errno) {
            echo("Oops, errno:".$link->connect_errno."...<br/>");
            die("Connection failed: " . $conn->connect_error); // TODO Throw an exception
        } else {
            if ($verbose) {
                echo("Connected.<br/>");
            }
        }

        if ($verbose) {
            echo ("Executing [" . $sql . "]<br/>");
        }
        if (true) { // Do perform ?
            if ($link->query($sql) === TRUE) {
                if ($verbose) {
                    if ($lang != 'FR') {
                        echo "OK. Reservation creation performed successfully<br/><hr/>" . PHP_EOL;
                    } else {
                        echo "OK. R&eacute;servation cr&eacute;e avec succ&egrave;s.<br/><hr/>" . PHP_EOL;
                    }
                }
            } else {
              echo "ERROR executing: " . $sql . "<br/>" . $link->error . "<br/>";
            }
        } else {
            echo "Stby<br/>" . PHP_EOL;
        }
        // On ferme !
        $link->close();
        if ($verbose) {
            echo("Closed DB<br/>".PHP_EOL);
        }
    } catch (Throwable $e) {
    echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }
}

function getBoatDetails(string $dbhost, string $username, string $password, string $database, string $boatId, bool $verbose) : array {
    $sql = "SELECT EMAIL, BOAT_NAME, BOAT_TYPE, BASE FROM BOATS_AND_REFERENTS WHERE BOAT_ID = '$boatId';";
    $details = array();
    $index = 0;

    try {
        if ($verbose) {
            echo("Will connect on ".$database." ...<br/>");
        }
        $link = new mysqli($dbhost, $username, $password, $database);

        if ($link->connect_errno) {
            echo("Oops, errno:".$link->connect_errno."...<br/>");
            die("Connection failed: " . $conn->connect_error); // TODO Throw an exception
        } else {
            if ($verbose) {
                echo("Connected.<br/>");
            }
        }

        if ($verbose) {
            echo ("Executing [" . $sql . "]");
        }
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>");
        }
        while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
            $details[$index] = new BoatDetailRef();
            $details[$index]->referentEmail = $table[0];
            $details[$index]->boatId = $boatId;
            $details[$index]->boatName = $table[1];
            $details[$index]->boatType = $table[2];
            $details[$index]->boatBase = $table[3];
            $index++;
        }
        // On ferme !
        $link->close();
        if ($verbose) {
            echo("Closed DB<br/>".PHP_EOL);
        }
    } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }

    return $details;
}

function composeEmailToReferent(BoatDetailRef $boatDetails, Member $requester, Member $refDetails, string $from, string $to, string $lang='FR'): string {
    $messContent = "";

    if (false) {
        echo("<br/>----------<br/>");
        var_dump($boatDetails);
        echo("<br/>----------<br/>");
        var_dump($requester);
        echo("<br/>----------<br/>");
        var_dump($refDetails);
        echo("<br/>----------<br/>");
    }

    if ($lang == 'FR') {
        $messContent =
        "Bonjour " . $refDetails->firstName . ",\n" .
        "L'adh&eacute;rent " . $requester->firstName . ' ' . $requester->lastName . " souhaite r&eacute;server le bateau \"" . $boatDetails->boatName . "\" du " . $from . " au " . $to . ".\n\n" .
        "Nous vous invitons à prendre contact avec " . $requester->firstName . ' ' . $requester->lastName . " dans les meilleurs d&eacute;lais afin de confirmer ou annuler la r&eacute;servation.\n" .
        "La mise &agrave; jour du planning est disponible depuis votre espace Admin.\n\n" .
        "Voici les coordonn&eacute;es de " . $requester->firstName . ' ' . $requester->lastName . " :\n" .
        "- Email &agrave; " . $requester->email . "\n" .
        "- T&eacute;l&eacute;phone au " . $requester->telephone . "\n\n" .
        "- L'&eacute;quipe Passe-Coque";
    } else {
        $messContent =
        "Hello " . $refDetails->firstName . ",\n" .
        "The member " . $requester->firstName . ' ' . $requester->lastName . " wants to book the boat \"" . $boatDetails->boatName . "\" from " . $from . " to " . $to . ".\n\n" .
        "Please get in touch with " . $requester->firstName . ' ' . $requester->lastName . " at your earliest convenience in order to confirm or cancel the reservation.\n" .
        "Updating the planning can be done fromn your Admin space.\n\n" .
        "Here is how to reach " . $requester->firstName . ' ' . $requester->lastName . " :\n" .
        "-Email  " . $requester->email . "\n" .
        "-Telephone " . $requester->telephone . "\n\n" .
        "- The Passe-Coque team";
    }
    return $messContent;
}

function composeEmailToRequester(array $boatDetails, Member $requester, string $from, string $to, string $dbhost, string $username, string $password, string $database, string $lang='FR', bool $verbose=false): string {
    $messContent = "";
    if ($lang == 'FR') {
        $messContent =
        "Bonjour " . $requester->firstName . ", \n" .
        "Votre demande de réservation pour le voilier \"" . $boatDetails[0]->boatName . "\" au départ de " . $boatDetails[0]->boatBase . " du " . $from . " au " . $to . " a été enregistrée.\n" .
        "Un email de demande de confirmation a été envoyé au" . (count($boatDetails) > 1 ? "x" : "") . " référent" . (count($boatDetails) > 1 ? "s" : "") . " du bateau :\n";

        foreach($boatDetails as $detail) {
            $ref = getMember($dbhost, $username, $password, $database, $detail->referentEmail, $verbose);
            $messContent .= ("- " . $ref[0]->firstName . " " . $ref[0]->lastName . ", email " . $ref[0]->email . ", téléphone : " . ($ref[0]->telephone != null ? $ref[0]->telephone : "--") . "\n");
        }
        $messContent .= "En cas d'absence de réponse, vous pouvez également nous envoyer un email à : pcc@passe-coque.com.\n\n" .
                       "- L'équipe Passe-Coque\n";
    } else {
        $messContent =
        "Hello " . $requester->firstName . ", \n" .
        "Your reservation request for the boat \"" . $boatDetails[0]->boatName . "\" starting in " . $boatDetails[0]->boatBase . " from " . $from . " to " . $to . " has been recorded.\n" .
        "A confirmation request has been sent to the referent" . (count($boatDetails) > 1 ? "s" : "") . " of the boat:\n";

        foreach($boatDetails as $detail) {
            $ref = getMember($dbhost, $username, $password, $database, $detail->referentEmail, $verbose);
            $messContent .= ("- " . $ref[0]->firstName . " " . $ref[0]->lastName . ", email " . $ref[0]->email . ", telephone : " . ($ref[0]->telephone != null ? $ref[0]->telephone : "--") . "\n");
        }
        $messContent .= "In case you do not get a reply, you can also send an email to: pcc@passe-coque.com.\n\n" .
                       "- The Passe-Coque team\n";
    }
    return $messContent;
}

if (isset($_POST['operation'])) {

    $operation = $_POST['operation'];
    if ($operation == 'booking') {  // Submit a booking request
        $userId = $_POST['email'];
        $boatId = $_POST['boat-id'];
        $fromDate = $_POST['from-date'];
        $toDate = $_POST['to-date'];
        $comments = $_POST['comment-area'];

        $acceptExtraCrew = ($_POST['extra-crew'] == 'yes');
        $nbExtraCrew = $_POST['extra-crew-number'];

        // 0 - Check chronology
        $dateFrom = strtotime($fromDate);
        $dateTo = strtotime($toDate);

        // https://www.geeksforgeeks.org/comparing-two-dates-in-php/

        if (false) { // Just a test
            $date1 = "2011-10-26";
            $date2 = "2011-10-24";

            // Use strtotime() function to convert
            // date into dateTimestamp
            $dateTimestamp1 = strtotime($date1);
            $dateTimestamp2 = strtotime($date2);

            // Compare the timestamp date
            if ($dateTimestamp1 > $dateTimestamp2) {
                echo "$date1 is later than $date2 <br/>";
            } else {
                echo "$date1 is older than $date2 <br/>";
            }
            echo("<hr/>" . PHP_EOL);
        }

        if ($VERBOSE) {
            if ($lang != 'FR') {
                echo("Checking chronology from " . date('Y-m-d', $dateFrom) . " to " . date('Y-m-d', $dateTo) . "<br/>" . PHP_EOL);
            } else {
                echo("V&eacute;rification des dates, de " . date('Y-m-d', $dateFrom) . " &agrave; " . date('Y-m-d', $dateTo) . "<br/>" . PHP_EOL);
            }
            if ($dateFrom > $dateTo) {
                if ($lang != 'FR') {
                    echo "$fromDate after $toDate <br/>";
                } else {
                    echo "$fromDate post&eacute;rieur &agrave; $toDate <br/>";
                }
            } else {
                if ($lang != 'FR') {
                    echo "$fromDate before $toDate <br/>";
                } else {
                    echo "$fromDate ant&eacute;rieur &agrave; $toDate <br/>";
                }
            }
        }

        // echo("From < To : $fromDate < $toDate : $dateFrom < $dateTo : " . (($dateFrom < $dateTo) ? "true" : "false") . ", v2 (needs true to move on): " . (($dateTo >= $dateFrom) ? "true" : "false") . "<br/>");

        if (($dateTo >= $dateFrom)) {
            // 1 - Check Membership
            $status = checkMemberShip($dbhost, $username, $password, $database, $userId, $VERBOSE);
            if ($VERBOSE) {
                var_dump($status);
            }
            echo "<br/>" . PHP_EOL;
            if ($status->status) { // Passe-Coque (1) AND Boat-Club (2)
                if ($VERBOSE) {
                    echo ("Status OK, checking $boatId's availability between $fromDate and $toDate .<br/>" . PHP_EOL);
                }
                // 2 - Check boat Availability
                $boatAvailability = checkBoatAvailability($dbhost, $username, $password, $database, $boatId, $fromDate, $toDate, $VERBOSE);
                if ($boatAvailability->status) {
                    // 3 - Proceed, and send emails
                    if ($VERBOSE) {
                        if ($lang != 'FR') {
                            echo("Boat availability OK, proceeding.<br/>" . PHP_EOL);
                        } else {
                            echo("Le bateau est disponible, on continue...<br/>" . PHP_EOL);
                        }
                    }
                    // 3-1 Book
                    $escapedComment = str_replace("'", "\'", $comments); // Escape !!

                    // TODO Manage Extra Crew Members !!

                    bookTheBoat($dbhost, $username, $password, $database, $userId, $boatId, $fromDate, $toDate, $escapedComment, $VERBOSE, $lang);
                    // 3-2 Emails
                    $details = getBoatDetails($dbhost, $username, $password, $database, $boatId, $VERBOSE);
                    $member = getMember($dbhost, $username, $password, $database, $userId, $VERBOSE); // Requester

                    // 3-2-1 Referent(s) and PCC
                    if ($lang != 'FR') {
                        $message = $member[0]->firstName . " " . $member[0]->lastName . "($userId) wants to reserve \"" . $details[0]->boatName . "\" from " . $fromDate . " to " . $toDate .
                                ". As a referent of the boat your insight is required.";
                    } else {
                        $message = $member[0]->firstName . " " . $member[0]->lastName . " ($userId) veut r&eacute;server \"" . $details[0]->boatName . "\" du " . $fromDate . " au " . $toDate .
                                ". En tant que r&eacute;f&eacute;rent du bateau, votre intervention est requise.";
                    }
                    foreach ($details as $detail) {
                        try {
                            $refDetails = getMember($dbhost, $username, $password, $database, $detail->referentEmail, $VERBOSE);
                            $refMess = composeEmailToReferent($detail, $member[0], $refDetails[0], $fromDate, $toDate, $lang);
                            sendEmail($detail->referentEmail, "Reservation Boat Club", $refMess, $lang, false, false, $VERBOSE);
                        } catch (Throwable $e) {
                            echo "Captured Throwable for composeEmailToReferent : " . $e->getMessage() . "<br/>" . PHP_EOL;
                        }
                    }
                    sendEmail("pcc@passe-coque.com", "Reservation Boat Club", $message, $lang, false, false, false);
                    // 3-2-2 Requester.
                    try {
                        $reqMess = composeEmailToRequester($details, $member[0], $fromDate, $toDate, $dbhost, $username, $password, $database, $lang, $VERBOSE);
                        $subject = ($lang == 'FR' ? "Votre Réservation au Boat Club" : "Your Boat Club Reservation");
                        // echo ("Sending<br/>" . $reqMess . "<br/>With subject [" . $subject . "]<br/>");
                        sendEmail($userId, $subject, $reqMess, $lang, false, false, $VERBOSE);
                    } catch (Throwable $e) {
                        echo "Captured Throwable for composeEmailToRequester : " . $e->getMessage() . "<br/>" . PHP_EOL;
                    }

                    if ($lang != 'FR') {
                        // sendEmail($userId, "Your Boat Club Reservation",
                        //         "Hello " . $member[0]->firstName . ",<br/>" .
                        //         "Your reservation request for the " . $details[0]->boatType . " \"" . $details[0]->boatName . "\" based in " . $details[0]->boatBase . " from $fromDate to $toDate has been recorded successfully!\n" .
                        //         "The referent of the boat can be contacted at the following email address: $detail->referentEmail \n" .
                        //         "Please do <a href='mailto:pcc@passe-coque.com'>re-contact us</a> if you do not hear from us within the next few days.<br/><br/>" .
                        //         "- The Passe-Coque team.", $lang, false, false, true);
                        ?>
                        <p>
                            Your request is recorded. You will receive a
                            email summarizing the information: date(s), boat, base, number of crew, equipment to be provided and
                            other information, contact details of the contact person to contact for access, handling and restitution
                            of the boat.<br/>
                            If there is no response within 24/48 hours, you can contact us at the
                            following email address: pcc@passe-coque.com.<br/>
                            The same AR was sent to the referent to inform him that he must accept or reject the reservation request.<br/>
                            The reservation planning should be updated (refresh if needed).
                        </p>
                        <a href="<?php echo(basename(__FILE__) . "?lang=EN") ?>">Back</a>.
                        <?php
                    } else {
                        // sendEmail($userId, "Votre R&eacute;servation au Boat Club",
                        //         "Bonjour " . $member[0]->firstName . ",<br/>" .
                        //         "Votre demande de r&eacute;servation pour le " . $details[0]->boatType . " \"" . $details[0]->boatName . "\" bas&eacute; &agrave; " . $details[0]->boatBase . " du $fromDate au $toDate a bien &eacute;t&eacute; enregistr&eacute;e !\n" .
                        //         "Le r&eacute;f&eacute;rent du bateau peut &ecirc;tre contact&eacute; &agrave; l'adresse email suivante : $detail->referentEmail \n" .
                        //         "Merci de <a href='mailto:pcc@passe-coque.com'>nous recontacter</a> si vous n'avez pas de nos nouvelles dans les prochains jours.<br/><br/>" .
                        //         "- L'&eacute;quipe Passe-Coque.", $lang, false);
                        ?>
                        <p>
                            Votre demande de r&eacute;servation est enregistr&eacute;e. Vous allez recevoir un
                            mail r&eacute;capitulant les informations : date(s), bateau, base, nombre d'&eacute;quipiers, mat&eacute;riel &agrave; pr&eacute;voir et
                            autres mentions, coordonn&eacute;es du r&eacute;f&eacute;rent &agrave; contacter pour l'acc&egrave;s, la prise en main et la restitution
                            du bateau.<br/>
                            En cas d'absence de r&eacute;ponse sous 24/48 heures, vous pouvez contacter nous contacter &agrave; l'adresse
                            mail suivante : pcc@passe-coque.com.<br/>
                            Le m&ecirc;me AR est envoy&eacute; au r&eacute;f&eacute;rent pour l'informer qu'il doit accepter ou refuser la demande de r&eacute;servation.<br/>
                            Le planning des r&eacute;servations doit &ecirc;tre mis &agrave; jour (faites un refresh si n&eacute;cessaire).
                        </p>
                        <a href="<?php echo(basename(__FILE__) . "?lang=FR") ?>">Retour</a>.
                        <?php
                    }
                    if ($admin) {
                        ?>
                        <a href="_reservations.01.php">Query Reservation Screen</a>
                        <?php
                    }
                } else {
                    if ($lang != 'FR') {
                        echo ("Reservation conflict:<br/>" . PHP_EOL);
                    } else {
                        echo ("Conflict de r&eacute;servation (pas dispo):<br/>" . PHP_EOL);
                    }
                    echo ($boatAvailability->message);
                }
            } else {
                // Membership problem
                // TODO check $status->errNo (1 or 2)
                // Passe-Coque (1) AND Boat-Club (2)
                if ($lang != 'FR') {
                    echo("There is a membership problem for $userId : " . $status->errMess . ". Click the button below to subscribe!<br/>" . PHP_EOL);
                    echo("To make a reservation, you must be a member of Passe-Coque and of the Boat&nbsp;Club.<br/>" . PHP_EOL);
                    if ($status->errNo == 1) {
                        echo("You are not a member of Passe-Coque. Please join Passe-Coque first.<br/>" . PHP_EOL);
                        ?>
                        <!--button onclick="clack_pcc('_4');" class="pc-button" style="margin: 5px;">Join the boat&nbsp;club</button-->
                        <a href="https://passe-coque.com/?lang=EN&nav-to=51" target="_PARENT">Join Passe-Coque</a>
                        <?php
                    } else if ($status->errNo == 2) {
                        echo("You are a member of Passe-Coque, but not a member of the Boat&nbsp;Club. Please join the Boat&nbsp;Club first.<br/>" . PHP_EOL);
                        ?>
                        <!--button onclick="clack_pcc('_4');" class="pc-button" style="margin: 5px;">Join the boat&nbsp;club</button-->
                        <a href="https://passe-coque.com/boat-club/?lang=EN&nav-to=4" target="_PARENT">Join the boat&nbsp;club</a>
                        <?php
                    } else {
                        // Other (wierd)
                        echo("Please contact Passe-Coque to resolve the membership problem.<br/>" . PHP_EOL);
                    }
                } else {
                    echo("Probl&egrave;me d'adh&eacute;sion pour $userId : " . $status->errMess . ". Cliquez le bouton pour adh&eacute;rer !<br/>" . PHP_EOL);
                    echo("Pour soumettre une r&eacute;servation, vous devez &ecirc;tre membre de Passe-Coque et du Boat&nbsp;Club.<br/>" . PHP_EOL);
                    if ($status->errNo == 1) {
                        echo("Vous n'&ecirc;tes pas membre de Passe-Coque. Merci d'adh&eacute;rer.<br/>" . PHP_EOL);
                        ?>
                        <!--button onclick="clack_pcc('_4');" class="pc-button" style="margin: 5px;">Join the boat&nbsp;club</button-->
                        <a href="https://passe-coque.com/?lang=FR&nav-to=51" target="_PARENT">Adh&eacute;rer &agrave; Passe-Coque</a>
                        <?php
                    } else if ($status->errNo == 2) {
                        echo("Vous &ecirc;tes membre de Passe-Coque, mais pas du Boat&nbsp;Club. Merci d'adh&eacute;rer au Boat&nbsp;Club.<br/>" . PHP_EOL);
                        ?>
                        <!--button onclick="clack_pcc('_4');" class="pc-button" style="margin: 5px;">Join the boat&nbsp;club</button-->
                        <a href="https://passe-coque.com/boat-club/?lang=FR&nav-to=4" target="_PARENT">Adh&eacute;rer au boat&nbsp;club</a>
                        <?php
                    } else {
                        // Other (bizarre)
                        echo("Merci de contacter Passe-Coque pour r&eacute;gler le probl&egrave;me d'adh&eacute;sion.<br/>" . PHP_EOL);
                    }
                }
            }
        } else {
            if ($lang != 'FR') {
                echo("Bad chronology: to-date " . $toDate . " ($dateTo)" . " is before from-date " . $fromDate . " ($dateFrom)" . "<br/>" . PHP_EOL);
            } else {
                echo("Mauvaise chronologie dans vos dates : la date de fin " . $toDate . " ($dateTo)" . " est ant&eacute;rieure &agrave; la date de d&eacute;but " . $fromDate . " ($dateFrom)" . "<br/>" . PHP_EOL);
            }
        }
    } else {
        echo ("Unknown operation [$operation]");
    }
} else {

    // Display Booking form

    $boatsArray = getBoats($dbhost, $username, $password, $database, $VERBOSE);
    // Filter list on 'CLUB'
    $boatsOfTheClub = array();
    $nbClub = 0;
    foreach ($boatsArray as $boat) {
        if ($boat->category == 'CLUB') {
            $boatsOfTheClub[$nbClub] = $boat;
            $nbClub++;
        }
    }
?>
<p>
<?php echo(($lang != 'FR') ? "Please fill out the following form:" : "Merci de remplir le formulaire suivant :"); ?>
</p>

<!-- Booking form -->
<form action="<?php echo basename(__FILE__); ?>"  onsubmit="return validateForm();" method="post">
    <input type="hidden" name="operation" value="booking">
    <!-- User Name, Boat Id, Date From, Date To -->
    <table>
        <tr>
            <td><?php if ($lang != 'FR') { echo("Your Email Address"); } else { echo("Votre adresse email"); } ?></td>
            <td><input type="email" name="email" size="40" value="<?php echo $userId; ?>" required></td>
        </tr>
        <tr>
            <td><?php echo(($lang != 'FR') ? "The boat" : "Le bateau"); ?></td>
            <td>
                <select name="boat-id">
                    <?php
                    foreach($boatsOfTheClub as $boat) {
                        if ($lang != 'FR') {
                            echo ("<option value='" . $boat->id ."'>" . $boat->type . " \"" . $boat->name . "\", " . "(" . $boat->base . ")" . "</option>" . PHP_EOL);
                        } else {
                            echo ("<option value='" . $boat->id ."'>" . $boat->type . " \"" . $boat->name . "\", " . "("  . $boat->base . ")" . "</option>" . PHP_EOL);
                        }
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
                    "Project (navigation zone)<br/>Crew list (first and last name of each crew member)<br/>Comments, questions..." :
                    "Projet (zone de navigation)<br/>Liste d'&eacute;quipage (nom et pr&eacute;nom de chacun)<br/>Commentaires, questions...");
                 ?>
            </td>
            <td>
                <textarea cols="60" rows="10" name="comment-area" style="line-height: normal;">
Projet et Zone de navigation :&#13;
. . .
&#13;
Équipage :&#13;
- Joe Shmow, toto@laflotte.bzh&#13;
- . . .&#13;
&#13;
Questions & commentaires :&#13;
On va partir avec la marée, on reviendra si on y pense.
               </textarea>
            </td>
        </tr>
        <tr>
            <td>
                <?php echo(($lang != 'FR') ? "Would you accept extra crew members?" : "Acceptez-vous des &eacute;quipiers suppl&eacute;mentaires ?"); ?>
            </td>
            <td>
                <input type="radio" id="extra-crew-yes" name="extra-crew" value="yes" onchange="enableExtraCrewNumber(true)" checked>
                <label for="extra-crew-yes"><?php echo(($lang != 'FR') ? "Yes" : "Oui"); ?></label>
                <input type="radio" id="extra-crew-no" name="extra-crew" value="no" onchange="enableExtraCrewNumber(false)">
                <label for="extra-crew-no"><?php echo(($lang != 'FR') ? "No" : "Non"); ?></label>
                <div id="extra-crew-message" style="display: inline-block; margin-left: 10px;">
                    <?php echo(($lang != 'FR') ? " - How many?" : " - Combien ?"); ?>
                    <input type="number" id="extra-crew-number" name="extra-crew-number" value="1" min="1" max="6" style="width: 50px;">
                </div>
            </td>
        </tr>
    </table>
    <div id="submit-message"></div>

    <input type="submit" value="<?php echo(($lang != 'FR') ? "Submit reservation" : "Soumettre votre r&eacute;servation"); ?>">
</form>

<?php

}

?>
  <hr/>
  </body>
</html>
