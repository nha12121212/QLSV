<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();
if (!isAdmin()) { setFlash('danger','Không có quyền!'); header("Location: index.php"); exit(); }

$conn = getDB();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'them') {
        $username = trim($_POST['username'] ?? '');
        $ho_ten   = trim($_POST['ho_ten'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'user';
        
        if (empty($username) || empty($ho_ten) || empty($password)) $errors[] = 'Vui lòng nhập đầy đủ thông tin.';
        elseif (strlen($password) < 6) $errors[] = 'Mật khẩu ít nhất 6 ký tự.';
        else {
            $check = $conn->prepare("SELECT id FROM users WHERE username=?");
            $check->bind_param("s", $username); $check->execute();
            if ($check->get_result()->num_rows > 0) { $errors[] = 'Tên đăng nhập đã tồn tại.'; }
            else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, ho_ten, role) VALUES (?,?,?,?)");
                $stmt->bind_param("ssss", $username, $hash, $ho_ten, $role);
                $stmt->execute();
                setFlash('success', "Đã tạo tài khoản $username!"); header("Location: tai_khoan.php"); exit();
            }
        }
    } elseif ($action === 'doi_mat_khau') {
        $id = (int)$_POST['id'];
        $pass = $_POST['new_password'] ?? '';
        if (strlen($pass) < 6) { $errors[] = 'Mật khẩu ít nhất 6 ký tự.'; }
        else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->bind_param("si", $hash, $id); $stmt->execute();
            setFlash('success', 'Đã đổi mật khẩu!'); header("Location: tai_khoan.php"); exit();
        }
    } elseif ($action === 'xoa') {
        $id = (int)$_POST['id'];
        $me = getCurrentUser();
        if ($id == $me['id']) { $errors[] = 'Không thể xóa tài khoản đang đăng nhập!'; }
        else {
            $conn->query("DELETE FROM users WHERE id=$id");
            setFlash('success', 'Đã xóa tài khoản!'); header("Location: tai_khoan.php"); exit();
        }
    }
}

$users = $conn->query("SELECT * FROM users ORDER BY role DESC, ho_ten");
$flash = getFlash();
$me = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản lý Tài khoản</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="layout">
  <?php include 'includes/sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div>
        <div class="page-title">👥 Tài khoản</div>
        <div class="page-subtitle">Quản lý tài khoản người dùng</div>
      </div>
      <button onclick="openModal('addModal')" class="btn btn-primary">➕ Thêm tài khoản</button>
    </div>
    <div class="content">
      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type']==='success'?'success':'danger' ?>">
        <?= $flash['type']==='success'?'✅':'⚠️' ?> <?= htmlspecialchars($flash['msg']) ?>
      </div>
      <?php endif; ?>
      <?php if ($errors): ?>
      <div class="alert alert-danger">⚠️ <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
      <?php endif; ?>
      
      <div class="card">
        <div class="table-wrap">
          <table>
            <thead><tr><th>#</th><th>Tài khoản</th><th>Họ tên</th><th>Vai trò</th><th>Ngày tạo</th><th>Thao tác</th></tr></thead>
            <tbody>
              <?php $i=1; while ($u=$users->fetch_assoc()): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><code style="background:#f1f5f9;padding:2px 8px;border-radius:5px"><?= htmlspecialchars($u['username']) ?></code>
                  <?php if ($u['id']==$me['id']): ?><span class="badge badge-blue" style="margin-left:6px">Bạn</span><?php endif; ?>
                </td>
                <td style="font-weight:600"><?= htmlspecialchars($u['ho_ten']) ?></td>
                <td>
                  <?php if ($u['role']==='admin'): ?>
                  <span class="badge badge-orange">⭐ Admin</span>
                  <?php else: ?>
                  <span class="badge badge-gray">👤 User</span>
                  <?php endif; ?>
                </td>
                <td style="font-size:13px; color:var(--muted)"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                <td>
                  <div style="display:flex;gap:6px">
                    <button onclick="openPassModal(<?= $u['id'] ?>, '<?= addslashes(htmlspecialchars($u['username'])) ?>')" class="btn btn-warning btn-sm">🔑 Đổi mật khẩu</button>
                    <?php if ($u['id'] != $me['id']): ?>
                    <form method="POST" onsubmit="return confirm('Xóa tài khoản <?= addslashes(htmlspecialchars($u['username'])) ?>?')">
                      <input type="hidden" name="action" value="xoa">
                      <input type="hidden" name="id" value="<?= $u['id'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm">🗑 Xóa</button>
                    </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Thêm tài khoản -->
<div class="modal-overlay" id="addModal">
  <div class="modal" style="max-width:440px">
    <div class="modal-header"><h3>➕ Tạo tài khoản mới</h3><button class="modal-close" onclick="closeModal('addModal')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="them">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group"><label>Họ tên</label><input type="text" name="ho_ten" required></div>
          <div class="form-group"><label>Tên đăng nhập</label><input type="text" name="username" required></div>
          <div class="form-group"><label>Mật khẩu</label><input type="password" name="password" required></div>
          <div class="form-group"><label>Vai trò</label>
            <select name="role">
              <option value="user">👤 User</option>
              <option value="admin">⭐ Admin</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('addModal')" class="btn btn-secondary">Hủy</button>
        <button type="submit" class="btn btn-primary">✅ Tạo tài khoản</button>
      </div>
    </form>
  </div>
</div>

<!-- Đổi mật khẩu -->
<div class="modal-overlay" id="passModal">
  <div class="modal" style="max-width:380px">
    <div class="modal-header"><h3>🔑 Đổi mật khẩu</h3><button class="modal-close" onclick="closeModal('passModal')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="doi_mat_khau">
      <input type="hidden" name="id" id="passId">
      <div class="modal-body">
        <p style="margin-bottom:14px; color:var(--muted)">Tài khoản: <strong id="passUsername"></strong></p>
        <div class="form-group"><label>Mật khẩu mới</label><input type="password" name="new_password" placeholder="Ít nhất 6 ký tự" required></div>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('passModal')" class="btn btn-secondary">Hủy</button>
        <button type="submit" class="btn btn-primary">💾 Đổi mật khẩu</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function openPassModal(id, name) {
  document.getElementById('passId').value = id;
  document.getElementById('passUsername').textContent = name;
  openModal('passModal');
}
window.onclick = e => { if (e.target.classList.contains('modal-overlay')) e.target.classList.remove('open'); };
</script>
</body>
</html>
<?php $conn->close(); ?>
