<!DOCTYPE html>
<html lang="fr-FR">

<?php

$request_verb = $_SERVER['REQUEST_METHOD'];

// Manage form submission
if (isset($_POST)) {
    $lang = $_POST["lang"];
    $amount = $_POST["amount"];
    $first_name = $_POST["firstName"];
    $last_name = $_POST["lastName"];
    $email = $_POST["email"];
    // echo "POST request: $lang, $amount, $first_name, $last_name, $email <br/>" . PHP_EOL;

    // Used by formToken.php
    define('PC_ITEM_AMOUNT', $amount);
    define('PC_LANG', $lang);
    // TODO Others...
    define('PC_FIRST_NAME', $first_name);
    define('PC_LAST_NAME', $last_name);
    define('PC_EMAIL', $email);
} else {
    echo "Request Verb : $request_verb <br/>" . PHP_EOL;
}

?>


<?php include_once 'config.php'; ?>
<?php include_once 'formToken.php'; ?>

<head>
<!--
'kr' below stands for krypton

STEPS :
1 : load the JS librairy
2 : required public key
3 : the JS parameters url sucess and langage EN
-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <script type="text/javascript"
            src="<?php echo DOMAIN_URL; ?>/static/js/krypton-client/V4.0/stable/kr-payment-form.min.js"
            kr-public-key="<?php echo PUBLIC_KEY; ?>"
            kr-post-url-success="paid.php"
            kr-language="fr-FR">
    </script>
    <!--  theme NEON should be loaded in the HEAD section   -->
    <link rel="stylesheet" href= "<?php echo DOMAIN_URL; ?>/static/js/krypton-client/V4.0/ext/neon-reset.css">
    <script src= "<?php echo DOMAIN_URL; ?>/static/js/krypton-client/V4.0/ext/neon.js">
    </script>
</head>
<body>
    <?php
echo 'Command for ' . PC_FIRST_NAME. ' ' . PC_LAST_NAME . '<br/>' . PHP_EOL;
    ?>
    <!-- $formToken is generated in formToken.php -->
    <div class="kr-smart-form" kr-form-token="<?php echo $formToken; ?>" >
    </div>
</body>
</html>
