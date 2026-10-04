<?php
/**
 * N.A Fresh Fruits & Coconuts - Admin Customer Reviews
 */

$adminTitle = 'Customer Ratings & Reviews';
require_once __DIR__ . '/header.php';

$db = getDB();

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $db->prepare("UPDATE reviews SET status = 1 - status WHERE id = ?")->execute([$id]);
    header("Location: /admin/reviews.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM reviews WHERE id = ?")->execute([$id]);
    header("Location: /admin/reviews.php");
    exit;
}

$reviews = $db->query("SELECT * FROM reviews ORDER BY id DESC")->fetchAll();
?>

<div class="space-y-6">
  
  <div>
    <h1 class="font-bold text-2xl text-white">Customer Feedback & Ratings</h1>
    <p class="text-xs text-slate-400 mt-1">Approve or moderate reviews posted by customers across Jaora.</p>
  </div>

  <!-- Reviews - Responsive Card Grid (No Table) -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php foreach ($reviews as $r): ?>
      <div class="p-6 rounded-3xl bg-[#09160e] border border-emerald-900/40 hover:border-emerald-600/60 transition-all flex flex-col justify-between space-y-4 group shadow-xl">
        
        <div class="space-y-3">
          <!-- Top Row: Customer & Status -->
          <div class="flex items-start justify-between gap-2">
            <div>
              <h3 class="font-bold text-base text-white group-hover:text-emerald-400 transition-colors">
                <?= e($r['customer_name']) ?>
              </h3>
              <span class="text-[11px] text-slate-400 flex items-center gap-1 mt-0.5">
                <svg class="w-3 h-3 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                <span><?= e($r['location']) ?></span>
              </span>
            </div>

            <a href="/admin/reviews.php?toggle=<?= $r['id'] ?>" 
               class="px-2.5 py-1 rounded-full text-[10px] font-black transition-colors <?= $r['status'] ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-slate-900 text-slate-400 border border-slate-700' ?>">
              <?= $r['status'] ? '● Approved' : '● Hidden' ?>
            </a>
          </div>

          <!-- Star Rating -->
          <div class="flex items-center text-amber-400 text-sm tracking-widest">
            <?php for ($i = 0; $i < $r['rating']; $i++): ?>★<?php endfor; ?>
          </div>

          <!-- Comment -->
          <p class="text-xs text-slate-300 leading-relaxed italic p-3 rounded-2xl bg-[#061009] border border-emerald-950">
            "<?= e($r['comment']) ?>"
          </p>
        </div>

        <!-- Action Buttons -->
        <div class="pt-3 border-t border-emerald-950 flex items-center justify-between gap-2">
          <a href="/admin/reviews.php?toggle=<?= $r['id'] ?>" 
             class="text-xs font-bold <?= $r['status'] ? 'text-amber-400 hover:underline' : 'text-emerald-400 hover:underline' ?>">
            <?= $r['status'] ? 'Hide on Storefront' : 'Approve for Storefront' ?>
          </a>

          <a href="/admin/reviews.php?delete=<?= $r['id'] ?>" 
             onclick="return confirm('Delete review?')" 
             class="px-3 py-1.5 rounded-xl bg-rose-950/60 hover:bg-rose-900 border border-rose-900/60 text-rose-300 text-xs font-semibold transition-colors">
            Delete
          </a>
        </div>

      </div>
    <?php endforeach; ?>
  </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
