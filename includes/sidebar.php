<?php
$active = $active ?? "";
function aclass($key, $active) { return $key === $active ? "active" : ""; }
?>
<aside class="hesap-sol">
  <h3>Üye İşlemleri</h3>

  <a class="hesap-link <?php echo aclass('profil',$active); ?>" href="profil.php">
    <i class="fa-regular fa-circle-user ikon"></i>Üye Bilgileri
  </a>

  <a class="hesap-link <?php echo aclass('odunc',$active); ?>" href="odunc.php">
    <i class="fa-solid fa-book ikon"></i>Ödünç / İade
  </a>

  <a class="hesap-link <?php echo aclass('ayirt',$active); ?>" href="ayirttiklarim.php">
    <i class="fa-solid fa-bookmark ikon"></i>Ayırttıklarım
  </a>

  <a class="hesap-link <?php echo aclass('yayin',$active); ?>" href="yayin_istek.php">
    <i class="fa-solid fa-pen-to-square ikon"></i>Yayın İstek
  </a>

  <a class="hesap-link <?php echo aclass('sor',$active); ?>" href="kutuphaneciye_sor.php">
    <i class="fa-regular fa-circle-question ikon"></i>Kütüphaneciye Sor
  </a>

  <a class="hesap-link <?php echo aclass('favori',$active); ?>" href="favorilerim.php">
    <i class="fa-regular fa-heart ikon"></i>Favorilerim
  </a>

  <a class="hesap-link <?php echo aclass('bagis',$active); ?>" href="bagislarim.php">
    <i class="fa-solid fa-hand-holding-heart ikon"></i>Bağışlarım
  </a>
</aside>
