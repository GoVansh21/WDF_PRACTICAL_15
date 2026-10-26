<?php
require_once __DIR__.'/config/database.php'; require_once __DIR__.'/includes/functions.php'; require_login();
$admin=($_SESSION['role']??'')==='admin'; $errors=[];
$uploadDir=__DIR__.'/uploads/events/';
if (!is_dir($uploadDir)) mkdir($uploadDir,0755,true);
if ($_SERVER['REQUEST_METHOD']==='POST') {
 verify_csrf();
 if (!$admin) { http_response_code(403); exit('Administrator access required.'); }
 $action=$_POST['action']??'';
 if ($action==='delete') {
  $id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
  if ($id) { $stmt=$conn->prepare('DELETE FROM events WHERE id=?'); $stmt->bind_param('i',$id); $stmt->execute(); audit($conn,(int)$_SESSION['user_id'],'event.delete','event_id='.$id); flash('success','Event deleted.'); }
  header('Location: events.php'); exit;
 }
 $title=trim($_POST['title']??''); $description=trim($_POST['description']??''); $eventDate=trim($_POST['event_date']??''); $eventDate=($eventDate==='') ? null : $eventDate; $id=(int)($_POST['id']??0); $poster=null;
 if ($title==='' || mb_strlen($title)>150) $errors[]='Title is required (maximum 150 characters).';
 if ($eventDate!==null && !preg_match('/^\d{4}-\d{2}-\d{2}$/',$eventDate)) $errors[]='Enter a valid date.';
 if (!empty($_FILES['poster']['name'])) {
  if ($_FILES['poster']['error']!==UPLOAD_ERR_OK) $errors[]='Poster upload failed.';
  elseif ($_FILES['poster']['size']>2*1024*1024) $errors[]='Poster must be 2 MB or smaller.';
  else {
   $finfo=new finfo(FILEINFO_MIME_TYPE); $mime=$finfo->file($_FILES['poster']['tmp_name']);
   $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
   if (!isset($allowed[$mime])) $errors[]='Poster must be JPG, PNG, or WEBP.';
   else { $poster=bin2hex(random_bytes(16)).'.'.$allowed[$mime]; if (!move_uploaded_file($_FILES['poster']['tmp_name'],$uploadDir.$poster)) { $errors[]='Could not save poster.'; $poster=null; } }
  }
 }
 if (!$errors) {
  if ($id>0) {
   if ($poster) { $stmt=$conn->prepare('UPDATE events SET title=?,description=?,event_date=?,poster=? WHERE id=?'); $stmt->bind_param('ssssi',$title,$description,$eventDate,$poster,$id); }
   else { $stmt=$conn->prepare('UPDATE events SET title=?,description=?,event_date=? WHERE id=?'); $stmt->bind_param('sssi',$title,$description,$eventDate,$id); }
   $stmt->execute(); audit($conn,(int)$_SESSION['user_id'],'event.update','event_id='.$id);
  } else { $stmt=$conn->prepare('INSERT INTO events (title,description,event_date,poster,created_by) VALUES (?,?,?,?,?)'); $uid=(int)$_SESSION['user_id']; $stmt->bind_param('ssssi',$title,$description,$eventDate,$poster,$uid); $stmt->execute(); audit($conn,$uid,'event.create','event_id='.$conn->insert_id); }
  flash('success','Event saved successfully.'); header('Location: events.php'); exit;
 }
}
$events=$conn->query('SELECT e.*,u.name AS creator FROM events e LEFT JOIN users u ON e.created_by=u.id ORDER BY e.event_date DESC,e.id DESC');
$pageTitle='Events'; require __DIR__.'/includes/header.php';
?>
<h1 class="h3 mb-3">Events</h1><?php foreach($errors as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
<?php if($admin): ?><div class="card p-3 mb-4"><h2 class="h5">Add event</h2><form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save"><div class="mb-3"><label class="form-label required" for="title">Title</label><input class="form-control" id="title" name="title" maxlength="150" required></div><div class="mb-3"><label class="form-label" for="description">Description</label><textarea class="form-control" id="description" name="description" rows="3"></textarea></div><div class="row g-3"><div class="col-md-6"><label class="form-label" for="event_date">Event date</label><input class="form-control" type="date" id="event_date" name="event_date"></div><div class="col-md-6"><label class="form-label" for="poster">Poster (JPG/PNG/WEBP, max 2 MB)</label><input class="form-control" type="file" id="poster" name="poster" accept="image/jpeg,image/png,image/webp"></div></div><button class="btn btn-primary mt-3">Save event</button></form></div><?php endif; ?>
<div class="row g-3"><?php while($event=$events->fetch_assoc()): ?><div class="col-md-6 col-lg-4"><article class="card h-100"><?php if($event['poster']): ?><img src="uploads/events/<?= e($event['poster']) ?>" class="card-img-top" alt="Poster for <?= e($event['title']) ?>" style="max-height:220px;object-fit:cover"><?php endif; ?><div class="card-body"><h2 class="h5"><?= e($event['title']) ?></h2><p class="text-muted"><?= e($event['event_date']??'Date to be announced') ?></p><p><?= nl2br(e($event['description']??'')) ?></p><?php if($admin): ?><form method="post" data-confirm="Delete this event?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$event['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form><?php endif; ?></div></article></div><?php endwhile; ?></div>
<?php require __DIR__.'/includes/footer.php'; ?>
