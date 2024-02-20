<html lang="en">
  <!--
   ! Edit a NL_SUBSCRIBERS record
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>NL Subscribers</title>
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
    <h1>PHP / MYSQL. Update News Letter subscribers</h1>

    <?php

$username = "passecc128";
$password = "zcDmf7e53eTs";
$database = "passecc128";
$dbhost = "passecc128.mysql.db";

if (!isset($_GET['nl-id'])) {
  $operation = $_POST['operation'];
  if ($operation == 'update') { // Then do the update
    try {
      $id =  $_POST['nl-id'];
      $name =  $_POST['name'];
      $email =  $_POST['email'];
      $active =  $_POST['active'];
      if ($active === '1') {
        $active = 'TRUE';
      } else {
        $active = 'FALSE';
      }

      // echo("Will connect on ".$database." ...<br/>");
      $link = new mysqli($dbhost, $username, $password, $database);
    
      if ($link->connect_errno) {
        echo("Oops, errno:".$link->connect_errno."...<br/>");
        die("Connection failed: " . $conn->connect_error);
      } else {
        // echo("Connected.<br/>");
      }

      if (true) { // Do the update
        $sql = "UPDATE NL_SUBSCRIBERS SET NAME = '$name', EMAIL = '$email', ACTIVE = $active WHERE ID = $id;"; 
        
        // echo('Performing update <code>'.$sql.'</code><br/>');
      
        if (true) { // Do perform
          if ($link->query($sql) === TRUE) {
            echo "OK. Record updated successfully<br/>" . PHP_EOL;
          } else {
            echo "ERROR: " . $sql . "<br/>" . $link->error . "<br/>";
          }
        }
        ?>
        <a href="dbQuery.02.php">Back to Query</a>
        <?php
      }
      // On ferme !
      $link->close();
      // echo("Closed DB<br/>".PHP_EOL);
    } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }
  } else if ($operation == 'delete') {
    try {
      $id =  $_POST['nl-id'];
      // echo("Will connect on ".$database." ...<br/>");
      $link = new mysqli($dbhost, $username, $password, $database);
    
      if ($link->connect_errno) {
        echo("Oops, errno:".$link->connect_errno."...<br/>");
        die("Connection failed: " . $conn->connect_error);
      } else {
        // echo("Connected.<br/>");
      }

      if (true) { // Do the delete
        $sql = "DELETE FROM NL_SUBSCRIBERS WHERE ID = $id;"; 
        // echo('Performing update <code>'.$sql.'</code><br/>');
        if (true) { // Do perform
          if ($link->query($sql) === TRUE) {
            echo "OK. Record deleted successfully<br/>" . PHP_EOL;
          } else {
            echo "ERROR: " . $sql . "<br/>" . $link->error . "<br/>";
          }
        }
        ?>
        <a href="dbQuery.02.php">Back to Query</a>
        <?php
      }
      // On ferme !
      $link->close();
      // echo("Closed DB<br/>".PHP_EOL);
    } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }    
  } else {
    echo "Unknown situation...<br/>" . PHP_EOL;
  }
} else { // Then query, and display the form
  $nl_id = $_GET['nl-id'];
  try {
    $link = new mysqli($dbhost, $username, $password, $database);
  
    if ($link->connect_errno) {
      echo("Oops, errno:".$link->connect_errno."...<br/>");
      die("Connection failed: " . $conn->connect_error);
    } else {
      // echo("Connected.<br/>");
    }
    $sql = 'SELECT ID, NAME, EMAIL, SUBSCRIPTION_DATE, ACTIVE FROM NL_SUBSCRIBERS WHERE ID = ' . $nl_id . ';'; 
    
    // echo('Performing query <code>'.$sql.'</code><br/>');
  
    // $result = mysql_query($sql, $link);
    $result = mysqli_query($link, $sql);
    // echo ("Returned " . $result->num_rows . " row(s)<br/>");
    if ($result->num_rows !== 1) {
      echo "Bad number of rows: $result->num_rows <br/>". PHP_EOL;
    } else {
      $name = '';
      $email = '';
      $active = null;
    }
    while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
      // echo "table contains ". count($table) . " entry(ies).<br/>";
      $name = $table[1];
      $email = $table[2];
      $active = $table[4];
    }      
    // Display form here
    ?>
    <!-- Update form -->
    <form action="dbQuery.02.02.php" method="post">
      <input type="hidden" name="operation" value="update">
      <input type="hidden" name="nl-id" value="<?php echo $nl_id ?>">
      <table>
        <tr>
          <td valign="top">Name:</td><td><input type="text" name="name" size="40" value="<?php echo $name ?>"></td>
        </tr>
        <tr>
          <td valign="top">Email:</td><td><input type="email" name="email" size="40" value="<?php echo $email ?>"></td>
        </tr>
        <tr>
          <td valign="top">Active:</td><td><input type="checkbox" name="active" value="1" <?php echo $active ? 'checked' : '' ?>></td>
        </tr>
        <tr>
          <td colspan="2" style="text-align: center;"><input type="submit" value="Update"></td>
        </tr>
      </table>
    </form>
    <!-- Delete form -->
    <form action="dbQuery.02.02.php" method="post">
      <input type="hidden" name="operation" value="delete">
      <input type="hidden" name="nl-id" value="<?php echo $nl_id ?>">
      <input type="submit" value="Delete">
    </form>
    <a href="dbQuery.02.php">Back to Query</a>
    <?php
    // On ferme !
    $link->close();
    // echo("Closed DB<br/>".PHP_EOL);
  } catch (Throwable $e) {
    echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
  }
}  
    ?>        
  </body>        
</html>