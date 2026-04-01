<?php
session_start();
require 'koneksi.php';

$error   = "";
$success = "";

if (isset($_POST['daftar'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    
    $cek = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $cek->bind_param("s", $username);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        $error = "Username sudah digunakan, coba yang lain.";
    } else {
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashedPassword);

        if ($stmt->execute()) {
            header("Location: login.php?pesan=berhasil");
            exit;
        } else {
            $error = "Registrasi gagal, coba lagi.";
        }
        $stmt->close();
    }
    $cek->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar Akun</title>
  <link rel="stylesheet" href="registrasi.css">
</head>
<body class="login-page">
        
  <div class="card">
    <h2>DAFTAR AKUN</h2>

    <form method="POST">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>

    <button type="submit" name="daftar">Daftar</button>
  </form>

  <?php if (!empty($dataUser)) { ?>
  <h3>Data User:</h3>
  <ul>
    <?php foreach ($dataUser as $user) { ?>
      <li>
        Username: <?php echo $user['username']; ?> |
        Password: <?php echo $user['password']; ?>
      </li>
    <?php } ?>
  </ul>
<?php } ?>

   <a href="login.php">Login</a>
    </div>
</body>
</html>