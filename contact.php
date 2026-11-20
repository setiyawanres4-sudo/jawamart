<?php
/**
 * JAWA MART - Kontak & Informasi Toko
 * File: contact.php
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Hubungi Kami — JAWA MART';
$metaDesc  = 'Hubungi JAWA MART untuk informasi produk sembako, pesanan dalam jumlah besar, kerja sama, dan layanan pelanggan.';

$storeName        = getSetting('store_name', 'JAWA MART');
$whatsappOfficial = getStoreWhatsApp();
$whatsappDisplay  = getSetting('whatsapp_display', '+62 878-1212-2735');
$storeEmail       = getSetting('store_email', 'kontak@jawamart.id');
$storeAddress     = getSetting('store_address', 'Jl. Pasar Tradisional & Sembako Jaya No. 88, Jawa, Indonesia');
$operatingHours   = getSetting('operating_hours', 'Senin - Minggu: 07.00 - 20.00 WIB');

$contactSent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senderName = trim($_POST['name'] ?? '');
    $senderMsg  = trim($_POST['message'] ?? '');
    if (!empty($senderName) && !empty($senderMsg)) {
        $contactSent = true;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 24px; padding-bottom: 50px;">
  
  <!-- Breadcrumb -->
  <div style="font-size: 13.5px; color: #6c757d; margin-bottom: 20px;">
    <a href="index.php">Beranda</a> &rsaquo; 
    <span style="color: #212529; font-weight: 600;">Kontak &amp; Alamat Toko</span>
  </div>

  <div class="section-header">
    <div>
      <h1 class="section-title">Hubungi <?= htmlspecialchars($storeName) ?></h1>
      <p class="section-subtitle">Kami siap membantu kebutuhan sembako dan pesanan harian Anda</p>
    </div>
  </div>

  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px; align-items: start;">
    
    <!-- Info Card -->
    <div style="background: #ffffff; border: 1px solid #dee2e6; border-radius: 14px; padding: 32px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
      <h2 style="font-size: 18px; font-weight: 800; color: #212529; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1.5px solid #e8f5e9;">
        Informasi Layanan &amp; Toko
      </h2>

      <div style="display: flex; flex-direction: column; gap: 20px; font-size: 14.5px;">
        <div style="display: flex; gap: 14px; align-items: flex-start;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #e8f5e9; color: #198754; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">📍</div>
          <div>
            <div style="font-weight: 700; color: #212529;">Alamat Toko &amp; Gudang:</div>
            <div style="color: #495057; line-height: 1.5; margin-top: 3px;"><?= nl2br(htmlspecialchars($storeAddress)) ?></div>
          </div>
        </div>

        <div style="display: flex; gap: 14px; align-items: flex-start;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #d1e7dd; color: #0f5132; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">💬</div>
          <div>
            <div style="font-weight: 700; color: #212529;">WhatsApp Customer Service:</div>
            <div style="margin-top: 3px;">
              <a href="https://wa.me/<?= $whatsappOfficial ?>" target="_blank" rel="noopener" style="font-weight: 800; font-size: 16px; color: #25d366;">
                <?= htmlspecialchars($whatsappDisplay) ?>
              </a>
            </div>
            <small style="color: #6c757d;">Fast response via WhatsApp setiap hari kerja.</small>
          </div>
        </div>

        <div style="display: flex; gap: 14px; align-items: flex-start;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #cfe2ff; color: #084298; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">🕒</div>
          <div>
            <div style="font-weight: 700; color: #212529;">Jam Operasional:</div>
            <div style="color: #495057; margin-top: 3px;"><?= htmlspecialchars($operatingHours) ?></div>
          </div>
        </div>

        <div style="display: flex; gap: 14px; align-items: flex-start;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #f8d7da; color: #842029; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">✉️</div>
          <div>
            <div style="font-weight: 700; color: #212529;">Email Resmi:</div>
            <div style="color: #495057; margin-top: 3px;"><?= htmlspecialchars($storeEmail) ?></div>
          </div>
        </div>
      </div>

      <!-- WhatsApp Direct Big Action -->
      <div style="margin-top: 28px; padding-top: 20px; border-top: 1px solid #dee2e6;">
        <a href="https://wa.me/<?= $whatsappOfficial ?>?text=Halo%20<?= urlencode($storeName) ?>%2C%20saya%20ingin%20berkonsultasi%20seputar%20pesanan%20sembako." 
           target="_blank" rel="noopener" class="btn-wa-checkout" style="text-decoration: none;">
          <span>Hubungi CS Kami Langsung di WhatsApp &rarr;</span>
        </a>
      </div>
    </div>

    <!-- Quick Message / Inquiry Form -->
    <div style="background: #ffffff; border: 1px solid #dee2e6; border-radius: 14px; padding: 32px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
      <h2 style="font-size: 18px; font-weight: 800; color: #212529; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1.5px solid #e8f5e9;">
        Kirim Pesan atau Pertanyaan
      </h2>

      <?php if ($contactSent): ?>
        <div class="flash-alert success" style="margin-bottom: 20px;">
          <span>Pesan Anda telah siap diteruskan ke customer service kami. Silakan klik tombol di bawah untuk membukanya di WhatsApp.</span>
        </div>
      <?php endif; ?>

      <form action="contact.php" method="POST">
        <div class="form-group">
          <label class="form-label" for="c_name">Nama Anda <span class="required">*</span></label>
          <input type="text" id="c_name" name="name" class="form-control" placeholder="Nama lengkap Anda" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="c_phone">Nomor WhatsApp / HP</label>
          <input type="tel" id="c_phone" name="phone" class="form-control" placeholder="Nomor kontak Anda">
        </div>

        <div class="form-group">
          <label class="form-label" for="c_message">Isi Pesan / Pertanyaan <span class="required">*</span></label>
          <textarea id="c_message" name="message" class="form-control" rows="4" placeholder="Tuliskan pertanyaan seputar produk atau kebutuhan sembako Anda..." required></textarea>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; padding: 12px; font-size: 15px;">
          Kirim Pesan
        </button>
      </form>
    </div>

  </div>

</div>

<style>
@media (max-width: 768px) {
  div[style*="grid-template-columns: 1fr 1fr"] {
    grid-template-columns: 1fr !important;
  }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
