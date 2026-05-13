<?php

session_start();

function isUserLoggedIn() {
    return isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true;
}

function redirectIfNotLoggedIn() {
    if (!isUserLoggedIn()) {
        header("Location: ../pages/login.php");
        exit();
    }
}

function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}


?>
