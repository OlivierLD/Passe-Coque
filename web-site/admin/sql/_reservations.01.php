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
    <h1>PHP / MySQL. Boat-Club Planning</h1>

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
                "BOAT_ID = '" . $boatId . "';";
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

echo ("This month (" . $currentYear . " - " . $MONTHS[$currentMonth - 1] . ") we have " . getNbDays($currentYear, $currentMonth) . " days.<br/>");

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
            $resData = "By " . $reservation->owner . ", from " . $reservation->from . " to " . $reservation->to . ", status " . $reservation->status;
            echo $resData;
            echo("</li>" . PHP_EOL);
        }
        echo ("</ul>" . PHP_EOL);
    }
}

echo("<hr/>" . PHP_EOL);

?>
  </body>        
</html>