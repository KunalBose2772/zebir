<?php
/**
 * ZEBIR LIBAS – Admin Header (Overhauled premium enterprise-grade layout)
 */
require_once __DIR__ . '/../../includes/bootstrap.php';
requireAdmin();

$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard – ZEBIR LIBAS</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>admin/assets/css/admin.css">
  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('adminSidebar');
      const overlay = document.getElementById('adminSidebarOverlay');
      sidebar.classList.toggle('open');
      overlay.classList.toggle('active');
    }

    document.addEventListener('DOMContentLoaded', function () {
      const sidebar = document.getElementById('adminSidebar');
      const overlay = document.getElementById('adminSidebarOverlay');

      document.querySelectorAll('.admin-menu a').forEach(function (link) {
        link.addEventListener('click', function () {
          if (window.innerWidth <= 991) {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
          }
        });
      });

      window.addEventListener('resize', function () {
        if (window.innerWidth > 991) {
          sidebar.classList.remove('open');
          overlay.classList.remove('active');
        }
      });
      
      // Auto-hide toast notifications after 5 seconds
      setTimeout(() => {
        const toasts = document.querySelectorAll('.admin-flash');
        toasts.forEach(toast => {
          toast.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
          toast.style.opacity = '0';
          toast.style.transform = 'translateY(-10px)';
          setTimeout(() => toast.remove(), 500);
        });
      }, 5000);
    });
  </script>
</head>
<body>

<!-- Toast Notification Container (Top Right, Non-blocking) -->
<div class="toast-container">
  <?php $flash = getFlash(); if ($flash): 
    $flashType = e($flash['type']);
    $isSuccess = ($flashType === 'success');
  ?>
    <div class="admin-flash <?= $flashType ?>" role="alert">
      <div class="admin-flash-icon">
        <?php if ($isSuccess): ?>
          <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <?php else: ?>
          <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <?php endif; ?>
      </div>
      <div class="admin-flash-content">
        <?= e($flash['message']) ?>
      </div>
      <button type="button" class="admin-flash-close" onclick="this.parentElement.remove()" aria-label="Dismiss">&times;</button>
    </div>
  <?php endif; ?>
</div>

<div class="admin-sidebar-overlay" id="adminSidebarOverlay" onclick="toggleSidebar()"></div>

<div class="admin-layout">
  <!-- Sidebar -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand-wrapper">
      <a href="index.php" class="brand" style="width: 100%; display: flex; justify-content: center;">
        <img src="<?= assetUrl('images/logoZ.webp') ?>" alt="Logo">
      </a>
      <button class="sidebar-close-btn" onclick="toggleSidebar()" aria-label="Close menu">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
      </button>
    </div>
    
    <ul class="admin-menu">
      <li>
        <a href="index" class="<?= activeClass('index') ?>">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path></svg>
          Dashboard
        </a>
      </li>
      <li>
        <a href="orders" class="<?= activeClass('orders') ?>">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
          Orders
        </a>
      </li>
      <li>
        <a href="products" class="<?= activeClass('products') ?>">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
          Products
        </a>
      </li>
      <li>
        <a href="categories" class="<?= activeClass('categories') ?>">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
          Categories
        </a>
      </li>
      <li>
        <a href="coupons" class="<?= activeClass('coupons') ?>">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
          Coupons
        </a>
      </li>
      <li>
        <a href="customers" class="<?= activeClass('customers') ?>">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
          Customers
        </a>
      </li>
      
      <li class="menu-divider"></li>
      
      <li>
        <a href="import" class="<?= activeClass('import') ?>">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
          Import Products
        </a>
      </li>
      <li>
        <a href="instagram" class="<?= activeClass('instagram') ?>">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path></svg>
          Instagram Reels
        </a>
      </li>
      <li>
        <a href="homepage" class="<?= activeClass('homepage') ?>">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
          Homepage Slider
        </a>
      </li>
      <li>
        <a href="settings" class="<?= activeClass('settings') ?>">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
          Settings
        </a>
      </li>
      <li>
        <a href="change-password" class="<?= activeClass('change-password') ?>">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m-5-3a3 3 0 11-6 0 3 3 0 016 0zM4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
          Change Password
        </a>
      </li>
      
      <li class="menu-divider"></li>
      
      <li>
        <a href="logout" style="color: #f87171;">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #f87171;"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
          Logout
        </a>
      </li>
    </ul>
  </aside>

  <!-- Main Section -->
  <div class="admin-main">
    <header class="admin-topbar">
      <div style="display:flex; align-items:center; gap:12px;">
        <button class="mobile-hamburger-btn" onclick="toggleSidebar()" aria-label="Open menu">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        <div style="font-weight: 700; color: var(--primary-color); font-size: 1.05rem; letter-spacing: -0.02em;">ZEBIR Admin Console</div>
      </div>
      <div class="topbar-links" style="display: flex; align-items: center; gap: 24px;">
        <a href="<?= BASE_URL ?>" target="_blank" style="font-size:0.875rem; color: var(--text-muted); text-decoration:none; display: inline-flex; align-items: center; gap: 6px; font-weight: 500; transition: color 0.2s ease;">
          View Storefront
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
        </a>
        <div style="height: 16px; width: 1px; background-color: var(--border-color);"></div>
        <span style="font-weight: 600; font-size: 0.875rem; color: var(--primary-color); display: inline-flex; align-items: center; gap: 8px;">
          <span style="width: 8px; height: 8px; background-color: #10b981; border-radius: 50%;"></span>
          <?= e($adminName) ?>
        </span>
      </div>
    </header>

    <div class="admin-content">
