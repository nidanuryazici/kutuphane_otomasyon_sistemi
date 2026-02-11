<?php
require_once "db.php";
require_once "includes/auth.php";
require_once "includes/kurallar.php";

require_login($pdo);
ayirtma_sure_kontrol($pdo);

$kullanici_id = (int)$_SESSION["kullanici_id"];
$kitap_id     = (int)($_GET["id"] ?? 0);

$return = trim($_GET["return"] ?? "");
$geri   = $return !== "" ? $return : ($_SERVER["HTTP_REFERER"] ?? "ayirttiklarim.php");

if ($kitap_id <= 0) {
  header("Location: " . $geri);
  exit;
}

$kontrol = $pdo->prepare("
  SELECT 1
  FROM ayirtma
  WHERE kullanici_id = :uid AND kitap_id = :kid AND durum = 'bekliyor'
  LIMIT 1
");
$kontrol->execute([":uid" => $kullanici_id, ":kid" => $kitap_id]);

if ($kontrol->fetchColumn()) {
  header("Location: ayirttiklarim.php?err=zaten");
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
  header("Location: ayirttiklarim.php?err=limit");
  exit;
}

$stmt = $pdo->prepare("SELECT stok_mevcut FROM kitaplar WHERE kitap_id=:kid");
$stmt->execute([":kid" => $kitap_id]);
$stok = $stmt->fetchColumn();

if ($stok === false || (int)$stok <= 0) {
  header("Location: " . $geri . (str_contains($geri, "?") ? "&" : "?") . "err=stok");
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
    header("Location: " . $geri . (str_contains($geri, "?") ? "&" : "?") . "err=stok");
    exit;
  }

  $pdo->commit();
  header("Location: " . $geri);
  exit;

} catch (Throwable $e) {
  $pdo->rollBack();
  header("Location: ayirttiklarim.php?err=1");
  exit;
}
