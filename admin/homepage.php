<?php
/**
 * ZEBIR LIBAS – Admin Homepage Configuration (Premium Redesign)
 */
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDB();
$categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
$defaultCategories = getHomepageCategoryDefaults();
if (empty($categories)) {
    $insert = $pdo->prepare("INSERT INTO categories (name, slug, description, image, sort_order, is_active) VALUES (?, ?, ?, ?, ?, 1)");
    foreach ($defaultCategories as $index => $seed) {
        $insert->execute([$seed['name'], $seed['slug'], $seed['description'], $seed['image'], $index + 1]);
    }
    $categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
}

$heroConfig = getSettingArray('home_hero_config', []);
$heroMode = getSetting('home_hero_mode', 'content');
$selectedCategoryIds = array_values(array_filter(array_map('intval', getSettingArray('home_category_ids', []))));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $heroMode = in_array($_POST['home_hero_mode'] ?? 'content', ['content', 'image']) ? $_POST['home_hero_mode'] : 'content';
    $submittedIds = array_values(array_filter(array_map('intval', (array)($_POST['home_category_ids'] ?? []))));
    $availableIds = array_map(fn($cat) => $cat['id'], $categories);
    $selectedCategoryIds = array_values(array_intersect($submittedIds, $availableIds));

    if (count($selectedCategoryIds) < 6) {
        $selectedCategoryIds = array_slice(array_unique(array_merge($selectedCategoryIds, $availableIds)), 0, 6);
        setFlash('warning', 'Pick at least 6 categories. We added the first active categories for you.');
    }

    $slides = [];
    $indices = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'hero_title_') === 0) {
            $indices[] = (int)substr($key, 11);
        }
    }
    if (empty($indices)) {
        foreach ($_FILES as $key => $file) {
            if (strpos($key, 'hero_image_') === 0) {
                $indices[] = (int)substr($key, 11);
            }
        }
    }
    $indices = array_unique($indices);
    sort($indices);

    foreach ($indices as $i) {
        $existingImage = trim($_POST['hero_image_existing_' . $i] ?? '');
        $imageName = $existingImage;

        if (!empty($_FILES['hero_image_' . $i]['name'])) {
            $uploaded = uploadImage($_FILES['hero_image_' . $i], UPLOAD_BANNERS, 1920, 1200);
            if ($uploaded) {
                $imageName = $uploaded;
            }
        }

        $slide = [
            'image' => $imageName,
            'title' => sanitize($_POST['hero_title_' . $i] ?? ''),
            'description' => sanitize($_POST['hero_description_' . $i] ?? ''),
            'button_text' => sanitize($_POST['hero_button_text_' . $i] ?? ''),
            'button_link' => filter_var(trim($_POST['hero_button_link_' . $i] ?? ''), FILTER_SANITIZE_URL),
            'link' => filter_var(trim($_POST['hero_link_' . $i] ?? ''), FILTER_SANITIZE_URL),
        ];

        if ($slide['image'] || $slide['title'] || $slide['description'] || $slide['button_text'] || $slide['button_link'] || $slide['link']) {
            $slides[] = $slide;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
    $stmt->execute(['home_hero_mode', $heroMode]);
    $stmt->execute(['home_category_ids', json_encode(array_values($selectedCategoryIds), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]);
    $stmt->execute(['home_hero_config', json_encode(array_values($slides), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]);

    setFlash('success', 'Homepage settings saved successfully.');
    redirectTo('admin/homepage');
}

if (empty($selectedCategoryIds)) {
    $selectedCategoryIds = array_slice(array_map(fn($cat) => $cat['id'], $categories), 0, 6);
}

if (empty($heroConfig)) {
    $heroConfig = [
        ['image' => 'HERO_03_WEDDING_COLLECTION_DESKTOP.png', 'title' => 'Bridal Collection', 'description' => 'Discover couture-inspired suits designed for unforgettable celebrations and graceful presence.', 'button_text' => 'Shop Now', 'button_link' => 'shop', 'link' => ''],
        ['image' => 'HERO_01_LUXURY_COLLECTION_DESKTOP.png', 'title' => 'Signature Couture', 'description' => 'Modern luxury crafted in every detail.', 'button_text' => 'Shop Now', 'button_link' => 'shop', 'link' => ''],
        ['image' => 'HERO_02_FESTIVE_COLLECTION_DESKTO.png', 'title' => 'Festive Edit', 'description' => 'Find richly textured suits and festive pieces made to shine through every occasion.', 'button_text' => 'Shop Now', 'button_link' => 'shop', 'link' => ''],
    ];
}
?>

<div class="admin-page-header">
  <h2>Homepage Configuration</h2>
</div>

<form action="homepage" method="POST" enctype="multipart/form-data">
  <?= csrfField() ?>

  <div style="display:flex; flex-direction:column; gap:24px;">
    
    <!-- Hero Slider Configuration -->
    <div class="admin-card">
      <h3 style="margin-top:0; font-size:1.15rem; font-weight:700; margin-bottom: 20px;">Homepage Hero Carousel</h3>

      <div class="form-group">
        <label class="field-label">Choose your Hero Layout Mode</label>
        <div class="toggle-pill-group" style="margin-top: 8px;">
          <label class="toggle-pill">
            <input type="radio" name="home_hero_mode" value="content" <?= $heroMode === 'content' ? 'checked' : '' ?>>
            <span>Content Slider</span>
          </label>
          <label class="toggle-pill">
            <input type="radio" name="home_hero_mode" value="image" <?= $heroMode === 'image' ? 'checked' : '' ?>>
            <span>Image Only</span>
          </label>
        </div>
        <span style="display:block; font-size:0.75rem; color:var(--text-muted); margin-top:8px;">Choose content slider for dynamic texts and custom CTAs, or image only for clean custom-designed graphics.</span>
      </div>

      <div id="slides-container" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:24px; margin-top: 32px;">
        <?php 
        $slidesCount = count($heroConfig);
        for ($i = 1; $i <= $slidesCount; $i++):
            $slide = $heroConfig[$i - 1] ?? ['image' => '', 'title' => '', 'description' => '', 'button_text' => '', 'button_link' => '', 'link' => ''];
        ?>
          <div class="slide-card" data-index="<?= $i ?>" style="background: var(--border-light); border: 1px solid var(--border-color); padding: 20px; border-radius: 12px; display: flex; flex-direction: column; justify-content: space-between; position: relative;">
            <div>
              <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                <h4 style="margin:0; font-size:0.95rem; font-weight:700; color: var(--primary-color);">Carousel Slide <?= $i ?></h4>
                <button type="button" onclick="this.closest('.slide-card').remove();" style="background:none; border:none; color:#dc2626; cursor:pointer; font-size:0.75rem; font-weight:600; padding:0;">Remove</button>
              </div>

              <div class="form-group">
                <label class="field-label">Slide Banner Image</label>
                <?php if (!empty($slide['image'])): ?>
                  <img src="<?= e(strpos($slide['image'], 'http') === 0 ? $slide['image'] : bannerImageUrl($slide['image'])) ?>" alt="Slide <?= $i ?>" style="width:100%; height:110px; object-fit:cover; border-radius:8px; display:block; margin-bottom:12px; border: 1px solid var(--border-color);">
                  <input type="hidden" name="hero_image_existing_<?= $i ?>" value="<?= e($slide['image']) ?>">
                <?php endif; ?>
                <input type="file" name="hero_image_<?= $i ?>" accept="image/*" class="form-control">
              </div>

              <div class="form-group hero-content-only">
                <label class="field-label">Headline Copy</label>
                <input type="text" name="hero_title_<?= $i ?>" class="form-control" placeholder="e.g. Signature Couture" value="<?= e($slide['title'] ?? '') ?>">
              </div>

              <div class="form-group hero-content-only">
                <label class="field-label">Slide Description</label>
                <textarea name="hero_description_<?= $i ?>" class="form-control" style="height:60px; resize:none;" placeholder="A brief elegant line describing the slide..."><?= e($slide['description'] ?? '') ?></textarea>
              </div>

              <div class="admin-form-inline-grid form-group hero-content-only">
                <div>
                  <label class="field-label">CTA Text</label>
                  <input type="text" name="hero_button_text_<?= $i ?>" class="form-control" placeholder="Shop Collection" value="<?= e($slide['button_text'] ?? '') ?>">
                </div>
                <div>
                  <label class="field-label">CTA Link</label>
                  <input type="text" name="hero_button_link_<?= $i ?>" class="form-control" placeholder="shop.php" value="<?= e($slide['button_link'] ?? '') ?>">
                </div>
              </div>

              <div class="form-group hero-image-only">
                <label class="field-label">Fallback Banner Link</label>
                <input type="text" name="hero_link_<?= $i ?>" class="form-control" placeholder="e.g. shop.php?category=silk" value="<?= e($slide['link'] ?? '') ?>">
                <span style="display:block; font-size:0.75rem; color:var(--text-muted); margin-top:4px;">Useful fallback link when in image-only layout mode.</span>
              </div>
            </div>
          </div>
        <?php endfor; ?>
      </div>

      <div style="display:flex; justify-content:flex-end; margin-top: 20px;">
        <button type="button" id="add-slide-btn" class="btn-admin btn-admin-primary" style="height:32px; font-size:0.75rem; padding: 0 12px;">+ Add Slide</button>
      </div>
      </div>
    </div>

    <!-- Homepage featured categories selector -->
    <div class="admin-card">
      <h3 style="margin-top:0; font-size:1.15rem; font-weight:700; margin-bottom: 8px;">Homepage Featured Categories</h3>
      <p style="font-size:0.8rem; color: var(--text-muted); margin-bottom: 24px; margin-top: 0;">Select exactly 6 categories to show in the homepage grid. If fewer than 6 are picked, defaults will automatically populate the remaining slots.</p>
      
      <?php if (empty($categories)): ?>
        <div style="font-size: 0.85rem; color: #dc2626; font-weight: 600;">No active categories available. Please create categories first.</div>
      <?php else:
        $defaultImages = array_column($defaultCategories, 'image', 'slug');
      ?>
        <div class="category-select-grid">
          <?php foreach ($categories as $cat):
            $categoryImage = !empty($cat['image']) ? categoryImageUrl($cat['image']) : assetUrl($defaultImages[$cat['slug']] ?? 'images/placeholder.jpg');
          ?>
            <label class="category-card">
              <input type="checkbox" name="home_category_ids[]" value="<?= $cat['id'] ?>" <?= in_array($cat['id'], $selectedCategoryIds, true) ? 'checked' : '' ?> />
              <div class="category-card-thumb" style="background-image:url('<?= e($categoryImage) ?>');"></div>
              <div class="category-card-title"><?= e($cat['name']) ?></div>
            </label>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div style="display:flex; justify-content:flex-start; margin-top: 8px;">
      <button type="submit" class="btn-admin btn-admin-gold">Save Configurations</button>
    </div>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modes = document.querySelectorAll('input[name="home_hero_mode"]');
    function updateHeroFields() {
        const activeMode = document.querySelector('input[name="home_hero_mode"]:checked') ? document.querySelector('input[name="home_hero_mode"]:checked').value : 'content';
        document.querySelectorAll('.hero-content-only').forEach(el => {
            el.style.display = (activeMode === 'content') ? 'block' : 'none';
        });
        document.querySelectorAll('.hero-image-only').forEach(el => {
            el.style.display = (activeMode === 'image') ? 'block' : 'none';
        });
    }
    modes.forEach(input => input.addEventListener('change', updateHeroFields));
    updateHeroFields();

    // Dynamic slide addition logic
    const container = document.getElementById('slides-container');
    const addBtn = document.getElementById('add-slide-btn');
    let nextIndex = container.children.length ? Math.max(...Array.from(container.children).map(c => parseInt(c.dataset.index || 0))) + 1 : 1;

    addBtn.addEventListener('click', function() {
        const index = nextIndex++;
        const cardHtml = `
          <div class="slide-card" data-index="${index}" style="background: var(--border-light); border: 1px solid var(--border-color); padding: 20px; border-radius: 12px; display: flex; flex-direction: column; justify-content: space-between; position: relative;">
            <div>
              <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                <h4 style="margin:0; font-size:0.95rem; font-weight:700; color: var(--primary-color);">Carousel Slide ${index}</h4>
                <button type="button" onclick="this.closest('.slide-card').remove();" style="background:none; border:none; color:#dc2626; cursor:pointer; font-size:0.75rem; font-weight:600; padding:0;">Remove</button>
              </div>

              <div class="form-group">
                <label class="field-label">Slide Banner Image</label>
                <input type="file" name="hero_image_${index}" accept="image/*" class="form-control">
              </div>

              <div class="form-group hero-content-only">
                <label class="field-label">Headline Copy</label>
                <input type="text" name="hero_title_${index}" class="form-control" placeholder="e.g. Signature Couture" value="">
              </div>

              <div class="form-group hero-content-only">
                <label class="field-label">Slide Description</label>
                <textarea name="hero_description_${index}" class="form-control" style="height:60px; resize:none;" placeholder="A brief elegant line describing the slide..."></textarea>
              </div>

              <div class="admin-form-inline-grid form-group hero-content-only">
                <div>
                  <label class="field-label">CTA Text</label>
                  <input type="text" name="hero_button_text_${index}" class="form-control" placeholder="Shop Collection" value="">
                </div>
                <div>
                  <label class="field-label">CTA Link</label>
                  <input type="text" name="hero_button_link_${index}" class="form-control" placeholder="shop.php" value="">
                </div>
              </div>

              <div class="form-group hero-image-only">
                <label class="field-label">Fallback Banner Link</label>
                <input type="text" name="hero_link_${index}" class="form-control" placeholder="e.g. shop.php?category=silk" value="">
                <span style="display:block; font-size:0.75rem; color:var(--text-muted); margin-top:4px;">Useful fallback link when in image-only layout mode.</span>
              </div>
            </div>
          </div>
        `;
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = cardHtml.trim();
        const newCard = tempDiv.firstChild;
        container.appendChild(newCard);
        updateHeroFields();
    });
});
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
