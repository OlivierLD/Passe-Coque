<?php

require __DIR__ . "/db.cred.php";
require __DIR__ . "/../admin/sql/_db.utils.php";

$VERBOSE = false;

/*
 * Get equivalent of the_fleet.json from the DB, in JSON format.
 * No HTML involved, absolutely none.
 * Used as a service, returns a JSON object containing the required positions.
 * Invoked in a fetch (with a GET), like from where.are.they.html.
 *
 * TODO Types enforcements...
 */

try {

    $host = $dbhost;
    $user = $username;
    $pass = $password;
    $db = $database;

    $hostname = $_SERVER['SERVER_NAME']; // gethostname(); // TODO Find 'localhost'...

    // echo "[get_the_fleet.php] Hostname is " . $hostname . PHP_EOL;

    // if (true && str_starts_with( $hostname, "macbook")) { // To access local DB
    if ($hostname == "localhost") { // To access local DB
        // Local dev
        $user = "pc"; // DB_USER
        $pass = "pc"; // DB_PASSWORD
        $db = "pcDB"; // DB_DATABASE
        $host = "localhost"; // DB_HOST
    }

    $origin = $hostname; // "$user@$host/$db";

    $data = getBoatsJSON($host, $user, $pass, $db, $VERBOSE);
    header('Content-Type: application/json; charset=utf-8');
    // echo json_encode($data); // This is for text (not json)
    // echo $data;
    echo "{ \"origin\": \"$origin\", \"data\": $data }";   // TODO hostname ?
    // http_response_code(200);  // Mmh ?
} catch (Throwable $e) {
    echo "[Captured Throwable for get_the_fleet.php : " . $e . "] " . PHP_EOL;
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error - ' . $e, true, 500);
    // echo $e;
    http_response_code(500);
}
?>
