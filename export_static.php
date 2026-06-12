<?php
/**
 * export_static.php
 * Render selected PHP pages to static HTML files for static hosting.
 * Run from project root: php export_static.php
 */

set_time_limit(0);

 /* Pages to export: src path relative and output filename */
 $pages = [
     ['src' => 'index.php', 'out' => 'index.html'],
     ['src' => 'playlist.php', 'out' => 'playlist.html'],
     ['src' => 'why-i-love-you.php', 'out' => 'why-i-love-you.html'],
     ['src' => 'letters.php', 'out' => 'letters.html'],
     ['src' => 'photo-booth.html', 'out' => 'photo-booth.html'],
 ];

$root = __DIR__;
chdir($root);
// Ensure DB connection is available for pages that require it
if (file_exists(__DIR__ . '/php/db.php')) {
    require_once __DIR__ . '/php/db.php';
}

 // Prefer fetching through the local webserver so PHP and DB are executed
 $base = getenv('EXPORT_BASE') ?: 'http://localhost/WEBSITE%20FOR%20MY%20BABY/love-site/';

 function fetch_url($url)
 {
     $opts = [
         'http' => [
             'method' => 'GET',
             'timeout' => 10,
             'header' => "User-Agent: StaticExporter/1.0\r\n"
         ]
     ];
     $context = stream_context_create($opts);
     return @file_get_contents($url, false, $context);
 }

 function render_page_local($file)
 {
     ob_start();
     include $file;
     return ob_get_clean();
 }

echo "Exporting static pages...\n";

foreach ($pages as $p) {
    $src = $p['src'];
    $out = $p['out'];
    if (!file_exists($src)) {
        echo " - Skipping missing source: $src\n";
        continue;
    }

    try {
        // Try fetching through local webserver first
        $url = rtrim($base, '/') . '/' . ltrim($src, '/');
        $html = fetch_url($url);
        if ($html === false || strlen(trim($html)) === 0) {
            // Fallback to including locally (may work for static files)
            $html = render_page_local($src);
        }

        if ($html === false || strlen(trim($html)) === 0) {
            throw new Exception('Empty output');
        }

        // Save output
        file_put_contents($out, $html);
        echo " - Wrote $out\n";
    } catch (Throwable $e) {
        echo " - Error rendering $src: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";

?>
