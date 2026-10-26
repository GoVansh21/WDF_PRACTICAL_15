<?php
require_once __DIR__.'/config/database.php'; require_once __DIR__.'/includes/functions.php'; require_admin();
$logs=$conn->query('SELECT a.*,u.name AS user_name FROM audit_logs a LEFT JOIN users u ON a.user_id=u.id ORDER BY a.id DESC LIMIT 100');
$pageTitle='Admin panel'; require __DIR__.'/includes/header.php';
?>
<h1 class="h3">Admin panel</h1><p>Latest 100 audit events.</p>
<div class="table-responsive card p-2"><table class="table table-striped"><thead><tr><th>Time</th><th>User</th><th>Action</th><th>Details</th><th>IP</th></tr></thead><tbody><?php while($r=$logs->fetch_assoc()): ?><tr><td><?= e($r['created_at']) ?></td><td><?= e($r['user_name']??'System/deleted user') ?></td><td><?= e($r['action']) ?></td><td><?= e($r['details']) ?></td><td><?= e($r['ip_address']) ?></td></tr><?php endwhile; ?></tbody></table></div>
<?php require __DIR__.'/includes/footer.php'; ?>
