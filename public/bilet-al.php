<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/../src/auth.php';

requireLogin();

$db = getDB();
$user = getCurrentUser();
$sefer_id = $_GET['sefer_id'] ?? 0;

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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $koltuk_no = $_POST['koltuk_no'] ?? 0;
    $kupon_kod = $_POST['kupon_kod'] ?? '';
    
    if (in_array($koltuk_no, $dolu_koltuklar)) {
        $error = 'Bu koltuk dolu';
    } else {
        $fiyat = $sefer['price'];
        $kupon_id = null;
        
        if ($kupon_kod) {
            $stmt = $db->prepare("SELECT c.* FROM Coupons c 
                                 LEFT JOIN User_Coupons uc ON c.id = uc.coupon_id AND uc.user_id = ?
                                 WHERE c.code = ? AND 
                                 (c.company_id IS NULL OR c.company_id = ?) AND 
                                 c.expire_date >= datetime('now') AND 
                                 c.usage_limit > 0 AND
                                 uc.id IS NULL");
            $stmt->execute([$user['id'], $kupon_kod, $sefer['company_id']]);
            $kupon = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($kupon) {
                $fiyat = $fiyat * (1 - $kupon['discount'] / 100);
                $kupon_id = $kupon['id'];
            }
        }
        
        if ($user['balance'] < $fiyat) {
            $error = 'Yetersiz bakiye';
        } else {
            $db->beginTransaction();
            try {
                $ticket_id = generateId('ticket-');
                $stmt = $db->prepare("INSERT INTO Tickets (id, trip_id, user_id, total_price, status) VALUES (?, ?, ?, ?, 'active')");
                $stmt->execute([$ticket_id, $sefer_id, $user['id'], $fiyat]);
                
                $seat_id = generateId('seat-');
                $stmt = $db->prepare("INSERT INTO Booked_Seats (id, ticket_id, seat_number) VALUES (?, ?, ?)");
                $stmt->execute([$seat_id, $ticket_id, $koltuk_no]);
                
                $stmt = $db->prepare("UPDATE User SET balance = balance - ? WHERE id = ?");
                $stmt->execute([$fiyat, $user['id']]);
                
                if ($kupon_id) {
                    $user_coupon_id = generateId('uc-');
                    $stmt = $db->prepare("INSERT INTO User_Coupons (id, coupon_id, user_id) VALUES (?, ?, ?)");
                    $stmt->execute([$user_coupon_id, $kupon_id, $user['id']]);
                    
                    $stmt = $db->prepare("UPDATE Coupons SET usage_limit = usage_limit - 1 WHERE id = ?");
                    $stmt->execute([$kupon_id]);
                }
                
                $db->commit();
                header('Location: /biletlerim.php');
                exit;
            } catch (Exception $e) {
                $db->rollBack();
                $error = 'Bilet alımı sırasında hata oluştu';
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
    <title>Bilet Satın Al</title>
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
        <div class="card">
            <h2>Bilet Satın Al</h2>
            <div class="user-info">
                <p>Bakiyeniz: <span class="credit-display"><?= number_format($user['balance'], 2) ?> TL</span></p>
            </div>
            
            <div class="sefer-info">
                <h3><?= htmlspecialchars($sefer['firma_adi']) ?></h3>
                <p><?= htmlspecialchars($sefer['departure_city']) ?> - <?= htmlspecialchars($sefer['destination_city']) ?></p>
                <p><?= date('d.m.Y H:i', strtotime($sefer['departure_time'])) ?></p>
                <p><strong>Fiyat: <?= number_format($sefer['price'], 2) ?> TL</strong></p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Koltuk Seçin</label>
                    
                    <div class="otobus-container">
                        <div class="otobus-govde">
                            <div class="sofor-alani">
                                <div class="direksiyon"></div>
                            </div>
                            
                            <div class="koltuk-alani">
                                <?php
                                $total = $sefer['capacity'];
                                $satirlar = 4;
                                $sutunlar = ceil($total / $satirlar);
                                ?>
                                
                                <div class="koltuk-ust-sira">
                                    <?php for ($col = 0; $col < $sutunlar; $col++): ?>
                                        <?php 
                                        $ust_pencere = ($col * 4) + 1;
                                        $ust_koridor = ($col * 4) + 2;
                                        ?>
                                        <?php if ($ust_pencere <= $total): ?>
                                            <?php $dolu = in_array($ust_pencere, $dolu_koltuklar); ?>
                                            <label class="koltuk <?= $dolu ? 'dolu' : 'bos' ?>">
                                                <input type="radio" name="koltuk_no" value="<?= $ust_pencere ?>" 
                                                       <?= $dolu ? 'disabled' : '' ?> required>
                                                <span class="koltuk-no"><?= $ust_pencere ?></span>
                                            </label>
                                        <?php else: ?>
                                            <div class="koltuk-bos-alan"></div>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                
                                <div class="koltuk-ust-koridor">
                                    <?php for ($col = 0; $col < $sutunlar; $col++): ?>
                                        <?php $ust_koridor = ($col * 4) + 2; ?>
                                        <?php if ($ust_koridor <= $total): ?>
                                            <?php $dolu = in_array($ust_koridor, $dolu_koltuklar); ?>
                                            <label class="koltuk <?= $dolu ? 'dolu' : 'bos' ?>">
                                                <input type="radio" name="koltuk_no" value="<?= $ust_koridor ?>" 
                                                       <?= $dolu ? 'disabled' : '' ?> required>
                                                <span class="koltuk-no"><?= $ust_koridor ?></span>
                                            </label>
                                        <?php else: ?>
                                            <div class="koltuk-bos-alan"></div>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                
                                <div class="koridor-yatay"></div>
                                
                                <div class="koltuk-alt-koridor">
                                    <?php for ($col = 0; $col < $sutunlar; $col++): ?>
                                        <?php $alt_koridor = ($col * 4) + 3; ?>
                                        <?php if ($alt_koridor <= $total): ?>
                                            <?php $dolu = in_array($alt_koridor, $dolu_koltuklar); ?>
                                            <label class="koltuk <?= $dolu ? 'dolu' : 'bos' ?>">
                                                <input type="radio" name="koltuk_no" value="<?= $alt_koridor ?>" 
                                                       <?= $dolu ? 'disabled' : '' ?> required>
                                                <span class="koltuk-no"><?= $alt_koridor ?></span>
                                            </label>
                                        <?php else: ?>
                                            <div class="koltuk-bos-alan"></div>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                
                                <div class="koltuk-alt-sira">
                                    <?php for ($col = 0; $col < $sutunlar; $col++): ?>
                                        <?php $alt_pencere = ($col * 4) + 4; ?>
                                        <?php if ($alt_pencere <= $total): ?>
                                            <?php $dolu = in_array($alt_pencere, $dolu_koltuklar); ?>
                                            <label class="koltuk <?= $dolu ? 'dolu' : 'bos' ?>">
                                                <input type="radio" name="koltuk_no" value="<?= $alt_pencere ?>" 
                                                       <?= $dolu ? 'disabled' : '' ?> required>
                                                <span class="koltuk-no"><?= $alt_pencere ?></span>
                                            </label>
                                        <?php else: ?>
                                            <div class="koltuk-bos-alan"></div>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="koltuk-legend">
                            <div class="legend-item">
                                <div class="legend-box bos"></div>
                                <span>Boş Koltuk</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box dolu"></div>
                                <span>Dolu Koltuk</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box secili"></div>
                                <span>Seçilen Koltuk</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Kupon Kodu (Opsiyonel)</label>
                    <input type="text" name="kupon_kod" placeholder="İndirim kuponu varsa giriniz">
                </div>

                <button type="submit" class="btn btn-primary">Satın Al</button>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('.koltuk.bos').forEach(koltuk => {
            koltuk.addEventListener('click', function() {
                document.querySelectorAll('.koltuk').forEach(k => k.classList.remove('secili'));
                this.classList.add('secili');
                this.querySelector('input').checked = true;
            });
        });
    </script>
</body>
</html>
