<?php include_once 'config.php'; ?>
<?php
// STEP 0 : Get DEFINED data

if (defined("PC_ITEM_AMOUNT")) {
    $itemAmount = ((float)constant('PC_ITEM_AMOUNT')) * 100;
} else {
    $itemAmount = 10;
}
if (defined('PC_FIRST_NAME')) {
    $first_name = constant('PC_FIRST_NAME');
} else {
    $first_name = 'Test';
}
if (defined('PC_LAST_NAME')) {
    $last_name = constant('PC_LAST_NAME');
} else {
    $last_name = 'Krypton';
}
if (defined('PC_EMAIL')) {
    $email = constant('PC_EMAIL');
} else {
    $email = 'sample@example.com';
}
if (defined('PC_LANG')) {
    $lang = strtolower(constant('PC_LANG'));
} else {
    $lang = 'fr';
}

// echo "Lang becomes $lang <br/>" . PHP_EOL;


// STEP 1 : the data request. TODO Get those data
$data = array(
    'orderId' => uniqid('order_'),
    'amount' => $itemAmount,                 // 2500 = 25.00 Euro
    'currency' => 'EUR',
    'customer' => array(
        'email' => $email,
        'reference' => uniqid('customer_'),
        'billingDetails' => array(
            'language' => $lang,
            'title' => 'M.',
            'firstName' => $first_name,
            'lastName' => $last_name,
            'category' => 'PRIVATE',
            'address' => '25 rue de l\'Innovation',
            'zipCode' => '31000',
            'city' => 'Testville',
            'phoneNumber' => '0615665555',
            'country' => 'FR'
        )
 ),
 'transactionOptions' => array(
        'cardOptions' => array('retry' => 1)
 )
);
// STEP 3 : call the endpoint V4/Charge/CreatePayment with the json data.
$response = post(SERVER."/api-payment/V4/Charge/CreatePayment", json_encode($data));
// Check if there is errors.
if ($response['status'] != 'SUCCESS') {
    // An error occurs, throw exception
    $error = $response['answer'];
    throw new Exception('error ' . $error['errorCode'] . ': ' . $error['errorMessage'] );
}

// echo ("- Response:<br/>" . PHP_EOL);
// var_dump($response);
// echo ("----------------------------------------<br/>" . PHP_EOL);


// Everything is fine, extract the formToken
// STEP 4 : the answer with the creation of the formToken
$formToken = $response['answer']['formToken'];

// echo ("Returned formToken: " . $formToken . "<br/>" . PHP_EOL);


function post($url, $data) {
// STEP 2 : send data with curl command
    $curl = curl_init($url);
    // Values below (like USERNAME, PASSWORD, etc) are DEFINEd in 'config.php'
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'test');
    curl_setopt($curl, CURLOPT_USERPWD, USERNAME . ':' . PASSWORD);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 45);
    curl_setopt($curl, CURLOPT_TIMEOUT, 45);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

    // execution of the curl command
    // echo ("- Will execute curl: <br/>" . PHP_EOL);
    // var_dump($curl);
    // echo ("----------------------------------------<br/>" . PHP_EOL);

    $raw_response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if (!in_array($status, array(200, 401))) {
        curl_close($curl);
        throw new \Exception("Error: call to URL $url failed with unexpected status $status, response $raw_response.");
    }
    $response = json_decode($raw_response, true);
    if (!is_array($response)) {
        $error = curl_error($curl);
        $errno = curl_errno($curl);
        curl_close($curl);
        throw new \Exception("Error: call to URL $url failed, response $raw_response, curl_error $error, curl_errno $errno.");
    }
    curl_close($curl);
    return $response;
}
?>
