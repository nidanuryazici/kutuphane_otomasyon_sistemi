<?php
require_once "db.php";
require_once "includes/auth.php";
require_once "includes/kurallar.php";

require_login($pdo);
ayirtma_sure_kontrol($pdo);

$kullanici_id = (int)$_SESSION["kullanici_id"];
$tab = $_GET["tab"] ?? "aktif";

if ($tab === "iade") {
  $sql = "
    SELECT o.odunc_id, k.kitap_adi, o.odunc_tarihi, o.iade_tarihi
    FROM odunc o
    JOIN kitaplar k ON k.kitap_id = o.kitap_id
    WHERE o.kullanici_id = :uid AND o.durum = 'iade'
    ORDER BY o.iade_tarihi DESC
  ";
} elseif ($tab === "arsiv") {
  $sql = "
    SELECT o.odunc_id, k.kitap_adi, o.odunc_tarihi, o.iade_tarihi, o.durum,
           DATEDIFF(o.iade_tarihi, CURDATE()) AS kalan_gun
    FROM odunc o
    JOIN kitaplar k ON k.kitap_id = o.kitap_id
    WHERE o.kullanici_id = :uid
    ORDER BY o.odunc_tarihi DESC
  ";
} else {
  $tab = "aktif";
  $sql = "
    SELECT o.odunc_id, k.kitap_adi, o.odunc_tarihi, o.iade_tarihi,
           DATEDIFF(o.iade_tarihi, CURDATE()) AS kalan_gun
    FROM odunc o
    JOIN kitaplar k ON k.kitap_id = o.kitap_id
    WHERE o.kullanici_id = :uid AND o.durum = 'aktif'
    ORDER BY o.odunc_tarihi DESC
  ";
}

$stmt = $pdo->prepare($sql);
$stmt->execute([":uid" => $kullanici_id]);
$liste = $stmt->fetchAll(PDO::FETCH_ASSOC);
$page_title = "Ödünç / İade";
$logo_text  = "“Kitap tekrar tekrar açabileceğiniz bir hediyedir.” ~Garrison Keillor";
$top_nav = [
  ["href"=>"panel.php", "icon"=>"fa-solid fa-house", "text"=>"Anasayfa"],
  ["href"=>"cikis.php", "icon"=>"fa-solid fa-right-from-bracket", "text"=>"Çıkış Yap"],
];

require_once "includes/header.php";
?>

<section class="hesap-wrapper">
  <?php $active="odunc"; require "includes/sidebar.php"; ?>
  <main class="hesap-sag">

    <div class="sekme-bar">
      <a href="odunc.php?tab=aktif" class="sekme <?php if($tab=="aktif") echo "aktif"; ?>">Üzerimdekiler</a>
      <a href="odunc.php?tab=iade"  class="sekme <?php if($tab=="iade") echo "aktif"; ?>">İadelerim</a>
      <a href="odunc.php?tab=arsiv" class="sekme <?php if($tab=="arsiv") echo "aktif"; ?>">Arşivim</a>
    </div>

    <div class="sekme-icerik">
      <?php if (empty($liste)): ?>
        <p>Kayıt bulunamadı.</p>
      <?php else: ?>
        <table class="odunc-table">
          <tr>
            <th>Kitap</th>
            <th>Ödünç Tarihi</th>
            <th>İade Tarihi</th>

            <?php if ($tab === "aktif" || $tab === "arsiv"): ?>
              <th>Kalan Süre</th>
            <?php endif; ?>

            <?php if ($tab === "arsiv"): ?>
              <th>Durum</th>
            <?php endif; ?>
          </tr>

          <?php foreach ($liste as $row): ?>
            <tr>
              <td><?php echo htmlspecialchars($row["kitap_adi"]); ?></td>
              <td><?php echo htmlspecialchars($row["odunc_tarihi"]); ?></td>
              <td><?php echo htmlspecialchars($row["iade_tarihi"] ?? "-"); ?></td>

              <?php if ($tab === "aktif" || $tab === "arsiv"): ?>
                <td>
                  <?php
                    if (!isset($row["kalan_gun"]) || $row["iade_tarihi"] === null) {
                      echo "-";
                    } else {
                      $kalan = (int)$row["kalan_gun"];
                      echo $kalan >= 0 ? ($kalan . " gün") : ("Gecikti: " . abs($kalan) . " gün");
                    }
                  ?>
                </td>
              <?php endif; ?>
              <?php if ($tab === "arsiv"): ?>
                <td><?php echo htmlspecialchars($row["durum"]); ?></td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
    </div>
  </main>
</section>
</body>
</html>
