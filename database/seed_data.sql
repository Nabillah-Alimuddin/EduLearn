-- Data Seed E-Learning untuk PostgreSQL + Supabase

-- 1. Insert ke tabel users
INSERT INTO users (user_id, full_name, gelar, email, password_hash, role, nim, nik, gender, study_program, jabatan_akademik, bidang_keahlian, status_kepegawaian, religion, nationality, place_of_birth, date_of_birth, address, phone_number, ruang_kerja, jam_konsultasi, previous_school, nisn, school_city, profile_picture_url, created_at, updated_at) VALUES
(1, 'Aulia Resty Nur Aini', NULL, 'aulia.resty@students.amikom.ac.id', '$2y$10$x2fzM7QEYf3mbeLLny5G4.dBi8NTCxMpnBdTHszTh/rAmTqMrYUkm', 'student', '23.11.5571', '3304011234567890', 'Perempuan', 'Teknik Informatika', NULL, NULL, NULL, 'Islam', 'Indonesia', 'Yogyakarta', '2004-05-15', NULL, '081234567890', NULL, NULL, 'SMA Negeri 1 Yogyakarta', '9991112223', 'Yogyakarta', 'uploads/profiles/6878083aad13a_profil.jpg', '2025-06-20 10:30:10', '2025-07-18 03:08:01'),
(2, 'Dr. Budi Santosa, M.Sc.', NULL, 'budi.santosa@amikom.ac.id', '$2y$10$x2fzM7QEYf3mbeLLny5G4.dBi8NTCxMpnBdTHszTh/rAmTqMrYUkm', 'lecturer', NULL, NULL, 'Laki-laki', 'Teknik Informatika', NULL, NULL, NULL, 'Islam', 'Indonesia', 'Jakarta', '1975-01-20', NULL, '081122334455', NULL, NULL, NULL, NULL, NULL, 'default_profile.svg', '2025-06-20 10:30:10', '2025-07-18 03:08:01'),
(3, 'Ir. Rika Handayani, M.Kom.', NULL, 'rika.handayani@amikom.ac.id', '$2y$10$x2fzM7QEYf3mbeLLny5G4.dBi8NTCxMpnBdTHszTh/rAmTqMrYUkm', 'lecturer', NULL, NULL, 'Perempuan', 'Teknik Informatika', NULL, NULL, NULL, 'Islam', 'Indonesia', 'Bandung', '1980-03-10', NULL, '085678901234', NULL, NULL, NULL, NULL, NULL, 'default_profile.svg', '2025-06-20 10:30:10', '2025-07-18 03:08:01'),
(4, 'Dr. Eng. Andi Pratama, S.T., M.T.', NULL, 'andi.pratama@amikom.ac.id', '$2y$10$x2fzM7QEYf3mbeLLny5G4.dBi8NTCxMpnBdTHszTh/rAmTqMrYUkm', 'lecturer', NULL, NULL, 'Laki-laki', 'Teknik Informatika', NULL, NULL, NULL, 'Islam', 'Indonesia', 'Surabaya', '1978-07-25', NULL, '087812345678', NULL, NULL, NULL, NULL, NULL, 'default_profile.svg', '2025-06-20 10:30:10', '2025-07-18 03:08:01'),
(5, 'Yuli Astuti, S.Sn., M.Sn.', NULL, 'yuli.astuti@amikom.ac.id', '$2y$10$x2fzM7QEYf3mbeLLny5G4.dBi8NTCxMpnBdTHszTh/rAmTqMrYUkm', 'lecturer', NULL, NULL, 'Perempuan', 'Seni & Desain', NULL, NULL, NULL, 'Kristen', 'Indonesia', 'Semarang', '1985-09-01', NULL, '089988776655', NULL, NULL, NULL, NULL, NULL, 'default_profile.svg', '2025-06-20 10:30:10', '2025-07-18 03:08:01'),
(6, 'Dr. Ahmad Zulkarnain, M.Kom.', NULL, 'ahmad.zulkarnain@amikom.ac.id', '$2y$10$x2fzM7QEYf3mbeLLny5G4.dBi8NTCxMpnBdTHszTh/rAmTqMrYUkm', 'lecturer', NULL, NULL, 'Laki-laki', 'Teknik Informatika', NULL, NULL, NULL, 'Islam', 'Indonesia', 'Medan', '1970-11-11', NULL, '081298765432', NULL, NULL, NULL, NULL, NULL, 'default_profile.svg', '2025-06-20 10:30:10', '2025-07-18 03:08:01'),
(7, 'Prof. Dr. Hendra Wijaya, M.T.', NULL, 'hendra.wijaya@amikom.ac.id', '$2y$10$x2fzM7QEYf3mbeLLny5G4.dBi8NTCxMpnBdTHszTh/rAmTqMrYUkm', 'lecturer', NULL, NULL, 'Laki-laki', 'Teknik Informatika', NULL, NULL, NULL, 'Islam', 'Indonesia', 'Palembang', '1965-04-03', NULL, '081345678901', NULL, NULL, NULL, NULL, NULL, 'default_profile.svg', '2025-06-20 10:30:10', '2025-07-18 03:08:01'),
(8, 'Ir. Teguh Raharjo, M.Eng.', NULL, 'teguh.raharjo@amikom.ac.id', '$2y$10$x2fzM7QEYf3mbeLLny5G4.dBi8NTCxMpnBdTHszTh/rAmTqMrYUkm', 'lecturer', NULL, NULL, 'Laki-laki', 'Teknik Elektro', NULL, NULL, NULL, 'Islam', 'Indonesia', 'Makassar', '1972-06-18', NULL, '082109876543', NULL, NULL, NULL, NULL, NULL, 'default_profile.svg', '2025-06-20 10:30:10', '2025-07-18 03:08:01'),
(9, 'Dewi Lestari, S.Kom., M.Kom.', NULL, 'dewi.lestari@amikom.ac.id', '$2y$10$x2fzM7QEYf3mbeLLny5G4.dBi8NTCxMpnBdTHszTh/rAmTqMrYUkm', 'lecturer', NULL, NULL, 'Perempuan', 'Teknik Informatika', NULL, NULL, NULL, 'Islam', 'Indonesia', 'Surakarta', '1983-02-28', NULL, '081555667788', NULL, NULL, NULL, NULL, NULL, 'default_profile.svg', '2025-06-20 10:30:10', '2025-07-18 03:08:01'),
(10, 'Ledyvia Audiz Coranov', NULL, 'ledyvia.audiz@students.amikom.ac.id', '$2y$10$x2fzM7QEYf3mbeLLny5G4.dBi8NTCxMpnBdTHszTh/rAmTqMrYUkm', 'student', '23.11.5572', '3304011234567891', 'Perempuan', 'Teknik Informatika', NULL, NULL, NULL, 'Islam', 'Indonesia', 'Sleman', '2004-06-20', NULL, '081234567891', NULL, NULL, 'SMA Negeri 2 Yogyakarta', '9993334445', 'Yogyakarta', 'default_profile.svg', '2025-07-05 04:06:00', '2025-07-18 03:08:01'),
(11, 'Nabillah Alimuddin', NULL, 'nabillah.alimuddin@students.amikom.ac.id', '$2y$10$x2fzM7QEYf3mbeLLny5G4.dBi8NTCxMpnBdTHszTh/rAmTqMrYUkm', 'student', '23.11.5573', '3304011234567892', 'Perempuan', 'Teknik Informatika', NULL, NULL, NULL, 'Islam', 'Indonesia', 'Bantul', '2004-07-01', NULL, '081234567892', NULL, NULL, 'SMA Negeri 3 Yogyakarta', '9995556667', 'Yogyakarta', 'default_profile.svg', '2025-07-05 04:06:00', '2025-07-18 03:08:01'),
(12, 'Muh. Dhivan Musanifi', NULL, 'muh.dhivan@students.amikom.ac.id', '$2y$10$x2fzM7QEYf3mbeLLny5G4.dBi8NTCxMpnBdTHszTh/rAmTqMrYUkm', 'student', '23.11.5574', '3304011234567893', 'Laki-laki', 'Teknik Informatika', NULL, NULL, NULL, 'Islam', 'Indonesia', 'Kulon Progo', '2004-08-10', NULL, '081234567893', NULL, NULL, 'SMA Negeri 1 Wates', '9997778889', 'Kulon Progo', 'default_profile.svg', '2025-07-05 04:06:00', '2025-07-18 03:08:01'),
(13, 'Putra Jaya Santoso', NULL, 'putra.santoso@students.amikom.ac.id', '$2y$10$x2fzM7QEYf3mbeLLny5G4.dBi8NTCxMpnBdTHszTh/rAmTqMrYUkm', 'student', '23.11.5575', '3304011234567894', 'Laki-laki', 'Teknik Informatika', NULL, NULL, NULL, 'Islam', 'Indonesia', 'Gunung Kidul', '2004-09-05', NULL, '081234567894', NULL, NULL, 'SMA Negeri 1 Wonosari', '9990001112', 'Gunung Kidul', 'default_profile.svg', '2025-07-05 04:06:00', '2025-07-18 03:08:01'),
(14, 'Cindy Permata Sari', NULL, 'cindy.sari@students.amikom.ac.id', '$2y$10$x2fzM7QEYf3mbeLLny5G4.dBi8NTCxMpnBdTHszTh/rAmTqMrYUkm', 'student', '23.11.5576', '3304011234567895', 'Perempuan', 'Teknik Informatika', NULL, NULL, NULL, 'Islam', 'Indonesia', 'Sleman', '2004-10-12', NULL, '081234567895', NULL, NULL, 'SMA Negeri 4 Yogyakarta', '9992223334', 'Yogyakarta', 'default_profile.svg', '2025-07-05 04:06:00', '2025-07-18 03:08:01');

