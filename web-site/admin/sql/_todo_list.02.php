<?php
// Must be on top
$timeout = 300;  // In seconds
$applyTimeout = false; // Change at will, or with QSParam 'verbose=y

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
   ! - Gestion des TODO lists.
   ! - Tasks per user
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>TODO lists management</title>

    <link rel="stylesheet" href="../../fonts/font.01.css">
	<link rel="stylesheet" href="../../fonts/font.02.css">
	<!--link rel="stylesheet" href="./passe-coque.menu.css"-->
	<link rel="stylesheet" href="../../passe-coque.css" type="text/css"/>

    <style type="text/css">
      /* * {
        font-family: 'Courier New'
      } */

      * {
        font-size: 18px;
        line-height: 1em;
      }

      tr > td {
        border: 1px solid silver;
        vertical-align: top;
      }

      textarea {
        text-align: justify;
        white-space: normal;
        line-height: normal;
      }

      pre {
        line-height: normal;
      }

      ul > li {
        /* display: grid;
        grid-template-columns: 80% 20%; */
        border: 1px solid silver;
        border-radius: 5px;
        padding: 5px 10px;
      }

      body {
        background: transparent;
      }

      input[type=button], input[type=submit], input[type=reset] {
        padding: 8px 16px;
        text-decoration: none;
        margin: 4px 2px;
        cursor: pointer;
        position: relative;
        background-color: var(--pc-bg-color);
        color: white;
        border: 1px solid silver;
        border-radius: 6px;
      }

      a:link, a:visited {
          background-color: #f44336;
          color: white;
          padding: 5px 25px;
          text-align: center;
          text-decoration: none;
          display: inline-block;
          border: 1px solid silver;
          border-radius: 6px;
      }

      a:hover, a:active {
          background-color: red;
      }

      .big-column {
          max-width: 40vw;
          overflow-x: scroll;
      }
    </style>

  </head>

  <script type="text/javascript">
    const validateForm = () => { // Used below ?
        // Empty
    };
  </script>

  <body>
    <!--h1>PHP / MySQL. TODO lists</h1-->

<?php
// phpinfo();

require __DIR__ . "/../../php/db.cred.php";
require __DIR__ . "/_db.utils.php";
// require __DIR__ . "/_emails.utils.php";

$VERBOSE = false; // Change at will

if (isset($_GET['verbose'])) {
    $VERBOSE = ($_GET['verbose'] == 'y' | $_GET['verbose'] == 'yes' | $_GET['verbose'] == 'YES' | $_GET['verbose'] == 'Y' | $_GET['verbose'] == 'true');
}

