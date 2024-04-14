<html lang="en">
  <!--
   ! WiP.
   ! A Form to Update the THE_FLEET table, leads to an update form.
   ! PK (email) as a GET prm.
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Edit Boats</title>
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
    <h1>PHP / MySQL. Passe-Coque Fleet</h1>

    <?php
// phpinfo();

require __DIR__ . "/../../php/db.cred.php";

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
      $boat_name = $_POST['boat-name']; 
      $id = $_POST['id']; 
      $pix_loc = $_POST['pix-loc']; 
      $boat_type = $_POST['boat-type']; 
      $category = $_POST['category']; 
      $base = $_POST['base']; 

      // $link = mysqli_init();  // Mandatory ?
    
      $link = new mysqli($dbhost, $username, $password, $database);
    
      if ($link->connect_errno) {
        echo("Oops, errno:" . $link->connect_errno . "...<br/>");
        die("Connection failed: " . $conn->connect_error);
      } else {
        echo("Connected.<br/>");
      }
    
      if (isset($_POST['update'])) {  // TODO urlencode ?

        // echo ("Update for $email ... <br/>");

        $sql = 'UPDATE THE_FLEET ' .
               'SET BOAT_NAME = \'' . urlencode($boat_name) . '\', PIX_LOC = \'' . urlencode($pix_loc) . '\', ' .
                    'BOAT_TYPE = \'' . urlencode(trim($boat_type)) . '\', ' . 
                    'CATEGORY = \'' . urlencode($category) . '\', ' . 
                    'BASE = \'' . urlencode(trim($base)) . '\' ' . 
                'WHERE ID = \'' . $id . '\'';
        // echo ("Update Stmt: $sql ; <br/>");
      } else if (isset($_POST['delete'])) {
        $sql = 'DELETE FROM THE_FLEET 
                WHERE ID = \'' . $id . '\'';
      } else if ($operation === 'insert') {
        $sql = 'INSERT INTO THE_FLEET ' .
               'VALUES (\'' . urlencode($boat_name) . '\', \'' . urlencode($id) . '\', \'' . urlencode($pix_loc) . '\', ' .
                       '\'' . urlencode($boat_type) . '\', \'' . urlencode($category) . '\', \'' . urlencode($base) . '\')';
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
    <form action="./_the_fleet.02.php" method="get">
      <!--input type="hidden" name="operation" value="blank"-->
      <input type="hidden" name="id" value="<?php echo($id) ?>">
      <table>
        <tr>
          <td colspan="2" style="text-align: center;"><input type="submit" value="Re-Query?"></td>
        </tr>
      </table>
    </form>
    <?php
  } else {
    echo ("Operation $operation ...");
  }
} else if (isset($_GET['id'])) { // Then display the form to update
  $id = $_GET['id'];
  try {
    $link = new mysqli($dbhost, $username, $password, $database);
    
    if ($link->connect_errno) {
      echo("Oops, errno:".$link->connect_errno."...<br/>");
      die("Connection failed: " . $conn->connect_error);
    } else {
      echo("Connected.<br/>");
    }
  
    $sql = 'SELECT BOAT_NAME, ID, PIX_LOC, BOAT_TYPE, CATEGORY, BASE FROM THE_FLEET WHERE ID = \'' . $id . '\';';
    //             |          |   |        |          |         |
    //             |          |   |        |          |         5
    //             |          |   |        |          4
    //             |          |   |        3
    //             |          |   2
    //             |          1
    //             0

    echo('Performing query <code>' . $sql . '</code><br/>');
        
    // $result = mysql_query($sql, $link);
    $result = mysqli_query($link, $sql);
    echo ("Returned " . $result->num_rows . " row(s)<br/>");
    if ($result->num_rows > 1) {
      echo "Bizarre...";
    }
    echo("<h2>Row for $id </h2>");
    ?>
    <form action="#" method="post">
      <input type="hidden" name="operation" value="update">
    <?php

    // Open the form
    echo("<form action='#' method='post'>" . PHP_EOL);
    echo "<table>" . PHP_EOL;
    while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result

      // echo ("Debug: " . $table[7] . "...");

      echo('<tr><td>Boat Name</td><td><input type="text" name="boat-name" value="' . urldecode($table[0]) . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>ID</td><td><input type="text" name="id" value="' . urldecode($table[1]) . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>Picture Location</td><td><input type="text" name="pix-loc" value="' . urldecode($table[2]) . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>Boat Type</td><td><input type="text" name="boat-type" value="' . urldecode($table[3]) . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>Category</td><td><select name="category">'. 
                                   '<option value="NONE"' . ($table[4]=== 'NONE' ? ' selected' : '') . '>NONE</option>' . 
                                   '<option value="CLUB"' . ($table[4]=== 'CLUB' ? ' selected' : '') . '>CLUB</option>' . 
                                   '<option value="EX_BOAT"' . ($table[4]=== 'EX_BOAT' ? ' selected' : '') . '>EX_BOAT</option>' . 
                                   '<option value="TO_GRAB"' . ($table[4]=== 'TO_GRAB' ? ' selected' : '') . '>TO_GRAB</option>' . 
                                 '</select></td><tr>' . PHP_EOL);
      echo('<tr><td>Base</td><td><input type="text" name="base" value="' . $table[5] . '" size="40"></td><tr>' . PHP_EOL);
    }
    echo "</table>" . PHP_EOL;
    ?>
      <input type="submit" value="Update" name="update"> <input type="submit" value="Delete" name="delete">
    </form>
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
      <tr><td>Boat Name</td><td><input type="text" name="boat-name" size="40"></td><tr>
      <tr><td>ID</td><td><input type="text" name="id" size="40"></td><tr>
      <tr><td>Picture Location</td><td><input type="text" name="pix-loc" size="40"></td><tr>
      <tr><td>Boat Type</td><td><input type="text" name="boat-type" size="40"></td><tr>
      <tr>
        <td>Category</td>
        <td>
          <select name="category">
            <option value="NONE">NONE</option>
            <option value="CLUB">CLUB</option>
            <option value="EX_BOAT">EX_BOAT</option>
            <option value="TO_GRAB">TO_GRAB</option>
          </select>
        </td>
      <tr>
      <tr><td>Base</td><td><input type="text" name="base" size="40"></td><tr>
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