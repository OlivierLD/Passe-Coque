<html lang="en">
  <!--
   ! WiP.
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
    <h1>PHP / MySQL. Boat-Club Reservation Planning</h1>

    <?php
// phpinfo();

require __DIR__ . "/../../php/db.cred.php";
require __DIR__ . "/_db.utils.php";

$VERBOSE = false;

function isLeapYear(int $year): bool {
    $ret = false;
    if ($year % 4 == 0) {
        $ret = true;
        if ($year % 100 == 0) {
            $ret = false;
            if ($year % 400 == 0) {
                $ret = true;
            }
        }
    }
    return $ret;
}

$MONTHS = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");

function getNbDays(int $year, int $month): int {
    $NB_DAYS = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    $nbDays = $NB_DAYS[$month - 1];
    if ($month == 2 && isLeapYear($year)) {
        $nbDays = 29;
    }
    return $nbDays;
}

class Reservation {
    public $owner;
    public $boat;
    public $resDate;
    public $from;
    public $to;
    public $status;
    public $comment;
}

function getReservations(string $dbhost, string $username, string $password, string $database, string $boatId, string $from, string $to, bool $verbose) : array {
    $sql =  "SELECT EMAIL, BOAT_ID, RESERVATION_DATE, DATE_FORMAT(FROM_DATE, '%Y-%b-%d'), DATE_FORMAT(TO_DATE, '%Y-%b-%d')TO_DATE, RESERVATION_STATUS, MISC_COMMENT FROM BC_RESERVATIONS " .
            "WHERE ((FROM_DATE >= STR_TO_DATE('" . $from . "', '%Y-%m-%d') AND FROM_DATE <= STR_TO_DATE('" . $to . "', '%Y-%m-%d')) OR " . 
                " (TO_DATE >= STR_TO_DATE('" . $from . "', '%Y-%m-%d') AND TO_DATE <= STR_TO_DATE('" . $to . "', '%Y-%m-%d'))) AND " .
                "BOAT_ID = '" . $boatId . "' " .
                "ORDER BY FROM_DATE;";
    $reservations = array();
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
            $reservations[$index] = new Reservation();
            $reservations[$index]->owner = $table[0];
            $reservations[$index]->boat = $table[1];
            $reservations[$index]->resDate = $table[2];
            $reservations[$index]->from = $table[3];
            $reservations[$index]->to = $table[4];
            $reservations[$index]->status = $table[5];
            $reservations[$index]->comment = $table[6];
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

    return $reservations;
}

function getReservation(string $dbhost, string $username, string $password, string $database, string $boatId, string $from, string $resDate, bool $verbose) : Reservation {

    $sql = "SELECT EMAIL, BOAT_ID, RESERVATION_DATE, FROM_DATE, TO_DATE, RESERVATION_STATUS, MISC_COMMENT FROM BC_RESERVATIONS " .
        "WHERE EMAIL = '" . $from . "' AND BOAT_ID = '" . $boatId . "' AND RESERVATION_DATE = STR_TO_DATE('$resDate', '%Y-%m-%d %H:%i:%s');";

    $reservation = new Reservation();        
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

        if (true || $verbose) {
            echo ("Executing [" . $sql . "]" . PHP_EOL);
        }
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>");
        }
        while ($table = mysqli_fetch_array($result)) { // Should be only one
            $reservation->owner = $table[0];
            $reservation->boat = $table[1];
            $reservation->resDate = $table[2];
            $reservation->from = $table[3];
            $reservation->to = $table[4];
            $reservation->status = $table[5];
            $reservation->comment = $table[6];
        }        
        // On ferme !
        $link->close();
        if ($verbose) {
            echo("Closed DB<br/>".PHP_EOL);
        }
    } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }
    return $reservation;
}

// For INSERT, DELETE, UPDATE
function executeSQL(string $dbhost, string $username, string $password, string $database, string $sql, bool $verbose) : void {

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

        if (true || $verbose) {
            echo ("Executing [" . $sql . "]" . PHP_EOL);
        }
        if (true) { // Do perform ?
            if ($link->query($sql) === TRUE) {
              echo "OK. Statement executed successfully<br/><hr/>" . PHP_EOL;
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

// Find current date (for the month)
if (false) {
    echo "Today is " . date("Y/m/d") . "<br>";
    echo "Today is " . date("Y.m.d") . "<br>";
    echo "Today is " . date("Y-m-d") . "<br>";
    echo "Today is " . date("l") . "<br>";
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
          <input type='hidden' name='operation' value='update'>
          <input type='hidden' name='owner' value='<?php echo $res->owner; ?>'>
          <input type='hidden' name='boat' value='<?php echo $res->boat; ?>'>
          <input type='hidden' name='res-date' value='<?php echo $res->resDate; ?>'>
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
        $comment = $_POST["comment"];

        if ($_POST['table-operation'] == 'Update') {
            echo "It's an UPDATE<br/>";
            $sql = "UPDATE BC_RESERVATIONS SET " .
                   "RESERVATION_STATUS = '$status', " .
                   "MISC_COMMENT = '$comment' " .
                   "WHERE EMAIL = '$owner' AND " .
                        " BOAT_ID = '$boat' AND " .
                        " RESERVATION_DATE = STR_TO_DATE('$resDate', '%Y-%m-%d %H:%i:%s');";
            executeSQL($dbhost, $username, $password, $database, $sql, $VERBOSE);
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
    } else { // TODO Delete, Update
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
    <option value="01"<?php echo(($currentMonth == 1) ? ' selected' : ''); ?>>January</option>
    <option value="02"<?php echo(($currentMonth == 2) ? ' selected' : ''); ?>>February</option>
    <option value="03"<?php echo(($currentMonth == 3) ? ' selected' : ''); ?>>March</option>
    <option value="04"<?php echo(($currentMonth == 4) ? ' selected' : ''); ?>>April</option>
    <option value="05"<?php echo(($currentMonth == 5) ? ' selected' : ''); ?>>May</option>
    <option value="06"<?php echo(($currentMonth == 6) ? ' selected' : ''); ?>>June</option>
    <option value="07"<?php echo(($currentMonth == 7) ? ' selected' : ''); ?>>July</option>
    <option value="08"<?php echo(($currentMonth == 8) ? ' selected' : ''); ?>>August</option>
    <option value="09"<?php echo(($currentMonth == 9) ? ' selected' : ''); ?>>September</option>
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

    echo ("<h2>Reservation Planning for " . $MONTHS[$currentMonth - 1] . " " . $currentYear . "</h2>");

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
    $firstDayOfMonth = $currentYear . "-" . $currentMonth . "-" . "01";
    $lastDayOfMonth = $currentYear . "-" . $currentMonth . "-" . getNbDays($currentYear, $currentMonth);

    foreach($boatsOfTheClub as $boat) {
        if ($VERBOSE) {
            echo("Fetching planning for " . $boat->name . " in " . $MONTHS[$currentMonth - 1] . " " . $currentYear . "<br/>" . PHP_EOL);
        }
        $boatId = $boat->id;
        $res = getReservations($dbhost, $username, $password, $database, $boatId, $firstDayOfMonth, $lastDayOfMonth, $VERBOSE);
        // var_dump($res);
        echo("<br/>" . PHP_EOL);
        if (count($res) == 0) {
            echo ("No reservation for " . $boat->name  . " in " . $MONTHS[$currentMonth - 1] . " " . $currentYear . "<br/>" . PHP_EOL);
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