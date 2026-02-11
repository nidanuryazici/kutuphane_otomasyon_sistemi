<?php
require_once "db.php";
require_once "includes/auth.php";
$return = $_GET["return"] ?? ($_SERVER["HTTP_REFERER"] ?? "arama.php");
if (!isset($_SESSION["kullanici_id"])) {
  $_SESSION["redirect_after_login"] = $return;
}
require_login($pdo);
$kullanici_id = (int)$_SESSION["kullanici_id"];
$kitap_id     = (int)($_GET["id"] ?? 0);
if ($kitap_id > 0) {
  $stmt = $pdo->prepare("
    INSERT INTO favoriler (kullanici_id, kitap_id, durum, tarih)
    VALUES (:uid, :kid, 'aktif', NOW())
    ON DUPLICATE KEY UPDATE
      durum = 'aktif',
      tarih = NOW(),
      kaldirma_tarihi = NULL
  ");
  $stmt->execute([":uid" => $kullanici_id, ":kid" => $kitap_id]);
}
header("Location: " . $return);
exit;
