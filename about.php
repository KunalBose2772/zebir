<?php
/**
 * ZEBIR LIBAS – About Us Page
 * Content mirrored from zebirlibas.com
 */
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = "About Us – Zebir Libas";
$pageDesc  = "Welcome to Zebir, where elegance meets empowerment. We specialize in crafting exquisite ladies' suitwear designed for the modern woman who embraces confidence, sophistication, and timeless style.";
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<div class="py-4 page-breadcrumbs-header">
  <div class="container text-center">
    <h1 class="font-serif display-4">About Us</h1>
    <p class="text-muted mt-3" style="font-size:0.85rem; letter-spacing:1px; text-transform:uppercase;">
      <a href="<?= pageUrl('index') ?>">Home</a> / <span>About Us</span>
    </p>
  </div>
</div>

<!-- Our Story -->
<section class="py-5" style="background: var(--bg-secondary);">
  <div class="container">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center;">
      <div>
        <span class="section-subtitle">Our Story</span>
        <h2 class="font-serif" style="font-size: clamp(1.8rem, 3vw, 2.4rem); margin-bottom: 1.5rem;">Founded in 2024</h2>
        <p style="color: var(--text-muted); line-height: 1.9; font-size: 1rem; margin-bottom: 1.2rem;">
          Founded in 2024, Zebir was born from a passion for redefining women wear. Inspired by the strength and grace of today's women, we set out to create suits that celebrate individuality while offering unmatched comfort and versatility.
        </p>
        <a href="<?= pageUrl('shop') ?>" class="btn-luxury btn-gold" style="display: inline-flex; margin-top: 0.5rem;">Shop Now</a>
      </div>
      <div>
        <img src="<?= assetUrl('images/ABOUT_ZEBIR.webp') ?>" alt="About Zebir Libas – Our Story" style="width:100%; border-radius:20px; box-shadow: var(--shadow-md); object-fit: cover;">
      </div>
    </div>
  </div>
</section>

<!-- About Us -->
<section class="py-5">
  <div class="container">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center;">
      <div>
        <img src="<?= assetUrl('images/ABOUT_ZEBIR.webp') ?>" alt="Zebir Libas – Elegance meets Empowerment" style="width:100%; border-radius:20px; box-shadow: var(--shadow-md); object-fit: cover;">
      </div>
      <div>
        <span class="section-subtitle">About Us</span>
        <h2 class="font-serif" style="font-size: clamp(1.8rem, 3vw, 2.4rem); margin-bottom: 1.5rem;">Elegance Meets Empowerment</h2>
        <p style="color: var(--text-muted); line-height: 1.9; font-size: 1rem; margin-bottom: 1.2rem;">
          Welcome to Zebir, where elegance meets empowerment. We specialize in crafting exquisite ladies' suitwear designed for the modern woman who embraces confidence, sophistication, and timeless style.
        </p>
        <p style="color: var(--text-muted); line-height: 1.9; font-size: 1rem;">
          At Zebir, we believe that a well-tailored suit is more than just clothing—it's a statement. Whether you're dressing for the boardroom, a special occasion, or everyday sophistication, our curated collection blends premium fabrics, impeccable craftsmanship, and contemporary designs to ensure you look and feel your best.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- Our Mission -->
<section class="py-5" style="background: var(--bg-secondary);">
  <div class="container" style="max-width: 900px; text-align: center;">
    <span class="section-subtitle">Our Mission</span>
    <h2 class="font-serif section-title mb-4">Every Woman Deserves to Look Luxurious</h2>
    <p style="color: var(--text-muted); line-height: 1.9; font-size: 1rem; margin-bottom: 1.2rem;">
      At Zebir, we believe every woman deserves to look effortlessly luxurious—without the extravagant price tag. Our mission is to empower you with high-end style, unmatched comfort, and accessible pricing, because true elegance shouldn't cost a fortune.
    </p>
    <p style="color: var(--text-muted); line-height: 1.9; font-size: 1rem; margin-bottom: 1.2rem;">
      We craft meticulously designed suits that exude sophistication, using premium fabrics and smart tailoring to ensure you feel as confident as you look. Whether you're stepping into the boardroom or a special occasion, our pieces are made to help you command attention, comfortably and affordably.
    </p>
    <p style="color: var(--text-main); font-weight: 600; font-size: 1.05rem; font-style: italic; line-height: 1.8;">
      "Because when you look rich, you feel rich—and we're here to make that dream a reality for every woman."
    </p>
  </div>
</section>

