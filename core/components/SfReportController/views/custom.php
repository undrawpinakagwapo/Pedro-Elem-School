<?php
// SfReportController/views/custom.php
// DESIGN-FIRST view: grid of report tiles

$reports = isset($reports) && is_array($reports) ? $reports : [
  ['title' => 'Registration Form',                          'code' => 'SF1',  'href' => '#', 'img' => 'src/images/logos/document.png'],
  ['title' => 'Learners Daily Class Attendance',            'code' => 'SF2',  'href' => '#', 'img' => 'src/images/logos/document.png'],
  ['title' => 'Learners Progress Report Card',              'code' => 'SF9',  'href' => '#', 'img' => 'src/images/logos/document.png'],
  ['title' => 'Learners Permanent Academic Record',         'code' => 'SF10', 'href' => '#', 'img' => 'src/images/logos/document.png'],
  ['title' => 'Monthly learners movements and attendance',  'code' => 'SF4',  'href' => '#', 'img' => 'src/images/logos/document.png'],
];

if (!function_exists('e')) {
  function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('asset_url')) {
  function asset_url($p) {
    $p = (string)$p;
    if ($p === '') return '';
    if (preg_match('#^https?://#i', $p)) return $p;
    if (strpos($p, '/') === 0) return $p;
    return '/' . ltrim($p, '/');
  }
}
?>

<div class="sf-report">

  <div class="sf-report__header">
    <h2 class="sf-report__title">AVAILABLE DOCUMENTS</h2>
  </div>

  <div class="sf-report__grid">
    <?php foreach ($reports as $r):
      $imgSrc = asset_url($r['img'] ?? '');
      $href   = (string)($r['href'] ?? '');
      if ($href === '' || $href === '#') {
        if (($r['code'] ?? '') === 'SF2') {
          $href = '/component/sf-report/learners_attendance'; // new SF2 route
        } else {
          $href = '#';
        }
      }
    ?>
      <a class="sf-report__tile" href="<?= e($href) ?>" data-href="<?= e($href) ?>">
        <div class="sf-report__thumb">
          <?php if ($imgSrc): ?>
            <img src="<?= e($imgSrc) ?>" alt="Report" loading="lazy">
          <?php else: ?>
            <div class="sf-report__thumb-placeholder">
              <i class="fa fa-file-alt"></i>
            </div>
          <?php endif; ?>
        </div>
        <div class="sf-report__caption">
          <div class="sf-report__name"><?= e($r['title'] ?? '') ?></div>
          <div class="sf-report__code">(<?= e($r['code'] ?? '') ?>)</div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>

</div>

<style>
.sf-report { padding: 8px; }
.sf-report__header { margin: 8px 8px 18px; }
.sf-report__title { margin: 0 0 8px 0; font-weight: 800; letter-spacing: .5px; }

.sf-report__grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(220px, 1fr));
  gap: 36px 48px;
  padding: 8px 12px 24px;
}
@media (max-width: 1199px) { .sf-report__grid { grid-template-columns: repeat(2, minmax(220px, 1fr)); } }
@media (max-width: 575px)  { .sf-report__grid { grid-template-columns: 1fr; } }

.sf-report__tile {
  display: flex; flex-direction: column; align-items: center;
  text-decoration: none; background: #fff; border-radius: 14px;
  box-shadow: 0 1px 0 rgba(0,0,0,.02), 0 6px 22px rgba(0,0,0,.06);
  padding: 20px 18px 16px; transition: transform .12s, box-shadow .12s; color: inherit;
}
.sf-report__tile:hover { transform: translateY(-3px); box-shadow: 0 2px 0 rgba(0,0,0,.03), 0 12px 28px rgba(0,0,0,.10); }

.sf-report__thumb { display: grid; place-items: center; width: 180px; height: 140px; margin-bottom: 14px; }
.sf-report__thumb img { width: 100%; height: auto; display: block; }
.sf-report__thumb-placeholder { width: 120px; height: 120px; border-radius: 12px; display: grid; place-items: center; background: #f2f4f7; }
.sf-report__thumb-placeholder .fa { font-size: 48px; opacity: .6; }

.sf-report__caption { text-align: center; color: #222; }
.sf-report__name { font-weight: 600; margin-bottom: 2px; }
.sf-report__code { font-weight: 800; }
</style>
