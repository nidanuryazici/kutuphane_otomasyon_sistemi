<?php
require_once "db.php";
$sql = "
  SELECT 
    k.kitap_id,
    k.kitap_adi,
    k.aciklama,
    k.eklenme_tarihi,
    GROUP_CONCAT(DISTINCT y.yazar_adi SEPARATOR ', ') AS yazarlar
  FROM kitaplar k
  LEFT JOIN kitap_yazar ky ON ky.kitap_id = k.kitap_id
  LEFT JOIN yazarlar y ON y.yazar_id = ky.yazar_id
  GROUP BY k.kitap_id
  ORDER BY k.eklenme_tarihi DESC
  LIMIT 8
";
$yeniKitaplar = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$page_title = "Kütüphane Sistemi";
$logo_text  = "“Kitap tekrar tekrar açabileceğiniz bir hediyedir.” ~Garrison Keillor";
$top_nav = [
  ["href"=>"giris.php", "icon"=>"fa-regular fa-circle-user", "text"=>"Giriş Yap"],
  ["href"=>"kayit.php", "icon"=>"fa-solid fa-user-plus", "text"=>"Kayıt Ol"],
];
require_once "includes/header.php";
?>
<section id="arama-alani">
  <h2>Kitap veya Yazar Ara</h2>
  <form action="arama.php" method="get">
    <input type="text" name="q" placeholder="Kitap adı veya yazar adı..."
           value="<?php echo htmlspecialchars($_GET["q"] ?? ""); ?>">
    <select name="konu">
      <option value="">Tüm Konular</option>
      <option value="Distopya">Distopya</option>
      <option value="Fantastik">Fantastik</option>
      <option value="Türk Edebiyatı">Türk Edebiyatı</option>
      <option value="Türk Edebiyatı / Klasik">Türk Edebiyatı / Klasik</option>
      <option value="Klasik / Psikoloji">Klasik / Psikoloji</option>
      <option value="Novella / Psikoloji">Novella / Psikoloji</option>
      <option value="Felsefi Roman">Felsefi Roman</option>
      <option value="Klasik / Roman">Klasik / Roman</option>
      <option value="Klasik / Toplum">Klasik / Toplum</option>
      <option value="Kişisel Gelişim / Roman">Kişisel Gelişim / Roman</option>
    </select>
    <button type="submit">Ara</button>
  </form>
</section>
<section id="yeni-kitaplar">
  <h2>Yeni Eklenen Kitaplar</h2>
  <div class="kitap-grid">
    <?php if (empty($yeniKitaplar)): ?>
      <p>Henüz kitap eklenmemiş.</p>
    <?php else: ?>
      <?php foreach ($yeniKitaplar as $kitap): ?>
        <div class="kitap-kart" style="cursor:pointer;"
             onclick="location.href='kitap.php?id=<?php echo (int)$kitap['kitap_id']; ?>'">
          <h3><?php echo htmlspecialchars($kitap["kitap_adi"]); ?></h3>
          <p class="yazar"><?php echo htmlspecialchars($kitap["yazarlar"] ?: "Yazar bilgisi yok"); ?></p>
          <p class="aciklama"><?php echo htmlspecialchars(mb_strimwidth($kitap["aciklama"] ?? "", 0, 120, "...")); ?></p>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>
<section id="geri-bildirim">
  <div class="geri-container">
    <?php if (isset($_GET["gb"])): ?>
      <?php if ($_GET["gb"] == "1"): ?>
        <div class="alert success">Geri bildiriminiz kaydedildi. Teşekkürler!</div>
      <?php else: ?>
        <div class="alert error">
          <?php
            $hata = $_GET["hata"] ?? "";
            if ($hata === "email") echo "Lütfen geçerli bir e-posta adresi girin.";
            else if ($hata === "puan") echo "Puan 1 ile 5 arasında olmalı.";
            else echo "Geri bildirim gönderilemedi.";
          ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <h2>Geri Bildirim</h2>
    <p>Deneyimini değerlendir, sistemi geliştirelim.</p>

    <form class="geri-form" action="geri_bildirim_kaydet.php" method="post">
      <input type="hidden" name="return" value="index.php">
      <div class="form-row">
        <input type="email" name="email" placeholder="E-posta adresiniz" required>
        <select name="puan" required>
          <option value="">Puan seç</option>
          <option value="5">5 - Mükemmel</option>
          <option value="4">4 - İyi</option>
          <option value="3">3 - Orta</option>
          <option value="2">2 - Geliştirilmeli</option>
          <option value="1">1 - Kötü</option>
        </select>
      </div>
      <textarea name="mesaj" rows="5" placeholder="Mesajın..."></textarea>
      <button type="submit" class="btn">Gönder</button>
    </form>
  </div>
</section>
<footer id="footer">
  <div class="footer-icerik">
    <div class="footer-kolon">
      <h3>Yazıcı Kütüphanesi</h3>
      <p>Kitap adı veya yazar adı ile arama yapabileceğiniz kütüphane sistemi.</p>
    </div>
    <div class="footer-kolon">
      <h3>Hızlı Linkler</h3>
      <ul>
        <li><a href="index.php">Anasayfa</a></li>
        <li><a href="giris.php">Giriş Yap</a></li>
        <li><a href="kutuphaneciye_sor.php?from=index.php">Kütüphaneciye Sor</a></li>
      </ul>
    </div>
    <div class="footer-kolon">
      <h3>İletişim</h3>
      <p><i class="fa-regular fa-envelope ikon"></i> info@kutuphane.com</p>
      <p><i class="fa-solid fa-phone ikon"></i> +90 5xx xxx xx xx</p>
      <p><i class="fa-solid fa-location-dot ikon"></i> Türkiye</p>
    </div>
  </div>
  <div class="footer-alt">
    <p>© 2026 Yazıcı Kütüphanesi. Tüm hakları saklıdır.</p>
  </div>
</footer>
</body>
</html>