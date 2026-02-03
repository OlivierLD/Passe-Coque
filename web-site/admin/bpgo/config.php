<?php
/**
 * Define configuration
  * Configuration initialisation, using Lyra account informations.
 * provided in your Back Office (Menu: Settings > Shop > API REST Keys).
 **/
try {
    // DEMO SHOP
    define('USERNAME', '73239078');
    define('PASSWORD', 'testpassword_SbEbeOueaMDyg8Rtei1bSaiB5lms9V0ZDjzldGXGAnIwH');
    define('PUBLIC_KEY', '73239078:testpublickey_Zr3fXIKKx0mLY9YNBQEan42ano2QsdrLuyb2W54QWmUJQ');
    define('SHA_KEY', 'VgbDd550wI6W1rwODGy56QAUkUQwIEdwXG5ziDUUC72BS');
    define('SERVER', 'https://api.systempay.fr');
    $URL_JS = 'https://static.systempay.fr/static/js/krypton-client/V4.0/stable/kr-payment-form.min.js';
    // SUBSTITUTE BY MERCHANT SHOP (Menu: Settings > Shop > API REST Keys)
    // define('USERNAME', 'KEY Number 1');
    // define('PASSWORD', 'KEY Number 2');
    // define('PUBLIC_KEY', 'KEY Number 3');
    // define('SHA_KEY', 'KEY Number 4');
    // define('SERVER', 'KEY Number 5');
    // $URL_JS = 'KEY Number 6';
    /*DOMAIN_URL : racine domaine URL_JS */
    define('DOMAIN_URL', strstr($URL_JS, '/static/', true)); // Returns whatever ig BEFOORE '/static/'

    // echo constant("DOMAIN_URL") . "<br/>" . PHP_EOL;

    // echo "Done for now" . "<br/>" . PHP_EOL;
} catch (Throwable $e) {
  echo "Oops: " . $e->getMessage() . "<br/>" . PHP_EOL;
}

?>
