<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/_layout.php';
require_login();
$pageTitle = 'Manage Positions';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        execute("INSERT INTO electoral_positions (position_name) VALUES (?)", [trim($_POST['position_name'])]);
        flash('success', 'Position added.');
    } elseif ($action === 'edit') {
        execute("UPDATE electoral_positions SET position_name=? WHERE position_id=?", [trim($_POST['position_name']), (int)$_POST['position_id']]);
        flash('success', 'Position updated.');
    } elseif ($action === 'delete') {
        execute("DELETE FROM electoral_positions WHERE position_id=?", [(int)$_POST['position_id']]);
        flash('success', 'Position deleted.');
    }
    header('Location: positions.php'); exit;
}
$positions = query("SELECT p.*, COUNT(c.candidate_id) AS candidate_count FROM electoral_positions p LEFT JOIN candidates c ON c.electoral_position_id=p.position_id GROUP BY p.position_id ORDER BY p.position_name");
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><i class="bi bi-trophy me-2"></i>Electoral Positions</h4>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg me-1"></i>Add Position</button>
</div>
<?php if ($ok = flash('success')): ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
  <thead><tr><th>#</th><th>Position</th><th>Candidates</th><th>Actions</th></tr></thead>
  <tbody>
    <?php foreach ($positions as $p): ?>
    <tr>
      <td><?= $p->position_id ?></td>
      <td><span class="badge-position"><?= htmlspecialchars($p->position_name) ?></span></td>
      <td><?= $p->candidate_count ?></td>
      <td>
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?= $p->position_id ?>" data-name="<?= htmlspecialchars($p->position_name) ?>"><i class="bi bi-pencil"></i></button>
        <form method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
          <input type="hidden" name="action" value="delete"><input type="hidden" name="position_id" value="<?= $p->position_id ?>">
          <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table></div></div>

<div class="modal fade" id="addModal" tabindex="-1"><div class="modal-dialog"><form method="POST" class="modal-content">
  <input type="hidden" name="action" value="add">
  <div class="modal-header"><h5 class="modal-title">Add Position</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body"><div class="mb-3"><label class="form-label">Position Name</label><input type="text" name="position_name" class="form-control" required></div></div>
  <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Add</button></div>
</form></div></div>

<div class="modal fade" id="editModal" tabindex="-1"><div class="modal-dialog"><form method="POST" class="modal-content">
  <input type="hidden" name="action" value="edit"><input type="hidden" name="position_id" id="e_id">
  <div class="modal-header"><h5 class="modal-title">Edit Position</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body"><div class="mb-3"><label class="form-label">Position Name</label><input type="text" name="position_name" id="e_name" class="form-control" required></div></div>
  <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
</form></div></div>
<script>document.getElementById('editModal').addEventListener('show.bs.modal',e=>{const b=e.relatedTarget;document.getElementById('e_id').value=b.dataset.id;document.getElementById('e_name').value=b.dataset.name;});</script>
<?php admin_layout(ob_get_clean(), 'positions.php'); ?>
