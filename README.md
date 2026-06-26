# E-ORMAWA

Sistem manajemen organisasi mahasiswa berbasis web untuk mempermudah pengelolaan kegiatan organisasi, keanggotaan, dan administrasi.

## 📋 Fitur Utama

### Untuk Super Admin
- **Dashboard** - Statistik dan data visualisasi organisasi, pengguna, dan kegiatan
- **Kelola Organisasi** - CRUD organisasi dengan detail dalam modal
- **Kelola Pengguna** - Manajemen user dengan soft delete (arsip)
- **Pengumuman** - Kelola pengumuman global dan organisasi
- **Laporan** - Laporan kegiatan dan statistik
- **Log Aktivitas** - Tracking aktivitas pengguna di sistem

### Untuk Mahasiswa
- **Dashboard** - Informasi organisasi yang diikuti dan notifikasi
- **Jelajah Organisasi** - Melihat dan bergabung dengan organisasi
- **Manajemen Organisasi** (sub-role ditentukan per organisasi di tabel anggota):
  - **Leader**: Kelola organisasi, manajemen member, manajemen kegiatan
  - **Staff**: Manajemen member, manajemen kegiatan
  - **Member**: Lihat member, lihat kegiatan
- **Jadwal Kegiatan** - Kalender kegiatan organisasi
- **Kartu Anggota** - Kartu keanggotaan digital dengan export (Foto/PDF)
- **Notifikasi** - Notifikasi aktivitas dan pengumuman

## 🛠️ Teknologi

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Styling**: Tailwind CSS
- **Charts**: Chart.js
- **Icons**: SVG (Lucide style)
- **Export**: html2canvas, jsPDF

## 📦 Persyaratan

- XAMPP/WAMP/MAMP (atau server PHP lainnya)
- PHP 7.4 atau lebih tinggi
- MySQL/MariaDB
- Browser modern (Chrome, Firefox, Edge, Safari)

## 🚀 Instalasi

### 1. Clone/Download Project

```bash
cd c:\xampp\htdocs\web-programming\
# Extract atau clone project e-ormawa
```

### 2. Setup Database

