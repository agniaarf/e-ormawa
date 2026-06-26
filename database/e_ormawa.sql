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
    nama VARCHAR(50) NOT NULL UNIQUE, -- Super Admin, Mahasiswa
    deskripsi VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (id, nama, deskripsi) VALUES
(1, 'Super Admin', 'Mengelola seluruh sistem'),
(2, 'Mahasiswa', 'Pengguna umum mahasiswa (sub-role: leader, staff, member)');

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
    role_id INT NOT NULL DEFAULT 2,
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

-- =====================================================
-- DUMMY DATA
-- Password for all dummy users: password
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- =====================================================

-- Additional Super Admin
INSERT INTO users (nama, email, nim, password, role_id, no_hp, jurusan, angkatan, status) VALUES
('Admin Utama', 'superadmin@eormawa.test', '0000000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '081234567890', 'Teknik Informatika', '2020', 'aktif');

-- Mahasiswa with Leader role (defined in anggota table)
INSERT INTO users (nama, email, nim, password, role_id, no_hp, jurusan, angkatan, status) VALUES
('Budi Santoso', 'budi@eormawa.test', '2021001001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, '081234567891', 'Manajemen', '2021', 'aktif'),
('Siti Rahayu', 'siti@eormawa.test', '2021001002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, '081234567892', 'Akuntansi', '2021', 'aktif'),
('Ahmad Wijaya', 'ahmad@eormawa.test', '2021001003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, '081234567893', 'Teknik Sipil', '2021', 'aktif');

-- Mahasiswa (Regular Members)
INSERT INTO users (nama, email, nim, password, role_id, no_hp, jurusan, angkatan, status) VALUES
('Dewi Lestari', 'dewi@eormawa.test', '2022001001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, '081234567894', 'Teknik Informatika', '2022', 'aktif'),
('Rizky Pratama', 'rizky@eormawa.test', '2022001002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, '081234567895', 'Sistem Informasi', '2022', 'aktif'),
('Maya Sari', 'maya@eormawa.test', '2022001003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, '081234567896', 'Desain Komunikasi Visual', '2022', 'aktif'),
('Fajar Nugraha', 'fajar@eormawa.test', '2022001004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, '081234567897', 'Teknik Elektro', '2022', 'aktif'),
('Lina Permata', 'lina@eormawa.test', '2022001005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, '081234567898', 'Psikologi', '2022', 'aktif'),
('Dimas Anggara', 'dimas@eormawa.test', '2023001001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, '081234567899', 'Teknik Mesin', '2023', 'aktif'),
('Citra Kirana', 'citra@eormawa.test', '2023001002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, '081234567900', 'Farmasi', '2023', 'aktif'),
('Bayu Saputra', 'bayu@eormawa.test', '2023001003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, '081234567901', 'Hukum', '2023', 'aktif');

-- Organizations
INSERT INTO organisasi (nama, singkatan, deskripsi, visi, misi, ketua_id, status) VALUES
('Himpunan Mahasiswa Teknik Informatika', 'HIMATIF', 'Organisasi mahasiswa Teknik Informatika yang berfokus pada pengembangan skill teknologi dan networking.', 'Menjadi himpunan mahasiswa Teknik Informatika yang unggul dan inovatif.', '1. Mengembangkan skill teknologi mahasiswa\n2. Membangun networking antar mahasiswa\n3. Mengadakan kegiatan yang bermanfaat', 3, 'aktif'),
('Unit Kegiatan Mahasiswa Seni Tari', 'UKM Tari', 'UKM yang bergerak di bidang seni tari tradisional dan modern.', 'Melestarikan seni tari Indonesia dan mengembangkan kreativitas mahasiswa.', '1. Menyelenggarakan pelatihan tari\n2. Mengadakan pertunjukan seni\n3. Mengikuti kompetisi tari', 4, 'aktif'),
('Himpunan Mahasiswa Manajemen', 'HIMMA', 'Organisasi mahasiswa Manajemen untuk pengembangan skill bisnis dan kepemimpinan.', 'Mencetak mahasiswa Manajemen yang siap berkarir di dunia bisnis.', '1. Mengadakan seminar bisnis\n2. Pelatihan kepemimpinan\n3. Kunjungan industri', 5, 'aktif'),
('Komunitas Pemrograman', 'CODEX', 'Komunitas untuk pecinta pemrograman dan pengembangan software.', 'Menjadi komunitas pemrograman terbesar di kampus.', '1. Mengadakan hackathon\n2. Workshop pemrograman\n3. Sharing session developer', 6, 'aktif'),
('UKM Olahraga Basket', 'UKM Basket', 'UKM basket untuk mengembangkan bakat olahraga mahasiswa.', 'Membentuk tim basket yang kompetitif.', '1. Latihan rutin\n2. Mengikuti kompetisi\n3. Turnamen antar fakultas', 7, 'aktif');

-- User-Organisasi Relationships (anggota table)
INSERT INTO anggota (user_id, organisasi_id, jabatan, divisi, status, tanggal_masuk) VALUES
(3, 1, 'Ketua', 'Inti', 'aktif', '2023-01-15'),
(6, 1, 'Staff', 'Pengembangan Skill', 'aktif', '2023-02-01'),
(7, 1, 'Anggota', 'Pengembangan Skill', 'aktif', '2023-02-01'),
(8, 1, 'Staff', 'Humas', 'aktif', '2023-03-01'),
(4, 2, 'Ketua', 'Inti', 'aktif', '2023-01-20'),
(9, 1, 'Anggota', 'Inti', 'aktif', '2023-02-15'),
(10, 2, 'Staff', 'Koreografi', 'aktif', '2023-02-20'),
(11, 2, 'Anggota', 'Koreografi', 'aktif', '2023-03-10'),
(5, 3, 'Ketua', 'Inti', 'aktif', '2023-01-10'),
(12, 3, 'Staff', 'Bisnis', 'aktif', '2023-02-05'),
(13, 3, 'Anggota', 'Humas', 'aktif', '2023-02-05'),
(6, 4, 'Ketua', 'Inti', 'aktif', '2023-01-25'),
(7, 4, 'Staff', 'Web Development', 'aktif', '2023-03-01'),
(11, 4, 'Anggota', 'Mobile Development', 'aktif', '2023-03-15'),
(7, 5, 'Ketua', 'Inti', 'aktif', '2023-01-30'),
(8, 5, 'Staff', 'Tim Inti', 'aktif', '2023-02-10'),
(13, 5, 'Anggota', 'Tim Inti', 'aktif', '2023-02-10');

-- Activities (Kegiatan)
INSERT INTO kegiatan (organisasi_id, nama, deskripsi, tipe, tanggal_mulai, tanggal_selesai, lokasi, status, created_by) VALUES
(1, 'Workshop Web Development', 'Pelatihan dasar web development dengan HTML, CSS, dan JavaScript', 'pelatihan', '2024-02-15 09:00:00', '2024-02-15 17:00:00', 'Lab Komputer A', 'selesai', 3),
(1, 'Hackathon 2024', 'Kompetisi pembuatan aplikasi dalam 24 jam', 'lomba', '2024-03-20 09:00:00', '2024-03-21 09:00:00', 'Aula Utama', 'selesai', 3),
(1, 'Rapat Kerja Semester', 'Perencanaan kegiatan semester ganjil 2024', 'rapat', '2024-07-01 13:00:00', '2024-07-01 16:00:00', 'Ruang Meeting HIMATIF', 'rencana', 3),
(2, 'Pertunjukan Seni Tari', 'Pertunjukan tari tradisional dalam acara Dies Natalis', 'sosial', '2024-04-15 19:00:00', '2024-04-15 21:00:00', 'Gedung Serbaguna', 'selesai', 4),
(2, 'Latihan Rutin Tari Jawa', 'Latihan tari jawa mingguan', 'proker', '2024-07-02 16:00:00', '2024-07-02 18:00:00', 'Studio Tari', 'berlangsung', 4),
(3, 'Seminar Bisnis Digital', 'Seminar tentang peluang bisnis di era digital', 'pelatihan', '2024-05-10 09:00:00', '2024-05-10 12:00:00', 'Auditorium', 'selesai', 5),
(3, 'Kunjungan Industri ke Startup', 'Kunjungan ke perusahaan startup lokal', 'proker', '2024-07-05 08:00:00', '2024-07-05 16:00:00', 'PT Tech Startup', 'rencana', 5),
(4, 'Code Review Session', 'Sesi review code antar member', 'proker', '2024-06-01 19:00:00', '2024-06-01 21:00:00', 'Lab Komputer B', 'selesai', 6),
(4, 'Workshop Mobile Development', 'Pelatihan pengembangan aplikasi mobile dengan Flutter', 'pelatihan', '2024-07-10 09:00:00', '2024-07-10 17:00:00', 'Lab Komputer A', 'rencana', 6),
(5, 'Latihan Basket Mingguan', 'Latihan rutin tim basket', 'proker', '2024-07-03 16:00:00', '2024-07-03 18:00:00', 'Lapangan Basket', 'berlangsung', 7),
(5, 'Turnamen Basket Antar Fakultas', 'Kompetisi basket antar fakultas', 'lomba', '2024-08-15 09:00:00', '2024-08-17 18:00:00', 'Gedung Olahraga', 'rencana', 7);

-- Activity Participants
INSERT INTO peserta_kegiatan (kegiatan_id, user_id, status) VALUES
(1, 6, 'hadir'),
(1, 7, 'hadir'),
(1, 8, 'hadir'),
(1, 9, 'hadir'),
(2, 6, 'hadir'),
(2, 7, 'hadir'),
(2, 11, 'hadir'),
(6, 12, 'hadir'),
(6, 13, 'hadir'),
(6, 6, 'hadir'),
(8, 6, 'hadir'),
(8, 7, 'hadir'),
(8, 11, 'hadir');

-- Announcements (Pengumuman)
INSERT INTO pengumuman (judul, isi, tipe, organisasi_id, created_by) VALUES
('Pendaftaran Anggota Baru HIMATIF Dibuka', 'Pendaftaran anggota baru HIMATIF untuk tahun ajaran 2024/2025 telah dibuka. Silakan daftar melalui form yang tersedia.', 'organisasi', 1, 3),
('Workshop Web Development Gratis', 'HIMATIF mengadakan workshop web development gratis untuk semua mahasiswa. Daftar sekarang!', 'organisasi', 1, 3),
('Undangan Rapat Kerja Semester', 'Diundang seluruh pengurus HIMATIF untuk menghadiri rapat kerja semester.', 'organisasi', 1, 3),
('Pertunjukan Seni Tari - Dies Natalis', 'UKM Tari akan mengadakan pertunjukan seni dalam rangka Dies Natalis kampus.', 'organisasi', 2, 4),
('Turnamen Basket Antar Fakultas', 'UKM Basket akan mengadakan turnamen basket antar fakultas. Segera daftarkan tim fakultas Anda!', 'organisasi', 5, 7),
('Pengumuman Libur Semester', 'Libur semester ganjil akan dimulai pada tanggal 20 Desember 2024.', 'global', NULL, 1);

-- Notifications
INSERT INTO notifikasi (user_id, judul, pesan, tipe, is_read) VALUES
(6, 'Pendaftaran Diterima', 'Selamat! Anda telah diterima sebagai anggota HIMATIF.', 'success', 0),
(6, 'Undangan Workshop', 'Anda diundang untuk menghadiri Workshop Web Development.', 'info', 0),
(6, 'Pengingat Kegiatan', 'Workshop Web Development akan dimulai besok pukul 09:00.', 'reminder', 1),
(7, 'Pendaftaran Diterima', 'Selamat! Anda telah diterima sebagai anggota HIMATIF.', 'success', 0),
(7, 'Undangan Hackathon', 'Anda diundang untuk mengikuti Hackathon 2024.', 'info', 0),
(8, 'Pendaftaran Diterima', 'Selamat! Anda telah diterima sebagai anggota HIMATIF.', 'success', 1),
(8, 'Tugas Baru', 'Anda ditugaskan untuk mengurus kehumasan HIMATIF.', 'info', 0),
(3, 'Permintaan Bergabung Baru', 'Ada 3 permintaan bergabung baru yang perlu diproses.', 'info', 0),
(3, 'Jadwal Rapat', 'Rapat kerja semester dijadwalkan tanggal 1 Juli 2024.', 'reminder', 1);

-- Activity Log
INSERT INTO activity_log (user_id, aksi, detail, ip_address, user_agent) VALUES
(1, 'Login', 'Super Admin login ke sistem', '127.0.0.1', 'Mozilla/5.0'),
(3, 'Login', 'Budi Santoso login ke sistem', '127.0.0.1', 'Mozilla/5.0'),
(3, 'Buat Organisasi', 'Membuat organisasi HIMATIF', '127.0.0.1', 'Mozilla/5.0'),
(3, 'Update Organisasi', 'Update deskripsi organisasi HIMATIF', '127.0.0.1', 'Mozilla/5.0'),
(6, 'Login', 'Dewi Lestari login ke sistem', '127.0.0.1', 'Mozilla/5.0'),
(6, 'Gabung Organisasi', 'Bergabung dengan HIMATIF', '127.0.0.1', 'Mozilla/5.0'),
(3, 'Terima Anggota', 'Menerima Dewi Lestari sebagai anggota HIMATIF', '127.0.0.1', 'Mozilla/5.0'),
(3, 'Buat Kegiatan', 'Membuat kegiatan Workshop Web Development', '127.0.0.1', 'Mozilla/5.0'),
(6, 'Daftar Kegiatan', 'Mendaftar Workshop Web Development', '127.0.0.1', 'Mozilla/5.0'),
(3, 'Buat Pengumuman', 'Membuat pengumuman pendaftaran anggota baru', '127.0.0.1', 'Mozilla/5.0');

-- Registration Requests (Pendaftaran Organisasi)
INSERT INTO pendaftaran_organisasi (user_id, organisasi_id, motivasi, status) VALUES
(9, 1, 'Saya ingin mengembangkan skill pemrograman dan berkontribusi dalam kegiatan HIMATIF.', 'diterima'),
(10, 1, 'Tertarik dengan dunia teknologi dan ingin belajar lebih banyak.', 'menunggu'),
(11, 4, 'Penggemar pemrograman dan ingin bergabung dengan komunitas developer.', 'diterima'),
(12, 5, 'Hobi bermain basket dan ingin mengembangkan skill olahraga.', 'menunggu');
