-- ORBITA Database Schema
-- Versi: 1.0.0

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS orbita CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE orbita;

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
    deleted_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default Super Admin (password: admin123)
INSERT INTO users (nama, email, nim, password, role_id, status) VALUES
('Super Admin', 'admin@orbita.test', '0000000000', '$2y$10$XRAkND7otu5Rr3NweygK9OBRXUXWzdn/p0UO7gCy3Cvkhc7KtaElS', 1, 'aktif');

-- Additional Super Admin Users (password: admin123)
INSERT INTO users (nama, email, nim, password, role_id, status, jurusan, angkatan) VALUES
('Admin Satu', 'admin1@orbita.test', '1000000001', '$2y$10$XRAkND7otu5Rr3NweygK9OBRXUXWzdn/p0UO7gCy3Cvkhc7KtaElS', 1, 'aktif', 'Teknik Informatika', '2020'),
('Admin Dua', 'admin2@orbita.test', '1000000002', '$2y$10$XRAkND7otu5Rr3NweygK9OBRXUXWzdn/p0UO7gCy3Cvkhc7KtaElS', 1, 'aktif', 'Sistem Informasi', '2021'),
('Admin Tiga', 'admin3@orbita.test', '1000000003', '$2y$10$XRAkND7otu5Rr3NweygK9OBRXUXWzdn/p0UO7gCy3Cvkhc7KtaElS', 1, 'aktif', 'Manajemen', '2020'),
('Admin Empat', 'admin4@orbita.test', '1000000004', '$2y$10$XRAkND7otu5Rr3NweygK9OBRXUXWzdn/p0UO7gCy3Cvkhc7KtaElS', 1, 'aktif', 'Teknik Elektro', '2021');

