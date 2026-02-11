<?php
require_once "db.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  header("Location: index.php");
  exit;
}
$return = $_POST["return"] ?? "index.php";
$allowed = ["index.php", "panel.php"];
if (!in_array($return, $allowed, true)) $return = "index.php";

function go(string $base, array $params): void {
  $q = http_build_query($params);
  $sep = (strpos($base, "?") !== false) ? "&" : "?";
  header("Location: " . $base . $sep . $q);
  exit;
}
$email = trim($_POST["email"] ?? "");
$puan  = $_POST["puan"] ?? "";
$mesaj = trim($_POST["mesaj"] ?? "");

if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  go($return, ["gb" => 0, "hata" => "email"]);
}

$allowed_puan = ["1","2","3","4","5"];
if (!in_array($puan, $allowed_puan, true)) {
  go($return, ["gb" => 0, "hata" => "puan"]);
}

$puanInt = (int)$puan;
if (mb_strlen($mesaj) > 2000) {
  $mesaj = mb_substr($mesaj, 0, 2000);
}
$sql = "INSERT INTO geri_bildirim (email, puan, mesaj) VALUES (:email, :puan, :mesaj)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
  ":email" => $email,
  ":puan"  => $puanInt,
  ":mesaj" => $mesaj
]);
go($return, ["gb" => 1]);