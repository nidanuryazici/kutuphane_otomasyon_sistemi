<?php
require_once "db.php";
require_once "includes/auth.php";
require_login($pdo);
$kullanici_id = (int)$_SESSION["kullanici_id"];
$bagis_id      = (int)($_GET["id"] ?? 0);

if ($bagis_id <= 0) {
  header("Location: bagislarim.php");
  exit;
}

$stmt = $pdo->prepare("
  UPDATE bagislar
  SET durum = 'iptal'
  WHERE bagis_id = :bid
    AND kullanici_id = :uid
    AND durum = 'bekliyor'
");
$stmt->execute([":bid" => $bagis_id, ":uid" => $kullanici_id]);
header("Location: bagislarim.php?ok=iptal");
exit;
