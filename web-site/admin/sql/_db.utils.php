<?php
/**
 * Several utilities, functions and classes
 * used in several places.
 */

class Boat {
    public $id;
    public $name;
    public $type;
    public $category;
    public $base;
}

class Member {
    public $email;
    public $firstName;
    public $lastName;
    public $telephone;
    public $status;
}

class Project {
    public $id;
    public $name;
    public $description;
}

function getProjects(string $dbhost, string $username, string $password, string $database, bool $verbose): array {
    try {
        $link = new mysqli($dbhost, $username, $password, $database);

        if ($link->connect_errno) {
            echo("[Oops, errno:".$link->connect_errno."...] ");
            // die("Connection failed: " . $conn->connect_error);
            throw $conn->connect_error;
        } else {
            if ($verbose) {
                echo("Connected.<br/>" . PHP_EOL);
            }
        }
        $sql = "SELECT PROJECT_ID, PROJECT_NAME, DESCRIPTION FROM PROJECTS;";
        if ($verbose) {
            echo('[Performing instruction ['.$sql.']] <br/>' . PHP_EOL);
        }

        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
        }

        $projects = array();
        $projectIdx = 0;
        while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
            $projects[$projectIdx] = new Project();
            $projects[$boatIndex]->id = $table[0];
            $projects[$boatIndex]->name = $table[1];
            $projects[$boatIndex]->description = $table[3];
            $boatIndex++;
        }
        // On ferme !
        $link->close();
        if ($verbose) {
            echo("[Closed DB] ".PHP_EOL);
            echo "Finally, returning $projects";
        }
        return $projects;

    } catch (Throwable $e) {
        echo "[ Captured Throwable for connection : " . $e->getMessage() . "] " . PHP_EOL;
        throw $e;
    }
    return null;
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
                echo("Connected.<br/>" . PHP_EOL);
            }
        }
        $sql = "SELECT ID, BOAT_NAME, BOAT_TYPE, CATEGORY, BASE FROM THE_FLEET;";
        if ($verbose) {
            echo('[Performing instruction ['.$sql.']] <br/>' . PHP_EOL);
        }

        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
        }

        $boats = array();
        $boatIndex = 0;
        while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
            $boats[$boatIndex] = new Boat();
            $boats[$boatIndex]->id = $table[0];
            $boats[$boatIndex]->name = $table[1];
            $boats[$boatIndex]->type = $table[2];
            $boats[$boatIndex]->category = $table[3];
            $boats[$boatIndex]->base = $table[4];
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

function getBoatName(string $dbhost, string $username, string $password, string $database, string $boatId, bool $verbose): string {
    try {
        $link = new mysqli($dbhost, $username, $password, $database);

        if ($link->connect_errno) {
            echo("[Oops, errno:".$link->connect_errno."...] ");
            // die("Connection failed: " . $conn->connect_error);
            throw $conn->connect_error;
        } else {
            if ($verbose) {
                echo("Connected.<br/>" . PHP_EOL);
            }
        }
        $sql = "SELECT BOAT_NAME FROM THE_FLEET WHERE ID = '" . $boatId . "';";
        if ($verbose) {
            echo('[Performing instruction ['.$sql.']] <br/>' . PHP_EOL);
        }

        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
        }

        $boatName = 'Not found';
        while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
            $boatName = $table[0];
        }
        // On ferme !
        $link->close();
        if ($verbose) {
            echo("[Closed DB] ".PHP_EOL);
            echo "Finally, returning $boats";
        }
        return $boatName;

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
                echo("Connected.<br/>" . PHP_EOL);
            }
        }
        $sql = "SELECT * FROM THE_FLEET;";
        if ($verbose) {
            echo('[Performing instruction ['.$sql.']] <br/>' . PHP_EOL);
        }

        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
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
                echo("Connected.<br/>" . PHP_EOL);
            }
        }
        $sql = "SELECT EMAIL, FIRST_NAME, LAST_NAME, TELEPHONE, TARIF FROM PASSE_COQUE_MEMBERS;";
        if ($verbose) {
            echo('[Performing instruction ['.$sql.']] <br/>' . PHP_EOL);
        }

        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
        }

        $members = array();
        $memberIndex = 0;
        while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
            $members[$memberIndex] = new Member();
            $members[$memberIndex]->email = $table[0];
            $members[$memberIndex]->firstName = urldecode($table[1]);
            $members[$memberIndex]->lastName = urldecode($table[2]);
            $members[$memberIndex]->telephone = $table[3];
            $members[$memberIndex]->status = $table[4];
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

