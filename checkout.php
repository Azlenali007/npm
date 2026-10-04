<?php
/**
 * N.A Fresh Fruits & Coconuts - Checkout Page
 * Real Order Processing & Real Server-Side Razorpay Verification
 */

require_once __DIR__ . '/config/database.php';

$db = getDB();
$cartData = get_cart_data();

if ($cartData['item_count'] === 0) {
    header("Location: /cart.php");
    exit;
}

$user = current_user();

// Fetch Active Delivery Areas
$stmt = $db->query("SELECT * FROM delivery_areas WHERE is_active = 1 ORDER BY delivery_charge ASC");
$deliveryAreas = $stmt->fetchAll();

if (empty($deliveryAreas)) {
    die("No active delivery areas configured. Please contact administrator.");
}

$preselectedAreaId = (int)($_GET['area_id'] ?? $deliveryAreas[0]['id']);

// Razorpay Settings from MySQL
$razorpayEnabled = (int)get_setting('razorpay_enabled', 1);
$razorpayKeyId = get_setting('razorpay_key_id', 'rzp_test_NafreshCoconuts2026');

$appliedCoupon = $_SESSION['applied_coupon'] ?? null;
$discount = $appliedCoupon['discount'] ?? 0;
$couponCode = $appliedCoupon['code'] ?? null;

$error = '';
$orderCreated = null;

