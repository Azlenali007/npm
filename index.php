<?php
/**
 * N.A Fresh Fruits & Coconuts - Premium Landing Page
 * Jaora, Madhya Pradesh, India
 */

require_once __DIR__ . '/config/database.php';

$db = getDB();

// 1. Fetch Coconut Water Products (Primary Showcase)
$stmt = $db->query("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE c.slug = 'coconut-water' AND p.status = 1
    ORDER BY p.sort_order ASC, p.id ASC
");
$coconutProducts = $stmt->fetchAll();

// 2. Fetch Fresh Juices Products (Secondary Showcase)
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

// 5. Fetch Gallery
$stmt = $db->query("SELECT * FROM gallery ORDER BY sort_order ASC LIMIT 4");
$galleryItems = $stmt->fetchAll();

// Settings
$heroHeadline = get_setting('hero_headline', 'Freshness You Can Taste');
$heroSubtitle = get_setting('hero_subtitle', 'Fresh Coconut Water & Juices, prepared with care in Jaora.');
$bizPhone = get_setting('phone', '+91 98260 12345');
$bizWhatsApp = get_setting('whatsapp', '919826012345');

$pageTitle = 'N.A Fresh Fruits & Coconuts – Fresh Coconut Water & Pure Juices in Jaora';
$pageDesc = 'Order 100% natural green coconut water & cold-pressed fresh juices in Jaora, MP. Prepared fresh upon order with doorstep delivery across Station Road, Azad Chowk & more.';

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. HERO SECTION -->
<section class="relative bg-gradient-to-b from-brand-950 via-brand-900 to-brand-950 text-white overflow-hidden py-16 sm:py-24 lg:py-28">
  <!-- Subtle Botanical Ambient Background Glow -->
  <div class="absolute inset-0 pointer-events-none opacity-20">
    <div class="absolute top-0 right-1/4 w-96 h-96 rounded-full bg-brand-400 blur-3xl"></div>
    <div class="absolute bottom-0 left-1/4 w-96 h-96 rounded-full bg-amber-400 blur-3xl"></div>
  </div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
      
      <!-- Left Hero Text & Trust Points -->
      <div class="lg:col-span-7 space-y-6 text-center lg:text-left hero-content">
        
        <!-- Quiet Freshness Kicker -->
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand-800/80 border border-brand-700/60 text-brand-200 text-xs font-semibold backdrop-blur-xs">
          <span class="w-2 h-2 rounded-full bg-brand-400 animate-pulse"></span>
          <span>Jaora's Authentic Tender Coconuts & Raw Juices</span>
        </div>

        <!-- Main Headline -->
        <h1 class="font-serif font-bold text-4xl sm:text-5xl lg:text-6xl text-white tracking-tight leading-[1.12]">
          <?= e($heroHeadline) ?>
        </h1>

        <!-- Supporting Text -->
        <p class="text-base sm:text-lg text-brand-100/90 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
          <?= e($heroSubtitle) ?> Cut, strained, and bottled directly upon your order with zero added sugar, zero water dilution, and zero artificial preservatives.
        </p>

        <!-- CTA Buttons -->
        <div class="pt-2 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3 sm:gap-4">
          <a href="#coconuts" 
             class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-brand-500 hover:bg-brand-400 text-brand-950 font-bold text-base shadow-lg shadow-brand-500/25 transition-all duration-200 hover:-translate-y-0.5 text-center flex items-center justify-center gap-2">
            <span>Order Now</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </a>
          <a href="#juices" 
             class="w-full sm:w-auto px-7 py-4 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-semibold text-base border border-white/20 backdrop-blur-xs transition-colors text-center">
            Explore Juices
          </a>
        </div>

        <!-- Small Trust Points (3 Columns) -->
        <div class="pt-6 border-t border-brand-800/60 grid grid-cols-3 gap-4 text-center lg:text-left">
          <div>
            <div class="font-serif font-bold text-xl sm:text-2xl text-brand-300">100%</div>
            <div class="text-xs text-brand-200/80 font-medium">Pure & Natural</div>
          </div>
          <div>
            <div class="font-serif font-bold text-xl sm:text-2xl text-brand-300">Made Fresh</div>
            <div class="text-xs text-brand-200/80 font-medium">Prepared on Order</div>
          </div>
          <div>
            <div class="font-serif font-bold text-xl sm:text-2xl text-brand-300">Fast Local</div>
            <div class="text-xs text-brand-200/80 font-medium">Jaora Doorstep Delivery</div>
          </div>
        </div>

      </div>

      <!-- Right Visual Feature (Hero Coconuts & Fresh Juice) -->
      <div class="lg:col-span-5 relative flex justify-center">
        <div class="relative w-full max-w-md aspect-square rounded-3xl overflow-hidden shadow-2xl border-4 border-white/10 bg-brand-900 group">
          <img src="https://images.unsplash.com/photo-1544378730-8b5104b18790?auto=format&fit=crop&w=900&q=80" 
               alt="Fresh Green Tender Coconuts in Jaora" 
               class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
          
          <div class="absolute inset-0 bg-gradient-to-t from-brand-950/90 via-transparent to-transparent"></div>
          
          <!-- Floating Live Badge -->
          <div class="absolute bottom-5 left-5 right-5 p-4 rounded-2xl bg-white/95 backdrop-blur-md text-slate-800 shadow-xl border border-white flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-brand-100 text-brand-800 flex items-center justify-center font-bold">
                🥥
              </div>
              <div>
                <p class="font-serif font-bold text-sm text-slate-900">Sweet Tender Coconuts</p>
                <p class="text-xs text-slate-500">Fresh daily harvest batch in stock</p>
              </div>
            </div>
            <span class="font-bold text-brand-800 text-sm">₹60</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>


<!-- 2. 🥥 COCONUT WATER PRODUCT SECTION (PRIMARY SECTION - HORIZONTAL SWIPE CAROUSEL) -->
<section id="coconuts" 
         class="py-16 sm:py-24 bg-coconut-cream border-b border-slate-200/80 relative"
         x-data="coconutCarousel()">
  
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Section Header with Controls -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8 sm:mb-12">
      <div>
        <div class="flex items-center gap-2 text-xs font-bold text-brand-700 uppercase tracking-wider mb-2">
          <span>Primary Specialty</span>
          <span aria-hidden="true">·</span>
          <span>100% Raw Electrolytes</span>
        </div>
        <h2 class="font-serif font-bold text-3xl sm:text-4xl text-brand-950 tracking-tight">
          Pure Tender Coconut Water
        </h2>
        <p class="text-sm text-slate-600 mt-2 max-w-xl">
          Sourced from coastal tender green palms. Swipe through our hand-cut coconuts, jumbo bottled water, and tender malai bowls.
        </p>
      </div>

      <!-- Desktop Previous / Next Arrows -->
      <div class="hidden sm:flex items-center gap-3">
        <button @click="prev()" 
                type="button" 
                class="w-11 h-11 rounded-2xl border border-slate-300 bg-white text-slate-700 hover:bg-brand-50 hover:text-brand-800 hover:border-brand-400 transition-colors flex items-center justify-center shadow-xs focus:outline-none"
                aria-label="Previous Coconut Product">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button @click="next()" 
                type="button" 
                class="w-11 h-11 rounded-2xl border border-slate-300 bg-white text-slate-700 hover:bg-brand-50 hover:text-brand-800 hover:border-brand-400 transition-colors flex items-center justify-center shadow-xs focus:outline-none"
                aria-label="Next Coconut Product">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </button>
      </div>
    </div>

    <!-- Swipeable Carousel Track -->
    <div class="relative -mx-4 sm:mx-0 px-4 sm:px-0">
      <div x-ref="track" 
           @scroll.passive="onScroll()"
           class="snap-carousel flex gap-4 sm:gap-6 overflow-x-auto pb-4 pt-1 items-stretch cursor-grab active:cursor-grabbing select-none"
           style="scroll-behavior: smooth;">
        
        <?php foreach ($coconutProducts as $index => $product): ?>
          <!-- Swipe Card -->
          <div class="snap-card shrink-0 w-[84vw] sm:w-[360px] lg:w-[380px] flex flex-col">
            <?php include __DIR__ . '/includes/product-card.php'; ?>
          </div>
        <?php endforeach; ?>

      </div>
    </div>

    <!-- Mobile Pagination Dots & Swipe Hint -->
    <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-1.5">
        <template x-for="(dot, idx) in totalCards" :key="idx">
          <button @click="scrollToCard(idx)" 
                  type="button" 
                  class="h-2 rounded-full transition-all duration-300"
                  :class="activeIdx === idx ? 'w-8 bg-brand-700' : 'w-2 bg-slate-300 hover:bg-slate-400'"
                  :aria-label="'Go to coconut product ' + (idx + 1)">
          </button>
        </template>
      </div>
      <div class="text-xs text-slate-400 flex items-center gap-1.5">
        <svg class="w-4 h-4 text-slate-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        <span>Swipe left or right to explore all coconut variants</span>
      </div>
    </div>

  </div>
</section>


<!-- 3. 🧃 FRESH JUICES SECTION (IMMEDIATELY BELOW COCONUT WATER) -->
<section id="juices" 
         class="py-16 sm:py-24 bg-white border-b border-slate-200/80 relative"
         x-data="juiceCarousel()">
  
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Section Header with Controls -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8 sm:mb-12">
      <div>
        <div class="flex items-center gap-2 text-xs font-bold text-amber-700 uppercase tracking-wider mb-2">
          <span>Cold-Pressed & Squeezed on Order</span>
          <span aria-hidden="true">·</span>
          <span>Zero Sugar Added</span>
        </div>
        <h2 class="font-serif font-bold text-3xl sm:text-4xl text-slate-900 tracking-tight">
          Freshly Prepared Juices
        </h2>
        <p class="text-sm text-slate-600 mt-2 max-w-xl">
          Pressed using stainless-steel slow juice extractors to keep live enzymes, natural citrus sweetness, and rich vitamins intact.
        </p>
      </div>

      <!-- Desktop Previous / Next Arrows -->
      <div class="hidden sm:flex items-center gap-3">
        <button @click="prev()" 
                type="button" 
                class="w-11 h-11 rounded-2xl border border-slate-300 bg-white text-slate-700 hover:bg-amber-50 hover:text-amber-800 hover:border-amber-400 transition-colors flex items-center justify-center shadow-xs focus:outline-none"
                aria-label="Previous Juice Product">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button @click="next()" 
                type="button" 
                class="w-11 h-11 rounded-2xl border border-slate-300 bg-white text-slate-700 hover:bg-amber-50 hover:text-amber-800 hover:border-amber-400 transition-colors flex items-center justify-center shadow-xs focus:outline-none"
                aria-label="Next Juice Product">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </button>
      </div>
    </div>

    <!-- Swipeable Carousel Track -->
    <div class="relative -mx-4 sm:mx-0 px-4 sm:px-0">
      <div x-ref="track" 
           @scroll.passive="onScroll()"
           class="snap-carousel flex gap-4 sm:gap-6 overflow-x-auto pb-4 pt-1 items-stretch cursor-grab active:cursor-grabbing select-none"
           style="scroll-behavior: smooth;">
        
        <?php foreach ($juiceProducts as $index => $product): ?>
          <!-- Swipe Card -->
          <div class="snap-card shrink-0 w-[84vw] sm:w-[360px] lg:w-[380px] flex flex-col">
            <?php include __DIR__ . '/includes/product-card.php'; ?>
          </div>
        <?php endforeach; ?>

      </div>
    </div>

    <!-- Mobile Pagination Dots & View All Link -->
    <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-1.5">
        <template x-for="(dot, idx) in totalCards" :key="idx">
          <button @click="scrollToCard(idx)" 
                  type="button" 
                  class="h-2 rounded-full transition-all duration-300"
                  :class="activeIdx === idx ? 'w-8 bg-amber-600' : 'w-2 bg-slate-300 hover:bg-slate-400'"
                  :aria-label="'Go to juice ' + (idx + 1)">
          </button>
        </template>
      </div>
      <a href="/products.php" class="text-xs font-bold text-brand-800 hover:text-brand-900 flex items-center gap-1.5">
        <span>View Full Menu & Platters</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
      </a>
    </div>

  </div>
</section>


<!-- 4. WHY CHOOSE US -->
<section id="why-us" class="py-16 sm:py-24 bg-coconut-cream border-b border-slate-200/80">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="text-center max-w-2xl mx-auto mb-16">
      <h2 class="font-serif font-bold text-3xl sm:text-4xl text-brand-950 tracking-tight">
        Why Jaora Trusts N.A Fresh
      </h2>
      <p class="text-sm text-slate-600 mt-2">
        We built our juice and coconut shop with an uncompromising standard of hygiene and pure freshness.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
      
      <!-- Feature 1 -->
      <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
        <div>
          <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center mb-5 text-xl font-bold">
            🌿
          </div>
          <h3 class="font-serif font-bold text-lg text-slate-900 mb-2">100% Raw & Natural</h3>
          <p class="text-xs text-slate-600 leading-relaxed">
            Zero water dilution, zero added sugar, and zero preservatives. Only the raw extract of nature.
          </p>
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] font-semibold text-brand-700">
          Pure fruit & coconut
        </div>
      </div>

      <!-- Feature 2 -->
      <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
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

      <!-- Feature 3 -->
      <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
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

      <!-- Feature 4 -->
      <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
        <div>
          <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center mb-5 text-xl font-bold">
            ⚡
          </div>
          <h3 class="font-serif font-bold text-lg text-slate-900 mb-2">Fast Local Jaora Delivery</h3>
          <p class="text-xs text-slate-600 leading-relaxed">
            Our riders reach Station Road, Azad Chowk, Shastri Colony, and Piploda Road in 20-35 minutes.
          </p>
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] font-semibold text-brand-700">
          Doorstep delivery
        </div>
      </div>

    </div>

  </div>
