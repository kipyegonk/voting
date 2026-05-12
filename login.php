<?php
require_once __DIR__ . '/includes/auth.php';

session_start_safe();
if (is_logged_in()) { header('Location: ' . BASE_URL . 'admin/'); exit; }

$pageTitle = 'Admin Login';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (login($username, $password)) {
        header('Location: ' . BASE_URL . 'admin/');
        exit;
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login — <?= APP_NAME ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
body { background: linear-gradient(135deg,#1e3a5f 0%,#2563eb 100%); min-height:100vh; display:flex; align-items:center; }
.login-card { border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
</style>
</head>
<body>
<div class="container">
<div class="row justify-content-center">
<div class="col-md-5 col-lg-4">
  <div class="card login-card p-4">
    <div class="text-center mb-4">
      <i class="bi bi-check2-square text-primary" style="font-size:2.5rem"></i>
      <h4 class="mt-2 fw-bold"><?= APP_NAME ?></h4>
      <p class="text-muted small">Admin Login</p>
    </div>
    <?php if ($error): ?>
    <div class="alert alert-danger py-2"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person"></i></span>
          <input type="text" name="username" class="form-control" required autofocus
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" name="password" class="form-control" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
      </button>
    </form>
    <div class="text-center mt-3">
      <a href="<?= BASE_URL ?>" class="text-muted small">← Back to Results</a>
    </div>
  </div>
</div>
</div>
</div>
</body>
</html>
