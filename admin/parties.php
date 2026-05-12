<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/_layout.php';
require_login();
$pageTitle = 'Manage Parties';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        execute("INSERT INTO parties (party_name, party_initials) VALUES (?,?)", [trim($_POST['party_name']), trim($_POST['party_initials'])]);
        flash('success', 'Party added.');
    } elseif ($action === 'edit') {
        execute("UPDATE parties SET party_name=?, party_initials=? WHERE party_id=?", [trim($_POST['party_name']), trim($_POST['party_initials']), (int)$_POST['party_id']]);
        flash('success', 'Party updated.');
    } elseif ($action === 'delete') {
        execute("DELETE FROM parties WHERE party_id=?", [(int)$_POST['party_id']]);
        flash('success', 'Party deleted.');
    }
    header('Location: parties.php'); exit;
}
$parties = query("SELECT * FROM parties ORDER BY party_name");
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><i class="bi bi-flag me-2"></i>Parties</h4>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg me-1"></i>Add Party</button>
</div>
<?php if ($ok = flash('success')): ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
  <thead><tr><th>#</th><th>Party Name</th><th>Initials</th><th>Actions</th></tr></thead>
  <tbody>
    <?php foreach ($parties as $p): ?>
    <tr>
      <td><?= $p->party_id ?></td>
      <td><?= htmlspecialchars($p->party_name) ?></td>
      <td><strong><?= htmlspecialchars($p->party_initials) ?></strong></td>
      <td>
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?= $p->party_id ?>" data-name="<?= htmlspecialchars($p->party_name) ?>" data-initials="<?= htmlspecialchars($p->party_initials) ?>"><i class="bi bi-pencil"></i></button>
        <form method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
          <input type="hidden" name="action" value="delete"><input type="hidden" name="party_id" value="<?= $p->party_id ?>">
          <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table></div></div>

<div class="modal fade" id="addModal" tabindex="-1"><div class="modal-dialog"><form method="POST" class="modal-content">
  <input type="hidden" name="action" value="add">
  <div class="modal-header"><h5 class="modal-title">Add Party</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="mb-3"><label class="form-label">Party Name</label><input type="text" name="party_name" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Initials</label><input type="text" name="party_initials" class="form-control" required></div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Add</button></div>
</form></div></div>

<div class="modal fade" id="editModal" tabindex="-1"><div class="modal-dialog"><form method="POST" class="modal-content">
  <input type="hidden" name="action" value="edit"><input type="hidden" name="party_id" id="e_id">
  <div class="modal-header"><h5 class="modal-title">Edit Party</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="mb-3"><label class="form-label">Party Name</label><input type="text" name="party_name" id="e_name" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Initials</label><input type="text" name="party_initials" id="e_initials" class="form-control" required></div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
</form></div></div>
<script>document.getElementById('editModal').addEventListener('show.bs.modal',e=>{const b=e.relatedTarget;document.getElementById('e_id').value=b.dataset.id;document.getElementById('e_name').value=b.dataset.name;document.getElementById('e_initials').value=b.dataset.initials;});</script>
<?php admin_layout(ob_get_clean(), 'parties.php'); ?>
