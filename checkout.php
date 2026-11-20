<?php
/**
 * JAWA MART - Halaman Checkout & Pemesanan
 * File: checkout.php
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Checkout Pesanan Sembako';
$metaDesc  = 'Lengkapi data pemesanan sembako Anda untuk diproses oleh JAWA MART.';

$shippingCostDefault = (float)getSetting('default_shipping_cost', '10000');
$freeShippingMin     = (float)getSetting('free_shipping_minimum', '150000');
$storeName           = getSetting('store_name', 'JAWA MART');

// Tangani Pengiriman Form Checkout (POST)
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        $errorMessage = 'Sesi form Anda telah kedaluwarsa. Silakan muat ulang halaman.';
    } else {
        $customerName    = trim($_POST['customer_name'] ?? '');
        $customerPhone   = trim($_POST['customer_phone'] ?? '');
        $customerAddress = trim($_POST['customer_address'] ?? '');
        $customerNote    = trim($_POST['customer_note'] ?? '');
        $cartDataJson    = trim($_POST['cart_data'] ?? '[]');

        if (empty($customerName) || empty($customerPhone) || empty($customerAddress)) {
            $errorMessage = 'Nama, nomor WhatsApp, dan alamat pengiriman wajib diisi lengkap.';
        } else {
            $cartItems = json_decode($cartDataJson, true);

            if (empty($cartItems) || !is_array($cartItems)) {
                $errorMessage = 'Keranjang belanja Anda kosong. Silakan pilih produk terlebih dahulu.';
            } else {
                // ATURAN TRANSAKSI (PRD 5.5): Harga dan stok wajib diperiksa langsung dari database server!
                try {
                    $pdo->beginTransaction();

                    $productIds = array_column($cartItems, 'id');
                    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
                    
                    // Kunci baris untuk pengecekan konsistensi
                    $stmtCheck = $pdo->prepare("
                        SELECT id, sku, name, price, discount_price, stock, unit, image, status, deleted_at 
                        FROM products 
                        WHERE id IN ($placeholders)
                    ");
                    $stmtCheck->execute($productIds);
                    $dbProducts = [];
                    while ($row = $stmtCheck->fetch()) {
                        $dbProducts[$row['id']] = $row;
                    }

                    $verifiedOrderItems = [];
                    $subtotal = 0.0;
                    $stockError = null;

                    foreach ($cartItems as $cItem) {
                        $pId = (int)($cItem['id'] ?? 0);
                        $qty = max(1, (int)($cItem['quantity'] ?? 1));

                        if (!isset($dbProducts[$pId]) || $dbProducts[$pId]['status'] != 1 || $dbProducts[$pId]['deleted_at'] !== null) {
                            $stockError = "Produk '" . ($cItem['name'] ?? 'Pilihan') . "' sudah tidak tersedia.";
                            break;
                        }

                        $p = $dbProducts[$pId];

                        // Cek ketersediaan stok
                        if ($qty > (int)$p['stock']) {
                            $stockError = "Stok untuk '" . $p['name'] . "' hanya tersisa " . $p['stock'] . " " . $p['unit'] . ". Harap sesuaikan jumlah pesanan Anda.";
                            break;
                        }

                        // Tentukan harga server (bukan dari client localStorage!)
                        $effectivePrice = (!empty($p['discount_price']) && $p['discount_price'] < $p['price']) 
                            ? (float)$p['discount_price'] 
                            : (float)$p['price'];

                        $itemSubtotal = $effectivePrice * $qty;
                        $subtotal += $itemSubtotal;

                        $verifiedOrderItems[] = [
                            'product_id'    => (int)$p['id'],
                            'product_name'  => $p['name'],
                            'product_sku'   => $p['sku'],
                            'product_image' => $p['image'],
                            'unit'          => $p['unit'],
                            'quantity'      => $qty,
                            'unit_price'    => $effectivePrice,
                            'subtotal'      => $itemSubtotal
                        ];
                    }

                    if ($stockError !== null) {
                        $pdo->rollBack();
                        $errorMessage = $stockError;
                    } else {
                        // Hitung Ongkos Kirim di Server
                        $shippingCost = $shippingCostDefault;
                        if ($freeShippingMin > 0 && $subtotal >= $freeShippingMin) {
                            $shippingCost = 0.0;
                        }

                        $grandTotal = $subtotal + $shippingCost;
                        $orderCode = generateOrderCode();

                        // 1. Simpan ke Tabel orders
                        $stmtOrder = $pdo->prepare("
                            INSERT INTO orders (
                                order_code, customer_name, customer_phone, customer_address, customer_note, 
                                subtotal, shipping_cost, total, status, stock_deducted, created_at, updated_at
                            ) VALUES (
                                :order_code, :customer_name, :customer_phone, :customer_address, :customer_note, 
                                :subtotal, :shipping_cost, :total, 'Menunggu Konfirmasi', 0, NOW(), NOW()
                            )
                        ");

                        $stmtOrder->execute([
                            'order_code'       => $orderCode,
                            'customer_name'    => $customerName,
                            'customer_phone'   => cleanPhoneForWhatsApp($customerPhone),
                            'customer_address' => $customerAddress,
                            'customer_note'    => $customerNote,
                            'subtotal'         => $subtotal,
                            'shipping_cost'    => $shippingCost,
                            'total'            => $grandTotal
                        ]);

                        $orderId = (int)$pdo->lastInsertId();

                        // 2. Simpan snapshot ke Tabel order_items
                        $stmtItem = $pdo->prepare("
                            INSERT INTO order_items (
                                order_id, product_id, product_name, product_sku, product_image, 
                                unit, quantity, unit_price, subtotal, created_at
                            ) VALUES (
                                :order_id, :product_id, :product_name, :product_sku, :product_image, 
                                :unit, :quantity, :unit_price, :subtotal, NOW()
                            )
                        ");

                        foreach ($verifiedOrderItems as $vItem) {
                            $stmtItem->execute([
                                'order_id'      => $orderId,
                                'product_id'    => $vItem['product_id'],
                                'product_name'  => $vItem['product_name'],
                                'product_sku'   => $vItem['product_sku'],
                                'product_image' => $vItem['product_image'],
                                'unit'          => $vItem['unit'],
                                'quantity'      => $vItem['quantity'],
                                'unit_price'    => $vItem['unit_price'],
                                'subtotal'      => $vItem['subtotal']
                            ]);
                        }

                        // 3. Catat Riwayat Status Pesanan (PRD Section 5.3 & 8.7)
                        $stmtHistory = $pdo->prepare("
                            INSERT INTO order_status_history (
                                order_id, admin_id, old_status, new_status, note, created_at
                            ) VALUES (
                                :order_id, NULL, NULL, 'Menunggu Konfirmasi', 'Pesanan baru dibuat oleh pelanggan via website JAWA MART', NOW()
                            )
                        ");
                        $stmtHistory->execute(['order_id' => $orderId]);

                        // Komit transaksi
                        $pdo->commit();

                        // Siapkan Pesan WhatsApp dan Redirect
                        $orderData = [
                            'id'               => $orderId,
                            'order_code'       => $orderCode,
                            'customer_name'    => $customerName,
                            'customer_phone'   => $customerPhone,
                            'customer_address' => $customerAddress,
                            'customer_note'    => $customerNote,
                            'subtotal'         => $subtotal,
                            'shipping_cost'    => $shippingCost,
                            'total'            => $grandTotal
                        ];

                        $waUrl = buildWhatsAppOrderUrl($orderData, $verifiedOrderItems);

                        $_SESSION['recent_order'] = [
                            'order' => $orderData,
                            'items' => $verifiedOrderItems,
                            'wa_url'=> $waUrl
                        ];

                        // Redirect ke halaman sukses
                        header("Location: order-success.php?code=" . urlencode($orderCode));
                        exit;
                    }
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    error_log("Checkout transaction error: " . $e->getMessage());
                    $errorMessage = 'Terjadi kesalahan sistem saat memproses pesanan Anda. Silakan coba kembali.';
                }
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 24px; padding-bottom: 50px;">
  
  <!-- Breadcrumb -->
  <div style="font-size: 13.5px; color: #6c757d; margin-bottom: 20px;">
    <a href="index.php">Beranda</a> &rsaquo; 
    <a href="cart.php">Keranjang</a> &rsaquo; 
    <span style="color: #212529; font-weight: 600;">Checkout Pemesanan</span>
  </div>

  <div class="section-header">
    <div>
      <h1 class="section-title">Konfirmasi &amp; Checkout Pesanan</h1>
      <p class="section-subtitle">Isi data pengiriman Anda. Pesanan akan otomatis tersimpan dan dikonfirmasi via WhatsApp.</p>
    </div>
  </div>

  <?php if (!empty($errorMessage)): ?>
    <div class="flash-alert danger" style="margin-bottom: 24px;">
      <span>⚠️ <?= htmlspecialchars($errorMessage) ?></span>
    </div>
  <?php endif; ?>

  <!-- Checkout Grid (Form + Summary) -->
  <div id="checkoutContent" style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 32px; align-items: start;">
    
    <!-- Customer Data Form -->
    <div style="background: #ffffff; border: 1px solid #dee2e6; border-radius: 12px; padding: 28px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
      <h2 style="font-size: 18px; font-weight: 800; color: #212529; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid #dee2e6;">
        Data Pengiriman Pelanggan
      </h2>

      <form id="checkoutForm" action="checkout.php" method="POST" onsubmit="return validateCheckoutSubmission()">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
        <input type="hidden" name="cart_data" id="cartDataHidden">

        <div class="form-group">
          <label class="form-label" for="customer_name">Nama Lengkap <span class="required">*</span></label>
          <input type="text" id="customer_name" name="customer_name" class="form-control" placeholder="Contoh: Ibu Siti Rahayu" required value="<?= htmlspecialchars($_POST['customer_name'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="customer_phone">Nomor WhatsApp Aktif <span class="required">*</span></label>
          <input type="tel" id="customer_phone" name="customer_phone" class="form-control" placeholder="Contoh: 081234567890" required value="<?= htmlspecialchars($_POST['customer_phone'] ?? '') ?>">
          <small style="color: #6c757d; font-size: 12px;">Untuk konfirmasi pesanan dan informasi pengiriman oleh kurir toko.</small>
        </div>

        <div class="form-group">
          <label class="form-label" for="customer_address">Alamat Pengiriman Lengkap <span class="required">*</span></label>
          <textarea id="customer_address" name="customer_address" class="form-control" rows="3" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan, patokan..." required><?= htmlspecialchars($_POST['customer_address'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
          <label class="form-label" for="customer_note">Catatan Tambahan (Opsional)</label>
          <input type="text" id="customer_note" name="customer_note" class="form-control" placeholder="Contoh: Tolong antar sebelum jam 12 siang / titip satpam" value="<?= htmlspecialchars($_POST['customer_note'] ?? '') ?>">
        </div>

        <div style="background: #e8f5e9; border: 1px solid #c3e6cb; border-radius: 8px; padding: 14px; margin: 20px 0; font-size: 13.5px; color: #0f5132;">
          <strong>🔒 Tanpa Ribet Bikin Akun!</strong><br>
          Pesanan Anda langsung tersimpan ke sistem database JAWA MART dan otomatis membuka WhatsApp toko untuk konfirmasi instan.
        </div>

        <button type="submit" id="btnSubmitCheckout" class="btn-wa-checkout">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
            <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.016-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
          </svg>
          <span>Pesan Sekarang via WhatsApp</span>
        </button>
      </form>
    </div>

    <!-- Order Items Summary Column -->
    <div>
      <div class="order-summary-card">
        <h3>Ringkasan Pesanan</h3>

        <div id="checkoutItemsList" style="max-height: 280px; overflow-y: auto; margin-bottom: 16px; padding-right: 4px;">
          <!-- Rendered via JS -->
        </div>

        <div class="summary-row">
          <span>Subtotal Produk</span>
          <span id="coSubtotal" style="font-weight: 700;">Rp 0</span>
        </div>

        <div class="summary-row">
          <span>Biaya Pengiriman</span>
          <span id="coShipping" style="font-weight: 700;">Rp <?= number_format($shippingCostDefault, 0, ',', '.') ?></span>
        </div>

        <div id="coFreeShippingBadge" style="display: none; background: #d1e7dd; color: #0f5132; font-size: 12px; font-weight: 700; padding: 6px 10px; border-radius: 4px; margin-bottom: 10px;">
          🎉 Selamat! Anda mendapatkan Gratis Ongkos Kirim (Belanja &ge; <?= formatRupiah($freeShippingMin) ?>).
        </div>

        <div class="summary-row summary-total">
          <span>Total Pembayaran</span>
          <span id="coTotal">Rp 0</span>
        </div>
      </div>
    </div>

  </div>

  <!-- If Cart Empty State -->
  <div id="checkoutEmptyState" style="display: none; background: #ffffff; border: 1px dashed #dee2e6; border-radius: 12px; padding: 50px 20px; text-align: center;">
    <div style="font-size: 50px; margin-bottom: 14px;">🛍️</div>
    <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Keranjang Belanja Anda Kosong</h3>
    <p style="color: #6c757d; font-size: 14px; margin-bottom: 20px;">Silakan pilih kebutuhan sembako Anda terlebih dahulu sebelum melakukan checkout.</p>
    <a href="products.php" class="btn-primary">Lihat Produk Sembako</a>
  </div>

</div>

<script>
const defaultShippingCost = <?= $shippingCostDefault ?>;
const freeShippingMin = <?= $freeShippingMin ?>;

function formatRupiahJs(number) {
  return 'Rp ' + Number(number).toLocaleString('id-ID');
}

function renderCheckoutSummary() {
  const items = JawaCart.getItems();
  const checkoutContent = document.getElementById('checkoutContent');
  const emptyState = document.getElementById('checkoutEmptyState');
  const itemsListEl = document.getElementById('checkoutItemsList');

  if (items.length === 0) {
    checkoutContent.style.display = 'none';
    emptyState.style.display = 'block';
    return;
  }

  checkoutContent.style.display = 'grid';
  emptyState.style.display = 'none';

  let subtotal = 0;
  let itemsHtml = '';

  items.forEach(item => {
    const qty = parseInt(item.quantity) || 1;
    const itemSubtotal = item.price * qty;
    subtotal += itemSubtotal;

    itemsHtml += `
      <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
        <img src="${item.image || 'assets/images/placeholder.svg'}" alt="${item.name}" style="width: 48px; height: 48px; object-fit: contain; border-radius: 6px; background: #fafbfc; border: 1px solid #e2e8f0; padding: 2px;">
        <div style="flex: 1; min-width: 0;">
          <div style="font-weight: 700; font-size: 13.5px; color: #212529; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.name}</div>
          <div style="font-size: 12px; color: #6c757d;">${qty} ${item.unit || 'pcs'} × ${formatRupiahJs(item.price)}</div>
        </div>
        <div style="font-weight: 700; font-size: 13.5px; color: #198754;">
          ${formatRupiahJs(itemSubtotal)}
        </div>
      </div>
    `;
  });

  itemsListEl.innerHTML = itemsHtml;
  document.getElementById('coSubtotal').textContent = formatRupiahJs(subtotal);

  let shipping = defaultShippingCost;
  if (freeShippingMin > 0 && subtotal >= freeShippingMin) {
    shipping = 0;
    document.getElementById('coFreeShippingBadge').style.display = 'block';
    document.getElementById('coShipping').textContent = 'GRATIS (Rp 0)';
  } else {
    document.getElementById('coFreeShippingBadge').style.display = 'none';
    document.getElementById('coShipping').textContent = formatRupiahJs(shipping);
  }

  const grandTotal = subtotal + shipping;
  document.getElementById('coTotal').textContent = formatRupiahJs(grandTotal);

  // Set json to hidden field
  document.getElementById('cartDataHidden').value = JSON.stringify(items);
}

function validateCheckoutSubmission() {
  const items = JawaCart.getItems();
  if (items.length === 0) {
    alert('Keranjang belanja Anda masih kosong.');
    return false;
  }

  const btn = document.getElementById('btnSubmitCheckout');
  btn.disabled = true;
  btn.innerHTML = '<span>Sedang Memproses Pesanan...</span>';
  return true;
}

document.addEventListener('DOMContentLoaded', renderCheckoutSummary);
</script>

<style>
@media (max-width: 860px) {
  #checkoutContent {
    grid-template-columns: 1fr !important;
  }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
