<?php
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/includes/functions.php';
if (is_logged_in()) audit($conn,(int)$_SESSION['user_id'],'user.logout','User logged out');
$_SESSION=[];
if (ini_get('session.use_cookies')) {
 $p=session_get_cookie_params();
 setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']);
}
session_destroy();
header('Location: index.php'); exit;
