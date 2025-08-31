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
   ! Access to the PROJECTS_DONORS View and PROJECT_CONTRIBUTORS Table.
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Members, Projects, Donors &amp; Contributors</title>
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
    <h1>PHP / MySQL. Passe-Coque Projects Donors and Contributors.</h1>
    <h3>Depends on Members and Projects</h3>

    <?php
// phpinfo();

require __DIR__ . "/../../php/db.cred.php";


// Utilities
class ShortProject {
    public $id;
    public $name;
}

function getProjectList(string $dbhost, string $username, string $password, string $database, bool $verbose = false): array {
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
      $sql = "SELECT PROJECT_ID, PROJECT_NAME FROM PROJECTS;";
      if ($verbose) {
          echo('[Performing instruction [' . $sql . ']] ');
      }

      $result = mysqli_query($link, $sql);
      if ($verbose) {
          echo ("Returned " . $result->num_rows . " row(s)<br/>");
      }

      $projects = array();
      $prjIndex = 0;

      while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
          $projects[$prjIndex] = new ShortProject(); // $table[0];
          $projects[$prjIndex]->id = $table[0];
          $projects[$prjIndex]->name = urldecode($table[1]);
          $prjIndex++;
      }
      // $projects[$prjIndex] = '-'; // For the nulls
      // On ferme !
      $link->close();
      if ($verbose) {
          echo("[Closed DB] ".PHP_EOL);
          echo "Finally, returning $boats";
      }
      return $projects;

  } catch (Throwable $e) {
      echo "[ Captured Throwable for connection : " . $e->getMessage() . "] " . PHP_EOL;
      throw $e;
  }
  return null;
}

class ShortMember {
    public $email;
    public $name;
}

