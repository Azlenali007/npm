<?php
/**
 * N.A Fresh Fruits & Coconuts - Dedicated Cart Page
 */

require_once __DIR__ . '/config/database.php';

$db = getDB();
$cartData = get_cart_data();

// Fetch Active Delivery Areas from MySQL
$stmt = $db->query("SELECT * FROM delivery_areas WHERE is_active = 1 ORDER BY delivery_charge ASC");
$deliveryAreas = $stmt->fetchAll();

// Default Delivery Area
$defaultArea = $deliveryAreas[0] ?? ['id' => 1, 'delivery_charge' => 20, 'area_name' => 'Station Road'];

$appliedCoupon = $_SESSION['applied_coupon'] ?? null;
$discount = $appliedCoupon['discount'] ?? 0;

$pageTitle = 'Your Shopping Cart – N.A Fresh Fruits & Coconuts, Jaora';
$pageDesc = 'Review your tender coconut and fresh juice order with doorstep delivery in Jaora.';

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-coconut-cream min-h-screen py-10" 
     x-data="cartPage(<?= json_encode($deliveryAreas) ?>, <?= (float)$discount ?>)">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="mb-8">
      <h1 class="font-serif font-bold text-3xl sm:text-4xl text-brand-950 tracking-tight">
        Your Fresh Drinks Cart
      </h1>
      <p class="text-xs text-slate-500 mt-1">Review your fresh coconut water and natural juices before checkout.</p>
    </div>

    <template x-if="cartItems.length === 0">
      <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/80 shadow-xs max-w-xl mx-auto">
        <div class="w-16 h-16 rounded-full bg-brand-50 text-brand-700 flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
          🥥
        </div>
        <h2 class="font-serif font-bold text-xl text-slate-900">Your Cart is Empty</h2>
        <p class="text-xs text-slate-500 mt-2 max-w-sm mx-auto leading-relaxed">
          You haven't added any tender coconuts or freshly pressed juices yet. Explore our fresh daily items in Jaora!
        </p>
        <a href="/products.php" class="inline-block mt-6 px-6 py-3 bg-brand-700 hover:bg-brand-800 text-white rounded-2xl text-xs font-bold shadow transition-colors">
          Browse Fresh Drinks
        </a>
      </div>
    </template>

    <template x-if="cartItems.length > 0">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Cart Items List (Left Column) -->
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-4">
          <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <span class="font-bold text-sm text-slate-800">Item Details</span>
            <span class="text-xs text-slate-500" x-text="cartCount + ' item(s)'"></span>
          </div>

          <!-- Items -->
          <div class="divide-y divide-slate-100">
            <template x-for="item in cartItems" :key="item.item_id">
              <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                  <img :src="item.image_url" :alt="item.name" class="w-20 h-20 rounded-2xl object-cover bg-slate-100 shrink-0">
                  <div>
                    <h3 class="font-serif font-bold text-base text-slate-900" x-text="item.name"></h3>
                    <div class="text-xs text-slate-500" x-text="item.unit_size"></div>
                    <div class="font-bold text-brand-800 text-sm mt-1" x-text="'₹' + item.unit_price"></div>
                  </div>
                </div>

                <div class="flex items-center justify-between sm:justify-end gap-6 pt-2 sm:pt-0">
                  <!-- Stepper -->
                  <div class="flex items-center border border-slate-200 rounded-xl bg-slate-50 overflow-hidden shadow-2xs">
                    <button @click="updateCartQty(item.product_id, item.quantity - 1)" 
                            type="button" 
                            class="w-8 h-8 flex items-center justify-center text-slate-600 hover:bg-slate-200 font-bold transition-colors">
                      -
                    </button>
                    <span class="w-8 text-center text-xs font-bold text-slate-800" x-text="item.quantity"></span>
                    <button @click="updateCartQty(item.product_id, item.quantity + 1)" 
                            type="button" 
                            class="w-8 h-8 flex items-center justify-center text-slate-600 hover:bg-slate-200 font-bold transition-colors">
                      +
                    </button>
                  </div>

                  <!-- Subtotal for item -->
                  <div class="font-bold text-slate-900 text-sm w-20 text-right" x-text="'₹' + (item.unit_price * item.quantity)"></div>

                  <!-- Remove -->
                  <button @click="removeFromCart(item.product_id)" 
                          type="button" 
                          class="p-2 text-slate-400 hover:text-red-600 transition-colors"
                          title="Remove item">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </div>
              </div>
            </template>
          </div>

          <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
            <a href="/products.php" class="text-xs font-bold text-brand-700 hover:text-brand-800 flex items-center gap-1.5">
              <span>← Add More Drinks</span>
            </a>
          </div>
        </div>

        <!-- Order Summary & Delivery Area (Right Column) -->
        <div class="lg:col-span-4 space-y-6">
          
          <!-- Summary Card -->
          <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-5">
            <h2 class="font-serif font-bold text-xl text-slate-900">Order Summary</h2>

            <!-- Delivery Area Selector -->
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                Jaora Delivery Area
              </label>
              <select x-model="selectedAreaId" 
                      @change="onAreaChange()" 
                      class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none bg-slate-50">
                <template x-for="area in deliveryAreas" :key="area.id">
                  <option :value="area.id" x-text="area.area_name + ' (₹' + area.delivery_charge + ')'"></option>
                </template>
              </select>
              <div class="text-[11px] text-slate-500 mt-1" x-text="'Est. Transit: ' + currentAreaEstTime"></div>
            </div>

            <!-- Coupon Code Section -->
            <div class="pt-3 border-t border-slate-100">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                Discount Coupon
              </label>
              <div class="flex items-center gap-2">
                <input type="text" 
                       x-model="couponCode" 
                       placeholder="e.g. FRESH10, JAORA50" 
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs uppercase focus:ring-2 focus:ring-brand-500 focus:outline-none">
                <button @click="applyCoupon()" 
                        type="button" 
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-colors">
                  Apply
                </button>
              </div>
              <p x-show="couponMsg" x-text="couponMsg" :class="couponError ? 'text-red-600' : 'text-emerald-700'" class="text-[11px] font-bold mt-1.5"></p>
            </div>

            <!-- Price Breakdown -->
            <div class="pt-4 border-t border-slate-100 space-y-2.5 text-xs text-slate-600">
              <div class="flex items-center justify-between">
                <span>Items Subtotal</span>
                <span class="font-bold text-slate-900" x-text="'₹' + cartSubtotal"></span>
              </div>
              <div class="flex items-center justify-between">
                <span>Jaora Local Delivery</span>
                <span class="font-bold text-slate-900" x-text="'₹' + currentDeliveryCharge"></span>
              </div>
              <div x-show="discountAmount > 0" class="flex items-center justify-between text-emerald-700 font-bold">
                <span>Coupon Discount</span>
                <span x-text="'- ₹' + discountAmount"></span>
              </div>
              <div class="pt-3 border-t border-slate-200 flex items-baseline justify-between text-base font-bold text-slate-900">
                <span class="font-serif">Grand Total</span>
                <span class="font-serif text-2xl text-brand-900" x-text="'₹' + grandTotal"></span>
              </div>
            </div>

            <!-- Checkout CTA -->
            <button @click="goToCheckout()" 
                    type="button" 
                    class="w-full py-4 rounded-2xl bg-brand-700 hover:bg-brand-800 text-white font-bold text-sm shadow-md transition-colors flex items-center justify-center gap-2">
              <span>Proceed to Checkout</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>

            <!-- Small Guarantee -->
            <p class="text-[11px] text-slate-400 text-center">
              Prepared fresh upon order confirmation. Cash on Delivery or Secure UPI/Razorpay available.
            </p>

          </div>

        </div>

      </div>
    </template>

  </div>
