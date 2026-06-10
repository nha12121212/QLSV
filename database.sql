-- ============================================================
--  HỆ THỐNG QUẢN LÝ SINH VIÊN
--  File: database.sql
--  Mô tả: Tạo database, bảng và dữ liệu mẫu đầy đủ
--  Chạy bằng: phpMyAdmin hoặc mysql -u root -p < database.sql
-- ============================================================

-- Tạo & chọn database
DROP DATABASE IF EXISTS quan_ly_sinh_vien;
CREATE DATABASE quan_ly_sinh_vien
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE quan_ly_sinh_vien;

-- ============================================================
-- BẢNG: users (Tài khoản đăng nhập)
-- ============================================================
CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    ho_ten     VARCHAR(100),
    role       ENUM('admin','user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BẢNG: khoa (Khoa / Bộ môn)
-- ============================================================
CREATE TABLE khoa (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    ten_khoa VARCHAR(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BẢNG: sinh_vien (Hồ sơ sinh viên)
-- ============================================================
CREATE TABLE sinh_vien (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    ma_sv          VARCHAR(20)  NOT NULL UNIQUE,
    ho_ten         VARCHAR(100) NOT NULL,
    ngay_sinh      DATE,
    gioi_tinh      ENUM('Nam','Nữ','Khác') DEFAULT 'Nam',
    dia_chi        TEXT,
    email          VARCHAR(100),
    so_dien_thoai  VARCHAR(20),
    khoa_id        INT,
    nien_khoa      VARCHAR(20),
    gpa            DECIMAL(3,2) DEFAULT 0.00,
    trang_thai     ENUM('Đang học','Bảo lưu','Tốt nghiệp','Đình chỉ') DEFAULT 'Đang học',
    anh_dai_dien   VARCHAR(255) DEFAULT '',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (khoa_id) REFERENCES khoa(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DỮ LIỆU: users
-- Mật khẩu admin  = admin123
-- Mật khẩu user1  = user123
-- Mật khẩu user2  = user123
-- (băm bằng password_hash PHP / bcrypt)
-- ============================================================
INSERT INTO users (username, password, ho_ten, role) VALUES
('admin',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản trị viên',      'admin'),
('giaovu', '$2y$10$TKh8H1.PFgs3GPzN0/9Q8.k2E.n/4YpkK6RBMIY8tCbFOOJpAhO.2', 'Nguyễn Thị Giáo Vụ', 'user'),
('ketoan', '$2y$10$TKh8H1.PFgs3GPzN0/9Q8.k2E.n/4YpkK6RBMIY8tCbFOOJpAhO.2', 'Trần Văn Kế Toán',   'user');

-- Lưu ý: password_hash dùng PASSWORD_DEFAULT (bcrypt)
-- admin123 => $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- user123  => $2y$10$TKh8H1.PFgs3GPzN0/9Q8.k2E.n/4YpkK6RBMIY8tCbFOOJpAhO.2

-- ============================================================
-- DỮ LIỆU: khoa
-- ============================================================
INSERT INTO khoa (id, ten_khoa) VALUES
(1, 'Công nghệ thông tin'),
(2, 'Kinh tế'),
(3, 'Kỹ thuật điện – Điện tử'),
(4, 'Quản trị kinh doanh'),
(5, 'Ngôn ngữ Anh'),
(6, 'Kế toán – Kiểm toán'),
(7, 'Xây dựng – Kiến trúc'),
(8, 'Y dược');

-- ============================================================
-- DỮ LIỆU: sinh_vien  (40 sinh viên mẫu)
-- ============================================================
INSERT INTO sinh_vien
    (ma_sv, ho_ten, ngay_sinh, gioi_tinh, dia_chi, email, so_dien_thoai, khoa_id, nien_khoa, gpa, trang_thai)
VALUES

-- CNTT (khoa 1)
('SV2101', 'Nguyễn Văn An',        '2003-03-15', 'Nam', '12 Lê Lợi, Quảng Ngãi',         'nguyenvanan@email.com',       '0901234501', 1, '2021-2025', 3.65, 'Đang học'),
('SV2102', 'Trần Thị Bảo Châu',    '2003-07-22', 'Nữ',  '45 Hùng Vương, Đà Nẵng',        'tranthichaub@email.com',      '0901234502', 1, '2021-2025', 3.82, 'Đang học'),
('SV2103', 'Lê Minh Cường',        '2003-11-08', 'Nam', '78 Nguyễn Huệ, TP.HCM',         'leminhcuong@email.com',       '0901234503', 1, '2021-2025', 2.95, 'Đang học'),
('SV2104', 'Phạm Thị Diễm',        '2002-05-30', 'Nữ',  '23 Trần Phú, Nha Trang',        'phamthidiem@email.com',       '0901234504', 1, '2021-2025', 3.40, 'Đang học'),
('SV2105', 'Hoàng Văn Đức',        '2003-01-17', 'Nam', '56 Đinh Tiên Hoàng, Huế',       'hoangvanduc@email.com',       '0901234505', 1, '2021-2025', 3.15, 'Đang học'),

-- Kinh tế (khoa 2)
('SV2006', 'Võ Thị Ems',           '2002-09-12', 'Nữ',  '34 Lý Thường Kiệt, Hà Nội',     'vothiems@email.com',          '0901234506', 2, '2020-2024', 3.55, 'Đang học'),
('SV2007', 'Đặng Quốc Fong',       '2002-04-25', 'Nam', '89 Bạch Đằng, Hải Phòng',       'dangquocfong@email.com',      '0901234507', 2, '2020-2024', 2.80, 'Đang học'),
('SV2008', 'Bùi Thị Giang',        '2002-12-03', 'Nữ',  '67 Cách Mạng Tháng 8, Cần Thơ','buithigiang@email.com',       '0901234508', 2, '2020-2024', 3.90, 'Đang học'),
('SV1909', 'Ngô Hữu Hùng',         '2001-06-18', 'Nam', '11 Pasteur, Biên Hòa',          'ngohuuhung@email.com',        '0901234509', 2, '2019-2023', 3.20, 'Tốt nghiệp'),
('SV1910', 'Phan Thị Kim Yen',     '2001-02-27', 'Nữ',  '5 Hoàng Văn Thụ, Buôn Ma Thuột','phanthikimyen@email.com',    '0901234510', 2, '2019-2023', 3.78, 'Tốt nghiệp'),

-- Kỹ thuật điện (khoa 3)
('SV2211', 'Trịnh Văn Khoa',       '2004-08-14', 'Nam', '90 Trần Hưng Đạo, Vinh',        'trinhvankhoa@email.com',      '0901234511', 3, '2022-2026', 3.05, 'Đang học'),
('SV2212', 'Lương Thị Lan',        '2004-03-07', 'Nữ',  '22 Ngô Quyền, Thái Nguyên',     'luongthilan@email.com',       '0901234512', 3, '2022-2026', 2.75, 'Đang học'),
('SV2113', 'Đinh Công Minh',       '2003-10-20', 'Nam', '44 Lê Duẩn, Đồng Hới',          'dinhcongminh@email.com',      '0901234513', 3, '2021-2025', 3.50, 'Đang học'),
('SV2014', 'Hồ Thị Ngọc',         '2002-01-09', 'Nữ',  '77 Hai Bà Trưng, Đà Lạt',       'hothingoc@email.com',         '0901234514', 3, '2020-2024', 3.35, 'Đang học'),
('SV1915', 'Vũ Đình Oanh',        '2001-07-31', 'Nam', '13 Phan Đình Phùng, Quy Nhơn',  'vudinhoah@email.com',         '0901234515', 3, '2019-2023', 2.60, 'Bảo lưu'),

-- Quản trị kinh doanh (khoa 4)
('SV2216', 'Mai Thị Phương',       '2004-05-22', 'Nữ',  '38 Nguyễn Trãi, Long An',       'maithiphuong@email.com',      '0901234516', 4, '2022-2026', 3.70, 'Đang học'),
('SV2117', 'Cao Xuân Quý',         '2003-09-16', 'Nam', '61 Trịnh Công Sơn, Huế',        'caoxuanquy@email.com',        '0901234517', 4, '2021-2025', 3.25, 'Đang học'),
('SV2118', 'Đỗ Thị Rồng',         '2003-04-11', 'Nữ',  '9 Chu Văn An, Hà Tĩnh',         'dothirong@email.com',         '0901234518', 4, '2021-2025', 2.90, 'Đang học'),
('SV2019', 'Lê Văn Sơn',          '2002-11-28', 'Nam', '55 Lý Tự Trọng, Cà Mau',        'levanson@email.com',          '0901234519', 4, '2020-2024', 3.45, 'Đang học'),
('SV1920', 'Phùng Thị Tâm',       '2001-08-05', 'Nữ',  '18 Nguyễn Văn Cừ, Vũng Tàu',   'phunghitam@email.com',        '0901234520', 4, '2019-2023', 3.88, 'Tốt nghiệp'),

-- Ngôn ngữ Anh (khoa 5)
('SV2221', 'Trương Văn Uy',        '2004-02-14', 'Nam', '72 Lê Thánh Tông, Quảng Nam',   'truongvanuy@email.com',       '0901234521', 5, '2022-2026', 3.60, 'Đang học'),
('SV2222', 'Nguyễn Thị Vân',      '2004-06-30', 'Nữ',  '3 Phạm Hồng Thái, Lâm Đồng',   'nguyenthivan@email.com',      '0901234522', 5, '2022-2026', 3.95, 'Đang học'),
('SV2123', 'Hoàng Minh Ước',      '2003-12-19', 'Nam', '26 Trần Cao Vân, Tam Kỳ',       'hoangminhuc@email.com',       '0901234523', 5, '2021-2025', 3.10, 'Đang học'),
('SV2024', 'Dương Thị Xuân',      '2002-03-08', 'Nữ',  '49 Hoàng Diệu, Phan Thiết',     'duongthixuan@email.com',      '0901234524', 5, '2020-2024', 3.30, 'Đang học'),
('SV1925', 'Lâm Văn Yên',         '2001-10-23', 'Nam', '84 Lê Hồng Phong, Rạch Giá',    'lamvanyen@email.com',         '0901234525', 5, '2019-2023', 2.55, 'Đình chỉ'),

-- Kế toán (khoa 6)
('SV2226', 'Kiều Thị Zin',        '2004-07-04', 'Nữ',  '31 Nguyễn Công Trứ, Thủ Dầu Một','kieuthi.zin@email.com',      '0901234526', 6, '2022-2026', 3.75, 'Đang học'),
('SV2227', 'Mạc Văn Anh Tuấn',    '2004-01-17', 'Nam', '57 Điện Biên Phủ, Đà Nẵng',     'macvananhtuan@email.com',     '0901234527', 6, '2022-2026', 3.20, 'Đang học'),
('SV2128', 'Tô Thị Bích Ngân',    '2003-05-26', 'Nữ',  '16 Quang Trung, Hội An',        'tothibichngan@email.com',     '0901234528', 6, '2021-2025', 3.50, 'Đang học'),
('SV2029', 'Nguyễn Đức Cảnh',    '2002-09-02', 'Nam', '42 Lê Văn Sỹ, TP.HCM',          'nguyenductanh@email.com',    '0901234529', 6, '2020-2024', 2.70, 'Đang học'),
('SV1930', 'Trần Thị Diệu Linh', '2001-04-15', 'Nữ',  '8 Nam Kỳ Khởi Nghĩa, Cần Thơ',  'tranthidieulinh@email.com',  '0901234530', 6, '2019-2023', 3.65, 'Tốt nghiệp'),

-- Xây dựng (khoa 7)
('SV2231', 'Phan Minh Đăng',      '2004-11-11', 'Nam', '65 Lý Nhân Tông, Bình Dương',    'phanminhdang@email.com',      '0901234531', 7, '2022-2026', 3.00, 'Đang học'),
('SV2132', 'Đặng Thị Ế Em',       '2003-06-29', 'Nữ',  '29 Trần Nhân Tông, Hà Nội',     'dangthieem@email.com',        '0901234532', 7, '2021-2025', 2.85, 'Đang học'),
('SV2033', 'Bùi Quốc Hào',        '2002-02-21', 'Nam', '73 Nguyễn Đình Chiểu, Quy Nhơn','buiquochao@email.com',        '0901234533', 7, '2020-2024', 3.40, 'Đang học'),
('SV1934', 'Võ Thị Ích',          '2001-12-06', 'Nữ',  '37 Phan Bội Châu, Đà Lạt',      'vothiich@email.com',          '0901234534', 7, '2019-2023', 3.15, 'Bảo lưu'),
('SV2335', 'Hà Văn Khải',         '2005-08-18', 'Nam', '94 Trần Bình Trọng, Vinh',       'havankhai@email.com',         '0901234535', 7, '2023-2027', 2.50, 'Đang học'),

-- Y dược (khoa 8)
('SV2236', 'Lý Thị Lan Phương',   '2004-04-03', 'Nữ',  '52 Hai Bà Trưng, Huế',          'lythilanphuong@email.com',    '0901234536', 8, '2022-2028', 3.80, 'Đang học'),
('SV2237', 'Mỵ Văn Minh',         '2004-10-27', 'Nam', '14 Lý Chiêu Hoàng, Hà Nội',     'myvanminh@email.com',         '0901234537', 8, '2022-2028', 3.55, 'Đang học'),
('SV2138', 'Nông Thị Ngọc Hà',   '2003-07-15', 'Nữ',  '81 Trần Quý Cáp, Đà Nẵng',      'nongthingoc@email.com',       '0901234538', 8, '2021-2027', 3.70, 'Đang học'),
('SV2039', 'Ông Văn Phúc',        '2002-01-30', 'Nam', '28 Ngô Gia Tự, TP.HCM',         'ongvanphuc@email.com',        '0901234539', 8, '2020-2026', 3.25, 'Đang học'),
('SV1940', 'Phó Thị Quyên',       '2001-05-12', 'Nữ',  '63 Phan Chu Trinh, Nha Trang',  'phothi.quyen@email.com',      '0901234540', 8, '2019-2025', 2.95, 'Đang học');

-- ============================================================
-- VIEW: thống kê theo khoa
-- ============================================================
CREATE OR REPLACE VIEW v_thong_ke_khoa AS
SELECT
    k.id,
    k.ten_khoa,
    COUNT(s.id)                                         AS tong_sv,
    SUM(s.trang_thai = 'Đang học')                      AS dang_hoc,
    SUM(s.trang_thai = 'Tốt nghiệp')                    AS tot_nghiep,
    SUM(s.trang_thai = 'Bảo lưu')                       AS bao_luu,
    SUM(s.trang_thai = 'Đình chỉ')                      AS dinh_chi,
    ROUND(AVG(s.gpa), 2)                                AS gpa_tb
FROM khoa k
LEFT JOIN sinh_vien s ON s.khoa_id = k.id
GROUP BY k.id, k.ten_khoa;

-- ============================================================
-- Xác nhận dữ liệu
-- ============================================================
SELECT CONCAT('✅ users:      ', COUNT(*), ' bản ghi') AS ket_qua FROM users
UNION ALL
SELECT CONCAT('✅ khoa:       ', COUNT(*), ' bản ghi') FROM khoa
UNION ALL
SELECT CONCAT('✅ sinh_vien:  ', COUNT(*), ' bản ghi') FROM sinh_vien;
