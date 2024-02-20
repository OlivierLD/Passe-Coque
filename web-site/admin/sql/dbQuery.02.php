<html lang="en">
  <!--
   ! A Form to query the NL_SUBSCRIBERS table
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Query NL Subscribers</title>
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
    <h1>PHP / MYSQL. Query News Letter subscribers</h1>

    <?php
// phpinfo();

$username = "passecc128";
$password = "zcDmf7e53eTs";
$database = "passecc128";
$dbhost = "passecc128.mysql.db";

if (isset($_POST['operation'])) {
  $operation = $_POST['operation'];
  if ($operation == 'query') { // Then do the query
    try {
      $email =  $_POST['email'];

      $link = mysqli_init();  // Mandatory ?
    
      echo("Will connect on ".$database." ...<br/>");
      $link = new mysqli($dbhost, $username, $password, $database);
    
      if ($link->connect_errno) {
        echo("Oops, errno:".$link->connect_errno."...<br/>");
        die("Connection failed: " . $conn->connect_error);
      } else {
        echo("Connected.<br/>");
      }
    
      $sql = 'SELECT ID, NAME, EMAIL, SUBSCRIPTION_DATE, ACTIVE FROM NL_SUBSCRIBERS WHERE EMAIL LIKE \'%' . $email . '%\'' . ';'; 
      
      echo('Performing query <code>'.$sql.'</code><br/>');
    
      // $result = mysql_query($sql, $link);
      $result = mysqli_query($link, $sql);
      echo ("Returned " . $result->num_rows . " row(s)<br/>");
    
      echo("<h2>News Letter Subscribers</h2>");
      echo "<table>";
      echo("<tr><th></th><th>ID</th><th>NAME</th><th>EMAIL</th><th>SINCE</th><th>ACTIVE</th></tr>" . PHP_EOL); 
      while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
        // echo "table contains ". count($table) . " entry(ies).<br/>";
        $active = ($table[4]/* === true*/) ? "Yes" : "No";
        $nl_id = $table[0];
        echo ("<tr>");
          echo ("<td><a href='./dbQuery.02.02.php?nl-id=$nl_id'>Edit</a></td>"); //  target='updateNLS'
          echo ("<td>" . $nl_id . "</td><td>" . $table[1] . "</td><td>" . $table[2] . "</td><td>" . $table[3] . "</td><td align='center'>" . $active . "</td>");
          if ($table[4]) {
            echo ("<td><a href='../../php/unsubscribe.php?subscriber-id=$nl_id' target='unsub'>Unsubscribe</a></td>");
          }
        echo ("</tr>" . PHP_EOL); 
      }
      echo "</table>";
      
      // On ferme !
      $link->close();
      echo("Closed DB<br/>".PHP_EOL);
    } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }
  }
} else { // Then display the form
    ?>
    <form action="dbQuery.02.php" method="post">
      <input type="hidden" name="operation" value="query">
      <table>
        <tr>
          <td valign="top">Email (part of):</td><td><input type="email" name="email" size="40"></td>
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