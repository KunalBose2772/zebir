<?php
/**
 * Global Storefront Footer Layout – ZEBIR LIBAS
 */
?>

<!-- Quick View Modal Container -->
<div class="modal-backdrop" id="quickViewBackdrop">
  <div class="modal-content" style="max-width: 900px;">
    <button class="modal-close" id="quickViewClose">&times;</button>
    <div id="quickViewContent"></div>
  </div>
</div>

<!-- Luxury Trust Bar -->
<section class="trust-bar">
  <div class="container">
    <div class="trust-grid">
      <div class="trust-item">
        <h4 class="font-serif">Complimentary Shipping</h4>
        <p>Express delivery on orders over ₹999</p>
      </div>
      <div class="trust-item">
        <h4 class="font-serif">Artisan Craftsmanship</h4>
        <p>100% authentic, hand-embroidered heritage couture</p>
      </div>
      <div class="trust-item">
        <h4 class="font-serif">Bespoke Concierge</h4>
        <p>Personal styling & sizing guidance 24/7</p>
      </div>
      <div class="trust-item">
        <h4 class="font-serif">Secure Checkout</h4>
        <p>Encrypted UPI, Credit Card & Cash on Delivery</p>
      </div>
    </div>
  </div>
</section>

<!-- Site Footer -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">

      <!-- Brand -->
      <div class="footer-brand">
        <a href="<?= BASE_URL ?>" style="display:flex; align-items:center; margin-bottom:18px;">
          <img src="<?= assetUrl('images/logoZ.webp') ?>" alt="ZEBIR LIBAS" style="height:50px; width:auto; filter:brightness(1.15); object-fit:contain;">
        </a>
        <p class="footer-about">
          Zebir is a contemporary women's wear brand specializing in elegant suit sets that blend tradition with modern style. Founded in 2024, Zebir empowers women through timeless designs, luxurious fabrics, and tailored comfort—offering sophistication, confidence, and affordability in every piece for today's bold and graceful woman.
        </p>
        
        <!-- Social -->
        <div class="footer-socials">
          <?php if ($ig = getSetting('instagram_url')): ?>
            <a href="<?= e($ig) ?>" target="_blank" class="footer-social-btn" aria-label="Instagram">
              <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                  <linearGradient id="ig_circle_grad" x1="0" y1="40" x2="40" y2="0" gradientUnits="userSpaceOnUse">
                    <stop offset="0" stop-color="#FEE411"/>
                    <stop offset="0.3" stop-color="#FE5F5E"/>
                    <stop offset="0.7" stop-color="#FE2181"/>
                    <stop offset="1" stop-color="#9000DC"/>
                  </linearGradient>
                </defs>
                <circle cx="20" cy="20" r="20" fill="url(#ig_circle_grad)"/>
                <path d="M20 14.5c1.79 0 2 .01 2.7.04.65.03 1 .14 1.23.23.31.12.53.27.76.5.23.23.38.45.5.76.09.23.2.58.23 1.23.03.7.04.91.04 2.7s-.01 2-.04 2.7c-.03.65-.14 1-.23 1.23-.12.31-.27.53-.5.76-.23.23-.45.38-.76.5-.23.09-.58.2-1.23.23-.7.03-.91.04-2.7.04s-2-.01-2.7-.04c-.65-.03-1-.14-1.23-.23-.31-.12-.53-.27-.76-.5-.23-.23-.38-.45-.5-.76-.09-.23-.2-.58-.23-1.23-.03-.7-.04-.91-.04-2.7s.01-2 .04-2.7c.03-.65.14-1.23.23-1.23.12-.31.27-.53.5-.76.23-.23.45-.38.76-.5.23-.09.58-.2 1.23-.23.7-.03.91-.04 2.7-.04zm0-1.35c-1.82 0-2.05.01-2.76.04-.71.03-1.2.15-1.63.31-.44.17-.82.4-1.2.78-.38.38-.61.76-.78 1.2-.16.43-.28.92-.31 1.63-.03.71-.04.94-.04 2.76s.01 2.05.04 2.76c.03.71.15 1.2.31 1.63.17.44.4.82.78 1.2.38.38.76.61 1.2.78.43.16.92.28 1.63.31.71.03.94.04 2.76.04s2.05-.01 2.76-.04c.71-.03 1.2-.15 1.63-.31.44-.17.82-.4 1.2-.78.38-.38.61-.76.78-1.2.16-.43.28-.92.31-1.63.03-.71.04-.94.04-2.76s-.01-2.05-.04-2.76c-.03-.71-.15-1.2-.31-1.63-.17-.44-.4-.82-.78-1.2-.38-.38-.76-.61-.1-1.2-.43-.16-.92-.28-1.63-.31-.71-.03-.94-.04-2.76-.04z" fill="#FFFFFF"/>
                <path d="M20 16.92A3.08 3.08 0 1023.08 20 3.08 3.08 0 0020 16.92zm0 4.81A1.73 1.73 0 1121.73 20 1.73 1.73 0 0120 21.73z" fill="#FFFFFF"/>
                <circle cx="23.2" cy="16.8" r="0.72" fill="#FFFFFF"/>
              </svg>
            </a>
          <?php endif; ?>
          <?php if ($fb = getSetting('facebook_url')): ?>
            <a href="<?= e($fb) ?>" target="_blank" class="footer-social-btn" aria-label="Facebook">
              <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="20" cy="20" r="20" fill="#1877F2"/>
                <path d="M24.84 21l.64-4.14h-3.97v-2.69c0-1.13.56-2.24 2.33-2.24H25.6V8.41s-1.63-.28-3.18-.28c-3.24 0-5.37 1.97-5.37 5.53V16.9h-3.64V21h3.64v10h4.42V21h3.97z" fill="#FFFFFF"/>
              </svg>
            </a>
          <?php endif; ?>
          <a href="https://wa.me/919006666622" target="_blank" class="footer-social-btn" aria-label="WhatsApp">
            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="20" cy="20" r="20" fill="#25D366"/>
              <path d="M20 11c-4.97 0-9 4.03-9 9 0 1.59.41 3.09 1.13 4.41L11 29l4.72-1.24A8.93 8.93 0 0020 29c4.97 0 9-4.03 9-9s-4.03-9-9-9zm5.22 12.28c-.22.62-1.29 1.21-1.78 1.26-.44.05-.88.22-2.83-.55-2.33-.92-3.83-3.28-3.95-3.44-.12-.16-.95-1.26-.95-2.4 0-1.15.6-1.71.82-1.95.22-.24.49-.3.65-.3.16 0 .33 0 .47.01.15.01.35-.06.55.43.2.49.69 1.68.75 1.8.06.12.1.27.02.43-.08.16-.18.26-.35.45-.17.19-.36.42-.51.57-.17.17-.35.35-.15.69.2.34.88 1.45 1.89 2.35 1.3 1.16 2.39 1.52 2.73 1.69.34.17.54.14.74-.09.2-.23.86-1 .1-1.5-.12-.08-.34-.14-.56-.24-.22-.1-.85-.42-.98-.47-.13-.05-.23-.08-.33.08-.1.16-.39.49-.48.59-.09.1-.18.11-.4.01-.22-.1-.93-.34-1.77-1.09-.65-.58-1.09-1.29-1.22-1.51-.13-.22-.01-.34.1-.45.1-.1.22-.26.33-.39.11-.13.15-.22.22-.37.07-.15.03-.28-.02-.38-.05-.1-.48-1.15-.65-1.58-.17-.41-.35-.35-.48-.36h-.41c-.13 0-.35.05-.53.25-.18.2-.7.69-.7 1.69s.73 1.96.83 2.1c.1.14 1.43 2.19 3.47 3.07.49.21.87.34 1.17.43.5.16.95.14 1.3.09.4-.06 1.29-.53 1.48-1.04z" fill="#FFFFFF"/>
            </svg>
          </a>
          <?php if ($yt = getSetting('youtube_url')): ?>
            <a href="<?= e($yt) ?>" target="_blank" class="footer-social-btn" aria-label="YouTube">
              <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="20" cy="20" r="20" fill="#FF0000"/>
                <path d="M27.07 15.5c-.17-.64-.67-1.14-1.3-1.31C24.63 13.92 20 13.92 20 13.92s-4.63 0-5.77.27c-.63.17-1.13.67-1.3 1.31C12.65 16.64 12.65 19 12.65 19s0 2.36.28 3.5c.17.64.67 1.14 1.3 1.31 1.14.27 5.77.27 5.77.27s4.63 0 5.77-.27c.63-.17 1.13-.67 1.3-1.31.28-1.14.28-3.5.28-3.5s0-2.36-.28-3.5zM18.53 21.5v-5l4.33 2.5-4.33 2.5z" fill="#FFFFFF"/>
              </svg>
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Quick Links -->
      <div>
        <h4 class="footer-heading">Quick Links</h4>
        <ul class="footer-links">
          <li><a href="<?= pageUrl('index') ?>">Home</a></li>
          <li><a href="<?= pageUrl('about') ?>">About Us</a></li>
          <li><a href="<?= pageUrl('shop') ?>">Shop All</a></li>
          <li><a href="<?= pageUrl('contact') ?>">Contact Us</a></li>
          <li><a href="<?= pageUrl('terms') ?>">Terms &amp; Conditions</a></li>
          <li><a href="<?= pageUrl('privacy') ?>">Privacy Policy</a></li>
          <li><a href="<?= pageUrl('refund') ?>">Return &amp; Refund Policy</a></li>
        </ul>
      </div>

      <!-- Collections -->
      <div>
        <h4 class="footer-heading">Collections</h4>
        <ul class="footer-links">
          <li><a href="<?= categoryUrl('cotton-suits') ?>">Cotton Suits</a></li>
          <li><a href="<?= categoryUrl('masleen-suits') ?>">Masleen Suits</a></li>
          <li><a href="<?= categoryUrl('pakistani-lawn-suits') ?>">Pakistani Lawn</a></li>
          <li><a href="<?= categoryUrl('georgette-suits') ?>">Georgette Suits</a></li>
          <li><a href="<?= categoryUrl('chiffon-suits') ?>">Chiffon Suits</a></li>
          <li><a href="<?= categoryUrl('hand-work-suits') ?>">Hand Work Suits</a></li>
        </ul>
      </div>

      <!-- Contact -->
      <div>
        <h4 class="footer-heading">Contact Us</h4>
        <ul class="footer-contact-list">
          <li>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.27h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9a16 16 0 0 0 6.29 6.29l.62-.79a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <a href="tel:<?= preg_replace('/\D/', '', getSetting('site_phone', '9006666622')) ?>"><?= e(getSetting('site_phone', '+91 9006666622')) ?></a>
          </li>
          <li>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <a href="mailto:<?= e(getSetting('site_email', 'zebirlibas@gmail.com')) ?>"><?= e(getSetting('site_email', 'zebirlibas@gmail.com')) ?></a>
          </li>
          <li>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <span><?= e(getSetting('site_address', 'Dhipatoli Pundag Ranchi, Mirza Lane')) ?></span>
          </li>
        </ul>
      </div>

    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
      <p>© <?= date('Y') ?> Zebir All rights Reserved</p>
      <p style="color:#8d867d; font-size:0.78rem;">Made with ♥ in Jharkhand, India</p>
    </div>
  </div>
