<?php
// Load CodeIgniter bootstrap
define('BASEPATH', realpath(dirname(__FILE__)) . '/system/');
require_once('system/core/Common.php');

// Include database config
require_once('application/config/database.php');
require_once('application/config/flexi_auth.php');

// Create a simple mysqli connection
$db = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);

if ($db->connect_error) {
    echo "Connection failed: " . $db->connect_error;
    exit();
}

// Load phpass
require_once('application/libraries/phpass/PasswordHash.php');

// Settings
$database_salt = 'fYmFgq9pd2';
$static_salt = 'change-me!';
$password = 'Morris@123';
$username = 'morris';

// Try direct bcrypt since phpass seems broken
// Using PHP's password_hash with cost 8
$simple_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 8]);

// Update database
$stmt = $db->prepare("UPDATE user_accounts SET uacc_password = ? WHERE uacc_username = ?");
$stmt->bind_param("ss", $simple_hash, $username);

if ($stmt->execute()) {
    echo "Password updated successfully for user: " . $username . "\n";
    echo "New password: " . $password . "\n";
} else {
    echo "Error updating password: " . $stmt->error . "\n";
}

$stmt->close();
$db->close();
?>
