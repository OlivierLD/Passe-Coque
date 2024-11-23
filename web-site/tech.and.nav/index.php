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
    </ul>
  </div>
  <hr/>
  <i>&copy; Passe-Coque, 2024</i>
  </body>
</html>

