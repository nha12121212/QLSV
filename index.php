<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$conn = getDB();

// Thống kê
$total   = $conn->query("SELECT COUNT(*) as c FROM sinh_vien")->fetch_assoc()['c'];
$dang_hoc = $conn->query("SELECT COUNT(*) as c FROM sinh_vien WHERE trang_thai='Đang học'")->fetch_assoc()['c'];
$tot_nghiep = $conn->query("SELECT COUNT(*) as c FROM sinh_vien WHERE trang_thai='Tốt nghiệp'")->fetch_assoc()['c'];
$avg_gpa = $conn->query("SELECT ROUND(AVG(gpa),2) as a FROM sinh_vien")->fetch_assoc()['a'];

// Sinh viên mới nhất
$recent = $conn->query("SELECT s.*, k.ten_khoa FROM sinh_vien s LEFT JOIN khoa k ON s.khoa_id=k.id ORDER BY s.created_at DESC LIMIT 5");

// Thống kê theo khoa
$by_khoa = $conn->query("SELECT k.ten_khoa, COUNT(s.id) as so_luong FROM khoa k LEFT JOIN sinh_vien s ON s.khoa_id=k.id GROUP BY k.id ORDER BY so_luong DESC");

$flash = getFlash();
$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard – Quản lý Sinh viên</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="layout">
  <?php include 'includes/sidebar.php'; ?>
  
  <div class="main">
    <div class="topbar">
      <div>
        <div class="page-title">📊 Dashboard</div>
        <div class="page-subtitle">Tổng quan hệ thống quản lý sinh viên</div>
      </div>
      <a href="them_sv.php" class="btn btn-primary">➕ Thêm sinh viên</a>
    </div>
    
    <div class="content">
      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>">
        <?= $flash['type'] === 'success' ? '✅' : '⚠️' ?> <?= htmlspecialchars($flash['msg']) ?>
      </div>
      <?php endif; ?>
      
      <!-- Stats -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue">🎓</div>
          <div>
            <div class="stat-value"><?= $total ?></div>
            <div class="stat-label">Tổng sinh viên</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green">✅</div>
          <div>
            <div class="stat-value"><?= $dang_hoc ?></div>
            <div class="stat-label">Đang học</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon orange">🏆</div>
          <div>
            <div class="stat-value"><?= $tot_nghiep ?></div>
            <div class="stat-label">Tốt nghiệp</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon red">⭐</div>
          <div>
            <div class="stat-value"><?= $avg_gpa ?: '0.00' ?></div>
            <div class="stat-label">GPA trung bình</div>
          </div>
        </div>
      </div>
      
      <div style="display:grid; grid-template-columns: 2fr 1fr; gap:24px">
        <!-- Sinh viên mới nhất -->
        <div class="card">
          <div class="card-header">
            <h3>🕐 Sinh viên mới nhất</h3>
            <a href="sinh_vien.php" class="btn btn-secondary btn-sm">Xem tất cả</a>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Sinh viên</th>
                  <th>Mã SV</th>
                  <th>Khoa</th>
                  <th>GPA</th>
                  <th>Trạng thái</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($sv = $recent->fetch_assoc()): 
                  $init = mb_strtoupper(mb_substr($sv['ho_ten'], 0, 1));
                  $gpa_class = $sv['gpa'] >= 3.5 ? 'gpa-high' : ($sv['gpa'] >= 2.5 ? 'gpa-mid' : 'gpa-low');
                  $badge = match($sv['trang_thai']) {
                    'Đang học'  => 'badge-green',
                    'Tốt nghiệp'=> 'badge-blue',
                    'Bảo lưu'   => 'badge-orange',
                    default     => 'badge-red',
                  };
                ?>
                <tr>
                  <td>
                    <div class="sv-name-wrap">
                      <div class="sv-avatar"><?= $init ?></div>
                      <div class="sv-name"><?= htmlspecialchars($sv['ho_ten']) ?></div>
                    </div>
                  </td>
                  <td><code><?= htmlspecialchars($sv['ma_sv']) ?></code></td>
                  <td><?= htmlspecialchars($sv['ten_khoa'] ?? 'N/A') ?></td>
                  <td class="<?= $gpa_class ?>"><?= number_format($sv['gpa'], 2) ?></td>
                  <td><span class="badge <?= $badge ?>"><?= $sv['trang_thai'] ?></span></td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
        
        <!-- Theo khoa -->
        <div class="card">
          <div class="card-header"><h3>🏫 Theo khoa</h3></div>
          <div class="card-body">
            <?php while ($k = $by_khoa->fetch_assoc()): 
              $pct = $total > 0 ? round($k['so_luong']/$total*100) : 0;
            ?>
            <div style="margin-bottom:16px">
              <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:5px">
                <span style="font-weight:600"><?= htmlspecialchars($k['ten_khoa']) ?></span>
                <span style="color:var(--muted)"><?= $k['so_luong'] ?> SV</span>
              </div>
              <div style="background:#f1f5f9; border-radius:6px; height:8px; overflow:hidden">
                <div style="width:<?= $pct ?>%; height:100%; background:linear-gradient(90deg,var(--blue),var(--teal)); border-radius:6px; transition:width .5s"></div>
              </div>
            </div>
            <?php endwhile; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
