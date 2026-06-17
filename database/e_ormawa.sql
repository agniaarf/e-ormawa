-- E-ORMAWA Database Schema
-- Versi: 1.0.0

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS e_ormawa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE e_ormawa;

-- =====================================================
-- 1. roles
-- =====================================================
DROP TABLE IF EXISTS roles;
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL UNIQUE, -- Super Admin, Admin Organisasi, Mahasiswa
    deskripsi VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (id, nama, deskripsi) VALUES
(1, 'Super Admin', 'Mengelola seluruh sistem'),
(2, 'Admin Organisasi', 'Mengelola organisasi tertentu'),
(3, 'Mahasiswa', 'Pengguna umum mahasiswa');

-- =====================================================
-- 2. users
-- =====================================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    nim VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL DEFAULT 3,
    no_hp VARCHAR(20),
    jurusan VARCHAR(100),
    angkatan VARCHAR(4),
    foto_profile VARCHAR(255) DEFAULT NULL,
    status ENUM('aktif', 'nonaktif', 'menunggu') DEFAULT 'aktif',
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_expires DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default Super Admin (password: admin123)
INSERT INTO users (nama, email, nim, password, role_id, status) VALUES
('Super Admin', 'admin@eormawa.test', '0000000000', '$2y$10$XRAkND7otu5Rr3NweygK9OBRXUXWzdn/p0UO7gCy3Cvkhc7KtaElS', 1, 'aktif');

-- =====================================================
-- 3. organisasi
-- =====================================================
DROP TABLE IF EXISTS organisasi;
CREATE TABLE organisasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    singkatan VARCHAR(20),
    deskripsi TEXT,
    visi TEXT,
    misi TEXT,
    logo VARCHAR(255),
    ketua_id INT DEFAULT NULL,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ketua_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. anggota
-- =====================================================
DROP TABLE IF EXISTS anggota;
CREATE TABLE anggota (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    organisasi_id INT NOT NULL,
    jabatan VARCHAR(50) DEFAULT 'Anggota',
    divisi VARCHAR(50),
    status ENUM('aktif', 'nonaktif', 'alumni') DEFAULT 'aktif',
    tanggal_masuk DATE DEFAULT (CURRENT_DATE),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organisasi_id) REFERENCES organisasi(id) ON DELETE CASCADE,
    UNIQUE KEY unique_anggota (user_id, organisasi_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. pendaftaran_organisasi
-- =====================================================
DROP TABLE IF EXISTS pendaftaran_organisasi;
CREATE TABLE pendaftaran_organisasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    organisasi_id INT NOT NULL,
    motivasi TEXT,
    cv_link VARCHAR(255),
    status ENUM('menunggu', 'wawancara', 'diterima', 'ditolak') DEFAULT 'menunggu',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organisasi_id) REFERENCES organisasi(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. wawancara
-- =====================================================
DROP TABLE IF EXISTS wawancara;
CREATE TABLE wawancara (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pendaftaran_id INT NOT NULL,
    jadwal DATETIME NOT NULL,
    lokasi VARCHAR(255),
    catatan TEXT,
    hasil ENUM('lulus', 'tidak_lulus', 'menunggu') DEFAULT 'menunggu',
    interviewer_id INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pendaftaran_id) REFERENCES pendaftaran_organisasi(id) ON DELETE CASCADE,
    FOREIGN KEY (interviewer_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. kegiatan
-- =====================================================
DROP TABLE IF EXISTS kegiatan;
CREATE TABLE kegiatan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisasi_id INT NOT NULL,
    nama VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    tipe ENUM('rapat', 'pelatihan', 'proker', 'lomba', 'sosial', 'lainnya') DEFAULT 'proker',
    tanggal_mulai DATETIME NOT NULL,
    tanggal_selesai DATETIME,
    lokasi VARCHAR(255),
    poster VARCHAR(255),
    status ENUM('rencana', 'berlangsung', 'selesai', 'dibatalkan') DEFAULT 'rencana',
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organisasi_id) REFERENCES organisasi(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. peserta_kegiatan
-- =====================================================
DROP TABLE IF EXISTS peserta_kegiatan;
CREATE TABLE peserta_kegiatan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kegiatan_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('terdaftar', 'hadir', 'tidak_hadir', 'batal') DEFAULT 'terdaftar',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kegiatan_id) REFERENCES kegiatan(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_peserta (kegiatan_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. presensi
-- =====================================================
DROP TABLE IF EXISTS presensi;
CREATE TABLE presensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kegiatan_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('hadir', 'izin', 'sakit', 'alpha') DEFAULT 'hadir',
    keterangan VARCHAR(255),
    waktu DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kegiatan_id) REFERENCES kegiatan(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_presensi (kegiatan_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. pengumuman
-- =====================================================
DROP TABLE IF EXISTS pengumuman;
CREATE TABLE pengumuman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    isi TEXT NOT NULL,
    tipe ENUM('global', 'organisasi') DEFAULT 'global',
    organisasi_id INT DEFAULT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organisasi_id) REFERENCES organisasi(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. notifikasi
-- =====================================================
DROP TABLE IF EXISTS notifikasi;
CREATE TABLE notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(150) NOT NULL,
    pesan TEXT NOT NULL,
    tipe VARCHAR(30) DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 12. activity_log
-- =====================================================
DROP TABLE IF EXISTS activity_log;
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    aksi VARCHAR(100) NOT NULL,
    detail TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
