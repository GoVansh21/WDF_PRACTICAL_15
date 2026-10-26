<?php
require_once __DIR__.'/config/database.php'; require_once __DIR__.'/includes/functions.php'; require_login();
$admin=($_SESSION['role']??'')==='admin'; $errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
 verify_csrf();
 if (!$admin) { http_response_code(403); exit('Administrator access required.'); }
 $action=$_POST['action']??'';
 if ($action==='delete') {
  $id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
  if ($id) { $stmt=$conn->prepare('DELETE FROM students WHERE id=?'); $stmt->bind_param('i',$id); $stmt->execute(); audit($conn,(int)$_SESSION['user_id'],'student.delete','student_id='.$id); flash('success','Student deleted.'); }
  header('Location: students.php'); exit;
 }
 $name=trim($_POST['name']??''); $email=strtolower(trim($_POST['email']??'')); $course=trim($_POST['course']??''); $id=(int)($_POST['id']??0);
 if ($name==='' || mb_strlen($name)>100) $errors[]='Name is required (maximum 100 characters).';
 if (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>190) $errors[]='Enter a valid email.';
 if ($course==='' || mb_strlen($course)>100) $errors[]='Course is required (maximum 100 characters).';
 if (!$errors) {
  if ($id>0) { $stmt=$conn->prepare('UPDATE students SET name=?,email=?,course=? WHERE id=?'); $stmt->bind_param('sssi',$name,$email,$course,$id); $stmt->execute(); audit($conn,(int)$_SESSION['user_id'],'student.update','student_id='.$id); flash('success','Student updated.'); }
  else { $stmt=$conn->prepare('INSERT INTO students (name,email,course) VALUES (?,?,?)'); $stmt->bind_param('sss',$name,$email,$course); $stmt->execute(); audit($conn,(int)$_SESSION['user_id'],'student.create','student_id='.$conn->insert_id); flash('success','Student added.'); }
  header('Location: students.php'); exit;
 }
}
$q=trim($_GET['q']??'');
if ($q!=='') { $like='%'.$q.'%'; $stmt=$conn->prepare('SELECT id,name,email,course,created_at FROM students WHERE name LIKE ? OR email LIKE ? OR course LIKE ? ORDER BY id DESC'); $stmt->bind_param('sss',$like,$like,$like); $stmt->execute(); $rows=$stmt->get_result(); }
else $rows=$conn->query('SELECT id,name,email,course,created_at FROM students ORDER BY id DESC');
$edit=null;
if ($admin && isset($_GET['edit']) && ctype_digit((string)$_GET['edit'])) { $id=(int)$_GET['edit']; $stmt=$conn->prepare('SELECT id,name,email,course FROM students WHERE id=?'); $stmt->bind_param('i',$id); $stmt->execute(); $edit=$stmt->get_result()->fetch_assoc() ?: null; }
$pageTitle='Students'; require __DIR__.'/includes/header.php';
?>
<h1 class="h3 mb-3">Student management</h1>
<?php foreach($errors as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
<form class="row g-2 mb-3" method="get"><div class="col-sm-9"><label class="visually-hidden" for="q">Search students</label><input class="form-control" id="q" name="q" placeholder="Search name, email or course" value="<?= e($q) ?>"></div><div class="col-sm-3 d-grid"><button class="btn btn-outline-primary">Search</button></div></form>
<?php if($admin): ?><div class="card p-3 mb-4"><h2 class="h5"><?= $edit?'Edit student':'Add student' ?></h2><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>"><div class="row g-3"><div class="col-md-4"><label class="form-label required" for="name">Name</label><input class="form-control" id="name" name="name" maxlength="100" required value="<?= e($edit['name']??$_POST['name']??'') ?>"></div><div class="col-md-4"><label class="form-label required" for="email">Email</label><input class="form-control" type="email" id="email" name="email" maxlength="190" required value="<?= e($edit['email']??$_POST['email']??'') ?>"></div><div class="col-md-4"><label class="form-label required" for="course">Course</label><input class="form-control" id="course" name="course" maxlength="100" required value="<?= e($edit['course']??$_POST['course']??'') ?>"></div></div><button class="btn btn-primary mt-3"><?= $edit?'Update':'Add student' ?></button><?php if($edit): ?> <a class="btn btn-secondary mt-3" href="students.php">Cancel</a><?php endif; ?></form></div><?php endif; ?>
<div class="table-responsive card p-2"><table class="table table-striped mb-0"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Course</th><?php if($admin): ?><th>Actions</th><?php endif; ?></tr></thead><tbody>
<?php while($row=$rows->fetch_assoc()): ?><tr><td><?= (int)$row['id'] ?></td><td><?= e($row['name']) ?></td><td><?= e($row['email']) ?></td><td><?= e($row['course']) ?></td><?php if($admin): ?><td class="d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="students.php?edit=<?= (int)$row['id'] ?>">Edit</a><form method="post" data-confirm="Delete this student?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></td><?php endif; ?></tr><?php endwhile; ?>
</tbody></table></div>
<?php require __DIR__.'/includes/footer.php'; ?>
