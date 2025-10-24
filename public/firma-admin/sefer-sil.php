<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/database.php';
require_once __DIR__ . '/../../src/auth.php';

requireRole('firma_admin');

$db = getDB();
$user = getCurrentUser();
$sefer_id = $_GET['id'] ?? 0;

$stmt = $db->prepare("DELETE FROM Trips WHERE id = ? AND company_id = ?");
$stmt->execute([$sefer_id, $user['company_id']]);

header('Location: /firma-admin/index.php');
exit;
