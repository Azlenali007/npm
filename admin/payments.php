<?php
/**
 * N.A Fresh Fruits & Coconuts - SaaS Payments & Gateway Journal
 * 100% Responsive Card Grid (No Table)
 */

$adminTitle = 'Payments & Transactions';
require_once __DIR__ . '/header.php';

// Fetch orders with payment details
$query = "
    SELECT id, order_number, customer_name, customer_phone, area_name,
           total_amount, payment_method, payment_status, razorpay_payment_id,
           created_at
    FROM orders
    ORDER BY id DESC
    LIMIT 60
";
$payments = $db->query($query)->fetchAll();

// Payment stats
$totalPaidRevenue = (float)$db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'Paid'")->fetchColumn();
$totalPendingPayments = (float)$db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'Pending'")->fetchColumn();
$paidCount = (int)$db->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'Paid'")->fetchColumn();
?>

<div class="space-y-6">
  
  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="font-black text-2xl text-white tracking-tight">Payments & Transactions</h1>
      <p class="text-xs text-slate-400 mt-1">Payment logs for Cash on Delivery, UPI QR & Razorpay transactions in Jaora.</p>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
    <div class="p-6 rounded-3xl bg-[#09160e] border border-emerald-900/40 shadow-xl space-y-1">
      <span class="text-[10px] font-extrabold text-emerald-400 uppercase tracking-widest">Total Collected</span>
      <div class="font-black text-2xl sm:text-3xl text-white"><?= format_price($totalPaidRevenue) ?></div>
      <span class="text-xs text-slate-400 font-semibold"><?= $paidCount ?> Verified Payments</span>
    </div>

    <div class="p-6 rounded-3xl bg-[#09160e] border border-emerald-900/40 shadow-xl space-y-1">
      <span class="text-[10px] font-extrabold text-amber-400 uppercase tracking-widest">Pending Collection (COD)</span>
      <div class="font-black text-2xl sm:text-3xl text-amber-300"><?= format_price($totalPendingPayments) ?></div>
      <span class="text-xs text-slate-400 font-semibold">Cash to collect upon doorstep delivery</span>
    </div>

    <div class="p-6 rounded-3xl bg-[#09160e] border border-emerald-900/40 shadow-xl space-y-1">
      <span class="text-[10px] font-extrabold text-cyan-400 uppercase tracking-widest">Payment Gateways</span>
      <div class="font-bold text-sm text-white pt-1">Razorpay Verified + Cash on Delivery</div>
      <span class="text-xs text-slate-400 font-semibold">Instant verification via webhooks & HMAC</span>
    </div>
  </div>

  <!-- Payment Cards Grid (No Table) -->
  <?php if (empty($payments)): ?>
    <div class="p-12 text-center rounded-3xl bg-[#09160e] border border-emerald-900/40 text-slate-400 text-sm shadow-xl">
      No transactions recorded yet.
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <?php foreach ($payments as $pay): ?>
        <div class="p-6 rounded-3xl bg-[#09160e] border border-emerald-900/40 hover:border-emerald-600/60 transition-all flex flex-col justify-between space-y-4 group shadow-xl">
          
          <!-- Top Row: Payment ID / Order & Status -->
          <div class="flex items-start justify-between gap-2 pb-3 border-b border-emerald-950">
            <div>
              <span class="font-mono font-black text-sm text-white group-hover:text-emerald-400 transition-colors">
                <?= e($pay['order_number']) ?>
              </span>
              <span class="text-[11px] text-slate-400 block mt-0.5">
                <?= date('d M Y, h:i A', strtotime($pay['created_at'])) ?>
              </span>
            </div>

            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wide <?= $pay['payment_status'] === 'Paid' ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-amber-950 text-amber-300 border border-amber-800' ?>">
              ● <?= e($pay['payment_status']) ?>
            </span>
          </div>

          <!-- Customer & Method Details -->
          <div class="space-y-2 text-xs">
            <div class="flex items-center justify-between">
              <span class="font-bold text-white text-sm"><?= e($pay['customer_name']) ?></span>
              <span class="font-mono text-slate-400"><?= e($pay['customer_phone']) ?></span>
            </div>

            <div class="p-3.5 rounded-2xl bg-[#061009] border border-emerald-950 space-y-1.5">
              <div class="flex items-center justify-between text-slate-300">
                <span class="text-slate-400">Method:</span>
                <span class="font-bold text-white"><?= e($pay['payment_method']) ?></span>
              </div>
              <?php if (!empty($pay['razorpay_payment_id'])): ?>
                <div class="flex items-center justify-between text-slate-300">
                  <span class="text-slate-400">Gateway Ref:</span>
                  <span class="font-mono text-[10px] text-emerald-400"><?= e($pay['razorpay_payment_id']) ?></span>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Amount -->
          <div class="pt-3 border-t border-emerald-950 flex items-center justify-between">
            <div>
              <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Amount</span>
              <span class="font-black text-xl text-emerald-400">
                <?= format_price($pay['total_amount']) ?>
              </span>
            </div>

            <div class="flex items-center gap-2">
              <a href="/invoice.php?id=<?= $pay['id'] ?>" target="_blank" 
                 class="px-3 py-1.5 rounded-xl bg-[#061009] hover:bg-slate-900 border border-emerald-950 text-slate-300 hover:text-white font-bold text-xs transition-colors">
                Invoice
              </a>
              <a href="/admin/orders.php?view=<?= $pay['id'] ?>" 
                 class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs transition-colors shadow-sm">
                Order →
              </a>
            </div>
          </div>

        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