function getMember(string $dbhost, string $username, string $password, string $database, string $email, bool $verbose=false): array {
    try {
        $link = new mysqli($dbhost, $username, $password, $database);

        if ($link->connect_errno) {
            echo("[Oops, errno:".$link->connect_errno."...] ");
            // die("Connection failed: " . $conn->connect_error);
            throw $conn->connect_error;
        } else {
            if ($verbose) {
                echo("Connected.<br/>" . PHP_EOL);
            }
        }
        $sql = "SELECT EMAIL, FIRST_NAME, LAST_NAME, TELEPHONE, TARIF FROM PASSE_COQUE_MEMBERS WHERE EMAIL = '$email';";
        if ($verbose) {
            echo('[Performing instruction ['.$sql.']] <br/>' . PHP_EOL);
        }

        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
        }

        $members = array();
        $memberIndex = 0;
        while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
            $members[$memberIndex] = new Member();
            $members[$memberIndex]->email = $table[0];
            $members[$memberIndex]->firstName = urldecode($table[1]);
            $members[$memberIndex]->lastName = urldecode($table[2]);
            $members[$memberIndex]->telephone = $table[3];
            $members[$memberIndex]->status = $table[4];
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

function getBCMember(string $dbhost, string $username, string $password, string $database, string $email, bool $verbose=false): array {
    try {
        $link = new mysqli($dbhost, $username, $password, $database);

        if ($link->connect_errno) {
            echo("[Oops, errno:".$link->connect_errno."...] ");
            // die("Connection failed: " . $conn->connect_error);
            throw $conn->connect_error;
        } else {
            if ($verbose) {
                echo("Connected.<br/>" . PHP_EOL);
            }
        }
        $sql = "SELECT BCM.EMAIL, PCM.FIRST_NAME, PCM.LAST_NAME, PCM.TELEPHONE FROM BOAT_CLUB_MEMBERS BCM, PASSE_COQUE_MEMBERS PCM WHERE BCM.EMAIL = '$email' AND BCM.EMAIL = PCM.EMAIL;";
        if ($verbose) {
            echo('[Performing instruction ['.$sql.']] <br/>' . PHP_EOL);
        }

        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
        }

        $members = array();
        $memberIndex = 0;
        while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
            $members[$memberIndex] = new Member();
            $members[$memberIndex]->email = $table[0];
            $members[$memberIndex]->firstName = utf8_encode($table[1]);
            $members[$memberIndex]->lastName = utf8_encode($table[2]);
            $members[$memberIndex]->telephone = $table[3];
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

function getReservations(string $dbhost, string $username, string $password, string $database, string $boatId, string $from, string $to, bool $admin, bool $verbose) : array {
    // $sql =  "SELECT EMAIL, BOAT_ID, RESERVATION_DATE, DATE_FORMAT(FROM_DATE, '%Y-%m-%d'), DATE_FORMAT(TO_DATE, '%Y-%m-%d'), RESERVATION_STATUS, MISC_COMMENT FROM BC_RESERVATIONS " .
    //         "WHERE ((FROM_DATE <= STR_TO_DATE('" . $from . "', '%Y-%m-%d') AND FROM_DATE <= STR_TO_DATE('" . $to . "', '%Y-%m-%d')) OR " .
    //             " (TO_DATE >= STR_TO_DATE('" . $from . "', '%Y-%m-%d') AND TO_DATE >= STR_TO_DATE('" . $to . "', '%Y-%m-%d'))) AND " .
    //             "BOAT_ID = '" . $boatId . "' " .
    //             ( $admin ? "" : "AND RESERVATION_STATUS NOT IN ('CANCELED', 'REJECTED') " ) .
    //             "ORDER BY FROM_DATE;";

    $sql =  "SELECT EMAIL, BOAT_ID, RESERVATION_DATE, DATE_FORMAT(FROM_DATE, '%Y-%m-%d'), DATE_FORMAT(TO_DATE, '%Y-%m-%d'), RESERVATION_STATUS, MISC_COMMENT FROM BC_RESERVATIONS " .
            "WHERE (FROM_DATE <= STR_TO_DATE('" . $to . "', '%Y-%m-%d') AND TO_DATE >= STR_TO_DATE('" . $from . "', '%Y-%m-%d')) AND " .
                    "BOAT_ID = '" . $boatId . "' " .
                    ( $admin ? "" : "AND RESERVATION_STATUS NOT IN ('CANCELED', 'REJECTED') " ) .
                    "ORDER BY FROM_DATE;";

    $reservations = array();
    $index = 0;

    // $verbose = true; // This is for debug

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
                echo("Connected.<br/>" . PHP_EOL);
            }
        }

        if ($verbose) {
            echo ("Executing [" . $sql . "]<br/>" . PHP_EOL);
        }
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
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
                echo("Connected.<br/>" . PHP_EOL);
            }
        }

        if ($verbose) {
            echo ("Executing [" . $sql . "]<br/>" . PHP_EOL);
        }
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
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

