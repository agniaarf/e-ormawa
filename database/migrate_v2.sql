-- =====================================================
-- E-ORMAWA Migration v2
-- Role & Route Refactor
-- =====================================================
-- Personas: Super Admin (id=1), Mahasiswa (id=3)
-- Org-scoped roles: leader, staff, member (via user_organisasi)
-- Run AFTER e_ormawa.sql has been imported.
-- This migration is safe to re-run.
-- =====================================================

USE e_ormawa;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- 1. Simplify roles to 2 personas
-- -----------------------------------------------------
-- Reassign any "Admin Organisasi" (role_id=2) users to Mahasiswa (role_id=3)
UPDATE users SET role_id = 3 WHERE role_id = 2;
-- Remove the Admin Organisasi role
DELETE FROM roles WHERE id = 2;
-- Ensure descriptions are current
UPDATE roles SET deskripsi = 'Mengelola seluruh sistem' WHERE id = 1;
UPDATE roles SET deskripsi = 'Pengguna mahasiswa; peran organisasi diatur per-organisasi' WHERE id = 3;

-- -----------------------------------------------------
-- 2. Soft delete columns
-- -----------------------------------------------------
-- users.deleted_at
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'e_ormawa' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'deleted_at');
SET @sql := IF(@col = 0, 'ALTER TABLE users ADD COLUMN deleted_at DATETIME DEFAULT NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- organisasi.deleted_at
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'e_ormawa' AND TABLE_NAME = 'organisasi' AND COLUMN_NAME = 'deleted_at');
SET @sql := IF(@col = 0, 'ALTER TABLE organisasi ADD COLUMN deleted_at DATETIME DEFAULT NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- kegiatan.deleted_at
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'e_ormawa' AND TABLE_NAME = 'kegiatan' AND COLUMN_NAME = 'deleted_at');
SET @sql := IF(@col = 0, 'ALTER TABLE kegiatan ADD COLUMN deleted_at DATETIME DEFAULT NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- -----------------------------------------------------
-- 3. user_organisasi (replaces anggota as source of truth)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS user_organisasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    organisasi_id INT NOT NULL,
    role ENUM('leader','staff','member') NOT NULL DEFAULT 'member',
    status ENUM('aktif','nonaktif','menunggu') DEFAULT 'aktif',
    deskripsi TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_membership (user_id, organisasi_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organisasi_id) REFERENCES organisasi(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrate existing memberships from anggota (if the table exists)
INSERT IGNORE INTO user_organisasi (user_id, organisasi_id, role, status, created_at)
SELECT a.user_id, a.organisasi_id,
       CASE WHEN LOWER(a.jabatan) IN ('ketua','leader','lead') THEN 'leader' ELSE 'member' END,
       CASE WHEN a.status = 'aktif' THEN 'aktif' ELSE 'nonaktif' END,
       a.created_at
FROM anggota a;

-- Promote each organisasi's ketua_id to leader
INSERT IGNORE INTO user_organisasi (user_id, organisasi_id, role, status, created_at)
SELECT o.ketua_id, o.id, 'leader', 'aktif', NOW()
FROM organisasi o
WHERE o.ketua_id IS NOT NULL;

UPDATE user_organisasi uo
JOIN organisasi o ON o.ketua_id = uo.user_id AND o.id = uo.organisasi_id
SET uo.role = 'leader', uo.status = 'aktif';

-- -----------------------------------------------------
-- 4. permintaan_bergabung (replaces pendaftaran_organisasi + wawancara)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS permintaan_bergabung (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    organisasi_id INT NOT NULL,
    motivasi TEXT,
    status ENUM('menunggu','diterima','ditolak') DEFAULT 'menunggu',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    responded_at DATETIME DEFAULT NULL,
    responded_by INT DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organisasi_id) REFERENCES organisasi(id) ON DELETE CASCADE,
    FOREIGN KEY (responded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrate existing applications (map wawancara/diterima/ditolak/menunggu)
INSERT INTO permintaan_bergabung (user_id, organisasi_id, motivasi, status, created_at)
SELECT po.user_id, po.organisasi_id, po.motivasi,
       CASE
           WHEN po.status = 'diterima' THEN 'diterima'
           WHEN po.status = 'ditolak'  THEN 'ditolak'
           ELSE 'menunggu'
       END,
       po.created_at
FROM pendaftaran_organisasi po;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- Notes:
--  * Old tables (anggota, pendaftaran_organisasi, wawancara) are left intact
--    for safety but are no longer used by the application.
--  * organisasi.ketua_id is retained but the leader is now derived from
--    user_organisasi (role = 'leader').
-- =====================================================
