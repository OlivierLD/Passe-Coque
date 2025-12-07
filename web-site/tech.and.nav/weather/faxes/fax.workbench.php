<?php

// Inspired from https://www.geeksforgeeks.org/saving-an-image-from-url-in-php/

// North-West Atlantic: https://tgftp.nws.noaa.gov/fax/PYAA12.gif
// North-East Atlantic: https://tgftp.nws.noaa.gov/fax/PYAA11.gif
// North Atlantic 500mb: https://tgftp.nws.noaa.gov/fax/PPAA10.gif


$origin = "https://tgftp.nws.noaa.gov/fax/PYAA12.gif";
$destination = "surface.west.atl.gif";

echo("CURLOPT_HEADER:" . CURLOPT_HEADER . "<br/>" . PHP_EOL);
echo("CURLOPT_RETURNTRANSFER:" . CURLOPT_RETURNTRANSFER . "<br/>" . PHP_EOL);
echo("CURLOPT_URL:" . CURLOPT_URL . "<br/>" . PHP_EOL);


?>
