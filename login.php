<?php
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/includes/functions.php';
if (is_logged_in()) { header('Location: dashboard.php'); exit; }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 verify_csrf();
 $email=strtolower(trim($_POST['email']??'')); $password=$_POST['password']??'';
 if (!filter_var($email,FILTER_VALIDATE_EMAIL) || $password==='') $error='Enter a valid email and password.';
 else {
  $stmt=$conn->prepare('SELECT id,name,email,password_hash,role,is_active FROM users WHERE email=?');
  $stmt->bind_param('s',$email); $stmt->execute(); $user=$stmt->get_result()->fetch_assoc();
  if ($user && (int)$user['is_active']===1 && password_verify($password,$user['password_hash'])) {
   session_regenerate_id(true);
   $_SESSION['user_id']=(int)$user['id']; $_SESSION['name']=$user['name']; $_SESSION['email']=$user['email']; $_SESSION['role']=$user['role']; $_SESSION['last_activity']=time();
   $stmt=$conn->prepare('UPDATE users SET last_login=NOW() WHERE id=?'); $stmt->bind_param('i',$_SESSION['user_id']); $stmt->execute();
   audit($conn,$_SESSION['user_id'],'user.login','Successful login');
   header('Location: dashboard.php'); exit;
  }
  $error='Login failed. Check your credentials or account status.';
 }
}
$pageTitle='Login'; require __DIR__.'/includes/header.php';
?>
<h1 class="h3 mb-3">Login</h1>
<?php if(isset($_GET['timeout'])): ?><div class="alert alert-warning">Your session expired. Please log in again.</div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="card p-4" autocomplete="on">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="mb-3"><label class="form-label required" for="email">Email</label><input class="form-control" type="email" id="email" name="email" required autocomplete="username" value="<?= e($_POST['email']??'') ?>"></div>
<div class="mb-3"><label class="form-label required" for="password">Password</label><input class="form-control" type="password" id="password" name="password" required autocomplete="current-password"></div>
<button class="btn btn-primary">Login</button>
</form>
<?php require __DIR__.'/includes/footer.php'; ?>