class BoatAvailability {
    public $status;
    public $message;
}

function checkBoatAvailability(string $dbhost, string $username, string $password, string $database, string $boatId, string $fromDate, string $toDate, bool $verbose) : BoatAvailability {
    $boatAvailability = new BoatAvailability();
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
                echo("Connected.<br/>" . PHP_EOL);
            }
        }

        $sql =  "SELECT EMAIL, BOAT_ID, RESERVATION_DATE, DATE_FORMAT(FROM_DATE, '%Y-%b-%d'), DATE_FORMAT(TO_DATE, '%Y-%b-%d'), RESERVATION_STATUS, MISC_COMMENT FROM BC_RESERVATIONS " .
                "WHERE ((STR_TO_DATE('$fromDate', '%Y-%m-%d') BETWEEN FROM_DATE AND TO_DATE) OR " .
                    "(STR_TO_DATE('$toDate', '%Y-%m-%d') BETWEEN FROM_DATE AND TO_DATE) OR " .
                    "(FROM_DATE BETWEEN STR_TO_DATE('$fromDate', '%Y-%m-%d') AND STR_TO_DATE('$toDate', '%Y-%m-%d')) OR " .
                    "(TO_DATE BETWEEN STR_TO_DATE('$fromDate', '%Y-%m-%d') AND STR_TO_DATE('$toDate', '%Y-%m-%d'))) AND " .
                    "BOAT_ID = '" . $boatId . "' AND " .
                    "RESERVATION_STATUS NOT IN ('TENTATIVE', 'CANCELED', 'REJECTED') " .  // Only CONFIRMED, ADMIN left
                    "ORDER BY FROM_DATE;";

        if ($verbose) {
            echo ("Executing [" . $sql . "]<br/>" . PHP_EOL);
        }
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
        }
        if ($result->num_rows == 0) {
            $boatAvailability->status = true;
        } else {
            $message = '';
            while ($table = mysqli_fetch_array($result)) {
                $message .= ($table[0] . " reserved from " . $table[3] . " to " . $table[4] . "<br/>");
            }
            $boatAvailability->status = false;
            $boatAvailability->message = $message;
        }
        // On ferme !
        $link->close();
        if ($verbose) {
            echo("Closed DB<br/>".PHP_EOL);
        }
    } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }

    return $boatAvailability;
}

class AllBoatDetails {
    public $refEmail;
    public $refFullName;
    public $boatId;
    public $boatName;
    public $boatType;
    public $boatBase;
    public $refTel;
}

