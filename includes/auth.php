<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function register_user(
    string $nama,
    string $email,
    string $nim,
    string $password,
    int $role_id = 3, // default Mahasiswa
    ?string $no_hp = null,
    ?string $jurusan = null,
    ?string $angkatan = null
): array {
    global $pdo;

    // Check existing
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR nim = ? LIMIT 1");
    $stmt->execute([$email, $nim]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email atau NIM sudah terdaftar.'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (nama, email, nim, password, role_id, no_hp, jurusan, angkatan, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'aktif', NOW())");
    $stmt->execute([$nama, $email, $nim, $hash, $role_id, $no_hp, $jurusan, $angkatan]);

    $user_id = (int) $pdo->lastInsertId();
    log_activity($user_id, 'Register', 'Pendaftaran akun baru');

    return ['success' => true, 'message' => 'Registrasi berhasil. Silakan login.', 'user_id' => $user_id];
}

function login_user(string $identifier, string $password): array {
    global $pdo;

    $stmt = $pdo->prepare("SELECT u.*, r.nama as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE (u.email = ? OR u.nim = ?) AND u.status = 'aktif' LIMIT 1");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'Akun tidak ditemukan atau dinonaktifkan.'];
    }

    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Password salah.'];
    }

    // Rehash if needed
    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$new_hash, $user['id']]);
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role_name'];
    $_SESSION['nama'] = $user['nama'];

    log_activity($user['id'], 'Login', 'Masuk ke sistem');

    return ['success' => true, 'message' => 'Login berhasil.', 'user' => $user];
}

function logout_user(): void {
    if (is_logged_in()) {
        log_activity($_SESSION['user_id'], 'Logout', 'Keluar dari sistem');
    }
    $_SESSION = [];
    session_destroy();
}

function send_reset_token(string $email): array {
    global $pdo;

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'Email tidak ditemukan.'];
    }

    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?")
        ->execute([$token, $expires, $user['id']]);

    // In production, send email here
    return ['success' => true, 'message' => 'Token reset telah dikirim (simulasi: ' . $token . ')'];
}

function reset_password(string $token, string $new_password): array {
    global $pdo;

    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'Token tidak valid atau sudah kadaluarsa.'];
    }

    $hash = password_hash($new_password, PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?")
        ->execute([$hash, $user['id']]);

    return ['success' => true, 'message' => 'Password berhasil direset. Silakan login.'];
}
