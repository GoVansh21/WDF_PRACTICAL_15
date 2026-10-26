<?php
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/includes/functions.php';
if (is_logged_in()) { header('Location: dashboard.php'); exit; }
$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $name=trim($_POST['name']??''); $email=strtolower(trim($_POST['email']??''));
    $password=$_POST['password']??''; $confirm=$_POST['confirm_password']??'';
    if ($name==='' || mb_strlen($name)>100) $errors[]='Enter a name (maximum 100 characters).';
    if (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>190) $errors[]='Enter a valid email address.';
    if (strlen($password)<8) $errors[]='Password must contain at least 8 characters.';
    if ($password!==$confirm) $errors[]='Passwords do not match.';
    if (!$errors) {
        $stmt=$conn->prepare('SELECT id FROM users WHERE email=?'); $stmt->bind_param('s',$email); $stmt->execute();
        if ($stmt->get_result()->num_rows) $errors[]='This email is already registered.';
    }
    if (!$errors) {
        $hash=password_hash($password,PASSWORD_DEFAULT); $role='student';
        $stmt=$conn->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)');
        $stmt->bind_param('ssss',$name,$email,$hash,$role); $stmt->execute();
        audit($conn,(int)$conn->insert_id,'user.register','Self registration');
        flash('success','Registration successful. You can now log in.');
        header('Location: login.php'); exit;
    }
}
$pageTitle='Register'; require __DIR__.'/includes/header.php';
?>
<h1 class="h3 mb-3">Create account</h1>
<?php foreach($errors as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
<form method="post" class="card p-4" autocomplete="on">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="mb-3"><label class="form-label required" for="name">Full name</label><input class="form-control" id="name" name="name" maxlength="100" required value="<?= e($_POST['name']??'') ?>"></div>
<div class="mb-3"><label class="form-label required" for="email">Email</label><input class="form-control" type="email" id="email" name="email" maxlength="190" required value="<?= e($_POST['email']??'') ?>"></div>
<div class="mb-3"><label class="form-label required" for="password">Password (minimum 8 characters)</label><input class="form-control" type="password" id="password" name="password" minlength="8" required></div>
<div class="mb-3"><label class="form-label required" for="confirm_password">Confirm password</label><input class="form-control" type="password" id="confirm_password" name="confirm_password" minlength="8" required></div>
<button class="btn btn-primary">Register</button>
</form>
<?php require __DIR__.'/includes/footer.php'; ?>
