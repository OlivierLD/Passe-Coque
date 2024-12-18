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
    <h1>PHP / MySQL. Passe-Coque Members and Project Contribution</h1>

    <?php
// phpinfo();

require __DIR__ . "/../../php/db.cred.php";

function getProjects(string $dbhost, string $username, string $password, string $database, bool $verbose = false): array {
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
      $sql = "SELECT PROJECT_ID, PROJECT_NAME FROM PROJECTS ORDER BY 2;";
      if ($verbose) {
          echo('[Performing instruction ['.$sql.']] ');
      }
      
      $result = mysqli_query($link, $sql);
      if ($verbose) {
          echo ("Returned " . $result->num_rows . " row(s)<br/>");
      }

      $projects = array();
      $projectIndex = 0;
      while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
          $projects[$projectIndex] = array("id" => $table[0], "name" => $table[1]);
          $projectIndex++;
      }
      // On ferme !
      $link->close();
      if ($verbose) {
          echo("[Closed DB] ".PHP_EOL);
      }
      return $projects;

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

// var_dump($_POST);

if (isset($_POST['operation'])) { // delete-donor, create-donor, insert-donor
  $operation = $_POST['operation'];
  if ($operation == 'delete-donor' || $operation == 'create-donor') { 

    if ($operation == 'delete-donor') {
      $email = $_POST['email'];
      $projectId = $_POST['project-id'];

      try {
        $link = new mysqli($dbhost, $username, $password, $database);
      
        if ($link->connect_errno) {
          echo("Oops, errno:" . $link->connect_errno . "...<br/>");
          die("Connection failed: " . $conn->connect_error);
        } else {
          echo("Connected.<br/>");
        }
        // Do the delete and get back
        $sql = "DELETE FROM PROJECT_CONTRIBUTORS WHERE DONOR_EMAIL = '" . $email . "' AND PROJECT_ID = '" . $projectId . "';";
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
        ?>
        <button onclick="history.back()">Get Back</button>
        <?php
      } catch (Throwable $e) {
        echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
      }
    } else if ($operation == 'create-donor') {
      $email = $_POST['email'];
      // Display the form, with project list. And submit with 'insert-donor'.
      ?>
      <h3>Create contribution for <?php echo $email; ?></h3>
      <form action="<?php echo(basename(__FILE__)); ?>" method="post" id="project-form">
        <input type="hidden" name="operation" value="insert-donor">
        <input type="hidden" name="email" value="<?php echo $email; ?>">

        <!-- The project list here -->
        <?php
        try {
          $projectList = getProjects($dbhost, $username, $password, $database); // For the drop-down list
          // foreach($projectList as $project) {
          //   echo('Project: ' . $project . PHP_EOL);  
          // }
        } catch (Throwable $e) {
          echo "Captured Throwable for connection (getProjects) : " . $e->getMessage() . "<br/>" . PHP_EOL;
        }


        echo('For project <select name="project-id" form="project-form">');
        foreach($projectList as $project) {
          echo('<option value="' . $project["id"] . '">' . $project["name"] . '</option>');
        }
        echo ('</select>');
        ?>
        <br/>
        <input type="submit" value="Create Contribution">
      </form>
      <?php

    }
  } else if ($operation == 'insert-donor') {
    // Need project-id, email

    try {
      $email = $_POST['email']; 
      $projectID = $_POST['project-id'];

      $link = new mysqli($dbhost, $username, $password, $database);
    
      if ($link->connect_errno) {
        echo("Oops, errno:" . $link->connect_errno . "...<br/>");
        die("Connection failed: " . $conn->connect_error);
      } else {
        echo("Connected.<br/>");
      }
      // echo ("Update for $email ... <br/>");

      $sql = "INSERT INTO PROJECT_CONTRIBUTORS (DONOR_EMAIL, PROJECT_ID) VALUES ('" . $email . "', '" . $projectID . "' );";
      // echo ("Update Stmt: $sql ; <br/>");

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
    echo ("Operation [$operation] ...");
  }
} else {
  // WHAT ??
  echo "What ??";
}
    ?>        
  </body>        
</html>