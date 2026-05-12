<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/_layout.php';
require_login();
$pageTitle = 'Admin Users';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $hash = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost'=>12]);
        execute("INSERT INTO users (username, email, password, active) VALUES (?,?,?,1)", [trim($_POST['username']), trim($_POST['email']), $hash]);
        flash('success', 'User added.');
    } elseif ($action === 'edit') {
        if (!empty($_POST['password'])) {
            $hash = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost'=>12]);
            execute("UPDATE users SET username=?, email=?, password=? WHERE id=?", [trim($_POST['username']), trim($_POST['email']), $hash, (int)$_POST['user_id']]);
        } else {
            execute("UPDATE users SET username=?, email=? WHERE id=?", [trim($_POST['username']), trim($_POST['email']), (int)$_POST['user_id']]);
        }
        flash('success', 'User updated.');
    } elseif ($action === 'delete') {
        execute("DELETE FROM users WHERE id=?", [(int)$_POST['user_id']]);
        flash('success', 'User deleted.');
    }
    header('Location: users.php'); exit;
}
$users = query("SELECT * FROM users ORDER BY id");
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><i class="bi bi-person-lock me-2"></i>Admin Users</h4>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg me-1"></i>Add User</button>
</div>
<?php if ($ok = flash('success')): ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
  <thead><tr><th>#</th><th>Username</th><th>Email</th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
    <?php foreach ($users as $u): ?>
    <tr>
      <td><?= $u->id ?></td>
      <td><?= htmlspecialchars($u->username) ?></td>
      <td><?= htmlspecialchars($u->email) ?></td>
      <td><?= $u->active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
      <td>
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?= $u->id ?>" data-username="<?= htmlspecialchars($u->username) ?>" data-email="<?= htmlspecialchars($u->email) ?>"><i class="bi bi-pencil"></i></button>
        <form method="POST" class="d-inline" onsubmit="return confirm('Delete user?')">
          <input type="hidden" name="action" value="delete"><input type="hidden" name="user_id" value="<?= $u->id ?>">
          <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table></div></div>

<div class="modal fade" id="addModal" tabindex="-1"><div class="modal-dialog"><form method="POST" class="modal-content">
  <input type="hidden" name="action" value="add">
  <div class="modal-header"><h5 class="modal-title">Add Admin User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Add</button></div>
</form></div></div>

<div class="modal fade" id="editModal" tabindex="-1"><div class="modal-dialog"><form method="POST" class="modal-content">
  <input type="hidden" name="action" value="edit"><input type="hidden" name="user_id" id="e_id">
  <div class="modal-header"><h5 class="modal-title">Edit User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" id="e_username" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" id="e_email" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">New Password <small class="text-muted">(leave blank to keep)</small></label><input type="password" name="password" class="form-control"></div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
</form></div></div>
<script>document.getElementById('editModal').addEventListener('show.bs.modal',e=>{const b=e.relatedTarget;document.getElementById('e_id').value=b.dataset.id;document.getElementById('e_username').value=b.dataset.username;document.getElementById('e_email').value=b.dataset.email;});</script>
<?php admin_layout(ob_get_clean(), 'users.php'); ?>