<!-- Why Choose Us -->
<section class="py-5">
  <div class="container">
    <div class="section-header">
      <span class="section-subtitle">Why Choose Us?</span>
      <h2 class="section-title font-serif">Where Every Outfit Feels Made Just for You</h2>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px;">

      <div class="trust-item" style="text-align: left; padding: 1.8rem 1.5rem;">
        <div style="width: 46px; height: 46px; border-radius: 50%; background: rgba(200,150,12,0.12); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent-gold)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <h4 class="font-serif" style="margin-bottom: 0.5rem; font-size: 1.1rem;">Tailored for Perfection</h4>
        <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.7;">Every piece is designed with precision to flatter diverse body types</p>
      </div>

      <div class="trust-item" style="text-align: left; padding: 1.8rem 1.5rem;">
        <div style="width: 46px; height: 46px; border-radius: 50%; background: rgba(200,150,12,0.12); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent-gold)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </div>
        <h4 class="font-serif" style="margin-bottom: 0.5rem; font-size: 1.1rem;">Luxury Fabrics</h4>
        <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.7;">We source high-quality materials for durability and a flawless finish.</p>
      </div>

      <div class="trust-item" style="text-align: left; padding: 1.8rem 1.5rem;">
        <div style="width: 46px; height: 46px; border-radius: 50%; background: rgba(200,150,12,0.12); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent-gold)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <h4 class="font-serif" style="margin-bottom: 0.5rem; font-size: 1.1rem;">Unmatched Comfort &amp; Affordability</h4>
        <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.7;">Experience effortless style without compromising on ease or budget.</p>
      </div>

      <div class="trust-item" style="text-align: left; padding: 1.8rem 1.5rem;">
        <div style="width: 46px; height: 46px; border-radius: 50%; background: rgba(200,150,12,0.12); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent-gold)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <h4 class="font-serif" style="margin-bottom: 0.5rem; font-size: 1.1rem;">Secured Payment</h4>
        <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.7;">Secure checkout with trusted gateways and encrypted payment protection</p>
      </div>

      <div class="trust-item" style="text-align: left; padding: 1.8rem 1.5rem;">
        <div style="width: 46px; height: 46px; border-radius: 50%; background: rgba(200,150,12,0.12); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent-gold)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <h4 class="font-serif" style="margin-bottom: 0.5rem; font-size: 1.1rem;">Empowering Women</h4>
        <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.7;">We champion confidence, helping you own every room you walk into.</p>
      </div>

    </div>
  </div>
</section>

<!-- Honest Reviews / Testimonials -->
<section class="py-5" style="background: var(--bg-secondary);">
  <div class="container">
    <div class="section-header">
      <span class="section-subtitle">Honest Reviews</span>
      <h2 class="section-title font-serif">What Our Customers Say</h2>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 28px;">

      <div class="content-card" style="padding: 2rem;">
        <div style="display: flex; gap: 4px; color: #f59e0b; margin-bottom: 1rem;">
          <?php for($i=0; $i<5; $i++): ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <?php endfor; ?>
        </div>
        <p style="font-size: 0.92rem; color: var(--text-muted); line-height: 1.75; font-style: italic; margin-bottom: 1.2rem;">
          "Zebir helped me rediscover power dressing. I've always struggled to find suits that felt both elegant and comfortable—until I found Zebir. Their pieces are beautifully tailored, flattering, and perfect for both meetings and evenings out. I feel confident every time I wear one."
        </p>
        <div style="display: flex; align-items: center; gap: 12px;">
          <div style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-gold), #f5c842); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-family: var(--font-heading); font-size: 1rem;">A</div>
          <div>
            <strong style="font-size: 0.9rem;">Aarushi Mehta</strong>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">Marketing Executive</p>
          </div>
        </div>
      </div>

      <div class="content-card" style="padding: 2rem;">
        <div style="display: flex; gap: 4px; color: #f59e0b; margin-bottom: 1rem;">
          <?php for($i=0; $i<5; $i++): ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <?php endfor; ?>
        </div>
        <p style="font-size: 0.92rem; color: var(--text-muted); line-height: 1.75; font-style: italic; margin-bottom: 1.2rem;">
          "Zebir has cracked the code—high-quality fabrics, stunning fits, and a price that doesn't make you think twice. Every time I wear a Zebir outfit, I get compliments!"
        </p>
        <div style="display: flex; align-items: center; gap: 12px;">
          <div style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-gold), #f5c842); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-family: var(--font-heading); font-size: 1rem;">S</div>
          <div>
            <strong style="font-size: 0.9rem;">Sneha Sivaji</strong>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">Entrepreneur</p>
          </div>
        </div>
      </div>

      <div class="content-card" style="padding: 2rem;">
        <div style="display: flex; gap: 4px; color: #f59e0b; margin-bottom: 1rem;">
          <?php for($i=0; $i<5; $i++): ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <?php endfor; ?>
        </div>
        <p style="font-size: 0.92rem; color: var(--text-muted); line-height: 1.75; font-style: italic; margin-bottom: 1.2rem;">
          "My go-to brand for elegant suits. From boardroom to dinner date, Zebir suits never fail to impress. The detailing is subtle yet striking, and the comfort is unmatched."
        </p>
        <div style="display: flex; align-items: center; gap: 12px;">
          <div style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-gold), #f5c842); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-family: var(--font-heading); font-size: 1rem;">S</div>
          <div>
            <strong style="font-size: 0.9rem;">Sneha</strong>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">Artist</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Newsletter CTA -->
<section class="py-5" style="background: linear-gradient(135deg, #0d0d0d 0%, #1a1208 100%);">
  <div class="container text-center" style="max-width: 700px;">
    <span class="section-subtitle">Stay Updated</span>
    <h2 class="font-serif" style="color: #fff; font-size: clamp(1.6rem, 3vw, 2.2rem); margin-bottom: 1rem;">Keep Up With The Most Recent Styles</h2>
    <p style="color: rgba(255,255,255,0.65); line-height: 1.8; margin-bottom: 2rem; font-size: 0.95rem;">
      Subscribe to receive exclusive offers, new arrivals, and style inspiration directly in your inbox.
    </p>
    <form style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
      <input type="email" placeholder="Your email address" class="newsletter-input" style="max-width: 320px; border-radius: 999px;">
      <button type="submit" class="btn-luxury btn-gold">Subscribe</button>
    </form>
  </div>
</section>

<style>
@media (max-width: 768px) {
  section .container > div[style*="grid-template-columns: 1fr 1fr"] {
    grid-template-columns: 1fr !important;
    gap: 32px !important;
  }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
