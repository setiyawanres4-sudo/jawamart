<?php
/**
 * JAWA MART - Beranda (Homepage)
 * File: index.php
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'JAWA MART — Belanja Sembako Mudah, Hemat, dan Praktis';
$metaDesc  = 'JAWA MART menyediakan aneka kebutuhan sembako dan kebutuhan rumah tangga berkualitas dengan harga hemat dan pemesanan praktis via WhatsApp.';

// Ambil Banner Aktif
$banners = [];
try {
    $stmtBanners = $pdo->query("SELECT * FROM banners WHERE status = 1 ORDER BY sort_order ASC, id ASC");
    $banners = $stmtBanners->fetchAll();
} catch (Throwable $e) {
    error_log("Error fetching banners: " . $e->getMessage());
}

// Ambil Kategori Aktif beserta Jumlah Produk
$categories = [];
try {
    $stmtCat = $pdo->query("
        SELECT c.*, COUNT(p.id) AS total_products 
        FROM categories c 
        LEFT JOIN products p ON p.category_id = c.id AND p.status = 1 AND p.deleted_at IS NULL 
        WHERE c.status = 1 
        GROUP BY c.id 
        ORDER BY c.id ASC
    ");
    $categories = $stmtCat->fetchAll();
} catch (Throwable $e) {
    error_log("Error fetching categories: " . $e->getMessage());
}

// Ambil Produk Unggulan / Rekomendasi
$featuredProducts = [];
try {
    $stmtFeat = $pdo->query("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug 
        FROM products p 
        JOIN categories c ON c.id = p.category_id 
        WHERE p.is_featured = 1 AND p.status = 1 AND p.deleted_at IS NULL 
        ORDER BY p.id DESC 
        LIMIT 8
    ");
    $featuredProducts = $stmtFeat->fetchAll();
} catch (Throwable $e) {
    error_log("Error fetching featured products: " . $e->getMessage());
}

// Ambil Produk Terbaru
$latestProducts = [];
try {
    $stmtLatest = $pdo->query("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug 
        FROM products p 
        JOIN categories c ON c.id = p.category_id 
        WHERE p.status = 1 AND p.deleted_at IS NULL 
        ORDER BY p.id DESC 
        LIMIT 8
    ");
    $latestProducts = $stmtLatest->fetchAll();
} catch (Throwable $e) {
    error_log("Error fetching latest products: " . $e->getMessage());
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner Slider -->
<section class="hero-slider-section">
  <div class="container">
    <div class="slider-container">
      <div class="slider-track">
        <?php if (!empty($banners)): ?>
          <?php foreach ($banners as $banner): ?>
            <div class="slide">
              <a href="<?= !empty($banner['button_link']) ? htmlspecialchars($banner['button_link']) : 'products.php' ?>">
                <img src="<?= htmlspecialchars($banner['image']) ?>" alt="<?= htmlspecialchars($banner['title']) ?>" loading="lazy">
              </a>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="slide">
            <a href="products.php">
              <img src="uploads/banners/banner-1.svg" alt="Promo Sembako JAWA MART">
            </a>
          </div>
        <?php endif; ?>
      </div>

      <?php if (count($banners) > 1): ?>
        <button class="slider-btn prev" aria-label="Banner Sebelumnya">&lt;</button>
        <button class="slider-btn next" aria-label="Banner Selanjutnya">&gt;</button>
        <div class="slider-dots"></div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Keunggulan Toko Bar -->
<section class="features-section">
  <div class="container">
    <div class="features-grid">
      <div class="feature-box">
        <div class="feature-icon">🚚</div>
        <div class="feature-info">
          <h4>Pengiriman Cepat</h4>
          <p>Sembako diantar langsung ke rumah dengan aman dan terjaga kesegarannya.</p>
        </div>
      </div>
      <div class="feature-box">
        <div class="feature-icon">💰</div>
        <div class="feature-info">
          <h4>Harga Hemat &amp; Grosir</h4>
          <p>Kualitas terjamin dengan harga bersahabat untuk belanja harian keluarga.</p>
        </div>
      </div>
      <div class="feature-box">
        <div class="feature-info">
          <h4>💬 Checkout WhatsApp</h4>
          <p>Pemesanan otomatis terhubung ke WhatsApp resmi JAWA MART tanpa ribet login.</p>
        </div>
      </div>
      <div class="feature-box">
        <div class="feature-info">
          <h4>✨ 100% Produk Asli</h4>
          <p>Beras pulen, minyak jernih, gula, dan bahan makanan pilihan terpercaya.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Kategori Sembako -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">Kategori Sembako</h2>
        <p class="section-subtitle">Temukan bahan pokok sehari-hari sesuai kebutuhan Anda</p>
      </div>
      <a href="categories.php" class="view-all-link">Lihat Semua Kategori &rarr;</a>
    </div>

    <div class="categories-grid">
      <?php foreach ($categories as $cat): ?>
        <a href="products.php?category=<?= urlencode($cat['slug']) ?>" class="category-card">
          <div class="category-icon-box">
            <img src="<?= htmlspecialchars(!empty($cat['image']) ? $cat['image'] : 'assets/images/placeholder.svg') ?>" alt="<?= htmlspecialchars($cat['name']) ?>" loading="lazy">
          </div>
          <span class="category-name"><?= htmlspecialchars($cat['name']) ?></span>
          <span class="category-count"><?= (int)$cat['total_products'] ?> Produk</span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Produk Rekomendasi / Unggulan -->
<section class="section" style="background-color: #ffffff; padding-top: 40px; padding-bottom: 40px; border-top: 1px solid #eef2f5; border-bottom: 1px solid #eef2f5;">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">Produk Rekomendasi</h2>
        <p class="section-subtitle">Pilihan sembako paling dicari dengan harga hemat terbaik</p>
      </div>
      <a href="products.php?featured=1" class="view-all-link">Lihat Semua &rarr;</a>
    </div>

    <div class="products-grid">
      <?php if (!empty($featuredProducts)): ?>
        <?php foreach ($featuredProducts as $product): ?>
          <?php 
            $hasDiscount = !empty($product['discount_price']) && $product['discount_price'] < $product['price'];
            $currentPrice = $hasDiscount ? $product['discount_price'] : $product['price'];
            $discountPercent = $hasDiscount ? round((($product['price'] - $product['discount_price']) / $product['price']) * 100) : 0;
            $isOutOfStock = $product['stock'] <= 0;
            $isLowStock = !$isOutOfStock && $product['stock'] <= $product['minimum_stock'];
          ?>
          <div class="product-card">
            <div class="product-thumb-wrap">
              <?php if ($hasDiscount): ?>
                <span class="badge-discount">-<?= $discountPercent ?>%</span>
              <?php endif; ?>

              <?php if ($isOutOfStock): ?>
                <span class="badge-stock badge-out-of-stock">Habis</span>
              <?php elseif ($isLowStock): ?>
                <span class="badge-stock badge-low-stock">Sisa <?= $product['stock'] ?></span>
              <?php endif; ?>

              <a href="product-detail.php?slug=<?= urlencode($product['slug']) ?>">
                <img src="<?= htmlspecialchars(!empty($product['image']) ? $product['image'] : 'assets/images/placeholder.svg') ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
              </a>
            </div>

            <div class="product-body">
              <span class="product-category"><?= htmlspecialchars($product['category_name']) ?></span>
              <h3 class="product-title">
                <a href="product-detail.php?slug=<?= urlencode($product['slug']) ?>"><?= htmlspecialchars($product['name']) ?></a>
              </h3>
              <div class="product-unit">Satuan: <strong><?= htmlspecialchars($product['unit']) ?></strong></div>

              <div class="product-prices">
                <span class="product-price-current"><?= formatRupiah($currentPrice) ?></span>
                <?php if ($hasDiscount): ?>
                  <span class="product-price-normal"><?= formatRupiah($product['price']) ?></span>
                <?php endif; ?>
              </div>

              <div class="product-actions">
                <button type="button" 
                        class="btn-add-cart" 
                        <?= $isOutOfStock ? 'disabled' : '' ?>
                        onclick='JawaCart.addItem(<?= json_encode([
                            'id' => (int)$product['id'],
                            'name' => $product['name'],
                            'price' => (float)$currentPrice,
                            'unit' => $product['unit'],
                            'image' => !empty($product['image']) ? $product['image'] : 'assets/images/placeholder.svg',
                            'stock' => (int)$product['stock'],
                            'sku' => $product['sku']
                        ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>, 1)'>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9V5.5z"/>
                    <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5z"/>
                  </svg>
                  <span><?= $isOutOfStock ? 'Habis' : '+ Keranjang' ?></span>
                </button>
                <a href="product-detail.php?slug=<?= urlencode($product['slug']) ?>" class="btn-detail-link" title="Detail Produk">
                  Detail
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Produk Terbaru -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">Katalog Sembako Terbaru</h2>
        <p class="section-subtitle">Stok sembako fresh baru masuk siap dipesan</p>
      </div>
      <a href="products.php" class="view-all-link">Lihat Semua Katalog &rarr;</a>
    </div>

    <div class="products-grid">
      <?php if (!empty($latestProducts)): ?>
        <?php foreach ($latestProducts as $product): ?>
          <?php 
            $hasDiscount = !empty($product['discount_price']) && $product['discount_price'] < $product['price'];
            $currentPrice = $hasDiscount ? $product['discount_price'] : $product['price'];
            $discountPercent = $hasDiscount ? round((($product['price'] - $product['discount_price']) / $product['price']) * 100) : 0;
            $isOutOfStock = $product['stock'] <= 0;
            $isLowStock = !$isOutOfStock && $product['stock'] <= $product['minimum_stock'];
          ?>
          <div class="product-card">
            <div class="product-thumb-wrap">
              <?php if ($hasDiscount): ?>
                <span class="badge-discount">-<?= $discountPercent ?>%</span>
              <?php endif; ?>

              <?php if ($isOutOfStock): ?>
                <span class="badge-stock badge-out-of-stock">Habis</span>
              <?php elseif ($isLowStock): ?>
                <span class="badge-stock badge-low-stock">Sisa <?= $product['stock'] ?></span>
              <?php endif; ?>

              <a href="product-detail.php?slug=<?= urlencode($product['slug']) ?>">
                <img src="<?= htmlspecialchars(!empty($product['image']) ? $product['image'] : 'assets/images/placeholder.svg') ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
              </a>
            </div>

            <div class="product-body">
              <span class="product-category"><?= htmlspecialchars($product['category_name']) ?></span>
              <h3 class="product-title">
                <a href="product-detail.php?slug=<?= urlencode($product['slug']) ?>"><?= htmlspecialchars($product['name']) ?></a>
              </h3>
              <div class="product-unit">Satuan: <strong><?= htmlspecialchars($product['unit']) ?></strong></div>

              <div class="product-prices">
                <span class="product-price-current"><?= formatRupiah($currentPrice) ?></span>
                <?php if ($hasDiscount): ?>
                  <span class="product-price-normal"><?= formatRupiah($product['price']) ?></span>
                <?php endif; ?>
              </div>

              <div class="product-actions">
                <button type="button" 
                        class="btn-add-cart" 
                        <?= $isOutOfStock ? 'disabled' : '' ?>
                        onclick='JawaCart.addItem(<?= json_encode([
                            'id' => (int)$product['id'],
                            'name' => $product['name'],
                            'price' => (float)$currentPrice,
                            'unit' => $product['unit'],
                            'image' => !empty($product['image']) ? $product['image'] : 'assets/images/placeholder.svg',
                            'stock' => (int)$product['stock'],
                            'sku' => $product['sku']
                        ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>, 1)'>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9V5.5z"/>
                    <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5z"/>
                  </svg>
                  <span><?= $isOutOfStock ? 'Habis' : '+ Keranjang' ?></span>
                </button>
                <a href="product-detail.php?slug=<?= urlencode($product['slug']) ?>" class="btn-detail-link" title="Detail Produk">
                  Detail
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