-- Mahasiswa Users (password: mahasiswa123)
INSERT INTO users (nama, email, nim, password, role_id, status, jurusan, angkatan, no_hp) VALUES
('Ahmad Rizky', 'ahmad@orbita.test', '2021001001', '$2y$10$XRcUIw535eLt4XMDlQ/7weM/1HzITjJ6aQg6.enl6DdbVJFUBoqY.', 3, 'aktif', 'Teknik Informatika', '2021', '081234567890'),
('Budi Santoso', 'budi@orbita.test', '2021001002', '$2y$10$XRcUIw535eLt4XMDlQ/7weM/1HzITjJ6aQg6.enl6DdbVJFUBoqY.', 3, 'aktif', 'Teknik Informatika', '2021', '081234567891'),
('Citra Dewi', 'citra@orbita.test', '2021001003', '$2y$10$XRcUIw535eLt4XMDlQ/7weM/1HzITjJ6aQg6.enl6DdbVJFUBoqY.', 3, 'aktif', 'Sistem Informasi', '2021', '081234567892'),
('Dian Pratama', 'dian@orbita.test', '2021001004', '$2y$10$XRcUIw535eLt4XMDlQ/7weM/1HzITjJ6aQg6.enl6DdbVJFUBoqY.', 3, 'aktif', 'Teknik Elektro', '2021', '081234567893'),
('Eka Wijaya', 'eka@orbita.test', '2021001005', '$2y$10$XRcUIw535eLt4XMDlQ/7weM/1HzITjJ6aQg6.enl6DdbVJFUBoqY.', 3, 'aktif', 'Manajemen', '2021', '081234567894'),
('Fajar Nugraha', 'fajar@orbita.test', '2021001006', '$2y$10$XRcUIw535eLt4XMDlQ/7weM/1HzITjJ6aQg6.enl6DdbVJFUBoqY.', 3, 'aktif', 'Teknik Informatika', '2021', '081234567895'),
('Gita Permata', 'gita@orbita.test', '2021001007', '$2y$10$XRcUIw535eLt4XMDlQ/7weM/1HzITjJ6aQg6.enl6DdbVJFUBoqY.', 3, 'aktif', 'Sistem Informasi', '2021', '081234567896'),
('Hendra Saputra', 'hendra@orbita.test', '2021001008', '$2y$10$XRcUIw535eLt4XMDlQ/7weM/1HzITjJ6aQg6.enl6DdbVJFUBoqY.', 3, 'aktif', 'Teknik Elektro', '2021', '081234567897'),
('Indah Sari', 'indah@orbita.test', '2021001009', '$2y$10$XRcUIw535eLt4XMDlQ/7weM/1HzITjJ6aQg6.enl6DdbVJFUBoqY.', 3, 'aktif', 'Manajemen', '2021', '081234567898'),
('Joko Widodo', 'joko@orbita.test', '2021001010', '$2y$10$XRcUIw535eLt4XMDlQ/7weM/1HzITjJ6aQg6.enl6DdbVJFUBoqY.', 3, 'aktif', 'Teknik Informatika', '2021', '081234567899'),
('Kartika Putri', 'kartika@orbita.test', '2021001011', '$2y$10$XRcUIw535eLt4XMDlQ/7weM/1HzITjJ6aQg6.enl6DdbVJFUBoqY.', 3, 'aktif', 'Sistem Informasi', '2021', '081234567900'),
('Lukman Hakim', 'lukman@orbita.test', '2021001012', '$2y$10$XRcUIw535eLt4XMDlQ/7weM/1HzITjJ6aQg6.enl6DdbVJFUBoqY.', 3, 'aktif', 'Teknik Elektro', '2021', '081234567901'),
('Maya Sari', 'maya@orbita.test', '2021001013', '$2y$10$XRcUIw535eLt4XMDlQ/7weM/1HzITjJ6aQg6.enl6DdbVJFUBoqY.', 3, 'aktif', 'Manajemen', '2021', '081234567902'),
('Nurul Hidayah', 'nurul@orbita.test', '2021001014', '$2y$10$XRcUIw535eLt4XMDlQ/7weM/1HzITjJ6aQg6.enl6DdbVJFUBoqY.', 3, 'aktif', 'Teknik Informatika', '2021', '081234567903'),
('Oscar Pratama', 'oscar@orbita.test', '2021001015', '$2y$10$XRcUIw535eLt4XMDlQ/7weM/1HzITjJ6aQg6.enl6DdbVJFUBoqY.', 3, 'aktif', 'Sistem Informasi', '2021', '081234567904'),
('Putri Ayu', 'putri@orbita.test', '2021001016', '$2y$10$XRcUIw535eLt4XMDlQ/7weM/1HzITjJ6aQg6.enl6DdbVJFUBoqY.', 3, 'aktif', 'Teknik Elektro', '2021', '081234567905'),
('Qori Aulia', 'qori@orbita.test', '2021001017', '$2y$10$XRcUIw535eLt4XMDlQ/7weM/1HzITjJ6aQg6.enl6DdbVJFUBoqY.', 3, 'aktif', 'Manajemen', '2021', '081234567906'),
('Rian Hidayat', 'rian@orbita.test', '2021001018', '$2y$10$XRcUIw535eLt4XMDlQ/7weM/1HzITjJ6aQg6.enl6DdbVJFUBoqY.', 3, 'aktif', 'Teknik Informatika', '2021', '081234567907'),
('Siti Aminah', 'siti@orbita.test', '2021001019', '$2y$10$XRcUIw535eLt4XMDlQ/7weM/1HzITjJ6aQg6.enl6DdbVJFUBoqY.', 3, 'aktif', 'Sistem Informasi', '2021', '081234567908'),
('Taufik Hidayat', 'taufik@orbita.test', '2021001020', '$2y$10$XRcUIw535eLt4XMDlQ/7weM/1HzITjJ6aQg6.enl6DdbVJFUBoqY.', 3, 'aktif', 'Teknik Elektro', '2021', '081234567909');

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
    deleted_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ketua_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dummy Organizations
