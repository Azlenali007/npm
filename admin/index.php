<?php
/**
 * N.A Fresh Fruits & Coconuts - SaaS Executive Store Dashboard
 * Real MySQL statistics and ApexCharts analytics visualization
 */

$adminTitle = 'Store Executive Dashboard';
require_once __DIR__ . '/header.php';

$db = getDB();

// 1. Metric: Total Paid Revenue
$stmt = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'Paid'");
$totalRevenue = (float)$stmt->fetchColumn();

// 2. Metric: Total Orders
$stmt = $db->query("SELECT COUNT(*) FROM orders");
$totalOrders = (int)$stmt->fetchColumn();

// 3. Metric: Pending Orders
$stmt = $db->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('Pending', 'Confirmed', 'Preparing')");
$pendingOrders = (int)$stmt->fetchColumn();

// 4. Metric: Total Registered Customers
$stmt = $db->query("SELECT COUNT(*) FROM users");
$totalCustomers = (int)$stmt->fetchColumn();

// 5. 7-Day Revenue Trend (Real MySQL Query)
$revenueDays = [];
$revenueTotals = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $displayDate = date('d M', strtotime($date));
    $stmt = $db->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE DATE(created_at) = ? AND payment_status = 'Paid'");
    $stmt->execute([$date]);
    $revenueDays[] = $displayDate;
    $revenueTotals[] = (float)$stmt->fetchColumn();
}

