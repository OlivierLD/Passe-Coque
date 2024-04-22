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
  <body>
    <h1>PHP / MySQL. Make a reservation</h1>

    <?php
// phpinfo();

require __DIR__ . "/../../php/db.cred.php";
require __DIR__ . "/_db.utils.php";

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
            "RESERVATION_STATUS <> 'CANCELED' " .
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

function sendEmail(string $destinationEmail, string $emailContent) : void {
    try {
        $lang = 'EN'; // For now

        // echo ("<h2>Password process initiated</h2>" . PHP_EOL);

        $from_email		 = 'pcc@passe-coque.com'; // 'sender@abc.com';    // from mail, sender email address
        
        // Load POST data from HTML form
        $pc_email       = $destinationEmail; // sender email, it will be used in "reply-to" header
        $subject	    = "Boat Club Reservation";
        $message	    = $emailContent;
        // $message       .= ("<a href='http://www.passe-coque.com/php/password.02.php?subscriber-id=$pc_email&lang=$lang'>" . (($lang == "FR") ? "Reset mot de passe" : "Reset password") . "</a><br/>"); // Use the real ID

        $boundary = md5("random"); // define boundary with a md5 hashed value

        // header
        $headers = "MIME-Version: 1.0\r\n";              // Defining the MIME version
        $headers .= "From:".$from_email."\r\n";          // Sender Email (contact)
        $headers .= "Reply-To: ".$pc_email."\r\n";       // Email address to reach back
        $headers .= "Content-Type: multipart/mixed;";    // Defining Content-Type
        $headers .= "boundary = $boundary\r\n";          // Defining the Boundary
                
        try {
            $footer = "<br/><hr/><p>"; 
            $footer .= "<img src='http://www.passe-coque.com/logos/LOGO_PC_rvb.png' width='40'><br/>";  // The full URL of the image.
            $footer .= "The <a href='http://www.passe-coque.com' target='PC'>Passe-Coque</a> web site<br/>"; // Web site
            $footer .= "</p>";
            $fmt_message = str_replace("\n", "\n<br/>", $message);
            $fmt_message .= $footer;
        
            // plain text, or html
            $body = "--$boundary\r\n";
            // $body .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n"; // To allow HTML artifacts, like links and Co.
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= chunk_split(base64_encode($fmt_message));
                
            // TODO? Bcc in the headers (see https://stackoverflow.com/questions/9525415/php-email-sending-bcc)
            $sentMailResult = mail($pc_email, $subject, $body, $headers);

            if ($sentMailResult) {
                if ($lang == "FR") {
                    echo "Un email pour $pc_email est parti.<br/>" . PHP_EOL;
                } else {
                    echo "Email to $pc_email was sent successfully.<br/>" . PHP_EOL;
                }
                // unlink($name); // delete the file after attachment sent.
            } else {
                if ($lang == "FR") {
                    echo "Oops ! Probl&egrave;me pour $pc_email ...<br/>";
                } else {
                    echo "There was a problem for $pc_email ...<br/>";
                }
                die("Sorry but the email to $pc_email could not be sent. Please go back and try again!");
            }	  
        } catch (Throwable $e) {
            echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
        }
        // echo "<hr/>" . PHP_EOL;
    } catch (Throwable $e) {
        echo "[Captured Throwable for " . __FILE__ . " : " . $e . "] <br/>" . PHP_EOL;
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
                $message = "$userId wants to make a reservation for " . $details[0]->boatName . " from " . $fromDate . " to " . $toDate . 
                           ". Your referent feedback is needed.";
                foreach ($details as $detail) {
                    sendEmail($detail->referentEmail, $message);
                } 
                sendEmail("pcc@passe-coque.com", $message);
                // 3-2-2 Requester.
                sendEmail($userId, "Your reservation request for the " . $details[0]->boatType . "\"" . $details[0]->boatName . "\" based in " . $details[0]->boatBase . " from $fromDate to $toDate is on its way!\n" .
                                   "Please do <a href='mailto:pcc@passe-coque.com'>re-contact us</a> if you have no news within the next days.");

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
<form action="<?php echo basename(__FILE__); ?>" method="post">
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
        <tr><td>From</td><td><input type="date" name="from-date" required></td></tr>
        <tr><td>To</td><td><input type="date" name="to-date" required></td></tr>
    </table>

    <input type="submit" value="Submit reservation">
</form>    

<?php

}

?>
  <hr/>
  </body>        
</html>