INSERT INTO organisasi (nama, singkatan, deskripsi, visi, misi, status) VALUES
('Himpunan Mahasiswa Teknik Informatika', 'HIMTI', 'Organisasi mahasiswa Teknik Informatika yang berfokus pada pengembangan skill teknologi dan networking.', 'Menjadi himpunan mahasiswa Teknik Informatika yang unggul dalam teknologi dan berdaya saing global.', '1. Mengembangkan skill teknologi mahasiswa\n2. Membangun networking antar mahasiswa\n3. Menyelenggarakan kegiatan edukatif', 'aktif'),
('UKM Paduan Suara Mahasiswa', 'PSM', 'Unit Kegiatan Mahasiswa Paduan Suara yang mengembangkan bakat vokal dan musik mahasiswa.', 'Menjadi UKM paduan suara yang berkualitas dan berprestasi di tingkat nasional.', '1. Melatih vokal dan harmonisasi\n2. Mengikuti kompetisi paduan suara\n3. Mengadakan konser tahunan', 'aktif'),
('BEM Fakultas Teknik', 'BEM FT', 'Badan Eksekutif Mahasiswa Fakultas Teknik yang mewakili aspirasi mahasiswa teknik.', 'Menjadi BEM yang aspiratif, kreatif, dan inovatif dalam melayani mahasiswa.', '1. Menjembatani mahasiswa dengan pihak fakultas\n2. Menyelenggarakan kegiatan kemahasiswaan\n3. Mengembangkan soft skill mahasiswa', 'aktif'),
('UKM Robotika', 'ROBOTIKA', 'Unit Kegiatan Mahasiswa Robotika untuk pengembangan skill robotik dan otomasi.', 'Menjadi pusat pengembangan robotik terdepan di kampus.', '1. Melatih desain dan pemrograman robot\n2. Mengikuti kompetisi robotik nasional\n3. Mengadakan workshop robotik', 'aktif'),
('Himpunan Mahasiswa Manajemen', 'HIMMA', 'Organisasi mahasiswa Manajemen yang berfokus pada pengembangan skill bisnis dan kepemimpinan.', 'Menjadi himpunan mahasiswa Manajemen yang mencetak pemimpin bisnis berkualitas.', '1. Mengembangkan skill bisnis dan manajemen\n2. Menyelenggarakan seminar bisnis\n3. Membangun networking dengan industri', 'aktif');

