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

      * {
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
                $translated = ($lang == 'FR') ? "Termin&eacute;" : "Comnpleted";
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
        // La suite...
        // $boats = getAllBoatsByReferent($dbhost, $username, $password, $database, $userId, ($adminPriv ? true : false), $VERBOSE); // One per referent
        $boats = getDistinctBoatsWithReferents($dbhost, $username, $password, $database, $VERBOSE); // No duplicate
        if (true) { // List the boats for this referent (or admin)

            if ($VERBOSE) {
                echo ("For $userId, " . count($boats) . " boat(s).<br/>" . PHP_EOL);
            }

            echo ("<ul>" . PHP_EOL);
            foreach($boats as $boat) {
                $good2display = true;
                if ($option == 'no-empty') {
                    $todolist = getBoatsTODOList($dbhost, $username, $password, $database, $userId, $boat[0], $VERBOSE);
                    if ($VERBOSE) {
                        echo ("Boat $boat[0], " . count($todolist) . 
                              " item(s) in the list, user $userId, ref-email(s): $boat[4], strpos says: " . 
                              ((strpos($boat[3], $userId) === false) ? "No" : "Yes") . "<br/>" . PHP_EOL);
                    }
                    if (count($todolist) == 0) {
                        if (strpos($boat[4], $userId) === false) { // Except if user is referent
                            $good2display = false;
                        }
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
            echo ("Got " . count($todoLines) . " lines for '$boat_id'.<br/>" . PHP_EOL);
            try {
                echo ("Can Modify: Admin [$adminPriv], [$userId] in [$contact]: [" . (strpos($contact, $userId) !== false ? 'yes' : 'no') . "]<br/>" . PHP_EOL);
            } catch (Throwable $e) {
                echo "Captured Throwable for str_contains/strpos : " . $e->getMessage() . "<br/>" . PHP_EOL;
            }
        }                        

        $canModify = ($adminPriv || // $userId == $contact);  // contains
                                    (strpos($contact, $userId) !== false));
        if ($VERBOSE) {
            echo ("Can Modify => [" . ($canModify ? 'yes' : 'no') . "]<br/>" . PHP_EOL);
        }                        

        echo ("<h3>" . ($lang == 'FR' ? "TODO list pour $boatName" : "TODO list for $boatName") . "</h3>" . PHP_EOL);
        echo ("<hr/>" . PHP_EOL);
        // Back to boats list
        if ($lang == 'FR') {
            echo ("Retour &agrave; la <a href='javascript:history.back()'>liste des bateaux</a><br/>" . PHP_EOL);
        } else {
            echo ("Back to <a href='javascript:history.back()'>Boat list</a><br/>" . PHP_EOL);
        }
        echo ("<hr/>" . PHP_EOL);

        if (count($todoLines) == 0) {
            echo ((($lang == 'FR') ? "Rien sur la TODO list de $boatName..." : "Nothing on the TODO list for $boatName...") . "<br/>" . PHP_EOL);
        } else {
            echo ("<table>" . PHP_EOL);
            echo ("<tr><th>Description</th><th>Cr&eacute;&eacute;e le</th><th>Status</th><th>Modifi&eacute;e le</th></tr>" . PHP_EOL);
            foreach ($todoLines as $line) {
                echo ("<tr>" . PHP_EOL);
                echo (  "<td class='big-column'><pre>$line[2]</pre></td><td>$line[3]</td><td>" . translateStatus($lang, $line[4]) . "</td><td>$line[5]</td>" . PHP_EOL);
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
    }

        if ($canModify) {
            // echo ("<button>Create new Line</button>" . PHP_EOL);
            echo ("<hr/>" . PHP_EOL);
            ?>
            <form action="<?php echo basename(__FILE__); ?>" method="post">
                <input type="hidden" name="operation" value="create-line">
                <input type="hidden" name="lang" value="<?php echo($lang); ?>">
                <input type="hidden" name="ref" value="<?php echo($contact); ?>">
                <input type="hidden" name="boat-id" value="<?php echo($boat_id); ?>">
                <input type="submit" value="<?php echo(($lang != 'FR') ? "Create an element" : "Cr&eacute;er un &eacute;l&eacute;ment"); ?>">
            </form>
<?php            
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
                                <option value="OPENED"<?php echo('OPENED' == $line[4] ? "selected" : ""); ?>><?php echo translateStatus($lang, 'OPENED'); ?></option>
                                <option value="IN_PROGRESS"<?php echo('IN_PROGRESS' == $line[4] ? "selected" : ""); ?>><?php echo translateStatus($lang, 'IN_PROGRESS'); ?></option>
                                <option value="CANCELED"<?php echo('CANCELED' == $line[4] ? "selected" : ""); ?>><?php echo translateStatus($lang, 'CANCELED'); ?></option>
                                <option value="COMPLETED"<?php echo('COMPLETED' == $line[4] ? "selected" : ""); ?>><?php echo translateStatus($lang, 'COMPLETED'); ?></option>
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

    echo("<h3>" . (($lang == 'FR') ? "Ajout d'un &eacute;l&eacute;ment dans la liste" : "Add an element in the List") . "</h3>" . PHP_EOL);
    echo ("<hr/>" . PHP_EOL);
    // Back to boats list
    if ($lang == 'FR') {
        echo ("<a href='javascript:history.back()'>Retour</a><br/>" . PHP_EOL);
    } else {
        echo ("<a href='javascript:history.back()'>Back</a><br/>" . PHP_EOL);
    }
    echo ("<hr/>" . PHP_EOL);

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
                            <textarea rows="5" cols="60" name="description" placeholder="<?php echo(($lang != 'FR') ? "Job Description" : "Description du job"); ?>"></textarea>
                        </td>
                    </tr>
                </table>
                <hr/>
                <input type="submit" value="<?php echo(($lang != 'FR') ? "Create" : "Cr&eacute;er"); ?>">
                <hr/>
            </form>

<?php
} else if ($operation == "insert-line") {
    $lang = $_POST["lang"];
    $ref = $_POST["ref"];
    $boatId = $_POST["boat-id"];

    $description = $_POST["description"];

    $sql = "INSERT INTO TODO_LISTS (BOAT_ID, LINE_DESC, LINE_STATUS) VALUES ('$boatId', '" . str_replace("'", "\'", $description) . "', 'OPENED');";

    try {
        executeSQL($dbhost, $username, $password, $database, $sql, $VERBOSE);
        echo ((($lang == 'FR') ? "C'est fait. Ligne cr&eacute;&eacute;e." : "Done. Line created.") . "<br/>" . PHP_EOL);
    } catch (Throwable $e) {
        echo "Captured Throwable for executeSQL : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }

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
    try {
        executeSQL($dbhost, $username, $password, $database, $sql, $VERBOSE);
        echo ((($lang == 'FR') ? "Mise &agrave; jour effectu&eacute;e." : "Update completed.") . "<br/>" . PHP_EOL);
    } catch (Throwable $e) {
        echo "Captured Throwable for executeSQL : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }

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

    try {
        executeSQL($dbhost, $username, $password, $database, $sql, $VERBOSE);
        echo ((($lang == 'FR') ? "Suppression effectu&eacute;e." : "Delete completed.") . "<br/>" . PHP_EOL);
    } catch (Throwable $e) {
        echo "Captured Throwable for executeSQL : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }

    echo ("<hr/>" . PHP_EOL);
    echo ("<a href='" . basename(__FILE__) . "?option=for-boat&boat-id=$boatId&ref=$ref&lang=$lang'>" . ($lang == 'FR' ? 'Retour TODO list' : 'Back to TODO List') . "</a>" . PHP_EOL);

} else {
    echo ("Unknown operation [" . $operation . "]" . PHP_EOL);
}
?>
  </body>        
</html>
