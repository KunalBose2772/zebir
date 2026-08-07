<?php
/**
 * ZEBIR LIBAS – Return, Refund & Shipping Policy
 */
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = "Return, Refund & Shipping Policy – Zebir Libas";
$pageDesc  = "Our guidelines regarding shipping, delivery times, return procedures, and refund status for Zebir Libas orders.";
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<div class="py-4 page-breadcrumbs-header">
  <div class="container text-center">
    <h1 class="font-serif display-4" style="font-size: clamp(2rem, 4vw, 3rem);">Returns & Shipping</h1>
    <p class="text-muted mt-3" style="font-size:0.85rem; letter-spacing:1px; text-transform:uppercase;">
      <a href="<?= pageUrl('index') ?>">Home</a> / <span>Returns &amp; Shipping</span>
    </p>
  </div>
</div>

<section class="py-5">
  <div class="container" style="max-width: 900px; line-height: 1.8; color: var(--text-muted); font-size: 0.95rem;">
    
    <div class="content-card" style="padding: 40px; margin-bottom: 30px; border-radius: 12px;">
      <h2 class="font-serif mb-4" style="color: var(--accent-gold); font-size: 1.8rem;">1. Shipping & Delivery</h2>
      <p class="mb-3">
        At Zebir Libas, we work with leading shipping partners across India (such as Delhivery, BlueDart, and DTDC) to ensure your premium unstitched suit sets reach you securely and promptly.
      </p>
      <ul class="mb-4" style="padding-left: 20px; list-style-type: square;">
        <li class="mb-2"><strong>Dispatch Timeline:</strong> All orders are dispatched within 24 to 48 business hours from our Ranchi atelier.</li>
        <li class="mb-2"><strong>Transit Times:</strong> Standard delivery takes 4 to 7 business days depending on your location across India.</li>
        <li class="mb-2"><strong>Shipping Fees:</strong> We offer complimentary standard shipping on all orders over ₹999. For orders below ₹999, a flat shipping charge of ₹99 applies.</li>
        <li class="mb-2"><strong>Tracking:</strong> Once your package is dispatched, a tracking AWB number and courier link will be shared via email and SMS.</li>
      </ul>
    </div>

    <div class="content-card" style="padding: 40px; margin-bottom: 30px; border-radius: 12px;">
      <h2 class="font-serif mb-4" style="color: var(--accent-gold); font-size: 1.8rem;">2. Return & Exchange Policy</h2>
      <p class="mb-3">
        Since we offer premium, unstitched suit sets, items are eligible for exchange only under the following strict conditions:
      </p>
      <ul class="mb-4" style="padding-left: 20px; list-style-type: square;">
        <li class="mb-2"><strong>Damaged or Defective Items:</strong> If you receive a product with a manufacturing defect, fabric damage, or embroidery issue, you must report it within 48 hours of delivery.</li>
        <li class="mb-2"><strong>Wrong Product Delivered:</strong> If you receive an item different from the design you ordered, we will arrange a return pickup and ship the correct item.</li>
        <li class="mb-2"><strong>Condition:</strong> Items submitted for exchange must be unstitched, unwashed, unaltered, and returned with the original brand tags and packaging intact.</li>
      </ul>
    </div>

    <div class="content-card" style="padding: 40px; margin-bottom: 30px; border-radius: 12px;">
      <h2 class="font-serif mb-4" style="color: var(--accent-gold); font-size: 1.8rem;">3. Refund Policy</h2>
      <p class="mb-3">
        Refunds are processed solely for orders that qualify under our return criteria (e.g. damaged goods or incorrect shipments that cannot be replaced):
      </p>
      <ul class="mb-4" style="padding-left: 20px; list-style-type: square;">
        <li class="mb-2"><strong>Mode of Refund:</strong> Once the returned package is received and inspected at our atelier, the refund will be credited back to your original source of payment (for online orders) or to your preferred bank account/UPI ID (for Cash on Delivery orders).</li>
        <li class="mb-2"><strong>Processing Time:</strong> Refunds take 5 to 7 business days to reflect in your account after confirmation of receipt from our quality check team.</li>
      </ul>
    </div>

    <div class="content-card" style="padding: 40px; border-radius: 12px;">
      <h2 class="font-serif mb-4" style="color: var(--accent-gold); font-size: 1.8rem;">4. How to Request an Exchange/Refund</h2>
      <p class="mb-3">
        To initiate an exchange or report a defect:
      </p>
      <p class="mb-3">
        1. Capture clear photographs or an unboxing video showing the shipping label and the specific damage or issue.
      </p>
      <p class="mb-3">
        2. Contact our client concierge immediately on WhatsApp at <a href="https://wa.me/919006666622" target="_blank" style="color: var(--accent-gold); text-decoration: underline; font-weight: 600;">+91 9006666622</a> or send an email to <a href="mailto:zebirlibas@gmail.com" style="color: var(--accent-gold); text-decoration: underline; font-weight: 600;">zebirlibas@gmail.com</a>.
      </p>
      <p class="mb-0">
        Our customer support team is available 24/7 to resolve your inquiries as quickly as possible.
      </p>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
