<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/database.php';
require_once __DIR__ . '/../../src/auth.php';

requireRole('admin');

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'ekle') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $firma_id = $_POST['firma_id'] ?? 0;
        
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $user_id = generateId('fadmin-');
        $stmt = $db->prepare("INSERT INTO User (id, full_name, email, password, role, company_id, balance) 
                             VALUES (?, ?, ?, ?, 'firma_admin', ?, 0)");
        $stmt->execute([$user_id, $name, $email, $hashed, $firma_id]);
    } elseif ($action === 'sil') {
        $user_id = $_POST['user_id'] ?? 0;
        $stmt = $db->prepare("DELETE FROM User WHERE id = ? AND role = 'firma_admin'");
        $stmt->execute([$user_id]);
    }
}

$stmt = $db->query("SELECT * FROM Bus_Company ORDER BY name");
$firmalar = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT u.*, c.name as firma_adi FROM User u 
                    LEFT JOIN Bus_Company c ON u.company_id = c.id 
                    WHERE u.role = 'firma_admin' 
                    ORDER BY u.full_name");
$firma_adminler = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Admin Yönetimi</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Admin Paneli</h1>
            <nav>
                <a href="/index.php">Ana Sayfa</a>
                <a href="/admin/index.php">Yönetim</a>
                <a href="/admin/kupon-yonetimi.php">Kuponlar</a>
                <a href="/logout.php">Çıkış Yap</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2>Yeni Firma Admin Ekle</h2>
            <form method="POST">
                <input type="hidden" name="action" value="ekle">
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
                    <label>Firma</label>
                    <select name="firma_id" required>
                        <option value="">Firma Seçin</option>
                        <?php foreach ($firmalar as $firma): ?>
                            <option value="<?= $firma['id'] ?>"><?= htmlspecialchars($firma['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Admin Ekle</button>
            </form>
        </div>

        <div class="card">
            <h2>Firma Adminleri</h2>
            <?php if (empty($firma_adminler)): ?>
                <div class="alert alert-info">Henüz firma admin eklenmemiş.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Ad Soyad</th>
                            <th>E-posta</th>
                            <th>Firma</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($firma_adminler as $admin): ?>
                            <tr>
                                <td><?= htmlspecialchars($admin['full_name']) ?></td>
                                <td><?= htmlspecialchars($admin['email']) ?></td>
                                <td><?= htmlspecialchars($admin['firma_adi']) ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="sil">
                                        <input type="hidden" name="user_id" value="<?= $admin['id'] ?>">
                                        <button type="submit" class="btn btn-danger" 
                                                onclick="return confirm('Admini silmek istediğinizden emin misiniz?')">Sil</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
