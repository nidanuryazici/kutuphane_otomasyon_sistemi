<?php
require_once "db.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  header("Location: index.php");
  exit;
}
$email = trim($_POST["email"] ?? "");
$puan  = (string)($_POST["puan"] ?? "");
$mesaj = trim($_POST["mesaj"] ?? "");
if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  header("Location: index.php?gb=0&hata=email");
  exit;
}
if (!in_array($puan, ["1","2","3","4","5"], true)) {
  header("Location: index.php?gb=0&hata=puan");
  exit;
}
if (mb_strlen($mesaj) > 2000) {
  $mesaj = mb_substr($mesaj, 0, 2000);
}
$stmt = $pdo->prepare("INSERT INTO geri_bildirim (email, puan, mesaj) VALUES (:email, :puan, :mesaj)");
$stmt->execute([
  ":email" => $email,
  ":puan"  => (int)$puan,
  ":mesaj" => $mesaj
]);
header("Location: index.php?gb=1");
exit;
