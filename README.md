# 🎓 EduLearn — Modern Higher Education Learning Management System (LMS)

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![Database](https://img.shields.io/badge/PostgreSQL-14%2B-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Cloud DB](https://img.shields.io/badge/Supabase-Supported-3ECF8E?style=for-the-badge&logo=supabase&logoColor=white)](https://supabase.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)
[![Security](https://img.shields.io/badge/CSRF%20%26%20PDO-Protected-brightgreen?style=for-the-badge&logo=shield)](middleware.php)

**EduLearn** adalah platform Learning Management System (LMS) berbasis web yang dirancang khusus untuk memenuhi kebutuhan akademis perguruan tinggi. Aplikasi ini menyediakan lingkungan pembelajaran digital yang terintegrasi, interaktif, dan aman bagi **Mahasiswa**, **Dosen**, dan **Administrator**.

---

## 🌟 Fitur Utama (Key Features)

### 👨‍🎓 Portal Mahasiswa (Student Portal)
- **Interactive Dashboard**: Ringkasan mata kuliah, jadwal perkuliahan, pengumuman terbaru, dan tenggat waktu tugas (deadline tracking).
- **Materi & Tugas**: Mengunduh materi pembelajaran (PDF/PPT/Video) serta mengunggah pengumpulan tugas dengan status real-time.
- **Sistem Kuis & Ujian Online**:
  - Pengerjaan kuis berbasis timer interaktif.
  - Beragam tipe soal: Pilihan Ganda, Essay, dan Coding.
  - Evaluasi & penilaian otomatis untuk soal pilihan ganda.
- **Transkrip & Rekap Nilai**: Pemantauan nilai tugas, kuis, UTS, dan UAS secara transparan.
- **Manajemen Profil**: Pembaruan data pribadi, foto profil, riwayat pendidikan, dan kontak.

### 👨‍🏫 Portal Dosen (Lecturer Portal)
- **Kelola Kelas & Mata Kuliah**: Penjadwalan ruang kuliah, pembuatan silabus, dan distribusi materi.
- **Kuis & Bank Soal (Quiz Builder)**: Modul CRUD kuis interaktif dengan pengaturan passing score, batas waktu, dan formula soal.
- **Penilaian & Feedback (Grading System)**:
  - Pemeriksaan tugas mahasiswa dengan opsi pemberian komentar/feedback.
  - Fitur **Export Nilai ke Excel** (.xlsx) untuk kemudahan rekapitulasi akademis.
- **Pusat Pengumuman (Announcements)**: Penyiaran pengumuman penting secara spesifik per mata kuliah atau umum.
- **Dashboard Analitik**: Pemantauan progres pengumpulan tugas dan statistik nilai mahasiswa.

### 🛡️ Keamanan & Infrastruktur (Security & Core Infrastructure)
- **Role-Based Access Control (RBAC)**: Middleware verifikasi sesi dan otorisasi ketat berbasis peran (`student`, `lecturer`, `admin`).
- **CSRF Token Protection**: Proteksi serangan Cross-Site Request Forgery pada seluruh submission form dan API.
- **Database Abstraction (PDO)**: Keamanan terhadap SQL Injection menggunakan prepared statements dan koneksi terenkripsi (SSL mode).
- **Centralized Environment (`.env`)**: Pengelolaan kredensial aman berbasis environment variable parser.
- **Structured Error Handling**: Dynamic logging error ke log file internal tanpa mengekspos detail kredensial sistem ke end-user.

---

## 🏗️ Arsitektur & Teknologi (Tech Stack)

| Komponen | Teknologi | Deskripsi |
| :--- | :--- | :--- |
| **Backend** | Native PHP 8.x | Arsitektur modular dengan PDO Database Wrapper & Custom Middleware |
| **Database** | PostgreSQL / Supabase | Relational Database Management System dengan Foreign Key Constraints |
| **Frontend** | HTML5, CSS3, JavaScript (ES6) | Responsive UI layout dengan Chart.js & Tailwind CSS utilities |
| **Security** | CSRF Guards & Hashing | `password_hash()` (Bcrypt), CSRF One-time Tokens, Input Sanitization |
| **Export/Import** | PHP Spreadsheet Integration | Ekspor laporan nilai mahasiswa dalam format Excel |

---

## 📁 Struktur Direktori (Directory Structure)

```text
elearning/
├── api/                        # RESTful JSON API endpoints
│   ├── get_announcements.php   # Endpoint data pengumuman
│   ├── get_assignments.php     # Endpoint data tugas mahasiswa
│   ├── get_schedule.php        # Endpoint jadwal perkuliahan
│   ├── submit_assignment.php   # Handler pengumpulan tugas
│   └── save_grades.php         # Handler simpan nilai dosen
├── database/                   # Skrip DDL & Seed Database
│   ├── postgresql_schema.sql   # DDL Schema utama PostgreSQL
│   └── seed_data.sql           # Data sampel awal (dummy data)
├── logs/                       # Log aktivitas & error aplikasi
├── uploads/                    # File penyimpanan tugas & materi
├── config.php                  # Konfigurasi terpusat & env parser
├── db_connection.php           # Inisialisasi koneksi PDO PostgreSQL
├── middleware.php              # Auth Guard & CSRF protection
├── error_handler.php           # Error & Exception Handler
├── helpers.php                 # Library helper utility functions
├── dash-mahasiswa.php          # Dashboard utama mahasiswa
├── dash-dosen.php              # Dashboard utama dosen
├── quiz-crud-dosen.php         # Modul pembuatan kuis dosen
├── export_excel.php            # Skrip ekspor nilai ke Excel
├── login.html / login.php      # Autentikasi user & penanganan sesi
└── .env.example                # Template konfigurasi environment
```

---

## ⚡ Panduan Instalasi (Installation & Setup)

### 1. Prasyarat Sistem
- **PHP 8.1** atau versi lebih baru.
- **PostgreSQL 14+** (Local Server / Supabase Cloud Instance).
- Web Server (**Apache** via XAMPP / Nginx).

### 2. Clone Repository
```bash
git clone https://github.com/username/elearning.git
cd elearning
```

### 3. Konfigurasi Environment Variable
Salin file `.env.example` menjadi `.env` dan menyesuaikan kredensial database Anda:
```bash
cp .env.example .env
```

Buka `.env` dan atur parameter berikut:
```ini
APP_NAME=EduLearn
APP_ENV=development
APP_DEBUG=true
APP_TIMEZONE=Asia/Makassar

DB_HOST=127.0.0.1
DB_PORT=5432
DB_NAME=elearning_db
DB_USER=postgres
DB_PASSWORD=your_password
DB_SSLMODE=prefer
```

### 4. Import Database Schema
Jalankan file SQL schema ke PostgreSQL / Supabase instance Anda:
```bash
psql -h 127.0.0.1 -U postgres -d elearning_db -f database/postgresql_schema.sql
psql -h 127.0.0.1 -U postgres -d elearning_db -f database/seed_data.sql
```
*Atau jalankan skrip `run_migration.php` melalui terminal/browser untuk eksekusi migrasi otomatis.*

### 5. Jalankan Aplikasi
Jika menggunakan XAMPP, tempatkan folder ini di `htdocs` dan akses melalui browser:
```text
http://localhost/elearning/landingpage.html
```
Atau gunakan PHP Built-in Server:
```bash
php -S localhost:8000
```
Buka `http://localhost:8000/landingpage.html` di browser Anda.

---

## 🔒 Fitur Keamanan (Security Highlights)

- **Prepared Statements**: Seluruh kueri basis data menggunakan PDO `prepare()` dan parameter binding untuk mencegah ancaman **SQL Injection**.
- **CSRF Token Validation**: Setiap form permintaan data yang sensitif dilindungi oleh fungsi `verify_csrf_token()`.
- **Session Protection**: Pemanfaatan `session_regenerate_id()` dan pembersihan sesi berkala untuk mencegah **Session Hijacking**.
- **Password Hashing**: Kata sandi disimpan secara aman mengodekan algoritma Bcrypt via `password_hash()`.

---

