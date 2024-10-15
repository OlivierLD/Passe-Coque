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
   ! A Form to Update the PASSE_COQUE_MEMBERS table, leads to an update form.
   !
   ! PK (email) as a GET prm.
   ! Can also be used to create or delete a new member.
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
        padding: 2px 20px;
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

require __DIR__ . "/../../php/db.cred.php"; // Warinig: a concatenation

// echo('Before...' . PHP_EOL);

function getTarifs(string $dbhost, string $username, string $password, string $database, bool $verbose = false): array {
  try {
      $link = new mysqli($dbhost, $username, $password, $database);
      
      if ($link->connect_errno) {
          echo("[Oops, errno:".$link->connect_errno."...] ");
          // die("Connection failed: " . $conn->connect_error);
          throw $conn->connect_error;
      } else {
          if ($verbose) {
              echo("[Connected.] ");
          }
      }
      $sql = "SELECT LABEL FROM TARIFS;";
      if ($verbose) {
          echo('[Performing instruction ['.$sql.']] ');
      }
      
      $result = mysqli_query($link, $sql);
      if ($verbose) {
          echo ("Returned " . $result->num_rows . " row(s)<br/>");
      }

      $tarifs = array();
      $tarifIndex = 0;
      while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
          $tarifs[$tarifIndex] = $table[0];
          $tarifIndex++;
      }
      $tarifs[$tarifIndex] = '-'; // For the nulls
      // On ferme !
      $link->close();
      if ($verbose) {
          echo("[Closed DB] ".PHP_EOL);
          echo "Finally, returning $boats";
      }
      return $tarifs;

  } catch (Throwable $e) {
      echo "[ Captured Throwable for connection : " . $e->getMessage() . "] " . PHP_EOL;
      throw $e;
  }                
  return null;
}
// echo('After...' . PHP_EOL);

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

try {
  $tarifList = getTarifs($dbhost, $username, $password, $database); // For the drop-down list
  // foreach($tarifList as $tarif) {
  //   echo('Tarif: ' . $tarif . PHP_EOL);  
  // }
} catch (Throwable $e) {
  echo "Captured Throwable for connection (getTarifs) : " . $e->getMessage() . "<br/>" . PHP_EOL;
}

$create_record = false;
if (isset($_GET['task'])) {
  if ($_GET['task'] === 'create') {
    $create_record = true;
  }
}

