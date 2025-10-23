<?php // components/ManageGradelevelController/views/custom.php ?>
<style>
  /* Width helper for your custom modals opened from this page */
  .modal-l { width: 600px; }

  /* Optional page heading polish (can be removed if you don’t want it) */
  .gl-page-title{
    font-weight:800; font-size:1.5rem; color:#0f172a; margin-bottom:.25rem;
  }
  .gl-page-sub{ color:#64748b; margin-bottom:1rem; }
</style>

<div class="col-xl-12 col-md-12">
  <!-- <div class="gl-page-title">Grade Levels</div>
  <div class="gl-page-sub">Create, edit, or remove grade levels used by sections and curricula.</div> -->

  <!-- Vanilla web component renders the table, actions, and modals -->
  <gradelevel-widget></gradelevel-widget>
</div>
