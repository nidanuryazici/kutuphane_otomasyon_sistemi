<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/../db.php";
function require_login(PDO $pdo) {
  if (!isset($_SESSION["kullanici_id"])) {
    $_SESSION["redirect_after_login"] = $_SERVER["REQUEST_URI"];
    header("Location: giris.php");
    exit;
  }
  $uid = (int)$_SESSION["kullanici_id"];
  $stmt = $pdo->prepare("SELECT session_version FROM kullanicilar WHERE kullanici_id=:id");
  $stmt->execute([":id" => $uid]);
  $sv = $stmt->fetchColumn();
  if (!$sv) {
    session_destroy();
    header("Location: giris.php");
    exit;
  }
  $session_sv = (int)($_SESSION["session_version"] ?? 0);
  if ((int)$sv !== $session_sv) {
    session_destroy();
    header("Location: giris.php?err=oturum");
    exit;
  }
}
