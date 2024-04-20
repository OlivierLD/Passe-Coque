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
    $data = getBoatsJSON($dbhost, $username, $password, $database, $VERBOSE);
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
