<?php
/**
 * N.A Fresh Fruits & Coconuts - Admin Header & Navigation
 */

require_once __DIR__ . '/../config/database.php';

if (!is_admin_logged_in()) {
    header("Location: /admin/login.php");
    exit;
}

$admin = current_admin();
$db = getDB();

// Pending orders count for badge
$pendingCount = (int)$db->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('Pending', 'Confirmed')")->fetchColumn();
$activePage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($adminTitle ?? 'Admin Dashboard') ?> – N.A Fresh Fruits & Coconuts</title>
  
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- ApexCharts (Real MySQL Admin Charts) -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

  <!-- Alpine.js -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

  <style>
    [x-cloak] { display: none !important; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
  </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex" x-data="{ sidebarOpen: false }">

  <!-- Mobile Sidebar Backdrop -->
  <div x-show="sidebarOpen" 
       x-cloak
       @click="sidebarOpen = false" 
       class="fixed inset-0 z-40 bg-black/60 backdrop-blur-xs lg:hidden"></div>

  <!-- Admin Sidebar -->
  <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-950 border-r border-slate-800 flex flex-col justify-between transition-transform duration-300 lg:static lg:translate-x-0"
         :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
    
    <div>
      <!-- Brand Logo in Sidebar -->
      <div class="h-20 px-6 flex items-center justify-between border-b border-slate-800">
        <a href="/admin/index.php" class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-lg shadow">
            NA
          </div>
          <div>
            <span class="font-bold text-sm text-white block leading-tight">N.A Fresh Fruits</span>
            <span class="text-[10px] text-emerald-400 font-semibold uppercase tracking-wider">Admin Control</span>
          </div>
        </a>
        <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <!-- Navigation Links -->
      <nav class="p-4 space-y-1 text-xs font-semibold">
        <a href="/admin/index.php" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors <?= $activePage === 'index' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' ?>">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
          <span>Dashboard</span>
        </a>

        <a href="/admin/orders.php" 
           class="flex items-center justify-between px-4 py-3 rounded-xl transition-colors <?= $activePage === 'orders' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' ?>">
          <div class="flex items-center gap-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            <span>Orders</span>
          </div>
          <?php if ($pendingCount > 0): ?>
            <span class="px-2 py-0.5 rounded-full bg-amber-500 text-slate-950 font-bold text-[10px]">
              <?= $pendingCount ?>
            </span>
          <?php endif; ?>
        </a>

        <a href="/admin/products.php" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors <?= $activePage === 'products' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' ?>">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
          <span>Products & Juices</span>
        </a>

        <a href="/admin/delivery-areas.php" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors <?= $activePage === 'delivery-areas' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' ?>">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <span>Delivery Areas (Jaora)</span>
        </a>

        <a href="/admin/coupons.php" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors <?= $activePage === 'coupons' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' ?>">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
          <span>Coupons & Offers</span>
        </a>

        <a href="/admin/reviews.php" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors <?= $activePage === 'reviews' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' ?>">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
          <span>Customer Reviews</span>
        </a>

        <a href="/admin/gallery.php" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors <?= $activePage === 'gallery' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' ?>">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          <span>Hygiene & Shop Gallery</span>
        </a>

        <a href="/admin/push.php" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors <?= $activePage === 'push' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' ?>">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
          <span>Push Notifications</span>
        </a>

        <a href="/admin/settings.php" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors <?= $activePage === 'settings' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' ?>">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <span>Store Settings (Zomato/Razorpay)</span>
        </a>
      </nav>
    </div>

    <!-- Admin Footer in Sidebar -->
    <div class="p-4 border-t border-slate-800">
      <div class="flex items-center justify-between">
        <div>
          <span class="block text-xs font-bold text-white"><?= e($admin['full_name']) ?></span>
          <span class="text-[10px] text-slate-400">@<?= e($admin['username']) ?></span>
        </div>
        <a href="/admin/logout.php" class="p-2 text-slate-400 hover:text-red-400 transition-colors" title="Log Out">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        </a>
      </div>
    </div>

  </aside>

  <!-- Main Admin Content Area -->
  <div class="flex-1 flex flex-col min-w-0">
    
    <!-- Top Bar -->
    <header class="h-20 bg-slate-950 border-b border-slate-800 px-6 sm:px-8 flex items-center justify-between sticky top-0 z-30">
      <div class="flex items-center gap-4">
        <button @click="sidebarOpen = true" class="lg:hidden p-2 text-slate-400 hover:text-white">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
        </button>
        <h2 class="font-bold text-lg text-white"><?= e($adminTitle ?? 'Dashboard') ?></h2>
      </div>

      <div class="flex items-center gap-4">
        <a href="/" target="_blank" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-xs font-bold text-slate-300 hover:text-white hover:border-slate-600 transition-colors flex items-center gap-1.5">
          <span>Live Store</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>
      </div>
    </header>

    <main class="flex-1 p-6 sm:p-8 overflow-y-auto">
