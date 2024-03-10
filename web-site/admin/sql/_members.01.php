<html lang="en">
  <!--
   ! WiP.
   ! A Form to query the PASSE_COQUE_MEMBERS table, leads to an update form.
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

if (isset($_POST['operation'])) {
  $operation = $_POST['operation'];
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
          ) AS NAME
      FROM 
          PASSE_COQUE_MEMBERS PCM
      WHERE 
           UPPER(PCM.FIRST_NAME) LIKE UPPER(\'%' . $name . '%\') OR 
           UPPER(PCM.LAST_NAME) LIKE UPPER(\'%' . $name . '%\')
      ORDER BY 1;';
      
      echo('Performing query <code>' . $sql . '</code><br/>');
    
      // $result = mysql_query($sql, $link);
      $result = mysqli_query($link, $sql);
      echo ("Returned " . $result->num_rows . " row(s)<br/>");
    
      echo("<h2>Passe-Coque Members</h2>");

      ?> <!-- Member creation form -->

      <form action="./_members.02.php" method="get">
        <input type="hidden" name="task" value="create">
        <input type="submit" value="Create New Member">
      </form> 

      <?php


      echo "<table>";
      echo "<tr><th>Email</th><th>Name</th><th> - </th></tr>";
      while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
        echo(
          "<tr><td>" . 
            urldecode($table[0]) . "</td><td>" . urldecode($table[1]) . "</td><td>" . "<a href='./_members.02.php?id=" . $table[0] . "' target='PCUpdate'>Edit</a>" .
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
    <form action="#" method="get">
      <!--input type="hidden" name="operation" value="blank"-->
      <table>
        <tr>
          <td colspan="2" style="text-align: center;"><input type="submit" value="Again ?"></td>
        </tr>
      </table>
    </form>
    <?php
  }
} else { // Then display the form
    ?>
    <!--form action="dbQuery.03.php" method="post"-->
    <form action="#" method="post">
      <input type="hidden" name="operation" value="query">
      <table>
        <tr>
          <td valign="top">Name (part of):</td><td><input type="text" name="full-name" size="40"></td>
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