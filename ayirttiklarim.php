<?php
require_once "db.php";
require_once "includes/auth.php";
require_once "includes/kurallar.php";

require_login($pdo);
ayirtma_sure_kontrol($pdo);

$kullanici_id = (int)$_SESSION["kullanici_id"];

$stmt = $pdo->prepare("
  SELECT a.ayirtma_id, a.tarih, a.durum, k.kitap_adi
  FROM ayirtma a
  JOIN kitaplar k ON k.kitap_id = a.kitap_id
  WHERE a.kullanici_id = :uid
    AND a.durum IN ('bekliyor','iptal')
  ORDER BY a.tarih DESC
");
$stmt->execute([":uid" => $kullanici_id]);
$liste = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sayi = count($liste);

$page_title = "Ayırttıklarım";
$logo_text  = "“Kitap tekrar tekrar açabileceğiniz bir hediyedir.” ~Garrison Keillor";
$top_nav = [
  ["href"=>"panel.php", "icon"=>"fa-solid fa-house", "text"=>"Anasayfa"],
  ["href"=>"cikis.php", "icon"=>"fa-solid fa-right-from-bracket", "text"=>"Çıkış Yap"],
];

require_once "includes/header.php";
?>

<section class="hesap-wrapper">
  <?php $active="ayirt"; require "includes/sidebar.php"; ?>

  <main class="hesap-sag">
    <div class="sekme-icerik">
      <div class="sayfa-baslik">
        <h2>Ayırttıklarım</h2>
        <span class="badge"><?php echo $sayi; ?></span>
      </div>

      <?php if (empty($liste)): ?>
        <p>Kayıt bulunamadı.</p>
      <?php else: ?>
        <table class="odunc-table">
          <tr>
            <th>Kitap</th>
            <th>Tarih</th>
            <th>Durum</th>
            <th>İşlem</th>
          </tr>

          <?php foreach ($liste as $row): ?>
            <tr class="<?php echo ($row["durum"] === "iptal") ? "satir-iptal" : ""; ?>">
              <td><?php echo htmlspecialchars($row["kitap_adi"]); ?></td>
              <td><?php echo htmlspecialchars($row["tarih"]); ?></td>

              <td>
                <?php if ($row["durum"] === "bekliyor"): ?>
                  <span class="badge-durum badge-bekliyor">Bekliyor</span>
                <?php else: ?>
                  <span class="badge-durum badge-iptal">İptal</span>
                <?php endif; ?>
              </td>

              <td>
                <?php if ($row["durum"] === "bekliyor"): ?>
                  <a href="ayirtma_iptal.php?id=<?php echo (int)$row["ayirtma_id"]; ?>"
                     onclick="return confirm('Bu ayırtmayı iptal etmek istiyor musunuz?');"
                     style="color:red;font-weight:600;">
                    İptal Et
                  </a>
                <?php else: ?>
                  -
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

