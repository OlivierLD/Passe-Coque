<?php
// Must be on top
session_start();
?>
<html lang="en">
  <!--
   ! WiP.
   ! Make a reservation
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

class BoatAvailability {
    public $status;
    public $message;
}

class BoatDetailRef {
    public $referentEmail;
    public $boatId;
    public $boatName;
    public $boatType;
    public $boatBase;
}

function checkBoatAvailability(string $dbhost, string $username, string $password, string $database, string $boatId, string $fromDate, string $toDate, bool $verbose) : BoatAvailability {
    $boatAvailability = new BoatAvailability();
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

        $sql =  "SELECT EMAIL, BOAT_ID, RESERVATION_DATE, DATE_FORMAT(FROM_DATE, '%Y-%b-%d'), DATE_FORMAT(TO_DATE, '%Y-%b-%d'), RESERVATION_STATUS, MISC_COMMENT FROM BC_RESERVATIONS " .
                "WHERE ((STR_TO_DATE('$fromDate', '%Y-%m-%d') BETWEEN FROM_DATE AND TO_DATE) OR " .
                    "(STR_TO_DATE('$toDate', '%Y-%m-%d') BETWEEN FROM_DATE AND TO_DATE)) AND " .
                    "BOAT_ID = '" . $boatId . "' AND " .
                    "RESERVATION_STATUS NOT IN ('CANCELED', 'REJECTED') " .
                    "ORDER BY FROM_DATE;";

        if ($verbose) {
            echo ("Executing [" . $sql . "]<br/>");
        }
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>");
        }
        if ($result->num_rows == 0) {

            $boatAvailability->status = true;
        } else {
            $message = '';
            while ($table = mysqli_fetch_array($result)) {
                $message .= ($table[0] . " reserved from " . $table[3] . " to " . $table[4] . "<br/>");
            }
            $boatAvailability->status = false;
            $boatAvailability->message = $message;
        } 
        // On ferme !
        $link->close();
        if ($verbose) {
            echo("Closed DB<br/>".PHP_EOL);
        }
    } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }

    return $boatAvailability;
}

