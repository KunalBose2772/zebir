/**
 * ZEBIR LIBAS – Modern Luxury Frontend Javascript Engine
 */

document.addEventListener('DOMContentLoaded', () => {
  initCartDrawer();
  initCartAddForms();
  initQuickView();
  initWishlist();
  initLiveSearch();
  initMobileMenu();
  initQuantityInputs();
});

// ── Cart Drawer Handler ─────────────────────────────────────────
function initCartDrawer() {
  const cartBtn = document.querySelectorAll('.js-cart-toggle');
  const cartDrawer = document.getElementById('cartDrawer');
  const cartBackdrop = document.getElementById('cartBackdrop');
  const closeBtn = document.getElementById('cartDrawerClose');

  if (!cartDrawer) return;

  const openDrawer = () => {
    cartDrawer.classList.add('active');
    cartBackdrop?.classList.add('active');
    document.body.style.overflow = 'hidden';
    loadCartDrawerContents();
  };

  const closeDrawer = () => {
    cartDrawer.classList.remove('active');
    cartBackdrop?.classList.remove('active');
    document.body.style.overflow = '';
  };

  cartBtn.forEach(btn => btn.addEventListener('click', (e) => {
    e.preventDefault();
    openDrawer();
  }));

  closeBtn?.addEventListener('click', closeDrawer);
  cartBackdrop?.addEventListener('click', closeDrawer);
}

function loadCartDrawerContents() {
  const container = document.getElementById('cartDrawerBody');
  if (!container) return;

  container.innerHTML = '<div class="text-center py-5"><div class="spinner">Loading...</div></div>';

  fetch(BASE_URL + 'ajax/cart.php?action=get_drawer')
    .then(res => res.text())
    .then(html => {
      container.innerHTML = html;
      updateCartCounts();
    })
    .catch(() => {
      container.innerHTML = '<p class="text-center text-muted">Failed to load cart items.</p>';
    });
}

// ── Wishlist AJAX Handler ───────────────────────────────────────
function initWishlist() {
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-wishlist-btn');
    if (!btn) return;
    e.preventDefault();

    const productId = btn.dataset.id;
    if (!productId) return;

    fetch(BASE_URL + 'ajax/wishlist.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: `product_id=${productId}&csrf_token=${CSRF_TOKEN}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        btn.classList.toggle('active');
        if (data.in_wishlist) {
          btn.classList.add('text-gold');
          showToast(data.message || 'Added to wishlist', 'success');
        } else {
          btn.classList.remove('text-gold');
          showToast(data.message || 'Removed from wishlist', 'info');
        }
        updateWishlistCounts(data.count);
      } else {
        showToast(data.message || 'Error updating wishlist', 'error');
      }
    })
    .catch(err => {
      console.error(err);
      showToast('Network error', 'error');
    });
  });
}

// ── Quick View Modal Handler ───────────────────────────────────
function initQuickView() {
  const backdrop = document.getElementById('quickViewBackdrop');
  const modalContent = document.getElementById('quickViewContent');
  const closeBtn = document.getElementById('quickViewClose');

  if (!backdrop) return;

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-quickview-btn');
    if (!btn) return;
    e.preventDefault();

    const productId = btn.dataset.id;
    if (!productId) return;

    backdrop.classList.add('active');
    document.body.style.overflow = 'hidden';
    modalContent.innerHTML = '<div class="text-center py-5">Loading product details...</div>';

    fetch(BASE_URL + 'ajax/quickview.php?id=' + productId)
      .then(res => res.text())
      .then(html => {
        modalContent.innerHTML = html;
      });
  });

  const closeModal = () => {
    backdrop.classList.remove('active');
    document.body.style.overflow = '';
  };

  closeBtn?.addEventListener('click', closeModal);
  backdrop?.addEventListener('click', (e) => {
    if (e.target === backdrop) closeModal();
  });
}

