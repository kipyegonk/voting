<?php
// Shared admin nav renderer
function admin_nav(string $active = ''): string {
    $nav = [
        ['./',             'bi-speedometer2', 'Dashboard'],
        ['voters.php',     'bi-people',       'Voters'],
        ['candidates.php', 'bi-person-badge', 'Candidates'],
        ['positions.php',  'bi-trophy',       'Positions'],
        ['parties.php',    'bi-flag',         'Parties'],
        ['users.php',      'bi-person-lock',  'Admin Users'],
    ];
    $html = '<div class="card"><div class="list-group list-group-flush">';
    foreach ($nav as [$href, $icon, $label]) {
        $isActive = basename($href) === $active ? ' active' : '';
        $html .= "<a href=\"{$href}\" class=\"list-group-item list-group-item-action py-2{$isActive}\">
          <i class=\"bi {$icon} me-2\"></i>{$label}</a>";
    }
    return $html . '</div></div>';
}

function admin_layout(string $content, string $active = ''): void {
    global $pageTitle;
    require_once __DIR__ . '/../includes/header.php';
    echo '<div class="row">';
    echo '<div class="col-lg-2 mb-3">' . admin_nav($active) . '</div>';
    echo '<div class="col-lg-10">' . $content . '</div>';
    echo '</div>';
    require_once __DIR__ . '/../includes/footer.php';
}
