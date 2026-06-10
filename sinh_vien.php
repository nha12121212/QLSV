<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$conn = getDB();

// Xử lý xóa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)$_POST['id'];
    $conn->query("DELETE FROM sinh_vien WHERE id=$id");
    setFlash('success', 'Đã xóa sinh viên thành công!');
    header("Location: sinh_vien.php");
    exit();
}

// Xử lý xóa nhiều
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_multi') {
    $ids = array_map('intval', explode(',', $_POST['ids'] ?? ''));
    if ($ids) {
        $in = implode(',', $ids);
        $conn->query("DELETE FROM sinh_vien WHERE id IN ($in)");
        setFlash('success', 'Đã xóa ' . count($ids) . ' sinh viên!');
    }
    header("Location: sinh_vien.php");
    exit();
}

// Xử lý cập nhật nhanh trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    $id = (int)$_POST['id'];
    $tt = $conn->real_escape_string($_POST['trang_thai']);
    $conn->query("UPDATE sinh_vien SET trang_thai='$tt' WHERE id=$id");
    setFlash('success', 'Đã cập nhật trạng thái!');
    header("Location: sinh_vien.php");
    exit();
}

// Lọc & tìm kiếm
$search    = trim($_GET['search'] ?? '');
$filter_khoa = (int)($_GET['khoa'] ?? 0);
$filter_tt   = trim($_GET['trang_thai'] ?? '');
$filter_gioitinh = trim($_GET['gioi_tinh'] ?? '');
$sort  = $_GET['sort'] ?? 'id';
$order = strtoupper($_GET['order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
$page  = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;

$where = ['1=1'];
if ($search) {
    $s = $conn->real_escape_string($search);
    $where[] = "(s.ho_ten LIKE '%$s%' OR s.ma_sv LIKE '%$s%' OR s.email LIKE '%$s%')";
}
if ($filter_khoa) $where[] = "s.khoa_id=$filter_khoa";
if ($filter_tt)   $where[] = "s.trang_thai='". $conn->real_escape_string($filter_tt) ."'";
if ($filter_gioitinh) $where[] = "s.gioi_tinh='". $conn->real_escape_string($filter_gioitinh) ."'";

$where_sql = implode(' AND ', $where);
$allowed_sort = ['id','ma_sv','ho_ten','gpa','trang_thai','created_at'];
$sort_col = in_array($sort, $allowed_sort) ? $sort : 'id';

$count_q = $conn->query("SELECT COUNT(*) as c FROM sinh_vien s LEFT JOIN khoa k ON s.khoa_id=k.id WHERE $where_sql");
$total_rows = $count_q->fetch_assoc()['c'];
$total_pages = ceil($total_rows / $per_page);
$offset = ($page - 1) * $per_page;

$query = "SELECT s.*, k.ten_khoa FROM sinh_vien s 
          LEFT JOIN khoa k ON s.khoa_id=k.id 
          WHERE $where_sql 
          ORDER BY s.$sort_col $order 
          LIMIT $per_page OFFSET $offset";
$result = $conn->query($query);

$khoa_list = $conn->query("SELECT * FROM khoa ORDER BY ten_khoa");
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Danh sách Sinh viên</title>
<link rel="stylesheet" href="css/style.css">
<style>
.filter-bar { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
.filter-bar select { width:auto; min-width:160px; }
.pagination { display:flex; gap:6px; align-items:center; margin-top:20px; justify-content:center; }
.pg-btn {
  padding:7px 13px; border-radius:7px; border:1.5px solid var(--border);
  background:#fff; font-size:13px; cursor:pointer; text-decoration:none; color:var(--text);
  transition:all .15s;
}
.pg-btn:hover, .pg-btn.active { background:var(--blue); color:#fff; border-color:var(--blue); }
.pg-btn.disabled { opacity:.4; pointer-events:none; }
.sort-link { color:inherit; text-decoration:none; display:flex; align-items:center; gap:4px; }
.sort-link:hover { color:var(--blue); }
.check-all, .row-check { width:16px; height:16px; cursor:pointer; }
.bulk-bar {
  display:none; background:linear-gradient(90deg,var(--blue),var(--teal));
  padding:12px 24px; border-radius:9px; align-items:center;
  justify-content:space-between; color:#fff; margin-bottom:14px;
}
.bulk-bar.show { display:flex; }
</style>
</head>
<body>
<div class="layout">
  <?php include 'includes/sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div>
        <div class="page-title">👨‍🎓 Danh sách Sinh viên</div>
        <div class="page-subtitle">Quản lý toàn bộ hồ sơ sinh viên</div>
      </div>
      <a href="them_sv.php" class="btn btn-primary">➕ Thêm sinh viên</a>
    </div>
    
    <div class="content">
      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type']==='success'?'success':'danger' ?>">
        <?= $flash['type']==='success'?'✅':'⚠️' ?> <?= htmlspecialchars($flash['msg']) ?>
      </div>
      <?php endif; ?>
      
      <!-- Thanh lọc -->
      <div class="card" style="margin-bottom:20px">
        <div class="card-body" style="padding:16px 20px">
          <form method="GET" class="filter-bar">
            <div class="search-bar" style="flex:1; min-width:200px">
              <span class="icon">🔍</span>
              <input type="text" name="search" placeholder="Tìm theo tên, mã SV, email..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <select name="khoa">
              <option value="">-- Tất cả khoa --</option>
              <?php 
              $khoa_list->data_seek(0);
              while($k=$khoa_list->fetch_assoc()): ?>
              <option value="<?= $k['id'] ?>" <?= $filter_khoa==$k['id']?'selected':'' ?>><?= htmlspecialchars($k['ten_khoa']) ?></option>
              <?php endwhile; ?>
            </select>
            <select name="trang_thai">
              <option value="">-- Trạng thái --</option>
              <?php foreach(['Đang học','Bảo lưu','Tốt nghiệp','Đình chỉ'] as $tt): ?>
              <option value="<?= $tt ?>" <?= $filter_tt===$tt?'selected':'' ?>><?= $tt ?></option>
              <?php endforeach; ?>
            </select>
            <select name="gioi_tinh">
              <option value="">-- Giới tính --</option>
              <option value="Nam" <?= $filter_gioitinh==='Nam'?'selected':'' ?>>Nam</option>
              <option value="Nữ" <?= $filter_gioitinh==='Nữ'?'selected':'' ?>>Nữ</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">🔍 Lọc</button>
            <a href="sinh_vien.php" class="btn btn-secondary btn-sm">✖ Xóa lọc</a>
          </form>
        </div>
      </div>
      
      <!-- Bulk action bar -->
      <div class="bulk-bar" id="bulkBar">
        <span id="bulkCount">0 sinh viên được chọn</span>
        <form method="POST" id="bulkForm">
          <input type="hidden" name="action" value="delete_multi">
          <input type="hidden" name="ids" id="bulkIds">
          <button type="button" onclick="confirmBulkDelete()" class="btn btn-danger btn-sm">🗑 Xóa đã chọn</button>
        </form>
      </div>
      
      <div class="card">
        <div class="card-header">
          <h3>📋 <?= number_format($total_rows) ?> sinh viên</h3>
          <div style="display:flex; gap:8px; align-items:center; font-size:13px; color:var(--muted)">
            Sắp xếp theo:
            <?php
            $sorts = ['ho_ten'=>'Tên','gpa'=>'GPA','created_at'=>'Ngày tạo'];
            foreach($sorts as $k=>$v): ?>
            <a href="?search=<?= urlencode($search) ?>&khoa=<?= $filter_khoa ?>&trang_thai=<?= urlencode($filter_tt) ?>&sort=<?= $k ?>&order=<?= $sort===$k && $order==='ASC'?'DESC':'ASC' ?>"
               style="color:<?= $sort===$k?'var(--blue)':'inherit' ?>; text-decoration:none; font-weight:<?= $sort===$k?600:400 ?>">
              <?= $v ?><?= $sort===$k?($order==='ASC'?' ↑':' ↓'):'' ?>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th><input type="checkbox" class="check-all" id="checkAll" onchange="toggleAll(this)"></th>
                <th>Sinh viên</th>
                <th>Mã SV</th>
                <th>Giới tính</th>
                <th>Ngày sinh</th>
                <th>Khoa</th>
                <th>GPA</th>
                <th>Trạng thái</th>
                <th>Liên hệ</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $badge_map = ['Đang học'=>'badge-green','Tốt nghiệp'=>'badge-blue','Bảo lưu'=>'badge-orange','Đình chỉ'=>'badge-red'];
              while ($sv = $result->fetch_assoc()):
                $init = mb_strtoupper(mb_substr($sv['ho_ten'], 0, 1));
                $gpa_class = $sv['gpa'] >= 3.5 ? 'gpa-high' : ($sv['gpa'] >= 2.5 ? 'gpa-mid' : 'gpa-low');
              ?>
              <tr>
                <td><input type="checkbox" class="row-check" value="<?= $sv['id'] ?>" onchange="updateBulk()"></td>
                <td>
                  <div class="sv-name-wrap">
                    <div class="sv-avatar"><?= $init ?></div>
                    <div>
                      <div class="sv-name"><?= htmlspecialchars($sv['ho_ten']) ?></div>
                      <div class="sv-masv"><?= htmlspecialchars($sv['nien_khoa'] ?? '') ?></div>
                    </div>
                  </div>
                </td>
                <td><code style="background:#f1f5f9; padding:2px 7px; border-radius:5px"><?= htmlspecialchars($sv['ma_sv']) ?></code></td>
                <td><?= $sv['gioi_tinh'] === 'Nam' ? '👦 Nam' : '👧 Nữ' ?></td>
                <td style="white-space:nowrap"><?= $sv['ngay_sinh'] ? date('d/m/Y', strtotime($sv['ngay_sinh'])) : '—' ?></td>
                <td style="font-size:13px"><?= htmlspecialchars($sv['ten_khoa'] ?? 'N/A') ?></td>
                <td class="<?= $gpa_class ?>"><?= number_format($sv['gpa'], 2) ?></td>
                <td>
                  <span class="badge <?= $badge_map[$sv['trang_thai']] ?? 'badge-gray' ?>"><?= $sv['trang_thai'] ?></span>
                </td>
                <td style="font-size:12px">
                  <?php if ($sv['email']): ?>
                  <a href="mailto:<?= htmlspecialchars($sv['email']) ?>" style="color:var(--blue)">📧</a>
                  <?php endif; ?>
                  <?php if ($sv['so_dien_thoai']): ?>
                  <a href="tel:<?= htmlspecialchars($sv['so_dien_thoai']) ?>" style="color:var(--green); margin-left:4px">📞</a>
                  <?php endif; ?>
                </td>
                <td>
                  <div style="display:flex; gap:5px">
                    <a href="sua_sv.php?id=<?= $sv['id'] ?>" class="btn btn-warning btn-sm btn-icon" title="Sửa">✏️</a>
                    <button onclick="confirmDelete(<?= $sv['id'] ?>, '<?= addslashes(htmlspecialchars($sv['ho_ten'])) ?>')" 
                            class="btn btn-danger btn-sm btn-icon" title="Xóa">🗑</button>
                    <button onclick="openDetail(<?= htmlspecialchars(json_encode($sv)) ?>)" 
                            class="btn btn-secondary btn-sm btn-icon" title="Chi tiết">👁</button>
                  </div>
                </td>
              </tr>
              <?php endwhile; ?>
              <?php if ($total_rows == 0): ?>
              <tr><td colspan="10" style="text-align:center; padding:40px; color:var(--muted)">
                😕 Không tìm thấy sinh viên nào
              </td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div style="padding:16px 24px; border-top:1px solid var(--border)">
          <div class="pagination">
            <a href="?<?= http_build_query(array_merge($_GET, ['page'=>1])) ?>" class="pg-btn <?= $page==1?'disabled':'' ?>">«</a>
            <a href="?<?= http_build_query(array_merge($_GET, ['page'=>max(1,$page-1)])) ?>" class="pg-btn <?= $page==1?'disabled':'' ?>">‹</a>
            <?php for($i=max(1,$page-2); $i<=min($total_pages,$page+2); $i++): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$i])) ?>" class="pg-btn <?= $i==$page?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page'=>min($total_pages,$page+1)])) ?>" class="pg-btn <?= $page==$total_pages?'disabled':'' ?>">›</a>
            <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$total_pages])) ?>" class="pg-btn <?= $page==$total_pages?'disabled':'' ?>">»</a>
            <span style="font-size:13px; color:var(--muted); margin-left:8px">Trang <?= $page ?>/<?= $total_pages ?></span>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal" style="max-width:400px">
    <div class="modal-header"><h3>⚠️ Xác nhận xóa</h3><button class="modal-close" onclick="closeModal('deleteModal')">✕</button></div>
    <div class="modal-body">
      <p>Bạn có chắc muốn xóa sinh viên <strong id="deleteName"></strong>?</p>
      <p style="color:var(--red); font-size:13px; margin-top:8px">⚠️ Hành động này không thể hoàn tác!</p>
    </div>
    <div class="modal-footer">
      <button onclick="closeModal('deleteModal')" class="btn btn-secondary">Hủy</button>
      <form method="POST" style="display:inline">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteId">
        <button type="submit" class="btn btn-danger">🗑 Xóa</button>
      </form>
    </div>
  </div>
