<?php

require_once '../includes/auth.php';
session_destroy();

unset($_SESSION);
header("Location: index.php");
exit();

?>
