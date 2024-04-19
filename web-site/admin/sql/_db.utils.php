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
  

?>
