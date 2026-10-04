<?php
/**
 * N.A Fresh Fruits & Coconuts - Header Component
 * Jaora, Madhya Pradesh, India
 */

require_once __DIR__ . '/../config/database.php';

$cartData = get_cart_data();
$cartCount = $cartData['item_count'];
$user = current_user();

$bizName = get_setting('business_name', 'N.A Fresh Fruits & Coconuts');
$bizPhone = get_setting('phone', '+91 98260 12345');
$bizWhatsApp = get_setting('whatsapp', '919826012345');
$zomatoEnabled = (int)get_setting('zomato_enabled', 1);
$zomatoUrl = get_setting('zomato_url', 'https://www.zomato.com');

$pageTitle = $pageTitle ?? ($bizName . ' – Fresh Coconut Water & Juices | Jaora');
$pageDesc = $pageDesc ?? 'Order 100% raw tender coconut water and freshly squeezed juices in Jaora, MP. Cold-cut & pressed fresh upon order with fast local doorstep delivery.';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($pageDesc) ?>">
  <meta name="theme-color" content="#14532d">
  <meta name="robots" content="index, follow">
  
  <!-- OpenGraph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($pageDesc) ?>">
  <meta property="og:image" content="https://images.unsplash.com/photo-1544378730-8b5104b18790?auto=format&fit=crop&w=1200&q=80">
  <meta property="og:locale" content="en_IN">
  
  <!-- Twitter Cards -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= e($pageTitle) ?>">
  <meta name="twitter:description" content="<?= e($pageDesc) ?>">
  <meta name="twitter:image" content="https://images.unsplash.com/photo-1544378730-8b5104b18790?auto=format&fit=crop&w=1200&q=80">

  <!-- Schema.org LocalBusiness Structured Data -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "FoodEstablishment",
    "name": "<?= e($bizName) ?>",
    "image": "https://images.unsplash.com/photo-1544378730-8b5104b18790?auto=format&fit=crop&w=800&q=80",
    "telephone": "<?= e($bizPhone) ?>",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "Shop No. 4, Station Road, Opp. Municipal Garden",
      "addressLocality": "Jaora",
      "addressRegion": "Madhya Pradesh",
      "postalCode": "457226",
      "addressCountry": "IN"
    },
    "geo": {
      "@type": "GeoCoordinates",
      "latitude": 23.6338,
      "longitude": 75.1278
    },
    "servesCuisine": "Fresh Coconut Water, Cold Pressed Juices, Fruit Platters",
    "priceRange": "₹₹",
    "paymentAccepted": "Cash, UPI, Credit Card, Razorpay",
    "currenciesAccepted": "INR",
    "openingHours": "Mo-Su 07:00-22:30"
  }
  </script>

  <!-- Google Fonts: Plus Jakarta Sans, Caveat (Cursive script), and Fraunces -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700;1,9..144,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Tailwind CSS Play CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              50: '#f0fdf4',
              100: '#dcfce7',
              200: '#bbf7d0',
              300: '#86efac',
              400: '#4ade80',
              500: '#22c55e',
              600: '#16a34a',
              700: '#15803d',
              800: '#166534',
              900: '#0b3b24',
              950: '#052e16',
            },
            coconut: {
              cream: '#fdfbf7',
              shell: '#452b14',
              husk: '#8d6e63',
              milk: '#faf7ee',
              light: '#f7f4ea'
            },
            citrus: {
              amber: '#f59e0b',
              orange: '#ea580c',
              gold: '#d97706'
            }
          },
          fontFamily: {
            sans: ['"Plus Jakarta Sans"', 'sans-serif'],
            serif: ['"Fraunces"', 'serif'],
            script: ['"Caveat"', 'cursive'],
          }
        }
      }
    }
  </script>

  <!-- GSAP Animation Library -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

  <!-- Alpine.js Core -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

  <style>
    [x-cloak] { display: none !important; }
    
    /* Horizontal Carousel Smooth Scroll */
    .snap-carousel {
      scroll-snap-type: x mandatory;
      -webkit-overflow-scrolling: touch;
      scrollbar-width: none;
    }
    .snap-carousel::-webkit-scrollbar {
      display: none;
    }
    .snap-card {
      scroll-snap-align: center;
    }
    
    /* Subtle Grain Texture */
    .bg-natural-texture {
      background-color: #fdfbf7;
      background-image: radial-gradient(#14532d 0.5px, transparent 0.5px);
      background-size: 24px 24px;
      background-opacity: 0.03;
    }
  </style>
</head>
<body class="bg-coconut-cream text-slate-800 font-sans antialiased min-h-screen flex flex-col selection:bg-brand-500 selection:text-white pb-16 md:pb-0"
      x-data="globalApp()">

  <!-- Top Announcement Bar -->
  <div class="bg-brand-950 text-brand-100 text-xs py-2 px-4 border-b border-brand-900/60">
    <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <span class="inline-block w-2 h-2 rounded-full bg-brand-400 animate-pulse"></span>
        <span class="font-medium">Direct Grove Harvest</span>
        <span class="text-brand-400/60" aria-hidden="true">•</span>
        <span>Cold-cut & pressed fresh upon order in Jaora</span>
      </div>
      <div class="hidden sm:flex items-center gap-4 text-brand-200">
        <a href="tel:<?= preg_replace('/[^0-9+]/', '', $bizPhone) ?>" class="hover:text-white transition-colors flex items-center gap-1">
          <svg class="w-3.5 h-3.5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
          <?= e($bizPhone) ?>
        </a>
        <span class="text-brand-700">|</span>
        <span class="text-brand-300">Station Road, Jaora (M.P.)</span>
      </div>
    </div>
  </div>

  <!-- Main Premium Header / Navbar -->
  <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 transition-shadow duration-300 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20">
        
        <!-- Brand Logo matching reference -->
        <a href="/" class="flex items-center gap-3 group focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 rounded-lg">
          <!-- Coconut outline with palm leaves icon -->
          <div class="w-12 h-12 flex items-center justify-center text-brand-600 transition-transform duration-200 group-hover:scale-105">
            <svg class="w-11 h-11" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
              <!-- Coconut body with green gradient -->
              <ellipse cx="30" cy="38" rx="20" ry="18" fill="#22c55e" stroke="#15803d" stroke-width="2.5" />
              <!-- Coconut sliced top opening -->
              <path d="M19 28 C23 22, 37 22, 41 28 C37 32, 23 32, 19 28 Z" fill="#ffffff" stroke="#15803d" stroke-width="2" />
              <!-- Coconut water & inner shell ring -->
              <ellipse cx="30" cy="28" rx="8" ry="3.5" fill="#f0fdf4" stroke="#86efac" stroke-width="1.5" />
              <!-- Palm leaves on top -->
              <path d="M30 22 C30 10, 44 8, 48 10 C46 16, 38 18, 30 22 Z" fill="#16a34a" />
              <path d="M28 22 C22 12, 12 14, 10 18 C16 22, 22 22, 28 22 Z" fill="#15803d" />
              <!-- Eco straw -->
              <line x1="30" y1="28" x2="38" y2="12" stroke="#166534" stroke-width="2.5" stroke-linecap="round" />
            </svg>
          </div>
          <div class="flex flex-col">
            <div class="flex items-baseline gap-1.5">
              <span class="font-extrabold text-2xl text-slate-900 leading-none tracking-tight">N.A</span>
            </div>
            <span class="font-bold text-sm text-slate-900 leading-tight tracking-tight">
              Fresh Fruits & Coconuts
            </span>
            <span class="text-[9px] text-slate-500 font-semibold tracking-widest uppercase mt-0.5">
              FRESH • HEALTHY • NATURAL
            </span>
          </div>
        </a>

        <!-- Desktop Navigation Links -->
        <nav class="hidden lg:flex items-center gap-7 text-sm font-semibold text-slate-700">
          <a href="/" class="hover:text-brand-700 transition-colors">Home</a>
          <a href="/#coconuts" class="hover:text-brand-700 transition-colors flex items-center gap-1.5">
            <span>Coconut Water</span>
            <span class="text-[10px] font-bold uppercase tracking-wider text-brand-600 bg-brand-50 px-1.5 py-0.5 rounded">Hero</span>
          </a>
          <a href="/#juices" class="hover:text-brand-700 transition-colors">Fresh Juices</a>
          <a href="/products.php" class="hover:text-brand-700 transition-colors">Full Menu</a>
          <a href="/#why-us" class="hover:text-brand-700 transition-colors">Why Choose Us</a>
          <a href="/#delivery-areas" class="hover:text-brand-700 transition-colors">Delivery in Jaora</a>
        </nav>

        <!-- Right Action Items (Search, Zomato, Cart, Hamburger) -->
        <div class="flex items-center gap-3 sm:gap-4">
          
          <!-- Search Icon Button -->
          <a href="/products.php" 
             class="p-2 text-slate-700 hover:text-brand-700 hover:bg-slate-100 rounded-full transition-colors"
             aria-label="Search drinks and coconuts">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </a>

          <!-- Zomato Button (Admin Controlled) -->
          <?php if ($zomatoEnabled): ?>
          <a href="<?= e($zomatoUrl) ?>" target="_blank" rel="noopener noreferrer" 
             class="hidden md:flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 text-red-600 border border-red-200/80 text-xs font-bold hover:bg-red-100 transition-colors"
             title="Order via Zomato">
            <span class="w-2 h-2 rounded-full bg-red-500"></span>
            <span>Zomato</span>
          </a>
          <?php endif; ?>

          <!-- User Account / Login -->
          <?php if ($user): ?>
            <a href="/account.php" class="hidden sm:flex items-center gap-2 p-1.5 rounded-xl text-slate-700 hover:text-brand-800 hover:bg-slate-100 transition-colors text-xs font-semibold" title="My Account">
              <div class="w-7 h-7 rounded-full bg-brand-100 text-brand-800 flex items-center justify-center font-bold text-xs">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
              </div>
              <span class="max-w-[80px] truncate"><?= e(explode(' ', $user['name'])[0]) ?></span>
            </a>
          <?php endif; ?>

          <!-- Shopping Cart Trigger Button (Matching reference icon & pill badge) -->
          <button @click="openCart()" 
                  type="button"
                  class="relative p-2 text-slate-800 hover:text-brand-700 transition-all focus:outline-none"
                  aria-label="View Shopping Cart">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span x-text="cartCount" 
                  class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1 rounded-full bg-[#064e3b] text-white text-[11px] font-bold flex items-center justify-center shadow"
                  :class="{'animate-bounce': cartUpdated}">
              <?= $cartCount ?>
            </span>
          </button>

          <!-- Mobile Menu Hamburger Button -->
          <button @click="mobileMenuOpen = !mobileMenuOpen" 
                  type="button" 
                  class="p-2 text-slate-800 hover:bg-slate-100 rounded-xl focus:outline-none"
                  aria-label="Toggle navigation">
            <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

      </div>
    </div>

    <!-- Mobile Slide Down Menu (Alpine.js) -->
    <div x-show="mobileMenuOpen" 
         x-cloak
         @click.away="mobileMenuOpen = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="lg:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-6 shadow-xl">
      <nav class="flex flex-col space-y-2 text-base font-semibold text-slate-800">
        <a href="/" @click="mobileMenuOpen = false" class="px-3 py-2 rounded-lg hover:bg-brand-50 hover:text-brand-800">Home</a>
        <a href="/#coconuts" @click="mobileMenuOpen = false" class="px-3 py-2 rounded-lg hover:bg-brand-50 hover:text-brand-800 flex items-center justify-between">
          <span>Coconut Water</span>
          <span class="text-xs bg-brand-100 text-brand-800 px-2 py-0.5 rounded font-bold">Cold Cut</span>
        </a>
        <a href="/#juices" @click="mobileMenuOpen = false" class="px-3 py-2 rounded-lg hover:bg-brand-50 hover:text-brand-800">Fresh Juices</a>
        <a href="/products.php" @click="mobileMenuOpen = false" class="px-3 py-2 rounded-lg hover:bg-brand-50 hover:text-brand-800">Full Menu</a>
        <a href="/#why-us" @click="mobileMenuOpen = false" class="px-3 py-2 rounded-lg hover:bg-brand-50 hover:text-brand-800">Why Choose Us</a>
        <a href="/#delivery-areas" @click="mobileMenuOpen = false" class="px-3 py-2 rounded-lg hover:bg-brand-50 hover:text-brand-800">Delivery Areas</a>
        <a href="/#reviews" @click="mobileMenuOpen = false" class="px-3 py-2 rounded-lg hover:bg-brand-50 hover:text-brand-800">Customer Reviews</a>
        
        <div class="pt-3 border-t border-slate-200 flex flex-col gap-2">
          <?php if ($user): ?>
            <a href="/account.php" class="px-3 py-2 text-brand-800 bg-brand-50 rounded-lg flex items-center justify-between">
              <span>My Account (<?= e($user['name']) ?>)</span>
              <span class="text-xs font-bold text-brand-700">Orders & Addresses →</span>
            </a>
            <a href="/logout.php" class="px-3 py-2 text-red-600 rounded-lg text-sm">Sign Out</a>
          <?php else: ?>
            <div class="grid grid-cols-2 gap-2 pt-1">
              <a href="/login.php" class="text-center py-2.5 px-4 rounded-xl border border-slate-300 font-semibold text-slate-700 text-sm">Sign In</a>
              <a href="/register.php" class="text-center py-2.5 px-4 rounded-xl bg-brand-700 text-white font-semibold text-sm">Register</a>
            </div>
          <?php endif; ?>

          <?php if ($zomatoEnabled): ?>
          <a href="<?= e($zomatoUrl) ?>" target="_blank" rel="noopener noreferrer" class="mt-2 text-center py-2.5 px-4 rounded-xl bg-red-600 text-white font-bold text-sm flex items-center justify-center gap-2">
            <span>Order on Zomato</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
          </a>
          <?php endif; ?>
        </div>
      </nav>
    </div>
  </header>

  <!-- Slide-Over Shopping Cart Drawer (Alpine.js) -->
  <div x-show="cartDrawerOpen" 
       x-cloak
       class="fixed inset-0 z-50 overflow-hidden" 
       aria-labelledby="slide-over-title" 
       role="dialog" 
       aria-modal="true">
    <div class="absolute inset-0 overflow-hidden">
      <!-- Backdrop -->
      <div x-show="cartDrawerOpen"
           x-transition:enter="ease-in-out duration-300"
           x-transition:enter-start="opacity-0"
           x-transition:enter-end="opacity-100"
           x-transition:leave="ease-in-out duration-300"
           x-transition:leave-start="opacity-100"
           x-transition:leave-end="opacity-0"
           @click="cartDrawerOpen = false"
           class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

      <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
        <div x-show="cartDrawerOpen"
             x-transition:enter="transform transition ease-in-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in-out duration-300"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="pointer-events-auto w-screen max-w-md bg-white shadow-2xl flex flex-col">
          
          <!-- Drawer Header -->
          <div class="px-6 py-5 bg-brand-900 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
              <svg class="w-6 h-6 text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
              <h2 class="font-serif font-bold text-lg text-white" id="slide-over-title">Your Fresh Cart</h2>
            </div>
            <button @click="cartDrawerOpen = false" type="button" class="text-brand-200 hover:text-white p-1 rounded-lg">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <!-- Drawer Body (Items List) -->
          <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
            <template x-if="cartItems.length === 0">
              <div class="text-center py-12 flex flex-col items-center justify-center text-slate-500">
                <div class="w-16 h-16 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mb-3">
                  <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <p class="font-bold text-slate-800 text-base">Your cart is empty</p>
                <p class="text-xs text-slate-500 mt-1 max-w-[220px]">Explore our fresh tender coconuts and 100% pure extracted juices.</p>
                <button @click="cartDrawerOpen = false" class="mt-4 px-4 py-2 bg-brand-700 text-white rounded-xl text-xs font-semibold hover:bg-brand-800">
                  Browse Drinks
                </button>
              </div>
            </template>

            <template x-for="item in cartItems" :key="item.item_id">
              <div class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100 hover:border-slate-200 transition-colors">
                <img :src="item.image_url" :alt="item.name" class="w-16 h-16 rounded-xl object-cover bg-slate-200 shrink-0">
                <div class="flex-1 min-w-0">
                  <h4 class="font-bold text-sm text-slate-900 truncate" x-text="item.name"></h4>
                  <div class="text-xs text-slate-500" x-text="item.unit_size"></div>
                  <div class="font-bold text-brand-700 text-sm mt-1" x-text="'₹' + item.unit_price"></div>
                </div>
                <div class="flex flex-col items-end gap-2">
                  <!-- Quantity Stepper -->
                  <div class="flex items-center border border-slate-200 rounded-lg bg-white overflow-hidden shadow-xs">
                    <button @click="updateCartQty(item.product_id, item.quantity - 1)" 
                            type="button" 
                            class="w-7 h-7 flex items-center justify-center text-slate-600 hover:bg-slate-100">
                      -
                    </button>
                    <span class="w-8 text-center text-xs font-bold text-slate-800" x-text="item.quantity"></span>
                    <button @click="updateCartQty(item.product_id, item.quantity + 1)" 
                            type="button" 
                            class="w-7 h-7 flex items-center justify-center text-slate-600 hover:bg-slate-100">
                      +
                    </button>
                  </div>
                  <button @click="removeFromCart(item.product_id)" 
                          type="button" 
                          class="text-[11px] text-red-500 hover:text-red-700 hover:underline">
                    Remove
                  </button>
                </div>
              </div>
            </template>
          </div>

          <!-- Drawer Footer (Subtotal & Checkout CTA) -->
          <div x-show="cartItems.length > 0" class="border-t border-slate-200 px-6 py-4 bg-slate-50 space-y-3">
            <div class="flex items-center justify-between text-sm text-slate-600">
              <span>Item Subtotal</span>
              <span class="font-bold text-slate-900 text-base" x-text="'₹' + cartSubtotal"></span>
            </div>
            <p class="text-[11px] text-slate-500">Delivery charges calculated at checkout based on your Jaora location.</p>
            <div class="grid grid-cols-2 gap-2 pt-1">
              <a href="/cart.php" class="text-center py-3 px-4 rounded-xl border border-slate-300 font-bold text-slate-700 text-sm hover:bg-white transition-colors">
                View Cart
              </a>
              <a href="/checkout.php" class="text-center py-3 px-4 rounded-xl bg-brand-700 hover:bg-brand-800 text-white font-bold text-sm shadow transition-colors flex items-center justify-center gap-1.5">
                <span>Checkout</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
              </a>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- Toast Notification Floating Container -->
  <div class="fixed top-5 right-5 z-50 space-y-2 pointer-events-none max-w-sm w-full px-4">
    <template x-for="toast in toasts" :key="toast.id">
      <div x-show="toast.visible"
           x-transition:enter="transition ease-out duration-300"
           x-transition:enter-start="opacity-0 translate-y-2 scale-95"
           x-transition:enter-end="opacity-100 translate-y-0 scale-100"
           x-transition:leave="transition ease-in duration-200"
           x-transition:leave-start="opacity-100"
           x-transition:leave-end="opacity-0 scale-95"
           class="pointer-events-auto p-4 rounded-2xl shadow-xl flex items-center gap-3 border text-sm"
           :class="toast.type === 'error' ? 'bg-red-900 text-white border-red-700' : 'bg-brand-900 text-white border-brand-700'">
        <svg x-show="toast.type !== 'error'" class="w-5 h-5 text-brand-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <svg x-show="toast.type === 'error'" class="w-5 h-5 text-red-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        <div class="flex-1 font-medium" x-text="toast.message"></div>
      </div>
    </template>
  </div>
