<!DOCTYPE html>
<!--
 ! WiP
 +-->
<html>
  <head>
    <title>Passe-Coque</title>
    <link rel="icon" type="image/png" href="../logos/LOGO_PC_no_txt.png">
    <link rel="stylesheet" href="../passe-coque.css" type="text/css" id="styleLink">
    <style type="text/css">

    </style>
  </head>
  <body>
    <h1>Publication d'almanachs</h1>
    <?php
/**
 * What's wrong with a comment ?
 * @author Zebulon
 */
try {
  // echo "From PHP.<br/>" . PHP_EOL;

  // OVH does not like this type in class declaration...
  class EtTaSoeur {
    public /* int */ $counter;
  }
} catch (Throwable $plaf) {
  echo "[Captured Throwable (top level) for index.php : " . $plaf . "] " . PHP_EOL;
}
  ?>
  <div>
    <h2>&Eacute;ph&eacute;m&eacute;rides astronomiques</h2>
    <ul>
      <li><a href="./astro.php/almanac.publisher.html" target="_blank">Celestial Almanac Publisher / Publication d'&eacute;ph&eacute;m&eacute;rides astronomiques</a></li>
    </ul>

    <h2>Almanachs de mar&eacute;e</h2>
    <ul>
      <li>
        <a href="./tides.es6/leaflet.tide.stations.html" target="_blank">&Agrave; partir d'une carte / From a chart.</a><br/>
        Choisissez la sation dont vous voulez l'almanach sur une carte g&eacute;ographique (ceux qui savent cliquer avec une souris vont y arriver).<br/>
        Choose the station you want the almanac of on a chart (if you can click, you can do it).
      </li>
      <li>
        <a href="./tides.php/tide.publisher/tide.publisher.101.php?lang=FR" target="_blank">Interface texte, en fran&ccedil;ais.</a><br/>
        Choisissez la station par son nom (ou une partie de son nom).
      </li>
      <li>
        <a href="./tides.php/tide.publisher/tide.publisher.101.php?lang=EN" target="_blank">Text interface, in English.</a><br/>
        Choose the station by name (or a part of it).
      </li>
    </ul>

    <h2>Meteo (en travaux)</h2>
    <ul>
      <li><a href="./weather/internet.faxes.NAtl.colors.html" target="_blank">Nort Altantic faxes, real time, B&W and colored versions.</a></li>

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

