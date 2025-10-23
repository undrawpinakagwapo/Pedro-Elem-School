<?php
/**
 * Access gate: Only Admin (1) and Principal (3) can view this page.
 * Adjust the session key or retrieval logic to match your auth system.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$currentRole  = (int)($_SESSION['user_type'] ?? 0); // e.g., 1=Admin, 3=Principal
$allowedRoles = [1, 3];

if (!in_array($currentRole, $allowedRoles, true)) {
    http_response_code(403);
    ?>
    <style>
      body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; background:#f8fafc; margin:0; }
      .forbidden-wrap { min-height: 100vh; display:flex; align-items:center; justify-content:center; }
      .forbidden-card {
        max-width: 560px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:28px; box-shadow:0 10px 24px rgba(2,6,23,.06);
        text-align:center;
      }
      .forbidden-card h1 { margin:0 0 6px; font-size:22px; color:#0f172a; }
      .forbidden-card p { margin:0; color:#475569; }
    </style>
    <div class="forbidden-wrap">
      <div class="forbidden-card">
        <h1>403 – Access Restricted</h1>
        <p>You don’t have permission to view this page.</p>
      </div>
    </div>
    <?php
    exit;
}

// If we got here, user is allowed.
// $list is supplied by the controller (already filtered to Admin/Principal)
$list = $list ?? [];
?>

<style>
    .modal-xl { width: 1140px; }
    .preview-img {
        width: 100%; height: 200px; object-fit: cover;
        border: 2px dashed #ccc; display: block; margin-top: 10px;
    }
</style>

<div class="col-xl-12 col-md-12">
    <button class="btn waves-effect waves-light btn-primary openmodaldetails-modal" data-type="add">
        <i class="fa fa-plus"></i>&nbsp;Add New
    </button>

    <div class="card table-card">
        <div class="card-header">
            <h5>Manage Users</h5>
            <div class="card-header-right">
                <ul class="list-unstyled card-option">
                    <li><i class="fa fa fa-wrench open-card-option"></i></li>
                    <li><i class="fa fa-window-maximize full-card"></i></li>
                    <li><i class="fa fa-minus minimize-card"></i></li>
                    <li><i class="fa fa-refresh reload-card"></i></li>
                    <li><i class="fa fa-trash close-card"></i></li>
                </ul>
            </div>
        </div>

        <div class="card-block">
            <div class="table-responsive">
                <table class="table table-hover" id="mainTable">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name / Username</th>
                            <th>Email</th>
                            <th>User Role</th>
                            <th>Date Registered</th>
                            <th>Status</th>
                            <th style="width:110px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if (!empty($list)) {
                                $roleMap = [
                                    1 => 'Admin',
                                    2 => 'Teacher',
                                    3 => 'Principal',
                                    5 => 'Student',
                                ];
                                $statusMap = [
                                    1 => '<label class="label label-success">ACTIVE</label>',
                                    0 => '<label class="label label-danger">INACTIVE</label>',
                                ];
                                foreach ($list as $row) {
                                    $roleLabel   = $roleMap[$row["user_type"]] ?? $row["user_type"];
                                    $statusLabel = $statusMap[$row["status"]] ?? $row["status"];
                                    $fullName = trim(
                                        ($row["account_last_name"] ?? '') . ', ' .
                                        ($row["account_first_name"] ?? '') . ', ' .
                                        ($row["account_middle_name"] ?? '')
                                    );
                                    if ($fullName === ', ,') $fullName = '';
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row["user_id"]) ?></td>
                                        <td>
                                            <div style="font-weight:600;">
                                                <?= $fullName ? htmlspecialchars($fullName) : htmlspecialchars($row["username"] ?? '') ?>
                                            </div>
                                            <?php if ($fullName && !empty($row["username"])): ?>
                                                <div style="font-size:12px;color:#6b7280;">
                                                    Username: <?= htmlspecialchars($row["username"]) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($row["email"] ?? '') ?></td>
                                        <td><?= htmlspecialchars($roleLabel) ?></td>
                                        <td><?= htmlspecialchars($row["created_at"] ?? '') ?></td>
                                        <td><?= $statusLabel ?></td>
                                        <td>
                                            <button class="btn waves-effect waves-light btn-grd-primary btn-sm openmodaldetails-modal"
                                                    data-type="edit" data-id="<?= htmlspecialchars($row["user_id"]) ?>">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <!--
                                            <button class="btn waves-effect waves-light btn-grd-danger btn-sm delete"
                                                    data-id="<?= htmlspecialchars($row["user_id"]) ?>">
                                                <i class="fa fa-times"></i>
                                            </button>
                                            -->
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } 
                            else {
                                ?>
                                <!-- <tr>
                                    <td colspan="7">NO RECORD FOUND!</td>
                                </tr> -->
                                <?php
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
