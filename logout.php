<?php
session_start();
$_SESSION = [];
session_unset();
session_destroy();

// Arahkan kembali ke halaman login
header("Location: login.php");
exit;
?>
