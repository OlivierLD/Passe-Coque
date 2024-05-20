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
   ! Update the member fees
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <!--meta http-equiv="Content-Type" content="text/html; charset=utf-8"/-->
    <title>Edit Members</title>
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

// var_dump($_POST);

if (isset($_POST['operation'])) { // update-fee, create-fee
  $operation = $_POST['operation'];
  if ($operation == 'update-fee' || $operation == 'create-fee') { // Then do the update/insert...

    // echo("Update:" . $_POST['update'] . ", Delete:" . $_POST['delete'] . " <br/>" . PHP_EOL); // Two Submit buttons

    try {
      $email = $_POST['email']; 
      $amount = (isset($_POST['fee-amount']) ? $_POST['fee-amount'] : ''); 
      $period = (isset($_POST['period']) ? $_POST['period'] : ''); 

      $link = new mysqli($dbhost, $username, $password, $database);
    
      if ($link->connect_errno) {
        echo("Oops, errno:" . $link->connect_errno . "...<br/>");
        die("Connection failed: " . $conn->connect_error);
      } else {
        echo("Connected.<br/>");
      }
    
      if (isset($_POST['update'])) {

        // echo ("Update for $email ... <br/>");

        $sql = 'UPDATE MEMBER_FEES ' .
               'SET AMOUNT = ' . (strlen(trim($amount)) > 0 ? trim($amount) : 'NULL') . ' ' .
               'WHERE EMAIL = \'' . $email . '\' AND PERIOD = STR_TO_DATE(\'' . $period . '\', \'%Y-%m-%d\');';
        // echo ("Update Stmt: $sql ; <br/>");
      } else if (isset($_POST['delete'])) {
        $sql = 'DELETE FROM MEMBER_FEES 
                WHERE EMAIL = \'' . $email . '\' AND PERIOD = STR_TO_DATE(\'' . $period . '\', \'%Y-%m-%d\');';
      } else if (isset($_POST['insert'])) {
        // New fee
        $sql = 'INSERT INTO MEMBER_FEES (EMAIL, PERIOD, AMOUNT)
                VALUES (\'' . ($email) . '\', STR_TO_DATE(\'' . $period . '\', \'%Y-%m-%d\'), ' . (strlen(trim($amount)) > 0 ? trim($amount) : 'NULL') . ')';
      } else if ($operation == 'create-fee') {
        $sql = '';
        // Enter new fee, the form
        ?>
        <form action="<?php echo(basename(__FILE__)); ?>" method="post">
        <input type="hidden" name="operation" value="create-fee">
        <input type="hidden" name="email" value="<?php echo($email) ?>">
        <table>
          <tr>
            <td>Date</td><td><input type="date" name="period"></td>
          </tr>
          <tr>
            <td>Amount</td><td><input type="number" name="fee-amount"></td>
          </tr>
        </table>
        <input type="submit" value="Create Fee" name="insert">
      </form>
  
        <?php
      } else {
        echo "What do you want ?? <br/>" . PHP_EOL;
        $sql = '';
      }

      echo('Performing query <code>' . $sql . '</code><br/>');
    
      if (true && strlen(trim($sql)) > 0) { // Do perform ?
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
    <form action="./_members.02" method="get">
      <!--input type="hidden" name="operation" value="blank"-->
      <input type="hidden" name="id" value="<?php echo($email) ?>">
      <table>
        <tr>
          <td colspan="2" style="text-align: center;"><input type="submit" value="Re-Query?"></td>
        </tr>
      </table>
    </form>
    <form action="./_members.02.php" method="get">
      <input type="hidden" name="id" value="<?php echo($email) ?>">
      <input type="submit" value="Query Form">
    </form>
    <?php
  } else {
    echo ("Operation $operation ...");
  }
} else if (isset($_GET['id'])) { // Then display the form to update
  $email = $_GET['id'];
  try {
    $link = new mysqli($dbhost, $username, $password, $database);
    
    if ($link->connect_errno) {
      echo("Oops, errno:".$link->connect_errno."...<br/>");
      die("Connection failed: " . $conn->connect_error);
    } else {
      echo("Connected.<br/>");
    }
  
    $sql = 'SELECT
        PCM.EMAIL,
        PCM.LAST_NAME,
        PCM.FIRST_NAME,
        PCM.TARIF,
        PCM.AMOUNT,
        PCM.TELEPHONE,
        PCM.FIRST_ENROLLED,
        PCM.NEWS_LETTER_OK,
        PCM.PASSWORD,
        PCM.ADMIN_PRIVILEGES,
        PCM.SAILING_EXPERIENCE,
        PCM.SHIPYARD_EXPERIENCE,
        PCM.BIRTH_DATE,
        PCM.ADDRESS
    FROM 
        PASSE_COQUE_MEMBERS PCM
    WHERE 
         PCM.EMAIL = \'' . $email . '\';';

    echo('Performing query <code>' . $sql . '</code><br/>');
        
    // $result = mysql_query($sql, $link);
    $result = mysqli_query($link, $sql);
    echo ("Returned " . $result->num_rows . " row(s)<br/>");
    if ($result->num_rows > 1) {
      echo "Bizarre...";
    }
    echo("<h2>Row for $email </h2>");
    ?>
    <form action="#" method="post">
      <input type="hidden" name="operation" value="update">
      <div style="display: grid; grid-template-columns: 50% 50%;"> <!-- 2 columns: Member, and Member's Fees -->
      <div id="left">
    <?php

    // Open the form
    echo("<form action='#' method='post'>" . PHP_EOL);
    echo "<table>" . PHP_EOL;
    while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result

      // echo ("Debug: " . $table[7] . "...");

      echo('<tr><td>Email</td><td><input type="email" name="email" value="' . urldecode($table[0]) . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>Last Name</td><td><input type="text" name="last-name" value="' . /*utf8_encode*/($table[1]) . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>First Name</td><td><input type="text" name="first-name" value="' . /*utf8_encode*/($table[2]) . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>Tarif</td><td><input type="text" name="tarif" value="' . ($table[3] != null ? /*utf8_encode*/($table[3]) : '') . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>Amount</td><td><input type="number" name="amount" value="' . $table[4] . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>Telephone</td><td><input type="text" name="telephone" value="' . $table[5] . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>Enrolled</td><td><input type="text" name="first-enrolled" value="' . ($table[6] != null ? urldecode($table[6]) : '') . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>Birth Date</td><td><input type="text" name="birth-date" value="' . ($table[12] != null ? urldecode($table[12]) : '') . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td style="vertical-align: top;">Address</td><td><textarea rows="4" cols="40" name="address">' . /*utf8_encode*/($table[13]) . '</textarea></td><tr>' . PHP_EOL);
      echo('<tr><td>News Letter ?</td><td><input type="checkbox" name="nl-ok" value="1"' . ($table[7] ? " checked" : "")  . '></td><tr>' . PHP_EOL); 
      echo('<tr><td>Password</td><td><input type="password" name="user-password" value="' . ($table[8] != null ? urldecode($table[8]) : '') . '" size="40"></td><td><input type="checkbox" name="upd-psswd" value="1"> Update password</td><tr>' . PHP_EOL);
      echo('<tr><td>Admin privileges</td><td><input type="checkbox" name="admin-priv" value="1"' . ($table[9] ? " checked" : "")  . '></td><tr>' . PHP_EOL); 
      echo('<tr><td style="vertical-align: top;">Sailing Experience</td><td><textarea rows="4" cols="40" name="user-comments">' . /*utf8_encode*/($table[10]) . '</textarea></td><tr>' . PHP_EOL);
      echo('<tr><td style="vertical-align: top;">Shipyard Experience</td><td><textarea rows="4" cols="40" name="user-comments-2">' . /*utf8_encode*/($table[11]) . '</textarea></td><tr>' . PHP_EOL);

    }
    echo "</table>" . PHP_EOL;
    ?>
      <input type="submit" value="Update" name="update"> <input type="submit" value="Delete" name="delete">
    </form>
    <form action="./_members.01.php" method="get">
      <input type="submit" value="Query Form">
    </form>
    </div>  <!-- End of left div -->

    <div id="right"> <!-- Fees -->
    <?php  
    $sql = 'SELECT PERIOD, AMOUNT FROM MEMBER_FEES WHERE  EMAIL = \'' . $email . '\' ORDER BY 1;';

    echo('Performing query <code>' . $sql . '</code><br/>');

    try {
      $result = mysqli_query($link, $sql);
      echo ("Returned " . $result->num_rows . " row(s)<br/>");

      echo("<h3>Fees for " . $email . "</h3>" . PHP_EOL);

      echo("<table>");
      echo("<tr><th>Date</th><th>Amount</th><th-</th></tr>");
      while ($table = mysqli_fetch_array($result)) {
        ?>
        <form action='./_member.fees.php' method='post'>
          <input type='hidden' name='operation' value='update-fee'>
          <input type='hidden' name='id' value='<?php echo $email; ?>'>
        <?php
        echo("<tr>" . 
              "<td>" . $table[0] . "</td>" . 
              "<td><input type='number' name='fee-amount' value='" . $table[1] . "'></td>" . 
              "<td><input type='submit' value='Update'></td>" .
              "<td><input type='submit' value='Delete'></td>" .
            "</tr>");
        ?>
        </form>
        <?php
      }
      echo("</table>");
      ?>

      <form action='./_member.fees.php' method='post'>  <!--  TODO Implement !!! -->
        <input type='hidden' name='email' value='<?php echo $email; ?>'>
        <input type='hidden' name='operation' value='create-fee'>
        <input type="submit" value="Create a Fee">
      </form>
      <?php
      echo("</form>");

    } catch (Throwable $ex) {
      echo "Captured Throwable for connection : " . $ex->getMessage() . "<br/>" . PHP_EOL;
  }
    ?>
    </div>
    <?php
    // On ferme !
    $link->close();


  } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
  }
} else {
  // WHAT ??
  echo "What ??";
}
    ?>        
  </body>        
</html>