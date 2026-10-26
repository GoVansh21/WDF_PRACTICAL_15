<?php
require_once __DIR__.'/includes/functions.php';
$pageTitle='Welcome to StudentHub';
require __DIR__.'/includes/header.php';
?>
<section class="hero">
 <h1 class="display-5 fw-bold">StudentHub Portal</h1>
 <p class="lead">A student and event management portal built with PHP, MySQL, JavaScript and Bootstrap.</p>
 <?php if (is_logged_in()): ?><a class="btn btn-primary btn-lg" href="dashboard.php">Open dashboard</a><?php else: ?><a class="btn btn-primary btn-lg me-2" href="login.php">Login</a><a class="btn btn-outline-primary btn-lg" href="register.php">Create account</a><?php endif; ?>
</section>
<div class="row g-3 mt-3">
 <div class="col-md-4"><div class="card p-3 h-100"><h2 class="h5">Student management</h2><p>Add, search, edit and delete student records with prepared statements.</p></div></div>
 <div class="col-md-4"><div class="card p-3 h-100"><h2 class="h5">Event management</h2><p>Manage events and safely validate poster uploads.</p></div></div>
 <div class="col-md-4"><div class="card p-3 h-100"><h2 class="h5">Secure access</h2><p>Session authentication, role checks, password hashing and CSRF protection.</p></div></div>
</div>
<?php require __DIR__.'/includes/footer.php'; ?>
