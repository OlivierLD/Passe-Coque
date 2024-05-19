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
   ! WiP.
   ! A Form to query the PASSE_COQUE_MEMBERS table, leads to an update form.
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Query Members</title>
    <style type="text/css">
      * {
        font-family: 'Courier New'
      }

      tr > td {
        border: 1px solid silver;
      }

      a:link, a:visited {
        background-color: #f44336;
        color: white;
        padding: 10px 20px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        border-radius: 5px;
      }

      a:hover, a:active {
        background-color: red;
      }
    </style>
  </head>
  <body>
    <h1>PHP / MySQL. Passe-Coque Members</h1>

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
      $name = $_POST['full-name']; 

      // $link = mysqli_init();  // Mandatory ?
    
      echo("Will connect on ".$database." ...<br/>");
      $link = new mysqli($dbhost, $username, $password, $database);
    
      if ($link->connect_errno) {
        echo("Oops, errno:".$link->connect_errno."...<br/>");
        die("Connection failed: " . $conn->connect_error);
      } else {
        echo("Connected.<br/>");
      }
    
      $sql = 'SELECT
          PCM.EMAIL,
          CONCAT(
              UPPER(PCM.LAST_NAME),
              \' \',
              PCM.FIRST_NAME
          ) AS NAME,
          PCM.TARIF,
          (SELECT IF(COUNT(*) = 0, FALSE, TRUE) FROM BOAT_CLUB_MEMBERS BC WHERE BC.EMAIL = PCM.EMAIL) AS BC
      FROM 
          PASSE_COQUE_MEMBERS PCM
      WHERE 
           UPPER(PCM.FIRST_NAME) LIKE UPPER(\'%' . $name . '%\') OR 
           UPPER(PCM.LAST_NAME) LIKE UPPER(\'%' . $name . '%\') OR 
           UPPER(PCM.EMAIL) LIKE UPPER(\'%' . $name . '%\')
      ORDER BY 2;'; // Order by LastName-FirstName
      
      echo('Performing query <code>' . $sql . '</code><br/>');
    
      // $result = mysql_query($sql, $link);
      $result = mysqli_query($link, $sql);
      echo ("Returned " . $result->num_rows . " row(s)<br/>");
    
      echo("<h2>Passe-Coque Members</h2>");

      ?> <!-- Member creation form -->

      <form action="./_members.02.php" method="get">
        <input type="hidden" name="task" value="create">
        <input type="submit" value="Create New Member">
      </form> 

      <?php


      echo "<table>";
      echo "<tr><th>Email</th><th>Name</th><th>Tarif</th><th>Boat Club</th><th> - </th></tr>";
      while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
        echo(
          "<tr><td>" . 
            urldecode($table[0]) . // Email
          "</td><td>" .  
            /*mb_convert_encoding($table[1], 'UTF-8', 'ISO-8859-1') . //  utf8_encode*/ ($table[1]) . // Name (full) // 
          "</td><td>" .  
            $table[2] . 
          "</td><td style='text-align: center;'>" .  
            ($table[3] ? 'Member' : "<a href='./_boat-club.01.php?operation=subscribe&email=" . $table[0] . "'>Subscribe</a>") . // already in Boat Club, or subscribe
          "</td><td>" . 
            "<a href='./_members.02.php?id=" . $table[0] . "'>Edit</a>" .  // Edit Member //  target='PCUpdate'
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
    <form action="" method="get">
      <!--input type="hidden" name="operation" value="blank"-->
      <table>
        <tr>
          <td colspan="2" style="text-align: center;"><input type="submit" value="Query Again ?"></td>
        </tr>
      </table>
    </form>
    <?php
  }
} else { // Then display the form
    ?>
    <form action="" method="post">
      <input type="hidden" name="operation" value="query">
      <table>
        <tr>
          <td valign="top">Name (part of first name, last name, email):</td><td><input type="text" name="full-name" size="40"></td>
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