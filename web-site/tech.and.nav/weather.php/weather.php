<?php
/*
To be invoked as REST service, like here:

to select:
    curl -X GET "http://localhost/tech.and.nav/weather.php/weather.php?type=PRMSL&verbose=1" -H "accept: application/json"

to insert:
    curl -X POST "http://localhost/tech.and.nav/weather.php/weather.php" \
         -H "accept: application/json" \
         -H "Content-type: application/json; charset=UTF-8" \
         -d '{ "type": "AT", "value": 12 }'

from JavaScript:

    let prmslData = ".../weather.php?type=PRMSL";
    fetch(prmslData, {
        method: "GET",
        headers: {
                "Content-type": "application/json; charset=UTF-8"
            }
        }).then(response => {

        . . .

        });

    SQLite date functions, see https://www.sqlite.org/lang_datefunc.html

    select (unixepoch(datetime('now')) - unixepoch(data_date)) from weather_data;
    select (unixepoch() - unixepoch(data_date)) from weather_data;

    unixepoch: in seconds. One week = 7 * 24 * 60 * 60 = 604800 seconds
    unixepoch does not work on OVH...

    SELECT strftime('%FT%T', DATA_DATE) AS DURATION, VALUE
    FROM WEATHER_DATA
    WHERE TYPE='PRMSL' AND (unixepoch() - unixepoch(DATA_DATE)) < 604800
    ORDER BY DATA_DATE ASC;

    select data_date,
           value
    from weather_data
    where type='PRMSL' and ((julianday() - julianday(data_date)) * 86400) < (6048007)
    order by data_date asc;

    select 'Type ' || type || ' has ' || count(*) || ' entries' as result from weather_data group by type;

    select data_date,
           datetime('now'),
           julianday(data_date),
           julianday(),
           (julianday() - julianday(data_date)) * 86400 as diff_in_seconds
    from weather_data
    where type='PRMSL'
    order by data_date asc;

*/

ini_set('memory_limit', '-1'); // For no limit ...


function getData_JSON(SQLite3 $database, string $type, bool $verbose): string {
    try {
        if (false) {
            $sql = "SELECT * FROM WEATHER_DATA;";
        } else if (false) {
            $sql = "SELECT DATA_DATE, VALUE " .
                   "FROM WEATHER_DATA " .
                   ($type === "ALL" ? "" : "WHERE TYPE='" . SQLite3::escapeString($type) . "' ") .
                   "ORDER BY DATA_DATE ASC;";
        } else if (false) {
            $sql = "SELECT DATA_DATE, VALUE " .
                   "FROM WEATHER_DATA " .
                   ($type === "ALL" ? "" : "WHERE TYPE='" . SQLite3::escapeString($type) . "' ") .
                   "ORDER BY DATA_DATE ASC;";
        } else {
            // 86400 seconds in a day, 604800 seconds in a week
            $sql = // "SELECT strftime('%FT%T', DATA_DATE) AS DURATION, VALUE " .
                   "SELECT DATA_DATE, VALUE " .
                   "FROM WEATHER_DATA " .
                // "WHERE " . ($type === "ALL" ? "" :  "TYPE='" . SQLite3::escapeString($type) . "' AND ") . " ((julianday() - julianday(DATA_DATE)) * 86400) < (6048007) " .
                   "WHERE " . ($type === "ALL" ? "" :  "TYPE='" . SQLite3::escapeString($type) . "' AND ") . " (julianday() - julianday(DATA_DATE)) < 7 " .
                   "ORDER BY DATA_DATE ASC;";
        }
        if ($verbose) {
            echo('[Performing statement [' . $sql . ']] ' . PHP_EOL);
        }

        $results = $database->query($sql); // returns 'false' if problem...

        if ($verbose) {
            echo ("Query Returned" . PHP_EOL);
        }

        $json_result = "[";
        $first = true;

        if ($results === false) {
            throw new Exception("Query [$sql] failed.");
        }
        while ($row = $results->fetchArray()) { // TODO display number of rows ?
            $dataDate = $row[0];
            $value = (float)$row[1];

            // str_replace to replace the space with T for JSON date-time format
            $next_element = "{ \"". str_replace(" ","T", $dataDate) . "\": " . $value . " }";
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
        if ($verbose) {
            echo "[ Captured Throwable for getPRMSL_JSON : " . $e->getMessage() . "] " . PHP_EOL;
        }
        throw $e;
    }
    return null;
}

$VERBOSE = false; // Would generate an invalid JSON payload if true... But still. See QS Prm 'verbose'
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
        $type = "ALL";
        if (isset($_GET['type'])) {
            $type = $_GET['type'];
            if ($VERBOSE) {
                echo "Received type parameter: $type" . PHP_EOL;
            }
        }
        $verbose = $VERBOSE;
        if (isset($_GET['verbose'])) {
            $verbose = $_GET['verbose'] === '1' || strtolower($_GET['verbose']) === 'true';
            if ($VERBOSE) {
                echo "Received verbose parameter: $verbose" . PHP_EOL;
            }
        }
        $data = getData_JSON($db, $type, $verbose);
        // header('Content-Type: application/json; charset=utf-8');
        // echo json_encode($data); // This is for text (not json)
        // echo $data;
        // Send data back to client
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(200);  // Order is important. This is BEFORE the content.
        echo "{ \"origin\": \"$origin\", \"type\": \"$type\" , \"data\": $data }";
    } else if ($request_verb === 'POST') {
        // Create new entry(ies)
        if (isset($_POST)) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if ($VERBOSE) {
                echo "------------------------" . PHP_EOL;
                echo "Received JSON data: " . $json . PHP_EOL;
                // echo $data . PHP_EOL;
                echo "------------------------" . PHP_EOL;
            }

            $type = $data['type'] ?? null;
            $value = isset($data['value']) ? (float)$data['value'] : null;

            // if called with -d type=AT -d value=12
            // $type = $_POST['type'];
            // $value = (float)$_POST['value'];

            if ($type !== null && $value !== null) {
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
                throw new Exception("Missing 'type' and/or 'value' in POST data.");
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
    // Content
    echo "{ \"error\": \"Internal Server Error\", \"message\": \"" . $e->getMessage() . "\" }";
    // echo $e;
}