if (isset($_POST['operation'])) {
  $operation = $_POST['operation'];
  if ($operation == 'update' || $operation == 'insert') { // Then do the update/insert...

    // echo("Update:" . $_POST['update'] . ", Delete:" . $_POST['delete'] . " <br/>" . PHP_EOL); // Two Submit buttons

    try {
      $email = $_POST['email']; 
      $last_name = $_POST['last-name']; 
      $first_name = $_POST['first-name']; 
      $tarif = $_POST['tarif']; 
      $amount = $_POST['amount']; 
      $telephone = $_POST['telephone']; 
      $first_enrolled = $_POST['first-enrolled']; 
      $birth_date = $_POST['birth-date']; 
      $address = $_POST['address']; 
      $nl_ok = $_POST['nl-ok']; 
      $user_password = $_POST['user-password'];
      $update_psw = $_POST['upd-psswd']; 
      $admin = $_POST['admin-priv']; 
      $user_comments = (isset($_POST['user-comments']) ? $_POST['user-comments'] : '');
      $user_comments_2 = (isset($_POST['user-comments-2']) ? $_POST['user-comments-2'] : '');

      echo ("Comment: [$user_comments] <br/>");

      // $link = mysqli_init();  // Mandatory ?
    
      // echo("Will connect on ".$database." ...<br/> NL_OK: " . $nl_ok . ", email: " . $email . ", Update pswd: " . $update_psw . " to " . $user_password . "<br/>" . PHP_EOL);
      $link = new mysqli($dbhost, $username, $password, $database);
    
      if ($link->connect_errno) {
        echo("Oops, errno:" . $link->connect_errno . "...<br/>");
        die("Connection failed: " . $conn->connect_error);
      } else {
        echo("Connected.<br/>");
      }
    
      if (isset($_POST['update'])) {  // TODO urlencode, dates management ?

        // echo ("Update for $email ... <br/>");

        // TODO STR_TO_DATE for the dates ?:
        $sql = 'UPDATE PASSE_COQUE_MEMBERS ' .
               'SET LAST_NAME = \'' . /*urlencode*/($last_name) . '\', FIRST_NAME = \'' . /*urlencode*/($first_name) . '\', ' .
                    'TARIF = ' . (strlen(trim($tarif)) > 0 && $tarif != '-' ? ('\'' . /*urlencode*/str_replace("'","\'", (trim($tarif))) . '\'') : 'NULL') . ', ' . 
                    'AMOUNT = ' . (strlen(trim($amount)) > 0 ? trim($amount) : 'NULL') . ', ' .
                    'TELEPHONE = ' . (strlen(trim($telephone)) > 0 ? ('\'' . trim($telephone) . '\'') : 'NULL') . ',  ' .
                    'FIRST_ENROLLED = ' . (strlen(trim($first_enrolled)) > 0 ? ('\'' . trim($first_enrolled) . '\'') : 'NULL') . ',  ' .
                    'BIRTH_DATE = ' . (strlen(trim($birth_date)) > 0 ? ('\'' . trim($birth_date) . '\'') : 'NULL') . ',  ' .
                    'ADDRESS = ' . (strlen(trim($address)) > 0 ? ('\'' . (str_replace("'","\'", trim($address))) . '\'') : 'NULL') . ',  ' .
                    'NEWS_LETTER_OK = ' . ($nl_ok === '1' ? 'TRUE' : 'FALSE') . ', ' .
                    ($update_psw === '1' ? 'PASSWORD = \'' . sha1($user_password) . '\', ' : '') .
                    'ADMIN_PRIVILEGES = ' . ($admin === '1' ? 'TRUE' : 'FALSE') . ', ' .
                    'SAILING_EXPERIENCE = ' . (strlen(trim($user_comments)) > 0 ? ('\'' . (str_replace("'","\'", trim($user_comments))) . '\'') : 'NULL') . ', ' .
                    'SHIPYARD_EXPERIENCE = ' . (strlen(trim($user_comments_2)) > 0 ? ('\'' . (str_replace("'","\'", trim($user_comments_2))) . '\'') : 'NULL') . ' ' .
                'WHERE EMAIL = \'' . $email . '\'';
        // echo ("Update Stmt: $sql ; <br/>");
      } else if (isset($_POST['delete'])) {
        $sql = 'DELETE FROM PASSE_COQUE_MEMBERS 
                WHERE EMAIL = \'' . $email . '\'';
      } else if ($operation === 'insert') {
          // TODO STR_TO_DATE for the dates ?:
          $sql = 'INSERT INTO PASSE_COQUE_MEMBERS (EMAIL, LAST_NAME, FIRST_NAME, TARIF, AMOUNT, TELEPHONE, FIRST_ENROLLED, BIRTH_DATE, ADDRESS, NEWS_LETTER_OK, PASSWORD, ADMIN_PRIVILEGES, SAILING_EXPERIENCE, SHIPYARD_EXPERIENCE)
                VALUES (\'' . ($email) . '\', \'' . /*urlencode*/($last_name) . '\', \'' . /*urlencode*/($first_name) . '\', 
                    ' . (strlen(trim($tarif)) > 0 && $tarif != '-' ? ('\'' . /*urlencode*/str_replace("'","\'", (trim($tarif))) . '\'') : 'NULL') . ', 
                    ' . (strlen(trim($amount)) > 0 ? trim($amount) : 'NULL') . ', 
                    ' . (strlen(trim($telephone)) > 0 ? ('\'' . trim($telephone) . '\'') : 'NULL') . ', 
                    ' . (strlen(trim($first_enrolled)) > 0 ? ('\'' . trim($first_enrolled) . '\'') : 'NULL') . ', 
                    ' . (strlen(trim($birth_date)) > 0 ? ('\'' . trim($birth_date) . '\'') : 'NULL') . ', 
                    ' . (strlen(trim($address)) > 0 ? ('\'' . (str_replace("'","\'", trim($address))) . '\'') : 'NULL') . ', 
                    ' . ($nl_ok === '1' ? 'TRUE' : 'FALSE') . ', 
                    ' . (strlen(trim($user_password)) > 0 ? ('\'' . sha1(trim($user_password)) . '\'') : 'NULL') . ', 
                    ' . ($admin === '1' ? 'TRUE' : 'FALSE') . ', 
                    ' . (strlen(trim($user_comments)) > 0 ? ('\'' . (str_replace("'","\'", trim($user_comments))) . '\'') : 'NULL') . ',
                    ' . (strlen(trim($user_comments_2)) > 0 ? ('\'' . (str_replace("'","\'", trim($user_comments_2))) . '\'') : 'NULL') . 
                    ')';
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
    <form action="<?php echo(basename(__FILE__)); ?>" method="get">
      <!--input type="hidden" name="operation" value="blank"-->
      <input type="hidden" name="id" value="<?php echo($email) ?>">
      <table>
        <tr>
          <td colspan="2" style="text-align: center;"><input type="submit" value="Re-Query?"></td>
        </tr>
      </table>
    </form>
    <form action="./_members.01.php" method="get">
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
        IFNULL(PCM.TARIF, \'-\'),
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

    // Open the form, for update
    echo("<form action='#' method='post'>" . PHP_EOL);
    echo "<table>" . PHP_EOL;
    while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result

      // echo ("Debug: " . $table[7] . "...");

      echo('<tr><td>Email</td><td><input type="email" name="email" value="' . urldecode($table[0]) . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>Last Name</td><td><input type="text" name="last-name" value="' . /*utf8_encode*/($table[1]) . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>First Name</td><td><input type="text" name="first-name" value="' . /*utf8_encode*/($table[2]) . '" size="40"></td><tr>' . PHP_EOL);

      echo('<tr><td>Tarif</td><td>'); // <input type="text" name="tarif" value="' . ($table[3] != null ? /*utf8_encode*/($table[3]) : '') . '" size="40"></td><tr>' . PHP_EOL);
      echo('<select name="tarif">');
      foreach($tarifList as $tarif) {
        echo('<option value="' . $tarif . '"' . ($tarif == $table[3] ? ' selected' : '') . '>' . $tarif . '</option>');
      }
      echo ('</select>');
      echo('</td><tr>' . PHP_EOL);

      echo('<tr><td>Amount</td><td><input type="number" name="amount" value="' . $table[4] . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>Telephone</td><td><input type="text" name="telephone" value="' . $table[5] . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>First Enrolled</td><td><input type="date" name="first-enrolled" value="' . ($table[6] != null ? date('Y-m-d', strtotime($table[6])) : '') . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>Birth Date</td><td><input type="date" name="birth-date" value="' . ($table[12] != null ? /*urldecode*/($table[12]) : '') . '" size="40"></td><tr>' . PHP_EOL);
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
          <input type='hidden' name='email' value='<?php echo $email; ?>'>
          <input type='hidden' name='period' value='<?php echo $table[0]; ?>'>
        <?php
        echo("<tr>" . 
              "<td>" . $table[0] . "</td>" . 
              "<td><input type='number' name='fee-amount' value='" . $table[1] . "'></td>" . 
              "<td><input type='submit' name='update' value='Update'></td>" .
              "<td><input type='submit' name='delete' value='Delete'></td>" .
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
} else if ($create_record) {
  // Display the form to create the record
  ?>
  <form action="#" method="post">
    <input type="hidden" name="operation" value="insert">
    <table>
      <tr><td>Email</td><td><input type="email" name="email" size="40"></td><tr>
      <tr><td>Last Name</td><td><input type="text" name="last-name" size="40"></td><tr>
      <tr><td>First Name</td><td><input type="text" name="first-name" size="40"></td><tr>
      <tr><td>Tarif</td><td><!--input type="text" name="tarif" size="40"-->
      <?php
      echo('<select name="tarif">');
      foreach($tarifList as $tarif) {
        echo('<option value="' . $tarif . '">' . $tarif . '</option>');
      }
      echo ('</select>');
      ?>
      </td><tr>
      <tr><td>Amount</td><td><input type="number" name="amount" size="40"></td><tr>
      <tr><td>Telephone</td><td><input type="text" name="telephone" size="40"></td><tr>
      <tr><td>Enrolled</td><td><input type="date" name="first-enrolled" size="40"></td><tr>
      <tr><td>Birth Date</td><td><input type="date" name="birth-date" size="40"></td><tr>
      <tr><td style="vertical-align: top;">Address</td><td><textarea rows="4" cols="40" name="address"></textarea></td><tr>
      <tr><td>News Letter ?</td><td><input type="checkbox" name="nl-ok" value="1" checked></td><tr>
      <tr><td>Password</td><td><input type="password" name="user-password" size="40"></td><tr>
      <tr><td>Admin privileges</td><td><input type="checkbox" name="admin-priv" value="1"></td><tr>
      <tr><td style="vertical-align: top;">Sailing Experience</td><td><textarea rows="4" cols="40" name="user-comments"></textarea></td><tr>
      <tr><td style="vertical-align: top;">Shipyard Experience</td><td><textarea rows="4" cols="40" name="user-comments-2"></textarea></td><tr>

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