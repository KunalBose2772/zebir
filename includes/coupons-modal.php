<?php
/**
 * ZEBIR LIBAS – Available Coupons Modal (Rewrite)
 * Works on both cart & checkout, logged in or not.
 */
$_couponPdo = getDB();
$availableCoupons = $_couponPdo->query("SELECT * FROM coupons WHERE is_active = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE()) ORDER BY min_order_amount ASC")->fetchAll();
?>

<!-- ============================================================
     COUPONS MODAL — always rendered, shown via JS
     ============================================================ -->
<style>
/* Modal overlay — always available regardless of login state */
#couponsModal {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
  z-index: 9999;
  align-items: center;
  justify-content: center;
  padding: 16px;
  opacity: 0;
  transition: opacity 0.3s ease;
}
#couponsModal.is-open {
  display: flex;
  opacity: 1;
}
.coupons-modal-card {
  background: var(--bg-surface, #ffffff);
  border: 1px solid var(--border-color, #e7dfd4);
  border-radius: 10px;
  width: 100%;
  max-width: 480px;
  max-height: 88vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.3);
  overflow: hidden;
}
.coupons-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid var(--border-color, #e7dfd4);
  flex-shrink: 0;
}
.coupons-modal-header h3 {
  font-family: var(--font-heading, Georgia, serif);
  font-size: 1.35rem;
  margin: 0;
  color: var(--text-main, #151515);
  display: flex;
  align-items: center;
  gap: 8px;
}
.coupons-modal-close {
  background: none;
  border: none;
  color: var(--text-muted, #6f6a62);
  font-size: 1.6rem;
  cursor: pointer;
  line-height: 1;
  padding: 0 4px;
  transition: color 0.2s;
}
.coupons-modal-close:hover { color: var(--text-main, #151515); }
.coupons-modal-body {
  overflow-y: auto;
  flex-grow: 1;
  padding: 20px 24px;
  -webkit-overflow-scrolling: touch;
}
.coupon-offer-card {
  border: 1.5px dashed var(--accent-gold, #C8960C);
  background: rgba(200, 150, 12, 0.04);
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 14px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
}
.coupon-offer-card:hover {
  background: rgba(200, 150, 12, 0.09);
  transform: translateY(-1px);
  box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}
.coupon-code-pill {
  font-weight: 700;
  font-size: 0.85rem;
  color: #fff;
  background: #1a1a1a;
  border: 1px solid var(--accent-gold, #C8960C);
  padding: 4px 10px;
  border-radius: 4px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  display: inline-block;
  font-family: var(--font-body, monospace);
}
.coupon-value {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--accent-gold, #C8960C);
  margin-top: 6px;
}
.coupon-meta {
  font-size: 0.75rem;
  color: var(--text-muted, #6f6a62);
  margin-top: 5px;
  line-height: 1.5;
}
.coupon-apply-btn {
  background: var(--accent-gold, #C8960C);
  color: #fff;
  border: none;
  padding: 9px 18px;
  font-size: 0.72rem;
  letter-spacing: 1.2px;
  font-weight: 700;
  text-transform: uppercase;
  border-radius: 3px;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.2s, transform 0.1s;
  flex-shrink: 0;
}
.coupon-apply-btn:hover {
  background: var(--accent-gold-hover, #A67C0A);
  transform: scale(1.03);
}
.coupons-empty {
  text-align: center;
  color: var(--text-muted, #6f6a62);
  padding: 40px 20px;
  font-size: 0.9rem;
}
@media (max-width: 480px) {
  .coupons-modal-card { max-height: 92vh; border-radius: 12px 12px 0 0; align-self: flex-end; max-width: 100%; }
  #couponsModal { align-items: flex-end; padding: 0; }
}
</style>

<div id="couponsModal" role="dialog" aria-modal="true" aria-labelledby="couponsModalTitle">
  <!-- Backdrop click to close -->
  <div style="position:absolute;inset:0;z-index:0;" onclick="closeCouponsModal()"></div>
  
  <div class="coupons-modal-card" style="position:relative;z-index:1;">
    <!-- Header -->
    <div class="coupons-modal-header">
      <h3 id="couponsModalTitle">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--accent-gold,#C8960C);flex-shrink:0;">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
        </svg>
        Available Offers
      </h3>
      <button class="coupons-modal-close" onclick="closeCouponsModal()" aria-label="Close">&times;</button>
    </div>

    <!-- Body -->
    <div class="coupons-modal-body">
      <?php if (!empty($availableCoupons)): ?>
        <?php foreach ($availableCoupons as $_c): ?>
          <div class="coupon-offer-card">
            <div style="min-width:0;">
              <span class="coupon-code-pill"><?= e($_c['code']) ?></span>
              <div class="coupon-value">
                <?= $_c['type'] === 'percentage' ? (float)$_c['value'] . '% OFF' : '₹' . number_format((float)$_c['value'], 0) . ' OFF' ?>
              </div>
              <div class="coupon-meta">
                Min. order ₹<?= number_format((float)$_c['min_order_amount'], 0) ?>
                <?php if ($_c['expiry_date']): ?>
                  &nbsp;·&nbsp; Expires <?= date('d M Y', strtotime($_c['expiry_date'])) ?>
                <?php endif; ?>
              </div>
            </div>
            <button type="button" class="coupon-apply-btn" onclick="applySelectedCoupon('<?= e($_c['code']) ?>')">Apply</button>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="coupons-empty">No offers available right now. Check back soon!</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
(function() {
  // Avoid re-declaring if already included
  if (window._couponsModalInit) return;
  window._couponsModalInit = true;

  window.openCouponsModal = function() {
    var modal = document.getElementById('couponsModal');
    if (!modal) return;
    modal.style.display = 'flex';
    // Trigger transition
    requestAnimationFrame(function() {
      requestAnimationFrame(function() {
        modal.classList.add('is-open');
      });
    });
    document.body.style.overflow = 'hidden';
  };

  window.closeCouponsModal = function() {
    var modal = document.getElementById('couponsModal');
    if (!modal) return;
    modal.classList.remove('is-open');
    document.body.style.overflow = '';
    setTimeout(function() {
      if (!modal.classList.contains('is-open')) {
        modal.style.display = 'none';
      }
    }, 320);
  };

  // Close on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeCouponsModal();
  });

  window.applySelectedCoupon = function(code) {
    closeCouponsModal();

    // On checkout page — hidden form submit
    var checkoutInput = document.getElementById('checkout_coupon_code');
    if (checkoutInput !== null) {
      checkoutInput.value = code;
      if (typeof applyCheckoutCoupon === 'function') {
        applyCheckoutCoupon();
      }
      return;
    }

    // On cart page — fill input and click apply
    var cartInput = document.querySelector('input[name="coupon_code"]');
    if (cartInput) {
      cartInput.value = code;
      var applyBtn = document.querySelector('button[name="apply_coupon"]');
      if (applyBtn) applyBtn.click();
    }
  };
})();
</script>
