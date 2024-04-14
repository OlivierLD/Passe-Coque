<?php

require __DIR__ . "/db.cred.php";

$VERBOSE = false;

/*
 * Get equivalent of the_fleet.json from the DB, in JSON format.
 * No HTML involved, absolutely none.
 * Used as a service, returns a JSON object containing the required positions.
 * Invoked in a fetch (with a GET), like from where.are.they.html.
 * 
 * TODO Types enforcements...
 */

function getBoats(string $dbhost, string $username, string $password, string $database, bool $verbose): string {
    try {
        $link = new mysqli($dbhost, $username, $password, $database);
        
        if ($link->connect_errno) {
            echo("[Oops, errno:".$link->connect_errno."...] ");
            // die("Connection failed: " . $conn->connect_error);
            throw $conn->connect_error;

        } else {
            if ($verbose) {
                echo("[Connected.] ");
            }
        }
        $sql = "SELECT * FROM THE_FLEET;";
        if ($verbose) {
            echo('[Performing instruction ['.$sql.']] ');
        }
        
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>");
        }

        $json_result = "[";
        $first = true;
        while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
            $next_element = "{ \"name\": \"" . $table[0] . "\", \"id\": \"" . $table[1] . "\", \"pix\": \"" . $table[2] . "\", \"type\": \"" . $table[3] . "\", \"category\": \"" . $table[4] . "\", \"base\": \"" . $table[5] . "\" } ";
            // echo $next_element . "<br/>" . PHP_EOL;
            $json_result = $json_result . ($first ? "" : ", ") . $next_element;
            $first = false;
        }
        $json_result = $json_result . "]";

        // On ferme !
        $link->close();
        if ($verbose) {
            echo("[Closed DB] ".PHP_EOL);
            echo "Finally, returning $json_result";
        }
        return $json_result;

    } catch (Throwable $e) {
        echo "[ Captured Throwable for connection : " . $e->getMessage() . "] " . PHP_EOL;
        throw $e;
    }                
    return null;
}

try {
    $data = getBoats($dbhost, $username, $password, $database, $VERBOSE);
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
