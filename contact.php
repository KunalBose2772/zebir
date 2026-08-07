<?php
/**
 * ZEBIR LIBAS – Contact Us Page
 * Content mirrored from zebirlibas.com
 */
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = "Contact Us – Zebir Libas";
$pageDesc  = "Have a question or need support? Reach out to Zebir Libas — we're here to help.";
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $name    = sanitize($_POST['name'] ?? '');
    $email   = sanitize($_POST['email'] ?? '');
    $phone   = sanitize($_POST['phone'] ?? '');
    $subject = sanitize($_POST['subject'] ?? 'Contact Inquiry');
    $message = sanitize($_POST['message'] ?? '');

    if ($name && isValidEmail($email) && $message) {
        require_once __DIR__ . '/includes/mailer.php';
        $body = "<h2>New Contact Inquiry</h2>
                 <p><strong>From:</strong> $name ($email)</p>
                 <p><strong>Phone:</strong> $phone</p>
                 <p><strong>Subject:</strong> $subject</p>
                 <p><strong>Message:</strong><br>$message</p>";
        sendMail(getSetting('site_email', 'zebirlibas@gmail.com'), 'Zebir Libas', 'Contact Form: ' . $subject, emailWrapper($body));
        setFlash('success', 'Thank you for reaching out! We will get back to you shortly.');
        redirectTo('contact.php');
    } else {
        $msg = 'Please fill in all required fields with a valid email.';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<div class="py-4 page-breadcrumbs-header">
  <div class="container text-center">
    <h1 class="font-serif display-4">Contact Us</h1>
    <p class="text-muted mt-3" style="font-size:0.85rem; letter-spacing:1px; text-transform:uppercase;">
      <a href="<?= pageUrl('index') ?>">Home</a> / <span>Contact Us</span>
    </p>
  </div>
</div>

<!-- Speak With Us Section -->
<section class="py-5">
  <div class="container">

    <div class="section-header" style="margin-bottom: 3.5rem; text-align: center;">
      <span class="section-subtitle">Speak With Us</span>
      <h2 class="section-title font-serif" style="font-size: 2.2rem; margin-top: 10px;">Have a question or need support?<br>Reach out — we're here to help.</h2>
    </div>

    <div class="contact-grid-layout">

      <!-- Contact Info Cards -->
      <div class="contact-info-column">
        
        <div class="contact-info-header mb-4">
          <h3 class="font-serif" style="font-size: 1.5rem; margin-bottom: 8px;">Contact Information</h3>
          <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.6;">Our client service advisors are ready to assist you with order inquiries, sizing, and styling advice.</p>
        </div>

        <div class="contact-cards-grid">
          <!-- Address -->
          <div class="contact-detail-card">
            <div class="contact-icon-wrapper">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-gold)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div>
              <h4>Store Address</h4>
              <p>Dhipatoli Pundag Ranchi, Mirza Lane</p>
            </div>
          </div>

          <!-- Phone -->
          <div class="contact-detail-card">
            <div class="contact-icon-wrapper">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-gold)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.27h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9a16 16 0 0 0 6.29 6.29l.62-.79a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
            <div>
              <h4>Call Us</h4>
              <a href="tel:9006666622">+91 9006666622</a>
            </div>
          </div>

          <!-- Email -->
          <div class="contact-detail-card">
            <div class="contact-icon-wrapper">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-gold)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <div>
              <h4>Mail Us</h4>
              <a href="mailto:zebirlibas@gmail.com">zebirlibas@gmail.com</a>
            </div>
          </div>

          <!-- WhatsApp -->
          <div class="contact-detail-card">
            <div class="contact-icon-wrapper" style="background: rgba(37,211,102,0.08);">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            </div>
            <div>
              <h4>WhatsApp Support</h4>
              <a href="https://wa.me/919006666622" target="_blank">+91 9006666622</a>
            </div>
          </div>

          <!-- Support Hours -->
          <div class="contact-detail-card" style="grid-column: span 1;">
            <div class="contact-icon-wrapper">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-gold)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
              <h4>24/7 Concierge</h4>
              <p>Online support available around the clock.</p>
            </div>
          </div>
        </div>

      </div>

      <!-- Contact Form -->
      <div class="contact-form-wrapper">
        <?php if ($msg): ?>
          <div style="padding: 12px 18px; background: rgba(229,57,53,0.08); border: 1px solid rgba(229,57,53,0.25); border-radius: 6px; color: #e53935; font-size: 0.88rem; margin-bottom: 1.5rem;">
            <?= e($msg) ?>
          </div>
        <?php endif; ?>

        <form action="contact.php" method="POST" class="content-card compact-contact-form">
          <?= csrfField() ?>

          <h3 class="font-serif text-center" style="font-size: 1.4rem; margin-bottom: 1.5rem; letter-spacing: 0.5px;">Send Us a Message</h3>

          <div class="form-row-2">
            <div class="mb-3">
              <label class="form-label-small">Your Name *</label>
              <input type="text" name="name" class="newsletter-input form-control-compact" placeholder="Full name" required>
            </div>
            <div class="mb-3">
              <label class="form-label-small">Email Address *</label>
              <input type="email" name="email" class="newsletter-input form-control-compact" placeholder="you@example.com" required>
            </div>
          </div>

          <div class="form-row-2">
            <div class="mb-3">
              <label class="form-label-small">Phone Number</label>
              <input type="tel" name="phone" class="newsletter-input form-control-compact" placeholder="+91 XXXXX XXXXX">
            </div>
            <div class="mb-3">
              <label class="form-label-small">Subject</label>
              <input type="text" name="subject" class="newsletter-input form-control-compact" placeholder="Subject">
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label-small">Your Message *</label>
            <textarea name="message" rows="4" class="newsletter-input form-control-compact" placeholder="How can we help you?" required style="min-height: 100px; resize: vertical;"></textarea>
          </div>

          <button type="submit" class="btn-luxury btn-gold btn-full" style="padding: 14px; font-weight: 700; letter-spacing: 1.5px; font-size: 0.8rem;">SEND MESSAGE</button>
        </form>
      </div>

    </div>
  </div>
