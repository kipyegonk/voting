<?php
require_once('application/libraries/phpass/PasswordHash.php');
$phpass = new PasswordHash(8, FALSE);

$database_salt = 'fYmFgq9pd2';
$password = 'Morris@123';
$static_salt = 'change-me!';

$hash_input = $database_salt . $password . $static_salt;
$hashed = $phpass->HashPassword($hash_input);

echo "Hash input: " . $hash_input . PHP_EOL;
echo "Hashed password: " . $hashed . PHP_EOL;
?>
