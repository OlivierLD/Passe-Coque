<?php
// Must be on top
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
?>
<html lang="en">
  <!--
   ! WiP.
   ! Help requests list - 3 month. (see the $span variable)
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Help Requests</title>
    <style type="text/css">
      * {
        font-family: 'Courier New'
      }

      tr > td {
        border: 1px solid silver;
      }

      ul > li {
        display: grid;
        grid-template-columns: 90% 10%;
      }
    </style>
  </head>
  <body>
    <!--h1>PHP / MySQL. Help Request Planning</h1-->

    <?php
// phpinfo();

require __DIR__ . "/../../php/db.cred.php";
require __DIR__ . "/_db.utils.php";
require __DIR__ . "/_emails.utils.php";

$VERBOSE = false;

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
echo("Admin privileges: " . ($adminPriv ? "yes" : "no") . "<br/>");
$userId = '';
if (isset($_SESSION['USER_NAME'])) {
    $userId = $_SESSION['USER_NAME'];
}

$currentYear = date("Y");
// echo("Current year is " . $currentYear . ", next will be " . ($currentYear + 1) . "<br/>");
$currentMonth = date("m");

if (isset($_GET['year'])) {
    $currentYear = $_GET['year'];
}
if (isset($_GET['month'])) {
    $currentMonth = $_GET['month'];
}

$helpType = 'SAILING';
if (isset($_GET['type'])) {
    $helpType = $_GET['type'];
}
$lang = 'FR';
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
}

$operation = 'list';
if (isset($_POST['operation'])) {
    $operation = $_POST['operation'];
}

// echo ("This month (" . $currentYear . " - " . $MONTHS[$currentMonth - 1] . ") we have " . getNbDays($currentYear, $currentMonth) . " days.<br/>");

if (false) {
    $year = 1900;
    $month = 2;
    echo ("In $year, $month, there were " . getNbDays($year, $month) . " days.<br/>");

    $year = 2000;
    $month = 2;
    echo ("In $year, $month, there were " . getNbDays($year, $month) . " days.<br/>");

    $year = 1959;
    $month = 3;
    echo ("In $year, $month, there were " . getNbDays($year, $month) . " days.<br/>");

    $year = 2024;
    $month = 2;
    echo ("In $year, $month, there were " . getNbDays($year, $month) . " days.<br/>");
}

if ($operation == 'list') {
    // echo ("<h2>Requests Planning for " . $MONTHS[$currentMonth - 1] . " " . $currentYear . " (3 months)</h2>");

    $requestsArray = getHelpRequests($dbhost, $username, $password, $database, $helpType, $VERBOSE);     

    if ($VERBOSE) {
        echo ("We have " . count($requestsArray) . " requests<br/>");
    }

    $from = $MONTHS[$currentMonth - 1] . " " . $currentYear;

    $firstDayOfMonth = $currentYear . "-" . $currentMonth . "-" . "01";

    $span = 3; // 3 months
    $currentMonth += ($span - 1);
    while ($currentMonth > 12) {
        $currentMonth -= 12;
        $currentYear += 1;
    }

    $to = $MONTHS[$currentMonth - 1] . " " . $currentYear;
    if ($lang == 'FR') {
        echo ("<h2>Planning des demandes d'aide de " . $from . " &agrave; " . $to . " </h2>");
    } else {
        echo ("<h2>Requests Planning from " . $from . " to " . $to . " </h2>");
    }

    $lastDayOfMonth = $currentYear . "-" . $currentMonth . "-" . getNbDays($currentYear, $currentMonth);

    echo("<ul>" . PHP_EOL);
    foreach($requestsArray as $request) {
        if ($VERBOSE) {
            echo("Fetching requests for " . $request->owner . "/" . $request->boat . " in " . $MONTHS[$currentMonth - 1] . " " . $currentYear . "<br/>" . PHP_EOL);
        }
        echo("<li>" . PHP_EOL);
        $memberArray = getMember($dbhost, $username, $password, $database, $request->owner, $VERBOSE);
        $ownerName = 'Unknown';
        if (count($memberArray) > 0) {
            $ownerName = $memberArray[0]->firstName . ' ' . $memberArray[0]->lastName;
        }
        $resData = (($lang == 'FR') ? "De " : "By ") . $ownerName . 
                (($lang == 'FR') ? " sur " : " on ") . getBoatName($dbhost, $username, $password, $database, $request->boat, $VERBOSE);
        if ($request->to == null) {
            $resData .=  (($lang == 'FR') ? ", le " : ", on ") . $request->from;
        } else {
            $resData .=  (($lang == 'FR') ? ", de " : ", from ") . $request->from . 
                        (($lang == 'FR') ? ", &agrave; " : ", to ") . $request->to;
        }
        $resData .= /*", type " . $request->type . */ ", " . $request->comment .
                "<form action='" . basename(__FILE__) . "' method='post'>" .
                "<input type='hidden' name='operation' value='reply'>" .
                "<input type='hidden' name='idx' value='" . $request->idx . "'>" .
                "<input type='submit' value='" . (($lang == 'FR') ? "Je viens !" : "I'm coming!") . "'>" .
                "</form>";
        echo $resData;
        echo("</li>" . PHP_EOL);
    }
    echo ("</ul>" . PHP_EOL);

    echo("<hr/>" . PHP_EOL);
} else if ($operation == 'reply') {
    echo ("Managing request #" . $_POST['idx'] . "<br/>Coming !!" . PHP_EOL);

} else {
    echo ("Unknown operation [" . $operation . "]" . PHP_EOL);
}
?>
  </body>        
</html>