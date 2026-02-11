<?php
require_once "db.php";
require_once "includes/auth.php";
require_login($pdo);
$kullanici_id = (int)$_SESSION["kullanici_id"];
$mesaj = "";
$hata = ""
$edit_id = (int)($_GET["edit"] ?? 0);
$edit_soru = "";

if ($edit_id > 0) {
  $st = $pdo->prepare("
    SELECT soru_metni
    FROM sorular
    WHERE soru_id = :sid
      AND kullanici_id = :uid
      AND durum = 'aktif'
      AND (cevap_metni IS NULL OR cevap_metni = '')
    LIMIT 1
  ");
  $st->execute([":sid" => $edit_id, ":uid" => $kullanici_id]);
  $edit_soru = (string)$st->fetchColumn();

  if ($edit_soru === "") {
    $edit_id = 0;
  }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $soru = trim($_POST["soru_metni"] ?? "");
  $soru_id_post = (int)($_POST["soru_id"] ?? 0);

  if ($soru === "") {
    $hata = "Soru metni boş olamaz.";
  } else {
    if ($soru_id_post > 0) {
      $up = $pdo->prepare("
        UPDATE sorular
        SET soru_metni = :metin,
            duzenlendi_tarihi = NOW()
        WHERE soru_id = :sid
          AND kullanici_id = :uid
          AND durum = 'aktif'
          AND (cevap_metni IS NULL OR cevap_metni = '')
      ");
      $up->execute([
        ":metin" => $soru,
        ":sid" => $soru_id_post,
        ":uid" => $kullanici_id
      ]);

      if ($up->rowCount() > 0) $mesaj = "Sorunuz güncellendi.";
      else $hata = "Bu soru düzenlenemiyor (cevaplanmış veya silinmiş olabilir).";
    } else {
      $ins = $pdo->prepare("
        INSERT INTO sorular (kullanici_id, soru_metni, durum)
        VALUES (:uid, :soru, 'aktif')
      ");
      $ins->execute([":uid" => $kullanici_id, ":soru" => $soru]);
      $mesaj = "Sorunuz gönderildi. Kütüphaneci en kısa sürede dönüş yapacaktır.";
    }
  }
}

$stmt = $pdo->prepare("
  SELECT soru_id, soru_metni, soru_tarihi, cevap_metni, cevap_tarihi, durum, duzenlendi_tarihi
  FROM sorular
  WHERE kullanici_id = :uid
  ORDER BY soru_tarihi DESC
");
$stmt->execute([":uid" => $kullanici_id]);
$sorular = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Kütüphaneciye Sor";
$logo_text  = "“Bir kitap, sadece raflarda değil; insanın hayatında da yer açar.”";
$top_nav = [
  ["href"=>"panel.php", "icon"=>"fa-solid fa-house", "text"=>"Anasayfa"],
  ["href"=>"cikis.php", "icon"=>"fa-solid fa-right-from-bracket", "text"=>"Çıkış Yap"],
];

require_once "includes/header.php";
?>

<section class="hesap-wrapper">
  <?php $active="sor"; require "includes/sidebar.php"; ?>

  <main class="hesap-sag">
    <div class="sekme-icerik">

      <div class="sayfa-baslik">
        <h2>Kütüphaneciye Sor</h2>
      </div>

      <?php if ($mesaj): ?>
        <div class="alert success"><?php echo htmlspecialchars($mesaj); ?></div>
      <?php endif; ?>

      <?php if ($hata): ?>
        <div class="alert error"><?php echo htmlspecialchars($hata); ?></div>
      <?php endif; ?>

      <form method="post" class="soru-form">
        <label style="display:block; margin-bottom:8px; font-weight:600;">
          <?php echo $edit_id > 0 ? "Soruyu Düzenle" : "Sorunuz"; ?>
        </label>

        <textarea name="soru_metni" rows="5" required
          placeholder="Kütüphaneciye sormak istediğiniz şeyi yazın..."><?php
            echo htmlspecialchars($edit_id > 0 ? $edit_soru : "");
          ?></textarea>

        <input type="hidden" name="soru_id" value="<?php echo $edit_id; ?>">

        <button type="submit" class="btn-login" style="margin-top:12px;">
          <?php echo $edit_id > 0 ? "Güncelle" : "Gönder"; ?>
        </button>

        <?php if ($edit_id > 0): ?>
          <a href="kutuphaneciye_sor.php" style="display:inline-block;margin-top:10px;text-decoration:none;font-weight:600;">
            İptal
          </a>
        <?php endif; ?>
      </form>

      <hr class="cizgi">
      <h3 style="margin-top:0;">Geçmiş Sorularım</h3>

      <?php if (empty($sorular)): ?>
        <p>Henüz soru sormadınız.</p>
      <?php else: ?>
        <div class="soru-liste">
          <?php foreach ($sorular as $s): ?>
            <?php
              $cevap_var = !empty($s["cevap_metni"]);
              $silindi = ($s["durum"] === "silindi");
              $duzenlendi = !empty($s["duzenlendi_tarihi"]);
              $duzenlenebilir = (!$cevap_var && !$silindi);
            ?>

            <div class="soru-kart <?php echo $silindi ? "satir-iptal" : ""; ?>">
              <div class="soru-tarih">
                <?php echo htmlspecialchars($s["soru_tarihi"]); ?>

                <?php if ($silindi): ?>
                  <span class="badge-durum badge-iptal" style="margin-left:8px;">Silindi</span>
                <?php elseif ($duzenlendi): ?>
                  <span class="badge-durum badge-bekliyor" style="margin-left:8px;">Düzenlendi</span>
                <?php endif; ?>
              </div>

              <div class="soru-metin">
                <?php echo nl2br(htmlspecialchars($s["soru_metni"])); ?>
              </div>

              <?php if ($cevap_var): ?>
                <div class="cevap-kutu">
                  <div class="cevap-baslik">Kütüphaneci Cevabı (<?php echo htmlspecialchars($s["cevap_tarihi"]); ?>)</div>
                  <div><?php echo nl2br(htmlspecialchars($s["cevap_metni"])); ?></div>
                </div>
              <?php else: ?>
                <div class="cevap-bekliyor">Cevap bekleniyor...</div>

                <?php if ($duzenlenebilir): ?>
                  <div style="margin-top:10px; display:flex; gap:12px;">
                    <a href="kutuphaneciye_sor.php?edit=<?php echo (int)$s["soru_id"]; ?>"
                       style="font-weight:600; text-decoration:none;">
                      Düzenle
                    </a>

                    <a href="soru_sil.php?id=<?php echo (int)$s["soru_id"]; ?>"
                       onclick="return confirm('Bu soruyu silmek istiyor musunuz?');"
                       style="color:red;font-weight:600; text-decoration:none;">
                      Sil
                    </a>
                  </div>
                <?php endif; ?>
              <?php endif; ?>
              <?php if ($duzenlendi && !$silindi): ?>
                <div style="margin-top:8px; font-size:12px; color:#666;">
                  Son düzenleme: <?php echo htmlspecialchars($s["duzenlendi_tarihi"]); ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>
</section>
</body>
</html>
