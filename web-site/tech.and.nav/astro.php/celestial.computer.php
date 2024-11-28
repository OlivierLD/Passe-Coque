<?php
//
// This is a first test, validating the architecture and the concept.
//

try {
    // phpinfo();
    include __DIR__ . '/celestial.computer/autoload.php';

    $VERBOSE = false;

    $phpVersion = (int)phpversion()[0];
    if ($phpVersion < 7) {
        echo("PHP Version is " . phpversion() . "... This might be too low.");
    }

    /*
    * This is a layer on top of the AstroComputer
    * 
    * We are going to produce a JSON structure, that is then going to be fetched
    * from some ES6 code...
    *
    * For PHP UTC Dates, see https://www.w3schools.com/php/func_date_gmdate.asp
    */

    class Container {
        public $deltaT;
        public $calcDate;
        public $ctx;
        public $oneStar;
        public $starGHA;
        public $starCatalog;
        public $stars;
    }

    function doYourJob(bool $verbose) : string {

        try {
            // Quick test
            try {
                // Core test
                if (false) {
                    $dummyGHA = 301.8575336115582;
                    // Expect 301° 51' 27", 301°51.45', 301°51'27.1080"
                    echo("Raw: " . $dummyGHA . ", Fmt: ". Utils::decToSex($dummyGHA, Utils::$NONE) );
                }

                // Current UTC dateTime
                $year = (int)gmdate("Y");
                $month = (int)gmdate("m");
                $day = (int)gmdate("d");
                $hours = (int)gmdate("H");
                $minutes = (int)gmdate("i");
                $seconds = (int)gmdate("s");

                $container = new Container();

                // Astro Computer basic test
                $ac = new AstroComputer(); 
                // $ac->setDateTime($year, $month, $day, $hours, $minutes, $seconds);
                $ac->calculate($year, $month, $day, $hours, $minutes, $seconds, true, true);
                $context2 = $ac->getContext();
                // echo ("From calculate: EoT:" . $context2->EoT . " ");

                $ALDEBARAN = "Aldebaran";
                $star = Star::getStar($ALDEBARAN);
                // assertTrue(String.format("%s not found in Star Catalog", ALDEBARAN), star != null);

                $ac->starPos($ALDEBARAN);
                $starGHA = $ac->getStarGHA($ALDEBARAN);

                $container->ctx = $context2; // The full Context
                $container->deltaT = $ac->getDeltaT();
                $container->calcDate = "$year:$month:$day $hours:$minutes:$seconds UTC";
                $container->oneStar = Star::getStar("Zubenelgenubi");
                $container->starCatalog = Star::getCatalog();
                $container->starGHA = $starGHA;
                $container->stars = $ac->getStars();

                $jsonData = json_encode($container); // , JSON_FORCE_OBJECT);
                // End of Basic Test

                if ($verbose) {
                    echo("Invoking SightReductionUtil...<br/>");
                }
                $sru = new SightReductionUtil(
                    $ac->getSunGHA(),
                    $ac->getSunDecl(),
                    43.0,
                    -3); // 0, 0, 0, 0);
                $sru->calculate();
                if ($verbose) {
                    echo("He:" . Utils::decToSex($sru->getHe()) . ", Z:" . sprintf("%f&deg;", $sru->getZ()) . "<br/>");
                    echo("Done invoking SightReductionUtil.<br/>");
                }
            } catch (Throwable $e) {
                if ($verbose) {
                    echo "[ Captured Throwable (2) for doYourJob : " . $e->getMessage() . "] " . PHP_EOL;
                }
                throw $e;
            }
        
            // Final one
            return $jsonData;

        } catch (Throwable $e) {
            echo "[ Captured Throwable (1) for doYourJob : " . $e->getMessage() . "] " . PHP_EOL;
            throw $e;
        }
        return null;
    }

    function moreSpecific_1(bool $verbose) : string {
        // Sun current status
        try {
            // Current dateTime UTC
            $year = (int)gmdate("Y");
            $month = (int)gmdate("m");
            $day = (int)gmdate("d");
            $hours = (int)gmdate("H");
            $minutes = (int)gmdate("i");
            $seconds = (int)gmdate("s");

            $container = "<h4>Sun current status</h4>" . PHP_EOL;
            $container .= "<ul>" . PHP_EOL;

            // Astro Computer basic test
            $before = microtime(true); // See https://www.w3schools.com/php/func_date_microtime.asp
            $ac = new AstroComputer(); 
            // $ac->setDateTime($year, $month, $day, $hours, $minutes, $seconds);
            $ac->calculate($year, $month, $day, $hours, $minutes, $seconds, true);
            $after = microtime(true);

            $timeDiff = ($after - $before) * 1000;
            $container .= ("<li>Calculated in " . sprintf("%f ms", $timeDiff)  .  " (" . sprintf("From %f to %f", $before, $after)  . ")</li>" . PHP_EOL);

            $context2 = $ac->getContext();
            // echo ("From calculate: EoT:" . $context2->EoT . " ");

            $container .= ("<li>Calculated at $year:$month:$day $hours:$minutes:$seconds UTC</li>" . PHP_EOL);
            $container .= ("<li>DeltaT: " . $ac->getDeltaT() . " s</li>" . PHP_EOL);
            // $container .= ("<li>Raw - Sun GHA: " . Utils::decToSex($context2->GHAsun, Utils::$NONE) . ", Sun Dec: " . Utils::decToSex($context2->DECsun, Utils::$NS) . "</li>" . PHP_EOL);
            $container .= ("<li>Raw - Sun GHA: " . $context2->GHAsun . ", Sun Dec: " . $context2->DECsun . "</li>" . PHP_EOL);
            $container .= ("<li>Fmt - Sun GHA: " . Utils::decToSex($ac->getSunGHA(), Utils::$NONE) . ", Sun Dec: " . Utils::decToSex($ac->getSunDecl(), Utils::$NS) . "</li>" . PHP_EOL);

            if ($verbose) {
                echo("Invoking SightReductionUtil...<br/>");
            }
            $lat = 43.677667; $lng = -3.135667;
            $sru = new SightReductionUtil(
                $ac->getSunGHA(),
                $ac->getSunDecl(),
                $lat,
                $lng);
            $sru->calculate();
            if ($verbose) {
                echo("He:" . Utils::decToSex($sru->getHe()) . ", Z:" . sprintf("%f&deg;", $sru->getZ()) . "<br/>");
                echo("Done invoking SightReductionUtil.<br/>");
            }
            $container .= ("<li>From Pos: " . Utils::decToSex($lat, Utils::$NS) . " / " . Utils::decToSex($lng, Utils::$EW) . "</li>" . PHP_EOL);
            $container .= ("<li>Sun He:" . Utils::decToSex($sru->getHe()) . ", Sun Z:" . sprintf("%f&deg;", $sru->getZ()) . "</li>" . PHP_EOL);

            $container .= ("</ul>" . PHP_EOL);
            $container .= ("<hr/>" . PHP_EOL);

        } catch (Throwable $e) {
            if ($verbose) {
                echo "[ Captured Throwable (2) for doYourJob : " . $e->getMessage() . "] " . PHP_EOL;
            }
            throw $e;
        }

        // Final one
        return $container;
    }

    function oneDayAlmanac(bool $verbose) : string {
        try {
            // See this: https://www.w3schools.com/php/func_date_date_add.asp
            // date_add($date, date_interval_create_from_date_string("1 day"));

            // Current UTC dateTime
            $year = (int)gmdate("Y");
            $month = (int)gmdate("m");
            $day = (int)gmdate("d");
            $hours = (int)gmdate("H");
            $minutes = (int)gmdate("i");
            $seconds = (int)gmdate("s");

            $nbDaysThisMonth = TimeUtil::getNbDays($year, $month);
            // echo("This month, $nbDaysThisMonth days.<br/>" . PHP_EOL);

            $htmlContentSunMoonAries = "";
            $htmlContentPlanets = "";
            $htmlContentSemiDiamAndCo = "";
            $htmlContentStarsPage = "";
    
            $htmlContentSunMoonAries = ("<p>Calculated at $year:$month:$day $hours:$minutes:$seconds UTC</p>" . PHP_EOL);
            // date("l jS \of F Y h:i:s A"). See https://www.w3schools.com/php/func_date_date.asp
            $htmlContentSunMoonAries .= "<div class='sub-title'>Celestial Almanac for " . gmdate("l F jS, Y") .  "</div>" . PHP_EOL;
            $htmlContentSunMoonAries .= "<table>" . PHP_EOL;
            $htmlContentSunMoonAries .= "<tr>" . 
                               "<th></th><th colspan='4'>Sun</th>" . 
                                          "<th colspan='4'>Moon</th>" . "<th>Aries</th><th></th>" . 
                          "</tr>" . PHP_EOL;
            $htmlContentSunMoonAries .= "<tr>" . 
                               "<th>UT</th><th>Sun GHA</th><th>&delta; GHA</th><th>Sun RA</th><th>Sun Decl</th>" . 
                                          "<th>Moon GHA</th><th>&delta; GHA</th><th>Moon RA</th><th>Moon Decl</th>" . "<th>Aries GHA</th><th>UT</th>" . 
                          "</tr>" . PHP_EOL;
            $htmlContentSunMoonAries .= "<tr>" . 
                               "<th>TU</th><th>Soleil AHvo</th><th>&delta; AHvo</th><th>Soleil AHso</th><th>Soleil Decl</th>" . 
                                           "<th>Lune AHao</th><th>&delta; AHao</th><th>Lune AHso</th><th>Lune Decl</th>" . "<th>Pt Vernal AHso</th><th>TU</th>" . 
                          "</tr>" . PHP_EOL;

            // Astro Computer
            $ac = new AstroComputer(); 

            $prevGHASun = null;
            $prevGHAMoon = null;
            $withStars = true;

            for ($i=0; $i<24; $i++) {
                $h = $i;
                $ac->calculate($year, $month, $day, $h, 0, 0, true, $withStars);
                $context2 = $ac->getContext();
                $deltaGHASun = "";
                if ($prevGHASun != null) {
                    $diff = $ac->getSunGHA() - $prevGHASun;
                    while ($diff < 0) {
                        $diff += 360;
                    }
                    $deltaGHASun = sprintf("%1\$.4f&deg;", $diff);
                }
                $deltaGHAMoon = "";
                if ($prevGHAMoon != null) {
                    $diff = $ac->getMoonGHA() - $prevGHAMoon;
                    while ($diff < 0) {
                        $diff += 360;
                    }
                    $deltaGHAMoon = sprintf("%1\$.4f&deg;", $diff);
                }
                $prevGHASun = $ac->getSunGHA();
                $prevGHAMoon = $ac->getMoonGHA();
                $htmlContentSunMoonAries .= ("<tr><td>" . sprintf("%02d", $h) . 
                                "</td><td>" . Utils::decToSex($ac->getSunGHA()) . 
                                "</td><td>" . $deltaGHASun . 
                                "</td><td>" . Utils::decToSex($ac->getSunRA(), Utils::$NONE) . 
                                "</td><td>" . Utils::decToSex($ac->getSunDecl(), Utils::$NS) . 
                                "</td><td>" . Utils::decToSex($ac->getMoonGHA()) . 
                                "</td><td>" . $deltaGHAMoon . 
                                "</td><td>" . Utils::decToSex($ac->getSunRA(), Utils::$NONE) . 
                                "</td><td>" . Utils::decToSex($ac->getMoonDecl(), Utils::$NS) . 
                                "</td><td>" . Utils::decToSex($ac->getAriesGHA(), Utils::$NONE) .
                                "<td>" . sprintf("%02d", $h) . 
                                "</td></tr>" . PHP_EOL); 
            }
            // End of Test

            $htmlContentSunMoonAries .= ("</table>" . PHP_EOL);
            $htmlContentSunMoonAries .= ("<hr/>" . PHP_EOL);

            return $htmlContentSunMoonAries;

        } catch (Throwable $e) {
            if ($verbose) {
                echo "[ Captured Throwable (2) for doYourJob : " . $e->getMessage() . "] " . PHP_EOL;
            }
            throw $e;
        }
    }

    $option = "basic";
    // Whatever you want it to be 
    if (isset($_GET['option'])) {
        $option = $_GET['option'];
    }

    if ($option == "basic") {
        try {
            $data = doYourJob($VERBOSE);
            header('Content-Type: application/json; charset=utf-8');
            // echo json_encode($data); // This is for text (not json)
            echo $data;
            // http_response_code(200);
        } catch (Throwable $e) {
            echo "[Captured Throwable (3) for celestial.computer.php : " . $e . "] " . PHP_EOL;
        }
    } else if ($option == "1") {
        try {
            $data = moreSpecific_1($VERBOSE);
            header('Content-Type: text/html; charset=utf-8');
            echo $data;
            // http_response_code(200);
        } catch (Throwable $e) {
            echo "[Captured Throwable (4) for celestial.computer.php : " . $e . "] " . PHP_EOL;
        }
    } else if ($option == "2") {
        try {
            $data = oneDayAlmanac($VERBOSE);
            header('Content-Type: text/html; charset=utf-8');
            echo $data;
            // http_response_code(200);
        } catch (Throwable $e) {
            echo "[Captured Throwable (5) for celestial.computer.php : " . $e . "] " . PHP_EOL;
        }
    } else if ($option == "info") {
        phpinfo();
    } else { 
        echo "Option is [$option], not supported.<br/>";
    }

} catch (Throwable $plaf) {
    echo "[Captured Throwable (big) for celestial.computer.php : " . $plaf . "] " . PHP_EOL;
}
?>
