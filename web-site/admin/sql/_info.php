<html lang="en">
  <!--
   ! WiP.
   ! See https://www.php.net/manual/en/language.constants.magic.php
   +-->
  <head>
    <!--meta charset="UTF-8">
    <meta charset="ISO-8859-1"-->
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Info</title>
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
    <h1>PHP / MySQL. Info</h1>
    <div>
      <?php
        print "Origin (full, __FILE__): " . __FILE__ . "\n";
        echo "<br/>";
        print "Origin (basename(__FILE__)): " . basename(__FILE__) . PHP_EOL;
        echo "<br/>";
      ?>
    </div>
    <?php
phpinfo();

// require __DIR__ . "/../../php/db.cred.php";

?>
  </body>        
</html>