<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

// ---------- CSRF ----------
function generate_csrf_token(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function validate_csrf_token(string $token): bool {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function csrf_input(): string {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

// ---------- XSS ----------
function e(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// ---------- Redirect ----------
function redirect(string $url): void {
    header('Location: ' . BASE_URL . '/' . ltrim($url, '/'));
    exit;
}

// ---------- Flash Messages ----------
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ---------- Auth Helpers ----------
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array {
    if (!is_logged_in()) return null;
    global $pdo;
    $stmt = $pdo->prepare("SELECT u.*, r.nama as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function has_role(string $role): bool {
    $user = current_user();
    return $user && $user['role_name'] === $role;
}

function require_role(string $role): void {
    if (!has_role($role)) {
        set_flash('error', 'Akses ditolak. Anda tidak memiliki izin.');
        redirect('/');
    }
}

function require_login(): void {
    if (!is_logged_in()) {
        set_flash('error', 'Silakan login terlebih dahulu.');
        redirect('/login.php');
    }
}

// ---------- Org-scoped Roles & Permissions ----------
function is_super_admin(): bool {
    return ($_SESSION['role'] ?? '') === 'Super Admin';
}

/**
 * Returns the current user's role within an organisation:
 * 'leader', 'staff', 'member', or null if not a member.
 */
function get_org_role(int $user_id, int $org_id): ?string {
    global $pdo;
    $stmt = $pdo->prepare("SELECT role FROM user_organisasi WHERE user_id = ? AND organisasi_id = ? AND status = 'aktif' LIMIT 1");
    $stmt->execute([$user_id, $org_id]);
    $role = $stmt->fetchColumn();
    return $role !== false ? (string) $role : null;
}

function org_role_label(?string $role): string {
    return match ($role) {
        'leader' => 'Leader',
        'staff'  => 'Staff',
        'member' => 'Member',
        default  => '-',
    };
}

/**
 * Central permission gate. Super Admin can do anything.
 * Org-scoped actions require the appropriate role in $org_id.
 */
function can(string $action, ?int $org_id = null): bool {
    if (!is_logged_in()) return false;
    if (is_super_admin()) return true;

    $uid = (int) $_SESSION['user_id'];
    $role = ($org_id !== null) ? get_org_role($uid, $org_id) : null;

    switch ($action) {
        // Super Admin only (already returned true above for SA)
        case 'organisasi.create':
        case 'organisasi.delete':
        case 'pengguna.manage':
        case 'laporan.view':
        case 'log.view':
            return false;

        // Leader of the org
        case 'organisasi.update':
        case 'member.assign_role':
            return $role === 'leader';

        // Leader or Staff of the org
        case 'member.manage':
        case 'kegiatan.manage':
        case 'pengumuman.manage':
        case 'permintaan.manage':
            return in_array($role, ['leader', 'staff'], true);

        // Any member of the org
        case 'organisasi.read':
        case 'member.read':
        case 'kegiatan.read':
            return $role !== null;

        default:
            return false;
    }
}

function require_can(string $action, ?int $org_id = null): void {
    if (!can($action, $org_id)) {
        set_flash('error', 'Akses ditolak. Anda tidak memiliki izin untuk tindakan ini.');
        redirect('/');
    }
}

/**
 * Organisations the user belongs to (active), including their role.
 */
function my_organisasi(int $user_id): array {
    global $pdo;
    $stmt = $pdo->prepare(
        "SELECT o.*, uo.role AS my_role
         FROM user_organisasi uo
         JOIN organisasi o ON uo.organisasi_id = o.id
         WHERE uo.user_id = ? AND uo.status = 'aktif' AND o.deleted_at IS NULL
         ORDER BY o.nama"
    );
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// ---------- URL helper for clean routes ----------
function url(string $path = ''): string {
    return BASE_URL . '/' . ltrim($path, '/');
}

// ---------- Formatting ----------
function format_tanggal(string $date): string {
    $bulan = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
        '04' => 'April', '05' => 'Mei', '06' => 'Juni',
        '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
        '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
    $d = date('d', strtotime($date));
    $m = date('m', strtotime($date));
    $y = date('Y', strtotime($date));
    return $d . ' ' . $bulan[$m] . ' ' . $y;
}

function format_waktu(string $datetime): string {
    return date('H:i', strtotime($datetime)) . ' WIB';
}

// ---------- Upload ----------
function upload_file(array $file, string $folder): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > MAX_UPLOAD_SIZE) return null;
    if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) return null;

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $path = BASE_PATH . '/uploads/' . $folder . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $path)) {
        return $filename;
    }
    return null;
}

// ---------- Logging ----------
function log_activity(int $user_id, string $aksi, string $detail = ''): void {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, aksi, detail, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $user_id,
        $aksi,
        $detail,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
}

// ---------- Notifications ----------
function send_notification(int $user_id, string $judul, string $pesan, string $tipe = 'info'): void {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notifikasi (user_id, judul, pesan, tipe, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$user_id, $judul, $pesan, $tipe]);
}

function unread_notif_count(): int {
    if (!is_logged_in()) return 0;
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifikasi WHERE user_id=? AND is_read=0");
    $stmt->execute([$_SESSION['user_id']]);
    return (int) $stmt->fetchColumn();
}
