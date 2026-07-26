CREATE DATABASE IF NOT EXISTS Project_cv;

USE Project_cv;

-- Tabel users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel cv_data
CREATE TABLE cv_data (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    nama VARCHAR(100),
    email VARCHAR(100),
    telepon VARCHAR(20),
    alamat VARCHAR(255),
    pendidikan TEXT,
    pengalaman_kerja TEXT,
    keahlian TEXT,
    deskripsi TEXT,
    foto VARCHAR(255),
    is_default BOOLEAN DEFAULT FALSE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert data default
INSERT INTO users (username, password, email, role, status) VALUES 
('admin', MD5('admin123'), 'admin@cvapp.com', 'admin', 'active'),
('cecep_suwanda', MD5('cecep123'), 'cecep@unibba.ac.id', 'user', 'active'),
('setiawan_hadi', MD5('hadi123'), 'setiawanhadi@760.com', 'user', 'active');

INSERT INTO cv_data (user_id, nama, email, telepon, alamat, pendidikan, pengalaman_kerja, keahlian, deskripsi, foto, is_default) VALUES 
(2, 'Cecep Suwanda, S.Si., M.Kom.', 'cecep@unibba.ac.id', '+62 812 3456 7890', 'Bandung, Indonesia', 
 'S2 Teknik Informatika - ITB (2015-2017)\nS1 Teknik Informatika - UNIBBA (2010-2014)',
 'Dosen Tetap - Universitas Bale Bandung (2018-sekarang)\n• Mengajar Pemrograman Internet, Basis Data, dan Jaringan\n• Membimbing skripsi dan magang mahasiswa\n• Menjadi Ketua Program Studi Teknik Informatika\n\nPengembang Web Freelance (2015-2018)\n• Membangun aplikasi web untuk UMKM\n• Konsultan IT untuk perusahaan startup',
 'PHP, JavaScript, Python, MySQL, Laravel, React, Jaringan Komputer, Keamanan Siber, UML, Figma',
 'Saya adalah akademisi dan praktisi di bidang teknologi informasi dengan pengalaman lebih dari 8 tahun dalam pengajaran dan pengembangan aplikasi web. Berkomitmen untuk mencetak generasi IT yang kompeten dan berdaya saing tinggi.',
 'uploads/photos/cecep.jpg', TRUE),

(3, 'Setiawan Hadi Nugraha', 'setiawanhadi760@gmail.com', '+62 851 4739 2060', 'Bandung, Indonesia',
 'SMK 2 LPPM RI MAJALAYA (2019-2022)\nJuara 1 Game Developer - Agate Academy Challenge Indonesia',
 'Game Developer (Freelance / Indie) (2023 - )\n• Merancang dan mengimplementasikan mekanik gameplay, logika permainan, serta sistem AI/NPC\n• Mengoptimalkan performa game dan melakukan debugging pada kode pemrograman.\n• Mengintegrasikan sistem interaktif seperti gesture recognition dan kontrol berbasis sensor.',
 'Game Development, Gameplay Programming, C# / C++, Python, Game Mechanics, Unity / Unreal Engine, Computer Vision & Interaction, Algoritma AI Game, Debugging & Optimization',
 'Saya adalah Game Developer dan Mahasiswa Teknik Informatika yang berfokus pada perancangan gameplay logic, pembuatan sistem interaktif, dan optimalisasi performa game.',
 NULL, FALSE);
 