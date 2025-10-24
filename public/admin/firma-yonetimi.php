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
        $company_id = generateId('company-');
        $stmt = $db->prepare("INSERT INTO Bus_Company (id, name) VALUES (?, ?)");
        $stmt->execute([$company_id, $name]);
    } elseif ($action === 'duzenle') {
        $firma_id = $_POST['firma_id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $stmt = $db->prepare("UPDATE Bus_Company SET name = ? WHERE id = ?");
        $stmt->execute([$name, $firma_id]);
    } elseif ($action === 'sil') {
        $firma_id = $_POST['firma_id'] ?? 0;
        $stmt = $db->prepare("DELETE FROM Bus_Company WHERE id = ?");
        $stmt->execute([$firma_id]);
    }
}

$stmt = $db->query("SELECT * FROM Bus_Company ORDER BY name");
$firmalar = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Yönetimi</title>
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
            <h2>Yeni Firma Ekle</h2>
            <form method="POST">
                <input type="hidden" name="action" value="ekle">
                <div class="form-group">
                    <label>Firma Adı</label>
                    <input type="text" name="name" required>
                </div>
                <button type="submit" class="btn btn-primary">Firma Ekle</button>
            </form>
        </div>

        <div class="card">
            <h2>Firmalar</h2>
            <?php if (empty($firmalar)): ?>
                <div class="alert alert-info">Henüz firma eklenmemiş.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Firma Adı</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($firmalar as $firma): ?>
                            <tr>
                                <td><?= htmlspecialchars(substr($firma['id'], 0, 15)) ?>...</td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="duzenle">
                                        <input type="hidden" name="firma_id" value="<?= $firma['id'] ?>">
                                        <input type="text" name="name" value="<?= htmlspecialchars($firma['name']) ?>" 
                                               style="border: none; background: transparent;">
                                        <button type="submit" class="btn btn-secondary">Güncelle</button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="sil">
                                        <input type="hidden" name="firma_id" value="<?= $firma['id'] ?>">
                                        <button type="submit" class="btn btn-danger" 
                                                onclick="return confirm('Firmayı silmek istediğinizden emin misiniz?')">Sil</button>
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
