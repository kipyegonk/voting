<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Vote Results';

// Fetch positions
$positions = query("SELECT * FROM electoral_positions ORDER BY position_id");

// Fetch votes per candidate per position
$votes = query("
    SELECT v.position_id, c.candidate_name, c.candidate_id,
           COUNT(vt.vote_id) AS vote_count
    FROM candidates c
    JOIN electoral_positions v ON c.electoral_position_id = v.position_id
    LEFT JOIN votes vt ON vt.candidate_id = c.candidate_id
    GROUP BY c.candidate_id, v.position_id
    ORDER BY v.position_id, vote_count DESC
");

// Group by position
$byPosition = [];
foreach ($votes as $v) {
    $byPosition[$v->position_id][] = $v;
}

// Total votes cast
$totalVotes = queryOne("SELECT COUNT(DISTINCT voter_id) AS n FROM votes")->n ?? 0;
$totalVoters = queryOne("SELECT COUNT(*) AS n FROM voters")->n ?? 0;

ob_start();
?>

<!-- Stats bar -->
<div class="row g-3 mb-4">
  <div class="col-sm-4">
    <div class="card text-center p-3">
      <div class="fs-1 fw-bold text-primary"><?= $totalVotes ?></div>
      <div class="text-muted small">Voters who voted</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card text-center p-3">
      <div class="fs-1 fw-bold text-success"><?= $totalVoters ?></div>
      <div class="text-muted small">Registered Voters</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card text-center p-3">
      <div class="fs-1 fw-bold text-warning">
        <?= $totalVoters > 0 ? round(($totalVotes / $totalVoters) * 100) : 0 ?>%
      </div>
      <div class="text-muted small">Voter Turnout</div>
    </div>
  </div>
</div>

<?php foreach ($positions as $pos): ?>
  <?php $pVotes = $byPosition[$pos->position_id] ?? []; ?>
  <div class="card mb-4">
    <div class="card-header d-flex align-items-center gap-2">
      <span class="badge-position"><i class="bi bi-trophy me-1"></i><?= htmlspecialchars($pos->position_name) ?></span>
    </div>
    <div class="card-body">
      <div class="row align-items-center">
        <!-- Table -->
        <div class="col-md-4">
          <table class="table table-sm">
            <thead><tr><th>Candidate</th><th>Votes</th></tr></thead>
            <tbody>
              <?php foreach ($pVotes as $v): ?>
              <tr>
                <td><?= htmlspecialchars($v->candidate_name) ?></td>
                <td><strong><?= $v->vote_count ?></strong></td>
              </tr>
              <?php endforeach; ?>
              <?php if (!$pVotes): ?>
              <tr><td colspan="2" class="text-muted">No votes yet</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <!-- Bar chart -->
        <div class="col-md-4">
          <div class="chart-wrap">
            <canvas id="bar-<?= $pos->position_id ?>"></canvas>
          </div>
        </div>
        <!-- Doughnut chart -->
        <div class="col-md-4">
          <div class="chart-wrap">
            <canvas id="dnut-<?= $pos->position_id ?>"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<?php
$content = ob_get_clean();

// Build chart JS
$chartJs = '<script>';
$colors = ['#2563eb','#16a34a','#dc2626','#d97706','#7c3aed','#0891b2','#be185d'];
foreach ($positions as $pos) {
    $pVotes = $byPosition[$pos->position_id] ?? [];
    $labels = json_encode(array_map(fn($v) => $v->candidate_name, $pVotes));
    $data   = json_encode(array_map(fn($v) => (int)$v->vote_count, $pVotes));
    $bgColors = json_encode(array_slice($colors, 0, count($pVotes)));
    $pid = $pos->position_id;
    $chartJs .= "
new Chart(document.getElementById('bar-{$pid}'), {
  type:'bar',
  data:{labels:{$labels},datasets:[{label:'Votes',data:{$data},backgroundColor:{$bgColors},borderRadius:6}]},
  options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}
});
new Chart(document.getElementById('dnut-{$pid}'), {
  type:'doughnut',
  data:{labels:{$labels},datasets:[{data:{$data},backgroundColor:{$bgColors}}]},
  options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{font:{size:11}}}}}
});";
}
$chartJs .= '</script>';
$extraJs = $chartJs;

include __DIR__ . '/includes/header.php';
echo $content;
include __DIR__ . '/includes/footer.php';
