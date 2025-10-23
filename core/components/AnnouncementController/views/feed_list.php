<style>
.annc-wrap { margin-bottom: 1.25rem; }
.annc-card { border: 1px solid #e8edf6; border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 6px 16px rgba(15,23,42,.06); transition: box-shadow .2s ease, transform .2s ease; }
.annc-card:hover { box-shadow: 0 10px 22px rgba(15,23,42,.08); transform: translateY(-1px); }

.annc-head { display:flex; align-items:center; justify-content:space-between; padding: .85rem 1rem; background:#f8fafc; border-bottom:1px solid #eef2f7; }
.annc-title { margin:0; font-weight:800; font-size:1rem; color:#111827; letter-spacing:.2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.annc-meta { display:flex; flex-wrap:wrap; gap:8px; padding: .65rem 1rem .25rem 1rem; border-bottom:1px solid #f1f5f9; background:#fff; }
.chip { display:inline-flex; align-items:center; gap:6px; padding:6px 10px; font-size:.78rem; font-weight:600; border-radius:999px; border:1px solid #e5e7eb; color:#334155; background:#fff; }
.chip i { font-size:.9em; opacity:.8; }

.chip.scope-all{ border-color:#dbeafe; background:#eff6ff; color:#1d4ed8; }
.chip.scope-students{ border-color:#dcfce7; background:#f0fdf4; color:#166534; }
.chip.scope-teachers{ border-color:#fee2e2; background:#fef2f2; color:#991b1b; }

.chip.status-active{ border-color:#bbf7d0; background:#f0fdf4; color:#166534; }
.chip.status-inactive{ border-color:#fde68a; background:#fffbeb; color:#92400e; }

.annc-body { padding: .85rem 1rem 1rem 1rem; color:#0f172a; }
.annc-body p { margin-bottom:.65rem; line-height:1.5; }
.annc-dates { color:#64748b; font-size:.85rem; margin-bottom:.4rem; }

.annc-text { position: relative; }
.annc-text.collapsed { max-height: 5.5rem; overflow: hidden; }
.annc-fade { content:""; position:absolute; bottom:0; left:0; right:0; height:2.2rem; background: linear-gradient(to bottom, rgba(255,255,255,0), #fff); display:none; }
.annc-text.collapsed + .annc-fade { display:block; }
.readmore-btn { background:none; border:0; color:#1d4ed8; font-weight:700; font-size:.85rem; padding:0; cursor:pointer; }

.annc-gallery { margin-top:.5rem; }
.annc-gallery .annc-img { width:100%; height:180px; object-fit:cover; border-radius:10px; border:1px solid #e5e7eb; margin-bottom:.6rem; }

.annc-empty { padding:1.25rem; border:1px dashed #e5e7eb; border-radius:12px; background:#fafafa; text-align:center; color:#64748b; }

.card.table-card { border-radius:12px; border:1px solid #eef2f7; overflow:hidden; }
.card.table-card .card-header { background:#f8fafc; border-bottom:1px solid #eef2f7; }
.card.table-card .card-block { padding:1rem; }

@media (max-width:576px){ .annc-head{ padding:.75rem .85rem; } .annc-meta{ padding:.5rem .85rem; } .annc-body{ padding:.75rem .85rem .85rem .85rem; } }
</style>

<div class="col-xl-12 col-md-12 annc-wrap">
  <div class="card table-card">
    <div class="card-header"><h5 class="mb-0">Announcements</h5></div>
    <div class="card-block">

      <?php if(isset($list) && count($list) > 0): ?>
        <?php foreach ($list as $row): ?>
          <?php
            $scope   = isset($row['audience_scope']) ? strtolower($row['audience_scope']) : 'all';
            $statusV = isset($row['status']) ? ((int)$row['status'] === 1 ? 'active' : 'inactive') : null;
            $sd = !empty($row['start_date']) ? date('M d, Y g:i A', strtotime($row['start_date'])) : '';
            $ed = !empty($row['end_date'])   ? date('M d, Y g:i A', strtotime($row['end_date']))   : '';
            $scopeClass = ($scope === 'students') ? 'scope-students' : (($scope === 'teachers') ? 'scope-teachers' : 'scope-all');
          ?>
          <div class="annc-card mb-3">
            <div class="annc-head">
              <h6 class="annc-title" title="<?= htmlspecialchars($row['title']) ?>"><?= htmlspecialchars($row['title']) ?></h6>
              <div class="d-none d-sm-flex" style="gap:8px;">
                <span class="chip <?= $scopeClass ?>"><i class="fa fa-users"></i><?= strtoupper(htmlspecialchars($scope)) ?></span>
                <?php if ($statusV !== null): ?>
                  <span class="chip <?= $statusV === 'active' ? 'status-active' : 'status-inactive' ?>"><i class="fa fa-check-circle-o"></i><?= strtoupper($statusV) ?></span>
                <?php endif; ?>
              </div>
            </div>

            <div class="annc-meta d-sm-none">
              <span class="chip <?= $scopeClass ?>"><i class="fa fa-users"></i><?= strtoupper(htmlspecialchars($scope)) ?></span>
              <?php if ($statusV !== null): ?>
                <span class="chip <?= $statusV === 'active' ? 'status-active' : 'status-inactive' ?>"><i class="fa fa-check-circle-o"></i><?= strtoupper($statusV) ?></span>
              <?php endif; ?>
            </div>

            <div class="annc-body">
              <?php if ($sd || $ed): ?>
                <div class="annc-dates">
                  <i class="fa fa-clock-o"></i>
                  <?php if ($sd): ?><strong>Visible From:</strong> <?= htmlspecialchars($sd) ?><?php endif; ?>
                  <?php if ($ed): ?>&nbsp; &mdash; &nbsp; <strong>Until:</strong> <?= htmlspecialchars($ed) ?><?php endif; ?>
                </div>
              <?php endif; ?>

              <?php $bodyHtml = nl2br(htmlspecialchars($row['body'] ?? '')); ?>
              <div class="annc-text collapsed"><?= $bodyHtml ?></div>
              <div class="annc-fade"></div>
              <button type="button" class="readmore-btn" aria-expanded="false">Read more</button>

              <?php if (!empty($row["image"])): ?>
                <div class="annc-gallery mt-2"><div class="row">
                  <?php foreach (explode('|', $row["image"]) as $img): $src = htmlspecialchars($img); ?>
                    <div class="col-12 col-sm-6 col-md-4"><img src="<?= $src ?>" class="annc-img" alt="Announcement image"></div>
                  <?php endforeach; ?>
                </div></div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="annc-empty">
          <i class="fa fa-bullhorn" aria-hidden="true"></i>
          <div class="mt-2 fw-bold">No announcements at this time</div>
          <div class="mt-1" style="font-size:.9rem;">Check back later for updates for students and teachers.</div>
        </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<script>
(function(){
  var cards = document.querySelectorAll('.annc-card');
  cards.forEach(function(card){
    var text = card.querySelector('.annc-text');
    var btn  = card.querySelector('.readmore-btn');
    var fade = card.querySelector('.annc-fade');
    if (!text || !btn) return;

    var clone = text.cloneNode(true);
    clone.style.maxHeight = 'none';
    clone.classList.remove('collapsed');
    clone.style.position = 'absolute';
    clone.style.visibility = 'hidden';
    clone.style.pointerEvents = 'none';
    card.appendChild(clone);
    var full = clone.clientHeight;
    card.removeChild(clone);

    if (full <= 88) {
      btn.style.display = 'none';
      if (fade) fade.style.display = 'none';
      text.classList.remove('collapsed');
      return;
    }

    btn.addEventListener('click', function(){
      var collapsed = text.classList.toggle('collapsed');
      if (fade) fade.style.display = collapsed ? 'block' : 'none';
      btn.textContent = collapsed ? 'Read more' : 'Show less';
      btn.setAttribute('aria-expanded', (!collapsed).toString());
    });
  });
})();
</script>
