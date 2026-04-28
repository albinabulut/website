<?php
session_start();
$_SESSION = array();
session_destroy();

if (isset($_COOKIE['gk_user'])) {
    setcookie('gk_user', '', time() - 3600, "/");
}

header("Location: index.php");
exit;