</div>

<!-- Detail Modal -->
<div class="modal-overlay" id="detailModal">
  <div class="modal">
    <div class="modal-header"><h3>👁 Chi tiết Sinh viên</h3><button class="modal-close" onclick="closeModal('detailModal')">✕</button></div>
    <div class="modal-body" id="detailBody"></div>
    <div class="modal-footer">
      <button onclick="closeModal('detailModal')" class="btn btn-secondary">Đóng</button>
      <a id="editLink" href="#" class="btn btn-primary">✏️ Chỉnh sửa</a>
    </div>
  </div>
</div>

<script>
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function openModal(id)  { document.getElementById(id).classList.add('open'); }

function confirmDelete(id, name) {
  document.getElementById('deleteId').value = id;
  document.getElementById('deleteName').textContent = name;
  openModal('deleteModal');
}

function openDetail(sv) {
  document.getElementById('editLink').href = 'sua_sv.php?id=' + sv.id;
  const badge = {
    'Đang học': 'badge-green', 'Tốt nghiệp': 'badge-blue',
    'Bảo lưu': 'badge-orange', 'Đình chỉ': 'badge-red'
  };
  const gpa = parseFloat(sv.gpa);
  const gpaClass = gpa >= 3.5 ? 'gpa-high' : gpa >= 2.5 ? 'gpa-mid' : 'gpa-low';
  document.getElementById('detailBody').innerHTML = `
    <div style="display:flex; align-items:center; gap:16px; margin-bottom:24px; padding:16px; background:#f8fafc; border-radius:10px">
      <div class="sv-avatar" style="width:56px;height:56px;font-size:22px">${sv.ho_ten.charAt(0).toUpperCase()}</div>
      <div>
        <div style="font-size:20px; font-weight:700">${sv.ho_ten}</div>
        <div style="color:var(--muted)">${sv.ma_sv}</div>
      </div>
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px">
      <div><div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;margin-bottom:3px">Email</div><div>${sv.email || '—'}</div></div>
      <div><div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;margin-bottom:3px">Điện thoại</div><div>${sv.so_dien_thoai || '—'}</div></div>
      <div><div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;margin-bottom:3px">Ngày sinh</div><div>${sv.ngay_sinh || '—'}</div></div>
      <div><div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;margin-bottom:3px">Giới tính</div><div>${sv.gioi_tinh}</div></div>
      <div><div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;margin-bottom:3px">Niên khóa</div><div>${sv.nien_khoa || '—'}</div></div>
      <div><div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;margin-bottom:3px">GPA</div><div class="${gpaClass}" style="font-size:18px">${parseFloat(sv.gpa).toFixed(2)}</div></div>
      <div><div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;margin-bottom:3px">Trạng thái</div><span class="badge ${badge[sv.trang_thai] || 'badge-gray'}">${sv.trang_thai}</span></div>
      <div><div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;margin-bottom:3px">Địa chỉ</div><div>${sv.dia_chi || '—'}</div></div>
    </div>`;
  openModal('detailModal');
}

function toggleAll(cb) {
  document.querySelectorAll('.row-check').forEach(c => c.checked = cb.checked);
  updateBulk();
}
function updateBulk() {
  const checked = document.querySelectorAll('.row-check:checked');
  const bar = document.getElementById('bulkBar');
  if (checked.length > 0) {
    bar.classList.add('show');
    document.getElementById('bulkCount').textContent = checked.length + ' sinh viên được chọn';
    document.getElementById('bulkIds').value = [...checked].map(c=>c.value).join(',');
  } else {
    bar.classList.remove('show');
  }
}
function confirmBulkDelete() {
  if (confirm('Bạn có chắc muốn xóa các sinh viên đã chọn?')) {
    document.getElementById('bulkForm').submit();
  }
}
window.onclick = e => {
  if (e.target.classList.contains('modal-overlay')) e.target.classList.remove('open');
};
</script>
</body>
</html>
<?php $conn->close(); ?>
