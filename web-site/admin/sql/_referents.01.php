<?php
// Must be on top
$timeout = 60;  // In seconds
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
   ! Access to the REFERENTS Table
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Referents - Members, Fleet</title>
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
    <h1>PHP / MySQL. Passe-Coque Referents. Depends on Members and Fleet</h1>

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
      $boat_name = $_POST['boat-name']; 
      $ref_name = $_POST['ref-name'];  

      // $link = mysqli_init();  // Mandatory ?
    
      echo("Will connect on ".$database." ...<br/>");
      $link = new mysqli($dbhost, $username, $password, $database);
    
      if ($link->connect_errno) {
        echo("Oops, errno:".$link->connect_errno."...<br/>");
        die("Connection failed: " . $conn->connect_error);
      } else {
        echo("Connected.<br/>");
      }
    
      $sql = 'SELECT EMAIL, 
                     FIRST_NAME, 
                     LAST_NAME, 
                     BOAT_NAME, 
                     BOAT_TYPE, 
                     BOAT_ID, 
                     TELEPHONE 
              FROM BOATS_AND_REFERENTS 
              WHERE (UPPER(BOAT_NAME) LIKE UPPER(\'%' . $boat_name . '%\') OR
                    UPPER(BOAT_ID) LIKE UPPER(\'%' . $boat_name . '%\')) AND (
                    UPPER(FIRST_NAME) LIKE UPPER(\'%' . $ref_name . '%\') OR
                    UPPER(LAST_NAME) LIKE UPPER(\'%' . $ref_name . '%\') OR
                    UPPER(EMAIL) LIKE UPPER(\'%' . $ref_name . '%\'))
              ORDER BY 1;';
      
      echo('Performing query <code>' . $sql . '</code><br/>');
    
      // $result = mysql_query($sql, $link);
      $result = mysqli_query($link, $sql);
      echo ("Returned " . $result->num_rows . " row(s)<br/>");
    
      echo("<h2>Passe-Coque Referents</h2>");

      ?> <!-- Member creation form -->

      <form action="./_referents.02.php" method="get">
        <input type="hidden" name="task" value="create">
        <input type="submit" value="Create New Referent">
      </form> 

      <?php


      echo "<table>";
      echo "<tr><th>Email</th><th>First Name</th><th>Last Name</th><th>Boat Name</th><th>Boat Type</th><th>Boat ID</th><th>Ref. Tel.</th><th>-</th></tr>";
      //             0             1                  2                 3                 4                 5               6
      while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
        echo(
          "<tr><td>" . 
            urldecode($table[0]) . "</td><td>" . urldecode($table[1]) . "</td><td>" . urldecode($table[2]) . "</td><td>" . urldecode($table[3]) . "</td><td>" . urldecode($table[4]) . "</td><td>" . urldecode($table[5]) . "</td><td>" . ($table[6]) . "</td><td>" . "<a href='./_referents.02.php?email=" . $table[0] . "&boat-id=" . $table[5] . "'>Edit</a>" . //  target='RefUpdate'
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
          <td valign="top">Boat (part of Name or ID):</td><td><input type="text" name="boat-name" size="40"></td>
        </tr>
        <tr>
          <td valign="top">Referent (part of first or last name, email):</td><td><input type="text" name="ref-name" size="40"></td>
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