<?php
/**
 * JAWA MART - Detail Produk
 * File: product-detail.php
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
$id   = (int)($_GET['id'] ?? 0);

if (empty($slug) && $id <= 0) {
    header('Location: products.php');
    exit;
}

// Cari produk
$product = null;
try {
    if (!empty($slug)) {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name AS category_name, c.slug AS category_slug 
            FROM products p 
            JOIN categories c ON c.id = p.category_id 
            WHERE p.slug = :slug AND p.status = 1 AND p.deleted_at IS NULL 
            LIMIT 1
        ");
        $stmt->execute(['slug' => $slug]);
        $product = $stmt->fetch();
    } elseif ($id > 0) {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name AS category_name, c.slug AS category_slug 
            FROM products p 
            JOIN categories c ON c.id = p.category_id 
            WHERE p.id = :id AND p.status = 1 AND p.deleted_at IS NULL 
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();
    }
} catch (Exception $e) {
    error_log("Error fetching product detail: " . $e->getMessage());
}

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Produk Tidak Ditemukan';
    require_once __DIR__ . '/includes/header.php';
    echo "
        <div class='container' style='padding: 60px 16px; text-align: center;'>
            <h2>Produk Tidak Ditemukan</h2>
            <p style='color: #6c757d; margin: 12px 0 24px;'>Maaf, produk sembako yang Anda cari tidak tersedia atau telah dinonaktifkan.</p>
            <a href='products.php' class='btn-primary'>Kembali ke Katalog Produk</a>
        </div>
    ";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Ambil Galeri Gambar Tambahan jika ada
$galleryImages = [];
try {
    $stmtGal = $pdo->prepare("SELECT * FROM product_images WHERE product_id = :pid ORDER BY is_primary DESC, sort_order ASC");
    $stmtGal->execute(['pid' => $product['id']]);
    $galleryImages = $stmtGal->fetchAll();
} catch (Exception $e) {
    //
}

// Ambil Produk Terkait dari Kategori yang Sama
$relatedProducts = [];
try {
    $stmtRel = $pdo->prepare("
        SELECT p.*, c.name AS category_name 
        FROM products p 
        JOIN categories c ON c.id = p.category_id 
        WHERE p.category_id = :cat_id AND p.id != :pid AND p.status = 1 AND p.deleted_at IS NULL 
        LIMIT 4
    ");
    $stmtRel->execute(['cat_id' => $product['category_id'], 'pid' => $product['id']]);
    $relatedProducts = $stmtRel->fetchAll();
} catch (Exception $e) {
    //
}

$hasDiscount = !empty($product['discount_price']) && $product['discount_price'] < $product['price'];
$currentPrice = $hasDiscount ? (float)$product['discount_price'] : (float)$product['price'];
$discountPercent = $hasDiscount ? round((($product['price'] - $product['discount_price']) / $product['price']) * 100) : 0;
$isOutOfStock = $product['stock'] <= 0;
$isLowStock = !$isOutOfStock && $product['stock'] <= $product['minimum_stock'];

$pageTitle = $product['name'];
$metaDesc  = !empty($product['description']) ? mb_strimwidth(strip_tags($product['description']), 0, 155, '...') : $product['name'] . ' berkualitas di JAWA MART.';

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 24px; padding-bottom: 50px;">
  
  <!-- Breadcrumb -->
  <div style="font-size: 13.5px; color: #6c757d; margin-bottom: 20px;">
    <a href="index.php">Beranda</a> &rsaquo; 
    <a href="products.php">Produk</a> &rsaquo; 
    <a href="products.php?category=<?= urlencode($product['category_slug']) ?>"><?= htmlspecialchars($product['category_name']) ?></a> &rsaquo; 
    <span style="color: #212529; font-weight: 600;"><?= htmlspecialchars($product['name']) ?></span>
  </div>

  <!-- Detail Container -->
  <div style="background: #ffffff; border: 1px solid #dee2e6; border-radius: 14px; padding: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 40px;">
    <div style="display: grid; grid-template-columns: 420px 1fr; gap: 40px; align-items: start;">
      
      <!-- Gallery Column -->
      <div>
        <div style="position: relative; border-radius: 12px; overflow: hidden; border: 1px solid #dee2e6; background: #fafbfc; padding-top: 100%;">
          <?php if ($hasDiscount): ?>
            <span class="badge-discount" style="font-size: 13px; padding: 4px 10px;">Hemat <?= $discountPercent ?>%</span>
          <?php endif; ?>

          <?php if ($isOutOfStock): ?>
            <span class="badge-stock badge-out-of-stock" style="font-size: 12px; padding: 4px 10px;">Stok Habis</span>
          <?php elseif ($isLowStock): ?>
            <span class="badge-stock badge-low-stock" style="font-size: 12px; padding: 4px 10px;">Sisa <?= $product['stock'] ?></span>
          <?php endif; ?>

          <img id="mainProductImg" 
               src="<?= htmlspecialchars(!empty($product['image']) ? $product['image'] : 'assets/images/placeholder.svg') ?>" 
               alt="<?= htmlspecialchars($product['name']) ?>" 
               style="position: absolute; top:0; left:0; width:100%; height:100%; object-fit: contain; padding: 18px;">
        </div>

        <!-- Thumbnails Gallery if available -->
        <?php if (!empty($galleryImages)): ?>
          <div style="display: flex; gap: 10px; margin-top: 12px; overflow-x: auto;">
            <!-- Primary Image Thumb -->
            <div style="width: 64px; height: 64px; border: 2px solid #198754; border-radius: 8px; cursor: pointer; padding: 4px; background: #fff;" onclick="document.getElementById('mainProductImg').src='<?= htmlspecialchars($product['image']) ?>'">
              <img src="<?= htmlspecialchars($product['image']) ?>" style="width:100%; height:100%; object-fit:contain;">
            </div>
            <?php foreach ($galleryImages as $img): ?>
              <div style="width: 64px; height: 64px; border: 1.5px solid #dee2e6; border-radius: 8px; cursor: pointer; padding: 4px; background: #fff;" onclick="document.getElementById('mainProductImg').src='<?= htmlspecialchars($img['image']) ?>'">
                <img src="<?= htmlspecialchars($img['image']) ?>" style="width:100%; height:100%; object-fit:contain;">
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Info Column -->
      <div>
        <div style="display: inline-block; background: #e8f5e9; color: #198754; font-weight: 700; font-size: 12px; padding: 4px 10px; border-radius: 9999px; text-transform: uppercase; margin-bottom: 8px;">
          <?= htmlspecialchars($product['category_name']) ?>
        </div>

        <h1 style="font-size: 26px; font-weight: 800; color: #212529; line-height: 1.3; margin-bottom: 8px;">
          <?= htmlspecialchars($product['name']) ?>
        </h1>

        <div style="font-size: 13px; color: #6c757d; margin-bottom: 16px;">
          SKU: <strong><?= htmlspecialchars($product['sku']) ?></strong> &nbsp;|&nbsp; 
          Satuan: <strong><?= htmlspecialchars($product['unit']) ?></strong>
        </div>

        <!-- Prices -->
        <div style="background: #f8faf9; border-radius: 10px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: baseline; gap: 12px;">
          <span style="font-size: 28px; font-weight: 900; color: #198754;">
            <?= formatRupiah($currentPrice) ?>
          </span>
          <?php if ($hasDiscount): ?>
            <span style="font-size: 16px; color: #6c757d; text-decoration: line-through;">
              <?= formatRupiah($product['price']) ?>
            </span>
            <span style="font-size: 13px; font-weight: 700; color: #dc3545; background: #f8d7da; padding: 2px 8px; border-radius: 4px;">
              Hemat <?= formatRupiah($product['price'] - $product['discount_price']) ?>
            </span>
          <?php endif; ?>
        </div>

        <!-- Stock Status -->
        <div style="margin-bottom: 24px; font-size: 14.5px;">
          <span>Ketersediaan Stok: </span>
          <?php if ($isOutOfStock): ?>
            <strong style="color: #dc3545;">Habis</strong>
          <?php elseif ($isLowStock): ?>
            <strong style="color: #ffc107;">Tersedia (Sisa <?= $product['stock'] ?> <?= htmlspecialchars($product['unit']) ?>)</strong>
          <?php else: ?>
            <strong style="color: #198754;">Tersedia (<?= $product['stock'] ?> <?= htmlspecialchars($product['unit']) ?>)</strong>
          <?php endif; ?>
        </div>

        <!-- Quantity & Add to Cart Controls (PRD Section 4.4) -->
        <?php if (!$isOutOfStock): ?>
          <div style="margin-bottom: 28px;">
            <label style="display: block; font-size: 13.5px; font-weight: 700; margin-bottom: 8px;">Jumlah Pembelian:</label>
            <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
              <div class="qty-control" style="border: 2px solid #dee2e6;">
                <button type="button" class="qty-btn" onclick="stepDownQty()">-</button>
                <input type="number" id="detailQtyInput" class="qty-input" value="1" min="1" max="<?= (int)$product['stock'] ?>" readonly>
                <button type="button" class="qty-btn" onclick="stepUpQty(<?= (int)$product['stock'] ?>)">+</button>
              </div>
              <span style="font-size: 13px; color: #6c757d;">Maksimal: <?= $product['stock'] ?> <?= htmlspecialchars($product['unit']) ?></span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div style="display: flex; gap: 14px; flex-wrap: wrap;">
            <button type="button" class="btn-primary" style="padding: 14px 28px; font-size: 15px;" onclick="addCurrentProductToCart()">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                <path d="M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9V5.5z"/>
                <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5z"/>
              </svg>
              <span>+ Masukkan ke Keranjang</span>
            </button>

            <!-- Direct Buy / Chat WhatsApp Button -->
            <?php 
              $directWaMsg = "Halo JAWA MART, saya tertarik membeli *{$product['name']}* seharga " . formatRupiah($currentPrice) . ". Apakah stoknya masih tersedia?";
              $directWaUrl = "https://wa.me/" . getStoreWhatsApp() . "?text=" . rawurlencode($directWaMsg);
            ?>
            <a href="<?= $directWaUrl ?>" target="_blank" rel="noopener" class="btn-wa-header" style="padding: 14px 22px; font-size: 15px; border-radius: 6px;">
              <span>Tanya / Pesan Langsung via WhatsApp &rarr;</span>
            </a>
          </div>
        <?php else: ?>
          <div style="padding: 16px; background: #f8d7da; color: #842029; border-radius: 8px; font-weight: 600; margin-bottom: 24px;">
            Mohon maaf, stok produk ini saat ini sedang habis. Silakan periksa kembali nanti atau hubungi kami via WhatsApp untuk pemesanan khusus.
          </div>
          <a href="https://wa.me/<?= getStoreWhatsApp() ?>?text=Halo%20JAWA%20MART%2C%20apakah%20produk%20<?= urlencode($product['name']) ?>%20akan%20segera%20restock%3F" target="_blank" rel="noopener" class="btn-wa-header" style="padding: 12px 20px; font-size: 14.5px;">
            Hubungi Toko Mengenai Restock via WhatsApp
          </a>
        <?php endif; ?>

        <!-- Deskripsi Produk -->
        <div style="margin-top: 36px; padding-top: 24px; border-top: 1px solid #dee2e6;">
          <h3 style="font-size: 16px; font-weight: 800; margin-bottom: 12px; color: #212529;">Deskripsi Produk</h3>
          <div style="font-size: 14.5px; line-height: 1.7; color: #495057;">
            <?= nl2br(htmlspecialchars($product['description'] ?? 'Produk sembako berkualitas dari JAWA MART.')) ?>
          </div>
        </div>

      </div>

    </div>
  </div>

  <!-- Related Products Section -->
  <?php if (!empty($relatedProducts)): ?>
    <div style="margin-top: 40px;">
      <div class="section-header">
        <div>
          <h2 class="section-title">Produk Terkait di Kategori Ini</h2>
          <p class="section-subtitle">Pilihan lain yang mungkin Anda butuhkan</p>
        </div>
      </div>

      <div class="products-grid">
        <?php foreach ($relatedProducts as $rel): ?>
          <?php 
            $relDiscount = !empty($rel['discount_price']) && $rel['discount_price'] < $rel['price'];
            $relPrice = $relDiscount ? $rel['discount_price'] : $rel['price'];
          ?>
          <div class="product-card">
            <div class="product-thumb-wrap">
              <a href="product-detail.php?slug=<?= urlencode($rel['slug']) ?>">
                <img src="<?= htmlspecialchars(!empty($rel['image']) ? $rel['image'] : 'assets/images/placeholder.svg') ?>" alt="<?= htmlspecialchars($rel['name']) ?>" loading="lazy">
              </a>
            </div>
            <div class="product-body">
              <span class="product-category"><?= htmlspecialchars($rel['category_name']) ?></span>
              <h3 class="product-title">
                <a href="product-detail.php?slug=<?= urlencode($rel['slug']) ?>"><?= htmlspecialchars($rel['name']) ?></a>
              </h3>
              <div class="product-prices">
                <span class="product-price-current"><?= formatRupiah($relPrice) ?></span>
              </div>
              <div class="product-actions">
                <a href="product-detail.php?slug=<?= urlencode($rel['slug']) ?>" class="btn-primary" style="width: 100%; padding: 7px; font-size: 13px;">
                  Lihat Detail
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

</div>

<script>
function stepDownQty() {
  const input = document.getElementById('detailQtyInput');
  let val = parseInt(input.value) || 1;
  if (val > 1) {
    input.value = val - 1;
  }
}

function stepUpQty(maxStock) {
  const input = document.getElementById('detailQtyInput');
  let val = parseInt(input.value) || 1;
  if (val < maxStock) {
    input.value = val + 1;
  } else {
    JawaCart.showToast('Jumlah tidak boleh melebihi stok yang tersedia (' + maxStock + ').', 'warning');
  }
}

function addCurrentProductToCart() {
  const qty = parseInt(document.getElementById('detailQtyInput').value) || 1;
  const productData = {
    id: <?= (int)$product['id'] ?>,
    name: <?= json_encode($product['name']) ?>,
    price: <?= (float)$currentPrice ?>,
    unit: <?= json_encode($product['unit']) ?>,
    image: <?= json_encode(!empty($product['image']) ? $product['image'] : 'assets/images/placeholder.svg') ?>,
    stock: <?= (int)$product['stock'] ?>,
    sku: <?= json_encode($product['sku']) ?>
  };

  JawaCart.addItem(productData, qty);
}
</script>

<style>
@media (max-width: 820px) {
  div[style*="grid-template-columns: 420px 1fr"] {
    grid-template-columns: 1fr !important;
  }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
