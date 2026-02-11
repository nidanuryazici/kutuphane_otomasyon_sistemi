<?php
require_once "db.php";
require_once "includes/auth.php";

require_login($pdo);

$kullanici_id = (int)$_SESSION["kullanici_id"];

$basari = "";
$hata   = "";

if (($_GET["ok"] ?? "") === "iptal") {
  $basari = "Yayın isteğiniz iptal edildi.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $kitap_adi = trim($_POST["kitap_adi"] ?? "");
  $yazar_adi = trim($_POST["yazar_adi"] ?? "");
  $aciklama  = trim($_POST["aciklama"] ?? "");

  if ($kitap_adi === "") {
    $hata = "Kitap adı zorunludur.";
  } else {
    $stmt = $pdo->prepare("
      INSERT INTO yayin_istekleri (kullanici_id, kitap_adi, yazar_adi, aciklama)
      VALUES (:uid, :ka, :ya, :ac)
    ");
    $stmt->execute([
      ":uid" => $kullanici_id,
      ":ka"  => $kitap_adi,
      ":ya"  => ($yazar_adi === "" ? null : $yazar_adi),
      ":ac"  => ($aciklama === "" ? null : $aciklama),
    ]);

    $basari = "Yayın isteğiniz alındı. Teşekkürler!";
  }
}

$stmt = $pdo->prepare("
  SELECT istek_id, kitap_adi, yazar_adi, aciklama, tarih, durum
  FROM yayin_istekleri
  WHERE kullanici_id = :uid
  ORDER BY tarih DESC
");
$stmt->execute([":uid" => $kullanici_id]);
$liste = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sayi = count($liste);

$page_title = "Yayın İstek";
$logo_text  = "“Bir kitap, sadece raflarda değil; insanın hayatında da yer açar.”";
$top_nav = [
  ["href"=>"panel.php", "icon"=>"fa-solid fa-house", "text"=>"Anasayfa"],
  ["href"=>"cikis.php", "icon"=>"fa-solid fa-right-from-bracket", "text"=>"Çıkış Yap"],
];

require_once "includes/header.php";
?>

<section class="hesap-wrapper">

  <?php $active="yayin"; require "includes/sidebar.php"; ?>

  <main class="hesap-sag">
    <div class="sekme-icerik">

      <div class="sayfa-baslik">
        <h2>Yayın İstek</h2>
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

        <label style="display:block;margin:10px 0 6px;font-weight:600;">Yazar Adı</label>
        <input type="text" name="yazar_adi"
               style="width:100%;padding:12px;border-radius:12px;border:1px solid #ccc;">

        <label style="display:block;margin:10px 0 6px;font-weight:600;">Açıklama</label>
        <textarea name="aciklama" rows="4" placeholder="Neden öneriyorsunuz? Baskı yılı, yayınevi vb..."></textarea>

        <button type="submit" class="btn-login" style="margin-top:12px;">İstek Gönder</button>
      </form>

      <hr class="cizgi">

      <h3 style="margin:0 0 10px 0;">İsteklerim</h3>

      <?php if (empty($liste)): ?>
        <p>Kayıt bulunamadı.</p>
      <?php else: ?>
        <table class="odunc-table">
          <tr>
            <th>Kitap</th>
            <th>Yazar</th>
            <th>Tarih</th>
            <th>Durum</th>
            <th>İşlem</th>
          </tr>

          <?php foreach ($liste as $r): ?>
            <?php $iptal = ($r["durum"] === "iptal"); ?>
            <tr class="<?php echo $iptal ? "satir-iptal" : ""; ?>">
              <td><?php echo htmlspecialchars($r["kitap_adi"]); ?></td>
              <td><?php echo htmlspecialchars($r["yazar_adi"] ?? "-"); ?></td>
              <td><?php echo htmlspecialchars($r["tarih"]); ?></td>

              <td>
                <?php if ($r["durum"] === "bekliyor"): ?>
                  <span class="badge-durum badge-bekliyor">Bekliyor</span>
                <?php elseif ($r["durum"] === "incelemede"): ?>
                  <span class="badge-durum">İncelemede</span>
                <?php elseif ($r["durum"] === "kabul"): ?>
                  <span class="badge-durum">Kabul</span>
                <?php elseif ($r["durum"] === "reddedildi"): ?>
                  <span class="badge-durum">Reddedildi</span>
                <?php elseif ($r["durum"] === "iptal"): ?>
                  <span class="badge-durum badge-iptal">İptal</span>
                <?php else: ?>
                  <span class="badge-durum">-</span>
                <?php endif; ?>
              </td>

              <td>
                <?php if ($r["durum"] === "bekliyor"): ?>
                  <a href="yayin_istek_sil.php?id=<?php echo (int)$r["istek_id"]; ?>"
                     onclick="return confirm('Bu yayın isteğini iptal etmek istiyor musunuz?');"
                     style="color:red;font-weight:600;text-decoration:none;">
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