-- 2. Insert ke tabel courses
INSERT INTO courses (course_id, course_name, course_code, description, credits, lecturer_id, created_at, updated_at) VALUES
(1, 'Aljabar Linear & Matriks', 'IF1401', 'Mempelajari konsep dasar aljabar linear dan matriks serta aplikasinya.', 3, 2, '2025-06-20 10:30:10', '2025-06-29 11:31:11'),
(2, 'Pemrograman Web', 'IF1402', 'Pengembangan aplikasi web dinamis menggunakan teknologi front-end dan back-end.', 3, 2, '2025-06-20 10:30:10', '2025-06-20 10:30:10'),
(3, 'Analisis Desain Sistem Informasi', 'IF1403', 'Menganalisis dan merancang sistem informasi yang efektif dan efisien.', 2, 3, '2025-06-20 10:30:10', '2025-06-20 10:30:10'),
(4, 'Multimedia', 'IF1404', 'Konsep dasar dan aplikasi teknologi multimedia.', 3, 4, '2025-06-20 10:30:10', '2025-06-20 10:30:10'),
(5, 'Big Data', 'IF1405', 'Pengenalan dan analisis data berukuran besar.', 3, 5, '2025-06-20 10:30:10', '2025-06-20 10:30:10'),
(6, 'Kecerdasan Buatan', 'IF1406', 'Mempelajari konsep dan implementasi kecerdasan buatan.', 3, 6, '2025-06-20 10:30:10', '2025-06-20 10:30:10'),
(8, 'Mikrokontroler', 'IF1408', 'Pengenalan dan pemrograman mikrokontroler.', 1, 6, '2025-06-20 10:30:10', '2025-07-17 06:47:25'),
(9, 'Riset Operasi', 'UMUM101', 'Pengantar Riset Operasi.', 3, 5, '2025-06-20 10:30:10', '2025-07-17 06:44:45'),
(10, 'Algoritma', 'UMUM102', 'Pengantar Algoritma dan Struktur Data.', 3, 3, '2025-06-20 10:30:10', '2025-06-20 10:30:10');

