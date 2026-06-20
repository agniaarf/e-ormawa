<?php
declare(strict_types=1);
/**
 * Front Controller / Router
 * Maps clean URLs to component pages under /pages.
 * The .htaccess rewrites all non-file/non-directory requests here.
 */
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    redirect('/login.php');
}

// --- Resolve the request path relative to the project base ---
$base = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
if ($base !== '' && $base !== '/' && str_starts_with($path, $base)) {
    $path = substr($path, strlen($base));
}
$path = '/' . trim(rawurldecode($path), '/');
$segments = ($path === '/') ? [] : explode('/', trim($path, '/'));

// --- Route table: pattern => page file (relative to /pages) ---
$routes = [
    ''                                       => 'dashboard.php',
    'dashboard'                              => 'dashboard.php',
    'organisasi'                             => 'organisasi/index.php',
    'organisasi/{org}'                       => 'organisasi/detail.php',
    'organisasi/{org}/member'                => 'organisasi/members.php',
    'organisasi/{org}/permintaan'            => 'organisasi/requests.php',
    'organisasi/{org}/kegiatan'              => 'organisasi/kegiatan.php',
    'organisasi/{org}/kartu'                 => 'organisasi/kartu_anggota.php',
    'organisasi/{org}/jadwal'                => 'organisasi/jadwal.php',
    'organisasi/{org}/kegiatan/{kid}'        => 'kegiatan/detail.php',
    'pengumuman'                             => 'pengumuman/index.php',
    'pengguna'                               => 'pengguna/index.php',
    'laporan'                                => 'laporan/index.php',
    'log'                                    => 'log/index.php',
    'profil'                                 => 'profil.php',
    'notifikasi'                             => 'notifikasi.php',
];

/**
 * Try to match the request segments against a route pattern.
 * Captures {name} segments into $params. Numeric-named segments
 * ({org}, {kid}, {id}) must be digits.
 */
function match_route(array $segments, string $pattern, array &$params): bool {
    $parts = ($pattern === '') ? [] : explode('/', $pattern);
    if (count($parts) !== count($segments)) return false;
    $params = [];
    foreach ($parts as $i => $part) {
        if (preg_match('/^\{(\w+)\}$/', $part, $m)) {
            // dynamic segment — must be a positive integer for our id params
            if (!ctype_digit($segments[$i])) return false;
            $params[$m[1]] = (int) $segments[$i];
        } elseif ($part !== $segments[$i]) {
            return false;
        }
    }
    return true;
}

$matched_file = null;
$params = [];
foreach ($routes as $pattern => $file) {
    if (match_route($segments, $pattern, $params)) {
        $matched_file = $file;
        break;
    }
}

if ($matched_file === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// --- Expose captured params via $_GET so component pages can read them ---
if (isset($params['org'])) {
    $_GET['org_id'] = $params['org'];
    // For the org detail page, the org id is the primary id
    if ($matched_file === 'organisasi/detail.php') {
        $_GET['id'] = $params['org'];
    }
}
if (isset($params['kid'])) {
    $_GET['id'] = $params['kid'];
}
if (isset($params['id'])) {
    $_GET['id'] = $params['id'];
}

$page = __DIR__ . '/pages/' . $matched_file;
if (!is_file($page)) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

require $page;
