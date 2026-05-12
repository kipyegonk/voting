<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/_layout.php';
require_login();
$pageTitle = 'Manage Candidates';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        execute("INSERT INTO candidates (candidate_name, party_id, electoral_position_id) VALUES (?,?,?)", [
            trim($_POST['candidate_name']),
            (int)$_POST['party_id'] ?: null,
            (int)$_POST['electoral_position_id'],
        ]);
        flash('success', 'Candidate added.');
    } elseif ($action === 'edit') {
        execute("UPDATE candidates SET candidate_name=?, party_id=?, electoral_position_id=? WHERE candidate_id=?", [
            trim($_POST['candidate_name']),
            (int)$_POST['party_id'] ?: null,
            (int)$_POST['electoral_position_id'],
            (int)$_POST['candidate_id'],
        ]);
        flash('success', 'Candidate updated.');
    } elseif ($action === 'delete') {
        execute("DELETE FROM candidates WHERE candidate_id=?", [(int)$_POST['candidate_id']]);
        flash('success', 'Candidate deleted.');
    }
    header('Location: candidates.php'); exit;
}

$candidates = query("
    SELECT c.*, p.party_name, p.party_initials, ep.position_name
    FROM candidates c
    LEFT JOIN parties p ON p.party_id = c.party_id
    LEFT JOIN electoral_positions ep ON ep.position_id = c.electoral_position_id
    ORDER BY ep.position_name, c.candidate_name
");
$parties   = query("SELECT * FROM parties ORDER BY party_name");
$positions = query("SELECT * FROM electoral_positions ORDER BY position_name");

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><i class="bi bi-person-badge me-2"></i>Candidates</h4>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
    <i class="bi bi-plus-lg me-1"></i>Add Candidate
  </button>
</div>
<?php if ($ok = flash('success')): ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>#</th><th>Name</th><th>Party</th><th>Position</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($candidates as $c): ?>
        <tr>
          <td><?= $c->candidate_id ?></td>
          <td><?= htmlspecialchars($c->candidate_name) ?></td>
          <td><?= htmlspecialchars($c->party_initials ?? '—') ?></td>
          <td><span class="badge-position"><?= htmlspecialchars($c->position_name ?? '—') ?></span></td>
          <td>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal"
              data-id="<?= $c->candidate_id ?>" data-name="<?= htmlspecialchars($c->candidate_name) ?>"
              data-party="<?= $c->party_id ?>" data-pos="<?= $c->electoral_position_id ?>">
              <i class="bi bi-pencil"></i>
            </button>
            <form method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="candidate_id" value="<?= $c->candidate_id ?>">
              <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1"><div class="modal-dialog">
  <form method="POST" class="modal-content">
    <input type="hidden" name="action" value="add">
    <div class="modal-header"><h5 class="modal-title">Add Candidate</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-3"><label class="form-label">Name</label>
        <input type="text" name="candidate_name" class="form-control" required></div>
      <div class="mb-3"><label class="form-label">Party</label>
        <select name="party_id" class="form-select">
          <option value="">— None —</option>
          <?php foreach ($parties as $p): ?>
          <option value="<?= $p->party_id ?>"><?= htmlspecialchars($p->party_name) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="mb-3"><label class="form-label">Position</label>
        <select name="electoral_position_id" class="form-select" required>
          <option value="">— Select —</option>
          <?php foreach ($positions as $pos): ?>
          <option value="<?= $pos->position_id ?>"><?= htmlspecialchars($pos->position_name) ?></option>
          <?php endforeach; ?>
        </select></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      <button class="btn btn-primary">Add</button>
    </div>
  </form>
</div></div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1"><div class="modal-dialog">
  <form method="POST" class="modal-content">
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="candidate_id" id="e_id">
    <div class="modal-header"><h5 class="modal-title">Edit Candidate</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-3"><label class="form-label">Name</label>
        <input type="text" name="candidate_name" id="e_name" class="form-control" required></div>
      <div class="mb-3"><label class="form-label">Party</label>
        <select name="party_id" id="e_party" class="form-select">
          <option value="">— None —</option>
          <?php foreach ($parties as $p): ?>
          <option value="<?= $p->party_id ?>"><?= htmlspecialchars($p->party_name) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="mb-3"><label class="form-label">Position</label>
        <select name="electoral_position_id" id="e_pos" class="form-select" required>
          <?php foreach ($positions as $pos): ?>
          <option value="<?= $pos->position_id ?>"><?= htmlspecialchars($pos->position_name) ?></option>
          <?php endforeach; ?>
        </select></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      <button class="btn btn-primary">Save</button>
    </div>
  </form>
</div></div>

<script>
document.getElementById('editModal').addEventListener('show.bs.modal', e => {
  const b = e.relatedTarget;
  document.getElementById('e_id').value = b.dataset.id;
  document.getElementById('e_name').value = b.dataset.name;
  document.getElementById('e_party').value = b.dataset.party;
  document.getElementById('e_pos').value = b.dataset.pos;
});
</script>

<?php admin_layout(ob_get_clean(), 'candidates.php'); ?>