-- 3. Insert ke tabel course_enrollments
INSERT INTO course_enrollments (enrollment_id, student_id, course_id, enrollment_date) VALUES
(1, 1, 1, '2025-06-20 10:30:10'),
(3, 1, 3, '2025-06-20 10:30:10'),
(4, 1, 4, '2025-06-20 10:30:10'),
(5, 1, 5, '2025-06-20 10:30:10'),
(6, 1, 6, '2025-06-20 10:30:10'),
(8, 1, 8, '2025-06-20 10:30:10'),
(9, 1, 9, '2025-06-20 10:30:10'),
(10, 1, 10, '2025-06-20 10:30:10'),
(14, 10, 1, '2025-07-05 04:06:00'),
(15, 11, 1, '2025-07-05 04:06:00'),
(16, 12, 1, '2025-07-05 04:06:00'),
(17, 13, 1, '2025-07-05 04:06:00'),
(18, 14, 1, '2025-07-05 04:06:00'),
(19, 10, 2, '2025-07-05 04:06:00'),
(20, 11, 2, '2025-07-05 04:06:00'),
(21, 12, 2, '2025-07-05 04:06:00'),
(22, 13, 2, '2025-07-05 04:06:00'),
(23, 14, 2, '2025-07-05 04:06:00'),
(24, 10, 3, '2025-07-05 04:06:00'),
(25, 11, 3, '2025-07-05 04:06:00'),
(26, 12, 3, '2025-07-05 04:06:00'),
(27, 13, 3, '2025-07-05 04:06:00'),
(28, 14, 3, '2025-07-05 04:06:00'),
(29, 10, 4, '2025-07-05 04:06:00'),
(30, 11, 4, '2025-07-05 04:06:00'),
(31, 12, 4, '2025-07-05 04:06:00'),
(32, 13, 4, '2025-07-05 04:06:00'),
(33, 14, 4, '2025-07-05 04:06:00'),
(34, 10, 5, '2025-07-05 04:06:00'),
(35, 11, 5, '2025-07-05 04:06:00'),
(36, 12, 5, '2025-07-05 04:06:00'),
(37, 13, 5, '2025-07-05 04:06:00'),
(38, 14, 5, '2025-07-05 04:06:00'),
(39, 10, 6, '2025-07-05 04:06:00'),
(40, 11, 6, '2025-07-05 04:06:00'),
(41, 12, 6, '2025-07-05 04:06:00'),
(42, 13, 6, '2025-07-05 04:06:00'),
(43, 14, 6, '2025-07-05 04:06:00'),
(49, 10, 8, '2025-07-05 04:06:00'),
(50, 11, 8, '2025-07-05 04:06:00'),
(51, 12, 8, '2025-07-05 04:06:00'),
(52, 13, 8, '2025-07-05 04:06:00'),
(53, 14, 8, '2025-07-05 04:06:00'),
(54, 10, 9, '2025-07-05 04:06:00'),
(55, 11, 9, '2025-07-05 04:06:00'),
(56, 12, 9, '2025-07-05 04:06:00'),
(57, 13, 9, '2025-07-05 04:06:00'),
(58, 14, 9, '2025-07-05 04:06:00'),
(59, 10, 10, '2025-07-05 04:06:00'),
(60, 11, 10, '2025-07-05 04:06:00'),
(61, 12, 10, '2025-07-05 04:06:00'),
(62, 13, 10, '2025-07-05 04:06:00'),
(63, 14, 10, '2025-07-05 04:06:00'),
(64, 1, 2, '2025-07-17 12:54:41');

-- 4. Insert ke tabel system_settings
INSERT INTO system_settings (id, setting_key, setting_value, description) VALUES
(1, 'site_name', 'E-Learning System', 'Nama aplikasi'),
(2, 'site_description', 'Sistem Manajemen Pembelajaran Online', 'Deskripsi aplikasi'),
(3, 'max_login_attempts', '3', 'Maksimal percobaan login'),
(4, 'lockout_duration', '1800', 'Durasi lockout dalam detik (30 menit)'),
(5, 'session_timeout', '3600', 'Timeout session dalam detik (1 jam)'),
(6, 'maintenance_mode', '0', 'Mode maintenance (0=off, 1=on)');

-- 5. Insert ke tabel schedules
INSERT INTO schedules (schedule_id, course_id, lecturer_id, day_of_week, start_time, end_time, room, class_type) VALUES
(2, 1, 2, 'Selasa', '07:00:00', '08:40:00', '07.01.04', 'Teori'),
(3, 2, 2, 'Selasa', '08:50:00', '10:30:00', '05.02.03', 'Teori'),
(4, 2, 2, 'Selasa', '10:40:00', '12:20:00', 'L 2.4.5', 'Praktikum'),
(5, 3, 3, 'Selasa', '13:20:00', '15:00:00', '05.04.02', 'Teori'),
(6, 4, 4, 'Rabu', '07:00:00', '08:40:00', '7.5.3', 'Praktikum'),
(7, 4, 4, 'Rabu', '10:40:00', '12:20:00', '05.04.04', 'Teori'),
(8, 5, 5, 'Rabu', '13:20:00', '15:00:00', '05.04.07', 'Teori'),
(9, 6, 6, 'Kamis', '08:50:00', '10:30:00', '05.04.03', 'Teori'),
(10, 5, 5, 'Kamis', '13:20:00', '15:00:00', '05.02.03', 'Teori'),
(11, 8, 7, 'Kamis', '15:30:00', '17:05:00', '05.04.02', 'Teori'),
(13, 8, 7, 'Jumat', '08:50:00', '10:30:00', 'L 2.4.3', 'Praktikum');