function getBoatAndReferentDetails(string $dbhost, string $username, string $password, string $database, string $boatId, bool $verbose=false) : array {
    $sql = "SELECT M.EMAIL, " .
                  "CONCAT(M.FIRST_NAME, ' ', UPPER(M.LAST_NAME)), " .
                  "B.BOAT_NAME, " .
                  "B.BOAT_TYPE, " .
                  "B.BASE, " .
                  "R.TELEPHONE " .
            "FROM PASSE_COQUE_MEMBERS M, ".
                 "THE_FLEET B, " .
                 "REFERENTS R " .
            "WHERE R.BOAT_ID = B.ID AND " .
                  "B.ID = '" . $boatId . "' AND " .
                  "B.CATEGORY = 'CLUB' AND " .
                  "R.EMAIL = M.EMAIL;";

    $allDetails = array();
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
                echo("Connected.<br/>" . PHP_EOL);
            }
        }

        if ($verbose) {
            echo ("Executing [" . $sql . "]<br/>" . PHP_EOL);
        }
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
        }
        while ($table = mysqli_fetch_array($result)) {
            $allDetails[$index] = new AllBoatDetails();
            $allDetails[$index]->refEmail = $table[0];
            $allDetails[$index]->refFullName = urldecode($table[1]);
            $allDetails[$index]->boatId = $boatId;
            $allDetails[$index]->boatName = $table[2];
            $allDetails[$index]->boatType = $table[3];
            $allDetails[$index]->boatBase = $table[4];
            $allDetails[$index]->refTel = $table[5];
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

    return $allDetails;
}

// For the boats of the CLUB only
function getBoatsByReferent(string $dbhost, string $username, string $password, string $database, string $userId, bool $verbose=false) : array {
    $sql = "SELECT B.ID, B.BOAT_NAME, B.BOAT_TYPE, B.BASE " .
           "FROM PASSE_COQUE_MEMBERS M, THE_FLEET B, REFERENTS R " .
           "WHERE R.BOAT_ID = B.ID AND B.CATEGORY = 'CLUB' AND R.EMAIL = M.EMAIL AND M.EMAIL = '$userId';";

    $boats = array();
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
                echo("Connected.<br/>" . PHP_EOL);
            }
        }

        if ($verbose) {
            echo ("Executing [" . $sql . "]<br/>" . PHP_EOL);
        }
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
        }
        while ($table = mysqli_fetch_array($result)) {
            $boats[$index] = $table[0];
            $index++;
        }
        // On ferme !
        $link->close();
        if ($verbose) {
            echo("Closed DB<br/>" . PHP_EOL);
        }
    } catch (Throwable $e) {
        echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }

    return $boats;
}

// Not CLUB only
function getAllBoatsByReferent(string $dbhost, string $username, string $password, string $database, string $userId, bool $admin=false, bool $verbose=false) : array {
    $sql = "SELECT BR.BOAT_ID, BR.BOAT_NAME, BR.BASE, CONCAT(PC.FIRST_NAME, ' ', UPPER(PC.LAST_NAME)), BR.EMAIL " .
           "FROM BOATS_AND_REFERENTS BR, PASSE_COQUE_MEMBERS PC " .
           "WHERE BR.EMAIL = PC.EMAIL AND (('$userId' = '') OR ('$userId' <> '' AND BR.EMAIL = '$userId') OR " . ($admin ? 1 : 0) . ");";

    $boats = array();
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
                echo("Connected.<br/>" . PHP_EOL);
            }
        }

        if ($verbose) {
            echo ("Executing [" . $sql . "]<br/>" . PHP_EOL);
        }
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
        }
        while ($table = mysqli_fetch_array($result)) {
            $boatData = array();
            $boatData[0] = $table[0]; // Boat ID
            $boatData[1] = $table[1]; // Boat Name
            $boatData[2] = $table[2]; // Boat Base
            $boatData[3] = $table[3]; // Referent name
            $boatData[4] = $table[4]; // Referent email

            $boats[$index] = $boatData;
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

    return $boats;
}

