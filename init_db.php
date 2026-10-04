<?php
/**
 * N.A Fresh Fruits & Coconuts - Database Initializer & Seeder
 * Location: Jaora, Madhya Pradesh, India
 */

require_once __DIR__ . '/config/database.php';

try {
    $db = getDB();

    // 1. Run schema.sql
    $sql = file_get_contents(__DIR__ . '/config/schema.sql');
    $db->exec($sql);

    // 2. Seed Admin User
    $stmt = $db->query("SELECT COUNT(*) FROM admins");
    if ($stmt->fetchColumn() == 0) {
        $adminPass = password_hash('admin123', PASSWORD_BCRYPT);
        $insertAdmin = $db->prepare("
            INSERT INTO admins (username, email, password, full_name, role) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $insertAdmin->execute(['admin', 'admin@nafresh.in', $adminPass, 'N.A Store Manager', 'admin']);
    }

    // 3. Seed Settings
    $defaultSettings = [
        ['business_name', 'N.A Fresh Fruits & Coconuts', 'general'],
        ['tagline', 'Fresh Coconut Water & Juices, Prepared with Care', 'general'],
        ['phone', '+91 98260 12345', 'contact'],
        ['whatsapp', '919826012345', 'contact'],
        ['email', 'contact@nafreshfruits.in', 'contact'],
        ['address', 'Shop No. 4, Station Road, Opp. Municipal Garden, Jaora, MP 457226', 'contact'],
        ['city', 'Jaora', 'general'],
        ['state', 'Madhya Pradesh', 'general'],
        ['currency_symbol', '₹', 'general'],
        ['currency_code', 'INR', 'general'],
        ['min_order_amount', '100', 'orders'],
        ['default_delivery_charge', '20', 'orders'],
        ['hero_headline', 'Freshness You Can Taste', 'hero'],
        ['hero_subtitle', 'Fresh Coconut Water & Juices, prepared with care in Jaora.', 'hero'],
        ['hero_badge', '100% Raw & Natural • Cold-Pressed Daily', 'hero'],
        ['zomato_enabled', '1', 'zomato'],
        ['zomato_url', 'https://www.zomato.com/jaora/na-fresh-fruits-coconuts', 'zomato'],
        ['razorpay_enabled', '1', 'razorpay'],
        ['razorpay_key_id', 'rzp_test_NafreshCoconuts2026', 'razorpay'],
        ['razorpay_key_secret', 's3cr3tKeyNafreshJaora2026', 'razorpay'],
        ['razorpay_mode', 'test', 'razorpay'],
        ['push_notifications_enabled', '1', 'notifications'],
        ['operating_hours', '7:00 AM – 10:30 PM (Mon - Sun)', 'general'],
        ['shop_open_status', '1', 'general'],
        ['instagram_url', 'https://instagram.com/nafreshfruitsjaora', 'social'],
        ['facebook_url', 'https://facebook.com/nafreshfruitsjaora', 'social']
    ];

    $checkSetting = $db->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
    $insertSetting = $db->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)");
    foreach ($defaultSettings as $s) {
        $checkSetting->execute([$s[0]]);
        if ($checkSetting->fetchColumn() == 0) {
            $insertSetting->execute([$s[0], $s[1], $s[2]]);
        }
    }

    // 4. Seed Delivery Areas in Jaora
    $stmt = $db->query("SELECT COUNT(*) FROM delivery_areas");
    if ($stmt->fetchColumn() == 0) {
        $areas = [
            ['Station Road & Station Area', '457226', 15.00, 100.00, '15-25 mins', 1],
            ['Azad Chowk & Main Market', '457226', 15.00, 100.00, '20-25 mins', 1],
            ['Bada Bazaar & Sarafa', '457226', 15.00, 100.00, '20-30 mins', 1],
            ['Housing Board Colony', '457226', 20.00, 120.00, '25-35 mins', 1],
            ['Shastri Colony & Civil Lines', '457226', 20.00, 120.00, '25-35 mins', 1],
            ['Piploda Road Colony Area', '457226', 25.00, 150.00, '30-40 mins', 1],
            ['Hussain Tekri Road Area', '457226', 25.00, 150.00, '30-40 mins', 1],
            ['Ratlam Naka & By-pass Link', '457226', 25.00, 150.00, '35-45 mins', 1]
        ];
        $insertArea = $db->prepare("
            INSERT INTO delivery_areas (area_name, pincode, delivery_charge, min_order_amount, est_delivery_time, is_active)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        foreach ($areas as $a) {
            $insertArea->execute($a);
        }
    }

    // 5. Seed Categories
    $stmt = $db->query("SELECT COUNT(*) FROM categories");
    if ($stmt->fetchColumn() == 0) {
        $categories = [
            ['Fresh Coconut Water', 'coconut-water', 'Direct-sourced sweet green coconuts, cold-cut upon your order.', 'https://images.unsplash.com/photo-1544378730-8b5104b18790?auto=format&fit=crop&w=800&q=80', 1, 1],
            ['Freshly Prepared Juices', 'fresh-juices', '100% raw, cold-pressed and hand-extracted juices with zero artificial colors or added sugar.', 'https://images.unsplash.com/photo-1613478223719-2ab802602423?auto=format&fit=crop&w=800&q=80', 2, 1],
            ['Fruit Platters & Bowls', 'fruit-bowls', 'Hygienically washed, chilled seasonal fruit cuts with lemon-mint rock salt dressing.', 'https://images.unsplash.com/photo-1568571780765-9276ac8b75a2?auto=format&fit=crop&w=800&q=80', 3, 1],
            ['Wellness & Immunity Boosters', 'wellness-boosters', 'Potent ginger, amla, mosambi and mint energy cleansers.', 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=800&q=80', 4, 1]
        ];
        $insertCat = $db->prepare("
            INSERT INTO categories (name, slug, description, image, sort_order, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        foreach ($categories as $c) {
            $insertCat->execute($c);
        }
    }

    // 6. Seed Products
    $stmt = $db->query("SELECT COUNT(*) FROM products");
    if ($stmt->fetchColumn() == 0) {
        // Fetch category IDs
        $catStmt = $db->query("SELECT id, slug FROM categories");
        $catMap = [];
        while ($row = $catStmt->fetch()) {
            $catMap[$row['slug']] = $row['id'];
        }

        $products = [
            // Coconut Water Products (Primary Showcase)
            [
                $catMap['coconut-water'],
                'Royal Tender Green Coconut (Fresh Cut)',
                'royal-tender-green-coconut',
                'Sweet natural electrolyte water with tender malai',
                'Handpicked tender green coconut from prime coastal groves. Cut and prepared immediately before dispatch. Naturally sweet, packed with potassium and essential electrolytes. Includes eco-friendly straw.',
                60.00,
                70.00,
                '1 Whole Coconut (~350ml Water)',
                1, 1, 50,
                'https://images.unsplash.com/photo-1525385133512-2f3bdd039054?auto=format&fit=crop&w=800&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1525385133512-2f3bdd039054?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1544378730-8b5104b18790?auto=format&fit=crop&w=800&q=80'
                ]),
                1, 1, 1, 1
            ],
            [
                $catMap['coconut-water'],
                'Pure Coconut Water Bottled (500ml Jumbo)',
                'pure-coconut-water-bottled-500ml',
                'Raw, 100% natural, chilled on crushed ice',
                'Freshly extracted coconut water from two whole tender coconuts, strained into a food-grade sealed bottle on ice. No added sugar, water, or preservatives. Ready to drink right away.',
                80.00,
                95.00,
                '500ml Sealed Bottle',
                1, 1, 40,
                'https://images.unsplash.com/photo-1544378730-8b5104b18790?auto=format&fit=crop&w=800&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1544378730-8b5104b18790?auto=format&fit=crop&w=800&q=80'
                ]),
                1, 1, 2, 1
            ],
            [
                $catMap['coconut-water'],
                'Tender Coconut with Fresh Malai Scoop (Bowl)',
                'tender-coconut-malai-scoop-bowl',
                'Sweet coconut water + fresh creamy soft malai cup',
                'The ultimate coconut treat: One bottle of fresh pure water paired with a separate hygienic tub of soft, melt-in-mouth tender coconut malai scoop. Freshly scooped on order.',
                95.00,
                110.00,
                '350ml Water + 100g Malai',
                1, 1, 35,
                'https://images.unsplash.com/photo-1502741224143-90386d7f8c82?auto=format&fit=crop&w=800&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1502741224143-90386d7f8c82?auto=format&fit=crop&w=800&q=80'
                ]),
                1, 0, 3, 1
            ],
            [
                $catMap['coconut-water'],
                'Chilled Pre-Slit Coconut (Party & Travel)',
                'chilled-pre-slit-coconut',
                'Pre-drilled with silicone plug & sealed straw',
                'Hygienically cleaned and pre-cut with a leak-proof opening. Easy to pop open anywhere in Jaora. Keep it in your car or desk for a chilled instant refresh.',
                70.00,
                80.00,
                '1 Whole Coconut',
                1, 1, 45,
                'https://images.unsplash.com/photo-1517420879524-86d64ac2f339?auto=format&fit=crop&w=800&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1517420879524-86d64ac2f339?auto=format&fit=crop&w=800&q=80'
                ]),
                1, 0, 4, 1
            ],
            [
                $catMap['coconut-water'],
                'Coconut Water with Pudina & Nimbu Twist',
                'coconut-water-pudina-nimbu-twist',
                'Hand-muddled fresh mint, rock salt and lemon zest',
                'Refreshing artisanal coconut blend: pure raw coconut water shaken with fresh handpicked mint leaves, lemon juice, and a hint of Kala Namak rock salt.',
                75.00,
                85.00,
                '350ml Chilled Bottle',
                1, 1, 30,
                'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=800&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=800&q=80'
                ]),
                1, 1, 5, 1
            ],

            // Fresh Juices (Secondary Showcase)
            [
                $catMap['fresh-juices'],
                'Pure Valencia Orange Juice (No Added Sugar)',
                'pure-valencia-orange-juice',
                '100% freshly pressed sun-ripened orange nectar',
                'Cold-extracted from premium Nagpur and Valencia oranges. Unfiltered with rich citrus pulp. Packed with Vitamin C. No ice dilution unless requested.',
                70.00,
                85.00,
                '300ml Chilled Glass/Bottle',
                1, 1, 40,
                'https://images.unsplash.com/photo-1613478223719-2ab802602423?auto=format&fit=crop&w=800&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1613478223719-2ab802602423?auto=format&fit=crop&w=800&q=80'
                ]),
                1, 1, 6, 1
            ],
            [
                $catMap['fresh-juices'],
                'Fresh Mosambi (Sweet Lime) Juice',
                'fresh-mosambi-sweet-lime-juice',
                'Freshly pressed with roasted cumin & rock salt',
                'The beloved Jaora refresher! Juicy sweet limes pressed on order to prevent any bitterness. Served chilled with our signature blend of roasted jeera and black salt.',
                60.00,
                70.00,
                '300ml Chilled Glass/Bottle',
                1, 1, 50,
                'https://images.unsplash.com/photo-1589733955941-5eeaf752f6dd?auto=format&fit=crop&w=800&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1589733955941-5eeaf752f6dd?auto=format&fit=crop&w=800&q=80'
                ]),
                1, 1, 7, 1
            ],
            [
                $catMap['fresh-juices'],
                'Royal Alphonso Mango Nectar (Seasonal Special)',
                'royal-alphonso-mango-nectar',
                'Rich, thick, aromatic cold-pressed mango juice',
                'Velvety mango juice prepared from tree-ripened select mangoes. Rich in flavor and aroma, naturally thick and sweet with zero artificial essence.',
                80.00,
                99.00,
                '350ml Chilled Bottle',
                1, 1, 35,
                'https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&w=800&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&w=800&q=80'
                ]),
                1, 1, 8, 1
            ],
            [
                $catMap['fresh-juices'],
                'Queen Pineapple Mint Fresh Juice',
                'queen-pineapple-mint-fresh-juice',
                'Tangy tropical pineapple with garden mint infusion',
                'Golden ripe pineapple cold-pressed with sweet aroma, strained and blended with handpicked fresh mint leaves and a light hint of ginger.',
                65.00,
                80.00,
                '300ml Chilled Bottle',
                1, 1, 30,
                'https://images.unsplash.com/photo-1550583724-b2692b85b150?auto=format&fit=crop&w=800&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1550583724-b2692b85b150?auto=format&fit=crop&w=800&q=80'
                ]),
                1, 0, 9, 1
            ],
            [
                $catMap['fresh-juices'],
                'Crisp Himachal Apple Cold-Extract',
                'crisp-himachal-apple-cold-extract',
                '100% pure apple extract with natural sweetness',
                'Extracted slowly from crisp red Himachal apples to preserve natural enzymes, polyphenols and vibrant color. Light, refreshing and pure.',
                85.00,
                100.00,
                '300ml Chilled Glass/Bottle',
                1, 1, 30,
                'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?auto=format&fit=crop&w=800&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?auto=format&fit=crop&w=800&q=80'
                ]),
                1, 0, 10, 1
            ],
            [
                $catMap['fresh-juices'],
                'Pure Ruby Pomegranate (Anar) Vitality Juice',
                'pure-ruby-pomegranate-anar-vitality-juice',
                'Cold-pressed deep ruby arils, antioxidant powerhouse',
                'Hand-deseeded fresh red pomegranates gently pressed without crushing bitter rind seeds. Rich in iron and antioxidants, ultra smooth.',
                110.00,
                130.00,
                '300ml Chilled Glass/Bottle',
                1, 1, 25,
                'https://images.unsplash.com/photo-1622597467836-f3285f2131b8?auto=format&fit=crop&w=800&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1622597467836-f3285f2131b8?auto=format&fit=crop&w=800&q=80'
                ]),
                1, 1, 11, 1
            ],
            [
                $catMap['fresh-juices'],
                'Chilled Watermelon Mint Cooler',
                'chilled-watermelon-mint-cooler',
                'Crisp sweet watermelon crushed with mint & lime',
                'Hydrating, sweet and vibrant ruby melon crushed on order with garden mint and lime. The ultimate thirst quencher on warm Jaora afternoons.',
                55.00,
                65.00,
                '350ml Chilled Glass/Bottle',
                1, 1, 45,
                'https://images.unsplash.com/photo-1589733955941-5eeaf752f6dd?auto=format&fit=crop&w=800&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1589733955941-5eeaf752f6dd?auto=format&fit=crop&w=800&q=80'
                ]),
                1, 0, 12, 1
            ],
            [
                $catMap['fruit-bowls'],
                'N.A Deluxe Tropical Fruit Platter (500g)',
                'na-deluxe-tropical-fruit-platter-500g',
                'Papaya, pineapple, apple, pomegranate & kiwi cuts',
                'Freshly cut cubes of sweet papaya, juicy pineapple, crisp apple, pomegranate pearls and kiwi slices. Served with lemon-rock salt dressing pouch and wooden forks.',
                120.00,
                140.00,
                '500g Hygienic Sealed Box',
                1, 1, 25,
                'https://images.unsplash.com/photo-1568571780765-9276ac8b75a2?auto=format&fit=crop&w=800&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1568571780765-9276ac8b75a2?auto=format&fit=crop&w=800&q=80'
                ]),
                0, 1, 13, 1
            ]
        ];

        $insertProd = $db->prepare("
            INSERT INTO products (
                category_id, name, slug, subtitle, description, price, original_price, 
                unit_size, is_fresh_cut, in_stock, stock_qty, image_url, gallery_json, 
                is_featured, is_bestseller, sort_order, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($products as $p) {
            $insertProd->execute($p);
        }
    }

    // 7. Seed Reviews
    $stmt = $db->query("SELECT COUNT(*) FROM reviews");
    if ($stmt->fetchColumn() == 0) {
        $reviews = [
            [null, 'Rahul Patidar', 'Station Road, Jaora', 5, 'The coconut water was ice cold, naturally sweet, and had so much soft malai inside! Delivered to my shop in 18 minutes. Truly the best in Jaora!', 1, 1],
            [null, 'Dr. Sunita Sharma', 'Civil Lines, Jaora', 5, 'We order pure Mosambi and Anar juices every morning for our family. Zero water, zero artificial sugar, just pure fruit extract. Outstanding hygiene.', 1, 1],
            [null, 'Faizan Qureshi', 'Azad Chowk, Jaora', 5, 'N.A Fresh Fruits has completely upgraded drink quality in town. The packaging is leakproof and premium. Definitely recommending to all friends.', 1, 1],
            [null, 'Pooja Mandloi', 'Housing Board Colony', 5, 'Loved the Tender Coconut with malai bowl! Very fresh and sweet. Delivery was super quick via online booking.', 1, 1]
        ];
        $insertReview = $db->prepare("
            INSERT INTO reviews (product_id, customer_name, location, rating, comment, is_verified_purchase, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($reviews as $r) {
            $insertReview->execute($r);
        }
    }

    // 8. Seed Coupons
    $stmt = $db->query("SELECT COUNT(*) FROM coupons");
    if ($stmt->fetchColumn() == 0) {
        $coupons = [
            ['FRESH10', 'Get 10% OFF on all fresh juices & coconuts', 'percentage', 10.00, 150.00, 50.00, 500, 1],
            ['JAORA50', 'Flat ₹50 OFF on family orders above ₹300', 'fixed', 50.00, 300.00, 50.00, 200, 1],
            ['COCONUT', 'Special ₹20 OFF on tender coconut water', 'fixed', 20.00, 120.00, 20.00, 300, 1]
        ];
        $insertCoupon = $db->prepare("
            INSERT INTO coupons (code, description, discount_type, discount_value, min_order_amount, max_discount, usage_limit, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($coupons as $c) {
            $insertCoupon->execute($c);
        }
    }

    // 9. Seed Gallery
    $stmt = $db->query("SELECT COUNT(*) FROM gallery");
    if ($stmt->fetchColumn() == 0) {
        $gallery = [
            ['Fresh Tender Coconuts Stack', 'shop', 'https://images.unsplash.com/photo-1544378730-8b5104b18790?auto=format&fit=crop&w=800&q=80', 'Daily morning harvest batch directly from groves', 1],
            ['Cold-Press Juice Prep Bar', 'prep', 'https://images.unsplash.com/photo-1613478223719-2ab802602423?auto=format&fit=crop&w=800&q=80', 'Pure stainless-steel press and hygiene station', 2],
            ['Valencia Oranges Sorting', 'prep', 'https://images.unsplash.com/photo-1589733955941-5eeaf752f6dd?auto=format&fit=crop&w=800&q=80', 'Hand-sorted sweet citrus fruit ready for extraction', 3],
            ['Sealed Chilled Dispatch Box', 'delivery', 'https://images.unsplash.com/photo-1525385133512-2f3bdd039054?auto=format&fit=crop&w=800&q=80', 'Eco friendly straws and thermal chilled insulation', 4]
        ];
        $insertGal = $db->prepare("
            INSERT INTO gallery (title, category, image_url, caption, sort_order)
            VALUES (?, ?, ?, ?, ?)
        ");
        foreach ($gallery as $g) {
            $insertGal->execute($g);
        }
    }

    echo "Database initialization and seeding completed successfully.\n";
} catch (Exception $e) {
    echo "Database init error: " . $e->getMessage() . "\n";
}
