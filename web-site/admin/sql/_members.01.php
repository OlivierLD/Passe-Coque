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
        vertical-align: top;
      }

      a:link, a:visited {
        background-color: #f44336;
        color: white;
        padding: 2px 20px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        border-radius: 5px;
      }

      a:hover, a:active {
        background-color: red;
      }

      .no-wrap {
        max-width: 300px;
        vertical-align: top;
        white-space: nowrap;
        overflow-x: auto;
      }
      .no-wrap-2 {
        max-width: 200px;
        vertical-align: top;
        white-space: nowrap;
        overflow-x: auto;
      }
      .comments {
        vertical-align: top;
        width: 400px;
        /* min-width: 400px; */
        /* max-width: 400px; */
        max-height: 40px;
        overflow: auto;
      }
      .cb {
        text-align: center;
      }
      table {
        border-collapse: collapse;
        --table-border-width: 1px;
      }
      th.no-rotate {
        vertical-align: bottom;
      }
      th.rotate {
        position: relative;
        height: 140px;
        white-space: nowrap;
        min-width: 20px;
      }

      th.rotate > div {
        position: absolute;
        bottom: 0;
        left: 0;
        /* Make sure short labels still meet the corner of the parent otherwise you'll get a gap */
        text-align: left;
        transform: 
          /* Magic Numbers */
          /* translate(25px, 51px) */
          /* rotate(315deg); */
          translate(calc(100% - var(--table-border-width) / 2), var(--table-border-width))
          /* rotate(315deg); */
          rotate(270deg);
        transform-origin: 0% calc(100% - var(--table-border-width));
        width: 100%;
        /* width: 30px; */
      }
      th.rotate > div > span {
        /*border-bottom: 1px solid #ccc;*/
        /* padding: 5px 10px; */
        position: absolute;
        bottom: 0;
        left: 0;
        border-bottom: var(--table-border-width) solid gray;
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
          PCM.AMOUNT,
          PCM.TELEPHONE,
          PCM.FIRST_ENROLLED,
          PCM.NEWS_LETTER_OK,
          PCM.ADMIN_PRIVILEGES,
          PCM.SAILING_EXPERIENCE,
          PCM.SHIPYARD_EXPERIENCE,
          PCM.BIRTH_DATE,
          PCM.ADDRESS,
          (SELECT IF(COUNT(*) = 0, FALSE, TRUE) FROM BOAT_CLUB_MEMBERS BC WHERE BC.EMAIL = PCM.EMAIL) AS BC,
          (SELECT GROUP_CONCAT(F.PERIOD) FROM MEMBERS_AND_FEES F WHERE PCM.EMAIL = F.EMAIL GROUP BY F.EMAIL) AS PAYMENTS
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

      <table cellspacing="20">
        <tr>
          <td style="border: none;">
            <form action="./_members.02.php" method="get">
              <input type="hidden" name="task" value="create">
              <input type="submit" value="Create New Member">
            </form> 
          </td>
          <td style="border: none;">  
            <form action="" method="get">
              <input type="submit" value="Query Again ?">
            </form>
          </td>
        </tr>
      </table>  

      <?php

      echo "<table>";
      // <th class="rotate"><div><span>Column header 1</span></div></th>
      // style='transform: rotate(-90deg);'
      echo "<tr><th class='no-rotate'><div><span>Email</span></div></th>" . 
               "<th class='no-rotate'><div><span>Name</span></div></th>" . 
               "<th class='no-rotate'><div><span>Tarif</span></div></th>" . 
               "<th class='no-rotate'><div><span>Amount</span></div></th>" .
               "<th class='no-rotate'><div><span>Payments</span></div></th>" .
               "<th class='no-rotate'><div><span>Telephone</span></div></th>" .
               "<th class='no-rotate'><div><span>Enrolled</span></div></th>" .
               "<th class='rotate' title='News Letter OK?'><div><span>News Letter</span></div></th>" .
               "<th class='rotate'title='Admin Privileges?'><div><span>Admin Priv.</span></div></th>" .
               "<th class='no-rotate'><div><span>BirthDay</span></div></th>" .
               "<th class='no-rotate'><div><span>Address</span></div></th>" .
               "<th class='no-rotate'><div><span>Sailing Exp.</span></div></th>" .
               "<th class='no-rotate'><div><span>Shipyard Exp.</span></div></th>" .
               "<th class='no-rotate'><div><span>Boat Club</span></div></th>" . 
               "<th class='no-rotate'><div><span> - </span></div></th></tr>";
      while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
        echo(
          "<tr><td>" . 
            urldecode($table[0]) . // Email
          "</td><td class='no-wrap'>" .  
            /*mb_convert_encoding($table[1], 'UTF-8', 'ISO-8859-1') . //  utf8_encode*/ ($table[1]) . // Name (full) // 
          "</td><td class='no-wrap-2'>" .  
            $table[2] .   // tarif
          "</td><td style='text-align: right;'>" .  
            $table[3] .   // amount
            "</td><td class='no-wrap-2'>" .  
            $table[13] .   // payments
            "</td><td class='no-wrap-2' style='text-align: center;'>" .  
            $table[4] .   // telephone
            "</td><td class='no-wrap-2'>" .  
            $table[5] .   // first_enrolled
            "</td><td class='cb'>" .  
            ($table[6] ? 'x' : '') .   // NL OK
            "</td><td class='cb'>" .  
            ($table[7] ? 'x' : '') .   // Admin
            "</td><td class='no-wrap-2'>" .  
            $table[10] .   // b-date
            "</td><td class='no-wrap-2'>" .  
            $table[11] .   // address
            "</td><td class='comments'><div class='comments'>" .  
            $table[8] .   // Sailing
            "</div></td><td class='comments'><div class='comments'>" .  
            $table[9] .   // Shipyard
          "</div></td><td style='text-align: center;'>" .  
            ($table[12] ? 'Member' : ($table[2] == 'Passeur d\'Écoute' ? "<a href='./_boat-club.01.php?operation=subscribe&email=" . $table[0] . "'>Subscribe</a>" : "")) . // already in Boat Club, or subscribe if Passeur d'Ecoute.
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
    <!-- Query again button was here -->
    <?php
  }
} else { // Then display the form
    ?>
    <form action="" method="post">
      <input type="hidden" name="operation" value="query">
      <h3>Enter a filter below and click the button (empty filter returns the full list)</h3>
      Joker character is '%'.
      <table>
        <tr>
          <td valign="top">Name (part of first name, last name, email):</td><td><input type="text" name="full-name" size="40" placeholder="Your filter"></td>
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