<?php
/**
 * JAWA MART - Katalog Semua Produk
 * File: products.php
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

// Parameter Filter & Pagination
$q          = trim($_GET['q'] ?? '');
$catSlug    = trim($_GET['category'] ?? '');
$sort       = trim($_GET['sort'] ?? 'latest');
$isFeatured = isset($_GET['featured']) && $_GET['featured'] == '1';
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 12;
$offset     = ($page - 1) * $perPage;

// Base Query Conditions
$whereConditions = ["p.status = 1", "p.deleted_at IS NULL"];
$params = [];

if (!empty($q)) {
    $whereConditions[] = "(p.name LIKE :q OR p.sku LIKE :q OR p.description LIKE :q)";
    $params['q'] = '%' . $q . '%';
}

$selectedCategory = null;
if (!empty($catSlug)) {
    $stmtFindCat = $pdo->prepare("SELECT * FROM categories WHERE slug = :cslug LIMIT 1");
    $stmtFindCat->execute(['cslug' => $catSlug]);
    $selectedCategory = $stmtFindCat->fetch();
    if ($selectedCategory) {
        $whereConditions[] = "p.category_id = :cat_id";
        $params['cat_id'] = $selectedCategory['id'];
    }
}

if ($isFeatured) {
    $whereConditions[] = "p.is_featured = 1";
}

$whereClause = "WHERE " . implode(" AND ", $whereConditions);

// Sorting
$orderBy = "p.id DESC";
if ($sort === 'price_asc') {
    $orderBy = "COALESCE(p.discount_price, p.price) ASC";
} elseif ($sort === 'price_desc') {
    $orderBy = "COALESCE(p.discount_price, p.price) DESC";
} elseif ($sort === 'name_asc') {
    $orderBy = "p.name ASC";
}

// Hitung Total Produk
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id $whereClause");
$stmtCount->execute($params);
$totalProducts = (int)$stmtCount->fetchColumn();
$totalPages = max(1, (int)ceil($totalProducts / $perPage));

// Ambil Data Produk
$sqlProducts = "
    SELECT p.*, c.name AS category_name, c.slug AS category_slug 
    FROM products p 
    JOIN categories c ON c.id = p.category_id 
    $whereClause 
    ORDER BY $orderBy 
    LIMIT $perPage OFFSET $offset
";
$stmtProducts = $pdo->prepare($sqlProducts);
$stmtProducts->execute($params);
$products = $stmtProducts->fetchAll();

// Ambil Seluruh Kategori Aktif untuk Sidebar Filter
$allCategories = [];
try {
    $stmtCats = $pdo->query("
        SELECT c.*, COUNT(p.id) AS total_products 
        FROM categories c 
        LEFT JOIN products p ON p.category_id = c.id AND p.status = 1 AND p.deleted_at IS NULL 
        WHERE c.status = 1 
        GROUP BY c.id 
        ORDER BY c.name ASC
    ");
    $allCategories = $stmtCats->fetchAll();
} catch (Exception $e) {
    //
}

$pageTitle = !empty($selectedCategory) ? 'Kategori ' . $selectedCategory['name'] : (!empty($q) ? 'Hasil Pencarian: "' . $q . '"' : 'Katalog Sembako Lengkap');
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 24px; padding-bottom: 40px;">
  
  <!-- Breadcrumb -->
  <div style="font-size: 13.5px; color: #6c757d; margin-bottom: 16px;">
    <a href="index.php">Beranda</a> &rsaquo; 
    <a href="products.php">Produk</a>
    <?php if ($selectedCategory): ?>
      &rsaquo; <span style="color: #212529; font-weight: 600;"><?= htmlspecialchars($selectedCategory['name']) ?></span>
    <?php elseif (!empty($q)): ?>
      &rsaquo; <span style="color: #212529; font-weight: 600;">Cari: "<?= htmlspecialchars($q) ?>"</span>
    <?php endif; ?>
  </div>

  <!-- Header Katalog -->
  <div class="section-header" style="margin-bottom: 18px; flex-wrap: wrap;">
    <div>
      <h1 class="section-title" style="font-size: 24px;">
        <?= $selectedCategory ? htmlspecialchars($selectedCategory['name']) : (!empty($q) ? 'Pencarian: "' . htmlspecialchars($q) . '"' : 'Semua Produk Sembako') ?>
      </h1>
      <p class="section-subtitle">
        Menampilkan <?= count($products) ?> dari total <?= $totalProducts ?> produk berkualitas
      </p>
    </div>

    <!-- Filter & Sort Bar -->
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
      <form method="GET" action="products.php" style="display: flex; gap: 8px; align-items: center;">
        <?php if (!empty($q)): ?>
          <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
        <?php endif; ?>
        <?php if (!empty($catSlug)): ?>
          <input type="hidden" name="category" value="<?= htmlspecialchars($catSlug) ?>">
        <?php endif; ?>
        <?php if ($isFeatured): ?>
          <input type="hidden" name="featured" value="1">
        <?php endif; ?>

        <label for="sortSelect" style="font-size: 13.5px; font-weight: 600; color: #495057;">Urutkan:</label>
        <select id="sortSelect" name="sort" class="form-control" style="width: auto; padding: 7px 12px; font-size: 13.5px;" onchange="this.form.submit()">
          <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Terbaru</option>
          <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Harga: Rendah ke Tinggi</option>
          <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Harga: Tinggi ke Rendah</option>
          <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Nama A - Z</option>
        </select>
      </form>
    </div>
  </div>

  <!-- Main Content Layout (Sidebar Filter + Grid) -->
  <div style="display: grid; grid-template-columns: 240px 1fr; gap: 24px; align-items: start;">
    
    <!-- Sidebar Kategori (Desktop/Tablet) -->
    <aside style="background: #ffffff; border: 1px solid #dee2e6; border-radius: 10px; padding: 18px; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
      <h3 style="font-size: 15px; font-weight: 800; color: #212529; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1.5px solid #e8f5e9;">
        Kategori Sembako
      </h3>
      <ul style="list-style: none; display: flex; flex-direction: column; gap: 6px;">
        <li>
          <a href="products.php<?= !empty($q) ? '?q=' . urlencode($q) : '' ?>" 
             style="display: flex; justify-content: space-between; padding: 6px 8px; border-radius: 6px; font-size: 13.5px; font-weight: <?= empty($catSlug) ? '700' : '500' ?>; color: <?= empty($catSlug) ? '#198754' : '#495057' ?>; background: <?= empty($catSlug) ? '#e8f5e9' : 'transparent' ?>;">
            <span>Semua Kategori</span>
            <span style="font-size: 12px; color: #6c757d;"><?= $totalProducts ?></span>
          </a>
        </li>
        <?php foreach ($allCategories as $c): ?>
          <li>
            <a href="products.php?category=<?= urlencode($c['slug']) ?><?= !empty($q) ? '&q=' . urlencode($q) : '' ?>" 
               style="display: flex; justify-content: space-between; padding: 6px 8px; border-radius: 6px; font-size: 13.5px; font-weight: <?= $catSlug === $c['slug'] ? '700' : '500' ?>; color: <?= $catSlug === $c['slug'] ? '#198754' : '#495057' ?>; background: <?= $catSlug === $c['slug'] ? '#e8f5e9' : 'transparent' ?>;">
              <span><?= htmlspecialchars($c['name']) ?></span>
              <span style="font-size: 12px; color: #6c757d;"><?= $c['total_products'] ?></span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </aside>

    <!-- Products Grid -->
    <div>
      <?php if (!empty($products)): ?>
        <div class="products-grid">
          <?php foreach ($products as $product): ?>
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
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
          <div class="pagination">
            <?php if ($page > 1): ?>
              <div class="page-item">
                <a class="page-link" href="products.php?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&laquo;</a>
              </div>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <div class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="products.php?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
              </div>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
              <div class="page-item">
                <a class="page-link" href="products.php?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">&raquo;</a>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      <?php else: ?>
        <div style="background: #ffffff; border: 1px dashed #dee2e6; border-radius: 12px; padding: 48px; text-align: center;">
          <div style="font-size: 48px; margin-bottom: 12px;">🌾</div>
          <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">Tidak Ada Produk Ditemukan</h3>
          <p style="color: #6c757d; font-size: 14px; max-width: 400px; margin: 0 auto 20px;">
            Maaf, tidak ada produk sembako yang cocok dengan filter atau kata kunci pencarian Anda.
          </p>
          <a href="products.php" class="btn-primary">Lihat Seluruh Produk</a>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<style>
@media (max-width: 768px) {
  div[style*="grid-template-columns: 240px 1fr"] {
    grid-template-columns: 1fr !important;
  }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
