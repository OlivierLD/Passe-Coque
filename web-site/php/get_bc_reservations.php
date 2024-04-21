<?php

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
 // TODO Get dates from the user

$currentYear = date("Y"); // Default, current year
// echo("Current year is " . $currentYear . ", next will be " . ($currentYear + 1) . "<br/>");
$currentMonth = date("m"); // Default, current month

if (isset($_GET['year'])) {
    $currentYear = $_GET['year'];
}
if (isset($_GET['month'])) {
    $currentMonth = $_GET['month'];
}

$firstDayOfMonth = $currentYear . "-" . $currentMonth . "-" . "01";
$lastDayOfMonth = $currentYear . "-" . $currentMonth . "-" . getNbDays($currentYear, $currentMonth);

class BoatReservation {
    public $boat;
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
        $res = getReservations($dbhost, $username, $password, $database, $boatId, $firstDayOfMonth, $lastDayOfMonth, $VERBOSE);
        $boatAndReservations[$mainIdx]->reservations = $res;
        $mainIdx++;
    }

    $data = json_encode($boatAndReservations);
    header('Content-Type: application/json; charset=utf-8');
    // echo json_encode($data); // This is for text (not json)
    echo $data;
    http_response_code(200);
} catch (Throwable $e) {
    // echo "[Captured Throwable for geo_members.php : " . $e . "] " . PHP_EOL;
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error - ' . $e, true, 500);
    // echo $e;
    http_response_code(500);
}
?>
