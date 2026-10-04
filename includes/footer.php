<?php
/**
 * N.A Fresh Fruits & Coconuts - Completely Modern Premium Redesigned Footer
 * Jaora, Madhya Pradesh, India
 */

$bizName = get_setting('business_name', 'N.A Fresh Fruits & Coconuts');
$bizPhone = get_setting('phone', '+91 98260 12345');
$bizWhatsApp = get_setting('whatsapp', '919826012345');
$bizAddress = get_setting('address', 'Shop No. 4, Station Road, Opp. Municipal Garden, Jaora, MP 457226');
$opHours = get_setting('operating_hours', '7:00 AM – 10:30 PM Daily');
$zomatoEnabled = (int)get_setting('zomato_enabled', 1);
$zomatoUrl = get_setting('zomato_url', 'https://www.zomato.com');
?>
  <!-- ========================================================================= -->
  <!-- MODERN PREMIUM REDESIGNED FOOTER                                          -->
  <!-- ========================================================================= -->
  <footer class="relative bg-[#052014] text-slate-300 pt-16 sm:pt-20 pb-28 md:pb-14 border-t border-emerald-900/60 mt-auto overflow-hidden">
    
    <!-- Subtle Botanical Ambient Glow at Top Center -->
    <div class="pointer-events-none absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-4xl h-48 bg-emerald-500/10 blur-[100px]"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Top Callout Banner: "Direct Fresh Harvest & Instant Jaora Delivery" -->
      <div class="mb-14 sm:mb-16 p-6 sm:p-8 lg:p-10 rounded-3xl bg-emerald-950/60 border border-emerald-800/60 backdrop-blur-md shadow-2xl flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="space-y-1.5 max-w-xl">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-900/80 border border-emerald-700/60 text-emerald-300 text-[11px] font-bold tracking-wider uppercase">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>Jaora Doorstep Delivery Active</span>
          </div>
          <h3 class="font-serif font-bold text-2xl sm:text-3xl text-white tracking-tight">
            Craving fresh coconut water or cold-pressed juice?
          </h3>
          <p class="text-xs sm:text-sm text-emerald-200/80 leading-relaxed font-normal">
            Order online in seconds or message us directly on WhatsApp. Prepared fresh upon order and delivered chilled across Jaora.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-3 shrink-0">
          <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $bizWhatsApp) ?>?text=Hello%20NA%20Fresh%20Fruits,%20I%20would%20like%20to%20order%20tender%20coconuts" 
             target="_blank" 
             class="px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm flex items-center gap-2.5 shadow-lg shadow-emerald-900/30 transition-all hover:scale-105 active:scale-95">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.962 1.199.662.589 1.221.771 1.394.858.173.086.274.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z"/></svg>
            <span>WhatsApp Order</span>
          </a>

          <a href="tel:<?= preg_replace('/[^0-9+]/', '', $bizPhone) ?>" 
             class="px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/15 text-white font-semibold text-xs sm:text-sm border border-white/20 backdrop-blur-xs flex items-center gap-2 transition-all hover:scale-105 active:scale-95">
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            <span>Call <?= e($bizPhone) ?></span>
          </a>

          <?php if ($zomatoEnabled): ?>
          <a href="<?= e($zomatoUrl) ?>" target="_blank" rel="noopener noreferrer" 
             class="px-4 py-3 rounded-2xl bg-red-600/90 hover:bg-red-600 text-white font-bold text-xs sm:text-sm flex items-center gap-1.5 shadow-sm transition-all hover:scale-105 active:scale-95">
            <span class="w-2 h-2 rounded-full bg-white"></span>
            <span>Zomato</span>
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Main Responsive 4-Column Footer Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8 pb-14 border-b border-emerald-900/60">
        
        <!-- Column 1: Brand & Ethos (lg:col-span-4) -->
        <div class="lg:col-span-4 space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-emerald-900/80 border border-emerald-700/60 flex items-center justify-center text-emerald-400 shadow-md">
              <svg class="w-8 h-8" viewBox="0 0 64 64" fill="none">
                <ellipse cx="30" cy="38" rx="20" ry="18" fill="#22c55e" stroke="#15803d" stroke-width="2.5" />
                <path d="M19 28 C23 22, 37 22, 41 28 C37 32, 23 32, 19 28 Z" fill="#ffffff" stroke="#15803d" stroke-width="2" />
                <ellipse cx="30" cy="28" rx="8" ry="3.5" fill="#f0fdf4" stroke="#86efac" stroke-width="1.5" />
                <path d="M30 22 C30 10, 44 8, 48 10 C46 16, 38 18, 30 22 Z" fill="#16a34a" />
                <line x1="30" y1="28" x2="38" y2="12" stroke="#166534" stroke-width="2.5" stroke-linecap="round" />
              </svg>
            </div>
            <div class="flex flex-col">
              <span class="font-extrabold text-2xl text-white leading-none tracking-tight">N.A</span>
              <span class="font-bold text-sm text-emerald-300 leading-tight">Fresh Fruits & Coconuts</span>
              <span class="text-[9px] text-emerald-400/80 font-bold tracking-widest uppercase mt-0.5">FRESH • HEALTHY • NATURAL</span>
            </div>
          </div>

          <p class="text-xs sm:text-sm text-emerald-200/80 leading-relaxed font-normal">
            Jaora's dedicated hub for pure raw tender coconut water and cold-pressed fresh juices. Cut, pressed, and bottled fresh to order with zero added sugar, zero water dilution, and zero artificial preservatives.
          </p>

          <div class="pt-2 flex flex-wrap gap-2 text-[10px] font-bold text-emerald-300">
            <span class="px-3 py-1 rounded-full bg-emerald-900/60 border border-emerald-800">🍃 100% Pure & Raw</span>
            <span class="px-3 py-1 rounded-full bg-emerald-900/60 border border-emerald-800">🥥 Cut on Order</span>
            <span class="px-3 py-1 rounded-full bg-emerald-900/60 border border-emerald-800">⚡ Local Jaora Delivery</span>
          </div>
        </div>

        <!-- Column 2: Signature Categories (lg:col-span-3) -->
        <div class="lg:col-span-3 space-y-4">
          <h4 class="font-serif font-bold text-base text-white tracking-wide flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            <span>Our Fresh Specialties</span>
          </h4>
          <ul class="space-y-2.5 text-xs text-slate-300">
            <li>
              <a href="/#coconuts" class="hover:text-emerald-400 transition-colors flex items-center justify-between group">
                <span>Fresh Green Tender Coconut</span>
                <span class="text-[10px] text-emerald-400 font-bold opacity-0 group-hover:opacity-100 transition-opacity">₹60</span>
              </a>
            </li>
            <li>
              <a href="/#coconuts" class="hover:text-emerald-400 transition-colors flex items-center justify-between group">
                <span>Jumbo Pure Coconut Water Bottle</span>
                <span class="text-[10px] text-emerald-400 font-bold opacity-0 group-hover:opacity-100 transition-opacity">₹80</span>
              </a>
            </li>
            <li>
              <a href="/#coconuts" class="hover:text-emerald-400 transition-colors flex items-center justify-between group">
                <span>Tender Malai Scoop Bowl</span>
                <span class="text-[10px] text-emerald-400 font-bold opacity-0 group-hover:opacity-100 transition-opacity">₹95</span>
              </a>
            </li>
            <li>
              <a href="/#juices" class="hover:text-amber-400 transition-colors flex items-center justify-between group">
                <span>Pure Valencia Orange Juice</span>
                <span class="text-[10px] text-amber-400 font-bold opacity-0 group-hover:opacity-100 transition-opacity">₹60</span>
              </a>
            </li>
            <li>
              <a href="/#juices" class="hover:text-amber-400 transition-colors flex items-center justify-between group">
                <span>Watermelon Hydration Cooler</span>
                <span class="text-[10px] text-amber-400 font-bold opacity-0 group-hover:opacity-100 transition-opacity">₹50</span>
              </a>
            </li>
            <li>
              <a href="/#juices" class="hover:text-amber-400 transition-colors flex items-center justify-between group">
                <span>Fresh Mosambi with Roasted Cumin</span>
                <span class="text-[10px] text-amber-400 font-bold opacity-0 group-hover:opacity-100 transition-opacity">₹60</span>
              </a>
            </li>
            <li>
              <a href="/#juices" class="hover:text-amber-400 transition-colors flex items-center justify-between group">
                <span>Royal Alphonso Mango Nectar</span>
                <span class="text-[10px] text-amber-400 font-bold opacity-0 group-hover:opacity-100 transition-opacity">₹80</span>
              </a>
            </li>
          </ul>
        </div>

        <!-- Column 3: Quick Navigation (lg:col-span-2) -->
        <div class="lg:col-span-2 space-y-4">
          <h4 class="font-serif font-bold text-base text-white tracking-wide flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            <span>Quick Links</span>
          </h4>
          <ul class="space-y-2.5 text-xs text-slate-300">
            <li><a href="/" class="hover:text-emerald-400 transition-colors">Home Page</a></li>
            <li><a href="/products.php" class="hover:text-emerald-400 transition-colors font-medium">Browse Full Menu</a></li>
            <li><a href="/#why-us" class="hover:text-emerald-400 transition-colors">Why Choose Us</a></li>
            <li><a href="/#delivery-areas" class="hover:text-emerald-400 transition-colors">Jaora Delivery Zones</a></li>
            <li><a href="/cart.php" class="hover:text-emerald-400 transition-colors">Shopping Cart</a></li>
            <li><a href="/wishlist.php" class="hover:text-emerald-400 transition-colors">Saved Wishlist</a></li>
            <li><a href="/account.php" class="hover:text-emerald-400 transition-colors">Order History & Tracking</a></li>
            <li><a href="/admin/login.php" class="hover:text-emerald-400 transition-colors text-emerald-400/90 font-semibold">Admin Login</a></li>
          </ul>
        </div>

        <!-- Column 4: Jaora Hub & Timings (lg:col-span-3) -->
        <div class="lg:col-span-3 space-y-4">
          <h4 class="font-serif font-bold text-base text-white tracking-wide flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            <span>Store Location & Hours</span>
          </h4>
          
          <div class="p-4 rounded-2xl bg-emerald-950/70 border border-emerald-800/80 space-y-3 text-xs">
            <div class="flex items-start gap-2.5 text-emerald-200">
              <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              <div>
                <p class="font-bold text-white"><?= e($bizAddress) ?></p>
                <p class="text-[11px] text-emerald-300/70">Jaora, Madhya Pradesh 457226</p>
              </div>
            </div>

            <div class="flex items-center gap-2.5 text-emerald-200 pt-2 border-t border-emerald-900/60">
              <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <div>
                <span class="block text-[10px] uppercase font-bold text-emerald-400 tracking-wider">Operating Timings</span>
                <p class="font-semibold text-white"><?= e($opHours) ?></p>
              </div>
            </div>

            <div class="pt-2 border-t border-emerald-900/60">
              <span class="block text-[10px] uppercase font-bold text-emerald-400 tracking-wider mb-1">Local Fast Coverage</span>
              <p class="text-[11px] text-slate-300 leading-snug">Station Road • Azad Chowk • Shastri Colony • Piploda Rd • Housing Board • Hussain Tekri</p>
            </div>
          </div>

          <!-- Accepted Payment Methods Reassurance -->
          <div class="pt-1">
            <span class="block text-[10px] uppercase font-bold text-emerald-400 tracking-wider mb-1.5">Payment Modes</span>
            <div class="flex flex-wrap gap-1.5 text-[10px] font-bold text-slate-300">
              <span class="px-2.5 py-1 rounded-md bg-emerald-950 border border-emerald-800">Cash on Delivery</span>
              <span class="px-2.5 py-1 rounded-md bg-emerald-950 border border-emerald-800">UPI / QR</span>
              <span class="px-2.5 py-1 rounded-md bg-emerald-950 border border-emerald-800">Razorpay Verified</span>
            </div>
          </div>
        </div>

      </div>

      <!-- Bottom Bar: Copyright & Compliance -->
      <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-emerald-400/80">
        <p>© <?= date('Y') ?> <?= e($bizName) ?>. All rights reserved. Station Road, Jaora (M.P.), India.</p>
        
        <div class="flex items-center gap-5">
          <span class="font-script text-base text-emerald-300 select-none">Goodness in Every Sip</span>
          <span class="text-emerald-800">|</span>
          <a href="/products.php" class="hover:text-white transition-colors">Menu</a>
          <span class="text-emerald-800">·</span>
          <a href="/admin/login.php" class="hover:text-white transition-colors">Admin Login</a>
        </div>
      </div>

    </div>
  </footer>

  <!-- ========================================================================= -->
  <!-- MODERN SLEEK MOBILE BOTTOM FLOATING NAVIGATION BAR                       -->
  <!-- ========================================================================= -->
  <div class="md:hidden fixed bottom-3 left-3 right-3 z-40 bg-white/95 backdrop-blur-md border border-slate-200/90 px-3 py-2 rounded-2xl shadow-[0_10px_30px_rgba(0,0,0,0.15)]">
    <div class="grid grid-cols-5 items-center text-center">
      
      <!-- Home -->
      <a href="/" class="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-emerald-800 transition-colors">
        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        <span class="text-[10px] font-bold">Home</span>
      </a>

      <!-- Menu -->
      <a href="/products.php" class="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-emerald-800 transition-colors">
        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        <span class="text-[10px] font-bold">Menu</span>
      </a>

      <!-- Cart (Center Highlight) -->
      <button @click="openCart()" type="button" class="flex flex-col items-center justify-center py-1 text-emerald-800 relative focus:outline-none">
        <div class="relative">
          <svg class="w-6 h-6 mb-0.5 text-emerald-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          <span x-text="cartCount" class="absolute -top-1.5 -right-2 min-w-[18px] h-[18px] px-1 bg-emerald-700 text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow">
            <?= $cartCount ?>
          </span>
        </div>
        <span class="text-[10px] font-bold">Cart</span>
      </button>

      <!-- Orders -->
      <a href="/account.php#orders" class="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-emerald-800 transition-colors">
        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
        <span class="text-[10px] font-bold">Orders</span>
      </a>

      <!-- Account -->
      <a href="<?= $user ? '/account.php' : '/login.php' ?>" class="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-emerald-800 transition-colors">
        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        <span class="text-[10px] font-bold"><?= $user ? 'Account' : 'Login' ?></span>
      </a>

    </div>
  </div>

  <!-- Alpine.js Global Application State & Methods -->
  <script>
    function globalApp() {
      return {
        mobileMenuOpen: false,
        cartDrawerOpen: false,
        cartItems: <?= json_encode($cartData['items']) ?>,
        cartCount: <?= (int)$cartCount ?>,
        cartSubtotal: <?= (float)$cartData['subtotal'] ?>,
        cartUpdated: false,
        toasts: [],

        init() {
          this.initPushNotifications();
        },

        openCart() {
          this.cartDrawerOpen = true;
          this.fetchCart();
        },

        showToast(message, type = 'success') {
          const id = Date.now();
          this.toasts.push({ id, message, type, visible: true });
          setTimeout(() => {
            const t = this.toasts.find(item => item.id === id);
            if (t) t.visible = false;
          }, 3500);
        },

        async fetchCart() {
          try {
            const res = await fetch('/api/cart.php?action=get');
            const data = await res.json();
            if (data.success) {
              this.cartItems = data.items;
              this.cartCount = data.item_count;
              this.cartSubtotal = data.subtotal;
            }
          } catch (e) {
            console.error('Cart fetch error', e);
          }
        },

        async addToCart(productId, qty = 1, showDrawer = false) {
          try {
            const res = await fetch('/api/cart.php?action=add', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ product_id: productId, quantity: qty })
            });
            const data = await res.json();
            if (data.success) {
              this.cartItems = data.items;
              this.cartCount = data.item_count;
              this.cartSubtotal = data.subtotal;
              this.cartUpdated = true;
              setTimeout(() => { this.cartUpdated = false; }, 800);
              this.showToast(data.message || 'Added to fresh cart!');
              if (showDrawer) {
                this.cartDrawerOpen = true;
              }
            } else {
              this.showToast(data.message || 'Could not add to cart', 'error');
            }
          } catch (e) {
            this.showToast('Network error while adding item', 'error');
          }
        },

        async updateCartQty(productId, qty) {
          try {
            const res = await fetch('/api/cart.php?action=update', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ product_id: productId, quantity: qty })
            });
            const data = await res.json();
            if (data.success) {
              this.cartItems = data.items;
              this.cartCount = data.item_count;
              this.cartSubtotal = data.subtotal;
            }
          } catch (e) {
            console.error('Update qty error', e);
          }
        },

        async removeFromCart(productId) {
          try {
            const res = await fetch('/api/cart.php?action=remove', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ product_id: productId })
            });
            const data = await res.json();
            if (data.success) {
              this.cartItems = data.items;
              this.cartCount = data.item_count;
              this.cartSubtotal = data.subtotal;
              this.showToast('Item removed from cart');
            }
          } catch (e) {
            console.error('Remove error', e);
          }
        },

        async buyNow(productId, qty = 1) {
          await this.addToCart(productId, qty, false);
          window.location.href = '/checkout.php';
        },

        async initPushNotifications() {
          if (!('Notification' in window) || !('serviceWorker' in navigator)) return;
        }
      };
    }
  </script>
</body>
</html>