function getDistinctBoatsWithReferents(string $dbhost, string $username, string $password, string $database, bool $verbose=false) : array {
    $sql = "SELECT DISTINCT BR.BOAT_ID,
                   BR.BOAT_NAME,
                   BR.BASE,
                   (SELECT GROUP_CONCAT(CONCAT(' ', PCM.FIRST_NAME, ' ', UPPER(PCM.LAST_NAME)))
                       FROM BOATS_AND_REFERENTS BR2, PASSE_COQUE_MEMBERS PCM
                       WHERE BR2.BOAT_ID = BR.BOAT_ID AND PCM.EMAIL = BR2.EMAIL) AS REFERENTS,
                   (SELECT GROUP_CONCAT(CONCAT(' ', PCM.EMAIL))
                       FROM BOATS_AND_REFERENTS BR2, PASSE_COQUE_MEMBERS PCM
                       WHERE BR2.BOAT_ID = BR.BOAT_ID AND PCM.EMAIL = BR2.EMAIL) AS REF_EMAILS
            FROM BOATS_AND_REFERENTS BR;";

    $boats = array();
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
                echo("Connected.<br/>" . PHP_EOL);
            }
        }

        if ($verbose) {
            echo ("Executing [" . $sql . "]<br/>" . PHP_EOL);
        }
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
        }
        while ($table = mysqli_fetch_array($result)) {
            $boatData = array();
            $boatData[0] = $table[0]; // Boat ID
            $boatData[1] = $table[1]; // Boat Name
            $boatData[2] = $table[2]; // Boat Base
            $boatData[3] = trim($table[3]); // Referent(s) name(s)
            $boatData[4] = trim($table[4]); // Referent(s) emails

            $boats[$index] = $boatData;
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

    return $boats;
}

function getBoatsTODOList(string $dbhost, string $username, string $password, string $database, string $userId, string $boatId, bool $verbose=false) : array {
    $sql = "SELECT BOAT_ID, LINE_ID, LINE_DESC, unix_timestamp(CREATION_DATE), LINE_STATUS, unix_timestamp(LAST_UPDATED)
            FROM TODO_LISTS
            WHERE BOAT_ID = '$boatId'
            ORDER BY LINE_ID;";

    $lines = array();
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
                echo("Connected.<br/>" . PHP_EOL);
            }
        }

        if ($verbose) {
            echo ("Executing [" . $sql . "]<br/>" . PHP_EOL);
        }
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
        }
        while ($table = mysqli_fetch_array($result)) {
            $lineData = array();
            $lineData[0] = $table[0]; // Boat ID
            $lineData[1] = $table[1]; // LINE_ID
            $lineData[2] = $table[2]; // LINE_DESC
            $lineData[3] = date("Y-M-d H:i:s",$table[3]); // CREATION_DATE
            $lineData[4] = $table[4]; // LINE_STATUS
            $lineData[5] = ($table[5] != null && $table[5] != '') ? date("Y-M-d H:i:s",$table[5]) : $table[5]; // LAST_UPDATED

            $lines[$index] = $lineData;
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

    return $lines;
}

function getTODOListLine(string $dbhost, string $username, string $password, string $database, int $lineId, bool $verbose=false) : array {
    $sql = "SELECT BOAT_ID, LINE_ID, LINE_DESC, unix_timestamp(CREATION_DATE), LINE_STATUS, unix_timestamp(LAST_UPDATED)
            FROM TODO_LISTS
            WHERE LINE_ID = $lineId;";

    $line = array(); // On line only

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
                echo("Connected.<br/>" . PHP_EOL);
            }
        }

        if ($verbose) {
            echo ("Executing [" . $sql . "]<br/>" . PHP_EOL);
        }
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
        }
        while ($table = mysqli_fetch_array($result)) {
            $line[0] = $table[0]; // Boat ID
            $line[1] = $table[1]; // LINE_ID
            $line[2] = $table[2]; // LINE_DESC
            $line[3] = date("Y-M-d H:i:s", $table[3]); // CREATION_DATE
            $line[4] = $table[4]; // LINE_STATUS
            $line[5] = $table[5]; // LAST_UPDATED

            // echo ("Could be null: ($table[5])");
            if ($table[5] != null & $table[5] != '') {
                $line[5] = date("Y-M-d H:i:s", $table[5]);
            }

            if (false) {
                echo("Dates are [$line[3]] and [$line[5]]");
            }
        }
        // On ferme !
        $link->close();
        if ($verbose) {
            echo("Closed DB<br/>".PHP_EOL);
        }
    } catch (Throwable $e) {
        echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }

    return $line;
}

