<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($page_title ?? "Kütüphane Sistemi"); ?></title>
  <link rel="stylesheet" href="css/style.css">
  <script src="https://kit.fontawesome.com/0190887ba7.js" crossorigin="anonymous"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Funnel+Display:wght@300..800&family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Poppins:wght@300&display=swap" rel="stylesheet">
</head>
<body>

<section id="menu">
  <div id="logo"><?php echo htmlspecialchars($logo_text ?? "“Kitap tekrar tekrar açabileceğiniz bir hediyedir.” ~Garrison Keillor"); ?></div>
  <nav>
    <?php if (!empty($top_nav ?? [])): ?>
      <?php foreach ($top_nav as $item): ?>
        <a href="<?php echo htmlspecialchars($item["href"]); ?>">
          <?php if (!empty($item["icon"])): ?><i class="<?php echo htmlspecialchars($item["icon"]); ?> ikon"></i><?php endif; ?>
          <?php echo htmlspecialchars($item["text"]); ?>
        </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </nav>
</section>
</body>
</html>
