<?php

class Boat {
    public $id;
    public $name;
    public $type;
    public $category;
}
  
class Referent {
    public $email;
    public $firstName;
    public $lastName;
}
  
function getBoats(string $dbhost, string $username, string $password, string $database, bool $verbose): array {
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
        $sql = "SELECT ID, BOAT_NAME, BOAT_TYPE, CATEGORY FROM THE_FLEET;";
        if ($verbose) {
            echo('[Performing instruction ['.$sql.']] ');
        }
        
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>");
        }
  
        $boats = array();
        $boatIndex = 0;
        while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
            $boats[$boatIndex] = new Boat();
            $boats[$boatIndex]->id = $table[0];
            $boats[$boatIndex]->name = $table[1];
            $boats[$boatIndex]->type = $table[2];
            $boats[$boatIndex]->category = $table[3];
            $boatIndex++;
        }
        // On ferme !
        $link->close();
        if ($verbose) {
            echo("[Closed DB] ".PHP_EOL);
            echo "Finally, returning $boats";
        }
        return $boats;
  
    } catch (Throwable $e) {
        echo "[ Captured Throwable for connection : " . $e->getMessage() . "] " . PHP_EOL;
        throw $e;
    }                
    return null;
}
  
function getBoatsJSON(string $dbhost, string $username, string $password, string $database, bool $verbose): string {
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
            $next_element = "{ \"name\": \"" . urldecode($table[0]) . "\", \"id\": \"" . $table[1] . "\", \"pix\": \"" . $table[2] . "\", \"type\": \"" . urldecode($table[3]) . "\", \"category\": \"" . urldecode($table[4]) . "\", \"base\": \"" . urldecode($table[5]) . "\" } ";
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

function getMembers(string $dbhost, string $username, string $password, string $database, bool $verbose): array {
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
        $sql = "SELECT EMAIL, FIRST_NAME, LAST_NAME FROM PASSE_COQUE_MEMBERS;";
        if ($verbose) {
            echo('[Performing instruction ['.$sql.']] ');
        }
        
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>");
        }
  
        $members = array();
        $memberIndex = 0;
        while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
            $members[$memberIndex] = new Referent();
            $members[$memberIndex]->email = $table[0];
            $members[$memberIndex]->firstName = urldecode($table[1]);
            $members[$memberIndex]->lastName = urldecode($table[2]);
            $memberIndex++;
        }
        // On ferme !
        $link->close();
        if ($verbose) {
            echo("[Closed DB] ".PHP_EOL);
            echo "Finally, returning $boats";
        }
        return $members;
    } catch (Throwable $e) {
        echo "[ Captured Throwable for connection : " . $e->getMessage() . "] " . PHP_EOL;
        throw $e;
    }                
    return null;
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
    $sql =  "SELECT EMAIL, BOAT_ID, RESERVATION_DATE, DATE_FORMAT(FROM_DATE, '%Y-%m-%d'), DATE_FORMAT(TO_DATE, '%Y-%m-%d')TO_DATE, RESERVATION_STATUS, MISC_COMMENT FROM BC_RESERVATIONS " .
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
            echo ("Executing [" . $sql . "]<br/>" . PHP_EOL);
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

function getNbDays(int $year, int $month): int {
    $NB_DAYS = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    $nbDays = $NB_DAYS[$month - 1];
    if ($month == 2 && isLeapYear($year)) {
        $nbDays = 29;
    }
    return $nbDays;
}

function addMonth(int $year, int $month, int $toAdd): array {
    $month += $toAdd;
    while ($month > 12) {
        $month -= 12;
        $year += 1;
    }
    return array($year, sprintf('%02d', $month));
}

?>
