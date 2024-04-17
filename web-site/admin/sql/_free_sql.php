<html lang="en">
  <!--
   ! Free SQL Statement
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Free SQL</title>
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
    <h1>PHP / MySQL. Free SQL</h1>

    <?php
// phpinfo();

require __DIR__ . "/../../php/db.cred.php";

if (isset($_POST['operation'])) {
  $operation = $_POST['operation'];
  if ($operation == 'execute') { // Then execute the statement
    try {
      $sql = $_POST['free-sql']; 

      echo("Will connect on " . $database . " ...<br/>");
      $link = new mysqli($dbhost, $username, $password, $database);
    
      if ($link->connect_errno) {
        echo("Oops, errno:".$link->connect_errno."...<br/>");
        die("Connection failed: " . $conn->connect_error);
      } else {
        echo("Connected.<br/>");
      }
    
      echo('Executing <code>' . $sql . '</code><br/>');
    
      // $result = mysqli_query($link, $sql);
      $result = $link->query($sql);

      echo("Affected rows: " . $link->affected_rows . "<br/>");
      echo("Errno: " . $link->errno . "<br/>");
      if ($link->errno != 0) {
        echo("error: " . $link->error . "<br/>");
      }

      // echo("-- link --<br/>");
      // var_dump($link);
      // echo("<br/>----------<br/>");

      if ($result) {
        // echo("<pre>" . $result . "</pre>");
        // echo("-- result --<br/>");
        // var_dump($result);
        // echo("<br/>------------<br/>");

        // echo("<br/>");

        if ($result->num_rows) { // Query result
          echo("Returned " . $result->num_rows . " row(s)<br/>");
        
          echo("<h2>Execution returned:</h2>");

          if (true) {
            $nbFields = $result->field_count;
            echo "<table>";
            while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
              echo("<tr>" . PHP_EOL);
              for ($i = 0; $i < $nbFields; $i++) {
                echo("<td>" . $table[$i] . "</td>" . PHP_EOL);
              }  
              echo("</tr>" . PHP_EOL);
            }
            echo "</table>";
          }
        }
      } else {
        echo("Execution failed for <br/>" . $sql . "<br/>");
        // var_dump($result);
        // echo "<br/>";
        // echo "Error:<br/>" . $link->error . "<br/>";
        // var_dump($link);
        // echo "<br/>";
      }
      // On ferme !
      $link->close();
      echo("<hr>Closed DB<br/>" . PHP_EOL);
    } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }
    echo("<hr/>" . PHP_EOL);
    // echo("Again ? Click <a href='#'>Here</a>.");

    ?>
    <form action="" method="get">
      <input type="hidden" name="stmt" value="<?php echo($sql); ?>">
      <table>
        <tr>
          <td colspan="2" style="text-align: center;"><input type="submit" value="Another one ?"></td>
        </tr>
      </table>
    </form>
    <?php
  }
} else { // Then display the form
  if (isset($_GET['stmt'])) {
    $previousStmt = $_GET['stmt'];
  } else {
    $previousStmt = '';
  }
    ?>
    <form action="" method="post">
      <input type="hidden" name="operation" value="execute">
      <textarea id="free-sql" name="free-sql" rows="10" cols="80" placeholder="Your SQL Statement goes here"><?php echo($previousStmt); ?></textarea>
      <br/>
      <input type="submit" value="Execute">
    </form>
    <?php
}  
    ?>        
  </body>        
</html>