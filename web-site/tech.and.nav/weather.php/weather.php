<?php
/*
To be invoked like here:
    to select:
    curl -X GET "http://localhost/tech.and.nav/weather.php/weather.php" -H "accept: application/json"
    to insert:
    curl -X POST "http://localhost/tech.and.nav/weather.php/weather.php" -H "accept: application/json" -d type=AT -d value=12

    from JavaScript:

    let boatData = ".../weather.php";
    fetch(boatData, {
        method: "GET",
        headers: {
                "Content-type": "application/json; charset=UTF-8"
            }
        }).then(response => {

        . . .

        });

    select (unixepoch(datetime('now')) - unixepoch(data_date)) from weather_data;
    select (unixepoch() - unixepoch(data_date)) from weather_data;
    unixepoch: in seconds. One week = 7 * 24 * 60 * 60 = 604800 seconds

    SELECT strftime('%FT%T', DATA_DATE) AS DURATION, VALUE
    FROM WEATHER_DATA
    WHERE TYPE='PRMSL' AND (unixepoch() - unixepoch(DATA_DATE)) < 604800
    ORDER BY DATA_DATE ASC;
*/

ini_set('memory_limit', '-1'); // For no limit ...


function getPRMSL_JSON(SQLite3 $database, bool $verbose): string {
    try {

        $sql = "SELECT strftime('%FT%T', DATA_DATE) AS DURATION, VALUE " .
               "FROM WEATHER_DATA " .
               "WHERE TYPE='PRMSL' AND (unixepoch() - unixepoch(DATA_DATE)) < 604800 " .
               "ORDER BY DATA_DATE ASC;";
        if ($verbose) {
            echo('[Performing instruction ['.$sql.']] ' . PHP_EOL);
        }

        $results = $database->query($sql);

        if ($verbose) {
            echo ("Query Returned" . PHP_EOL);
        }

        $json_result = "[";
        $first = true;
        while ($row = $results->fetchArray()) { // TODO disply number of rows ?
            $dataDate = $row[0];
            $value = (float)$row[1];

            $next_element = "{ \"". $dataDate . "\": " . $value . " }";
            // echo $next_element . "<br/>" . PHP_EOL;
            $json_result = $json_result . ($first ? "" : ", ") . $next_element;
            $first = false;
        }
        $json_result = $json_result . "]";

        // On ferme !
        $database->close();
        if ($verbose) {
            echo("[Closed DB] ".PHP_EOL);
            echo "Finally, returning $json_result" . PHP_EOL;
        }
        return $json_result;

    } catch (Throwable $e) {
        echo "[ Captured Throwable for connection : " . $e->getMessage() . "] " . PHP_EOL;
        throw $e;
    }
    return null;
}

$VERBOSE = false; // Would generate an invalid JSON payload if true...
$hostname = $_SERVER['SERVER_NAME'];

if (true) {
    $request_verb = $_SERVER['REQUEST_METHOD'];
    if ($VERBOSE) {
        echo "[weather.php] Received request verb: $request_verb " . PHP_EOL;
    }
} else {
    var_dump($_SERVER);
}

// The service.
try {
    $db = new SQLite3('./sql/weather.db');

    $origin = $hostname; // "$user@$host/$db";

    if ($request_verb === 'GET') { // It's a query
        $data = getPRMSL_JSON($db, $VERBOSE);
        // header('Content-Type: application/json; charset=utf-8');
        // echo json_encode($data); // This is for text (not json)
        // echo $data;
        // Send data back to client
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(200);  // Order is important. This is BEFORE the content.
        echo "{ \"origin\": \"$origin\", \"data\": $data }";
    } else if ($request_verb === 'POST') {
        // Create new entry(ies)
        if (isset($_POST)) {
            // var_dump($_POST);
            if (isset($_POST['type']) && isset($_POST['value'])) {
                $type = $_POST['type'];
                $value = (float)$_POST['value'];
                // Prepare insert
                $stmt = $db->prepare('INSERT INTO WEATHER_DATA (TYPE, VALUE, DATA_DATE) VALUES (:type, :value, datetime("now"))');
                $stmt->bindValue(':type', $type, SQLITE3_TEXT);
                $stmt->bindValue(':value', $value, SQLITE3_FLOAT);
                $result = $stmt->execute();
                if ($result) {
                    // Success
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(201);  // Created
                    echo "{ \"status\": \"success\", \"message\": \"Entry created.\", \"type\": \"$type\", \"value\": $value }";
                } else {
                    throw new Exception("Failed to insert data.");
                }
            } else {
                throw new Exception("Missing 'type' or 'value' in POST data.");
            }
        } else {
            throw new Exception("POST method not implemented yet.");
        }
    } else {
        throw new Exception("Unsupported request verb: $request_verb");
    }
} catch (Throwable $e) {
    // echo "[Captured Throwable for weather.php : " . $e . "] " . PHP_EOL;
    // header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error - ' . $e, true, 500);
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo "{ \"error\": \"Internal Server Error\", \"message\": \"" . $e->getMessage() . "\" }";
    // echo $e;
}
