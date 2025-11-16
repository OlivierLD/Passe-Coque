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
   ! BOAT CLUB MANAGEMENT
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
    <h1>PHP / MySQL. Passe-Coque Boat-Club Members</h1>

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

$operation = '';
if (isset($_POST['operation'])) {
  $operation = $_POST['operation'];
} else if (isset($_GET['operation'])) {
  $operation = $_GET['operation'];
}

if ($operation != '') {

  echo("Operation [" . $operation . "]<br/>");

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
          DATE_FORMAT(BC.ENROLLED, \'%d-%b-%Y\'),
          BC.MEMBER_LEVEL,
          BC.FEE_AMOUNT,
          DATE_FORMAT(BC.LAST_FEE_UPDATE, \'%d-%b-%Y\')
      FROM
          PASSE_COQUE_MEMBERS PCM,
          BOAT_CLUB_MEMBERS BC
      WHERE
           (UPPER(PCM.FIRST_NAME) LIKE UPPER(\'%' . $name . '%\') OR
            UPPER(PCM.LAST_NAME) LIKE UPPER(\'%' . $name . '%\') OR
            UPPER(PCM.EMAIL) LIKE UPPER(\'%' . $name . '%\')) AND
           (PCM.EMAIL = BC.EMAIL)
      ORDER BY 2;';

      echo('Performing query <code>' . $sql . '</code><br/>');

      // $result = mysql_query($sql, $link);
      $result = mysqli_query($link, $sql);
      echo ("Returned " . $result->num_rows . " row(s)<br/>");

      echo("<h2>Passe-Coque Boat Club Members</h2>");

      ?>
      <!-- Member creation form -->

      <form action="<?php echo basename(__FILE__); ?>" method="post">
        <input type="hidden" name="operation" value="new-member">
        <input type="submit" value="Create New Boat Club Member">
      </form>

      <?php


      echo "<table>";
      echo "<tr><th>Email</th><th>Name</th><th>Enrolled</th><th>Level</th><th>Amount</th><th>Renewed</th><th>-</th></tr>";
      while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
        echo(
          "<tr><td>" .
            /*urldecode*/($table[0]) . // Email
          "</td><td>" .
            /*utf8_encode*/($table[1]) . // Name (full)
          "</td><td>" .
            $table[2] . // Enrolled
          "</td><td>" .
            $table[3] . // Level
          "</td><td>" .
            $table[4] . // Amount
          "</td><td>" .
            $table[5] . // Re-newed
          "</td><td>" .
            "<a href='./_boat-club.01.php?operation=edit&id=" . $table[0] . "'>Edit</a>" .
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
    <form action="<?php echo basename(__FILE__); ?>" method="get">
      <table>
        <tr>
          <td colspan="2" style="text-align: center;"><input type="submit" value="Query Boat Club Again ?"></td>
        </tr>
      </table>
    </form>
    <!-- Query Passe-Coque Member -->
    <form action="./_members.01.php" method="get">
      <table>
        <tr>
          <td colspan="2" style="text-align: center;"><input type="submit" value="Query Asso ?"></td>
        </tr>
      </table>
    </form>
    <?php
  } else if ($operation == 'subscribe') { // Create New Boat-Club Member from his email

    // Fetch existing data, display submit form
    ?>
    <h2>New Boat Club Member ! Welcome !</h2>
    <?php
    $email = $_GET['email'];

    echo("For user $email");
    // Display form
    ?>
    <form action="<?php echo basename(__FILE__); ?>" method="post">
      <input type="hidden" name="operation" value="create">
      <input type="hidden" name="email" value="<?php echo $email; ?>">
      <table>
        <tr>
          <td>Member Level</td>
          <td>
            <select name="level">
              <option value="CREW">CREW</option>
              <option value="SKIPPER">SKIPPER</option>
              <option value="NONE">NONE</option>
            </select>
          </td>
        </tr>
        <tr><td>Fee Amount</td><td><input type="number" name="fee-amount" min="0" step="0.01"></td></tr>
      </table>
      <input type="submit" value="Subscribe">
    </form>

    <?php

  } else if ($operation == 'create') {
    // Receive the values, do the insert
    $email = $_POST['email'];
    $level = $_POST['level'];
    $amount = $_POST['fee-amount'];

    try {
      echo("Will connect on ".$database." ...<br/>");
      $link = new mysqli($dbhost, $username, $password, $database);

      if ($link->connect_errno) {
        echo("Oops, errno:".$link->connect_errno."...<br/>");
        die("Connection failed: " . $conn->connect_error);
      } else {
        echo("Connected.<br/>");
      }

      $sql = 'INSERT INTO BOAT_CLUB_MEMBERS (EMAIL, MEMBER_LEVEL, FEE_AMOUNT)
      VALUES (\'' .$email . '\', \'' . $level . '\', ' . $amount . ');';

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
    ?>

    <form action="<?php echo basename(__FILE__); ?>" method="get">
      <input type="submit" value="Query Form">
    </form>
    <!-- Query Passe-Coque Member -->
    <form action="./_members.01.php" method="get">
         <input type="submit" value="Query Asso ?">
    </form>

    <?php
  } else if ($operation == 'edit') { // Edit Boat-Club Member

    $email = $_GET['id'];

    echo("Editing " . $email);
    // Get the data to update

    try {
    echo("Will connect on ".$database." ...<br/>");
    $link = new mysqli($dbhost, $username, $password, $database);

    if ($link->connect_errno) {
      echo("Oops, errno:".$link->connect_errno."...<br/>");
      die("Connection failed: " . $conn->connect_error);
    } else {
      echo("Connected.<br/>");
    }

    $sql = 'SELECT
        BC.EMAIL,
        CONCAT(
            UPPER(PCM.LAST_NAME),
            \' \',
            PCM.FIRST_NAME
        ) AS NAME,
        DATE_FORMAT(BC.ENROLLED, \'%Y-%m-%d\'),
        BC.MEMBER_LEVEL,
        BC.FEE_AMOUNT,
        DATE_FORMAT(BC.LAST_FEE_UPDATE, \'%Y-%m-%d\')
    FROM
        PASSE_COQUE_MEMBERS PCM,
        BOAT_CLUB_MEMBERS BC
    WHERE
         PCM.EMAIL = \'' . $email . '\' AND
         (PCM.EMAIL = BC.EMAIL);';

    echo('Performing query <code>' . $sql . '</code><br/>');

    // $result = mysql_query($sql, $link);
    $result = mysqli_query($link, $sql);
    echo ("Returned " . $result->num_rows . " row(s)<br/>");

    ?>
    <h2>Passe-Coque Boat Club Members Update</h2>
    <form action='<?php echo basename(__FILE__); ?>' method='post'>
    <?php
    echo("<input type='hidden' name='operation' value='update'>");
    echo("<input type='hidden' name='email' value='$email'>");
    echo "<table>";
    while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
      echo(
        "<tr>" .
          "<td>Email</td><td>" . urldecode($table[0]) . "</td>" .
        "</tr><tr>" .
          "<td>Full Name</td><td>" . utf8_encode($table[1]) . "</td>" .
        "</tr><tr>" .
          "<td>Enrolled</td><td><input type='date' name='enrolled' value='" . ($table[2]) . "'></td>" .
        "</tr><tr>" .
          "<td>Level</td><td>" .
            "<select name='level'>" .
              "<option value='CREW'" . ($table[3] == 'CREW' ? ' selected' : '') . ">CREW</option>" .
              "<option value='SKIPPER'" . ($table[3] == 'SKIPPER' ? ' selected' : '') . ">SKIPPER</option>" .
              "<option value='NONE'" . ($table[3] == 'NONE' ? ' selected' : '') . ">NONE</option>" .
            "</select>" .
          "</td>" .
        "</tr><tr>" .
          "<td>Amount</td><td><input type='number' name='amount' value='" . ($table[4]) . "' min='0' step='0.01'></td>" .
        "</tr><tr>" .
          "<td>Renewed</td><td><input type='date' name='renewed' value='" . ($table[5]) . "'></td>" .
        "</tr>" . PHP_EOL);
    }
    ?>
      </table>
      <input type="submit" name="update" value="Update"> <input type="submit" name="delete" value="Delete">
    </form>
    <?php
    // On ferme !
    $link->close();
    echo("Closed DB<br/>".PHP_EOL);
  } catch (Throwable $e) {
    echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
  }
  echo("<hr/>" . PHP_EOL);
    ?>

    <form action="<?php echo basename(__FILE__); ?>" method="get">
      <input type="submit" value="Back to Query Form">
    </form>

    <?php
  } else if ($operation == 'update') {

    // echo ("It's an update !!");

    $task = 'update';
    if (isset($_POST['delete'])) {
      $task = 'delete';
    }
    // Receive the values, do the insert
    $email = $_POST['email'];
    $enrolled = $_POST['enrolled'];
    $level = $_POST['level'];
    $amount = $_POST['amount'];
    $renewed = $_POST['renewed'];

    try {
      echo("Will connect on ".$database." ...<br/>");
      $link = new mysqli($dbhost, $username, $password, $database);

      if ($link->connect_errno) {
        echo("Oops, errno:".$link->connect_errno."...<br/>");
        die("Connection failed: " . $conn->connect_error);
      } else {
        echo("Connected.<br/>");
      }

      if ($task == 'update') {
        $sql = 'UPDATE BOAT_CLUB_MEMBERS ' .
                'SET ENROLLED = STR_TO_DATE(\'' .$enrolled . '\', \'%Y-%m-%d\'), ' .
                    'MEMBER_LEVEL = \'' . $level . '\', ' .
                    'FEE_AMOUNT = ' . $amount . ', ' .
                    'LAST_FEE_UPDATE = ' . (strlen(trim($renewed)) == 0 ? 'NULL' : 'STR_TO_DATE(\'' .$renewed . '\', \'%Y-%m-%d\')') . ' ' .
              'WHERE EMAIL = \'' . $email . '\';';
      } else if ($task == 'delete') {
        $sql = 'DELETE FROM BOAT_CLUB_MEMBERS ' .
              'WHERE EMAIL = \'' . $email . '\';';
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
    ?>

    <!--form action="./_boat-club.01.php" method="get"-->
    <form action="<?php echo basename(__FILE__); ?>" method="get">
      <input type="submit" value="Query Form">
    </form>

    <?php
  } else if ($operation == 'new-member') {
    ?>
      <ol>
        <li>Go to the <a href="./_members.01.php">Passe-Coque Members List</a></li>
        <li>choose the Passe-Coque member you want to add to the Boat Club</li>
        <li>and click 'Subscribe' (if the 'Subscribe' button is not here, check the member's 'Tarif')</li>
      </ol>

    <?php
  }
} else { // Then display the query form
    ?>
    <!--form action="./_boat-club.01.php" method="post"-->
    <form action="<?php echo basename(__FILE__); ?>" method="post">

      <input type="hidden" name="operation" value="query">
      <table>
        <tr>
          <td valign="top">Subscriber's Name (part of first name, last name, email):</td><td><input type="text" name="full-name" size="40"></td>
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