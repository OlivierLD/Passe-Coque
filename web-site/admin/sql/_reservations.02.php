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
    <style type="text/css">
      * {
        font-family: 'Courier New'
      }

      tr > td {
        border: 1px solid silver;
      }
    </style>
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

  <body>
    <h1>PHP / MySQL. Make a reservation</h1>

    <?php
// phpinfo();

require __DIR__ . "/../../php/db.cred.php";
require __DIR__ . "/_db.utils.php";
require __DIR__ . "/_emails.utils.php";

$VERBOSE = false;

class MemberStatus {
    public $status;  // bool
    public $errNo;   // int
    public $errMess; // string
}

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

function checkMemberShip(string $dbhost, string $username, string $password, string $database, string $userId, bool $verbose) : MemberStatus {

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

        $memberStatus = new MemberStatus();

        $sql = "SELECT PC.EMAIL, (SELECT BC.EMAIL FROM BOAT_CLUB_MEMBERS BC WHERE BC.EMAIL = PC.EMAIL) FROM PASSE_COQUE_MEMBERS PC WHERE PC.EMAIL = '$userId';";

        if ($verbose) {
            echo ("Executing [" . $sql . "]<br/>");
        }
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>");
        }
        if ($result->num_rows == 0) {
            $memberStatus->status = false;
            $memberStatus->errNo = 1;
            $memberStatus->errMess = "Not a Passe-Coque Member";
        } else {
            // Assume there is only one record returned.
            $pcEmail = '';
            $bcEmail = '';
            while ($table = mysqli_fetch_array($result)) {
                $pcEmail = $table[0];
                $bcEmail = $table[1];
            }
            if ($bcEmail == null || strlen($bcEmail) == 0) {
                $memberStatus->status = false;
                $memberStatus->errNo = 2;
                $memberStatus->errMess = "Passe-Coque Member, but not Boat Club Member";
            } else {
                $memberStatus->status = true;
                $memberStatus->errNo = 0;
                $memberStatus->errMess = "";
            }
        } 
        // On ferme !
        $link->close();
        if ($verbose) {
            echo("Closed DB<br/>".PHP_EOL);
        }
    } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }
    return $memberStatus;
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

