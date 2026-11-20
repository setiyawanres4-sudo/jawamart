<?php
/**
 * JAWA MART - Keranjang Belanja
 * File: cart.php
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Keranjang Belanja';
$metaDesc  = 'Periksa daftar belanja sembako Anda di keranjang belanja JAWA MART.';

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 24px; padding-bottom: 50px;">
  
  <!-- Breadcrumb -->
  <div style="font-size: 13.5px; color: #6c757d; margin-bottom: 20px;">
    <a href="index.php">Beranda</a> &rsaquo; 
    <span style="color: #212529; font-weight: 600;">Keranjang Belanja</span>
  </div>

  <div class="section-header">
    <div>
      <h1 class="section-title">Keranjang Belanja Anda</h1>
      <p class="section-subtitle">Atur jumlah kebutuhan sembako sebelum melanjutkan ke pemesanan</p>
    </div>
  </div>

  <!-- Cart Content Area (Dynamically rendered by JS from LocalStorage) -->
  <div id="cartContentWrap">
    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;">
      
      <!-- Cart Table Column -->
      <div>
        <div class="cart-table-wrap">
          <table class="cart-table">
            <thead>
              <tr>
                <th>Produk</th>
                <th>Harga</th>
                <th style="text-align: center;">Jumlah</th>
                <th>Subtotal</th>
                <th style="width: 50px;">Aksi</th>
              </tr>
            </thead>
            <tbody id="cartTableBody">
              <!-- Rendered via JS -->
            </tbody>
          </table>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; flex-wrap: wrap; gap: 12px;">
          <a href="products.php" class="btn" style="background: #e8f5e9; color: #198754; font-weight: 700; border: 1px solid #c3e6cb;">
            &larr; Lanjut Belanja Sembako Lainnya
          </a>
          <button type="button" class="btn" style="background: #f8d7da; color: #842029; font-weight: 600;" onclick="confirmClearCart()">
            Kosongkan Keranjang
          </button>
        </div>
      </div>

      <!-- Summary Column -->
      <div>
        <div class="order-summary-card">
          <h3>Ringkasan Keranjang</h3>

          <div class="summary-row">
            <span>Total Barang</span>
            <span id="summaryTotalItems">0 item</span>
          </div>

          <div class="summary-row">
            <span>Subtotal Produk</span>
            <strong id="summarySubtotal">Rp 0</strong>
          </div>

          <div style="font-size: 12.5px; color: #6c757d; background: #e8f5e9; padding: 10px; border-radius: 6px; margin: 14px 0;">
            🚚 Ongkos kirim dan total akhir akan dihitung otomatis saat checkout sesuai alamat pengiriman Anda.
          </div>

          <div class="summary-row summary-total">
            <span>Estimasi Total</span>
            <span id="summaryEstimateTotal">Rp 0</span>
          </div>

          <a href="checkout.php" class="btn-primary" style="width: 100%; margin-top: 20px; padding: 14px; font-size: 15px; text-decoration: none;">
            Lanjut ke Form Checkout &rarr;
          </a>
        </div>
      </div>

    </div>
  </div>

  <!-- Empty Cart State -->
  <div id="cartEmptyState" style="display: none; background: #ffffff; border: 1px dashed #dee2e6; border-radius: 12px; padding: 60px 20px; text-align: center;">
    <div style="font-size: 56px; margin-bottom: 16px;">🛒</div>
    <h2 style="font-size: 20px; font-weight: 800; color: #212529; margin-bottom: 8px;">Keranjang Belanja Anda Masih Kosong</h2>
    <p style="color: #6c757d; font-size: 14.5px; max-width: 420px; margin: 0 auto 24px;">
      Anda belum menambahkan produk sembako ke dalam keranjang. Yuk pilih kebutuhan harian keluarga sekarang!
    </p>
    <a href="products.php" class="btn-primary" style="padding: 12px 28px;">Mulai Belanja Sembako</a>
  </div>

</div>

<script>
function formatRupiahJs(number) {
  return 'Rp ' + Number(number).toLocaleString('id-ID');
}

function renderCartView() {
  const items = JawaCart.getItems();
  const contentWrap = document.getElementById('cartContentWrap');
  const emptyState = document.getElementById('cartEmptyState');
  const tableBody = document.getElementById('cartTableBody');

  if (items.length === 0) {
    contentWrap.style.display = 'none';
    emptyState.style.display = 'block';
    return;
  }

  contentWrap.style.display = 'block';
  emptyState.style.display = 'none';

  let totalItemsCount = 0;
  let subtotal = 0;
  let rowsHtml = '';

  items.forEach(item => {
    const qty = parseInt(item.quantity) || 1;
    const itemSubtotal = item.price * qty;
    totalItemsCount += qty;
    subtotal += itemSubtotal;

    rowsHtml += `
      <tr>
        <td>
          <div class="cart-item-info">
            <img src="${item.image || 'assets/images/placeholder.svg'}" alt="${item.name}" class="cart-item-img">
            <div>
              <div style="font-weight: 700; color: #212529; font-size: 14px;">${item.name}</div>
              <div style="font-size: 12px; color: #6c757d;">Satuan: ${item.unit || 'pcs'}</div>
            </div>
          </div>
        </td>
        <td>
          <span style="font-weight: 600; color: #198754;">${formatRupiahJs(item.price)}</span>
        </td>
        <td style="text-align: center;">
          <div class="qty-control">
            <button type="button" class="qty-btn" onclick="JawaCart.updateQuantity(${item.id}, ${qty - 1})">-</button>
            <input type="text" class="qty-input" value="${qty}" readonly>
            <button type="button" class="qty-btn" onclick="JawaCart.updateQuantity(${item.id}, ${qty + 1})">+</button>
          </div>
        </td>
        <td>
          <strong style="color: #212529; font-size: 14.5px;">${formatRupiahJs(itemSubtotal)}</strong>
        </td>
        <td>
          <button type="button" class="btn-remove-item" onclick="JawaCart.removeItem(${item.id})" title="Hapus produk">&times;</button>
        </td>
      </tr>
    `;
  });

  tableBody.innerHTML = rowsHtml;
  document.getElementById('summaryTotalItems').textContent = totalItemsCount + ' item';
  document.getElementById('summarySubtotal').textContent = formatRupiahJs(subtotal);
  document.getElementById('summaryEstimateTotal').textContent = formatRupiahJs(subtotal);
}

function confirmClearCart() {
  if (confirm('Apakah Anda yakin ingin mengosongkan seluruh isi keranjang belanja?')) {
    JawaCart.clear();
  }
}

// Listen for updates
window.addEventListener('jawa_cart:updated', renderCartView);
document.addEventListener('DOMContentLoaded', renderCartView);
</script>

<style>
@media (max-width: 860px) {
  #cartContentWrap > div {
    grid-template-columns: 1fr !important;
  }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
