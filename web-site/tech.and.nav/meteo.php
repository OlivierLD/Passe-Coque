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
    <title>Passe-Coque - Meteo</title>
    <link rel="icon" type="image/png" href="../logos/LOGO_PC_no_txt.png">
    <link rel="stylesheet" href="../passe-coque.css" type="text/css" id="styleLink">
    <style type="text/css">

    .transp-box {
        margin: 0px;
        padding: 10px;
        background-color: #ffffff;
        /* border: 1px solid black;*/
        opacity: 0.55;
    }

    </style>
  </head>
  <body style="color: black; background-image: url(/images/weather.jpg); background-size: 100%; background-repeat: no-repeat;">
    <div class="transp-box">
      <h1><?php echo( $lang == 'FR' ? "M&eacute;t&eacute;o (Faxes, GRIBs, etc...)" : "Weather Forecasts (Faxes, GRIBs, etc...)"); ?></h1>
      <hr/>
      <ul>
          <li>
              <?php echo( $lang == 'FR' ? "Un barographe en temps r&eacute;el est disponible " : "A barograph in real time is available "); ?>
              <a href="/tech.and.nav/weather.php/web/instant.html" target="_blank"><?php echo( $lang == 'FR' ? "ici" : "here" ) ?></a>.<br/>
              <?php echo( $lang == 'FR' ? "Les capteurs sont install&eacute;s &agrave; Belz (F 56550)." : "Sensors are located in Belz (F 56550).") ?><br/>
          </li>
      </ul>

      <ul>
        <li><a href="./weather/internet.faxes.NAtl.colors.html" target="_blank"><?php echo( $lang == 'FR' ? "Faxes de l'Atlantique Nord, temps r&eacute;el, en couleurs." : "North Altantic faxes, real time, Colored version."); ?></a></li>
        <li><a href="https://olivierld.github.io/web.stuff/boat.stuff/weather/forecast.html" target="OlivLD"><?php echo( $lang == 'FR' ? "Plusieurs sites affichant des GRIBs." : "Several GRIB web sites."); ?></a></li>
      </ul>
      <hr/>
      <ul>
        <li><a href="https://olivierld.github.io/web.stuff/boat.stuff/weather/internet.faxes.NAtl.html" target="OlivLD"><?php echo( $lang == 'FR' ? "Faxes Atlantique Nord (en temps r&eacute;el)" : "Faxes, North Atlantic (real time)"); ?></a></li>
        <li><a href="https://olivierld.github.io/web.stuff/boat.stuff/weather/internet.faxes.NAtl.2.html" target="OlivLD"><?php echo( $lang == 'FR' ? "Faxes Atlantique Nord, v2." : "Several Atlantic faxes (v2), reworked on the client side..."); ?></a></li>
        <li><a href="https://olivierld.github.io/web.stuff/boat.stuff/weather/internet.faxes.NPac.html" target="OlivLD"><?php echo( $lang == 'FR' ? "Faxes, Pacifique Nord (en temps r&eacute;el)" : "Faxes, North Pacific (real time)"); ?></a></li>
      </ul>
      <hr/>
      <ul>
        <li><a href="https://www.meteo-marine.com/consulter/atlantique/" target="_bank"><?php echo( $lang == 'FR' ? "M&eacute;t&eacute;o Marine Atlantique" : "Atlantic Marine Weather (in French)"); ?></a></li>
        <li><a href="https://portail.ping-info-nautique.fr/" target="_blank">PING</a></li>
        <li><a href="https://ocean.weather.gov/Atl_tab.php" target="NOAA">Ocean Prediciton Center</a></li>
      </ul>
      <hr/>
      <?php echo( $lang == 'FR' ? "Et plus bient&ocirc;t..." : "And more soon..."); ?>
    </div>

    <hr/>
    <i style="color: black;">&copy; Passe-Coque, 2024</i>
  </body>
</html>

