<?php

// Tu use as a REST GET service
// Use like in https://passe-coque.com/php/get_bc_reservations.php?year=2024&month=05 

require __DIR__ . "/db.cred.php";
require __DIR__ . "/../admin/sql/_db.utils.php";

$VERBOSE = false;

/**
 * Get Boat Club reservations from the DB, in JSON format.
 * No HTML involved, absolutely none.
 * Used as a service, returns a JSON object containing the required positions.
 * Invoked in a fetch (with a GET)..
 */


 // WiP.

$span = 1; // One month wide, by default

$currentYear = date("Y"); // Default, current year
// echo("Current year is " . $currentYear . ", next will be " . ($currentYear + 1) . "<br/>");
$currentMonth = date("m"); // Default, current month

if (isset($_GET['year'])) {
    $currentYear = $_GET['year'];
}
if (isset($_GET['month'])) {
    $currentMonth = $_GET['month'];
}
if (isset($_GET['span'])) {
    $span = $_GET['span'];
}

$fromDate = $currentYear . "-" . $currentMonth . "-" . "01";
$lastDateYM = addMonth($currentYear, $currentMonth, $span - 1);
// $toDate = $currentYear . "-" . $currentMonth . "-" . getNbDays($currentYear, $currentMonth);
$toDate = $lastDateYM[0] . "-" . $lastDateYM[1] . "-" . getNbDays($lastDateYM[0], $lastDateYM[1]);

if ($VERBOSE) {
    echo ("From " . $fromDate . " to " . $toDate);
}

class BoatReservation {
    public $boat;
    public $reservations;
}

class YMDdName {
    public $year;
    public $month;   // 1-12
    public $day;     // 0-31
    public $weekDay; // 0-6 0: Sunday, 6:Saturday
}

class Calendar {
    public $from;
    public $to;
    public $days;
}

class Planning {
    public $calendar;
    public $reservations;
}

try {

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

    $boatAndReservations = array();
    $mainIdx = 0;

    foreach($boatsOfTheClub as $boat) {
        $boatAndReservations[$mainIdx] = new BoatReservation();
        $boatAndReservations[$mainIdx]->boat = $boat;
        if ($VERBOSE) {
            echo("Fetching planning for " . $boat->name . " in " . $MONTHS[$currentMonth - 1] . " " . $currentYear . "<br/>" . PHP_EOL);
        }
        $boatId = $boat->id;
        $res = getReservations($dbhost, $username, $password, $database, $boatId, $fromDate, $toDate, $VERBOSE);
        $boatAndReservations[$mainIdx]->reservations = $res;
        $mainIdx++;
    }

    // Calendar, $fromDate, $toDate
    $calendar = new Calendar();
    $calendar->from = $fromDate;
    $calendar->to = $toDate;
    $calendar->days = array();

    $year = $currentYear;
    $month = $currentMonth;
    $day = 1;
    $keepLooping = true;
    $currentIndex = 0;
    $stopDate = new DateTime($calendar->to); // Format YYYY-MM-DD
    // See https://www.w3schools.com/php/phptryit.asp?filename=tryphp_func_date
    //     https://www.w3schools.com/php/func_date_date.asp
    while ($keepLooping) {
        try {
            $theDate = (new DateTime($year . "-" . sprintf('%02d', $month) . "-" . sprintf('%02d', $day)))->format('Y-m-d');
            if ($theDate == $stopDate->format('Y-m-d') || $currentIndex > 370) { // 370: ceinture & bretelles
                $keepLooping = false; // This is tha last loop. Code below still executed.
            }
            $weekDay = date("w", mktime(0,0,0, $month, $day, $year));
            $oneDay = new YMDdName();
            $oneDay->year = intval($year);
            $oneDay->month = intval($month);
            $oneDay->day = intval($day);
            $oneDay->weekDay = intval($weekDay);
            $calendar->days[$currentIndex] = $oneDay;
            $currentIndex++;

            // Add one day
            $loopDate = new DateTime($year . "-" . sprintf('%02d', $month) . "-" . sprintf('%02d', $day));
            // $date = new DateTime('2000-12-31');
            $loopDate->modify('+1 day');
            // echo $loopDate->format('Y-m-d') . "\n";
            // Update year, month, day
            $year = $loopDate->format('Y');
            $month = $loopDate->format('m');
            $day = $loopDate->format('d');
        } catch (Throwable $e) {
            die ("Error in the loop: " . $e);
        }
    }

    // Final object
    $planning = new Planning();
    $planning->calendar = $calendar;
    $planning->reservations = $boatAndReservations;

    $data = json_encode($planning);
    header('Content-Type: application/json; charset=utf-8');
    echo $data;
    http_response_code(200);
} catch (Throwable $e) {
    // echo "[Captured Throwable for geo_members.php : " . $e . "] " . PHP_EOL;
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error - ' . $e, true, 500);
    // echo $e;
    http_response_code(500);
}
?>