class MemberStatus {
    public static $PASSE_COQUE_AND_BOAT_CLUB_MEMBER = 0;
    public static $NO_PASSE_COQUE_MEMBER = 1;
    public static $NO_BOAT_CLUB_MEMBER = 2;

    public $status;  // bool
    public $errNo;   // int. O: Passe-Coque & Boat-Club, 1: Not Passe-Coque, 2: Not Boat-Club
    public $errMess; // string
}

function checkMemberShip(string $dbhost, string $username, string $password, string $database, string $userId, bool $verbose) : MemberStatus {

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
                echo("Connected.<br/>" . PHP_EOL);
            }
        }

        $memberStatus = new MemberStatus();

        $sql = "SELECT PC.EMAIL, (SELECT BC.EMAIL FROM BOAT_CLUB_MEMBERS BC WHERE BC.EMAIL = PC.EMAIL) FROM PASSE_COQUE_MEMBERS PC WHERE PC.EMAIL = '$userId';";

        if ($verbose) {
            echo ("Executing [" . $sql . "]<br/>" . PHP_EOL);
        }
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
        }
        if ($result->num_rows == 0) {
            $memberStatus->status = false;
            $memberStatus->errNo = MemberStatus::$NO_PASSE_COQUE_MEMBER;
            $memberStatus->errMess = "Not a Passe-Coque Member";
        } else {
            // Assume there is only one record returned.
            $pcEmail = '';
            $bcEmail = '';
            while ($table = mysqli_fetch_array($result)) {
                $pcEmail = $table[0];
                $bcEmail = $table[1];
            }
            if ($bcEmail == null || strlen($bcEmail) == 0) {
                $memberStatus->status = false;
                $memberStatus->errNo = MemberStatus::$NO_BOAT_CLUB_MEMBER;
                $memberStatus->errMess = "Passe-Coque Member, but not Boat Club Member";
            } else {
                $memberStatus->status = true;
                $memberStatus->errNo = MemberStatus::$PASSE_COQUE_AND_BOAT_CLUB_MEMBER;
                $memberStatus->errMess = "PC & BC";
            }
        }
        // On ferme !
        $link->close();
        if ($verbose) {
            echo("Closed DB<br/>".PHP_EOL);
        }
    } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }
    return $memberStatus;
}

class HelpRequest {
    public $idx;
    public $owner;
    public $boat;
    public $created;
    public $from;
    public $to;
    public $type;
    public $comment;
}

function getAllHelpRequests(string $dbhost, string $username, string $password, string $database, bool $verbose) : array {
    $sql =  "SELECT IDX, ORIGIN_EMAIL, BOAT_ID, DATE_FORMAT(CREATION_DATE, '%Y-%m-%d'), DATE_FORMAT(FROM_DATE, '%Y-%m-%d'), DATE_FORMAT(TO_DATE, '%Y-%m-%d'), HELP_TYPE, MISC_COMMENT FROM HELP_REQUESTS " .
            "ORDER BY FROM_DATE;";
    $requests = array();
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
                echo("Connected.<br/>" . PHP_EOL);
            }
        }

        if ($verbose) {
            echo ("Executing [" . $sql . "]<br/>" . PHP_EOL);
        }
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
        }
        while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
            $requests[$index] = new HelpRequest();
            $requests[$index]->idx = $table[0];
            $requests[$index]->owner = $table[1];
            $requests[$index]->boat = $table[2];
            $requests[$index]->created = $table[3];
            $requests[$index]->from = $table[4];
            $requests[$index]->to = $table[5];
            $requests[$index]->type = $table[6];
            $requests[$index]->comment = $table[7];
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

    return $requests;
}

