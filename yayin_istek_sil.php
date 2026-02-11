<?php
require_once "db.php";
require_once "includes/auth.php";

require_login($pdo);

$kullanici_id = (int)$_SESSION["kullanici_id"];
$istek_id = (int)($_GET["id"] ?? 0);

if ($istek_id > 0) {
  $stmt = $pdo->prepare("
    UPDATE yayin_istekleri
    SET durum = 'iptal'
    WHERE istek_id = :id
      AND kullanici_id = :uid
      AND durum = 'bekliyor'
  ");
  $stmt->execute([":id" => $istek_id, ":uid" => $kullanici_id]);
}

header("Location: yayin_istek.php?ok=iptal");
exit;
