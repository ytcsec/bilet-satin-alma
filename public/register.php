<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/../src/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if ($password !== $password_confirm) {
        $error = 'Şifreler eşleşmiyor';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM User WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Bu e-posta adresi zaten kullanılıyor';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $userId = generateId('user-');
            $stmt = $db->prepare("INSERT INTO User (id, full_name, email, password, role, balance) VALUES (?, ?, ?, ?, 'user', 800.0)");
            
            if ($stmt->execute([$userId, $name, $email, $hashed])) {
                $success = 'Kayıt başarılı! Giriş yapabilirsiniz.';
            } else {
                $error = 'Kayıt sırasında bir hata oluştu';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Bilet Satın Alma Platformu</h1>
            <nav>
                <a href="/index.php">Ana Sayfa</a>
                <a href="/login.php">Giriş Yap</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card" style="max-width: 500px; margin: 50px auto;">
            <h2>Kayıt Ol</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Ad Soyad</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>E-posta</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Şifre</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Şifre Tekrar</label>
                    <input type="password" name="password_confirm" required>
                </div>
                <button type="submit" class="btn btn-primary">Kayıt Ol</button>
            </form>
            <p style="margin-top: 20px;">Zaten hesabınız var mı? <a href="/login.php">Giriş yapın</a></p>
        </div>
    </div>
</body>
</html>
