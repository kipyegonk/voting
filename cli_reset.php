#!/usr/bin/env php
<?php
/*
 * CodeIgniter CLI Password Reset Script
 */

// Set the environment
if (!defined('ENVIRONMENT'))
{
    define('ENVIRONMENT', 'development');
}

// Set the working directory to the application folder
chdir(dirname(__FILE__));

// Add current directory to path
set_include_path(get_include_path() . PATH_SEPARATOR . '.');

// Ensure CodeIgniter is accessible
if (!file_exists(dirname(__FILE__) . '/system'))
{
    echo "ERROR: Cannot find CodeIgniter system folder.\n";
    exit(1);
}

// Include CodeIgniter's base files
require_once dirname(__FILE__) . '/system/core/Common.php';
require_once dirname(__FILE__) . '/system/core/Log.php';

// Load the database config
require_once dirname(__FILE__) . '/application/config/database.php';
require_once dirname(__FILE__) . '/application/config/flexi_auth.php';

// Create connection
$db = new mysqli(
    $db['default']['hostname'],
    $db['default']['username'],
    $db['default']['password'],
    $db['default']['database']
);

if ($db->connect_error)
{
    echo "Database connection failed: " . $db->connect_error . "\n";
    exit(1);
}

// Test: Just create a new simple test password
// We'll use a test account first
$username = 'test_user';
$email = 'test@example.com';
$password = 'Test@123456';

// Check if test user exists
$result = $db->query("SELECT COUNT(*) as cnt FROM user_accounts WHERE uacc_username = '$username' OR uacc_email = '$email'");
$row = $result->fetch_assoc();

if ($row['cnt'] > 0)
{
    echo "Test user already exists\n";
    exit(0);
}

// For now, just update Morris with a bcrypt hash and test
$hash = password_hash("Morris123456", PASSWORD_BCRYPT, ['cost' => 8]);

$stmt = $db->prepare("UPDATE user_accounts SET uacc_password = ? WHERE uacc_username = 'morris'");
$stmt->bind_param("s", $hash);

if ($stmt->execute())
{
    echo "✓ Morris password reset to: Morris123456\n";
}
else
{
    echo "✗ Failed to update password: " . $stmt->error . "\n";
}

$stmt->close();
$db->close();
?>
