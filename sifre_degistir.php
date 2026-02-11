<?php
require_once "db.php";
require_once "includes/auth.php";
require_login($pdo);
$kullanici_id = (int)$_SESSION["kullanici_id"];
$basari = "";
$hata = "";

function sifre_guclu_mu(string $sifre): bool {
  return (bool)preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $sifre);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $mevcut = trim($_POST["mevcut_parola"] ?? "");
  $yeni   = trim($_POST["yeni_parola"] ?? "");
  $yeni2  = trim($_POST["yeni_parola_tekrar"] ?? "");

  if ($mevcut === "" || $yeni === "" || $yeni2 === "") {
    $hata = "Tüm alanlar zorunludur.";
  } elseif ($yeni !== $yeni2) {
    $hata = "Yeni parola ve tekrarı aynı olmalıdır.";
  } elseif (!sifre_guclu_mu($yeni)) {
    $hata = "Yeni şifre en az 8 karakter olmalı ve en az 1 büyük harf, 1 küçük harf ve 1 rakam içermelidir.";
  } else {
    $stmt = $pdo->prepare("SELECT parola FROM kullanicilar WHERE kullanici_id = :id LIMIT 1");
    $stmt->execute([":id" => $kullanici_id]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$u) {
      $hata = "Kullanıcı bulunamadı.";
    } elseif (!password_verify($mevcut, $u["parola"])) {
      $hata = "Mevcut parola yanlış.";
    } else {
      $yeni_hash = password_hash($yeni, PASSWORD_DEFAULT);

      $up = $pdo->prepare("
        UPDATE kullanicilar
        SET parola = :p, session_version = session_version + 1
        WHERE kullanici_id = :id
      ");
      $up->execute([":p" => $yeni_hash, ":id" => $kullanici_id]);

      $_SESSION["session_version"] = (int)($_SESSION["session_version"] ?? 0) + 1;
      $basari = "Parolanız başarıyla güncellendi.";
    }
  }
}

$page_title = "Şifre Değiştir";
$logo_text  = "“Kitap tekrar tekrar açabileceğiniz bir hediyedir.” ~Garrison Keillor";
$top_nav = [
  ["href"=>"profil.php", "icon"=>"fa-regular fa-circle-user", "text"=>"Hesabım"],
  ["href"=>"cikis.php", "icon"=>"fa-solid fa-right-from-bracket", "text"=>"Çıkış Yap"],
];

require_once "includes/header.php";
?>

<section id="login-alani">
  <div class="login-kutu">
    <h2>Şifre Değiştir</h2>

    <?php if ($basari): ?>
      <div class="alert success"><?php echo htmlspecialchars($basari); ?></div>
    <?php endif; ?>

    <?php if ($hata): ?>
      <div class="alert error"><?php echo htmlspecialchars($hata); ?></div>
    <?php endif; ?>

    <form method="post">
      <label>Mevcut Parola</label>
      <input type="password" name="mevcut_parola" required>

      <label>Yeni Parola</label>
      <input type="password" name="yeni_parola" required>

      <label>Yeni Parola (Tekrar)</label>
      <input type="password" name="yeni_parola_tekrar" required>

      <button type="submit" class="btn-login" style="margin-top:14px;">Güncelle</button>

      <div class="form-alt-metin" style="margin-top:12px;">
        <a href="profil.php">Geri dön</a>
      </div>
    </form>
  </div>
</section>
</body>
</html>
