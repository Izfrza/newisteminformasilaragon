-- school_website.sql
-- Buat database
CREATE DATABASE IF NOT EXISTS school_website;
USE school_website;

-- Tabel untuk pengaturan sekolah
CREATE TABLE school_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    vision TEXT NOT NULL,
    mission TEXT NOT NULL,
    total_students INT DEFAULT 0,
    hero_background TEXT,
    section_background TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk admin
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'superadmin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel untuk berita
CREATE TABLE news (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image TEXT NOT NULL,
    date DATE NOT NULL,
    status ENUM('published', 'draft') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk galeri
CREATE TABLE gallery (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    image TEXT NOT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel untuk guru
CREATE TABLE teachers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert data default
INSERT INTO school_settings (name, address, phone, email, vision, mission, total_students, hero_background, section_background) VALUES
('SMA Negeri 1 Maju Jaya', 'Jl. Pendidikan No. 123, Kelurahan Maju Jaya, Kecamatan Cerdas, Kota Pintar', '(021) 1234-5678', 'info@sman1majujaya.sch.id', 'Menjadi sekolah unggulan yang menghasilkan lulusan berkarakter, berprestasi, dan mampu bersaing di era global.', 'Menyelenggarakan pendidikan yang berkualitas dan berkarakter\nMengembangkan potensi peserta didik secara optimal\nMendorong inovasi dalam pembelajaran\nMenjalin kerjasama dengan masyarakat dan dunia usaha', 1250, 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80', '');

INSERT INTO admins (username, password, fullname, phone, role) VALUES
('superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Administrator', '+628123456789', 'superadmin'),
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '+628987654321', 'admin');

INSERT INTO news (title, content, image, date, status) VALUES
('Penerimaan Peserta Didik Baru Tahun 2023', 'SMA Negeri 1 Maju Jaya membuka pendaftaran untuk peserta didik baru tahun ajaran 2023/2024. Pendaftaran dibuka dari tanggal 1 Maret hingga 30 April 2023.', 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', '2023-02-15', 'published');

INSERT INTO gallery (title, image, date) VALUES
('Kegiatan Upacara Bendera', 'https://images.unsplash.com/photo-1588072432836-100b94eb52c7?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', '2023-02-17');

INSERT INTO teachers (name, subject) VALUES
('Dr. Budi Santoso, M.Pd.', 'Kepala Sekolah'),
('Ibu Rita Wati, S.Si.', 'Kimia');