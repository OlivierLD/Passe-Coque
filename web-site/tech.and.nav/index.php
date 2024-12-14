<!DOCTYPE html>
<!--
 ! WiP
 +-->
<html>
  <head>
    <title>Passe-Coque</title>
    <!--link rel="stylesheet" href="../passe-coque.css" type="text/css" id="styleLink"-->
    <style type="text/css">
		* {
			font-family: 'Courier New', Courier, monospace;
		}
        td {
            border: 1px solid black;
            border-radius: 5px;
            padding: 5px;
        }
    </style>
  </head>
  <body>
    <h1>Index Passe-Coque, navigation, calculs, techniques, etc</h1>
    <p style="font-style: italic;">
      This is a workbench. Sexy UI is <b>not</b> in the scope...
    </p>
    <?php
/**
 * What's wrong with a comment ?
 * @author Zebulon
 */
try {
  echo "From PHP.<br/>" . PHP_EOL;

  // OVH does not like this type in class declaration...
  class EtTaSoeur {
    public /* int */ $counter;
  }
} catch (Throwable $plaf) {
  echo "[Captured Throwable (top level) for index.php : " . $plaf . "] " . PHP_EOL;
}
  ?>
  <div>
    <h2>Misc links and stuff</h2>
    <ul>
      <li><a href="./astro.php/index.01.html" target="_new">Astro PHP, first POC</a></li>
      <li><a href="./astro.php/almanac.publisher.html" target="_new">Celestial Almanac Publisher (WiP)</a> <i>Close to prod</i></li>
      <li>
        Tides in PHP
        <ul>
          <li><a href="./tides.php/tide.sample.php" target="_new">Tide Workbench (WiP)</a></li>
          <li><a href="./tides.php/tide.publisher/tide.publisher.101.php" target="_new">Tide Almanac Publisher (WiP).</a> <i>Close to prod</i></li>
        </ul>
      </li>
      <li>
        For later (for inspiration)
        <ul>
          <li><a href="https://olivierld.github.io/web.stuff/tides/Basic.04.html" target="_new">Basic test, with Graph</a></li>
          <li><a href="https://olivierld.github.io/web.stuff/tides/earth.tide.stations.html" target="_new">Earth Tide Stations</a></li>
          <li><a href="https://olivierld.github.io/web.stuff/tides/leaflet.tide.stations.html" target="_new">Leaflet Tide Stations</a></li>
        </ul>
      </li>
      <li>
        Meteo &amp; Co
        <ul>
          <li><a href="./weather/internet.faxes.NAtl.colors.html" target="_blank">Nort Altantic faxes, real time, B&W and colored versions.</a></li>
          <li><a href="https://olivierld.github.io/web.stuff/boat.stuff/weather/internet.faxes.NAtl.html" target="OlivLD">Faxes, North Atlantic (real time)</a></li>
          <li><a href="https://olivierld.github.io/web.stuff/boat.stuff/weather/internet.faxes.NAtl.2.html" target="OlivLD">Several Atlantic faxes (v2), reworked on the client side...</a></li>
          <li><a href="https://olivierld.github.io/web.stuff/boat.stuff/weather/internet.faxes.NPac.html" target="OlivLD">Faxes, North Pacific (real time)</a></li>
          <li><a href="https://olivierld.github.io/web.stuff/boat.stuff/weather/forecast.html" target="OlivLD">Several GRIB-based sites</a></li>
        </ul>
      </li>
    </ul>

    <h2>How to print your documents</h2>
    An example, with the tide almanac publisher (above):<br/>
    You will see:
    <ul>
      <li>How to produce the tide almanac you want</li>
      <li>
        How to print it (or save it as a pdf) with the right size or so (from a right-click in the browser)...
      </li>
    </ul>
    <video style="border: 1px solid black;" width="80%" height="auto" controls>
        <source src="./how-to.print.mov" type="video/mp4">
        <!--source src="/videos/Passe&#8209;Coque-SO.mp4" type="video/mp4"-->
        <!--source src="movie.ogg" type="video/ogg"-->
      Your browser does not support the video tag.
    </video>

    <h2>Documents</h2>
    <ul>
      <li><a href="almanacs/almanac.2025.pdf" target="docs">&Eacute;ph&eacute;m&eacute;rides Nautiques</a> pour 2025</li>
      <li><a href="almanacs/lunar.2025.pdf" target="docs">Distances Lunaires</a> pour 2025</li>
      <li><a href="almanacs/perpetual.2025.2030.pdf" target="docs">Almanach "perp&eacute;tuel"</a> pour 2025-2030</li>
    </ul>
  </div>
  <hr/>
  <i>&copy; Passe-Coque, 2024</i>
  </body>
</html>