-- 6. Insert ke tabel announcements
INSERT INTO announcements (announcement_id, title, content, published_at, lecturer_id, course_id) VALUES
(3, 'Kuliah Pengganti', 'Kuliah Analisis Desain yang batal pada 30 Mei 2025 akan diganti pada 5 Juni 2025 pukul 13:00 di Ruang 204 Gedung B.', '2025-06-01 08:00:00', 3, 3),
(5, 'Perubahan Jadwal', 'Kuliah Big Data pada 3 Juni 2025 diubah waktunya menjadi pukul 15:00 di Ruang 405 Gedung D karena ada kegiatan kampus.', '2025-06-02 00:30:00', 5, 5),
(6, 'Pembatalan Kuliah', 'Kuliah Kecerdasan Buatan pada tanggal 5 Juni 2025 ditiadakan karena dosen, Prof. Dewi Sartika, Ph.D., sedang dinas luar.', '2025-06-02 04:00:00', 6, 6),
(8, 'Kuliah Pengganti', 'Kuliah Mikrokontroler yang batal pada 1 Juni 2025 akan diganti pada 7 Juni 2025 pukul 10:00 di Ruang 305 Gedung C.', '2025-06-02 07:00:00', 7, 8),
(9, 'Kelas hari ini kosong', 'soalnya gedung full', '2025-07-16 17:30:07', 2, 1),
(19, 'kelas kosong', 'ganti online ya', '2025-07-18 05:05:01', 2, 2);

-- 7. Insert ke tabel assignments
INSERT INTO assignments (assignment_id, course_id, title, description, due_date, max_grade, created_at, updated_at, file_path, file_type) VALUES
(1, 1, 'Tugas: Laporan Pemrograman Web Selasa 24 Juni 2025', 'Silakan unduh file tugas ini.', '2025-07-17 15:26:47', 100.00, '2025-07-10 13:26:47', '2025-07-10 13:26:47', 'uploads/686fbf9734f01_Laporan Pemrograman Web Selasa 24 Juni 2025.docx', 'docx'),
(2, 1, 'Tugas: elearning_designer diagram', 'Silakan unduh file tugas ini.', '2025-07-23 06:38:42', 100.00, '2025-07-16 04:38:42', '2025-07-16 04:38:42', 'uploads/68772cd29a960_elearning_designer diagram.pdf', 'pdf'),
(3, 1, 'Tugas: contoh format presentasi', 'Silakan unduh file tugas ini.', '2025-07-23 15:57:27', 100.00, '2025-07-16 13:57:27', '2025-07-16 13:57:27', 'uploads/6877afc7be1ea_contoh format presentasi.pptx', 'pptx'),
(4, 1, 'Tugas: profil', 'Silakan unduh file tugas ini.', '2025-07-24 04:38:09', 100.00, '2025-07-17 02:38:09', '2025-07-17 02:38:09', 'uploads/68786211098ec_profil.jpg', 'jpg'),
(6, 3, 'Tugas: Penugasan materi stakeholder', 'Silakan unduh file tugas ini.', '2025-08-30 13:35:00', 100.00, '2025-07-17 06:35:27', '2025-07-17 06:35:27', 'uploads/687899afca1e2_Penugasan materi stakeholder.docx', 'docx'),
(7, 3, 'Tugas: Tugas Materi Metodologi Pengembangan Sistem', 'Silakan unduh file tugas ini.', '2025-08-09 13:35:00', 100.00, '2025-07-17 06:35:55', '2025-07-17 06:35:55', 'uploads/687899cb95164_Tugas Materi Metodologi Pengembangan Sistem.docx', 'docx'),
(8, 10, 'Tugas: Tugas 1 Algoritma', 'Silakan unduh file tugas ini.', '2025-08-09 13:39:00', 100.00, '2025-07-17 06:39:03', '2025-07-17 06:39:03', 'uploads/68789a87b5c25_Tugas 1 Algoritma.pptx', 'pptx'),
(9, 10, 'Tugas: Tugas 2 Algoritma', 'Silakan unduh file tugas ini.', '2025-08-09 13:39:00', 100.00, '2025-07-17 06:39:17', '2025-07-17 06:39:17', 'uploads/68789a95085e2_Tugas 2 Algoritma.pptx', 'pptx'),
(10, 5, 'Tugas: Tugas_Big Data Praktikum2', 'Silakan unduh file tugas ini.', '2025-08-09 13:53:00', 100.00, '2025-07-17 06:53:53', '2025-07-17 06:53:53', 'uploads/68789e01e5755_Tugas_Big Data Praktikum2.pdf', 'pdf'),
(11, 9, 'Tugas: quiz riset', 'Silakan unduh file tugas ini.', '2025-08-09 13:55:00', 100.00, '2025-07-17 06:55:56', '2025-07-17 06:55:56', 'uploads/68789e7c69316_quiz riset.docx', 'docx'),
(12, 2, 'Tugas: Tugas 2 Algoritma', 'Silakan unduh file tugas ini.', '2025-07-24 09:14:08', 100.00, '2025-07-17 07:14:08', '2025-07-17 07:14:08', 'uploads/6878a2c0db3e9_Tugas 2 Algoritma.pptx', 'pptx'),
(13, 6, 'Tugas: Tugas #11 - Praktikum Pemrograman Web', 'Silakan unduh file tugas ini.', '2025-07-24 14:56:09', 100.00, '2025-07-17 12:56:09', '2025-07-17 12:56:09', 'uploads/6878f2e9dd4fe_Tugas #11 - Praktikum Pemrograman Web.docx', 'docx'),
(14, 8, 'Tugas: StudiKasus_Elearning', 'Silakan unduh file tugas ini.', '2025-07-24 14:56:30', 100.00, '2025-07-17 12:56:30', '2025-07-17 12:56:30', 'uploads/6878f2fedc9df_StudiKasus_Elearning.pdf', 'pdf');

