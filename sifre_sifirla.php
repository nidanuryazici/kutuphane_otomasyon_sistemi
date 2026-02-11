<?php
require_once "db.php";
$token = trim($_GET["token"] ?? "");
$hata = "";
$ok = "";

function sifre_guclu_mu(string $sifre): bool {
  return (bool)preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $sifre);
}

$u = null;

if ($token === "") {
  $hata = "Geçersiz bağlantı.";
} else {
  $hash = hash("sha256", $token);
  $stmt = $pdo->prepare("
    SELECT kullanici_id, email
    FROM kullanicilar
    WHERE sifre_token_hash=:h
      AND sifre_token_son IS NOT NULL
      AND sifre_token_son >= NOW()
    LIMIT 1
  ");
  $stmt->execute([":h" => $hash]);
  $u = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$u) {
    $hata = "Bağlantının süresi dolmuş veya geçersiz. Yeni bağlantı isteyebilirsiniz.";
  } else {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $p1 = trim($_POST["parola"] ?? "");
      $p2 = trim($_POST["parola2"] ?? "");

      if ($p1 === "" || $p2 === "") {
        $hata = "Parola alanları boş olamaz.";
      } elseif ($p1 !== $p2) {
        $hata = "Parolalar eşleşmiyor.";
      } elseif (!sifre_guclu_mu($p1)) {
        $hata = "Şifre en az 8 karakter olmalı ve en az 1 büyük harf, 1 küçük harf ve 1 rakam içermelidir.";
      } else {
        $hashpass = password_hash($p1, PASSWORD_DEFAULT);

        $pdo->beginTransaction();
        try {
          $up = $pdo->prepare("
            UPDATE kullanicilar
            SET parola=:p,
                sifre_token_hash=NULL,
                sifre_token_son=NULL,
                session_version = session_version + 1
            WHERE kullanici_id=:id
          ");
          $up->execute([":p" => $hashpass, ":id" => (int)$u["kullanici_id"]]);
          $pdo->commit();

          $ok = "Şifreniz başarıyla değiştirildi. Şimdi giriş yapabilirsiniz.";
        } catch (Throwable $e) {
          $pdo->rollBack();
          $hata = "Bir hata oluştu.";
        }
      }
    }
  }
}

$page_title = "Şifre Sıfırla";
$logo_text  = "“Kitap tekrar tekrar açabileceğiniz bir hediyedir.” ~Garrison Keillor";
$top_nav = [
  ["href"=>"giris.php", "icon"=>"fa-solid fa-right-to-bracket", "text"=>"Giriş Yap"],
];

require_once "includes/header.php";
?>

<section id="login-alani">
  <div class="login-kutu">
    <h2>Şifre Sıfırla</h2>

    <?php if ($ok): ?>
      <div class="alert success"><?php echo htmlspecialchars($ok); ?></div>
      <div class="form-alt-metin" style="margin-top:12px;">
        <a href="giris.php">Giriş sayfasına git</a>
      </div>
    <?php else: ?>

      <?php if ($hata): ?>
        <div class="alert error"><?php echo htmlspecialchars($hata); ?></div>
      <?php endif; ?>

      <?php if (!$hata): ?>
        <form method="post" autocomplete="off">
          <label>Yeni Parola</label>
          <input type="password" name="parola" required>

          <label>Yeni Parola (Tekrar)</label>
          <input type="password" name="parola2" required>

          <button type="submit" class="btn-login" style="margin-top:14px;">Şifreyi Güncelle</button>

          <div class="form-alt-metin" style="margin-top:12px;">
            <a href="sifremi_unuttum.php">Yeni sıfırlama linki iste</a>
          </div>
        </form>
      <?php endif; ?>

    <?php endif; ?>
  </div>
</section>
</body>
</html>