</section>

<style>
.contact-grid-layout {
  display: grid;
  grid-template-columns: 1fr;
  gap: 48px;
  align-items: start;
}
.contact-cards-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
}
.contact-detail-card {
  display: flex;
  align-items: center;
  gap: 16px;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  padding: 16px 20px;
  border-radius: 6px;
  transition: transform 0.2s ease, border-color 0.2s ease;
}
.contact-detail-card:hover {
  border-color: var(--accent-gold);
}
.contact-icon-wrapper {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: rgba(200, 150, 12, 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.contact-detail-card h4 {
  font-family: var(--font-serif);
  font-size: 0.95rem;
  margin: 0 0 4px 0;
  color: var(--accent-gold);
}
.contact-detail-card p, .contact-detail-card a {
  font-size: 0.85rem;
  color: var(--text-muted);
  margin: 0;
  text-decoration: none;
  transition: color 0.2s ease;
}
.contact-detail-card a:hover {
  color: var(--text-main);
}
.compact-contact-form {
  padding: 32px !important;
  border: 1px solid var(--border-color);
  border-radius: 8px;
}
.form-label-small {
  display: block;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--text-muted);
  margin-bottom: 6px;
}
.form-control-compact {
  width: 100%;
  border-radius: 3px;
  padding: 12px 14px;
}
.form-row-2 {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0;
}

@media (min-width: 768px) {
  .contact-cards-grid {
    grid-template-columns: 1fr 1fr;
  }
  .contact-detail-card:last-child {
    grid-column: span 2;
  }
  .form-row-2 {
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }
}
@media (min-width: 992px) {
  .contact-grid-layout {
    grid-template-columns: 1fr 1.2fr;
    gap: 60px;
  }
  .contact-cards-grid {
    grid-template-columns: 1fr;
  }
  .contact-detail-card:last-child {
    grid-column: span 1;
  }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
