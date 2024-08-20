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
   ! A Form to query the THE_FLEET table, leads to an update form.
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Query the Fleet</title>
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
    <h1>PHP / MySQL. Passe-Coque Fleet</h1>

    <?php
// phpinfo();

require __DIR__ . "/../../php/db.cred.php";

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

if (isset($_POST['operation'])) {
  $operation = $_POST['operation'];
  if ($operation == 'query') { // Then do the query
    try {
      $name = $_POST['boat-name']; 

      // $link = mysqli_init();  // Mandatory ?
    
      echo("Will connect on ".$database." ...<br/>");
      $link = new mysqli($dbhost, $username, $password, $database);
    
      if ($link->connect_errno) {
        echo("Oops, errno:".$link->connect_errno."...<br/>");
        die("Connection failed: " . $conn->connect_error);
      } else {
        echo("Connected.<br/>");
      }
    
      $sql = 'SELECT F.BOAT_NAME, F.ID, F.BOAT_TYPE,
      (SELECT COUNT(*) FROM REFERENTS R WHERE R.BOAT_ID = F.ID) AS NB_REF
       FROM THE_FLEET F WHERE UPPER(BOAT_NAME) LIKE UPPER(\'%' . $name . '%\') OR UPPER(ID) LIKE UPPER(\'%' . $name . '%\') ORDER BY 1;';
      
      echo('Performing query <code>' . $sql . '</code><br/>');
    
      // $result = mysql_query($sql, $link);
      $result = mysqli_query($link, $sql);
      echo ("Returned " . $result->num_rows . " row(s)<br/>");
    
      echo("<h2>Passe-Coque Fleet</h2>");

      ?> <!-- Member creation form -->

      <form action="./_the_fleet.02.php" method="get">
        <input type="hidden" name="task" value="create">
        <input type="submit" value="Create New Boat">
      </form> 

      <?php

      function generateLink(float $nb, string $id) : string {
        $stmt= '';
        if ($nb > 0) {
          $stmt .= ("<form action='./_referents.01.php' method='post' style='margin: 0;'>");
          $stmt .= ("<input type='hidden' name='operation' value='query'>");
          $stmt .= ("<input type='hidden' name='boat-name' value='$id'>");
          $stmt .= ("<input type='hidden' name='ref-name' value=''>");
          $stmt .= ("<input type='submit' value='$nb' title='" . $nb . " referent" . ($nb > 1 ? "s" : "") . "'>");
          $stmt .= ("</form>");
        } else {
          $stmt = $nb;
        }
        return $stmt;
      }

      echo "<table>";
      echo "<tr><th>Boat Name</th><th>ID</th><th>Boat Type</th><th>Nb Ref</th><th>-</th></tr>";
      while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
        echo(
          "<tr><td>" . urldecode($table[0]) . 
          "</td><td>" . urldecode($table[1]) . 
          "</td><td>" . urldecode($table[2]) . 
          "</td><td>" . generateLink($table[3], urldecode($table[1])) . 
          "</td><td>" . "<a href='./_the_fleet.02.php?id=" . $table[1] . "'>Edit</a>" .  //  target='PCUpdate'
          "</td></tr>\n"
        ); 
      }
      echo "</table>";
      
      // On ferme !
      $link->close();
      echo("Closed DB<br/>".PHP_EOL);
    } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }
    echo("<hr/>" . PHP_EOL);
    // echo("Again ? Click <a href='#'>Here</a>.");
    ?>
    <form action="<?php echo(basename(__FILE__)); ?>" method="get">
      <!--input type="hidden" name="operation" value="blank"-->
      <table>
        <tr>
          <td colspan="2" style="text-align: center;"><input type="submit" value="Query Again ?"></td>
        </tr>
      </table>
    </form>
    <?php
  }
} else { // Then display the query form
    ?>
    <form action="#" method="post">
      <input type="hidden" name="operation" value="query">
      <table>
        <tr>
          <td valign="top">Boat Name (part of):</td><td><input type="text" name="boat-name" size="40"></td>
        </tr>
        <tr>
          <td colspan="2" style="text-align: center;"><input type="submit" value="Query"></td>
        </tr>
      </table>
    </form>
    <?php
}  
    ?>        
  </body>        
</html>