<?php
/**
 * JAWA MART - Halaman Kategori Sembako
 * File: categories.php
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Kategori Sembako Lengkap';
$metaDesc  = 'Jelajahi seluruh kategori sembako dan kebutuhan rumah tangga di JAWA MART. Mulai dari beras, minyak goreng, gula, tepung, mie, telur, hingga bumbu dapur.';

// Ambil seluruh kategori dengan jumlah produk aktif
$categories = [];
try {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(p.id) AS total_products 
        FROM categories c 
        LEFT JOIN products p ON p.category_id = c.id AND p.status = 1 AND p.deleted_at IS NULL 
        WHERE c.status = 1 
        GROUP BY c.id 
        ORDER BY c.name ASC
    ");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error fetching categories: " . $e->getMessage());
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 24px; padding-bottom: 50px;">
  
  <!-- Breadcrumb -->
  <div style="font-size: 13.5px; color: #6c757d; margin-bottom: 16px;">
    <a href="index.php">Beranda</a> &rsaquo; 
    <span style="color: #212529; font-weight: 600;">Kategori Sembako</span>
  </div>

  <div class="section-header">
    <div>
      <h1 class="section-title">Kategori Kebutuhan Sembako</h1>
      <p class="section-subtitle">Temukan bahan makanan pokok segar dan perlengkapan rumah tangga berkualitas</p>
    </div>
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px;">
    <?php foreach ($categories as $cat): ?>
      <a href="products.php?category=<?= urlencode($cat['slug']) ?>" 
         style="background: #ffffff; border: 1px solid #dee2e6; border-radius: 12px; padding: 24px; display: flex; flex-direction: column; align-items: center; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.04); transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;"
         onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.08)'; this.style.borderColor='#198754';"
         onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.04)'; this.style.borderColor='#dee2e6';">
        
        <div style="width: 88px; height: 88px; border-radius: 50%; background: #e8f5e9; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; overflow: hidden;">
          <img src="<?= htmlspecialchars(!empty($cat['image']) ? $cat['image'] : 'assets/images/placeholder.svg') ?>" alt="<?= htmlspecialchars($cat['name']) ?>" style="width: 70px; height: 70px; object-fit: contain;">
        </div>

        <h3 style="font-size: 16px; font-weight: 800; color: #212529; margin-bottom: 6px;">
          <?= htmlspecialchars($cat['name']) ?>
        </h3>

        <p style="font-size: 13px; color: #6c757d; line-height: 1.4; margin-bottom: 14px; flex: 1;">
          <?= htmlspecialchars($cat['description'] ?? 'Pilihan produk segar dan berkualitas terbaik.') ?>
        </p>

        <span style="font-size: 12.5px; font-weight: 700; color: #198754; background: #e8f5e9; padding: 4px 12px; border-radius: 9999px;">
          <?= (int)$cat['total_products'] ?> Produk Tersedia &rarr;
        </span>
      </a>
    <?php endforeach; ?>
  </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
