<html lang="en">
  <!--
   ! A Form to insert into the NL_SUBSCRIBERS table
   ! Good doc at https://www.w3schools.com/php/php_mysql_insert.asp
   !
   ! User Lang, FR or EN
   ! Use it like https://passe-coque.com/admin/sql/dbQuery.01.php?lang=EN
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>NL Subscription</title>
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
    <?php
// Get the lang QS parameter !!
$lang = "FR";
if (isset($_GET['lang'])) {
  $lang = $_GET['lang'];
}

$username = "passecc128";
$password = "zcDmf7e53eTs";
$database = "passecc128";
$dbhost = "passecc128.mysql.db";

$VERBOSE = false;

if (isset($_POST['operation'])) {
  $operation = $_POST['operation'];
  if ($operation == 'insert') { // Then do the insert
    try {
      $lang = $_POST['lang'];
      $name = $_POST['name'];
      $email = $_POST['email'];

      $link = mysqli_init();
    
      if ($VERBOSE) {
        echo("Will connect on ".$database." ...<br/>");
      }
      $link = new mysqli($dbhost, $username, $password, $database);
    
      if ($link->connect_errno) {
        echo("Oops, errno:".$link->connect_errno."...<br/>");
        die("Connection failed: " . $conn->connect_error);
      } else {
        if ($VERBOSE) {
          echo("Connected.<br/>");
        }
      }
    
      $sql = 'INSERT INTO NL_SUBSCRIBERS (NAME, EMAIL, SUBSCRIPTION_DATE) VALUES (\'' . $name . '\', \'' . $email . '\', CURRENT_TIMESTAMP());'; 
      
      if ($VERBOSE) {
        echo('Performing instruction <code>'.$sql.'</code><br/>');
      }
    
      // Returned message may be interpreted by the client.
      // Starts with OK or ERROR.
      if ($link->query($sql) === TRUE) {
        if ($lang == 'FR') {
          echo "OK. Nouveau record dans la base.<br/> Vous recevrez maintenant la news letter.<br/>";
        } else {
          echo "OK. New record created successfully.<br/> You've subscribed successfully.<br/>";
        }
      } else {
        echo "ERROR: " . $sql . "<br/>" . $link->error . "<br/>";
      }

      // On ferme !
      $link->close();
      if ($VERBOSE) {
        echo("Closed DB<br/>".PHP_EOL);
      }
    } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }
    
  }
} else { // Then display the form
  if ($lang == "FR") {
    echo "<h1>Creation d'une souscription a la news-letter.</h1>" . PHP_EOL;
  } else {
    echo "<h1>News Letter Subscriber creation</h1>" . PHP_EOL;
  }
    ?>
    <form action="dbQuery.01.php" method="post">
      <input type="hidden" name="operation" value="insert">
      <input type="hidden" name="lang" value="<?php echo $lang; ?>">
      <table>
        <tr>
          <td valign="top"><?php echo ($lang == "FR") ? "Nom :" : "Name:" ?></td><td><input type="text" name="name" size="40"></td>
        </tr>
        <tr>
          <td valign="top"><?php echo ($lang == "FR") ? "Email :" : "Email:" ?></td><td><input type="text" name="email" size="40"></td>
        </tr>
        <tr>
          <td colspan="2" style="text-align: center;"><input type="submit" value="<?php echo ($lang == "FR") ? "Souscrire" : "Create" ?>"></td>
        </tr>
      </table>
    </form>
    <?php
}  
    ?>        
  </body>        
</html>