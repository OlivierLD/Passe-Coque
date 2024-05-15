<html lang="en">
  <!--
   ! Duh...
   +-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Boat-Club - Migration CSV to DB</title>
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
<h2>Update DB, Boat-Club CSV</h2>
<?php 
// https://www.w3schools.com/php/php_file_open.asp

$HA_FILE_NAME = "./HelloAsso.Boat-Club-04_05_2024-09_05_2024.csv";


class HelloAssoBCRecord {
    static $REF_COMMANDE      =  0;
    static $DATE_COMMANDE     =  1; // - Date de la commande;
    static $STATUS            =  2; // - Statut de la commande;
    static $MEMBER_LAST_NAME  =  3; // - Nom adhérent;
    static $MEMBER_FIRST_NAME =  4; // - Prénom adhérent;
    static $MEMBER_CARD       =  5; // - Carte d'adhérent;
    static $CUST_LAST_NAME    =  6; // - Nom payeur;
    static $CUST_FIRST_NAME   =  7; // - Prénom payeur;
    static $EMAIL             =  8; // - Email payeur;
    static $SOCIAL            =  9; // - Raison sociale;
    static $PAYMENT_MEAN      = 10; // - Moyen de paiement;
    static $TARIF             = 11; // - Tarif;
    static $AMOUNT            = 12; // - Montant tarif;
    static $PROMO             = 13; // - Code Promo;
    static $PROMO_AMOUNT      = 14; // - Montant code promo;
    static $ADDRESS           = 15; // - Adresse;
    static $TELEPHONE         = 16; // - Telephone;
    static $COMMENTS          = 17; // - Commentaires
   

    var $date;
    var $lastName;
    var $firstName;
    var $tarif;
    var $amount;
    var $email;
    var $telephone;
    var $address;
    var $comments;

    function __construct (array $record) {
        $this->lastName = trim(unFrame($record[self::$MEMBER_LAST_NAME])); 
        $this->firstName = trim(unFrame($record[self::$MEMBER_FIRST_NAME]));
        $this->email = strtolower(trim($record[self::$EMAIL])); 
        $this->tarif = $record[self::$TARIF];        
        $this->amount = $record[self::$AMOUNT];
        $this->telephone = $record[self::$TELEPHONE];
        $this->address = $record[self::$ADDRESS];
        $this->date = $record[self::$DATE_COMMANDE];
        $this->comments = $record[self::$COMMENTS];
    }

    function dump(string $endWith="<br/>"): string {
        return(trim($this->email) . ";" . 
             trim($this->firstName) . ";" . 
             trim($this->lastName) . ";" .
             trim(unFrame($this->tarif)) . ";" .  // Remove quotes
             trim($this->amount) . ";" .
             trim($this->telephone) . ";" .
             trim($this->address) . ";" .
             trim($this->date) . ";" .
             trim($this->comments) .
             $endWith);
    }
    function doDump(string $endWith="<br/>"): void {
        echo $this->dump($endWith);
    }

