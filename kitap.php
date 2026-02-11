<?php
session_start();
require_once "db.php";
$kitap_id = (int)($_GET["id"] ?? 0);
if ($kitap_id <= 0) {
  header("Location: index.php");
  exit;
}
$sql = "
  SELECT
    k.kitap_id,
    k.kitap_adi,
    k.aciklama,
    k.konu,
    k.kat,
    k.kitaplik,
    k.raf,
    k.eklenme_tarihi,
    GROUP_CONCAT(DISTINCT y.yazar_adi SEPARATOR ', ') AS yazarlar
  FROM kitaplar k
  LEFT JOIN kitap_yazar ky ON ky.kitap_id = k.kitap_id
  LEFT JOIN yazarlar y ON y.yazar_id = ky.yazar_id
  WHERE k.kitap_id = :id
  GROUP BY k.kitap_id
  LIMIT 1
";
$stmt = $pdo->prepare($sql);
$stmt->execute([":id" => $kitap_id]);
$kitap = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kitap) {
  header("Location: index.php");
  exit;
}
$loginli = isset($_SESSION["kullanici_id"]);
$page_title = $kitap["kitap_adi"];
$logo_text  = "“Kitap tekrar tekrar açabileceğiniz bir hediyedir.” ~Garrison Keillor";
$top_nav = $loginli
  ? [
      ["href"=>"panel.php", "icon"=>"fa-solid fa-house", "text"=>"Panel"],
      ["href"=>"cikis.php", "icon"=>"fa-solid fa-right-from-bracket", "text"=>"Çıkış Yap"],
    ]
  : [
      ["href"=>"index.php", "icon"=>"fa-solid fa-house", "text"=>"Anasayfa"],
      ["href"=>"giris.php", "icon"=>"fa-regular fa-circle-user", "text"=>"Giriş Yap"],
    ];

require_once "includes/header.php";
?>
<section id="yeni-kitaplar">
  <h2>Kitap Bilgisi</h2>

  <div class="kitap-grid">
    <div class="kitap-kart" style="max-width:900px; margin:0 auto;">
      <h3><?php echo htmlspecialchars($kitap["kitap_adi"]); ?></h3>
      <p class="yazar"><?php echo htmlspecialchars($kitap["yazarlar"] ?: "Yazar bilgisi yok"); ?></p>
      <p class="aciklama" style="font-size:15px; line-height:1.6;">
        <?php echo nl2br(htmlspecialchars($kitap["aciklama"] ?? "")); ?>
      </p>

      <p class="konu-konum" style="margin-top:12px;">
        <strong>Konu:</strong> <?php echo htmlspecialchars($kitap["konu"] ?? "-"); ?><br>
        <strong>Konum:</strong>
        <?php echo htmlspecialchars(($kitap["kat"] ?? "-")." / ".($kitap["kitaplik"] ?? "-")." / ".($kitap["raf"] ?? "-")); ?>
      </p>

      <?php if ($loginli): ?>
        <div class="kitap-aksiyon" style="margin-top:14px;">
          <a class="btn-mini" href="favori_ekle.php?id=<?php echo (int)$kitap["kitap_id"]; ?>&return=<?php echo urlencode("kitap.php?id=".$kitap_id); ?>">
            Favoriye Ekle
          </a>
          <a class="btn-mini btn-outline" href="ayirt.php?id=<?php echo (int)$kitap["kitap_id"]; ?>&return=<?php echo urlencode("kitap.php?id=".$kitap_id); ?>">
            Ayırt
          </a>
        </div>
      <?php else: ?>
        <div style="margin-top:14px; text-align:center;">
          <a class="btn-login" style="display:inline-block; text-decoration:none;"
             href="giris.php?return=<?php echo urlencode("kitap.php?id=".$kitap_id); ?>">
            Ayırt / Favori için giriş yap
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
</body>
</html>

