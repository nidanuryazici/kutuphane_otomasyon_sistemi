<?php
session_start();
require_once "db.php";
$hata = "";
if (!empty($_GET["return"])) {
  $_SESSION["redirect_after_login"] = $_GET["return"];
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $kullanici_adi = trim($_POST["kullanici_adi"] ?? "");
  $parola        = $_POST["parola"] ?? "";

  if ($kullanici_adi === "" || $parola === "") {
    $hata = "Kullanıcı adı ve parola zorunludur.";
  } else {
    $stmt = $pdo->prepare("
      SELECT kullanici_id, parola, session_version
      FROM kullanicilar
      WHERE kullanici_adi = :ka
      LIMIT 1
    ");
    $stmt->execute([":ka" => $kullanici_adi]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($parola, $user["parola"])) {
      $_SESSION["kullanici_id"]     = (int)$user["kullanici_id"];
      $_SESSION["session_version"]  = (int)$user["session_version"];

      if (!empty($_SESSION["redirect_after_login"])) {
        $hedef = $_SESSION["redirect_after_login"];
        unset($_SESSION["redirect_after_login"]);
        header("Location: " . $hedef);
        exit;
      }

      header("Location: panel.php");
      exit;
    } else {
      $hata = "Kullanıcı adı veya parola hatalı.";
    }
  }
}

$page_title = "Giriş Yap";
$logo_text  = "“Kitap tekrar tekrar açabileceğiniz bir hediyedir.” ~Garrison Keillor";
$top_nav = [
  ["href"=>"index.php", "icon"=>"fa-solid fa-house", "text"=>"Anasayfa"],
];
require_once "includes/header.php";
?>
<section id="login-alani">
  <div class="login-kutu">
    <h2>Giriş Yap</h2>

    <?php if ($hata): ?>
      <div class="alert error"><?php echo htmlspecialchars($hata); ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="off">
      <label>Kullanıcı Adı</label>
      <input type="text" name="kullanici_adi" required
             value="<?php echo htmlspecialchars($_POST["kullanici_adi"] ?? ""); ?>">
      <label>Parola</label>
      <input type="password" name="parola" required>
      <div class="form-alt-metin">Hesabınız yok mu? <a href="kayit.php">Kayıt Ol</a></div>
      <div class="form-alt-metin">Şifreni mi unuttun? <a href="sifremi_unuttum.php">Şifremi Unuttum</a></div>
      <button type="submit" class="btn-login">Giriş Yap</button>
    </form>
  </div>
</section>
</body>
</html>
