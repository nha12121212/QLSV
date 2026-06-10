<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';
    
    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Vui lòng nhập đầy đủ thông tin.';
        } else {
            $conn = getDB();
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $conn->close();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['ho_ten']   = $user['ho_ten'];
                $_SESSION['role']     = $user['role'];
                setFlash('success', 'Chào mừng, ' . $user['ho_ten'] . '!');
                header("Location: index.php");
                exit();
            } else {
                $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
            }
        }
    } elseif ($action === 'register') {
        $username  = trim($_POST['reg_username'] ?? '');
        $ho_ten    = trim($_POST['reg_hoten'] ?? '');
        $password  = $_POST['reg_password'] ?? '';
        $password2 = $_POST['reg_password2'] ?? '';
        
        if (empty($username) || empty($ho_ten) || empty($password)) {
            $error = 'Vui lòng nhập đầy đủ thông tin đăng ký.';
        } elseif ($password !== $password2) {
            $error = 'Mật khẩu xác nhận không khớp.';
        } elseif (strlen($password) < 6) {
            $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
        } else {
            $conn = getDB();
            $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check->bind_param("s", $username);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $error = 'Tên đăng nhập đã tồn tại.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, ho_ten, role) VALUES (?, ?, ?, 'user')");
                $stmt->bind_param("sss", $username, $hashed, $ho_ten);
                $stmt->execute();
                $conn->close();
                $error = ''; 
                $success = 'Đăng ký thành công! Bạn có thể đăng nhập.';
            }
            
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng nhập – Quản lý Sinh viên</title>
<link rel="stylesheet" href="css/style.css">
<style>
.tabs-auth { display: flex; gap: 0; border-radius: 10px 10px 0 0; overflow: hidden; margin-bottom: 28px; border: 1.5px solid var(--border); }
.tab-auth-btn { flex: 1; padding: 12px; border: none; font-size: 14px; font-weight: 600; cursor: pointer; background: #f8fafc; color: var(--muted); transition: all .15s; }
.tab-auth-btn.active { background: var(--blue); color: #fff; }
.reg-form { display: none; }
.reg-form.show { display: block; }
.log-form.hide { display: none; }
</style>
</head>
<body>
<div class="login-page">
  <div class="login-box">
    <div class="login-logo">
      <div class="icon-wrap">🎓</div>
      <h2>Quản lý Sinh viên</h2>
      <p>Hệ thống quản lý toàn diện</p>
    </div>
    
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <div class="tabs-auth">
      <button class="tab-auth-btn active" onclick="switchTab('login', this)">🔑 Đăng nhập</button>
      <button class="tab-auth-btn" onclick="switchTab('register', this)">📝 Đăng ký</button>
    </div>
    
    <!-- ĐĂNG NHẬP -->
    <div class="log-form" id="loginForm">
      <form method="POST">
        <input type="hidden" name="action" value="login">
        <div class="form-group" style="margin-bottom:16px">
          <label>Tên đăng nhập</label>
          <input type="text" name="username" placeholder="Nhập tên đăng nhập" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        </div>
        <div class="form-group" style="margin-bottom:22px">
          <label>Mật khẩu</label>
          <input type="password" name="password" placeholder="Nhập mật khẩu" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">🔑 Đăng nhập</button>
      </form>
      <p style="text-align:center; margin-top:14px; font-size:13px; color:var(--muted);">
        Tài khoản mặc định: <strong>admin</strong> / <strong>admin123</strong>
      </p>
    </div>
    
    <!-- ĐĂNG KÝ -->
    <div class="reg-form" id="registerForm">
      <form method="POST">
        <input type="hidden" name="action" value="register">
        <div class="form-group" style="margin-bottom:14px">
          <label>Họ và tên</label>
          <input type="text" name="reg_hoten" placeholder="Nhập họ và tên" required>
        </div>
        <div class="form-group" style="margin-bottom:14px">
          <label>Tên đăng nhập</label>
          <input type="text" name="reg_username" placeholder="Nhập tên đăng nhập" required>
        </div>
        <div class="form-group" style="margin-bottom:14px">
          <label>Mật khẩu</label>
          <input type="password" name="reg_password" placeholder="Tối thiểu 6 ký tự" required>
        </div>
        <div class="form-group" style="margin-bottom:22px">
          <label>Xác nhận mật khẩu</label>
          <input type="password" name="reg_password2" placeholder="Nhập lại mật khẩu" required>
        </div>
        <button type="submit" class="btn btn-success" style="width:100%; justify-content:center; padding:12px;">📝 Tạo tài khoản</button>
      </form>
    </div>
  </div>
</div>
<script>
function switchTab(tab, btn) {
  document.querySelectorAll('.tab-auth-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  if (tab === 'login') {
    document.getElementById('loginForm').classList.remove('hide');
    document.getElementById('registerForm').classList.remove('show');
    document.getElementById('registerForm').style.display = 'none';
    document.getElementById('loginForm').style.display = 'block';
  } else {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('registerForm').style.display = 'block';
  }
}
<?php if (!empty($success)): ?>
// Đăng ký thành công -> hiển thị form login
document.getElementById('loginForm').style.display = 'block';
document.getElementById('registerForm').style.display = 'none';
document.querySelectorAll('.tab-auth-btn')[0].classList.add('active');
document.querySelectorAll('.tab-auth-btn')[1].classList.remove('active');
<?php elseif (isset($_POST['action']) && $_POST['action'] === 'register'): ?>
document.getElementById('loginForm').style.display = 'none';
document.getElementById('registerForm').style.display = 'block';
document.querySelectorAll('.tab-auth-btn')[0].classList.remove('active');
document.querySelectorAll('.tab-auth-btn')[1].classList.add('active');
<?php endif; ?>
</script>
</body>
</html>
