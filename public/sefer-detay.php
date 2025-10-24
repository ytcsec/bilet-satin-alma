<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/../src/auth.php';

$db = getDB();
$user = getCurrentUser();
$sefer_id = $_GET['id'] ?? 0;

$stmt = $db->prepare("SELECT t.*, c.name as firma_adi FROM Trips t 
                      JOIN Bus_Company c ON t.company_id = c.id WHERE t.id = ?");
$stmt->execute([$sefer_id]);
$sefer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sefer) {
    header('Location: /index.php');
    exit;
}

$stmt = $db->prepare("SELECT bs.seat_number FROM Booked_Seats bs 
                      JOIN Tickets tk ON bs.ticket_id = tk.id 
                      WHERE tk.trip_id = ? AND tk.status = 'active'");
$stmt->execute([$sefer_id]);
$dolu_koltuklar = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sefer Detayı</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Bilet Satın Alma Platformu</h1>
            <nav>
                <a href="/index.php">Ana Sayfa</a>
                <?php if ($user): ?>
                    <a href="/biletlerim.php">Biletlerim</a>
                    <a href="/logout.php">Çıkış Yap</a>
                <?php else: ?>
                    <a href="/login.php">Giriş Yap</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2>Sefer Detayı</h2>
            <div class="sefer-info">
                <h3><?= htmlspecialchars($sefer['firma_adi']) ?></h3>
                <p><strong>Kalkış:</strong> <?= htmlspecialchars($sefer['departure_city']) ?></p>
                <p><strong>Varış:</strong> <?= htmlspecialchars($sefer['destination_city']) ?></p>
                <p><strong>Kalkış Zamanı:</strong> <?= date('d.m.Y H:i', strtotime($sefer['departure_time'])) ?></p>
                <p><strong>Varış Zamanı:</strong> <?= date('d.m.Y H:i', strtotime($sefer['arrival_time'])) ?></p>
                <p><strong>Fiyat:</strong> <?= number_format($sefer['price'], 2) ?> TL</p>
                <p><strong>Toplam Koltuk:</strong> <?= $sefer['capacity'] ?></p>
                <p><strong>Dolu Koltuk:</strong> <?= count($dolu_koltuklar) ?></p>
                <p><strong>Boş Koltuk:</strong> <?= $sefer['capacity'] - count($dolu_koltuklar) ?></p>
            </div>
            
            <?php if ($user): ?>
                <a href="/bilet-al.php?sefer_id=<?= $sefer['id'] ?>" class="btn btn-primary">Bilet Satın Al</a>
            <?php else: ?>
                <div class="alert alert-info">Bilet satın almak için lütfen giriş yapın.</div>
                <a href="/login.php" class="btn btn-primary">Giriş Yap</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
