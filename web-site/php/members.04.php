<html lang="en">
  <!--
   ! After Custom Authentication.
   ! Crendentials stored in DB.
   ! Member space, once identified.
   !
   !  SEE YOUR FEES
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

if ($current_lang == "FR") {
  echo "<h2>Bienvenue [$displayName].</h2><br/>" . PHP_EOL;
  // echo "Admin: [$adminPrivileges] .<br/>" . PHP_EOL;
} else {
  echo "<h2>Welcome [$displayName].</h2><br/>" . PHP_EOL;
}

$sql = "SELECT YEAR FROM MEMBERS_AND_FEES WHERE EMAIL = '$user_id' ORDER BY 1;";
// echo('Performing statement <code>' . $sql . '</code><br/>');
  
$link = new mysqli($dbhost, $username, $password, $database);
  
if ($link->connect_errno) {
  echo("Oops, errno:" . $link->connect_errno . "...<br/>");
  die("Connection failed: " . $conn->connect_error);
} else {
  // echo("Connected.<br/>");
}

$result = mysqli_query($link, $sql);
echo (($current_lang == "FR" ? 
       "Trouv&eacute; " . $result->num_rows . " ann&eacute;e(s)<br/>"  :
       "Returned " . $result->num_rows . " year(s)<br/>") . PHP_EOL);

if ($result->num_rows > 0) {
  $mess = $current_lang == "FR" ? "Cotisations pour " : "Subscriptions for ";
  $nb = 0;
  while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
    // echo "table contains ". count($table) . " entry(ies).<br/>";
    $year = $table[0];
    $mess .= (($nb > 0 ? ", " : "") . $year);
    $nb += 1;
  }
  echo ("$mess. <br/>");
} else {
  echo $current_lang == "FR" ? "Pas trouv&eacute; de cotisation.<br/>" : "No cotisation was found.<br/>" . PHP_EOL;
}

// On ferme !
$link->close();
    ?>
    <hr/>
       
  </body>        
</html>