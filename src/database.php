<?php

require_once __DIR__ . '/config.php';

function getDB() {
    try {
        $dbExists = file_exists(DB_PATH) && filesize(DB_PATH) > 0;
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        if (!$dbExists) {
            $sql = file_get_contents(__DIR__ . '/../database/init.sql');
            $db->exec($sql);
        } else {
            $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='Trips'");
            if (!$result->fetch()) {
                unlink(DB_PATH);
                $db = new PDO('sqlite:' . DB_PATH);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $sql = file_get_contents(__DIR__ . '/../database/init.sql');
                $db->exec($sql);
            }
        }
        
        return $db;
    } catch (PDOException $e) {
        die("Veritabanı bağlantı hatası: " . $e->getMessage());
    }
}
