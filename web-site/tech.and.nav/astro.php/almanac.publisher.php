<?php
// Celestial Almanac publication
// Wow.

try {
    // phpinfo();
    include __DIR__ . '/celestial.computer/autoload.php';

    $VERBOSE = false;

    $phpVersion = (int)phpversion()[0];
    if ($phpVersion < 7) {
        echo("PHP Version is " . phpversion() . "... This might be too low.");
    }

    function decimalHoursToHMS(float $decHours) : string {
		$hours = floor($decHours);
		$min = ($decHours - $hours) * 60;
		$sec = ($min - floor($min)) * 60;
		return sprintf("%02d:%02d:%06.03f", $hours, $min, $sec);
    }

    /*
     * This is a layer on top of the AstroComputer
     * 
     * We are going to produce a JSON structure (from AstroComputer), that is then going to be fetched
     * from some ES6 code, that will turn it into (printable) HTML...
     *
     * Astro Symbols: https://www.w3schools.com/charsets/ref_utf_symbols.asp

    ☉	9737	2609	SUN
    ♀	9792	2640	FEMALE SIGN (and Venus)
    ♁	9793	2641	EARTH
    ♂	9794	2642	MALE SIGN (and Mars)
    ♃	9795	2643	JUPITER
    ♄	9796	2644	SATURN

    */

    function oneDayAlmanac(bool $verbose, DateTime $date, $withStars) : string {
        $starCatalog = null;

        try {

            // Current dateTime
            $year = (int)date("Y");
            $month = (int)date("m");
            $day = (int)date("d");
            $hours = (int)date("H");
            $minutes = (int)date("i");
            $seconds = (int)date("s");

            $nbDaysThisMonth = TimeUtil::getNbDays($year, $month);
            // echo("This month, $nbDaysThisMonth days.<br/>" . PHP_EOL);

            $htmlContentSunMoonAries = "";
            $htmlContentPlanets = "";
            $htmlContentSemiDiamAndCo = "";
            $htmlContentStarsPage = "";
    
            $htmlContentSunMoonAries = ("<p>Calculated at $year:$month:$day $hours:$minutes:$seconds UTC</p>" . PHP_EOL);
            // date("l jS \of F Y h:i:s A"). See https://www.w3schools.com/php/func_date_date.asp

            $year = (int)date_format($date, "Y");
            $month = (int)date_format($date, "m");
            $day = (int)date_format($date, "d");

            /*
            https://www.w3schools.com/php/func_date_strftime.asp
            setlocale(LC_TIME, "fr_FR");
            strftime(" in French %d.%M.%Y and");
            */
            $theDate = date_create(sprintf("%04d-%02d-%02d", $year, $month, $day));
            $htmlContentSunMoonAries .= "<div class='sub-title'> " . date_format($theDate, "l F jS, Y") .  "</div>" . PHP_EOL;
            $htmlContentSunMoonAries .= "<table style='margin: auto;'>" . PHP_EOL;
            $htmlContentSunMoonAries .= "<tr>" . 
                               "<th></th><th colspan='5' style='font-size: 2rem;'>Sun &#9737;</th>" . 
                                          "<th colspan='6' style='font-size: 2rem;'>Moon &#9790;</th>" . "<th style='font-size: 2rem;'>Aries &gamma;</th><th></th>" . 
                          "</tr>" . PHP_EOL;
            $htmlContentSunMoonAries .= "<tr>" . 
                               "<th>UT</th><th>Sun GHA</th><th>&delta; GHA</th><th>Sun RA</th><th>Sun Decl</th><th>&delta; Decl</th>" . 
                                          "<th>Moon GHA</th><th>&delta; GHA</th><th>Moon RA</th><th>Moon Decl</th><th>&delta; Decl</th><th>hp (&pi;)</th>" . "<th>Aries GHA</th><th>UT</th>" . 
                          "</tr>" . PHP_EOL;
            $htmlContentSunMoonAries .= "<tr>" . 
                               "<th>TU</th><th>Soleil AHvo</th><th>&delta; AHvo</th><th>Soleil AHso</th><th>Soleil Decl</th><th>&delta; Decl</th>" . 
                                           "<th>Lune AHao</th><th>&delta; AHao</th><th>Lune AHso</th><th>Lune Decl</th><th>&delta; Decl</th><th>ph (&pi;)</th>" . "<th>Pt Vernal AHso</th><th>TU</th>" . 
                          "</tr>" . PHP_EOL;

            $htmlContentPlanets .= "<table style='margin: auto;'>" . PHP_EOL;
            $htmlContentPlanets .= "<tr><th rowspan='2'>UT</th><th colspan='3' style='font-size: 2rem;'>Venus &#9792;</th><th colspan='3' style='font-size: 2rem;'>Mars &#9794;</th><th colspan='3' style='font-size: 2rem;'>Jupiter &#9795;</th><th colspan='3' style='font-size: 2rem;'>Saturn &#9796;</th><th rowspan='2'>UT</th></tr>" . PHP_EOL;
            $htmlContentPlanets .= "<tr><th>GHA</th><th>SHA</th><th>Decl</th><th>GHA</th><th>SHA</th><th>Decl</th><th>GHA</th><th>SHA</th><th>Decl</th><th>GHA</th><th>SHA</th><th>Decl</th></tr>" . PHP_EOL;
    
            // Astro Computer. Roule ma poule.
            $ac = new AstroComputer(); 

            $prevGHASun = null;
            $prevGHAMoon = null;
            $prevDeclSun = null;
            $prevDeclMoon = null;

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
                $deltaDeclSun = "";
                if ($prevDeclSun != null) {
                    $diff = abs($ac->getSunDecl() - $prevDeclSun);
                    // while ($diff < 0) {
                    //     $diff += 360;
                    // }
                    $deltaDeclSun = sprintf("%1\$.4f&apos;", ($diff * 60));
                }
                $deltaGHAMoon = "";
                if ($prevGHAMoon != null) {
                    $diff = $ac->getMoonGHA() - $prevGHAMoon;
                    while ($diff < 0) {
                        $diff += 360;
                    }
                    $deltaGHAMoon = sprintf("%1\$.4f&deg;", $diff);
                }
                $deltaDeclMoon = "";
                if ($prevDeclMoon != null) {
                    $diff = abs($ac->getMoonDecl() - $prevDeclMoon);
                    // while ($diff < 0) {
                    //     $diff += 360;
                    // }
                    $deltaDeclMoon = sprintf("%1\$.4f&apos;", ($diff * 60));
                }
                $prevGHASun = $ac->getSunGHA();
                $prevDeclSun = $ac->getSunDecl();
                $prevDeclMoon = $ac->getMoonDecl();
                $prevGHAMoon = $ac->getMoonGHA();
                $prevDeclMoon = $ac->getMoonDecl();
                $htmlContentSunMoonAries .= 
                            ("<tr>" . 
                                 "<td>" . sprintf("%02d", $h) . "</td>" .
                                 "<td>" . Utils::decToSex($ac->getSunGHA()) .  "</td>" .
                                 "<td>" . $deltaGHASun .  "</td>" .
                                 "<td>" . Utils::decToSex($ac->getSunRA(), Utils::$NONE) .  "</td>" .
                                 "<td>" . Utils::decToSex($ac->getSunDecl(), Utils::$NS) .  "</td>" .
                                 "<td>" . $deltaDeclSun .  "</td>" .
                                 "<td>" . Utils::decToSex($ac->getMoonGHA()) .  "</td>" .
                                 "<td>" . $deltaGHAMoon .  "</td>" .
                                 "<td>" . Utils::decToSex($ac->getSunRA(), Utils::$NONE) .  "</td>" .
                                 "<td>" . Utils::decToSex($ac->getMoonDecl(), Utils::$NS) .  "</td>" .
                                 "<td>" . $deltaDeclMoon .  "</td>" .
                                 "<td>" . sprintf("%.04f&apos;", ($ac->getMoonHp() / 60)) .  "</td>" .
                                 "<td>" . Utils::decToSex($ac->getAriesGHA(), Utils::$NONE) . "</td>" .
                                 "<td>" . sprintf("%02d", $h) .  "</td>" .
                             "</tr>" . PHP_EOL); 

                $venusGHA = Utils::decToSex($context2->GHAvenus);
                $venusRA = Utils::decToSex($context2->RAvenus);
                $venusDecl = Utils::decToSex($context2->DECvenus, Utils::$NS);
    
                $marsGHA = Utils::decToSex($context2->GHAmars);
                $marsRA = Utils::decToSex($context2->RAmars);
                $marsDecl = Utils::decToSex($context2->DECmars, Utils::$NS);
    
                $jupiterGHA = Utils::decToSex($context2->GHAjupiter);
                $jupiterRA = Utils::decToSex($context2->RAjupiter);
                $jupiterDecl = Utils::decToSex($context2->DECjupiter, Utils::$NS);
    
                $saturnGHA = Utils::decToSex($context2->GHAsaturn);
                $saturnRA = Utils::decToSex($context2->RAsaturn);
                $saturnDecl = Utils::decToSex($context2->DECsaturn, Utils::$NS);
    
                $htmlContentPlanets .= ("<tr><td style='font-weight: bold;'>" . sprintf("%02d", $h) .  "</td><td>$venusGHA</td><td>$venusRA</td><td>$venusDecl</td>" . 
                                                                                "<td>$marsGHA</td><td>$marsRA</td><td>$marsDecl</td>" .
                                                                                "<td>$jupiterGHA</td><td>$jupiterRA</td><td>$jupiterDecl</td>" .
                                                                                "<td>$saturnGHA</td><td>$saturnRA</td><td>$saturnDecl</td>" .
                                                "<td style='font-weight: bold;'>" . sprintf("%02d", $h) .  "</td></tr>");

                if ($h === 12) {
                    $semiDiamSun = sprintf("%.04f", ($context2->SDsun / 60));
                    $sunHP = sprintf("%.04f", ($context2->HPsun / 60)); 
                    $semiDiamMoon = sprintf("%.04f", ($context2->SDmoon / 60)); 
                    // var_dump($ac->getMoonPhase());
                    $moonPhaseAngle = $ac->getMoonPhase()->phase;                      // TODO Fix that
                    $moonPhase = sprintf("%.02f %%, ", $context2->k_moon) . $ac->getMoonPhaseStr(); // sprintf("", ) "${calcResult.moon.illum.toFixed(2)}% ${calcResult.moon.phase.phase}";
                    $phaseIndex = floor($moonPhaseAngle / (360 / 28.5)) + 1;
                    if ($phaseIndex > 28) {
                        $phaseIndex = 28;
                    }
                    $phaseImageName = sprintf("./moon/phase%02d.gif", $phaseIndex);

                    $tPassSun = decimalHoursToHMS(12 - ($context2->EoT / 60));
                    $moonAge = ($moonPhaseAngle * 28 / 360);

                    // $htmlContentSemiDiamAndCo .= "<table>";
                    $htmlContentSemiDiamAndCo .= (
                        "<tr><td colspan='14'>&nbsp;</td></tr>" .
                        "<tr>" .
                            "<td rowspan='3'></td>" .
                            "<td colspan='2'>&frac12;&nbsp;&#x2300 " . $semiDiamSun . "'</td>" . 
                            "<td colspan='3'>hp (&pi;) " . $sunHP . "'</td>" . 
                            "<td colspan='3'>&frac12;&nbsp;&#x2300 " . $semiDiamMoon . "'</td>" . 
                            "<td colspan='2'>" . $moonPhase . "</td>" . 
                            "<td rowspan='3' colspan='2'><img src='" . $phaseImageName . "' alt='" . sprintf("%.02f", $moonPhaseAngle)  . "' title='" . sprintf("%.02f", $moonPhaseAngle) . "'&deg'}'>" .
                            "<td rowspan='3'></td>" . 
                        "</tr>" .
                        "<tr><td colspan='5'>EoT at 12:00 UTC : " . $context2->EoT . " (in minutes)</td><td colspan='5'>Phase at 12:00 UTC : " . sprintf("%.02f", $moonPhaseAngle) . "&deg;</td></tr>" .
                        "<tr><td colspan='5'>Meridian Pass. Time : " . $tPassSun . "</td><td colspan='5'>Age : " . sprintf("%.01f", $moonAge) . " day(s)</td></tr>" 
                    );
                }

                if ($withStars && $h === 0) {
                    if ($starCatalog === null) {
                        $starCatalog = Star::getCatalog(); // from STAR_CATALOG...
                    }

                    $htmlContentStarsPage .= "<div class='sub-title'> " . date_format($theDate, "l F jS, Y") .  "</div>" . PHP_EOL;
                    $htmlContentStarsPage .= "<div style='display: grid; grid-template-columns: auto auto;'>";
                    $htmlContentStarsPage .= "<div>";
                    $htmlContentStarsPage .= ("Stars at 0000 U.T. (GHA(star) = SHA(star) + GHA(Aries))");
    
                    $htmlContentStarsPage .= (
                        "<br/>" .
                        "<table>" .
                        "<tr><th>Name</th><th>SHA</th><th>Dec</th></tr>"
                    );
    
                    $ariesGHA = $ac->getAriesGHA();
                    $starArray = $ac->getStars();
    
                    $nbStars = count($starArray);
                    // $htmlContentStarsPage .= "<br/>Found $nbStars stars.<br/>";
    
                    for ($j=0; $j<$nbStars; $j++) {
                        $star = Star::getStar($starArray[$j]->name);
                        if ($star === null) {
                            echo("Star " . $starArray[$j][0] . " not found in catalog");
                        } else {
                            // console.log("Found ${starArray[i].name}: ${JSON.stringify(star)}");
                            $starSHA = $starArray[$j]->GHAStar - $ariesGHA;
                            while ($starSHA < 0) {
                                $starSHA += 360;
                            }
                            $starDec = $starArray[$j]->DECStar;
                            $htmlContentStarsPage .= (
                                "<tr><td" . (($starDec < 0) ? " style='background: silver;'" : "") ."><b>" . $starArray[$j]->name . "</b>, " . $star->getConstellation() . "</td>" . 
                                    "<td>" . Utils::decToSex($starSHA) . "</td>" . 
                                    "<td>" . Utils::decToSex($starDec, Utils::$NS) . "</td></tr>"
                            );
                        }
                    }
                    $htmlContentStarsPage .= "</table>";
                    $htmlContentStarsPage .= "</div>";
    
                    $htmlContentStarsPage .= "<div>";
                    $htmlContentStarsPage .= "<b>Calculated at 00:00:00 U.T.</b>";
    
                    $htmlContentStarsPage .= "<table>";
    
                    $htmlContentStarsPage .= "<tr><td>Mean Obliquity of Ecliptic</td><td>" . $context2->eps0 . "&deg;</td></tr>";                    
                    $htmlContentStarsPage .= "<tr><td>True Obliquity of Ecliptic</td><td>" . $context2->eps . "&deg;</td></tr>";   
                    $htmlContentStarsPage .= "<tr><td>Delta &psi;</td><td>" . round(3600000 * $context2->delta_psi) / 1000 . "&quot;</td></tr>";                    
                    $htmlContentStarsPage .= "<tr><td>Delta &epsilon;</td><td> " . round(3600000 * $context2->delta_eps) / 1000 . "&quot;</td></tr>";                    
                    $htmlContentStarsPage .= "<tr><td>Julian Date</td><td>" . round(1000000 * $context2->JD) / 1000000 . "</td></tr>";                    
                    $htmlContentStarsPage .= "<tr><td>Julian Ephemeris Date</td><td>" . round(1000000 * $context2->JDE) / 1000000 . "</td></tr>";                    
    
                    $htmlContentStarsPage .= "</table>";
    
                    $htmlContentStarsPage .= "</div>";
    
                    $htmlContentStarsPage .= "</div>";

                    // echo ("End of Stars<br/>");
    
                }
                // echo ("End of hour $h.<br/>". PHP_EOL);
            }
            $htmlContentSunMoonAries .= $htmlContentSemiDiamAndCo;
            $htmlContentSunMoonAries .= ("</table>" . PHP_EOL);

            $htmlContentPlanets .=      ("</table>" . PHP_EOL);

            return ($htmlContentSunMoonAries . "<br/>" . 
                    $htmlContentPlanets . "<hr/>" . 
                    ($withStars ? $htmlContentStarsPage . "<hr/>" : "")
                   );
        } catch (Throwable $e) {
            if ($verbose) {
                echo "[Captured Throwable (2) for oneDayAlmanac : " . $e->getMessage() . "] " . PHP_EOL;
            }
            throw $e;
        }
        // return null;
    }

    function plublishAlmanac(bool $verbose, string $from, string $to, bool $withStars) : string {
        $finalOutput = "";
        $startDate = date_create($from); // Format "2024-11-25"
        $endDate = date_create($to);
        $currentDate = $startDate;
        while ($currentDate <= $endDate) {
            // $finalOutput .= "Calculating Almanac for " . date_format($currentDate,"Y/m/d") . "<br/>";
            $finalOutput .= oneDayAlmanac($verbose, $currentDate, $withStars) . "<br/>";
            // See this: https://www.w3schools.com/php/func_date_date_add.asp
            // date_add($date, date_interval_create_from_date_string("1 day"));
            date_add($currentDate, date_interval_create_from_date_string("1 day"));
        }
        return $finalOutput;
    }

    $option = "none";
    $fromDate = "-";
    $toDate = "-";
    $withStars = "false";

    // Whatever you want it to be 
    if (isset($_GET['option'])) {
        $option = $_GET['option'];
    }
    if (isset($_GET['from'])) {
        $fromDate = $_GET['from'];
    }
    if (isset($_GET['to'])) {
        $toDate = $_GET['to'];
    }
    if (isset($_GET['stars'])) {
        $withStars = $_GET['stars'];
    }


    if ($option == "1") {
        try {
            $data = plublishAlmanac($VERBOSE, $fromDate, $toDate, $withStars == 'true'); // oneDayAlmanac($VERBOSE);
            header('Content-Type: text/html; charset=utf-8');
            echo $data;
            // http_response_code(200);
        } catch (Throwable $e) {
            echo "[Captured Throwable (5) for celestial.computer.php : " . $e . "] " . PHP_EOL;
        }
    } else { 
        echo "Option is [$option], not supported.<br/>";
    }

} catch (Throwable $plaf) {
    echo "[Captured Throwable (big) for celestial.computer.php : " . $plaf . "] " . PHP_EOL;
}
?>
