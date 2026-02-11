<?php
require_once "db.php";
require_once "includes/auth.php";
require_once "includes/kurallar.php";

require_login($pdo);
ayirtma_sure_kontrol($pdo);

$kullanici_id = (int)$_SESSION["kullanici_id"];
$ayirtma_id   = (int)($_GET["id"] ?? 0);

if ($ayirtma_id <= 0) {
  header("Location: ayirttiklarim.php");
  exit;
}

$stmt = $pdo->prepare("
  SELECT kitap_id
  FROM ayirtma
  WHERE ayirtma_id = :aid AND kullanici_id = :uid AND durum='bekliyor'
  LIMIT 1
");
$stmt->execute([":aid" => $ayirtma_id, ":uid" => $kullanici_id]);
$kitap_id = $stmt->fetchColumn();

if (!$kitap_id) {
  header("Location: ayirttiklarim.php?err=bulunamadi");
  exit;
}

$pdo->beginTransaction();

try {
  $up = $pdo->prepare("
    UPDATE ayirtma
    SET durum='iptal'
    WHERE ayirtma_id = :aid AND kullanici_id = :uid AND durum='bekliyor'
  ");
  $up->execute([":aid" => $ayirtma_id, ":uid" => $kullanici_id]);

  if ($up->rowCount() > 0) {
    $pdo->prepare("
      UPDATE kitaplar
      SET stok_mevcut = stok_mevcut + 1
      WHERE kitap_id = :kid
    ")->execute([":kid" => (int)$kitap_id]);
  }

  $pdo->commit();
  header("Location: ayirttiklarim.php?ok=iptal");
  exit;

} catch (Throwable $e) {
  $pdo->rollBack();
  header("Location: ayirttiklarim.php?err=1");
  exit;
}
