<html lang="en">
  <!--
   ! Duh...
   +-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Migration CSV to DB</title>
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
<h2>Update DB, from CSV</h2>
<?php 
// https://www.w3schools.com/php/php_file_open.asp

$FILE_NAME = "./HelloAsso-25_03_2021-08_05_2024.csv";

$DATE = 1;
$LAST_NAME = 6;
$FIRST_NAME = 7;
$TARIF = 11;
$AMOUNT = 12;
$EMAIL = 16;
$TELEPHONE = 15;
$ADDR_1 = 17;
$ADDR_2 = 18;
$B_DATE = 19;
$ADDR_3 = 20;
$TXT_1 = 21;
$TXT_2 = 22;

require __DIR__ . "/../../../php/db.cred.php";
require __DIR__ . "/../_db.utils.php";

function createRecord(string $dbhost, string $username, string $password, string $database, string $email,  
                      string $lastName, string $firstName, string $date, string $tarif, string $amount, string $telephone, 
                      string $bDate, string $addr1, string $addr2, string $addr3, string $txt1, string $txt2, bool $verbose=false): void {
    $address = $addr1 . '\n' . $addr2 . '\n' . $addr3;
    $sql = 'INSERT INTO PASSE_COQUE_MEMBERS (EMAIL, FIRST_NAME, LAST_NAME, TARIF, AMOUNT, TELEPHONE, FIRST_ENROLLED, BIRTH_DATE, ADDRESS, SAILING_EXPERIENCE, SHIPYARD_EXPERIENCE) ' .
                'VALUES (\'' . $email . '\', \'' . $firstName . '\',  \'' . $lastName . '\', \'' . str_replace("'", "\'", $tarif) . '\', ' . str_replace(",", ".", $amount) . ', ' .
                    '\'' . $telephone . '\', STR_TO_DATE(\'' .$date . '\', \'%d/%m/%Y %H:%i\'), STR_TO_DATE(\'' .$bDate . '\', \'%d/%m/%Y\'), ' .
                    '\'' . str_replace("'", "\'", $address) . '\', \'' . str_replace("'", "\'", $txt1) . '\', \'' . str_replace("'", "\'", $txt2) . '\');';
    echo("Will execute [" . $sql . "]<br/>");                    
}

function updateRecord(string $dbhost, string $username, string $password, string $database, string $email, 
                      string $lastName, string $firstName, string $date, string $tarif, string $amount, string $telephone, 
                      string $bDate, string $addr1, string $addr2, string $addr3, string $txt1, string $txt2, bool $verbose=false): void {
    $address = $addr1 . '\n' . $addr2 . '\n' . $addr3;
    $sql = 'UPDATE PASSE_COQUE_MEMBERS ' .
                'SET FIRST_NAME = \'' . $firstName . '\', ' .
                    'LAST_NAME =  \'' . $lastName . '\', ' .
                    'TARIF =  \'' . str_replace("'", "\'", $tarif) . '\', ' .
                    'AMOUNT =  ' . str_replace(",", ".", $amount) . ', ' .
                    'TELEPHONE =  \'' . $telephone . '\', ' .
                    'FIRST_ENROLLED = STR_TO_DATE(\'' .$date . '\', \'%d/%m/%Y %H:%i\'), ' .
                    'BIRTH_DATE = STR_TO_DATE(\'' .$bDate . '\', \'%d/%m/%Y\'), ' .
                    'ADDRESS = \'' . str_replace("'", "\'", $address) . '\', ' . 
                    'SAILING_EXPERIENCE = \'' . str_replace("'", "\'", $txt1) . '\', ' . 
                    'SHIPYARD_EXPERIENCE = \'' . str_replace("'", "\'", $txt2) . '\' ' . 
              'WHERE EMAIL = \'' . $email . '\';';
    echo("Will execute [" . $sql . "]<br/>");
}

try {
    // $fullContent = readfile($FILE_NAME);
    $fullContent = file_get_contents($FILE_NAME);

    $allLines = explode(PHP_EOL, $fullContent);

    echo ("Found " . count($allLines) . " lines. <br/>");
    $idx = 0;
    foreach($allLines as $line) {
        if (strlen($line) > 0) {
            if ($idx > 0) {
                $fields = explode(";", $line);
                try {
                    echo ("- " . $fields[$FIRST_NAME] . ' ' . $fields[$LAST_NAME] . ", email:" . $fields[$EMAIL] . "<br/>");
                } catch (Throwable $e2) {
                    echo "Captured Throwable : " . $e2->getMessage() . "<br/>" . PHP_EOL;
                }
                // All data available, look int0 the DB
                $members = getMember($dbhost, $username, $password, $database, $fields[$EMAIL], false);
                if (count($members) == 0) {
                    echo("<span style='color: red;'>" . $fields[$EMAIL] . " : Not in DB.</span><br/>");
                    createRecord($dbhost, $username, $password, $database, 
                    $fields[$EMAIL], $fields[$LAST_NAME], $fields[$FIRST_NAME], $fields[$DATE], $fields[$TARIF], $fields[$AMOUNT], $fields[$TELEPHONE], 
                    $fields[$B_DATE], $fields[$ADDR_1], $fields[$ADDR_2], $fields[$ADDR_3], $fields[$TXT_1], $fields[$TXT_2]);
                // } else if (count($members) > 1) { // TODO Detect duplicates in CSV
                //     echo("<span style='color: blue;'>" . $fields[$EMAIL] . " : " . count($members) . " in DB.</span><br/>");
                } else { // 1 in DB
                    echo($fields[$EMAIL] . " : " . count($members) . " in DB.<br/>");
                    updateRecord($dbhost, $username, $password, $database, 
                                 $fields[$EMAIL], $fields[$LAST_NAME], $fields[$FIRST_NAME], $fields[$DATE], $fields[$TARIF], $fields[$AMOUNT], $fields[$TELEPHONE], 
                                 $fields[$B_DATE], $fields[$ADDR_1], $fields[$ADDR_2], $fields[$ADDR_3], $fields[$TXT_1], $fields[$TXT_2]);
                }
            }
        }
        $idx++;
    }

} catch (Throwable $e) {
    echo "Captured Throwable : " . $e->getMessage() . "<br/>" . PHP_EOL;
}

?>
</body>
</html>