function bookTheBoat(string $dbhost, string $username, string $password, string $database, string $userId, string $boatId, string $fromDate, string $toDate, string $comments, bool $verbose, string $lang) : void {
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

if (isset($_POST['operation'])) {

    $operation = $_POST['operation'];
    if ($operation == 'booking') {
        $userId = $_POST['email'];
        $boatId = $_POST['boat-id'];
        $fromDate = $_POST['from-date'];
        $toDate = $_POST['to-date'];
        $comments = $_POST['comment-area'];

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
            if ($status->status) { // Passe-Coque AND Boat-Club
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
                    bookTheBoat($dbhost, $username, $password, $database, $userId, $boatId, $fromDate, $toDate, $escapedComment, $VERBOSE, $lang);
                    // 3-2 Emails
                    $details = getBoatDetails($dbhost, $username, $password, $database, $boatId, $VERBOSE);
                    // 3-2-1 Referent(s) and PCC
                    if ($lang != 'FR') {
                        $message = "$userId wants to reserve " . $details[0]->boatName . " from " . $fromDate . " to " . $toDate . 
                                ". As a referent of the boat your insight is required.";
                    } else {
                        $message = "$userId veut r&eacute;server " . $details[0]->boatName . " du " . $fromDate . " au " . $toDate . 
                                ". En tant que r&eacute;f&eacute;rent du bateau, votre intervention est requise.";
                    }
                    foreach ($details as $detail) {
                        sendEmail($detail->referentEmail, "Reservation Boat Club", $message, $lang, false);
                    } 
                    sendEmail("pcc@passe-coque.com", "Reservation Boat Club", $message, $lang, false);
                    // 3-2-2 Requester.
                    if ($lang != 'FR') {
                        sendEmail($userId, "Reservation Boat Club", 
                                "Your reservation request for the " . $details[0]->boatType . "\"" . $details[0]->boatName . "\" based in " . $details[0]->boatBase . " from $fromDate to $toDate has been recorded successfully!\n" .
                                "The referent of the boat can be contacted at the following email address: $detail->referentEmail \n" .
                                "Please do <a href='mailto:pcc@passe-coque.com'>re-contact us</a> if you do not hear from us within the next days.", $lang, true);
                        ?>
                        <p>
                            Your request is recorded. You will receive a
                            email summarizing the information: date(s), boat, base, number of crew, equipment to be provided and
                            other information, contact details of the contact person to contact for access, handling and restitution
                            of the boat.<br/>
                            If there is no response within 24/48 hours, you can contact us at the address
                            following email: pcc@passe-coque.com.<br/>
                            The same AR was sent to the referent to inform him that he must accept or reject the reservation request.
                        </p>
                        <a href="<?php echo(basename(__FILE__) . "?lang=EN") ?>">Back</a>.
                        <?php
                                    } else {
                        sendEmail($userId, "Reservation Boat Club", 
                                "Votre demande de reservation pour le " . $details[0]->boatType . "\"" . $details[0]->boatName . "\" bas&eacute; &agrave; " . $details[0]->boatBase . " du $fromDate au $toDate a bien &eacute;t&eacute; enregistr&eacute;e !\n" .
                                "Le r&eacute;f&eacute;rent du bateau peut &ecirc;tre contact&eacute; &agrave; l'adresse email suivante : $detail->referentEmail \n" .
                                "Merci de <a href='mailto:pcc@passe-coque.com'>nous recontacter</a> si vous n'avez pas de nos nouvelles dans les prochains jours.", $lang, true);
                        ?>
                        <p>
                            Votre r&eacute;servation est enregistr&eacute;e. Vous allez recevoir un
                            mail r&eacute;capitulant les informations : date(s), bateau, base, nombre d'&eacute;quipiers, mat&eacute;riel &agrave; pr&eacute;voir et
                            autres mentions, coordonn&eacute;es du r&eacute;f&eacute;rent &agrave; contacter pour l'acc&egrave;s, la prise en main et la restitution
                            du bateau.<br/>
                            En cas d'absence de r&eacute;ponse sous 24/48 heures, vous pouvez contacter nous contacter &agrave; l'adresse
                            mail suivante : pcc@passe-coque.com.<br/>
                            Le m&ecirc;me AR est envoy&eacute; au r&eacute;f&eacute;rent pour l'informer qu'il doit accepter ou refuser la demande de r&eacute;servation.
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
                if ($lang != 'FR') {
                    echo("There is a membership problem for $userId : " . $status->errMess . ". Click the button below to subscribe!<br/>" . PHP_EOL); 
                    ?>
                    <!--button onclick="clack_pcc('_4');" class="pc-button" style="margin: 5px;">Join the boat&nbsp;club</button-->
                    <a href="https://passe-coque.com/boat-club/?lang=EN&nav-to=4" target="_PARENT">Join the boat&nbsp;club</a>
                    <?php
                } else {
                    echo("Probl&egrave;me d'adh&eacute;sion pour $userId : " . $status->errMess . ". Cliquez le bouton pour adh&eacute;rer !<br/>" . PHP_EOL); 
                    ?>
                    <!--button onclick="clack_pcc('_4');" class="pc-button" style="margin: 5px;">Adh&eacute;rer au boat&nbsp;club</button-->
                    <a href="https://passe-coque.com/boat-club/?lang=FR&nav-to=4" target="_PARENT">Adh&eacute;rer au boat&nbsp;club</a>
                    <?php
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
            <td><?php if ($lang != 'FR') { echo"Your Email Address"; } else { echo("Votre adresse email, $userId"); } ?></td>
            <td><input type="email" name="email" size="40" value="<?php echo $userId; ?>" required></td>
        </tr>
        <tr>
            <td><?php echo(($lang != 'FR') ? "The boat" : "Le bateau"); ?></td>
            <td>
                <select name="boat-id">
                    <?php
                    foreach($boatsOfTheClub as $boat) {
                        if ($lang != 'FR') {
                            echo ("<option value='" . $boat->id ."'>" . $boat->type . " \"" . $boat->name . "\", " . "(Base will go here)" . "</option>" . PHP_EOL);
                        } else {
                            echo ("<option value='" . $boat->id ."'>" . $boat->type . " \"" . $boat->name . "\", " . "(Nom de la base ici...)" . "</option>" . PHP_EOL);
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
