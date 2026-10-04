<?php
/**
 * N.A Fresh Fruits & Coconuts - SaaS Executive Admin Shell
 * Modern premium dashboard navigation, mobile drawer, responsive top-bar & theme
 */

require_once __DIR__ . '/../config/database.php';

if (!is_admin_logged_in()) {
    header("Location: /admin/login.php");
    exit;
}

$admin = current_admin();
$db = getDB();

// Dynamic counter badges
$pendingCount = (int)$db->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('Pending', 'Confirmed')")->fetchColumn();
$lowStockCount = (int)$db->query("SELECT COUNT(*) FROM products WHERE in_stock = 0")->fetchColumn();

$activePage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($adminTitle ?? 'Dashboard') ?> – N.A Fresh Fruits Admin Portal</title>
  
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:wght@600;700&display=swap" rel="stylesheet">
  
  <!-- ApexCharts (Real MySQL Admin Charts) -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

  <!-- Alpine.js -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

  <style>
    [x-cloak] { display: none !important; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    
    /* Modern SaaS Scrollbars */
    ::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    ::-webkit-scrollbar-track {
      background: #050d08;
    }
    ::-webkit-scrollbar-thumb {
      background: #143522;
      border-radius: 9999px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: #1e5234;
    }
  </style>
</head>
<body class="bg-[#061009] text-slate-100 min-h-screen flex antialiased selection:bg-emerald-600 selection:text-white" x-data="{ sidebarOpen: false }">

  <!-- Mobile Sidebar Backdrop Overlay -->
  <div x-show="sidebarOpen" 
       x-cloak
       @click="sidebarOpen = false" 
       x-transition:enter="transition-opacity ease-out duration-300"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition-opacity ease-in duration-200"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       class="fixed inset-0 z-40 bg-black/80 backdrop-blur-sm lg:hidden"></div>

  <!-- Premium SaaS Admin Sidebar -->
  <aside class="fixed inset-y-0 left-0 z-50 w-72 bg-[#040b06] border-r border-emerald-950/70 flex flex-col justify-between transition-transform duration-300 lg:static lg:translate-x-0 shadow-2xl"
         :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
    
    <div class="flex-1 flex flex-col min-h-0">
      
      <!-- Brand Header in Sidebar -->
      <div class="h-20 px-6 flex items-center justify-between border-b border-emerald-950/80 bg-[#030905]">
        <a href="/admin/index.php" class="flex items-center gap-3 group">
          <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white shadow-lg shadow-emerald-950/50 group-hover:scale-105 transition-transform border border-emerald-400/20">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <ellipse cx="12" cy="13" rx="8" ry="7" />
              <path d="M12 6v7" />
              <path d="M9 10l3 3 5-5" />
            </svg>
          </div>
          <div class="flex flex-col">
            <span class="font-extrabold text-sm text-white tracking-tight leading-tight">N.A Fresh Fruits</span>
            <span class="text-[10px] text-emerald-400 font-bold tracking-widest uppercase mt-0.5">Control Center</span>
          </div>
        </a>
        <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-900 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <!-- Quick Store Status Pill -->
      <div class="px-5 pt-4">
        <div class="p-2.5 rounded-2xl bg-emerald-950/40 border border-emerald-800/40 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span class="text-[11px] font-bold text-emerald-300">Jaora Hub Live</span>
          </div>
          <span class="text-[10px] font-mono text-emerald-400/80">Station Rd</span>
        </div>
      </div>

      <!-- Navigation Links Container -->
      <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-5 text-xs font-semibold">
        
        <!-- Main Section -->
        <div>
          <span class="px-3 text-[10px] font-extrabold text-emerald-500/60 uppercase tracking-widest block mb-2">Overview</span>
          <div class="space-y-1">
            <a href="/admin/index.php" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 <?= $activePage === 'index' ? 'bg-gradient-to-r from-emerald-600 to-emerald-700 text-white shadow-md shadow-emerald-950/60 font-bold border border-emerald-500/30' : 'text-slate-400 hover:bg-slate-900/80 hover:text-slate-200' ?>">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
              <span>Dashboard</span>
            </a>

            <a href="/" target="_blank" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-slate-400 hover:bg-slate-900/80 hover:text-slate-200 transition-colors">
              <div class="flex items-center gap-3">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Live Customer Store</span>
              </div>
              <span class="text-[9px] bg-emerald-950 border border-emerald-800 text-emerald-300 px-1.5 py-0.5 rounded font-bold uppercase">Storefront</span>
            </a>
          </div>
        </div>

        <!-- Operations & Catalog Section -->
        <div>
          <span class="px-3 text-[10px] font-extrabold text-emerald-500/60 uppercase tracking-widest block mb-2">Inventory & Orders</span>
          <div class="space-y-1">
            <a href="/admin/orders.php" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-200 <?= $activePage === 'orders' ? 'bg-gradient-to-r from-emerald-600 to-emerald-700 text-white shadow-md shadow-emerald-950/60 font-bold border border-emerald-500/30' : 'text-slate-400 hover:bg-slate-900/80 hover:text-slate-200' ?>">
              <div class="flex items-center gap-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <span>Orders & Dispatch</span>
              </div>
              <?php if ($pendingCount > 0): ?>
                <span class="px-2 py-0.5 rounded-full bg-amber-500 text-slate-950 font-black text-[10px] animate-pulse">
                  <?= $pendingCount ?>
                </span>
              <?php endif; ?>
            </a>

            <a href="/admin/products.php" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-200 <?= $activePage === 'products' ? 'bg-gradient-to-r from-emerald-600 to-emerald-700 text-white shadow-md shadow-emerald-950/60 font-bold border border-emerald-500/30' : 'text-slate-400 hover:bg-slate-900/80 hover:text-slate-200' ?>">
              <div class="flex items-center gap-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Products & Stock</span>
              </div>
              <?php if ($lowStockCount > 0): ?>
                <span class="px-1.5 py-0.5 rounded bg-rose-950 border border-rose-800 text-rose-300 text-[10px] font-bold">
                  <?= $lowStockCount ?> Out
                </span>
              <?php endif; ?>
            </a>

            <a href="/admin/payments.php" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 <?= $activePage === 'payments' ? 'bg-gradient-to-r from-emerald-600 to-emerald-700 text-white shadow-md shadow-emerald-950/60 font-bold border border-emerald-500/30' : 'text-slate-400 hover:bg-slate-900/80 hover:text-slate-200' ?>">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
              <span>Payments & UPI</span>
            </a>

            <a href="/admin/customers.php" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 <?= $activePage === 'customers' ? 'bg-gradient-to-r from-emerald-600 to-emerald-700 text-white shadow-md shadow-emerald-950/60 font-bold border border-emerald-500/30' : 'text-slate-400 hover:bg-slate-900/80 hover:text-slate-200' ?>">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
              <span>Customer Accounts</span>
            </a>
          </div>
        </div>

        <!-- Logistics & Engagement Section -->
        <div>
          <span class="px-3 text-[10px] font-extrabold text-emerald-500/60 uppercase tracking-widest block mb-2">Jaora Logistics & Promo</span>
          <div class="space-y-1">
            <a href="/admin/delivery-areas.php" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 <?= $activePage === 'delivery-areas' ? 'bg-gradient-to-r from-emerald-600 to-emerald-700 text-white shadow-md shadow-emerald-950/60 font-bold border border-emerald-500/30' : 'text-slate-400 hover:bg-slate-900/80 hover:text-slate-200' ?>">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              <span>Delivery Areas (Jaora)</span>
            </a>

            <a href="/admin/coupons.php" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 <?= $activePage === 'coupons' ? 'bg-gradient-to-r from-emerald-600 to-emerald-700 text-white shadow-md shadow-emerald-950/60 font-bold border border-emerald-500/30' : 'text-slate-400 hover:bg-slate-900/80 hover:text-slate-200' ?>">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
              <span>Coupons & Offers</span>
            </a>

            <a href="/admin/reviews.php" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 <?= $activePage === 'reviews' ? 'bg-gradient-to-r from-emerald-600 to-emerald-700 text-white shadow-md shadow-emerald-950/60 font-bold border border-emerald-500/30' : 'text-slate-400 hover:bg-slate-900/80 hover:text-slate-200' ?>">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
              <span>Customer Reviews</span>
            </a>

            <a href="/admin/gallery.php" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 <?= $activePage === 'gallery' ? 'bg-gradient-to-r from-emerald-600 to-emerald-700 text-white shadow-md shadow-emerald-950/60 font-bold border border-emerald-500/30' : 'text-slate-400 hover:bg-slate-900/80 hover:text-slate-200' ?>">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              <span>Shop & Hygiene Gallery</span>
            </a>

            <a href="/admin/push.php" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 <?= $activePage === 'push' ? 'bg-gradient-to-r from-emerald-600 to-emerald-700 text-white shadow-md shadow-emerald-950/60 font-bold border border-emerald-500/30' : 'text-slate-400 hover:bg-slate-900/80 hover:text-slate-200' ?>">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
              <span>Broadcast Alerts</span>
            </a>
          </div>
        </div>

        <!-- Configuration Section -->
        <div>
          <span class="px-3 text-[10px] font-extrabold text-emerald-500/60 uppercase tracking-widest block mb-2">System</span>
          <div class="space-y-1">
            <a href="/admin/settings.php" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 <?= $activePage === 'settings' ? 'bg-gradient-to-r from-emerald-600 to-emerald-700 text-white shadow-md shadow-emerald-950/60 font-bold border border-emerald-500/30' : 'text-slate-400 hover:bg-slate-900/80 hover:text-slate-200' ?>">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              <span>Store Configuration</span>
            </a>
          </div>
        </div>

      </nav>
    </div>

    <!-- Admin User Info & Logout in Sidebar -->
    <div class="p-4 border-t border-emerald-950/80 bg-[#030905]">
      <div class="flex items-center justify-between p-2.5 rounded-2xl bg-slate-900/70 border border-emerald-900/30">
        <div class="flex items-center gap-2.5 overflow-hidden">
          <div class="relative w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-800 text-white font-black text-xs flex items-center justify-center shrink-0 shadow-md">
            <?= strtoupper(substr($admin['full_name'] ?? 'A', 0, 1)) ?>
            <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-400 border-2 border-[#030905]"></span>
          </div>
          <div class="overflow-hidden">
            <span class="block text-xs font-bold text-white truncate"><?= e($admin['full_name']) ?></span>
            <span class="text-[10px] text-emerald-400 font-semibold block truncate">Super Administrator</span>
          </div>
        </div>
        <a href="/admin/logout.php" class="p-2 text-slate-400 hover:text-rose-400 transition-colors rounded-xl hover:bg-slate-800/80" title="Log Out">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        </a>
      </div>
    </div>

  </aside>

  <!-- Main Admin Content Area -->
  <div class="flex-1 flex flex-col min-w-0">
    
    <!-- Top Header Bar -->
    <header class="h-20 bg-[#050e08]/90 backdrop-blur-md border-b border-emerald-950/70 px-5 sm:px-8 flex items-center justify-between sticky top-0 z-30 shadow-lg">
      <div class="flex items-center gap-3 sm:gap-4">
        <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-900 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
        </button>
        <div>
          <div class="flex items-center gap-1.5 text-[11px] text-emerald-400 font-bold uppercase tracking-wider">
            <span>Admin</span>
            <span>/</span>
            <span class="text-slate-300"><?= e($adminTitle ?? 'Dashboard') ?></span>
          </div>
          <h2 class="font-black text-lg sm:text-xl text-white tracking-tight mt-0.5"><?= e($adminTitle ?? 'Dashboard') ?></h2>
        </div>
      </div>

      <div class="flex items-center gap-2.5 sm:gap-3">
        <!-- Live Status Pill -->
        <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-950/60 border border-emerald-800/60 text-[11px] font-bold text-emerald-300">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          <span>Jaora Deliveries Active</span>
        </div>

        <!-- Quick Storefront Link -->
        <a href="/" target="_blank" 
           class="px-3 sm:px-4 py-2 rounded-xl bg-slate-900/90 hover:bg-emerald-950/80 border border-slate-700/80 hover:border-emerald-700/60 text-xs font-bold text-slate-200 hover:text-white transition-all flex items-center gap-2 shadow-xs">
          <span>Customer Store</span>
          <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>

        <!-- Add Drink CTA Button -->
        <a href="/admin/products.php" 
           class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-950/40 transition-colors">
          <span>+ Add Drink</span>
        </a>
      </div>
    </header>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
