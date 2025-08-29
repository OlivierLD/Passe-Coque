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
   ! Access to the PROJECT_OWNERS Table
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Referents - Members, Projects</title>
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
    <h1>PHP / MySQL. Passe-Coque Projects. Depends on Members and Projects</h1>

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
      $prj_id = $_POST['prj-id'];
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

      $sql = 'SELECT PO.PROJECT_ID,
                     PO.OWNER_EMAIL,
                     PRJ.PROJECT_NAME,
                     CONCAT(MEM.FIRST_NAME, \' \', UPPER(MEM.LAST_NAME)) AS FULL_NAME
              FROM PROJECT_OWNERS PO,
                   PROJECTS PRJ,
                   PASSE_COQUE_MEMBERS MEM
              WHERE (UPPER(PO.PROJECT_ID) LIKE UPPER(\'%' . $prj_id . '%\') AND
                     (UPPER(PO.OWNER_EMAIL) LIKE UPPER(\'%' . $ref_name . '%\') OR
                      UPPER(MEM.FIRST_NAME) LIKE UPPER(\'%' . $ref_name . '%\') OR
                      UPPER(MEM.LAST_NAME) LIKE UPPER(\'%' . $ref_name . '%\'))) AND
                    PO.PROJECT_ID = PRJ.PROJECT_ID AND
                    PO.OWNER_EMAIL = MEM.EMAIL
              ORDER BY 1;';

      echo('Performing query <code>' . $sql . '</code><br/>');

      // $result = mysql_query($sql, $link);
      $result = mysqli_query($link, $sql);
      echo ("Returned " . $result->num_rows . " row(s)<br/>");

      echo("<h2>Passe-Coque Project Owners</h2>");

      ?> <!-- Member creation form -->

      <form action="./_projects.04.php" method="get">
        <input type="hidden" name="task" value="create">
        <input type="submit" value="Create New Project Owner">
      </form>

      <?php


      echo "<table>";
      echo "<tr><th>Project</th><th>Project Owner</th><th>-</th></tr>";

      while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
        echo(
          "<tr><td>" .
            urldecode($table[0]) . ', ' . $table[2] . "</td><td>" . urldecode($table[1]) . ' - ' . $table[3] . "</td><td>" . "<a href='./_projects.04.php?tx-id=" . $table[0] . "&owner=" . $table[1] . "'>Edit</a>" . //  target='RefUpdate'
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
          <td valign="top">Project (part of ID):</td><td><input type="text" name="prj-id" size="40"></td>
        </tr>
        <tr>
          <td valign="top">Referent (part of email, first or last name):</td><td><input type="text" name="ref-name" size="40"></td>
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