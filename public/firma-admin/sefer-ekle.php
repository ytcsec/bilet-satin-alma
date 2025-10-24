<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/database.php';
require_once __DIR__ . '/../../src/auth.php';

requireRole('firma_admin');

$db = getDB();
$user = getCurrentUser();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kalkis = $_POST['kalkis'] ?? '';
    $varis = $_POST['varis'] ?? '';
    $kalkis_zamani = $_POST['kalkis_zamani'] ?? '';
    $varis_zamani = $_POST['varis_zamani'] ?? '';
    $fiyat = $_POST['fiyat'] ?? 0;
    $koltuk_sayisi = $_POST['koltuk_sayisi'] ?? 0;
    
    $trip_id = generateId('trip-');
    $stmt = $db->prepare("INSERT INTO Trips (id, company_id, departure_city, destination_city, departure_time, arrival_time, price, capacity) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$trip_id, $user['company_id'], $kalkis, $varis, $kalkis_zamani, $varis_zamani, $fiyat, $koltuk_sayisi])) {
        header('Location: /firma-admin/index.php');
        exit;
    } else {
        $error = 'Sefer eklenirken hata oluştu';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sefer Ekle</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Firma Admin Paneli</h1>
            <nav>
                <a href="/index.php">Ana Sayfa</a>
                <a href="/firma-admin/index.php">Seferlerim</a>
                <a href="/logout.php">Çıkış Yap</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2>Yeni Sefer Ekle</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="grid-2">
                    <div class="form-group">
                        <label>Kalkış Şehri</label>
                        <input type="text" name="kalkis" required>
                    </div>
                    <div class="form-group">
                        <label>Varış Şehri</label>
                        <input type="text" name="varis" required>
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Kalkış Zamanı</label>
                        <input type="datetime-local" name="kalkis_zamani" required>
                    </div>
                    <div class="form-group">
                        <label>Varış Zamanı</label>
                        <input type="datetime-local" name="varis_zamani" required>
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Fiyat (TL)</label>
                        <input type="number" name="fiyat" step="1" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Koltuk Sayısı</label>
                        <input type="number" name="koltuk_sayisi" min="1" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Sefer Ekle</button>
                <a href="/firma-admin/index.php" class="btn btn-secondary">İptal</a>
            </form>
        </div>
    </div>
</body>
</html>
