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
<h2>Update DB, from Cacha's CSV</h2>
<?php 
// https://www.w3schools.com/php/php_file_open.asp

$FILE_NAME = "./Cacha.2024.05.09.csv";
$HA_FILE_NAME = "./HelloAsso-25_03_2021-08_05_2024.csv";


class CachaRecord {
    /*public*/ static  $LAST_NAME = 0;
    /*public*/ static  $FIRST_NAME = 1;
    /*public*/ static  $TARIF = 2;
    /*public*/ static  $AMOUNT = 3;
    /*public*/ static  $TELEPHONE = 4;
    /*public*/ static  $ADDR = 5;
    /*public*/ static  $EMAIL = 6;
    /*public*/ static  $DATE = 7;
    /*public*/ static  $_2021 = 8;
    /*public*/ static  $_2022 = 9;
    /*public*/ static  $_2023 = 10;
    /*public*/ static  $_2024 = 11;

    var $lastName;
    var $firstName;
    var $email;
    var $tarif;
    var $amount;
    var $telephone;
    var $address;
    var $date;
    var $fee_2021;
    var $fee_2022;
    var $fee_2023;
    var $fee_2024;

    function __construct (array $record) {
        $this->lastName = $record[self::$LAST_NAME];   // initialize lastName
        $this->firstName = $record[self::$FIRST_NAME]; // initialize firstName
        $this->email = $record[self::$EMAIL];          // initialize email
        $this->tarif = $record[self::$TARIF];          // etc... 
        $this->amount = $record[self::$AMOUNT];
        $this->telephone = $record[self::$TELEPHONE];
        $this->address = $record[self::$ADDR];
        $this->date = $record[self::$DATE];
        $this->fee_2021 = $record[self::$_2021];
        $this->fee_2022 = $record[self::$_2022];
        $this->fee_2023 = $record[self::$_2023];
        $this->fee_2024 = $record[self::$_2024];
    }

    public static function getRecordLength (): int {
        return self::$_2024; // Last field index
    }

    function dump() {
        echo(trim($this->email) . ";" . 
             trim($this->firstName) . ";" . 
             trim($this->lastName) . ";" .
             trim($this->tarif) . ";" .
             trim($this->amount) . ";" .
             trim($this->telephone) . ";" .
             trim($this->address) . ";" .
             trim($this->date) . 
             "<br/>");
    }

    function getFirstName() {
        return $this->firstName;
    }
    function getLastName() {
        return $this->lastName;
    }
    function getEmail() {
        return $this->email;
    }
    function getTarif() {
        return $this->tarif;
    }
    function getAmount() {
        return $this->amount;
    }
    function getTelephone() {
        return $this->telephone;
    }
    function getAddress() {
        return $this->address;
    }
    function getDate() {
        return $this->date;
    }

}

class HelloAssoRecord {
    static $DATE = 1;
    static $LAST_NAME = 6;
    static $FIRST_NAME = 7;
    static $TARIF = 11;
    static $AMOUNT = 12;
    static $EMAIL = 16;  // Email. 8 is "email payeur"
    static $TELEPHONE = 15;
    static $ADDR_1 = 17;
    static $ADDR_2 = 18;
    static $B_DATE = 19;
    static $ADDR_3 = 20;
    static $TXT_1 = 21;
    static $TXT_2 = 22;

    var $date;
    var $lastName;
    var $firstName;
    var $tarif;
    var $amount;
    var $email;
    var $telephone;
    var $address;
    var $bDate;
    var $txt1;
    var $txt2;

    function __construct (array $record) {
        $this->lastName = $record[self::$LAST_NAME];   // initialize lastName
        $this->firstName = $record[self::$FIRST_NAME]; // initialize firstName
        $this->email = $record[self::$EMAIL];          // initialize email
        $this->tarif = $record[self::$TARIF];          // etc... 
        $this->amount = $record[self::$AMOUNT];
        $this->telephone = $record[self::$TELEPHONE];
        $this->address = $record[self::$ADDR_1] . "\n" . $record[self::$ADDR_2] . "\n" . $record[self::$ADDR_3];
        $this->date = $record[self::$DATE];
        $this->bDate = $record[self::$B_DATE];
        $this->txt1 = $record[self::$TXT_1];
        $this->txt2 = $record[self::$TXT_2];
    }

