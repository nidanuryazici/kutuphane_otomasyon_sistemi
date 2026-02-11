<?php
require_once "db.php";
require_once "includes/auth.php";
require_login($pdo);
$kullanici_id = (int)$_SESSION["kullanici_id"];
$soru_id = (int)($_GET["id"] ?? 0);

if ($soru_id > 0) {
  $stmt = $pdo->prepare("
    UPDATE sorular
    SET durum = 'silindi'
    WHERE soru_id = :sid
      AND kullanici_id = :uid
      AND durum = 'aktif'
      AND (cevap_metni IS NULL OR cevap_metni = '')
  ");
  $stmt->execute([":sid" => $soru_id, ":uid" => $kullanici_id]);
}

header("Location: kutuphaneciye_sor.php");
exit;