function getHelpRequests(string $dbhost, string $username, string $password, string $database, string $helpType, bool $verbose) : array {
    $sql =  "SELECT IDX, ORIGIN_EMAIL, BOAT_ID, DATE_FORMAT(CREATION_DATE, '%Y-%m-%d'), DATE_FORMAT(FROM_DATE, '%Y-%m-%d'), DATE_FORMAT(TO_DATE, '%Y-%m-%d'), HELP_TYPE, MISC_COMMENT FROM HELP_REQUESTS " .
            "WHERE HELP_TYPE = '" . $helpType . "' " .
            // TODO START_DATE > now
            "ORDER BY FROM_DATE;";
    $requests = array();
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
                echo("Connected.<br/>" . PHP_EOL);
            }
        }

        if ($verbose) {
            echo ("Executing [" . $sql . "]<br/>" . PHP_EOL);
        }
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
        }
        while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
            $requests[$index] = new HelpRequest();
            $requests[$index]->idx = $table[0];
            $requests[$index]->owner = $table[1];
            $requests[$index]->boat = $table[2];
            $requests[$index]->created = $table[3];
            $requests[$index]->from = $table[4];
            $requests[$index]->to = $table[5];
            $requests[$index]->type = $table[6];
            $requests[$index]->comment = $table[7];
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

    return $requests;
}

function getHelpRequestById(string $dbhost, string $username, string $password, string $database, string $idx, bool $verbose) : array {
    $sql =  "SELECT IDX, ORIGIN_EMAIL, BOAT_ID, DATE_FORMAT(CREATION_DATE, '%Y-%m-%d'), DATE_FORMAT(FROM_DATE, '%Y-%m-%d'), DATE_FORMAT(TO_DATE, '%Y-%m-%d'), HELP_TYPE, MISC_COMMENT FROM HELP_REQUESTS " .
            "WHERE IDX = " . $idx . ";";
    $requests = array();
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
                echo("Connected.<br/>" . PHP_EOL);
            }
        }

        if ($verbose) {
            echo ("Executing [" . $sql . "]<br/>" . PHP_EOL);
        }
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
        }
        while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
            $requests[$index] = new HelpRequest();
            $requests[$index]->idx = $table[0];
            $requests[$index]->owner = $table[1];
            $requests[$index]->boat = $table[2];
            $requests[$index]->created = $table[3];
            $requests[$index]->from = $table[4];
            $requests[$index]->to = $table[5];
            $requests[$index]->type = $table[6];
            $requests[$index]->comment = $table[7];
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
    return $requests;
}

function getHelpRequestByUserId(string $dbhost, string $username, string $password, string $database, string $userId, bool $verbose) : array {
    $sql =  "SELECT IDX, ORIGIN_EMAIL, BOAT_ID, DATE_FORMAT(CREATION_DATE, '%Y-%m-%d'), DATE_FORMAT(FROM_DATE, '%Y-%m-%d'), DATE_FORMAT(TO_DATE, '%Y-%m-%d'), HELP_TYPE, MISC_COMMENT FROM HELP_REQUESTS " .
            "WHERE ORIGIN_EMAIL = '" . $userId . "';";
    $requests = array();
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
                echo("Connected.<br/>" . PHP_EOL);
            }
        }

        if ($verbose) {
            echo ("Executing [" . $sql . "]<br/>" . PHP_EOL);
        }
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
        }
        while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
            $requests[$index] = new HelpRequest();
            $requests[$index]->idx = $table[0];
            $requests[$index]->owner = $table[1];
            $requests[$index]->boat = $table[2];
            $requests[$index]->created = $table[3];
            $requests[$index]->from = $table[4];
            $requests[$index]->to = $table[5];
            $requests[$index]->type = $table[6];
            $requests[$index]->comment = $table[7];
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
    return $requests;
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
            die("Connection failed: " . $conn->connect_error); // TODO Throw an exception ?
            // throw $conn->connect_error;
        } else {
            if ($verbose) {
                echo("Connected.<br/>" . PHP_EOL);
            }
        }

        if ($verbose) {
            echo ("Executing [" . $sql . "]<br/>" . PHP_EOL);
        }
        if (true) { // Do perform ?
            if ($link->query($sql) === TRUE) {
                if ($verbose) {
                    echo "OK. Statement executed successfully<br/><hr/>" . PHP_EOL;
                }
            } else {
                if ($verbose) {
                    echo "ERROR executing: " . $sql . "<br/>" . $link->error . "<br/>" . PHP_EOL;
                }
                throw new Exception($link->error);
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
        if ($verbose) {
            echo "Captured Throwable for executeSQL : " . $e->getMessage() . "<br/>" . PHP_EOL;
        }
        throw new Exception($e->getMessage());
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
