<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

session_start_safe();
$pageTitle = 'Cast Your Vote';
$step  = $_SESSION['vote_step'] ?? 'phone';
$error = '';
$voter = null;

// ── Step 1: phone entry ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone'])) {
    $phone = trim($_POST['phone']);
    $voter = queryOne("SELECT * FROM voters WHERE phone = ?", [$phone]);

    if (!$voter) {
        $error = 'This phone number is not registered to vote.';
    } elseif (hasVotedForAll($voter->voter_id)) {
        $error = 'You have already voted for all positions.';
    } else {
        // No SMS configured — skip confirmation, go straight to ballot
        $_SESSION['vote_voter_id'] = $voter->voter_id;
        $_SESSION['vote_step']     = 'ballot';
        header('Location: vote.php');
        exit;
    }
}

// ── Step 2: ballot ────────────────────────────────────────────────────────
if ($step === 'ballot') {
    if (empty($_SESSION['vote_voter_id'])) {
        $_SESSION['vote_step'] = 'phone';
        header('Location: vote.php');
        exit;
    }
    $voter = queryOne("SELECT * FROM voters WHERE voter_id = ?", [$_SESSION['vote_voter_id']]);
}

// ── Step 3: submit votes ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_votes'])) {
    $voter_id = $_SESSION['vote_voter_id'] ?? null;
    if (!$voter_id) { header('Location: vote.php'); exit; }

    $positions = query("SELECT * FROM electoral_positions");
    $db = db();
    $db->beginTransaction();
    try {
        foreach ($positions as $pos) {
            $candidate_id = $_POST['position_' . $pos->position_id] ?? null;
            if ($candidate_id && !hasVoted($voter_id, $pos->position_id)) {
                execute(
                    "INSERT INTO votes (voter_id, candidate_id, position_id) VALUES (?,?,?)",
                    [$voter_id, $candidate_id, $pos->position_id]
                );
            }
        }
        $db->commit();
    } catch (\Exception $e) {
        $db->rollBack();
        $error = 'Error saving votes. Please try again.';
    }

    unset($_SESSION['vote_voter_id'], $_SESSION['vote_step']);
    flash('success', 'Thank you! Your vote has been recorded.');
    header('Location: ' . BASE_URL);
    exit;
}

function hasVoted(int $voter_id, int $position_id): bool {
    $r = queryOne(
        "SELECT COUNT(*) AS n FROM votes WHERE voter_id=? AND position_id=?",
        [$voter_id, $position_id]
    );
    return ($r->n ?? 0) > 0;
}

function hasVotedForAll(int $voter_id): bool {
    $total    = queryOne("SELECT COUNT(*) AS n FROM electoral_positions")->n ?? 0;
    $voted    = queryOne("SELECT COUNT(DISTINCT position_id) AS n FROM votes WHERE voter_id=?", [$voter_id])->n ?? 0;
    return $total > 0 && $voted >= $total;
}

// Fetch ballot data
$positions  = ($step === 'ballot') ? query("SELECT * FROM electoral_positions ORDER BY position_id") : [];
$candidates = ($step === 'ballot') ? query("
    SELECT c.*, p.party_initials, p.party_name
    FROM candidates c
    LEFT JOIN parties p ON p.party_id = c.party_id
    ORDER BY c.electoral_position_id, c.candidate_name
") : [];

$byPosition = [];
foreach ($candidates as $c) {
    $byPosition[$c->electoral_position_id][] = $c;
}

ob_start();
?>

<div class="row justify-content-center">
<div class="col-lg-7">

<?php if ($step === 'phone'): ?>
<!-- ── Phone Entry ── -->
<div class="card">
  <div class="card-header"><i class="bi bi-phone me-2"></i>Enter Your Phone Number</div>
  <div class="card-body p-4">
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <p class="text-muted">Enter the phone number you registered with to access your ballot.</p>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Phone Number</label>
        <input type="tel" name="phone" class="form-control form-control-lg"
               placeholder="e.g. 0712345678" required autofocus
               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
      </div>
      <button type="submit" class="btn btn-primary btn-lg w-100">
        <i class="bi bi-arrow-right-circle me-2"></i>Continue
      </button>
    </form>
  </div>
</div>

<?php elseif ($step === 'ballot' && $voter): ?>
<!-- ── Ballot ── -->
<div class="card mb-3">
  <div class="card-body py-3">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-person-check-fill text-success fs-4"></i>
      <div>
        <div class="fw-bold"><?= htmlspecialchars($voter->voter_name ?? 'Voter') ?></div>
        <div class="text-muted small"><?= htmlspecialchars($voter->phone) ?></div>
      </div>
    </div>
  </div>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<form method="POST">
  <input type="hidden" name="submit_votes" value="1">

  <?php foreach ($positions as $pos): ?>
    <?php
    $posCandidates = $byPosition[$pos->position_id] ?? [];
    $alreadyVoted  = hasVoted($voter->voter_id, $pos->position_id);
    ?>
    <div class="card mb-3 <?= $alreadyVoted ? 'opacity-50' : '' ?>">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-trophy me-2"></i><?= htmlspecialchars($pos->position_name) ?></span>
        <?php if ($alreadyVoted): ?>
          <span class="badge bg-success">Already voted</span>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <?php if ($alreadyVoted): ?>
          <p class="text-muted mb-0">You have already voted for this position.</p>
        <?php else: ?>
          <div class="row g-3">
            <?php foreach ($posCandidates as $c): ?>
            <div class="col-6">
              <label class="card p-3 h-100 cursor-pointer candidate-card">
                <div class="d-flex gap-2 align-items-center">
                  <input type="radio" name="position_<?= $pos->position_id ?>"
                         value="<?= $c->candidate_id ?>" class="form-check-input mt-0" required>
                  <div>
                    <div class="fw-semibold"><?= htmlspecialchars($c->candidate_name) ?></div>
                    <?php if (!empty($c->party_initials)): ?>
                    <div class="text-muted small"><?= htmlspecialchars($c->party_initials) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              </label>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <div class="d-grid gap-2">
    <button type="submit" class="btn btn-success btn-lg"
            onclick="return confirm('Submit your votes? This cannot be undone.')">
      <i class="bi bi-check-circle me-2"></i>Submit My Votes
    </button>
    <a href="<?= BASE_URL ?>" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>
<?php endif; ?>

</div>
</div>

<style>
.candidate-card { border: 2px solid #e5e7eb; cursor: pointer; transition: border-color .15s; }
.candidate-card:has(input:checked) { border-color: #2563eb; background: #eff6ff; }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/includes/header.php';
echo $content;
include __DIR__ . '/includes/footer.php';
