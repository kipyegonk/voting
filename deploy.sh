#!/bin/bash
# ============================================================
# Voting New System Deploy Script
# Run as: sudo bash deploy.sh
# ============================================================
set -e
WEBROOT="/var/www/html/voting_new"
echo "======================================"
echo " Deploying New Voting System"
echo "======================================"

# 1. Copy files
echo "[1] Copying files..."
mkdir -p "$WEBROOT"
cp -r ./* "$WEBROOT/"
chown -R www-data:www-data "$WEBROOT"
chmod -R 755 "$WEBROOT"
echo "    Done"

# 2. Apache
echo "[2] Configuring Apache..."
a2enmod rewrite > /dev/null 2>&1
if grep -q "AllowOverride None" /etc/apache2/apache2.conf; then
    sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
fi

# 3. MySQL sql_mode
echo "[3] Fixing MySQL..."
MYCNF="/etc/mysql/mysql.conf.d/mysqld.cnf"
grep -q "only_full_group_by" "$MYCNF" 2>/dev/null || echo -e '\n[mysqld]\nsql_mode = "STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION"' >> "$MYCNF"
systemctl restart mysql

# 4. Create new tables
echo "[4] Setting up database..."
mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS voting;"
mysql -u root -proot voting << 'SQL'
-- New simple users table for admin login
CREATE TABLE IF NOT EXISTS users (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  email    VARCHAR(200) NOT NULL,
  password VARCHAR(255) NOT NULL,
  active   TINYINT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Make sure votes table has position_id column
-- ALTER TABLE votes ADD COLUMN IF NOT EXISTS position_id INT NULL;
SQL

# 5. Create admin users from existing user_accounts
echo "[5] Creating admin accounts..."
php << 'PHP'
<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=voting', 'root', 'root');
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

$defaultPass = password_hash('Admin@123', PASSWORD_BCRYPT, ['cost'=>12]);

// Import admins from old user_accounts table if exists
try {
    $oldUsers = $pdo->query("SELECT uacc_username, uacc_email FROM user_accounts WHERE uacc_active=1 LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($oldUsers as $u) {
        $check = $pdo->prepare("SELECT id FROM users WHERE username=?");
        $check->execute([$u['uacc_username']]);
        if (!$check->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, active) VALUES (?,?,?,1)");
            $stmt->execute([$u['uacc_username'], $u['uacc_email'], $defaultPass]);
            echo "  Created admin: {$u['uacc_username']} / Admin@123\n";
        }
    }
} catch (Exception $e) {
    echo "  Skipping import from user_accounts (table may not exist): " . $e->getMessage() . "\n";
}

// Always ensure 'admin' exists
$check = $pdo->prepare("SELECT id FROM users WHERE username='admin'");
$check->execute();
if (!$check->fetch()) {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, active) VALUES ('admin','admin@voting.local',?,1)");
    $stmt->execute([$defaultPass]);
    echo "  Created admin: admin / Admin@123\n";
}
PHP

# 6. Restart services
systemctl restart apache2
systemctl restart mysql

echo ""
echo "======================================"
echo " DONE! Visit your new system:"
echo " http://localhost/voting_new/"
echo ""
echo " Admin login:"
echo " http://localhost/voting_new/login.php"
echo " Username: admin"
echo " Password: Admin@123"
echo ""
echo " Also created accounts for all existing"
echo " users (morris, morrismukiri, etc) with"
echo " password: Admin@123"
echo "======================================"
