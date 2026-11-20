<?php
/**
 * JAWA MART - Tentang Kami
 * File: about.php
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Tentang JAWA MART — Toko Online Sembako';
$metaDesc  = 'Kenali JAWA MART, toko online penyedia aneka kebutuhan sembako dan rumah tangga berkualitas dengan harga hemat dan pengiriman cepat.';
$storeName = getSetting('store_name', 'JAWA MART');
$storeTagline = getSetting('store_tagline', 'Belanja Sembako Mudah, Hemat, dan Praktis.');

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 24px; padding-bottom: 50px;">
  
  <!-- Breadcrumb -->
  <div style="font-size: 13.5px; color: #6c757d; margin-bottom: 20px;">
    <a href="index.php">Beranda</a> &rsaquo; 
    <span style="color: #212529; font-weight: 600;">Tentang Kami</span>
  </div>

  <div style="background: #ffffff; border: 1px solid #dee2e6; border-radius: 14px; padding: 36px; box-shadow: 0 2px 6px rgba(0,0,0,0.04); margin-bottom: 30px;">
    
    <div style="max-width: 800px; margin: 0 auto;">
      <div style="text-align: center; margin-bottom: 30px;">
        <span style="background: #e8f5e9; color: #198754; font-size: 12.5px; font-weight: 800; padding: 4px 14px; border-radius: 9999px; text-transform: uppercase;">
          Profil Perusahaan
        </span>
        <h1 style="font-size: 28px; font-weight: 900; color: #212529; margin-top: 10px;">
          Tentang <?= htmlspecialchars($storeName) ?>
        </h1>
        <p style="color: #198754; font-weight: 700; font-size: 16px; margin-top: 6px;">
          "<?= htmlspecialchars($storeTagline) ?>"
        </p>
      </div>

      <div style="font-size: 15px; line-height: 1.8; color: #374151;">
        <p style="margin-bottom: 18px;">
          <strong><?= htmlspecialchars($storeName) ?></strong> lahir sebagai solusi belanja kebutuhan pokok yang mudah, modern, dan terjangkau bagi setiap keluarga Indonesia. Kami memahami betapa berharganya waktu Anda, sehingga kami menghadirkan platform toko online sembako yang memungkinkan Anda memesan beras, minyak goreng, gula, tepung, telur, dan bumbu dapur langsung dari genggaman tanpa harus antre atau mengangkat beban berat.
        </p>

        <p style="margin-bottom: 24px;">
          Seluruh produk yang kami sediakan dipilih secara teliti dari produsen terpercaya dan petani lokal untuk memastikan kesegaran, kebersihan, dan mutu terbaik saat sampai di meja makan Anda.
        </p>

        <h3 style="font-size: 18px; font-weight: 800; color: #198754; margin: 28px 0 14px;">
          Nilai Utama &amp; Komitmen Kami
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 30px;">
          <div style="background: #f8faf9; padding: 18px; border-radius: 10px; border-left: 4px solid #198754;">
            <div style="font-weight: 800; font-size: 15px; color: #212529; margin-bottom: 4px;">Kualitas Teruji</div>
            <p style="font-size: 13px; color: #6c757d;">Beras murni tanpa pemutih, minyak sawit jernih, dan telur selalu segar setiap hari.</p>
          </div>
          <div style="background: #f8faf9; padding: 18px; border-radius: 10px; border-left: 4px solid #198754;">
            <div style="font-weight: 800; font-size: 15px; color: #212529; margin-bottom: 4px;">Harga Bersahabat</div>
            <p style="font-size: 13px; color: #6c757d;">Harga transparan, bersaing, serta banyak promo diskon dan gratis ongkir hemat.</p>
          </div>
          <div style="background: #f8faf9; padding: 18px; border-radius: 10px; border-left: 4px solid #198754;">
            <div style="font-weight: 800; font-size: 15px; color: #212529; margin-bottom: 4px;">Pelayanan Cepat</div>
            <p style="font-size: 13px; color: #6c757d;">Integrasi WhatsApp langsung dengan admin responsif dan pengantaran kurir cepat.</p>
          </div>
        </div>

        <div style="text-align: center; margin-top: 36px; padding-top: 24px; border-top: 1px solid #dee2e6;">
          <a href="products.php" class="btn-primary" style="padding: 12px 28px; font-size: 15px;">
            Mulai Belanja di <?= htmlspecialchars($storeName) ?> &rarr;
          </a>
        </div>
      </div>

    </div>

  </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