function bookTheBoat(string $dbhost, string $username, string $password, string $database, string $userId, string $boatId, string $fromDate, string $toDate, bool $verbose) : void {
    $sql = "INSERT INTO BC_RESERVATIONS (EMAIL, BOAT_ID, FROM_DATE, TO_DATE, MISC_COMMENT) VALUES
    ('$userId', '$boatId', STR_TO_DATE('$fromDate', '%Y-%m-%d'), STR_TO_DATE('$toDate', '%Y-%m-%d'), 'From the PHP UI.');";

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
              echo "OK. Reservation creation performed successfully<br/><hr/>" . PHP_EOL;
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

        // 0 - Check chronology
        $dateFrom = strtotime($fromDate);
        $dateTo = strtotime($toDate);

        // https://www.geeksforgeeks.org/comparing-two-dates-in-php/

        if (false) {
            $date1 = "2011-10-26"; 
            $date2 = "2011-10-24"; 
              
            // Use strtotime() function to convert 
            // date into dateTimestamp 
            $dateTimestamp1 = strtotime($date1); 
            $dateTimestamp2 = strtotime($date2); 
              
            // Compare the timestamp date  
            if ($dateTimestamp1 > $dateTimestamp2) 
                echo "$date1 is later than $date2 <br/>"; 
            else
                echo "$date1 is older than $date2 <br/>"; 
            echo("<hr/>" . PHP_EOL);
        }

        echo("Checking chronology from " . date('Y-m-d', $dateFrom) . " to " . date('Y-m-d', $dateTo) . "<br/>" . PHP_EOL);
        if ($dateFrom > $dateTo) {
            echo "$fromDate after $toDate <br/>";
        } else {
            echo "$fromDate before $toDate <br/>";
        }

        echo("From < To : $fromDate < $toDate : $dateFrom < $dateTo : " . (($dateFrom < $dateTo) ? "true" : "false") . ", v2 (needs true to move on): " . (($dateTo >= $dateFrom) ? "true" : "false") . "<br/>");

        if (($dateTo >= $dateFrom)) {
            // 1 - Check Membership
            $status = checkMemberShip($dbhost, $username, $password, $database, $userId, $VERBOSE);
            if ($VERBOSE) {
                var_dump($status);
            }
            echo "<br/>" . PHP_EOL;
            if ($status->status) {
                if ($VERBOSE) {
                    echo ("Status OK, checking $boatId's availability between $fromDate and $toDate .<br/>" . PHP_EOL);
                }
                // 2 - Check boat Availability
                $boatAvailability = checkBoatAvailability($dbhost, $username, $password, $database, $boatId, $fromDate, $toDate, $VERBOSE);
                if ($boatAvailability->status) {
                    // 3 - Proceed, and send emails
                    echo("Boat availability OK, proceeding.<br/>" . PHP_EOL);
                    // 3-1 Book
                    bookTheBoat($dbhost, $username, $password, $database, $userId, $boatId, $fromDate, $toDate, $VERBOSE);
                    // 3-2 Emails
                    $details = getBoatDetails($dbhost, $username, $password, $database, $boatId, $VERBOSE);
                    // 3-2-1 Referent(s) and PCC
                    $message = "$userId Veut r&eacute;server " . $details[0]->boatName . " du " . $fromDate . " au " . $toDate . 
                            ". En tant que r&eacute;f&eacute;rent du bateau, votre intervention est requise.";
                    foreach ($details as $detail) {
                        sendEmail($detail->referentEmail, "Reservation Boat Club", $message, 'FR');
                    } 
                    sendEmail("pcc@passe-coque.com", "Reservation Boat Club", $message, 'FR');
                    // 3-2-2 Requester.
                    sendEmail($userId, "Reservation Boat Club", 
                            "Votre demande de reservation pour le " . $details[0]->boatType . "\"" . $details[0]->boatName . "\" bas&eacute; &agrave; " . $details[0]->boatBase . " du $fromDate au $toDate a bien &eacute;t&eacute; enregistr&eacute;e !\n" .
                                    "Merci de <a href='mailto:pcc@passe-coque.com'>nous recontacter</a> si vous n'avez pas de nos nouvelles dans les prochains jours.", "FR");

                    ?>
                    <a href="_reservations.01.php">Query Reservation Screen</a>
                    <?php                                   

                } else {
                    echo ("Reservation conflict:<br/>" . PHP_EOL);
                    echo ($boatAvailability->message);
                }
            } else {
                // Membership problem
                echo("There is a membership problem for $userId : " . $status->errMess . "<br/>" . PHP_EOL);
            }
        } else {
            echo("Bad chronology: to-date " . $toDate . " ($dateTo)" . " is before from-date " . $fromDate . " ($dateFrom)" . "<br/>" . PHP_EOL);
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
<!-- Booking form -->
<form action="<?php echo basename(__FILE__); ?>"  onsubmit="return validateForm();" method="post">
    <input type="hidden" name="operation" value="booking">
    <!-- User Name, Boat Id, Date From, Date To -->
    <table>
        <tr><td>Your Email Address</td><td><input type="email" name="email" size="40" required></td></tr>
        <tr>
            <td>The boat</td>
            <td>
                <select name="boat-id">
                    <?php
                    foreach($boatsOfTheClub as $boat) {
                        echo ("<option value='" . $boat->id ."'>" . $boat->type . " \"" . $boat->name . "\", " . "(Base will go here)" . "</option>" . PHP_EOL);
                    }
                    ?>
                </select>    
            </td>
        </tr>
        <tr><td>From</td><td><input type="date" id="from-date" name="from-date" required></td></tr>
        <tr><td>To</td><td><input type="date" id="to-date" name="to-date" required></td></tr>
    </table>
    <div id="submit-message"></div>

    <input type="submit" value="Submit reservation">
</form>    

<?php

}

?>
  <hr/>
  </body>    
</html>
