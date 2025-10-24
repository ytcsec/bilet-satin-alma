<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/../src/auth.php';

requireLogin();

$db = getDB();
$user = getCurrentUser();
$bilet_id = $_GET['id'] ?? 0;

$stmt = $db->prepare("SELECT tk.*, t.departure_city, t.destination_city, t.departure_time, t.arrival_time, 
                      c.name as firma_adi, u.full_name as yolcu_adi, bs.seat_number
                      FROM Tickets tk 
                      JOIN Trips t ON tk.trip_id = t.id 
                      JOIN Bus_Company c ON t.company_id = c.id 
                      JOIN User u ON tk.user_id = u.id 
                      JOIN Booked_Seats bs ON bs.ticket_id = tk.id
                      WHERE tk.id = ? AND tk.user_id = ?");
$stmt->execute([$bilet_id, $user['id']]);
$bilet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bilet) {
    header('Location: /biletlerim.php');
    exit;
}

function turkishToAscii($text) {
    $turkish = array('ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç');
    $ascii = array('i', 'g', 'u', 's', 'o', 'c', 'I', 'G', 'U', 'S', 'O', 'C');
    return str_replace($turkish, $ascii, $text);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilet - <?= $bilet_id ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .bilet-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border: 3px solid #FF8C00;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .bilet-header {
            background: linear-gradient(135deg, #FF8C00 0%, #FFA500 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .bilet-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .bilet-no {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .bilet-body {
            padding: 40px;
        }
        
        .firma-info {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px dashed #FFE4B5;
        }
        
        .firma-info h2 {
            color: #FF8C00;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .route-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 30px 0;
            padding: 20px;
            background: #FFF8DC;
            border-radius: 10px;
        }
        
        .route-point {
            flex: 1;
            text-align: center;
        }
        
        .route-point .city {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }
        
        .route-point .time {
            font-size: 20px;
            color: #FF8C00;
            font-weight: bold;
        }
        
        .route-point .date {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        .route-arrow {
            flex: 0 0 100px;
            text-align: center;
            font-size: 40px;
            color: #FF8C00;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 30px;
        }
        
        .info-item {
            padding: 15px;
            background: #FFFAF0;
            border-radius: 8px;
            border-left: 4px solid #FF8C00;
        }
        
        .info-item label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .info-item .value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        .bilet-footer {
            background: #FFF8DC;
            padding: 20px 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        
        .print-button {
            text-align: center;
            margin: 20px 0;
        }
        
        .btn-print {
            background: linear-gradient(135deg, #FF8C00 0%, #FFA500 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 140, 0, 0.4);
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .print-button {
                display: none;
            }
            
            .bilet-container {
                box-shadow: none;
                border: 2px solid #FF8C00;
            }
        }
    </style>
</head>
<body>
    <div class="print-button">
        <button onclick="window.print()" class="btn-print">Yazdır / PDF Olarak Kaydet</button>
        <a href="/biletlerim.php" class="btn-print" style="background: #6c757d; margin-left: 10px;">Geri Dön</a>
    </div>
    
    <div class="bilet-container">
        <div class="bilet-header">
            <h1>OTOBÜS BİLETİ</h1>
            <div class="bilet-no">Bilet No: <?= htmlspecialchars($bilet['id']) ?></div>
        </div>
        
        <div class="bilet-body">
            <div class="firma-info">
                <h2><?= htmlspecialchars($bilet['firma_adi']) ?></h2>
            </div>
            
            <div class="route-container">
                <div class="route-point">
                    <div class="city"><?= htmlspecialchars($bilet['departure_city']) ?></div>
                    <div class="time"><?= date('H:i', strtotime($bilet['departure_time'])) ?></div>
                    <div class="date"><?= date('d.m.Y', strtotime($bilet['departure_time'])) ?></div>
                </div>
                
                <div class="route-arrow">→</div>
                
                <div class="route-point">
                    <div class="city"><?= htmlspecialchars($bilet['destination_city']) ?></div>
                    <div class="time"><?= date('H:i', strtotime($bilet['arrival_time'])) ?></div>
                    <div class="date"><?= date('d.m.Y', strtotime($bilet['arrival_time'])) ?></div>
                </div>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <label>Yolcu Adı</label>
                    <div class="value"><?= htmlspecialchars($bilet['yolcu_adi']) ?></div>
                </div>
                
                <div class="info-item">
                    <label>Koltuk No</label>
                    <div class="value"><?= $bilet['seat_number'] ?></div>
                </div>
                
                <div class="info-item">
                    <label>Ücret</label>
                    <div class="value"><?= number_format($bilet['total_price'], 2) ?> TL</div>
                </div>
                
                <div class="info-item">
                    <label>Durum</label>
                    <div class="value"><?= $bilet['status'] === 'active' ? 'Aktif' : 'İptal Edildi' ?></div>
                </div>
            </div>
        </div>
        
        <div class="bilet-footer">
            Bilet Alım Tarihi: <?= date('d.m.Y H:i', strtotime($bilet['created_at'])) ?><br>
            Bu bilet kişiye özeldir ve devredilemez. Yolculuk sırasında kimlik ibraz edilmelidir.
        </div>
    </div>
</body>
</html>
