<?php
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Voting Settings';

// Check permissions
requireAdmin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voting_start = $_POST['voting_start'] ?? null;
    $voting_end = $_POST['voting_end'] ?? null;
    $show_results = isset($_POST['show_results']) ? 1 : 0;

    // Update settings
    query("
        UPDATE voting_settings 
        SET voting_start = " . ($voting_start ? "'" . $voting_start . "'" : "NULL") . ",
            voting_end = " . ($voting_end ? "'" . $voting_end . "'" : "NULL") . ",
            show_results = " . $show_results . "
        WHERE setting_id = 1
    ");

    $_SESSION['message'] = 'Voting settings updated successfully!';
    header('Location: ' . BASE_URL . 'admin/voting_settings.php');
    exit;
}

// Fetch current settings
$settings = queryOne("SELECT * FROM voting_settings WHERE setting_id = 1");

ob_start();
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2 class="mb-4">Voting Settings</h2>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="voting_start" class="form-label">Voting Start Date & Time</label>
                            <input type="datetime-local" 
                                   id="voting_start" 
                                   name="voting_start" 
                                   class="form-control"
                                   value="<?= $settings->voting_start ? str_replace(' ', 'T', $settings->voting_start) : '' ?>">
                            <small class="text-muted">Leave empty if no voting period is set</small>
                        </div>

                        <div class="mb-3">
                            <label for="voting_end" class="form-label">Voting End Date & Time</label>
                            <input type="datetime-local" 
                                   id="voting_end" 
                                   name="voting_end" 
                                   class="form-control"
                                   value="<?= $settings->voting_end ? str_replace(' ', 'T', $settings->voting_end) : '' ?>">
                            <small class="text-muted">Leave empty if no voting period is set</small>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" 
                                   id="show_results" 
                                   name="show_results" 
                                   class="form-check-input"
                                   <?= $settings->show_results ? 'checked' : '' ?>>
                            <label class="form-check-label" for="show_results">
                                Show Detailed Results
                                <small class="text-muted d-block">
                                    If unchecked and voting is ongoing, only total votes cast will be shown
                                </small>
                            </label>
                        </div>

                        <div class="alert alert-info">
                            <strong>Current Status:</strong>
                            <?php 
                            $now = new DateTime();
                            $start = $settings->voting_start ? new DateTime($settings->voting_start) : null;
                            $end = $settings->voting_end ? new DateTime($settings->voting_end) : null;
                            
                            if (!$start || !$end) {
                                echo 'No voting period set';
                            } elseif ($now < $start) {
                                echo '<span class="badge bg-warning">Voting Not Started</span>';
                            } elseif ($now > $end) {
                                echo '<span class="badge bg-success">Voting Ended</span>';
                            } else {
                                echo '<span class="badge bg-danger">Voting In Progress</span>';
                            }
                            ?>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Settings</button>
                        <a href="<?= BASE_URL ?>admin/index.php" class="btn btn-secondary">Back</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/header.php';
echo $content;
include __DIR__ . '/../includes/footer.php';
