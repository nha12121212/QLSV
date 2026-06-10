<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$conn = getDB();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma_sv      = trim($_POST['ma_sv'] ?? '');
    $ho_ten     = trim($_POST['ho_ten'] ?? '');
    $ngay_sinh  = $_POST['ngay_sinh'] ?? '';
    $gioi_tinh  = $_POST['gioi_tinh'] ?? 'Nam';
    $dia_chi    = trim($_POST['dia_chi'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $sdt        = trim($_POST['so_dien_thoai'] ?? '');
    $khoa_id    = (int)$_POST['khoa_id'];
    $nien_khoa  = trim($_POST['nien_khoa'] ?? '');
    $gpa        = floatval($_POST['gpa'] ?? 0);
    $trang_thai = $_POST['trang_thai'] ?? 'Đang học';
    
    // Validate
    if (empty($ma_sv))   $errors[] = 'Mã sinh viên không được để trống.';
    if (empty($ho_ten))  $errors[] = 'Họ tên không được để trống.';
    if ($gpa < 0 || $gpa > 4) $errors[] = 'GPA phải từ 0 đến 4.';
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ.';
    }
    
    if (empty($errors)) {
        // Kiểm tra mã SV trùng
        $check = $conn->prepare("SELECT id FROM sinh_vien WHERE ma_sv = ?");
        $check->bind_param("s", $ma_sv);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $errors[] = 'Mã sinh viên đã tồn tại.';
        }
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO sinh_vien 
            (ma_sv, ho_ten, ngay_sinh, gioi_tinh, dia_chi, email, so_dien_thoai, khoa_id, nien_khoa, gpa, trang_thai) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssisds", $ma_sv, $ho_ten, $ngay_sinh, $gioi_tinh, $dia_chi, $email, $sdt, $khoa_id, $nien_khoa, $gpa, $trang_thai);
        $stmt->execute();
        setFlash('success', "Đã thêm sinh viên $ho_ten thành công!");
        header("Location: sinh_vien.php");
        exit();
    }
}

$khoa_list = $conn->query("SELECT * FROM khoa ORDER BY ten_khoa");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Thêm Sinh viên</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="layout">
  <?php include 'includes/sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div>
        <div class="page-title">➕ Thêm Sinh viên Mới</div>
        <div class="page-subtitle">Nhập đầy đủ thông tin sinh viên</div>
      </div>
      <a href="sinh_vien.php" class="btn btn-secondary">← Quay lại</a>
    </div>
    <div class="content">
      <?php if ($errors): ?>
      <div class="alert alert-danger">
        ⚠️ <strong>Vui lòng kiểm tra lại:</strong>
        <ul style="margin-top:6px; padding-left:18px">
          <?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
      
      <form method="POST">
        <div style="display:grid; grid-template-columns:2fr 1fr; gap:24px">
          <!-- Thông tin cơ bản -->
          <div class="card">
            <div class="card-header"><h3>📋 Thông tin cơ bản</h3></div>
            <div class="card-body">
              <div class="form-grid">
                <div class="form-group">
                  <label>Mã sinh viên <span style="color:var(--red)">*</span></label>
                  <input type="text" name="ma_sv" placeholder="VD: SV006" value="<?= htmlspecialchars($_POST['ma_sv'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                  <label>Họ và tên <span style="color:var(--red)">*</span></label>
                  <input type="text" name="ho_ten" placeholder="Nguyễn Văn A" value="<?= htmlspecialchars($_POST['ho_ten'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                  <label>Ngày sinh</label>
                  <input type="date" name="ngay_sinh" value="<?= htmlspecialchars($_POST['ngay_sinh'] ?? '') ?>">
                </div>
                <div class="form-group">
                  <label>Giới tính</label>
                  <select name="gioi_tinh">
                    <?php foreach(['Nam','Nữ','Khác'] as $gt): ?>
                    <option value="<?= $gt ?>" <?= ($_POST['gioi_tinh']??'Nam')===$gt?'selected':'' ?>><?= $gt ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Email</label>
                  <input type="email" name="email" placeholder="example@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                  <label>Số điện thoại</label>
                  <input type="text" name="so_dien_thoai" placeholder="09xx xxx xxx" value="<?= htmlspecialchars($_POST['so_dien_thoai'] ?? '') ?>">
                </div>
                <div class="form-group full">
                  <label>Địa chỉ</label>
                  <textarea name="dia_chi" placeholder="Địa chỉ thường trú..."><?= htmlspecialchars($_POST['dia_chi'] ?? '') ?></textarea>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Thông tin học tập -->
          <div style="display:flex; flex-direction:column; gap:20px">
            <div class="card">
              <div class="card-header"><h3>🎓 Học tập</h3></div>
              <div class="card-body">
                <div class="form-group" style="margin-bottom:14px">
                  <label>Khoa</label>
                  <select name="khoa_id">
                    <option value="0">-- Chọn khoa --</option>
                    <?php while($k=$khoa_list->fetch_assoc()): ?>
                    <option value="<?= $k['id'] ?>" <?= ($_POST['khoa_id']??0)==$k['id']?'selected':'' ?>><?= htmlspecialchars($k['ten_khoa']) ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>
                <div class="form-group" style="margin-bottom:14px">
                  <label>Niên khóa</label>
                  <input type="text" name="nien_khoa" placeholder="VD: 2022-2026" value="<?= htmlspecialchars($_POST['nien_khoa'] ?? '') ?>">
                </div>
                <div class="form-group" style="margin-bottom:14px">
                  <label>GPA (0 - 4.0)</label>
                  <input type="number" name="gpa" step="0.01" min="0" max="4" placeholder="0.00" value="<?= htmlspecialchars($_POST['gpa'] ?? '0') ?>">
                </div>
                <div class="form-group">
                  <label>Trạng thái</label>
                  <select name="trang_thai">
                    <?php foreach(['Đang học','Bảo lưu','Tốt nghiệp','Đình chỉ'] as $tt): ?>
                    <option value="<?= $tt ?>" <?= ($_POST['trang_thai']??'Đang học')===$tt?'selected':'' ?>><?= $tt ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            
            <div class="card">
              <div class="card-body" style="text-align:center">
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:13px; font-size:15px">
                  ✅ Thêm sinh viên
                </button>
                <a href="sinh_vien.php" class="btn btn-secondary" style="width:100%; justify-content:center; margin-top:10px">
                  ✖ Hủy
                </a>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
<?php $conn->close(); ?>
