<html lang="en">
  <!--
   ! WiP.
   ! A Form to Update the PASSE_COQUE_MEMBERS table, leads to an update form.
   ! PK (email) as a GET prm.
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Edit Members</title>
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
    <h1>PHP / MySQL. Passe-Coque Members</h1>

    <?php
// phpinfo();

$username = "passecc128";
$password = "zcDmf7e53eTs";
$database = "passecc128";
$dbhost = "passecc128.mysql.db";

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
      $nl_ok = $_POST['nl-ok']; 
      $user_password = $_POST['user-password'];
      $update_psw = $_POST['upd-psswd']; 
      $admin = $_POST['admin-priv']; 
      $user_comments = (isset($_POST['user-comments']) ? $_POST['user-comments'] : '');

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
    
      if (isset($_POST['update'])) {  // TODO urlencode ?

        // echo ("Update for $email ... <br/>");

        $sql = 'UPDATE PASSE_COQUE_MEMBERS ' .
               'SET LAST_NAME = \'' . urlencode($last_name) . '\', FIRST_NAME = \'' . urlencode($first_name) . '\', ' .
                    'TARIF = ' . (strlen(trim($tarif)) > 0 ? ('\'' . urlencode(trim($tarif)) . '\'') : 'NULL') . ', ' . 
                    'AMOUNT = ' . (strlen(trim($amount)) > 0 ? trim($amount) : 'NULL') . ', ' .
                    'TELEPHONE = ' . (strlen(trim($telephone)) > 0 ? ('\'' . trim($telephone) . '\'') : 'NULL') . ',  ' .
                    'FIRST_ENROLLED = ' . (strlen(trim($first_enrolled)) > 0 ? ('\'' . trim($first_enrolled) . '\'') : 'NULL') . ',  ' .
                    'NEWS_LETTER_OK = ' . ($nl_ok === '1' ? 'TRUE' : 'FALSE') . ', ' .
                    ($update_psw === '1' ? 'PASSWORD = \'' . sha1($user_password) . '\', ' : '') .
                    'ADMIN_PRIVILEGES = ' . ($admin === '1' ? 'TRUE' : 'FALSE') . ', ' .
                    'SOME_CONTENT = ' . (strlen(trim($user_comments)) > 0 ? ('\'' . (str_replace("'","\'", trim($user_comments))) . '\'') : 'NULL') . ' ' .
                'WHERE EMAIL = \'' . $email . '\'';
        // echo ("Update Stmt: $sql ; <br/>");
      } else if (isset($_POST['delete'])) {
        $sql = 'DELETE FROM PASSE_COQUE_MEMBERS 
                WHERE EMAIL = \'' . $email . '\'';
      } else if ($operation === 'insert') {
        $sql = 'INSERT INTO PASSE_COQUE_MEMBERS (EMAIL, LAST_NAME, FIRST_NAME, TARIF, AMOUNT, TELEPHONE, FIRST_ENROLLED, NEWS_LETTER_OK, PASSWORD, ADMIN_PRIVILEGES, SOME_CONTENT)
                VALUES (\'' . ($email) . '\', \'' . urlencode($last_name) . '\', \'' . urlencode($first_name) . '\', 
                    ' . (strlen(trim($tarif)) > 0 ? ('\'' . urlencode(trim($tarif)) . '\'') : 'NULL') . ', 
                    ' . (strlen(trim($amount)) > 0 ? trim($amount) : 'NULL') . ', 
                    ' . (strlen(trim($telephone)) > 0 ? ('\'' . trim($telephone) . '\'') : 'NULL') . ', 
                    ' . (strlen(trim($first_enrolled)) > 0 ? ('\'' . trim($first_enrolled) . '\'') : 'NULL') . ', 
                    ' . ($nl_ok === '1' ? 'TRUE' : 'FALSE') . ', 
                    ' . (strlen(trim($user_password)) > 0 ? ('\'' . sha1(trim($user_password)) . '\'') : 'NULL') . ', 
                    ' . ($admin === '1' ? 'TRUE' : 'FALSE') . ', 
                    ' . (strlen(trim($user_comments)) > 0 ? ('\'' . trim($user_comments) . '\'') : 'NULL') . 
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
    <form action="#" method="get">
      <!--input type="hidden" name="operation" value="blank"-->
      <input type="hidden" name="id" value="<?php echo($email) ?>">
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
        PCM.SOME_CONTENT
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
    <?php

    // Open the form
    echo("<form action='#' method='post'>" . PHP_EOL);
    echo "<table>" . PHP_EOL;
    while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result

      // echo ("Debug: " . $table[7] . "...");

      echo('<tr><td>Email</td><td><input type="email" name="email" value="' . urldecode($table[0]) . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>Last Name</td><td><input type="text" name="last-name" value="' . urldecode($table[1]) . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>First Name</td><td><input type="text" name="first-name" value="' . urldecode($table[2]) . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>Tarif</td><td><input type="text" name="tarif" value="' . urldecode($table[3]) . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>Amount</td><td><input type="number" name="amount" value="' . $table[4] . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>Telephone</td><td><input type="text" name="telephone" value="' . $table[5] . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>First Enrolled</td><td><input type="text" name="first-enrolled" value="' . urldecode($table[6]) . '" size="40"></td><tr>' . PHP_EOL);
      echo('<tr><td>News Letter ?</td><td><input type="checkbox" name="nl-ok" value="1"' . ($table[7] ? " checked" : "")  . '></td><tr>' . PHP_EOL); 
      echo('<tr><td>Password</td><td><input type="password" name="user-password" value="' . urldecode($table[8]) . '" size="40"></td><td><input type="checkbox" name="upd-psswd" value="1"> Update password</td><tr>' . PHP_EOL);
      echo('<tr><td>Admin privileges</td><td><input type="checkbox" name="admin-priv" value="1"' . ($table[9] ? " checked" : "")  . '></td><tr>' . PHP_EOL); 
      echo('<tr><td style="vertical-align: top;">Comments</td><td><textarea rows="4" cols="40" name="user-comments">' . $table[10] . '</textarea></td><tr>' . PHP_EOL);
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
      <tr><td>Email</td><td><input type="email" name="email" size="40"></td><tr>
      <tr><td>Last Name</td><td><input type="text" name="last-name" size="40"></td><tr>
      <tr><td>First Name</td><td><input type="text" name="first-name" size="40"></td><tr>
      <tr><td>Tarif</td><td><input type="text" name="tarif" size="40"></td><tr>
      <tr><td>Amount</td><td><input type="number" name="amount" size="40"></td><tr>
      <tr><td>Telephone</td><td><input type="text" name="telephone" size="40"></td><tr>
      <tr><td>First Enrolled</td><td><input type="text" name="first-enrolled" size="40"></td><tr>
      <tr><td>News Letter ?</td><td><input type="checkbox" name="nl-ok" value="1" checked></td><tr>
      <tr><td>Password</td><td><input type="password" name="user-password" size="40"></td><tr>
      <tr><td>Admin privileges</td><td><input type="checkbox" name="admin-priv" value="1"></td><tr>
      <tr><td style="vertical-align: top;">Comments</td><td><textarea rows="4" cols="40" name="user-comments"></textarea></td><tr>

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