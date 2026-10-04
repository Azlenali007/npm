<?php
/**
 * N.A Fresh Fruits & Coconuts - Hero Showcase & Landing Page
 * Designed strictly following the visual presentation:
 * - Fresh & Naturally Refreshing header
 * - Prominent, high-visual swipeable Coconut Water cards (Hero Category)
 * - Prominent, high-visual swipeable Fresh Juice cards (Secondary Category)
 * - Goodness in Every Sip cursive signature
 * - Real MySQL data & live ordering
 */

require_once __DIR__ . '/config/database.php';

$db = getDB();

// 1. Fetch Coconut Water Products (Hero Category)
$stmt = $db->query("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE c.slug = 'coconut-water' AND p.status = 1
    ORDER BY p.sort_order ASC, p.id ASC
");
$coconutProducts = $stmt->fetchAll();

// 2. Fetch Fresh Juices Products (Secondary Category)
$stmt = $db->query("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE c.slug = 'fresh-juices' AND p.status = 1
    ORDER BY p.sort_order ASC, p.id ASC
");
$juiceProducts = $stmt->fetchAll();

// 3. Fetch Delivery Areas
$stmt = $db->query("SELECT * FROM delivery_areas WHERE is_active = 1 ORDER BY delivery_charge ASC, id ASC");
$deliveryAreas = $stmt->fetchAll();

// 4. Fetch Reviews
$stmt = $db->query("SELECT * FROM reviews WHERE status = 1 ORDER BY id DESC LIMIT 4");
$reviews = $stmt->fetchAll();

$bizPhone = get_setting('phone', '+91 98260 12345');
$bizWhatsApp = get_setting('whatsapp', '919826012345');

$pageTitle = 'N.A Fresh Fruits & Coconuts – Fresh Coconut Water & Pure Juices | Jaora';
$pageDesc = 'Pure, natural and refreshing coconut water straight from fresh green coconuts. Cold-pressed juices made on order in Jaora, M.P.';

require_once __DIR__ . '/includes/header.php';
?>

<!-- ========================================================================= -->
<!-- 1. 🥥 COCONUT WATER PRODUCT SHOWCASE (HERO CATEGORY - EXACT REFERENCE)    -->
<!-- ========================================================================= -->
<section id="coconuts" 
         class="relative pt-10 sm:pt-14 pb-20 sm:pb-24 bg-[#faf9f5] overflow-hidden"
         x-data="coconutShowcase()">
  
  <!-- Subtle Tropical Foreground Palm Silhouette Accents (Framing Corners like reference) -->
  <div class="pointer-events-none absolute bottom-0 left-0 -ml-12 -mb-10 w-48 sm:w-64 opacity-25 filter blur-[2px] z-10 hidden sm:block">
    <svg viewBox="0 0 200 200" fill="none" class="w-full h-full text-emerald-800">
      <path d="M10 190 C40 120, 110 90, 190 70 C160 100, 110 130, 40 195 Z" fill="currentColor"/>
      <path d="M5 195 C60 150, 130 130, 185 125 C145 150, 95 175, 20 200 Z" fill="currentColor" opacity="0.7"/>
      <path d="M0 200 C30 160, 90 140, 160 145 C120 170, 70 190, 10 200 Z" fill="currentColor" opacity="0.5"/>
    </svg>
  </div>
  <div class="pointer-events-none absolute bottom-0 right-0 -mr-12 -mb-10 w-48 sm:w-64 opacity-25 filter blur-[2px] z-10 hidden sm:block">
    <svg viewBox="0 0 200 200" fill="none" class="w-full h-full text-emerald-800 transform -scale-x-100">
      <path d="M10 190 C40 120, 110 90, 190 70 C160 100, 110 130, 40 195 Z" fill="currentColor"/>
      <path d="M5 195 C60 150, 130 130, 185 125 C145 150, 95 175, 20 200 Z" fill="currentColor" opacity="0.7"/>
    </svg>
  </div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20">
    
    <!-- Header: Twin Leaf Emblem & "Fresh & Naturally Refreshing" (Matching Reference) -->
    <div class="text-center max-w-2xl mx-auto mb-10 sm:mb-12">
      <!-- Twin Leaf Icon with delicate side lines -->
      <div class="flex items-center justify-center gap-3 mb-2.5">
        <span class="w-10 sm:w-14 h-[1px] bg-emerald-300/80"></span>
        <svg class="w-5 h-5 text-emerald-700 fill-current" viewBox="0 0 24 24">
          <path d="M17 8C8 10 5.9 16.17 3.82 21.34L5.71 22l1-2.3A4.49 4.49 0 008 20C19 20 22 3 22 3c-1 2-8 2-8 2s-3-2-7 0a8.77 8.77 0 00-4 4.54A11.36 11.36 0 0117 8z"/>
        </svg>
        <span class="w-10 sm:w-14 h-[1px] bg-emerald-300/80"></span>
      </div>

      <!-- Main Headline: "Fresh & Naturally Refreshing" with Cursive script -->
      <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
        Fresh & Naturally 
        <span class="font-script text-4xl sm:text-5xl lg:text-6xl text-emerald-700 font-normal ml-1 inline-block -rotate-1">
          Refreshing
        </span>
      </h2>
      <p class="text-xs sm:text-sm text-slate-500 mt-2 font-medium">
        Handpicked sweet coastal green coconuts • Cut & tapped fresh upon order in Jaora
      </p>
    </div>

    <!-- Swipeable Horizontal Mobile Carousel Track -->
    <div class="relative max-w-5xl mx-auto">
      
      <!-- Circular Desktop/Tablet Left Arrow Button -->
      <button @click="prev()" 
              type="button" 
              class="hidden sm:flex absolute -left-5 top-1/2 -translate-y-1/2 z-30 w-12 h-12 rounded-full bg-white shadow-xl border border-slate-100 items-center justify-center text-slate-700 hover:text-emerald-700 hover:scale-110 active:scale-95 transition-all focus:outline-none"
              aria-label="Previous Coconut Drink">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
        </svg>
      </button>

      <!-- Circular Desktop/Tablet Right Arrow Button -->
      <button @click="next()" 
              type="button" 
              class="hidden sm:flex absolute -right-5 top-1/2 -translate-y-1/2 z-30 w-12 h-12 rounded-full bg-white shadow-xl border border-slate-100 items-center justify-center text-slate-700 hover:text-emerald-700 hover:scale-110 active:scale-95 transition-all focus:outline-none"
              aria-label="Next Coconut Drink">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
      </button>

      <!-- Carousel Container with Smooth Snap -->
      <div class="overflow-x-auto snap-carousel flex gap-4 sm:gap-6 py-4 px-2 sm:px-6 cursor-grab active:cursor-grabbing select-none"
           x-ref="coconutTrack" 
           @scroll.passive="onScroll()"
           style="scroll-behavior: smooth;">
        
        <?php foreach ($coconutProducts as $idx => $product): ?>
          <!-- Individual Swipe Card -->
          <div class="snap-card shrink-0 w-[84vw] max-w-[360px] sm:max-w-[380px] lg:max-w-[400px] flex flex-col transition-all duration-300"
               :class="activeIdx === <?= $idx ?> ? 'scale-100 opacity-100' : 'sm:scale-[0.98] sm:opacity-90'">
            <?php include __DIR__ . '/includes/product-card.php'; ?>
          </div>
        <?php endforeach; ?>

      </div>

      <!-- Mobile Floating Navigation Chevrons (Visible on touch devices) -->
      <div class="sm:hidden flex items-center justify-between px-2 -mt-4 mb-2 pointer-events-none">
        <button @click="prev()" type="button" class="pointer-events-auto w-10 h-10 rounded-full bg-white/95 shadow-md border border-slate-200 flex items-center justify-center text-slate-700 focus:outline-none">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button @click="next()" type="button" class="pointer-events-auto w-10 h-10 rounded-full bg-white/95 shadow-md border border-slate-200 flex items-center justify-center text-slate-700 focus:outline-none">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </button>
      </div>

    </div>

    <!-- Pagination Dots (Matching Reference Style: 4-5 dots below) -->
    <div class="mt-4 sm:mt-6 flex flex-col items-center justify-center gap-4">
      <div class="flex items-center gap-2">
        <template x-for="(dot, idx) in totalCards" :key="idx">
          <button @click="scrollToCard(idx)" 
                  type="button" 
                  class="rounded-full transition-all duration-300 focus:outline-none"
                  :class="activeIdx === idx ? 'w-3 h-3 bg-emerald-700' : 'w-2.5 h-2.5 bg-emerald-200 hover:bg-emerald-300'"
                  :aria-label="'Go to item ' + (idx + 1)">
          </button>
        </template>
      </div>

      <!-- Bottom Slogan: "Goodness in Every Sip" with green brush accent line -->
      <div class="pt-6 sm:pt-8 text-center flex flex-col items-center select-none">
        <span class="font-script text-3xl sm:text-4xl lg:text-5xl text-emerald-800 font-bold tracking-wide leading-none inline-block">
          Goodness in Every Sip
        </span>
        <!-- Curved Green Brush Underline -->
        <svg class="w-40 sm:w-52 h-4 text-emerald-600 mt-1" viewBox="0 0 200 20" fill="none">
          <path d="M10 12 Q 100 2 190 14" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
        </svg>
      </div>
    </div>

  </div>
</section>


<!-- ========================================================================= -->
<!-- 2. 🧃 FRESH JUICES PRODUCT SHOWCASE (SECONDARY CATEGORY - MATCHING CARDS) -->
<!-- ========================================================================= -->
<section id="juices" 
         class="relative pt-16 sm:pt-20 pb-20 sm:pb-24 bg-white border-t border-slate-200/60 overflow-hidden"
         x-data="juiceShowcase()">
  
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20">
    
    <!-- Header: Twin Leaf Emblem & "Cold-Pressed & Naturally Pure" -->
    <div class="text-center max-w-2xl mx-auto mb-10 sm:mb-12">
      <!-- Twin Leaf Icon with subtle lines -->
      <div class="flex items-center justify-center gap-3 mb-2.5">
        <span class="w-10 sm:w-14 h-[1px] bg-amber-300/80"></span>
        <svg class="w-5 h-5 text-amber-600 fill-current" viewBox="0 0 24 24">
          <path d="M17 8C8 10 5.9 16.17 3.82 21.34L5.71 22l1-2.3A4.49 4.49 0 008 20C19 20 22 3 22 3c-1 2-8 2-8 2s-3-2-7 0a8.77 8.77 0 00-4 4.54A11.36 11.36 0 0117 8z"/>
        </svg>
        <span class="w-10 sm:w-14 h-[1px] bg-amber-300/80"></span>
      </div>

      <!-- Main Headline: "Cold-Pressed & Naturally Pure" with Cursive script -->
      <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
        Cold-Pressed & 
        <span class="font-script text-4xl sm:text-5xl lg:text-6xl text-amber-600 font-normal ml-1 inline-block -rotate-1">
          Naturally Pure
        </span>
      </h2>
      <p class="text-xs sm:text-sm text-slate-500 mt-2 font-medium">
        100% Raw Juice Extract • No Added Sugar • No Preservatives • Squeezed on Order
      </p>
    </div>

    <!-- Swipeable Horizontal Mobile Carousel Track -->
    <div class="relative max-w-5xl mx-auto">
      
      <!-- Circular Left Arrow Button -->
      <button @click="prev()" 
              type="button" 
              class="hidden sm:flex absolute -left-5 top-1/2 -translate-y-1/2 z-30 w-12 h-12 rounded-full bg-white shadow-xl border border-slate-100 items-center justify-center text-slate-700 hover:text-amber-700 hover:scale-110 active:scale-95 transition-all focus:outline-none"
              aria-label="Previous Juice Drink">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
        </svg>
      </button>

      <!-- Circular Right Arrow Button -->
      <button @click="next()" 
              type="button" 
              class="hidden sm:flex absolute -right-5 top-1/2 -translate-y-1/2 z-30 w-12 h-12 rounded-full bg-white shadow-xl border border-slate-100 items-center justify-center text-slate-700 hover:text-amber-700 hover:scale-110 active:scale-95 transition-all focus:outline-none"
              aria-label="Next Juice Drink">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
      </button>

      <!-- Carousel Container with Smooth Snap -->
      <div class="overflow-x-auto snap-carousel flex gap-4 sm:gap-6 py-4 px-2 sm:px-6 cursor-grab active:cursor-grabbing select-none"
           x-ref="juiceTrack" 
           @scroll.passive="onScroll()"
           style="scroll-behavior: smooth;">
        
        <?php foreach ($juiceProducts as $idx => $product): ?>
          <!-- Individual Juice Swipe Card -->
          <div class="snap-card shrink-0 w-[84vw] max-w-[360px] sm:max-w-[380px] lg:max-w-[400px] flex flex-col transition-all duration-300"
               :class="activeIdx === <?= $idx ?> ? 'scale-100 opacity-100' : 'sm:scale-[0.98] sm:opacity-90'">
            <?php include __DIR__ . '/includes/product-card.php'; ?>
          </div>
        <?php endforeach; ?>

      </div>

      <!-- Mobile Chevrons -->
      <div class="sm:hidden flex items-center justify-between px-2 -mt-4 mb-2 pointer-events-none">
        <button @click="prev()" type="button" class="pointer-events-auto w-10 h-10 rounded-full bg-white/95 shadow-md border border-slate-200 flex items-center justify-center text-slate-700 focus:outline-none">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button @click="next()" type="button" class="pointer-events-auto w-10 h-10 rounded-full bg-white/95 shadow-md border border-slate-200 flex items-center justify-center text-slate-700 focus:outline-none">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </button>
      </div>

    </div>

    <!-- Pagination Dots -->
    <div class="mt-4 sm:mt-6 flex flex-col items-center justify-center gap-4">
      <div class="flex items-center gap-2">
        <template x-for="(dot, idx) in totalCards" :key="idx">
          <button @click="scrollToCard(idx)" 
                  type="button" 
                  class="rounded-full transition-all duration-300 focus:outline-none"
                  :class="activeIdx === idx ? 'w-3 h-3 bg-amber-600' : 'w-2.5 h-2.5 bg-amber-200 hover:bg-amber-300'"
                  :aria-label="'Go to juice ' + (idx + 1)">
          </button>
        </template>
      </div>

      <!-- Secondary Slogan: "100% Real Fruit • Cold Pressed" with amber brush stroke -->
      <div class="pt-6 sm:pt-8 text-center flex flex-col items-center select-none">
        <span class="font-script text-3xl sm:text-4xl text-amber-700 font-bold tracking-wide leading-none inline-block">
          Pure Fruit in Every Glass
        </span>
        <svg class="w-40 sm:w-52 h-4 text-amber-500 mt-1" viewBox="0 0 200 20" fill="none">
          <path d="M10 12 Q 100 2 190 14" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
        </svg>
      </div>
    </div>

  </div>
</section>


<!-- ========================================================================= -->
<!-- 3. WHY CHOOSE US (HYGIENE & ZERO PRESERVATIVE PROMISE)                    -->
<!-- ========================================================================= -->
<section id="why-us" class="py-16 sm:py-24 bg-[#faf9f5] border-t border-slate-200/80">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="text-center max-w-2xl mx-auto mb-16">
      <span class="text-xs font-bold text-emerald-700 uppercase tracking-widest block mb-2">Our Hygiene Standards</span>
      <h2 class="font-serif font-bold text-3xl sm:text-4xl text-slate-900 tracking-tight">
        Why Jaora Trusts N.A Fresh Fruits
      </h2>
      <p class="text-xs sm:text-sm text-slate-600 mt-2">
        We built our juice and coconut shop with an uncompromising standard of purity and hygiene.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      
      <div class="p-6 rounded-3xl bg-white border border-emerald-100 shadow-sm flex flex-col justify-between">
        <div>
          <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-5 text-xl font-bold">
            🥥
          </div>
          <h3 class="font-serif font-bold text-lg text-slate-900 mb-2">100% Raw & Natural</h3>
          <p class="text-xs text-slate-600 leading-relaxed">
            Zero water dilution, zero added sugar, and zero preservatives. Only the raw extract of nature.
          </p>
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] font-semibold text-emerald-700">
          Pure fruit & coconut
        </div>
      </div>

      <div class="p-6 rounded-3xl bg-white border border-emerald-100 shadow-sm flex flex-col justify-between">
        <div>
          <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center mb-5 text-xl font-bold">
            🔪
          </div>
          <h3 class="font-serif font-bold text-lg text-slate-900 mb-2">Cut & Pressed on Order</h3>
          <p class="text-xs text-slate-600 leading-relaxed">
            No pre-cut spoiled fruits. Every coconut is chopped and every glass of juice squeezed after you order.
          </p>
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] font-semibold text-amber-700">
          Peak aroma & enzymes
        </div>
      </div>

      <div class="p-6 rounded-3xl bg-white border border-emerald-100 shadow-sm flex flex-col justify-between">
        <div>
          <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-5 text-xl font-bold">
            🧊
          </div>
          <h3 class="font-serif font-bold text-lg text-slate-900 mb-2">Hygienic Chilled Packaging</h3>
          <p class="text-xs text-slate-600 leading-relaxed">
            Food-grade sealed containers, bio-degradable paper straws, and thermal dispatch pouches to keep drinks cold.
          </p>
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] font-semibold text-emerald-700">
          Leakproof & clean
        </div>
      </div>

      <div class="p-6 rounded-3xl bg-white border border-emerald-100 shadow-sm flex flex-col justify-between">
        <div>
          <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-5 text-xl font-bold">
            ⚡
          </div>
          <h3 class="font-serif font-bold text-lg text-slate-900 mb-2">Fast Local Jaora Delivery</h3>
          <p class="text-xs text-slate-600 leading-relaxed">
            Our riders reach Station Road, Azad Chowk, Shastri Colony, and Piploda Road in 20-35 minutes.
          </p>
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] font-semibold text-emerald-700">
          Doorstep delivery
        </div>
      </div>

    </div>

  </div>