    public static function getRecordLength (): int {
        return self::$COMMENTS; // Last field index
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
    function getComments() {
        return $this->comments;
    }
}

require __DIR__ . "/../../../php/db.cred.php";
require __DIR__ . "/../_db.utils.php";

$VERBOSE = false;

function createRecord(string $dbhost, string $username, string $password, string $database, string $email,  
                      string $lastName, string $firstName, string $date, string $tarif, string $amount, string $telephone, 
                      string $bDate, string $address, string $txt1, string $txt2, bool $verbose=false): void {
    $sql = 'INSERT INTO PASSE_COQUE_MEMBERS (EMAIL, FIRST_NAME, LAST_NAME, TARIF, AMOUNT, TELEPHONE, FIRST_ENROLLED, BIRTH_DATE, ADDRESS, SAILING_EXPERIENCE, SHIPYARD_EXPERIENCE) ' .
                'VALUES (\'' . $email . '\', \'' . str_replace("'", "\'", $firstName) . '\',  \'' . str_replace("'", "\'", $lastName) . '\', \'' . str_replace("'", "\'", $tarif) . '\', ' . (strlen(trim($amount)) == 0 ? 'NULL' : str_replace(",", ".", $amount)) . ', ' .
                    '\'' . $telephone . '\', ' . ( strlen($date) > 0 ? 'STR_TO_DATE(\'' . $date . '\', \'%d/%m/%Y %H:%i\')' : 'NULL' ) . ', ' . (strlen($bDate) > 0 ? 'STR_TO_DATE(\'' .$bDate . '\', \'%d/%m/%Y\')' : 'NULL') . ', ' .
                    '\'' . str_replace("'", "\'", $address) . '\', \'' . str_replace("'", "\'", $txt1) . '\', \'' . str_replace("'", "\'", $txt2) . '\');';
    if ($verbose) {
        echo("Will execute [" . $sql . "]<br/>");                    
    } else {
        echo $sql . "\n";
    }
}

function createBCRecord(string $dbhost, string $username, string $password, string $database, string $email,  
                        string $enrolled, string $level, string $amount, string $lastUpdate, bool $verbose=false): void {
    $sql = 'INSERT INTO BOAT_CLUB_MEMBERS (EMAIL, ENROLLED, MEMBER_LEVEL, FEE_AMOUNT, LAST_FEE_UPDATE) ' .
                'VALUES (\'' . $email . '\', ' . (strlen($enrolled) > 0 ? 'STR_TO_DATE(\'' . $enrolled . '\', \'%d/%m/%Y %H:%i\')' : 'NULL' ) . ', ' . 
                        '\'' . $level . '\', ' . $amount . ', ' .
                        (strlen($lastUpdate) > 0 ? 'STR_TO_DATE(\'' . $lastUpdate . '\', \'%d/%m/%Y %H:%i\')' : 'NULL' ) . ');';
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

function reworkAmount(string $orig): int {
    $euroChar = "€";
    $reworked = $orig;
    if (strpos($reworked, $euroChar)) {
        $reworked = trim(str_replace($euroChar, "", $reworked)); // remove euro sign
    }
    if (strpos($reworked, ",")) {
        $reworked = str_replace(",", ".", $reworked); // replace comma by dot
    }
    // echo("-- IntVal of [" . $reworked . "]\n");
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
    // Populate HA records
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
                // if ($fields[4] == 'Guy' || $fields[4] == 'Gabrielle') {
                //     var_dump($fields);
                // }
                try {
                    if (count($fields) >= HelloAssoBCRecord::getRecordLength()) {
                        $haRecord = new HelloAssoBCRecord($fields);
                        $haRecords[$recordIdx] = $haRecord;
                        $recordIdx++;
                        // echo($haRecord->getEmail() . " - " . $cachaRecord->getFirstName() . " " . $cachaRecord->getLastName() . "- Telephone: [" . $cachaRecord->getTelephone() . "]<br/>");
                    } else {
                        echo ("Nope. Record length is " . count($fields) . ", needed " . HelloAssoBCRecord::getRecordLength() . "<br/>");
                    }

                    if ($VERBOSE) {
                        echo ("- HA Rec ord found<br/>");
                    }

                } catch (Throwable $e2) {
                    echo "Captured Throwable : " . $e2->getMessage() . "<br/>" . PHP_EOL;
                }
            }
        }
        $idx++;
    }
    echo("Built an array of " . count($haRecords) . " HA records.<br/>");

    // Finding double entries in HA.
    foreach($haRecords as $rec) {
        try {
            $haRecs = findHARecords($rec->getEmail(), $haRecords);
            if (count($haRecs) > 1) {
                echo("In HA, " . count($haRecs) . " records for " . $rec->getEmail() . "<br/>");
            }
        } catch (Throwable $e2) {
            echo "Captured Throwable : " . $e2->getMessage() . "<br/>" . PHP_EOL;
        }
    }
    echo("<hr/>");

