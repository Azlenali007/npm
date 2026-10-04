<?php
/**
 * N.A Fresh Fruits & Coconuts - Admin Customers (Card-Based UI)
 */

$adminTitle = 'Customer Directory';
require_once __DIR__ . '/header.php';

// Fetch users with order count and total spend
$query = "
    SELECT u.id, u.name, u.phone, u.email, u.created_at,
           COUNT(o.id) as total_orders,
           COALESCE(SUM(o.total_amount), 0) as total_spent,
           MAX(o.created_at) as last_order_date
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    GROUP BY u.id
    ORDER BY total_spent DESC, u.id DESC
";
$customers = $db->query($query)->fetchAll();
$totalCustomerCount = count($customers);
?>

<div class="space-y-6">
  
  <!-- Header Title & Metric Summary -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="font-extrabold text-2xl text-white tracking-tight">Customer Directory</h1>
      <p class="text-xs text-slate-400 mt-1">Customers registered and ordering fresh drinks across Jaora.</p>
    </div>

    <div class="flex items-center gap-3">
      <div class="px-4 py-2 rounded-2xl bg-slate-900 border border-slate-800 text-xs font-bold text-slate-200">
        <span>Total Customers: </span>
        <span class="text-emerald-400"><?= $totalCustomerCount ?></span>
      </div>
    </div>
  </div>

  <!-- Customer Cards Grid (No Table) -->
  <?php if (empty($customers)): ?>
    <div class="p-12 text-center rounded-3xl bg-slate-950 border border-slate-800 text-slate-400 text-sm">
      No registered customer accounts yet. Customers creating an account during checkout will appear here.
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
      <?php foreach ($customers as $c): ?>
        <div class="p-6 rounded-3xl bg-[#09160e] border border-emerald-900/40 hover:border-emerald-600/60 transition-all flex flex-col justify-between space-y-4 group shadow-xl">
          
          <div class="space-y-3">
            <!-- Profile Avatar & Name -->
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-600 to-emerald-800 text-white font-black text-base flex items-center justify-center shrink-0 shadow-md border border-emerald-400/20">
                <?= strtoupper(substr($c['name'] ?? 'U', 0, 1)) ?>
              </div>
              <div class="overflow-hidden">
                <h3 class="font-bold text-base text-white group-hover:text-emerald-400 transition-colors truncate">
                  <?= e($c['name']) ?>
                </h3>
                <span class="text-[11px] text-slate-400 block truncate">
                  Joined <?= date('M Y', strtotime($c['created_at'])) ?>
                </span>
              </div>
            </div>

            <!-- Phone & Quick Actions -->
            <div class="p-3 rounded-2xl bg-[#061009] border border-emerald-950 flex items-center justify-between">
              <span class="font-mono text-xs text-slate-200"><?= e($c['phone']) ?></span>
              <div class="flex items-center gap-1.5">
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $c['phone']) ?>" 
                   target="_blank" 
                   class="p-1.5 rounded-lg bg-emerald-950 text-emerald-300 hover:bg-emerald-900 border border-emerald-800 transition-colors"
                   title="WhatsApp Customer">
                  <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.962 1.199.662.589 1.221.771 1.394.858.173.086.274.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z"/></svg>
                </a>
                <a href="tel:<?= e($c['phone']) ?>" 
                   class="p-1.5 rounded-lg bg-[#061009] border border-emerald-950 text-slate-300 hover:text-white transition-colors"
                   title="Call Customer">
                  <svg class="w-3.5 h-3.5 fill-none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </a>
              </div>
            </div>
          </div>

          <!-- Order Stats -->
          <div class="pt-3 border-t border-emerald-950 grid grid-cols-2 gap-2 text-center">
            <div class="p-2.5 rounded-xl bg-[#061009] border border-emerald-950">
              <span class="text-[10px] uppercase font-bold text-slate-400 block">Orders</span>
              <span class="font-black text-base text-white block mt-0.5">
                <?= (int)$c['total_orders'] ?>
              </span>
            </div>
            <div class="p-2.5 rounded-xl bg-[#061009] border border-emerald-950">
              <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Spend</span>
              <span class="font-black text-base text-emerald-400 block mt-0.5">
                <?= format_price($c['total_spent']) ?>
              </span>
            </div>
          </div>

          <!-- Bottom Action -->
          <div>
            <a href="/admin/orders.php?q=<?= urlencode($c['phone']) ?>" 
               class="w-full py-2.5 px-3 rounded-xl bg-[#061009] hover:bg-emerald-600 border border-emerald-950 hover:border-emerald-600 text-slate-300 hover:text-white font-bold text-xs flex items-center justify-center gap-1.5 transition-colors">
              <span>View Customer Orders (<?= (int)$c['total_orders'] ?>)</span>
              <span>→</span>
            </a>
          </div>

        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
