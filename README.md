# ORBITA

Sistem manajemen organisasi mahasiswa berbasis web yang memfasilitasi pengelolaan organisasi, kegiatan, dan keanggotaan mahasiswa di lingkungan kampus.

## 📋 Project Overview

ORBITA (Organisasi Berbasis Integrasi Teknologi Aplikasi) adalah aplikasi web yang dirancang untuk mempermudah pengelolaan organisasi mahasiswa, termasuk himpunan mahasiswa, UKM, dan BEM. Sistem ini menyediakan fitur lengkap untuk manajemen anggota, kegiatan, pendaftaran, dan notifikasi.

### Fitur Utama

- **Manajemen Organisasi**: Membuat, mengedit, dan menghapus organisasi
- **Manajemen Anggota**: Kelola anggota dengan role-based access (Leader, Staff, Member)
- **Manajemen Kegiatan**: Buat dan kelola kegiatan organisasi dengan berbagai tipe
- **Sistem Pendaftaran**: Proses pendaftaran anggota baru dengan wawancara
- **Kartu Anggota Digital**: Generate kartu anggota dalam format gambar (PNG) atau PDF
- **Notifikasi**: Sistem notifikasi real-time untuk update kegiatan dan informasi
- **Laporan**: Dashboard dengan visualisasi data menggunakan Chart.js
- **Activity Log**: Tracking aktivitas pengguna untuk audit trail

### Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Libraries**:
  - Chart.js (Data visualization)
  - html2canvas (Screenshot generation)
  - jsPDF (PDF generation)
  - Tailwind CSS (Styling)

## 🚀 Installation Instructions

### Prerequisites

- XAMPP (atau web server lain dengan PHP dan MySQL)
- PHP 7.4 atau lebih tinggi
- MySQL/MariaDB
- Web browser modern (Chrome, Firefox, Edge)

### Langkah-langkah Instalasi

1. **Clone atau download repository**
   ```bash
   cd htdocs
   # Copy folder orbita ke htdocs XAMPP
   ```

2. **Konfigurasi Database**
   - Buka phpMyAdmin di `http://localhost/phpmyadmin`
   - Buat database baru bernama `orbita`
   - Import file `database/orbita.sql` ke database tersebut

3. **Konfigurasi Aplikasi**
   - Pastikan file `includes/config.php` sudah terkonfigurasi dengan benar
   - Sesuaikan konfigurasi database jika diperlukan:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'orbita');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

4. **Jalankan Aplikasi**
   - Start Apache dan MySQL di XAMPP
   - Buka browser dan akses `http://localhost/orbita`

## 👥 User Guide

### Role Descriptions

#### Super Admin
- **Akses Penuh**: Memiliki akses penuh ke seluruh sistem
- **Manajemen Organisasi**: Dapat membuat, mengedit, dan menghapus organisasi
- **Manajemen Pengguna**: Dapat mengelola semua pengguna sistem
- **Dashboard**: Melihat statistik global seluruh organisasi

#### Leader (Ketua Organisasi)
- **Manajemen Organisasi**: Kelola organisasi yang dipimpin
- **Assign Role**: Dapat mengubah role member (leader, staff, member)
- **Manajemen Member**: Tambah, edit, dan hapus anggota
- **Manajemen Kegiatan**: Buat, edit, dan hapus kegiatan
- **Pendaftaran**: Terima atau tolak pendaftaran anggota baru
- **Menu**: Manajemen Member, Manajemen Kegiatan

#### Staff
- **Manajemen Member**: Edit status member (tidak dapat mengubah role)
- **Manajemen Kegiatan**: Buat, edit, dan hapus kegiatan
- **Pendaftaran**: Terima atau tolak pendaftaran anggota baru
- **Menu**: Manajemen Member, Manajemen Kegiatan

#### Member
- **View Only**: Hanya dapat melihat informasi
- **Kartu Anggota**: Dapat melihat dan download kartu anggota
- **Kegiatan**: Dapat melihat dan mendaftar ke kegiatan
- **Menu**: Lihat Member, Lihat Kegiatan

### Fitur per Role

| Fitur | Super Admin | Leader | Staff | Member |
|-------|-------------|--------|-------|--------|
| Dashboard Global | ✅ | ❌ | ❌ | ❌ |
| Kelola Organisasi | ✅ | ❌ | ❌ | ❌ |
| Kelola Pengguna | ✅ | ❌ | ❌ | ❌ |
| Assign Role | ❌ | ✅ | ❌ | ❌ |
| Manajemen Member | ❌ | ✅ | ✅ | ❌ |
| Manajemen Kegiatan | ❌ | ✅ | ✅ | ❌ |
| Lihat Member | ❌ | ✅ | ✅ | ✅ |
| Lihat Kegiatan | ❌ | ✅ | ✅ | ✅ |
| Kartu Anggota | ❌ | ✅ | ✅ | ✅ |
| Daftar Kegiatan | ❌ | ✅ | ✅ | ✅ |

## 🔐 Dummy Data Credentials

### Super Admin Accounts
Password untuk semua Super Admin: `admin123`

| Email | NIM | Nama |
|-------|-----|------|
| admin@orbita.test | 0000000000 | Super Admin |
| admin1@orbita.test | 1000000001 | Admin Satu |
| admin2@orbita.test | 1000000002 | Admin Dua |
| admin3@orbita.test | 1000000003 | Admin Tiga |
| admin4@orbita.test | 1000000004 | Admin Empat |