// ── Live Search ────────────────────────────────────────────────
function initLiveSearch() {
  const searchInputs = document.querySelectorAll('.js-live-search-input');
  
  searchInputs.forEach(input => {
    const container = input.parentNode.querySelector('.js-live-search-results');
    if (!container) return;

    let debounceTimer;
    input.addEventListener('input', (e) => {
      clearTimeout(debounceTimer);
      const query = e.target.value.trim();

      if (query.length < 2) {
        container.style.display = 'none';
        container.innerHTML = '';
        return;
      }

      debounceTimer = setTimeout(() => {
        fetch(BASE_URL + 'ajax/search.php?q=' + encodeURIComponent(query))
          .then(res => res.text())
          .then(html => {
            container.innerHTML = html;
            container.style.display = 'block';
          });
      }, 250);
    });

    // Close when clicking outside
    document.addEventListener('click', (e) => {
      if (!input.contains(e.target) && !container.contains(e.target)) {
        container.style.display = 'none';
      }
    });

    // Re-open on focus if input has value
    input.addEventListener('focus', () => {
      if (input.value.trim().length >= 2 && container.innerHTML !== '') {
        container.style.display = 'block';
      }
    });
  });
}

// ── Mobile Menu Handler ────────────────────────────────────────
function initMobileMenu() {
  const toggleBtn = document.getElementById('mobileMenuToggle');
  const menuDrawer = document.getElementById('mobileMenuDrawer');
  const menuBackdrop = document.getElementById('mobileMenuBackdrop');

  if (!toggleBtn || !menuDrawer) return;

  const openMenu = () => {
    menuDrawer.classList.add('active');
    menuBackdrop?.classList.add('active');
  };

  const closeMenu = () => {
    menuDrawer.classList.remove('active');
    menuBackdrop?.classList.remove('active');
  };

  toggleBtn.addEventListener('click', openMenu);
  menuBackdrop?.addEventListener('click', closeMenu);
}

// ── Quantity Inputs Handler ────────────────────────────────────
function initQuantityInputs() {
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.qty-btn');
    if (!btn) return;

    const input = btn.parentNode.querySelector('.qty-input');
    if (!input) return;

    let val = parseInt(input.value) || 1;
    if (btn.classList.contains('qty-plus')) {
      val++;
    } else if (btn.classList.contains('qty-minus')) {
      val = Math.max(1, val - 1);
    }
    input.value = val;
    input.dispatchEvent(new Event('change', { bubbles: true }));
  });
}

// ── Helper Utilities ───────────────────────────────────────────
function updateCartCounts() {
  fetch(BASE_URL + 'ajax/cart.php?action=get_count')
    .then(res => res.json())
    .then(data => {
      document.querySelectorAll('.js-cart-count').forEach(el => el.textContent = data.count);
    });
}

function updateWishlistCounts(count) {
  document.querySelectorAll('.js-wishlist-count').forEach(el => el.textContent = count);
}

function showToast(message, type = 'info') {
  let toastContainer = document.getElementById('toastContainer');
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.id = 'toastContainer';
    toastContainer.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;';
    document.body.appendChild(toastContainer);
  }

  const toast = document.createElement('div');
  const bg = type === 'success' ? '#16a34a' : type === 'error' ? '#dc2626' : '#111827';
  toast.style.cssText = `background:${bg};color:#fff;padding:10px 16px;border-radius:10px;font-size:0.9rem;letter-spacing:0.2px;box-shadow:0 10px 24px rgba(0,0,0,0.18);transition:all 0.3s ease;max-width:340px;min-width:220px;`;
  toast.textContent = message;

  toastContainer.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(10px)';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

// ── Intercept Add to Cart Forms Globally ────────────────────────
function initCartAddForms() {
  document.addEventListener('submit', (e) => {
    const form = e.target.closest('.js-cart-form');
    if (!form) return;
    e.preventDefault();

    const formData = new FormData(form);

    const actionUrl = form.getAttribute('action') || (typeof BASE_URL !== 'undefined' ? BASE_URL + 'ajax/cart.php' : 'ajax/cart.php');

    fetch(actionUrl, {
      method: 'POST',
      body: new URLSearchParams(formData),
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showToast(data.message || 'Added to bag successfully.', 'success');
        updateCartCounts();
        
        // Open cart drawer automatically
        const cartDrawer = document.getElementById('cartDrawer');
        const cartBackdrop = document.getElementById('cartBackdrop');
        if (cartDrawer) {
          cartDrawer.classList.add('active');
          cartBackdrop?.classList.add('active');
          document.body.style.overflow = 'hidden';
          loadCartDrawerContents();
        }
      } else {
        showToast(data.message || 'Error adding to bag.', 'error');
      }
    })
    .catch(err => {
      console.error(err);
      showToast('Network error', 'error');
    });
  });
}
