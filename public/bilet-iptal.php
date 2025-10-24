<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/../src/auth.php';

requireLogin();

$db = getDB();
$user = getCurrentUser();
$bilet_id = $_GET['id'] ?? 0;

$stmt = $db->prepare("SELECT tk.*, t.departure_time FROM Tickets tk 
                      JOIN Trips t ON tk.trip_id = t.id 
                      WHERE tk.id = ? AND tk.user_id = ? AND tk.status = 'active'");
$stmt->execute([$bilet_id, $user['id']]);
$bilet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bilet) {
    header('Location: /biletlerim.php');
    exit;
}

date_default_timezone_set('Europe/Istanbul');

$sefer_zamani = strtotime($bilet['departure_time']);
$simdi = time();
$fark_saniye = $sefer_zamani - $simdi;
$fark_saat = $fark_saniye / 3600;

if ($fark_saat < 1) {
    $kalan = round($fark_saat * 60);
    if ($kalan > 0) {
        $_SESSION['error'] = "Kalkışa sadece {$kalan} dakika kaldığı için bilet iptal edilemez (En az 1 saat öncesinden iptal edilmelidir)";
    } else {
        $_SESSION['error'] = 'Sefer başladığı veya geçtiği için bilet iptal edilemez';
    }
    header('Location: /biletlerim.php');
    exit;
}

$db->beginTransaction();
try {
    $stmt = $db->prepare("UPDATE Tickets SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$bilet_id]);
    
    $stmt = $db->prepare("UPDATE User SET balance = balance + ? WHERE id = ?");
    $stmt->execute([$bilet['total_price'], $user['id']]);
    
    $db->commit();
    $_SESSION['success'] = 'Bilet başarıyla iptal edildi ve ücret iade edildi';
} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['error'] = 'İptal işlemi sırasında hata oluştu';
}

header('Location: /biletlerim.php');
exit;
