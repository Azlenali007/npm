<?php
/**
 * N.A Fresh Fruits & Coconuts - Admin Products & Juices Management
 */

$adminTitle = 'Product & Juice Inventory';
require_once __DIR__ . '/header.php';

$db = getDB();
$msg = '';
$err = '';

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $catId = (int)$_POST['category_id'];
    $name = trim($_POST['name'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $price = (float)$_POST['price'];
    $origPrice = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
    $unitSize = trim($_POST['unit_size'] ?? '1 Pc');
    $imageUrl = trim($_POST['image_url'] ?? 'https://images.unsplash.com/photo-1544378730-8b5104b18790?auto=format&fit=crop&w=800&q=80');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isBestseller = isset($_POST['is_bestseller']) ? 1 : 0;
    $inStock = isset($_POST['in_stock']) ? 1 : 0;
    $sortOrder = (int)($_POST['sort_order'] ?? 0);

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    // Ensure unique slug
    $check = $db->prepare("SELECT COUNT(*) FROM products WHERE slug = ?");
    $check->execute([$slug]);
    if ($check->fetchColumn() > 0) {
        $slug .= '-' . time();
    }

    if (!empty($name) && $price > 0) {
        $ins = $db->prepare("
            INSERT INTO products (category_id, name, slug, subtitle, description, price, original_price, unit_size, image_url, is_featured, is_bestseller, in_stock, sort_order, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ");
        $ins->execute([$catId, $name, $slug, $subtitle, $desc, $price, $origPrice, $unitSize, $imageUrl, $isFeatured, $isBestseller, $inStock, $sortOrder]);
        $msg = "Product '{$name}' added successfully!";
    } else {
        $err = "Product name and positive price are required.";
    }
}

// Handle Edit Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $prodId = (int)$_POST['product_id'];
    $catId = (int)$_POST['category_id'];
    $name = trim($_POST['name'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $price = (float)$_POST['price'];
    $origPrice = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
    $unitSize = trim($_POST['unit_size'] ?? '1 Pc');
    $imageUrl = trim($_POST['image_url'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isBestseller = isset($_POST['is_bestseller']) ? 1 : 0;
    $inStock = isset($_POST['in_stock']) ? 1 : 0;
    $sortOrder = (int)($_POST['sort_order'] ?? 0);

    if (!empty($name) && $price > 0) {
        $upd = $db->prepare("
            UPDATE products 
            SET category_id = ?, name = ?, subtitle = ?, description = ?, price = ?, 
                original_price = ?, unit_size = ?, image_url = ?, is_featured = ?, 
                is_bestseller = ?, in_stock = ?, sort_order = ?
            WHERE id = ?
        ");
        $upd->execute([$catId, $name, $subtitle, $desc, $price, $origPrice, $unitSize, $imageUrl, $isFeatured, $isBestseller, $inStock, $sortOrder, $prodId]);
        $msg = "Product updated successfully!";
    }
}

// Handle Delete Product
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

// Fetch All Categories
$cats = $db->query("SELECT * FROM categories ORDER BY sort_order ASC")->fetchAll();

// Fetch All Products
$stmt = $db->query("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.sort_order ASC, p.id DESC
");
$products = $stmt->fetchAll();
?>

<div class="space-y-6" x-data="{ addModal: false, editModal: false, editItem: {} }">
  
  <!-- Header Bar -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="font-bold text-2xl text-white">Menu & Juice Inventory</h1>
      <p class="text-xs text-slate-400 mt-1">Manage tender coconut water items, fresh juices, prices, and daily availability in Jaora.</p>
    </div>
    <button @click="addModal = true" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-colors flex items-center gap-2 shadow-xs">
      <span>+ Add New Drink</span>
    </button>
  </div>

  <?php if ($msg): ?>
    <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-xs font-bold">
      <?= e($msg) ?>
    </div>
  <?php endif; ?>

  <?php if ($err): ?>
    <div class="p-4 rounded-2xl bg-rose-950/80 border border-rose-800 text-rose-300 text-xs font-bold">
      <?= e($err) ?>
    </div>
  <?php endif; ?>

  <!-- Products Table -->
  <div class="rounded-3xl bg-slate-950 border border-slate-800 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead>
          <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase tracking-wider bg-slate-900/40">
            <th class="py-3.5 px-4">Item</th>
            <th class="py-3.5 px-3">Category</th>
            <th class="py-3.5 px-3">Price</th>
            <th class="py-3.5 px-3">Unit / Volume</th>
            <th class="py-3.5 px-3">Stock Status</th>
            <th class="py-3.5 px-3">Badges</th>
            <th class="py-3.5 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800">
          <?php foreach ($products as $p): ?>
            <tr class="hover:bg-slate-900/60 transition-colors">
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>" class="w-12 h-12 rounded-xl object-cover bg-slate-800 shrink-0">
                  <div>
                    <h3 class="font-bold text-white text-sm"><?= e($p['name']) ?></h3>
                    <p class="text-[11px] text-slate-400 line-clamp-1 max-w-xs"><?= e($p['subtitle'] ?: $p['description']) ?></p>
                  </div>
                </div>
              </td>
              <td class="py-3 px-3 text-slate-300">
                <?= e($p['category_name']) ?>
              </td>
              <td class="py-3 px-3 font-serif font-bold text-emerald-400 text-sm">
                <?= format_price($p['price']) ?>
                <?php if (!empty($p['original_price'])): ?>
                  <span class="text-[11px] text-slate-500 line-through block font-sans"><?= format_price($p['original_price']) ?></span>
                <?php endif; ?>
              </td>
              <td class="py-3 px-3 text-slate-400">
                <?= e($p['unit_size']) ?>
              </td>
              <td class="py-3 px-3">
                <a href="/admin/products.php?toggle_stock=<?= $p['id'] ?>" 
                   class="px-2.5 py-1 rounded-full text-[10px] font-bold inline-block transition-colors <?= $p['in_stock'] ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-rose-950 text-rose-300 border border-rose-800' ?>">
                  <?= $p['in_stock'] ? 'In Stock (Click to Disable)' : 'Sold Out' ?>
                </a>
              </td>
              <td class="py-3 px-3 space-x-1">
                <?php if ($p['is_featured']): ?>
                  <span class="px-2 py-0.5 rounded bg-brand-900 text-brand-300 text-[10px] font-bold">Featured</span>
                <?php endif; ?>
                <?php if ($p['is_bestseller']): ?>
                  <span class="px-2 py-0.5 rounded bg-amber-950 text-amber-300 text-[10px] font-bold">Bestseller</span>
                <?php endif; ?>
              </td>
              <td class="py-3 px-4 text-right space-x-2">
                <button @click="editItem = <?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>; editModal = true" 
                        class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold">
                  Edit
                </button>
                <a href="/admin/products.php?delete=<?= $p['id'] ?>" 
                   onclick="return confirm('Delete <?= e(addslashes($p['name'])) ?>?')"
                   class="px-3 py-1.5 rounded-lg bg-rose-950 text-rose-300 hover:bg-rose-900 text-xs font-semibold">
                  Delete
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ADD PRODUCT MODAL -->
  <div x-show="addModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs">
    <div @click.away="addModal = false" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto space-y-4">
      <h2 class="font-bold text-xl text-white">Add New Drink / Coconut Item</h2>
      <form method="POST" class="space-y-4 text-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Product Name *</label>
            <input type="text" name="name" required placeholder="e.g. Pure Mosambi Juice" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
          </div>
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Category *</label>
            <select name="category_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
              <?php foreach ($cats as $c): ?>
                <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div>
          <label class="block font-bold text-slate-400 uppercase mb-1">Subtitle / Quick Note</label>
          <input type="text" name="subtitle" placeholder="e.g. 100% freshly pressed with black salt" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
        </div>

        <div>
          <label class="block font-bold text-slate-400 uppercase mb-1">Full Description</label>
          <textarea name="description" rows="3" placeholder="Describe the taste, health benefits and cold-press process..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Price (₹) *</label>
            <input type="number" step="0.5" name="price" required placeholder="60" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
          </div>
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Original Price (Strike)</label>
            <input type="number" step="0.5" name="original_price" placeholder="70" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
          </div>
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Unit / Size</label>
            <input type="text" name="unit_size" value="300ml Chilled Bottle" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
          </div>
        </div>

        <div>
          <label class="block font-bold text-slate-400 uppercase mb-1">Image URL</label>
          <input type="url" name="image_url" value="https://images.unsplash.com/photo-1613478223719-2ab802602423?auto=format&fit=crop&w=800&q=80" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
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

        <div class="pt-4 flex justify-end gap-3">
          <button @click="addModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300">Cancel</button>
          <button type="submit" name="add_product" class="px-6 py-2 rounded-xl bg-emerald-600 text-white font-bold">Save Drink</button>
        </div>
      </form>
    </div>
  </div>

  <!-- EDIT PRODUCT MODAL -->
  <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs">
    <div @click.away="editModal = false" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto space-y-4">
      <h2 class="font-bold text-xl text-white">Edit Drink Details</h2>
      <form method="POST" class="space-y-4 text-xs">
        <input type="hidden" name="product_id" :value="editItem.id">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Product Name *</label>
            <input type="text" name="name" required x-model="editItem.name" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
          </div>
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Category *</label>
            <select name="category_id" x-model="editItem.category_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
              <?php foreach ($cats as $c): ?>
                <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div>
          <label class="block font-bold text-slate-400 uppercase mb-1">Subtitle</label>
          <input type="text" name="subtitle" x-model="editItem.subtitle" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
        </div>

        <div>
          <label class="block font-bold text-slate-400 uppercase mb-1">Description</label>
          <textarea name="description" rows="3" x-model="editItem.description" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Price (₹)</label>
            <input type="number" step="0.5" name="price" required x-model="editItem.price" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
          </div>
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Original Price</label>
            <input type="number" step="0.5" name="original_price" x-model="editItem.original_price" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
          </div>
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Unit / Size</label>
            <input type="text" name="unit_size" x-model="editItem.unit_size" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
          </div>
        </div>

        <div>
          <label class="block font-bold text-slate-400 uppercase mb-1">Image URL</label>
          <input type="url" name="image_url" x-model="editItem.image_url" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
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

        <div class="pt-4 flex justify-end gap-3">
          <button @click="editModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300">Cancel</button>
          <button type="submit" name="edit_product" class="px-6 py-2 rounded-xl bg-emerald-600 text-white font-bold">Update Drink</button>
        </div>
      </form>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
