<?php
/**
 * Global Storefront Header Layout
 * ZEBIR LIBAS – Premium Fashion
 */

require_once __DIR__ . '/bootstrap.php';

$pageTitle = $pageTitle ?? getSetting('seo_title', 'ZEBIR LIBAS – Premium Fashion');
$pageDesc  = $pageDesc  ?? getSetting('seo_description', 'Curated luxury fashion collections.');
$currentTheme = getTheme();

// Fetch All Active Categories for Mega Menu
$pdo = getDB();
$allActiveCategories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC")->fetchAll();
$totalCats = count($allActiveCategories);
$half = ceil($totalCats / 2);
$exclusiveCats = array_slice($allActiveCategories, 0, $half);
$otherCats = array_slice($allActiveCategories, $half);
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= e($currentTheme) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($pageDesc) ?>">
  <meta name="keywords" content="<?= e(getSetting('meta_keywords')) ?>">

  <!-- Favicon -->
  <link rel="icon" type="image/webp" href="<?= assetUrl('images/favicon.webp') ?>">

  <!-- Fonts & Core CSS -->
  <link rel="stylesheet" href="<?= assetUrl('css/style.css') ?>">
  
  <!-- Swiper.js for Luxury Sliders -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <script>
    const BASE_URL = '<?= BASE_URL ?>';
    const CSRF_TOKEN = '<?= csrfToken() ?>';
    const IS_LOGGED_IN = <?= isLoggedIn() ? 'true' : 'false' ?>;
  </script>
</head>
<body>

<?php
$isIndex = (basename($_SERVER['PHP_SELF']) === 'index.php');
$heroMode = getSetting('home_hero_mode', 'content');
$isTransparentHeader = $isIndex && ($heroMode !== 'image');
?>

