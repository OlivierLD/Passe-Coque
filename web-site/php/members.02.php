<?php
// Must be on top
$timeout = 300;  // In seconds
$applyTimeout = false; // Change at will

try {
  if (!isset($_SESSION)) {
    if ($applyTimeout) {
      ini_set("session.gc_maxlifetime", $timeout);
      ini_set("session.cookie_lifetime", $timeout);
    }
    session_start();
  }
} catch (Throwable $e) {
  echo "Session settings: Captured Throwable: " . $e->getMessage() . "<br/>" . PHP_EOL;
}
?>
<html lang="en">
  <!--
   ! After Custom Authentication.
   ! Crendentials stored in DB.
   ! Member space, once identified.
   ! -> Invoked from members.php
   !
   ! Also:
   ! - Admin privileges
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
    <style type="text/css">
      * {
        line-height: 1em;
      }
    </style>
  </head>
  <body>
    <!--h1>Espace Membres</h1-->
    <?php

require __DIR__ . "/db.cred.php";

// echo "Default GC_MaxLifeTime: " . ini_get("session.gc_maxlifetime") . " s<br/>" . PHP_EOL;
// $timeout = 300;
// ini_set("session.gc_maxlifetime", $timeout);
// ini_set("session.cookie_lifetime", $timeout);
// echo "GC_MaxLifeTime now: " . ini_get("session.gc_maxlifetime") . " s<br/>" . PHP_EOL;

// session_start();

$current_lang = "FR";
if (isset($_GET['lang'])) {
    $current_lang = $_GET['lang'];
    $_SESSION['CURRENT_LANG'] = $current_lang;
} else {
    if (isset($_SESSION['CURRENT_LANG'])) {
        $current_lang = $_SESSION['CURRENT_LANG'];
    }
}

// Authentication required !!
if (!isset($_SESSION['USER_NAME'])) {
  echo ("<button onclick='window.open(\"https://passe-coque.com/php/admin.menu.html\");'>Authenticate</button><br/>" . PHP_EOL);
  die ("You are not connected! Please log in first!");
} else {
  if (false) { // If ADMIN privileges are required...
    if (!isset($_SESSION['ADMIN'])) {
      ?>
      <button onclick="window.open('https://passe-coque.com/php/admin.menu.html');">Authenticate</button><br/>
      <form action="../../php/members.php" method="post"> <!-- members.php -->
          <input type="hidden" name="operation" value="logout">
          <table>
            <tr>
              <td colspan="2" style="text-align: center;"><input type="submit" value="Log out"></td>
            </tr>
          </table>
        </form>
      <?php
      echo("From script " . basename(__FILE__) . "<br/>" . PHP_EOL);
      die ("No ADMIN property found! Please log in first!");
    } else {
      if (!$_SESSION['ADMIN']) {
        ?>
        <button onclick="window.open('https://passe-coque.com/php/admin.menu.html');">Authenticate</button><br/>
        <form action="../../php/members.php" method="post"> <!-- members.php -->
            <input type="hidden" name="operation" value="logout">
            <table>
              <tr>
                <td colspan="2" style="text-align: center;"><input type="submit" value="Log out"></td>
              </tr>
            </table>
          </form>
        <?php
        echo("From script " . basename(__FILE__) . "<br/>" . PHP_EOL);
        die("Sorry, you're NOT an Admin.");
      }
    }
  }
}

$userName = $_SESSION['USER_NAME'];
$displayName = $_SESSION['DISPLAY_NAME'];
$adminPrivileges = $_SESSION['ADMIN'];
$bcMember = $_SESSION['BC_MEMBER'];
$isReferent = $_SESSION['IS_REFERENT'];
$user_id = $_SESSION['USER_ID'];


if ($current_lang == "FR") {
  echo "<h2>Bienvenue [$displayName] dans votre espace priv&eacute;.</h2><br/>" . PHP_EOL;
  // echo "Admin: [$adminPrivileges] .<br/>" . PHP_EOL;
} else {
  echo "<h2>Welcome [$displayName] to your private space.</h2><br/>" . PHP_EOL;
}
// echo "Info: We'll speak $current_lang .<br/>" . PHP_EOL;

    ?>
    <hr/>
    <?php
