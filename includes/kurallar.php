<?php
function ayirtma_sure_kontrol(PDO $pdo): void
{
  $pdo->beginTransaction();
  try {
    $stmt = $pdo->query("
      SELECT kitap_id, COUNT(*) AS adet
      FROM ayirtma
      WHERE durum = 'bekliyor'
        AND tarih < (NOW() - INTERVAL 3 DAY)
      GROUP BY kitap_id
    ");
    $stoklar = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($stoklar)) {
      $pdo->commit();
      return;
    }
    $pdo->exec("
      UPDATE ayirtma
      SET durum = 'iptal'
      WHERE durum = 'bekliyor'
        AND tarih < (NOW() - INTERVAL 3 DAY)
    ");
    $up = $pdo->prepare("
      UPDATE kitaplar
      SET stok_mevcut = stok_mevcut + :adet
      WHERE kitap_id = :kid
    ");
    foreach ($stoklar as $r) {
      $up->execute([
        ":adet" => (int)$r["adet"],
        ":kid"  => (int)$r["kitap_id"],
      ]);
    }
    $pdo->commit();
  } catch (Throwable $e) {
    $pdo->rollBack();
  }
}
