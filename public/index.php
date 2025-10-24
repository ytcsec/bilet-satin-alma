<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/../src/auth.php';

$db = getDB();
$user = getCurrentUser();

$kalkis = $_GET['kalkis'] ?? '';
$varis = $_GET['varis'] ?? '';
$tarih = $_GET['tarih'] ?? date('Y-m-d');

$seferler = [];
$arama_yapildi = false;

if ($kalkis && $varis) {
    $arama_yapildi = true;
    $query = "SELECT t.*, c.name as firma_adi FROM Trips t 
              JOIN Bus_Company c ON t.company_id = c.id WHERE 1=1";
    $params = [];
    
    $query .= " AND t.departure_city LIKE ?";
    $params[] = "%$kalkis%";
    
    $query .= " AND t.destination_city LIKE ?";
    $params[] = "%$varis%";
    
    if ($tarih) {
        $query .= " AND DATE(t.departure_time) = ?";
        $params[] = $tarih;
    }
    
    $query .= " ORDER BY t.departure_time";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $seferler = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilet Satın Alma Platformu</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Bilet Satın Alma Platformu</h1>
            <nav>
                <a href="/index.php">Ana Sayfa</a>
                <?php if ($user): ?>
                    <?php if ($user['role'] === 'admin'): ?>
                        <a href="/admin/index.php">Admin Panel</a>
                    <?php elseif ($user['role'] === 'firma_admin'): ?>
                        <a href="/firma-admin/index.php">Firma Panel</a>
                    <?php else: ?>
                        <a href="/biletlerim.php">Biletlerim</a>
                    <?php endif; ?>
                    <a href="/logout.php">Çıkış Yap (<?= htmlspecialchars($user['full_name']) ?>)</a>
                <?php else: ?>
                    <a href="/login.php">Giriş Yap</a>
                    <a href="/register.php">Kayıt Ol</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2>Sefer Ara</h2>
            <form method="GET" action="">
                <div class="grid-2">
                    <div class="form-group">
                        <label>Kalkış <span style="color: #dc3545;">*</span></label>
                        <input type="text" name="kalkis" value="<?= htmlspecialchars($kalkis) ?>" placeholder="Örn: İstanbul" required>
                    </div>
                    <div class="form-group">
                        <label>Varış <span style="color: #dc3545;">*</span></label>
                        <input type="text" name="varis" value="<?= htmlspecialchars($varis) ?>" placeholder="Örn: Ankara" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Tarih</label>
                    <input type="date" name="tarih" value="<?= htmlspecialchars($tarih) ?>">
                </div>
                <button type="submit" class="btn btn-primary">Sefer Ara</button>
            </form>
        </div>

        <?php if (!empty($seferler)): ?>
            <div class="card">
                <h2>Bulunan Seferler (<?= count($seferler) ?> adet)</h2>
                <div class="sefer-list">
                    <?php foreach ($seferler as $sefer): ?>
                        <?php
                        $stmt = $db->prepare("SELECT COUNT(DISTINCT bs.id) as dolu FROM Booked_Seats bs 
                                             JOIN Tickets t ON bs.ticket_id = t.id 
                                             WHERE t.trip_id = ? AND t.status = 'active'");
                        $stmt->execute([$sefer['id']]);
                        $dolu = $stmt->fetch(PDO::FETCH_ASSOC)['dolu'];
                        $bos = $sefer['capacity'] - $dolu;
                        $doluluk_orani = ($dolu / $sefer['capacity']) * 100;
                        
                        $kalkis_zaman = strtotime($sefer['departure_time']);
                        $varis_zaman = strtotime($sefer['arrival_time']);
                        $sure_saat = ($varis_zaman - $kalkis_zaman) / 3600;
                        $sure_dakika = ($sure_saat - floor($sure_saat)) * 60;
                        ?>
                        <div class="sefer-item <?= $bos == 0 ? 'sefer-dolu' : '' ?>">
                            <div class="sefer-header">
                                <div class="firma-badge">
                                    <h3><?= htmlspecialchars($sefer['firma_adi']) ?></h3>
                                </div>
                                <div class="fiyat-badge">
                                    <span class="fiyat"><?= number_format($sefer['price'], 0) ?> TL</span>
                                </div>
                            </div>
                            
                            <div class="sefer-route">
                                <div class="route-point">
                                    <div class="sehir"><?= htmlspecialchars($sefer['departure_city']) ?></div>
                                    <div class="zaman"><?= date('H:i', strtotime($sefer['departure_time'])) ?></div>
                                    <div class="tarih"><?= date('d.m.Y', strtotime($sefer['departure_time'])) ?></div>
                                </div>
                                <div class="route-line">
                                    <div class="sure-bilgi">
                                        <?php if ($sure_saat >= 1): ?>
                                            <?= floor($sure_saat) ?>s <?= round($sure_dakika) ?>dk
                                        <?php else: ?>
                                            <?= round($sure_dakika) ?> dakika
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="route-point">
                                    <div class="sehir"><?= htmlspecialchars($sefer['destination_city']) ?></div>
                                    <div class="zaman"><?= date('H:i', strtotime($sefer['arrival_time'])) ?></div>
                                    <div class="tarih"><?= date('d.m.Y', strtotime($sefer['arrival_time'])) ?></div>
                                </div>
                            </div>
                            
                            <div class="sefer-footer">
                                <div class="koltuk-bilgi">
                                    <?php if ($bos > 0): ?>
                                        <span class="koltuk-durum musait"><?= $bos ?> koltuk müsait</span>
                                        <?php if ($doluluk_orani > 80): ?>
                                            <span class="uyari">Son koltuklar!</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="koltuk-durum dolu">Dolu</span>
                                    <?php endif; ?>
                                </div>
                                <div class="sefer-actions">
                                    <?php if ($bos > 0): ?>
                                        <?php if ($user): ?>
                                            <a href="/bilet-al.php?sefer_id=<?= $sefer['id'] ?>" class="btn btn-primary">Bilet Al</a>
                                        <?php else: ?>
                                            <a href="/login.php" class="btn btn-primary">Giriş Yap</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="btn btn-secondary" disabled>Dolu</button>
                                    <?php endif; ?>
                                    <a href="/sefer-detay.php?id=<?= $sefer['id'] ?>" class="btn btn-secondary">Detay</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php elseif ($arama_yapildi): ?>
            <div class="card">
                <div class="alert alert-info">
                    <strong><?= htmlspecialchars($kalkis) ?></strong> - <strong><?= htmlspecialchars($varis) ?></strong> 
                    güzergahında <?= $tarih ? date('d.m.Y', strtotime($tarih)) . ' tarihinde' : '' ?> sefer bulunamadı. 
                    Lütfen farklı tarih veya şehir deneyin.
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

