<?php
/**
 * ZEBIR LIBAS – Privacy Policy
 */
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = "Privacy Policy – Zebir Libas";
$pageDesc  = "Our privacy statement details how we collect, protect, and use your personal information when visiting Zebir Libas.";
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<div class="py-4 page-breadcrumbs-header">
  <div class="container text-center">
    <h1 class="font-serif display-4" style="font-size: clamp(2rem, 4vw, 3rem);">Privacy Policy</h1>
    <p class="text-muted mt-3" style="font-size:0.85rem; letter-spacing:1px; text-transform:uppercase;">
      <a href="<?= pageUrl('index') ?>">Home</a> / <span>Privacy Policy</span>
    </p>
  </div>
</div>

<section class="py-5">
  <div class="container" style="max-width: 900px; line-height: 1.8; color: var(--text-muted); font-size: 0.95rem;">

    <div class="content-card" style="padding: 40px; margin-bottom: 30px; border-radius: 12px;">
      <p class="mb-4">
        At Zebir Libas, accessible from <a href="<?= BASE_URL ?>" style="color: var(--accent-gold); font-weight: 600;"><?= BASE_URL ?></a>, one of our main priorities is the privacy of our visitors. This Privacy Policy document contains types of information that are collected and recorded by Zebir Libas and how we use it.
      </p>
      <p class="mb-0">
        If you have additional questions or require more information about our Privacy Policy, do not hesitate to contact us at <strong>zebirlibas@gmail.com</strong>.
      </p>
    </div>

    <div class="content-card" style="padding: 40px; margin-bottom: 30px; border-radius: 12px;">
      <h2 class="font-serif mb-4" style="color: var(--accent-gold); font-size: 1.8rem;">1. Information We Collect</h2>
      <p class="mb-3">
        The personal information that you are asked to provide, and the reasons why you are asked to provide it, will be made clear to you at the point we ask you to provide your personal information.
      </p>
      <ul style="padding-left: 20px; list-style-type: square;" class="mb-0">
        <li class="mb-2"><strong>Account Registration:</strong> When you register for an Account, we may ask for your contact information, including items such as name, company name, address, email address, and telephone number.</li>
        <li class="mb-2"><strong>Order Details:</strong> When you purchase items, we collect your shipping and billing addresses, phone number, and payment verification data (such as UPI screenshots) to process your order securely.</li>
        <li class="mb-2"><strong>Communications:</strong> If you contact us directly, we may receive additional information about you such as your name, email address, phone number, the contents of the message and/or attachments you may send us, and any other information you may choose to provide.</li>
      </ul>
    </div>

    <div class="content-card" style="padding: 40px; margin-bottom: 30px; border-radius: 12px;">
      <h2 class="font-serif mb-4" style="color: var(--accent-gold); font-size: 1.8rem;">2. How We Use Your Information</h2>
      <p class="mb-3">
        We use the information we collect in various ways, including to:
      </p>
      <ul style="padding-left: 20px; list-style-type: square;" class="mb-0">
        <li class="mb-2">Provide, operate, and maintain our website.</li>
        <li class="mb-2">Improve, personalize, and expand our storefront and collections.</li>
        <li class="mb-2">Understand and analyze how you use our website.</li>
        <li class="mb-2">Process transactions, manage order tracking, and facilitate custom sizing services.</li>
        <li class="mb-2">Communicate with you, either directly or through one of our partners, including for customer service, to provide you with updates and other information relating to the website.</li>
        <li class="mb-2">Send you order notifications, invoice copies, and tracking updates.</li>
      </ul>
    </div>

    <div class="content-card" style="padding: 40px; margin-bottom: 30px; border-radius: 12px;">
      <h2 class="font-serif mb-4" style="color: var(--accent-gold); font-size: 1.8rem;">3. Log Files & Analytics</h2>
      <p class="mb-0">
        Zebir Libas follows a standard procedure of using log files. These files log visitors when they visit websites. All hosting companies do this and a part of hosting services' analytics. The information collected by log files includes internet protocol (IP) addresses, browser type, Internet Service Provider (ISP), date and time stamp, referring/exit pages, and possibly the number of clicks. These are not linked to any information that is personally identifiable. The purpose of the information is for analyzing trends, administering the site, tracking users' movement on the website, and gathering demographic information.
      </p>
    </div>

    <div class="content-card" style="padding: 40px; margin-bottom: 30px; border-radius: 12px;">
      <h2 class="font-serif mb-4" style="color: var(--accent-gold); font-size: 1.8rem;">4. Cookies and Web Beacons</h2>
      <p class="mb-0">
        Like any other website, Zebir Libas uses "cookies". These cookies are used to store information including visitors' preferences, and the pages on the website that the visitor accessed or visited. The information is used to optimize the users' experience by customizing our web page content based on visitors' browser type and/or other information.
      </p>
    </div>

    <div class="content-card" style="padding: 40px; margin-bottom: 30px; border-radius: 12px;">
      <h2 class="font-serif mb-4" style="color: var(--accent-gold); font-size: 1.8rem;">5. Third-Party Privacy Policies</h2>
      <p class="mb-3">
        Zebir Libas's Privacy Policy does not apply to other advertisers or websites. Thus, we are advising you to consult the respective Privacy Policies of these third-party servers for more detailed information. It may include their practices and instructions about how to opt-out of certain options.
      </p>
      <p class="mb-0">
        You can choose to disable cookies through your individual browser options. To know more detailed information about cookie management with specific web browsers, it can be found at the browsers' respective websites.
      </p>
    </div>

    <div class="content-card" style="padding: 40px; border-radius: 12px;">
      <h2 class="font-serif mb-4" style="color: var(--accent-gold); font-size: 1.8rem;">6. Consent</h2>
      <p class="mb-0">
        By using our website, you hereby consent to our Privacy Policy and agree to its terms.
      </p>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
