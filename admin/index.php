<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_login();

$pageTitle = 'Admin Dashboard';

$stats = [
    'voters'     => queryOne("SELECT COUNT(*) AS n FROM voters")->n,
    'candidates' => queryOne("SELECT COUNT(*) AS n FROM candidates")->n,
    'positions'  => queryOne("SELECT COUNT(*) AS n FROM electoral_positions")->n,
    'parties'    => queryOne("SELECT COUNT(*) AS n FROM parties")->n,
    'votes'      => queryOne("SELECT COUNT(DISTINCT voter_id) AS n FROM votes")->n,
];

$recentVoters = query("SELECT * FROM voters ORDER BY voter_id DESC LIMIT 5");

ob_start();
?>

<h4 class="mb-4"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h4>

<!-- Stats -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['voters',     'Voters',       'bi-people',    'primary'],
    ['votes',      'Voted',        'bi-check-all', 'success'],
    ['candidates', 'Candidates',   'bi-person-badge','warning'],
    ['positions',  'Positions',    'bi-trophy',    'info'],
    ['parties',    'Parties',      'bi-flag',      'secondary'],
  ];
  foreach ($cards as [$key, $label, $icon, $color]):
  ?>
  <div class="col-6 col-md-4 col-lg">
    <div class="card text-center p-3">
      <i class="bi <?= $icon ?> text-<?= $color ?> fs-3"></i>
      <div class="fs-2 fw-bold mt-1"><?= $stats[$key] ?></div>
      <div class="text-muted small"><?= $label ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Quick links -->
<div class="row g-3 mb-4">
  <?php
  $links = [
    ['voters.php',     'bi-people-fill',    'Manage Voters',     'primary'],
    ['candidates.php', 'bi-person-badge',   'Manage Candidates', 'warning'],
    ['positions.php',  'bi-trophy-fill',    'Manage Positions',  'info'],
    ['parties.php',    'bi-flag-fill',      'Manage Parties',    'secondary'],
  ];
  foreach ($links as [$href, $icon, $label, $color]):
  ?>
  <div class="col-6 col-md-3">
    <a href="<?= $href ?>" class="card p-3 text-decoration-none text-center d-block">
      <i class="bi <?= $icon ?> text-<?= $color ?> fs-3"></i>
      <div class="mt-2 fw-semibold text-dark"><?= $label ?></div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<!-- Recent voters -->
<div class="card">
  <div class="card-header"><i class="bi bi-clock-history me-2"></i>Recent Voters</div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>#</th><th>Name</th><th>Phone</th><th>National ID</th></tr></thead>
      <tbody>
        <?php foreach ($recentVoters as $v): ?>
        <tr>
          <td><?= $v->voter_id ?></td>
          <td><?= htmlspecialchars($v->voter_name ?? '') ?></td>
          <td><?= htmlspecialchars($v->phone ?? '') ?></td>
          <td><?= htmlspecialchars($v->national_id ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer text-end">
    <a href="voters.php" class="btn btn-sm btn-outline-primary">View All Voters</a>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/header.php';
// Admin sidebar nav
echo '<div class="row"><div class="col-lg-2 mb-3">';
echo '<div class="card"><div class="list-group list-group-flush">';
$adminNav = [
    ['./',             'bi-speedometer2', 'Dashboard'],
    ['voters.php',     'bi-people',       'Voters'],
    ['candidates.php', 'bi-person-badge', 'Candidates'],
    ['positions.php',  'bi-trophy',       'Positions'],
    ['parties.php',    'bi-flag',         'Parties'],
    ['users.php',      'bi-person-lock',  'Admin Users'],
];
foreach ($adminNav as [$href, $icon, $label]) {
    echo "<a href=\"{$href}\" class=\"list-group-item list-group-item-action py-2\">
      <i class=\"bi {$icon} me-2\"></i>{$label}</a>";
}
echo '</div></div></div>';
echo '<div class="col-lg-10">' . $content . '</div></div>';
include __DIR__ . '/../includes/footer.php';