    function dump() {
        echo(trim($this->email) . ";" . 
             trim($this->firstName) . ";" . 
             trim($this->lastName) . ";" .
             trim(unFrame($this->tarif)) . ";" .  // TODO Remove quotes
             trim($this->amount) . ";" .
             trim($this->telephone) . ";" .
             trim($this->address) . ";" .
             trim($this->date) . ";" .
             trim($this->bDate) . ";" .
             trim($this->txt1) . ";" .
             trim($this->txt2) . 
             "<br/>");
    }

    public static function getRecordLength (): int {
        return self::$TXT_2; // Last field index
    }
    function getFirstName() {
        return $this->firstName;
    }
    function getLastName() {
        return $this->lastName;
    }
    function getEmail() {
        return $this->email;
    }
    function getTarif() {
        return $this->tarif;
    }
    function getAmount() {
        return $this->amount;
    }
    function getTelephone() {
        return $this->telephone;
    }
    function getAddress() {
        return $this->address;
    }
    function getDate() {
        return $this->date;
    }
    function getBDate() {
        return $this->bDate;
    }
    function getTxt1() {
        return $this->txt1;
    }
    function getTxt2() {
        return $this->txt2;
    }
}

require __DIR__ . "/../../../php/db.cred.php";
require __DIR__ . "/../_db.utils.php";

$VERBOSE = false;

function createRecord(string $dbhost, string $username, string $password, string $database, string $email,  
                      string $lastName, string $firstName, string $date, string $tarif, string $amount, string $telephone, 
                      string $bDate, string $addr1, string $addr2, string $addr3, string $txt1, string $txt2, bool $verbose=false): void {
    $address = $addr1 . '\n' . $addr2 . '\n' . $addr3;
    $sql = 'INSERT INTO PASSE_COQUE_MEMBERS (EMAIL, FIRST_NAME, LAST_NAME, TARIF, AMOUNT, TELEPHONE, FIRST_ENROLLED, BIRTH_DATE, ADDRESS, SAILING_EXPERIENCE, SHIPYARD_EXPERIENCE) ' .
                'VALUES (\'' . $email . '\', \'' . str_replace("'", "\'", $firstName) . '\',  \'' . str_replace("'", "\'", $lastName) . '\', \'' . str_replace("'", "\'", $tarif) . '\', ' . str_replace(",", ".", $amount) . ', ' .
                    '\'' . $telephone . '\', STR_TO_DATE(\'' .$date . '\', \'%d/%m/%Y %H:%i\'), ' . (strlen($bDate) > 0 ? 'STR_TO_DATE(\'' .$bDate . '\', \'%d/%m/%Y\')' : 'NULL') . ', ' .
                    '\'' . str_replace("'", "\'", $address) . '\', \'' . str_replace("'", "\'", $txt1) . '\', \'' . str_replace("'", "\'", $txt2) . '\');';
    if ($verbose) {
        echo("Will execute [" . $sql . "]<br/>");                    
    } else {
        echo $sql . "\n";
    }
}