-- 8. Insert ke tabel materials
INSERT INTO materials (material_id, course_id, title, description, file_path, file_type, uploaded_at) VALUES
(1, 1, 'WhatsApp Image 2025-07-14 at 08.20.22_1d5864b5.jpg', NULL, 'uploads/68772985633bc_WhatsApp Image 2025-07-14 at 08.20.22_1d5864b5.jpg', 'jpg', '2025-07-16 04:24:37'),
(2, 1, 'elearning_designer diagram.pdf', NULL, 'uploads/68772c5c46730_elearning_designer diagram.pdf', 'pdf', '2025-07-16 04:36:44'),
(3, 2, 'elearning_designer diagram.pdf', NULL, 'uploads/68772ce377563_elearning_designer diagram.pdf', 'pdf', '2025-07-16 04:38:59'),
(4, 1, 'WhatsApp Image 2025-07-13 at 18.48.00_0aae542a.jpg', NULL, 'uploads/6877474ee6289_WhatsApp Image 2025-07-13 at 18.48.00_0aae542a.jpg', 'jpg', '2025-07-16 06:31:42'),
(5, 3, 'Materi 1 Project Planning.pptx', NULL, 'uploads/6878917001034_Materi 1 Project Planning.pptx', 'pptx', '2025-07-17 06:00:16'),
(6, 3, 'Materi 2 Analisis Kelemahan Sistem.pptx', NULL, 'uploads/6878917aa94a4_Materi 2 Analisis Kelemahan Sistem.pptx', 'pptx', '2025-07-17 06:00:26'),
(7, 3, 'Materi 3 Analisis Kelayakan.pptx', NULL, 'uploads/6878918341a1f_Materi 3 Analisis Kelayakan.pptx', 'pptx', '2025-07-17 06:00:35'),
(8, 10, 'Materi 1 ALgoritma.pptx', NULL, 'uploads/68789a70df0a9_Materi 1 ALgoritma.pptx', 'pptx', '2025-07-17 06:38:40'),
(9, 10, 'Materi 2 Algoritma.pptx', NULL, 'uploads/68789a792b735_Materi 2 Algoritma.pptx', 'pptx', '2025-07-17 06:38:49'),
(10, 5, 'Praktikum 1_Evaluasi Model Regresi.pdf', NULL, 'uploads/68789d7b085ee_Praktikum 1_Evaluasi Model Regresi.pdf', 'pdf', '2025-07-17 06:51:39'),
(11, 5, 'Praktikum 2_EDA dan Deskriptive Statistik.pdf', NULL, 'uploads/68789dba910d1_Praktikum 2_EDA dan Deskriptive Statistik.pdf', 'pdf', '2025-07-17 06:52:42'),
(12, 9, 'Kuliah inventory.pdf', NULL, 'uploads/68789e6f6fd2a_Kuliah inventory.pdf', 'pdf', '2025-07-17 06:55:43'),
(13, 6, 'Pertemuan 4- Algoritma Genetika.pdf', NULL, 'uploads/68789f11968f8_Pertemuan 4- Algoritma Genetika.pdf', 'pdf', '2025-07-17 06:58:25'),
(14, 8, 'materi pertemuan 1.pdf', NULL, 'uploads/68789f3e849d9_materi pertemuan 1.pdf', 'pdf', '2025-07-17 06:59:10'),
(15, 5, '—Pngtree—traditional bow and arrow_7270789.png', NULL, 'uploads/6879d5085501a_—Pngtree—traditional bow and arrow_7270789.png', 'png', '2025-07-18 05:00:56');

-- 9. Insert ke tabel quizzes
INSERT INTO quizzes (quiz_id, course_id, title, description, duration_minutes, total_questions, passing_score, start_date, end_date, created_at, updated_at) VALUES
(1, 1, 'Quiz Aljabar Linear & Matriks', 'Kuis untuk menguji pemahaman materi Aljabar Linear & Matriks.', 90, 8, 70.00, '2025-07-01 00:00:00', '2025-07-31 23:59:59', '2025-06-20 10:30:10', '2025-07-16 16:34:50'),
(2, 2, 'Quiz Pemrograman Web Dasar', 'Kuis untuk menguji pemahaman dasar Pemrograman Web (HTML, CSS, JS).', 60, 10, 60.00, '2025-07-01 00:00:00', '2025-07-31 23:59:59', '2025-06-20 10:30:10', '2025-07-16 16:34:50'),
(3, 3, 'Kuis Analisis Desain Sistem Informasi', 'Kuis untuk menguji pemahaman materi Analisis Desain Sistem Informasi', 60, 3, 70.00, '2025-07-18 10:13:33', '2025-08-18 10:13:33', '2025-07-18 03:15:39', '2025-07-18 03:15:39'),
(4, 4, 'Kuis Dasar Multimedia', 'Kuis untuk menguji pemahaman materi Multimedia', 60, 3, 70.00, '2025-07-18 10:13:33', '2025-08-18 10:13:33', '2025-07-18 03:15:39', '2025-07-18 03:15:39'),
(5, 5, 'Kuis Pengantar Big Data', 'Kuis untuk menguji pemahaman materi Big Data', 60, 2, 70.00, '2025-07-18 10:13:33', '2025-08-18 10:13:33', '2025-07-18 03:15:39', '2025-07-18 03:15:39'),
(6, 6, 'Kuis Dasar Kecerdasan Buatan', 'Kuis untuk menguji pemahaman materi Kecerdasan Buatan', 60, 3, 70.00, '2025-07-18 10:13:33', '2025-08-18 10:13:33', '2025-07-18 03:15:39', '2025-07-18 03:15:39'),
(7, 8, 'Kuis Mikrokontroler Dasar', 'Kuis untuk menguji pemahaman materi Mikrokontroler', 60, 2, 70.00, '2025-07-18 10:13:33', '2025-08-18 10:13:33', '2025-07-18 03:15:40', '2025-07-18 03:15:40'),
(8, 9, 'Kuis Pengantar Riset Operasi', 'Kuis untuk menguji pemahaman materi Riset Operasi', 60, 2, 70.00, '2025-07-18 10:13:33', '2025-08-18 10:13:33', '2025-07-18 03:15:40', '2025-07-18 03:15:40'),
(9, 10, 'Kuis Dasar Algoritma', 'Kuis untuk menguji pemahaman materi Algoritma', 60, 3, 70.00, '2025-07-18 10:13:33', '2025-08-18 10:13:33', '2025-07-18 03:15:40', '2025-07-18 03:15:40');

