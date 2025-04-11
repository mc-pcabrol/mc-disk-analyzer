<?php
/*
Plugin Name: MC Disk Analyzer
Plugin URI: https://github.com/mc-pcabrol/plugin-mc-disk-analyzer
Description: Visualisez les fichiers et dossiers les plus lourds de votre site WordPress. Développé par Pierre Cabrol.
Version: 0.5.0
Author: Pierre Cabrol
Author URI: https://www.midiconcept.fr
License: MIT
License URI: https://opensource.org/licenses/MIT
*/

require plugin_dir_path(__FILE__) . 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/mc-pcabrol/plugin-mc-disk-analyzer/',
    __FILE__,
    'mc-disk-analyzer'
);
$updateChecker->getVcsApi()->enableReleaseAssets();

add_action('admin_menu', function () {
    add_action('admin_enqueue_scripts', function() {
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
        wp_enqueue_script('mc-disk-chart', plugin_dir_url(__FILE__) . 'assets/js/chart.js', ['chartjs'], null, true);
    });

    add_menu_page(
        'Analyse Disque',
        'MC Disk Analyzer',
        'manage_options',
        'mc-disk-analyzer',
        'mc_disk_analyzer_page',
        'dashicons-hdd',
        80
    );
});

add_action('wp_ajax_mc_disk_analyzer_load', 'mc_disk_analyzer_ajax');

function mc_disk_analyzer_page() {
    ?>
    <div class="wrap">
        <h1>MC Disk Analyzer</h1>
        <p>Choisissez un seuil minimum pour afficher les fichiers lourds :</p>
        <select id="mc-disk-threshold" name="mc-disk-threshold">
            <option value="5">5 Mo</option>
            <option value="10">10 Mo</option>
            <option value="50">50 Mo</option>
            <option value="100">100 Mo</option>
            <option value="200">200 Mo</option>
            <option value="500">500 Mo</option>
            <option value="1024">1 Go</option>
        </select>

        <div id="mc-disk-results" style="margin-top: 30px;">
            <p>Chargement...</p>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        function loadResults(threshold) {
            const results = document.getElementById('mc-disk-results');
            results.innerHTML = '<p>Chargement...</p>';
            fetch(ajaxurl + '?action=mc_disk_analyzer_load&threshold=' + threshold)
                .then(response => response.text())
                .then(html => {
                    results.innerHTML = html;
                    renderMCCharts(); 
                })
                .catch(error => {
                    results.innerHTML = '<p>Erreur lors du chargement des données.</p>';
                    console.error(error);
                });
        }

        const select = document.getElementById('mc-disk-threshold');
        loadResults(select.value);
        select.addEventListener('change', () => loadResults(select.value));
    });
    </script>
    <?php
}

function mc_disk_analyzer_ajax() {
    if (!current_user_can('manage_options')) {
        wp_die('Non autorisé');
    }

    $threshold = isset($_GET['threshold']) ? intval($_GET['threshold']) : 5;
    $min_size = $threshold * 1024 * 1024;

    $results = mc_disk_analyzer_scan(ABSPATH, $min_size);

    echo '<h2>Top fichiers > ' . esc_html($threshold) . ' Mo</h2>';
    echo '<table class="widefat"><thead><tr><th>Fichier</th><th>Taille</th></tr></thead><tbody>';
    foreach ($results['large_files'] as $file => $size) {
        echo '<tr><td>' . esc_html(str_replace(ABSPATH, '', $file)) . '</td><td>' . esc_html(size_format($size)) . '</td></tr>';
    }
    echo '</tbody></table>';

    echo '<h2>Top dossiers</h2>';
    echo '<table class="widefat"><thead><tr><th>Dossier</th><th>Taille</th></tr></thead><tbody>';
    foreach ($results['folders'] as $folder => $size) {
        echo '<tr><td>' . esc_html(str_replace(ABSPATH, '', $folder)) . '</td><td>' . esc_html(size_format($size)) . '</td></tr>';
    }
    echo '</tbody></table>';

    // Graphiques
    echo '<h2>Graphique global</h2>';
    $chart_data = [
        'labels' => array_map(function($f) { return str_replace(ABSPATH, '', $f); }, array_keys($results['folders'])),
        'sizes' => array_values($results['folders']),
    ];
    echo '<canvas id="mc-disk-chart" width="500" height="300" data-chart="' . esc_attr(json_encode($chart_data)) . '"></canvas>';

    $uploads = WP_CONTENT_DIR . '/uploads';
    if (file_exists($uploads)) {
        echo '<h2>Graphique wp-content/uploads</h2>';
        $upload_folders = array_filter(glob($uploads . '/*'), 'is_dir');
        $upload_data = [];
        foreach ($upload_folders as $folder) {
            $upload_data[basename($folder)] = mc_folder_size($folder);
        }
        arsort($upload_data);
        $upload_chart = [
            'labels' => array_keys($upload_data),
            'sizes' => array_values($upload_data),
        ];
        echo '<canvas id="mc-disk-chart-uploads" width="500" height="300" data-chart="' . esc_attr(json_encode($upload_chart)) . '"></canvas>';
    }

    wp_die();
}

function mc_disk_analyzer_scan($dir, $min_file_size = 5242880) {
    $large_files = [];
    $folders = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        try {
            if ($file->isFile()) {
                $size = $file->getSize();
                if ($size >= $min_file_size) {
                    $large_files[$file->getPathname()] = $size;
                }
            } elseif ($file->isDir()) {
                $folder = $file->getPathname();
                if (!isset($folders[$folder])) {
                    $folders[$folder] = mc_folder_size($folder);
                }
            }
        } catch (Exception $e) {}
    }

    arsort($large_files);
    arsort($folders);

    return [
        'large_files' => array_slice($large_files, 0, 30),
        'folders' => array_slice($folders, 0, 30),
    ];
}

function mc_folder_size($dir) {
    $size = 0;
    foreach (scandir($dir) as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_file($path)) {
            $size += filesize($path);
        } elseif (is_dir($path)) {
            $size += mc_folder_size($path);
        }
    }
    return $size;
}
