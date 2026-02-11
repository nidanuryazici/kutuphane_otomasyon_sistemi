<?php
require_once "db.php";
require_once "includes/auth.php";
require_once "includes/kurallar.php";
require_login($pdo);
ayirtma_sure_kontrol($pdo);
$kullanici_id = (int)$_SESSION["kullanici_id"];

$basari = "";
$hata = "";

if (isset($_GET["ok"])) {
  if ($_GET["ok"] === "1") $basari = "Bağış bildiriminiz alındı. Teşekkürler!";
  if ($_GET["ok"] === "iptal") $basari = "Bağış isteğiniz iptal edildi.";
}
if (isset($_GET["err"])) {
  $hata = "İşlem sırasında hata oluştu.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $kitap_adi = trim($_POST["kitap_adi"] ?? "");
  $yazar_adi = trim($_POST["yazar_adi"] ?? "");
  $adet      = (int)($_POST["adet"] ?? 1);
  $aciklama  = trim($_POST["aciklama"] ?? "");

  if ($kitap_adi === "") {
    $hata = "Kitap adı zorunludur.";
  } elseif ($adet < 1) {
    $hata = "Adet en az 1 olmalıdır.";
  } else {
    $stmt = $pdo->prepare("
      INSERT INTO bagislar (kullanici_id, kitap_adi, yazar_adi, adet, aciklama)
      VALUES (:uid, :ka, :ya, :ad, :ac)
    ");
    $stmt->execute([
      ":uid" => $kullanici_id,
      ":ka"  => $kitap_adi,
      ":ya"  => ($yazar_adi === "" ? null : $yazar_adi),
      ":ad"  => $adet,
      ":ac"  => ($aciklama === "" ? null : $aciklama),
    ]);

    header("Location: bagislarim.php?ok=1");
    exit;
  }
}

$stmt = $pdo->prepare("
  SELECT bagis_id, kitap_adi, yazar_adi, adet, aciklama, tarih, durum
  FROM bagislar
  WHERE kullanici_id = :uid
  ORDER BY tarih DESC
");
$stmt->execute([":uid" => $kullanici_id]);
$liste = $stmt->fetchAll(PDO::FETCH_ASSOC);
$sayi = count($liste);

$page_title = "Bağışlarım";
$logo_text  = "“Kitap tekrar tekrar açabileceğiniz bir hediyedir.” ~Garrison Keillor";
$top_nav = [
  ["href"=>"panel.php", "icon"=>"fa-solid fa-house", "text"=>"Anasayfa"],
  ["href"=>"cikis.php", "icon"=>"fa-solid fa-right-from-bracket", "text"=>"Çıkış Yap"],
];

require_once "includes/header.php";
?>

<section class="hesap-wrapper">
  <?php $active="bagislar"; require "includes/sidebar.php"; ?>

  <main class="hesap-sag">
    <div class="sekme-icerik">

      <div class="sayfa-baslik">
        <h2>Bağışlarım</h2>
        <span class="badge"><?php echo $sayi; ?></span>
      </div>

      <?php if ($basari): ?>
        <div class="alert success"><?php echo htmlspecialchars($basari); ?></div>
      <?php endif; ?>

      <?php if ($hata): ?>
        <div class="alert error"><?php echo htmlspecialchars($hata); ?></div>
      <?php endif; ?>

      <form method="post" class="soru-form">
        <label style="display:block;margin:10px 0 6px;font-weight:600;">Kitap Adı *</label>
        <input type="text" name="kitap_adi" required
               style="width:100%;padding:12px;border-radius:12px;border:1px solid #ccc;">

        <div style="display:flex; gap:12px; margin-top:10px;">
          <div style="flex:1;">
            <label style="display:block;margin:0 0 6px;font-weight:600;">Yazar Adı</label>
            <input type="text" name="yazar_adi"
                   style="width:100%;padding:12px;border-radius:12px;border:1px solid #ccc;">
          </div>

          <div style="width:140px;">
            <label style="display:block;margin:0 0 6px;font-weight:600;">Adet</label>
            <input type="number" name="adet" value="1" min="1"
                   style="width:100%;padding:12px;border-radius:12px;border:1px solid #ccc;">
          </div>
        </div>

        <label style="display:block;margin:10px 0 6px;font-weight:600;">Açıklama</label>
        <textarea name="aciklama" rows="4" placeholder="Kitapların durumu, baskı yılı vb..."></textarea>

        <button type="submit" class="btn-login" style="margin-top:12px;">Bağış Bildir</button>
      </form>

      <hr class="cizgi">

      <h3 style="margin:0 0 10px 0;">Bağış Kayıtlarım</h3>

      <?php if (empty($liste)): ?>
        <p>Kayıt bulunamadı.</p>
      <?php else: ?>
        <table class="odunc-table">
          <tr>
            <th>Kitap</th>
            <th>Yazar</th>
            <th>Adet</th>
            <th>Tarih</th>
            <th>Durum</th>
            <th>İşlem</th>
          </tr>

          <?php foreach ($liste as $r): ?>
            <tr class="<?php echo ($r["durum"] === "iptal") ? "satir-iptal" : ""; ?>">
              <td><?php echo htmlspecialchars($r["kitap_adi"]); ?></td>
              <td><?php echo htmlspecialchars($r["yazar_adi"] ?? "-"); ?></td>
              <td><?php echo (int)$r["adet"]; ?></td>
              <td><?php echo htmlspecialchars($r["tarih"]); ?></td>

              <td>
                <?php if ($r["durum"] === "bekliyor"): ?>
                  <span class="badge-durum badge-bekliyor">Bekliyor</span>
                <?php elseif ($r["durum"] === "teslim_alindi"): ?>
                  <span class="badge-durum badge-teslim">Teslim Alındı</span>
                <?php elseif ($r["durum"] === "reddedildi"): ?>
                  <span class="badge-durum badge-red">Reddedildi</span>
                <?php elseif ($r["durum"] === "iptal"): ?>
                  <span class="badge-durum badge-iptal">İptal</span>
                <?php else: ?>
                  <span class="badge-durum"><?php echo htmlspecialchars($r["durum"]); ?></span>
                <?php endif; ?>
              </td>

              <td>
                <?php if ($r["durum"] === "bekliyor"): ?>
                  <a href="bagis_iptal.php?id=<?php echo (int)$r["bagis_id"]; ?>"
                     onclick="return confirm('Bu bağış isteğini iptal etmek istiyor musunuz?');"
                     style="color:red;font-weight:600;">İptal Et</a>
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

