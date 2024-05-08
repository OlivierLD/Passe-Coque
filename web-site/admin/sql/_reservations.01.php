<html lang="en">
  <!--
   ! WiP.
   ! Basic reservation planning - 3 month. (see the $span variable)
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

      ul > li {
        display: grid;
        grid-template-columns: 90% 10%;
      }
    </style>
  </head>
  <body>
    <h1>PHP / MySQL. Boat-Club Reservation Planning</h1>

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

function translateStatus(string $coded) : string {
    $translated = "inconnu";
    switch ($coded) {
        case "TENTATIVE":
            $translated = "Soumise";
            break;
        case "CONFIRMED":
            $translated = "Valid&eacute;e";
            break;
        case "CANCELED":
            $translated = "Annul&eacute;e";
            break;
        case "REJECTED":
            $translated = "Refus&eacute;e";
            break;
        case "ADMIN":
            $translated = "R&eacute;serv&eacute;e";
            break;
        default:
            break;
    }
    return $translated;
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
if (isset($_POST['operation'])) {
    $operation = $_POST['operation'];
    if ($operation == 'edit') {
        // Edit form
        // Get PK values
        $from = $_POST['email'];
        $boatId = $_POST['boat-id'];
        $resDate = $_POST['res-date'];

        // Get res details
        echo("Editing reservation from [$from] for [$boatId], made at [$resDate]");
        $res = getReservation($dbhost, $username, $password, $database, $boatId, $from, $resDate, $VERBOSE);

        // var_dump($res);
        // echo("<br/>");
        ?>
        <form action="<?php echo basename(__FILE__); ?>" method="post">
          <input type='hidden' name='operation' value='update'>                  <!-- Previous Status -->
          <input type='hidden' name='owner' value='<?php echo $res->owner; ?>'>
          <input type='hidden' name='boat' value='<?php echo $res->boat; ?>'>
          <input type='hidden' name='res-date' value='<?php echo $res->resDate; ?>'>
          <input type='hidden' name='prev-status' value='<?php echo $res->status; ?>'>
          <table>
            <tr><td>From User</td><td><?php echo $res->owner; ?></td></tr>
            <tr><td>For</td><td><?php echo $res->boat; ?></td></tr>
            <tr><td>At</td><td><?php echo $res->resDate; ?></td></tr>
            <tr><td>from</td><td><?php echo $res->from; ?></td></tr>
            <tr><td>to</td><td><?php echo $res->to; ?></td></tr>
            <tr>
                <td>Status</td>
                <td>
                    <select name='status'>
                        <option value="TENTATIVE"<?php echo($res->status == 'TENTATIVE' ? ' selected' : ''); ?>>TENTATIVE</option>
                        <option value="CONFIRMED"<?php echo($res->status == 'CONFIRMED' ? ' selected' : ''); ?>>CONFIRMED</option>
                        <option value="REJECTED"<?php echo($res->status == 'REJECTED' ? ' selected' : ''); ?>>REJECTED</option>
                        <option value="CANCELED"<?php echo($res->status == 'CANCELED' ? ' selected' : ''); ?>>CANCELED</option>
                        <option value="ADMIN"<?php echo($res->status == 'ADMIN' ? ' selected' : ''); ?>>ADMIN</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Comments</td>
                <td>
                    <textarea name="comment" rows="10" cols="80" placeholder="Your comments go here"><?php echo($res->comment); ?></textarea>
                </td>
            </tr>
          </table>  
          <input type="submit" name="table-operation" value="Update">
          <input type="submit" name="table-operation" value="Delete">
        </form>    
        <?php
    } else if ($operation == 'update') {
        // var_dump($_POST);
        // echo("<br/>");
        $owner = $_POST["owner"];
        $boat = $_POST["boat"];
        $resDate = $_POST["res-date"];
        $status = $_POST["status"];
        $prevStatus = $_POST["prev-status"];
        $comment = $_POST["comment"];

        if ($_POST['table-operation'] == 'Update') {
            echo "It's an UPDATE<br/>";

            $ownerDetails = getMember($dbhost, $username, $password, $database, $owner, $VERBOSE);

            // Get detailed boat data, referent(s) infos.
            $allDetails = getBoatAndReferentDetails($dbhost, $username, $password, $database, $boat, $VERBOSE);

            $sql = "UPDATE BC_RESERVATIONS SET " .
                   "RESERVATION_STATUS = '$status', " .
                   "MISC_COMMENT = '$comment' " .
                   "WHERE EMAIL = '$owner' AND " .
                        " BOAT_ID = '$boat' AND " .
                        " RESERVATION_DATE = STR_TO_DATE('$resDate', '%Y-%m-%d %H:%i:%s');";
            executeSQL($dbhost, $username, $password, $database, $sql, $VERBOSE);
            if ($prevStatus != $status) {
                // Send email to $owner (the guy who reserved).
                $message = "Bonjour " . $ownerDetails[0]->firstName . ", <br/>";
                $message .= ("Votre r&eacute;servation du " . $resDate . " pour le bateau \""  . $allDetails[0]->boatName . "\" (id \"" . $boat . "\") a &eacute;t&eacute; modifi&eacute;e de \"" . translateStatus($prevStatus) . "\" &agrave; \"" . translateStatus($status) . "\".<br/>");

                $message .= ("\"" . $allDetails[0]->boatName . "\" a " . count($allDetails) . " r&eacute;f&eacute;rent" . (count($allDetails) > 1 ? "s" : "") . " dont voici les coordonn&eacute;es :<br/>");
                if (true) {
                    foreach ($allDetails as $details) {
                        $message .= ("R&eacute;f&eacute;rent : " . $details->refFullName . ", email : " . $details->refEmail . ", t&eacute;l&eacute;phone : " . $details->refTel . "<br/>");
                    }
                } else {
                    echo "--------------</br>";
                    var_dump($allDetails);
                    echo "<br/>--------------</br>";
                }
                $message .= "<br/>- L'&eacute;quipe du Passe-Coque Club.<br/>";

                echo("Sending message:<br/>" . $message . "<br/>");

                sendEmail($owner, "Boat Club Passe-Coque", $message, "FR");
            }
        } else if ($_POST['table-operation'] == 'Delete') {
            echo "It's a DELETE<br/>";
            $sql = "DELETE FROM BC_RESERVATIONS " .
                   "WHERE EMAIL = '$owner' AND " .
                        " BOAT_ID = '$boat' AND " .
                        " RESERVATION_DATE = STR_TO_DATE('$resDate', '%Y-%m-%d %H:%i:%s');";
            executeSQL($dbhost, $username, $password, $database, $sql, $VERBOSE);
        }
        ?>
        <a href="<?php echo basename(__FILE__); ?>">Back to Query</a>
        <?php
    } else { 
        echo ("Un-managed operation [$operation]<br/>" . PHP_EOL);
    }
} else { // Starting form
?>
<!-- Display date widgets -->
<form action="<?php echo basename(__FILE__); ?>" method="get">
    Go to 
  <select name="year">
    <option value="2024"<?php echo(($currentYear == 2024) ? ' selected' : ''); ?>>2024</option>
    <option value="2025"<?php echo(($currentYear == 2025) ? ' selected' : ''); ?>>2025</option>
    <option value="2026"<?php echo(($currentYear == 2026) ? ' selected' : ''); ?>>2026</option>
  </select>
  <select name="month">
    <option value="01"<?php echo(($currentMonth == 1)  ? ' selected' : ''); ?>>January</option>
    <option value="02"<?php echo(($currentMonth == 2)  ? ' selected' : ''); ?>>February</option>
    <option value="03"<?php echo(($currentMonth == 3)  ? ' selected' : ''); ?>>March</option>
    <option value="04"<?php echo(($currentMonth == 4)  ? ' selected' : ''); ?>>April</option>
    <option value="05"<?php echo(($currentMonth == 5)  ? ' selected' : ''); ?>>May</option>
    <option value="06"<?php echo(($currentMonth == 6)  ? ' selected' : ''); ?>>June</option>
    <option value="07"<?php echo(($currentMonth == 7)  ? ' selected' : ''); ?>>July</option>
    <option value="08"<?php echo(($currentMonth == 8)  ? ' selected' : ''); ?>>August</option>
    <option value="09"<?php echo(($currentMonth == 9)  ? ' selected' : ''); ?>>September</option>
    <option value="10"<?php echo(($currentMonth == 10) ? ' selected' : ''); ?>>October</option>
    <option value="11"<?php echo(($currentMonth == 11) ? ' selected' : ''); ?>>November</option>
    <option value="12"<?php echo(($currentMonth == 12) ? ' selected' : ''); ?>>December</option>
  </select>
  <input type="submit" value="Go">
</form>    

<?php

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

    // echo ("<h2>Reservation Planning for " . $MONTHS[$currentMonth - 1] . " " . $currentYear . " (3 months)</h2>");

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
    if ($VERBOSE) {
        echo ("We have " . count($boatsOfTheClub) . " boats in the club<br/>");
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
    echo ("<h2>Reservation Planning from " . $from . " to " . $to . " </h2>");

    $lastDayOfMonth = $currentYear . "-" . $currentMonth . "-" . getNbDays($currentYear, $currentMonth);

    foreach($boatsOfTheClub as $boat) {
        if ($VERBOSE) {
            echo("Fetching planning for " . $boat->name . " in " . $MONTHS[$currentMonth - 1] . " " . $currentYear . "<br/>" . PHP_EOL);
        }
        $boatId = $boat->id;
        $res = getReservations($dbhost, $username, $password, $database, $boatId, $firstDayOfMonth, $lastDayOfMonth, true, $VERBOSE);
        // var_dump($res);
        echo("<br/>" . PHP_EOL);
        if (count($res) == 0) {
            echo ("No reservation for " . $boat->name  /* . " in " . $MONTHS[$currentMonth - 1] . " " . $currentYear */ . "<br/>" . PHP_EOL);
        } else {
            echo ("Reservation(s) for " . $boat->name . ":<br/>" . PHP_EOL);
            echo ("<ul>" . PHP_EOL);
            foreach($res as $reservation) {
                echo("<li>" . PHP_EOL);
                $resData = "By " . $reservation->owner . ", from " . $reservation->from . " to " . $reservation->to . ", status " . $reservation->status .
                        "<form action='" . basename(__FILE__) . "' method='post'>" .
                        "<input type='hidden' name='operation' value='edit'>" .
                        "<input type='hidden' name='email' value='" . $reservation->owner . "'>" .
                        "<input type='hidden' name='boat-id' value='" . $reservation->boat . "'>" .
                        "<input type='hidden' name='res-date' value='" . $reservation->resDate . "'>" .
                        "<input type='submit' value='Edit'>" .
                        "</form>";
                echo $resData;
                echo("</li>" . PHP_EOL);
            }
            echo ("</ul>" . PHP_EOL);
        }
    }

    echo("<hr/>" . PHP_EOL);
}
?>
  </body>        
</html>