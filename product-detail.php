<?php
/**
 * N.A Fresh Fruits & Coconuts - Product Detail Page
 */

require_once __DIR__ . '/config/database.php';

$productId = (int)($_GET['id'] ?? 0);
$db = getDB();

$stmt = $db->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.id = ? AND p.status = 1
");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: /products.php");
    exit;
}

// Fetch Gallery Images
$gallery = [];
if (!empty($product['gallery_json'])) {
    $gallery = json_decode($product['gallery_json'], true) ?: [];
}
if (empty($gallery)) {
    $gallery = [$product['image_url']];
}

// Fetch Reviews
$stmt = $db->prepare("SELECT * FROM reviews WHERE (product_id = ? OR product_id IS NULL) AND status = 1 ORDER BY id DESC LIMIT 5");
$stmt->execute([$productId]);
$reviews = $stmt->fetchAll();

// Handle Review Submission
$reviewSuccess = false;
$reviewError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $name = trim($_POST['customer_name'] ?? '');
    $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $comment = trim($_POST['comment'] ?? '');
    $location = trim($_POST['location'] ?? 'Jaora');

    if (empty($name) || empty($comment)) {
        $reviewError = 'Please provide both your name and review comments.';
    } else {
        $ins = $db->prepare("INSERT INTO reviews (product_id, customer_name, location, rating, comment, is_verified_purchase, status) VALUES (?, ?, ?, ?, ?, 1, 1)");
        $ins->execute([$productId, $name, $location, $rating, $comment]);
        $reviewSuccess = true;
        // Refresh reviews
        $stmt->execute([$productId]);
        $reviews = $stmt->fetchAll();
    }
}

// Related Products
$stmt = $db->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.category_id = ? AND p.id != ? AND p.status = 1
    ORDER BY p.sort_order ASC LIMIT 3
");
$stmt->execute([$product['category_id'], $productId]);
$related = $stmt->fetchAll();

