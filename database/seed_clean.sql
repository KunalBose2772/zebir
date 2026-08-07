-- ============================================================
-- ZEBIR LIBAS – Clean Reset + Seed Script
-- Clears orders, customers, wishlists, coupons
-- Seeds categories with images
-- Seeds hero section config and home_category_ids
-- ============================================================

USE `zebirl`;

-- Disable FK checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- STEP 1: CLEAR ORDERS & TRANSACTIONAL DATA
-- ============================================================
TRUNCATE TABLE `order_status_history`;
TRUNCATE TABLE `order_items`;
TRUNCATE TABLE `orders`;
TRUNCATE TABLE `customers`;
TRUNCATE TABLE `customer_addresses`;
TRUNCATE TABLE `wishlists`;
TRUNCATE TABLE `newsletter`;
TRUNCATE TABLE `email_logs`;

-- Clear coupons (fresh start)
TRUNCATE TABLE `coupons`;

-- Re-enable FK checks
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- STEP 2: ADD SAMPLE COUPONS FOR TESTING
-- ============================================================
INSERT INTO `coupons` (`code`, `type`, `value`, `min_order_amount`, `max_uses`, `expiry_date`, `is_active`) VALUES
('WELCOME10', 'percentage', 10.00, 500.00, 100, DATE_ADD(CURDATE(), INTERVAL 6 MONTH), 1),
('FLAT200', 'fixed', 200.00, 999.00, 50, DATE_ADD(CURDATE(), INTERVAL 3 MONTH), 1),
('FESTIVE15', 'percentage', 15.00, 1500.00, 200, DATE_ADD(CURDATE(), INTERVAL 4 MONTH), 1);

-- ============================================================
-- STEP 3: UPDATE CATEGORY IMAGES (asset-backed filenames)
-- ============================================================

-- IDs 5-10 already have correct images; now update remaining categories

-- Masleen Suits (id=11)
UPDATE `categories` SET `image` = 'CAT_MASLEEN_SUITS.png' WHERE `id` = 11;

-- Chiffon Suits (id=12)
UPDATE `categories` SET `image` = 'CAT_CHIFFON_SUITS.png' WHERE `id` = 12;

-- Chanderi Suits (id=13)
UPDATE `categories` SET `image` = 'CAT_CHANDERI_SUITS.png' WHERE `id` = 13;

-- Cotton Printed Suits (id=14) – use the cotton suits image
UPDATE `categories` SET `image` = 'CAT_COTTON_SUITS.png' WHERE `id` = 14;

-- Georgette Suits (id=15)
UPDATE `categories` SET `image` = 'CAT_GEORGETTE_SUITS.png' WHERE `id` = 15;

-- Hand Work Suits (id=16)
UPDATE `categories` SET `image` = 'CAT_HANDWORK_SUITS.png' WHERE `id` = 16;

-- Lakhnawi Suits (id=17) – use handwork image (similar aesthetic)
UPDATE `categories` SET `image` = 'CAT_HANDWORK_SUITS.png' WHERE `id` = 17;

-- Jam Cotton Suits (id=18)
UPDATE `categories` SET `image` = 'CAT_JAM_COTTON_SUITS.png' WHERE `id` = 18;

-- Crunchy Fabric (id=19) – use masleen image
UPDATE `categories` SET `image` = 'CAT_MASLEEN_SUITS.png' WHERE `id` = 19;

-- readymade (id=20) – use festive image
UPDATE `categories` SET `image` = 'CAT_FESTIVE.png' WHERE `id` = 20;

-- Simar Suits (id=21) – use georgette image
UPDATE `categories` SET `image` = 'CAT_GEORGETTE_SUITS.png' WHERE `id` = 21;

-- glaze cotton (id=22) – use cotton suits image
UPDATE `categories` SET `image` = 'CAT_COTTON_SUITS.png' WHERE `id` = 22;

-- Pakistani Lawn Suits (id=23) – use pakistani lawn image
UPDATE `categories` SET `image` = 'CAT_PAKISTANI_LAWN.png' WHERE `id` = 23;

-- Lawn Patch Work Suits (id=24) – use lawn cotton suits image
UPDATE `categories` SET `image` = 'CAT_LAWN_COTTON_SUITS.png' WHERE `id` = 24;

-- Thread Work Suits (id=25) – use handwork image
UPDATE `categories` SET `image` = 'CAT_HANDWORK_SUITS.png' WHERE `id` = 25;

-- Malmal Cotton Suits (id=26) – use cotton suits image
UPDATE `categories` SET `image` = 'CAT_COTTON_SUITS.png' WHERE `id` = 26;

