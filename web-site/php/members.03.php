<html lang="en">
  <!--
   ! After Custom Authentication.
   ! Crendentials stored in DB.
   ! Member space, once identified.
   ! -> Invoked from members.02.php
   !
   !  CHANGE USER PASSWORD
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Custom Authentication</title>
    <!-- TO be used from an iFrame, css is required -->
    <link rel="stylesheet" href="../fonts/font.01.css">
    <link rel="stylesheet" href="../fonts/font.02.css">
    <link rel="stylesheet" href="../passe-coque.css" type="text/css"/>
  </head>
  <body>
    <!--h1>Espace Membres</h1-->
    <?php
// echo "Default GC_MaxLifeTime: " . ini_get("session.gc_maxlifetime") . " s<br/>" . PHP_EOL;
$timeout = 60;
ini_set("session.gc_maxlifetime", $timeout);
ini_set("session.cookie_lifetime", $timeout);
// echo "GC_MaxLifeTime now: " . ini_get("session.gc_maxlifetime") . " s<br/>" . PHP_EOL;

session_start();

$username = "passecc128";
$password = "zcDmf7e53eTs";
$database = "passecc128";
$dbhost = "passecc128.mysql.db";

$current_lang = "FR";
if (isset($_GET['lang'])) {
    $current_lang = $_GET['lang'];
    $_SESSION['CURRENT_LANG'] = $current_lang;
} else {
    if (isset($_SESSION['CURRENT_LANG'])) {
        $current_lang = $_SESSION['CURRENT_LANG'];
    }
}
$userName = $_SESSION['USER_NAME'];
$displayName = $_SESSION['DISPLAY_NAME'];
$adminPrivileges = $_SESSION['ADMIN'];
$user_id = $_SESSION['USER_ID'];  // Email

if (isset($_POST['operation']) && $_POST['operation'] === 'update-pswd') {
  // Then do the update with the new password
  $new_password = $_POST['new-password'];
  $sql = "UPDATE PASSE_COQUE_MEMBERS SET PASSWORD = '" . sha1($new_password) . "' WHERE EMAIL = '$user_id' ;";

  // echo('Performing statement <code>' . $sql . '</code><br/>');
    
  $link = new mysqli($dbhost, $username, $password, $database);
    
  if ($link->connect_errno) {
    echo("Oops, errno:" . $link->connect_errno . "...<br/>");
    die("Connection failed: " . $conn->connect_error);
  } else {
    // echo("Connected.<br/>");
  }

  if (true) { // Do perform ?
    if ($link->query($sql) === TRUE) {
      if ($current_lang === 'FR') {
        echo "OK, C'est fait !<br/>" . PHP_EOL;
      } else {
        echo "OK. Operation performed successfully<br/>" . PHP_EOL;
      }
    } else {
      echo "ERROR executing: " . $sql . "<br/>" . $link->error . "<br/>";
    }
  } else {
    echo "Stby<br/>" . PHP_EOL;
  }
  ?>
  <!-- Back button -->
  <!--button onclick="history.back()">Retour</button-->
  <?php
  // On ferme !
  $link->close();

} else {

if ($current_lang == "FR") {
  echo "<h2>Bienvenue [$displayName].</h2><br/>" . PHP_EOL;
  // echo "Admin: [$adminPrivileges] .<br/>" . PHP_EOL;
} else {
  echo "<h2>Welcome [$displayName].</h2><br/>" . PHP_EOL;
}
// echo "Info: We'll speak $current_lang .<br/>" . PHP_EOL;

    ?>
    <hr/>
    <?php
  if ($current_lang == "FR") {
      ?>
      <h3>Changement de mot de passe</h3>
      <form action="" method="post">
        <input type="hidden" name="operation" value="update-pswd">
        <table>
          <tr>
            <td>Nouveau Mot de Passe</td>
            <td><input type="password" name="new-password"></td>
          </tr>
          <tr><td colspan="2"><input type="submit" value="Update"></td></tr>
        </table>
      </form>
      <?php
  } else {
      ?>
      <h3>Password update</h3>
      <form action="" method="post">
        <input type="hidden" name="operation" value="update-pswd">
        <table>
          <tr>
            <td>New Password</td>
            <td><input type="password" name="new-password"></td>
          </tr>
          <tr><td colspan="2"><input type="submit" value="Update"></td></tr>
        </table>
      </form>
      <?php
  }
}
    ?>
    <hr/>
       
  </body>        
</html>