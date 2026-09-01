# 🎓 EduLearn — Modern Higher Education Learning Management System (LMS)

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![Architecture](https://img.shields.io/badge/Architecture-Custom%20MVC-ff69b4?style=for-the-badge&logo=php)](index.php)
[![Database](https://img.shields.io/badge/PostgreSQL-14%2B-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Cloud DB & Storage](https://img.shields.io/badge/Supabase-DB%20%26%20Storage-3ECF8E?style=for-the-badge&logo=supabase&logoColor=white)](https://supabase.com/)
[![Security](https://img.shields.io/badge/CSRF%20%26%20PDO-Protected-brightgreen?style=for-the-badge&logo=shield)](app/Core/Middleware.php)

**EduLearn** adalah platform Learning Management System (LMS) berbasis web berstandar *enterprise* yang dirancang khusus untuk perguruan tinggi. Aplikasi ini dibangun dengan **Custom MVC Framework (PHP 8.x)** yang modular, mendukung **Supabase Cloud PostgreSQL**, **Supabase Cloud Storage (Zero-RAM Streaming)**, **Interactive Quiz Engine**, **Grading Matrix & KHS Engine**, serta **Kartu Rencana Studi (KRS)**.

---

## 🌟 Fitur Utama (Key Features)

### 👨‍🎓 Portal Mahasiswa (Student Portal)
- **Virtual Classroom (Ruang Kelas Digital)**:
  - Antarmuka tabbed interaktif per mata kuliah: **Materi Ajar**, **Kuis Evaluasi**, dan **Pengumuman Kelas**.
  - Mengunduh materi pembelajaran (PDF/DOCX/PPT/ZIP) dan mengunggah tugas dengan pelacakan status *real-time*.
- **Kartu Rencana Studi (KRS & Ambil Kelas)**:
  - Pendaftaran mata kuliah mandiri dari katalog kampus dengan akumulasi total SKS real-time serta opsi *Enroll* dan *Drop* kelas.
- **Interactive Quiz Engine**:
  - Timer countdown real-time, auto-submit saat waktu habis, evaluasi nilai instant (pass/fail status), serta review kunci jawaban dan skor *leaderboard*.
- **Transkrip Akademik & KHS**:
  - Pemantauan nilai Tugas (20%), UTS (30%), UAS (40%), dan Partisipasi (10%) terhitung otomatis menjadi IPK Kumulatif dan transkrip cetak KHS.
- **Manajemen Profil**: Pembaruan data pribadi, foto profil cloud, riwayat pendidikan, dan kontak.

### 👨‍🏫 Portal Dosen (Lecturer Portal)
- **Kelola Kelas & Ruang Kuliah**: Manajemen silabus, materi perkuliahan, dan tugas perkuliahan.
- **Quiz & Question Builder (Bank Soal)**:
  - Pembuatan kuis interaktif dengan *passing score*, batas durasi, dukungan formula matematika, 4 pilihan jawaban, dan tabel *Leaderboard* mahasiswa.
- **Grading Matrix & Review Tugas**:
  - Modal review pekerjaan mahasiswa (teks submission & berkas unduhan) dengan form input nilai otomatis terhitung (20% Tugas, 30% UTS, 40% UAS, 10% Partisipasi).
  - Rekapitulasi laporan nilai kelas lengkap dengan statistik kelulusan & **Export ke Excel / CSV**.
- **Pusat Pengumuman (Announcements)**: Penyiaran pengumuman per mata kuliah atau pengumuman kampus umum.

### ☁️ Hybrid Storage Engine (Zero-RAM Streaming)
- **Dual Driver (`local` & `supabase`)**:
  - **Local Disk Storage**: Penyimpanan rapi terorganisir per taksonomi subfolder (`uploads/dosen/`, `uploads/mahasiswa/`, `uploads/profiles/`).
  - **Supabase Cloud Storage REST API**: *Zero-RAM File-Pointer Streaming* (`fopen` & `CURLOPT_INFILE`) yang mengirimkan berkas dari disk OS ke Cloud Supabase tanpa beban RAM PHP.
  - **Universal Octet-Stream MIME Fallback**: Menjamin 100% berkas `.docx`, `.pdf`, `.zip`, `.png` diterima oleh Supabase tanpa terhalang pembatasan MIME type.

---

## 🏗️ Arsitektur & Teknologi (Tech Stack)

| Komponen | Teknologi | Deskripsi |
| :--- | :--- | :--- |
| **Arsitektur** | Custom MVC Framework | Front Controller, PSR-4 Autoloading, Routing & Dispatcher Engine |
| **Backend** | Native PHP 8.x | Modular Controllers, Domain Models, Middleware RBAC, Database Singleton |
| **Cloud Database** | PostgreSQL / Supabase | Cloud Relational Database dengan Foreign Key Constraints & Prepared Statements |
| **Cloud Storage** | Supabase Storage REST API | Zero-RAM cURL Stream Upload, CDN Public File Delivery |
| **Frontend** | HTML5, CSS3, JavaScript (ES6) | Glassmorphism Responsive UI layout dengan FontAwesome & Bootstrap 5 |
| **Security** | CSRF Guards & Hashing | `password_hash()` (Bcrypt), CSRF One-time Tokens, Input Sanitization |

---

## 📁 Struktur Direktori (Directory Structure)

```text
elearning/
├── index.php                    # Front Controller — Single entry point
├── app/                         # MVC Application Root
├── Core/                    # Framework Core (App, Controller, Database, Middleware)
│   ├── Controllers/             # Domain Controllers (Auth, Student, Lecturer, Quiz, Exam, Grade, Announcement)
│   ├── Models/                  # Data Layer Models (User, Course, Assignment, Quiz, Exam, Grade, Schedule, etc)
│   ├── Helpers/                 # StorageHelper, Grade Calculator, Date & UI Helpers
│   └── Views/                   # HTML Templates (auth, student, lecturer, errors)
├── database/                   # DDL SQL Script & Seed Data
├── logs/                       # Log aktivitas & error aplikasi (app_error.log)
├── uploads/                    # Struktur Penyimpanan Lokal Terpisah
│   ├── dosen/                   # materi/ & tugas/
│   ├── mahasiswa/               # submissions/ & ujian/
│   └── profiles/                # foto profil pengguna
├── config.php                  # Konfigurasi terpusat & env parser
└── .env.example                # Template konfigurasi environment (PostgreSQL & Storage)
```

---

## ⚡ Panduan Instalasi & Jalankan (Installation & Setup)

### 1. Prasyarat Sistem
- **PHP 8.1+** dengan ekstensi `curl` dan `pdo_pgsql` aktif.
- **PostgreSQL 14+** (Local Server / Supabase Cloud PostgreSQL Instance).
- Web Server (**Apache** via XAMPP / Nginx).

### 2. Clone Repository
```bash
git clone https://github.com/Nabillah-Alimuddin/EduLearn.git
cd elearning
```

### 3. Konfigurasi Environment Variable
Salin file `.env.example` menjadi `.env` dan sesuaikan kredensial PostgreSQL & Supabase Storage Anda:
```bash
cp .env.example .env
```

Isi file `.env`:
```ini
; --- Database (Supabase PostgreSQL) ---
DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com
DB_PORT=5432
DB_NAME=postgres
DB_USER=postgres.xxxxxxxxxx
DB_PASSWORD=your_password
DB_SSLMODE=require

; --- File Storage Settings (local / supabase) ---
STORAGE_DRIVER=supabase
SUPABASE_STORAGE_URL=https://xxxxxxxxxx.supabase.co/storage/v1
SUPABASE_ANON_KEY=eyJhbGciOiJKV1QiLCJhbGci... (JWT service_role / anon key)
SUPABASE_BUCKET=elearning
```

### 4. Jalankan Aplikasi
Akses via XAMPP browser:
```text
http://localhost/elearning/index.php
```
Atau jalankan PHP Built-in Server:
```bash
php -S localhost:8000
```
Buka `http://localhost:8000/index.php` di browser Anda.

---

## 🔒 Keamanan & Performa (Security Highlights)

- **Prepared Statements**: Seluruh kueri basis data menggunakan PDO `prepare()` dan parameter binding untuk mencegah ancaman **SQL Injection**.
- **Zero-RAM File Streaming**: Menggunakan stream pointer `fopen()` pada cURL untuk mengunggah berkas besar ke Cloud Storage tanpa konsumsi RAM PHP.
- **CSRF Token Validation**: Setiap form permintaan data yang sensitif dilindungi oleh `Middleware::verifyCsrfToken()`.
- **Session Protection**: Pemanfaatan `session_regenerate_id()` dan pembersihan sesi berkala untuk mencegah **Session Hijacking**.
- **Password Hashing**: Kata sandi disimpan secara aman menggunakan Bcrypt via `password_hash()`.
