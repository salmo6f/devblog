<?php
require_once __DIR__ . '/../src/bootstrap.php';
logout_user();
flash_set('info', 'You have been logged out.');
header('Location: ' . url_for('public/login.php'));
exit;
?>
