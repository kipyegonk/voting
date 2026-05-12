<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/_layout.php';
require_login();
$pageTitle = 'Manage Voters';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        execute("INSERT INTO voters (voter_name, phone, national_id) VALUES (?,?,?)", [
            trim($_POST['voter_name']),
            trim($_POST['phone']),
            trim($_POST['national_id']),
        ]);
        flash('success', 'Voter added.');
    } elseif ($action === 'edit') {
        execute("UPDATE voters SET voter_name=?, phone=?, national_id=? WHERE voter_id=?", [
            trim($_POST['voter_name']),
            trim($_POST['phone']),
            trim($_POST['national_id']),
            (int)$_POST['voter_id'],
        ]);
        flash('success', 'Voter updated.');
    } elseif ($action === 'delete') {
        execute("DELETE FROM voters WHERE voter_id=?", [(int)$_POST['voter_id']]);
        flash('success', 'Voter deleted.');
    }
    header('Location: voters.php'); exit;
}

$voters = query("SELECT v.*, 
    (SELECT COUNT(DISTINCT position_id) FROM votes WHERE voter_id=v.voter_id) AS positions_voted
    FROM voters v ORDER BY v.voter_id DESC");

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><i class="bi bi-people me-2"></i>Voters</h4>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
    <i class="bi bi-plus-lg me-1"></i>Add Voter
  </button>
</div>

<?php if ($ok = flash('success')): ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif; ?>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>#</th><th>Name</th><th>Phone</th><th>National ID</th><th>Voted</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($voters as $v): ?>
        <tr>
          <td><?= $v->voter_id ?></td>
          <td><?= htmlspecialchars($v->voter_name ?? '') ?></td>
          <td><?= htmlspecialchars($v->phone ?? '') ?></td>
          <td><?= htmlspecialchars($v->national_id ?? '') ?></td>
          <td>
            <?php if ($v->positions_voted > 0): ?>
              <span class="badge bg-success">Voted (<?= $v->positions_voted ?>)</span>
            <?php else: ?>
              <span class="badge bg-secondary">Not voted</span>
            <?php endif; ?>
          </td>
          <td>
            <button class="btn btn-sm btn-outline-primary"
              data-bs-toggle="modal" data-bs-target="#editModal"
              data-id="<?= $v->voter_id ?>"
              data-name="<?= htmlspecialchars($v->voter_name ?? '') ?>"
              data-phone="<?= htmlspecialchars($v->phone ?? '') ?>"
              data-nid="<?= htmlspecialchars($v->national_id ?? '') ?>">
              <i class="bi bi-pencil"></i>
            </button>
            <form method="POST" class="d-inline"
                  onsubmit="return confirm('Delete this voter?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="voter_id" value="<?= $v->voter_id ?>">
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
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="add">
      <div class="modal-header"><h5 class="modal-title">Add Voter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-3"><label class="form-label">Full Name</label>
          <input type="text" name="voter_name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Phone</label>
          <input type="tel" name="phone" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">National ID</label>
          <input type="text" name="national_id" class="form-control"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary">Add Voter</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="voter_id" id="edit_id">
      <div class="modal-header"><h5 class="modal-title">Edit Voter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-3"><label class="form-label">Full Name</label>
          <input type="text" name="voter_name" id="edit_name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Phone</label>
          <input type="tel" name="phone" id="edit_phone" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">National ID</label>
          <input type="text" name="national_id" id="edit_nid" class="form-control"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('editModal').addEventListener('show.bs.modal', function(e) {
  const btn = e.relatedTarget;
  document.getElementById('edit_id').value    = btn.dataset.id;
  document.getElementById('edit_name').value  = btn.dataset.name;
  document.getElementById('edit_phone').value = btn.dataset.phone;
  document.getElementById('edit_nid').value   = btn.dataset.nid;
});
</script>

<?php admin_layout(ob_get_clean(), 'voters.php'); ?>
