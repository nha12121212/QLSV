<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();
if (!isAdmin()) { setFlash('danger','Bạn không có quyền truy cập!'); header("Location: index.php"); exit(); }

$conn = getDB();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'them') {
        $ten = trim($_POST['ten_khoa'] ?? '');
        if (empty($ten)) { $errors[] = 'Tên khoa không được để trống.'; }
        else {
            $stmt = $conn->prepare("INSERT INTO khoa (ten_khoa) VALUES (?)");
            $stmt->bind_param("s", $ten);
            $stmt->execute();
            setFlash('success', "Đã thêm khoa '$ten'!");
            header("Location: khoa.php"); exit();
        }
    } elseif ($action === 'sua') {
        $id  = (int)$_POST['id'];
        $ten = trim($_POST['ten_khoa'] ?? '');
        if (empty($ten)) { $errors[] = 'Tên khoa không được để trống.'; }
        else {
            $stmt = $conn->prepare("UPDATE khoa SET ten_khoa=? WHERE id=?");
            $stmt->bind_param("si", $ten, $id);
            $stmt->execute();
            setFlash('success', "Đã cập nhật khoa!");
            header("Location: khoa.php"); exit();
        }
    } elseif ($action === 'xoa') {
        $id = (int)$_POST['id'];
        $sv_count = $conn->query("SELECT COUNT(*) as c FROM sinh_vien WHERE khoa_id=$id")->fetch_assoc()['c'];
        if ($sv_count > 0) {
            $errors[] = "Không thể xóa khoa này vì đang có $sv_count sinh viên!";
        } else {
            $conn->query("DELETE FROM khoa WHERE id=$id");
            setFlash('success', 'Đã xóa khoa!');
            header("Location: khoa.php"); exit();
        }
    }
}

$khoa_list = $conn->query("SELECT k.*, COUNT(s.id) as so_sv FROM khoa k LEFT JOIN sinh_vien s ON s.khoa_id=k.id GROUP BY k.id ORDER BY k.ten_khoa");
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản lý Khoa</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="layout">
  <?php include 'includes/sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div>
        <div class="page-title">🏫 Quản lý Khoa</div>
        <div class="page-subtitle">Danh sách các khoa trong trường</div>
      </div>
      <button onclick="openModal('addModal')" class="btn btn-primary">➕ Thêm khoa</button>
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
        <div class="card-header"><h3>📋 Danh sách khoa</h3></div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>#</th><th>Tên khoa</th><th>Số sinh viên</th><th>Thao tác</th></tr>
            </thead>
            <tbody>
              <?php $i=1; while ($k=$khoa_list->fetch_assoc()): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td style="font-weight:600"><?= htmlspecialchars($k['ten_khoa']) ?></td>
                <td>
                  <span class="badge badge-blue"><?= $k['so_sv'] ?> SV</span>
                </td>
                <td>
                  <div style="display:flex; gap:6px">
                    <button onclick="openEdit(<?= $k['id'] ?>, '<?= addslashes(htmlspecialchars($k['ten_khoa'])) ?>')" class="btn btn-warning btn-sm">✏️ Sửa</button>
                    <?php if ($k['so_sv'] == 0): ?>
                    <form method="POST" onsubmit="return confirm('Xóa khoa <?= addslashes(htmlspecialchars($k['ten_khoa'])) ?>?')">
                      <input type="hidden" name="action" value="xoa">
                      <input type="hidden" name="id" value="<?= $k['id'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm">🗑 Xóa</button>
                    </form>
                    <?php else: ?>
                    <button disabled class="btn btn-secondary btn-sm" title="Có sinh viên trong khoa">🔒 Xóa</button>
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

<!-- Thêm khoa -->
<div class="modal-overlay" id="addModal">
  <div class="modal" style="max-width:420px">
    <div class="modal-header"><h3>➕ Thêm Khoa mới</h3><button class="modal-close" onclick="closeModal('addModal')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="them">
      <div class="modal-body">
        <div class="form-group">
          <label>Tên khoa</label>
          <input type="text" name="ten_khoa" placeholder="VD: Công nghệ thông tin" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('addModal')" class="btn btn-secondary">Hủy</button>
        <button type="submit" class="btn btn-primary">✅ Thêm</button>
      </div>
    </form>
  </div>
</div>

<!-- Sửa khoa -->
<div class="modal-overlay" id="editModal">
  <div class="modal" style="max-width:420px">
    <div class="modal-header"><h3>✏️ Sửa Khoa</h3><button class="modal-close" onclick="closeModal('editModal')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="sua">
      <input type="hidden" name="id" id="editId">
      <div class="modal-body">
        <div class="form-group">
          <label>Tên khoa</label>
          <input type="text" name="ten_khoa" id="editTenKhoa" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('editModal')" class="btn btn-secondary">Hủy</button>
        <button type="submit" class="btn btn-primary">💾 Lưu</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function openEdit(id, ten) {
  document.getElementById('editId').value = id;
  document.getElementById('editTenKhoa').value = ten;
  openModal('editModal');
}
window.onclick = e => { if (e.target.classList.contains('modal-overlay')) e.target.classList.remove('open'); };
</script>
</body>
</html>
<?php $conn->close(); ?>
