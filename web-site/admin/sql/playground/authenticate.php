<?php

$valid_passwords = array ("akeu" => "coucou");
$valid_users = array_keys($valid_passwords);

// For tests
$_SERVER['PHP_AUTH_USER'] = 'akeu';
$_SERVER['PHP_AUTH_PW'] = 'coucou';

// For real
$user = $_SERVER['PHP_AUTH_USER'];
$pass = $_SERVER['PHP_AUTH_PW'];

echo "Validating $user / $pass ...<br/>" . PHP_EOL;

$validated = (in_array($user, $valid_users)) && ($pass == $valid_passwords[$user]);

if (!$validated) {
  header('WWW-Authenticate: Basic realm="My Realm"');
  header('HTTP/1.0 401 Unauthorized');
  die ("Not authorized");
}

// If arrives here, is a valid user.
echo "<p>Welcome $user.</p>";
echo "<p>Congratulation, you are into the system.</p>";

?>