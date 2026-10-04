<?php
/**
 * N.A Fresh Fruits & Coconuts - Admin Dashboard
 * Real MySQL statistics and real ApexCharts visualization
 */

$adminTitle = 'Executive Store Dashboard';
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
    // If no order items yet, show top seeded products
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

<!-- Metrics Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
  
  <!-- Metric 1: Total Revenue -->
  <div class="p-6 rounded-3xl bg-slate-950 border border-slate-800 space-y-2">
    <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase tracking-wider">
      <span>Total Paid Sales</span>
      <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400">₹</span>
    </div>
    <div class="font-serif font-bold text-3xl text-white">
      <?= format_price($totalRevenue) ?>
    </div>
    <p class="text-[11px] text-slate-400">Verified received orders</p>
  </div>

  <!-- Metric 2: Total Orders -->
  <div class="p-6 rounded-3xl bg-slate-950 border border-slate-800 space-y-2">
    <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase tracking-wider">
      <span>Total Orders</span>
      <span class="p-2 rounded-xl bg-blue-500/10 text-blue-400">📦</span>
    </div>
    <div class="font-serif font-bold text-3xl text-white">
      <?= $totalOrders ?>
    </div>
    <p class="text-[11px] text-slate-400">Lifetime orders placed in Jaora</p>
  </div>

  <!-- Metric 3: Active Orders -->
  <div class="p-6 rounded-3xl bg-slate-950 border border-slate-800 space-y-2">
    <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase tracking-wider">
      <span>Active Orders</span>
      <span class="p-2 rounded-xl bg-amber-500/10 text-amber-400">⚡</span>
    </div>
    <div class="font-serif font-bold text-3xl text-amber-400">
      <?= $pendingOrders ?>
    </div>
    <p class="text-[11px] text-slate-400">Pending / Preparing / Out for delivery</p>
  </div>

  <!-- Metric 4: Total Customers -->
  <div class="p-6 rounded-3xl bg-slate-950 border border-slate-800 space-y-2">
    <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase tracking-wider">
      <span>Registered Customers</span>
      <span class="p-2 rounded-xl bg-purple-500/10 text-purple-400">👥</span>
    </div>
    <div class="font-serif font-bold text-3xl text-white">
      <?= $totalCustomers ?>
    </div>
    <p class="text-[11px] text-slate-400">Local Jaora accounts</p>
  </div>

</div>

<!-- ApexCharts Visualizations (Real MySQL Data) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
  
  <!-- 7-Day Revenue Trend Chart (8 Cols) -->
  <div class="lg:col-span-8 p-6 sm:p-8 rounded-3xl bg-slate-950 border border-slate-800 space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h3 class="font-bold text-base text-white">7-Day Revenue Trend</h3>
        <p class="text-xs text-slate-400 mt-0.5">Daily sales verified in INR (₹)</p>
      </div>
      <span class="text-xs font-bold text-emerald-400 bg-emerald-950/60 border border-emerald-800/80 px-2.5 py-1 rounded-lg">
        Live MySQL Data
      </span>
    </div>

    <!-- Chart Container -->
    <div id="revenueChart" class="w-full"></div>
  </div>

  <!-- Orders by Status Donut Chart (4 Cols) -->
  <div class="lg:col-span-4 p-6 sm:p-8 rounded-3xl bg-slate-950 border border-slate-800 space-y-4">
    <div>
      <h3 class="font-bold text-base text-white">Order Statuses</h3>
      <p class="text-xs text-slate-400 mt-0.5">Breakdown by current lifecycle stage</p>
    </div>

    <div id="statusChart" class="w-full flex items-center justify-center"></div>
  </div>

</div>

<!-- Second Charts Row: Product Performance -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
  <div class="lg:col-span-12 p-6 sm:p-8 rounded-3xl bg-slate-950 border border-slate-800 space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h3 class="font-bold text-base text-white">Top Ordered Fresh Juices & Coconuts</h3>
        <p class="text-xs text-slate-400 mt-0.5">Quantity sold per product in Jaora</p>
      </div>
    </div>
    <div id="topProductsChart" class="w-full"></div>
  </div>
</div>

<!-- Recent Orders Table -->
<div class="p-6 sm:p-8 rounded-3xl bg-slate-950 border border-slate-800 space-y-4">
  <div class="flex items-center justify-between">
    <div>
      <h3 class="font-bold text-base text-white">Latest Orders</h3>
      <p class="text-xs text-slate-400 mt-0.5">Real-time orders received from Jaora residents</p>
    </div>
    <a href="/admin/orders.php" class="text-xs font-bold text-emerald-400 hover:underline">
      View All Orders (<?= $totalOrders ?>) →
    </a>
  </div>

  <?php if (empty($recentOrders)): ?>
    <div class="py-8 text-center text-xs text-slate-500">
      No orders placed yet. Orders made via storefront will appear here immediately.
    </div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead>
          <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
            <th class="py-3 px-2">Order #</th>
            <th class="py-3 px-2">Customer</th>
            <th class="py-3 px-2">Area</th>
            <th class="py-3 px-2">Total</th>
            <th class="py-3 px-2">Payment</th>
            <th class="py-3 px-2">Status</th>
            <th class="py-3 px-2 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800">
          <?php foreach ($recentOrders as $ord): ?>
            <tr class="hover:bg-slate-900/60 transition-colors">
              <td class="py-3.5 px-2 font-mono font-bold text-white">
                <?= e($ord['order_number']) ?>
              </td>
              <td class="py-3.5 px-2">
                <span class="font-semibold text-slate-200 block"><?= e($ord['customer_name']) ?></span>
                <span class="text-[11px] text-slate-400"><?= e($ord['customer_phone']) ?></span>
              </td>
              <td class="py-3.5 px-2 text-slate-300">
                <?= e($ord['area_name']) ?>
              </td>
              <td class="py-3.5 px-2 font-serif font-bold text-emerald-400 text-sm">
                <?= format_price($ord['total_amount']) ?>
              </td>
              <td class="py-3.5 px-2">
                <span class="font-medium text-slate-300 block"><?= e($ord['payment_method']) ?></span>
                <span class="text-[10px] <?= $ord['payment_status'] === 'Paid' ? 'text-emerald-400' : 'text-amber-400' ?>">
                  <?= e($ord['payment_status']) ?>
                </span>
              </td>
              <td class="py-3.5 px-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                  <?php 
                  if ($ord['order_status'] === 'Delivered') echo 'bg-emerald-950 text-emerald-300 border border-emerald-800';
                  elseif ($ord['order_status'] === 'Cancelled') echo 'bg-rose-950 text-rose-300 border border-rose-800';
                  else echo 'bg-amber-950 text-amber-300 border border-amber-800';
                  ?>">
                  <?= e($ord['order_status']) ?>
                </span>
              </td>
              <td class="py-3.5 px-2 text-right space-x-2">
                <a href="/admin/orders.php?view=<?= $ord['id'] ?>" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition-colors">
                  Open
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
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
      stroke: { curve: 'smooth', width: 2 },
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
      grid: { borderColor: '#1e293b' }
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
      colors: ['#f59e0b'],
      plotOptions: {
        bar: {
          borderRadius: 6,
          columnWidth: '40%'
        }
      },
      dataLabels: { enabled: false },
      grid: { borderColor: '#1e293b' }
    };
    new ApexCharts(document.querySelector("#topProductsChart"), productOptions).render();
  });
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
