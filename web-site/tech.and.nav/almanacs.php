<!DOCTYPE html>
<!--
 ! WiP. Celestial and Tidal Almanacs
 +-->
 <?php

$lang = 'FR';
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
}

 ?>
<html>
  <head>
    <title><?php echo( $lang == 'FR' ? "Almanachs Passe-Coque" : "Passe-Coque Almanacs"); ?></title>
    <link rel="icon" type="image/png" href="../logos/LOGO_PC_no_txt.png">
    <link rel="stylesheet" href="../passe-coque.css" type="text/css" id="styleLink">
    <style type="text/css">

    .bg-doc {
      color: white; 
      background-color: black;
      background-image: url(/images/book-printing.webp); 
      background-size: 70%; 
      background-position-x: center; 
      background-repeat: no-repeat;
    }

    .transp-box {
        margin: 0px;
        padding: 10px;
        background-color: #ffffff;
        /* border: 1px solid black;*/
        opacity: 0.75;
    }

    </style>
  </head>
  <body class="bg-doc">
    <div class="transp-box">
      <h1><?php echo( $lang == 'FR' ? "Publication d'almanachs" : "Almanac publication"); ?></h1>
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

    <?php 
        if ($lang == 'FR') {
    ?>
          <p style="margin: 5px; padding: 5px; border: 1px solid silver; border-radius: 5px;">
              Les documents qui suivent sont con&ccedil;us pour &ecirc;tre visualis&eacute;s dans un navigateur web, <i>puis</i> imprim&eacute;s &agrave; partir de celui-ci (on peut aussi g&eacute;n&eacute;rer un pdf).
              La version imprim&eacute;e comporte des sauts de page et autres formattages qui ne seront pas visibles &agrave; partir du navigateur.<br/>
              Lors de l'impression, vous aurez la possibilit&eacute; - &agrave; partir des boites de dialogue du navigateur - de faire appara&icirc;tre ou pas
              des donn&eacute;es comme les en-t&ecirc;tes et bas de page, num&eacute;ros de page, etc, et m&ecirc;me l'&eacute;chelle.<br/>
              Pour les d&eacute;tails sur la fa&ccedil;on de proc&eacute;der, voyez <a href="./how-to/how.to.print.FR.html" target="_blank">ici</a>.
          </p>
    <?php 
        } else {  
    ?>  
          <p style="margin: 5px; padding: 5px; border: 1px solid silver; border-radius: 5px;">
              The following documents are designed to be visualized from a web browser, <i>and then</i> printed from it (a pdf can also be generated from the page).
              The printed version will have page breaks and other formattings that will not be displayed from the web browser.<br/>
              When printing, you will have the possibility - from the dialog showing up in the browser - to hide or show different 
              data like headers, footers, page numbers, etc, and even scale.<br/>
              For details, see <a href="./how-to/how.to.print.EN.html" target="_blank">here</a>.
          </p>
    <?php
        }
    ?>

  <div>
      <h2><?php echo( $lang == 'FR' ? "&Eacute;ph&eacute;m&eacute;rides astronomiques" : "Celestial Almanacs"); ?></h2>
      <ul>
        <?php 
          if ($lang == 'FR') {
        ?>
        <li><a href="./astro.php/almanac.publisher.html" target="_blank">Publication d'&eacute;ph&eacute;m&eacute;rides astronomiques</a><img style="vertical-align: middle;" src="./astro.php/sextant.gif"></li>
        <?php 
          } else {  
        ?>  
        <li><a href="./astro.php/almanac.publisher.html" target="_blank">Celestial Almanac Publisher</a><img style="vertical-align: middle;" src="./astro.php/sextant.gif"></li>
        <?php
          }
        ?>
      </ul>

      <h2><?php echo( $lang == 'FR' ? "Almanachs de mar&eacute;e" : "Tidal Almanacs"); ?></h2>
      <ul>
        <li>
          <?php
          if ($lang == 'FR') {
          ?>
            <a href="./tides.es6/leaflet.tide.stations.html" target="_blank">&Agrave; partir d'une carte.</a><br/>
            Choisissez la sation dont vous voulez l'almanach sur une carte g&eacute;ographique (ceux qui savent cliquer avec une souris vont y arriver).
          <?php
          } else {
          ?>
            <a href="./tides.es6/leaflet.tide.stations.html" target="_blank">From a chart.</a><br/>
            Choose the station you want the almanac of on a chart (if you can click, you can do it).
          <?php  
          }
          ?>
        </li>
        <li>
        <?php
          if ($lang == 'FR') {
          ?>
            <a href="./tides.php/tide.publisher/tide.publisher.101.php?lang=FR" target="_blank">Interface texte, en fran&ccedil;ais.</a><br/>
            Choisissez la station par son nom (ou une partie de son nom).
          <?php
          } else {
          ?>
            <a href="./tides.php/tide.publisher/tide.publisher.101.php?lang=EN" target="_blank">Text interface, in English.</a><br/>
            Choose the station by name (or a part of it).
          <?php  
          }
          ?>
        </li>
      </ul>
    </div>
  </div>
  <hr/>
  <i>&copy; Passe-Coque, 2024</i>
  </body>
</html>

