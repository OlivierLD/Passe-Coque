<?php
// Must be on top
$timeout = 300;  // In seconds
try {
  if (!isset($_SESSION)) {
    ini_set("session.gc_maxlifetime", $timeout);
    ini_set("session.cookie_lifetime", $timeout);
    session_start();
  }
} catch (Throwable $e) {
  echo "Session settings: Captured Throwable: " . $e->getMessage() . "<br/>" . PHP_EOL;
}
?>
<html lang="en">
  <!--
   ! A Form to Update the REFERENTS table, leads to an update form.
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Edit Referents</title>
    <style type="text/css">
      * {
        font-family: 'Courier New'
      }

      tr > td {
        border: 1px solid silver;
      }
    </style>
  </head>
  <body>
    <h1>PHP / MySQL. Passe-Coque Referents</h1>

    <?php
// phpinfo();

$VERBOSE = false;

require __DIR__ . "/../../php/db.cred.php";
require __DIR__ . "/_db.utils.php";

// Authentication required !!
if (!isset($_SESSION['USER_NAME'])) {
  echo ("<button onclick='window.open(\"https://passe-coque.com/php/admin.menu.html\");'>Authenticate</button><br/>" . PHP_EOL);
  die ("You are not connected! Please log in first!");
} else {
  if (!isset($_SESSION['ADMIN'])) {
    ?>
    <button onclick="window.open('https://passe-coque.com/php/admin.menu.html');">Authenticate</button><br/>
    <form action="../../php/members.php" method="post"> <!-- members.php -->
        <input type="hidden" name="operation" value="logout">
        <table>
          <tr>
            <td colspan="2" style="text-align: center;"><input type="submit" value="Log out"></td>
          </tr>
        </table>
      </form>
    <?php
    echo("From script " . basename(__FILE__) . "<br/>" . PHP_EOL);
    die ("No ADMIN property found! Please log in first!");
  } else {
    if (!$_SESSION['ADMIN']) {
      ?>
      <button onclick="window.open('https://passe-coque.com/php/admin.menu.html');">Authenticate</button><br/>
      <form action="../../php/members.php" method="post"> <!-- members.php -->
          <input type="hidden" name="operation" value="logout">
          <table>
            <tr>
              <td colspan="2" style="text-align: center;"><input type="submit" value="Log out"></td>
            </tr>
          </table>
        </form>
      <?php
      echo("From script " . basename(__FILE__) . "<br/>" . PHP_EOL);
      die("Sorry, you're NOT an Admin.");
    }
  }
}

function populateBoatOptions($boatsArray, $boatId) {
  foreach ($boatsArray as $boat) {
    echo '<option value="' . $boat->id . '"' . ($boat->id == $boatId ? ' selected' : '') . '>' . $boat->id . ', ' . $boat->name . ' (' . $boat->type . ') </option>' . PHP_EOL;
  }
}

function populateMemberOptions($membersArray, $memberEmail) {
  foreach ($membersArray as $member) {
    echo '<option value="' . $member->email . '"' . ($member->email == $memberEmail ? ' selected' : '') . '>' . $member->email. ', ' . $member->firstName . ' ' . strtoupper($member->lastName) . '</option>' . PHP_EOL;
  }
}

$create_record = false;
if (isset($_GET['task'])) {
  if ($_GET['task'] === 'create') {
    $create_record = true;
  }
}