</section>


<!-- ========================================================================= -->
<!-- 4. JAORA LOCAL DOORSTEP DELIVERY COVERAGE                                 -->
<!-- ========================================================================= -->
<section id="delivery-areas" class="py-16 sm:py-24 bg-white border-t border-slate-200/80">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="text-center max-w-2xl mx-auto mb-16">
      <span class="text-xs font-bold text-emerald-700 uppercase tracking-widest block mb-2">Doorstep Coverage</span>
      <h2 class="font-serif font-bold text-3xl sm:text-4xl text-slate-900 tracking-tight">
        Delivering Across Jaora (M.P.)
      </h2>
      <p class="text-xs sm:text-sm text-slate-600 mt-2">
        Fresh drinks delivered to your home, office, or shop with transparent local rates.
      </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <?php foreach ($deliveryAreas as $area): ?>
        <div class="p-4 rounded-2xl border border-slate-200/90 bg-slate-50/70 hover:bg-white hover:border-emerald-300 transition-colors flex items-center justify-between">
          <div>
            <h4 class="font-bold text-sm text-slate-900"><?= e($area['area_name']) ?></h4>
            <span class="text-xs text-slate-500"><?= e($area['est_delivery_time']) ?></span>
          </div>
          <div class="text-right">
            <span class="text-xs font-bold text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-lg">
              <?= format_price($area['delivery_charge']) ?> Delivery
            </span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ========================================================================= -->
