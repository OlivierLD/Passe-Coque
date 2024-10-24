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
   ! Contains Custom Authentication.
   ! Crendentials stored in DB
   ! PHP Sessions: See https://www.w3schools.com/php/php_sessions.asp
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
      .bodygrad {
        /*background: silver;*/
        background-image: linear-gradient(to right, rgba(0, 0, 0, 0.05), rgba(0, 0, 0, 0.3));
      }
    </style>
  </head>
  <body class="bodygrad">
    <!--h1>Espace Membres</h1-->
    <?php

require __DIR__ . "/db.cred.php";


// $timeout = 300;  // In seconds
// try {
//   ini_set("session.gc_maxlifetime", $timeout);
//   ini_set("session.cookie_lifetime", $timeout);
// } catch (Throwable $e) {
//   echo "Sesssion settings: Captured Throwable: " . $e->getMessage() . "<br/>" . PHP_EOL;
// }


if (false) {
  echo "Session vars, before everything (1):<br/>" . PHP_EOL;
  echo "USER_NAME: " . $_SESSION['USER_NAME'] . "<br/>" . PHP_EOL;
  echo "CURRENT_LANG: " . $_SESSION['CURRENT_LANG'] . "<br/>" . PHP_EOL;
  echo "Full _SESSION Object: <br/>" . PHP_EOL;
  var_dump($_SESSION);
  echo "<br/>--------------------------------<br/>" . PHP_EOL;
}

$operation = null;
if (isset($_POST['operation'])) {
  $operation = $_POST['operation'];
}
if (!($operation == 'logout')) {
  if (isset($_SESSION['USER_NAME']) && 
      isset($_SESSION['DISPLAY_NAME']) &&
      isset($_SESSION['ADMIN']) && 
      isset($_SESSION['USER_ID'])) {

    // Redirect to members.02.php
    if (false) {
      echo "Session vars, before everything (2):<br/>" . PHP_EOL;
      echo "USER_NAME: " . $_SESSION['USER_NAME'] . "<br/>" . PHP_EOL;
      echo "CURRENT_LANG: " . $_SESSION['CURRENT_LANG'] . "<br/>" . PHP_EOL;
      echo "Full _SESSION Object: <br/>" . PHP_EOL;
      var_dump($_SESSION);
      echo "<br/>--------------------------------<br/>" . PHP_EOL;
    }
    if (true) { // Redirect
      // sleep(1);
      header("Location: members.02.php");
      exit();
    } else { // Link for the user to click (will produce the rest of the page...)
      ?>
      <?php
      echo ("From " . __FILE__ . ", line " . __LINE__ . "</br>");
      ?>
      <a href="members.02.php"><?php echo ($current_lang == "FR" || $_SESSION['CURRENT_LANG'] == "FR") ? "Acc&egrave;s au menu..." : "Menu access..." ?></a> <!-- LA SUITE ! -->
      <?php
      // exit();
    }
  }
}
$current_lang = "FR";
if (isset($_GET['lang'])) {
    $current_lang = $_GET['lang'];
    $_SESSION['CURRENT_LANG'] = $current_lang;
} else {
    if (isset($_SESSION['CURRENT_LANG'])) {
        $current_lang = $_SESSION['CURRENT_LANG'];
    }
}
if (false) {
  echo "Info: We'll speak $current_lang .<br/>" . PHP_EOL;
}

