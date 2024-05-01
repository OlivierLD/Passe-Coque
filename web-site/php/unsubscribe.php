<html lang="en">
  <!--
   ! News-Letter unsubscribe
   ! Call it like https://passe-coque.com/php/unsubscribe.php?subscriber-id=194
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Unsubscribe</title>
    <!-- TO be used from an iFrame, css is required -->
    <link rel="stylesheet" href="../fonts/font.01.css">
    <link rel="stylesheet" href="../fonts/font.02.css">
    <link rel="stylesheet" href="../passe-coque.css" type="text/css"/>
    <!--style type="text/css">
      * {
        font-family: 'Courier New'
      }

      tr > td, th {
        border: 1px solid silver;
      }
      h1 {
        /* text-stroke: 2px orange; */ /* Might not be supported */
        font-size: 3em;
        color: black;
        -webkit-text-fill-color: white; /* Will override color (regardless of order) */
        -webkit-text-stroke: 3px black;
      }
    </style-->
  </head>
  <body>
    <!--h1>Espace Membres</h1-->
    <?php

require __DIR__ . "/db.cred.php";

// Call it like https://passe-coque.com/php/unsubscribe.php?subscriber-id=194

if (isset($_GET['subscriber-id'])) {

    $nl_id = $_GET['subscriber-id'];

    try {
        // echo("Will connect on ".$database." ...<br/>");
        $link = new mysqli($dbhost, $username, $password, $database);

        if ($link->connect_errno) {
            echo("Oops, DB Connection errno:".$link->connect_errno."...<br/>");
            die("Connection failed: " . $conn->connect_error);
        } else {
            // echo("Connected.<br/>");
        }

        $sql = "SELECT EMAIL, LAST_NAME, FIRST_NAME, NEWS_LETTER_OK FROM PASSE_COQUE_MEMBERS WHERE EMAIL = '$nl_id';"; 
        
        echo('Performing query <code>'.$sql.'</code><br/>');

        // $result = mysql_query($sql, $link);
        $result = mysqli_query($link, $sql);
        // echo ("Returned " . $result->num_rows . " row(s)<br/>" . PHP_EOL);
        if ($result->num_rows == 0) {
            echo "Utilisateur id $nl_id inconnu.<br/>" . PHP_EOL;
        } else if ($result->num_rows > 1) {
            echo "More than one entry for id $nl_id, wierd...<br/>" . PHP_EOL;
        } else {
            // Retrieving user's data
            while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
                // echo "table contains ". count($table) . " entry(ies).<br/>";
                $email = $table[0];
                $name = $table[1] . ' ' . $table[2];
                $active = $table[3];
            }
            echo "<p>Bienvenue $name.</p>" . PHP_EOL;
            if ($active) {
                echo "Vous souhaitez vous d&eacute;sabonner de la news letter (sur l'email $email) <br/>" . PHP_EOL; 
                echo "Merci de confirmer en cliquant le bouton ci-dessous. <br/>" . PHP_EOL;
                ?>
                <form action="<?php echo(basename(__FILE__)); ?>" method="post">
                    <input type="hidden" name="operation" value="unsubscribe">
                    <input type="hidden" name="nl-id" value="<?php echo $nl_id ?>">
                    <input type="submit" value="D&eacute;sabonner">
                </form>
                <hr/>
                <?php
            } else {
                echo "Vous &ecirc;tes d&eacute;j&agrave; d&eacute;sabonn&eacute;.<br/>" . PHP_EOL;
            }
            // header('WWW-Authenticate: Basic realm="Passe-Coque Realm"');
        }
        // On ferme !
        $link->close();
        // echo("Closed DB<br/>".PHP_EOL);
    } catch (Throwable $e) {
        echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }
} else if (isset($_POST['operation'])) { // Proceed to update
    $operation = $_POST['operation'];
    if ($operation == 'unsubscribe') {
        if (isset($_POST['nl-id'])) {
            $nl_id = $_POST['nl-id'];
            $sql = "UPDATE PASSE_COQUE_MEMBERS SET NEWS_LETTER_OK = FALSE WHERE EMAIL = '$nl_id';";
            try {
                // echo("Will connect on ".$database." ...<br/>");
                $link = new mysqli($dbhost, $username, $password, $database);
                
                if ($link->connect_errno) {
                    echo("Oops, errno:".$link->connect_errno."...<br/>");
                    die("Connection failed: " . $conn->connect_error);
                } else {
                    // echo("Connected.<br/>");
                }
                if (true) { // Do perform
                    if ($link->query($sql) === TRUE) {
                        echo "OK. Record updated successfully.<br/>" . PHP_EOL;
                        echo "Vous ne recevrez plus la news letter.<br/>" . PHP_EOL;
                    } else {
                        echo "ERROR: " . $sql . "<br/>" . $link->error . "<br/>";
                    }
                } else {
                    echo "Will execute $sql <br/>" . PHP_EOL;
                }
                // On ferme !
                $link->close();
                // echo("Closed DB<br/>".PHP_EOL);
            } catch (Throwable $e) {
                echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
            }
        } else {
            echo "ID not found where expected.<br/>" .PHP_EOL;
        }
    } else {
        echo "Unknown operation $operation.<br/>" . PHP_EOL;
    }
} else {
    ?>
    <b>Un-expected context...</b>
    <?php
}  
    ?>        
  </body>        
</html>