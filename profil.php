<?php
require_once "db.php";
require_once "includes/auth.php";

require_login($pdo);

$kullanici_id = (int)$_SESSION["kullanici_id"];
$eposta_hata = "";
$eposta_ok = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["email_guncelle"])) {
  $yeni_email = trim($_POST["yeni_email"] ?? "");

  if ($yeni_email === "") {
    $eposta_hata = "E-posta zorunludur.";
  } elseif (!filter_var($yeni_email, FILTER_VALIDATE_EMAIL)) {
    $eposta_hata = "Geçerli bir e-posta adresi girin.";
  } else {
    try {
      $stmt = $pdo->prepare("UPDATE kullanicilar SET email = :email WHERE kullanici_id = :uid");
      $stmt->execute([":email" => $yeni_email, ":uid" => $kullanici_id]);

      header("Location: profil.php?ok=email");
      exit;
    } catch (PDOException $e) {
      $eposta_hata = ($e->getCode() === "23000")
        ? "Bu e-posta zaten başka bir kullanıcı tarafından kullanılıyor."
        : "E-posta güncellenemedi.";
    }
  }
}

if (($_GET["ok"] ?? "") === "email") {
  $eposta_ok = "E-posta adresiniz güncellendi.";
}

$stmt = $pdo->prepare("
  SELECT kullanici_adi, email, ad, soyad, kayit_tarihi
  FROM kullanicilar
  WHERE kullanici_id = :id
  LIMIT 1
");
$stmt->execute([":id" => $kullanici_id]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$u) {
  header("Location: cikis.php");
  exit;
}

$page_title = "Hesabım";
$logo_text  = "“Kitap tekrar tekrar açabileceğiniz bir hediyedir.” ~Garrison Keillor";
$top_nav = [
  ["href"=>"panel.php", "icon"=>"fa-solid fa-house", "text"=>"Anasayfa"],
  ["href"=>"cikis.php", "icon"=>"fa-solid fa-right-from-bracket", "text"=>"Çıkış Yap"],
];

require_once "includes/header.php";
?>

<section class="hesap-wrapper">
  <?php $active="profil"; require "includes/sidebar.php"; ?>

  <main class="hesap-sag">
    <div class="uye-kart">

      <div class="uye-ust">
        <div class="avatar">
          <?php
            $harf = mb_strtoupper(mb_substr(($u["ad"] ?: $u["kullanici_adi"]), 0, 1));
            echo htmlspecialchars($harf);
          ?>
        </div>

        <div class="uye-baslik">
          <div class="etiket">Ad Soyad</div>
          <div class="deger">
            <?php echo htmlspecialchars(trim(($u["ad"] ?? "")." ".($u["soyad"] ?? "")) ?: $u["kullanici_adi"]); ?>
          </div>
          <div class="mini">
            Kullanıcı Adı: <?php echo htmlspecialchars($u["kullanici_adi"]); ?>
          </div>
        </div>
      </div>

      <hr class="cizgi">
      <div class="uye-grid">
        <div class="uye-item">
          <div class="etiket">E-posta</div>
          <div class="deger"><?php echo htmlspecialchars($u["email"] ?? "-"); ?></div>
        </div>

        <div class="uye-item">
          <div class="etiket">Kayıt Tarihi</div>
          <div class="deger"><?php echo htmlspecialchars($u["kayit_tarihi"]); ?></div>
        </div>

        <div class="uye-item">
          <div class="etiket">Üye ID</div>
          <div class="deger"><?php echo (int)$kullanici_id; ?></div>
        </div>
      </div>

      <div class="uye-actions" style="display:flex; gap:12px; flex-wrap:wrap; margin-top:14px;">
        <a class="btn-login" style="text-decoration:none; display:inline-block; text-align:center;" href="sifre_degistir.php">
          Şifre Değiştir
        </a>
      </div>

      <hr class="cizgi">
      <h3 style="margin: 0 0 10px 0;">E-posta Değiştir</h3>

      <?php if ($eposta_ok): ?>
        <div class="alert success"><?php echo htmlspecialchars($eposta_ok); ?></div>
      <?php endif; ?>

      <?php if ($eposta_hata): ?>
        <div class="alert error"><?php echo htmlspecialchars($eposta_hata); ?></div>
      <?php endif; ?>

      <form method="post" class="soru-form">
        <label style="display:block;margin:10px 0 6px;font-weight:600;">Yeni E-posta *</label>
        <input type="email" name="yeni_email" required
               value="<?php echo htmlspecialchars($u["email"] ?? ""); ?>"
               style="width:100%;padding:12px;border-radius:12px;border:1px solid #ccc;">

        <button type="submit" name="email_guncelle" class="btn-login" style="margin-top:12px;">
          E-postayı Güncelle
        </button>
      </form>
    </div>
  </main>
</section>
</body>
</html>
