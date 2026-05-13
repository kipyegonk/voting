<?php
<<<<<<< HEAD
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
=======
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2019, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2019, British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */

/*
 *---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 *---------------------------------------------------------------
 *
 * You can load different configurations depending on your
 * current environment. Setting the environment also influences
 * things like logging and error reporting.
 *
 * This can be set to anything, but default usage is:
 *
 *     development
 *     testing
 *     production
 *
 * NOTE: If you change these, also change the error_reporting() code below
 */
	define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');

/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */
switch (ENVIRONMENT)
{
	case 'development':
		error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
		ini_set('display_errors', 1);
	break;

	case 'testing':
	case 'production':
		ini_set('display_errors', 0);
		if (version_compare(PHP_VERSION, '5.3', '>='))
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
		}
		else
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
		}
	break;

	default:
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'The application environment is not set correctly.';
		exit(1); // EXIT_ERROR
}

/*
 *---------------------------------------------------------------
 * SYSTEM DIRECTORY NAME
 *---------------------------------------------------------------
 *
 * This variable must contain the name of your "system" directory.
 * Set the path if it is not in the same directory as this file.
 */
	$system_path = 'system';

/*
 *---------------------------------------------------------------
 * APPLICATION DIRECTORY NAME
 *---------------------------------------------------------------
 *
 * If you want this front controller to use a different "application"
 * directory than the default one you can set its name here. The directory
 * can also be renamed or relocated anywhere on your server. If you do,
 * use an absolute (full) server path.
 * For more info please see the user guide:
 *
 * https://codeigniter.com/userguide3/general/managing_apps.html
 *
 * NO TRAILING SLASH!
 */
	$application_folder = 'application';

/*
 *---------------------------------------------------------------
 * VIEW DIRECTORY NAME
 *---------------------------------------------------------------
 *
 * If you want to move the view directory out of the application
 * directory, set the path to it here. The directory can be renamed
 * and relocated anywhere on your server. If blank, it will default
 * to the standard location inside your application directory.
 * If you do move this, use an absolute (full) server path.
 *
 * NO TRAILING SLASH!
 */
	$view_folder = '';


/*
 * --------------------------------------------------------------------
 * DEFAULT CONTROLLER
 * --------------------------------------------------------------------
 *
 * Normally you will set your default controller in the routes.php file.
 * You can, however, force a custom routing by hard-coding a
 * specific controller class/function here. For most applications, you
 * WILL NOT set your routing here, but it's an option for those
 * special instances where you might want to override the standard
 * routing in a specific front controller that shares a common CI installation.
 *
 * IMPORTANT: If you set the routing here, NO OTHER controller will be
 * callable. In essence, this preference limits your application to ONE
 * specific controller. Leave the function name blank if you need
 * to call functions dynamically via the URI.
 *
 * Un-comment the $routing array below to use this feature
 */
	// The directory name, relative to the "controllers" directory.  Leave blank
	// if your controller is not in a sub-directory within the "controllers" one
	// $routing['directory'] = '';

	// The controller class file name.  Example:  mycontroller
	// $routing['controller'] = '';

	// The controller function you wish to be called.
	// $routing['function']	= '';


/*
 * -------------------------------------------------------------------
 *  CUSTOM CONFIG VALUES
 * -------------------------------------------------------------------
 *
 * The $assign_to_config array below will be passed dynamically to the
 * config class when initialized. This allows you to set custom config
 * items or override any default config values found in the config.php file.
 * This can be handy as it permits you to share one application between
 * multiple front controller files, with each file containing different
 * config values.
 *
 * Un-comment the $assign_to_config array below to use this feature
 */
	// $assign_to_config['name_of_config_item'] = 'value of config item';



// --------------------------------------------------------------------
// END OF USER CONFIGURABLE SETTINGS.  DO NOT EDIT BELOW THIS LINE
// --------------------------------------------------------------------

/*
 * ---------------------------------------------------------------
 *  Resolve the system path for increased reliability
 * ---------------------------------------------------------------
 */

	// Set the current directory correctly for CLI requests
	if (defined('STDIN'))
	{
		chdir(dirname(__FILE__));
	}

	if (($_temp = realpath($system_path)) !== FALSE)
	{
		$system_path = $_temp.DIRECTORY_SEPARATOR;
	}
	else
	{
		// Ensure there's a trailing slash
		$system_path = strtr(
			rtrim($system_path, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		).DIRECTORY_SEPARATOR;
	}

	// Is the system path correct?
	if ( ! is_dir($system_path))
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME);
		exit(3); // EXIT_CONFIG
	}

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
	// The name of THIS file
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

	// Path to the system directory
	define('BASEPATH', $system_path);

	// Path to the front controller (this file) directory
	define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

	// Name of the "system" directory
	define('SYSDIR', basename(BASEPATH));

	// The path to the "application" directory
	if (is_dir($application_folder))
	{
		if (($_temp = realpath($application_folder)) !== FALSE)
		{
			$application_folder = $_temp;
		}
		else
		{
			$application_folder = strtr(
				rtrim($application_folder, '/\\'),
				'/\\',
				DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
			);
		}
	}
	elseif (is_dir(BASEPATH.$application_folder.DIRECTORY_SEPARATOR))
	{
		$application_folder = BASEPATH.strtr(
			trim($application_folder, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		);
	}
	else
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
		exit(3); // EXIT_CONFIG
	}

	define('APPPATH', $application_folder.DIRECTORY_SEPARATOR);

	// The path to the "views" directory
	if ( ! isset($view_folder[0]) && is_dir(APPPATH.'views'.DIRECTORY_SEPARATOR))
	{
		$view_folder = APPPATH.'views';
	}
	elseif (is_dir($view_folder))
	{
		if (($_temp = realpath($view_folder)) !== FALSE)
		{
			$view_folder = $_temp;
		}
		else
		{
			$view_folder = strtr(
				rtrim($view_folder, '/\\'),
				'/\\',
				DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
			);
		}
	}
	elseif (is_dir(APPPATH.$view_folder.DIRECTORY_SEPARATOR))
	{
		$view_folder = APPPATH.strtr(
			trim($view_folder, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		);
	}
	else
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your view folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
		exit(3); // EXIT_CONFIG
	}

	define('VIEWPATH', $view_folder.DIRECTORY_SEPARATOR);

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 * And away we go...
 */
require_once BASEPATH.'core/CodeIgniter.php';
>>>>>>> origin/main
