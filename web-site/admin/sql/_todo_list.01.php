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
   ! - Gestion des TODO lists.
   ! - All CRUD operations
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
    </style>

  </head>

  <script type="text/javascript">
    const validateForm = () => { // Used below ?
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

if ($operation == 'list') { 
    if ($option == null || $option == 'no-empty') {
        // List the boats
        $title = ($lang == 'FR') ? "Liste des bateaux" : "Boat list";
        echo ("<h2>" . $title . "</h2>");
        // La suite
        $boats = getAllBoatsByReferent($dbhost, $username, $password, $database, $userId, ($adminPriv ? true : false), $VERBOSE);
        if (true) { // List the boats for this referent (or admin)

            if ($VERBOSE) {
                echo ("For $userId, " . count($boats) . " boat(s).<br/>" . PHP_EOL);
            }

            echo ("<ul>" . PHP_EOL);
            foreach($boats as $boat) {
                $good2display = true;
                if ($option == 'no-empty') {
                    $todolist = getBoatsTODOList($dbhost, $username, $password, $database, $userId, $boat[0], $VERBOSE);
                    if (count($todolist) == 0) {
                        $good2display = false;
                    }
                }
                if ($good2display) {
                    // echo("<li>$boat[0], <b>$boat[1]</b>, $boat[2] (ref: $boat[3])</li>" . PHP_EOL);
                    $url = basename(__FILE__) . "?option=for-boat&boat-id=$boat[0]&ref=$boat[4]&lang=$lang";
                    echo("<li><a href='$url'><b>$boat[1]</b></a>, $boat[2] (ref: $boat[3])</li>" . PHP_EOL);
                }
            }
            echo ("</ul>" . PHP_EOL);
        }
    } else if ($option == 'for-boat') {
        $boat_id = $_GET['boat-id']; 
        $contact = $_GET['ref'];
        if ($VERBOSE) {
            echo("Will get todo list for $boat_id, from $contact<br/>" . PHP_EOL);
        }
        $boatName = getBoatName($dbhost, $username, $password, $database, $boat_id, $VERBOSE);
        $todoLines = getBoatsTODOList($dbhost, $username, $password, $database, $contact, $boat_id, $VERBOSE);

        if ($VERBOSE) {
            echo ("Got " . count($todoLines) . " lines for $boat_id.<br/>" . PHP_EOL);
        }

        $canModify = ($adminPriv || $userId == $contact);

        echo ("<h3>" . ($lang == 'FR' ? "TODO list pour $boatName" : "TODO list for $boatName") . "</h3>" . PHP_EOL);

        echo ("<table>" . PHP_EOL);
        echo ("<tr><th>Description</th><th>Cr&eacute;&eacute;e le</th><th>Status</th><th>Modifi&eacute;e le</th></tr>" . PHP_EOL);
        foreach ($todoLines as $line) {
            echo ("<tr>" . PHP_EOL);
            echo (  "<td><pre>$line[2]</pre></td><td>$line[3]</td><td>$line[4]</td><td>$line[5]</td>" . PHP_EOL);
            if ($canModify) {
                echo ("<td>" . PHP_EOL);
?>
            <form action="<?php echo basename(__FILE__); ?>" method="post">
                <input type="hidden" name="operation" value="edit-line">
                <input type="hidden" name="lang" value="<?php echo($lang); ?>">
                <input type="hidden" name="ref" value="<?php echo($contact); ?>">
                <input type="hidden" name="boat-id" value="<?php echo($line[0]); ?>">
                <input type="hidden" name="line-id" value="<?php echo($line[1]); ?>">
                <input type="submit" value="<?php echo(($lang != 'FR') ? "Edit" : "Modifier"); ?>">
            </form>
<?php

                echo ("</td>" . PHP_EOL); 
                echo ("<td>" . PHP_EOL);
?>                 
            <form action="<?php echo basename(__FILE__); ?>" method="post">
                <input type="hidden" name="operation" value="delete-line">
                <input type="hidden" name="lang" value="<?php echo($lang); ?>">
                <input type="hidden" name="ref" value="<?php echo($contact); ?>">
                <input type="hidden" name="boat-id" value="<?php echo($line[0]); ?>">
                <input type="hidden" name="line-id" value="<?php echo($line[1]); ?>">
                <input type="submit" value="<?php echo(($lang != 'FR') ? "Delete" : "Supprimer"); ?>">
            </form>
<?php                
                echo("</td>" . PHP_EOL);
            }
            echo ("</tr>" . PHP_EOL);
        }
        echo ("</table>" . PHP_EOL);

        if ($canModify) {
            // echo ("<button>Create new Line</button>" . PHP_EOL);
?>
            <form action="<?php echo basename(__FILE__); ?>" method="post">
                <input type="hidden" name="operation" value="create-line">
                <input type="hidden" name="lang" value="<?php echo($lang); ?>">
                <input type="hidden" name="ref" value="<?php echo($contact); ?>">
                <input type="hidden" name="boat-id" value="<?php echo($boat_id); ?>">
                <input type="submit" value="<?php echo(($lang != 'FR') ? "Create" : "Cr&eacute;er"); ?>">
            </form>
<?php            
        }
        // Back to boats list
        if ($lang == 'FR') {
            echo ("Retour &agrave; la <a href='javascript:history.back()'>liste des bateaux</a><br/>" . PHP_EOL);
        } else {
            echo ("Back to <a href='javascript:history.back()'>Boat list</a><br/>" . PHP_EOL);
        }

        echo ("<hr/>" . PHP_EOL);
    }
} else if ($operation == "edit-line") {
    $boatId = $_POST['boat-id'];
    $lineId = $_POST['line-id'];
    $ref = $_POST['ref'];
    // Retrieve from DB, populate form
    $line = getTODOListLine($dbhost, $username, $password, $database, $lineId, $VERBOSE);

    if ($VERBOSE) {
        echo ("Got " . count($line) . " fields.<br/>" . PHP_EOL);
    }
    if (count($line) > 0) {
?>
            <form action="<?php echo basename(__FILE__); ?>" method="post">
                <input type="hidden" name="operation" value="update-line">
                <input type="hidden" name="lang" value="<?php echo($lang); ?>">
                <input type="hidden" name="ref" value="<?php echo($contact); ?>">
                <input type="hidden" name="boat-id" value="<?php echo($line[0]); ?>">
                <input type="hidden" name="line-id" value="<?php echo($line[1]); ?>">

                <table>
                    <tr>
                        <td>Description</td>
                        <td>
                            <!--input type="text" name="description" value="<?php echo($line[2]); ?>"-->
                            <textarea rows="5" cols="60" name="description"><?php echo($line[2]); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>
                            <!--input type="text" name="status" value="<?php echo($line[4]); ?>"-->
                            <select name="status">
                                <option value="OPENED"<?php echo('OPENED' == $line[4] ? "selected" : ""); ?>>OPENED</option>
                                <option value="IN_PROGRESS"<?php echo('IN_PROGRESS' == $line[4] ? "selected" : ""); ?>>IN_PROGRESS</option>
                                <option value="CANCELED"<?php echo('CANCELED' == $line[4] ? "selected" : ""); ?>>CANCELED</option>
                                <option value="COMPLETED"<?php echo('COMPLETED' == $line[4] ? "selected" : ""); ?>>COMPLETED</option>
                            </select>    
                        </td>
                    </tr>
                </table>

                <input type="submit" value="<?php echo(($lang != 'FR') ? "Update" : "Mettre &agrave; jour"); ?>">
            </form>
<?php
    } else {
        echo ("Required line ($lineId) was not found...<br/>" . PHP_EOL);
    }


    echo ("<hr/>" . PHP_EOL);
    echo ("<a href='" . basename(__FILE__) . "?option=for-boat&boat-id=$boatId&ref=$ref&lang=$lang'>" . ($lang == 'FR' ? 'Retour TODO list' : 'Back to TODO List') . "</a>" . PHP_EOL);
} else if ($operation == "create-line") {
    $lang = $_POST["lang"];
    $ref = $_POST["ref"];
    $boatId = $_POST["boat-id"];
?>
            <form action="<?php echo basename(__FILE__); ?>" method="post">
                <input type="hidden" name="operation" value="insert-line">
                <input type="hidden" name="lang" value="<?php echo($lang); ?>">
                <input type="hidden" name="ref" value="<?php echo($contact); ?>">
                <input type="hidden" name="boat-id" value="<?php echo($boatId); ?>">

                <table>
                    <tr>
                        <td>Description</td>
                        <td>
                            <textarea rows="5" cols="60" name="description" placeholder="On fait quoi ?"></textarea>
                        </td>
                    </tr>
                </table>
                <input type="submit" value="<?php echo(($lang != 'FR') ? "Create" : "Cr&eacute;er"); ?>">
            </form>

<?php
} else if ($operation == "insert-line") {
    $lang = $_POST["lang"];
    $ref = $_POST["ref"];
    $boatId = $_POST["boat-id"];

    $description = $_POST["description"];

    $sql = "INSERT INTO TODO_LISTS (BOAT_ID, LINE_DESC, LINE_STATUS) VALUES ('$boatId', '" . str_replace("'", "\'", $description) . "', 'OPENED');";

    executeSQL($dbhost, $username, $password, $database, $sql, $VERBOSE);

    echo ("<hr/>" . PHP_EOL);
    echo ("<a href='" . basename(__FILE__) . "?option=for-boat&boat-id=$boatId&ref=$ref&lang=$lang'>" . ($lang == 'FR' ? 'Retour TODO list' : 'Back to TODO List') . "</a>" . PHP_EOL);
} else if ($operation == "update-line") {
    $lang = $_POST["lang"];
    $ref = $_POST["ref"];
    $boatId = $_POST["boat-id"];
    $lineId = $_POST["line-id"];

    $newStatus  = $_POST["status"];
    $newDescription = $_POST["description"];

    // The update
    // echo ("Will update line $lineId, Desc [$newDescription], Status [$newStatus]" . PHP_EOL);
    $sql = "UPDATE TODO_LISTS SET LINE_DESC = '" . str_replace("'", "\'", $newDescription) . "', LINE_STATUS = '$newStatus', LAST_UPDATED = CURRENT_TIMESTAMP
            WHERE LINE_ID = $lineId;";

    executeSQL($dbhost, $username, $password, $database, $sql, $VERBOSE);

    echo ("<hr/>" . PHP_EOL);
    echo ("<a href='" . basename(__FILE__) . "?option=for-boat&boat-id=$boatId&ref=$ref&lang=$lang'>" . ($lang == 'FR' ? 'Retour TODO list' : 'Back to TODO List') . "</a>" . PHP_EOL);
} else if ($operation == "delete-line") {
    $lang = $_POST["lang"];
    $ref = $_POST["ref"];
    $boatId = $_POST["boat-id"];
    $lineId = $_POST["line-id"];

    // The update
    // echo ("Will update line $lineId, Desc [$newDescription], Status [$newStatus]" . PHP_EOL);
    $sql = "DELETE FROM TODO_LISTS WHERE LINE_ID = $lineId;";

    executeSQL($dbhost, $username, $password, $database, $sql, $VERBOSE);

    echo ("<hr/>" . PHP_EOL);
    echo ("<a href='" . basename(__FILE__) . "?option=for-boat&boat-id=$boatId&ref=$ref&lang=$lang'>" . ($lang == 'FR' ? 'Retour TODO list' : 'Back to TODO List') . "</a>" . PHP_EOL);

} else {
    echo ("Unknown operation [" . $operation . "]" . PHP_EOL);
}
?>
  </body>        
</html>