-- 10. Insert ke tabel quiz_questions
INSERT INTO quiz_questions (question_id, quiz_id, question_text, question_formula, question_type, explanation) VALUES
(1, 1, 'Apa yang dimaksud dengan matriks identitas?', NULL, 'multiple_choice', NULL),
(2, 1, 'Jika A adalah matriks 3×2 dan B adalah matriks 2×4, maka hasil perkalian A×B menghasilkan matriks berukuran:', NULL, 'multiple_choice', NULL),
(3, 1, 'Determinan dari matriks 2×2 berikut ini adalah:', 'A = [2  3]\n    [1  4]', 'multiple_choice', NULL),
(4, 1, 'Suatu sistem persamaan linear homogen selalu memiliki:', NULL, 'multiple_choice', NULL),
(5, 1, 'Vektor-vektor berikut yang membentuk basis untuk R² adalah:', NULL, 'multiple_choice', NULL),
(6, 1, 'Rank dari matriks adalah:', NULL, 'multiple_choice', NULL),
(7, 1, 'Matriks A dapat diinvers jika dan hanya jika:', NULL, 'multiple_choice', NULL),
(8, 1, 'Dalam transformasi linear T: R² → R², jika T(1,0) = (2,3) dan T(0,1) = (1,4), maka T(2,3) adalah:', NULL, 'multiple_choice', NULL),
(10, 1, '1 + 1 + 2 =', 'hitunglah pake cara yang benar dan sertakan cara', 'multiple_choice', 'ya emg 4'),
(11, 2, 'Apa itu HTML?', NULL, 'essay', NULL),
(12, 2, 'Tag HTML mana yang digunakan untuk membuat paragraf?', NULL, 'multiple_choice', NULL),
(13, 2, 'Pilih tag yang benar untuk membuat link.', NULL, 'multiple_choice', NULL),
(14, 3, 'Apa tujuan utama dari fase analisis sistem dalam siklus pengembangan sistem?', NULL, 'multiple_choice', NULL),
(15, 3, 'Metodologi pengembangan sistem mana yang dikenal fleksibel dan berulang?', NULL, 'multiple_choice', NULL),
(16, 3, 'Jelaskan perbedaan antara data flow diagram (DFD) dan entity relationship diagram (ERD).', NULL, 'essay', NULL),
(17, 4, 'Format file gambar mana yang mendukung transparansi?', NULL, 'multiple_choice', NULL),
(18, 4, 'Apa perbedaan utama antara video raster dan video vektor?', NULL, 'essay', NULL),
(19, 4, 'Sebutkan salah satu format audio lossless.', NULL, 'multiple_choice', NULL),
(20, 5, 'Sebutkan 3V dalam konsep Big Data.', NULL, 'multiple_choice', NULL),
(21, 5, 'Apa fungsi dari Hadoop HDFS?', NULL, 'essay', NULL),
(22, 6, 'Algoritma pencarian mana yang menjamin menemukan solusi optimal?', NULL, 'multiple_choice', NULL),
(23, 6, 'Apa yang dimaksud dengan "Machine Learning"?', NULL, 'essay', NULL),
(24, 6, 'Sebutkan satu contoh aplikasi dari "Computer Vision".', NULL, 'multiple_choice', NULL),
(25, 7, 'Apa perbedaan antara mikrokontroler dan mikroprosesor?', NULL, 'essay', NULL),
(26, 7, 'Fungsi apa yang digunakan untuk membaca input digital pada Arduino?', NULL, 'multiple_choice', NULL),
(27, 8, 'Apa tujuan dari metode Simplex?', NULL, 'multiple_choice', NULL),
(28, 8, 'Jelaskan definisi dari "model" dalam konteks riset operasi.', NULL, 'essay', NULL),
(29, 9, 'Apa itu algoritma "Bubble Sort"?', NULL, 'multiple_choice', NULL),
(30, 9, 'Jelaskan perbedaan antara struktur data array dan linked list.', NULL, 'essay', NULL),
(31, 9, 'Kompleksitas waktu terbaik untuk algoritma "Quick Sort" adalah...', NULL, 'multiple_choice', NULL);