</section>


<!-- 5. FRESHNESS & SHOP GALLERY -->
<section class="py-16 sm:py-24 bg-white border-b border-slate-200/80">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
      <div>
        <span class="text-xs font-bold text-brand-700 uppercase tracking-wider">Visual Hygiene</span>
        <h2 class="font-serif font-bold text-3xl sm:text-4xl text-slate-900 mt-1 tracking-tight">
          Inside N.A Fresh Fruits
        </h2>
      </div>
      <p class="text-xs text-slate-500 max-w-sm">
        Located at Shop No. 4, Station Road, Opp. Municipal Garden, Jaora. Visit us or order online.
      </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php foreach ($galleryItems as $g): ?>
        <div class="group relative rounded-3xl overflow-hidden aspect-4/3 bg-slate-100 shadow-sm border border-slate-200">
          <img src="<?= e($g['image_url']) ?>" 
               alt="<?= e($g['title']) ?>" 
               loading="lazy"
               class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
          <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent flex flex-col justify-end p-4 text-white">
            <h4 class="font-bold text-sm"><?= e($g['title']) ?></h4>
            <p class="text-[11px] text-slate-300"><?= e($g['caption']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- 6. CUSTOMER REVIEWS -->
<section id="reviews" class="py-16 sm:py-24 bg-coconut-cream border-b border-slate-200/80">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="text-center max-w-2xl mx-auto mb-16">
      <span class="text-xs font-bold text-brand-700 uppercase tracking-wider">Local Love in Jaora</span>
      <h2 class="font-serif font-bold text-3xl sm:text-4xl text-brand-950 mt-1 tracking-tight">
        What Our Regulars Say
      </h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php foreach ($reviews as $rev): ?>
        <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-xs flex flex-col justify-between">
          <div class="space-y-3">
            <!-- 5 Stars -->
            <div class="flex items-center text-amber-400 text-sm">
              <?php for ($i = 0; $i < $rev['rating']; $i++): ?>
                ★
              <?php endfor; ?>
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


<!-- 7. JAORA DELIVERY AREAS -->
<section id="delivery-areas" class="py-16 sm:py-24 bg-white border-b border-slate-200/80">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="text-center max-w-2xl mx-auto mb-16">
      <span class="text-xs font-bold text-brand-700 uppercase tracking-wider">Local Doorstep Service</span>
      <h2 class="font-serif font-bold text-3xl sm:text-4xl text-slate-900 mt-1 tracking-tight">
        Delivering Across Jaora (M.P.)
      </h2>
      <p class="text-sm text-slate-600 mt-2">
        We serve all key colonies and markets in Jaora with nominal delivery rates.
      </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <?php foreach ($deliveryAreas as $area): ?>
        <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/60 hover:bg-white hover:border-brand-300 transition-colors flex items-center justify-between">
          <div>
            <h4 class="font-bold text-sm text-slate-900"><?= e($area['area_name']) ?></h4>
            <span class="text-xs text-slate-500"><?= e($area['est_delivery_time']) ?></span>
          </div>
          <div class="text-right">
            <span class="text-xs font-bold text-brand-800 bg-brand-50 px-2.5 py-1 rounded-lg">
              <?= format_price($area['delivery_charge']) ?> Delivery
            </span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- 8. FINAL CALL TO ACTION -->
<section class="py-16 sm:py-20 bg-brand-900 text-white relative overflow-hidden">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10 space-y-6">
    <h2 class="font-serif font-bold text-3xl sm:text-5xl text-white tracking-tight leading-tight">
      Craving Pure Coconut Water & Fresh Juices?
    </h2>
    <p class="text-base sm:text-lg text-brand-200 max-w-2xl mx-auto font-normal">
      Order now and get cold, delicious, hygienic drinks delivered right to your home, office, or shop in Jaora.
    </p>
    <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-4">
      <a href="#coconuts" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-brand-400 hover:bg-brand-300 text-brand-950 font-bold text-base transition-colors shadow-lg">
        Order Coconut Water
      </a>
      <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $bizWhatsApp) ?>" target="_blank" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-base transition-colors shadow-lg flex items-center justify-center gap-2">
        <span>Order on WhatsApp</span>
      </a>
    </div>
  </div>
</section>

<!-- Carousel Logic in Alpine.js -->
<script>
  function coconutCarousel() {
    return {
      activeIdx: 0,
      totalCards: <?= count($coconutProducts) ?>,
      
      onScroll() {
        const el = this.$refs.track;
        if (!el) return;
        const scrollLeft = el.scrollLeft;
        const cardWidth = el.firstElementChild ? el.firstElementChild.offsetWidth : 360;
        this.activeIdx = Math.round(scrollLeft / cardWidth);
      },

      scrollToCard(idx) {
        const el = this.$refs.track;
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

  function juiceCarousel() {
    return {
      activeIdx: 0,
      totalCards: <?= count($juiceProducts) ?>,

      onScroll() {
        const el = this.$refs.track;
        if (!el) return;
        const scrollLeft = el.scrollLeft;
        const cardWidth = el.firstElementChild ? el.firstElementChild.offsetWidth : 360;
        this.activeIdx = Math.round(scrollLeft / cardWidth);
      },

      scrollToCard(idx) {
        const el = this.$refs.track;
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
