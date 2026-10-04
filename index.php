<?php
/**
 * N.A Fresh Fruits & Coconuts - 3D Swipeable Card Deck Showcase
 * Physical Tinder-style 3D Card Stack Interaction for:
 * 1. 🥥 Coconut Water (Hero 3D Card Deck)
 * 2. 🧃 Fresh Juices (Secondary 3D Card Deck)
 * Built with HTML5, CSS3, Vanilla JavaScript, and GSAP.
 */

require_once __DIR__ . '/config/database.php';

$db = getDB();

// 1. Fetch Coconut Water Products (Hero Category Stack)
$stmt = $db->query("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE c.slug = 'coconut-water' AND p.status = 1
    ORDER BY p.sort_order ASC, p.id ASC
");
$coconutProducts = $stmt->fetchAll();

// 2. Fetch Fresh Juices Products (Secondary Category Stack)
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
<!-- 1. 🥥 COCONUT WATER 3D CARD DECK (HERO SHOWCASE - TINDER STYLE 3D STACK)   -->
<!-- ========================================================================= -->
<section id="coconuts" class="relative pt-8 sm:pt-12 pb-16 sm:pb-20 bg-[#faf9f5] overflow-hidden select-none">
  
  <!-- Subtle Foreground Palm Silhouette Accents (Framing Corners like reference) -->
  <div class="pointer-events-none absolute bottom-0 left-0 -ml-10 -mb-8 w-44 sm:w-60 opacity-20 filter blur-[2px] z-10 hidden sm:block select-none">
    <svg viewBox="0 0 200 200" fill="none" class="w-full h-full text-emerald-800">
      <path d="M10 190 C40 120, 110 90, 190 70 C160 100, 110 130, 40 195 Z" fill="currentColor"/>
      <path d="M5 195 C60 150, 130 130, 185 125 C145 150, 95 175, 20 200 Z" fill="currentColor" opacity="0.7"/>
    </svg>
  </div>
  <div class="pointer-events-none absolute bottom-0 right-0 -mr-10 -mb-8 w-44 sm:w-60 opacity-20 filter blur-[2px] z-10 hidden sm:block select-none">
    <svg viewBox="0 0 200 200" fill="none" class="w-full h-full text-emerald-800 transform -scale-x-100">
      <path d="M10 190 C40 120, 110 90, 190 70 C160 100, 110 130, 40 195 Z" fill="currentColor"/>
      <path d="M5 195 C60 150, 130 130, 185 125 C145 150, 95 175, 20 200 Z" fill="currentColor" opacity="0.7"/>
    </svg>
  </div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20">
    
    <!-- Header: Twin Leaf Emblem & "Fresh & Naturally Refreshing" (Matching Reference) -->
    <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-10">
      <!-- Twin Leaf Icon with subtle divider lines -->
      <div class="flex items-center justify-center gap-3 mb-2">
        <span class="w-10 sm:w-14 h-[1px] bg-emerald-300/80"></span>
        <svg class="w-5 h-5 text-emerald-700 fill-current" viewBox="0 0 24 24">
          <path d="M17 8C8 10 5.9 16.17 3.82 21.34L5.71 22l1-2.3A4.49 4.49 0 008 20C19 20 22 3 22 3c-1 2-8 2-8 2s-3-2-7 0a8.77 8.77 0 00-4 4.54A11.36 11.36 0 0117 8z"/>
        </svg>
        <span class="w-10 sm:w-14 h-[1px] bg-emerald-300/80"></span>
      </div>

      <!-- Main Headline: "Fresh & Naturally Refreshing" with Cursive script -->
      <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
        Fresh & Naturally 
        <span class="font-script text-4xl sm:text-5xl lg:text-6xl text-emerald-700 font-normal ml-1 inline-block -rotate-1">
          Refreshing
        </span>
      </h1>
      <p class="text-xs sm:text-sm text-slate-500 mt-2 font-medium">
        Handpicked tender green coconuts • Swipe card left or right to browse fresh variants
      </p>
    </div>

    <!-- 3D Card Deck Arena (Much Larger & Visually Dominant on Desktop) -->
    <div class="relative max-w-xl sm:max-w-2xl md:max-w-3xl lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1440px] mx-auto flex items-center justify-center min-h-[600px] sm:min-h-[680px] md:min-h-[760px] lg:min-h-[880px] xl:min-h-[960px] 2xl:min-h-[1020px] px-2 sm:px-6 lg:px-12">
      
      <!-- Circular Left Throw Button (Positioned comfortably outside large deck) -->
      <button id="coconut-prev-btn" 
              type="button" 
              class="absolute -left-2 sm:-left-4 md:-left-6 lg:-left-8 xl:-left-12 2xl:-left-16 top-1/2 -translate-y-1/2 z-40 w-11 h-11 sm:w-12 sm:h-12 lg:w-16 lg:h-16 xl:w-18 xl:h-18 rounded-full bg-white shadow-xl lg:shadow-2xl border border-slate-100 flex items-center justify-center text-slate-700 hover:text-emerald-700 hover:scale-110 active:scale-90 transition-all focus:outline-none"
              title="Swipe Left"
              aria-label="Previous Coconut Drink">
        <svg class="w-5 h-5 lg:w-7 lg:h-7 xl:w-8 xl:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
        </svg>
      </button>

      <!-- Circular Right Throw Button -->
      <button id="coconut-next-btn" 
              type="button" 
              class="absolute -right-2 sm:-right-4 md:-right-6 lg:-right-8 xl:-right-12 2xl:-right-16 top-1/2 -translate-y-1/2 z-40 w-11 h-11 sm:w-12 sm:h-12 lg:w-16 lg:h-16 xl:w-18 xl:h-18 rounded-full bg-white shadow-xl lg:shadow-2xl border border-slate-100 flex items-center justify-center text-slate-700 hover:text-emerald-700 hover:scale-110 active:scale-90 transition-all focus:outline-none"
              title="Swipe Right"
              aria-label="Next Coconut Drink">
        <svg class="w-5 h-5 lg:w-7 lg:h-7 xl:w-8 xl:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
      </button>

      <!-- The 3D Physical Stack Container (Visually large, prominent & dominant on desktop: 780px to 1020px) -->
      <div id="coconut-deck" class="deck-stage relative w-[calc(100vw-32px)] max-w-[370px] sm:max-w-[480px] md:max-w-[580px] lg:max-w-[780px] xl:max-w-[900px] 2xl:max-w-[1020px] h-[590px] sm:h-[660px] md:h-[740px] lg:h-[860px] xl:h-[930px] 2xl:h-[980px] select-none perspective-[1400px]">
        <?php foreach ($coconutProducts as $idx => $product): ?>
          <!-- Individual Physical Card in 3D Stack -->
          <div class="deck-card absolute inset-0 cursor-grab active:cursor-grabbing will-change-transform" data-index="<?= $idx ?>">
            <?php include __DIR__ . '/includes/product-card.php'; ?>
          </div>
        <?php endforeach; ?>
      </div>

    </div>

    <!-- Pagination Dots & Cursive Slogan -->
    <div class="mt-6 sm:mt-8 flex flex-col items-center justify-center gap-3">
      <!-- Dynamic Dots populated by card-deck.js -->
      <div id="coconut-dots" class="flex items-center gap-2"></div>

      <!-- Slogan: "Goodness in Every Sip" with green brush stroke underline -->
      <div class="pt-5 sm:pt-7 text-center flex flex-col items-center select-none">
        <span class="font-script text-3xl sm:text-4xl lg:text-5xl text-emerald-800 font-bold tracking-wide leading-none inline-block">
          Goodness in Every Sip
        </span>
        <svg class="w-44 sm:w-56 h-4 text-emerald-600 mt-1" viewBox="0 0 200 20" fill="none">
          <path d="M10 12 Q 100 2 190 14" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
        </svg>
      </div>
    </div>

  </div>
</section>


<!-- ========================================================================= -->
<!-- 2. 🧃 FRESH JUICES 3D CARD DECK (SECONDARY SHOWCASE - TINDER STYLE STACK) -->
<!-- ========================================================================= -->
<section id="juices" class="relative pt-14 sm:pt-18 pb-20 sm:pb-24 bg-white border-t border-slate-200/80 overflow-hidden select-none">
  
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20">
    
    <!-- Header: Twin Leaf Emblem & "Cold-Pressed & Naturally Pure" -->
    <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-10">
      <div class="flex items-center justify-center gap-3 mb-2">
        <span class="w-10 sm:w-14 h-[1px] bg-amber-300/80"></span>
        <svg class="w-5 h-5 text-amber-600 fill-current" viewBox="0 0 24 24">
          <path d="M17 8C8 10 5.9 16.17 3.82 21.34L5.71 22l1-2.3A4.49 4.49 0 008 20C19 20 22 3 22 3c-1 2-8 2-8 2s-3-2-7 0a8.77 8.77 0 00-4 4.54A11.36 11.36 0 0117 8z"/>
        </svg>
        <span class="w-10 sm:w-14 h-[1px] bg-amber-300/80"></span>
      </div>

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

    <!-- 3D Card Deck Arena for Fresh Juices (Much Larger & Visually Dominant on Desktop) -->
    <div class="relative max-w-xl sm:max-w-2xl md:max-w-3xl lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1440px] mx-auto flex items-center justify-center min-h-[600px] sm:min-h-[680px] md:min-h-[760px] lg:min-h-[880px] xl:min-h-[960px] 2xl:min-h-[1020px] px-2 sm:px-6 lg:px-12">
      
      <!-- Circular Left Throw Button -->
      <button id="juice-prev-btn" 
              type="button" 
              class="absolute -left-2 sm:-left-4 md:-left-6 lg:-left-8 xl:-left-12 2xl:-left-16 top-1/2 -translate-y-1/2 z-40 w-11 h-11 sm:w-12 sm:h-12 lg:w-16 lg:h-16 xl:w-18 xl:h-18 rounded-full bg-white shadow-xl lg:shadow-2xl border border-slate-100 flex items-center justify-center text-slate-700 hover:text-amber-700 hover:scale-110 active:scale-90 transition-all focus:outline-none"
              title="Swipe Left"
              aria-label="Previous Juice Drink">
        <svg class="w-5 h-5 lg:w-7 lg:h-7 xl:w-8 xl:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
        </svg>
      </button>

      <!-- Circular Right Throw Button -->
      <button id="juice-next-btn" 
              type="button" 
              class="absolute -right-2 sm:-right-4 md:-right-6 lg:-right-8 xl:-right-12 2xl:-right-16 top-1/2 -translate-y-1/2 z-40 w-11 h-11 sm:w-12 sm:h-12 lg:w-16 lg:h-16 xl:w-18 xl:h-18 rounded-full bg-white shadow-xl lg:shadow-2xl border border-slate-100 flex items-center justify-center text-slate-700 hover:text-amber-700 hover:scale-110 active:scale-90 transition-all focus:outline-none"
              title="Swipe Right"
              aria-label="Next Juice Drink">
        <svg class="w-5 h-5 lg:w-7 lg:h-7 xl:w-8 xl:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
      </button>

      <!-- The 3D Physical Stack Container for Juices (Visually large, prominent & dominant on desktop: 780px to 1020px) -->
      <div id="juice-deck" class="deck-stage relative w-[calc(100vw-32px)] max-w-[370px] sm:max-w-[480px] md:max-w-[580px] lg:max-w-[780px] xl:max-w-[900px] 2xl:max-w-[1020px] h-[590px] sm:h-[660px] md:h-[740px] lg:h-[860px] xl:h-[930px] 2xl:h-[980px] select-none perspective-[1400px]">
        <?php foreach ($juiceProducts as $idx => $product): ?>
          <!-- Individual Physical Card in 3D Stack -->
          <div class="deck-card absolute inset-0 cursor-grab active:cursor-grabbing will-change-transform" data-index="<?= $idx ?>">
            <?php include __DIR__ . '/includes/product-card.php'; ?>
          </div>
        <?php endforeach; ?>
      </div>

    </div>

    <!-- Pagination Dots & Cursive Slogan -->
    <div class="mt-6 sm:mt-8 flex flex-col items-center justify-center gap-3">
      <!-- Dynamic Dots populated by card-deck.js -->
      <div id="juice-dots" class="flex items-center gap-2"></div>

      <!-- Slogan: "Pure Fruit in Every Glass" with amber brush stroke underline -->
      <div class="pt-5 sm:pt-7 text-center flex flex-col items-center select-none">
        <span class="font-script text-3xl sm:text-4xl text-amber-700 font-bold tracking-wide leading-none inline-block">
          Pure Fruit in Every Glass
        </span>
        <svg class="w-44 sm:w-56 h-4 text-amber-500 mt-1" viewBox="0 0 200 20" fill="none">
          <path d="M10 12 Q 100 2 190 14" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
        </svg>
      </div>
    </div>

  </div>
</section>


<!-- ========================================================================= -->
<!-- 3. WHY CHOOSE US (HYGIENE & PRESERVATION PILLARS)                         -->
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

<!-- Include 3D Card Deck Engine Script -->
<script src="/assets/js/card-deck.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // 1. Initialize Hero 3D Card Deck (Coconut Water)
    window.coconutDeck = initCardDeck('#coconut-deck', {
      dotsContainer: '#coconut-dots',
      prevBtn: '#coconut-prev-btn',
      nextBtn: '#coconut-next-btn',
      accentColor: '#15803d'
    });

    // 2. Initialize Secondary 3D Card Deck (Fresh Juices)
    window.juiceDeck = initCardDeck('#juice-deck', {
      dotsContainer: '#juice-dots',
      prevBtn: '#juice-prev-btn',
      nextBtn: '#juice-next-btn',
      accentColor: '#d97706'
    });
  });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