function updateRecord(string $dbhost, string $username, string $password, string $database, string $email, 
                      string $lastName, string $firstName, string $date, string $tarif, string $amount, string $telephone, 
                      string $bDate, string $addr1, string $addr2, string $addr3, string $txt1, string $txt2, bool $verbose=false): void {
    $address = $addr1 . '\n' . $addr2 . '\n' . $addr3;
    $sql = 'UPDATE PASSE_COQUE_MEMBERS ' .
                'SET FIRST_NAME = \'' . str_replace("'", "\'", $firstName) . '\', ' .
                    'LAST_NAME =  \'' . str_replace("'", "\'", $lastName) . '\', ' .
                    'TARIF = \'' . str_replace("'", "\'", $tarif) . '\', ' .
                    'AMOUNT = ' . str_replace(",", ".", $amount) . ', ' .
                    'TELEPHONE =  \'' . $telephone . '\', ' .
                    'FIRST_ENROLLED = STR_TO_DATE(\'' .$date . '\', \'%d/%m/%Y %H:%i\'), ' .
                    'BIRTH_DATE = ' . (strlen($bDate) > 0 ? 'STR_TO_DATE(\'' .$bDate . '\', \'%d/%m/%Y\')' : 'NULL') . ', ' .
                    'ADDRESS = \'' . str_replace("'", "\'", $address) . '\', ' . 
                    'SAILING_EXPERIENCE = \'' . str_replace("'", "\'", $txt1) . '\', ' . 
                    'SHIPYARD_EXPERIENCE = \'' . str_replace("'", "\'", $txt2) . '\' ' . 
              'WHERE EMAIL = \'' . $email . '\';';

    if ($verbose) {
        echo("Will execute [" . $sql . "]<br/>");
    } else {
        echo $sql . "\n";
    }
}

function findHARecords(string $email, array $haRec) : array {
    $foundRecs = array();
    $idx = 0;
    foreach($haRec as $ha) {
        if ($ha->getEmail() == $email) {
            $foundRecs[$idx] = $ha;
            $idx++;
        }
    }
    return $foundRecs;
}

function findCACHARecords(string $email, array $cachaRec) : array {
    $foundRecs = array();
    $idx = 0;
    foreach($cachaRec as $rec) {
        if ($rec->getEmail() == $email) {
            $foundRecs[$idx] = $rec;
            $idx++;
        }
    }
    return $foundRecs;
}

function reworkAmount(string $orig): int {
    $euroChar = "€";
    $reworked = $orig;
    if (strpos($reworked, $euroChar)) {
        $reworked = trim(str_replace($euroChar, "", $reworked)); // remove euro sign
    }
    if (strpos($reworked, ",")) {
        $reworked = str_replace(",", ".", $reworked); // replace comma by dot
    }
    return intval($reworked);
}

// Remove first and last double quotes
function unFrame(string $orig): string {
    $orig = trim($orig);
    if (str_starts_with($orig, '"') && str_ends_with($orig, '"')) {
        $orig = substr($orig, 1, strlen($orig) - 2);
    }
    return trim($orig);
}

// 2 functions not implemented in PHP 7...
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $str, string $needle): bool {
        return (substr($str, 0, 1) == $needle);
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $str, string $needle): bool {
        return (substr($str, strlen($str) - 1, 1) == $needle);
    }
}

