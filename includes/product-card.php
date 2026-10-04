<?php
/**
 * N.A Fresh Fruits & Coconuts - Reusable Product Card Component
 * $product array expected
 */

$isCoconut = (isset($product['category_slug']) && $product['category_slug'] === 'coconut-water') 
             || (isset($product['category_id']) && $product['category_id'] == 1);
$inStock = (bool)($product['in_stock'] ?? 1);
$discountPct = 0;
if (!empty($product['original_price']) && $product['original_price'] > $product['price']) {
    $discountPct = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
}
?>
<div class="group relative flex flex-col bg-white rounded-3xl border border-slate-200/90 hover:border-brand-300 shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden h-full"
     x-data="{ cardQty: 1 }">
  
  <!-- Image Container (Visually Dominant) -->
  <div class="relative w-full aspect-4/3 sm:aspect-square overflow-hidden bg-slate-100">
    <a href="/product-detail.php?id=<?= $product['id'] ?>" class="block w-full h-full">
      <img src="<?= e($product['image_url']) ?>" 
           alt="<?= e($product['name']) ?>" 
           loading="lazy"
           class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
    </a>

    <!-- Top Floating Elements (Clean unboxed tags) -->
    <div class="absolute top-3 left-3 right-3 flex items-center justify-between pointer-events-none">
      <div class="flex items-center gap-1.5">
        <?php if ($discountPct > 0): ?>
          <span class="px-2.5 py-1 rounded-full bg-amber-500 text-white text-[11px] font-bold shadow-xs">
            Save <?= $discountPct ?>%
          </span>
        <?php endif; ?>
        <?php if (!empty($product['is_bestseller'])): ?>
          <span class="px-2.5 py-1 rounded-full bg-brand-800 text-white text-[11px] font-bold shadow-xs">
            Popular in Jaora
          </span>
        <?php endif; ?>
      </div>

      <!-- Quick Wishlist Button -->
      <button type="button" 
              onclick="event.preventDefault(); window.location.href='/wishlist.php?add=<?= $product['id'] ?>'"
              class="pointer-events-auto p-2 rounded-full bg-white/90 text-slate-600 hover:text-red-500 hover:bg-white shadow transition-colors"
              title="Add to Wishlist">
        <svg class="w-4 h-4 fill-none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
      </button>
    </div>

    <!-- Fresh Preparation Indicator -->
    <div class="absolute bottom-3 left-3 bg-brand-950/80 backdrop-blur-xs text-white text-[10px] font-bold px-2.5 py-1 rounded-lg flex items-center gap-1.5">
      <span class="w-1.5 h-1.5 rounded-full bg-brand-400 animate-pulse"></span>
      <span><?= e($product['unit_size']) ?></span>
    </div>
  </div>

  <!-- Card Body -->
  <div class="p-5 flex-1 flex flex-col justify-between">
    
    <div>
      <!-- Subtle Metadata Kicker -->
      <div class="flex items-center gap-2 text-xs text-slate-500 mb-1.5">
        <span><?= $isCoconut ? 'Fresh Coconut' : 'Cold Pressed' ?></span>
        <span aria-hidden="true">·</span>
        <span class="<?= $inStock ? 'text-emerald-700 font-semibold' : 'text-rose-600 font-semibold' ?>">
          <?= $inStock ? 'Available Today' : 'Sold Out' ?>
        </span>
      </div>

      <!-- Product Title -->
      <h3 class="font-serif font-bold text-lg text-slate-900 group-hover:text-brand-800 transition-colors leading-snug line-clamp-1">
        <a href="/product-detail.php?id=<?= $product['id'] ?>">
          <?= e($product['name']) ?>
        </a>
      </h3>

      <!-- Subtitle / Notes -->
      <p class="text-xs text-slate-600 mt-1 line-clamp-2 leading-relaxed">
        <?= e($product['subtitle'] ?: $product['description']) ?>
      </p>
    </div>

    <!-- Price & Quantity & Actions -->
    <div class="mt-4 pt-4 border-t border-slate-100 space-y-3">
      
      <!-- Price Display -->
      <div class="flex items-baseline justify-between">
        <div class="flex items-baseline gap-2">
          <span class="font-serif font-bold text-2xl text-brand-900">
            <?= format_price($product['price']) ?>
          </span>
          <?php if (!empty($product['original_price']) && $product['original_price'] > $product['price']): ?>
            <span class="text-xs text-slate-400 line-through">
              <?= format_price($product['original_price']) ?>
            </span>
          <?php endif; ?>
        </div>

        <!-- Quantity Stepper -->
        <div class="flex items-center border border-slate-200 rounded-xl bg-slate-50 overflow-hidden shadow-2xs">
          <button @click="if (cardQty > 1) cardQty--" 
                  type="button" 
                  class="w-7 h-7 flex items-center justify-center text-slate-600 hover:bg-slate-200 font-bold transition-colors">
            -
          </button>
          <span class="w-7 text-center text-xs font-bold text-slate-800" x-text="cardQty">1</span>
          <button @click="if (cardQty < 20) cardQty++" 
                  type="button" 
                  class="w-7 h-7 flex items-center justify-center text-slate-600 hover:bg-slate-200 font-bold transition-colors">
            +
          </button>
        </div>
      </div>

      <!-- Action Buttons (Add to Cart & Buy Now) -->
      <?php if ($inStock): ?>
        <div class="grid grid-cols-2 gap-2">
          <button @click="addToCart(<?= $product['id'] ?>, cardQty, true)" 
                  type="button" 
                  class="w-full py-2.5 px-3 rounded-xl border border-brand-700 text-brand-800 hover:bg-brand-50 font-bold text-xs transition-colors flex items-center justify-center gap-1.5 focus:outline-none">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            <span>Add to Cart</span>
          </button>
          <button @click="buyNow(<?= $product['id'] ?>, cardQty)" 
                  type="button" 
                  class="w-full py-2.5 px-3 rounded-xl bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs transition-colors shadow-xs flex items-center justify-center gap-1">
            <span>Buy Now</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>
      <?php else: ?>
        <button disabled type="button" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 text-slate-400 font-bold text-xs cursor-not-allowed">
          Currently Sold Out
        </button>
      <?php endif; ?>

    </div>

  </div>
</div>