-- =====================================================
-- 4. user_organisasi
-- =====================================================
DROP TABLE IF EXISTS user_organisasi;
CREATE TABLE user_organisasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    organisasi_id INT NOT NULL,
    role ENUM('leader', 'staff', 'member') DEFAULT 'member',
    status ENUM('aktif', 'nonaktif', 'alumni') DEFAULT 'aktif',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organisasi_id) REFERENCES organisasi(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_org (user_id, organisasi_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dummy User-Organisasi Relationships
-- HIMTI (Org ID 1): Leader Ahmad Rizky (ID 6), Staff Budi Santoso (ID 7), Members Citra Dewi (ID 8), Dian Pratama (ID 9)
INSERT INTO user_organisasi (user_id, organisasi_id, role, status) VALUES
(6, 1, 'leader', 'aktif'),
(7, 1, 'staff', 'aktif'),
(8, 1, 'member', 'aktif'),
(9, 1, 'member', 'aktif');

-- PSM (Org ID 2): Leader Eka Wijaya (ID 10), Staff Fajar Nugraha (ID 11), Members Gita Permata (ID 12), Hendra Saputra (ID 13)
INSERT INTO user_organisasi (user_id, organisasi_id, role, status) VALUES
(10, 2, 'leader', 'aktif'),
(11, 2, 'staff', 'aktif'),
(12, 2, 'member', 'aktif'),
(13, 2, 'member', 'aktif');

-- BEM FT (Org ID 3): Leader Indah Sari (ID 14), Staff Joko Widodo (ID 15), Members Kartika Putri (ID 16), Lukman Hakim (ID 17)
INSERT INTO user_organisasi (user_id, organisasi_id, role, status) VALUES
(14, 3, 'leader', 'aktif'),
(15, 3, 'staff', 'aktif'),
(16, 3, 'member', 'aktif'),
(17, 3, 'member', 'aktif');

-- ROBOTIKA (Org ID 4): Leader Maya Sari (ID 18), Staff Nurul Hidayah (ID 19), Members Oscar Pratama (ID 20), Putri Ayu (ID 21)
INSERT INTO user_organisasi (user_id, organisasi_id, role, status) VALUES
(18, 4, 'leader', 'aktif'),
(19, 4, 'staff', 'aktif'),
(20, 4, 'member', 'aktif'),
(21, 4, 'member', 'aktif');

-- HIMMA (Org ID 5): Leader Qori Aulia (ID 22), Staff Rian Hidayat (ID 23), Members Siti Aminah (ID 24), Taufik Hidayat (ID 25)
INSERT INTO user_organisasi (user_id, organisasi_id, role, status) VALUES
(22, 5, 'leader', 'aktif'),
(23, 5, 'staff', 'aktif'),
(24, 5, 'member', 'aktif'),
(25, 5, 'member', 'aktif');

-- =====================================================
-- 5. permintaan_bergabung
-- =====================================================
DROP TABLE IF EXISTS permintaan_bergabung;
CREATE TABLE permintaan_bergabung (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    organisasi_id INT NOT NULL,
    motivasi TEXT,
    cv_link VARCHAR(255),
    status ENUM('menunggu', 'administrasi', 'wawancara', 'diterima', 'ditolak') DEFAULT 'menunggu',
    responded_at DATETIME DEFAULT NULL,
    responded_by INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organisasi_id) REFERENCES organisasi(id) ON DELETE CASCADE,
    FOREIGN KEY (responded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dummy Permintaan Bergabung (10 registrations)
INSERT INTO permintaan_bergabung (user_id, organisasi_id, motivasi, status) VALUES
(8, 2, 'Saya memiliki ketertarikan dalam musik dan ingin mengembangkan bakat vokal saya', 'diterima'),
(9, 2, 'Ingin belajar harmonisasi dan bergabung dengan komunitas musik', 'diterima'),
(10, 3, 'Ingin berkontribusi dalam kemahasiswaan dan mengembangkan soft skill', 'diterima'),
(11, 3, 'Tertarik dengan kegiatan organisasi kemahasiswaan', 'diterima'),
(12, 4, 'Saya suka robotika dan ingin belajar lebih dalam', 'diterima'),
(13, 4, 'Ingin mengikuti kompetisi robotik dan belajar pemrograman', 'diterima'),
(14, 5, 'Tertarik dengan dunia bisnis dan manajemen', 'diterima'),
(15, 5, 'Ingin belajar strategi bisnis dan networking', 'diterima'),
(16, 1, 'Ingin bergabung dengan HIMTI untuk mengembangkan skill IT', 'menunggu'),
(17, 1, 'Tertarik dengan kegiatan HIMTI dan ingin belajar web development', 'menunggu');

-- =====================================================
-- 6. wawancara
-- =====================================================
DROP TABLE IF EXISTS wawancara;
CREATE TABLE wawancara (
    id INT AUTO_INCREMENT PRIMARY KEY,
    permintaan_id INT NOT NULL,
    jadwal DATETIME NOT NULL,
    lokasi VARCHAR(255),
    catatan TEXT,
    hasil ENUM('lulus', 'tidak_lulus', 'menunggu') DEFAULT 'menunggu',
    interviewer_id INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (permintaan_id) REFERENCES permintaan_bergabung(id) ON DELETE CASCADE,
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
    deleted_at DATETIME DEFAULT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organisasi_id) REFERENCES organisasi(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dummy Kegiatan (Activities)
-- HIMTI Activities
INSERT INTO kegiatan (organisasi_id, nama, deskripsi, tipe, tanggal_mulai, tanggal_selesai, lokasi, status, created_by) VALUES
(1, 'Rapat Perdana HIMTI', 'Rapat perdana pengurus HIMTI periode 2024', 'rapat', '2024-01-15 10:00:00', '2024-01-15 12:00:00', 'Ruang Rapat HIMTI', 'selesai', 6),
(1, 'Workshop Web Development', 'Pelatihan pengembangan website dasar untuk mahasiswa', 'pelatihan', '2024-02-20 09:00:00', '2024-02-20 16:00:00', 'Lab Komputer 1', 'selesai', 6),
(1, 'Hackathon 2024', 'Kompetisi hacking dan pengembangan aplikasi', 'lomba', '2024-03-10 08:00:00', '2024-03-11 18:00:00', 'Aula Utama', 'selesai', 6),
(1, 'Bakti Sosial Coding', 'Mengajar coding ke siswa SMA', 'sosial', '2024-04-05 09:00:00', '2024-04-05 15:00:00', 'SMA Negeri 1', 'selesai', 6),
(1, 'Rapat Evaluasi Semester', 'Evaluasi kegiatan semester ganjil', 'rapat', '2024-06-01 13:00:00', '2024-06-01 15:00:00', 'Ruang Rapat HIMTI', 'selesai', 6);

-- PSM Activities
INSERT INTO kegiatan (organisasi_id, nama, deskripsi, tipe, tanggal_mulai, tanggal_selesai, lokasi, status, created_by) VALUES
(2, 'Latihan Vokal Mingguan', 'Latihan vokal rutin setiap minggu', 'proker', '2024-01-10 16:00:00', '2024-01-10 18:00:00', 'Studio Musik', 'selesai', 10),
(2, 'Audisi Anggota Baru', 'Audisi penerimaan anggota baru PSM', 'proker', '2024-02-01 09:00:00', '2024-02-02 17:00:00', 'Aula Musik', 'selesai', 10),
(2, 'Workshop Harmonisasi', 'Pelatihan teknik harmonisasi paduan suara', 'pelatihan', '2024-03-15 10:00:00', '2024-03-15 14:00:00', 'Studio Musik', 'selesai', 10),
(2, 'Konser Tahunan', 'Konser paduan suara tahunan PSM', 'proker', '2024-05-20 19:00:00', '2024-05-20 21:00:00', 'Aula Utama', 'selesai', 10),
(2, 'Kompetisi Paduan Suara Nasional', 'Partisipasi dalam kompetisi nasional', 'lomba', '2024-07-15 08:00:00', '2024-07-17 18:00:00', 'Jakarta Convention Center', 'selesai', 10);

-- BEM FT Activities
INSERT INTO kegiatan (organisasi_id, nama, deskripsi, tipe, tanggal_mulai, tanggal_selesai, lokasi, status, created_by) VALUES
(3, 'Rapat Kerja BEM', 'Penyusunan program kerja BEM FT', 'rapat', '2024-01-05 09:00:00', '2024-01-05 16:00:00', 'Ruang BEM', 'selesai', 14),
(3, 'Open Recruitment Staff', 'Penerimaan staff baru BEM FT', 'proker', '2024-02-10 08:00:00', '2024-02-12 17:00:00', 'Gedung Teknik', 'selesai', 14),
(3, 'Seminar Kepemimpinan', 'Seminar tentang kepemimpinan mahasiswa', 'pelatihan', '2024-03-20 09:00:00', '2024-03-20 12:00:00', 'Aula Teknik', 'selesai', 14),
(3, 'Bakti Sosial BEM', 'Kegiatan sosial kemasyarakatan', 'sosial', '2024-04-15 08:00:00', '2024-04-15 16:00:00', 'Desa Binaan', 'selesai', 14),
(3, 'Rapat Evaluasi Tengah Periode', 'Evaluasi kinerja BEM tengah periode', 'rapat', '2024-06-15 13:00:00', '2024-06-15 16:00:00', 'Ruang BEM', 'selesai', 14);

-- ROBOTIKA Activities
INSERT INTO kegiatan (organisasi_id, nama, deskripsi, tipe, tanggal_mulai, tanggal_selesai, lokasi, status, created_by) VALUES
(4, 'Pelatihan Arduino Dasar', 'Pelatihan dasar pemrograman Arduino', 'pelatihan', '2024-01-12 09:00:00', '2024-01-12 16:00:00', 'Lab Robotika', 'selesai', 18),
(4, 'Kompetisi Robotik Regional', 'Partisipasi kompetisi robotik regional', 'lomba', '2024-03-05 08:00:00', '2024-03-06 18:00:00', 'Universitas Sebelas Maret', 'selesai', 18),
(4, 'Workshop Robot Line Follower', 'Pelatihan membuat robot line follower', 'pelatihan', '2024-04-10 09:00:00', '2024-04-10 15:00:00', 'Lab Robotika', 'selesai', 18),
(4, 'Expo Robotika', 'Pameran karya robotika mahasiswa', 'proker', '2024-05-25 10:00:00', '2024-05-25 16:00:00', 'Lobby Gedung Teknik', 'selesai', 18),
(4, 'Rapat Perencanaan Proker', 'Perencanaan program kerja semester baru', 'rapat', '2024-07-01 14:00:00', '2024-07-01 16:00:00', 'Lab Robotika', 'selesai', 18);

-- HIMMA Activities
INSERT INTO kegiatan (organisasi_id, nama, deskripsi, tipe, tanggal_mulai, tanggal_selesai, lokasi, status, created_by) VALUES
(5, 'Seminar Bisnis Digital', 'Seminar tentang bisnis di era digital', 'pelatihan', '2024-01-18 09:00:00', '2024-01-18 12:00:00', 'Aula Manajemen', 'selesai', 22),
(5, 'Business Plan Competition', 'Kompetisi rencana bisnis mahasiswa', 'lomba', '2024-02-25 08:00:00', '2024-02-26 18:00:00', 'Aula Manajemen', 'selesai', 22),
(5, 'Workshop Marketing', 'Pelatihan strategi pemasaran', 'pelatihan', '2024-03-22 09:00:00', '2024-03-22 15:00:00', 'Ruang Seminar', 'selesai', 22),
(5, 'Kunjungan Industri', 'Kunjungan ke perusahaan manufaktur', 'proker', '2024-04-20 07:00:00', '2024-04-20 17:00:00', 'PT Astra Honda Motor', 'selesai', 22),
(5, 'Rapat Akhir Tahun', 'Rapat evaluasi akhir tahun HIMMA', 'rapat', '2024-12-15 13:00:00', '2024-12-15 16:00:00', 'Ruang HIMMA', 'selesai', 22);

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

-- Dummy Peserta Kegiatan (30 participants)
INSERT INTO peserta_kegiatan (kegiatan_id, user_id, status) VALUES
(1, 8, 'hadir'), (1, 9, 'hadir'), (1, 10, 'hadir'),
(2, 8, 'hadir'), (2, 9, 'hadir'), (2, 11, 'hadir'),
(3, 8, 'hadir'), (3, 9, 'hadir'), (3, 12, 'hadir'),
(4, 8, 'hadir'), (4, 9, 'hadir'), (4, 13, 'hadir'),
(5, 8, 'hadir'), (5, 9, 'hadir'), (5, 10, 'hadir'),
(6, 12, 'hadir'), (6, 13, 'hadir'), (6, 14, 'hadir'),
(7, 12, 'hadir'), (7, 13, 'hadir'), (7, 15, 'hadir'),
(8, 12, 'hadir'), (8, 13, 'hadir'), (8, 16, 'hadir'),
(9, 12, 'hadir'), (9, 13, 'hadir'), (9, 17, 'hadir'),
(10, 12, 'hadir'), (10, 13, 'hadir'), (10, 14, 'hadir');

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

-- Dummy Pengumuman (10 announcements)
INSERT INTO pengumuman (judul, isi, tipe, organisasi_id, created_by) VALUES
('Selamat Datang di ORBITA', 'Selamat datang di sistem manajemen organisasi mahasiswa. Silakan jelajahi fitur-fitur yang tersedia.', 'global', NULL, 1),
('Pengumuman Libur Nasional', 'Diberitahukan bahwa kampus akan libur pada tanggal 17 Agustus dalam rangka Hari Kemerdekaan.', 'global', NULL, 1),
('Workshop HIMTI: Web Development', 'HIMTI mengadakan workshop web development pada tanggal 20 Februari 2024. Silakan daftar segera.', 'organisasi', 1, 6),
('Audisi PSM Anggota Baru', 'UKM Paduan Suara Mahasiswa membuka pendaftaran anggota baru. Audisi akan dilaksanakan pada 1-2 Februari 2024.', 'organisasi', 2, 10),
('Open Recruitment BEM FT', 'BEM Fakultas Teknik membuka pendaftaran staff baru. Daftar sebelum 10 Februari 2024.', 'organisasi', 3, 14),
('Pelatihan Arduino ROBOTIKA', 'UKM Robotika mengadakan pelatihan Arduino dasar pada 12 Januari 2024. Tempat terbatas.', 'organisasi', 4, 18),
('Seminar Bisnis HIMMA', 'Himpunan Mahasiswa Manajemen menyelenggarakan seminar bisnis digital pada 18 Januari 2024.', 'organisasi', 5, 22),
('Maintenance Sistem', 'Sistem akan melakukan maintenance pada tanggal 15 Januari 2024 pukul 00:00-02:00 WIB.', 'global', NULL, 1),
('Perubahan Jadwal Kuliah', 'Ada perubahan jadwal kuliah untuk semester genap. Silakan cek portal akademik.', 'global', NULL, 1),
('Kompetisi Robotik Regional', 'ROBOTIKA akan mengikuti kompetisi robotik regional di UNS pada 5-6 Maret 2024.', 'organisasi', 4, 18);

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

-- Dummy Notifikasi (20 notifications)
INSERT INTO notifikasi (user_id, judul, pesan, tipe, is_read) VALUES
(6, 'Selamat Datang', 'Selamat datang di ORBITA sebagai Leader HIMTI', 'info', 1),
(7, 'Selamat Datang', 'Selamat datang di ORBITA sebagai Staff HIMTI', 'info', 1),
(8, 'Selamat Datang', 'Selamat datang di ORBITA sebagai Member HIMTI', 'info', 1),
(10, 'Selamat Datang', 'Selamat datang di ORBITA sebagai Leader PSM', 'info', 1),
(14, 'Selamat Datang', 'Selamat datang di ORBITA sebagai Leader BEM FT', 'info', 1),
(18, 'Selamat Datang', 'Selamat datang di ORBITA sebagai Leader ROBOTIKA', 'info', 1),
(22, 'Selamat Datang', 'Selamat datang di ORBITA sebagai Leader HIMMA', 'info', 1),
(8, 'Pendaftaran Diterima', 'Selamat! Anda telah diterima sebagai anggota PSM', 'success', 1),
(9, 'Pendaftaran Diterima', 'Selamat! Anda telah diterima sebagai anggota PSM', 'success', 1),
(10, 'Pendaftaran Diterima', 'Selamat! Anda telah diterima sebagai anggota BEM FT', 'success', 1),
(11, 'Pendaftaran Diterima', 'Selamat! Anda telah diterima sebagai anggota BEM FT', 'success', 1),
(12, 'Pendaftaran Diterima', 'Selamat! Anda telah diterima sebagai anggota ROBOTIKA', 'success', 1),
(13, 'Pendaftaran Diterima', 'Selamat! Anda telah diterima sebagai anggota ROBOTIKA', 'success', 1),
(14, 'Pendaftaran Diterima', 'Selamat! Anda telah diterima sebagai anggota HIMMA', 'success', 1),
(15, 'Pendaftaran Diterima', 'Selamat! Anda telah diterima sebagai anggota HIMMA', 'success', 1),
(6, 'Kegiatan Baru', 'Workshop Web Development telah ditambahkan', 'info', 0),
(10, 'Kegiatan Baru', 'Audisi Anggota Baru telah ditambahkan', 'info', 0),
(14, 'Kegiatan Baru', 'Open Recruitment Staff telah ditambahkan', 'info', 0),
(18, 'Kegiatan Baru', 'Pelatihan Arduino Dasar telah ditambahkan', 'info', 0),
(22, 'Kegiatan Baru', 'Seminar Bisnis Digital telah ditambahkan', 'info', 0);

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

-- Dummy Activity Log (30 activity logs)
INSERT INTO activity_log (user_id, aksi, detail, ip_address) VALUES
(1, 'Login', 'Super Admin login ke sistem', '127.0.0.1'),
(6, 'Login', 'Ahmad Rizky login ke sistem', '127.0.0.1'),
(6, 'Create Kegiatan', 'Membuat kegiatan Rapat Perdana HIMTI', '127.0.0.1'),
(6, 'Create Kegiatan', 'Membuat kegiatan Workshop Web Development', '127.0.0.1'),
(6, 'Update Member', 'Update member Budi Santosa menjadi staff', '127.0.0.1'),
(10, 'Login', 'Eka Wijaya login ke sistem', '127.0.0.1'),
(10, 'Create Kegiatan', 'Membuat kegiatan Latihan Vokal Mingguan', '127.0.0.1'),
(10, 'Create Kegiatan', 'Membuat kegiatan Audisi Anggota Baru', '127.0.0.1'),
(14, 'Login', 'Indah Sari login ke sistem', '127.0.0.1'),
(14, 'Create Kegiatan', 'Membuat kegiatan Rapat Kerja BEM', '127.0.0.1'),
(14, 'Create Kegiatan', 'Membuat kegiatan Open Recruitment Staff', '127.0.0.1'),
(18, 'Login', 'Maya Sari login ke sistem', '127.0.0.1'),
(18, 'Create Kegiatan', 'Membuat kegiatan Pelatihan Arduino Dasar', '127.0.0.1'),
(18, 'Create Kegiatan', 'Membuat kegiatan Kompetisi Robotik Regional', '127.0.0.1'),
(22, 'Login', 'Qori Aulia login ke sistem', '127.0.0.1'),
(22, 'Create Kegiatan', 'Membuat kegiatan Seminar Bisnis Digital', '127.0.0.1'),
(22, 'Create Kegiatan', 'Membuat kegiatan Business Plan Competition', '127.0.0.1'),
(8, 'Join Organisasi', 'Bergabung dengan HIMTI', '127.0.0.1'),
(9, 'Join Organisasi', 'Bergabung dengan HIMTI', '127.0.0.1'),
(12, 'Join Organisasi', 'Bergabung dengan PSM', '127.0.0.1'),
(13, 'Join Organisasi', 'Bergabung dengan PSM', '127.0.0.1'),
(16, 'Join Organisasi', 'Bergabung dengan BEM FT', '127.0.0.1'),
(17, 'Join Organisasi', 'Bergabung dengan BEM FT', '127.0.0.1'),
(20, 'Join Organisasi', 'Bergabung dengan ROBOTIKA', '127.0.0.1'),
(21, 'Join Organisasi', 'Bergabung dengan ROBOTIKA', '127.0.0.1'),
(24, 'Join Organisasi', 'Bergabung dengan HIMMA', '127.0.0.1'),
(25, 'Join Organisasi', 'Bergabung dengan HIMMA', '127.0.0.1'),
(1, 'Create Pengumuman', 'Membuat pengumuman Selamat Datang di ORBITA', '127.0.0.1'),
(1, 'Create Pengumuman', 'Membuat pengumuman Pengumuman Libur Nasional', '127.0.0.1'),
(6, 'Update Kegiatan', 'Update kegiatan Hackathon 2024', '127.0.0.1'),
(10, 'Update Kegiatan', 'Update kegiatan Konser Tahunan', '127.0.0.1');

SET FOREIGN_KEY_CHECKS = 1;