Buka phpMyAdmin (http://localhost/phpmyadmin) dan:

1. Buat database baru bernama `e_ormawa`
2. Import file `database/e_ormawa` ke database `e_ormawa`
   - File ini sudah termasuk schema dan dummy data
   - Data yang akan dibuat:
     - 2 Super Admin
     - 11 Mahasiswa (dengan sub-role leader/staff/member di tabel anggota)
     - 5 Organisasi
     - 11 Kegiatan
     - Pengumuman, Notifikasi, Activity Log

### 3. Konfigurasi

Edit file `includes/config.php` sesuai kebutuhan:

```php
define('BASE_URL', 'http://localhost/e-ormawa');
define('APP_NAME', 'E-ORMAWA');
```

### 4. Akses Aplikasi

Buka browser dan akses:
```
http://localhost/e-ormawa
```

## 👥 Akun Dummy

Password untuk semua akun: **password123**

### Super Admin
- Email: `admin@eormawa.test` (NIM: 0000000000)
- Email: `superadmin@eormawa.test` (NIM: 0000000001)

### Mahasiswa
Sub-role (leader/staff/member) ditentukan di tabel `anggota` berdasarkan organisasi yang diikuti.

**Mahasiswa dengan role Leader:**
- Email: `budi@eormawa.test` (NIM: 2021001001) - Ketua HIMATIF
- Email: `siti@eormawa.test` (NIM: 2021001002) - Ketua UKM Tari
- Email: `ahmad@eormawa.test` (NIM: 2021001003) - Ketua HIMMA
- Email: `dewi@eormawa.test` (NIM: 2022001001) - Ketua CODEX
- Email: `dimas@eormawa.test` (NIM: 2023001001) - Ketua CODEX
- Email: `bayu@eormawa.test` (NIM: 2023001003) - Ketua UKM Basket

**Mahasiswa dengan role Staff/Member:**
- Email: `rizky@eormawa.test` (NIM: 2022001002) - Staff HIMATIF & CODEX
- Email: `maya@eormawa.test` (NIM: 2022001003) - Staff HIMATIF
- Email: `fajar@eormawa.test` (NIM: 2022001004) - Staff HIMATIF & UKM Basket
- Email: `lina@eormawa.test` (NIM: 2022001005) - Staff UKM Tari
- Email: `citra@eormawa.test` (NIM: 2023001002) - Staff HIMMA

## 📖 Panduan Penggunaan

### Super Admin

#### Dashboard
- Lihat statistik: total organisasi, pengguna, mahasiswa, kegiatan aktif
- Grafik: status kegiatan, status pendaftaran, top 5 organisasi teraktif
- Daftar organisasi terbaru, pendaftaran pending, kegiatan terbaru

#### Kelola Organisasi
- **Tambah**: Klik tombol + untuk membuat organisasi baru
- **Edit**: Klik tombol edit pada organisasi
- **Hapus**: Klik tombol hapus (soft delete, bisa dipulihkan)
- **Detail**: Klik tombol lihat untuk melihat detail dalam modal
- **Arsip**: Lihat organisasi yang dihapus (klik "Lihat Arsip")

#### Kelola Pengguna
- **Tambah**: Klik tombol + untuk membuat user baru
- **Edit**: Klik tombol edit pada user
- **Hapus**: Klik tombol hapus (soft delete)
- **Cari**: Gunakan search bar untuk mencari user
- **Filter**: User aktif dan arsip terpisah

#### Pengumuman
- **Buat**: Klik tombol tambah untuk membuat pengumuman
- **Edit/Hapus**: Kelola pengumuman yang ada
- **Tipe**: Global (semua user) atau Organisasi (member organisasi tertentu)

#### Laporan
- Lihat laporan kegiatan dan statistik organisasi
- Filter berdasarkan organisasi dan periode

#### Log Aktivitas
- Tracking semua aktivitas user di sistem
- Filter berdasarkan user dan tanggal

### Mahasiswa

#### Dashboard
- Lihat organisasi yang diikuti
- Statistik: jumlah organisasi, kegiatan, notifikasi
- Kegiatan terbaru dari organisasi yang diikuti

#### Jelajah Organisasi
- **Lihat**: Daftar semua organisasi aktif
- **Detail**: Klik organisasi untuk melihat detail
- **Gabung**: Klik "Gabung Organisasi" untuk mendaftar
- **Status**: Menunggu, Wawancara, Diterima, Ditolak

#### Manajemen Organisasi (sub-role per organisasi)

Sub-role (Leader/Staff/Member) ditentukan di tabel `anggota` berdasarkan posisi dalam organisasi.

**Leader:**
- **Kelola Organisasi**: Edit info organisasi, visi, misi
- **Manajemen Member**: Terima/tolak permintaan bergabung, atur role member
- **Manajemen Kegiatan**: CRUD kegiatan, kelola peserta

**Staff:**
- **Manajemen Member**: Lihat member, atur role (jika diizinkan)
- **Manajemen Kegiatan**: CRUD kegiatan, kelola peserta

**Member:**
- **Lihat Member**: Daftar anggota organisasi
- **Lihat Kegiatan**: Daftar kegiatan organisasi

#### Jadwal Kegiatan
- Kalender kegiatan organisasi
- Filter berdasarkan organisasi
- Detail kegiatan dengan tombol daftar

#### Kartu Anggota
- **Lihat**: Kartu keanggotaan digital
- **Export Foto**: Download sebagai PNG
- **Export PDF**: Download sebagai PDF
- Kartu berisi: foto, nama, NIM, role, status, jurusan, tanggal bergabung

#### Notifikasi
- Notifikasi aktivitas (diterima organisasi, undangan kegiatan, dll)
- Notifikasi pengumuman
- Mark as read otomatis saat dibuka

## 📁 Struktur File

```
e-ormawa/
├── api/                      # API endpoints
├── assets/
│   ├── css/
│   │   └── custom.css       # Custom styles
│   ├── images/
│   │   └── orbita-logo.png  # Logo aplikasi
│   └── js/
│       └── main.js          # JavaScript utama
├── components/
│   ├── footer.php           # Footer
│   ├── head.php             # HTML head dengan CDN
│   ├── modal.php            # Modal components
│   ├── navbar.php           # Navigation bar
│   └── sidebar.php          # Sidebar navigation
├── components/tables/
│   ├── kegiatan.php         # Table kegiatan
│   ├── log.php              # Table activity log
│   ├── members.php          # Table member organisasi
│   ├── organisasi.php       # Table organisasi
│   └── pengguna.php         # Table users
├── database/
│   └── e_ormawa.sql         # Schema database + dummy data
├── includes/
│   ├── auth.php             # Authentication functions
│   ├── config.php           # Configuration
│   ├── database.php         # Database connection
│   └── functions.php        # Helper functions
├── pages/
│   ├── dashboard.php        # Dashboard
│   ├── forgot-password.php  # Lupa password
│   ├── index.php            # Landing page
│   ├── login.php            # Login page
│   ├── logout.php           # Logout
│   ├── notifikasi.php       # Notifikasi
│   ├── profil.php           # Profil user
│   ├── register.php         # Registrasi
│   ├── kegiatan/
│   │   └── detail.php       # Detail kegiatan
│   ├── laporan/
│   │   └── index.php        # Laporan
│   ├── log/
│   │   └── index.php        # Log aktivitas
│   ├── organisasi/
│   │   ├── detail.php       # Detail organisasi
│   │   ├── index.php        # Kelola organisasi
│   │   ├── jadwal.php       # Jadwal kegiatan
│   │   ├── kegiatan.php     # Manajemen kegiatan
│   │   ├── kartu.php        # Kartu anggota
│   │   ├── member.php       # Manajemen member
│   │   └── permintaan.php   # Permintaan bergabung
│   └── pengguna/
│       └── index.php        # Kelola pengguna
├── uploads/
│   └── cv/                  # Upload CV pendaftaran
├── .htaccess                # URL rewriting
├── 403.php                  # Halaman 403
├── 404.php                  # Halaman 404
├── DESIGN.md                # Dokumentasi desain
└── README.md                # Dokumentasi ini
```

## 🔧 Troubleshooting

### Database Connection Error
Pastikan konfigurasi di `includes/database.php` sesuai:
```php
$host = 'localhost';
$dbname = 'e_ormawa';
$username = 'root';
$password = '';
```

### Session Not Working
Pastikan folder `tmp` ada dan writable:
```bash
# Di XAMPP, biasanya otomatis
# Jika error, cek php.ini untuk session.save_path
```

### Upload CV Not Working
Pastikan folder `uploads/cv` ada dan writable:
```bash
# Windows: Pastikan permission folder
# Linux: chmod 755 uploads/cv
```

### Charts Not Displaying
Pastikan CDN Chart.js terload di `components/head.php`:
```html
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
```

### Export Kartu Anggota Not Working
Pastikan CDN html2canvas dan jsPDF terload:
```html
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
```

## 🎨 Customization

### Warna Tema
Edit di `components/head.php` di bagian `tailwind.config`:
```javascript
colors: {
    primary: {
        DEFAULT: '#244539',  // Warna utama
        light: '#3B5D50',
        dim: '#aacfbe',
    },
    // ... lainnya
}
```

### Logo
Ganti file di `assets/images/orbita-logo.png`

### Nama Aplikasi
Edit di `includes/config.php`:
```php
define('APP_NAME', 'Nama Aplikasi Anda');
```

## 📝 Catatan

- Password default untuk semua dummy user: `password123`
- Sistem menggunakan soft delete untuk pengguna dan organisasi
- Kartu anggota menggunakan html2canvas untuk export gambar
- Notifikasi real-time belum diimplementasikan (perlu WebSocket)
- Email notification belum diimplementasikan (perlu mail server)

## 🤝 Kontribusi

Untuk kontribusi atau pengembangan lebih lanjut:
1. Fork project
2. Buat branch baru
3. Commit perubahan
4. Push ke branch
5. Buat Pull Request

## 📄 Lisensi

Project ini dibuat untuk keperluan akademik dan organisasi mahasiswa.

## 📞 Kontak

Untuk pertanyaan atau support, hubungi tim pengembang.

---

**Versi**: 1.0.0  
**Terakhir Update**: Juni 2024
