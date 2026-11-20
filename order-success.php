<?php
/**
 * JAWA MART - Halaman Sukses Pemesanan & WhatsApp Redirect
 * File: order-success.php
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$orderCode = trim($_GET['code'] ?? '');

if (empty($orderCode)) {
    header('Location: index.php');
    exit;
}

// Ambil Data Order dari Database
$order = null;
$items = [];
try {
    $stmtOrder = $pdo->prepare("SELECT * FROM orders WHERE order_code = :code LIMIT 1");
    $stmtOrder->execute(['code' => $orderCode]);
    $order = $stmtOrder->fetch();

    if ($order) {
        $stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = :oid ORDER BY id ASC");
        $stmtItems->execute(['oid' => $order['id']]);
        $items = $stmtItems->fetchAll();
    }
} catch (Exception $e) {
    error_log("Error retrieving order in order-success: " . $e->getMessage());
}

if (!$order) {
    http_response_code(404);
    $pageTitle = 'Pesanan Tidak Ditemukan';
    require_once __DIR__ . '/includes/header.php';
    echo "
        <div class='container' style='padding: 60px 16px; text-align: center;'>
            <h2>Pesanan Tidak Ditemukan</h2>
            <p style='color: #6c757d; margin: 12px 0 24px;'>Kode pesanan yang Anda masukkan tidak valid.</p>
            <a href='index.php' class='btn-primary'>Kembali ke Beranda</a>
        </div>
    ";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Generate URL WhatsApp
$waUrl = buildWhatsAppOrderUrl($order, $items);
$storeName = getSetting('store_name', 'JAWA MART');
$whatsappOfficial = getStoreWhatsApp();
$whatsappDisplay  = getSetting('whatsapp_display', '+62 878-1212-2735');

$pageTitle = 'Pesanan Berhasil Disimpan — ' . $order['order_code'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 36px; padding-bottom: 60px;">
  
  <div style="max-width: 680px; margin: 0 auto; background: #ffffff; border: 1px solid #dee2e6; border-radius: 16px; padding: 36px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); text-align: center;">
    
    <!-- Success Icon -->
    <div style="width: 76px; height: 76px; background: #d1e7dd; color: #0f5132; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 38px; margin: 0 auto 18px;">
      ✓
    </div>

    <h1 style="font-size: 24px; font-weight: 800; color: #212529; margin-bottom: 8px;">
      Pesanan Berhasil Dibuat &amp; Tersimpan!
    </h1>
    
    <p style="color: #6c757d; font-size: 14.5px; margin-bottom: 20px;">
      Terima kasih telah berbelanja di <?= htmlspecialchars($storeName) ?>. Pesanan Anda telah tersimpan dengan aman di database toko.
    </p>

    <!-- Order Code Highlight Box -->
    <div style="background: #e8f5e9; border: 1.5px dashed #198754; border-radius: 10px; padding: 16px; margin-bottom: 24px;">
      <div style="font-size: 12.5px; font-weight: 700; color: #198754; text-transform: uppercase;">Kode Pesanan Anda</div>
      <div style="font-size: 26px; font-weight: 900; color: #0f5132; letter-spacing: 1.5px; margin-top: 4px;">
        <?= htmlspecialchars($order['order_code']) ?>
      </div>
      <div style="font-size: 12.5px; color: #6c757d; margin-top: 4px;">
        Status: <strong style="color: #856404; background: #fff3cd; padding: 2px 8px; border-radius: 4px;"><?= htmlspecialchars($order['status']) ?></strong>
      </div>
    </div>

    <!-- WhatsApp Forward CTA Box -->
    <div style="background: #f8faf9; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; margin-bottom: 28px; text-align: left;">
      <div style="display: flex; gap: 12px; align-items: flex-start; margin-bottom: 16px;">
        <div style="font-size: 24px;">💬</div>
        <div>
          <h3 style="font-size: 15.5px; font-weight: 700; color: #212529; margin-bottom: 4px;">Langkah Terakhir: Kirim Rincian ke WhatsApp</h3>
          <p style="font-size: 13.5px; color: #6c757d; line-height: 1.4;">
            Klik tombol hijau di bawah untuk membuka WhatsApp resmi <strong><?= htmlspecialchars($storeName) ?> (<?= htmlspecialchars($whatsappDisplay) ?>)</strong>. Rincian pesanan sudah tersusun otomatis dan siap dikirim!
          </p>
        </div>
      </div>

      <a href="<?= $waUrl ?>" target="_blank" rel="noopener" class="btn-wa-checkout" style="font-size: 16px; padding: 14px 20px; text-decoration: none;">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
          <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.016-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
        </svg>
        <span>Kirim Rincian Pesanan ke WhatsApp Sekarang</span>
      </a>
    </div>

    <!-- Summary Details Table -->
    <div style="text-align: left; background: #ffffff; border: 1px solid #dee2e6; border-radius: 10px; padding: 20px; margin-bottom: 28px;">
      <h3 style="font-size: 15px; font-weight: 800; border-bottom: 1px solid #dee2e6; padding-bottom: 8px; margin-bottom: 14px;">
        Rincian Barang &amp; Pengiriman
      </h3>

      <div style="font-size: 13.5px; line-height: 1.6; margin-bottom: 16px;">
        <div><strong>Nama Pemesan:</strong> <?= htmlspecialchars($order['customer_name']) ?></div>
        <div><strong>Nomor WhatsApp:</strong> <?= htmlspecialchars($order['customer_phone']) ?></div>
        <div><strong>Alamat Tujuan:</strong> <?= nl2br(htmlspecialchars($order['customer_address'])) ?></div>
        <?php if (!empty($order['customer_note'])): ?>
          <div><strong>Catatan:</strong> <?= htmlspecialchars($order['customer_note']) ?></div>
        <?php endif; ?>
      </div>

      <div style="border-top: 1px solid #f1f5f9; padding-top: 12px;">
        <?php foreach ($items as $item): ?>
          <div style="display: flex; justify-content: space-between; font-size: 13.5px; margin-bottom: 8px;">
            <span><?= htmlspecialchars($item['product_name']) ?> &times; <?= $item['quantity'] ?> <?= htmlspecialchars($item['unit']) ?></span>
            <span style="font-weight: 600;"><?= formatRupiah($item['subtotal']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>

      <div style="border-top: 1px dashed #dee2e6; padding-top: 12px; margin-top: 12px; font-size: 14px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
          <span>Subtotal</span>
          <span><?= formatRupiah($order['subtotal']) ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
          <span>Biaya Pengiriman</span>
          <span><?= $order['shipping_cost'] > 0 ? formatRupiah($order['shipping_cost']) : 'GRATIS' ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 17px; font-weight: 800; color: #198754; margin-top: 8px; border-top: 1px solid #dee2e6; padding-top: 8px;">
          <span>Total Pembayaran</span>
          <span><?= formatRupiah($order['total']) ?></span>
        </div>
      </div>
    </div>

    <!-- Navigation Action -->
    <a href="index.php" class="btn" style="background: #e8f5e9; color: #198754; font-weight: 700; padding: 10px 24px; border: 1px solid #c3e6cb;">
      &larr; Kembali ke Beranda JAWA MART
    </a>

  </div>

</div>

<script>
// Bersihkan Keranjang LocalStorage setelah checkout sukses
document.addEventListener('DOMContentLoaded', () => {
  if (typeof JawaCart !== 'undefined') {
    JawaCart.clear();
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
