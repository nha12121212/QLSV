# 🎓 Hệ thống Quản lý Sinh viên

Ứng dụng web PHP quản lý sinh viên đầy đủ chức năng.

## 📁 Cấu trúc thư mục

```
quan_ly_sinh_vien/
├── index.php          ← Dashboard tổng quan
├── login.php          ← Đăng nhập / Đăng ký
├── logout.php         ← Đăng xuất
├── sinh_vien.php      ← Danh sách sinh viên
├── them_sv.php        ← Thêm sinh viên mới
├── sua_sv.php         ← Sửa thông tin sinh viên
├── khoa.php           ← Quản lý khoa (Admin)
├── tai_khoan.php      ← Quản lý tài khoản (Admin)
├── css/
│   └── style.css      ← Giao diện
└── includes/
    ├── db.php         ← Kết nối & khởi tạo database
    ├── auth.php       ← Xác thực & phiên đăng nhập
    └── sidebar.php    ← Thanh điều hướng
```

## ⚙️ Cài đặt

### Yêu cầu
- PHP 7.4+
- MySQL 5.7+ hoặc MariaDB 10+
- Web server: Apache/Nginx (hoặc XAMPP/WAMP/Laragon)

### Các bước

1. **Sao chép thư mục** vào `htdocs` (XAMPP) hoặc `www` (WAMP):
   ```
   C:/xampp/htdocs/quan_ly_sinh_vien/
   ```

2. **Cấu hình database** trong `includes/db.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');       // username MySQL
   define('DB_PASS', '');           // password MySQL
   define('DB_NAME', 'quan_ly_sinh_vien');
   ```

3. **Truy cập trang web**:
   ```
   http://localhost/quan_ly_sinh_vien/login.php
   ```
   > Database và dữ liệu mẫu được tạo tự động khi truy cập lần đầu!

## 🔑 Tài khoản mặc định

| Vai trò | Username | Password |
|---------|----------|----------|
| Admin   | admin    | admin123 |

## ✨ Chức năng

| Chức năng | Mô tả |
|-----------|-------|
| 🔐 Đăng nhập/Đăng ký | Xác thực người dùng |
| 📊 Dashboard | Thống kê tổng quan |
| 👨‍🎓 Danh sách SV | Tìm kiếm, lọc, phân trang |
| ➕ Thêm SV | Form thêm sinh viên mới |
| ✏️ Sửa SV | Chỉnh sửa thông tin |
| 🗑 Xóa SV | Xóa đơn / xóa nhiều |
| 👁 Chi tiết | Xem thông tin đầy đủ |
| 🏫 Quản lý Khoa | CRUD khoa (Admin) |
| 👥 Tài khoản | Quản lý user (Admin) |

## 🛠 Công nghệ

- **Backend**: PHP 8+ (MySQLi)
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript (thuần)
- **Font**: Be Vietnam Pro, Space Grotesk (Google Fonts)