-- 11. Insert ke tabel question_options (Konversi boolean 0/1 ke FALSE/TRUE)
INSERT INTO question_options (option_id, question_id, option_text, is_correct) VALUES
(1, 1, 'Matriks yang semua elemennya bernilai 1', FALSE),
(2, 1, 'Matriks persegi yang elemen diagonal utamanya 1 dan elemen lainnya 0', TRUE),
(3, 1, 'Matriks yang determinannya sama dengan 1', FALSE),
(4, 1, 'Matriks yang tidak dapat diinvers', FALSE),
(5, 2, '3×4', TRUE),
(6, 2, '2×2', FALSE),
(7, 2, '3×2', FALSE),
(8, 2, 'Tidak dapat dikalikan', FALSE),
(9, 3, '5', TRUE),
(10, 3, '8', FALSE),
(11, 3, '11', FALSE),
(12, 3, '14', FALSE),
(13, 4, 'Solusi tunggal', FALSE),
(14, 4, 'Tidak ada solusi', FALSE),
(15, 4, 'Solusi trivial (x = 0)', TRUE),
(16, 4, 'Solusi tak hingga', FALSE),
(17, 5, '(1,2) dan (2,4)', FALSE),
(18, 5, '(1,0) dan (0,1)', TRUE),
(19, 5, '(3,6) dan (1,2)', FALSE),
(20, 5, '(0,0) dan (1,1)', FALSE),
(21, 6, 'Jumlah baris matriks', FALSE),
(22, 6, 'Jumlah kolom matriks', FALSE),
(23, 6, 'Jumlah maksimum baris atau kolom yang linear independen', TRUE),
(24, 6, 'Determinan matriks', FALSE),
(25, 7, 'A adalah matriks persegi', FALSE),
(26, 7, 'det(A) ≠ 0', TRUE),
(27, 7, 'A adalah matriks diagonal', FALSE),
(28, 7, 'Semua elemen A positif', FALSE),
(29, 8, '(7,18)', TRUE),
(30, 8, '(5,12)', FALSE),
(31, 8, '(4,9)', FALSE),
(32, 8, '(3,7)', FALSE),
(37, 10, '2', FALSE),
(38, 10, '4', TRUE),
(39, 10, '6', FALSE),
(40, 10, '8', FALSE),
(41, 11, '<p>', FALSE),
(42, 11, '<div>', FALSE),
(43, 11, '<br>', FALSE),
(44, 11, '<h1-6>', FALSE),
(45, 12, '<link>', FALSE),
(46, 12, '<href>', FALSE),
(47, 12, '<a>', TRUE),
(48, 12, '<url>', FALSE),
(49, 14, 'Membuat desain antarmuka pengguna', FALSE),
(50, 14, 'Mengidentifikasi kebutuhan pengguna dan sistem', TRUE),
(51, 14, 'Menulis kode program', FALSE),
(52, 14, 'Menguji perangkat lunak', FALSE),
(53, 15, 'Waterfall', FALSE),
(54, 15, 'Spiral', FALSE),
(55, 15, 'Agile', TRUE),
(56, 15, 'V-Model', FALSE),
(57, 17, 'JPEG', FALSE),
(58, 17, 'GIF', TRUE),
(59, 17, 'BMP', FALSE),
(60, 17, 'TIFF', FALSE),
(61, 19, 'MP3', FALSE),
(62, 19, 'WAV', TRUE),
(63, 19, 'AAC', FALSE),
(64, 19, 'Ogg Vorbis', FALSE),
(65, 20, 'Volume, Velocity, Variety', TRUE),
(66, 20, 'Value, Versatility, Volume', FALSE),
(67, 20, 'Velocity, Veracity, Validity', FALSE),
(68, 20, 'Variety, Velocity, Volatility', FALSE),
(69, 22, 'Breadth-First Search (BFS)', TRUE),
(70, 22, 'Depth-First Search (DFS)', FALSE),
(71, 22, 'Greedy Best-First Search', FALSE),
(72, 22, 'Hill Climbing', FALSE),
(73, 24, 'Chatbot', FALSE),
(74, 24, 'Sistem pengenalan wajah', TRUE),
(75, 24, 'Sistem rekomendasi', FALSE),
(76, 24, 'Analisis sentimen', FALSE),
(77, 26, 'pinMode()', FALSE),
(78, 26, 'digitalWrite()', FALSE),
(79, 26, 'digitalRead()', TRUE),
(80, 26, 'analogRead()', FALSE),
(81, 27, 'Memecahkan masalah non-linear', FALSE),
(82, 27, 'Mengoptimalkan masalah pemrograman linear', TRUE),
(83, 27, 'Menganalisis deret waktu', FALSE),
(84, 27, 'Mengklasifikasikan data', FALSE),
(85, 29, 'Algoritma pencarian', FALSE),
(86, 29, 'Algoritma pengurutan', TRUE),
(87, 29, 'Algoritma kompresi data', FALSE),
(88, 29, 'Algoritma graf', FALSE),
(89, 31, 'O(n^2)', FALSE),
(90, 31, 'O(log n)', FALSE),
(91, 31, 'O(n log n)', TRUE),
(92, 31, 'O(n)', FALSE);

