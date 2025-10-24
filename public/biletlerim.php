<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/../src/auth.php';

requireLogin();

$db = getDB();
$user = getCurrentUser();

$stmt = $db->prepare("SELECT tk.*, t.departure_city, t.destination_city, t.departure_time, t.arrival_time, 
                      c.name as firma_adi, bs.seat_number
                      FROM Tickets tk 
                      JOIN Trips t ON tk.trip_id = t.id 
                      JOIN Bus_Company c ON t.company_id = c.id 
                      JOIN Booked_Seats bs ON bs.ticket_id = tk.id
                      WHERE tk.user_id = ? 
                      ORDER BY t.departure_time DESC");
$stmt->execute([$user['id']]);
$biletler = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletlerim</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Bilet Satın Alma Platformu</h1>
            <nav>
                <a href="/index.php">Ana Sayfa</a>
                <a href="/biletlerim.php">Biletlerim</a>
                <a href="/logout.php">Çıkış Yap</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="user-info">
            <h2><?= htmlspecialchars($user['full_name']) ?></h2>
            <p>E-posta: <?= htmlspecialchars($user['email']) ?></p>
            <p>Bakiye: <span class="credit-display"><?= number_format($user['balance'], 2) ?> TL</span></p>
        </div>

        <div class="card">
            <h2>Biletlerim</h2>
            <?php if (empty($biletler)): ?>
                <div class="alert alert-info">Henüz biletiniz bulunmamaktadır.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Firma</th>
                            <th>Güzergah</th>
                            <th>Kalkış</th>
                            <th>Varış</th>
                            <th>Koltuk</th>
                            <th>Fiyat</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($biletler as $bilet): ?>
                            <tr>
                                <td><?= htmlspecialchars($bilet['firma_adi']) ?></td>
                                <td><?= htmlspecialchars($bilet['departure_city']) ?> - <?= htmlspecialchars($bilet['destination_city']) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($bilet['departure_time'])) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($bilet['arrival_time'])) ?></td>
                                <td><?= $bilet['seat_number'] ?></td>
                                <td><?= number_format($bilet['total_price'], 2) ?> TL</td>
                                <td><?= $bilet['status'] === 'active' ? 'Aktif' : 'İptal' ?></td>
                                <td>
                                    <?php if ($bilet['status'] === 'active'): ?>
                                        <?php
                                        date_default_timezone_set('Europe/Istanbul');
                                        $sefer_zamani = strtotime($bilet['departure_time']);
                                        $simdi = time();
                                        $fark_saat = ($sefer_zamani - $simdi) / 3600;
                                        $iptal_edilebilir = $fark_saat >= 1;
                                        ?>
                                        <a href="/bilet-pdf.php?id=<?= $bilet['id'] ?>" class="btn btn-secondary">PDF</a>
                                        <?php if ($iptal_edilebilir): ?>
                                            <a href="/bilet-iptal.php?id=<?= $bilet['id'] ?>" 
                                               class="btn btn-danger" 
                                               onclick="return confirm('Bileti iptal etmek istediğinizden emin misiniz?')">İptal</a>
                                        <?php else: ?>
                                            <button class="btn btn-danger" disabled title="Kalkışa 1 saatten az kaldı">İptal Edilemez</button>
                                        <?php endif; ?>
                                    <?php endif; ?>
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