// Handle Checkout POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $name = trim($_POST['customer_name'] ?? '');
        $phone = trim($_POST['customer_phone'] ?? '');
        $email = trim($_POST['customer_email'] ?? '');
        $address = trim($_POST['delivery_address'] ?? '');
        $landmark = trim($_POST['landmark'] ?? '');
        $areaId = (int)($_POST['delivery_area_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        $payMethod = strtoupper(trim($_POST['payment_method'] ?? 'COD'));

        if (empty($name) || empty($phone) || empty($address)) {
            $error = 'Please fill in your name, mobile number, and complete delivery address.';
        } elseif (strlen($phone) < 10) {
            $error = 'Please enter a valid 10-digit mobile number.';
        } else {
            // Find selected delivery area
            $stmt = $db->prepare("SELECT * FROM delivery_areas WHERE id = ? AND is_active = 1");
            $stmt->execute([$areaId]);
            $area = $stmt->fetch();

            if (!$area) {
                $error = 'Selected delivery area is currently inactive or not available.';
            } elseif ($cartData['subtotal'] < $area['min_order_amount']) {
                $error = 'Minimum order for ' . $area['area_name'] . ' is ' . format_price($area['min_order_amount']) . '.';
            } else {
                // Calculate totals
                $subtotal = (float)$cartData['subtotal'];
                $deliveryCharge = (float)$area['delivery_charge'];
                $totalAmount = max(0, ($subtotal + $deliveryCharge) - $discount);

                $orderNumber = 'NAF-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

                $initialPayStatus = 'Pending';
                $initialOrderStatus = ($payMethod === 'COD') ? 'Confirmed' : 'Pending';

                // Insert into orders table
                $insOrder = $db->prepare("
                    INSERT INTO orders (
                        order_number, user_id, customer_name, customer_phone, customer_email,
                        delivery_address, landmark, delivery_area_id, area_name, delivery_charge,
                        subtotal, discount_amount, coupon_code, total_amount, payment_method,
                        payment_status, order_status, notes, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");

                $insOrder->execute([
                    $orderNumber,
                    $user['id'] ?? null,
                    $name,
                    $phone,
                    $email,
                    $address,
                    $landmark,
                    $area['id'],
                    $area['area_name'],
                    $deliveryCharge,
                    $subtotal,
                    $discount,
                    $couponCode,
                    $totalAmount,
                    $payMethod,
                    $initialPayStatus,
                    $initialOrderStatus,
                    $notes
                ]);

                $orderId = (int)$db->lastInsertId();

                // Insert Order Items
                $insItem = $db->prepare("
                    INSERT INTO order_items (order_id, product_id, product_name, product_image, quantity, unit_price, total_price)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $invoiceItemsData = [];
                foreach ($cartData['items'] as $item) {
                    $itemTotal = (float)$item['unit_price'] * (int)$item['quantity'];
                    $insItem->execute([
                        $orderId,
                        $item['product_id'],
                        $item['name'],
                        $item['image_url'],
                        $item['quantity'],
                        $item['unit_price'],
                        $itemTotal
                    ]);
                    $invoiceItemsData[] = [
                        'name' => $item['name'],
                        'unit_size' => $item['unit_size'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total' => $itemTotal
                    ];
                }

                // Generate Invoice Record
                $invNumber = 'INV-' . date('Ymd') . '-' . str_pad($orderId, 4, '0', STR_PAD_LEFT);
                $customerDetails = [
                    'name' => $name,
                    'phone' => $phone,
                    'email' => $email,
                    'address' => $address,
                    'landmark' => $landmark,
                    'area' => $area['area_name']
                ];
                $insInv = $db->prepare("
                    INSERT INTO invoices (
                        invoice_number, order_id, invoice_date, customer_details_json, items_json,
                        subtotal, delivery_charge, discount, total_amount, payment_status, created_at
                    ) VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $insInv->execute([
                    $invNumber,
                    $orderId,
                    json_encode($customerDetails),
                    json_encode($invoiceItemsData),
                    $subtotal,
                    $deliveryCharge,
                    $discount,
                    $totalAmount,
                    $initialPayStatus
                ]);

                // Clear Shopping Cart in DB
                if (!empty($cartData['cart_id'])) {
                    $db->prepare("DELETE FROM cart_items WHERE cart_id = ?")->execute([$cartData['cart_id']]);
                }
                unset($_SESSION['applied_coupon']);

                if ($payMethod === 'COD') {
                    // Add real notification
                    if (!empty($user['id'])) {
                        add_user_notification(
                            $user['id'], 
                            'Order #' . $orderNumber . ' Placed!', 
                            'Your fresh drinks order has been confirmed. Our Jaora team is preparing it now.',
                            '/order-success.php?id=' . $orderId,
                            'order'
                        );
                    }
                    header("Location: /order-success.php?id=" . $orderId);
                    exit;
                } else {
                    // Razorpay Online Flow
                    $orderCreated = [
                        'id' => $orderId,
                        'order_number' => $orderNumber,
                        'total_amount' => $totalAmount,
                        'customer_name' => $name,
                        'customer_phone' => $phone,
                        'customer_email' => $email
                    ];
                }
            }
        }
    }
}

$pageTitle = 'Secure Checkout – N.A Fresh Fruits & Coconuts, Jaora';
$pageDesc = 'Place your tender coconut and fresh juice order with Cash on Delivery or Razorpay.';

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-coconut-cream min-h-screen py-10" 
     x-data="checkoutPage(<?= json_encode($deliveryAreas) ?>, <?= $preselectedAreaId ?>, <?= (float)$cartData['subtotal'] ?>, <?= (float)$discount ?>)">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="mb-8">
      <h1 class="font-serif font-bold text-3xl sm:text-4xl text-brand-950 tracking-tight">
        Checkout & Doorstep Delivery
      </h1>
      <p class="text-xs text-slate-500 mt-1">Direct preparation & dispatch from Station Road, Jaora.</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-2">
        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span><?= e($error) ?></span>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      
      <!-- Checkout Form (Left Column) -->
      <div class="lg:col-span-8 bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-xs">
        <form method="POST" id="checkout-form" class="space-y-6">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          
          <!-- Contact Details -->
          <div>
            <h2 class="font-serif font-bold text-lg text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
              <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-800 text-xs font-bold flex items-center justify-center">1</span>
              <span>Customer Information</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Full Name *</label>
                <input type="text" name="customer_name" required value="<?= e($user['name'] ?? '') ?>" placeholder="e.g. Imran Patel" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Mobile Number *</label>
                <input type="tel" name="customer_phone" required value="<?= e($user['phone'] ?? '') ?>" placeholder="10-digit mobile number" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
              </div>
              <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Address (Optional for Invoice)</label>
                <input type="email" name="customer_email" value="<?= e($user['email'] ?? '') ?>" placeholder="yourname@gmail.com" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
              </div>
            </div>
          </div>

          <!-- Delivery Address & Area Selection -->
          <div class="pt-4">
            <h2 class="font-serif font-bold text-lg text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
              <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-800 text-xs font-bold flex items-center justify-center">2</span>
              <span>Jaora Delivery Location</span>
            </h2>
            
            <div class="space-y-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Select Your Area *</label>
                <select name="delivery_area_id" 
                        x-model="selectedAreaId" 
                        @change="onAreaChange()"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none bg-slate-50">
                  <template x-for="area in deliveryAreas" :key="area.id">
                    <option :value="area.id" x-text="area.area_name + ' – Delivery ₹' + area.delivery_charge + ' (' + area.est_delivery_time + ')'"></option>
                  </template>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Complete Street Address / House / Shop No. *</label>
                <textarea name="delivery_address" required rows="2" placeholder="e.g. Flat 204, Opposite Bank of Baroda, Main Road" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"><?= e($user['address'] ?? '') ?></textarea>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nearby Landmark</label>
                  <input type="text" name="landmark" value="<?= e($user['landmark'] ?? '') ?>" placeholder="e.g. Near Jain Temple, Azad Chowk" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Special Instructions / Drink Notes</label>
                  <input type="text" name="notes" placeholder="e.g. Extra cold please, no ice in orange juice" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Method Selection -->
          <div class="pt-4">
            <h2 class="font-serif font-bold text-lg text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
              <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-800 text-xs font-bold flex items-center justify-center">3</span>
              <span>Payment Option</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Cash on Delivery Option -->
              <label class="relative flex items-start gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-colors"
                     :class="payMethod === 'COD' ? 'border-brand-600 bg-brand-50/50' : 'border-slate-200 bg-white hover:border-slate-300'">
                <input type="radio" name="payment_method" value="COD" x-model="payMethod" class="mt-1 text-brand-600 focus:ring-brand-500">
                <div>
                  <span class="font-bold text-sm text-slate-900 block">Cash on Delivery (COD)</span>
                  <span class="text-xs text-slate-500 block mt-0.5">Pay with cash or UPI QR upon receiving your order in Jaora.</span>
                </div>
              </label>

              <!-- Razorpay Online Option -->
              <?php if ($razorpayEnabled): ?>
              <label class="relative flex items-start gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-colors"
                     :class="payMethod === 'RAZORPAY' ? 'border-brand-600 bg-brand-50/50' : 'border-slate-200 bg-white hover:border-slate-300'">
                <input type="radio" name="payment_method" value="RAZORPAY" x-model="payMethod" class="mt-1 text-brand-600 focus:ring-brand-500">
                <div>
                  <div class="flex items-center gap-2">
                    <span class="font-bold text-sm text-slate-900 block">Razorpay Online</span>
                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">UPI / GPay / Cards</span>
                  </div>
                  <span class="text-xs text-slate-500 block mt-0.5">100% Secure instant checkout via Google Pay, PhonePe, Paytm, or Netbanking.</span>
                </div>
              </label>
              <?php endif; ?>
            </div>
          </div>

          <!-- Submit Order -->
          <div class="pt-6 border-t border-slate-100">
            <button type="submit" 
                    class="w-full py-4 rounded-2xl bg-brand-700 hover:bg-brand-800 text-white font-bold text-base shadow-md transition-colors flex items-center justify-center gap-2">
              <span x-text="payMethod === 'COD' ? 'Confirm Order with Cash on Delivery' : 'Proceed to Razorpay Secure Payment'"></span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
          </div>

        </form>
      </div>

      <!-- Order Review Sidebar (Right Column) -->
      <div class="lg:col-span-4 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-5">
        <h3 class="font-serif font-bold text-lg text-slate-900 pb-3 border-b border-slate-100">
          Order Items (<?= $cartData['item_count'] ?>)
        </h3>

        <!-- Items Preview -->
        <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
          <?php foreach ($cartData['items'] as $item): ?>
            <div class="flex items-center gap-3">
              <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['name']) ?>" class="w-12 h-12 rounded-xl object-cover bg-slate-100 shrink-0">
              <div class="flex-1 min-w-0">
                <h4 class="font-bold text-xs text-slate-900 truncate"><?= e($item['name']) ?></h4>
                <div class="text-[11px] text-slate-500">Qty: <?= (int)$item['quantity'] ?> × <?= format_price($item['unit_price']) ?></div>
              </div>
              <div class="font-bold text-xs text-slate-900">
                <?= format_price($item['unit_price'] * $item['quantity']) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Total Calculation Breakdown -->
        <div class="pt-4 border-t border-slate-100 space-y-2 text-xs text-slate-600">
          <div class="flex items-center justify-between">
            <span>Items Total</span>
            <span class="font-bold text-slate-900"><?= format_price($cartData['subtotal']) ?></span>
          </div>
          <div class="flex items-center justify-between">
            <span x-text="'Delivery: ' + currentAreaName"></span>
            <span class="font-bold text-slate-900" x-text="'₹' + currentDeliveryCharge"></span>
          </div>
          <?php if ($discount > 0): ?>
            <div class="flex items-center justify-between text-emerald-700 font-bold">
              <span>Coupon Discount (<?= e($couponCode) ?>)</span>
              <span>- <?= format_price($discount) ?></span>
            </div>
          <?php endif; ?>
          <div class="pt-3 border-t border-slate-200 flex items-baseline justify-between text-slate-900">
            <span class="font-serif font-bold text-base">To Pay</span>
            <span class="font-serif font-bold text-2xl text-brand-900" x-text="'₹' + grandTotal"></span>
          </div>
        </div>

        <!-- Freshness Promise -->
        <div class="p-3.5 rounded-2xl bg-brand-50 border border-brand-100 text-[11px] text-brand-800 space-y-1">
          <div class="font-bold flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-brand-500"></span>
            <span>Hygiene Guaranteed</span>
          </div>
          <p>Dispatched in cold insulated thermal bags to ensure refreshing temperature upon delivery in Jaora.</p>
        </div>

      </div>

    </div>

  </div>
</div>

<!-- Razorpay Modal Integration (Triggered if payment method was RAZORPAY) -->
<?php if ($orderCreated): ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const options = {
      key: "<?= e($razorpayKeyId) ?>",
      amount: <?= round($orderCreated['total_amount'] * 100) ?>, // in paise
      currency: "INR",
      name: "<?= e(get_setting('business_name', 'N.A Fresh Fruits & Coconuts')) ?>",
      description: "Order #<?= e($orderCreated['order_number']) ?>",
      image: "https://images.unsplash.com/photo-1544378730-8b5104b18790?auto=format&fit=crop&w=200&q=80",
      prefill: {
        name: "<?= e($orderCreated['customer_name']) ?>",
        contact: "<?= e($orderCreated['customer_phone']) ?>",
        email: "<?= e($orderCreated['customer_email']) ?>"
      },
      theme: {
        color: "#14532d"
      },
      handler: async function (response) {
        // Send payment signature to backend for REAL verification
        try {
          const verifyRes = await fetch('/api/verify-razorpay.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              order_id: <?= (int)$orderCreated['id'] ?>,
              razorpay_payment_id: response.razorpay_payment_id,
              razorpay_order_id: response.razorpay_order_id || 'RZP-<?= $orderCreated['order_number'] ?>',
              razorpay_signature: response.razorpay_signature || ''
            })
          });
          const verifyData = await verifyRes.json();
          if (verifyData.success) {
            window.location.href = '/order-success.php?id=<?= $orderCreated['id'] ?>';
          } else {
            alert('Payment verification failed: ' + verifyData.message);
          }
        } catch (e) {
          alert('Network verification error. Please contact store.');
        }
      },
      modal: {
        ondismiss: function () {
          alert('Payment cancelled. Your order is pending in your account.');
          window.location.href = '/account.php#orders';
        }
      }
    };
    const rzp = new Razorpay(options);
    rzp.open();
  });
</script>
<?php endif; ?>

<script>
  function checkoutPage(areas, initialAreaId, subtotal, discount) {
    return {
      deliveryAreas: areas,
      selectedAreaId: initialAreaId,
      subtotal: subtotal,
      discount: discount,
      payMethod: 'COD',
      currentDeliveryCharge: 20,
      currentAreaName: '',

      init() {
        this.onAreaChange();
      },

      onAreaChange() {
        const area = this.deliveryAreas.find(a => a.id == this.selectedAreaId);
        if (area) {
          this.currentDeliveryCharge = parseFloat(area.delivery_charge);
          this.currentAreaName = area.area_name;
        }
      },

      get grandTotal() {
        const t = (this.subtotal + this.currentDeliveryCharge) - this.discount;
        return Math.max(0, t);
      }
    };
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
