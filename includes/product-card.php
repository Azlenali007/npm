<?php
/**
 * N.A Fresh Fruits & Coconuts - Premium 3D Card Deck Component
 * Balanced for Mobile and Large Desktop Screens:
 * - Much larger, prominent, visually dominant on desktop (up to 1020px max-width)
 * - Large crystal-clear realistic food photography covering >55% of the card
 * - Bold pricing in INR, comfortable stepper, and prominent Order Now button
 */

$isCoconut = (isset($product['category_slug']) && $product['category_slug'] === 'coconut-water') 
             || (isset($product['category_id']) && $product['category_id'] == 1);
$inStock = (bool)($product['in_stock'] ?? 1);
$priceNum = round((float)$product['price']);
$origPriceNum = !empty($product['original_price']) ? round((float)$product['original_price']) : 0;
?>
<div class="product-showcase-card group relative flex flex-col bg-white rounded-[2.25rem] sm:rounded-[2.75rem] lg:rounded-[3rem] xl:rounded-[3.25rem] p-5 sm:p-7 lg:p-9 xl:p-10 2xl:p-11 border border-emerald-100/90 shadow-[0_20px_50px_rgba(22,163,74,0.16)] lg:shadow-[0_30px_80px_rgba(22,163,74,0.22)] transition-shadow duration-300 w-full h-full justify-between overflow-hidden"
     x-data="{ cardQty: 1 }">
  
  <div>
    <!-- Large Realistic Product Image (Visual Focus - Dominates Card on Desktop & Mobile) -->
    <div class="relative w-full aspect-[4/3.3] sm:aspect-[4/3] md:aspect-[16/11] lg:aspect-[16/10] xl:aspect-[16/9.5] 2xl:aspect-[16/9.2] rounded-[1.5rem] sm:rounded-[2rem] lg:rounded-[2.5rem] xl:rounded-[2.75rem] overflow-hidden bg-emerald-50/50 shadow-2xs mb-4 lg:mb-6 xl:mb-7 select-none">
      <a href="/product-detail.php?id=<?= $product['id'] ?>" class="block w-full h-full">
        <img src="<?= e($product['image_url']) ?>" 
             alt="<?= e($product['name']) ?>" 
             loading="lazy"
             draggable="false"
             class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105 pointer-events-none">
      </a>

      <!-- Circular "100% NATURAL" Badge on Top-Right (Matching Reference) -->
      <div class="absolute top-3.5 right-3.5 sm:top-5 sm:right-5 xl:top-6 xl:right-6 pointer-events-none select-none">
        <div class="w-12 h-12 sm:w-14 sm:h-14 lg:w-18 lg:h-18 xl:w-22 xl:h-22 2xl:w-24 2xl:h-24 rounded-full bg-white/95 backdrop-blur-xs border border-emerald-200/90 shadow-md flex flex-col items-center justify-center text-center p-1">
          <svg class="w-3.5 h-3.5 lg:w-5 lg:h-5 xl:w-6 xl:h-6 text-emerald-600 fill-current mb-0.5" viewBox="0 0 24 24">
            <path d="M17 8C8 10 5.9 16.17 3.82 21.34L5.71 22l1-2.3A4.49 4.49 0 008 20C19 20 22 3 22 3c-1 2-8 2-8 2s-3-2-7 0a8.77 8.77 0 00-4 4.54A11.36 11.36 0 0117 8z"/>
          </svg>
          <span class="text-[8px] sm:text-[9px] lg:text-[11px] xl:text-[13px] 2xl:text-[14px] font-black uppercase tracking-tight text-emerald-950 leading-tight">
            100%<br>Natural
          </span>
        </div>
      </div>

      <!-- Wishlist Heart Button (Top-Left) -->
      <button type="button" 
              onclick="event.preventDefault(); window.location.href='/wishlist.php?add=<?= $product['id'] ?>'"
              class="absolute top-3.5 left-3.5 sm:top-5 sm:left-5 xl:top-6 xl:left-6 w-9 h-9 sm:w-10 sm:h-10 lg:w-12 lg:h-12 xl:w-14 xl:h-14 rounded-full bg-white/95 backdrop-blur-xs text-slate-500 hover:text-red-500 hover:bg-white shadow-sm flex items-center justify-center transition-colors z-10"
              title="Save to Wishlist">
        <svg class="w-4 h-4 lg:w-5 lg:h-5 xl:w-6 xl:h-6 fill-none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
        </svg>
      </button>

      <!-- Handwritten Script Stamp (Bottom-Right of Image) -->
      <div class="absolute bottom-3 right-3 sm:bottom-4 sm:right-4 lg:bottom-5 lg:right-5 xl:bottom-6 xl:right-6 pointer-events-none drop-shadow-md select-none transform rotate-[-4deg]">
        <span class="font-script text-emerald-950 text-base sm:text-lg lg:text-2xl xl:text-3xl 2xl:text-4xl font-bold bg-white/90 backdrop-blur-xs px-2.5 py-0.5 lg:px-4 lg:py-1.5 xl:px-5 xl:py-2 rounded-lg lg:rounded-xl xl:rounded-2xl border border-emerald-100 shadow-2xs block">
          <?= $isCoconut ? 'Pure Coconut Water' : 'Pure Fresh Juice' ?>
        </span>
      </div>

      <!-- Unit Tag (Bottom-Left) -->
      <div class="absolute bottom-3 left-3 sm:bottom-4 sm:left-4 lg:bottom-5 lg:left-5 xl:bottom-6 xl:left-6 bg-emerald-950/80 backdrop-blur-xs text-emerald-100 text-[10px] sm:text-xs lg:text-sm xl:text-base font-bold px-2.5 py-1 lg:px-3.5 lg:py-1.5 xl:px-4 xl:py-2 rounded-md lg:rounded-lg xl:rounded-xl">
        <?= e($product['unit_size']) ?>
      </div>
    </div>

    <!-- Product Title & Description (Spacious, Clear & Readable) -->
    <div class="space-y-1.5 lg:space-y-2.5 xl:space-y-3 text-left">
      <h3 class="font-bold text-xl sm:text-2xl lg:text-3xl xl:text-4xl 2xl:text-5xl text-slate-900 group-hover:text-emerald-800 transition-colors tracking-tight leading-snug">
        <a href="/product-detail.php?id=<?= $product['id'] ?>">
          <?= e($product['name']) ?>
        </a>
      </h3>

      <p class="text-xs sm:text-sm lg:text-base xl:text-lg 2xl:text-xl text-slate-500 leading-relaxed line-clamp-2 mt-1 lg:mt-2 xl:mt-2.5">
        <?= e($product['description'] ?: $product['subtitle']) ?>
      </p>
    </div>
  </div>

  <!-- Bottom Details: Price, Natural Tag, Stepper & Full-Width Pill Button -->
  <div class="mt-4 sm:mt-5 lg:mt-6 xl:mt-7 pt-3 sm:pt-4 lg:pt-5 xl:pt-6 border-t border-slate-100 space-y-3 sm:space-y-4 lg:space-y-5 xl:space-y-6">
    
    <!-- Price & 100% Natural Tag Row -->
    <div class="flex items-center justify-between gap-2">
      <div class="flex items-baseline gap-2 xl:gap-3">
        <span class="font-extrabold text-2xl sm:text-3xl lg:text-4xl xl:text-5xl 2xl:text-6xl text-slate-900 tracking-tight">
          ₹<?= $priceNum ?>
        </span>
        <?php if ($origPriceNum > $priceNum): ?>
          <span class="text-xs sm:text-sm lg:text-base xl:text-xl 2xl:text-2xl text-slate-400 line-through font-semibold">
            ₹<?= $origPriceNum ?>
          </span>
        <?php endif; ?>
      </div>

      <!-- 100% Natural Pill Badge (Matching Reference) -->
      <div class="inline-flex items-center gap-1.5 xl:gap-2 px-3 py-1 sm:px-3.5 sm:py-1.5 lg:px-5 lg:py-2 xl:px-6 xl:py-2.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200/80 text-xs sm:text-xs lg:text-sm xl:text-base font-bold">
        <svg class="w-3.5 h-3.5 lg:w-4 lg:h-4 xl:w-5 xl:h-5 text-emerald-600 fill-current" viewBox="0 0 24 24">
          <path d="M17 8C8 10 5.9 16.17 3.82 21.34L5.71 22l1-2.3A4.49 4.49 0 008 20C19 20 22 3 22 3c-1 2-8 2-8 2s-3-2-7 0a8.77 8.77 0 00-4 4.54A11.36 11.36 0 0117 8z"/>
        </svg>
        <span>100% Natural</span>
      </div>
    </div>

    <!-- Stepper & Order Action Grid -->
    <div class="space-y-2.5 sm:space-y-3 lg:space-y-4 xl:space-y-5">
      <!-- Quantity Stepper -->
      <div class="flex items-center justify-between bg-slate-50 px-3 py-1.5 sm:px-4 sm:py-2 lg:px-5 lg:py-2.5 xl:px-6 xl:py-3 rounded-xl sm:rounded-2xl xl:rounded-3xl border border-slate-200/80">
        <span class="text-[11px] sm:text-xs lg:text-sm xl:text-base font-bold text-slate-500 uppercase tracking-wider">Quantity</span>
        <div class="flex items-center gap-2 xl:gap-3">
          <button @click.stop="if (cardQty > 1) cardQty--" 
                  type="button" 
                  class="w-8 h-8 sm:w-8 sm:h-8 lg:w-10 lg:h-10 xl:w-12 xl:h-12 rounded-lg lg:rounded-xl xl:rounded-2xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 font-bold flex items-center justify-center text-sm lg:text-lg xl:text-2xl shadow-2xs transition-colors">
            −
          </button>
          <span class="w-7 lg:w-10 xl:w-12 text-center text-xs sm:text-sm lg:text-lg xl:text-2xl font-extrabold text-slate-900" x-text="cardQty">1</span>
          <button @click.stop="if (cardQty < 20) cardQty++" 
                  type="button" 
                  class="w-8 h-8 sm:w-8 sm:h-8 lg:w-10 lg:h-10 xl:w-12 xl:h-12 rounded-lg lg:rounded-xl xl:rounded-2xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 font-bold flex items-center justify-center text-sm lg:text-lg xl:text-2xl shadow-2xs transition-colors">
            +
          </button>
        </div>
      </div>

      <!-- Large Rounded Pill Order Button (Dominant CTA) -->
      <?php if ($inStock): ?>
        <button @click.stop="addToCart(<?= $product['id'] ?>, cardQty, true)" 
                type="button" 
                class="w-full py-3.5 sm:py-4 lg:py-5 xl:py-5.5 px-6 lg:px-10 xl:px-14 rounded-full bg-[#064e3b] hover:bg-[#043d2e] active:scale-[0.98] text-white font-extrabold text-sm sm:text-base lg:text-xl xl:text-2xl shadow-lg lg:shadow-xl shadow-emerald-950/20 transition-all flex items-center justify-center gap-2.5 lg:gap-3 xl:gap-4">
          <svg class="w-5 h-5 lg:w-6 lg:h-6 xl:w-7 xl:h-7 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
          <span>Order Now</span>
          <span class="text-emerald-300">→</span>
        </button>
      <?php else: ?>
        <button disabled type="button" class="w-full py-3.5 sm:py-4 lg:py-5 px-6 rounded-full bg-slate-100 text-slate-400 font-bold text-sm lg:text-lg cursor-not-allowed">
          Sold Out for Today
        </button>
      <?php endif; ?>
    </div>

  </div>

</div>
