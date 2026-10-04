<?php
/**
 * N.A Fresh Fruits & Coconuts - Footer Component
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
  <!-- Footer -->
  <footer class="bg-brand-950 text-brand-100/90 pt-16 pb-24 md:pb-12 border-t border-brand-900 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Top Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 pb-12 border-b border-brand-900/60">
        
        <!-- Brand Info -->
        <div class="space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-brand-800 text-white flex items-center justify-center font-bold shadow-md">
              <svg class="w-5 h-5 text-brand-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 0 0 9-9c0-4.97-4.03-9-9-9S3 7.03 3 12a9 9 0 0 0 9 9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v9l5 5"/>
              </svg>
            </div>
            <span class="font-serif font-bold text-xl text-white">N.A Fresh Fruits</span>
          </div>
          <p class="text-xs text-brand-200/80 leading-relaxed">
            Jaora's premier destination for pure raw tender coconut water and 100% natural, cold-pressed juices. Prepared fresh to order with zero added sugar or artificial preservatives.
          </p>
          <div class="flex items-center gap-3 pt-2">
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $bizWhatsApp) ?>?text=Hello%20NA%20Fresh%20Fruits,%20I%20would%20like%20to%20order" 
               target="_blank" 
               class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center gap-2 shadow-sm transition-colors">
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.962 1.199.662.589 1.221.771 1.394.858.173.086.274.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z"/></svg>
              <span>WhatsApp Order</span>
            </a>
            <?php if ($zomatoEnabled): ?>
            <a href="<?= e($zomatoUrl) ?>" target="_blank" class="px-3.5 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors">
              <span>Zomato</span>
            </a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Quick Links -->
        <div>
          <h4 class="font-serif font-bold text-base text-white mb-4">Quick Navigation</h4>
          <ul class="space-y-2.5 text-xs text-brand-200">
            <li><a href="/" class="hover:text-white transition-colors">Home Page</a></li>
            <li><a href="/#coconuts" class="hover:text-white transition-colors">Tender Coconut Water (Fresh Cut)</a></li>
            <li><a href="/#juices" class="hover:text-white transition-colors">Freshly Squeezed Juices</a></li>
            <li><a href="/products.php" class="hover:text-white transition-colors">Browse Full Menu</a></li>
            <li><a href="/#why-us" class="hover:text-white transition-colors">Hygiene & Cold-Press Promise</a></li>
            <li><a href="/#delivery-areas" class="hover:text-white transition-colors">Jaora Local Delivery Coverage</a></li>
            <li><a href="/installer.php" class="hover:text-white transition-colors text-brand-400">Database Installer</a></li>
            <li><a href="/admin/login.php" class="hover:text-white transition-colors text-brand-400">Staff & Admin Portal</a></li>
          </ul>
        </div>

        <!-- Store & Hours -->
        <div>
          <h4 class="font-serif font-bold text-base text-white mb-4">Store Location</h4>
          <address class="not-italic text-xs text-brand-200 space-y-2 leading-relaxed">
            <p class="font-medium text-white"><?= e($bizAddress) ?></p>
            <p>Jaora, Dist. Ratlam, Madhya Pradesh 457226</p>
            <div class="pt-2">
              <span class="block text-brand-400 font-bold uppercase tracking-wider text-[10px]">Operating Hours</span>
              <p class="text-white font-medium"><?= e($opHours) ?></p>
            </div>
            <div class="pt-2 flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
              <span class="text-emerald-300 font-semibold">Store is Open for Online Orders</span>
            </div>
          </address>
        </div>

        <!-- Trust & Payment -->
        <div>
          <h4 class="font-serif font-bold text-base text-white mb-4">Payment & Guarantee</h4>
          <div class="space-y-3 text-xs text-brand-200">
            <p>We support Cash on Delivery (COD) and 100% verified secure online payments via Razorpay (UPI, Google Pay, PhonePe, Paytm, Cards & Netbanking).</p>
            
            <div class="flex flex-wrap gap-2 pt-1">
              <span class="px-2.5 py-1 rounded bg-brand-900 text-white font-bold text-[10px] border border-brand-800">Cash on Delivery</span>
              <span class="px-2.5 py-1 rounded bg-brand-900 text-white font-bold text-[10px] border border-brand-800">UPI / QR</span>
              <span class="px-2.5 py-1 rounded bg-brand-900 text-white font-bold text-[10px] border border-brand-800">Razorpay Verified</span>
              <span class="px-2.5 py-1 rounded bg-brand-900 text-white font-bold text-[10px] border border-brand-800">Zero Added Sugar</span>
            </div>

            <div class="pt-3 border-t border-brand-900/60 text-[11px] text-brand-300">
              Need bulk order for weddings, tekri gatherings or parties in Jaora? Call us directly at <span class="text-white font-bold"><?= e($bizPhone) ?></span>.
            </div>
          </div>
        </div>

      </div>

      <!-- Bottom Bar -->
      <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-brand-400">
        <p>© <?= date('Y') ?> <?= e($bizName) ?>. All rights reserved. Jaora, Madhya Pradesh, India.</p>
        <div class="flex items-center gap-6">
          <span>100% Raw • Fresh Extracted • Local Jaora Delivery</span>
          <a href="/admin/login.php" class="text-brand-500 hover:text-white transition-colors">Admin Login</a>
        </div>
      </div>

    </div>
  </footer>

  <!-- Mobile Bottom Sticky Navigation Bar (App Experience) -->
  <div class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 px-2 py-2 shadow-lg">
    <div class="grid grid-cols-5 items-center text-center">
      
      <!-- Home -->
      <a href="/" class="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-brand-700 transition-colors">
        <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        <span class="text-[10px] font-bold">Home</span>
      </a>

      <!-- Menu / Juices -->
      <a href="/products.php" class="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-brand-700 transition-colors">
        <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        <span class="text-[10px] font-bold">Menu</span>
      </a>

      <!-- Cart Button (With Badge) -->
      <button @click="openCart()" type="button" class="flex flex-col items-center justify-center py-1 text-brand-700 relative">
        <div class="relative">
          <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          <span x-text="cartCount" class="absolute -top-1.5 -right-2 min-w-[18px] h-[18px] px-1 bg-brand-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow">
            <?= $cartCount ?>
          </span>
        </div>
        <span class="text-[10px] font-bold">Cart</span>
      </button>

      <!-- Orders -->
      <a href="/account.php#orders" class="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-brand-700 transition-colors">
        <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
        <span class="text-[10px] font-bold">Orders</span>
      </a>

      <!-- Account / Profile -->
      <a href="<?= $user ? '/account.php' : '/login.php' ?>" class="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-brand-700 transition-colors">
        <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
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
          // Listen for push notifications permission if enabled
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
          if (Notification.permission === 'default') {
            // Can request on user interaction
          }
        }
      };
    }

    // GSAP Subtle Entrance Animations
    document.addEventListener('DOMContentLoaded', () => {
      if (typeof gsap !== 'undefined') {
        gsap.from('.hero-content', {
          y: 20,
          opacity: 0,
          duration: 0.8,
          ease: 'power2.out',
          stagger: 0.15
        });
      }
    });
  </script>
</body>
</html>
