<?php
require_once "db.php";
require_once "includes/auth.php";
require_login($pdo);
$kullanici_id = (int)$_SESSION["kullanici_id"];
$stmt = $pdo->prepare("
  SELECT
    f.kitap_id,
    f.tarih,
    f.durum,
    f.kaldirma_tarihi,
    k.kitap_adi,
    GROUP_CONCAT(DISTINCT y.yazar_adi SEPARATOR ', ') AS yazarlar
  FROM favoriler f
  JOIN kitaplar k ON k.kitap_id = f.kitap_id
  LEFT JOIN kitap_yazar ky ON ky.kitap_id = k.kitap_id
  LEFT JOIN yazarlar y ON y.yazar_id = ky.yazar_id
  WHERE f.kullanici_id = :uid
  GROUP BY f.kitap_id
  ORDER BY f.tarih DESC
");
$stmt->execute([":uid" => $kullanici_id]);
$liste = $stmt->fetchAll(PDO::FETCH_ASSOC);

$aktif_sayi = 0;
foreach ($liste as $x) {
  if (($x["durum"] ?? "aktif") === "aktif") $aktif_sayi++;
}

$page_title = "Favorilerim";
$logo_text  = "“Kitap tekrar tekrar açabileceğiniz bir hediyedir.” ~Garrison Keillor";
$top_nav = [
  ["href"=>"panel.php", "icon"=>"fa-solid fa-house", "text"=>"Anasayfa"],
  ["href"=>"cikis.php", "icon"=>"fa-solid fa-right-from-bracket", "text"=>"Çıkış Yap"],
];

require_once "includes/header.php";
?>

<section class="hesap-wrapper">
  <?php $active="favoriler"; require "includes/sidebar.php"; ?>

  <main class="hesap-sag">
    <div class="sekme-icerik">
      <div class="sayfa-baslik">
        <h2>Favorilerim</h2>
        <span class="badge"><?php echo $aktif_sayi; ?></span>
      </div>

      <?php if (empty($liste)): ?>
        <p>Kayıt bulunamadı.</p>
      <?php else: ?>
        <table class="odunc-table">
          <tr>
            <th>Kitap</th>
            <th>Yazar</th>
            <th>Eklenme Tarihi</th>
            <th>Durum</th>
            <th>İşlem</th>
          </tr>

          <?php foreach ($liste as $row): ?>
            <tr class="<?php echo (($row["durum"] ?? "aktif") === "kaldirildi") ? "satir-iptal" : ""; ?>">
              <td><?php echo htmlspecialchars($row["kitap_adi"]); ?></td>
              <td><?php echo htmlspecialchars($row["yazarlar"] ?: "Yazar bilgisi yok"); ?></td>
              <td><?php echo htmlspecialchars($row["tarih"]); ?></td>

              <td>
                <?php if (($row["durum"] ?? "aktif") === "aktif"): ?>
                  <span class="badge-durum badge-bekliyor">Aktif</span>
                <?php else: ?>
                  <span class="badge-durum badge-iptal">Kaldırıldı</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if (($row["durum"] ?? "aktif") === "aktif"): ?>
                  <a href="favori_sil.php?id=<?php echo (int)$row["kitap_id"]; ?>"
                     onclick="return confirm('Favorilerden kaldırmak istiyor musunuz?');"
                     style="color:red;font-weight:600;">Kaldır</a>
                <?php else: ?>
                  <a href="favori_ekle.php?id=<?php echo (int)$row["kitap_id"]; ?>"
                     style="color:blueviolet;font-weight:600;">Tekrar Ekle</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
    </div>
  </main>
</section>
</body>
</html>