?>
    <textarea rows="10" style="font-family: 'Courier New'; line-height: normal; width: 100%;">
<?php 


    $extraMessages = array();
    $messIdx = 0;
    // Arrays are built, now proceed
    foreach($haRecords as $rec) {
        try {
            // Look for dup email pattern (a regex test)
            $re = '/.*\.dup\.(\d)+$/m';  // like ...fr.dup.2
            $str = $rec->getEmail();
            if (preg_match($re, $str)) {
                $extraMessages[$messIdx++] = "<span style='color: blue;'>" . $rec->getEmail() . " is a duplicated one.</span><br/>";
            }
            
            // Look for this email in DB (PC Members)
            $members = getMember($dbhost, $username, $password, $database, $rec->getEmail(), false);
            if (count($members) > 0) {
                // Exists in POASSE_COQUE_MEMBERS
                echo("-- " . $rec->getEmail() . " found in DB\n");
            } else {
                echo("-- " . $rec->getEmail() . " NOT found in DB\n");
            }
            $email = $rec->getEmail();
            $firstName = '';
            $lastName = '';
            $date = '';
            $tarif = '';
            $amount = 0;
            $telephone = '';
            $bDate = '';
            $address = '';
            $txt1 = '';
            $txt2 = '';
            if (count($members) > 0) { 
                $firstName = trim($members[0]->firstName);
                $lastName = trim($members[0]->lastName);
                // $date = trim($rec->getDate());
                // $tarif = trim($rec->getTarif());
                $amount = reworkAmount($rec->getAmount());
                // $telephone = trim($members[0]->getTelephone());
                // $address = trim($members[0]->getAddress());
                // $txt1 = unFrame(trim($rec->getComments()));
            } else {
                $firstName = trim($rec->getFirstName());
                $lastName = trim($rec->getLastName());
                $date = trim($rec->getDate());
                $tarif = trim($rec->getTarif());
                $amount = reworkAmount($rec->getAmount());
                $telephone = trim($rec->getTelephone());
                $address = trim($rec->getAddress());
                $txt1 = unFrame(trim($rec->getComments()));
            }

            // echo("-- Amount for " . $email . " is [" . $amount . "] from [" . $rec->getAmount() . "]\n");

            // Generate new records in DB, CREATE or Update
            // All data available, look into the DB
            if (count($members) == 0) {
                // Create record in PASSE_COQUE_MEMBERS
                createRecord($dbhost, $username, $password, $database, 
                            $email, $lastName, $firstName, $date, $tarif, $amount, $telephone, 
                            $bDate, $address, str_replace('""', '"', $txt1), str_replace('""', '"', $txt2), $VERBOSE);
            } 
            // Create record in BOAT_CLUB
            // Check if BC Member...
            $bcMembers = getBCMember($dbhost, $username, $password, $database, $rec->getEmail(), false);
            if (count($bcMembers) == 0) {
                $enrolled = $rec->getDate();
                $level = ($rec->getTarif() == 'Chef de bord' ? 'SKIPPER' : 'CREW');
                $lastUpdate = '';
                createBCRecord($dbhost, $username, $password, $database, 
                            $email, $enrolled, $level, $amount, $lastUpdate, $VERBOSE);
            } else {
                echo("-- " . $email . " already a Boat-Club member\n");
            }
            // End of DB section
        } catch (Throwable $e2) {
            echo "Captured Throwable : " . $e2->getMessage() . "<br/>" . PHP_EOL;
        }
    }
?>
    </textarea>  
<?php
  
  echo ("<h3>" . $messIdx . " messages</h3>");
  // var_dump($extraMessages);
  foreach($extraMessages as $mess) {
    echo ($mess . "<br/>");
  }

} catch (Throwable $e) {
    echo "Captured Throwable : " . $e->getMessage() . "<br/>" . PHP_EOL;
}
?>

</body>
</html>