if (isset($_POST['operation'])) {
  $operation = $_POST['operation'];
  if ($operation == 'query') { // Then do the query (username, password)
    try {
      // $link = mysqli_init();  // Mandatory ?

      $form_username = $_POST['username'];  // Aka email
      $form_password = $_POST['password'];

      // echo ("looking for credentials for $form_username ...<br/>");
    
      // echo("Will connect on ".$database." ...<br/>");
      $link = new mysqli($dbhost, $username, $password, $database);
    
      if ($link->connect_errno) {
        echo("Oops, DB Connection errno:".$link->connect_errno."...<br/>");
        die("Connection failed: " . $conn->connect_error);
      } else {
        // echo("Connected.<br/>");
      }
    
      // Last fee date ?
      $sql = 'SELECT CONCAT(UPPER(M.LAST_NAME), \' \', M.FIRST_NAME), 
                     M.EMAIL,
                     M.TARIF,
                    (SELECT MAX(F.PERIOD) from MEMBERS_AND_FEES F WHERE M.EMAIL = F.EMAIL GROUP BY F.EMAIL)
              FROM PASSE_COQUE_MEMBERS M
              WHERE M.EMAIL = \'' . $form_username . '\' AND
                    M.TARIF IS NOT NULL
              ORDER BY 1;';
      // echo('Performing query <code>' . $sql . '</code><br/>');
      $result = mysqli_query($link, $sql);
      // echo ("Returned " . $result->num_rows . " row(s)<br/>");
      $now = date_create(date("Y-m-d"));
      $days_since_last_fee = 0;
      while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result. Should be just 1
        // The last date: more than one year ?
        $last_fee = date_create(urldecode($table[3]));
        // Diff
        $diff = date_diff($last_fee, $now);
        $days_since_last_fee = $diff->format("%a");
      }
      // Will return the days_since_last_fee. To be managed later (like if days_since_last_fee > 365)
      $_SESSION['DAYS_SINCE_LAST_FEE'] = $days_since_last_fee;
      
      // Also Is a Referent ?
      $sql = "SELECT PCM.PASSWORD, " . 
             "CONCAT(PCM.FIRST_NAME, ' ', PCM.LAST_NAME), " . 
             "PCM.ADMIN_PRIVILEGES, " .
             "(SELECT IF(COUNT(*) = 0, FALSE, TRUE) FROM BOAT_CLUB_MEMBERS BC WHERE BC.EMAIL = PCM.EMAIL) AS BC, " . 
             "(SELECT IF(COUNT(M.EMAIL) = 0, FALSE, TRUE) FROM PASSE_COQUE_MEMBERS M, THE_FLEET B, REFERENTS R WHERE R.BOAT_ID = B.ID AND B.CATEGORY = 'CLUB' AND R.EMAIL = M.EMAIL AND M.EMAIL = '$form_username') AS REF " .
             "FROM PASSE_COQUE_MEMBERS PCM " . 
             "WHERE PCM.EMAIL = '$form_username';"; 
      
      // echo('Performing query <code>'.$sql.'</code><br/>Pswd Length:' . strlen(trim($form_password)) );
      // echo('Performing query <code>'.$sql.'</code><br/>' . PHP_EOL);
    
      // $result = mysql_query($sql, $link);
      $result = mysqli_query($link, $sql);
      // echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
      if ($result->num_rows == 0 || strlen(trim($form_password)) == 0) {
        if ($result->num_rows == 0) {
          if ($current_lang == "FR") {
            echo "Utilisateur $form_username inconnu.<br/>" . PHP_EOL;
          } else {
            echo "No such user $form_username <br/>" . PHP_EOL;
          }
        }
        if (strlen(trim($form_password)) == 0) {
          if ($current_lang == "FR") {
            echo "Le mot de passe est obligatoire.<br/>" . PHP_EOL;
          } else {
            echo "Password is required.<br/>" . PHP_EOL;
          }
        }
        session_unset();
        session_destroy();
        ?>
        <p>
          <?php
          if ($current_lang == "FR") {
            echo "Vous pouvez demander une connexion &agrave; <a href=\"mailto:contact@passe-coque.com\">contact@passe-coque.com</a>." . PHP_EOL;
          } else {
            echo "You can request a logging by asking <a href=\"mailto:contact@passe-coque.com\">contact@passe-coque.com</a>." . PHP_EOL;
          }
          ?>
        </p>
        <a href="members.php">Log in again?</a><br/>
        <?php    
      } else if ($result->num_rows > 1) {
        echo "More than one entry for $form_username, wierd...<br/>" . PHP_EOL;
      } else {
        if ($current_lang == "FR") {
            echo("<h2>Validation des privil&egrave;ges...</h2>" . PHP_EOL);
        } else {
            echo("<h2>Validating credentials...</h2>" . PHP_EOL);
        }
        while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
          // echo "table contains ". count($table) . " entry(ies).<br/>";
          $db_password = $table[0];
          $display_name = $table[1];
          $admin_privileges = $table[2];
          $bc_member = $table[3];
          $is_referent = $table[4];
        }
        if ($db_password == sha1($form_password)) { // Valid.

          $_SESSION['USER_NAME'] = $form_username;
          // $_SERVER['PHP_AUTH_PW'] = $form_password;
          $_SESSION['DISPLAY_NAME'] = urldecode($display_name);
          $_SESSION['ADMIN'] = $admin_privileges;
          $_SESSION['USER_ID'] = $form_username;  // Same as USER_NAME
          $_SESSION['BC_MEMBER'] = $bc_member;
          $_SESSION['IS_REFERENT'] = $is_referent;
          // Welcome !
          // If arrives here, is a valid user.
          // $mess = ($current_lang == "FR") ? "Bienvenue" : "Welcome";
          if ($current_lang == "FR") {
            echo "<p>Bravo, vous &ecirc;tes maintenant connect&eacute; au syst&egrave;me.</p>" . PHP_EOL;
            echo "Membre Boat-Club : " . ($bc_member ? "Oui" : "Non") . "<br/>" . PHP_EOL;
            echo "Admin : " . ($admin_privileges ? "Oui" : "Non") . "<br/>" . PHP_EOL;
            echo "R&eacute;f&eacute;rent : " . ($is_referent ? "Oui" : "Non") . "<br/>" . PHP_EOL;
          } else {
            echo "<p>Congratulation, you are now into the system.</p>" . PHP_EOL;
            echo "Boat-Club Member: " . ($bc_member ? "Yes" : "No") . "<br/>" . PHP_EOL;
            echo "Admin: " . ($admin_privileges ? "Yes" : "No") . "<br/>" . PHP_EOL;
            echo "Referent: " . ($is_referent ? "Yes" : "No") . "<br/>" . PHP_EOL;
          }
          echo "<p style='line-height: normal;'>" . (($current_lang == "FR") ? "Bienvenue" : "Welcome") . " " . $_SESSION['DISPLAY_NAME'] . "<br/>" . PHP_EOL;    
          echo(($current_lang == "FR") ? "Vous pouvez maintenant acc&eacute;der &agrave; votre " : "You can now access your ");
          ?>
          <a href="members.02.php"><?php echo ($current_lang == "FR") ? "Menu" : "Menu" ?></a> <!-- LA SUITE ! -->
          </p>
          <hr/>
          <form action="<?php echo(basename(__FILE__)); ?>" method="post"> <!-- members.php -->
            <input type="hidden" name="operation" value="logout">
            <table>
              <tr>
                <td colspan="2" style="text-align: center;"><input type="submit" value="<?php echo ($current_lang == "FR") ? "D&eacute;connexion" : "Log out..." ?>"></td>
              </tr>
            </table>
          </form>
          <hr/>
          <?php
          // header('WWW-Authenticate: Basic realm="Passe-Coque Realm"');
        } else {
          // The header show the login alert...
          // header('WWW-Authenticate: Basic realm="Passe-Coque Realm"');
          // header('HTTP/1.0 401 Unauthorized');
          // die ("Not authorized");
          if ($current_lang == "FR") {
            echo "Acc&egrave;s refus&eacute;, mot de passe invalide...<br/>";
          } else {
            echo "Not authorized, invalid password...<br/>";
          }
          unset($_SESSION['USER_NAME']);
          // unset($_SERVER['PHP_AUTH_PW']);

          session_unset();
          session_destroy();

          http_response_code(401);
        }
      }
      // On ferme !
      $link->close();
      // echo("Closed DB<br/>".PHP_EOL);
    } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }
  } else if ($operation == 'logout') {
    $currentUser = "unknown"; 
    if (isset($_SESSION['USER_NAME'])) {
      $currentUser = $_SESSION['USER_NAME'];
    }
    if ($current_lang == "FR") {
      echo "Fin de session pour [$currentUser] <br/>" . PHP_EOL;
      echo "&Agrave; bient&ocirc;t !<br/>" . PHP_EOL;
    } else {
      echo "Logging out of session for user [$currentUser] <br/>" . PHP_EOL;
      echo "Bye! <br/>" . PHP_EOL;
    }
    unset($_SESSION['USER_NAME']);
    // unset($_SERVER['PHP_AUTH_PW']);
    session_unset();
    session_destroy();
    // http_response_code(401);

    // A time out ??
    // sleep(2);

    ?>
    <a href="members.php"><?php echo ($current_lang == "FR") ? "On se reconnecte ?" : "Log in again?" ?></a><br/>
    <?php
  } else {
    echo "Unsupported operation [$operation]";
  }
} else { // No $_POST['operation'], then display the logging form
    ?>
    <?php 
    if ($current_lang == "FR") {
      echo "<h3>Identifiez-vous au pr&eacute;alable</h3>" . PHP_EOL;
    } else {
      echo "<h3>Please identifiy yourself first</h3>" . PHP_EOL;
    }
    ?>
    <form action="<?php echo(basename(__FILE__)); ?>" method="post">
      <input type="hidden" name="operation" value="query">
      <table>
        <tr>
          <td><?php echo ($current_lang == "FR") ? "Identifiant" : "Username" ?></td>
          <td><input type="text" name="username" size="30" placeholder="Email"></td>
        </tr>
        <tr>
          <td><?php echo ($current_lang == "FR") ? "Mot de passe" : "Password" ?></td>
          <td><input type="password" name="password" size="30"></td>
        </tr>
        <tr>
          <?php
          if ($current_lang == "FR") {
            ?>
            <td colspan="2" style="text-align: center;"><input type="submit" value="Connexion..."></td>
            <?php
          } else {
            ?>
            <td colspan="2" style="text-align: center;"><input type="submit" value="Log in..."></td>
            <?php
          }
          ?>
          
        </tr>
      </table>
    </form>
    <?php
}  
    ?>        
  </body>        
</html>