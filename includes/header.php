<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle) ?> — <?= APP_NAME ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
  :root { --bs-primary: #2563eb; }
  body  { background: #f8fafc; }
  .navbar { background: #1e3a5f !important; }
  .navbar-brand, .nav-link { color: #fff !important; }
  .nav-link:hover { color: #93c5fd !important; }
  .card { border: none; box-shadow: 0 1px 4px rgba(0,0,0,.08); border-radius: 12px; }
  .card-header { background: transparent; border-bottom: 1px solid #e9ecef; font-weight: 600; }
  .badge-position { background: #dbeafe; color: #1d4ed8; padding: 4px 10px; border-radius: 20px; font-size: .8rem; }
  table th { font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; }
  .chart-wrap { height: 220px; }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>"><i class="bi bi-check2-square me-2"></i><?= APP_NAME ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>"><i class="bi bi-bar-chart me-1"></i>Results</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>vote.php"><i class="bi bi-check-circle me-1"></i>Vote</a></li>
        <?php if (is_logged_in()): ?>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>admin/"><i class="bi bi-gear me-1"></i>Admin</a></li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav ms-auto">
        <?php if (is_logged_in()): ?>
          <li class="nav-item"><span class="nav-link"><i class="bi bi-person me-1"></i><?= htmlspecialchars($_SESSION['username']) ?></span></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container py-4">
<?php if ($err = flash('error')): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
<?php if ($ok = flash('success')): ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
