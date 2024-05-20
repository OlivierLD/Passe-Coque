<html lang="en">
  <!--
   ! New Members and Fees.
   ! Migration.
   +-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>New Fees</title>
    <link rel="icon" type="image/png" href="/logos/LOGO_PC_no_txt.png">
    <!--link rel="stylesheet" href="/css/stylesheet.css" type="text/css"/-->
    <link rel="stylesheet" href="/fonts/font.01.css">
    <link rel="stylesheet" href="/fonts/font.02.css">

    <link rel="stylesheet" href="/passe-coque.menu.css">
    <link rel="stylesheet" href="/passe-coque.css" type="text/css"/>
    <style type="text/css">
        a:link, a:visited {
            background-color: #f44336;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            border-radius: 5px;
        }

        a:hover, a:active {
            background-color: red;
        }

        body {
            font-family: "Courier New";
        }
    </style>
</head>
<body>
<h2>Populate new fee structure</h2>
<?php 

require __DIR__ . "/../../../php/db.cred.php";
require __DIR__ . "/../_db.utils.php";

$VERBOSE = false;

function getMembersWithFees(string $dbhost, string $username, string $password, string $database, bool $verbose) : array {
    $sql =  "SELECT M.EMAIL FROM PASSE_COQUE_MEMBERS M WHERE EXISTS (SELECT MF.YEAR FROM MEMBER_FEES MF WHERE MF.EMAIL = M.EMAIL);";
    $emails = array();
    $index = 0;

    try {
        if ($verbose) {
            echo("Will connect on ".$database." ...<br/>");
        }
        $link = new mysqli($dbhost, $username, $password, $database);
    
        if ($link->connect_errno) {
            echo("Oops, errno:".$link->connect_errno."...<br/>");
            die("Connection failed: " . $conn->connect_error); // TODO Throw an exception
        } else {
            if ($verbose) {
                echo("Connected.<br/>");
            }
        }

        if ($verbose) {
            echo ("Executing [" . $sql . "]");
        }
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>");
        }
        while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
            $emails[$index] =  $table[0];
            $index++;
        }        
        // On ferme !
        $link->close();
        if ($verbose) {
            echo("Closed DB<br/>".PHP_EOL);
        }
    } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }

    return $emails;
}

function getMemberFees(string $dbhost, string $username, string $password, string $database, string $email, bool $verbose) : array {
    $sql =  "SELECT YEAR FROM MEMBER_FEES WHERE EMAIL = '$email';";
    $years = array();
    $index = 0;

    try {
        if ($verbose) {
            echo("Will connect on ".$database." ...<br/>");
        }
        $link = new mysqli($dbhost, $username, $password, $database);
    
        if ($link->connect_errno) {
            echo("Oops, errno:".$link->connect_errno."...<br/>");
            die("Connection failed: " . $conn->connect_error); // TODO Throw an exception
        } else {
            if ($verbose) {
                echo("Connected.<br/>");
            }
        }

        if ($verbose) {
            echo ("Executing [" . $sql . "]");
        }
        $result = mysqli_query($link, $sql);
        if ($verbose) {
            echo ("Returned " . $result->num_rows . " row(s)<br/>");
        }
        while ($table = mysqli_fetch_array($result)) { // go through each row that was returned in $result
            $years[$index] =  $table[0];
            $index++;
        }        
        // On ferme !
        $link->close();
        if ($verbose) {
            echo("Closed DB<br/>".PHP_EOL);
        }
    } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }

    return $years;
}

function createRecord(string $dbhost, string $username, string $password, string $database, string $email,  
                      string $year, bool $verbose=false): void {
    $newDate = '01/01/' . $year; // STR_TO_DATE('05/06/1966', '%d/%m/%Y')

    $sql = 'INSERT INTO MEMBER_FEES_2 (EMAIL, PERIOD, AMOUNT) ' .
                'VALUES (\'' . $email . '\', STR_TO_DATE(\'' . $newDate . '\', \'%d/%m/%Y\'), 0);';
    if ($verbose) {
        echo("Will execute [" . $sql . "]<br/>");                    
    } else {
        echo $sql . "\n";
    }
}

try {
    $emails = getMembersWithFees($dbhost, $username, $password, $database, $VERBOSE);

    echo ("Found " . count($emails) . " lines. <br/>");

    ?>
    <textarea rows="10" style="font-family: 'Courier New'; line-height: normal; width: 100%;">
<?php 

    foreach($emails as $email) {

        // Get fees for this email
        $years = getMemberFees($dbhost, $username, $password, $database, $email, $VERBOSE);
        foreach($years as $year) {
            createRecord($dbhost, $username, $password, $database, $email, $year, $VERBOSE);
        }
    }

} catch (Throwable $e) {
    echo "Captured Throwable : " . $e->getMessage() . "<br/>" . PHP_EOL;
}

// header('Content-Type: text/plain; charset=utf-8');

?>
  </textarea>

</body>
</html>
