<?php
require_once 'config/auth.php';
logout_user();
header('Location: login.php');
exit;
?>
