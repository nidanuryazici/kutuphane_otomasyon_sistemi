<?php
session_start();
require_once "db.php";
$hata = "";
$ok = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $kullanici_adi = trim($_POST["kullanici_adi"] ?? "");
  $email         = trim($_POST["email"] ?? "");
  $ad            = trim($_POST["ad"] ?? "");
  $soyad         = trim($_POST["soyad"] ?? "");
  $parola        = $_POST["parola"] ?? "";
  $parola2       = $_POST["parola2"] ?? "";

  if ($kullanici_adi === "" || $email === "" || $parola === "" || $parola2 === "") {
    $hata = "Kullanıcı adı, e-posta ve parola zorunludur.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $hata = "Geçerli bir e-posta adresi girin.";
  } elseif ($parola !== $parola2) {
    $hata = "Parolalar eşleşmiyor.";
  } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $parola)) {
    $hata = "Şifre en az 8 karakter olmalı ve en az 1 büyük harf, 1 küçük harf ve 1 rakam içermelidir.";
  } else {
    $stmt = $pdo->prepare("SELECT 1 FROM kullanicilar WHERE kullanici_adi = :ka LIMIT 1");
    $stmt->execute([":ka" => $kullanici_adi]);
    if ($stmt->fetchColumn()) {
      $hata = "Bu kullanıcı adı zaten kullanılıyor.";
    } else {
      $stmt = $pdo->prepare("SELECT 1 FROM kullanicilar WHERE email = :email LIMIT 1");
      $stmt->execute([":email" => $email]);
      if ($stmt->fetchColumn()) {
        $hata = "Bu e-posta zaten başka bir kullanıcı tarafından kullanılıyor.";
      } else {
        $hash = password_hash($parola, PASSWORD_DEFAULT);

        try {
          $ins = $pdo->prepare("
            INSERT INTO kullanicilar (kullanici_adi, parola, email, ad, soyad)
            VALUES (:ka, :parola, :email, :ad, :soyad)
          ");
          $ins->execute([
            ":ka"     => $kullanici_adi,
            ":parola" => $hash,
            ":email"  => $email,
            ":ad"     => ($ad === "" ? null : $ad),
            ":soyad"  => ($soyad === "" ? null : $soyad),
          ]);

          $ok = "Kayıt başarılı! Şimdi giriş yapabilirsiniz.";
        } catch (PDOException $e) {
          if ($e->getCode() === "23000") $hata = "Kullanıcı adı veya e-posta zaten kullanılıyor.";
          else $hata = "Kayıt sırasında hata oluştu.";
        }
      }
    }
  }
}

$page_title = "Kayıt Ol";
$logo_text  = "“Bir kitap, sadece raflarda değil; insanın hayatında da yer açar.”";
$top_nav = [
  ["href"=>"index.php", "icon"=>"fa-solid fa-house", "text"=>"Anasayfa"],
];

require_once "includes/header.php";
?>

<section id="login-alani">
  <div class="login-kutu">
    <h2>Kayıt Ol</h2>

    <?php if ($hata): ?>
      <div class="alert error"><?php echo htmlspecialchars($hata); ?></div>
    <?php endif; ?>

    <?php if ($ok): ?>
      <div class="alert success"><?php echo htmlspecialchars($ok); ?></div>
      <a class="btn btn-secondary" style="display:block;text-align:center;margin-top:10px;" href="giris.php">
        Giriş sayfasına git
      </a>
    <?php endif; ?>

    <form method="post">
      <label>Kullanıcı Adı *</label>
      <input type="text" name="kullanici_adi" required value="<?php echo htmlspecialchars($_POST["kullanici_adi"] ?? ""); ?>">

      <label>E-posta *</label>
      <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST["email"] ?? ""); ?>">

      <label>Ad</label>
      <input type="text" name="ad" value="<?php echo htmlspecialchars($_POST["ad"] ?? ""); ?>">

      <label>Soyad</label>
      <input type="text" name="soyad" value="<?php echo htmlspecialchars($_POST["soyad"] ?? ""); ?>">

      <label>Parola *</label>
      <input type="password" name="parola" required>

      <label>Parola (Tekrar) *</label>
      <input type="password" name="parola2" required>

      <div class="form-alt-metin">Zaten hesabınız var mı? <a href="giris.php">Giriş Yap</a></div>
      <button type="submit" class="btn-login">Kayıt Ol</button>
    </form>
  </div>
</section>
</body>
</html>

