<?php
session_start();
require_once "db.php";
require_once "includes/kurallar.php";

$return = $_GET["return"] ?? ($_SERVER["HTTP_REFERER"] ?? "arama.php");

if (!isset($_SESSION["kullanici_id"])) {
  $_SESSION["redirect_after_login"] = $return;
  header("Location: giris.php");
  exit;
}

ayirtma_sure_kontrol($pdo);

$kullanici_id = (int)$_SESSION["kullanici_id"];
$kitap_id = (int)($_GET["id"] ?? 0);

if ($kitap_id <= 0) {
  header("Location: " . $return);
  exit;
}

$kontrol = $pdo->prepare("
  SELECT 1
  FROM ayirtma
  WHERE kullanici_id = :uid AND kitap_id = :kid AND durum = 'bekliyor'
  LIMIT 1
");
$kontrol->execute([":uid" => $kullanici_id, ":kid" => $kitap_id]);

if ($kontrol->fetch()) {
  header("Location: " . $return);
  exit;
}

$stmt = $pdo->prepare("
  SELECT COUNT(*)
  FROM ayirtma
  WHERE kullanici_id = :uid AND durum='bekliyor'
");
$stmt->execute([":uid" => $kullanici_id]);
$bekleyen = (int)$stmt->fetchColumn();

if ($bekleyen >= 2) {
  header("Location: " . $return);
  exit;
}

$stmt = $pdo->prepare("SELECT stok_mevcut FROM kitaplar WHERE kitap_id=:kid");
$stmt->execute([":kid" => $kitap_id]);
$stok = $stmt->fetchColumn();

if ($stok === false || (int)$stok <= 0) {
  header("Location: " . $return);
  exit;
}

$pdo->beginTransaction();
try {
  $ins = $pdo->prepare("
    INSERT INTO ayirtma (kullanici_id, kitap_id, durum)
    VALUES (:uid, :kid, 'bekliyor')
  ");
  $ins->execute([":uid" => $kullanici_id, ":kid" => $kitap_id]);

  $up = $pdo->prepare("
    UPDATE kitaplar
    SET stok_mevcut = stok_mevcut - 1
    WHERE kitap_id = :kid AND stok_mevcut > 0
  ");
  $up->execute([":kid" => $kitap_id]);

  if ($up->rowCount() === 0) {
    $pdo->rollBack();
    header("Location: " . $return);
    exit;
  }

  $pdo->commit();
  header("Location: " . $return);
  exit;

} catch (Throwable $e) {
  $pdo->rollBack();
  header("Location: " . $return);
  exit;
}
