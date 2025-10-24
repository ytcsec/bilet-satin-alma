<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/database.php';
require_once __DIR__ . '/../../src/auth.php';

requireRole('firma_admin');

$db = getDB();
$user = getCurrentUser();

$stmt = $db->prepare("SELECT t.*, c.name as firma_adi FROM Trips t 
                      JOIN Bus_Company c ON t.company_id = c.id 
                      WHERE t.company_id = ? 
                      ORDER BY t.departure_time DESC");
$stmt->execute([$user['company_id']]);
$seferler = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT * FROM Bus_Company WHERE id = ?");
$stmt->execute([$user['company_id']]);
$firma = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Admin Paneli</title>
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
            <h2><?= htmlspecialchars($firma['name']) ?> - Sefer Yönetimi</h2>
            <a href="/firma-admin/sefer-ekle.php" class="btn btn-primary">Yeni Sefer Ekle</a>
        </div>

        <div class="card">
            <h2>Seferler</h2>
            <?php if (empty($seferler)): ?>
                <div class="alert alert-info">Henüz sefer eklenmemiş.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Kalkış</th>
                            <th>Varış</th>
                            <th>Kalkış Zamanı</th>
                            <th>Varış Zamanı</th>
                            <th>Fiyat</th>
                            <th>Koltuk</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($seferler as $sefer): ?>
                            <?php
                            $stmt = $db->prepare("SELECT COUNT(DISTINCT bs.id) as dolu FROM Booked_Seats bs 
                                                 JOIN Tickets tk ON bs.ticket_id = tk.id 
                                                 WHERE tk.trip_id = ? AND tk.status = 'active'");
                            $stmt->execute([$sefer['id']]);
                            $dolu = $stmt->fetch(PDO::FETCH_ASSOC)['dolu'];
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($sefer['departure_city']) ?></td>
                                <td><?= htmlspecialchars($sefer['destination_city']) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($sefer['departure_time'])) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($sefer['arrival_time'])) ?></td>
                                <td><?= number_format($sefer['price'], 2) ?> TL</td>
                                <td><?= $dolu ?> / <?= $sefer['capacity'] ?></td>
                                <td>
                                    <a href="/firma-admin/sefer-duzenle.php?id=<?= $sefer['id'] ?>" class="btn btn-secondary">Düzenle</a>
                                    <a href="/firma-admin/sefer-sil.php?id=<?= $sefer['id'] ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Seferi silmek istediğinizden emin misiniz?')">Sil</a>
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