if (isset($_POST['operation'])) {
  $operation = $_POST['operation'];
  if ($operation == 'update' || $operation == 'insert') { // Then do the update/insert, and delete...

    // echo("Update:" . $_POST['update'] . ", Delete:" . $_POST['delete'] . " <br/>" . PHP_EOL); // Two Submit buttons

    try {
      $ref_email = $_POST['ref-id']; 
      $boat_id = $_POST['boat-id']; 
      $telephone = $_POST['telephone']; 

      // $link = mysqli_init();  // Mandatory ?
    
      $link = new mysqli($dbhost, $username, $password, $database);
    
      if ($link->connect_errno) {
        echo("Oops, errno:" . $link->connect_errno . "...<br/>");
        die("Connection failed: " . $conn->connect_error);
      } else {
        echo("Connected.<br/>");
      }
    
      if (isset($_POST['update'])) {  // TODO urlencode ?

        // echo ("Update for $email ... <br/>");

        $sql = 'UPDATE REFERENTS ' .
               'SET TELEPHONE = \'' . ($telephone) . '\' ' .
               'WHERE EMAIL = \'' . $ref_email . '\' AND BOAT_ID = \'' . $boat_id . '\'';
        // echo ("Update Stmt: $sql ; <br/>");
      } else if (isset($_POST['delete'])) { // operation = update, delete is set.
        $sql = 'DELETE FROM REFERENTS 
                WHERE EMAIL = \'' . $ref_email . '\' AND BOAT_ID = \'' . $boat_id . '\'';
      } else if ($operation === 'insert') {
        $sql = 'INSERT INTO REFERENTS ' .
               'VALUES (\'' . $ref_email . '\', \'' . urlencode($boat_id) . '\', \'' . ($telephone) . '\')';
      } else {
        echo "What do you ant ?? <br/>" . PHP_EOL;
        $sql = '';
      }
      echo('Performing query <code>' . $sql . '</code><br/>');
    
      if (true) { // Do perform ?
        if ($link->query($sql) === TRUE) {
          echo "OK. Operation performed successfully<br/>" . PHP_EOL;
        } else {
          echo "ERROR executing: " . $sql . "<br/>" . $link->error . "<br/>";
        }
      } else {
        echo "Stby<br/>" . PHP_EOL;
      }
    
      // On ferme !
      $link->close();
      echo("Closed DB<br/>".PHP_EOL);
    } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }
    echo("<hr/>" . PHP_EOL);
    // echo("Again ? Click <a href='#'>Here</a>.");
    ?>
    <form action="./_referents.01.php" method="get">
      <table>
        <tr>
          <td colspan="2" style="text-align: center;"><input type="submit" value="Back to Query Form?"></td>
        </tr>
      </table>
    </form>
    <?php
  } else {
    echo ("Operation $operation ...");
  }
} else if (isset($_GET['email']) && isset($_GET['boat-id'])) { // Then display the form to update
  $ref_id = $_GET['email'];
  $boat_id = $_GET['boat-id'];

  $boatsArray = getBoats($dbhost, $username, $password, $database, $VERBOSE);
  $membersArray = getMembers($dbhost, $username, $password, $database, $VERBOSE);

  if ($VERBOSE) {
    foreach ($boatsArray as $boat) {
      echo 'Boat: ' . $boat->id . ' [' .  $boat->name . '] (' . $boat->type . ') <br/>' . PHP_EOL;
    }
  }

  try {
    $link = new mysqli($dbhost, $username, $password, $database);
    
    if ($link->connect_errno) {
      echo("Oops, errno:".$link->connect_errno."...<br/>");
      die("Connection failed: " . $conn->connect_error);
    } else {
      echo("Connected.<br/>");
    }
  
    $sql = 'SELECT EMAIL, BOAT_ID, TELEPHONE FROM BOATS_AND_REFERENTS WHERE EMAIL = \'' . $ref_id . '\' AND BOAT_ID = \'' . $boat_id . '\';';
    //             |      |        |
    //             0      1        2

    echo('Performing query <code>' . $sql . '</code><br/>');
        
    // $result = mysql_query($sql, $link);
    $result = mysqli_query($link, $sql);
    echo ("Returned " . $result->num_rows . " row(s)<br/>");
    if ($result->num_rows > 1) {
      echo "Bizarre...";
    }
    echo("<h2>Row for $ref_id and $boat_id </h2>");
    ?>
    <form action="#" method="post">
      <input type="hidden" name="operation" value="update">
      <!-- Those 2 below because selects are disabled -->
      <input type="hidden" name="ref-id" value="<?php echo $ref_id ?>">
      <input type="hidden" name="boat-id" value="<?php echo $boat_id ?>">
    <?php

    // Open the form for update. Only telephone can be updated (others are FKs).
    echo("<form action='#' method='post'>" . PHP_EOL);
    echo "<table>" . PHP_EOL;
    while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result

      // echo ("Debug: " . $table[7] . "...");

      echo('<tr><td>Referent</td><td><select name="ref-id" disabled>' . PHP_EOL);
      populateMemberOptions($membersArray, $table[0]);
      // echo('<option value="' . $table[0] . '">' . $table[0] . '</option>' . PHP_EOL);
      echo('</select></td></tr>' . PHP_EOL);
      echo('<tr><td>Boat</td><td><select name="boat-id" disabled>' . PHP_EOL); // value="' . urldecode($table[1]) . '" size="40"> ');
      populateBoatOptions($boatsArray, $table[1]);
      echo('</select></td></tr>' . PHP_EOL);
      echo('<tr><td>Telephone</td><td><input type="text" name="telephone" value="' . $table[2] . '" size="40"></td></tr>' . PHP_EOL);
    }
    echo "</table>" . PHP_EOL;
    ?>
      <input type="submit" value="Update" name="update"> <input type="submit" value="Delete" name="delete">
    </form>
    <?php
    // On ferme !
    $link->close();
  } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
  }
} else if ($create_record) {
  // Display the form to create the record
  $boatsArray = getBoats($dbhost, $username, $password, $database, $VERBOSE);
  $membersArray = getMembers($dbhost, $username, $password, $database, $VERBOSE);
  ?>
  <form action="#" method="post">
    <input type="hidden" name="operation" value="insert">
    <table>
      <tr>
        <td>Referent</td>
        <td>
          <select name="ref-id">
          <?php
            populateMemberOptions($membersArray, '');
          ?>
          </select>
        </td>
      <tr>
      <tr>
        <td>Boat</td>
        <td>
          <select name="boat-id">
            <?php
              populateBoatOptions($boatsArray, '');
            ?>
          </select>
        </td>
      <tr>
      <tr><td>Telephone</td><td><input type="text" name="telephone" size="40" value="unknown"></td><tr>
    </table>
    <input type="submit" value="Create" name="create">
  </form>
  <?php

} else {
  // WHAT ??
  echo "What ??";
}
    ?>        
  </body>        
</html>