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
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                  <linearGradient id="ig_grad" x1="2" y1="22" x2="22" y2="2" gradientUnits="userSpaceOnUse">
                    <stop offset="0" stop-color="#FEE411"/>
                    <stop offset="0.1" stop-color="#FEDB16"/>
                    <stop offset="0.2" stop-color="#FEC125"/>
                    <stop offset="0.3" stop-color="#FE983D"/>
                    <stop offset="0.4" stop-color="#FE5F5E"/>
                    <stop offset="0.5" stop-color="#FE2181"/>
                    <stop offset="0.6" stop-color="#9000DC"/>
                    <stop offset="0.8" stop-color="#5100F9"/>
                    <stop offset="1" stop-color="#2100FF"/>
                  </linearGradient>
                </defs>
                <path d="M12 2.16c3.2 0 3.58.01 4.85.07 3.25.15 4.77 1.69 4.92 4.92.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.15 3.23-1.67 4.77-4.92 4.92-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-3.26-.15-4.77-1.7-4.92-4.92-.06-1.27-.07-1.65-.07-4.85s.01-3.58.07-4.85c.15-3.23 1.67-4.77 4.92-4.92 1.27-.06 1.65-.07 4.85-.07M12 0C8.74 0 8.33.01 7.05.07 2.7.27.27 2.69.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.2 4.36 2.62 6.78 6.98 6.98 1.28.06 1.69.07 4.95.07s3.67-.01 4.95-.07c4.35-.2 6.78-2.62 6.98-6.98.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95c-.2-4.35-2.62-6.78-6.98-6.98C15.67.01 15.26 0 12 0zm0 5.84A6.16 6.16 0 1018.16 12 6.16 6.16 0 0012 5.84zm0 10.16A4 4 0 1116 12a4 4 0 01-4 4zm3.98-10.17a1.44 1.44 0 11-2.88 0 1.44 1.44 0 012.88 0z" fill="url(#ig_grad)"/>
              </svg>
            </a>
          <?php endif; ?>
          <?php if ($fb = getSetting('facebook_url')): ?>
            <a href="<?= e($fb) ?>" target="_blank" class="footer-social-btn" aria-label="Facebook">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="12" fill="#1877F2"/>
                <path d="M15.105 12.593l.096-2.584h-2.292V8.809c0-.772.154-1.139 1.127-1.139h1.507V5.177l-2.079-.009c-2.269 0-3.226 1.215-3.226 3.238v1.603H8.77v2.584h1.468v7.262h2.997v-7.262h1.87z" fill="#FFFFFF"/>
              </svg>
            </a>
          <?php endif; ?>
          <a href="https://wa.me/919006666622" target="_blank" class="footer-social-btn" aria-label="WhatsApp">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12.015 1.792c-5.632 0-10.213 4.582-10.213 10.214 0 1.956.549 3.84 1.595 5.51L1.93 22.845l5.485-1.439a10.151 10.151 0 004.6 1.107h.004c5.631 0 10.214-4.58 10.214-10.214 0-2.73-1.062-5.297-2.993-7.228-1.93-1.93-4.502-2.993-7.227-2.993v-.286z" fill="#25D366"/>
              <path d="M17.653 14.288c-.31-.155-1.834-.906-2.119-1.009-.285-.104-.492-.155-.7.155-.207.311-.803 1.01-.984 1.218-.181.207-.363.233-.673.078-1.164-.582-2.164-1.079-3.003-2.551-.208-.363.208-.337.802-1.526.104-.207.052-.389-.026-.544-.078-.155-.699-1.682-.957-2.304-.251-.605-.503-.523-.699-.533H8.7a.897.897 0 00-.647.306c-.285.31-1.087 1.062-1.087 2.59 0 1.528 1.113 3.004 1.268 3.21.155.207 2.19 3.344 5.308 4.69 2.082.898 2.76.767 3.251.646.559-.137 1.833-.751 2.092-1.476.259-.725.259-1.346.181-1.476-.077-.129-.284-.207-.594-.362z" fill="#FFFFFF"/>
            </svg>
          </a>
          <?php if ($yt = getSetting('youtube_url')): ?>
            <a href="<?= e($yt) ?>" target="_blank" class="footer-social-btn" aria-label="YouTube">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M21.582 6.186c-.23-.86-.908-1.538-1.768-1.768C18.254 4 12 4 12 4s-6.254 0-7.814.418c-.86.23-1.538.908-1.768 1.768C2 7.746 2 12 2 12s0 4.254.418 5.814c.23.86.908 1.538 1.768 1.768C5.746 20 12 20 12 20s6.254 0 7.814-.418c.86-.23 1.538-.908 1.768-1.768C22 16.254 22 12 22 12s0-4.254-.418-5.814z" fill="#FF0000"/>
                <path d="M10 15.464V8.536L16 12l-6 3.464z" fill="#FFFFFF"/>
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
