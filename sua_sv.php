<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$conn = getDB();
$id = (int)($_GET['id'] ?? 0);
$errors = [];

// Lấy thông tin sinh viên
$sv = $conn->query("SELECT * FROM sinh_vien WHERE id=$id")->fetch_assoc();
if (!$sv) {
    setFlash('danger', 'Không tìm thấy sinh viên!');
    header("Location: sinh_vien.php");
    exit();
}

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
    
    if (empty($ma_sv))   $errors[] = 'Mã sinh viên không được để trống.';
    if (empty($ho_ten))  $errors[] = 'Họ tên không được để trống.';
    if ($gpa < 0 || $gpa > 4) $errors[] = 'GPA phải từ 0 đến 4.';
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
    
    if (empty($errors)) {
        $check = $conn->prepare("SELECT id FROM sinh_vien WHERE ma_sv = ? AND id != ?");
        $check->bind_param("si", $ma_sv, $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) $errors[] = 'Mã sinh viên đã tồn tại.';
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE sinh_vien SET 
            ma_sv=?, ho_ten=?, ngay_sinh=?, gioi_tinh=?, dia_chi=?, email=?, so_dien_thoai=?, 
            khoa_id=?, nien_khoa=?, gpa=?, trang_thai=?
            WHERE id=?");
        $stmt->bind_param("sssssssisdsi", $ma_sv, $ho_ten, $ngay_sinh, $gioi_tinh, $dia_chi, $email, $sdt, $khoa_id, $nien_khoa, $gpa, $trang_thai, $id);
        $stmt->execute();
        setFlash('success', "Đã cập nhật sinh viên $ho_ten!");
        header("Location: sinh_vien.php");
        exit();
    }
    
    // Cập nhật giá trị nếu có lỗi
    $sv = array_merge($sv, $_POST);
}

$khoa_list = $conn->query("SELECT * FROM khoa ORDER BY ten_khoa");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sửa Sinh viên – <?= htmlspecialchars($sv['ho_ten']) ?></title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="layout">
  <?php include 'includes/sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div>
        <div class="page-title">✏️ Sửa Sinh viên</div>
        <div class="page-subtitle"><?= htmlspecialchars($sv['ho_ten']) ?> – <?= htmlspecialchars($sv['ma_sv']) ?></div>
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
          <div class="card">
            <div class="card-header">
              <h3>📋 Thông tin cơ bản</h3>
              <span class="badge badge-gray">ID: <?= $id ?></span>
            </div>
            <div class="card-body">
              <div class="form-grid">
                <div class="form-group">
                  <label>Mã sinh viên <span style="color:var(--red)">*</span></label>
                  <input type="text" name="ma_sv" value="<?= htmlspecialchars($sv['ma_sv']) ?>" required>
                </div>
                <div class="form-group">
                  <label>Họ và tên <span style="color:var(--red)">*</span></label>
                  <input type="text" name="ho_ten" value="<?= htmlspecialchars($sv['ho_ten']) ?>" required>
                </div>
                <div class="form-group">
                  <label>Ngày sinh</label>
                  <input type="date" name="ngay_sinh" value="<?= htmlspecialchars($sv['ngay_sinh'] ?? '') ?>">
                </div>
                <div class="form-group">
                  <label>Giới tính</label>
                  <select name="gioi_tinh">
                    <?php foreach(['Nam','Nữ','Khác'] as $gt): ?>
                    <option value="<?= $gt ?>" <?= $sv['gioi_tinh']===$gt?'selected':'' ?>><?= $gt ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Email</label>
                  <input type="email" name="email" value="<?= htmlspecialchars($sv['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                  <label>Số điện thoại</label>
                  <input type="text" name="so_dien_thoai" value="<?= htmlspecialchars($sv['so_dien_thoai'] ?? '') ?>">
                </div>
                <div class="form-group full">
                  <label>Địa chỉ</label>
                  <textarea name="dia_chi"><?= htmlspecialchars($sv['dia_chi'] ?? '') ?></textarea>
                </div>
              </div>
            </div>
          </div>
          
          <div style="display:flex; flex-direction:column; gap:20px">
            <div class="card">
              <div class="card-header"><h3>🎓 Học tập</h3></div>
              <div class="card-body">
                <div class="form-group" style="margin-bottom:14px">
                  <label>Khoa</label>
                  <select name="khoa_id">
                    <option value="0">-- Chọn khoa --</option>
                    <?php while($k=$khoa_list->fetch_assoc()): ?>
                    <option value="<?= $k['id'] ?>" <?= $sv['khoa_id']==$k['id']?'selected':'' ?>><?= htmlspecialchars($k['ten_khoa']) ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>
                <div class="form-group" style="margin-bottom:14px">
                  <label>Niên khóa</label>
                  <input type="text" name="nien_khoa" value="<?= htmlspecialchars($sv['nien_khoa'] ?? '') ?>">
                </div>
                <div class="form-group" style="margin-bottom:14px">
                  <label>GPA</label>
                  <input type="number" name="gpa" step="0.01" min="0" max="4" value="<?= $sv['gpa'] ?>">
                </div>
                <div class="form-group">
                  <label>Trạng thái</label>
                  <select name="trang_thai">
                    <?php foreach(['Đang học','Bảo lưu','Tốt nghiệp','Đình chỉ'] as $tt): ?>
                    <option value="<?= $tt ?>" <?= $sv['trang_thai']===$tt?'selected':'' ?>><?= $tt ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            
            <div class="card">
              <div class="card-body">
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:13px">
                  💾 Lưu thay đổi
                </button>
                <a href="sinh_vien.php" class="btn btn-secondary" style="width:100%; justify-content:center; margin-top:10px">
                  ✖ Hủy
                </a>
              </div>
            </div>
            
            <!-- Nguy hiểm -->
            <div class="card" style="border:1.5px solid #fecaca">
              <div class="card-header" style="background:#fef2f2">
                <h3 style="color:var(--red)">⚠️ Vùng nguy hiểm</h3>
              </div>
              <div class="card-body">
                <p style="font-size:13px; color:var(--muted); margin-bottom:12px">Xóa sinh viên này sẽ không thể khôi phục.</p>
                <form method="POST" action="sinh_vien.php" onsubmit="return confirm('Xác nhận xóa sinh viên này?')">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $id ?>">
                  <button type="submit" class="btn btn-danger" style="width:100%; justify-content:center">
                    🗑 Xóa sinh viên này
                  </button>
                </form>
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