### Mahasiswa Accounts
Password untuk semua Mahasiswa: `mahasiswa123`

#### HIMTI (Himpunan Mahasiswa Teknik Informatika)
- **Leader**: Ahmad Rizky (2021001001)
- **Staff**: Budi Santoso (2021001002)
- **Member**: Citra Dewi (2021001003), Dian Pratama (2021001004)

#### PSM (UKM Paduan Suara Mahasiswa)
- **Leader**: Eka Wijaya (2021001005)
- **Staff**: Fajar Nugraha (2021001006)
- **Member**: Gita Permata (2021001007), Hendra Saputra (2021001008)

#### BEM FT (Badan Eksekutif Mahasiswa Fakultas Teknik)
- **Leader**: Indah Sari (2021001009)
- **Staff**: Joko Widodo (2021001010)
- **Member**: Kartika Putri (2021001011), Lukman Hakim (2021001012)

#### ROBOTIKA (UKM Robotika)
- **Leader**: Maya Sari (2021001013)
- **Staff**: Nurul Hidayah (2021001014)
- **Member**: Oscar Pratama (2021001015), Putri Ayu (2021001016)

#### HIMMA (Himpunan Mahasiswa Manajemen)
- **Leader**: Qori Aulia (2021001017)
- **Staff**: Rian Hidayat (2021001018)
- **Member**: Siti Aminah (2021001019), Taufik Hidayat (2021001020)

## 📁 Project Structure

```
orbita/
├── api/                          # API endpoints (jika ada)
├── assets/
│   ├── css/
│   │   └── custom.css          # Custom styles
│   └── js/
│       └── main.js             # Main JavaScript
├── components/
│   ├── footer.php              # Footer component
│   ├── head.php                # Head component (CSS, JS includes)
│   ├── modal.php               # Modal component
│   ├── navbar.php              # Navigation bar
│   ├── sidebar.php             # Sidebar navigation
│   └── tables/
│       ├── kegiatan.php         # Kegiatan table rows
│       ├── log.php             # Activity log table rows
│       ├── members.php         # Members table rows
│       ├── organisasi.php      # Organisasi table rows
│       └── pengguna.php         # Users table rows
├── database/
│   └── orbita.sql           # Database schema & dummy data
├── includes/
│   ├── auth.php                # Authentication functions
│   ├── config.php              # Configuration
│   ├── database.php            # Database connection
│   └── functions.php           # Helper functions
├── pages/
│   ├── dashboard.php           # Dashboard page
│   ├── login.php               # Login page
│   ├── logout.php              # Logout page
│   ├── register.php            # Registration page
│   ├── forgot-password.php     # Forgot password page
│   ├── notifikasi.php          # Notifications page
│   ├── profil.php              # User profile page
│   ├── organisasi/
│   │   ├── index.php           # Organisasi list
│   │   ├── detail.php          # Organisasi detail
│   │   ├── jadwal.php          # Jadwal kegiatan
│   │   ├── kartu_anggota.php   # Member card
│   │   ├── kegiatan/
│   │   │   ├── detail.php      # Kegiatan detail
│   │   │   └── index.php       # Kegiatan list
│   │   ├── members.php         # Member management
│   │   └── requests.php        # Registration requests
│   ├── pengguna/
│   │   └── index.php           # User management
│   └── laporan/
│       └── index.php           # Reports page
├── uploads/
│   └── cv/                     # CV uploads
├── .htaccess                   # Apache configuration
├── 403.php                     # 403 Forbidden page
├── 404.php                     # 404 Not Found page
└── index.php                   # Main entry point
```

## 📝 Development Notes

### Coding Standards

- **PHP**: Menggunakan PSR-12 coding standard
- **HTML**: Menggunakan semantic HTML5
- **CSS**: Menggunakan Tailwind CSS dengan custom styles di `assets/css/custom.css`
- **JavaScript**: Vanilla JS dengan ES6+ features

### Security Features

- **CSRF Protection**: Token CSRF untuk setiap form submission
- **SQL Injection Prevention**: Menggunakan PDO prepared statements
- **XSS Prevention**: Output escaping dengan fungsi `e()`
- **Password Hashing**: Menggunakan bcrypt (password_hash)
- **Session Management**: Secure session handling

### Database Schema

Sistem menggunakan 12 tabel utama:

1. `roles` - Role pengguna (Super Admin, Admin Organisasi, Mahasiswa)
2. `users` - Data pengguna
3. `organisasi` - Data organisasi
4. `user_organisasi` - Relasi user-organisasi dengan role
5. `pendaftaran_organisasi` - Pendaftaran anggota baru
6. `wawancara` - Jadwal dan hasil wawancara
7. `kegiatan` - Kegiatan organisasi
8. `peserta_kegiatan` - Peserta kegiatan
9. `presensi` - Presensi kegiatan
10. `pengumuman` - Pengumuman global dan organisasi
11. `notifikasi` - Notifikasi pengguna
12. `activity_log` - Log aktivitas pengguna

### Contribution Guidelines

1. Fork repository
2. Buat branch untuk fitur baru (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buka Pull Request

### License

Proyek ini dibuat untuk keperluan pendidikan dan pengembangan skill.

## 📞 Support

Untuk pertanyaan atau masalah, silakan hubungi tim pengembang atau buka issue di repository.

---

**Version**: 1.0.0  
**Last Updated**: 2024
