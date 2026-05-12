<?php
// ─── Database Configuration ───────────────────────────────────────────────
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'voting');
define('DB_USER', 'root');
define('DB_PASS', 'root');          // change if your MySQL has a password
define('DB_CHARSET', 'utf8mb4');

// ─── App Configuration ────────────────────────────────────────────────────
define('APP_NAME', 'Voting System');
define('BASE_URL', '/voting_new/');   // trailing slash required

// ─── Session secret (change this!) ───────────────────────────────────────
define('SESSION_SECRET', 'v0t1ng-s3cr3t-k3y-2024');

// ─── Africa's Talking SMS (optional) ─────────────────────────────────────
define('AT_USERNAME', '');   // leave blank to skip SMS, use direct voting
define('AT_API_KEY',  '');
