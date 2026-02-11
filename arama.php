<?php
require_once "db.php";
require_once "includes/kurallar.php";

if (session_status() === PHP_SESSION_NONE) session_start();

ayirtma_sure_kontrol($pdo);

$girisli = isset($_SESSION["kullanici_id"]);

$q    = trim($_GET["q"] ?? "");
$konu = trim($_GET["konu"] ?? "");
$sonuclar = [];

$stmt = $pdo->query("
  SELECT DISTINCT konu
  FROM kitaplar
  WHERE konu IS NOT NULL AND konu <> ''
  ORDER BY konu
");
$konular = $stmt->fetchAll(PDO::FETCH_COLUMN);

$where = [];
$params = [];

if ($q !== "") {
  $where[] = "(k.kitap_adi LIKE :arama OR y.yazar_adi LIKE :arama)";
  $params[":arama"] = "%".$q."%";
}

if ($konu !== "") {
  $where[] = "k.konu = :konu";
  $params[":konu"] = $konu;
}

if (!empty($where)) {
  $sql = "
    SELECT DISTINCT
      k.kitap_id,
      k.kitap_adi,
      k.aciklama,
      k.konu,
      k.kat,
      k.kitaplik,
      k.raf,
      k.stok_mevcut,
      k.stok_toplam,
      GROUP_CONCAT(DISTINCT y.yazar_adi SEPARATOR ', ') AS yazarlar
    FROM kitaplar k
    LEFT JOIN kitap_yazar ky ON ky.kitap_id = k.kitap_id
    LEFT JOIN yazarlar y ON y.yazar_id = ky.yazar_id
    WHERE " . implode(" AND ", $where) . "
    GROUP BY k.kitap_id
    ORDER BY k.kitap_adi
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $sonuclar = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$return_uri = $_SERVER["REQUEST_URI"];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Arama Sonuçları</title>
  <link rel="stylesheet" href="css/style.css">
  <script src="https://kit.fontawesome.com/0190887ba7.js" crossorigin="anonymous"></script>
</head>
<body>

<section id="menu">
  <div id="logo">“Kitap tekrar tekrar açabileceğiniz bir hediyedir.” ~Garrison Keillor</div>
  <nav>
    <?php if ($girisli): ?>
      <a href="panel.php"><i class="fa-solid fa-house ikon"></i>Anasayfa</a>
      <a href="profil.php"><i class="fa-regular fa-circle-user ikon"></i>Hesabım</a>
      <a href="cikis.php"><i class="fa-solid fa-right-from-bracket ikon"></i>Çıkış Yap</a>
    <?php else: ?>
      <a href="index.php"><i class="fa-solid fa-house ikon"></i>Anasayfa</a>
      <a href="giris.php"><i class="fa-regular fa-circle-user ikon"></i>Giriş Yap</a>
      <a href="kayit.php"><i class="fa-solid fa-user-plus ikon"></i>Kayıt Ol</a>
    <?php endif; ?>
  </nav>
</section>

<section id="arama-alani">
  <h2>Kitap veya Yazar Ara</h2>

  <form action="arama.php" method="get">
    <input type="text" name="q" placeholder="Kitap adı veya yazar adı..." value="<?php echo htmlspecialchars($q); ?>">

    <select name="konu">
      <option value="">Tüm Konular</option>
      <?php foreach ($konular as $k): ?>
        <option value="<?php echo htmlspecialchars($k); ?>" <?php if ($konu === $k) echo "selected"; ?>>
          <?php echo htmlspecialchars($k); ?>
        </option>
      <?php endforeach; ?>
    </select>

    <button type="submit">Ara</button>
  </form>
</section>

<section id="yeni-kitaplar">
  <h2>Arama Sonuçları</h2>

  <div class="kitap-grid">
    <?php if ($q === "" && $konu === ""): ?>
      <p>Aramak için kitap/yazar yaz veya konu seç.</p>

    <?php elseif (empty($sonuclar)): ?>
      <p>Sonuç bulunamadı.</p>

    <?php else: ?>
      <?php foreach ($sonuclar as $kitap): ?>
        <div class="kitap-kart">
          <h3><?php echo htmlspecialchars($kitap["kitap_adi"]); ?></h3>

          <p class="yazar">
            <?php echo htmlspecialchars($kitap["yazarlar"] ?: "Yazar bilgisi yok"); ?>
          </p>

          <p class="aciklama">
            <?php echo htmlspecialchars(mb_strimwidth($kitap["aciklama"] ?? "", 0, 160, "...")); ?>
          </p>

          <p class="konu-konum">
            <strong>Konu:</strong> <?php echo htmlspecialchars($kitap["konu"] ?? "-"); ?><br>
            <strong>Stok:</strong> <?php echo (int)$kitap["stok_mevcut"]; ?> / <?php echo (int)$kitap["stok_toplam"]; ?><br>
            <strong>Konum:</strong>
            <?php
              $konumStr = trim(($kitap["kat"] ?? "")." / ".($kitap["kitaplik"] ?? "")." / ".($kitap["raf"] ?? ""));
              echo htmlspecialchars($konumStr ? $konumStr : "-");
            ?>
          </p>

          <?php if ($girisli): ?>
            <div class="kitap-aksiyon">
              <a class="btn-mini" href="favori_ekle.php?id=<?php echo (int)$kitap["kitap_id"]; ?>&return=<?php echo urlencode($return_uri); ?>">
                Favoriye Ekle
              </a>

              <a class="btn-mini btn-outline" href="ayirt.php?id=<?php echo (int)$kitap["kitap_id"]; ?>&return=<?php echo urlencode($return_uri); ?>">
                Ayırt
              </a>
            </div>
          <?php else: ?>
            <div class="kitap-aksiyon">
              <a class="btn-mini" href="giris.php?return=<?php echo urlencode($_SERVER["REQUEST_URI"]); ?>"> Favori / Ayırt için giriş yap </a>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>
</body>
</html>