<!-- Header / Navbar -->
<header class="site-header <?= $isTransparentHeader ? 'header-transparent' : 'header-solid' ?>" id="siteHeader">
  <!-- Announcement Bar -->
  <div class="announcement-bar" id="announcementBar" <?= $isTransparentHeader ? 'style="display: none;"' : '' ?>>
    <div class="announcement-marquee-wrapper">
      <div class="announcement-marquee-item">
        End Your Week in Style with Zebir's Great Deals! &nbsp;•&nbsp; Crafting the Finest Elegant Designs &nbsp;•&nbsp; Designed by You, Crafted by Zebir &nbsp;•&nbsp; Complimentary Shipping On Orders Over <?= formatPrice((float)getSetting('free_shipping_amount', '999')) ?> &nbsp;•&nbsp; Flat 10% Off On Your First Purchase | Use Code: ZEBIR10
      </div>
      <div class="announcement-marquee-item">
        End Your Week in Style with Zebir's Great Deals! &nbsp;•&nbsp; Crafting the Finest Elegant Designs &nbsp;•&nbsp; Designed by You, Crafted by Zebir &nbsp;•&nbsp; Complimentary Shipping On Orders Over <?= formatPrice((float)getSetting('free_shipping_amount', '999')) ?> &nbsp;•&nbsp; Flat 10% Off On Your First Purchase | Use Code: ZEBIR10
      </div>
    </div>
  </div>
  <div class="container">
    <div class="navbar-inner">
      
      <!-- Mobile Menu Toggle Button -->
      <button class="icon-btn d-lg-none" id="mobileMenuToggle" aria-label="Toggle Navigation">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"></path></svg>
      </button>

      <!-- Brand Logo -->
      <a href="<?= BASE_URL ?>" class="brand-logo" style="display:flex; align-items:center;">
        <img src="<?= assetUrl('images/logoZ.webp') ?>" alt="ZEBIR LIBAS Logo" style="height: 52px; width: auto; object-fit: contain;">
      </a>

      <!-- Desktop Navigation Menu -->
      <nav class="d-none d-lg-block">
        <ul class="nav-menu">
          <li><a href="<?= pageUrl('index') ?>" class="nav-link <?= activeClass('index.php') ?>">Home</a></li>
          <li><a href="<?= pageUrl('about') ?>" class="nav-link <?= activeClass('about.php') ?>">About Us</a></li>
          
          <li class="nav-item-has-megamenu">
            <a href="<?= pageUrl('shop') ?>" class="nav-link <?= activeClass('shop.php') ?>">Collection <span style="font-size:0.5rem; vertical-align:middle; margin-left:3px;">▼</span></a>
            <div class="megamenu">
              <?php if (!empty($exclusiveCats)): ?>
              <div>
                <h4 class="megamenu-title">Exclusive Collection</h4>
                <ul class="megamenu-list">
                  <?php foreach ($exclusiveCats as $cat): ?>
                    <li><a href="<?= categoryUrl($cat['slug']) ?>"><?= e($cat['name']) ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </div>
              <?php endif; ?>
              
              <?php if (!empty($otherCats)): ?>
              <div>
                <h4 class="megamenu-title">Other Categories</h4>
                <ul class="megamenu-list">
                  <?php foreach ($otherCats as $cat): ?>
                    <li><a href="<?= categoryUrl($cat['slug']) ?>"><?= e($cat['name']) ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </div>
              <?php endif; ?>
            </div>
          </li>
          
          <li><a href="<?= pageUrl('contact') ?>" class="nav-link <?= activeClass('contact.php') ?>">Contact Us</a></li>
        </ul>
      </nav>

      <!-- Desktop Header Search Bar -->
      <form action="<?= pageUrl('search') ?>" method="GET" class="header-search-form d-none d-lg-block" style="position:relative; width:260px; margin-left: 24px; margin-right: 12px;">
        <input type="text" name="q" class="header-search-input js-live-search-input" placeholder="Search our collection..." autocomplete="off" style="width:100%; padding: 10px 16px 10px 38px; border-radius:99px; background: rgba(255,255,255,0.06); border: 1px solid rgba(200, 150, 12, 0.25); color:#fff; font-size:0.8rem; outline:none; transition: var(--transition);">
        <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:rgba(200, 150, 12, 0.7); pointer-events:none; display:flex; align-items:center;">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </span>
        <!-- Autocomplete Dropdown -->
        <div class="header-search-results js-live-search-results" style="display:none; position:absolute; top:100%; right:0; width:340px; background:#fff; border:1px solid var(--border-color); box-shadow:var(--shadow-lg); max-height:400px; overflow-y:auto; z-index:1100; border-radius:12px; margin-top:8px;"></div>
      </form>
      
      <!-- Navbar Right Icons -->
      <div class="nav-icons">
        <!-- Live Search Button (Mobile/Tablet Only) -->
        <button class="icon-btn d-lg-none" id="mobileSearchToggle" aria-label="Search">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </button>

        <!-- User Account / Auth Dropdown -->
        <div class="nav-account-dropdown-wrapper" style="position:relative; display:inline-block;">
          <button class="icon-btn" title="My Account" style="cursor:pointer; border:none; background:none;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
          </button>
          <div class="account-dropdown-menu">
            <?php if (isLoggedIn()): ?>
              <div class="account-dropdown-menu-header">
                Hi, <?= e(explode(' ', trim($_SESSION['customer_name'] ?? 'User'))[0]) ?>
              </div>
              <a href="<?= pageUrl('account') ?>" class="dropdown-item">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" width="14" height="14" style="flex-shrink:0; color:var(--accent-gold);"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>Profile
              </a>
              <a href="<?= pageUrl('orders') ?>" class="dropdown-item">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" width="14" height="14" style="flex-shrink:0; color:var(--accent-gold);"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>Orders
              </a>
              <a href="<?= pageUrl('wishlist') ?>" class="dropdown-item">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" width="14" height="14" style="flex-shrink:0; color:var(--accent-gold);"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>Wishlist
              </a>
              <a href="<?= pageUrl('logout') ?>" class="dropdown-item" style="border-top:1px solid var(--border-color); color:#e05650 !important;">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" width="14" height="14" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>Logout
              </a>
            <?php else: ?>
              <a href="<?= pageUrl('login') ?>" class="dropdown-item">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" width="14" height="14" style="flex-shrink:0; color:var(--accent-gold);"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>Login
              </a>
              <a href="<?= pageUrl('register') ?>" class="dropdown-item">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" width="14" height="14" style="flex-shrink:0; color:var(--accent-gold);"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>Register
              </a>
              <a href="<?= pageUrl('orders') ?>" class="dropdown-item">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" width="14" height="14" style="flex-shrink:0; color:var(--accent-gold);"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Track
              </a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Wishlist -->
        <a href="<?= pageUrl('wishlist') ?>" class="icon-btn" title="Wishlist">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
          <span class="badge-count js-wishlist-count"><?= wishlistCount() ?></span>
        </a>

        <!-- Cart Drawer Toggle -->
        <button class="icon-btn js-cart-toggle" title="Bag">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
          <span class="badge-count js-cart-count"><?= cartCount() ?></span>
        </button>
      </div>

    </div>
  </div>
  
  <!-- Mobile Search Row (Appears at bottom of header) -->
  <div class="mobile-search-row d-lg-none" id="mobileSearchRow" style="display:none; border-top: 1px solid rgba(200, 150, 12, 0.22); background: #000000; padding: 10px 16px;">
    <form action="<?= BASE_URL ?>search.php" method="GET" style="position:relative; width:100%; margin:0;">
      <input type="text" name="q" class="header-search-input js-live-search-input" placeholder="Search our collection..." autocomplete="off" style="width:100%; padding: 8px 12px 8px 36px; border-radius:99px; background: rgba(255,255,255,0.06); border: 1px solid rgba(200, 150, 12, 0.25); color:#fff; font-size:0.8rem; outline:none;">
      <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:rgba(200, 150, 12, 0.7); pointer-events:none; display:flex; align-items:center;">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
      </span>
      <div class="header-search-results js-live-search-results" style="display:none; position:absolute; top:100%; left:0; right:0; width:100%; background:#fff; border:1px solid var(--border-color); box-shadow:var(--shadow-lg); max-height:300px; overflow-y:auto; z-index:1100; border-radius:10px; margin-top:8px;"></div>
    </form>
  </div>
