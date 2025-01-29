<?php
// Must be on top. This page generates NO HTML tags.
$timeout = 300;  // In seconds
$applyTimeout = false; // Change at will

try {
  if (!isset($_SESSION)) {
    if ($applyTimeout) {
      ini_set("session.gc_maxlifetime", $timeout);
      ini_set("session.cookie_lifetime", $timeout);
    }
    session_start();
  }
} catch (Throwable $e) {
  echo "Session settings: Captured Throwable: " . $e->getMessage() . "<br/>" . PHP_EOL;
}

// TODO List for one boat, format CSV

// phpinfo();

require __DIR__ . "/../../php/db.cred.php";
require __DIR__ . "/_db.utils.php";
// require __DIR__ . "/_emails.utils.php";

$VERBOSE = false; // Change at will

function translateStatus(string $lang, string $original) : string {
    $translated = $original;
    try {
        switch ($original) {
            case 'OPENED':
                $translated = ($lang == 'FR') ? "À faire" : "To Do";
                break;
            case 'CANCELED':
                $translated = ($lang == 'FR') ? "Annulé" : "Canceled";
                break;
            case 'COMPLETED':
                $translated = ($lang == 'FR') ? "Terminé" : "Completed";
                break;
            case 'IN_PROGRESS':
                $translated = ($lang == 'FR') ? "En cours" : "In progess";
                break;
            default:
                break;
        }
    } catch (Throwable $e) {
        echo "Captured Throwable for a switch : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }
    return $translated;
}

$MONTHS = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");

// Find current date (for the month)
if (false) {
    echo "Today is " . date("Y/m/d") . "<br>";
    echo "Today is " . date("Y.m.d") . "<br>";
    echo "Today is " . date("Y-m-d") . "<br>";
    echo "Today is " . date("l") . "<br>";
}

$adminPriv = false;
if (isset($_SESSION['ADMIN'])) {
    $adminPriv = $_SESSION['ADMIN'];
}
// echo("Admin privileges: " . ($adminPriv ? "yes" : "no") . "<br/>");
$userId = '';
if (isset($_SESSION['USER_NAME'])) {
    $userId = $_SESSION['USER_NAME'];
}

$currentYear = date("Y");
// echo("Current year is " . $currentYear . ", next will be " . ($currentYear + 1) . "<br/>");
$currentMonth = date("m");

$lang = 'FR';
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
}

$operation = 'list';  // Default operation
if (isset($_POST['operation'])) {
    $operation = $_POST['operation'];
} else if (isset($_GET['operation'])) {
    $operation = $_GET['operation'];
}

$option = null;
// Create, Update, Retrieve, Delete ?
if (isset($_GET['option'])) {
    $option = $_GET['option'];
}

if ($VERBOSE) {
    // echo ("This month (" . $currentYear . " - " . $MONTHS[$currentMonth - 1] . ") we have " . getNbDays($currentYear, $currentMonth) . " days.<br/>");
    echo("operation: [" . $operation . "], option: [" . $option . "]<br/>" . PHP_EOL);
    echo("UserID: [" . $userId . "], Admin Priv: [" . ($adminPriv ? 'yes' : 'no') . "]<br/>" . PHP_EOL);
}

if ($operation == "csv-list") {

    // ------- CSV-LIST -----
    $boat_id = $_GET['boat-id']; 
    $contact = $_GET['ref'];
    $completedOnly = false;
    if (isset($_GET['completed-option'])) {
        $completedValue = $_GET['completed-option'];
        $completedOnly = ($completedValue == 'restricted');
    }

    if ($VERBOSE) {
        echo("Will get todo list for $boat_id, from $contact<br/>" . PHP_EOL);
    }
    $boatName = getBoatName($dbhost, $username, $password, $database, $boat_id, $VERBOSE);
    $todoLines = getBoatsTODOList($dbhost, $username, $password, $database, $contact, $boat_id, $VERBOSE);

    if ($VERBOSE) {
        echo ("Got " . count($todoLines) . " lines for '$boat_id'.<br/>" . PHP_EOL);
        try {
            echo ("Can Modify: Admin [$adminPriv], [$userId] in [$contact]: [" . (strpos($contact, $userId) !== false ? 'yes' : 'no') . "]<br/>" . PHP_EOL);
        } catch (Throwable $e) {
            echo "Captured Throwable for str_contains/strpos : " . $e->getMessage() . "<br/>" . PHP_EOL;
        }
    }                        

    $delimiter = ";"; //","; 
    $filename = "todo_" . $boat_id . "_" . date('Y-m-d') . ".csv"; 
     
    // Create a file pointer 
    $f = fopen('php://memory', 'w'); 
     
    // Set column headers 
    $fields = array('TASK', 
                    'CREATED', 
                    'STATUS', 
                    'UPDATED'); 
    fputcsv($f, $fields, $delimiter); 

    if (count($todoLines) == 0) {
        echo ((($lang == 'FR') ? "Rien sur la TODO list de $boatName..." : "Nothing on the TODO list for $boatName...") . "<br/>" . PHP_EOL);
    } else {
        // echo("As $contact, $userId ...");
        // ------------------------------------
        foreach ($todoLines as $line) {
            $display = true;
            if ($display) {
                $lineData = array(/*utf8_encode*/($line[2]), // Description
                                  /*utf8_encode*/($line[3]), // Created
                                  /*utf8_encode*/(translateStatus($lang, $line[4])), // Status
                                  /*utf8_encode*/($line[5])  // Updated 
                                  ); 
                fputcsv($f, $lineData, $delimiter); 
            }
        }
        // Move back to beginning of file 
        fseek($f, 0); 
        
        // Set headers to download file rather than displayed 
        header('Content-Type: text/csv'); 
        header('Content-Disposition: attachment; filename="' . $filename . '";'); 
        
        // output all remaining data on a file pointer 
        fpassthru($f); 
    }
    // ------- End of CSV-LIST ----

} else {
    echo ("Unknown operation [" . $operation . "]" . PHP_EOL);
}
?>