</div>

<script>
  function cartPage(areas, initialDiscount) {
    return {
      deliveryAreas: areas,
      selectedAreaId: areas.length ? areas[0].id : 1,
      currentDeliveryCharge: areas.length ? parseFloat(areas[0].delivery_charge) : 20,
      currentAreaEstTime: areas.length ? areas[0].est_delivery_time : '20-30 mins',
      couponCode: '',
      couponMsg: '',
      couponError: false,
      discountAmount: initialDiscount || 0,

      init() {
        this.onAreaChange();
      },

      onAreaChange() {
        const area = this.deliveryAreas.find(a => a.id == this.selectedAreaId);
        if (area) {
          this.currentDeliveryCharge = parseFloat(area.delivery_charge);
          this.currentAreaEstTime = area.est_delivery_time;
        }
      },

      get grandTotal() {
        const total = (this.cartSubtotal + this.currentDeliveryCharge) - this.discountAmount;
        return Math.max(0, total);
      },

      async applyCoupon() {
        if (!this.couponCode.trim()) return;
        try {
          const res = await fetch('/api/cart.php?action=apply_coupon', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code: this.couponCode })
          });
          const data = await res.json();
          if (data.success) {
            this.discountAmount = data.discount;
            this.couponMsg = data.message;
            this.couponError = false;
          } else {
            this.couponMsg = data.message;
            this.couponError = true;
          }
        } catch (e) {
          this.couponMsg = 'Error applying coupon';
          this.couponError = true;
        }
      },

      goToCheckout() {
        window.location.href = '/checkout.php?area_id=' + this.selectedAreaId;
      }
    };
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