function translateStatus(string $lang, string $original) : string {
    $translated = $original;
    try {
        switch ($original) {
            case 'OPENED':
                $translated = ($lang == 'FR') ? "&Agrave; faire" : "To Do";
                break;
            case 'CANCELED':
                $translated = ($lang == 'FR') ? "Annul&eacute;" : "Canceled";
                break;
            case 'COMPLETED':
                $translated = ($lang == 'FR') ? "Termin&eacute;" : "Completed";
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

if (!isset($_SESSION['USER_NAME'])) {
  echo ("<button onclick='window.open(\"https://passe-coque.com/php/admin.menu.html\");'>Authenticate</button><br/>" . PHP_EOL);
  die ("You are not connected! Please log in first!");
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

if ($VERBOSE) {
    // echo ("This month (" . $currentYear . " - " . $MONTHS[$currentMonth - 1] . ") we have " . getNbDays($currentYear, $currentMonth) . " days.<br/>");
    echo("operation: [" . $operation . "]<br/>" . PHP_EOL);
    echo("UserID: [" . $userId . "], Admin Priv: [" . ($adminPriv ? 'yes' : 'no') . "]<br/>" . PHP_EOL);
}

if ($operation == 'list') {

    // List the tasks
    $title = ($lang == 'FR') ? "Liste des t&acirc;ches, par bateau et par personne" : "Task list, by boat and by person";
    echo ("<h2>" . $title . " (WiP)</h2>" . PHP_EOL);

    // La suite...

    $sql = "SELECT A.BOAT_ID, C.BOAT_NAME, A.ASSIGNED_TO, A.LINE_STATUS, COUNT(*), CONCAT(B.FIRST_NAME, ' ', UPPER(B.LAST_NAME)) " .
    //             0.         1.           2.             3.             4.        5.
           "FROM TODO_LISTS A,
                 PASSE_COQUE_MEMBERS B,
                 THE_FLEET C
            WHERE A.ASSIGNED_TO IS NOT NULL
              AND A.ASSIGNED_TO = B.EMAIL
              AND A.BOAT_ID = C.ID
            GROUP BY A.BOAT_ID, A.LINE_STATUS, A.ASSIGNED_TO
            ORDER BY A.BOAT_ID, A.ASSIGNED_TO;";

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
            $lineData[1] = $table[1]; // BOAT_NAME
            $lineData[2] = $table[2]; // ASSIGNED_TO
            $lineData[3] = $table[3]; // LINE_STATUS
            $lineData[4] = $table[4]; // TASK COUNT
            $lineData[5] = $table[5]; // FULL NAME

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

    // Array $lines is now populated
    echo ("<ul>" . PHP_EOL);
    foreach($lines as $line) {
        echo("<li>" . PHP_EOL);
        echo("<b>Boat:</b> " . $line[1] . " (ID: " . $line[0] . ") " . PHP_EOL);

        $url = "_todo_list.01.php?option=for-boat&boat-id=$line[0]&lang=$lang"; // &ref=dummy@home.com
        echo("<a href='$url'><b>" . (($lang == 'FR') ? "Voir les t&acirc;ches" : "See tasks") . "</b></a><br/>" . PHP_EOL);

        echo("<b>Assigned to:</b> " . $line[5] . " (" . $line[2] . ")<br/>" . PHP_EOL);
        echo("<b>Status:</b> " . translateStatus($lang, $line[3]) . "<br/>" . PHP_EOL);
        echo("<b>Number of tasks:</b> " . $line[4] . "<br/>" . PHP_EOL);
        echo("</li>" . PHP_EOL);
    }
    echo ("</ul>" . PHP_EOL);
} else if ($operation == 'per-assignee') {
    // Tasks per assignee
    echo ("<h2>" . (($lang == 'FR') ? "T&acirc;ches par personne" : "Tasks per person") . " (WiP)</h2>" . PHP_EOL);

    $sql = "SELECT ASSIGNED_TO AS OWNER,
                   CONCAT(PASSE_COQUE_MEMBERS.FIRST_NAME, ' ', UPPER(PASSE_COQUE_MEMBERS.LAST_NAME)) AS NAME,
                   TODO_LISTS.BOAT_ID,
                   THE_FLEET.BOAT_NAME,
                   LINE_STATUS,
                   COUNT(LINE_STATUS) AS TASKS
            FROM TODO_LISTS, PASSE_COQUE_MEMBERS, THE_FLEET
            WHERE ASSIGNED_TO IS NOT NULL
              AND PASSE_COQUE_MEMBERS.EMAIL = TODO_LISTS.ASSIGNED_TO
              AND TODO_LISTS.BOAT_ID = THE_FLEET.ID
            GROUP BY ASSIGNED_TO, BOAT_ID, LINE_STATUS
            ORDER BY ASSIGNED_TO, BOAT_ID, LINE_STATUS;";

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
            $lineData[0] = $table[0]; // OWNER (email)
            $lineData[1] = $table[1]; // OWNER NAME
            $lineData[2] = $table[2]; // BOAT_ID
            $lineData[3] = $table[3]; // BOAT_NAME
            $lineData[4] = $table[4]; // STATUS
            $lineData[5] = $table[5]; // TASK COUNT

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

    // Array $lines is now populated
    echo ("<table>" . PHP_EOL);
    echo("<tr><th>Person</th><th>Boat</th><th>Status</th><th>Number of tasks</th></tr>" . PHP_EOL);
    foreach($lines as $line) {
        echo("<tr>" . PHP_EOL);
        echo("<td>" . $line[0] . ", " . $line[1] . "</td>" . PHP_EOL);

        // $url = "_todo_list.01.php?option=for-boat&boat-id=$line[0]&lang=$lang"; // &ref=dummy@home.com
        // echo("<a href='$url'><b>" . (($lang == 'FR') ? "Voir les t&acirc;ches" : "See tasks") . "</b></a><br/>" . PHP_EOL);

        echo("<td>" . $line[3] . "</td>" . PHP_EOL);
        echo("<td>"  . translateStatus($lang, $line[4]) . "</td>" . PHP_EOL);
        echo("<td>" . $line[5] . "</td>" . PHP_EOL);
        echo("</tr>" . PHP_EOL);
    }
    echo ("</table>" . PHP_EOL);
} else {
    echo ("Unknown operation [" . $operation . "]" . PHP_EOL);
}
?>
  </body>
</html>
