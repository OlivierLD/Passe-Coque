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
   ! Member Space menu
   !
   ! After Custom Authentication.
   ! Crendentials stored in DB.
   ! Member space, once identified.
   !
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
      .gears-bg {
        position: relative;
        background-image: url("/images/gears.png");
        background-size: cover;
        background-repeat: repeat;
        background-attachment: fixed; /* <= This one */
        /* opacity: 0.25; */
      }
      .transp-box {
        margin: 0px;
        padding: 10px;
        background-color: #ffffff;
        border: 1px solid black;
        opacity: 0.75;
      }
      .tablinks {
        font-weight: bold;
      }
    </style>
  	<script type="text/javascript" src="../passe-coque.js"></script>
  </head>
  <body class="gears-bg">
    <!--h1>Espace Membres</h1-->
    <div class="transp-box">
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
$isPrjOwner = $_SESSION['IS_PRJ_OWNER'];
//
$days_since_last_fee = $_SESSION['DAYS_SINCE_LAST_FEE'];

// --- Start of the menu content ---

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

    <!-- 
      Menu sections depend on:
      - default (PC member)
      - $bcMember
      - $isReferent
      - $isPrjOwner
      - $adminPrivileges
     +-->
     <div class="tab" style="margin-top: 30px; margin-bottom: 10px; display: grid; grid-template-columns: auto auto auto auto auto auto auto auto auto auto auto;"> <!-- TODO Improve the grid-template... -->
        <button class="tablinks tab-active" onclick="openTab(event, 'member-space-01');" title="Passe-Coque">Espace Membre</button>
        <button class="tablinks" onclick="openTab(event, 'member-space-02');">Boat-Club</button>
        <button class="tablinks" onclick="openTab(event, 'member-space-03');">R&eacute;f&eacute;rent</button>
        <button class="tablinks" onclick="openTab(event, 'member-space-04');">Responsable de Projet</button>
        <button class="tablinks" onclick="openTab(event, 'member-space-05');">Administrateur</button>
    </div>


    <div id="member-space-01" class="tab-section" style="display: block;">
        Vous voulez :
        <ul>
          <li><a href="members.03.php">Changer votre mot de passe (pour <?php echo $user_id ?>)</a></li>
          <li><a href="members.04.php">Voir vos cotisations</a></li>
          <!--li><a href="../admin/sql/_reservations.01.php">Voir le planning des r&eacute;servations</a></li-->
          <li><a href="../admin/web/see.planning.2.html?admin=false">Voir le planning des r&eacute;servations</a></li>
          <li>
            Acc&eacute;der &agrave; des outils de Navigation
            <ul>
              <li>👉 Acc&eacute;der &agrave; la <a href="../tech.and.nav/almanacs.php?lang=FR" target="pub">publication d'almanachs</a> (de mar&eacute;e, et &eacute;ph&eacute;m&eacute;rides astronomiques)</li>
              <li>👉 Consulter la <a href="../tech.and.nav/meteo.php?lang=FR" target="pub">m&eacute;t&eacute;o</a> (Atlantique Nord, GRIBs et Faxes)</li>
            </ul>
          <li><a href="#" onclick="alert('Plus tard');">. . . </a></li>
        </ul>
        <?php
        // Cotisation a jour...
        if ($days_since_last_fee > 365) { // Oops
          echo "<div style='border: 1px solid silver; border-radius: 5px; padding: 10px; margin: 10px;'>" .PHP_EOL;
          echo "<b>Votre derni&egrave;re cotisation date de <span style='color: red;'>$days_since_last_fee jours</span>...<br/>" . PHP_EOL;
          echo "<i>Certains documents vous sont inaccessibles</i>.</b><br/>" . PHP_EOL;;
          echo "</div>" . PHP_EOL;
        } else { // It's OK.
        ?>
          Votre cotisation est &agrave; jour.<br/>
          <iframe src="./member.docs.html" frameBorder="0" style="width: 98%; height: 150px; border: 1px solid silver; border-radius: 5px; overflow: scroll;">
            <!-- Members only -->
          </iframe>
          <br/>
        <?php
        }
        ?>
    </div>

    <div id="member-space-02" class="tab-section" style="display: none;">
    <?php
    if ($bcMember) {
      ?>
      En tant que <i>membre du Boat Club</i>, vous pouvez aussi :
      <ul>
        <li><a href="../admin/sql/_reservations.02.php" target="admin">Faire une r&eacute;servation</a></li>
      </ul>
      <?php
    } else {
      ?>
        Vous n'&ecirc;tes pas membre du Boat-Club...
      <?php
    }
    ?>
    </div>
    <div id="member-space-03" class="tab-section" style="display: none;">
    <?php
    if ($isReferent) {
      ?>
      En tant que <i>r&eacute;f&eacute;rent d'un bateau</i>, vous pouvez :
      <ul>
        <li><a href="../admin/sql/_reservations.01.php" target="admin">G&eacute;rer les r&eacute;servations</a></li>
        <li><a href="../admin/sql/_help_requests.01.php?option=request" target="admin">Demander de l'aide (convoyages, bricolages, ...)</a></li>
        <li><a href="../admin/sql/_todo_list.01.php?option=no-empty" target="admin">G&eacute;rer les TODO lists</a></li>
        <li><a href="../misc-tech-docs/make.a.blog.html" target="tech-doc">Apprendre comment vous faire un blog</a></li>
      </ul>
      <?php
    } else {
      ?>
        Vous n'&ecirc;tes pas r&eacute;f&eacute;rent d'un bateau...
      <?php
    }
    ?>
    </div>
    <div id="member-space-04" class="tab-section" style="display: none;">
    <?php
    if ($isPrjOwner) {
      ?>
      En tant que <i>responsable d'un projet</i>, vous pouvez :
      <ul>
        <li><a href="../misc-tech-docs/make.a.blog.html" target="tech-doc">Apprendre comment vous faire un blog</a></li>
      </ul>
      <?php
    } else {
      ?>
        Vous n'&ecirc;tes pas responsable d'un projet...
      <?php
    }
    ?>
    </div>
    <div id="member-space-05" class="tab-section" style="display: none;">
    <?php
    if ($adminPrivileges) {
      ?>
      En tant qu'<i>adminstrateur</i>, vous pouvez aussi utiliser ceci :<br/>
      Mais attention, <a href="https://www.youtube.com/watch?v=guuYU74wU70&t=15s" target="YT">With great power comes great responsibility</a>.
      <ul>
        <li><a href="../admin/sql/" target="admin">Admin Menu</a></li>
        <li><a href="../admin/sql/nl.email.sender.php" target="admin">News Letter emailing</a></li>
        <li><a href="../admin/sql/_todo_list.01.php" target="admin">G&eacute;rer les TODO lists (toutes)</a></li>
      </ul>  
      <?php
    } else {
      ?>
        Vous n'avez pas les privil&egrave;ges Admin...
      <?php
    }
    ?>
    </div>
    <?php
} else {
  // echo "<div style='font-size: 3em; line-height: 1em;'>This page is being developped...</div>" . PHP_EOL;
    ?>
    <!-- 
      Menu sections depend on:
      - default (PC member)
      - $bcMember
      - $isReferent
      - $isPrjOwner
      - $adminPrivileges
     +-->
     <div class="tab" style="margin-top: 30px; margin-bottom: 10px; display: grid; grid-template-columns: auto auto auto auto auto auto auto auto auto auto auto;"> <!-- TODO Improve the grid-template... -->
        <button class="tablinks tab-active" onclick="openTab(event, 'member-space-01');" title="Passe-Coque">Member Space</button>
        <button class="tablinks" onclick="openTab(event, 'member-space-02');">Boat-Club</button>
        <button class="tablinks" onclick="openTab(event, 'member-space-03');">Referent</button>
        <button class="tablinks" onclick="openTab(event, 'member-space-04');">Projet Leader</button>
        <button class="tablinks" onclick="openTab(event, 'member-space-05');">Admin</button>
    </div>

    <div id="member-space-01" class="tab-section" style="display: block;">
        You want to:
        <ul>
          <li><a href="members.03.php">Change your password (for <?php echo $user_id ?>)</a></li>
          <li><a href="members.04.php">See your subscriptions</a></li>
          <li><a href="../admin/sql/_reservations.01.php">See the boat reservation planning</a></li>
          <li>
            Access Navigation tools
            <ul>
                <li>👉 Access the <a href="../tech.and.nav/almanacs.php?lang=EN" target="pub">Almanacs Publication</a> page (tides and celestial almanacs)</li>
                <li>👉 Check out the <a href="../tech.and.nav/meteo.php?lang=EN" target="pub">Weather Forecast</a> (North Atlantic, GRIBs and Faxes)</li>
            </ul>
          </li>
          <li><a href="#" onclick="alert('Later');">. . . </a></li>
        </ul>
      <?php
        // Cotisation a jour...
        if ($days_since_last_fee > 365) { // Oops
          echo "<div style='border: 1px solid silver; border-radius: 5px; padding: 10px; margin: 10px;'>" . PHP_EOL;
          echo "<b>Your last membership fee is <span style='color: red;'>$days_since_last_fee old</span>...<br/>" . PHP_EOL;
          echo "<i>You will not have access to some documents</i>.</b><br/>" . PHP_EOL;
          echo "</div>" . PHP_EOL;
        } else { // It's OK.
          ?>
          Your membership fee is up-to-date.<br/>
          <iframe src="./member.docs.html" frameBorder="0" style="width: 98%; height: 150px; border: 1px solid silver; border-radius: 5px; overflow: scroll;">
            <!-- Members only -->
          </iframe>
          <br/>
          <?php
        }
      ?>
    </div>
    <div id="member-space-02" class="tab-section" style="display: none;">
    <?php
    if ($bcMember) {
      ?>
      As a <i>Boat Club member</i>, you can also:
      <ul>
        <li><a href="../admin/sql/_reservations.02.php" target="admin">Make a reservation</a></li>
      </ul>
      <?php
    } else {
      ?>
      You're not a member of the Boat-Club...
      <?php
    }
    ?>
    </div>
    <div id="member-space-03" class="tab-section" style="display: none;">
    <?php
    if ($isReferent) {
      ?>
      As the <i>referent of a boat</i>, you can also:
      <ul>
        <li><a href="../admin/sql/_reservations.01.php" target="admin">Manage the reservations</a></li>
        <li><a href="../admin/sql/_help_requests.01.php?option=request" target="admin">Ask for help (deliveries, workshop, ...)</a></li>
        <li><a href="../misc-tech-docs/make.a.blog.html" target="tech-doc">Apprendre comment vous faire un blog</a></li>
      </ul>
      <?php
    } else {
      ?>
        You're not the referent of a boat...
      <?php
    }
    ?>
    </div>
    <div id="member-space-04" class="tab-section" style="display: none;">
    <?php
    if ($isPrjOwner) {
      ?>
      As a <i>Project Leader</i>, you can also:
      <ul>
        <li><a href="../misc-tech-docs/make.a.blog.html" target="tech-doc">Learn how to make a blog</a></li>
      </ul>
      <?php
    } else {
      ?>
        You're not leading any project (yet)...
      <?php
    }
    ?>
    </div>
    <div id="member-space-05" class="tab-section" style="display: none;">
    <?php
    if ($adminPrivileges) {
      ?>
      As an <i>administrator</i>, you can also use those:<br/>
      But remember, <a href="https://www.youtube.com/watch?v=guuYU74wU70&t=15s" target="YT">With great power comes great responsibility</a>. Be careful.
      <ul>
        <li><a href="../admin/sql/" target="admin">Admin Menu</a></li>
        <li><a href="../admin/sql/nl.email.sender.php" target="admin">News Letter emailing</a></li>
        <li><a href="../admin/sql/_todo_list.01.php" target="admin">Manage (all) TODO lists</a></li>
      </ul>  
      <?php
    } else {
      ?>
        You don't have Admin privileges...
      <?php
    }
    ?>
    </div>
    <?php
}
    ?>

    <!-- End of menu content -->

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
    </div>   
  </body>        
</html>