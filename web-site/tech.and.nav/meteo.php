<!DOCTYPE html>
<!--
 ! WiP
 +-->

 <?php

$lang = 'FR';
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
}

 ?>
<html>
  <head>
    <title>Passe-Coque</title>
    <link rel="icon" type="image/png" href="../logos/LOGO_PC_no_txt.png">
    <link rel="stylesheet" href="../passe-coque.css" type="text/css" id="styleLink">
    <style type="text/css">

    </style>
  </head>
  <body>
    <h1><?php echo( $lang == 'FR' ? "M&eacute;t&eacute;o" : "Weather Forecasts"); ?></h1>
    <ul>
      <li><a href="./weather/internet.faxes.NAtl.colors.html" target="_blank">North Altantic faxes, real time, Colored versions.</a></li>

      <li><a href="https://olivierld.github.io/web.stuff/boat.stuff/weather/internet.faxes.NAtl.html" target="OlivLD">Faxes, North Atlantic (real time)</a></li>
      <li><a href="https://olivierld.github.io/web.stuff/boat.stuff/weather/internet.faxes.NAtl.2.html" target="OlivLD">Several Atlantic faxes (v2), reworked on the client side...</a></li>
      <li><a href="https://olivierld.github.io/web.stuff/boat.stuff/weather/internet.faxes.NPac.html" target="OlivLD">Faxes, North Pacific (real time)</a></li>
      <li><a href="https://olivierld.github.io/web.stuff/boat.stuff/weather/forecast.html" target="OlivLD">Several GRIB-based sites</a></li>
    </ul>
  </div>
  <hr/>
  <i>&copy; Passe-Coque, 2024</i>
  </body>
</html>

