<?php
/**
 * N.A Fresh Fruits & Coconuts - Admin Gallery Management
 */

$adminTitle = 'Hygiene & Shop Gallery';
require_once __DIR__ . '/header.php';

$db = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_photo'])) {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'shop');
    $imageUrl = trim($_POST['image_url'] ?? '');
    $caption = trim($_POST['caption'] ?? '');

    if (!empty($title) && !empty($imageUrl)) {
        $ins = $db->prepare("INSERT INTO gallery (title, category, image_url, caption, created_at) VALUES (?, ?, ?, ?, NOW())");
        $ins->execute([$title, $category, $imageUrl, $caption]);
        $msg = "Photo '{$title}' added to gallery!";
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM gallery WHERE id = ?")->execute([$id]);
    header("Location: /admin/gallery.php");
    exit;
}

$items = $db->query("SELECT * FROM gallery ORDER BY id DESC")->fetchAll();
?>

<div class="space-y-6" x-data="{ addModal: false }">
  
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="font-bold text-2xl text-white">Hygiene & Preparation Gallery</h1>
      <p class="text-xs text-slate-400 mt-1">Manage photos showing fresh coconut stacks, fruit sorting, and juice prep at Station Road, Jaora.</p>
    </div>
    <button @click="addModal = true" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-2">
      <span>+ Add Photo</span>
    </button>
  </div>

  <?php if ($msg): ?>
    <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-xs font-bold">
      <?= e($msg) ?>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <?php foreach ($items as $it): ?>
      <div class="bg-slate-950 rounded-3xl overflow-hidden border border-slate-800 space-y-3 p-4">
        <img src="<?= e($it['image_url']) ?>" alt="<?= e($it['title']) ?>" class="w-full aspect-4/3 object-cover rounded-2xl bg-slate-900">
        <div>
          <h4 class="font-bold text-white text-sm"><?= e($it['title']) ?></h4>
          <p class="text-[11px] text-slate-400"><?= e($it['caption']) ?></p>
        </div>
        <div class="pt-2 flex justify-end">
          <a href="/admin/gallery.php?delete=<?= $it['id'] ?>" onclick="return confirm('Delete photo?')" class="text-xs text-rose-400 hover:underline">
            Delete
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- ADD MODAL -->
  <div x-show="addModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs">
    <div @click.away="addModal = false" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full space-y-4">
      <h2 class="font-bold text-xl text-white">Add Photo to Gallery</h2>
      <form method="POST" class="space-y-4 text-xs">
        <div>
          <label class="block font-bold text-slate-400 uppercase mb-1">Photo Title</label>
          <input type="text" name="title" required placeholder="e.g. Daily Coconut Harvest" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
        </div>
        <div>
          <label class="block font-bold text-slate-400 uppercase mb-1">Image URL</label>
          <input type="url" name="image_url" required placeholder="https://images.unsplash.com/..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
        </div>
        <div>
          <label class="block font-bold text-slate-400 uppercase mb-1">Caption</label>
          <input type="text" name="caption" placeholder="Short description" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
        </div>
        <div class="pt-4 flex justify-end gap-3">
          <button @click="addModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300">Cancel</button>
          <button type="submit" name="add_photo" class="px-6 py-2 rounded-xl bg-emerald-600 text-white font-bold">Save Photo</button>
        </div>
      </form>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