// 6. Orders by Status Distribution (Real MySQL Query)
$statuses = ['Pending', 'Confirmed', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'];
$statusCounts = [];
$checkStatus = $db->prepare("SELECT COUNT(*) FROM orders WHERE order_status = ?");
foreach ($statuses as $st) {
    $checkStatus->execute([$st]);
    $statusCounts[] = (int)$checkStatus->fetchColumn();
}

// 7. Top Selling Products (Real MySQL Query)
$stmt = $db->query("
    SELECT product_name, SUM(quantity) as total_qty, SUM(total_price) as total_sales
    FROM order_items
    GROUP BY product_name
    ORDER BY total_qty DESC
    LIMIT 5
");
$topProducts = $stmt->fetchAll();
$topProductNames = [];
$topProductQtys = [];
foreach ($topProducts as $tp) {
    $topProductNames[] = $tp['product_name'];
    $topProductQtys[] = (int)$tp['total_qty'];
}
if (empty($topProductNames)) {
    $stmt = $db->query("SELECT name FROM products WHERE is_bestseller = 1 LIMIT 4");
    while ($r = $stmt->fetch()) {
        $topProductNames[] = $r['name'];
        $topProductQtys[] = 0;
    }
}

// 8. Latest 6 Orders
$stmt = $db->query("
    SELECT * FROM orders 
    ORDER BY id DESC 
    LIMIT 6
");
$recentOrders = $stmt->fetchAll();
?>

<!-- Dispatch Alert Banner (When Pending Orders Exist) -->
<?php if ($pendingOrders > 0): ?>
  <div class="mb-8 p-4 sm:p-5 rounded-3xl bg-gradient-to-r from-amber-950/60 via-amber-900/30 to-amber-950/60 border border-amber-600/40 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-3.5">
      <div class="w-10 h-10 rounded-2xl bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-amber-400 text-lg shrink-0">
        ⚡
      </div>
      <div>
        <h4 class="font-extrabold text-sm text-white">Action Required: <?= $pendingOrders ?> Active <?= $pendingOrders === 1 ? 'Order' : 'Orders' ?> in Queue</h4>
        <p class="text-xs text-amber-200/80 mt-0.5">Orders are awaiting preparation or delivery rider assignment in Jaora.</p>
      </div>
    </div>
    <a href="/admin/orders.php?status=Pending" 
       class="px-5 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-xs shadow-md transition-colors flex items-center justify-center gap-1.5 shrink-0">
      <span>Open Dispatch Board</span>
      <span>→</span>
    </a>
  </div>
<?php endif; ?>

<!-- SaaS Executive KPI Metrics Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6 mb-8">
  
  <!-- Metric 1: Total Revenue -->
  <div class="p-6 rounded-3xl bg-[#09160e] border border-emerald-900/40 hover:border-emerald-600/50 transition-all duration-300 shadow-xl space-y-3 group">
    <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase tracking-wider">
      <span>Verified Revenue</span>
      <div class="w-10 h-10 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-base group-hover:scale-110 transition-transform">
        ₹
      </div>
    </div>
    <div class="font-sans font-black text-3xl sm:text-4xl text-white tracking-tight">
      <?= format_price($totalRevenue) ?>
    </div>
    <div class="flex items-center gap-1.5 text-[11px] text-emerald-400 font-semibold">
      <span>●</span>
      <span>Lifetime settled online & COD collections</span>
    </div>
  </div>

  <!-- Metric 2: Total Orders -->
  <div class="p-6 rounded-3xl bg-[#09160e] border border-emerald-900/40 hover:border-blue-600/50 transition-all duration-300 shadow-xl space-y-3 group">
    <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase tracking-wider">
      <span>Lifetime Orders</span>
      <div class="w-10 h-10 rounded-2xl bg-blue-500/15 border border-blue-500/30 text-blue-400 flex items-center justify-center text-base group-hover:scale-110 transition-transform">
        📦
      </div>
    </div>
    <div class="font-sans font-black text-3xl sm:text-4xl text-white tracking-tight">
      <?= $totalOrders ?>
    </div>
    <div class="flex items-center gap-1.5 text-[11px] text-blue-400 font-semibold">
      <span>●</span>
      <span>Total drink requests processed in Jaora</span>
    </div>
  </div>

  <!-- Metric 3: Active Orders -->
  <div class="p-6 rounded-3xl bg-[#09160e] border border-emerald-900/40 hover:border-amber-600/50 transition-all duration-300 shadow-xl space-y-3 group">
    <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase tracking-wider">
      <span>Active Dispatch</span>
      <div class="w-10 h-10 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center text-base group-hover:scale-110 transition-transform">
        ⚡
      </div>
    </div>
    <div class="font-sans font-black text-3xl sm:text-4xl text-amber-400 tracking-tight">
      <?= $pendingOrders ?>
    </div>
    <div class="flex items-center gap-1.5 text-[11px] text-amber-300/80 font-semibold">
      <span>●</span>
      <span>Pending preparation / out for delivery</span>
    </div>
  </div>

  <!-- Metric 4: Total Customers -->
  <div class="p-6 rounded-3xl bg-[#09160e] border border-emerald-900/40 hover:border-purple-600/50 transition-all duration-300 shadow-xl space-y-3 group">
    <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase tracking-wider">
      <span>Registered Buyers</span>
      <div class="w-10 h-10 rounded-2xl bg-purple-500/15 border border-purple-500/30 text-purple-400 flex items-center justify-center text-base group-hover:scale-110 transition-transform">
        👥
      </div>
    </div>
    <div class="font-sans font-black text-3xl sm:text-4xl text-white tracking-tight">
      <?= $totalCustomers ?>
    </div>
    <div class="flex items-center gap-1.5 text-[11px] text-purple-300/80 font-semibold">
      <span>●</span>
      <span>Resident customer accounts across Jaora</span>
    </div>
  </div>

</div>

<!-- ApexCharts Visualizations (Real MySQL Data) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 mb-8">
  
  <!-- 7-Day Revenue Trend Chart (8 Cols) -->
  <div class="lg:col-span-8 p-6 sm:p-8 rounded-3xl bg-[#09160e] border border-emerald-900/40 shadow-xl space-y-4">
    <div class="flex items-center justify-between pb-2 border-b border-emerald-950/60">
      <div>
        <h3 class="font-extrabold text-base text-white">7-Day Revenue Trajectory</h3>
        <p class="text-xs text-slate-400 mt-0.5">Daily verified sales collected in INR (₹)</p>
      </div>
      <span class="text-xs font-bold text-emerald-400 bg-emerald-950/70 border border-emerald-800/80 px-3 py-1 rounded-xl">
        Live MySQL Stream
      </span>
    </div>

    <!-- Chart Container -->
    <div id="revenueChart" class="w-full"></div>
  </div>

  <!-- Orders by Status Donut Chart (4 Cols) -->
  <div class="lg:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#09160e] border border-emerald-900/40 shadow-xl space-y-4">
    <div class="pb-2 border-b border-emerald-950/60">
      <h3 class="font-extrabold text-base text-white">Fulfillment Lifecycle</h3>
      <p class="text-xs text-slate-400 mt-0.5">Orders distributed across fulfillment states</p>
    </div>

    <div id="statusChart" class="w-full flex items-center justify-center"></div>
  </div>

</div>

<!-- Second Charts Row: Product Performance -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 mb-8">
  <div class="lg:col-span-12 p-6 sm:p-8 rounded-3xl bg-[#09160e] border border-emerald-900/40 shadow-xl space-y-4">
    <div class="flex items-center justify-between pb-2 border-b border-emerald-950/60">
      <div>
        <h3 class="font-extrabold text-base text-white">Best Performing Juices & Tender Coconuts</h3>
        <p class="text-xs text-slate-400 mt-0.5">Total units sold per product in Jaora</p>
      </div>
      <a href="/admin/products.php" class="text-xs font-bold text-emerald-400 hover:text-emerald-300">Manage Menu →</a>
    </div>
    <div id="topProductsChart" class="w-full"></div>
  </div>
</div>

<!-- Recent Orders - Responsive Cards (No Table) -->
<div class="p-6 sm:p-8 rounded-3xl bg-[#09160e] border border-emerald-900/40 shadow-xl space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-emerald-950/60">
    <div>
      <h3 class="font-extrabold text-lg text-white">Real-Time Jaora Orders</h3>
      <p class="text-xs text-slate-400 mt-0.5">Most recent doorstep customer orders received</p>
    </div>
    <a href="/admin/orders.php" class="text-xs font-extrabold text-emerald-400 hover:text-emerald-300 transition-colors flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-950/60 border border-emerald-800/60">
      <span>View All Orders (<?= $totalOrders ?>)</span>
      <span>→</span>
    </a>
  </div>

  <?php if (empty($recentOrders)): ?>
    <div class="py-12 text-center text-xs text-slate-500 bg-[#061009] rounded-2xl border border-emerald-950/80">
      No orders placed yet. Orders made via the storefront will appear here immediately.
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
      <?php foreach ($recentOrders as $ord): ?>
        <div class="p-5 rounded-2xl bg-[#061009] border border-emerald-950 hover:border-emerald-700/60 transition-all flex flex-col justify-between space-y-4 group shadow-md">
          
          <!-- Top Row: Order Number & Status -->
          <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-emerald-950/80">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
              <span class="font-mono font-bold text-sm text-white group-hover:text-emerald-400 transition-colors">
                <?= e($ord['order_number']) ?>
              </span>
            </div>
            
            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wide
              <?php 
              if ($ord['order_status'] === 'Delivered') echo 'bg-emerald-950 text-emerald-300 border border-emerald-800';
              elseif ($ord['order_status'] === 'Cancelled') echo 'bg-rose-950 text-rose-300 border border-rose-800';
              elseif ($ord['order_status'] === 'Preparing') echo 'bg-blue-950 text-blue-300 border border-blue-800';
              else echo 'bg-amber-950 text-amber-300 border border-amber-800';
              ?>">
              ● <?= e($ord['order_status']) ?>
            </span>
          </div>

          <!-- Customer & Address Summary -->
          <div class="space-y-1.5 text-xs">
            <div class="flex items-center justify-between">
              <span class="font-bold text-white text-sm"><?= e($ord['customer_name']) ?></span>
              <span class="font-mono text-slate-400 text-[11px]"><?= e($ord['customer_phone']) ?></span>
            </div>
            <p class="text-slate-400 text-[11px] truncate flex items-center gap-1">
              <span>📍 <?= e($ord['area_name'] ?? 'Jaora') ?></span>
              <?php if (!empty($ord['delivery_address'])): ?>
                <span>• <?= e($ord['delivery_address']) ?></span>
              <?php endif; ?>
            </p>
          </div>

          <!-- Amount, Payment Method & Status -->
          <div class="p-3 rounded-xl bg-slate-900/60 border border-emerald-950 flex items-center justify-between">
            <div>
              <span class="text-[10px] uppercase font-bold text-slate-500 block">Total</span>
              <span class="font-black text-base text-emerald-400 block mt-0.5">
                <?= format_price($ord['total_amount']) ?>
              </span>
            </div>

            <div class="text-right">
              <span class="text-[11px] text-slate-300 block font-semibold"><?= e($ord['payment_method']) ?></span>
              <span class="text-[10px] font-bold <?= $ord['payment_status'] === 'Paid' ? 'text-emerald-400' : 'text-amber-400' ?>">
                ● <?= e($ord['payment_status']) ?>
              </span>
            </div>
          </div>

          <!-- Action CTA -->
          <div class="pt-1">
            <a href="/admin/orders.php?view=<?= $ord['id'] ?>" 
               class="w-full py-2.5 px-3 rounded-xl bg-slate-900 hover:bg-emerald-600 text-slate-200 hover:text-white font-bold text-xs flex items-center justify-center gap-1.5 transition-colors border border-emerald-950 hover:border-emerald-600">
              <span>Manage Order</span>
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
          </div>

        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- ApexCharts Initialization Scripts -->
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // 1. Revenue Chart
    const revenueOptions = {
      chart: {
        type: 'area',
        height: 280,
        toolbar: { show: false },
        background: 'transparent'
      },
      theme: { mode: 'dark' },
      series: [{
        name: 'Paid Revenue (₹)',
        data: <?= json_encode($revenueTotals) ?>
      }],
      xaxis: {
        categories: <?= json_encode($revenueDays) ?>,
        labels: { style: { colors: '#94a3b8' } }
      },
      yaxis: {
        labels: {
          style: { colors: '#94a3b8' },
          formatter: (val) => '₹' + val
        }
      },
      colors: ['#10b981'],
      stroke: { curve: 'smooth', width: 2.5 },
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.45,
          opacityTo: 0.05,
          stops: [20, 100]
        }
      },
      dataLabels: { enabled: false },
      grid: { borderColor: '#13281c' }
    };
    new ApexCharts(document.querySelector("#revenueChart"), revenueOptions).render();

    // 2. Status Donut Chart
    const statusOptions = {
      chart: {
        type: 'donut',
        height: 280,
        background: 'transparent'
      },
      theme: { mode: 'dark' },
      series: <?= json_encode($statusCounts) ?>,
      labels: <?= json_encode($statuses) ?>,
      colors: ['#eab308', '#3b82f6', '#f97316', '#a855f7', '#10b981', '#ef4444'],
      legend: { position: 'bottom', labels: { colors: '#cbd5e1' } },
      dataLabels: { enabled: false }
    };
    new ApexCharts(document.querySelector("#statusChart"), statusOptions).render();

    // 3. Top Products Bar Chart
    const productOptions = {
      chart: {
        type: 'bar',
        height: 240,
        toolbar: { show: false },
        background: 'transparent'
      },
      theme: { mode: 'dark' },
      series: [{
        name: 'Units Sold',
        data: <?= json_encode($topProductQtys) ?>
      }],
      xaxis: {
        categories: <?= json_encode($topProductNames) ?>,
        labels: { style: { colors: '#94a3b8' } }
      },
      yaxis: {
        labels: { style: { colors: '#94a3b8' } }
      },
      colors: ['#10b981'],
      plotOptions: {
        bar: {
          borderRadius: 8,
          columnWidth: '40%'
        }
      },
      dataLabels: { enabled: false },
      grid: { borderColor: '#13281c' }
    };
    new ApexCharts(document.querySelector("#topProductsChart"), productOptions).render();
  });
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
