<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
require 'koneksi.php';

$pelapor = $_SESSION['username'];
$msg     = "";


if (isset($_POST['tambah'])) {
    $judul     = trim($_POST['judul']);
    $lokasi    = trim($_POST['lokasi']);
    $deskripsi = trim($_POST['deskripsi']);

    $stmt = $conn->prepare(
        "INSERT INTO Laporan (judul, lokasi, deskripsi, pelapor) VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("ssss", $judul, $lokasi, $deskripsi, $pelapor);
    $stmt->execute()
        ? ($msg = "✅ Laporan berhasil ditambahkan!")
        : ($msg = "❌ Gagal menambahkan laporan.");
    $stmt->close();
}


if (isset($_POST['update'])) {
    $id        = (int) $_POST['id'];
    $judul     = trim($_POST['judul']);
    $lokasi    = trim($_POST['lokasi']);
    $deskripsi = trim($_POST['deskripsi']);
    $status    = $_POST['status'];

    $stmt = $conn->prepare(
        "UPDATE Laporan SET judul=?, lokasi=?, deskripsi=?, status=? WHERE id=?"
    );
    $stmt->bind_param("ssssi", $judul, $lokasi, $deskripsi, $status, $id);
    $stmt->execute()
        ? ($msg = "✅ Laporan berhasil diperbarui!")
        : ($msg = "❌ Gagal memperbarui laporan.");
    $stmt->close();
}


if (isset($_POST['hapus'])) {
    $id   = (int) $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM Laporan WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute()
        ? ($msg = "✅ Laporan berhasil dihapus!")
        : ($msg = "❌ Gagal menghapus laporan.");
    $stmt->close();
}



$editData = null;
if (isset($_GET['edit'])) {
    $editId  = (int) $_GET['edit'];
    $res     = $conn->prepare("SELECT * FROM Laporan WHERE id=?");
    $res->bind_param("i", $editId);
    $res->execute();
    $editData = $res->get_result()->fetch_assoc();
    $res->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Sampah – SmartWaste</title>
  <link rel="stylesheet" href="homepage.css">
  <style>
    
    body { margin: 0; font-family: 'Poppins', Arial, sans-serif; background: #121212; color: #eee; }
    .wrapper { display: flex; min-height: 100vh; }

    
    .sidebar { width: 220px; background: #1f1f1f; padding: 20px; flex-shrink: 0; }
    .sidebar h3 { color: #fff; margin-bottom: 20px; }
    .sidebar ul { list-style: none; padding: 0; }
    .sidebar li { margin: 15px 0; }
    .sidebar a  { color: #ccc; text-decoration: none; }
    .sidebar a:hover, .sidebar .active { color: #5d00ff; }

   
    .main { flex: 1; padding: 30px; overflow-y: auto; }
    h2 { color: #a78bfa; margin-bottom: 4px; }
    .sub { color: #888; font-size: 13px; margin-bottom: 24px; }

  
    .msg { background: #1e3a2f; border-left: 4px solid #4caf50;
           padding: 10px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; }

    
    .form-box { background: #1f1f1f; border-radius: 12px; padding: 24px; margin-bottom: 32px; }
    .form-box h3 { margin-top: 0; color: #c4b5fd; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .form-box input, .form-box textarea, .form-box select {
      width: 100%; padding: 11px 14px; border-radius: 8px;
      border: 1px solid #333; background: #2a2a2a; color: #eee;
      font-size: 14px; margin-top: 6px; box-sizing: border-box;
    }
    .form-box textarea { resize: vertical; min-height: 80px; }
    .form-box label { font-size: 13px; color: #aaa; }
    .form-group { display: flex; flex-direction: column; margin-bottom: 14px; }
    .btn { padding: 11px 22px; border: none; border-radius: 8px; cursor: pointer;
           font-size: 14px; font-weight: 600; transition: opacity .2s; }
    .btn:hover { opacity: .85; }
    .btn-primary { background: linear-gradient(90deg,#7f00ff,#e100ff); color: #fff; }
    .btn-warning { background: #f59e0b; color: #1a1a1a; }
    .btn-danger  { background: #ef4444; color: #fff; }
    .btn-sm { padding: 6px 14px; font-size: 12px; border-radius: 6px; }

 
    table { width: 100%; border-collapse: collapse; background: #1f1f1f; border-radius: 12px; overflow: hidden; }
    thead { background: #2a2a2a; }
    th, td { padding: 12px 16px; text-align: left; font-size: 13px; border-bottom: 1px solid #2d2d2d; }
    th { color: #a78bfa; font-weight: 600; }
    td { color: #ddd; }
    tr:last-child td { border-bottom: none; }
    .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .badge-menunggu { background: #3b2f00; color: #fbbf24; }
    .badge-diproses { background: #1e3a5f; color: #60a5fa; }
    .badge-selesai  { background: #1e3a2f; color: #4ade80; }
    .actions { display: flex; gap: 6px; }

    @media(max-width:700px) { .form-row { grid-template-columns: 1fr; } .sidebar { display:none; } }
  </style>
</head>
<body>
<div class="wrapper">

  
  <aside class="sidebar">
    <h3>Menu</h3>
    <ul>
      <li><a href="homepage.php">Home</a></li>
      <li><a href="laporan.php" class="active" style="color:#5d00ff">Laporan</a></li>
      <li><a href="edling.html">Edukasi Lingkungan</a></li>
      <li><a href="admin.html">Dashboard Admin</a></li>
      <li><a href="forum komunitas.html">Forum Komunitas</a></li>
      <li><a href="laporan statistik.html">Laporan Statistik</a></li>
      <li><a href="halaman status laporan.html">Status Laporan</a></li>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </aside>

 
  <main class="main">
    <h2>📋 Laporan Sampah</h2>
    <p class="sub">Kelola laporan masalah sampah di daerahmu</p>

    <?php if ($msg): ?>
      <div class="msg"><?php echo $msg; ?></div>
    <?php endif; ?>

    
    <div class="form-box">
      <?php if ($editData): ?>
        <h3>✏️ Edit Laporan</h3>
        <form method="POST">
          <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
          <div class="form-row">
            <div class="form-group">
              <label>Judul Laporan</label>
              <input type="text" name="judul" value="<?php echo htmlspecialchars($editData['judul']); ?>" required>
            </div>
            <div class="form-group">
              <label>Lokasi</label>
              <input type="text" name="lokasi" value="<?php echo htmlspecialchars($editData['lokasi']); ?>" required>
            </div>
          </div>
          <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi"><?php echo htmlspecialchars($editData['deskripsi']); ?></textarea>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status">
              <?php foreach (['Menunggu','Diproses','Selesai'] as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo $editData['status']==$s?'selected':''; ?>>
                  <?php echo $s; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" name="update" class="btn btn-warning">💾 Simpan Perubahan</button>
          <a href="laporan.php" style="margin-left:10px; color:#aaa; font-size:13px;">Batal</a>
        </form>

      <?php else: ?>
        <h3>➕ Tambah Laporan Baru</h3>
        <form method="POST">
          <div class="form-row">
            <div class="form-group">
              <label>Judul Laporan</label>
              <input type="text" name="judul" placeholder="cth: Tumpukan sampah di pasar" required>
            </div>
            <div class="form-group">
              <label>Lokasi</label>
              <input type="text" name="lokasi" placeholder="cth: Jl. Sudirman No. 10" required>
            </div>
          </div>
          <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi" placeholder="Jelaskan masalah sampah yang ditemukan..."></textarea>
          </div>
          <button type="submit" name="tambah" class="btn btn-primary">📨 Kirim Laporan</button>
        </form>
      <?php endif; ?>
    </div>

    
    <h3 style="color:#c4b5fd; margin-bottom:12px;">📊 Daftar Semua Laporan</h3>
    <?php if (empty($laporan)): ?>
      <p style="color:#666;">Belum ada laporan yang masuk.</p>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Judul</th>
          <th>Lokasi</th>
          <th>Pelapor</th>
          <th>Status</th>
          <th>Tanggal</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($laporan as $row): ?>
        <tr>
          <td><?php echo $row['id']; ?></td>
          <td><?php echo htmlspecialchars($row['judul']); ?></td>
          <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
          <td><?php echo htmlspecialchars($row['pelapor']); ?></td>
          <td>
            <?php
              $badgeClass = match($row['status']) {
                'Diproses' => 'badge-diproses',
                'Selesai'  => 'badge-selesai',
                default    => 'badge-menunggu',
              };
            ?>
            <span class="badge <?php echo $badgeClass; ?>"><?php echo $row['status']; ?></span>
          </td>
          <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
          <td class="actions">
            
            <a href="laporan.php?edit=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">✏️</a>
           
            <form method="POST" onsubmit="return confirm('Hapus laporan ini?');" style="display:inline;">
              <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
              <button type="submit" name="hapus" class="btn btn-danger btn-sm">🗑️</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </main>

</div>
</body>
</html>
