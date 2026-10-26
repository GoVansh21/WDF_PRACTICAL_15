<?php
require_once __DIR__.'/config/database.php'; require_once __DIR__.'/includes/functions.php'; require_login();
$students=(int)$conn->query('SELECT COUNT(*) c FROM students')->fetch_assoc()['c'];
$events=(int)$conn->query('SELECT COUNT(*) c FROM events')->fetch_assoc()['c'];
$users=(int)$conn->query('SELECT COUNT(*) c FROM users')->fetch_assoc()['c'];
$regs=(int)$conn->query('SELECT COUNT(*) c FROM registrations')->fetch_assoc()['c'];
$pageTitle='Dashboard'; require __DIR__.'/includes/header.php';
?>
<h1 class="h3 mb-3">Dashboard</h1><p>Welcome, <?= e($_SESSION['name']) ?>.</p>
<div class="row g-3">
<?php foreach(['Students'=>$students,'Events'=>$events,'Users'=>$users,'Registrations'=>$regs] as $label=>$count): ?><div class="col-6 col-lg-3"><div class="card p-3"><div class="text-muted"><?= e($label) ?></div><div class="display-6 fw-bold"><?= $count ?></div></div></div><?php endforeach; ?>
</div>
<div class="card p-3 mt-4"><h2 class="h5">Quick links</h2><div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-primary" href="students.php">Manage students</a><a class="btn btn-outline-primary" href="events.php">View events</a><?php if(($_SESSION['role']??'')==='admin'): ?><a class="btn btn-outline-dark" href="admin.php">Admin panel & audit log</a><?php endif; ?></div>
<?php if(($_SESSION['role']??'')==='admin'): ?><p class="mt-3 mb-0">You have administrator access. Keep admin credentials private.</p><?php else: ?><p class="mt-3 mb-0">Student accounts can view the portal. Only administrators can modify records.</p><?php endif; ?></div>
<?php require __DIR__.'/includes/footer.php'; ?>
