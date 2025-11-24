<?php
require_once __DIR__ . '/config.php';

// Destroy session
session_destroy();

// Redirect to home
header('Location: home.php');
exit;
?>