</footer>

<!-- Floating WhatsApp Button -->
<a href="https://wa.me/919006666622?text=Hello%20Zebir%20Libas%2C%20I%20would%20like%20to%20place%20an%20order." target="_blank" class="whatsapp-float-btn" aria-label="Chat with us on WhatsApp">
  <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M18.403 5.633A8.919 8.919 0 0012.053 3c-4.948 0-8.976 4.027-8.978 8.977 0 1.582.413 3.126 1.198 4.488L3 21.116l4.759-1.249a8.981 8.981 0 004.29 1.097h.004c4.947 0 8.975-4.027 8.977-8.977a8.926 8.926 0 00-2.627-6.354zM12.053 19.46h-.003a7.481 7.481 0 01-3.82-1.042l-.274-.162-2.842.745.759-2.771-.178-.284A7.447 7.447 0 014.542 11.98c0-4.12 3.355-7.476 7.48-7.476 1.996 0 3.872.778 5.283 2.19a7.442 7.442 0 012.189 5.286c-.001 4.122-3.356 7.48-7.44 7.48zm4.093-5.592c-.224-.112-1.327-.655-1.533-.73-.205-.075-.355-.112-.504.112-.15.224-.579.73-.71.88-.13.15-.262.168-.486.056-.224-.112-.947-.349-1.803-1.114-.666-.595-1.116-1.33-1.247-1.554-.131-.225-.014-.347.098-.459.101-.102.224-.262.336-.393.112-.131.149-.224.224-.374.075-.15.037-.281-.019-.393-.056-.112-.504-1.216-.69-1.664-.181-.436-.366-.377-.504-.384-.13-.006-.28-.008-.43-.008a.826.826 0 00-.597.28c-.205.224-.784.767-.784 1.87 0 1.103.803 2.169.915 2.318.112.15 1.581 2.415 3.832 3.387.535.231.954.369 1.282.472.536.171 1.024.146 1.408.089.431-.064 1.327-.542 1.514-1.066.187-.524.187-.973.131-1.066-.056-.094-.205-.15-.43-.262z" fill="#FFF"/>
  </svg>
</a>

<!-- External Scripts -->
 <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
 <script src="<?= assetUrl('js/main.js') ?>"></script>

<?php if ($flash = getFlash()): ?>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    showToast("<?= e($flash['message']) ?>", "<?= e($flash['type']) ?>");
  });
</script>
<?php endif; ?>

</body>
</html>