</header>

<script>
  document.getElementById('mobileSearchToggle')?.addEventListener('click', () => {
    const row = document.getElementById('mobileSearchRow');
    if (row) {
      if (row.style.display === 'none') {
        row.style.display = 'block';
        row.querySelector('input')?.focus();
      } else {
        row.style.display = 'none';
      }
    }
  });
</script>

<!-- Offcanvas Cart Drawer -->
<div class="drawer-backdrop" id="cartBackdrop"></div>
<div class="drawer" id="cartDrawer">
  <div class="drawer-header">
    <h3 class="drawer-title font-serif">Shopping Bag (<span class="js-cart-count"><?= cartCount() ?></span>)</h3>
    <button class="icon-btn" id="cartDrawerClose">&times;</button>
  </div>
  <div class="drawer-body" id="cartDrawerBody">
    <!-- Populated via AJAX -->
  </div>
</div>

<!-- Mobile Navigation Drawer -->
<div class="drawer-backdrop" id="mobileMenuBackdrop"></div>
<div class="drawer drawer-left" id="mobileMenuDrawer">
  <div class="drawer-header">
    <h3 class="drawer-title font-serif">Menu</h3>
    <button class="icon-btn" id="mobileMenuClose">&times;</button>
  </div>
  <div class="drawer-body" style="padding: 20px; overflow-y: auto;">
    <ul style="list-style: none; display: flex; flex-direction: column; gap: 20px;">
      <li><a href="<?= pageUrl('index') ?>" style="font-size: 1.1rem; text-transform: uppercase; letter-spacing: 2px;">Home</a></li>
      <li><a href="<?= pageUrl('about') ?>" style="font-size: 1.1rem; text-transform: uppercase; letter-spacing: 2px;">About Us</a></li>
      <li>
        <span style="font-size: 1.1rem; text-transform: uppercase; letter-spacing: 2px; color: var(--accent-gold); display: block; margin-bottom: 10px;">Collection</span>
        <ul style="list-style: none; padding-left: 15px; display: flex; flex-direction: column; gap: 10px; border-left: 1px solid var(--border-color);">
          <?php foreach ($allActiveCategories as $cat): ?>
            <li><a href="<?= categoryUrl($cat['slug']) ?>" style="font-size: 0.9rem;"><?= e($cat['name']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </li>
      <li><a href="<?= pageUrl('contact') ?>" style="font-size: 1.1rem; text-transform: uppercase; letter-spacing: 2px;">Contact Us</a></li>
    </ul>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Menu Drawer Navigation handler
    const toggleBtn = document.getElementById('mobileMenuToggle');
    const menuDrawer = document.getElementById('mobileMenuDrawer');
    const menuBackdrop = document.getElementById('mobileMenuBackdrop');
    const closeBtn = document.getElementById('mobileMenuClose');

    if (toggleBtn && menuDrawer) {
      toggleBtn.addEventListener('click', () => {
        menuDrawer.classList.add('active');
        menuBackdrop?.classList.add('active');
      });

      const closeMenu = () => {
        menuDrawer.classList.remove('active');
        menuBackdrop?.classList.remove('active');
      };

      closeBtn?.addEventListener('click', closeMenu);
      menuBackdrop?.addEventListener('click', closeMenu);
    }

    // 2. Transparent -> Sticky Header Scroll Transition (homepage only)
    <?php if ($isTransparentHeader): ?>
    const siteHeader = document.getElementById('siteHeader');
    const announcementBar = document.getElementById('announcementBar');
    
    if (siteHeader) {
      const handleScroll = () => {
        if (window.scrollY > 50) {
          if (siteHeader.classList.contains('header-transparent')) {
            siteHeader.classList.remove('header-transparent');
            siteHeader.classList.add('header-sticky');
            if (announcementBar) {
              announcementBar.style.display = 'block';
            }
          }
        } else {
          if (siteHeader.classList.contains('header-sticky')) {
            siteHeader.classList.remove('header-sticky');
            siteHeader.classList.add('header-transparent');
            if (announcementBar) {
              announcementBar.style.display = 'none';
            }
          }
        }
      };
      
      window.addEventListener('scroll', handleScroll);
      handleScroll(); // Check scroll position on page load
    }
    <?php endif; ?>
  });
</script>