$pageTitle = e($product['name']) . ' – N.A Fresh Fruits & Coconuts, Jaora';
$pageDesc = e(substr(strip_tags($product['description']), 0, 150));

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-coconut-cream min-h-screen py-10" x-data="{ currentImg: '<?= e($gallery[0]) ?>', detailQty: 1 }">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 mb-8">
      <a href="/" class="hover:text-brand-800">Home</a>
      <span aria-hidden="true">/</span>
      <a href="/products.php?category=<?= e($product['category_slug']) ?>" class="hover:text-brand-800"><?= e($product['category_name']) ?></a>
      <span aria-hidden="true">/</span>
      <span class="text-slate-800 font-semibold truncate"><?= e($product['name']) ?></span>
    </nav>

    <!-- Product Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-xs">
      
      <!-- Gallery Column (Left) -->
      <div class="lg:col-span-6 space-y-4">
        <!-- Main Large Image -->
        <div class="aspect-square rounded-3xl overflow-hidden bg-slate-100 border border-slate-200 shadow-xs relative">
          <img :src="currentImg" 
               alt="<?= e($product['name']) ?>" 
               class="w-full h-full object-cover transition-all duration-300">
          
          <div class="absolute top-4 left-4 bg-brand-950/80 backdrop-blur-xs text-white text-xs font-bold px-3 py-1.5 rounded-xl">
            <?= e($product['unit_size']) ?>
          </div>

          <?php if (!empty($product['is_fresh_cut'])): ?>
            <div class="absolute bottom-4 left-4 bg-emerald-800/90 backdrop-blur-xs text-emerald-100 text-xs font-bold px-3 py-1.5 rounded-xl flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
              <span>Prepared Fresh on Order</span>
            </div>
          <?php endif; ?>
        </div>

        <!-- Thumbnails -->
        <?php if (count($gallery) > 1): ?>
          <div class="flex items-center gap-3">
            <?php foreach ($gallery as $imgUrl): ?>
              <button @click="currentImg = '<?= e($imgUrl) ?>'" 
                      type="button" 
                      class="w-20 h-20 rounded-2xl overflow-hidden border-2 transition-all focus:outline-none"
                      :class="currentImg === '<?= e($imgUrl) ?>' ? 'border-brand-600 scale-105 shadow-sm' : 'border-slate-200 hover:border-slate-300'">
                <img src="<?= e($imgUrl) ?>" alt="Thumbnail" class="w-full h-full object-cover">
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Product Details Column (Right) -->
      <div class="lg:col-span-6 flex flex-col justify-between space-y-6">
        <div class="space-y-4">
          
          <!-- Category & Status -->
          <div class="flex items-center gap-3 text-xs">
            <span class="font-bold text-brand-700 uppercase tracking-wider"><?= e($product['category_name']) ?></span>
            <span class="text-slate-300">|</span>
            <span class="<?= $product['in_stock'] ? 'text-emerald-700 font-bold' : 'text-rose-600 font-bold' ?>">
              <?= $product['in_stock'] ? 'In Stock in Jaora' : 'Currently Unavailable' ?>
            </span>
          </div>

          <!-- Product Title -->
          <h1 class="font-serif font-bold text-3xl sm:text-4xl text-slate-900 tracking-tight leading-tight">
            <?= e($product['name']) ?>
          </h1>

          <!-- Subtitle -->
          <?php if (!empty($product['subtitle'])): ?>
            <p class="text-base text-slate-600 font-medium leading-relaxed">
              <?= e($product['subtitle']) ?>
            </p>
          <?php endif; ?>

          <!-- Price & Discount Tag -->
          <div class="flex items-baseline gap-4 pt-2">
            <span class="font-serif font-bold text-4xl text-brand-900">
              <?= format_price($product['price']) ?>
            </span>
            <?php if (!empty($product['original_price']) && $product['original_price'] > $product['price']): ?>
              <span class="text-lg text-slate-400 line-through">
                <?= format_price($product['original_price']) ?>
              </span>
              <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold">
                Save <?= format_price($product['original_price'] - $product['price']) ?>
              </span>
            <?php endif; ?>
          </div>

          <!-- Description -->
          <div class="pt-4 border-t border-slate-100 text-sm text-slate-700 leading-relaxed space-y-3">
            <p><?= nl2br(e($product['description'])) ?></p>
          </div>

          <!-- Key Highlights -->
          <div class="grid grid-cols-2 gap-3 pt-2">
            <div class="p-3 rounded-2xl bg-brand-50/70 border border-brand-100/80">
              <span class="block text-xs font-bold text-brand-900">100% Raw Ingredients</span>
              <span class="text-[11px] text-brand-700">No water or sugar added</span>
            </div>
            <div class="p-3 rounded-2xl bg-brand-50/70 border border-brand-100/80">
              <span class="block text-xs font-bold text-brand-900">Food-Grade Sealed</span>
              <span class="text-[11px] text-brand-700">Includes bio-friendly straw</span>
            </div>
          </div>

        </div>

        <!-- Quantity & Purchase Actions -->
        <div class="pt-6 border-t border-slate-100 space-y-4">
          
          <div class="flex items-center gap-4">
            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Quantity</label>
            <div class="flex items-center border border-slate-300 rounded-2xl bg-slate-50 overflow-hidden shadow-xs">
              <button @click="if (detailQty > 1) detailQty--" type="button" class="w-10 h-10 flex items-center justify-center text-slate-700 hover:bg-slate-200 font-bold transition-colors">
                -
              </button>
              <span class="w-12 text-center font-bold text-sm text-slate-900" x-text="detailQty">1</span>
              <button @click="if (detailQty < 25) detailQty++" type="button" class="w-10 h-10 flex items-center justify-center text-slate-700 hover:bg-slate-200 font-bold transition-colors">
                +
              </button>
            </div>
          </div>

          <?php if ($product['in_stock']): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
              <button @click="addToCart(<?= $product['id'] ?>, detailQty, true)" 
                      type="button" 
                      class="py-4 px-6 rounded-2xl border-2 border-brand-700 text-brand-800 hover:bg-brand-50 font-bold text-sm transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span>Add to Fresh Cart</span>
              </button>
              <button @click="buyNow(<?= $product['id'] ?>, detailQty)" 
                      type="button" 
                      class="py-4 px-6 rounded-2xl bg-brand-700 hover:bg-brand-800 text-white font-bold text-sm shadow-md transition-colors flex items-center justify-center gap-2">
                <span>Buy Now (Instant Checkout)</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
              </button>
            </div>
          <?php else: ?>
            <div class="p-4 rounded-2xl bg-rose-50 text-rose-700 font-bold text-sm text-center">
              This item is currently sold out for today. Please check back tomorrow!
            </div>
          <?php endif; ?>

          <!-- Local Jaora Delivery Note -->
          <div class="flex items-center gap-2 text-xs text-slate-500 pt-2">
            <svg class="w-4 h-4 text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <span>Express doorstep delivery available across Jaora within 25–40 minutes.</span>
          </div>

        </div>

      </div>

    </div>

    <!-- Customer Reviews Section -->
    <div class="mt-16 bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-xs">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
        <div>
          <h2 class="font-serif font-bold text-2xl text-slate-900">Customer Ratings & Reviews</h2>
          <p class="text-xs text-slate-500 mt-1">Verified feedback from customers in Jaora</p>
        </div>
      </div>

      <?php if ($reviewSuccess): ?>
        <div class="my-4 p-4 rounded-2xl bg-emerald-50 text-emerald-800 text-xs font-bold">
          Thank you! Your review has been submitted and published.
        </div>
      <?php endif; ?>

      <?php if (!empty($reviewError)): ?>
        <div class="my-4 p-4 rounded-2xl bg-rose-50 text-rose-800 text-xs font-bold">
          <?= e($reviewError) ?>
        </div>
      <?php endif; ?>

      <!-- Reviews List -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6">
        <?php foreach ($reviews as $rev): ?>
          <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
            <div class="flex items-center justify-between">
              <span class="font-bold text-sm text-slate-900"><?= e($rev['customer_name']) ?></span>
              <div class="text-amber-400 text-xs">
                <?php for ($i = 0; $i < $rev['rating']; $i++): ?>★<?php endfor; ?>
              </div>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed italic">
              "<?= e($rev['comment']) ?>"
            </p>
            <div class="text-[11px] text-slate-400 pt-1">
              <?= e($rev['location'] ?: 'Jaora') ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Add Review Form -->
      <div class="mt-10 pt-8 border-t border-slate-100">
        <h3 class="font-serif font-bold text-lg text-slate-900 mb-4">Leave a Review</h3>
        <form method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Your Name *</label>
            <input type="text" name="customer_name" required placeholder="e.g. Imran Khan" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Location in Jaora</label>
            <input type="text" name="location" placeholder="e.g. Station Road" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Rating</label>
            <select name="rating" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
              <option value="5">★★★★★ (5 - Excellent)</option>
              <option value="4">★★★★☆ (4 - Very Good)</option>
              <option value="3">★★★☆☆ (3 - Good)</option>
            </select>
          </div>
          <div class="sm:col-span-2">
            <label class="block text-xs font-bold text-slate-700 mb-1">Review Comments *</label>
            <textarea name="comment" required rows="3" placeholder="Tell us how fresh and tasty your drink was..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
          </div>
          <div class="sm:col-span-2">
            <button type="submit" name="submit_review" class="px-6 py-2.5 bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs rounded-xl shadow-xs transition-colors">
              Submit Review
            </button>
          </div>
        </form>
      </div>

    </div>

    <!-- Related Products -->
    <?php if (!empty($related)): ?>
      <div class="mt-16">
        <h2 class="font-serif font-bold text-2xl text-slate-900 mb-6">More Fresh Drinks You'll Love</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach ($related as $product): ?>
            <?php include __DIR__ . '/includes/product-card.php'; ?>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
