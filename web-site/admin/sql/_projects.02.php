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
   ! A Form to Update the PROJECTS table, leads to an update form.
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
    <title>Edit Projects</title>
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
    <h1>PHP / MySQL. Passe-Coque Projects</h1>

    <?php
// phpinfo();

require __DIR__ . "/../../php/db.cred.php"; // Warinig: a concatenation

// echo('Before...' . PHP_EOL);

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
      $tx_id = $_POST['tx-id'];
      $project_name = $_POST['prj-name'];
      $description = $_POST['description'];

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

        $sql = 'UPDATE PROJECTS ' .
               'SET PROJECT_NAME = \'' . /*urlencode*/($project_name) . '\', DESCRIPTION = \'' . /*urlencode*/($description) . '\' ' .
                'WHERE PROJECT_ID = \'' . $tx_id . '\'';
        // echo ("Update Stmt: $sql ; <br/>");
      } else if (isset($_POST['delete'])) {
        $sql = 'DELETE FROM PROJECTS
                WHERE PROJECT_ID = \'' . $tx_id . '\'';
      } else if ($operation === 'insert') {
          // TODO STR_TO_DATE for the dates ?:
          $sql = 'INSERT INTO PROJECTS (PROJECT_ID, PROJECT_NAME, DESCRIPTION) ' .
                 'VALUES (\'' . $tx_id . '\', \'' . /*urlencode*/($project_name) . '\', \'' . /*urlencode*/($description) . '\')';
      } else {
        echo "What do you want ?? <br/>" . PHP_EOL;
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
      <input type="hidden" name="tx-id" value="<?php echo($tx_id) ?>">
      <table>
        <tr>
          <td colspan="2" style="text-align: center;"><input type="submit" value="Re-Query?"></td>
        </tr>
      </table>
    </form>
    <form action="./_projects.01.php" method="get">
      <input type="submit" value="Query Form">
    </form>
    <?php
  } else {
    echo ("Operation $operation ...");
  }
} else if (isset($_GET['tx-id'])) { // Then display the form to update
  $tx_id = $_GET['tx-id'];
  try {
    $link = new mysqli($dbhost, $username, $password, $database);

    if ($link->connect_errno) {
      echo("Oops, errno:".$link->connect_errno."...<br/>");
      die("Connection failed: " . $conn->connect_error);
    } else {
      echo("Connected.<br/>");
    }

    $sql = 'SELECT
        PRJ.PROJECT_ID,
        PRJ.PROJECT_NAME,
        PRJ.DESCRIPTION
    FROM
        PROJECTS PRJ
    WHERE
         PRJ.PROJECT_ID = \'' . $tx_id . '\';';

    echo('Performing query <code>' . $sql . '</code><br/>');

    // $result = mysql_query($sql, $link);
    $result = mysqli_query($link, $sql);
    echo ("Returned " . $result->num_rows . " row(s)<br/>");
    if ($result->num_rows > 1) {
      echo "Bizarre...";
    }
    echo("<h2>Row for $tx_id </h2>");
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

      echo('<tr><td>ID</td><td><input type="text" name="tx-id" value="' . urldecode($table[0]) . '" size="20"></td><tr>' . PHP_EOL);
      echo('<tr><td>Name</td><td><input type="text" name="prj-name" value="' . /*utf8_encode*/($table[1]) . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>Description</td><td><input type="text" name="description" value="' . /*utf8_encode*/($table[2]) . '" size="40"></td><tr>' . PHP_EOL);

    }
    echo "</table>" . PHP_EOL;
    ?>
      <input type="submit" value="Update" name="update"> <input type="submit" value="Delete" name="delete">
    </form>
    <form action="./_projects.01.php" method="get">
      <input type="submit" value="Query Form">
    </form>
    </div>  <!-- End of left div -->

    <div id="right">
      <!-- Empty -->
    </div>

      <?php
      echo("</form>");

    } catch (Throwable $ex) {
      echo "Captured Throwable for connection : " . $ex->getMessage() . "<br/>" . PHP_EOL;
    }
    ?>

    <?php
    // On ferme !
    $link->close();


  // } catch (Throwable $e) {
  //     echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
  // }
} else if ($create_record) {
  // Display the form to create the record
  ?>
  <form action="#" method="post">
    <input type="hidden" name="operation" value="insert">
    <table>
      <tr><td>ID</td><td><input type="text" name="tx-id" size="20"></td><tr>
      <tr><td>Name</td><td><input type="text" name="prj-name" size="40"></td><tr>
      <tr><td>Description</td><td><input type="text" name="description" size="40"></td><tr>
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