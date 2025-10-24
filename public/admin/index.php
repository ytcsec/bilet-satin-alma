<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/database.php';
require_once __DIR__ . '/../../src/auth.php';

requireRole('admin');

$db = getDB();

$stmt = $db->query("SELECT * FROM Bus_Company ORDER BY name");
$firmalar = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT u.*, c.name as firma_adi FROM User u 
                    LEFT JOIN Bus_Company c ON u.company_id = c.id 
                    WHERE u.role = 'firma_admin' 
                    ORDER BY u.full_name");
$firma_adminler = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli</title>
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
            <h2>Hoş Geldiniz</h2>
            <p>Admin paneline hoş geldiniz. Buradan firmaları, firma adminlerini ve kuponları yönetebilirsiniz.</p>
        </div>

        <div class="grid-2">
            <div class="card">
                <h3>Firmalar</h3>
                <a href="/admin/firma-yonetimi.php" class="btn btn-primary">Firma Yönetimi</a>
                <p style="margin-top: 15px;">Toplam Firma: <?= count($firmalar) ?></p>
            </div>

            <div class="card">
                <h3>Firma Adminleri</h3>
                <a href="/admin/firma-admin-yonetimi.php" class="btn btn-primary">Firma Admin Yönetimi</a>
                <p style="margin-top: 15px;">Toplam Admin: <?= count($firma_adminler) ?></p>
            </div>
        </div>
    </div>
</body>
</html>
