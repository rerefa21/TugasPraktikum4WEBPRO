<?php
session_start();
require 'koneksi.php';

$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        
        if (password_verify($password, $user['password'])) {
            $_SESSION['login']    = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id']  = $user['id'];

            header("Location: homepage.php");
            exit;
        } else {
            $error = "Username atau password salah.";
        }
    } else {
        $error = "Username atau password salah.";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
    <html lang="id">
    <head>
    <meta charset="UTF-8">
    <title>Client Portal</title>
    <link rel="stylesheet" href="login.css">
    </head>
    <body class="login-page">

            
    <div class="login-card">
        <?php
    if (isset($_GET['pesan'])) {
        echo "<p style='color:green;text-align:center;'>Registrasi berhasil, silakan login</p>";
    }
    ?>
        <h1>CLIENT PORTAL</h1>
        <p class="subtitle">SMART WASTE</p>

    <form method="POST">
        <input type="text" name="username" placeholder="Username atau Email" required>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" name="login">Log In</button>
    </form>
         <p class="register">
    Belum punya akun? <a href="registrasi.php">Daftar</a>
  </p>
    </div>
</body>
</html>
