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

// Require the db file...
require __DIR__ . "/../../php/db.cred.php";
// $username = "passecc128";
// $password = "zcDmf7e53eTs";
// $database = "passecc128";
// $dbhost = "passecc128.mysql.db";

$VERBOSE = false;

if (isset($_POST['operation'])) {
  $operation = $_POST['operation'];
  if ($operation == 'insert') { // Then do the insert
    try {
      $lang = $_POST['lang'];
      $firstName = $_POST['first-name'];
      $lastName = $_POST['last-name'];
      $email = trim($_POST['email']);

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
    
      // Update existing record in PASSE_COQUE_MEMBERS, or create a new one.
      $sql = "SELECT EMAIL, FIRST_NAME, LAST_NAME, NEWS_LETTER_OK FROM PASSE_COQUE_MEMBERS WHERE EMAIL = '$email';";
      if ($VERBOSE) {
        echo('Performing instruction <code>'.$sql.'</code><br/>');
      }

      $result = mysqli_query($link, $sql);
      if ($VERBOSE) {
        echo ("Returned " . $result->num_rows . " row(s)<br/>");
      }

      if ($result->num_rows == 0) {  // then create record
        $sql = 'INSERT INTO PASSE_COQUE_MEMBERS (EMAIL, FIRST_NAME, LAST_NAME, NEWS_LETTER_OK, SOME_CONTENT) VALUES (\'' . $email . '\', \'' . urlencode($firstName) . '\',  \'' . urlencode($lastName) . '\', TRUE, \'News Letter Subscriber\');'; 
        if ($VERBOSE) {
          echo('Performing instruction <code>' . $sql . '</code><br/>');
        }
        if ($link->query($sql) === TRUE) {
          if ($lang == 'FR') {
            echo "OK !<br/> Vous &ecirc;tes maintenant abonn&eacute; &agrave; la news letter.<br/>";
          } else {
            echo "OK. New record created successfully.<br/> You've subscribed successfully.<br/>";
          }
        } else {
          echo "ERROR: " . $sql . "<br/>" . $link->error . "<br/>";
        }
      } else {
        $sql = 'UPDATE PASSE_COQUE_MEMBERS SET NEWS_LETTER_OK = TRUE WHERE EMAIL = \'' . $email . '\';'; 
        if ($VERBOSE) {
          echo('Performing instruction <code>' . $sql . '</code><br/>');
        }
        if ($link->query($sql) === TRUE) {
          if ($lang == 'FR') {
            echo "OK !<br/> Vous &ecirc;tes maintenant abonn&eacute; &agrave; la news letter.<br/>";
          } else {
            echo "OK. New record created successfully.<br/> You've subscribed successfully.<br/>";
          }
        } else {
          echo "ERROR: " . $sql . "<br/>" . $link->error . "<br/>";
        }
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
    echo "<h1>Cr&eacute;ation d'une souscription &agrave; la news-letter.</h1>" . PHP_EOL;
  } else {
    echo "<h1>News Letter Subscriber creation</h1>" . PHP_EOL;
  }
    ?>
    <form action="dbQuery.01.php" method="post">
      <input type="hidden" name="operation" value="insert">
      <input type="hidden" name="lang" value="<?php echo $lang; ?>">
      <table>
        <tr>
          <td valign="top"><?php echo ($lang == "FR") ? "Pr&eacute;nom :" : "First Name:" ?></td><td><input type="text" name="first-name" size="40"></td>
        </tr>
        <tr>
          <td valign="top"><?php echo ($lang == "FR") ? "Nom :" : "Last Name:" ?></td><td><input type="text" name="last-name" size="40"></td>
        </tr>
        <tr>
          <td valign="top"><?php echo ($lang == "FR") ? "Email :" : "Email:" ?></td><td><input type="email" name="email" size="40"></td>
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