function getMemberList(string $dbhost, string $username, string $password, string $database, bool $verbose = false): array {
    try {
        $link = new mysqli($dbhost, $username, $password, $database);

        if ($link->connect_errno) {
            echo("[Oops, errno:".$link->connect_errno."...] ");
            // die("Connection failed: " . $conn->connect_error);
            throw $conn->connect_error;
        } else {
            if ($verbose) {
                echo("Connected.<br/>" . PHP_EOL);
            }
        }
        $sql = "SELECT EMAIL, CONCAT(FIRST_NAME, ' ', LAST_NAME) AS NAME FROM PASSE_COQUE_MEMBERS;";
        if ($verbose) {
            echo('[Performing instruction [' . $sql . ']] <br/>' . PHP_EOL);
        }

        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
        }

        $members = array();
        $memberIndex = 0;
        while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
            $members[$memberIndex] = new ShortMember();
            $members[$memberIndex]->email = $table[0];
            $members[$memberIndex]->name = urldecode($table[1]);
            $memberIndex++;
        }
        // On ferme !
        $link->close();
        if ($verbose) {
            echo("[Closed DB] ".PHP_EOL);
            echo "Finally, returning $members";
        }
        return $members;
    } catch (Throwable $e) {
        echo "[ Captured Throwable for connection : " . $e->getMessage() . "] " . PHP_EOL;
        throw $e;
    }
    return null;
}


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
      $member_name = $_POST['member-name'];

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
                     NAME,
                     PROJECT_ID,
                     PROJECT_NAME,
                     DESCRIPTION
              FROM PROJECTS_DONORS
              WHERE (UPPER(PROJECT_ID) LIKE UPPER(\'%' . $prj_id . '%\') OR UPPER(PROJECT_NAME) LIKE UPPER(\'%' . $prj_id . '%\')) AND
                     (UPPER(EMAIL) LIKE UPPER(\'%' . $member_name . '%\') OR
                      UPPER(NAME) LIKE UPPER(\'%' . $member_name . '%\'))
              ORDER BY 1;';

      echo('Performing query <code>' . $sql . '</code><br/>');

      // $result = mysql_query($sql, $link);
      $result = mysqli_query($link, $sql);
      echo ("Returned " . $result->num_rows . " row(s)<br/>");

      echo("<h2>Passe-Coque Project Donors and Contributors</h2>");

      ?> <!-- Member creation form -->

      <!--form action="./_projects.donors.php" method="get"-->
      <form action="#" method="post">
        <input type="hidden" name="operation" value="create">
        <input type="submit" value="Create New Project Donor or Contributor">
      </form>

      <?php


      echo "<table>";
      echo "<tr><th>Donor / Contributor</th><th>Project</th><th>Description</th><th>-</th></tr>";

      while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
        echo(
          "<tr><td>" . urldecode($table[0]) . ', ' . $table[1] . "</td>" .
              "<td>" . urldecode($table[2]) . ' - ' . $table[3] . "</td>" .
              "<td>" . $table[4] . "</td>" .
              "<td><form action=\"#\" method=\"post\">" .
                     "<input type=\"hidden\" name=\"operation\" value=\"delete\">" .
                     "<input type=\"hidden\" name=\"tx-id\" value=\"" . $table[2] . "\">" .
                     "<input type=\"hidden\" name=\"owner\" value=\"" . $table[0] . "\">" .
                     "<input type=\"submit\" value=\"Delete\">" .
                   "</form>" .
                // "<a href='./_projects.donors.php?tx-id=" . $table[2] . "&owner=" . $table[0] . "'>Delete</a>" . //  target='RefUpdate'
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
  }  else if ($operation == 'delete') {
    $prj_id = $_POST['tx-id'];
    $owner = $_POST['owner'];
    echo "Will delete Project " . $prj_id . ", owner " . $owner . " from PROJECT_CONTRIBUTORS... <br/>" . PHP_EOL;

    try {
      $link = new mysqli($dbhost, $username, $password, $database);

      if ($link->connect_errno) {
        echo("Oops, errno:" . $link->connect_errno . "...<br/>");
        die("Connection failed: " . $conn->connect_error);
      } else {
        echo("Connected.<br/>");
      }
      $sql = 'DELETE FROM PROJECT_CONTRIBUTORS WHERE (PROJECT_ID = \'' . ($prj_id) . '\' AND DONOR_EMAIL = \'' . ($owner) . '\')';
      echo('Performing statement <code>' . $sql . '</code><br/>');

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
      ?>
    <form action="#" method="get">
      <input type="submit" value="Query Form">
    </form>
      <?php

    } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }

  }  else if ($operation == 'create') {
    echo "Create a new Project Donor or Contributor. <br/>" . PHP_EOL;
    // Display a form to create a new entry, with members, and projects
    try {
      $prjList = getProjectList($dbhost, $username, $password, $database); // For the drop-down list
      $memberList = getMemberList($dbhost, $username, $password, $database, false); // For the drop-down list
    } catch (Throwable $e) {
      echo "Captured Throwable for connection (get Lists) : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }

    ?>
    <form action="<?php echo(basename(__FILE__)); ?>" method="post">
      <input type="hidden" name="operation" value="insert">
      <table>
        <tr>
          <td valign="top">Project:</td>
          <td>
            <!--input type="text" name="tx-id" size="40" required-->
            <?php
      echo('<select name="tx-id">');
      foreach($prjList as $project) {
        echo('<option value="' . $project->id . '">' . $project->id . ', ' . $project->name . '</option>');
      }
      echo ('</select>');
            ?>
          </td>
        </tr>
        <tr>
          <td valign="top">Member:</td>
          <td>
            <!--input type="email" name="owner" size="40" required-->
            <?php
      echo('<select name="owner">');
      foreach($memberList as $member) {
        echo('<option value="' . $member->email . '">' . $member->email . ', ' . $member->name . '</option>');
      }
      echo ('</select>');
            ?>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="text-align: center;"><input type="submit" value="Insert"></td>
        </tr>
      </table>
    </form>
    <?php
  }  else if ($operation == 'insert') {
    $prj_id = $_POST['tx-id'];
    $owner = $_POST['owner'];
    echo "Will insert Project into PROJECT_CONTRIBUTORS: " . $prj_id . ", owner " . $owner . "... <br/>" . PHP_EOL;

    try {
      $link = new mysqli($dbhost, $username, $password, $database);

      if ($link->connect_errno) {
        echo("Oops, errno:" . $link->connect_errno . "...<br/>");
        die("Connection failed: " . $conn->connect_error);
      } else {
        echo("Connected.<br/>");
      }
      $sql = 'INSERT INTO PROJECT_CONTRIBUTORS (PROJECT_ID, DONOR_EMAIL)
              VALUES (\'' . ($prj_id) . '\', \'' . ($owner) . '\')';
      echo('Performing statement <code>' . $sql . '</code><br/>');

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
      ?>
    <form action="#" method="get">
      <input type="submit" value="Query Form">
    </form>

      <?php

    } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }
  }
} else { // Then display the query form
    ?>
    <form action="#" method="post">
      <input type="hidden" name="operation" value="query">
      <table>
        <tr>
          <td valign="top">Project (part of ID or name):</td><td><input type="text" name="prj-id" size="40"></td>
        </tr>
        <tr>
          <td valign="top">Member (part of email, first or last name):</td><td><input type="text" name="member-name" size="40"></td>
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