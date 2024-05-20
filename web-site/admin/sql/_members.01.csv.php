<?php

/*
 * MySQL Query into a downloadable CSV file
 * Filename dynamically generated.
 * 
 * Good doc at https://www.codexworld.com/export-data-to-csv-file-using-php-mysql/
 */

// phpinfo();

// Require the db file...
require __DIR__ . "/../../php/db.cred.php";

// $username = "passecc128";
// $password = "zcDmf7e53eTs";
// $database = "passecc128";
// $dbhost = "passecc128.mysql.db";

if (isset($_POST['operation'])) {
  $operation = $_POST['operation'];
  if ($operation == 'query') { // Then do the query
    try {
      $name = $_POST['full-name']; 

      // $link = mysqli_init();  // Mandatory ?
      // echo("Will connect on ".$database." ...<br/>");
      $link = new mysqli($dbhost, $username, $password, $database);
    
      if ($link->connect_errno) {
        echo("Oops, errno:".$link->connect_errno."...<br/>");
        die("Connection failed: " . $conn->connect_error);
      } else {
        // echo("Connected.<br/>");
      }
    
      $sql = "SELECT
          PCM.EMAIL AS ID,
          PCM.LAST_NAME,
          PCM.FIRST_NAME,
          PCM.TARIF,
          PCM.AMOUNT,
          PCM.TELEPHONE,
          PCM.FIRST_ENROLLED,
          PCM.NEWS_LETTER_OK,
          PCM.ADMIN_PRIVILEGES,
          PCM.SAILING_EXPERIENCE,
          PCM.SHIPYARD_EXPERIENCE,
          PCM.BIRTH_DATE,
          PCM.ADDRESS,
          (SELECT 'x' from MEMBERS_AND_FEES F WHERE PCM.EMAIL = F.EMAIL AND DATE_FORMAT(PERIOD, '%Y') = '2021') AS '2021',
          (SELECT 'x' from MEMBERS_AND_FEES F WHERE PCM.EMAIL = F.EMAIL AND DATE_FORMAT(PERIOD, '%Y') = '2022') AS '2022',
          (SELECT 'x' from MEMBERS_AND_FEES F WHERE PCM.EMAIL = F.EMAIL AND DATE_FORMAT(PERIOD, '%Y') = '2023') AS '2023',
          (SELECT 'x' from MEMBERS_AND_FEES F WHERE PCM.EMAIL = F.EMAIL AND DATE_FORMAT(PERIOD, '%Y') = '2024') AS '2024'
      FROM 
          PASSE_COQUE_MEMBERS PCM
      WHERE 
           UPPER(PCM.FIRST_NAME) LIKE UPPER('%" . $name . "%') OR 
           UPPER(PCM.LAST_NAME) LIKE UPPER('%" . $name . "%')
      ORDER BY 1;";
      
      // echo('Performing query <code>' . $sql . '</code><br/>');
    
      $query = $link->query($sql); 
      // $result = mysqli_query($link, $sql);
 
      if ($query->num_rows > 0) { 
          $delimiter = ";"; //","; 
          $filename = "members-data_" . date('Y-m-d') . ".csv"; 
           
          // Create a file pointer 
          $f = fopen('php://memory', 'w'); 
           
          // Set column headers 
          $fields = array('ID', 
                          'LAST_NAME', 
                          'FIRST_NAME', 
                          'TARIF', 
                          'AMOUNT', 
                          'TELEPHONE', 
                          'FIRST_ENROLLED', 
                          'NL_OK', 
                          'ADMIN', 
                          'SAILING_EXPERIENCE', 
                          'SHIPYARD_EXPERIENCE',
                          'BIRTH_DATE',
                          'ADDRESS',
                          '2021',
                          '2022',
                          '2023',
                          '2024'
                        ); 
          fputcsv($f, $fields, $delimiter); 
           
          // Output each row of the data, format line as csv and write to file pointer 
          while ($row = $query->fetch_assoc()) { 
              // var_dump($row);
              //echo("<br/>");
              $nlOk = ($row['NEWS_LETTER_OK'] == 1) ? "yes" : "no";
              $admin = ($row['ADMIN_PRIVILEGES'] == 1) ? "yes" : "no";
              $lineData = array($row['ID'], 
                                utf8_encode($row['LAST_NAME']), 
                                utf8_encode($row['FIRST_NAME']), 
                                utf8_encode($row['TARIF']), 
                                $row['AMOUNT'], 
                                $row['TELEPHONE'], 
                                $row['FIRST_ENROLLED'], 
                                $nlOk, 
                                $admin, 
                                utf8_encode($row['SAILING_EXPERIENCE']), 
                                utf8_encode($row['SHIPYARD_EXPERIENCE']),
                                $row['BIRTH_DATE'],
                                utf8_encode($row['ADDRESS']),
                                $row['2021'],
                                $row['2022'],
                                $row['2023'],
                                $row['2024']
                              ); 
              fputcsv($f, $lineData, $delimiter); 
          } 
           
          // Move back to beginning of file 
          fseek($f, 0); 
           
          // Set headers to download file rather than displayed 
          header('Content-Type: text/csv'); 
          header('Content-Disposition: attachment; filename="' . $filename . '";'); 
           
          // output all remaining data on a file pointer 
          fpassthru($f); 
      } else {
        echo "Query $sql returned no data...<br/>";
      }
      // exit; 

      // On ferme !
      $link->close();
      // echo("Closed DB<br/>".PHP_EOL);
    } catch (Throwable $e) {
      echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
    }
    // echo("<hr/>" . PHP_EOL);
    // echo("Again ? Click <a href='#'>Here</a>.");
  }
} else { // Then display the form
?>
<html lang="en">
  <!--
   ! WiP.
   ! A Form to query the PASSE_COQUE_MEMBERS table, into a csv file.
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Query Members</title>
    <style type="text/css">
      * {
        font-family: 'Courier New'
      }

      tr > td {
        border: 1px solid silver;
      }
    </style>
  </head>
  <body>
    <h1>PHP / MySQL. Passe-Coque Members from the DB, in CSV file</h1>

    <form action="#" method="post">
      <input type="hidden" name="operation" value="query">
      <table>
        <tr>
          <td valign="top">Name (part of):</td><td><input type="text" name="full-name" size="40" placeholder="Filter?"></td>
        </tr>
        <tr>
          <td colspan="2" style="text-align: center;"><input type="submit" value="Query"></td>
        </tr>
      </table>
    </form>
    </body>        
</html>
<?php
}  
?>        
