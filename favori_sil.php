<?php
require_once "db.php";
require_once "includes/auth.php";
require_login($pdo);
$kullanici_id = (int)$_SESSION["kullanici_id"];
$kitap_id     = (int)($_GET["id"] ?? 0);
if ($kitap_id > 0) {
  $stmt = $pdo->prepare("
    UPDATE favoriler
    SET durum = 'kaldirildi', kaldirma_tarihi = NOW()
    WHERE kullanici_id = :uid AND kitap_id = :kid AND durum = 'aktif'
  ");
  $stmt->execute([":uid" => $kullanici_id, ":kid" => $kitap_id]);
}
header("Location: favorilerim.php");
exit;
