<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/database.php';
require_once __DIR__ . '/../../src/auth.php';

requireRole('firma_admin');

$db = getDB();
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'ekle') {
        $kod = $_POST['kod'] ?? '';
        $oran = $_POST['oran'] ?? 0;
        $kullanim_limiti = $_POST['kullanim_limiti'] ?? 0;
        $son_kullanma = $_POST['son_kullanma'] ?? '';
        
        $coupon_id = generateId('coupon-');
        $stmt = $db->prepare("INSERT INTO Coupons (id, code, discount, usage_limit, expire_date, company_id) 
                             VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$coupon_id, $kod, $oran, $kullanim_limiti, $son_kullanma, $user['company_id']]);
    } elseif ($action === 'sil') {
        $kupon_id = $_POST['kupon_id'] ?? 0;
        $stmt = $db->prepare("DELETE FROM Coupons WHERE id = ? AND company_id = ?");
        $stmt->execute([$kupon_id, $user['company_id']]);
    }
}

$stmt = $db->prepare("SELECT * FROM Coupons WHERE company_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['company_id']]);
$kuponlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kupon Yönetimi</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Firma Admin Paneli</h1>
            <nav>
                <a href="/index.php">Ana Sayfa</a>
                <a href="/firma-admin/index.php">Seferlerim</a>
                <a href="/firma-admin/kupon-yonetimi.php">Kuponlar</a>
                <a href="/logout.php">Çıkış Yap</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2>Yeni Kupon Ekle</h2>
            <form method="POST">
                <input type="hidden" name="action" value="ekle">
                <div class="grid-2">
                    <div class="form-group">
                        <label>Kupon Kodu</label>
                        <input type="text" name="kod" required>
                    </div>
                    <div class="form-group">
                        <label>İndirim Oranı (%)</label>
                        <input type="number" name="oran" min="1" max="100" step="0.01" required>
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Kullanım Limiti</label>
                        <input type="number" name="kullanim_limiti" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Son Kullanma Tarihi</label>
                        <input type="datetime-local" name="son_kullanma" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Kupon Ekle</button>
            </form>
        </div>

        <div class="card">
            <h2>Kuponlar</h2>
            <?php if (empty($kuponlar)): ?>
                <div class="alert alert-info">Henüz kupon eklenmemiş.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Kod</th>
                            <th>İndirim</th>
                            <th>Kullanım Limiti</th>
                            <th>Son Kullanma</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kuponlar as $kupon): ?>
                            <tr>
                                <td><?= htmlspecialchars($kupon['code']) ?></td>
                                <td>%<?= $kupon['discount'] ?></td>
                                <td><?= $kupon['usage_limit'] ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($kupon['expire_date'])) ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="sil">
                                        <input type="hidden" name="kupon_id" value="<?= $kupon['id'] ?>">
                                        <button type="submit" class="btn btn-danger" 
                                                onclick="return confirm('Kuponu silmek istediğinizden emin misiniz?')">Sil</button>
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
