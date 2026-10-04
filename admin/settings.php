<?php
/**
 * N.A Fresh Fruits & Coconuts - Admin Global Settings
 * Controls Store details, Zomato ON/OFF, Razorpay Keys, Operating Hours & Push Notifications
 */

$adminTitle = 'Store Configuration & Integrations';
require_once __DIR__ . '/header.php';

$db = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $settingsToUpdate = [
        // General
        'business_name' => trim($_POST['business_name'] ?? 'N.A Fresh Fruits & Coconuts'),
        'tagline' => trim($_POST['tagline'] ?? ''),
        'operating_hours' => trim($_POST['operating_hours'] ?? '7:00 AM – 10:30 PM Daily'),
        
        // Contact & Address
        'phone' => trim($_POST['phone'] ?? ''),
        'whatsapp' => trim($_POST['whatsapp'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),

        // Hero Banner
        'hero_headline' => trim($_POST['hero_headline'] ?? 'Freshness You Can Taste'),
        'hero_subtitle' => trim($_POST['hero_subtitle'] ?? 'Fresh Coconut Water & Juices, prepared with care in Jaora.'),

        // Zomato Integration
        'zomato_enabled' => isset($_POST['zomato_enabled']) ? '1' : '0',
        'zomato_url' => trim($_POST['zomato_url'] ?? ''),

        // Razorpay Integration
        'razorpay_enabled' => isset($_POST['razorpay_enabled']) ? '1' : '0',
        'razorpay_key_id' => trim($_POST['razorpay_key_id'] ?? ''),
        'razorpay_key_secret' => trim($_POST['razorpay_key_secret'] ?? ''),
        'razorpay_mode' => trim($_POST['razorpay_mode'] ?? 'test'),

        // Push Notifications
        'push_notifications_enabled' => isset($_POST['push_notifications_enabled']) ? '1' : '0',

        // Social Links
        'instagram_url' => trim($_POST['instagram_url'] ?? ''),
        'facebook_url' => trim($_POST['facebook_url'] ?? '')
    ];

    $stmt = $db->prepare("
        INSERT INTO settings (setting_key, setting_value, setting_group)
        VALUES (?, ?, 'general')
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
    ");

    foreach ($settingsToUpdate as $key => $val) {
        $stmt->execute([$key, $val]);
    }

    $msg = 'All store settings and integrations updated successfully!';
}

// Fetch All Current Settings
$stmt = $db->query("SELECT setting_key, setting_value FROM settings");
$currentSettings = [];
while ($row = $stmt->fetch()) {
    $currentSettings[$row['setting_key']] = $row['setting_value'];
}

function s($k, $d = '') {
    global $currentSettings;
    return $currentSettings[$k] ?? $d;
}
?>

<div class="space-y-6 max-w-5xl">
  
  <div>
    <h1 class="font-bold text-2xl text-white">Store Settings & Live Integrations</h1>
    <p class="text-xs text-slate-400 mt-1">Configure business profile, payment gateway credentials, and food delivery channels.</p>
  </div>

  <?php if ($msg): ?>
    <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-xs font-bold">
      <?= e($msg) ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="space-y-8">
    
    <!-- 1. ZOMATO INTEGRATION (CRITICAL REQUIREMENT) -->
    <div class="p-6 rounded-3xl bg-slate-950 border border-slate-800 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-slate-800">
        <div>
          <h2 class="font-bold text-base text-white flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
            <span>Zomato Food Delivery Integration</span>
          </h2>
          <p class="text-xs text-slate-400 mt-0.5">Toggle Zomato ordering button on the storefront navbar and footer.</p>
        </div>

        <label class="relative inline-flex items-center cursor-pointer">
          <input type="checkbox" name="zomato_enabled" value="1" <?= s('zomato_enabled', '1') === '1' ? 'checked' : '' ?> class="sr-only peer">
          <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
          <span class="ml-3 text-xs font-bold text-slate-300">
            <?= s('zomato_enabled', '1') === '1' ? 'ENABLED (ON)' : 'DISABLED (OFF)' ?>
          </span>
        </label>
      </div>

      <div class="grid grid-cols-1 gap-4 text-xs">
        <div>
          <label class="block font-bold text-slate-300 uppercase mb-1">Configured Zomato Store Destination URL</label>
          <input type="url" name="zomato_url" value="<?= e(s('zomato_url', 'https://www.zomato.com/jaora/na-fresh-fruits-coconuts')) ?>" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono">
          <p class="text-[11px] text-slate-500 mt-1">When ON, customers see this destination button. When OFF, all Zomato references are completely hidden.</p>
        </div>
      </div>
    </div>

    <!-- 2. RAZORPAY PAYMENT GATEWAY (REAL CREDENTIALS) -->
    <div class="p-6 rounded-3xl bg-slate-950 border border-slate-800 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-slate-800">
        <div>
          <h2 class="font-bold text-base text-white flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
            <span>Razorpay Online Gateway</span>
          </h2>
          <p class="text-xs text-slate-400 mt-0.5">Real UPI, PhonePe, GPay & Card payments with server-side HMAC validation.</p>
        </div>

        <label class="relative inline-flex items-center cursor-pointer">
          <input type="checkbox" name="razorpay_enabled" value="1" <?= s('razorpay_enabled', '1') === '1' ? 'checked' : '' ?> class="sr-only peer">
          <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
          <span class="ml-3 text-xs font-bold text-slate-300">
            <?= s('razorpay_enabled', '1') === '1' ? 'ENABLED (ON)' : 'DISABLED (OFF)' ?>
          </span>
        </label>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
        <div>
          <label class="block font-bold text-slate-300 uppercase mb-1">Key ID (Public)</label>
          <input type="text" name="razorpay_key_id" value="<?= e(s('razorpay_key_id', 'rzp_test_NafreshCoconuts2026')) ?>" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono">
        </div>

        <div>
          <label class="block font-bold text-slate-300 uppercase mb-1">Key Secret (Server Only)</label>
          <input type="password" name="razorpay_key_secret" value="<?= e(s('razorpay_key_secret', 's3cr3tKeyNafreshJaora2026')) ?>" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono">
          <p class="text-[10px] text-slate-500 mt-0.5">Never exposed to frontend.</p>
        </div>

        <div>
          <label class="block font-bold text-slate-300 uppercase mb-1">Environment Mode</label>
          <select name="razorpay_mode" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white">
            <option value="test" <?= s('razorpay_mode') === 'test' ? 'selected' : '' ?>>Test / Sandbox Mode</option>
            <option value="live" <?= s('razorpay_mode') === 'live' ? 'selected' : '' ?>>Production / Live Mode</option>
          </select>
        </div>
      </div>
    </div>

    <!-- 3. STORE PROFILE & LOCATION -->
    <div class="p-6 rounded-3xl bg-slate-950 border border-slate-800 space-y-4 text-xs">
      <h2 class="font-bold text-base text-white pb-3 border-b border-slate-800">Store Identity & Contact</h2>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block font-bold text-slate-300 uppercase mb-1">Business Name *</label>
          <input type="text" name="business_name" required value="<?= e(s('business_name', 'N.A Fresh Fruits & Coconuts')) ?>" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white">
        </div>
        <div>
          <label class="block font-bold text-slate-300 uppercase mb-1">Tagline</label>
          <input type="text" name="tagline" value="<?= e(s('tagline', 'Fresh Coconut Water & Juices, Prepared with Care')) ?>" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block font-bold text-slate-300 uppercase mb-1">Phone Number</label>
          <input type="text" name="phone" value="<?= e(s('phone', '+91 98260 12345')) ?>" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white">
        </div>
        <div>
          <label class="block font-bold text-slate-300 uppercase mb-1">WhatsApp Order Number</label>
          <input type="text" name="whatsapp" value="<?= e(s('whatsapp', '919826012345')) ?>" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white">
        </div>
        <div>
          <label class="block font-bold text-slate-300 uppercase mb-1">Operating Hours</label>
          <input type="text" name="operating_hours" value="<?= e(s('operating_hours', '7:00 AM – 10:30 PM Daily')) ?>" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white">
        </div>
      </div>

      <div>
        <label class="block font-bold text-slate-300 uppercase mb-1">Physical Shop Address (Jaora)</label>
        <textarea name="address" rows="2" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white"><?= e(s('address', 'Shop No. 4, Station Road, Opp. Municipal Garden, Jaora, MP 457226')) ?></textarea>
      </div>
    </div>

    <!-- 4. HERO BANNER CONTENT -->
    <div class="p-6 rounded-3xl bg-slate-950 border border-slate-800 space-y-4 text-xs">
      <h2 class="font-bold text-base text-white pb-3 border-b border-slate-800">Landing Page Hero Banner</h2>
      <div>
        <label class="block font-bold text-slate-300 uppercase mb-1">Hero Headline</label>
        <input type="text" name="hero_headline" value="<?= e(s('hero_headline', 'Freshness You Can Taste')) ?>" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white">
      </div>
      <div>
        <label class="block font-bold text-slate-300 uppercase mb-1">Hero Subtitle</label>
        <textarea name="hero_subtitle" rows="2" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white"><?= e(s('hero_subtitle', 'Fresh Coconut Water & Juices, prepared with care in Jaora.')) ?></textarea>
      </div>
    </div>

    <!-- Submit Button -->
    <div class="flex justify-end pt-2">
      <button type="submit" name="save_settings" class="px-8 py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-colors">
        Save All Settings & Integrations
      </button>
    </div>

  </form>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