if ($current_lang == "FR") {
  // echo "<div style='font-size: 3em; line-height: 1em;'>Cette page est en cours de d&eacute;veloppement...</div>" . PHP_EOL;
    ?>
    Vous voulez :
    <ul>
      <li><a href="members.03.php">Changer votre mot de passe (pour <?php echo $user_id ?>)</a></li>
      <li><a href="members.04.php">Voir vos cotisations</a></li>
      <!--li><a href="../admin/sql/_reservations.01.php">Voir le planning des r&eacute;servations</a></li-->
      <li><a href="../admin/web/see.planning.2.html?admin=false">Voir le planning des r&eacute;servations</a></li>
      <li><a href="#" onclick="alert('Plus tard');">. . . </a></li>
    </ul>
    <?php
    if ($bcMember) {
      ?>
      En tant que membre du Boat Club, vous pouvez aussi :
      <ul>
        <li><a href="../admin/sql/_reservations.02.php" target="admin">Faire une r&eacute;servation</a></li>
      </ul>
      <?php
    }
    if ($isReferent) {
      ?>
      En tant que r&eacute;f&eacute;rent d'un bateau, vous pouvez :
      <ul>
        <li><a href="../admin/sql/_reservations.01.php" target="admin">G&eacute;rer les r&eacute;servations</a></li>
      </ul>
      <?php
    }
    if ($adminPrivileges) {
      ?>
      En tant qu'adminstrateur, vous pouvez aussi utiliser ceci :<br/>
      Mais attention, <a href="https://www.youtube.com/watch?v=guuYU74wU70&t=15s" target="YT">With great power comes great responsibility</a>.
      <ul>
        <li><a href="../admin/sql/" target="admin">Admin Menu</a></li>
        <li><a href="../admin/sql/nl.email.sender.php" target="admin">News Letter emailing</a></li>
      </ul>  
      <?php
    }
    ?>
    <?php
} else {
  // echo "<div style='font-size: 3em; line-height: 1em;'>This page is being developped...</div>" . PHP_EOL;
    ?>
    You want to:
    <ul>
      <li><a href="members.03.php">Change your password (for <?php echo $user_id ?>)</a></li>
      <li><a href="members.04.php">See your subscriptions</a></li>
      <li><a href="../admin/sql/_reservations.01.php">See the boat reservation planning</a></li>
      <li><a href="#" onclick="alert('Later');">. . . </a></li>
    </ul>
    <?php
    if ($bcMember) {
      ?>
      As a Boat Club member, you can also:
      <ul>
        <li><a href="../admin/sql/_reservations.02.php" target="admin">Make a reservation</a></li>
      </ul>
      <?php
    }
    if ($isReferent) {
      ?>
      As the referent of a boat, you can also:
      <ul>
        <li><a href="../admin/sql/_reservations.01.php" target="admin">Manage the reservations</a></li>
      </ul>
      <?php
    }
    if ($adminPrivileges) {
      ?>
      As an administrator, you can also use those:<br/>
      But remember, <a href="https://www.youtube.com/watch?v=guuYU74wU70&t=15s" target="YT">With great power comes great responsibility</a>. Be careful.
      <ul>
        <li><a href="../admin/sql/" target="admin">Admin Menu</a></li>
        <li><a href="../admin/sql/nl.email.sender.php" target="admin">News Letter emailing</a></li>
      </ul>  
      <?php
    }
    ?>
    <?php
}
    ?>
    <hr/>
    <form action="members.php" method="post">
      <input type="hidden" name="operation" value="logout">
      <table>
        <tr>
          <td colspan="2" style="text-align: center;"><input type="submit" value="<?php echo ($current_lang == "FR") ? "D&eacute;connexion" : "Log out..." ?>"></td>
        </tr>
      </table>
    </form>
    <hr/>
       
  </body>        
</html>