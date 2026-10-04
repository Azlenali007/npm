<?php
/**
 * N.A Fresh Fruits & Coconuts - SaaS Product & Inventory Manager
 * 100% Responsive Card Grid (No Table) with instant stock toggle, categories & modal management
 */

$adminTitle = 'Menu & Inventory Management';
require_once __DIR__ . '/header.php';

$db = getDB();
$msg = '';
$err = '';

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name'] ?? '');
    $catId = (int)($_POST['category_id'] ?? 1);
    $price = (float)($_POST['price'] ?? 0);
    $origPrice = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
    $unitSize = trim($_POST['unit_size'] ?? '300ml');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? '');
    $inStock = isset($_POST['in_stock']) ? 1 : 0;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isBestseller = isset($_POST['is_bestseller']) ? 1 : 0;

    if (empty($name) || $price <= 0) {
        $err = 'Please provide product name and valid price.';
    } else {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $ins = $db->prepare("
            INSERT INTO products (category_id, name, slug, subtitle, description, price, original_price, unit_size, image_url, in_stock, is_featured, is_bestseller, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $ins->execute([$catId, $name, $slug, $subtitle, $description, $price, $origPrice, $unitSize, $imageUrl, $inStock, $isFeatured, $isBestseller]);
        $msg = "Drink '{$name}' created successfully!";
    }
}

// Handle Edit Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $id = (int)$_POST['product_id'];
    $name = trim($_POST['name'] ?? '');
    $catId = (int)($_POST['category_id'] ?? 1);
    $price = (float)($_POST['price'] ?? 0);
    $origPrice = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
    $unitSize = trim($_POST['unit_size'] ?? '300ml');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? '');
    $inStock = isset($_POST['in_stock']) ? 1 : 0;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isBestseller = isset($_POST['is_bestseller']) ? 1 : 0;

    if (empty($name) || $price <= 0) {
        $err = 'Please provide product name and valid price.';
    } else {
        $upd = $db->prepare("
            UPDATE products 
            SET category_id = ?, name = ?, subtitle = ?, description = ?, price = ?, original_price = ?, unit_size = ?, image_url = ?, in_stock = ?, is_featured = ?, is_bestseller = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $upd->execute([$catId, $name, $subtitle, $description, $price, $origPrice, $unitSize, $imageUrl, $inStock, $isFeatured, $isBestseller, $id]);
        $msg = "Drink '{$name}' updated successfully!";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $db->prepare("DELETE FROM products WHERE id = ?")->execute([$delId]);
    header("Location: /admin/products.php");
    exit;
}

// Handle Stock Toggle
if (isset($_GET['toggle_stock'])) {
    $toggleId = (int)$_GET['toggle_stock'];
    $db->prepare("UPDATE products SET in_stock = 1 - in_stock WHERE id = ?")->execute([$toggleId]);
    header("Location: /admin/products.php");
    exit;
}

// Category filter
$catFilter = (int)($_GET['category'] ?? 0);

// Fetch All Categories
$cats = $db->query("SELECT * FROM categories ORDER BY sort_order ASC")->fetchAll();

// Fetch Products
$query = "
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
";
$params = [];
if ($catFilter > 0) {
    $query .= " WHERE p.category_id = ?";
    $params[] = $catFilter;
}
$query .= " ORDER BY p.sort_order ASC, p.id DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Quick stats
$totalItems = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$inStockItems = (int)$db->query("SELECT COUNT(*) FROM products WHERE in_stock = 1")->fetchColumn();
$outOfStockItems = $totalItems - $inStockItems;
?>

<div class="space-y-6" x-data="{ addModal: false, editModal: false, editItem: {} }">
  
  <!-- Header Bar -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="font-black text-2xl text-white tracking-tight">Menu & Juice Inventory</h1>
      <p class="text-xs text-slate-400 mt-1">Manage tender coconut water items, fresh juices, prices, and daily availability in Jaora.</p>
    </div>
    <button @click="addModal = true" class="px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs transition-colors flex items-center justify-center gap-2 shadow-md shadow-emerald-950/40">
      <span>+ Add New Drink</span>
    </button>
  </div>

  <?php if ($msg): ?>
    <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-xs font-bold flex items-center justify-between shadow-md">
      <span><?= e($msg) ?></span>
      <a href="/admin/products.php" class="text-slate-400 hover:text-white">✕</a>
    </div>
  <?php endif; ?>

  <?php if ($err): ?>
    <div class="p-4 rounded-2xl bg-rose-950/80 border border-rose-800 text-rose-300 text-xs font-bold flex items-center justify-between shadow-md">
      <span><?= e($err) ?></span>
      <a href="/admin/products.php" class="text-slate-400 hover:text-white">✕</a>
    </div>
  <?php endif; ?>

  <!-- SaaS Metric Quick-Bar -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="p-4 rounded-2xl bg-[#09160e] border border-emerald-900/40 flex items-center justify-between shadow-lg">
      <span class="text-xs font-bold text-slate-400">Total Catalog Items</span>
      <span class="text-xl font-black text-white"><?= $totalItems ?></span>
    </div>
    <div class="p-4 rounded-2xl bg-[#09160e] border border-emerald-900/40 flex items-center justify-between shadow-lg">
      <span class="text-xs font-bold text-emerald-400">Currently In Stock</span>
      <span class="text-xl font-black text-emerald-400"><?= $inStockItems ?></span>
    </div>
    <div class="p-4 rounded-2xl bg-[#09160e] border border-emerald-900/40 flex items-center justify-between shadow-lg">
      <span class="text-xs font-bold text-rose-400">Sold Out Today</span>
      <span class="text-xl font-black text-rose-400"><?= $outOfStockItems ?></span>
    </div>
  </div>

  <!-- Category Filter Pills -->
  <div class="p-3 rounded-2xl bg-[#09160e] border border-emerald-900/40 flex items-center gap-2 overflow-x-auto scrollbar-none shadow-md">
    <a href="/admin/products.php" 
       class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap <?= $catFilter === 0 ? 'bg-emerald-600 text-white font-extrabold shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900' ?>">
      All Categories (<?= $totalItems ?>)
    </a>
    <?php foreach ($cats as $c): ?>
      <a href="/admin/products.php?category=<?= $c['id'] ?>" 
         class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap <?= $catFilter === (int)$c['id'] ? 'bg-emerald-600 text-white font-extrabold shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900' ?>">
        <?= e($c['name']) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Products - Responsive Card Grid (No Table) -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
    <?php foreach ($products as $p): ?>
      <div class="p-5 rounded-3xl bg-[#09160e] border border-emerald-900/40 hover:border-emerald-600/60 transition-all duration-300 flex flex-col justify-between space-y-4 group shadow-xl">
        
        <div>
          <!-- Product Image & Overlay Badges -->
          <div class="relative w-full aspect-[4/3] rounded-2xl overflow-hidden bg-slate-900 mb-3.5 border border-emerald-950">
            <img src="<?= e($p['image_url']) ?>" 
                 alt="<?= e($p['name']) ?>" 
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            
            <!-- Category Tag -->
            <div class="absolute top-2.5 left-2.5">
              <span class="px-2.5 py-1 rounded-lg bg-black/75 backdrop-blur-xs text-white text-[10px] font-bold">
                <?= e($p['category_name']) ?>
              </span>
            </div>

            <!-- In-Stock / Sold Out Indicator -->
            <div class="absolute top-2.5 right-2.5">
              <a href="/admin/products.php?toggle_stock=<?= $p['id'] ?>" 
                 class="px-2.5 py-1 rounded-full text-[10px] font-extrabold shadow-sm transition-colors block <?= $p['in_stock'] ? 'bg-emerald-950/90 text-emerald-300 border border-emerald-700' : 'bg-rose-950/90 text-rose-300 border border-rose-700' ?>"
                 title="Click to toggle stock status">
                <?= $p['in_stock'] ? '● In Stock' : '● Sold Out' ?>
              </a>
            </div>

            <!-- Badges (Featured / Bestseller) -->
            <div class="absolute bottom-2.5 left-2.5 flex items-center gap-1.5">
              <?php if ($p['is_featured']): ?>
                <span class="px-2 py-0.5 rounded bg-emerald-900/90 text-emerald-200 text-[10px] font-bold">Featured</span>
              <?php endif; ?>
              <?php if ($p['is_bestseller']): ?>
                <span class="px-2 py-0.5 rounded bg-amber-900/90 text-amber-200 text-[10px] font-bold">Bestseller</span>
              <?php endif; ?>
            </div>
          </div>

          <!-- Product Details -->
          <div class="space-y-1 text-xs">
            <h3 class="font-bold text-base text-white group-hover:text-emerald-400 transition-colors line-clamp-1">
              <?= e($p['name']) ?>
            </h3>
            <span class="text-[11px] text-emerald-400 font-semibold block">
              <?= e($p['unit_size']) ?>
            </span>
            <p class="text-slate-400 text-xs line-clamp-2 leading-relaxed mt-1">
              <?= e($p['subtitle'] ?: $p['description']) ?>
            </p>
          </div>
        </div>

        <!-- Pricing & Action Controls -->
        <div class="pt-3 border-t border-emerald-950/80 space-y-3">
          <!-- Price Display -->
          <div class="flex items-baseline justify-between">
            <div class="flex items-baseline gap-2">
              <span class="font-black text-xl text-emerald-400">
                <?= format_price($p['price']) ?>
              </span>
              <?php if (!empty($p['original_price']) && $p['original_price'] > $p['price']): ?>
                <span class="text-xs text-slate-500 line-through">
                  <?= format_price($p['original_price']) ?>
                </span>
              <?php endif; ?>
            </div>
            
            <a href="/admin/products.php?toggle_stock=<?= $p['id'] ?>" 
               class="text-[11px] font-bold <?= $p['in_stock'] ? 'text-amber-400 hover:underline' : 'text-emerald-400 hover:underline' ?>">
              <?= $p['in_stock'] ? 'Mark Sold Out' : 'Mark In Stock' ?>
            </a>
          </div>

          <!-- Action Buttons -->
          <div class="grid grid-cols-2 gap-2">
            <button @click="editItem = <?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>; editModal = true" 
                    type="button"
                    class="py-2.5 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 border border-emerald-950 text-slate-200 hover:text-white font-bold text-xs flex items-center justify-center gap-1.5 transition-colors">
              <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              <span>Edit</span>
            </button>

            <a href="/admin/products.php?delete=<?= $p['id'] ?>" 
               onclick="return confirm('Delete <?= e(addslashes($p['name'])) ?>?')"
               class="py-2.5 px-3 rounded-xl bg-rose-950/60 hover:bg-rose-900/80 border border-rose-900/70 text-rose-300 hover:text-white font-bold text-xs flex items-center justify-center gap-1.5 transition-colors">
              <svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              <span>Delete</span>
            </a>
          </div>
        </div>

      </div>
    <?php endforeach; ?>
  </div>

  <!-- ADD PRODUCT MODAL -->
  <div x-show="addModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-sm">
    <div @click.away="addModal = false" class="bg-[#09160e] border border-emerald-900/60 rounded-[2.5rem] p-6 sm:p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto space-y-5 shadow-2xl">
      <div class="flex items-center justify-between pb-3 border-b border-emerald-950">
        <h2 class="font-black text-xl text-white">Add New Drink / Coconut Item</h2>
        <button @click="addModal = false" class="text-slate-400 hover:text-white text-lg font-bold">✕</button>
      </div>
      <form method="POST" class="space-y-4 text-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Product Name *</label>
            <input type="text" name="name" required placeholder="e.g. Pure Mosambi Juice" class="w-full px-3.5 py-2.5 rounded-xl bg-[#061009] border border-emerald-950 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
          </div>
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Category *</label>
            <select name="category_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-[#061009] border border-emerald-950 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
              <?php foreach ($cats as $c): ?>
                <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div>
          <label class="block font-bold text-slate-400 uppercase mb-1">Subtitle / Quick Note</label>
          <input type="text" name="subtitle" placeholder="e.g. 100% freshly pressed with black salt" class="w-full px-3.5 py-2.5 rounded-xl bg-[#061009] border border-emerald-950 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <div>
          <label class="block font-bold text-slate-400 uppercase mb-1">Full Description</label>
          <textarea name="description" rows="3" placeholder="Describe the taste, health benefits and cold-press process..." class="w-full px-3.5 py-2.5 rounded-xl bg-[#061009] border border-emerald-950 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Price (₹) *</label>
            <input type="number" step="0.5" name="price" required placeholder="60" class="w-full px-3.5 py-2.5 rounded-xl bg-[#061009] border border-emerald-950 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
          </div>
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Original Price (Strike)</label>
            <input type="number" step="0.5" name="original_price" placeholder="70" class="w-full px-3.5 py-2.5 rounded-xl bg-[#061009] border border-emerald-950 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
          </div>
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Unit / Size</label>
            <input type="text" name="unit_size" value="300ml Chilled Bottle" class="w-full px-3.5 py-2.5 rounded-xl bg-[#061009] border border-emerald-950 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
          </div>
        </div>

        <div>
          <label class="block font-bold text-slate-400 uppercase mb-1">Image URL</label>
          <input type="url" name="image_url" value="https://images.unsplash.com/photo-1613478223719-2ab802602423?auto=format&fit=crop&w=800&q=80" class="w-full px-3.5 py-2.5 rounded-xl bg-[#061009] border border-emerald-950 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <div class="flex items-center gap-6 pt-2">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="in_stock" checked class="text-emerald-500 rounded">
            <span class="text-slate-200">In Stock</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_featured" class="text-emerald-500 rounded">
            <span class="text-slate-200">Featured on Home</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_bestseller" class="text-emerald-500 rounded">
            <span class="text-slate-200">Bestseller</span>
          </label>
        </div>

        <div class="pt-4 flex justify-end gap-3 border-t border-emerald-950">
          <button @click="addModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300">Cancel</button>
          <button type="submit" name="add_product" class="px-6 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black shadow-md">Save Drink</button>
        </div>
      </form>
    </div>
  </div>

  <!-- EDIT PRODUCT MODAL -->
  <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-sm">
    <div @click.away="editModal = false" class="bg-[#09160e] border border-emerald-900/60 rounded-[2.5rem] p-6 sm:p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto space-y-5 shadow-2xl">
      <div class="flex items-center justify-between pb-3 border-b border-emerald-950">
        <h2 class="font-black text-xl text-white">Edit Drink Details</h2>
        <button @click="editModal = false" class="text-slate-400 hover:text-white text-lg font-bold">✕</button>
      </div>
      <form method="POST" class="space-y-4 text-xs">
        <input type="hidden" name="product_id" :value="editItem.id">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Product Name *</label>
            <input type="text" name="name" required x-model="editItem.name" class="w-full px-3.5 py-2.5 rounded-xl bg-[#061009] border border-emerald-950 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
          </div>
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Category *</label>
            <select name="category_id" x-model="editItem.category_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-[#061009] border border-emerald-950 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
              <?php foreach ($cats as $c): ?>
                <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div>
          <label class="block font-bold text-slate-400 uppercase mb-1">Subtitle</label>
          <input type="text" name="subtitle" x-model="editItem.subtitle" class="w-full px-3.5 py-2.5 rounded-xl bg-[#061009] border border-emerald-950 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <div>
          <label class="block font-bold text-slate-400 uppercase mb-1">Description</label>
          <textarea name="description" rows="3" x-model="editItem.description" class="w-full px-3.5 py-2.5 rounded-xl bg-[#061009] border border-emerald-950 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Price (₹)</label>
            <input type="number" step="0.5" name="price" required x-model="editItem.price" class="w-full px-3.5 py-2.5 rounded-xl bg-[#061009] border border-emerald-950 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
          </div>
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Original Price</label>
            <input type="number" step="0.5" name="original_price" x-model="editItem.original_price" class="w-full px-3.5 py-2.5 rounded-xl bg-[#061009] border border-emerald-950 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
          </div>
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Unit / Size</label>
            <input type="text" name="unit_size" x-model="editItem.unit_size" class="w-full px-3.5 py-2.5 rounded-xl bg-[#061009] border border-emerald-950 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
          </div>
        </div>

        <div>
          <label class="block font-bold text-slate-400 uppercase mb-1">Image URL</label>
          <input type="url" name="image_url" x-model="editItem.image_url" class="w-full px-3.5 py-2.5 rounded-xl bg-[#061009] border border-emerald-950 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <div class="flex items-center gap-6 pt-2">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="in_stock" :checked="editItem.in_stock == 1" class="text-emerald-500 rounded">
            <span class="text-slate-200">In Stock</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_featured" :checked="editItem.is_featured == 1" class="text-emerald-500 rounded">
            <span class="text-slate-200">Featured</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_bestseller" :checked="editItem.is_bestseller == 1" class="text-emerald-500 rounded">
            <span class="text-slate-200">Bestseller</span>
          </label>
        </div>

        <div class="pt-4 flex justify-end gap-3 border-t border-emerald-950">
          <button @click="editModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300">Cancel</button>
          <button type="submit" name="edit_product" class="px-6 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black shadow-md">Update Drink</button>
        </div>
      </form>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