-- Jarkan Suits (id=27) – use chanderi image
UPDATE `categories` SET `image` = 'CAT_CHANDERI_SUITS.png' WHERE `id` = 27;

-- Lawn Suits (id=28) – use lawn cotton suits image
UPDATE `categories` SET `image` = 'CAT_LAWN_COTTON_SUITS.png' WHERE `id` = 28;

-- Kota Doria Suits (id=29) – use chiffon image
UPDATE `categories` SET `image` = 'CAT_CHIFFON_SUITS.png' WHERE `id` = 29;

-- Ready To Wear (id=1) – use festive image
UPDATE `categories` SET `image` = 'CAT_FESTIVE.png' WHERE `id` = 1;

-- Sarees & Ethnic (id=2) – use silk image
UPDATE `categories` SET `image` = 'CAT_SILK.png' WHERE `id` = 2;

-- Linen & Tailored (id=3) – use chanderi image
UPDATE `categories` SET `image` = 'CAT_CHANDERI_SUITS.png' WHERE `id` = 3;

-- Couture Dresses (id=4) – use masleen suits image
UPDATE `categories` SET `image` = 'CAT_MASLEEN_SUITS.png' WHERE `id` = 4;

-- ============================================================
-- STEP 4: UPDATE HOMEPAGE SETTINGS
-- Hero section: Use content mode with desktop images + rich copy
-- Featuring 6 categories from assets: IDs 5,6,7,8,9,10
-- ============================================================

-- Set hero mode to 'content' (overlay text on slides)
UPDATE `settings` SET `value` = 'content' WHERE `key` = 'home_hero_mode';

-- Set hero config with 3 slides (asset images + rich copy)
UPDATE `settings` SET `value` = '[
  {
    "image": "HERO_01_LUXURY_COLLECTION_DESKTOP.png",
    "image_mobile": "HERO_01_LUXURY_COLLECTION_MOBILE.png",
    "title": "Signature Couture",
    "subtitle": "NEW ARRIVAL 2025",
    "description": "Modern luxury crafted in every detail — premium unstitched suits designed for women who wear elegance effortlessly.",
    "button_text": "Explore Collection",
    "button_link": "shop.php",
    "link": ""
  },
  {
    "image": "HERO_02_FESTIVE_COLLECTION_DESKTO.png",
    "image_mobile": "HERO_02_FESTIVE_COLLECTION_MOBILE.png",
    "title": "Festive Edit",
    "subtitle": "THE OCCASION EDIT",
    "description": "Find richly textured suits and festive pieces made to shine through every celebration and special occasion.",
    "button_text": "Shop Festive",
    "button_link": "category/festive-wear",
    "link": ""
  },
  {
    "image": "HERO_03_WEDDING_COLLECTION_DESKTOP.png",
    "image_mobile": "HERO_03_WEDDING_COLLECTION_MOBILE.png",
    "title": "Bridal Collection",
    "subtitle": "WEDDING SEASON 2025",
    "description": "Discover couture-inspired suits designed for unforgettable celebrations, graceful presence, and timeless elegance.",
    "button_text": "Shop Bridal",
    "button_link": "category/pure-silk",
    "link": ""
  }
]' WHERE `key` = 'home_hero_config';

-- Set homepage featured categories: Pakistani Lawn (5), Pure Silk (6), Muslin & Organza (7), Cotton Suits (8), Lawn Cotton Suits (9), Festive Wear (10)
UPDATE `settings` SET `value` = '[5,6,7,8,9,10]' WHERE `key` = 'home_category_ids';

-- Ensure home_category_count is set to show all 6
UPDATE `settings` SET `value` = '6' WHERE `key` = 'home_category_count';

-- ============================================================
-- STEP 5: VERIFY (informational SELECTs)
-- ============================================================
SELECT 'Orders after clean:' AS info, COUNT(*) AS count FROM `orders`
UNION ALL
SELECT 'Customers after clean:', COUNT(*) FROM `customers`
UNION ALL
SELECT 'Banners count:', COUNT(*) FROM `banners`
UNION ALL
SELECT 'Categories with images:', COUNT(*) FROM `categories` WHERE `image` IS NOT NULL AND `image` != ''
UNION ALL
SELECT 'Categories total:', COUNT(*) FROM `categories`
UNION ALL
SELECT 'Coupons active:', COUNT(*) FROM `coupons` WHERE is_active = 1;

SELECT `key`, `value` FROM `settings` WHERE `key` IN ('home_hero_mode', 'home_category_ids', 'home_category_count');
