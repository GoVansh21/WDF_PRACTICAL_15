<?php
require_once __DIR__.'/includes/functions.php';
$dataFile=__DIR__.'/storage/contact-submissions.csv';
$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
 verify_csrf();
 $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $message=trim($_POST['message']??'');
 if ($name===''||mb_strlen($name)>100) $errors[]='Name is required and must be 100 characters or fewer.';
 if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Enter a valid email address.';
 if ($message===''||mb_strlen($message)>2000) $errors[]='Message is required and must be 2000 characters or fewer.';
 if (!$errors) {
  $dir=dirname($dataFile); if (!is_dir($dir)) mkdir($dir,0750,true);
  $fp=fopen($dataFile,'ab');
  if (!$fp) $errors[]='Could not open storage file.';
  else {
   if (flock($fp,LOCK_EX)) {
    fputcsv($fp,[date('c'),$name,$email,$message]); fflush($fp); flock($fp,LOCK_UN);
    flash('success','Your contact form was submitted successfully.'); fclose($fp); header('Location: form-storage.php'); exit;
   } else { $errors[]='Could not lock storage file.'; fclose($fp); }
  }
 }
}
$pageTitle='Contact form'; require __DIR__.'/includes/header.php';
?>
<h1 class="h3">Contact form (CSV file storage)</h1><p>Demonstrates the file-storage form-processing practical.</p>
<?php foreach($errors as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
<form method="post" class="card p-4"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="mb-3"><label class="form-label required" for="name">Name</label><input class="form-control" id="name" name="name" maxlength="100" required></div><div class="mb-3"><label class="form-label required" for="email">Email</label><input class="form-control" type="email" id="email" name="email" required></div><div class="mb-3"><label class="form-label required" for="message">Message</label><textarea class="form-control" id="message" name="message" maxlength="2000" rows="4" required></textarea></div><button class="btn btn-primary">Submit</button></form>
<?php require __DIR__.'/includes/footer.php'; ?>