<!-- 5. VERIFIED CUSTOMER REVIEWS                                              -->
<!-- ========================================================================= -->
<section id="reviews" class="py-16 sm:py-24 bg-[#faf9f5] border-t border-slate-200/80">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="text-center max-w-2xl mx-auto mb-16">
      <span class="text-xs font-bold text-emerald-700 uppercase tracking-widest block mb-2">Local Feedback</span>
      <h2 class="font-serif font-bold text-3xl sm:text-4xl text-slate-900 tracking-tight">
        Loved by Jaora Residents
      </h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php foreach ($reviews as $rev): ?>
        <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
          <div class="space-y-3">
            <div class="flex items-center text-amber-400 text-sm">
              <?php for ($i = 0; $i < $rev['rating']; $i++): ?>★<?php endfor; ?>
            </div>
            <p class="text-xs text-slate-700 leading-relaxed italic">
              "<?= e($rev['comment']) ?>"
            </p>
          </div>
          <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
            <div>
              <h4 class="font-bold text-xs text-slate-900"><?= e($rev['customer_name']) ?></h4>
              <span class="text-[10px] text-slate-500"><?= e($rev['location']) ?></span>
            </div>
            <span class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-bold">Verified</span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- Carousel Controller JavaScript in Alpine.js -->
<script>
  function coconutShowcase() {
    return {
      activeIdx: 0,
      totalCards: <?= count($coconutProducts) ?>,
      
      onScroll() {
        const el = this.$refs.coconutTrack;
        if (!el) return;
        const scrollLeft = el.scrollLeft;
        const cardWidth = el.firstElementChild ? el.firstElementChild.offsetWidth : 360;
        this.activeIdx = Math.round(scrollLeft / (cardWidth + 16));
      },

      scrollToCard(idx) {
        const el = this.$refs.coconutTrack;
        if (!el) return;
        const cardWidth = el.firstElementChild ? el.firstElementChild.offsetWidth : 360;
        el.scrollTo({ left: idx * (cardWidth + 16), behavior: 'smooth' });
        this.activeIdx = idx;
      },

      next() {
        if (this.activeIdx < this.totalCards - 1) {
          this.scrollToCard(this.activeIdx + 1);
        } else {
          this.scrollToCard(0);
        }
      },

      prev() {
        if (this.activeIdx > 0) {
          this.scrollToCard(this.activeIdx - 1);
        } else {
          this.scrollToCard(this.totalCards - 1);
        }
      }
    };
  }

  function juiceShowcase() {
    return {
      activeIdx: 0,
      totalCards: <?= count($juiceProducts) ?>,

      onScroll() {
        const el = this.$refs.juiceTrack;
        if (!el) return;
        const scrollLeft = el.scrollLeft;
        const cardWidth = el.firstElementChild ? el.firstElementChild.offsetWidth : 360;
        this.activeIdx = Math.round(scrollLeft / (cardWidth + 16));
      },

      scrollToCard(idx) {
        const el = this.$refs.juiceTrack;
        if (!el) return;
        const cardWidth = el.firstElementChild ? el.firstElementChild.offsetWidth : 360;
        el.scrollTo({ left: idx * (cardWidth + 16), behavior: 'smooth' });
        this.activeIdx = idx;
      },

      next() {
        if (this.activeIdx < this.totalCards - 1) {
          this.scrollToCard(this.activeIdx + 1);
        } else {
          this.scrollToCard(0);
        }
      },

      prev() {
        if (this.activeIdx > 0) {
          this.scrollToCard(this.activeIdx - 1);
        } else {
          this.scrollToCard(this.totalCards - 1);
        }
      }
    };
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
