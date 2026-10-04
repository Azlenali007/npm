<?php
/**
 * N.A Fresh Fruits & Coconuts - Full Products Menu
 */

require_once __DIR__ . '/config/database.php';

$db = getDB();

$selectedCategory = trim($_GET['category'] ?? '');
$search = trim($_GET['q'] ?? '');
$sort = trim($_GET['sort'] ?? 'sort_order');

// Fetch Categories
$catStmt = $db->query("SELECT * FROM categories WHERE status = 1 ORDER BY sort_order ASC");
$categories = $catStmt->fetchAll();

// Build Query
$sql = "
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.status = 1
";
$params = [];

if (!empty($selectedCategory)) {
    $sql .= " AND c.slug = ?";
    $params[] = $selectedCategory;
}

if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($sort === 'price_asc') {
    $sql .= " ORDER BY p.price ASC";
} elseif ($sort === 'price_desc') {
    $sql .= " ORDER BY p.price DESC";
} else {
    $sql .= " ORDER BY p.sort_order ASC, p.id ASC";
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$pageTitle = 'Full Drinks Menu & Tender Coconuts – N.A Fresh Fruits Jaora';
$pageDesc = 'Explore our full menu of raw tender coconuts, freshly squeezed orange, mosambi, mango and pomegranate juices in Jaora, MP.';

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-coconut-cream min-h-screen py-10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Page Header -->
    <div class="text-center max-w-2xl mx-auto mb-10">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-100 text-brand-800 text-xs font-bold mb-3">
        <span>Prepared Fresh Daily</span>
        <span aria-hidden="true">•</span>
        <span>Local Jaora Doorstep Delivery</span>
      </div>
      <h1 class="font-serif font-bold text-3xl sm:text-5xl text-brand-950 tracking-tight">
        Our Fresh Drinks Menu
      </h1>
      <p class="text-sm text-slate-600 mt-2">
        Cold-cut tender coconuts, raw extracted juices, and fresh tropical fruit platters.
      </p>
    </div>

    <!-- Filter & Search Controls -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs mb-8 space-y-4">
      
      <!-- Category Tabs (Functional buttons/links) -->
      <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
        <a href="/products.php" 
           class="px-4 py-2 rounded-xl text-xs font-bold shrink-0 transition-colors <?= empty($selectedCategory) ? 'bg-brand-800 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
          All Drinks (<?= count($products) ?>)
        </a>
        <?php foreach ($categories as $cat): ?>
          <a href="/products.php?category=<?= urlencode($cat['slug']) ?>" 
             class="px-4 py-2 rounded-xl text-xs font-bold shrink-0 transition-colors <?= $selectedCategory === $cat['slug'] ? 'bg-brand-800 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
            <?= e($cat['name']) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Search & Sort Row -->
      <form method="GET" class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-100">
        <?php if (!empty($selectedCategory)): ?>
          <input type="hidden" name="category" value="<?= e($selectedCategory) ?>">
        <?php endif; ?>

        <div class="relative w-full sm:max-w-sm">
          <input type="text" 
                 name="q" 
                 value="<?= e($search) ?>" 
                 placeholder="Search coconuts, orange, mosambi..." 
                 class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
          <label class="text-xs text-slate-500 font-medium">Sort:</label>
          <select name="sort" 
                  onchange="this.form.submit()" 
                  class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
            <option value="sort_order" <?= $sort === 'sort_order' ? 'selected' : '' ?>>Featured First</option>
            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
          </select>
          <?php if (!empty($search) || !empty($selectedCategory)): ?>
            <a href="/products.php" class="text-xs text-brand-700 font-bold hover:underline ml-2">Clear</a>
          <?php endif; ?>
        </div>
      </form>

    </div>

    <!-- Products Grid -->
    <?php if (empty($products)): ?>
      <div class="bg-white rounded-3xl p-12 text-center border border-slate-200">
        <p class="font-serif font-bold text-lg text-slate-800">No drinks found</p>
        <p class="text-xs text-slate-500 mt-1">Try adjusting your category filter or search keywords.</p>
        <a href="/products.php" class="inline-block mt-4 px-5 py-2.5 bg-brand-700 text-white rounded-xl text-xs font-bold">
          View All Drinks
        </a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($products as $product): ?>
          <?php include __DIR__ . '/includes/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