-- 12. Insert ke tabel exams
INSERT INTO exams (exam_id, course_id, title, exam_type, exam_date, start_time, end_time, room, is_online, online_link, duration_minutes, total_questions, exam_status, created_at, updated_at, quiz_id) VALUES
(1, 1, 'Ujian Tengah Semester Aljabar Linear', 'UTS', '2025-08-18', '08:00:00', '10:00:00', 'Ruang Ujian Utama', FALSE, NULL, 120, NULL, 'Scheduled', '2025-06-20 10:30:10', '2025-07-18 06:46:09', NULL),
(2, 9, 'Ujian Tengah Semester Riset Operasi', 'UTS', '2025-08-18', '08:00:00', '10:00:00', 'Ruang Ujian Utama', FALSE, NULL, 120, NULL, 'Scheduled', '2025-06-20 10:30:10', '2025-07-18 06:46:09', 1),
(3, 10, 'Ujian Tengah Semester Algoritma', 'UTS', '2025-08-18', '08:00:00', '10:00:00', 'Ruang Ujian Utama', FALSE, NULL, 120, NULL, 'Scheduled', '2025-06-20 10:30:10', '2025-07-18 06:46:09', NULL),
(4, 2, 'Ujian Tengah Semester Pemrograman', 'UTS', '2025-08-18', '08:00:00', '10:00:00', 'Ruang Ujian Utama', FALSE, NULL, 120, NULL, 'Scheduled', '2025-06-20 10:30:10', '2025-07-18 06:46:09', 2),
(8, 4, 'Ujian Tengah Semester Multimedia', 'UTS', '2025-08-18', '08:00:00', '10:00:00', 'Ruang Ujian Utama', FALSE, NULL, 120, NULL, 'Scheduled', '2025-06-20 10:30:10', '2025-07-18 06:46:09', NULL),
(9, 3, 'Ujian Tengah Semester Analisis Desain', 'UTS', '2025-08-18', '08:00:00', '10:00:00', 'Ruang Ujian Utama', FALSE, NULL, 120, NULL, 'Scheduled', '2025-06-20 10:30:10', '2025-07-18 06:46:09', 1),
(10, 1, 'Ujian Akhir Semester Aljabar Linear', 'UAS', '2025-08-18', '08:00:00', '10:00:00', 'Ruang Ujian Utama', FALSE, NULL, 180, NULL, 'Scheduled', '2025-06-20 10:30:10', '2025-07-18 06:46:09', NULL),
(11, 9, 'Ujian Akhir Semester Riset Operasi', 'UAS', '2025-08-18', '08:00:00', '10:00:00', 'Ruang Ujian Utama', FALSE, NULL, 180, NULL, 'Scheduled', '2025-06-20 10:30:10', '2025-07-18 06:46:09', 1),
(12, 10, 'Ujian Akhir Semester Algoritma', 'UAS', '2025-08-18', '08:00:00', '10:00:00', 'Ruang Ujian Utama', FALSE, NULL, 180, NULL, 'Scheduled', '2025-06-20 10:30:10', '2025-07-18 06:46:09', NULL),
(13, 2, 'Ujian Akhir Semester Pemrograman', 'UAS', '2025-08-18', '08:00:00', '10:00:00', 'Ruang Ujian Utama', FALSE, NULL, 180, NULL, 'Scheduled', '2025-06-20 10:30:10', '2025-07-18 06:46:09', NULL),
(17, 4, 'Ujian Akhir Semester Multimedia', 'UAS', '2025-08-18', '08:00:00', '10:00:00', 'Ruang Ujian Utama', FALSE, NULL, 180, NULL, 'Scheduled', '2025-06-20 10:30:10', '2025-07-18 06:46:09', NULL),
(18, 3, 'Ujian Akhir Semester Analisis Desain', 'UAS', '2025-08-18', '08:00:00', '10:00:00', 'Ruang Ujian Utama', FALSE, NULL, 180, NULL, 'Scheduled', '2025-06-20 10:30:10', '2025-07-18 06:46:09', 1);

-- 13. Insert ke tabel feedback
INSERT INTO feedback (feedback_id, name, email, subject, message, submitted_at) VALUES
(1, 'resti', 'auliaresty183@gmail.com', 'ya', 'ya', '2025-07-05 06:26:40'),
(2, 'rer', 'auliaresty183@gmail.com', 'y', 'y', '2025-07-05 06:29:26'),
(3, 'Aulia Resty', 'auliaresty183@gmail.com', 'ya', 'hehehehhe', '2025-07-05 06:35:18'),
(4, 'jiej', 'auliaresty183@gmail.com', 'hai', 'hai', '2025-07-05 06:44:10'),
(5, 'aulia', 'auliaresty183@gmail.com', 'hai pemro web', 'sangat seru belajar ngoding', '2025-07-05 06:45:16'),
(6, 'resti', 'auliaresty183@gmail.com', 'ya', 'bagus aplikasinya mantab', '2025-07-07 07:54:34'),
(7, 'Aulia Resty', 'auliaresty183@gmail.com', 'pesan', 'sudah bagus mantap', '2025-07-17 02:25:45'),
(8, 'almas', 'almas10@gmail.com', 'pesan', 'semoga sukses dan sehat selalu', '2025-07-17 12:09:10'),
(9, 'Aulia Resty', 'auliaresty183@gmail.com', 'hai pemro web', 'halo', '2025-07-18 04:57:40');

-- 14. Reset sequence auto-increment agar saat insert baru tidak konflik
SELECT setval(pg_get_serial_sequence('users', 'user_id'), COALESCE(MAX(user_id), 1)) FROM users;
SELECT setval(pg_get_serial_sequence('courses', 'course_id'), COALESCE(MAX(course_id), 1)) FROM courses;
SELECT setval(pg_get_serial_sequence('course_enrollments', 'enrollment_id'), COALESCE(MAX(enrollment_id), 1)) FROM course_enrollments;
SELECT setval(pg_get_serial_sequence('announcements', 'announcement_id'), COALESCE(MAX(announcement_id), 1)) FROM announcements;
SELECT setval(pg_get_serial_sequence('assignments', 'assignment_id'), COALESCE(MAX(assignment_id), 1)) FROM assignments;
SELECT setval(pg_get_serial_sequence('materials', 'material_id'), COALESCE(MAX(material_id), 1)) FROM materials;
SELECT setval(pg_get_serial_sequence('quizzes', 'quiz_id'), COALESCE(MAX(quiz_id), 1)) FROM quizzes;
SELECT setval(pg_get_serial_sequence('quiz_questions', 'question_id'), COALESCE(MAX(question_id), 1)) FROM quiz_questions;
SELECT setval(pg_get_serial_sequence('question_options', 'option_id'), COALESCE(MAX(option_id), 1)) FROM question_options;
SELECT setval(pg_get_serial_sequence('exams', 'exam_id'), COALESCE(MAX(exam_id), 1)) FROM exams;
SELECT setval(pg_get_serial_sequence('feedback', 'feedback_id'), COALESCE(MAX(feedback_id), 1)) FROM feedback;
SELECT setval(pg_get_serial_sequence('schedules', 'schedule_id'), COALESCE(MAX(schedule_id), 1)) FROM schedules;
SELECT setval(pg_get_serial_sequence('system_settings', 'id'), COALESCE(MAX(id), 1)) FROM system_settings;
