<?php
require_once "db.php";

$mesaj = "";
$hata  = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST["email"] ?? "");

  if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $hata = "Geçerli bir e-posta girin.";
  } else {
    $stmt = $pdo->prepare("SELECT kullanici_id FROM kullanicilar WHERE email=:e LIMIT 1");
    $stmt->execute([":e" => $email]);
    $uid = $stmt->fetchColumn();

    $mesaj = "Eğer bu e-posta sistemde kayıtlıysa, şifre sıfırlama bağlantısı gönderildi.";

    if ($uid) {
      $token = bin2hex(random_bytes(32));
      $hash  = hash("sha256", $token);
      $son   = date("Y-m-d H:i:s", time() + 15*60);

      $up = $pdo->prepare("
        UPDATE kullanicilar
        SET sifre_token_hash = :h,
            sifre_token_son  = :son
        WHERE kullanici_id = :id
      ");
      $up->execute([":h" => $hash, ":son" => $son, ":id" => (int)$uid]);

      $link = "http://localhost/satjcalismasi/sifre_sifirla.php?token=" . urlencode($token);
      $mesaj .= "<br><small>Test Link: <a href='" . htmlspecialchars($link) . "'>Şifreyi Sıfırla</a></small>";
    }
  }
}

$page_title = "Şifremi Unuttum";
$logo_text  = "“Bir kitap, sadece raflarda değil; insanın hayatında da yer açar.”";
$top_nav = [
  ["href"=>"giris.php", "icon"=>"fa-solid fa-right-to-bracket", "text"=>"Giriş Yap"],
];
require_once "includes/header.php";
?>
<section id="login-alani">
  <div class="login-kutu">
    <h2>Şifremi Unuttum</h2>

    <?php if ($mesaj): ?>
      <div class="alert success"><?php echo $mesaj; ?></div>
    <?php endif; ?>

    <?php if ($hata): ?>
      <div class="alert error"><?php echo htmlspecialchars($hata); ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <label>E-posta</label>
      <input type="email" name="email" required>
      <button type="submit" class="btn-login" style="margin-top:14px;">Sıfırlama Linki Gönder</button>
      <div class="form-alt-metin" style="margin-top:12px;">
        <a href="giris.php">Giriş sayfasına dön</a>
      </div>
    </form>
  </div>
</section>
</body>
</html>
