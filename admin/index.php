<?php
require_once 'includes/config.php';

// Redirect to dashboard if logged in, otherwise to login
if (isLoggedIn()) {
    header("Location: dashboard.php");
} else {
    header("Location: ../login.html");
}
exit();
?>