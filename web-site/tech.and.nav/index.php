<!DOCTYPE html>
<!--
 ! WiP
 +-->
<html>
  <head>
    <title>Passe-Coque</title>
    <link rel="stylesheet" href="../passe-coque.css" type="text/css" id="styleLink">
  </head>
  <body>
    <h1>Index Passe-Coque, navigation, calculs, techniques, etc</h1>
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
      <li><a href="./astro.php/almanac.publisher.html" target="_new">Almanac Publisher (WiP)</a></li>
      <li>
        Tides in PHP
        <ul>
          <li><a href="./tides.php/tide.sample.php" target="_new">Tide Workbench (WiP)</a></li>
          <li><a href="./tides.php/tide.publisher/tide.publisher.101.php" target="_new">Tide Almanac for Port-Tudy (currtent month)</a></li>
        </ul>
      </li>
    </ul>
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

