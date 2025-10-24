<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/../src/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM User WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: /index.php');
        exit;
    } else {
        $error = 'E-posta veya şifre hatalı';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>

<body>
    <header>
        <div class="container">
            <h1>Bilet Satın Alma Platformu</h1>
            <nav>
                <a href="/index.php">Ana Sayfa</a>
                <a href="/register.php">Kayıt Ol</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card" style="max-width: 500px; margin: 50px auto;">
            <h2>Giriş Yap</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>E-posta</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Şifre</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Giriş Yap</button>
            </form>
            <p style="margin-top: 20px;">Hesabınız yok mu? <a href="/register.php">Kayıt olun</a></p>
        </div>
    </div>
</body>

</html>