try {
    $fullHAContent = file_get_contents($HA_FILE_NAME);
    $allHALines = explode(PHP_EOL, $fullHAContent);

    echo ("Found " . count($allHALines) . " lines in " . $HA_FILE_NAME . "<br/>");

    $idx = 0;
    $recordIdx = 0;
    $haRecords = array();
    foreach($allHALines as $line) {
        if (strlen($line) > 0) {
            if ($idx > 0) {
                $fields = explode(";", $line);
                try {
                    if (count($fields) >= HelloAssoRecord::getRecordLength()) {
                        $haRecord = new HelloAssoRecord($fields);
                        $haRecords[$recordIdx] = $haRecord;
                        $recordIdx++;
                        // echo($haRecord->getEmail() . " - " . $cachaRecord->getFirstName() . " " . $cachaRecord->getLastName() . "- Telephone: [" . $cachaRecord->getTelephone() . "]<br/>");
                    } else {
                        echo ("Nope. Record length is " . count($fields) . ", needed " . HelloAssoRecord::getRecordLength() . "<br/>");
                    }

                    if ($VERBOSE) {
                        echo ("- " . $fields[$FIRST_NAME] . ' ' . $fields[$LAST_NAME] . ", email:" . $fields[$EMAIL] . "<br/>");
                    }

                } catch (Throwable $e2) {
                    echo "Captured Throwable : " . $e2->getMessage() . "<br/>" . PHP_EOL;
                }
            }
        }
        $idx++;
    }
    echo("Built an array of " . count($haRecords) . " HA records.<br/>");

    // $fullContent = readfile($FILE_NAME);
    $fullCachaContent = file_get_contents($FILE_NAME);
    $allCachaLines = explode(PHP_EOL, $fullCachaContent);

    echo ("Found " . count($allCachaLines) . " lines in " . $FILE_NAME . "<br/>");

    $idx = 0;
    $recordIdx = 0;
    $cachaRecords = array();
    foreach($allCachaLines as $line) {
        if (strlen($line) > 0) {
            if ($idx > 0) {
                $fields = explode(";", $line);
                try {
                    if (count($fields) >= CachaRecord::getRecordLength()) {
                        $cachaRecord = new CachaRecord($fields);
                        $cachaRecords[$recordIdx] = $cachaRecord;
                        $recordIdx++;
                    } else {
                        echo ("Nope. Record length is " . count($fields) . ", needed " . CachaRecord::getRecordLength() . "<br/>");
                    }

                    if ($VERBOSE) {
                        echo ("- " . $fields[$FIRST_NAME] . ' ' . $fields[$LAST_NAME] . ", email:" . $fields[$EMAIL] . "<br/>");
                    }

                } catch (Throwable $e2) {
                    echo "Captured Throwable : " . $e2->getMessage() . "<br/>" . PHP_EOL;
                }
            }
        }
        $idx++;
    }
    echo("Built an array of " . count($cachaRecords) . " CACHA records.<br/>");
    echo("<hr/>");

    // Finding double entries.
    foreach($cachaRecords as $rec) {
        try {
            $cachaRecs = findCACHARecords($rec->getEmail(), $cachaRecords);
            if (count($cachaRecs) > 1) {
                echo(count($cachaRecs) . " records for " . $rec->getEmail() . "<br/>");
            }
        } catch (Throwable $e2) {
            echo "Captured Throwable : " . $e2->getMessage() . "<br/>" . PHP_EOL;
        }
    }
    echo("<hr/>");

    // Arrays are built, now proceed
    foreach($cachaRecords as $rec) {
        try {
            // $rec->printSummary();
            // echo($cachaRecord->getEmail() . " - " . $cachaRecord->getFirstName() . " " . $cachaRecord->getLastName() . "- Telephone: [" . $cachaRecord->getTelephone() . "]<br/>");
            // Look for this email in HA
            $haRecs = findHARecords($rec->getEmail(), $haRecords);
            if (false) {
                echo("For " . $rec->getEmail() . " - " . $rec->getFirstName() . " " . $rec->getLastName() . ", found " . count($haRecs) . " HA Records.<br/>");
                if (strlen($rec->getAmount()) > 0) {
                    echo("Amount [" . $rec->getAmount() . "] becomes [" . reworkAmount($rec->getAmount()) . "]<br/>");
                } else { // Available in HA ?
                    if (count($haRecs) > 0) {
                        echo("No amount for " . $rec->getEmail() . ", looking in HA: [" . $haRecs[0]->getAmount() . "] => " . reworkAmount($haRecs[0]->getAmount()) . "<br/>");
                    }
                }
                if ($rec->getEmail() == "jeff.allais@hotmail.fr") { // A test
                    $rec->dump();
                    $haRecs[0]->dump();
                }
            } else {
                echo("<hr/>" . PHP_EOL);
                $rec->dump();
                if (count($haRecs) > 0) {
                    if (count($haRecs) > 1) {
                        echo("<span style='color: red;'>" . count($haRecs) . " records in HA</span><br/>");
                    }
                    foreach($haRecs as $haRec) {
                        $haRec->dump();
                    }
                } else {
                    echo("No HA record was found<br/>");
                }
                echo("<hr/>" . PHP_EOL);
            }
        } catch (Throwable $e2) {
            echo "Captured Throwable : " . $e2->getMessage() . "<br/>" . PHP_EOL;
        }
    }

} catch (Throwable $e) {
    echo "Captured Throwable : " . $e->getMessage() . "<br/>" . PHP_EOL;
}
?>

</body>
</html>
